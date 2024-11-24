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
 *     RewriteRule ^(.*)$ https://yourdomain.org/wp-login.php?redirect_to=https://%{HTTP_HOST}%{REQUEST_URI} [L,R=302]
 * </IfModule>
 * 
**/

# All code comments, user outputs and debugs must be in English.
# Some commands are commented out for further development. Do not remove them.

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Action to validate access before any request to the API or resource
function wp_webapp_check_access() {
    // Vérifier si l'utilisateur est connecté
    if (!is_user_logged_in()) {
        $redirect_to = urlencode($_GET['redirect_to'] ?? '/');
        error_log('WRAP auth redirect_to: ' . $redirect_to);
        wp_redirect(wp_login_url($redirect_to));
        exit;
    }

    // Basic check, allow access to any logged in user
    $user = wp_get_current_user();
    if (!$user) {
        wp_die('You do not have permission to access this resource.');
    }

    // Check if the user has the required role
    // if (!in_array('subscriber', (array) $user->roles)) {
    //     wp_die('You do not have permission to access this resource.');
    // }

    // If user is logged in and authorized, you can allow access to the webapp
    // You can also add additional actions if necessary
}

// Hook the validation function to the request handling
add_action('template_redirect', 'wp_webapp_check_access');
