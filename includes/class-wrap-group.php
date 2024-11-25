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
        add_action('wrap-group_add_form_fields', [self::class, 'add_group_user_field'], 10, 2);
        add_action('wrap-group_edit_form_fields', [self::class, 'edit_group_user_field'], 10, 2);
        add_action('edited_wrap-group', [self::class, 'save_group_user_field'], 10, 2);
        add_action('create_wrap-group', [self::class, 'save_group_user_field'], 10, 2);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_select2']);

        add_filter('parent_file', [self::class, 'set_current_menu']);

        // Ajouter les hooks pour les colonnes personnalisées
        add_filter('manage_edit-wrap-group_columns', [self::class, 'add_group_columns']);
        add_action('manage_wrap-group_custom_column', [self::class, 'fill_group_columns'], 10, 3);
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

        register_taxonomy('wrap-group', array(), $args);
    }

    public static function add_admin_menu() {
        add_submenu_page('wrap', __('Groups', 'magiiic-wrap'), __('Groups', 'magiiic-wrap'), 'manage_options', 'edit-tags.php?taxonomy=wrap-group');
    }

    public static function set_current_menu( $parent_file ) {
        global $submenu_file, $current_screen, $pagenow;
        if( ($pagenow == 'edit-tags.php' || $pagenow == 'term.php') && $current_screen->taxonomy == 'wrap-group') {
            $parent_file = 'wrap';
            $submenu_file = 'edit-tags.php?taxonomy=wrap-group';
        }
        return $parent_file;
    }

    public static function add_group_user_field($taxonomy) {
        ?>
        <div class="form-field term-wrap-group ">
            <label for="group_users"><?php _e('Allow authentication', 'wrap'); ?></label>
            <select multiple="multiple" name="group_users[]" id="group_users" class="postform select2 full-width">
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
        WrapAdminSidebar::add_sidebar(array(
            'main_selector' => 'form.validate',
            'page' => 'term.php',
            'taxonomy' => 'wrap-group'
        ));
        $group_users = get_term_meta($term->term_id, 'group_users', true);
        ?>
        <tr class="form-field term-wrap-group">
            <th scope="row"><label for="group_users"><?php _e('Allow authentication', 'wrap'); ?></label></th>
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
        $htaccess_rules = WrapAuth::build_htaccess_rules($term->slug);
        ?>
        <div class='wrap-admin-sidebar'>
                <label><?php _e('.htaccess rules', 'wrap'); ?></label>
                <textarea readonly rows="10" cols="50" class="large-text code"><?php echo esc_textarea($htaccess_rules); ?></textarea>
                <p class="description"><?php _e('Copy these rules and paste them into your .htaccess file of your group\'s main folder.', 'wrap'); ?></p>
        </div>
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
        wp_enqueue_style('wrap-admin-styles', WRAP_PLUGIN_URL . 'css/admin-styles.css', array(), '1.0.0');
        wp_add_inline_script('select2', 'jQuery(document).ready(function($) { $(".select2").select2(); });');
    }

    /**
     * Ajouter des colonnes personnalisées à la taxonomie wrap-group
     *
     * @param array $columns Colonnes existantes.
     * @return array Colonnes modifiées.
     */
    public static function add_group_columns($columns) {
        // Insérer la nouvelle colonne après la colonne 'name'
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'name') {
                $new_columns['group_users'] = __('Allowed Users', 'wrap');
            }
        }
        return $new_columns;
    }

    /**
     * Remplir les colonnes personnalisées de la taxonomie wrap-group
     *
     * @param string $content Contenu à afficher dans la colonne.
     * @param string $column_name Nom de la colonne.
     * @param int $term_id ID du terme.
     */
    public static function fill_group_columns($content, $column_name, $term_id) {
        if ($column_name === 'group_users') {
            $group_users = get_term_meta($term_id, 'group_users', true);
            if (!empty($group_users) && is_array($group_users)) {
                $users = get_users(['include' => $group_users]);
                if ($users) {
                    $user_names = array_map(function($user) {
                        // if connected user can edit users, return $user->display_name as a link to user's profile, otherwise plain text
                        if (current_user_can('edit_users')) {
                            return sprintf('<a href="%s">%s</a>', get_edit_user_link($user->ID), $user->display_name);
                        } else {
                            return $user->display_name;
                        }
                    }, $users);
                    $content = implode(', ', $user_names);
                } else {
                    $content = '—';
                }
            } else {
                $content = '—';
            }
        }
        return $content;
    }
}

// We don't initialize the class here, it's done in wrap.php
// WrapGroup::init();
