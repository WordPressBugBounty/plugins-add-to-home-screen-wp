<?php
/*
Plugin Name: Add to Home Screen WP
Plugin URI: https://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/
Description: Invite your visitors to add your WordPress blog to their iOS home screen (iPhone, iPod Touch, iPad) with a floating balloon.
Version: 2.6.6
Author: Ziyad Bachalany
Author URI: https://tulipemedia.com
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: add-to-home-screen-wp
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the license management file
require_once plugin_dir_path(__FILE__) . 'athswp-license.php';

// Load plugin text domain for translation
function athswp_load_textdomain() {
    load_plugin_textdomain('add-to-home-screen-wp', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'athswp_load_textdomain');

register_deactivation_hook(__FILE__, 'athswp_deactivation');
function athswp_deactivation() {
    wp_clear_scheduled_hook('athswp_check_license_event');
}

if (!class_exists('adhsOptions')) :

define('adhsOptions_ID', 'add_to_home_screen');
define('adhsOptions_NICK', 'ATHS Options');

class adhsOptions
{
    public static function file_path($file) {
        return plugin_dir_path(__FILE__) . $file;
    }

    public static function sanitize_message($input) {
        $allowed_html = array(
            'center' => array(),
            'h4'     => array(),
            'strong' => array(),
            'br'     => array(),
            'p'      => array(),
            'b'      => array(),
            'i'      => array(),
        );
        return wp_kses($input, $allowed_html);
    }

    public static function register() {
        register_setting(adhsOptions_ID.'_options', 'returningvisitor', array('sanitize_callback' => 'sanitize_key'));
        register_setting(adhsOptions_ID.'_options', 'message', array('sanitize_callback' => array('adhsOptions', 'sanitize_message')));
        register_setting(adhsOptions_ID.'_options', 'animationin', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting(adhsOptions_ID.'_options', 'animationout', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting(adhsOptions_ID.'_options', 'startdelay', array('sanitize_callback' => 'absint'));
        register_setting(adhsOptions_ID.'_options', 'lifespan', array('sanitize_callback' => 'absint'));
        register_setting(adhsOptions_ID.'_options', 'bottomoffset', array('sanitize_callback' => 'absint'));
        register_setting(adhsOptions_ID.'_options', 'expire', array('sanitize_callback' => 'absint'));
        register_setting(adhsOptions_ID.'_options', 'touchicon', array('sanitize_callback' => 'sanitize_key'));
        register_setting(adhsOptions_ID.'_options', 'touchicon_url', array('sanitize_callback' => 'esc_url_raw'));
        register_setting(adhsOptions_ID.'_options', 'addmetawebcapabletitle', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting(adhsOptions_ID.'_options', 'pagetarget', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting(adhsOptions_ID.'_options', 'aths_touchicon_precomposed', array('sanitize_callback' => 'sanitize_key'));
        register_setting(adhsOptions_ID.'_options', 'athswp_license_key', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting(adhsOptions_ID.'_options', 'athswp_premium_status', array('sanitize_callback' => 'sanitize_key'));
        register_setting(adhsOptions_ID.'_options', 'athswp_license_status', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting(adhsOptions_ID.'_options', 'athswp_license_expires', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting(adhsOptions_ID.'_options', 'athswp_license_message', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting(adhsOptions_ID.'_options', 'pwa_theme_color', array('sanitize_callback' => 'sanitize_hex_color'));
        register_setting(adhsOptions_ID.'_options', 'pwa_force_homepage', array('sanitize_callback' => 'sanitize_key'));
        register_setting(adhsOptions_ID.'_options', 'pwa_enable_features', array('sanitize_callback' => 'sanitize_key'));
        register_setting(adhsOptions_ID.'_options', 'pwa_show_loading', array('sanitize_callback' => 'sanitize_key'));
        register_setting(adhsOptions_ID.'_options', 'pwa_show_install_button', array('sanitize_callback' => 'sanitize_key'));
    }

    public static function menu() {
        add_options_page(adhsOptions_NICK.' Plugin Options', adhsOptions_NICK, 'manage_options', adhsOptions_ID.'_options', array('adhsOptions', 'options_page'));
    }

    public static function options_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'add-to-home-screen-wp'));
        }
        
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';
        $license_message = '';
        $options_message = '';
    
        // Sauvegarde des options gratuites
        if (isset($_POST['athswp_save_settings']) && check_admin_referer('athswp_options_update', 'athswp_nonce')) {
            update_option('returningvisitor', isset($_POST['returningvisitor']) ? 'on' : 'off');
            update_option('message', self::sanitize_message($_POST['message'] ?? ''));
            update_option('animationin', sanitize_text_field($_POST['animationin'] ?? 'fade'));
            update_option('animationout', sanitize_text_field($_POST['animationout'] ?? 'fade'));
            update_option('startdelay', absint($_POST['startdelay'] ?? 2000));
            update_option('lifespan', absint($_POST['lifespan'] ?? 20000));
            update_option('bottomoffset', absint($_POST['bottomoffset'] ?? 14));
            update_option('expire', absint($_POST['expire'] ?? 0));
            update_option('touchicon', isset($_POST['touchicon']) ? 'on' : 'off');
            update_option('touchicon_url', esc_url_raw($_POST['touchicon_url'] ?? ''));
            update_option('addmetawebcapabletitle', sanitize_text_field($_POST['addmetawebcapabletitle'] ?? ''));
            update_option('pagetarget', sanitize_text_field($_POST['pagetarget'] ?? 'allpages'));
            update_option('aths_touchicon_precomposed', isset($_POST['aths_touchicon_precomposed']) ? 'on' : 'off');
            wp_cache_delete('alloptions', 'options');
            $options_message = '<div class="updated"><p>' . __('Settings saved successfully!', 'add-to-home-screen-wp') . '</p></div>';
        }
    
        // Sauvegarde des options premium
        if (isset($_POST['athswp_save_premium']) && check_admin_referer('athswp_options_update', 'athswp_nonce') && athswp_is_premium()) {
            update_option('pwa_theme_color', sanitize_hex_color($_POST['pwa_theme_color'] ?? '#000000'));
            update_option('pwa_enable_features', isset($_POST['pwa_enable_features']) ? 'on' : 'off');
            update_option('pwa_force_homepage', isset($_POST['pwa_force_homepage']) ? 'on' : 'off');
            update_option('pwa_show_loading', isset($_POST['pwa_show_loading']) ? 'on' : 'off');
            update_option('pwa_show_install_button', isset($_POST['pwa_show_install_button']) ? 'on' : 'off');
            wp_cache_delete('alloptions', 'options');
            $options_message = '<div class="updated"><p>' . __('Settings saved successfully!', 'add-to-home-screen-wp') . '</p></div>';
        }
    
        // Sauvegarde de la licence (inchangée)
        if (isset($_POST['athswp_save_license']) && check_admin_referer('athswp_options_update', 'athswp_nonce')) {
            $old_key = get_option('athswp_license_key', '');
            $new_key = sanitize_text_field($_POST['athswp_license_key'] ?? '');
            if (get_option('athswp_license_status', 'inactive') === 'active' && ($new_key === $old_key || empty(trim($new_key)))) {
                $license_message = '<div class="updated"><p>' . __('License unchanged. No action taken.', 'add-to-home-screen-wp') . '</p></div>';
            } else {
                if ($old_key && $old_key !== $new_key && !empty($old_key) && !empty(trim($new_key))) {
                    athswp_deactivate_license($old_key);
                }
                if (!empty(trim($new_key))) {
                    update_option('athswp_license_key', $new_key, 'yes');
                    athswp_validate_license($new_key);
                    wp_cache_delete('athswp_license_key', 'options');
                    wp_cache_delete('athswp_license_status', 'options');
                    wp_cache_delete('athswp_license_message', 'options');
                    wp_cache_delete('athswp_premium_status', 'options');
                    wp_cache_delete('athswp_last_checked', 'options');
                } elseif (empty($new_key) && $old_key) {
                    athswp_deactivate_license($old_key);
                    update_option('athswp_license_key', '', 'yes');
                    update_option('athswp_license_status', 'inactive', 'yes');
                    update_option('athswp_premium_status', 'no', 'yes');
                    wp_cache_delete('athswp_license_key', 'options');
                    wp_cache_delete('athswp_license_status', 'options');
                    wp_cache_delete('athswp_license_message', 'options');
                    wp_cache_delete('athswp_premium_status', 'options');
                    wp_cache_delete('athswp_last_checked', 'options');
                }
                $license_message = '<div class="updated"><p>' . __('License settings saved!', 'add-to-home-screen-wp') . '</p></div>';
            }
        }
    
        // Gestion de la désactivation de la licence (inchangée)
        if (isset($_POST['athswp_deactivate_license']) && check_admin_referer('athswp_options_update', 'athswp_nonce')) {
            $license_key = sanitize_text_field($_POST['athswp_license_key'] ?? '');
            if (!empty($license_key) && athswp_deactivate_license($license_key)) {
                $license_message = '<div class="updated"><p>' . __('License deactivated successfully!', 'add-to-home-screen-wp') . '</p></div>';
            } else {
                $license_message = '<div class="error"><p>' . esc_html(get_option('athswp_license_message', __('Failed to deactivate license. Please try again.', 'add-to-home-screen-wp'))) . '</p></div>';
            }
        }
    
        // Récupération des valeurs actuelles (inchangée)
        $is_premium = athswp_is_premium();
        $license_key = get_option('athswp_license_key', '');
        $license_status = get_option('athswp_license_status', 'inactive');
        $license_status_message = get_option('athswp_license_message', 'No license entered yet.');
        $returningvisitor = get_option('returningvisitor', 'off');
        $message = get_option('message', '');
        $animationin = get_option('animationin', 'fade');
        $animationout = get_option('animationout', 'fade');
        $startdelay = get_option('startdelay', 2000);
        $lifespan = get_option('lifespan', 20000);
        $bottomoffset = get_option('bottomoffset', 14);
        $expire = get_option('expire', 0);
        $touchicon = get_option('touchicon', 'off');
        $touchicon_url = get_option('touchicon_url', '');
        $addmetawebcapabletitle = get_option('addmetawebcapabletitle', '');
        $pagetarget = get_option('pagetarget', 'allpages');
        $aths_touchicon_precomposed = get_option('aths_touchicon_precomposed', 'off');
        $pwa_theme_color = get_option('pwa_theme_color', '#000000');
        $pwa_enable_features = get_option('pwa_enable_features', 'on');
        $pwa_force_homepage = get_option('pwa_force_homepage', 'off');
        $pwa_show_loading = get_option('pwa_show_loading', 'off');
        $pwa_show_install_button = get_option('pwa_show_install_button', 'off');
    
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Add to Home Screen WP Settings', 'add-to-home-screen-wp'); ?></h1>
    
            <h2 class="nav-tab-wrapper">
                <a href="?page=add_to_home_screen_options&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Settings', 'add-to-home-screen-wp'); ?></a>
                <a href="?page=add_to_home_screen_options&tab=license" class="nav-tab <?php echo $active_tab === 'license' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('License', 'add-to-home-screen-wp'); ?></a>
                <a href="?page=add_to_home_screen_options&tab=premium" class="nav-tab <?php echo $active_tab === 'premium' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Premium Options', 'add-to-home-screen-wp'); ?></a>
            </h2>
    
            <form method="post" action="">
                <?php wp_nonce_field('athswp_options_update', 'athswp_nonce'); ?>
                <?php settings_fields(adhsOptions_ID.'_options'); ?>
    
                <?php if ($active_tab === 'settings') : ?>
                    <?php echo $options_message; ?>
                    <h3><?php esc_html_e('Free Options', 'add-to-home-screen-wp'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="returningvisitor"><?php esc_html_e('Show to returning visitors only', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="checkbox" name="returningvisitor" id="returningvisitor" <?php checked($returningvisitor === 'on'); ?> />
                                <p class="description">
                                    <?php esc_html_e('Set this to true and the message won\'t be shown the first time one user visits your blog. It can be useful to target only returning visitors and not irritate first time visitors.', 'add-to-home-screen-wp'); ?><br>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="message"><?php esc_html_e('Custom message', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <textarea rows="3" cols="50" name="message" id="message"><?php echo esc_textarea($message); ?></textarea>
                                <p class="description">
                                    <?php esc_html_e('Type the custom message that you want appearing in the balloon. You can also display default message in the language of your choice by typing the locale (e.g: en_us).', 'add-to-home-screen-wp'); ?><br>
                                    <?php
                                    $allowed_html = array(
                                        'i' => array(),
                                        'code' => array(),
                                    );
                                    echo wp_kses(
                                        __('Use <i>%device</i> to show user\'s device on message, <i>%icon</i> to display the first add icon, and <i>%add</i> to display the second add to home screen icon. You can also use HTML tags like <code>&lt;center&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;strong&gt;</code>, or <code>&lt;i&gt;</code> for formatting.', 'add-to-home-screen-wp'),
                                        $allowed_html
                                    );
                                    ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="animationin"><?php esc_html_e('Animation in', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <select name="animationin" id="animationin">
                                    <option value="drop" <?php selected($animationin, 'drop'); ?>><?php esc_html_e('drop', 'add-to-home-screen-wp'); ?></option>
                                    <option value="bubble" <?php selected($animationin, 'bubble'); ?>><?php esc_html_e('bubble', 'add-to-home-screen-wp'); ?></option>
                                    <option value="fade" <?php selected($animationin, 'fade'); ?>><?php esc_html_e('fade', 'add-to-home-screen-wp'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('The animation the balloon appears with.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="animationout"><?php esc_html_e('Animation out', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <select name="animationout" id="animationout">
                                    <option value="drop" <?php selected($animationout, 'drop'); ?>><?php esc_html_e('drop', 'add-to-home-screen-wp'); ?></option>
                                    <option value="bubble" <?php selected($animationout, 'bubble'); ?>><?php esc_html_e('bubble', 'add-to-home-screen-wp'); ?></option>
                                    <option value="fade" <?php selected($animationout, 'fade'); ?>><?php esc_html_e('fade', 'add-to-home-screen-wp'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('The animation the balloon exits with.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="startdelay"><?php esc_html_e('Start delay', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="text" name="startdelay" id="startdelay" value="<?php echo esc_attr($startdelay); ?>" />
                                <p class="description"><?php esc_html_e('Milliseconds to wait before showing the message. Default: 2000', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="lifespan"><?php esc_html_e('Lifespan', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="text" name="lifespan" id="lifespan" value="<?php echo esc_attr($lifespan); ?>" />
                                <p class="description"><?php esc_html_e('Milliseconds to wait before hiding the message. Default: 20000', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bottomoffset"><?php esc_html_e('Bottom offset', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="text" name="bottomoffset" id="bottomoffset" value="<?php echo esc_attr($bottomoffset); ?>" />
                                <p class="description"><?php esc_html_e('Distance in pixels from the bottom (iPhone) or the top (iPad). Default: 14', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="expire"><?php esc_html_e('Expire timeframe', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="text" name="expire" id="expire" value="<?php echo esc_attr($expire); ?>" />
                                <p class="description">
                                    <?php esc_html_e('Minutes before displaying the message again. Default: 0 (=always show). It\'s highly recommended to set a timeframe in order to prevent showing message at each and every page load for those who didn\'t add the Web App to their homescreen or those who added it but load the blog on Safari!', 'add-to-home-screen-wp'); ?><br>
                                    <i><?php esc_html_e('Recommended values: 43200 for one month or 525600 for one year.', 'add-to-home-screen-wp'); ?></i>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="touchicon"><?php esc_html_e('Enable touch icon', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="checkbox" name="touchicon" id="touchicon" <?php checked($touchicon === 'on'); ?> />
                                <p class="description"><?php esc_html_e('If checked, displays the application icon next to the message using the URL provided below.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="aths_touchicon_precomposed"><?php esc_html_e('Precomposed icon', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="checkbox" name="aths_touchicon_precomposed" id="aths_touchicon_precomposed" <?php checked($aths_touchicon_precomposed === 'on'); ?> />
                                <p class="description"><?php esc_html_e('If checked, the icon will display without the Apple gloss effect.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="touchicon_url"><?php esc_html_e('Touch icon URL', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="url" name="touchicon_url" id="touchicon_url" value="<?php echo esc_url($touchicon_url); ?>" />
                                <button type="button" class="button upload-icon-button" data-input="touchicon_url"><?php esc_html_e('Upload Icon', 'add-to-home-screen-wp'); ?></button>
                                <p class="description">
                                    <?php esc_html_e('Paste the URL or upload an icon (ideally 192x192 or 512x512 pixels, PNG format) for iOS and Android home screens. This will be used as link rel="apple-touch-icon" and in the PWA manifest.', 'add-to-home-screen-wp'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="addmetawebcapabletitle"><?php esc_html_e('Title of your Web App', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <input type="text" name="addmetawebcapabletitle" id="addmetawebcapabletitle" value="<?php echo esc_attr($addmetawebcapabletitle); ?>" />
                                <p class="description"><?php esc_html_e('Type the name of your blog (max: 12 characters!). Default: it takes the default title of the page.', 'add-to-home-screen-wp'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pagetarget"><?php esc_html_e('On which page the balloon should appear?', 'add-to-home-screen-wp'); ?></label></th>
                            <td>
                                <select name="pagetarget" id="pagetarget">
                                    <option value="homeonly" <?php selected($pagetarget, 'homeonly'); ?>><?php esc_html_e('Home only', 'add-to-home-screen-wp'); ?></option>
                                    <option value="allpages" <?php selected($pagetarget, 'allpages'); ?>><?php esc_html_e('All pages', 'add-to-home-screen-wp'); ?></option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('Keep in mind that if someone adds your blog to home screen from a single article page for instance, the web app will load this page and not the homepage of the blog. That\'s why you could choose to open the floating balloon on homepage only and not on all pages of your blog. In Premium mode, you can override this by forcing the homepage to launch.', 'add-to-home-screen-wp'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="athswp_save_settings" class="button-primary" value="<?php esc_html_e('Save Settings', 'add-to-home-screen-wp'); ?>" />
                    </p>
    
                <?php elseif ($active_tab === 'license') : ?>
                    <?php echo $license_message; ?>
                    <div class="athswp-license-section" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
                        <h3><?php esc_html_e('License Key', 'add-to-home-screen-wp'); ?></h3>
                        <p><?php esc_html_e('Enter your license key to unlock Premium features.', 'add-to-home-screen-wp'); ?></p>
                        <div class="athswp-field">
                            <label for="athswp_license_key"><?php esc_html_e('License Key:', 'add-to-home-screen-wp'); ?></label>
                            <input type="text" name="athswp_license_key" id="athswp_license_key" value="<?php echo esc_attr($license_key); ?>" class="regular-text" placeholder="ex: ATHSUS2W-HFII-5495" />
                            <p class="description">
                                <?php esc_html_e('Status:', 'add-to-home-screen-wp'); ?> <strong><?php echo esc_html($license_status); ?></strong><br>
                                <?php echo esc_html($license_status_message); ?>
                                <?php if (!$is_premium) : ?>
                                    <br><?php
                                    $allowed_html = array(
                                        'a' => array(
                                            'href' => array(),
                                            'target' => array(),
                                        ),
                                    );
                                    printf(
                                        wp_kses(__('Enter your premium license key and save settings to unlock all features. <a href="%s" target="_blank">Get your license now</a>.', 'add-to-home-screen-wp'), $allowed_html),
                                        'https://tulipemedia.com/en/product/aths-wordpress-premium/'
                                    ); ?>
                                <?php endif; ?>
                            </p>
                            <?php if ($is_premium) : ?>
                                <p>
                                    <input type="submit" name="athswp_deactivate_license" class="button" style="background-color: #d9534f; border-color: #d9534f; color: white;" value="<?php esc_html_e('Deactivate License', 'add-to-home-screen-wp'); ?>" onclick="return confirm('<?php esc_html_e('Are you sure you want to deactivate your license? This will disable premium features on this site.', 'add-to-home-screen-wp'); ?>');" />
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="submit">
                        <input type="submit" name="athswp_save_license" class="button-primary" value="<?php esc_html_e('Save License', 'add-to-home-screen-wp'); ?>" />
                    </p>
    
                <?php elseif ($active_tab === 'premium') : ?>
                    <?php echo $options_message; ?>
                    <h3><?php esc_html_e('Premium Options', 'add-to-home-screen-wp'); ?></h3>
                    <?php if ($is_premium) : ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="pwa_enable_features"><?php esc_html_e('Enable ATHS Premium Features', 'add-to-home-screen-wp'); ?></label></th>
                                <td>
                                    <input type="checkbox" name="pwa_enable_features" id="pwa_enable_features" <?php checked($pwa_enable_features === 'on'); ?> />
                                    <p class="description">
                                        <?php esc_html_e('Check to enable premium features (manifest, etc.). Uncheck to disable them without losing your settings. This turns your blog into a Web App, making it faster, giving it a native app feel on mobile devices, and allowing customization of the options below.', 'add-to-home-screen-wp'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pwa_theme_color"><?php esc_html_e('Top Bar and Spinner Color', 'add-to-home-screen-wp'); ?></label></th>
                                <td>
                                    <input type="color" name="pwa_theme_color" id="pwa_theme_color" value="<?php echo esc_attr($pwa_theme_color); ?>" />
                                    <button type="button" id="reset_top_color" class="button"><?php esc_html_e('Reset to Default', 'add-to-home-screen-wp'); ?></button>
                                    <p class="description"><?php esc_html_e('The color of the top bar in your Web App. Default: #000000 (black).', 'add-to-home-screen-wp'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pwa_force_homepage"><?php esc_html_e('Force Homepage on Launch', 'add-to-home-screen-wp'); ?></label></th>
                                <td>
                                    <input type="checkbox" name="pwa_force_homepage" id="pwa_force_homepage" <?php checked($pwa_force_homepage === 'on'); ?> />
                                    <p class="description"><?php esc_html_e('If checked, the Web App will always launch on the homepage, even if added from another page.', 'add-to-home-screen-wp'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pwa_show_loading"><?php esc_html_e('Show Loading Indicator', 'add-to-home-screen-wp'); ?></label></th>
                                <td>
                                    <input type="checkbox" name="pwa_show_loading" id="pwa_show_loading" <?php checked($pwa_show_loading === 'on'); ?> />
                                    <p class="description"><?php esc_html_e('If checked, a loading spinner will appear when navigating between pages in the Web App.', 'add-to-home-screen-wp'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pwa_show_install_button"><?php esc_html_e('Show Install Button for Android', 'add-to-home-screen-wp'); ?></label></th>
                                <td>
                                    <input type="checkbox" name="pwa_show_install_button" id="pwa_show_install_button" <?php checked($pwa_show_install_button === 'on'); ?> />
                                    <p class="description"><?php esc_html_e('If checked, a button will appear on Android devices to prompt users to add the Web App to their home screen.', 'add-to-home-screen-wp'); ?></p>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="athswp_save_premium" class="button-primary" value="<?php esc_html_e('Save Settings', 'add-to-home-screen-wp'); ?>" />
                        </p>
                    <?php else : ?>
                        <div style="max-width: 600px; margin: 20px auto; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; text-align: center;">
                            <h3><?php esc_html_e('Unlock Premium Features!', 'add-to-home-screen-wp'); ?></h3>
                            <p style="font-size: 16px; color: #333; line-height: 1.5;">
                                <?php esc_html_e('Take your WordPress blog to the next level with these powerful premium features:', 'add-to-home-screen-wp'); ?>
                            </p>
                            <ul style="list-style: none; padding: 0; text-align: left; margin: 20px 0;">
                                <li style="margin-bottom: 10px;">
                                    <strong>💻 Full PWA Support:</strong> <?php esc_html_e('Transform your blog into a fast and fluid Progressive Web App, launching like a native mobile app from the home screen.', 'add-to-home-screen-wp'); ?>
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <strong>📲 Install Button for Android & iOS:</strong> <?php esc_html_e('Add a simple “Add to Home Screen” button for both Android and iOS users with one click.', 'add-to-home-screen-wp'); ?>
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <strong>⏳ Stylish Loading Indicator:</strong> <?php esc_html_e('Enhance navigation with a professional spinner during page transitions.', 'add-to-home-screen-wp'); ?>
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <strong>🚀 Force Homepage on Launch:</strong> <?php esc_html_e('Ensure a consistent experience by always starting users on your homepage, no matter where they added your blog from.', 'add-to-home-screen-wp'); ?>
                                </li>
                            </ul>
                            <p>
                                <?php
                                $allowed_html = array(
                                    'a' => array(
                                        'href' => array(),
                                        'target' => array(),
                                        'style' => array(),
                                    ),
                                );
                                printf(
                                    wp_kses(
                                        __('Ready to upgrade? <a href="%s" target="_blank" style="color: #0073aa; text-decoration: underline;">Get your premium license now</a> or <a href="%s" style="color: #0073aa; text-decoration: underline;">enter your license key</a> in the License tab.', 'add-to-home-screen-wp'),
                                        $allowed_html
                                    ),
                                    esc_url('https://tulipemedia.com/en/product/aths-wordpress-premium/'),
                                    esc_url(admin_url('options-general.php?page=add_to_home_screen_options&tab=license'))
                                );
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
    
                <div style="max-width: 800px; margin: 20px auto; padding: 20px; background: #F2FBFD; border: 1px solid #B7E9E9; text-align: center; border-radius: 4px;">
                    <h3><?php esc_html_e('Keep in touch with me.', 'add-to-home-screen-wp'); ?></h3>
                    <div style="display: flex; justify-content: center; gap: 20px; align-items: center; flex-wrap: wrap; margin: 15px 0;">
                        <a href="https://twitter.com/ziyadbachalany" target="_blank">
                            <img src="<?php echo esc_url(plugins_url('assets/icons/twitter.png', __FILE__)); ?>" alt="Twitter" width="40">
                        </a>
                        <a href="https://www.facebook.com/ziyadbachalany" target="_blank">
                            <img src="<?php echo esc_url(plugins_url('assets/icons/facebook.png', __FILE__)); ?>" alt="Facebook" width="40">
                        </a>
                        <a href="https://www.linkedin.com/in/ziyadbachalany" target="_blank">
                            <img src="<?php echo esc_url(plugins_url('assets/icons/linkedin.png', __FILE__)); ?>" alt="LinkedIn" width="40">
                        </a>
                        <a href="https://instagram.com/ziyadbachalany" target="_blank">
                            <img src="<?php echo esc_url(plugins_url('assets/icons/instagram.png', __FILE__)); ?>" alt="Instagram" width="40">
                        </a>
                    </div>
                    <h4><?php esc_html_e('Let me know that you are using my plugin!', 'add-to-home-screen-wp'); ?></h4>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo esc_url(urlencode(__('Using the Add to home screen #WordPress #plugin by @ziyadbachalany! http://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/ #iPhone #iPad #Apple #iOS', 'add-to-home-screen-wp'))); ?>" 
                       target="_blank" 
                       class="twitter-share-button">
                        <?php esc_html_e('Spread the word!', 'add-to-home-screen-wp'); ?>
                    </a>
                </div>
            </form>
    
            <script>
            jQuery(document).ready(function($) {
                $('.upload-icon-button').click(function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var inputId = button.data('input');
                    var customUploader = wp.media({
                        title: '<?php esc_html_e('Select or Upload Icon', 'add-to-home-screen-wp'); ?>',
                        button: { text: '<?php esc_html_e('Use this Icon', 'add-to-home-screen-wp'); ?>' },
                        multiple: false,
                        library: { type: 'image' }
                    }).on('select', function() {
                        var attachment = customUploader.state().get('selection').first().toJSON();
                        $('#' + inputId).val(attachment.url);
                    }).open();
                });
    
                $('#reset_top_color').click(function(e) {
                    e.preventDefault();
                    $('#pwa_theme_color').val('#000000');
                });
            });
            </script>
        </div>
        <?php
    }
}
if (is_admin()) {
    add_action('admin_init', array('adhsOptions', 'register'));
    add_action('admin_menu', array('adhsOptions', 'menu'));
}

add_filter('plugin_action_links', 'aths_plugin_action_links', 10, 2);
function aths_plugin_action_links($links, $file) {
    static $this_plugin;
    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }
    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=add_to_home_screen_options">' . esc_html__('Settings', 'add-to-home-screen-wp') . '</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

function generate_pwa_manifest() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'pwa_manifest') return;
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on') {
        $icon_url = get_option('touchicon_url', get_site_icon_url(192));
        if (!$icon_url || !wp_remote_get($icon_url, ['timeout' => 2])['response']['code'] === 200) {
            $icon_url = plugins_url('assets/icons/default-icon.png', __FILE__);
        }
        $manifest = [
            "name" => get_option('addmetawebcapabletitle', get_bloginfo('name')),
            "start_url" => "/",
            "display" => "standalone",
            "theme_color" => get_option('pwa_theme_color', '#000000'),
            "icons" => [
                ["src" => $icon_url, "sizes" => "192x192", "type" => "image/png"]
            ]
        ];
        header('Content-Type: application/json');
        echo json_encode($manifest);
        exit;
    }
}
add_action('init', 'generate_pwa_manifest');

function force_homepage_on_standalone() {
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on' && get_option('pwa_force_homepage') === 'on') {
        echo '<script>
            (function() {
                if (window.matchMedia("(display-mode: standalone)").matches || ("standalone" in window.navigator && window.navigator.standalone)) {
                    if (!sessionStorage.getItem("pwa_launched")) {
                        if (window.location.pathname !== "/") {
                            window.location.href = "' . esc_url(home_url('/')) . '";
                        }
                        sessionStorage.setItem("pwa_launched", "true");
                    }
                }
            })();
        </script>';
    }
}
add_action('wp_head', 'force_homepage_on_standalone', 10);

function add_pwa_loading_indicator() {
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on' && get_option('pwa_show_loading', 'off') === 'on') {
        $theme_color = esc_attr(get_option('pwa_theme_color', '#000000'));
        echo '<div id="pwa-loading-indicator" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid ' . $theme_color . '; border-radius: 50%; animation: spin 1s linear infinite; z-index: 9999; margin: 0; padding: 0; box-sizing: border-box;"></div>';
        echo '<style>
            @keyframes spin {
                0% { transform: translate(-50%, -50%) rotate(0deg); }
                100% { transform: translate(-50%, -50%) rotate(360deg); }
            }
            /* Ajustement pour iOS safe areas */
            @supports (padding: env(safe-area-inset-top)) {
                #pwa-loading-indicator {
                    top: calc(50% + env(safe-area-inset-top));
                    left: calc(50% + env(safe-area-inset-left));
                }
            }
        </style>';
        echo '<script>
            (function() {
                if (window.matchMedia("(display-mode: standalone)").matches || ("standalone" in window.navigator && window.navigator.standalone)) {
                    const spinner = document.getElementById("pwa-loading-indicator");
                    if (!spinner) return;

                    // Réinitialiser l’état à chaque chargement
                    spinner.style.display = "none";

                    // Remplacer l’élément pour éviter les conflits d’événements
                    const newSpinner = spinner.cloneNode(true);
                    spinner.parentNode.replaceChild(newSpinner, spinner);

                    document.addEventListener("click", function(e) {
                        const link = e.target.closest("a");
                        if (link && link.href && link.href.indexOf(window.location.host) !== -1) {
                            newSpinner.style.display = "block";
                            setTimeout(() => { newSpinner.style.display = "none"; }, 500);
                        }
                    });

                    window.addEventListener("load", function() {
                        newSpinner.style.display = "none";
                    });
                }
            })();
        </script>';
    }
}
add_action('wp_footer', 'add_pwa_loading_indicator', 20);

function athswp_set_pwa_viewport() {
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on') {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">';
    }
}
add_action('wp_head', 'athswp_set_pwa_viewport', 1);

function add_android_install_button() {
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on' && get_option('pwa_show_install_button', 'off') === 'on') {
        echo '<button id="pwa-install-button">' . esc_html__('Add to Home Screen', 'add-to-home-screen-wp') . '</button>';
        echo '<script>
            (function() {
                let deferredPrompt;
                const installButton = document.getElementById("pwa-install-button");
                installButton.style.backgroundColor = "' . esc_js(get_option('pwa_theme_color', '#000000')) . '";

                window.addEventListener("beforeinstallprompt", function(e) {
                    e.preventDefault();
                    deferredPrompt = e;
                    installButton.style.display = "block";
                });

                installButton.addEventListener("click", function() {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.then((choiceResult) => {
                            if (choiceResult.outcome === "accepted") {
                                console.log("Utilisateur a accepté l’installation");
                            } else {
                                console.log("Utilisateur a refusé l’installation");
                            }
                            deferredPrompt = null;
                            installButton.style.display = "none";
                        });
                    }
                });

                if (window.matchMedia("(display-mode: standalone)").matches || ("standalone" in window.navigator && window.navigator.standalone)) {
                    installButton.style.display = "none";
                }

                if (/Android/i.test(navigator.userAgent)) {
                    installButton.dataset.android = "true";
                }
            })();
        </script>';
    }
}
add_action('wp_footer', 'add_android_install_button', 25);

function enable_premium_features() {
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on') {
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">';
        echo '<link rel="manifest" href="' . add_query_arg('action', 'pwa_manifest', site_url()) . '">';
    }
}
add_action('wp_head', 'enable_premium_features', 9);

function add2homecustom() {
    $allowed_html = array(
        'center' => array(),
        'h4'     => array(),
        'strong' => array(),
        'br'     => array(),
        'p'      => array(),
        'b'      => array(),
        'i'      => array(),
    );

    echo '<script type="text/javascript">';
    echo 'var addToHomeConfig = {';
    if (get_option('message')) {
        $str = get_option('message');
        $str = preg_replace("(\r\n|\n|\r)", " ", $str);
        $safe_message = wp_kses($str, $allowed_html);
        echo 'message: ' . wp_json_encode($safe_message) . ',';
    }
    if (get_option('returningvisitor') === 'on') {
        echo 'returningVisitor: true,';
    }
    echo 'animationIn: "' . esc_js(get_option('animationin', 'fade')) . '",';
    echo 'animationOut: "' . esc_js(get_option('animationout', 'fade')) . '",';
    echo 'startdelay: ' . (get_option('startdelay') ? absint(get_option('startdelay')) : 2000) . ',';
    echo 'lifespan: ' . (get_option('lifespan') ? absint(get_option('lifespan')) : 20000) . ',';
    echo 'expire: ' . (get_option('expire') ? absint(get_option('expire')) : 0) . ',';
    echo 'touchIcon: ' . (get_option('touchicon') === 'on' ? 'true' : 'false') . ',';
    echo 'bottomOffset: ' . (get_option('bottomoffset') ? absint(get_option('bottomoffset')) : 14) . ',';
    echo '};';
    echo '</script>';
}
add_action('wp_head', 'add2homecustom', 8);

add_action('wp_enqueue_scripts', 'addtohomecss');
function addtohomecss() {
    $pagetarget = get_option('pagetarget', 'allpages');
    if ($pagetarget === 'homeonly') {
        if (is_home() || is_front_page()) {
            wp_enqueue_style('adhs', plugins_url('add2home.css', __FILE__), [], '2.5');
        }
    } else {
        wp_enqueue_style('adhs', plugins_url('add2home.css', __FILE__), [], '2.5');
    }
}

add_action('wp_enqueue_scripts', 'addtohomejs', 10);
function addtohomejs() {
    $pagetarget = get_option('pagetarget', 'allpages');
    if ($pagetarget === 'homeonly') {
        if (is_home() || is_front_page()) {
            wp_enqueue_script('adhs', plugins_url('add2home.js', __FILE__), [], '2.5', true);
        }
    } else {
        wp_enqueue_script('adhs', plugins_url('add2home.js', __FILE__), [], '2.5', true);
    }
}

add_action('wp_enqueue_scripts', 'athswp_enqueue_frontend_styles');
function athswp_enqueue_frontend_styles() {
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on') {
        wp_enqueue_style('athswp-pwa', plugins_url('pwa-styles.css', __FILE__), [], '2.6.6');
        // Injecter la couleur dynamique
        $theme_color = esc_attr(get_option('pwa_theme_color', '#000000'));
        echo "<style>#pwa-loading-indicator { border-top-color: $theme_color !important; }</style>";
    }
}

function addmetawebcapable() { ?>
    <meta name="apple-mobile-web-app-capable" content="yes">
<?php }
if (get_option('browseraths') === 'fullscreenmode') {
    add_action('wp_head', 'addmetawebcapable', 3);
}

function addmetawebcapable_title() { ?>
    <meta name="apple-mobile-web-app-title" content="<?php if (get_option('addmetawebcapabletitle')) { echo esc_html(get_option('addmetawebcapabletitle')); } else { echo wp_title(''); } ?>">
<?php }
add_action('wp_head', 'addmetawebcapable_title', 2);

function addtouchicon_url() {
    $icon_url = get_option('touchicon_url', plugins_url('assets/icons/default-icon.png', __FILE__));
    echo '<link rel="apple-touch-icon';
    if (get_option('aths_touchicon_precomposed') === 'on') {
        echo '-precomposed';
    }
    echo '" sizes="180x180" href="';
    echo esc_url($icon_url);
    echo '">';
}
if (get_option('touchicon_url') || get_option('touchicon') === 'on') {
    add_action('wp_head', 'addtouchicon_url', 4);
}

function addmetawebcapablelinks() { ?>
    <script type="text/javascript">
    (function(document,navigator,standalone) {
        if ((standalone in navigator) && navigator[standalone]) {
            var curnode, location=document.location, stop=/^(a|html)$/i;
            document.addEventListener('click', function(e) {
                curnode=e.target;
                while (!(stop).test(curnode.nodeName)) {
                    curnode=curnode.parentNode;
                }
                if('href' in curnode && ( curnode.href.indexOf('http') || ~curnode.href.indexOf(location.host) ) ) {
                    e.preventDefault();
                    location.href = curnode.href;
                }
            },false);
        }
    })(document,window.navigator,'standalone');
    </script>
<?php }
if (get_option('addmetawebcapablelinks')) {
    add_action('wp_head', 'addmetawebcapablelinks', 3);
}

function aths_track() { ?>
    <script>
    if (window.navigator.standalone == true && ( navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/iPad/i) )) {
        if (typeof gtag === 'function') {
            gtag('event', 'webapp_usage', {
                'event_category': 'Web App',
                'event_label': 'Yes',
                'value': 1
            });
        }
    } else {
        if (typeof gtag === 'function') {
            gtag('event', 'webapp_usage', {
                'event_category': 'Web App',
                'event_label': 'No',
                'value': 0
            });
        }
    }
    </script>
<?php }
if (get_option('aths_track')) {
    add_action('wp_head', 'aths_track', 4);
}

function addbottommenu() { ?>
    <script>
    if (window.navigator.standalone == true) {
        document.write('<div id="backforward"><div id="backnav"><a href="javascript:history.back();"><span> </span></a></div><div id="nextnav"><a href="javascript:history.forward();"><span></span></a></div><div id="refreshnav"><A HREF="javascript:history.go(0)"><span>↻</span></A></div></div>');
    } else {
        document.write('');
    }
    </script>
<?php }
if ((get_option('browseraths') === 'fullscreenmode') && (!get_option('webappnavbar'))) {
    add_action('wp_footer', 'addbottommenu', 15);
}

endif;