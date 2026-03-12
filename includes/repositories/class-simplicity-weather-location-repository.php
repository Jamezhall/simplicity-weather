<?php
/**
 * Location repository.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles location persistence.
 */
class Simplicity_Weather_Location_Repository {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Database connection.
	 *
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * Constructor.
	 *
	 * @param wpdb $wpdb Database object.
	 */
	public function __construct( $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'simplicity_weather_locations';
	}

	/**
	 * Get all locations.
	 *
	 * @return array
	 */
	public function all() {
		$query = "SELECT * FROM {$this->table} ORDER BY name ASC"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $this->wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Get active locations.
	 *
	 * @return array
	 */
	public function active() {
		$query = $this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE is_active = %d ORDER BY name ASC", 1 );

		return $this->wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Find by ID.
	 *
	 * @param int $location_id Location ID.
	 * @return array|null
	 */
	public function find( $location_id ) {
		$query = $this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $location_id );

		return $this->wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Find by slug.
	 *
	 * @param string $slug Location slug.
	 * @return array|null
	 */
	public function find_by_slug( $slug ) {
		$query = $this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE slug = %s", $slug );

		return $this->wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Save a location.
	 *
	 * @param array $data Location data.
	 * @return int|WP_Error
	 */
	public function save( $data ) {
		$timestamp = current_time( 'mysql', true );
		$record    = array(
			'slug'             => $data['slug'],
			'name'             => $data['name'],
			'latitude'         => $data['latitude'],
			'longitude'        => $data['longitude'],
			'timezone'         => $data['timezone'],
			'refresh_interval' => $data['refresh_interval'],
			'is_active'        => $data['is_active'],
			'updated_at'       => $timestamp,
		);

		$formats = array( '%s', '%s', '%f', '%f', '%s', '%d', '%d', '%s' );

		if ( ! empty( $data['id'] ) ) {
			$result = $this->wpdb->update( $this->table, $record, array( 'id' => absint( $data['id'] ) ), $formats, array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

			if ( false === $result ) {
				return new WP_Error( 'simplicity_weather_location_update_failed', __( 'The location could not be updated.', 'simplicity-weather' ) );
			}

			return absint( $data['id'] );
		}

		$record['created_at'] = $timestamp;
		$formats[]            = '%s';

		$result = $this->wpdb->insert( $this->table, $record, $formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		if ( false === $result ) {
			return new WP_Error( 'simplicity_weather_location_insert_failed', __( 'The location could not be saved.', 'simplicity-weather' ) );
		}

		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Delete a location.
	 *
	 * @param int $location_id Location ID.
	 * @return bool
	 */
	public function delete( $location_id ) {
		$result = $this->wpdb->delete( $this->table, array( 'id' => absint( $location_id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		return false !== $result;
	}
}
