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

		add_submenu_page(
			'simplicity-weather',
			__( 'Logs', 'simplicity-weather' ),
			__( 'Logs', 'simplicity-weather' ),
			'manage_options',
			'simplicity-weather-logs',
			array( $this, 'render_logs_page' )
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

		if ( 'refresh_all_locations' === $action ) {
			$this->handle_refresh_all_locations();
		}

		if ( 'prune_logs' === $action ) {
			$this->handle_prune_logs();
		}

		if ( 'clear_logs' === $action ) {
			$this->handle_clear_logs();
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
						<td>
							<select id="simplicity-weather-timezone" class="regular-text" name="timezone" required>
								<?php $this->render_timezone_options( $editing ? $editing['timezone'] : 'Europe/London' ); ?>
							</select>
							<p class="description"><?php esc_html_e( 'Select the timezone used for this location. Scotland uses Europe/London.', 'simplicity-weather' ); ?></p>
						</td>
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

		$rows        = $this->service->get_location_status_rows();
		$diagnostics = $this->service->get_diagnostics();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Weather Status', 'simplicity-weather' ); ?></h1>
			<?php $this->render_notice(); ?>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=simplicity-weather-status&simplicity_weather_action=refresh_all_locations' ), 'simplicity_weather_refresh_all_locations' ) ); ?>"><?php esc_html_e( 'Refresh All Locations', 'simplicity-weather' ); ?></a>
			</p>
			<table class="widefat striped" style="max-width: 900px; margin-bottom: 20px;">
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'Cron Registered', 'simplicity-weather' ); ?></strong></td>
						<td><?php echo ! empty( $diagnostics['cron_registered'] ) ? esc_html__( 'Yes', 'simplicity-weather' ) : esc_html__( 'No', 'simplicity-weather' ); ?></td>
						<td><strong><?php esc_html_e( 'Next Scheduled Run', 'simplicity-weather' ); ?></strong></td>
						<td><?php echo ! empty( $diagnostics['next_run'] ) ? esc_html( wp_date( 'Y-m-d H:i:s', $diagnostics['next_run'] ) ) : esc_html__( 'Not scheduled', 'simplicity-weather' ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Active Locations', 'simplicity-weather' ); ?></strong></td>
						<td><?php echo esc_html( $diagnostics['active_count'] ); ?></td>
						<td><strong><?php esc_html_e( 'Stale Locations', 'simplicity-weather' ); ?></strong></td>
						<td><?php echo esc_html( $diagnostics['stale_count'] ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Locations With Errors', 'simplicity-weather' ); ?></strong></td>
						<td><?php echo esc_html( $diagnostics['error_count'] ); ?></td>
						<td><strong><?php esc_html_e( 'Log Retention', 'simplicity-weather' ); ?></strong></td>
						<td><?php echo ! empty( $this->service->get_settings()['log_retention_days'] ) ? esc_html( $this->service->get_settings()['log_retention_days'] . ' ' . __( 'days', 'simplicity-weather' ) ) : esc_html__( 'Unlimited', 'simplicity-weather' ); ?></td>
					</tr>
				</tbody>
			</table>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Location', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Current Weather', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Last Attempt', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Last Success', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Next Refresh', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Response', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'State', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Error', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'simplicity-weather' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr><td colspan="9"><?php esc_html_e( 'No locations available.', 'simplicity-weather' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $rows as $row ) : ?>
							<?php $weather = isset( $row['weather']['current'] ) ? $row['weather']['current'] : array(); ?>
							<?php $state_class = ! empty( $row['cache']['error_message'] ) ? 'error' : ( $row['is_stale'] ? 'warning' : 'success' ); ?>
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
								<td><?php echo ! empty( $row['cache']['expires_at'] ) ? esc_html( get_date_from_gmt( $row['cache']['expires_at'], 'Y-m-d H:i:s' ) ) : esc_html__( '—', 'simplicity-weather' ); ?></td>
								<td>
									<?php if ( ! empty( $row['log'] ) ) : ?>
										<?php echo esc_html( absint( $row['log']['response_code'] ) ); ?> / <?php echo esc_html( absint( $row['log']['duration_ms'] ) ); ?>ms
									<?php else : ?>
										<?php esc_html_e( '—', 'simplicity-weather' ); ?>
									<?php endif; ?>
								</td>
								<td><span class="notice-inline notice-<?php echo esc_attr( $state_class ); ?>" style="padding: 4px 8px; display: inline-block;"><?php echo ! empty( $row['cache']['error_message'] ) ? esc_html__( 'Error', 'simplicity-weather' ) : ( $row['is_stale'] ? esc_html__( 'Stale', 'simplicity-weather' ) : esc_html__( 'Fresh', 'simplicity-weather' ) ); ?></span></td>
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
						<th scope="row"><?php esc_html_e( 'Debug Logging', 'simplicity-weather' ); ?></th>
						<td><label><input type="checkbox" name="simplicity_weather_settings[enable_logging]" value="1" <?php checked( ! empty( $settings['enable_logging'] ) ); ?> /> <?php esc_html_e( 'Store refresh attempt logs.', 'simplicity-weather' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="simplicity-weather-log-retention"><?php esc_html_e( 'Log Retention', 'simplicity-weather' ); ?></label></th>
						<td>
							<select id="simplicity-weather-log-retention" name="simplicity_weather_settings[log_retention_days]">
								<option value="7" <?php selected( $settings['log_retention_days'], 7 ); ?>><?php esc_html_e( '7 days', 'simplicity-weather' ); ?></option>
								<option value="30" <?php selected( $settings['log_retention_days'], 30 ); ?>><?php esc_html_e( '30 days', 'simplicity-weather' ); ?></option>
								<option value="90" <?php selected( $settings['log_retention_days'], 90 ); ?>><?php esc_html_e( '90 days', 'simplicity-weather' ); ?></option>
								<option value="0" <?php selected( $settings['log_retention_days'], 0 ); ?>><?php esc_html_e( 'Unlimited', 'simplicity-weather' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Older logs are pruned automatically during scheduled refreshes unless retention is set to Unlimited.', 'simplicity-weather' ); ?></p>
						</td>
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
	 * Render logs page.
	 *
	 * @return void
	 */
	public function render_logs_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$filters = array(
			'location_id' => isset( $_GET['location_id'] ) ? absint( $_GET['location_id'] ) : 0,
			'status'      => isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '',
		);

		$logs          = $this->service->get_logs( $filters );
		$locations     = $this->service->get_locations();
		$location_map  = array();

		foreach ( $locations as $location ) {
			$location_map[ (int) $location['id'] ] = $location['name'];
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Weather Logs', 'simplicity-weather' ); ?></h1>
			<?php $this->render_notice(); ?>
			<p>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=simplicity-weather-logs&simplicity_weather_action=prune_logs' ), 'simplicity_weather_prune_logs' ) ); ?>"><?php esc_html_e( 'Prune Old Logs', 'simplicity-weather' ); ?></a>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=simplicity-weather-logs&simplicity_weather_action=clear_logs' ), 'simplicity_weather_clear_logs' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Clear all logs? This cannot be undone.', 'simplicity-weather' ) ); ?>');"><?php esc_html_e( 'Clear All Logs', 'simplicity-weather' ); ?></a>
			</p>
			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin-bottom: 15px;">
				<input type="hidden" name="page" value="simplicity-weather-logs" />
				<select name="location_id">
					<option value="0"><?php esc_html_e( 'All locations', 'simplicity-weather' ); ?></option>
					<?php foreach ( $locations as $location ) : ?>
						<option value="<?php echo esc_attr( $location['id'] ); ?>" <?php selected( $filters['location_id'], (int) $location['id'] ); ?>><?php echo esc_html( $location['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<select name="status">
					<option value=""><?php esc_html_e( 'All statuses', 'simplicity-weather' ); ?></option>
					<option value="success" <?php selected( $filters['status'], 'success' ); ?>><?php esc_html_e( 'Success', 'simplicity-weather' ); ?></option>
					<option value="error" <?php selected( $filters['status'], 'error' ); ?>><?php esc_html_e( 'Error', 'simplicity-weather' ); ?></option>
				</select>
				<?php submit_button( __( 'Filter Logs', 'simplicity-weather' ), 'secondary', '', false ); ?>
			</form>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Requested At', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Location', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Status', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Response Code', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Duration', 'simplicity-weather' ); ?></th>
						<th><?php esc_html_e( 'Message', 'simplicity-weather' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $logs ) ) : ?>
						<tr><td colspan="6"><?php esc_html_e( 'No logs found.', 'simplicity-weather' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><?php echo esc_html( get_date_from_gmt( $log['requested_at'], 'Y-m-d H:i:s' ) ); ?></td>
								<td><?php echo isset( $location_map[ (int) $log['location_id'] ] ) ? esc_html( $location_map[ (int) $log['location_id'] ] ) : esc_html( absint( $log['location_id'] ) ); ?></td>
								<td><?php echo esc_html( ucfirst( $log['status'] ) ); ?></td>
								<td><?php echo esc_html( absint( $log['response_code'] ) ); ?></td>
								<td><?php echo esc_html( absint( $log['duration_ms'] ) ); ?>ms</td>
								<td><?php echo esc_html( $log['message'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
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
	 * Refresh all locations.
	 *
	 * @return void
	 */
	protected function handle_refresh_all_locations() {
		check_admin_referer( 'simplicity_weather_refresh_all_locations' );

		$results = $this->service->refresh_all_locations();
		$message = sprintf(
			/* translators: 1: total locations, 2: successful refreshes, 3: failed refreshes. */
			__( 'Processed %1$d locations. Successful: %2$d. Failed: %3$d.', 'simplicity-weather' ),
			$results['total'],
			$results['success'],
			$results['error']
		);

		$this->redirect_with_notice( 'simplicity-weather-status', 0 === (int) $results['error'] ? 'success' : 'error', $message );
	}

	/**
	 * Prune old logs.
	 *
	 * @return void
	 */
	protected function handle_prune_logs() {
		check_admin_referer( 'simplicity_weather_prune_logs' );

		$count = $this->service->prune_logs_by_retention();
		$this->redirect_with_notice( 'simplicity-weather-logs', 'success', sprintf( __( 'Pruned %d old log entries.', 'simplicity-weather' ), $count ) );
	}

	/**
	 * Clear all logs.
	 *
	 * @return void
	 */
	protected function handle_clear_logs() {
		check_admin_referer( 'simplicity_weather_clear_logs' );

		$this->service->clear_all_logs();
		$this->redirect_with_notice( 'simplicity-weather-logs', 'success', __( 'All logs cleared.', 'simplicity-weather' ) );
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

	/**
	 * Render timezone select options.
	 *
	 * @param string $selected_timezone Selected timezone value.
	 * @return void
	 */
	protected function render_timezone_options( $selected_timezone ) {
		foreach ( timezone_identifiers_list() as $timezone ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $timezone ),
				selected( $selected_timezone, $timezone, false ),
				esc_html( $timezone )
			);
		}
	}
}
