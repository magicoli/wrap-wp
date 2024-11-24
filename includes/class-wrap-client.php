<?php

# All code comments, user outputs and debugs must be in English.
# Some commands are commented out for further development. Do not remove them.

// Ajouter une taxonomie personnalisée pour les clients
add_action('init', 'wrap_create_client_taxonomy');

function wrap_create_client_taxonomy() {
    $labels = array(
        'name' => _x('Clients', 'taxonomy general name', 'wrap'),
        'singular_name' => _x('Client', 'taxonomy singular name', 'wrap'),
        'search_items' => __('Search Clients', 'wrap'),
        'all_items' => __('All Clients', 'wrap'),
        'parent_item' => __('Parent Client', 'wrap'),
        'parent_item_colon' => __('Parent Client:', 'wrap'),
        'edit_item' => __('Edit Client', 'wrap'),
        'update_item' => __('Update Client', 'wrap'),
        'add_new_item' => __('Add New Client', 'wrap'),
        'new_item_name' => __('New Client Name', 'wrap'),
        'menu_name' => __('Clients', 'wrap'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'client'),
    );

    register_taxonomy('client', array('post'), $args);
}

// Ajouter un champ personnalisé pour associer les utilisateurs aux clients
add_action('client_add_form_fields', 'wrap_add_client_user_field', 10, 2);
add_action('client_edit_form_fields', 'wrap_edit_client_user_field', 10, 2);
add_action('edited_client', 'wrap_save_client_user_field', 10, 2);
add_action('create_client', 'wrap_save_client_user_field', 10, 2);

function wrap_add_client_user_field($taxonomy) {
    ?>
    <div class="form-field term-group">
        <label for="client_users"><?php _e('Users', 'wrap'); ?></label>
        <select multiple="multiple" name="client_users[]" id="client_users" class="postform">
            <?php
            $users = get_users();
            foreach ($users as $user) {
                echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
            }
            ?>
        </select>
    </div>
    <?php
}

function wrap_edit_client_user_field($term, $taxonomy) {
    $client_users = get_term_meta($term->term_id, 'client_users', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="client_users"><?php _e('Users', 'wrap'); ?></label></th>
        <td>
            <select multiple="multiple" name="client_users[]" id="client_users" class="postform">
                <?php
                $users = get_users();
                foreach ($users as $user) {
                    $selected = in_array($user->ID, (array) $client_users) ? 'selected="selected"' : '';
                    echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    <?php
}

function wrap_save_client_user_field($term_id, $tt_id) {
    if (isset($_POST['client_users']) && is_array($_POST['client_users'])) {
        update_term_meta($term_id, 'client_users', $_POST['client_users']);
    } else {
        delete_term_meta($term_id, 'client_users');
    }
}
