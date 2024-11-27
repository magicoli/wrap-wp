<?php
/**
 * User class
 *
 * Provides user related functionalities
 *  - Profile page
 **/

// All code comments, user outputs, and debugs must be in English.
// Some commands are commented out for further development. Do not remove them.

/**
 * Class WrapUser
 *
 * Provides user related functionalities including profile page
 */
class WrapUser {
	public static function init() {
		// Register shortcode for profile page
		add_shortcode( 'wrap_user_profile', array( self::class, 'render_profile_shortcode' ) );
		add_action( 'init', array( self::class, 'handle_profile_update' ) );
	}

	/**
	 * Render the user profile shortcode
	 *
	 * @return string HTML content for the profile page
	 */
	public static function render_profile_shortcode() {
		// Ensure the user is logged in
		if ( ! is_user_logged_in() ) {
			return '<p>' . __( 'You need to be logged in to view this page.', 'wrap' ) . '</p>';
		}

		$user = wp_get_current_user();

		// Check if the form is submitted and display success message
		if ( isset( $_GET['profile_updated'] ) && $_GET['profile_updated'] == 'true' ) {
			echo '<div class="wrap"><p>' . __( 'Your profile has been updated successfully.', 'wrap' ) . '</p></div>';
		}

		// Récupérer les groupes accessibles
		$groups        = self::get_user_groups();
		$options       = get_option( 'wrap_settings' );
		$wrap_base_url = isset( $options['wrap_base_url'] ) ? $options['wrap_base_url'] : '';
		ob_start();
		?>
		<style>
			.wrap-form .form-table th {
				vertical-align: top;
				text-align: left;
				padding: 20px 10px 20px 0;
				width: 200px;
				line-height: 1.3;
				font-size: 1rem;
				font-weight: 600;
			}
			.wrap-form .form-table td {
				margin-bottom: 9px;
				padding: 15px 10px;
				/* line-height: 1.3; */
				vertical-align: middle;
			}
			.wrap-form input[type=color],
			.wrap-form input[type=date],
			.wrap-form input[type=datetime-local],
			.wrap-form input[type=datetime],
			.wrap-form input[type=email],
			.wrap-form input[type=month],
			.wrap-form input[type=number],
			.wrap-form input[type=password],
			.wrap-form input[type=search],
			.wrap-form input[type=tel],
			.wrap-form input[type=text],
			.wrap-form input[type=time],
			.wrap-form input[type=url],
			.wrap-form input[type=week],
			.wrap-form .regular-text,
			.wrap-form .button,
			.wrap-form button,
			.wrap-form input,
			.wrap-form select,
			.wrap-form textarea,
			.wrap-form option {
				border-radius: 5px;
				border-color: rgba(0, 0, 0, .15);
				color: #373737;
				font-size: 1rem;
				padding: 10px;
				margin: 5px 0;
				/* padding: 1px 8px; */
			}
			.wrap-form .regular-text,
			.wrap-form .button {
				padding: 10px;
				margin: 5px 0;
			}
			.wrap-form .regular-text {
				width: 25em;
			}
		</style>
		<div class="wrap form wrap-form">
			<form method="post" class="form wrap-form wrap-user-profile">
				<?php wp_nonce_field( 'wrap_user_profile_update', 'wrap_user_profile_nonce' ); ?>
				<table class="form-table">
					<tr class="form-field">
						<th scope="row"><label for="name"><?php _e( 'Name', 'wrap' ); ?></label></th>
						<td>
							<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( get_user_meta( $user->ID, 'first_name', true ) ); ?>" class="regular-text" placeholder="<?php _e( 'First Name', 'wrap' ); ?>" />
							<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( get_user_meta( $user->ID, 'last_name', true ) ); ?>" class="regular-text" placeholder="<?php _e( 'Last Name', 'wrap' ); ?>" />
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="nickname"><?php _e( 'Nick name', 'wrap' ); ?></label></th>
						<td>
							<input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( get_user_meta( $user->ID, 'nickname', true ) ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="display_name"><?php _e( 'Display name publicly as', 'wrap' ); ?></label></th>
						<td>
							<select name="display_name" id="display_name" class="regular-text">
								<?php
								$public_display = array();
								$public_display['display_username']  = $user->user_login;
								$public_display['display_nickname']  = $user->nickname;
								if ( ! empty( $user->first_name ) ) {
									$public_display['display_firstname'] = $user->first_name;
								}
								if ( ! empty( $user->last_name ) ) {
									$public_display['display_lastname'] = $user->last_name;
								}
								if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
									$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
									$public_display['display_lastfirst'] = $user->last_name . ' ' . $user->first_name;
								}
								$public_display = array_unique( array_map( 'trim', $public_display ) );
								foreach ( $public_display as $id => $item ) {
									?>
									<option <?php selected( $user->display_name, $item ); ?>><?php echo $item; ?></option>
									<?php
								}
								?>
							</select>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="email"><?php _e( 'Email', 'wrap' ); ?></label></th>
						<td>
							<input type="email" name="email" id="email" value="<?php echo esc_attr( $user->user_email ); ?>" class="regular-text" required />
						</td>
					</tr>
					<?php if ( ! empty( $groups ) ) : ?>
						<tr class="form-field">
						<th scope="row"><label for="groups"><?php _e( 'WRAP sites', 'wrap' ); ?></label></th>
						<td>
							<ul class="no-bullets">
								<?php foreach ( $groups as $group ) : ?>
									<li>
										<?php echo esc_html( $group->name ); ?> 
										<a href="<?php echo esc_url( "$wrap_base_url/$group->slug/upload/" ); ?>">
											<?php _e( 'Uploads', 'wrap' ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
					<?php endif; ?>
				</table>
					<p class="submit">
					<input type="submit" value="<?php esc_attr_e( 'Update Profile', 'wrap' ); ?>" class="button button-primary" />
				</p>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get the list of groups accessible by the current user
	 *
	 * @return array Array of WP_Term objects representing the groups
	 */
	public static function get_user_groups() {
		$user_id = get_current_user_id();

		// If the user is an administrator, return all groups
		if ( current_user_can( 'manage_options' ) ) {
			$groups = get_terms(
				array(
					'taxonomy'   => 'wrap-group',
					'hide_empty' => false,
				)
			);
		} else {
			// Get groups where the user is a member
			$groups = get_terms(
				array(
					'taxonomy'   => 'wrap-group',
					'hide_empty' => false,
					'meta_query' => array(
						array(
							'key'     => 'group_users',
							'value'   => '"' . $user_id . '"',
							'compare' => 'LIKE',
						),
					),
				)
			);
		}

		// Check and return groups
		if ( is_wp_error( $groups ) ) {
			return array();
		}

		return $groups;
	}

	/**
	 * Handle the profile update form submission
	 */
	public static function handle_profile_update() {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['wrap_user_profile_nonce'] ) ) {
			// Verify nonce
			if ( ! wp_verify_nonce( $_POST['wrap_user_profile_nonce'], 'wrap_user_profile_update' ) ) {
				return;
			}

			// Ensure the user is logged in
			if ( ! is_user_logged_in() ) {
				return;
			}

			$user_id = get_current_user_id();
			$user    = get_user_by( 'id', $user_id );

			// Sanitize and validate input
			$first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
			$last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
			$nickname   = isset( $_POST['nickname'] ) ? sanitize_text_field( $_POST['nickname'] ) : '';
			$display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( $_POST['display_name'] ) : '';
			$email      = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

			// Validate email
			if ( ! is_email( $email ) ) {
				// Handle invalid email, possibly add an error message
				// For simplicity, we'll just return without updating
				return;
			}

			// Update user meta
			update_user_meta( $user_id, 'first_name', $first_name );
			update_user_meta( $user_id, 'last_name', $last_name );
			update_user_meta( $user_id, 'nickname', $nickname );
			wp_update_user(
				array(
					'ID'           => $user_id,
					'display_name' => $display_name,
				)
			);

			// Update user email if changed
			if ( $email !== $user->user_email ) {
				wp_update_user(
					array(
						'ID'         => $user_id,
						'user_email' => $email,
					)
				);
			}

			// Redirect to avoid resubmission
			wp_redirect( add_query_arg( 'profile_updated', 'true', get_permalink() ) );
			exit;
		}
	}
}

// We don't need to call the init() method here as it will be called by the main plugin file
// Stop adding this back again and again
// WrapUser::init();
