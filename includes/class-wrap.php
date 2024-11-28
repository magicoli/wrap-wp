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

    static function enqueue_style( $handle, $src, $hook=null ) {
        if ( empty($hook) ) {
            if ( is_admin() ) {
                $hook = 'admin_enqueue_scripts';
            } else {
                $hook = 'wp_enqueue_scripts';
            }
        }

        // if not a full URL, make it one
        if ( strpos( $src, 'http' ) !== 0 ) {
            $src = WRAP_PLUGIN_URL . $src;
        }
        
        // Check if we are in git repo
        $version = WRAP_VERSION;
        if ( file_exists( WRAP_PLUGIN_DIR . '.git' ) ) {
            $version .= '-dev.' . time();
        }

        if( function_exists( 'wp_enqueue_style' ) ) {
            wp_enqueue_style( $handle, $src, array(), $version );
        } else {
            add_action( $hook, function() use ( $hook, $handle, $src, $version ) {
                wp_enqueue_style( $handle, $src, array(), $version );
            });
        }
    }
}
