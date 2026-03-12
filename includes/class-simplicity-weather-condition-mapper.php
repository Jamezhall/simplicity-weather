<?php
/**
 * Weather condition label mapping.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps Open-Meteo weather codes to labels.
 */
class Simplicity_Weather_Condition_Mapper {

	/**
	 * Get condition label.
	 *
	 * @param int $code Weather code.
	 * @return string
	 */
	public static function get_label( $code ) {
		$map = array(
			0  => __( 'Clear sky', 'simplicity-weather' ),
			1  => __( 'Mainly clear', 'simplicity-weather' ),
			2  => __( 'Partly cloudy', 'simplicity-weather' ),
			3  => __( 'Overcast', 'simplicity-weather' ),
			45 => __( 'Fog', 'simplicity-weather' ),
			48 => __( 'Depositing rime fog', 'simplicity-weather' ),
			51 => __( 'Light drizzle', 'simplicity-weather' ),
			53 => __( 'Moderate drizzle', 'simplicity-weather' ),
			55 => __( 'Dense drizzle', 'simplicity-weather' ),
			56 => __( 'Light freezing drizzle', 'simplicity-weather' ),
			57 => __( 'Dense freezing drizzle', 'simplicity-weather' ),
			61 => __( 'Slight rain', 'simplicity-weather' ),
			63 => __( 'Moderate rain', 'simplicity-weather' ),
			65 => __( 'Heavy rain', 'simplicity-weather' ),
			66 => __( 'Light freezing rain', 'simplicity-weather' ),
			67 => __( 'Heavy freezing rain', 'simplicity-weather' ),
			71 => __( 'Slight snow', 'simplicity-weather' ),
			73 => __( 'Moderate snow', 'simplicity-weather' ),
			75 => __( 'Heavy snow', 'simplicity-weather' ),
			77 => __( 'Snow grains', 'simplicity-weather' ),
			80 => __( 'Slight rain showers', 'simplicity-weather' ),
			81 => __( 'Moderate rain showers', 'simplicity-weather' ),
			82 => __( 'Violent rain showers', 'simplicity-weather' ),
			85 => __( 'Slight snow showers', 'simplicity-weather' ),
			86 => __( 'Heavy snow showers', 'simplicity-weather' ),
			95 => __( 'Thunderstorm', 'simplicity-weather' ),
			96 => __( 'Thunderstorm with slight hail', 'simplicity-weather' ),
			99 => __( 'Thunderstorm with heavy hail', 'simplicity-weather' ),
		);

		return isset( $map[ $code ] ) ? $map[ $code ] : __( 'Unknown', 'simplicity-weather' );
	}
}
