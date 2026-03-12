<?php
/**
 * GitHub updater integration.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrates plugin updates with GitHub Releases.
 */
class Simplicity_Weather_Updater {

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
	 * Register updater hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
	}

	/**
	 * Inject update metadata into the plugins transient.
	 *
	 * @param stdClass $transient Update transient.
	 * @return stdClass
	 */
	public function check_for_updates( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_latest_release();

		if ( empty( $release['version'] ) || empty( $release['package'] ) ) {
			return $transient;
		}

		if ( version_compare( SIMPLICITY_WEATHER_VERSION, $release['version'], '>=' ) ) {
			return $transient;
		}

		$transient->response[ SIMPLICITY_WEATHER_BASENAME ] = (object) array(
			'slug'        => dirname( SIMPLICITY_WEATHER_BASENAME ),
			'plugin'      => SIMPLICITY_WEATHER_BASENAME,
			'new_version' => $release['version'],
			'url'         => $release['url'],
			'package'     => $release['package'],
		);

		return $transient;
	}

	/**
	 * Provide plugin information modal.
	 *
	 * @param false|object|array $result Existing result.
	 * @param string             $action API action.
	 * @param object             $args Request args.
	 * @return false|object|array
	 */
	public function plugins_api( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || dirname( SIMPLICITY_WEATHER_BASENAME ) !== $args->slug ) {
			return $result;
		}

		$release = $this->get_latest_release();

		if ( empty( $release['version'] ) ) {
			return $result;
		}

		return (object) array(
			'name'          => __( 'Simplicity Weather', 'simplicity-weather' ),
			'slug'          => dirname( SIMPLICITY_WEATHER_BASENAME ),
			'version'       => $release['version'],
			'author'        => '<a href="https://github.com">GitHub</a>',
			'homepage'      => $release['url'],
			'download_link' => $release['package'],
			'sections'      => array(
				'description' => $this->get_readme_description(),
				'changelog'   => ! empty( $release['body'] ) ? wp_kses_post( wpautop( $release['body'] ) ) : __( 'See the latest GitHub release for details.', 'simplicity-weather' ),
			),
		);
	}

	/**
	 * Get the plugin description section from readme.txt.
	 *
	 * @return string
	 */
	protected function get_readme_description() {
		$readme_path = SIMPLICITY_WEATHER_PATH . 'readme.txt';

		if ( ! file_exists( $readme_path ) || ! is_readable( $readme_path ) ) {
			return __( 'Current weather plugin powered by Open-Meteo with shortcode and template function output.', 'simplicity-weather' );
		}

		$contents = file_get_contents( $readme_path );

		if ( false === $contents ) {
			return __( 'Current weather plugin powered by Open-Meteo with shortcode and template function output.', 'simplicity-weather' );
		}

		if ( ! preg_match( '/== Description ==\s*(.+?)(?:\n== [^=]+ ==|\z)/s', $contents, $matches ) ) {
			return __( 'Current weather plugin powered by Open-Meteo with shortcode and template function output.', 'simplicity-weather' );
		}

		$description = trim( $matches[1] );

		if ( '' === $description ) {
			return __( 'Current weather plugin powered by Open-Meteo with shortcode and template function output.', 'simplicity-weather' );
		}

		$description = preg_replace( '/^\*\s+/m', '- ', $description );
		$description = preg_replace( '/^=\s*(.+?)\s*=\s*$/m', '<h4>$1</h4>', $description );

		return wp_kses_post( wpautop( $description ) );
	}

	/**
	 * Get latest release metadata.
	 *
	 * @return array
	 */
	protected function get_latest_release() {
		$settings   = $this->service->get_settings();
		$repository = ! empty( $settings['github_repository'] ) ? trim( $settings['github_repository'] ) : '';

		if ( ! preg_match( '/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/', $repository ) ) {
			return array();
		}

		$cache_key = 'simplicity_weather_release_' . md5( $repository );
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_remote_get(
			'https://api.github.com/repos/' . $repository . '/releases/latest',
			array(
				'timeout' => 15,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'Simplicity-Weather-Updater/' . SIMPLICITY_WEATHER_VERSION,
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$release = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $release['tag_name'] ) ) {
			return array();
		}

		$package = '';

		if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
			foreach ( $release['assets'] as $asset ) {
				if ( ! empty( $asset['browser_download_url'] ) && preg_match( '/\.zip$/i', $asset['name'] ) ) {
					$package = esc_url_raw( $asset['browser_download_url'] );
					break;
				}
			}
		}

		$data = array(
			'version' => ltrim( sanitize_text_field( $release['tag_name'] ), 'v' ),
			'package' => $package,
			'url'     => ! empty( $release['html_url'] ) ? esc_url_raw( $release['html_url'] ) : '',
			'body'    => ! empty( $release['body'] ) ? wp_kses_post( $release['body'] ) : '',
		);

		set_transient( $cache_key, $data, 6 * HOUR_IN_SECONDS );

		return $data;
	}
}
