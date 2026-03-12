<?php
/**
 * Uninstall handler for Simplicity Weather.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'simplicity_weather_settings', array() );

if ( empty( $settings['cleanup_on_uninstall'] ) ) {
	return;
}

global $wpdb;

$tables = array(
	$wpdb->prefix . 'simplicity_weather_logs',
	$wpdb->prefix . 'simplicity_weather_cache',
	$wpdb->prefix . 'simplicity_weather_locations',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
}

delete_option( 'simplicity_weather_settings' );
delete_option( 'simplicity_weather_db_version' );
