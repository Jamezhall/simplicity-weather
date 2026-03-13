<?php
/**
 * Shared plugin service layer.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates repositories and provider actions.
 */
class Simplicity_Weather_Service {

	/**
	 * Location repository.
	 *
	 * @var Simplicity_Weather_Location_Repository
	 */
	protected $locations;

	/**
	 * Cache repository.
	 *
	 * @var Simplicity_Weather_Cache_Repository
	 */
	protected $cache;

	/**
	 * Log repository.
	 *
	 * @var Simplicity_Weather_Log_Repository
	 */
	protected $logs;

	/**
	 * Provider.
	 *
	 * @var Simplicity_Weather_Provider
	 */
	protected $provider;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->locations = new Simplicity_Weather_Location_Repository( $wpdb );
		$this->cache     = new Simplicity_Weather_Cache_Repository( $wpdb );
		$this->logs      = new Simplicity_Weather_Log_Repository( $wpdb );
		$this->provider  = new Simplicity_Weather_Open_Meteo_Provider();
	}

	/**
	 * Get plugin settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		$defaults = array(
			'default_units'        => 'metric',
			'default_refresh'      => 30,
			'enable_logging'       => 1,
			'log_retention_days'   => 30,
			'badge_text_color'     => '#ffffff',
			'badge_background_color' => '#1f2937',
			'badge_font_family'    => 'Inter, sans-serif',
			'badge_font_size'      => '14px',
			'badge_padding'        => '6px 12px',
			'badge_border_radius'  => '999px',
			'cleanup_on_uninstall' => 0,
			'github_repository'    => 'Jamezhall/simplicity-weather',
		);

		return wp_parse_args( get_option( 'simplicity_weather_settings', array() ), $defaults );
	}

	/**
	 * Get all locations.
	 *
	 * @return array
	 */
	public function get_locations() {
		return $this->locations->all();
	}

	/**
	 * Get location by ID.
	 *
	 * @param int $location_id Location ID.
	 * @return array|null
	 */
	public function get_location( $location_id ) {
		return $this->locations->find( $location_id );
	}

	/**
	 * Get location by slug.
	 *
	 * @param string $slug Slug.
	 * @return array|null
	 */
	public function get_location_by_slug( $slug ) {
		return $this->locations->find_by_slug( $slug );
	}

	/**
	 * Get cached weather by slug.
	 *
	 * @param string $slug Location slug.
	 * @return array|null
	 */
	public function get_weather_by_slug( $slug ) {
		$location = $this->get_location_by_slug( $slug );

		if ( empty( $location ) ) {
			return null;
		}

		$cache_row = $this->cache->find_by_location( (int) $location['id'] );

		if ( empty( $cache_row['normalized_json'] ) ) {
			return null;
		}

		$data = json_decode( $cache_row['normalized_json'], true );

		if ( ! is_array( $data ) ) {
			return null;
		}

		$data['meta']['is_stale'] = $this->is_cache_stale( $cache_row );

		return $data;
	}

	/**
	 * Get joined status rows for admin.
	 *
	 * @return array
	 */
	public function get_location_status_rows() {
		$rows      = array();
		$locations = $this->get_locations();

		foreach ( $locations as $location ) {
			$cache = $this->cache->find_by_location( (int) $location['id'] );
			$data  = ! empty( $cache['normalized_json'] ) ? json_decode( $cache['normalized_json'], true ) : array();
			$log   = $this->logs->latest_by_location( (int) $location['id'] );

			$rows[] = array(
				'location' => $location,
				'cache'    => $cache,
				'log'      => $log,
				'weather'  => is_array( $data ) ? $data : array(),
				'is_stale' => $cache ? $this->is_cache_stale( $cache ) : true,
			);
		}

		return $rows;
	}

	/**
	 * Save a location.
	 *
	 * @param array $input Raw input.
	 * @return int|WP_Error
	 */
	public function save_location( $input ) {
		$timezone = ! empty( $input['timezone'] ) ? sanitize_text_field( $input['timezone'] ) : 'Europe/London';

		if ( ! in_array( $timezone, timezone_identifiers_list(), true ) ) {
			return new WP_Error( 'simplicity_weather_invalid_timezone', __( 'Please select a valid timezone.', 'simplicity-weather' ) );
		}

		$data = array(
			'id'               => ! empty( $input['id'] ) ? absint( $input['id'] ) : 0,
			'slug'             => sanitize_title( $input['slug'] ),
			'name'             => sanitize_text_field( $input['name'] ),
			'latitude'         => round( (float) $input['latitude'], 6 ),
			'longitude'        => round( (float) $input['longitude'], 6 ),
			'timezone'         => $timezone,
			'refresh_interval' => max( 15, absint( $input['refresh_interval'] ) ),
			'is_active'        => ! empty( $input['is_active'] ) ? 1 : 0,
		);

		if ( empty( $data['slug'] ) || empty( $data['name'] ) ) {
			return new WP_Error( 'simplicity_weather_missing_fields', __( 'Name and slug are required.', 'simplicity-weather' ) );
		}

		if ( $data['latitude'] < -90 || $data['latitude'] > 90 || $data['longitude'] < -180 || $data['longitude'] > 180 ) {
			return new WP_Error( 'simplicity_weather_invalid_coordinates', __( 'Please enter valid latitude and longitude values.', 'simplicity-weather' ) );
		}

		$existing = $this->locations->find_by_slug( $data['slug'] );

		if ( $existing && (int) $existing['id'] !== (int) $data['id'] ) {
			return new WP_Error( 'simplicity_weather_duplicate_slug', __( 'That location slug already exists.', 'simplicity-weather' ) );
		}

		return $this->locations->save( $data );
	}

	/**
	 * Delete location and related records.
	 *
	 * @param int $location_id Location ID.
	 * @return bool
	 */
	public function delete_location( $location_id ) {
		$this->cache->delete_by_location( $location_id );
		$this->logs->delete_by_location( $location_id );

		return $this->locations->delete( $location_id );
	}

	/**
	 * Refresh all due locations.
	 *
	 * @return void
	 */
	public function refresh_due_locations() {
		$this->prune_logs_by_retention();

		foreach ( $this->locations->active() as $location ) {
			$cache = $this->cache->find_by_location( (int) $location['id'] );

			if ( ! $cache || $this->is_cache_stale( $cache ) ) {
				$this->refresh_location( (int) $location['id'] );
			}
		}
	}

	/**
	 * Refresh a single location.
	 *
	 * @param int $location_id Location ID.
	 * @return true|WP_Error
	 */
	public function refresh_location( $location_id ) {
		$location = $this->locations->find( $location_id );

		if ( empty( $location ) ) {
			return new WP_Error( 'simplicity_weather_location_missing', __( 'Location not found.', 'simplicity-weather' ) );
		}

		$result = $this->provider->fetch_current_weather( $location, $this->get_settings() );

		if ( is_wp_error( $result ) ) {
			$this->handle_refresh_failure( $location, $result );
			return $result;
		}

		$expires_at = gmdate( 'Y-m-d H:i:s', strtotime( '+' . absint( $location['refresh_interval'] ) . ' minutes', current_time( 'timestamp', true ) ) );

		$this->cache->upsert(
			(int) $location['id'],
			$result['normalized'],
			$result['source'],
			'success',
			'',
			current_time( 'mysql', true ),
			$expires_at
		);

		if ( ! empty( $this->get_settings()['enable_logging'] ) ) {
			$this->logs->insert( (int) $location['id'], $result['metrics']['response_code'], $result['metrics']['duration_ms'], 'success', __( 'Weather refreshed successfully.', 'simplicity-weather' ) );
		}

		return true;
	}

	/**
	 * Refresh all active locations immediately.
	 *
	 * @return array
	 */
	public function refresh_all_locations() {
		$results = array(
			'success' => 0,
			'error'   => 0,
			'total'   => 0,
		);

		foreach ( $this->locations->active() as $location ) {
			++$results['total'];

			$result = $this->refresh_location( (int) $location['id'] );

			if ( is_wp_error( $result ) ) {
				++$results['error'];
			} else {
				++$results['success'];
			}
		}

		$this->prune_logs_by_retention();

		return $results;
	}

	/**
	 * Get status page diagnostics.
	 *
	 * @return array
	 */
	public function get_diagnostics() {
		$rows             = $this->get_location_status_rows();
		$active_locations = $this->locations->active();
		$stale_count      = 0;
		$error_count      = 0;

		foreach ( $rows as $row ) {
			if ( $row['is_stale'] ) {
				++$stale_count;
			}

			if ( ! empty( $row['cache']['error_message'] ) || ( ! empty( $row['log']['status'] ) && 'error' === $row['log']['status'] ) ) {
				++$error_count;
			}
		}

		return array(
			'cron_registered' => (bool) wp_next_scheduled( Simplicity_Weather_Scheduler::EVENT_HOOK ),
			'next_run'        => wp_next_scheduled( Simplicity_Weather_Scheduler::EVENT_HOOK ),
			'active_count'    => count( $active_locations ),
			'stale_count'     => $stale_count,
			'error_count'     => $error_count,
		);
	}

	/**
	 * Get recent logs.
	 *
	 * @param array $filters Log filters.
	 * @return array
	 */
	public function get_logs( $filters = array() ) {
		return $this->logs->get_logs( $filters, 200 );
	}

	/**
	 * Get formatted badge text from cached weather.
	 *
	 * @param string $slug Location slug.
	 * @param string $fields Requested fields list.
	 * @param string $separator Output separator.
	 * @param string $before Before text.
	 * @return string
	 */
	public function get_badge_text( $slug, $fields = '', $separator = ', ', $before = '' ) {
		$data = $this->get_weather_by_slug( $slug );

		if ( empty( $data['current'] ) || empty( $data['location'] ) ) {
			return '';
		}

		return simplicity_weather_build_text_output(
			$data,
			simplicity_weather_parse_fields( $fields ),
			array(
				'before'       => $before,
				'separator'    => $separator,
				'show_updated' => true,
			)
		);
	}

	/**
	 * Prune logs using configured retention.
	 *
	 * @return int
	 */
	public function prune_logs_by_retention() {
		$days = isset( $this->get_settings()['log_retention_days'] ) ? absint( $this->get_settings()['log_retention_days'] ) : 30;

		return $this->logs->prune_older_than_days( $days );
	}

	/**
	 * Clear all logs.
	 *
	 * @return int
	 */
	public function clear_all_logs() {
		return $this->logs->clear_all();
	}

	/**
	 * Handle failed weather refresh.
	 *
	 * @param array    $location Location row.
	 * @param WP_Error $error Error object.
	 * @return void
	 */
	protected function handle_refresh_failure( $location, $error ) {
		$cache_row   = $this->cache->find_by_location( (int) $location['id'] );
		$normalized  = array();
		$source      = array();
		$last_success = null;

		if ( $cache_row ) {
			$normalized   = json_decode( $cache_row['normalized_json'], true );
			$source       = json_decode( $cache_row['source_json'], true );
			$last_success = ! empty( $cache_row['last_success_at'] ) ? $cache_row['last_success_at'] : null;

			if ( is_array( $normalized ) ) {
				$normalized['meta']['is_stale'] = true;
				$normalized['meta']['status']   = 'error';
			}
		}

		$this->cache->upsert(
			(int) $location['id'],
			is_array( $normalized ) ? $normalized : array(),
			is_array( $source ) ? $source : array(),
			'error',
			$error->get_error_message(),
			$last_success,
			null
		);

		if ( ! empty( $this->get_settings()['enable_logging'] ) ) {
			$this->logs->insert( (int) $location['id'], 0, 0, 'error', $error->get_error_message() );
		}
	}

	/**
	 * Determine whether cache is stale.
	 *
	 * @param array $cache_row Cache row.
	 * @return bool
	 */
	protected function is_cache_stale( $cache_row ) {
		if ( empty( $cache_row['expires_at'] ) ) {
			return true;
		}

		return strtotime( $cache_row['expires_at'] ) <= current_time( 'timestamp', true );
	}
}
