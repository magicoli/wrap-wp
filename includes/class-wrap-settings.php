<?php
/**
 * Class WrapSettings
 * Provides settings for the WRAP plugin.
 */

# Do not remove these instructions:
# All code comments, user outputs and debugs must be in English.
# Some commands are commented out for further development. Do not remove them.

class WrapSettings {
    public static function init() {
        add_action('admin_menu', [self::class, 'add_admin_menu']);
        add_action('admin_init', [self::class, 'settings_init']);
    }

    public static function add_admin_menu() {
        add_menu_page(__('WRAP', 'magiiic-wrap'), 'WRAP', 'manage_options', 'wrap', [self::class, 'options_page'], 'dashicons-admin-generic');
        add_submenu_page('wrap', __('WRAP Settings', 'magiiic-wrap'), __('Settings', 'magiiic-wrap'), 'manage_options', 'wrap', [self::class, 'options_page']);
    }

    public static function settings_init() {
        register_setting('wrap_settings', 'wrap_settings', ['sanitize_callback' => [self::class, 'sanitize']]);

        add_settings_section(
            'wrap_settings_section',
            __('Settings for WRAP Plugin', 'magiiic-wrap'),
            [self::class, 'settings_section_callback'],
            'wrap_settings'
        );

        add_settings_field(
            'wrap_base_url',
            __('Base URL for External Site', 'magiiic-wrap'),
            [self::class, 'base_url_render'],
            'wrap_settings',
            'wrap_settings_section'
        );
    }

    public static function base_url_render() {
        $options = get_option('wrap_settings');
        $class = '';
        if (get_option('wrap_base_url_error')) {
            $class = 'error';
        }
        ?>
        <input type='text' name='wrap_settings[wrap_base_url]' value='<?php echo esc_attr($options['wrap_base_url']); ?>' class='<?php echo $class; ?>'>
        <?php
        if (get_option('wrap_base_url_error')) {
            echo '<p class="description" style="color: red;">' . __('The URL is valid but not reachable.', 'magiiic-wrap') . '</p>';
            delete_option('wrap_base_url_error');
        }
    }

    public static function settings_section_callback() {
        echo __('Enter the base URL for the external site.', 'magiiic-wrap');
    }

    public static function options_page() {
        ?>
        <form action='options.php' method='post'>
            <h2><?php _e('WRAP Settings', 'magiiic-wrap'); ?></h2>
            <?php
            settings_fields('wrap_settings');
            do_settings_sections('wrap_settings');
            submit_button();
            ?>
        </form>
        <?php
    }

    public static function sanitize($input) {
        $output = [];
        if (isset($input['wrap_base_url'])) {
            $url = esc_url_raw($input['wrap_base_url']);
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $output['wrap_base_url'] = $url;
                if (!self::url_exists($url)) {
                    add_settings_error(
                        'wrap_base_url',
                        'wrap_base_url_error',
                        # This message appears below the field, so it's not necessary to specify "WRAP:"
                        __('The URL is properly formatted but not reachable.', 'magiiic-wrap'),
                        'error'
                    );
                    update_option('wrap_base_url_error', true);
                }
            } else {
                add_settings_error(
                    'wrap_base_url',
                    'wrap_base_url_error',
                    # Keep "WRAP:" in the message, it appears abofe the page and it's annoying to have a message not knowing which plugin sent it
                    __('WRAP: Invalid URL. Please enter a valid URL.', 'magiiic-wrap'),
                    'error'
                );
            }
        }
        return $output;
    }

    private static function url_exists($url) {
        $headers = @get_headers($url);
        return $headers && strpos($headers[0], '200') !== false;
    }
}

// We don't initialize the class here, it's done in wrap.php
// WrapSettings::init();
