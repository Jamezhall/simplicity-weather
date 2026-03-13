<?php
/**
 * Plugin Name: Simplicity Weather
 * Plugin URI:  https://github.com/Jamezhall/simplicity-weather
 * Description: Pull current weather data from specific geolocations with admin management, shortcode rendering, and GitHub-based updates.
 * Version:     0.1.7
 * Author:      James Hall
 * Text Domain: simplicity-weather
 * Requires PHP: 7.4
 * Requires at least: 6.0
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SIMPLICITY_WEATHER_VERSION', '0.1.7' );
define( 'SIMPLICITY_WEATHER_FILE', __FILE__ );
define( 'SIMPLICITY_WEATHER_PATH', plugin_dir_path( __FILE__ ) );
define( 'SIMPLICITY_WEATHER_URL', plugin_dir_url( __FILE__ ) );
define( 'SIMPLICITY_WEATHER_BASENAME', plugin_basename( __FILE__ ) );

require_once SIMPLICITY_WEATHER_PATH . 'includes/class-simplicity-weather-plugin.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/frontend/template-functions.php';

register_activation_hook( SIMPLICITY_WEATHER_FILE, array( 'Simplicity_Weather_Plugin', 'activate' ) );
register_deactivation_hook( SIMPLICITY_WEATHER_FILE, array( 'Simplicity_Weather_Plugin', 'deactivate' ) );

Simplicity_Weather_Plugin::instance();
