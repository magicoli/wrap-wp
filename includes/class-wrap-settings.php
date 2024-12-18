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
		add_action( 'admin_init', array( self::class, 'restrict_admin_access' ) );
		add_action( 'after_setup_theme', array( self::class, 'remove_admin_bar' ) );
		self::initialize_default_options();
	}

	public static function add_admin_menu() {
        if(Wrap::get_option('setup_done')) {
            add_submenu_page( 'wrap', __( 'Settings', 'wrap' ), __( 'Settings', 'wrap' ), 'manage_options', 'wrap-settings', array( self::class, 'options_page' ) );
        }
	}

	public static function settings_init() {
		register_setting( 'wrap_settings', 'wrap_settings', array( 'sanitize_callback' => array( self::class, 'sanitize' ) ) );

		add_settings_section(
			'wrap_settings_section',
			__( 'General', 'wrap' ),
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
			'wrap_disable_admin_for_subscribers',
			__( 'Restrict Admin Section', 'wrap' ),
			array( self::class, 'disable_admin_for_subscribers_render' ),
			'wrap_settings',
			'wrap_settings_section'
		);

        add_settings_field(
			'wrap_base_url',
			__( 'Third-party app Base URL', 'wrap' ),
			array( self::class, 'base_url_render' ),
			'wrap_settings',
			'wrap_settings_section'
		);

        add_settings_field(
            'wrap_base_path',
            __('Third-party app Base Path', 'magiiic-wrap'),
            [self::class, 'base_path_render'],
            'wrap_settings',
            'wrap_settings_section'
        );

        // Ajouter le nouveau champ "Profile Page"
        add_settings_field(
            'profile_page',
            __('Profile Page', 'magiiic-wrap'),
            [self::class, 'profile_page_render'],
            'wrap_settings',
            'wrap_settings_section'
        );
    }

	public static function base_url_render() {
		$options = get_option( 'wrap_settings' );
		$class   = 'regular-text';
        $form_invalid = '';
		if ( get_option( 'wrap_base_url_error' ) ) {
			$class .= ' error';
            $form_invalid = 'form-invalid form-required';
		}
		$home_url = home_url('/');
		$uri_part = isset($options['wrap_base_url_uri']) ? $options['wrap_base_url_uri'] : '';
		?>
        <div  class="<?php echo $form_invalid; ?>">
            <label>
                <span class="wrap-input-prefix"><?php echo esc_html( $home_url ); ?></span>
                <span class="wrap-prefixed-input"><input type='text' name='wrap_settings[wrap_base_url_uri]' value='<?php echo esc_attr( $uri_part ); ?>' class='<?php echo $class; ?>' id='wrap_base_url_uri'>
            </label>
        </div>
		<?php
	}

	public static function base_path_render() {
		$options = get_option( 'wrap_settings' );
        $class   = 'regular-text';
        $form_invalid = '';
		if ( get_option( 'wrap_base_path_error' ) ) {
            $class .= ' error';
            $form_invalid = 'form-invalid form-required';
        }
        $document_root = $_SERVER['DOCUMENT_ROOT'];
        $subfolder = isset($options['wrap_base_path_subfolder']) ? $options['wrap_base_path_subfolder'] : '';
        ?>
        <div class="">
            <label class="wrap-path-input">
                <span class="wrap-input-prefix"><?php echo esc_html( "$document_root/" ); ?></span>
                <input type='text' name='wrap_settings[wrap_base_path_subfolder]' value='<?php echo esc_attr( $subfolder ); ?>' class='<?php echo "$class $form_invalid"; ?>' id='wrap_base_path_subfolder'>
            </label>
        </div>
        <script>
            document.getElementById('wrap_base_url_uri').addEventListener('input', function() {
                document.getElementById('wrap_base_path_subfolder').value = this.value;
            });
        </script>
        <?php
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
		<form action='options.php' method='post' class='wrap-form wrap-settings-form'>
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
        $original_options = get_option('wrap_settings');
		$output = array();
		if ( isset( $input['wrap_base_url_uri'] ) ) {
			$home_url = home_url('/');
			$uri = ltrim( sanitize_text_field( $input['wrap_base_url_uri'] ), '/' );
			$full_url = rtrim( esc_url_raw( $home_url . $uri ), '/' );
			if ( filter_var( $full_url, FILTER_VALIDATE_URL ) ) {
				$output['wrap_base_url'] = $full_url;
				$output['wrap_base_url_uri'] = $uri;
				if ( self::url_exists( $full_url ) ) {
					delete_option( 'wrap_base_url_error' );
				} else {
                    $output['wrap_base_url'] = $original_options['wrap_base_url'];
                    $output['wrap_base_url_uri'] = $original_options['wrap_base_url_uri'];
					add_settings_error(
						'wrap_settings',
						'wrap_base_url_error',
						sprintf(__( 'WRAP: The URL %s is not reachable.', 'wrap' ), $full_url),
						'error'
					);
					update_option( 'wrap_base_url_error', true );
				}
			} else {
                $output['wrap_base_url'] = $original_options['wrap_base_url'];
                $output['wrap_base_url_uri'] = $original_options['wrap_base_url_uri'];
				add_settings_error(
					'wrap_settings',
					'wrap_base_url_error',
					sprintf(__( 'WRAP: Invalid URL %s. Please enter a valid URL.', 'wrap' ), $full_url),
					'error'
				);
				update_option( 'wrap_base_url_error', true );
			}
            $output['setup_done'] = true;
		}
		if ( isset( $input['wrap_base_path_subfolder'] ) ) {
            $document_root = $_SERVER['DOCUMENT_ROOT'];
            $subfolder = ltrim( sanitize_text_field( $input['wrap_base_path_subfolder'] ), '/' );
            $full_path = rtrim( $document_root . '/' . $subfolder, '/' );
            if ( self::path_exists( $full_path ) ) {
                $output['wrap_base_path'] = $full_path;
                $output['wrap_base_path_subfolder'] = $subfolder;
                delete_option( 'wrap_base_path_error' );
            } else {
                $output['wrap_base_path'] = $original_options['wrap_base_path'];
                $output['wrap_base_path_subfolder'] = $original_options['wrap_base_path_subfolder'];
                add_settings_error(
                    'wrap_settings',
                    'wrap_base_path_error',
                    sprintf(__( 'The path %s does not exist or is not accessible.', 'wrap' ), $full_path),
                    'error'
                );
                update_option( 'wrap_base_path_error', true );
                // Keep the previous wrap_base_path option
            }
        }

        if (isset($input['profile_page'])) {
            $page_id = intval($input['profile_page']);
            if ($page_id > 0) {
                $output['profile_page'] = $page_id;
            }
        } elseif (isset($input['create_default_profile_page']) && $input['create_default_profile_page']) {
            $page_data = array(
                'post_title' => 'Profile',
                'post_content' => '[wrap_user_profile]',
                'post_status' => 'publish',
                'post_type' => 'page'
            );
            $page_id = wp_insert_post($page_data);
            if ($page_id && !is_wp_error($page_id)) {
                $output['profile_page'] = $page_id;
            }
        }

		$output['wrap_menu_position'] = isset( $input['wrap_menu_position'] ) ? 1 : 0;
		$output['wrap_disable_admin_for_subscribers'] = isset( $input['wrap_disable_admin_for_subscribers'] ) ? 1 : 0;

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

    /**
     * Render the Profile Page select field
     */
    public static function profile_page_render() {
        $options = get_option('wrap_settings');
        $selected_page = isset($options['profile_page']) ? $options['profile_page'] : '';
        $pages = self::get_pages_with_shortcode('wrap_user_profile');
        if (empty($pages)) {
            ?>
            <input type='checkbox' name='wrap_settings[create_default_profile_page]' value='1' <?php checked( 1, isset( $options['create_default_profile_page'] ) ? $options['create_default_profile_page'] : 0 ); ?>>
            <label for='wrap_settings[create_default_profile_page]'>
                <?php _e('No profile page found. Create a default profile page.', 'magiiic-wrap'); ?></label>
            <p class="description"><?php _e('Or insert [wrap_user_profile] shortcode in any existing page.', 'magiiic-wrap'); ?></p>
            <?php
        } else {
        ?>
        <select name='wrap_settings[profile_page]' id='profile_page_select'>
            <option value=''>-- <?php _e('Select a Profile Page', 'magiiic-wrap'); ?> --</option>
            <?php foreach ($pages as $page) : ?>
                <option value='<?php echo esc_attr($page->ID); ?>' <?php selected($selected_page, $page->ID); ?>><?php echo esc_html($page->post_title); ?></option>
            <?php endforeach; ?>
        </select>
        <span id="profile_page_links" style="display: <?php echo $selected_page ? 'inline' : 'none'; ?>;">
            <a href="<?php echo esc_url(get_permalink($selected_page)); ?>" target="_blank"><?php _e('View', 'magiiic-wrap'); ?></a> |
            <a href="<?php echo esc_url(get_edit_post_link($selected_page)); ?>" target="_blank"><?php _e('Edit', 'magiiic-wrap'); ?></a>
        </span>
        <script>
            document.getElementById('profile_page_select').addEventListener('change', function() {
                var selectedPage = this.value;
                var linksSpan = document.getElementById('profile_page_links');
                if (selectedPage) {
                    linksSpan.style.display = 'inline';
                    linksSpan.querySelector('a[href*="view"]').href = '<?php echo home_url('/'); ?>?p=' + selectedPage;
                    linksSpan.querySelector('a[href*="edit"]').href = '<?php echo admin_url('post.php?action=edit&post='); ?>' + selectedPage;
                } else {
                    linksSpan.style.display = 'none';
                }
            });
        </script>
        <?php
        }
    }

    /**
     * Get all pages containing a specific shortcode
     *
     * @param string $shortcode The shortcode to search for.
     * @return array Array of WP_Post objects.
     */
    public static function get_pages_with_shortcode($shortcode) {
        global $wpdb;
        $shortcode = esc_sql($shortcode);
        $query = "
        SELECT ID, post_title FROM {$wpdb->posts}
        WHERE post_type = 'page' 
        AND post_status = 'publish'
        AND post_content LIKE '%[" . $shortcode . "]%'
        ";
        $results = $wpdb->get_results($query);

        // Filter out translated pages if WPML is active
        if (function_exists('icl_object_id')) {
            $original_results = [];
            foreach ($results as $page) {
                $original_id = icl_object_id($page->ID, 'page', true, wpml_get_default_language());
                if ($original_id == $page->ID) {
                    $original_results[] = $page;
                }
            }
            return $original_results;
        }

        return $results;
    }

    public static function disable_admin_for_subscribers_render() {
		$options = get_option( 'wrap_settings' );
		?>
		<input type='checkbox' name='wrap_settings[wrap_disable_admin_for_subscribers]' value='1' <?php checked( 1, isset( $options['wrap_disable_admin_for_subscribers'] ) ? $options['wrap_disable_admin_for_subscribers'] : 0 ); ?>>
		<label for='wrap_settings[wrap_disable_admin_for_subscribers]'>
			<?php _e( 'Disable admin section for subscribers with no other roles', 'wrap' ); ?></label>
		<p class="description"><?php _e( 'Disable the admin section and admin bar for users who have only the subscriber role.', 'wrap' ); ?></p>
		<?php
	}

	public static function restrict_admin_access() {
		$options = get_option( 'wrap_settings' );
		if ( isset( $options['wrap_disable_admin_for_subscribers'] ) && $options['wrap_disable_admin_for_subscribers'] && self::user_has_only_role( 'subscriber' ) ) {
			wp_redirect( home_url() );
			exit;
		}
	}

	public static function remove_admin_bar() {
		$options = get_option( 'wrap_settings' );
		if ( isset( $options['wrap_disable_admin_for_subscribers'] ) && $options['wrap_disable_admin_for_subscribers'] && self::user_has_only_role( 'subscriber' ) ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}

	private static function user_has_only_role( $role ) {
		$user = wp_get_current_user();
		return count( $user->roles ) === 1 && in_array( $role, (array) $user->roles );
	}

	public static function initialize_default_options() {
		$default_options = array(
			'wrap_menu_position' => 1,
			'wrap_base_url' => home_url('/'),
			'wrap_base_path' => $_SERVER['DOCUMENT_ROOT'],
		);

		$options = get_option( 'wrap_settings' );
		if ( $options === false ) {
			update_option( 'wrap_settings', $default_options );
		} else {
			$options = array_merge( $default_options, $options );
			update_option( 'wrap_settings', $options );
		}
	}
}

// We don't initialize the class here, it's done in wrap.php
// WrapSettings::init();
