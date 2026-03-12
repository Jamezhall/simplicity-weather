<?php
/**
 * Weather provider interface.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provider contract.
 */
interface Simplicity_Weather_Provider {

	/**
	 * Fetch current weather for a location.
	 *
	 * @param array $location Location row.
	 * @param array $settings Plugin settings.
	 * @return array|WP_Error
	 */
	public function fetch_current_weather( $location, $settings );
}
