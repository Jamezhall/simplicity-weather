<?php
/**
 * Installer and schema upgrades.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installer class.
 */
class Simplicity_Weather_Installer {

	/**
	 * Schema version.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Run installation.
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$locations_table = $wpdb->prefix . 'simplicity_weather_locations';
		$cache_table     = $wpdb->prefix . 'simplicity_weather_cache';
		$logs_table      = $wpdb->prefix . 'simplicity_weather_logs';

		$locations_sql = "CREATE TABLE {$locations_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			slug varchar(100) NOT NULL,
			name varchar(190) NOT NULL,
			latitude decimal(10,6) NOT NULL,
			longitude decimal(10,6) NOT NULL,
			timezone varchar(100) NOT NULL,
			refresh_interval int(11) unsigned NOT NULL DEFAULT 30,
			is_active tinyint(1) unsigned NOT NULL DEFAULT 1,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			KEY is_active (is_active)
		) {$charset_collate};";

		$cache_sql = "CREATE TABLE {$cache_table} (
			location_id bigint(20) unsigned NOT NULL,
			normalized_json longtext NOT NULL,
			source_json longtext NOT NULL,
			last_attempt_at datetime DEFAULT NULL,
			last_success_at datetime DEFAULT NULL,
			expires_at datetime DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			error_message text DEFAULT NULL,
			PRIMARY KEY  (location_id),
			KEY expires_at (expires_at)
		) {$charset_collate};";

		$logs_sql = "CREATE TABLE {$logs_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			location_id bigint(20) unsigned NOT NULL,
			requested_at datetime NOT NULL,
			response_code int(11) unsigned DEFAULT NULL,
			duration_ms int(11) unsigned DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			message text DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY location_id (location_id),
			KEY requested_at (requested_at)
		) {$charset_collate};";

		dbDelta( $locations_sql );
		dbDelta( $cache_sql );
		dbDelta( $logs_sql );

		update_option( 'simplicity_weather_db_version', self::DB_VERSION );

		$defaults = array(
			'default_units'        => 'metric',
			'default_refresh'      => 30,
			'enable_logging'       => 1,
			'log_retention_days'   => 30,
			'badge_text_color'     => '#ffffff',
			'badge_background_color' => '#1f2937',
			'badge_font_family'    => 'Inter, sans-serif',
			'badge_font_size'      => '14px',
			'badge_padding'        => '6px 12px',
			'badge_border_radius'  => '999px',
			'cleanup_on_uninstall' => 0,
			'github_repository'    => 'Jamezhall/simplicity-weather',
		);

		update_option( 'simplicity_weather_settings', wp_parse_args( get_option( 'simplicity_weather_settings', array() ), $defaults ) );
	}

	/**
	 * Upgrade schema when required.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		if ( self::DB_VERSION !== get_option( 'simplicity_weather_db_version' ) ) {
			self::install();
		}
	}
}
