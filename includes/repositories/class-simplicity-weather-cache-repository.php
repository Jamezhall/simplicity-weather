<?php
/**
 * Cache repository.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles cached weather data.
 */
class Simplicity_Weather_Cache_Repository {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Database object.
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
		$this->table = $wpdb->prefix . 'simplicity_weather_cache';
	}

	/**
	 * Find cache row by location ID.
	 *
	 * @param int $location_id Location ID.
	 * @return array|null
	 */
	public function find_by_location( $location_id ) {
		$query = $this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE location_id = %d", $location_id );

		return $this->wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Upsert cache row.
	 *
	 * @param int         $location_id Location ID.
	 * @param array       $normalized Normalized payload.
	 * @param array       $source Source payload.
	 * @param string      $status Status string.
	 * @param string      $error_message Error message.
	 * @param string|null $last_success_at Last success time.
	 * @param string|null $expires_at Cache expiry time.
	 * @return bool
	 */
	public function upsert( $location_id, $normalized, $source, $status, $error_message, $last_success_at, $expires_at ) {
		$existing = $this->find_by_location( $location_id );
		$data     = array(
			'location_id'     => $location_id,
			'normalized_json' => wp_json_encode( $normalized ),
			'source_json'     => wp_json_encode( $source ),
			'last_attempt_at' => current_time( 'mysql', true ),
			'last_success_at' => $last_success_at,
			'expires_at'      => $expires_at,
			'status'          => $status,
			'error_message'   => $error_message,
		);

		$formats = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		if ( $existing ) {
			$result = $this->wpdb->update( $this->table, $data, array( 'location_id' => $location_id ), $formats, array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		} else {
			$result = $this->wpdb->insert( $this->table, $data, $formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		}

		return false !== $result;
	}

	/**
	 * Delete cache row for location.
	 *
	 * @param int $location_id Location ID.
	 * @return bool
	 */
	public function delete_by_location( $location_id ) {
		$result = $this->wpdb->delete( $this->table, array( 'location_id' => absint( $location_id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		return false !== $result;
	}
}
