<?php
/**
 * Main plugin bootstrap.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SIMPLICITY_WEATHER_PATH . 'includes/class-simplicity-weather-installer.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/class-simplicity-weather-scheduler.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/class-simplicity-weather-service.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/class-simplicity-weather-admin.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/class-simplicity-weather-shortcode.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/class-simplicity-weather-updater.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/class-simplicity-weather-condition-mapper.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/providers/interface-simplicity-weather-provider.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/providers/class-simplicity-weather-open-meteo-provider.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/repositories/class-simplicity-weather-location-repository.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/repositories/class-simplicity-weather-cache-repository.php';
require_once SIMPLICITY_WEATHER_PATH . 'includes/repositories/class-simplicity-weather-log-repository.php';

/**
 * Main plugin class.
 */
class Simplicity_Weather_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Simplicity_Weather_Plugin|null
	 */
	protected static $instance = null;

	/**
	 * Plugin service.
	 *
	 * @var Simplicity_Weather_Service
	 */
	protected $service;

	/**
	 * Get plugin instance.
	 *
	 * @return Simplicity_Weather_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate() {
		Simplicity_Weather_Installer::install();
		Simplicity_Weather_Scheduler::activate();
	}

	/**
	 * Deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate() {
		Simplicity_Weather_Scheduler::deactivate();
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->service = new Simplicity_Weather_Service();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'maybe_upgrade' ) );
		add_action( 'init', array( $this, 'register_frontend' ) );
		add_action( 'admin_menu', array( $this, 'register_admin' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'init', array( $this, 'register_scheduler' ) );
		add_action( 'init', array( $this, 'register_updater' ) );
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'simplicity-weather', false, dirname( SIMPLICITY_WEATHER_BASENAME ) . '/languages' );
	}

	/**
	 * Upgrade database when needed.
	 *
	 * @return void
	 */
	public function maybe_upgrade() {
		Simplicity_Weather_Installer::maybe_upgrade();
	}

	/**
	 * Register frontend integrations.
	 *
	 * @return void
	 */
	public function register_frontend() {
		$shortcode = new Simplicity_Weather_Shortcode( $this->service );
		$shortcode->register();
	}

	/**
	 * Register admin pages.
	 *
	 * @return void
	 */
	public function register_admin() {
		if ( ! is_admin() ) {
			return;
		}

		$admin = new Simplicity_Weather_Admin( $this->service );
		$admin->register_menu();
	}

	/**
	 * Handle admin actions.
	 *
	 * @return void
	 */
	public function handle_admin_actions() {
		if ( ! is_admin() ) {
			return;
		}

		$admin = new Simplicity_Weather_Admin( $this->service );
		$admin->handle_actions();
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( ! is_admin() ) {
			return;
		}

		register_setting( 'simplicity_weather_settings', 'simplicity_weather_settings', array( $this, 'sanitize_settings' ) );
	}

	/**
	 * Sanitize plugin settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array
	 */
	public function sanitize_settings( $settings ) {
		$current = $this->service->get_settings();

		$current['default_units']         = ( isset( $settings['default_units'] ) && 'imperial' === $settings['default_units'] ) ? 'imperial' : 'metric';
		$current['default_refresh']       = isset( $settings['default_refresh'] ) ? max( 15, absint( $settings['default_refresh'] ) ) : 30;
		$current['enable_logging']        = ! empty( $settings['enable_logging'] ) ? 1 : 0;
		$current['log_retention_days']    = isset( $settings['log_retention_days'] ) ? max( 0, absint( $settings['log_retention_days'] ) ) : 30;
		$current['badge_text_color']      = $this->sanitize_hex_setting( isset( $settings['badge_text_color'] ) ? $settings['badge_text_color'] : '', '#ffffff' );
		$current['badge_background_color'] = $this->sanitize_hex_setting( isset( $settings['badge_background_color'] ) ? $settings['badge_background_color'] : '', '#1f2937' );
		$current['badge_font_family']     = isset( $settings['badge_font_family'] ) ? sanitize_text_field( $settings['badge_font_family'] ) : 'Inter, sans-serif';
		$current['badge_padding']         = $this->sanitize_css_spacing_setting( isset( $settings['badge_padding'] ) ? $settings['badge_padding'] : '', '6px 12px' );
		$current['badge_border_radius']   = $this->sanitize_css_dimension_setting( isset( $settings['badge_border_radius'] ) ? $settings['badge_border_radius'] : '', '999px' );
		$current['cleanup_on_uninstall']  = ! empty( $settings['cleanup_on_uninstall'] ) ? 1 : 0;

		if ( '' === $current['badge_font_family'] ) {
			$current['badge_font_family'] = 'Inter, sans-serif';
		}

		return $current;
	}

	/**
	 * Sanitize a hex color setting.
	 *
	 * @param string $value Raw value.
	 * @param string $default Default value.
	 * @return string
	 */
	protected function sanitize_hex_setting( $value, $default ) {
		$color = sanitize_hex_color( $value );

		return $color ? $color : $default;
	}

	/**
	 * Sanitize a CSS spacing setting.
	 *
	 * @param string $value Raw value.
	 * @param string $default Default value.
	 * @return string
	 */
	protected function sanitize_css_spacing_setting( $value, $default ) {
		$value = trim( sanitize_text_field( $value ) );

		if ( '' === $value ) {
			return $default;
		}

		$parts = preg_split( '/\s+/', $value );

		if ( empty( $parts ) || count( $parts ) > 4 ) {
			return $default;
		}

		foreach ( $parts as $part ) {
			if ( ! preg_match( '/^\d+(?:\.\d+)?(?:px|em|rem|%)$/', $part ) ) {
				return $default;
			}
		}

		return implode( ' ', $parts );
	}

	/**
	 * Sanitize a CSS dimension setting.
	 *
	 * @param string $value Raw value.
	 * @param string $default Default value.
	 * @return string
	 */
	protected function sanitize_css_dimension_setting( $value, $default ) {
		$value = trim( sanitize_text_field( $value ) );

		if ( preg_match( '/^\d+(?:\.\d+)?(?:px|em|rem|%)$/', $value ) ) {
			return $value;
		}

		return $default;
	}

	/**
	 * Register scheduler hooks.
	 *
	 * @return void
	 */
	public function register_scheduler() {
		$scheduler = new Simplicity_Weather_Scheduler( $this->service );
		$scheduler->register();
	}

	/**
	 * Register plugin updater.
	 *
	 * @return void
	 */
	public function register_updater() {
		$updater = new Simplicity_Weather_Updater( $this->service );
		$updater->register();
	}

	/**
	 * Get shared service container.
	 *
	 * @return Simplicity_Weather_Service
	 */
	public function get_service() {
		return $this->service;
	}
}
