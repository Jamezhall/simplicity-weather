<?php
/**
 * Shortcode handler.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles frontend shortcode rendering.
 */
class Simplicity_Weather_Shortcode {

	/**
	 * Service object.
	 *
	 * @var Simplicity_Weather_Service
	 */
	protected $service;

	/**
	 * Whether AJAX badge assets are needed.
	 *
	 * @var bool
	 */
	protected $needs_assets = false;

	/**
	 * Constructor.
	 *
	 * @param Simplicity_Weather_Service $service Shared service.
	 */
	public function __construct( $service ) {
		$this->service = $service;
	}

	/**
	 * Register shortcode.
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( 'simplicity_weather', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp_footer', array( $this, 'print_assets_if_needed' ), 5 );
		add_action( 'wp_ajax_simplicity_weather_badge', array( $this, 'ajax_badge' ) );
		add_action( 'wp_ajax_nopriv_simplicity_weather_badge', array( $this, 'ajax_badge' ) );
	}

	/**
	 * Register frontend assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style( 'simplicity-weather-badge', SIMPLICITY_WEATHER_URL . 'assets/css/simplicity-weather-badge.css', array(), SIMPLICITY_WEATHER_VERSION );
		wp_register_script( 'simplicity-weather-badge', SIMPLICITY_WEATHER_URL . 'assets/js/simplicity-weather-badge.js', array(), SIMPLICITY_WEATHER_VERSION, true );
	}

	/**
	 * Enqueue AJAX badge assets if required.
	 *
	 * @return void
	 */
	public function print_assets_if_needed() {
		if ( ! $this->needs_assets ) {
			return;
		}

		wp_enqueue_style( 'simplicity-weather-badge' );
		wp_enqueue_script( 'simplicity-weather-badge' );
		wp_localize_script(
			'simplicity-weather-badge',
			'SimplicityWeatherBadge',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'errorText'     => __( 'Weather unavailable', 'simplicity-weather' ),
				'loadingLabel'  => __( 'Loading weather', 'simplicity-weather' ),
			)
		);

		wp_print_styles( array( 'simplicity-weather-badge' ) );
		wp_print_scripts( array( 'simplicity-weather-badge' ) );
	}

	/**
	 * Render shortcode output.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'location'  => '',
				'fields'    => '',
				'format'    => 'html',
				'mode'      => '',
				'separator' => ', ',
			),
			$atts,
			'simplicity_weather'
		);

		if ( empty( $atts['location'] ) ) {
			return '';
		}

		if ( 'ajax' === $atts['mode'] ) {
			$this->needs_assets = true;

			return $this->render_ajax_badge( $atts );
		}

		return simplicity_weather_render(
			$atts['location'],
			array(
				'fields'    => $atts['fields'],
				'format'    => $atts['format'],
				'separator' => $atts['separator'],
			)
		);
	}

	/**
	 * Render an AJAX badge placeholder.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	protected function render_ajax_badge( $atts ) {
		$settings = $this->service->get_settings();
		$style    = sprintf(
			'--sw-badge-color:%1$s;--sw-badge-background:%2$s;--sw-badge-font:%3$s;--sw-badge-padding:%4$s;--sw-badge-radius:%5$s;',
			esc_attr( $settings['badge_text_color'] ),
			esc_attr( $settings['badge_background_color'] ),
			esc_attr( $settings['badge_font_family'] ),
			esc_attr( $settings['badge_padding'] ),
			esc_attr( $settings['badge_border_radius'] )
		);

		return sprintf(
			'<div class="simplicity-weather-badge is-loading" aria-live="polite" aria-busy="true" data-location="%1$s" data-fields="%2$s" data-separator="%3$s" style="%4$s"><span class="simplicity-weather-badge__skeleton" aria-hidden="true"></span><span class="screen-reader-text">%5$s</span></div>',
			esc_attr( sanitize_title( $atts['location'] ) ),
			esc_attr( $atts['fields'] ),
			esc_attr( $atts['separator'] ),
			$style,
			esc_html__( 'Loading weather', 'simplicity-weather' )
		);
	}

	/**
	 * Return AJAX badge content.
	 *
	 * @return void
	 */
	public function ajax_badge() {
		$location  = isset( $_REQUEST['location'] ) ? sanitize_title( wp_unslash( $_REQUEST['location'] ) ) : '';
		$fields    = isset( $_REQUEST['fields'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['fields'] ) ) : '';
		$separator = isset( $_REQUEST['separator'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['separator'] ) ) : ', ';

		if ( empty( $location ) ) {
			wp_send_json_error( array( 'message' => __( 'Weather unavailable', 'simplicity-weather' ) ), 400 );
		}

		$text = $this->service->get_badge_text( $location, $fields, $separator );

		if ( '' === $text ) {
			wp_send_json_error( array( 'message' => __( 'Weather unavailable', 'simplicity-weather' ) ), 404 );
		}

		wp_send_json_success( array( 'text' => $text ) );
	}
}
