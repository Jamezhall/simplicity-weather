<?php
/**
 * Public template functions.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get weather data for a location slug.
 *
 * @param string $slug Location slug.
 * @return array|null
 */
function simplicity_weather_get( $slug ) {
	$plugin = Simplicity_Weather_Plugin::instance();

	return $plugin->get_service()->get_weather_by_slug( sanitize_title( $slug ) );
}

/**
 * Render weather markup for a location slug.
 *
 * @param string $slug Location slug.
 * @param array  $args Optional rendering arguments.
 * @return string
 */
function simplicity_weather_render( $slug, $args = array() ) {
	$data = simplicity_weather_get( $slug );

	if ( empty( $data['current'] ) || empty( $data['location'] ) ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'show_updated' => true,
		)
	);

	ob_start();
	?>
	<div class="simplicity-weather" data-location="<?php echo esc_attr( $data['location']['slug'] ); ?>">
		<div class="simplicity-weather__location"><?php echo esc_html( $data['location']['name'] ); ?></div>
		<div class="simplicity-weather__temperature"><?php echo esc_html( $data['current']['temperature'] . $data['current']['temperature_unit'] ); ?></div>
		<div class="simplicity-weather__condition"><?php echo esc_html( $data['current']['condition_label'] ); ?></div>
		<div class="simplicity-weather__wind">
			<?php
			printf(
				/* translators: 1: wind speed, 2: unit, 3: direction degrees. */
				esc_html__( 'Wind: %1$s %2$s at %3$sdeg', 'simplicity-weather' ),
				esc_html( $data['current']['wind_speed'] ),
				esc_html( $data['current']['wind_speed_unit'] ),
				esc_html( $data['current']['wind_direction'] )
			);
			?>
		</div>
		<?php if ( ! empty( $args['show_updated'] ) && ! empty( $data['meta']['last_updated'] ) ) : ?>
			<div class="simplicity-weather__updated">
				<?php
				printf(
					/* translators: %s: update timestamp. */
					esc_html__( 'Updated: %s', 'simplicity-weather' ),
					esc_html( get_date_from_gmt( $data['meta']['last_updated'], 'Y-m-d H:i:s' ) )
				);
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php

	return (string) ob_get_clean();
}
