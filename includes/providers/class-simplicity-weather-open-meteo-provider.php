<?php
/**
 * Open-Meteo provider.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current weather provider using Open-Meteo.
 */
class Simplicity_Weather_Open_Meteo_Provider implements Simplicity_Weather_Provider {

	/**
	 * Fetch current weather data.
	 *
	 * @param array $location Location row.
	 * @param array $settings Plugin settings.
	 * @return array|WP_Error
	 */
	public function fetch_current_weather( $location, $settings ) {
		$units = isset( $settings['default_units'] ) && 'imperial' === $settings['default_units'] ? 'fahrenheit' : 'celsius';

		$query = array(
			'latitude'               => $location['latitude'],
			'longitude'              => $location['longitude'],
			'timezone'               => $location['timezone'],
			'current'                => 'temperature_2m,apparent_temperature,wind_speed_10m,wind_direction_10m,weather_code',
			'temperature_unit'       => $units,
			'wind_speed_unit'        => 'imperial' === $settings['default_units'] ? 'mph' : 'kmh',
		);

		$url      = add_query_arg( $query, 'https://api.open-meteo.com/v1/forecast' );
		$start    = microtime( true );
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data['current'] ) || ! is_array( $data['current'] ) ) {
			return new WP_Error( 'simplicity_weather_invalid_response', __( 'Open-Meteo returned an invalid response.', 'simplicity-weather' ) );
		}

		$current = $data['current'];
		$code    = isset( $current['weather_code'] ) ? (int) $current['weather_code'] : -1;

		return array(
			'normalized' => array(
				'location' => array(
					'slug'      => $location['slug'],
					'name'      => $location['name'],
					'latitude'  => (float) $location['latitude'],
					'longitude' => (float) $location['longitude'],
					'timezone'  => $location['timezone'],
				),
				'current'  => array(
					'temperature'          => isset( $current['temperature_2m'] ) ? (float) $current['temperature_2m'] : null,
					'temperature_unit'     => isset( $data['current_units']['temperature_2m'] ) ? $data['current_units']['temperature_2m'] : '',
					'apparent_temperature' => isset( $current['apparent_temperature'] ) ? (float) $current['apparent_temperature'] : null,
					'wind_speed'           => isset( $current['wind_speed_10m'] ) ? (float) $current['wind_speed_10m'] : null,
					'wind_speed_unit'      => isset( $data['current_units']['wind_speed_10m'] ) ? $data['current_units']['wind_speed_10m'] : '',
					'wind_direction'       => isset( $current['wind_direction_10m'] ) ? (int) $current['wind_direction_10m'] : null,
					'weather_code'         => $code,
					'condition_label'      => Simplicity_Weather_Condition_Mapper::get_label( $code ),
					'observed_at'          => isset( $current['time'] ) ? sanitize_text_field( $current['time'] ) : '',
				),
				'meta'     => array(
					'provider'      => 'open-meteo',
					'last_updated'  => current_time( 'mysql', true ),
					'is_stale'      => false,
					'status'        => 'success',
				),
			),
			'source'     => $data,
			'metrics'    => array(
				'response_code' => (int) wp_remote_retrieve_response_code( $response ),
				'duration_ms'   => (int) round( ( microtime( true ) - $start ) * 1000 ),
			),
		);
	}
}
