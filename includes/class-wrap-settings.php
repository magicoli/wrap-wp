<?php
/**
 * Class WrapSettings
 * Provides settings for the WRAP plugin.
 */

// Do not remove these instructions:
// All code comments, user outputs and debugs must be in English.
// Some commands are commented out for further development. Do not remove them.

class WrapSettings {
	public static function init() {
		add_action( 'admin_menu', array( self::class, 'add_admin_menu' ) );
		add_action( 'admin_init', array( self::class, 'settings_init' ) );
	}

	public static function add_admin_menu() {
		$options  = get_option( 'wrap_settings' );
		$position = isset( $options['wrap_menu_position'] ) && $options['wrap_menu_position'] ? 2 : 80;
		add_menu_page( __( 'WRAP', 'wrap' ), 'WRAP', 'manage_options', 'wrap', array( self::class, 'options_page' ), 'dashicons-admin-generic', $position );
		add_submenu_page( 'wrap', __( 'WRAP Settings', 'wrap' ), __( 'Settings', 'wrap' ), 'manage_options', 'wrap', array( self::class, 'options_page' ) );
	}

	public static function settings_init() {
		register_setting( 'wrap_settings', 'wrap_settings', array( 'sanitize_callback' => array( self::class, 'sanitize' ) ) );

		add_settings_section(
			'wrap_settings_section',
			__( 'Settings for WRAP Plugin', 'wrap' ),
			array( self::class, 'settings_section_callback' ),
			'wrap_settings'
		);

		add_settings_field(
			'wrap_menu_position',
			__( 'Admin Menu Position', 'wrap' ),
			array( self::class, 'menu_position_render' ),
			'wrap_settings',
			'wrap_settings_section'
		);

		add_settings_field(
			'wrap_base_url',
			__( 'External Site Base URL', 'wrap' ),
			array( self::class, 'base_url_render' ),
			'wrap_settings',
			'wrap_settings_section'
		);

		add_settings_field(
			'wrap_base_path',
			__( 'External Site Base Path', 'wrap' ),
			array( self::class, 'base_path_render' ),
			'wrap_settings',
			'wrap_settings_section'
		);
	}

	public static function base_url_render() {
		$options = get_option( 'wrap_settings' );
		$class   = '';
		if ( get_option( 'wrap_base_url_error' ) ) {
			$class = 'error';
		}
		?>
		<input type='text' name='wrap_settings[wrap_base_url]' value='<?php echo esc_attr( $options['wrap_base_url'] ); ?>' class='<?php echo $class; ?>'>
		<?php
		if ( get_option( 'wrap_base_url_error' ) ) {
			echo '<p class="description error">' . __( 'URL is not reachable.', 'wrap' ) . '</p>';
			delete_option( 'wrap_base_url_error' );
		}
	}

	public static function base_path_render() {
		$options = get_option( 'wrap_settings' );
		?>
		<input type='text' name='wrap_settings[wrap_base_path]' value='<?php echo esc_attr( $options['wrap_base_path'] ); ?>'>
		<?php
		if ( get_option( 'wrap_base_path_error' ) ) {
			echo '<p class="description error">' . __( 'The path is not accessible.', 'wrap' ) . '</p>';
			delete_option( 'wrap_base_path_error' );
		}
	}

	public static function menu_position_render() {
		$options = get_option( 'wrap_settings' );
		?>
		<input type='checkbox' name='wrap_settings[wrap_menu_position]' value='1' <?php checked( 1, isset( $options['wrap_menu_position'] ) ? $options['wrap_menu_position'] : 1 ); ?>>
		<label for='wrap_settings[wrap_menu_position]'>
			<?php _e( 'Up Where We Belong...', 'wrap' ); ?></label>
		<?php
	}

	public static function settings_section_callback() {
		echo __( 'Enter the settings for the WRAP plugin.', 'wrap' );
	}

	public static function options_page() {
		?>
		<form action='options.php' method='post'>
			<h2><?php _e( 'WRAP Settings', 'wrap' ); ?></h2>
			<?php
			settings_errors( 'wrap_settings' ); // Afficher les erreurs ici
			settings_fields( 'wrap_settings' );
			do_settings_sections( 'wrap_settings' );
			submit_button();
			?>
		</form>
		<?php
	}

	public static function sanitize( $input ) {
		$output = array();
		if ( isset( $input['wrap_base_url'] ) ) {
			$url = rtrim( esc_url_raw( $input['wrap_base_url'] ), '/' );
			if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
				$output['wrap_base_url'] = $url;
				if ( self::url_exists( $url ) ) {
					delete_option( 'wrap_base_url_error' );
				} else {
					add_settings_error(
						'wrap_settings',
						'wrap_base_url_error',
						__( 'WRAP: The URL seems properly formatted but is not reachable.', 'wrap' ),
						'error'
					);
					update_option( 'wrap_base_url_error', true );
				}
			} else {
				add_settings_error(
					'wrap_settings',
					'wrap_base_url_error',
					__( 'WRAP: Invalid URL. Please enter a valid URL.', 'wrap' ),
					'error'
				);
				update_option( 'wrap_base_url_error', true );
			}
		}
		if ( ! empty( $input['wrap_base_path'] ) ) {
			$path                     = rtrim( sanitize_text_field( $input['wrap_base_path'] ), '/' );
			$output['wrap_base_path'] = $path;
			if ( ! self::path_exists( $path ) ) {
				add_settings_error(
					'wrap_settings',
					'wrap_base_path_error',
					__( 'The path is not accessible.', 'wrap' ),
					'error'
				);
				update_option( 'wrap_base_path_error', true );
			} else {
				delete_option( 'wrap_base_path_error' );
			}
		}

		$output['wrap_menu_position'] = isset( $input['wrap_menu_position'] ) ? 1 : 0;

		return $output;
	}

	private static function url_exists( $url ) {
		$response = wp_remote_head( $url );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$http_code = wp_remote_retrieve_response_code( $response );
		return in_array( $http_code, array( 200, 301, 302, 401, 403 ) );
	}

	private static function path_exists( $path ) {
		return is_dir( $path ) && is_readable( $path );
	}
}

// We don't initialize the class here, it's done in wrap.php
// WrapSettings::init();
