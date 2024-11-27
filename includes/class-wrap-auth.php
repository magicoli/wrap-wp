<?php
/**
 * Class WrapAuth
 * Provides authentication for external applications.
 */

// All code comments, user outputs, and debugs are in English.
// Some commands are commented out for further development. Do not remove them.

/**
 * Class WrapAuth
 * Provides authentication for external applications.
 *
 * - Register an endpoint that Apache can use to verify the user
 * - Use the first part of return URI to match the request with a wrap group (defined in wrap-group taxonomy)
 * - Verify that the user is one of the allowed users of the group taxonomy
 * - If fails, display a standard WP error page
 * - If success,
 *      - Store a cookie specific to the group and user, that Apache is able to confirm (by its name only or by the log values if Apache can read them)
 *      - Redirect to the complete return URI
 *
 * - Also include a function to generate the .htaccess rules for the groups
 **/

class WrapAuth {
	public static function init() {
		// Add rewrite rules during initialization
		add_action( 'init', array( self::class, 'add_rewrite_rules' ) );
		// Add custom query variables
		add_filter( 'query_vars', array( self::class, 'add_query_vars' ) );
		// Handle the authentication request
		add_action( 'template_redirect', array( self::class, 'handle_wrap_auth_request' ) );
	}

	public static function add_rewrite_rules() {
		// Add custom rewrite tag
		add_rewrite_tag( '%wrap_auth%', '([^&]+)' );
		// Add custom rewrite rule
		add_rewrite_rule( '^wrap-auth/?$', 'index.php?wrap_auth=1', 'top' );
	}

	public static function add_query_vars( $vars ) {
		$vars[] = 'wrap_auth';
		return $vars;
	}

	public static function handle_wrap_auth_request() {
		$wrap_auth = get_query_var( 'wrap_auth' );

		// If wrap_auth is not set or not equal to 1, do nothing
		if ( $wrap_auth != '1' ) {
			global $wp_query;
			return;
		}

		// Redirect to login page if user is not logged in
		if ( ! is_user_logged_in() ) {
			// Redirect to login page
			wp_redirect( wp_login_url( $_SERVER['REQUEST_URI'] ) );
			exit;
		}

		// Fetch the redirect URL
		$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : site_url();

		// Extract the group from the redirect URL
		$path       = parse_url( $redirect_to, PHP_URL_PATH );
		$path_parts = explode( '/', trim( $path, '/' ) );
		$wrap_group = isset( $path_parts[0] ) ? $path_parts[0] : '';

		// Check if group is valid
		if ( empty( $wrap_group ) ) {
			self::render_error_page( __( 'Invalid request.', 'wrap' ), 400 );
		}

		// Check that user is allowed to access the group
		$user = wp_get_current_user();
		$term = get_term_by( 'slug', $wrap_group, 'wrap-group' );
		if ( $term ) {
			$group_users = get_term_meta( $term->term_id, 'group_users', true );
			if ( ! in_array( $user->ID, (array) $group_users ) && ! current_user_can( 'manage_options' ) ) {
				self::render_error_page( __( 'You are not authorized to access this page.', 'wrap' ), 403 );
			}
		} else {
			self::render_error_page( __( 'Group not found.', 'wrap' ), 404 );
		}

		// Write cookie specific to the group for Apache to verify
		setcookie( 'wrap_auth_' . $wrap_group, '1', time() + 3600, '/', '.' . $_SERVER['HTTP_HOST'], is_ssl(), true );

		// Redirect to the original URL
		wp_redirect( $redirect_to );
		exit;
	}

	/**
	 * Render a standard WordPress error page with a custom message
	 *
	 * @param string $message Error message to display
	 * @param int    $status_code HTTP status code
	 */
	public static function render_error_page( $message, $status_code = 403 ) {
		// Set the HTTP status header
		status_header( $status_code );
		nocache_headers();

		// Include the WordPress header
		get_header();

		?>
		<div class="wrap">
			<p><?php echo esc_html( $message ); ?></p>
			<p><a href="<?php echo esc_url( home_url() ); ?>"><?php _e( 'Return to home page', 'wrap' ); ?></a></p>
		</div>
		<?php

		// Include the WordPress footer
		get_footer();
		exit;
	}

	/**
	 * Generate .htaccess rules for a specific group
	 *
	 * @param string $group Group slug
	 * @return string .htaccess rules
	 */
	public static function build_htaccess_rules( $group ) {
		// Check if group is valid
		$term = get_term_by( 'slug', $group, 'wrap-group' );
		if ( ! $term ) {
			return sprintf(
				__( '# Cannot generate rules, group %s not found.', 'wrap' ),
				$group
			);
		}
		$site_home = home_url();
		$rules     = "<IfModule mod_rewrite.c>\n";
		$rules    .= "  RewriteEngine On\n";
		$rules    .= "  RewriteCond %{HTTP_COOKIE} !wrap_auth_$group=1\n";
		$rules    .= '  RewriteRule ^(.*)$ ' . $site_home . '/wrap-auth/?redirect_to=%{REQUEST_SCHEME}://%{HTTP_HOST}%{REQUEST_URI}' . " [L,R=302]\n";
		$rules    .= "</IfModule>\n";

		return $rules;
	}
}
