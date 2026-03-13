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
			'fields'       => '',
			'format'       => 'html',
			'separator'    => ', ',
			'show_updated' => true,
		)
	);

	$fields = simplicity_weather_parse_fields( $args['fields'] );

	if ( 'text' === $args['format'] ) {
		return simplicity_weather_render_text( $data, $fields, $args );
	}

	ob_start();
	?>
	<div class="simplicity-weather" data-location="<?php echo esc_attr( $data['location']['slug'] ); ?>">
		<?php if ( simplicity_weather_should_render_field( 'location', $fields ) ) : ?>
			<div class="simplicity-weather__location"><?php echo esc_html( $data['location']['name'] ); ?></div>
		<?php endif; ?>
		<?php if ( simplicity_weather_should_render_field( 'temp', $fields ) ) : ?>
			<div class="simplicity-weather__temperature"><?php echo esc_html( $data['current']['temperature'] . $data['current']['temperature_unit'] ); ?></div>
		<?php endif; ?>
		<?php if ( simplicity_weather_should_render_field( 'condition', $fields ) ) : ?>
			<div class="simplicity-weather__condition"><?php echo esc_html( $data['current']['condition_label'] ); ?></div>
		<?php endif; ?>
		<?php if ( simplicity_weather_should_render_field( 'wind', $fields ) ) : ?>
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
		<?php endif; ?>
		<?php if ( ! empty( $args['show_updated'] ) && simplicity_weather_should_render_field( 'updated', $fields ) && ! empty( $data['meta']['last_updated'] ) ) : ?>
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

/**
 * Parse requested output fields.
 *
 * @param string|array $fields Fields value.
 * @return array
 */
function simplicity_weather_parse_fields( $fields ) {
	if ( empty( $fields ) ) {
		return array();
	}

	if ( is_string( $fields ) ) {
		$fields = explode( ',', $fields );
	}

	$fields = array_map( 'trim', (array) $fields );
	$fields = array_map( 'sanitize_key', $fields );
	$fields = array_filter( $fields );

	return array_values( array_intersect( $fields, array( 'location', 'temp', 'condition', 'wind', 'updated' ) ) );
}

/**
 * Determine whether a field should be rendered.
 *
 * @param string $field Field name.
 * @param array  $fields Requested fields.
 * @return bool
 */
function simplicity_weather_should_render_field( $field, $fields ) {
	if ( empty( $fields ) ) {
		return true;
	}

	return in_array( $field, $fields, true );
}

/**
 * Render plain text weather output.
 *
 * @param array $data Weather data.
 * @param array $fields Requested fields.
 * @param array $args Render arguments.
 * @return string
 */
function simplicity_weather_render_text( $data, $fields, $args ) {
	return esc_html( simplicity_weather_build_text_output( $data, $fields, $args ) );
}

/**
 * Build plain text weather output.
 *
 * @param array $data Weather data.
 * @param array $fields Requested fields.
 * @param array $args Render arguments.
 * @return string
 */
function simplicity_weather_build_text_output( $data, $fields, $args ) {
	$parts     = array();
	$separator = isset( $args['separator'] ) ? (string) $args['separator'] : ', ';

	if ( simplicity_weather_should_render_field( 'location', $fields ) ) {
		$parts[] = $data['location']['name'];
	}

	if ( simplicity_weather_should_render_field( 'temp', $fields ) ) {
		$parts[] = $data['current']['temperature'] . $data['current']['temperature_unit'];
	}

	if ( simplicity_weather_should_render_field( 'condition', $fields ) ) {
		$parts[] = $data['current']['condition_label'];
	}

	if ( simplicity_weather_should_render_field( 'wind', $fields ) ) {
		$parts[] = sprintf(
			/* translators: 1: wind speed, 2: unit, 3: direction degrees. */
			__( 'Wind: %1$s %2$s at %3$sdeg', 'simplicity-weather' ),
			$data['current']['wind_speed'],
			$data['current']['wind_speed_unit'],
			$data['current']['wind_direction']
		);
	}

	if ( ! empty( $args['show_updated'] ) && simplicity_weather_should_render_field( 'updated', $fields ) && ! empty( $data['meta']['last_updated'] ) ) {
		$parts[] = sprintf(
			/* translators: %s: update timestamp. */
			__( 'Updated: %s', 'simplicity-weather' ),
			get_date_from_gmt( $data['meta']['last_updated'], 'Y-m-d H:i:s' )
		);
	}

	return implode( $separator, $parts );
}
