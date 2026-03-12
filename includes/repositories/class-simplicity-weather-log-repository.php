<?php
/**
 * Log repository.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles fetch logs.
 */
class Simplicity_Weather_Log_Repository {

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
		$this->table = $wpdb->prefix . 'simplicity_weather_logs';
	}

	/**
	 * Insert log row.
	 *
	 * @param int         $location_id Location ID.
	 * @param int|null    $response_code Response code.
	 * @param int|null    $duration_ms Request duration.
	 * @param string      $status Status string.
	 * @param string|null $message Message.
	 * @return bool
	 */
	public function insert( $location_id, $response_code, $duration_ms, $status, $message ) {
		$result = $this->wpdb->insert(
			$this->table,
			array(
				'location_id'   => absint( $location_id ),
				'requested_at'  => current_time( 'mysql', true ),
				'response_code' => $response_code,
				'duration_ms'   => $duration_ms,
				'status'        => $status,
				'message'       => $message,
			),
			array( '%d', '%s', '%d', '%d', '%s', '%s' )
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		return false !== $result;
	}

	/**
	 * Delete logs by location.
	 *
	 * @param int $location_id Location ID.
	 * @return bool
	 */
	public function delete_by_location( $location_id ) {
		$result = $this->wpdb->delete( $this->table, array( 'location_id' => absint( $location_id ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		return false !== $result;
	}
}
