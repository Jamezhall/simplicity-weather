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
				'separator' => ', ',
			),
			$atts,
			'simplicity_weather'
		);

		if ( empty( $atts['location'] ) ) {
			return '';
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
}
