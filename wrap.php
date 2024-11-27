<?php
/**
 * Plugin Name: W.R.A.P. - Web Reel Automated Publishing
 * Plugin URI: https://wrap.rocks/
 * Description: WordPress-based authentication for third-party applications via .htaccess
 * Author: Olivier van Helden
 * Author URI: https://magiiic.media/
 * Text Domain: wrap
 * Domain Path: /languages
 * License: AGPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/agpl-3.0.html
 * Version: 1.0.0
 *
 * @package wrap
 *
 * A very minimal plugin to provide authentication for external applications via their .htaccess
 *
 * Add these lines in your external site's .htaccess file:
 * <IfModule mod_rewrite.c>
 *   RewriteEngine On
 *   RewriteCond %{HTTP_COOKIE} !wrap_auth_your-group=1
 *     RewriteRule ^(.*)$ https://yourdomain.org/wrap-auth/?redirect_to=%{REQUEST_SCHEME}://%{HTTP_HOST}%{REQUEST_URI} [L,R=302]
 * </IfModule>
 **/

// All code comments, user outputs and debugs must be in English.
// Some commands are commented out for further development. Do not remove them.

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Constants
define( 'WRAP_VERSION', '1.0.0' );
define( 'WRAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WRAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

function wrap_enqueue_frontend_styles() {
	wp_enqueue_style( 'wrap-styles', WRAP_PLUGIN_URL . 'css/styles.css', array(), '1.0.3' );
}
add_action( 'wp_enqueue_scripts', 'wrap_enqueue_frontend_styles' );
function wrap_enqueue_admin_styles() {
	wp_enqueue_style( 'wrap-admin-styles', WRAP_PLUGIN_URL . 'css/admin-styles.css', array(), '1.0.3' );
}
add_action( 'admin_enqueue_scripts', 'wrap_enqueue_admin_styles' );

// Include classes
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wrap-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wrap-user.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wrap-group.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wrap-auth.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/helpers/class-wrap-admin-sidebar.php';

// Initialise classes
WrapAdminSidebar::init(); // No really necessary for this class at this time
WrapSettings::init();
WrapUser::init();
WrapGroup::init();
WrapAuth::init();
