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
        // add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
        // add_action( 'init', array( __CLASS__, 'load_textdomain' ) );

        self::enqueue_style( 'wrap-styles', WRAP_PLUGIN_URL . 'css/styles.css' );
        self::enqueue_style( 'wrap-admin-styles', WRAP_PLUGIN_URL . 'css/admin-styles.css', 'admin_enqueue_scripts' );
        // wp_enqueue_style( 'wrap-admin-styles', WRAP_PLUGIN_URL . 'css/admin-styles.css', array(), '1.0.0' );
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
