<?php
/**
 * Cron scheduler.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scheduler class.
 */
class Simplicity_Weather_Scheduler {

	/**
	 * Cron event name.
	 *
	 * @var string
	 */
	const EVENT_HOOK = 'simplicity_weather_refresh_event';

	/**
	 * Service object.
	 *
	 * @var Simplicity_Weather_Service|null
	 */
	protected $service;

	/**
	 * Constructor.
	 *
	 * @param Simplicity_Weather_Service|null $service Shared service.
	 */
	public function __construct( $service = null ) {
		$this->service = $service;
	}

	/**
	 * Register scheduler hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'cron_schedules', array( $this, 'register_schedule' ) );
		add_action( self::EVENT_HOOK, array( $this, 'run' ) );

		$event = wp_get_scheduled_event( self::EVENT_HOOK );

		if ( $event && 'simplicity_weather_fifteen_minutes' !== $event->schedule ) {
			wp_unschedule_event( $event->timestamp, self::EVENT_HOOK );
			$event = false;
		}

		if ( ! $event ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'simplicity_weather_fifteen_minutes', self::EVENT_HOOK );
		}
	}

	/**
	 * Register custom cron interval.
	 *
	 * @param array $schedules Existing schedules.
	 * @return array
	 */
	public function register_schedule( $schedules ) {
		$schedules['simplicity_weather_fifteen_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every 15 Minutes (Simplicity Weather)', 'simplicity-weather' ),
		);

		return $schedules;
	}

	/**
	 * Run scheduled refresh.
	 *
	 * @return void
	 */
	public function run() {
		if ( $this->service ) {
			$this->service->refresh_due_locations();
		}
	}

	/**
	 * Activation handler.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! wp_next_scheduled( self::EVENT_HOOK ) ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'hourly', self::EVENT_HOOK );
		}
	}

	/**
	 * Deactivation handler.
	 *
	 * @return void
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( self::EVENT_HOOK );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::EVENT_HOOK );
		}
	}
}
