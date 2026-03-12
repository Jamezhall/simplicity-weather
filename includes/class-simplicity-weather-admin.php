<?php
/**
 * Admin pages and handlers.
 *
 * @package SimplicityWeather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin UI class.
 */
class Simplicity_Weather_Admin {

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
	 * Register admin menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Simplicity Weather', 'simplicity-weather' ),
			__( 'Simplicity Weather', 'simplicity-weather' ),
			'manage_options',
			'simplicity-weather',
			array( $this, 'render_locations_page' ),
			'dashicons-cloud',
			58
		);

		add_submenu_page(
			'simplicity-weather',
			__( 'Locations', 'simplicity-weather' ),
			__( 'Locations', 'simplicity-weather' ),
			'manage_options',
			'simplicity-weather',
			array( $this, 'render_locations_page' )
		);

		add_submenu_page(
			'simplicity-weather',
			__( 'Weather Status', 'simplicity-weather' ),
			__( 'Weather Status', 'simplicity-weather' ),
			'manage_options',
			'simplicity-weather-status',
			array( $this, 'render_status_page' )
		);

		add_submenu_page(
			'simplicity-weather',
			__( 'Settings', 'simplicity-weather' ),
			__( 'Settings', 'simplicity-weather' ),
			'manage_options',
			'simplicity-weather-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Handle form actions.
	 *
	 * @return void
	 */
	public function handle_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = isset( $_POST['simplicity_weather_action'] ) ? sanitize_key( wp_unslash( $_POST['simplicity_weather_action'] ) ) : '';

		if ( ! $action ) {
			$action = isset( $_GET['simplicity_weather_action'] ) ? sanitize_key( wp_unslash( $_GET['simplicity_weather_action'] ) ) : '';
		}

		if ( 'save_location' === $action ) {
			$this->handle_save_location();
		}

		if ( 'delete_location' === $action ) {
			$this->handle_delete_location();
		}

		if ( 'refresh_location' === $action ) {
			$this->handle_refresh_location();
		}
	}

	/**
	 * Render locations page.
	 *
	 * @return void
	 */
	public function render_locations_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$edit_id   = isset( $_GET['edit_location'] ) ? absint( $_GET['edit_location'] ) : 0;
		$editing   = $edit_id ? $this->service->get_location( $edit_id ) : null;
		$locations = $this->service->get_locations();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Simplicity Weather Locations', 'simplicity-weather' ); ?></h1>
			<?php $this->render_notice(); ?>
			<h2><?php echo $editing ? esc_html__( 'Edit Location', 'simplicity-weather' ) : esc_html__( 'Add Location', 'simplicity-weather' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=simplicity-weather' ) ); ?>">
				<?php wp_nonce_field( 'simplicity_weather_save_location' ); ?>
				<input type="hidden" name="simplicity_weather_action" value="save_location" />
				<input type="hidden" name="id" value="<?php echo $editing ? esc_attr( $editing['id'] ) : 0; ?>" />
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="simplicity-weather-name"><?php esc_html_e( 'Name', 'simplicity-weather' ); ?></label></th>
						<td><input id="simplicity-weather-name" class="regular-text" type="text" name="name" value="<?php echo $editing ? esc_attr( $editing['name'] ) : ''; ?>" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="simplicity-weather-slug"><?php esc_html_e( 'Slug', 'simplicity-weather' ); ?></label></th>
						<td><input id="simplicity-weather-slug" class="regular-text" type="text" name="slug" value="<?php echo $editing ? esc_attr( $editing['slug'] ) : ''; ?>" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="simplicity-weather-latitude"><?php esc_html_e( 'Latitude', 'simplicity-weather' ); ?></label></th>
						<td><input id="simplicity-weather-latitude" class="regular-text" type="number" step="0.000001" name="latitude" value="<?php echo $editing ? esc_attr( $editing['latitude'] ) : ''; ?>" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="simplicity-weather-longitude"><?php esc_html_e( 'Longitude', 'simplicity-weather' ); ?></label></th>
						<td><input id="simplicity-weather-longitude" class="regular-text" type="number" step="0.000001" name="longitude" value="<?php echo $editing ? esc_attr( $editing['longitude'] ) : ''; ?>" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="simplicity-weather-timezone"><?php esc_html_e( 'Timezone', 'simplicity-weather' ); ?></label></th>
						<td><input id="simplicity-weather-timezone" class="regular-text" type="text" name="timezone" value="<?php echo $editing ? esc_attr( $editing['timezone'] ) : 'UTC'; ?>" required />
						<p class="description"><?php esc_html_e( 'Use a PHP timezone identifier such as America/New_York.', 'simplicity-weather' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="simplicity-weather-refresh"><?php esc_html_e( 'Refresh Interval (minutes)', 'simplicity-weather' ); ?></label></th>
						<td><input id="simplicity-weather-refresh" class="small-text" type="number" min="15" step="15" name="refresh_interval" value="<?php echo $editing ? esc_attr( $editing['refresh_interval'] ) : esc_attr( $this->service->get_settings()['default_refresh'] ); ?>" required /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Active', 'simplicity-weather' ); ?></th>
						<td><label><input type="checkbox" name="is_active" value="1" <?php checked( ! $editing || ! empty( $editing['is_active'] ) ); ?> /> <?php esc_html_e( 'Enable scheduled refreshes for this location.', 'simplicity-weather' ); ?></label></td>
					</tr>
				</table>
				<?php submit_button( $editing ? __( 'Update Location', 'simplicity-weather' ) : __( 'Add Location', 'simplicity-weather' ) ); ?>
			</form>

			<h2><?php esc_html_e( 'Saved Locations', 'simplicity-weather' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Coordinates', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Timezone', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Refresh', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Status', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'simplicity-weather' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $locations ) ) : ?>
						<tr><td colspan="7"><?php esc_html_e( 'No locations configured yet.', 'simplicity-weather' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $locations as $location ) : ?>
							<tr>
								<td><?php echo esc_html( $location['name'] ); ?></td>
								<td><code><?php echo esc_html( $location['slug'] ); ?></code></td>
								<td><?php echo esc_html( $location['latitude'] . ', ' . $location['longitude'] ); ?></td>
								<td><?php echo esc_html( $location['timezone'] ); ?></td>
								<td><?php echo esc_html( absint( $location['refresh_interval'] ) ); ?> <?php esc_html_e( 'minutes', 'simplicity-weather' ); ?></td>
								<td><?php echo ! empty( $location['is_active'] ) ? esc_html__( 'Active', 'simplicity-weather' ) : esc_html__( 'Inactive', 'simplicity-weather' ); ?></td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=simplicity-weather&edit_location=' . absint( $location['id'] ) ) ); ?>"><?php esc_html_e( 'Edit', 'simplicity-weather' ); ?></a>
									|
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=simplicity-weather&simplicity_weather_action=delete_location&location_id=' . absint( $location['id'] ) ), 'simplicity_weather_delete_location' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this location and its cached weather?', 'simplicity-weather' ) ); ?>');"><?php esc_html_e( 'Delete', 'simplicity-weather' ); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render status page.
	 *
	 * @return void
	 */
	public function render_status_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$rows = $this->service->get_location_status_rows();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Weather Status', 'simplicity-weather' ); ?></h1>
			<?php $this->render_notice(); ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Location', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Current Weather', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Last Attempt', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Last Success', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'State', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Error', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'simplicity-weather' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr><td colspan="7"><?php esc_html_e( 'No locations available.', 'simplicity-weather' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $rows as $row ) : ?>
							<?php $weather = isset( $row['weather']['current'] ) ? $row['weather']['current'] : array(); ?>
							<tr>
								<td><?php echo esc_html( $row['location']['name'] ); ?><br /><code><?php echo esc_html( $row['location']['slug'] ); ?></code></td>
								<td>
									<?php if ( ! empty( $weather ) ) : ?>
										<strong><?php echo esc_html( $weather['temperature'] . $weather['temperature_unit'] ); ?></strong><br />
										<?php echo esc_html( $weather['condition_label'] ); ?>
									<?php else : ?>
										<?php esc_html_e( 'No cached weather yet.', 'simplicity-weather' ); ?>
									<?php endif; ?>
								</td>
								<td><?php echo ! empty( $row['cache']['last_attempt_at'] ) ? esc_html( get_date_from_gmt( $row['cache']['last_attempt_at'], 'Y-m-d H:i:s' ) ) : esc_html__( '—', 'simplicity-weather' ); ?></td>
								<td><?php echo ! empty( $row['cache']['last_success_at'] ) ? esc_html( get_date_from_gmt( $row['cache']['last_success_at'], 'Y-m-d H:i:s' ) ) : esc_html__( '—', 'simplicity-weather' ); ?></td>
								<td><?php echo $row['is_stale'] ? esc_html__( 'Stale', 'simplicity-weather' ) : esc_html__( 'Fresh', 'simplicity-weather' ); ?></td>
								<td><?php echo ! empty( $row['cache']['error_message'] ) ? esc_html( $row['cache']['error_message'] ) : esc_html__( '—', 'simplicity-weather' ); ?></td>
								<td><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=simplicity-weather-status&simplicity_weather_action=refresh_location&location_id=' . absint( $row['location']['id'] ) ), 'simplicity_weather_refresh_location' ) ); ?>"><?php esc_html_e( 'Refresh Now', 'simplicity-weather' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = $this->service->get_settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Simplicity Weather Settings', 'simplicity-weather' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'simplicity_weather_settings' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Default Units', 'simplicity-weather' ); ?></th>
						<td>
							<select name="simplicity_weather_settings[default_units]">
								<option value="metric" <?php selected( $settings['default_units'], 'metric' ); ?>><?php esc_html_e( 'Metric', 'simplicity-weather' ); ?></option>
								<option value="imperial" <?php selected( $settings['default_units'], 'imperial' ); ?>><?php esc_html_e( 'Imperial', 'simplicity-weather' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="simplicity-weather-default-refresh"><?php esc_html_e( 'Default Refresh Interval', 'simplicity-weather' ); ?></label></th>
						<td><input id="simplicity-weather-default-refresh" class="small-text" type="number" min="15" step="15" name="simplicity_weather_settings[default_refresh]" value="<?php echo esc_attr( $settings['default_refresh'] ); ?>" /> <?php esc_html_e( 'minutes', 'simplicity-weather' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="simplicity-weather-github-repository"><?php esc_html_e( 'GitHub Repository', 'simplicity-weather' ); ?></label></th>
						<td><input id="simplicity-weather-github-repository" class="regular-text" type="text" name="simplicity_weather_settings[github_repository]" value="<?php echo esc_attr( $settings['github_repository'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Enter the public repository as owner/repo. Example: yourname/simplicity-weather.', 'simplicity-weather' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Debug Logging', 'simplicity-weather' ); ?></th>
						<td><label><input type="checkbox" name="simplicity_weather_settings[enable_logging]" value="1" <?php checked( ! empty( $settings['enable_logging'] ) ); ?> /> <?php esc_html_e( 'Store refresh attempt logs.', 'simplicity-weather' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Cleanup on Uninstall', 'simplicity-weather' ); ?></th>
						<td><label><input type="checkbox" name="simplicity_weather_settings[cleanup_on_uninstall]" value="1" <?php checked( ! empty( $settings['cleanup_on_uninstall'] ) ); ?> /> <?php esc_html_e( 'Delete plugin tables and settings on uninstall.', 'simplicity-weather' ); ?></label></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Save a location.
	 *
	 * @return void
	 */
	protected function handle_save_location() {
		check_admin_referer( 'simplicity_weather_save_location' );

		$result = $this->service->save_location(
			array(
				'id'               => isset( $_POST['id'] ) ? wp_unslash( $_POST['id'] ) : 0,
				'name'             => isset( $_POST['name'] ) ? wp_unslash( $_POST['name'] ) : '',
				'slug'             => isset( $_POST['slug'] ) ? wp_unslash( $_POST['slug'] ) : '',
				'latitude'         => isset( $_POST['latitude'] ) ? wp_unslash( $_POST['latitude'] ) : '',
				'longitude'        => isset( $_POST['longitude'] ) ? wp_unslash( $_POST['longitude'] ) : '',
				'timezone'         => isset( $_POST['timezone'] ) ? wp_unslash( $_POST['timezone'] ) : '',
				'refresh_interval' => isset( $_POST['refresh_interval'] ) ? wp_unslash( $_POST['refresh_interval'] ) : 30,
				'is_active'        => isset( $_POST['is_active'] ) ? wp_unslash( $_POST['is_active'] ) : 0,
			)
		);

		if ( is_wp_error( $result ) ) {
			$this->redirect_with_notice( 'simplicity-weather', 'error', $result->get_error_message() );
		}

		$this->redirect_with_notice( 'simplicity-weather', 'success', __( 'Location saved successfully.', 'simplicity-weather' ) );
	}

	/**
	 * Delete a location.
	 *
	 * @return void
	 */
	protected function handle_delete_location() {
		check_admin_referer( 'simplicity_weather_delete_location' );

		$location_id = isset( $_GET['location_id'] ) ? absint( $_GET['location_id'] ) : 0;
		$this->service->delete_location( $location_id );

		$this->redirect_with_notice( 'simplicity-weather', 'success', __( 'Location deleted.', 'simplicity-weather' ) );
	}

	/**
	 * Refresh a location.
	 *
	 * @return void
	 */
	protected function handle_refresh_location() {
		check_admin_referer( 'simplicity_weather_refresh_location' );

		$location_id = isset( $_GET['location_id'] ) ? absint( $_GET['location_id'] ) : 0;
		$result      = $this->service->refresh_location( $location_id );

		if ( is_wp_error( $result ) ) {
			$this->redirect_with_notice( 'simplicity-weather-status', 'error', $result->get_error_message() );
		}

		$this->redirect_with_notice( 'simplicity-weather-status', 'success', __( 'Weather refreshed successfully.', 'simplicity-weather' ) );
	}

	/**
	 * Redirect back to page with notice.
	 *
	 * @param string $page Page slug.
	 * @param string $type Notice type.
	 * @param string $message Notice text.
	 * @return void
	 */
	protected function redirect_with_notice( $page, $type, $message ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                     => $page,
					'simplicity_weather_notice' => $type,
					'message'                  => $message,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Render admin notice if present.
	 *
	 * @return void
	 */
	protected function render_notice() {
		$type    = isset( $_GET['simplicity_weather_notice'] ) ? sanitize_key( wp_unslash( $_GET['simplicity_weather_notice'] ) ) : '';
		$message = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';

		if ( ! $type || ! $message ) {
			return;
		}

		$class = 'success' === $type ? 'notice notice-success' : 'notice notice-error';
		?>
		<div class="<?php echo esc_attr( $class ); ?>"><p><?php echo esc_html( $message ); ?></p></div>
		<?php
	}
}
