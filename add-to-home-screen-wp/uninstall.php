<?php
/**
 * Uninstall script for Add to Home Screen & PWA
 *
 * @package Add_to_Home_Screen_WP
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if data deletion is enabled
$is_network_activated = is_multisite();
$delete_data = $is_network_activated
    ? get_site_option('new_athswp_delete_data_on_uninstall', 'off') === 'on'
    : get_option('new_athswp_delete_data_on_uninstall', 'off') === 'on';

if (!$delete_data) {
    return;
}

// List of options to delete
$options = [
    'new_message_ios',
    'new_message_android',
    'new_startdelay',
    'new_lifespan',
    'new_expire_days',
    'new_bottomoffset',
    'new_animationin',
    'new_animationout',
    'new_touchicon_url',
    'new_web_app_title',
    'new_returning_visitors_only',
    'new_precomposed_icon',
    'new_enable_balloon_ios_frontend',
    'new_install_prompt_android',
    'new_enable_pwa',
    'new_balloon_display_frontend',
    'new_athswp_frontend_pwa_start_url',
    'new_athswp_pwa_custom_url',
    'new_athswp_delete_data_on_uninstall',
];

// Delete options
if ($is_network_activated) {
    foreach ($options as $option) {
        delete_site_option($option);
    }
} else {
    foreach ($options as $option) {
        delete_option($option);
    }
}