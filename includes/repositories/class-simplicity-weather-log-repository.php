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

	/**
	 * Get the latest log row for a location.
	 *
	 * @param int $location_id Location ID.
	 * @return array|null
	 */
	public function latest_by_location( $location_id ) {
		$query = $this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE location_id = %d ORDER BY requested_at DESC, id DESC LIMIT 1", absint( $location_id ) );

		return $this->wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Get filtered logs.
	 *
	 * @param array $filters Optional log filters.
	 * @param int   $limit Result limit.
	 * @return array
	 */
	public function get_logs( $filters = array(), $limit = 100 ) {
		$where  = array();
		$values = array();

		if ( ! empty( $filters['location_id'] ) ) {
			$where[]  = 'location_id = %d';
			$values[] = absint( $filters['location_id'] );
		}

		if ( ! empty( $filters['status'] ) ) {
			$where[]  = 'status = %s';
			$values[] = sanitize_key( $filters['status'] );
		}

		$sql = "SELECT * FROM {$this->table}"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		$sql     .= ' ORDER BY requested_at DESC, id DESC LIMIT %d';
		$values[] = absint( $limit );

		$query = $this->wpdb->prepare( $sql, $values );

		return $this->wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Prune logs older than a retention window.
	 *
	 * @param int $days Retention days.
	 * @return int
	 */
	public function prune_older_than_days( $days ) {
		$days = absint( $days );

		if ( $days <= 0 ) {
			return 0;
		}

		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $days . ' days', current_time( 'timestamp', true ) ) );
		$query  = $this->wpdb->prepare( "DELETE FROM {$this->table} WHERE requested_at < %s", $cutoff );
		$result = $this->wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		return false === $result ? 0 : (int) $result;
	}

	/**
	 * Delete all logs.
	 *
	 * @return int
	 */
	public function clear_all() {
		$result = $this->wpdb->query( "TRUNCATE TABLE {$this->table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared

		return false === $result ? 0 : 1;
	}
}
