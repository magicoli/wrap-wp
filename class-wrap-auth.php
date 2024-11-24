<?php
/**
 * Class WrapAuth
 * Provides authentication for external applications.
 */

# All code comments, user outputs and debugs must be in English.
# Some commands are commented out for further development. Do not remove them.

class WrapAuth {
    public static function check_access() {
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
}
