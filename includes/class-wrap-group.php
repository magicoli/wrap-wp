<?php
/**
 * Class WrapGroup
 * Provides group taxonomy and user association.
 */

# Do not remove these instructions:
# All code comments, user outputs and debugs must be in English.
# Some commands are commented out for further development. Do not remove them.

class WrapGroup {
    public static function init() {
        add_action('init', [self::class, 'create_group_taxonomy']);
        add_action('admin_menu', [self::class, 'add_admin_menu']);
        add_action('group_add_form_fields', [self::class, 'add_group_user_field'], 10, 2);
        add_action('group_edit_form_fields', [self::class, 'edit_group_user_field'], 10, 2);
        add_action('edited_group', [self::class, 'save_group_user_field'], 10, 2);
        add_action('create_group', [self::class, 'save_group_user_field'], 10, 2);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_select2']);
    }

    public static function create_group_taxonomy() {
        $labels = array(
            'name' => _x('Groups', 'taxonomy general name', 'wrap'),
            'singular_name' => _x('Group', 'taxonomy singular name', 'wrap'),
            'search_items' => __('Search Groups', 'wrap'),
            'all_items' => __('All Groups', 'wrap'),
            'parent_item' => __('Parent Group', 'wrap'),
            'parent_item_colon' => __('Parent Group:', 'wrap'),
            'edit_item' => __('Edit Group', 'wrap'),
            'update_item' => __('Update Group', 'wrap'),
            'add_new_item' => __('Add New Group', 'wrap'),
            'new_item_name' => __('New Group Name', 'wrap'),
            'menu_name' => __('Groups', 'wrap'),
        );

        $args = array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'wrap-group'),
        );

        register_taxonomy('wrap-group', array('post'), $args);
    }

    public static function add_admin_menu() {
        add_submenu_page('wrap', __('Groups', 'magiiic-wrap'), __('Groups', 'magiiic-wrap'), 'manage_options', 'edit-tags.php?taxonomy=wrap-group');
    }

    public static function add_group_user_field($taxonomy) {
        ?>
        <div class="form-field term-group">
            <label for="group_users"><?php _e('Allowed users', 'wrap'); ?></label>
            <select multiple="multiple" name="group_users[]" id="group_users" class="postform select2">
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

    public static function edit_group_user_field($term, $taxonomy) {
        $group_users = get_term_meta($term->term_id, 'group_users', true);
        ?>
        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="group_users"><?php _e('Allowed users', 'wrap'); ?></label></th>
            <td>
                <select multiple="multiple" name="group_users[]" id="group_users" class="postform select2">
                    <?php
                    $users = get_users();
                    foreach ($users as $user) {
                        $selected = in_array($user->ID, (array) $group_users) ? 'selected="selected"' : '';
                        echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    public static function save_group_user_field($term_id, $tt_id) {
        if (isset($_POST['group_users']) && is_array($_POST['group_users'])) {
            update_term_meta($term_id, 'group_users', $_POST['group_users']);
        } else {
            delete_term_meta($term_id, 'group_users');
        }
    }

    public static function enqueue_select2() {
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0');
        wp_add_inline_script('select2', 'jQuery(document).ready(function($) { $(".select2").select2(); });');
    }
}

// We don't initialize the class here, it's done in wrap.php
// WrapGroup::init();
