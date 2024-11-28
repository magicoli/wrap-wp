<?php
/**
 * Main class for the W.R.A.P. plugin
 * 
 * @package wrap
**/

class Wrap {
    /**
     * Initialise the plugin
     */
    public static function init() {
        // Add actions
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
        // add_action( 'init', array( __CLASS__, 'load_textdomain' ) );

        self::enqueue_style( 'wrap-styles', WRAP_PLUGIN_URL . 'css/styles.css' );
        self::enqueue_style( 'wrap-admin-styles', WRAP_PLUGIN_URL . 'css/admin-styles.css', 'admin_enqueue_scripts' );
        // wp_enqueue_style( 'wrap-admin-styles', WRAP_PLUGIN_URL . 'css/admin-styles.css', array(), '1.0.0' );
    }

    public static function get_option( $option_name ) {
        $options = get_option( 'wrap_settings' );
        return isset( $options[$option_name] ) ? $options[$option_name] : null;
    }

    public static function add_admin_menu() {
        $options  = get_option( 'wrap_settings' );
        $position = isset( $options['wrap_menu_position'] ) && $options['wrap_menu_position'] ? 2 : null;
        $icon_url = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents(WRAP_PLUGIN_DIR . 'icons/gift-solid-menu-color.svg'));

        // Check if the user is an admin or part of a group
        if ( ! empty( WrapUser::get_user_groups() ) ) {
            $capability = 'read';
        } else {
            $capability = 'manage_options';
        }
        // $capability = 'read';
        if(Wrap::get_option('setup_done')) {
            add_menu_page( __( 'WRAP', 'wrap' ), 'WRAP', $capability, 'wrap', array( __CLASS__, 'dashboard_page' ), $icon_url, $position );
            add_submenu_page( 'wrap', __( 'Dashboard', 'wrap' ), __( 'Dashboard', 'wrap' ), $capability, 'wrap', array( __CLASS__, 'dashboard_page' ) );
        } else {
            add_menu_page( __( 'WRAP', 'wrap' ), 'WRAP', 'manage_options', 'wrap', array( 'WrapSettings', 'options_page' ), $icon_url, $position );
        }
    }

    public static function dashboard_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'WRAP Dashboard', 'wrap' ); ?></h1>
            <p><?php _e( 'Welcome to the WRAP plugin dashboard.', 'wrap' ); ?></p>
        </div>
        <?php
    }

    static function enqueue_style( $handle, $src, $deps = array(), $ver = WRAP_VERSION, $media = 'all' ) {
        if ( is_admin() ) {
            $hook = 'admin_enqueue_scripts';
        } else {
            $hook = 'wp_enqueue_scripts';
        }

        // if not a full URL, make it one
        if ( strpos( $src, 'http' ) !== 0 ) {
            $src = WRAP_PLUGIN_URL . $src;
        }
        
        // If in git repo, add time() to version for internal urls
        if ( self::is_dev() && strpos( $src, WRAP_PLUGIN_URL ) !== false ) {
            $ver .= '-dev.' . time();
        }

        if( function_exists( 'wp_enqueue_style' ) ) {
            wp_enqueue_style( $handle, $src, array(), $ver );
        } else {
            add_action( $hook, function() use ( $hook, $handle, $src, $ver ) {
                wp_enqueue_style( $handle, $src, array(), $ver );
            });
        }
    }

    /**
     * If the .git directory exists, consider the plugin in development mode
     * 
     * @return bool
     */
    static public function is_dev() {
        return file_exists( WRAP_PLUGIN_DIR . '.git' );
    }

    public static function enqueue_select2() {
		wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );
		wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );
		wp_add_inline_script( 'select2', 'jQuery(document).ready(function($) { $(".select2").select2(); });' );
	}
}
