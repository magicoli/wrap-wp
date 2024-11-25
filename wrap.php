<?php
/**
 * Plugin Name: W.R.A.P. - Web Reel Automated Publishing
 * Plugin URI: https://wrap.rocks/
 * Description: Authentication for W.R.A.P. (WordPress REST API Proxy)
 * Version: 0.1.0-dev
 * Author: Olivier van Helden
 * Author URI: https://magiiic.media/
 * License: AGPL-3.0
 * License URI: https://www.gnu.org/licenses/agpl-3.0.html
 * Text Domain: magiiic-wrap
 * Domain Path: /languages
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * 
 * @package wrap-auth
 * @version 0.1.0-dev
 * 
 * A very minimal plugin to provide authentication for external applications via their .htaccess
 *
 * Add these lines in your external site's .htaccess file:
 * <IfModule mod_rewrite.c>
 *   RewriteEngine On
 *   RewriteCond %{HTTP_COOKIE} !wordpress_logged_in_
 *     RewriteRule ^(.*)$ https://yourdomain.org/wp-login.php?redirect_to=%{REQUEST_SCHEME}://%{HTTP_HOST}%{REQUEST_URI} [L,R=302]
 * </IfModule>
 * 
**/

# All code comments, user outputs and debugs must be in English.
# Some commands are commented out for further development. Do not remove them.

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

function wrap_enqueue_frontend_styles() {
    wp_enqueue_style('wrap-styles', plugin_dir_url(__FILE__) . 'css/styles.css', array(), '1.0.1');
}
add_action('wp_enqueue_scripts', 'wrap_enqueue_frontend_styles');
function wrap_enqueue_admin_styles() {
    wp_enqueue_style('wrap-admin-styles', plugin_dir_url(__FILE__) . 'css/admin-styles.css', array(), '1.0.1');
}
add_action('admin_enqueue_scripts', 'wrap_enqueue_admin_styles');

// Include classes
require_once plugin_dir_path(__FILE__) . 'includes/class-wrap-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-wrap-user.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-wrap-group.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-wrap-auth.php';

// Initialise classes
WrapSettings::init();
WrapUser::init();
WrapGroup::init();
WrapAuth::init();
