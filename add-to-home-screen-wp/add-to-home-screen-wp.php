<?php
/*
Plugin Name: Add to Home Screen & Progressive Web App
Plugin URI: https://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/
Description: Turn your WordPress site into a Web App (PWA) with a stylish 'Add to Home Screen' prompt for iOS & Android. Boost engagement without native app costs!
Version: 2.7
Author: Ziyad Bachalany
Author URI: https://tulipemedia.com
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: add-to-home-screen-wp
*/

if (!defined('ABSPATH')) {
    exit;
}

// Load plugin text domain for translation
function athswp_load_textdomain() {
    load_plugin_textdomain('add-to-home-screen-wp', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'athswp_load_textdomain');

if (!class_exists('SimpleATHSOptions')) :

    define('SimpleATHSOptions_ID', 'simple_aths_options');
    define('SimpleATHSOptions_NICK', 'ATHS Network Options');

    class SimpleATHSOptions {

        /**
         * Returns default settings for the plugin options.
         */
        public static function get_default_settings() {
            $is_network_activated = is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__));

            return [
                'new_message_ios' => $is_network_activated
                    ? __('🚀 Add %network_name (%site_name) to your %device with %icon and %add now! 🌟', 'add-to-home-screen-wp')
                    : __('🚀 Add %site_name to your %device with %icon and %add now! 🌟', 'add-to-home-screen-wp'),
                'new_message_android' => $is_network_activated
                    ? __('🚀 Add %network_name (%site_name) to your %device now! 🌟', 'add-to-home-screen-wp')
                    : __('🚀 Add %site_name to your %device now! 🌟', 'add-to-home-screen-wp'),
                'new_startdelay' => 2,
                'new_lifespan' => 20,
                'new_expire_days' => 0,
                'new_bottomoffset' => 14,
                'new_animationin' => 'fade',
                'new_animationout' => 'fade',
                'new_touchicon_url' => '',
                'new_web_app_title' => '',
                'new_returning_visitors_only' => 'off',
                'new_precomposed_icon' => 'off',
                'new_enable_balloon_ios_frontend' => 'on',
                'new_install_prompt_android' => 'native_button',
                'new_enable_pwa' => 'on',
                'new_balloon_display_frontend' => 'all_pages',
                'new_athswp_frontend_pwa_start_url' => 'homepage',
                'new_athswp_pwa_custom_url' => '',
                'new_athswp_delete_data_on_uninstall' => 'off',
            ];
        }

        /**
         * Sanitizes messages, allowing specific placeholders and basic HTML.
         */
        public static function sanitize_message($input) {
            if (!is_string($input)) {
                return '';
            }

            $allowed_tags = ['%site_name%', '%network_name%', '%device%', '%icon%', '%add%'];
            $placeholders = [];
            foreach ($allowed_tags as $index => $tag) {
                $placeholder = '[[TAG_' . $index . ']]';
                $placeholders[$placeholder] = $tag;
                $input = str_replace($tag, $placeholder, $input);
            }

            $allowed_html = [
                'center' => [],
                'h4' => [],
                'h3' => [],
                'h2' => [],
                'h1' => [],
                'strong' => [],
                'br' => [],
                'p' => [],
                'b' => [],
                'i' => [],
            ];
            $sanitized = wp_kses($input, $allowed_html);

            foreach ($placeholders as $placeholder => $tag) {
                $sanitized = str_replace($placeholder, $tag, $sanitized);
            }

            return $sanitized;
        }

        /**
         * Registers settings for the plugin.
         */
        public static function register() {
            $option_group = is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__)) ? 'simple_aths_network_options' : 'simple_aths_site_options';

            register_setting($option_group, 'new_message_ios', ['sanitize_callback' => [__CLASS__, 'sanitize_message']]);
            register_setting($option_group, 'new_message_android', ['sanitize_callback' => [__CLASS__, 'sanitize_message']]);
            register_setting($option_group, 'new_startdelay', ['sanitize_callback' => 'absint']);
            register_setting($option_group, 'new_lifespan', ['sanitize_callback' => 'absint']);
            register_setting($option_group, 'new_expire_days', ['sanitize_callback' => 'absint']);
            register_setting($option_group, 'new_animationin', ['sanitize_callback' => 'sanitize_text_field']);
            register_setting($option_group, 'new_animationout', ['sanitize_callback' => 'sanitize_text_field']);
            register_setting($option_group, 'new_bottomoffset', ['sanitize_callback' => 'absint']);
            register_setting($option_group, 'new_touchicon_url', ['sanitize_callback' => 'esc_url_raw']);
            register_setting($option_group, 'new_web_app_title', ['sanitize_callback' => 'sanitize_text_field']);
            register_setting($option_group, 'new_returning_visitors_only', ['sanitize_callback' => 'sanitize_key']);
            register_setting($option_group, 'new_precomposed_icon', ['sanitize_callback' => 'sanitize_key']);
            register_setting($option_group, 'new_enable_balloon_ios_frontend', ['sanitize_callback' => 'sanitize_key']);
            register_setting($option_group, 'new_install_prompt_android', ['sanitize_callback' => 'sanitize_text_field']);
            register_setting($option_group, 'new_enable_pwa', ['sanitize_callback' => 'sanitize_key']);
            register_setting($option_group, 'new_balloon_display_frontend', ['sanitize_callback' => 'sanitize_text_field']);
            register_setting($option_group, 'new_athswp_frontend_pwa_start_url', ['sanitize_callback' => 'sanitize_text_field']);
            register_setting($option_group, 'new_athswp_pwa_custom_url', ['sanitize_callback' => 'esc_url_raw']);
            register_setting($option_group, 'new_athswp_delete_data_on_uninstall', ['sanitize_callback' => 'sanitize_key']);

            do_action('athswp_register_pro_settings');
        }

        /**
         * Adds network admin menu for multisite.
         */
        public static function menu_network() {
            if (is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__))) {
                if (class_exists('ATHSWP_Pro')) {
                    return;
                }
                add_menu_page(
                    SimpleATHSOptions_NICK,
                    SimpleATHSOptions_NICK,
                    'manage_network_options',
                    'simple_aths_settings',
                    [__CLASS__, 'display_settings_page'],
                    'dashicons-smartphone',
                    80
                );
            }
        }

        /**
         * Adds site admin menu for single sites or subsites.
         */
        public static function menu_site() {
            if (!is_multisite() || !is_plugin_active_for_network(plugin_basename(__FILE__))) {
                add_menu_page(
                    __('Add to Home Screen', 'add-to-home-screen-wp'),
                    __('Add to Home Screen', 'add-to-home-screen-wp'),
                    'manage_options',
                    'simple_aths_settings',
                    [__CLASS__, 'display_settings_page'],
                    'dashicons-smartphone',
                    80
                );
            }
        }

        /**
         * Displays the settings page with tabs.
         */
        public static function display_settings_page() {
            $tabs = [
                'general' => __('General', 'add-to-home-screen-wp'),
                'pro' => __('Pro Settings', 'add-to-home-screen-wp'),
                'support' => __('Support', 'add-to-home-screen-wp'),
            ];
            if (class_exists('ATHSWP_Pro')) {
                $tabs['license'] = __('License', 'add-to-home-screen-pro');
            }
            $tabs['uninstall'] = __('Uninstall', 'add-to-home-screen-wp');
            $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('Add to Home Screen & PWA', 'add-to-home-screen-wp'); ?></h1>
                <h2 class="nav-tab-wrapper">
                    <?php foreach ($tabs as $tab => $name) : ?>
                        <a href="?page=simple_aths_settings&tab=<?php echo esc_attr($tab); ?>" class="nav-tab <?php echo $active_tab === $tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html($name); ?></a>
                    <?php endforeach; ?>
                </h2>
                <?php
                if ($active_tab === 'general') {
                    if (is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__))) {
                        self::options_page_network();
                    } else {
                        self::options_page_site();
                    }
                } elseif ($active_tab === 'pro') {
                    self::pro_teaser_page();
                } elseif ($active_tab === 'support') {
                    self::support_page();
                } elseif ($active_tab === 'license' && class_exists('ATHSWP_Pro')) {
                    do_action('athswp_pro_license_tab');
                } elseif ($active_tab === 'uninstall') {
                    self::uninstall_page();
                }
                ?>
            </div>
            <?php
        }

        /**
         * Network settings page for multisite.
         */
/**
 * Network settings page for multisite.
 */
public static function options_page_network() {
    if (!current_user_can('manage_network_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'add-to-home-screen-wp'));
    }

    $options_message = '';
    $defaults = self::get_default_settings();

    if (isset($_POST['simple_aths_save_settings']) && check_admin_referer('simple_aths_network_options_update', 'simple_aths_nonce')) {
        $settings = [
            'new_message_ios' => self::sanitize_message($_POST['new_message_ios'] ?? ''),
            'new_message_android' => self::sanitize_message($_POST['new_message_android'] ?? ''),
            'new_startdelay' => isset($_POST['new_startdelay']) && $_POST['new_startdelay'] !== '' ? absint($_POST['new_startdelay']) : $defaults['new_startdelay'],
            'new_lifespan' => isset($_POST['new_lifespan']) && $_POST['new_lifespan'] !== '' ? absint($_POST['new_lifespan']) : $defaults['new_lifespan'],
            'new_expire_days' => isset($_POST['new_expire_days']) && $_POST['new_expire_days'] !== '' ? absint($_POST['new_expire_days']) : $defaults['new_expire_days'],
            'new_animationin' => sanitize_text_field($_POST['new_animationin'] ?? 'fade'),
            'new_animationout' => sanitize_text_field($_POST['new_animationout'] ?? 'fade'),
            'new_bottomoffset' => isset($_POST['new_bottomoffset']) && $_POST['new_bottomoffset'] !== '' ? absint($_POST['new_bottomoffset']) : $defaults['new_bottomoffset'],
            'new_touchicon_url' => esc_url_raw($_POST['new_touchicon_url'] ?? ''),
            'new_web_app_title' => sanitize_text_field($_POST['new_web_app_title'] ?? ''),
            'new_returning_visitors_only' => isset($_POST['new_returning_visitors_only']) ? 'on' : 'off',
            'new_precomposed_icon' => isset($_POST['new_precomposed_icon']) ? 'on' : 'off',
            'new_enable_balloon_ios_frontend' => isset($_POST['new_enable_balloon_ios_frontend']) ? 'on' : 'off',
            'new_install_prompt_android' => sanitize_text_field($_POST['new_install_prompt_android'] ?? 'custom_floating_balloon'),
            'new_enable_pwa' => isset($_POST['new_enable_pwa']) ? 'on' : 'off',
            'new_balloon_display_frontend' => sanitize_text_field($_POST['new_balloon_display_frontend'] ?? 'all_pages'),
            'new_athswp_frontend_pwa_start_url' => sanitize_text_field($_POST['new_athswp_frontend_pwa_start_url'] ?? 'homepage'),
            'new_athswp_pwa_custom_url' => esc_url_raw($_POST['new_athswp_pwa_custom_url'] ?? ''),
        ];

        foreach ($settings as $key => $value) {
            simple_aths_update_setting($key, $value);
        }

        $options_message = '<div class="updated"><p>' . esc_html__('Settings saved successfully!', 'add-to-home-screen-wp') . '</p></div>';
    }

    $defaults = self::get_default_settings();
    $settings = [
        'new_message_ios' => simple_aths_get_setting('new_message_ios'),
        'new_message_android' => simple_aths_get_setting('new_message_android'),
        'new_startdelay' => simple_aths_get_setting('new_startdelay', $defaults['new_startdelay']),
        'new_lifespan' => simple_aths_get_setting('new_lifespan', $defaults['new_lifespan']),
        'new_expire_days' => simple_aths_get_setting('new_expire_days', $defaults['new_expire_days']),
        'new_animationin' => simple_aths_get_setting('new_animationin'),
        'new_animationout' => simple_aths_get_setting('new_animationout'),
        'new_bottomoffset' => simple_aths_get_setting('new_bottomoffset', $defaults['new_bottomoffset']),
        'new_touchicon_url' => simple_aths_get_setting('new_touchicon_url'),
        'new_web_app_title' => simple_aths_get_setting('new_web_app_title'),
        'new_returning_visitors_only' => simple_aths_get_setting('new_returning_visitors_only'),
        'new_precomposed_icon' => simple_aths_get_setting('new_precomposed_icon'),
        'new_enable_balloon_ios_frontend' => simple_aths_get_setting('new_enable_balloon_ios_frontend'),
        'new_install_prompt_android' => simple_aths_get_setting('new_install_prompt_android'),
        'new_enable_pwa' => simple_aths_get_setting('new_enable_pwa'),
        'new_balloon_display_frontend' => simple_aths_get_setting('new_balloon_display_frontend'),
        'new_athswp_frontend_pwa_start_url' => simple_aths_get_setting('new_athswp_frontend_pwa_start_url'),
        'new_athswp_pwa_custom_url' => simple_aths_get_setting('new_athswp_pwa_custom_url'),
    ];

    wp_enqueue_script('jquery');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Add to Home Screen & PWA (Network Settings)', 'add-to-home-screen-wp'); ?></h1>
        <?php echo $options_message; ?>

        <form method="post" action="">
            <?php wp_nonce_field('simple_aths_network_options_update', 'simple_aths_nonce'); ?>
            <?php settings_fields('simple_aths_network_options'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><h3><?php esc_html_e('PWA Settings', 'add-to-home-screen-wp'); ?></h3></th>
                    <td>
                        <p class="description"><?php esc_html_e('Configure Progressive Web App features.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_enable_pwa"><?php esc_html_e('Enable PWA', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="checkbox" name="new_enable_pwa" id="new_enable_pwa" <?php checked($settings['new_enable_pwa'] === 'on'); ?> />
                        <p class="description"><?php esc_html_e('Enable Progressive Web App features (spinner and basic caching) across the admin dashboard and frontend.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_athswp_frontend_pwa_start_url"><?php esc_html_e('Frontend PWA Start URL', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <select name="new_athswp_frontend_pwa_start_url" id="new_athswp_frontend_pwa_start_url">
                            <option value="homepage" <?php selected($settings['new_athswp_frontend_pwa_start_url'], 'homepage'); ?>><?php esc_html_e('Homepage', 'add-to-home-screen-wp'); ?></option>
                            <option value="homepage_with_path" <?php selected($settings['new_athswp_frontend_pwa_start_url'], 'homepage_with_path'); ?>><?php esc_html_e('Homepage with Path', 'add-to-home-screen-wp'); ?></option>
                        </select>
                        <input type="text" name="new_athswp_pwa_custom_url" id="new_athswp_pwa_custom_url" value="<?php echo esc_attr($settings['new_athswp_pwa_custom_url']); ?>" placeholder="<?php esc_attr_e('e.g., /category/video/', 'add-to-home-screen-wp'); ?>" style="display: <?php echo $settings['new_athswp_frontend_pwa_start_url'] === 'homepage_with_path' ? 'inline-block' : 'none'; ?>;" class="regular-text" />
                        <p class="description"><?php esc_html_e('Choose where the PWA launches when added from the frontend. "Homepage with Path" starts at the homepage and redirects to a relative path (e.g., /category/video/).', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><h3><?php esc_html_e('Frontend Floating Balloon', 'add-to-home-screen-wp'); ?></h3></th>
                    <td>
                        <p class="description"><?php esc_html_e('Configure how to prompt users to install your web app or add a shortcut to their home screen', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_enable_balloon_ios_frontend"><?php esc_html_e('Show Floating Balloon in Frontend (iOS)', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="checkbox" name="new_enable_balloon_ios_frontend" id="new_enable_balloon_ios_frontend" <?php checked($settings['new_enable_balloon_ios_frontend'] === 'on'); ?> />
                        <p class="description"><?php esc_html_e('Display a floating balloon to prompt iOS users to install the app in the frontend of all subsites.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_install_prompt_android"><?php esc_html_e('Installation Prompt in Frontend (Android)', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <select name="new_install_prompt_android" id="new_install_prompt_android">
                            <option value="custom_floating_balloon" <?php selected($settings['new_install_prompt_android'], 'custom_floating_balloon'); ?>><?php esc_html_e('Floating Balloon', 'add-to-home-screen-wp'); ?></option>
                            <option value="native_button" <?php selected($settings['new_install_prompt_android'], 'native_button'); ?>><?php esc_html_e('Native Install Button', 'add-to-home-screen-wp'); ?></option>
                            <option value="disabled" <?php selected($settings['new_install_prompt_android'], 'disabled'); ?>><?php esc_html_e('Disabled', 'add-to-home-screen-wp'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Choose how to prompt users to install the app on Android devices in the frontend. The \'Native Install Button\' uses the browser\'s native prompt, available only in the frontend.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_balloon_display_frontend"><?php esc_html_e('Balloon Display in Frontend', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <select name="new_balloon_display_frontend" id="new_balloon_display_frontend">
                            <option value="all_pages" <?php selected($settings['new_balloon_display_frontend'], 'all_pages'); ?>><?php esc_html_e('All Pages', 'add-to-home-screen-wp'); ?></option>
                            <option value="homepage" <?php selected($settings['new_balloon_display_frontend'], 'homepage'); ?>><?php esc_html_e('Homepage', 'add-to-home-screen-wp'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Choose where to display the floating balloon on the frontend.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_returning_visitors_only"><?php esc_html_e('Show to Returning Visitors Only', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="checkbox" name="new_returning_visitors_only" id="new_returning_visitors_only" <?php checked($settings['new_returning_visitors_only'] === 'on'); ?> />
                        <p class="description"><?php esc_html_e('Show the balloon only to returning visitors.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_message_ios"><?php esc_html_e('Floating Balloon Message for iOS', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <textarea rows="3" cols="50" name="new_message_ios" id="new_message_ios" placeholder="<?php echo esc_attr($defaults['new_message_ios']); ?>"><?php echo esc_textarea($settings['new_message_ios']); ?></textarea>
                        <p class="description">
                            <?php
                            esc_html_e('Custom message for iOS devices. Use %site_name for the site name, %device for the user\'s device, %icon for the first add icon, and %add for the second add icon. Supports basic HTML (e.g., <strong>, <i>). If empty, the default message will be used.', 'add-to-home-screen-wp');
                            if (is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__))) {
                                echo esc_html__(', %network_name for the network name', 'add-to-home-screen-wp');
                            }
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_message_android"><?php esc_html_e('Floating Balloon Message for Android', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <textarea rows="3" cols="50" name="new_message_android" id="new_message_android" placeholder="<?php echo esc_attr($defaults['new_message_android']); ?>"><?php echo esc_textarea($settings['new_message_android']); ?></textarea>
                        <p class="description">
                            <?php
                            esc_html_e('Custom message for Android devices. Use %site_name for the site name, and %device for the user\'s device. Note: %icon and %add are not supported in the Android balloon. Supports basic HTML (e.g., <strong>, <i>). If empty, the default message will be used.', 'add-to-home-screen-wp');
                            if (is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__))) {
                                echo esc_html__(', %network_name for the network name', 'add-to-home-screen-wp');
                            }
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_animationin"><?php esc_html_e('Animation In', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <select name="new_animationin" id="new_animationin">
                            <option value="drop" <?php selected($settings['new_animationin'], 'drop'); ?>><?php esc_html_e('Drop', 'add-to-home-screen-wp'); ?></option>
                            <option value="bubble" <?php selected($settings['new_animationin'], 'bubble'); ?>><?php esc_html_e('Bubble', 'add-to-home-screen-wp'); ?></option>
                            <option value="fade" <?php selected($settings['new_animationin'], 'fade'); ?>><?php esc_html_e('Fade', 'add-to-home-screen-wp'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Animation when the balloon appears.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_animationout"><?php esc_html_e('Animation Out', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <select name="new_animationout" id="new_animationout">
                            <option value="drop" <?php selected($settings['new_animationout'], 'drop'); ?>><?php esc_html_e('Drop', 'add-to-home-screen-wp'); ?></option>
                            <option value="bubble" <?php selected($settings['new_animationout'], 'bubble'); ?>><?php esc_html_e('Bubble', 'add-to-home-screen-wp'); ?></option>
                            <option value="fade" <?php selected($settings['new_animationout'], 'fade'); ?>><?php esc_html_e('Fade', 'add-to-home-screen-wp'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Animation when the balloon disappears.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_startdelay"><?php esc_html_e('Start Delay', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="number" name="new_startdelay" id="new_startdelay" value="<?php echo esc_attr($settings['new_startdelay']); ?>" placeholder="<?php echo esc_attr($defaults['new_startdelay']); ?>" min="0" step="0.1" />
                        <p class="description"><?php printf(esc_html__('Seconds before showing the balloon. Default: %s', 'add-to-home-screen-wp'), esc_html($defaults['new_startdelay'])); ?></p>
                        <p class="description"><?php esc_html_e('Note: Ensure this is not too close to Lifespan to give users enough time to read the balloon.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_lifespan"><?php esc_html_e('Lifespan', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="number" name="new_lifespan" id="new_lifespan" value="<?php echo esc_attr($settings['new_lifespan']); ?>" placeholder="<?php echo esc_attr($defaults['new_lifespan']); ?>" min="0" step="0.1" />
                        <p class="description"><?php printf(esc_html__('Seconds before hiding the balloon. Default: %s', 'add-to-home-screen-wp'), esc_html($defaults['new_lifespan'])); ?></p>
                        <p class="description"><?php esc_html_e('Note: Set this sufficiently higher than Start Delay to ensure the balloon stays visible long enough.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_expire_days"><?php esc_html_e('Expiration Time', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="number" name="new_expire_days" id="new_expire_days" value="<?php echo esc_attr($settings['new_expire_days']); ?>" placeholder="<?php echo esc_attr($defaults['new_expire_days']); ?>" min="0" />
                        <p class="description"><?php printf(esc_html__('Days before showing the balloon again after it has been closed. Default: %s', 'add-to-home-screen-wp'), esc_html($defaults['new_expire_days'])); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_bottomoffset"><?php esc_html_e('Bottom Offset', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="number" name="new_bottomoffset" id="new_bottomoffset" value="<?php echo esc_attr($settings['new_bottomoffset']); ?>" placeholder="<?php echo esc_attr($defaults['new_bottomoffset']); ?>" min="0" />
                        <p class="description"><?php printf(esc_html__('Distance in pixels from the bottom or top. Default: %s', 'add-to-home-screen-wp'), esc_html($defaults['new_bottomoffset'])); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_precomposed_icon"><?php esc_html_e('Precomposed Icon', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="checkbox" name="new_precomposed_icon" id="new_precomposed_icon" <?php checked($settings['new_precomposed_icon'] === 'on'); ?> />
                        <p class="description"><?php esc_html_e('Display the touch icon without gloss (iOS only).', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_touchicon_url"><?php esc_html_e('Touch Icon URL', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="url" name="new_touchicon_url" id="new_touchicon_url" value="<?php echo esc_url($settings['new_touchicon_url']); ?>" />
                        <button type="button" class="button upload-icon-button" data-input="new_touchicon_url"><?php esc_html_e('Upload Icon', 'add-to-home-screen-wp'); ?></button>
                        <p class="description"><?php esc_html_e('URL of the icon for the PWA on the home screen (192x192 or 512x512 PNG recommended). If empty, a default icon will be used. Note: Browsers may use an Apple Touch icon defined elsewhere (e.g., via WordPress Site Icon, your theme, or a plugin) for the home screen.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_web_app_title"><?php esc_html_e('Web App Title for all subsites', 'add-to-home-screen-wp'); ?></label></th>
                    <td>
                        <input type="text" name="new_web_app_title" id="new_web_app_title" value="<?php echo esc_attr($settings['new_web_app_title']); ?>" />
                        <p class="description"><?php esc_html_e('Custom title when added to the home screen. If empty, the site name will be used.', 'add-to-home-screen-wp'); ?></p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="simple_aths_save_settings" class="button-primary" value="<?php esc_attr_e('Save Settings', 'add-to-home-screen-wp'); ?>" />
            </p>
        </form>
    </div>
    <?php
}

        /**
         * Site settings page for single sites or subsites.
         */
        public static function options_page_site() {
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'add-to-home-screen-wp'));
            }

            $options_message = '';
            $defaults = self::get_default_settings();

            if (isset($_POST['simple_aths_save_settings']) && check_admin_referer('simple_aths_site_options_update', 'simple_aths_nonce')) {
                $settings = [
                    'new_message_ios' => self::sanitize_message($_POST['new_message_ios'] ?? ''),
                    'new_message_android' => self::sanitize_message($_POST['new_message_android'] ?? ''),
                    'new_startdelay' => isset($_POST['new_startdelay']) && $_POST['new_startdelay'] !== '' ? absint($_POST['new_startdelay']) : $defaults['new_startdelay'],
                    'new_lifespan' => isset($_POST['new_lifespan']) && $_POST['new_lifespan'] !== '' ? absint($_POST['new_lifespan']) : $defaults['new_lifespan'],
                    'new_expire_days' => isset($_POST['new_expire_days']) && $_POST['new_expire_days'] !== '' ? absint($_POST['new_expire_days']) : $defaults['new_expire_days'],
                    'new_animationin' => sanitize_text_field($_POST['new_animationin'] ?? 'fade'),
                    'new_animationout' => sanitize_text_field($_POST['new_animationout'] ?? 'fade'),
                    'new_bottomoffset' => isset($_POST['new_bottomoffset']) && $_POST['new_bottomoffset'] !== '' ? absint($_POST['new_bottomoffset']) : $defaults['new_bottomoffset'],
                    'new_touchicon_url' => esc_url_raw($_POST['new_touchicon_url'] ?? ''),
                    'new_web_app_title' => sanitize_text_field($_POST['new_web_app_title'] ?? ''),
                    'new_returning_visitors_only' => isset($_POST['new_returning_visitors_only']) ? 'on' : 'off',
                    'new_precomposed_icon' => isset($_POST['new_precomposed_icon']) ? 'on' : 'off',
                    'new_enable_balloon_ios_frontend' => isset($_POST['new_enable_balloon_ios_frontend']) ? 'on' : 'off',
                    'new_install_prompt_android' => sanitize_text_field($_POST['new_install_prompt_android'] ?? 'custom_floating_balloon'),
                    'new_enable_pwa' => isset($_POST['new_enable_pwa']) ? 'on' : 'off',
                    'new_balloon_display_frontend' => sanitize_text_field($_POST['new_balloon_display_frontend'] ?? 'all_pages'),
                    'new_athswp_frontend_pwa_start_url' => sanitize_text_field($_POST['new_athswp_frontend_pwa_start_url'] ?? 'homepage'),
                    'new_athswp_pwa_custom_url' => esc_url_raw($_POST['new_athswp_pwa_custom_url'] ?? ''),
                ];

                foreach ($settings as $key => $value) {
                    simple_aths_update_setting($key, $value);
                }

                $options_message = '<div class="updated"><p>' . esc_html__('Settings saved successfully!', 'add-to-home-screen-wp') . '</p></div>';
            }

            $defaults = self::get_default_settings();
            $settings = [
                'new_message_ios' => simple_aths_get_setting('new_message_ios'),
                'new_message_android' => simple_aths_get_setting('new_message_android'),
                'new_startdelay' => simple_aths_get_setting('new_startdelay', $defaults['new_startdelay']),
                'new_lifespan' => simple_aths_get_setting('new_lifespan', $defaults['new_lifespan']),
                'new_expire_days' => simple_aths_get_setting('new_expire_days', $defaults['new_expire_days']),
                'new_animationin' => simple_aths_get_setting('new_animationin'),
                'new_animationout' => simple_aths_get_setting('new_animationout'),
                'new_bottomoffset' => simple_aths_get_setting('new_bottomoffset', $defaults['new_bottomoffset']),
                'new_touchicon_url' => simple_aths_get_setting('new_touchicon_url'),
                'new_web_app_title' => simple_aths_get_setting('new_web_app_title'),
                'new_returning_visitors_only' => simple_aths_get_setting('new_returning_visitors_only'),
                'new_precomposed_icon' => simple_aths_get_setting('new_precomposed_icon'),
                'new_enable_balloon_ios_frontend' => simple_aths_get_setting('new_enable_balloon_ios_frontend'),
                'new_install_prompt_android' => simple_aths_get_setting('new_install_prompt_android'),
                'new_enable_pwa' => simple_aths_get_setting('new_enable_pwa'),
                'new_balloon_display_frontend' => simple_aths_get_setting('new_balloon_display_frontend'),
                'new_athswp_frontend_pwa_start_url' => simple_aths_get_setting('new_athswp_frontend_pwa_start_url'),
                'new_athswp_pwa_custom_url' => simple_aths_get_setting('new_athswp_pwa_custom_url'),
            ];

            wp_enqueue_script('jquery');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_style('wp-color-picker');
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('Add to Home Screen & PWA Settings', 'add-to-home-screen-wp'); ?></h1>
                <?php echo $options_message; ?>

                <form method="post" action="">
                    <?php wp_nonce_field('simple_aths_site_options_update', 'simple_aths_nonce'); ?>
                    <?php settings_fields('simple_aths_site_options'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><h3><?php esc_html_e('PWA Settings', 'add-to-home-screen-wp'); ?></h3></th>
                            <td>
                                <p class="description"><?php esc_html_e('Configure Progressive Web App features.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_enable_pwa"><?php esc_html_e('Enable PWA', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="checkbox" name="new_enable_pwa" id="new_enable_pwa" <?php checked($settings['new_enable_pwa'] === 'on'); ?> />
                                <p class="description"><?php esc_html_e('Enable Progressive Web App features.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_athswp_frontend_pwa_start_url"><?php esc_html_e('Frontend PWA Start URL', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <select name="new_athswp_frontend_pwa_start_url" id="new_athswp_frontend_pwa_start_url">
                                    <option value="homepage" <?php selected($settings['new_athswp_frontend_pwa_start_url'], 'homepage'); ?>><?php esc_html_e('Homepage', 'add-to-home-screen-wp'); ?></option>
                                    <option value="homepage_with_path" <?php selected($settings['new_athswp_frontend_pwa_start_url'], 'homepage_with_path'); ?>><?php esc_html_e('Homepage with Path', 'add-to-home-screen-wp'); ?></option>
                                </select>
                                <input type="text" name="new_athswp_pwa_custom_url" id="new_athswp_pwa_custom_url" value="<?php echo esc_attr($settings['new_athswp_pwa_custom_url']); ?>" placeholder="<?php esc_attr_e('e.g., /category/video/', 'add-to-home-screen-wp'); ?>" style="display: <?php echo $settings['new_athswp_frontend_pwa_start_url'] === 'homepage_with_path' ? 'inline-block' : 'none'; ?>;" class="regular-text" />
                                <p class="description"><?php esc_html_e('Choose where the PWA launches when added from the frontend. "Homepage with Path" starts at the homepage and redirects to a relative path (e.g., /category/video/).', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><h3><?php esc_html_e('Frontend Floating Balloon', 'add-to-home-screen-wp'); ?></h3></th>
                            <td>
                                <p class="description"><?php esc_html_e('Configure how to prompt users to install your web app or add a shortcut to their home screen', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_enable_balloon_ios_frontend"><?php esc_html_e('Show Floating Balloon in Frontend (iOS)', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="checkbox" name="new_enable_balloon_ios_frontend" id="new_enable_balloon_ios_frontend" <?php checked($settings['new_enable_balloon_ios_frontend'] === 'on'); ?> />
                                <p class="description"><?php esc_html_e('Display a floating balloon to prompt iOS users to install the app in the frontend.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_install_prompt_android"><?php esc_html_e('Installation Prompt in Frontend (Android)', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <select name="new_install_prompt_android" id="new_install_prompt_android">
                                    <option value="custom_floating_balloon" <?php selected($settings['new_install_prompt_android'], 'custom_floating_balloon'); ?>><?php esc_html_e('Floating Balloon', 'add-to-home-screen-wp'); ?></option>
                                    <option value="native_button" <?php selected($settings['new_install_prompt_android'], 'native_button'); ?>><?php esc_html_e('Native Install Button', 'add-to-home-screen-wp'); ?></option>
                                    <option value="disabled" <?php selected($settings['new_install_prompt_android'], 'disabled'); ?>><?php esc_html_e('Disabled', 'add-to-home-screen-wp'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Choose how to prompt users to install the app on Android devices in the frontend. The \'Native Install Button\' uses the browser\'s native prompt, available only in the frontend.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_balloon_display_frontend"><?php esc_html_e('Balloon Display in Frontend', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <select name="new_balloon_display_frontend" id="new_balloon_display_frontend">
                                    <option value="all_pages" <?php selected($settings['new_balloon_display_frontend'], 'all_pages'); ?>><?php esc_html_e('All Pages', 'add-to-home-screen-wp'); ?></option>
                                    <option value="homepage" <?php selected($settings['new_balloon_display_frontend'], 'homepage'); ?>><?php esc_html_e('Homepage', 'add-to-home-screen-wp'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Choose where to display the floating balloon on the frontend.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_returning_visitors_only"><?php esc_html_e('Show to Returning Visitors Only', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="checkbox" name="new_returning_visitors_only" id="new_returning_visitors_only" <?php checked($settings['new_returning_visitors_only'] === 'on'); ?> />
                                <p class="description"><?php esc_html_e('Show the balloon only to returning visitors.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_message_ios"><?php esc_html_e('Floating Balloon Message for iOS', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <textarea rows="3" cols="50" name="new_message_ios" id="new_message_ios" placeholder="<?php echo esc_attr($defaults['new_message_ios']); ?>"><?php echo esc_textarea($settings['new_message_ios']); ?></textarea>
                                <p class="description"><?php esc_html_e('Custom message for iOS devices. Use %site_name for the site name, %device for the user\'s device, %icon for the first add icon, and %add for the second add icon. Supports basic HTML (e.g., <strong>, <i>). If empty, the default message will be used.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_message_android"><?php esc_html_e('Floating Balloon Message for Android', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <textarea rows="3" cols="50" name="new_message_android" id="new_message_android" placeholder="<?php echo esc_attr($defaults['new_message_android']); ?>"><?php echo esc_textarea($settings['new_message_android']); ?></textarea>
                                <p class="description"><?php esc_html_e('Custom message for Android devices. Use %site_name for the site name, and %device for the user\'s device. Note: %icon and %add are not supported in the Android balloon. Supports basic HTML (e.g., <strong>, <i>). If empty, the default message will be used.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_animationin"><?php esc_html_e('Animation In', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <select name="new_animationin" id="new_animationin">
                                    <option value="drop" <?php selected($settings['new_animationin'], 'drop'); ?>><?php esc_html_e('Drop', 'add-to-home-screen-wp'); ?></option>
                                    <option value="bubble" <?php selected($settings['new_animationin'], 'bubble'); ?>><?php esc_html_e('Bubble', 'add-to-home-screen-wp'); ?></option>
                                    <option value="fade" <?php selected($settings['new_animationin'], 'fade'); ?>><?php esc_html_e('Fade', 'add-to-home-screen-wp'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Animation when the balloon appears.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_animationout"><?php esc_html_e('Animation Out', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <select name="new_animationout" id="new_animationout">
                                    <option value="drop" <?php selected($settings['new_animationout'], 'drop'); ?>><?php esc_html_e('Drop', 'add-to-home-screen-wp'); ?></option>
                                    <option value="bubble" <?php selected($settings['new_animationout'], 'bubble'); ?>><?php esc_html_e('Bubble', 'add-to-home-screen-wp'); ?></option>
                                    <option value="fade" <?php selected($settings['new_animationout'], 'fade'); ?>><?php esc_html_e('Fade', 'add-to-home-screen-wp'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Animation when the balloon disappears.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_startdelay"><?php esc_html_e('Start Delay', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="number" name="new_startdelay" id="new_startdelay" value="<?php echo esc_attr($settings['new_startdelay']); ?>" placeholder="<?php echo esc_attr($defaults['new_startdelay']); ?>" min="0" step="1" />
                                <p class="description"><?php printf(esc_html__('Seconds before showing the balloon. Default: %s', 'add-to-home-screen-wp'), esc_html($defaults['new_startdelay'])); ?></p>
                                <p class="description"><?php esc_html_e('Note: Ensure this is not too close to Lifespan to give users enough time to read the balloon.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_lifespan"><?php esc_html_e('Lifespan', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="number" name="new_lifespan" id="new_lifespan" value="<?php echo esc_attr($settings['new_lifespan']); ?>" placeholder="<?php echo esc_attr($defaults['new_lifespan']); ?>" min="0" step="1" />
                                <p class="description"><?php printf(esc_html__('Seconds before hiding the balloon. Default: %s', 'add-to-home-screen-wp'), esc_html($defaults['new_lifespan'])); ?></p>
                                <p class="description"><?php esc_html_e('Note: Set this sufficiently higher than Start Delay to ensure the balloon stays visible long enough.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_expire_days"><?php esc_html_e('Expiration Time', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="number" name="new_expire_days" id="new_expire_days" value="<?php echo esc_attr($settings['new_expire_days']); ?>" placeholder="<?php echo esc_attr($defaults['new_expire_days']); ?>" min="0" step="1" />
                                <p class="description"><?php printf(esc_html__('Days before showing the balloon again after it has been closed. Default: %s', 'add-to-home-screen-wp'), esc_html($defaults['new_expire_days'])); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_bottomoffset"><?php esc_html_e('Bottom Offset', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="number" name="new_bottomoffset" id="new_bottomoffset" value="<?php echo esc_attr($settings['new_bottomoffset']); ?>" placeholder="<?php echo esc_attr($defaults['new_bottomoffset']); ?>" min="0" step="1" />
                                <p class="description"><?php printf(esc_html__('Distance in pixels from the bottom or top. Default: %s', 'add-to-home-screen-wp'), esc_html($defaults['new_bottomoffset'])); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_precomposed_icon"><?php esc_html_e('Precomposed Icon', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="checkbox" name="new_precomposed_icon" id="new_precomposed_icon" <?php checked($settings['new_precomposed_icon'] === 'on'); ?> />
                                <p class="description"><?php esc_html_e('Display the touch icon without gloss (iOS only).', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_touchicon_url"><?php esc_html_e('Touch Icon URL', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="url" name="new_touchicon_url" id="new_touchicon_url" value="<?php echo esc_url($settings['new_touchicon_url']); ?>" />
                                <button type="button" class="button upload-icon-button" data-input="new_touchicon_url"><?php esc_html_e('Upload Icon', 'add-to-home-screen-wp'); ?></button>
                                <p class="description"><?php esc_html_e('URL of the icon for the PWA on the home screen (192x192 or 512x512 PNG recommended). If empty, a default icon will be used. Note: Browsers may use an Apple Touch icon defined elsewhere (e.g., via WordPress Site Icon, your theme, or a plugin) for the home screen.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="new_web_app_title"><?php esc_html_e('Web App Title', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="text" name="new_web_app_title" id="new_web_app_title" value="<?php echo esc_attr($settings['new_web_app_title']); ?>" />
                                <p class="description"><?php esc_html_e('Custom title when added to the home screen. If empty, the site name will be used.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" name="simple_aths_save_settings" class="button-primary" value="<?php esc_attr_e('Save Settings', 'add-to-home-screen-wp'); ?>" />
                    </p>
                </form>
            </div>
            <?php
        }

        /**
         * Support page with promotional content.
         */
        public static function support_page() {
            ?>
            <div class="athswp-support-promo" style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd;">
                <h2><?php esc_html_e('Support Add to Home Screen WP’s Future! 🌟', 'add-to-home-screen-wp'); ?></h2>
                <p style="font-size: 16px;"><?php esc_html_e('Add to Home Screen WP is a tool built with passion to help WordPress users like you enhance their mobile experience. Your support can make a huge difference in keeping it growing and improving!', 'add-to-home-screen-wp'); ?></p>

                <div style="max-width: 500px; margin: auto; text-align: center; background: #f9f9f9; padding: 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <h3><?php esc_html_e('Let me know that you are using my plugin!', 'add-to-home-screen-wp'); ?></h3>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode(__('Using the Add to home screen #WordPress #plugin by @ziyadbachalany! http://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/ #iPhone #iPad #Android', 'add-to-home-screen-wp')); ?>" 
                            target="_blank" 
                            class="button button-primary" style="font-size: 16px; padding: 10px 20px; background:rgb(186, 0, 84); border-color: #007cba;">
                                <?php esc_html_e('Spread the word on X!', 'add-to-home-screen-wp'); ?>
                        </a>
                </div>    

                <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
                    <div style="flex: 1; min-width: 300px; background: #f9f9f9; padding: 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h3>☕ <?php esc_html_e('Buy Me a Coffee', 'add-to-home-screen-wp'); ?></h3>
                        <p><?php esc_html_e('Love using Add to Home Screen WP? A small donation can fuel my coffee cup and help me dedicate more time to enhancing this plugin for you!', 'add-to-home-screen-wp'); ?></p>
                        <p>
                            <a href="https://paypal.me/ziyadbachalany" target="_blank" class="button button-primary" style="font-size: 16px; padding: 10px 20px; background: #007cba; border-color: #007cba;">
                                <?php esc_html_e('Donate Now', 'add-to-home-screen-wp'); ?> 💖
                            </a>
                        </p>
                    </div>
                    <div style="flex: 1; min-width: 300px; background: #f9f9f9; padding: 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h3>🌍 <?php esc_html_e('Help Translate', 'add-to-home-screen-wp'); ?></h3>
                        <p><?php esc_html_e('Speak another language? Help make Add to Home Screen WP accessible to more people around the world by contributing to its translations!', 'add-to-home-screen-wp'); ?></p>
                        <p>
                            <a href="https://translate.wordpress.org/projects/wp-plugins/add-to-home-screen-wp/" target="_blank" class="button button-primary" style="font-size: 16px; padding: 10px 20px; background: #46b450; border-color: #46b450;">
                                <?php esc_html_e('Translate Now', 'add-to-home-screen-wp'); ?> ✨
                            </a>
                        </p>
                    </div>
                    <div style="flex: 1; min-width: 300px; background: #f9f9f9; padding: 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h3>⭐ <?php esc_html_e('Rate on WordPress', 'add-to-home-screen-wp'); ?></h3>
                        <p><?php esc_html_e('Enjoying Add to Home Screen WP? Share your feedback by rating it on WordPress—it helps others discover it and motivates me to keep improving!', 'add-to-home-screen-wp'); ?></p>
                        <p>
                            <a href="https://wordpress.org/support/plugin/add-to-home-screen-wp/reviews/#new-post" target="_blank" class="button button-primary" style="font-size: 16px; padding: 10px 20px; background: #f7c948; border-color: #f7c948; color: #000;">
                                <?php esc_html_e('Rate Now', 'add-to-home-screen-wp'); ?> 🌟
                            </a>
                        </p>
                    </div>
                    <div style="flex: 1; min-width: 300px; background: #f9f9f9; padding: 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h3>❓ <?php esc_html_e('Need Help?', 'add-to-home-screen-wp'); ?></h3>
                        <p><?php esc_html_e('Have questions or need assistance? Visit our support page for resources and contact options.', 'add-to-home-screen-wp'); ?></p>
                        <p>
                            <a href="https://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/" target="_blank" class="button button-primary" style="font-size: 16px; padding: 10px 20px; background: #787878; border-color: #787878;">
                                <?php esc_html_e('Get Support', 'add-to-home-screen-wp'); ?> 🛠️
                            </a>
                        </p>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: center; background: #f9f9f9; padding: 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h3>📱 <?php esc_html_e('Follow Me on Social Media!', 'add-to-home-screen-wp'); ?></h3>
                        <p><?php esc_html_e('Stay updated on Add to Home Screen & PWA news, tips, and more by following me on your favorite platforms!', 'add-to-home-screen-wp'); ?></p>
                        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 15px; margin-top: 15px;">
                            <a href="https://www.linkedin.com/in/ziyadbachalany/" target="_blank" style="text-decoration: none;">
                                <img src="<?php echo plugins_url('add-to-home-screen-wp/assets/icons/linkedin-icon.png'); ?>" alt="LinkedIn" style="width: 32px; height: 32px; vertical-align: middle;"> LinkedIn
                            </a>
                            <a href="https://x.com/ziyadbachalany" target="_blank" style="text-decoration: none;">
                                <img src="<?php echo plugins_url('add-to-home-screen-wp/assets/icons/x-icon.png'); ?>" alt="X" style="width: 32px; height: 32px; vertical-align: middle;"> X
                            </a>
                            <a href="https://www.instagram.com/ziyadbachalany/" target="_blank" style="text-decoration: none;">
                                <img src="<?php echo plugins_url('add-to-home-screen-wp/assets/icons/instagram-icon.png'); ?>" alt="Instagram" style="width: 32px; height: 32px; vertical-align: middle;"> Instagram
                            </a>
                            <a href="https://www.facebook.com/ziyadbachalany" target="_blank" style="text-decoration: none;">
                                <img src="<?php echo plugins_url('add-to-home-screen-wp/assets/icons/facebook-icon.png'); ?>" alt="Facebook" style="width: 32px; height: 32px; vertical-align: middle;"> Facebook
                            </a>
                            <a href="https://www.youtube.com/channel/UClMfre0hj-UCxGocDleZxTQ" target="_blank" style="text-decoration: none;">
                                <img src="<?php echo plugins_url('add-to-home-screen-wp/assets/icons/youtube-icon.png'); ?>" alt="YouTube" style="width: 32px; height: 32px; vertical-align: middle;"> YouTube
                            </a>
                            <a href="https://www.tiktok.com/@ziyadbachalany" target="_blank" style="text-decoration: none;">
                                <img src="<?php echo plugins_url('add-to-home-screen-wp/assets/icons/tiktok-icon.png'); ?>" alt="TikTok" style="width: 32px; height: 32px; vertical-align: middle;"> TikTok
                            </a>
                        </div>
                    </div>

            </div>
            <?php
        }

        /**
         * Pro teaser page.
         */
        public static function pro_teaser_page() {
            $is_network_activated = is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__));
            $license_url = $is_network_activated && !is_network_admin() ? admin_url('network/settings.php?page=simple_aths_settings&tab=license') : admin_url('admin.php?page=simple_aths_settings&tab=license');
            ?>
            <div class="wrap athswp-pro-settings">
                <div style="max-width: 600px; margin: 20px auto; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; text-align: center;">
                    <h3><?php esc_html_e('Unlock Premium Features!', 'add-to-home-screen-wp'); ?></h3>
                    <p style="font-size: 16px; color: #333; line-height: 1.5;">
                        <?php esc_html_e('Enhance your Add to Home Screen experience with these powerful premium features:', 'add-to-home-screen-wp'); ?>
                    </p>
                    <ul style="list-style: none; padding: 0; text-align: left; margin: 20px 0;">
                        <li style="margin-bottom: 10px;">
                            <strong>🚀 <?php esc_html_e('Dashboard PWA Support:', 'add-to-home-screen-wp'); ?></strong> <?php esc_html_e('Enable PWA functionality specifically for the WordPress admin dashboard.', 'add-to-home-screen-wp'); ?>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <strong>🎈 <?php esc_html_e('Advanced Dashboard Balloon Control:', 'add-to-home-screen-wp'); ?></strong> <?php esc_html_e('Fine-tune where the balloon appears in the admin dashboard.', 'add-to-home-screen-wp'); ?>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <strong>🎨 <?php esc_html_e('Custom Top Bar & Spinner Color:', 'add-to-home-screen-wp'); ?></strong> <?php esc_html_e('Personalize your PWA’s look.', 'add-to-home-screen-wp'); ?>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <strong>🖼️ <?php esc_html_e('Floating Balloon Icon:', 'add-to-home-screen-wp'); ?></strong> <?php esc_html_e('Upload your own icon for the balloon.', 'add-to-home-screen-wp'); ?>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <strong>📊 <?php esc_html_e('Installation Statistics:', 'add-to-home-screen-wp'); ?></strong> <?php esc_html_e('Track how many users add your PWA to their home screens.', 'add-to-home-screen-wp'); ?>
                        </li>
                    </ul>
                    <p>
                        <?php
                        printf(
                            wp_kses(
                                __('Ready to upgrade? <a href="%s" target="_blank" style="color: #0073aa; text-decoration: underline;">Get your premium license now</a> or <a href="%s" style="color: #0073aa; text-decoration: underline;">enter your license key</a> in the License tab.', 'add-to-home-screen-wp'),
                                ['a' => ['href' => [], 'target' => [], 'style' => []]]
                            ),
                            'https://tulipemedia.com/en/product/aths-wordpress-premium/',
                            esc_url($license_url)
                        );
                        ?>
                    </p>
                </div>
            </div>
            <?php
        }

        /**
         * Uninstall settings page.
         */
        public static function uninstall_page() {
            if (!current_user_can(is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__)) ? 'manage_network_options' : 'manage_options')) {
                wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'add-to-home-screen-wp'));
            }

            $options_message = '';
            $option_group = is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__)) ? 'simple_aths_network_options' : 'simple_aths_site_options';

            if (isset($_POST['simple_aths_save_uninstall_settings']) && check_admin_referer('simple_aths_uninstall_options_update', 'simple_aths_uninstall_nonce')) {
                simple_aths_update_setting('new_athswp_delete_data_on_uninstall', isset($_POST['new_athswp_delete_data_on_uninstall']) ? 'on' : 'off');
                $options_message = '<div class="updated"><p>' . esc_html__('Uninstall settings saved successfully!', 'add-to-home-screen-wp') . '</p></div>';
            }

            $delete_data_on_uninstall = simple_aths_get_setting('new_athswp_delete_data_on_uninstall', 'off');
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('Add to Home Screen & PWA - Uninstall Settings', 'add-to-home-screen-wp'); ?></h1>
                <?php echo $options_message; ?>
                <form method="post" action="">
                    <?php wp_nonce_field('simple_aths_uninstall_options_update', 'simple_aths_uninstall_nonce'); ?>
                    <?php settings_fields($option_group); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="new_athswp_delete_data_on_uninstall"><?php esc_html_e('Delete Data on Uninstall', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="checkbox" name="new_athswp_delete_data_on_uninstall" id="new_athswp_delete_data_on_uninstall" <?php checked($delete_data_on_uninstall === 'on'); ?> />
                                <p class="description"><?php esc_html_e('If checked, all settings and data (including statistics and license activation for the Pro version, if applicable) will be deleted when the plugin is uninstalled. The Pro license will also be deactivated on the license server to free up the activation. Uncheck this if you plan to reinstall the plugin and want to keep your settings and license activation.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="simple_aths_save_uninstall_settings" class="button-primary" value="<?php esc_attr_e('Save Uninstall Settings', 'add-to-home-screen-wp'); ?>" />
                    </p>
                </form>
                <script>
                    jQuery(document).ready(function($) {
                        $('#new_athswp_delete_data_on_uninstall').on('change', function() {
                            var isChecked = $(this).is(':checked');
                            var message = isChecked
                                ? '<?php echo esc_js(__('Are you sure you want to delete all data on uninstall? This includes settings and, if applicable, statistics from the Pro version.', 'add-to-home-screen-wp')); ?>'
                                : '<?php echo esc_js(__('Are you sure you want to keep data on uninstall? Settings and statistics will remain in the database.', 'add-to-home-screen-wp')); ?>';
                            if (!confirm(message)) {
                                $(this).prop('checked', !isChecked);
                            }
                        });
                    });
                </script>
            </div>
            <?php
        }
    }

endif;

/**
 * Get a plugin setting.
 */
function simple_aths_get_setting($key, $default = '') {
    $defaults = SimpleATHSOptions::get_default_settings();
    $default = isset($defaults[$key]) ? $defaults[$key] : $default;

    if (is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__))) {
        return get_network_option(null, $key, $default);
    }
    return get_option($key, $default);
}

/**
 * Update a plugin setting.
 */
function simple_aths_update_setting($key, $value) {
    $is_network_activated = is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__));
    return $is_network_activated ? update_site_option($key, $value) : update_option($key, $value);
}

/**
 * Register admin menus.
 */
if (is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__))) {
    add_action('network_admin_menu', ['SimpleATHSOptions', 'menu_network'], 10);
} else {
    add_action('admin_menu', ['SimpleATHSOptions', 'menu_site'], 20);
}

add_action('admin_init', ['SimpleATHSOptions', 'register']);

/**
 * Enqueue frontend scripts and styles.
 */
add_action('wp_enqueue_scripts', 'simple_aths_enqueue_frontend_scripts', 20);
function simple_aths_enqueue_frontend_scripts() {
    if (!wp_is_mobile()) {
        return;
    }

    $enable_pwa = simple_aths_get_setting('new_enable_pwa', 'on');

    if ($enable_pwa === 'on') {
        wp_enqueue_script('simple-aths-pwa', plugins_url('athswp-pwa.js', __FILE__), ['jquery'], '3.0.0', true);
        wp_add_inline_script('simple-aths-pwa', 'const ATHSWP_SW_URL = "' . plugins_url('athswp-sw.js', __FILE__) . '";');
        wp_enqueue_style('simple-aths-pwa', plugins_url('athswp-pwa.css', __FILE__), [], '3.0.0');
        $spinner_color = apply_filters('athswp_topbar_spinner_color', '#000000');
        wp_add_inline_script('simple-aths-pwa', 'const ATHSWP_SPINNER_COLOR = "' . esc_js($spinner_color) . '";');
        if (class_exists('ATHSWP_Pro')) {
            $enable_spinner = simple_aths_get_setting('new_athswp_pro_enable_spinner', 'on');
            wp_add_inline_script('simple-aths-pwa', 'const ATHSWP_ENABLE_SPINNER = "' . ($enable_spinner === 'on' ? 'true' : 'false') . '";');
        }
        wp_enqueue_script('simple-aths-pwa-fix', plugins_url('athswp-pwa-fix.js', __FILE__), [], '3.0.0', true);
    }
}

/**
 * Enqueue admin scripts.
 */
add_action('admin_enqueue_scripts', 'simple_aths_enqueue_admin_scripts', 10);
function simple_aths_enqueue_admin_scripts($hook) {
    if (strpos($hook, 'simple_aths_settings') !== false || 
        strpos($hook, 'athswp_pro_settings') !== false || 
        (is_network_admin() && isset($_GET['page']) && in_array($_GET['page'], ['simple_aths_settings', 'athswp_pro_settings']))) {
        wp_enqueue_media();
        wp_enqueue_script('simple-aths-admin', plugins_url('athswp-admin.js', __FILE__), ['jquery', 'wp-mediaelement'], '3.0.0', true);
    }
}

/**
 * Add balloon configuration for frontend.
 */
/**
 * Add balloon configuration for frontend.
 */
add_action('wp_head', 'simple_aths_add_balloon_config_frontend', 100);
function simple_aths_add_balloon_config_frontend() {
    if (is_admin() || strpos($_SERVER['REQUEST_URI'] ?? '', '/wp-admin/') !== false) {
        return;
    }

    if (!wp_is_mobile()) {
        return;
    }

    $enable_balloon_ios_frontend = simple_aths_get_setting('new_enable_balloon_ios_frontend', 'on');
    $install_prompt_android = simple_aths_get_setting('new_install_prompt_android', 'custom_floating_balloon');
    $balloon_display_frontend = simple_aths_get_setting('new_balloon_display_frontend', 'all_pages');

    // Skip if balloon is set to homepage only and not on homepage
    if ($balloon_display_frontend === 'homepage' && !is_front_page()) {
        return;
    }

    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $is_ios = stripos($user_agent, 'iPhone') !== false || stripos($user_agent, 'iPad') !== false;
    $is_android = stripos($user_agent, 'Android') !== false;
    $is_chrome = stripos($user_agent, 'Chrome') !== false || stripos($user_agent, 'CriOS') !== false;

    // Only show balloon if enabled for the device
    if (!($is_ios && $enable_balloon_ios_frontend === 'on') && !($is_android && $install_prompt_android === 'custom_floating_balloon')) {
        return;
    }

    // Generate or retrieve UUID
    $uuid = isset($_COOKIE['simple_aths_uuid']) ? sanitize_text_field($_COOKIE['simple_aths_uuid']) : wp_generate_uuid4();
    $cookie_path = is_multisite() ? parse_url(get_site_url(), PHP_URL_PATH) : '/';
    setcookie('simple_aths_uuid', $uuid, time() + (365 * DAY_IN_SECONDS), $cookie_path);

    // Check returning visitors
    $returning_visitors_only = simple_aths_get_setting('new_returning_visitors_only', 'off') === 'on';
    if ($returning_visitors_only) {
        $first_visit = get_transient('simple_aths_first_visit_' . $uuid);
        if ($first_visit === false) {
            set_transient('simple_aths_first_visit_' . $uuid, true, YEAR_IN_SECONDS);
            return; // Skip balloon for first visit
        }
    } else {
        if (get_transient('simple_aths_first_visit_' . $uuid) === false) {
            set_transient('simple_aths_first_visit_' . $uuid, true, YEAR_IN_SECONDS);
        }
    }

    // Check if balloon was closed in this session
    if (isset($_COOKIE['simple_aths_session_closed']) && $_COOKIE['simple_aths_session_closed'] === '1') {
        return;
    }

    // Check expiration time
    $expire_days = absint(simple_aths_get_setting('new_expire_days', 0));
    if ($expire_days > 0) {
        $balloon_closed = get_transient('simple_aths_balloon_closed_' . $uuid);
        if ($balloon_closed !== false) {
            return; // Skip balloon if within expiration period
        }
    }

    // Get message
    $defaults = SimpleATHSOptions::get_default_settings();
    $message = $is_ios ? simple_aths_get_setting('new_message_ios') : simple_aths_get_setting('new_message_android');
    if (empty($message)) {
        $message = $is_ios ? $defaults['new_message_ios'] : $defaults['new_message_android'];
    }

    // Replace placeholders
    $message = str_replace('%site_name', esc_html(get_bloginfo('name')), $message);
    $message = str_replace('%network_name', esc_html(is_multisite() ? get_network()->site_name : get_bloginfo('name')), $message);
    $message = str_replace('%device', $is_ios ? 'iPhone' : 'Android', $message);
    $message = preg_replace("(\r\n|\n|\r)", " ", $message);

    // Sanitize message
    $allowed_html = [
        'center' => [],
        'h4' => [],
        'h3' => [],
        'h2' => [],
        'h1' => [],
        'strong' => [],
        'br' => [],
        'p' => [],
        'b' => [],
        'i' => [],
        'span' => ['class' => []],
    ];
    $safe_message = wp_kses($message, $allowed_html);
    if (empty($safe_message)) {
        $safe_message = $is_ios
            ? 'Add ' . esc_html(get_bloginfo('name')) . ' to your iPhone now!'
            : 'Add ' . esc_html(get_bloginfo('name')) . ' to your Android now!';
    }

    // Get other settings
    $startdelay = absint(simple_aths_get_setting('new_startdelay', 2));
    $lifespan = absint(simple_aths_get_setting('new_lifespan', 20));
    $bottomoffset = absint(simple_aths_get_setting('new_bottomoffset', 14));
    $animationin = sanitize_text_field(simple_aths_get_setting('new_animationin', 'fade'));
    $animationout = sanitize_text_field(simple_aths_get_setting('new_animationout', 'fade'));
    $touchicon_url = esc_url_raw(simple_aths_get_setting('new_touchicon_url', ''));
    $precomposed_icon = simple_aths_get_setting('new_precomposed_icon', 'off') === 'on';
    $balloon_icon_url = apply_filters('athswp_balloon_icon_url', $touchicon_url);

    // Enqueue scripts and styles
    $cache_buster = '3.0.0-' . wp_rand(100000, 999999);
    wp_enqueue_style('simple-aths', plugins_url('add2home.css', __FILE__) . '?v=' . $cache_buster, [], '3.0.0');
    wp_enqueue_script('simple-aths', plugins_url('add2home.js', __FILE__) . '?v=' . $cache_buster, ['jquery'], '3.0.0', true);

    // Generate nonce for AJAX
    $nonce = wp_create_nonce('simple_aths_close_balloon');

    // Output JavaScript configuration
    ?>
    <script type="text/javascript">
        (function() {
            // Skip if in PWA mode
            if (window.navigator.standalone === true || window.matchMedia("(display-mode: standalone)").matches) {
                return;
            }

            window.addToHomeMessage = <?php echo wp_json_encode($safe_message); ?>;
            window.addToHomeConfig = {
                message: window.addToHomeMessage,
                animationIn: '<?php echo esc_js($animationin); ?>',
                animationOut: '<?php echo esc_js($animationout); ?>',
                startDelay: <?php echo absint($startdelay * 1000); ?>,
                lifespan: <?php echo absint($lifespan * 1000); ?>,
                expire: 0,
                bottomOffset: <?php echo absint($bottomoffset); ?>,
                returningVisitor: false,
                touchIcon: <?php echo $touchicon_url ? 'true' : 'false'; ?>,
                balloonIcon: '<?php echo esc_js($balloon_icon_url); ?>',
                precomposedIcon: <?php echo $precomposed_icon ? 'true' : 'false'; ?>,
                enableBalloonIOS: '<?php echo esc_js($enable_balloon_ios_frontend); ?>',
                installPromptAndroid: '<?php echo esc_js($install_prompt_android); ?>'
            };

            document.addEventListener('DOMContentLoaded', function() {
                if (typeof addToHome !== 'undefined') {
                    // Debug to check why balloon might not show
                    console.log('ATHSWP: Attempting to show balloon - isChrome: <?php echo $is_chrome ? 'true' : 'false'; ?>, isIOS: <?php echo $is_ios ? 'true' : 'false'; ?>, isAndroid: <?php echo $is_android ? 'true' : 'false'; ?>, enableBalloonIOS: <?php echo json_encode($enable_balloon_ios_frontend); ?>');
                    addToHome.show();
                    // Apply Chrome-specific customizations
                    var isChrome = <?php echo $is_chrome ? 'true' : 'false'; ?>;
                    var isAndroid = <?php echo $is_android ? 'true' : 'false'; ?>;
                    var isIOS = <?php echo $is_ios ? 'true' : 'false'; ?>;
                    if (isChrome && (isAndroid || isIOS)) {
                        var balloon = document.querySelector('#addToHomeScreen');
                        if (balloon) {
                            console.log('ATHSWP: Applying Chrome customizations - adding addToHomeChrome class');
                            balloon.classList.add('addToHomeChrome');
                            // Adjust top position based on admin bar presence
                            var adminBar = document.querySelector('#wpadminbar');
                            var topOffset = adminBar ? (adminBar.offsetHeight + <?php echo absint($bottomoffset); ?>) : <?php echo absint($bottomoffset); ?>;
                            balloon.style.bottom = 'auto';
                            balloon.style.top = topOffset + 'px';
                            console.log('ATHSWP: Admin bar ' + (adminBar ? 'present' : 'absent') + ', setting top to ' + topOffset + 'px');
                            <?php if ($is_android && $install_prompt_android === 'custom_floating_balloon') : ?>
                            // Make balloon clickable on Android Chrome
                            console.log('ATHSWP: Setting up clickable balloon for Android Chrome');
                            let deferredPrompt;
                            window.addEventListener('beforeinstallprompt', function(e) {
                                console.log('ATHSWP: beforeinstallprompt event fired');
                                deferredPrompt = e;
                            });
                            balloon.style.cursor = 'pointer';
                            balloon.addEventListener('click', function(e) {
                                // Avoid triggering click on close button
                                if (e.target.classList.contains('addToHomeClose')) {
                                    console.log('ATHSWP: Click on close button, skipping prompt');
                                    return;
                                }
                                console.log('ATHSWP: Balloon clicked, attempting to prompt');
                                if (deferredPrompt) {
                                    deferredPrompt.prompt();
                                    deferredPrompt.userChoice.then((choiceResult) => {
                                        console.log('ATHSWP: User choice:', choiceResult.outcome);
                                        deferredPrompt = null;
                                    });
                                } else {
                                    console.log('ATHSWP: No deferredPrompt available');
                                }
                            });
                            <?php endif; ?>
                        } else {
                            console.log('ATHSWP: Balloon element not found');
                        }
                    }
                    // Handle close button click
                    var closeButton = document.querySelector('#addToHomeScreen .addToHomeClose');
                    if (closeButton) {
                        closeButton.addEventListener('click', function() {
                            console.log('ATHSWP: Close button clicked');
                            jQuery.ajax({
                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                type: 'POST',
                                data: {
                                    action: 'simple_aths_close_balloon',
                                    nonce: '<?php echo esc_js($nonce); ?>',
                                    uuid: '<?php echo esc_js($uuid); ?>'
                                },
                                success: function() {
                                    console.log('ATHSWP: Balloon closure recorded');
                                }
                            });
                        });
                    } else {
                        console.log('ATHSWP: Close button not found');
                    }
                } else {
                    console.log('ATHSWP: addToHome not defined');
                }
            });
        })();
    </script>
    <?php
}

/**
 * AJAX handler for balloon closure.
 */
add_action('wp_ajax_simple_aths_close_balloon', 'simple_aths_close_balloon');
add_action('wp_ajax_nopriv_simple_aths_close_balloon', 'simple_aths_close_balloon');
function simple_aths_close_balloon() {
    check_ajax_referer('simple_aths_close_balloon', 'nonce');

    $uuid = isset($_POST['uuid']) ? sanitize_text_field($_POST['uuid']) : '';
    if (empty($uuid)) {
        wp_send_json_error(['message' => 'Invalid UUID']);
    }

    // Set session cookie
    $cookie_path = is_multisite() ? parse_url(get_site_url(), PHP_URL_PATH) : '/';
    setcookie('simple_aths_session_closed', '1', 0, $cookie_path); // Session cookie (expires when browser closes)

    // Set expiration transient
    $expire_days = absint(simple_aths_get_setting('new_expire_days', 0));
    if ($expire_days > 0) {
        set_transient('simple_aths_balloon_closed_' . $uuid, true, $expire_days * DAY_IN_SECONDS);
    }

    wp_send_json_success();
}

/**
 * Add PWA manifest for frontend.
 */
add_action('wp_head', 'simple_aths_add_manifest_frontend', 100);
function simple_aths_add_manifest_frontend() {
    if (!wp_is_mobile()) {
        return;
    }

    $enable_pwa = simple_aths_get_setting('new_enable_pwa', 'on');
    if ($enable_pwa !== 'on') {
        return;
    }

    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    if (is_admin() || $request_uri === '/pwa-add/') {
        add_action('admin_notices', 'simple_aths_pwa_admin_notice');
        return;
    }

    $web_app_title = simple_aths_get_setting('new_web_app_title', '');
    $screen_title = !empty($web_app_title) ? $web_app_title : get_bloginfo('name');
    $touchicon_url = simple_aths_get_setting('new_touchicon_url', '');
    if (empty($touchicon_url)) {
        $touchicon_url = plugins_url('assets/icons/tulipwork-default-icon.png', __FILE__);
    }
    $topbar_spinner_color = apply_filters('athswp_topbar_spinner_color', '#000000');
    $frontend_pwa_start_url = simple_aths_get_setting('new_athswp_frontend_pwa_start_url', 'homepage');
    $pwa_custom_url = simple_aths_get_setting('new_athswp_pwa_custom_url', '');

    $is_ios = stripos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false;
    $is_android = stripos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false;
    if ($is_ios || $is_android) {
        ?>
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="<?php echo esc_attr($screen_title); ?>">
        <?php
    }

    $start_url = ($frontend_pwa_start_url === 'homepage_with_path' && !empty($pwa_custom_url))
        ? (preg_match('#^https?://#', $pwa_custom_url) ? $pwa_custom_url : home_url($pwa_custom_url))
        : home_url('/');

    $redirect_url = $start_url;

    $manifest = [
        'name' => $screen_title,
        'short_name' => $screen_title,
        'start_url' => $start_url,
        'scope' => home_url('/'),
        'display' => 'standalone',
        'background_color' => apply_filters('athswp_topbar_spinner_color', '#ffffff'),
        'theme_color' => $topbar_spinner_color,
        'context' => 'frontend',
        'icons' => [
            [
                'src' => $touchicon_url,
                'sizes' => '192x192',
                'type' => 'image/png'
            ]
        ]
    ];

    if ($frontend_pwa_start_url === 'homepage_with_path' && !empty($pwa_custom_url)) {
        ?>
        <script>
        jQuery(document).ready(function($) {
            if (window.navigator.standalone === true || window.matchMedia("(display-mode: standalone)").matches) {
                if (!localStorage.getItem('simple_aths_pwa_redirected_frontend')) {
                    localStorage.setItem('simple_aths_pwa_redirected_frontend', 'true');
                    window.location.href = '<?php echo esc_url($redirect_url); ?>';
                }
            }
        });
        </script>
        <?php
    }

    ?>
    <link rel="manifest" href="data:application/manifest+json,<?php echo rawurlencode(json_encode($manifest)); ?>">
    <?php
}

/**
 * Add Android native install button.
 */
add_action('wp_footer', 'simple_aths_add_android_install_button', 25);
function simple_aths_add_android_install_button() {
    if (!wp_is_mobile() || stripos($_SERVER['HTTP_USER_AGENT'], 'Android') === false) {
        return;
    }

    if (is_admin() || strpos($_SERVER['REQUEST_URI'] ?? '', '/wp-admin/') !== false) {
        return;
    }

    $install_prompt_android = simple_aths_get_setting('new_install_prompt_android', 'custom_floating_balloon');
    $enable_pwa = simple_aths_get_setting('new_enable_pwa', 'on');
    if ($install_prompt_android !== 'native_button' || $enable_pwa !== 'on') {
        return;
    }

    $topbar_spinner_color = apply_filters('athswp_topbar_spinner_color', '#000000');
    ?>
    <button id="pwa-install-button" style="display: none; position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; background-color: <?php echo esc_attr($topbar_spinner_color); ?>; color: white; border: none; border-radius: 5px; z-index: 1000; cursor: pointer;">
        <?php esc_html_e('Add to Home Screen', 'add-to-home-screen-wp'); ?>
    </button>
    <script>
        (function() {
            let deferredPrompt;
            const installButton = document.getElementById("pwa-install-button");

            window.addEventListener("beforeinstallprompt", function(e) {
                deferredPrompt = e;
                setTimeout(() => {
                    installButton.style.display = "block";
                }, 500);
            });

            installButton.addEventListener("click", function() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        deferredPrompt = null;
                        installButton.style.display = "none";
                    });
                }
            });

            if (window.matchMedia("(display-mode: standalone)").matches || ("standalone" in window.navigator && window.navigator.standalone)) {
                installButton.style.display = "none";
            }
        })();
    </script>
    <?php
}

/**
 * PWA admin notice for admin pages.
 */
function simple_aths_pwa_admin_notice() {
    ?>
    <div class="notice notice-info">
        <p><?php esc_html_e('PWA settings are applied for mobile devices only.', 'add-to-home-screen-wp'); ?></p>
    </div>
    <?php
}