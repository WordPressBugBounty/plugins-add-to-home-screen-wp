<?php
if (!defined('ABSPATH')) {
    exit;
}

function athswp_validate_license($license_key) {
    error_log("ATHSWP Début athswp_validate_license pour $license_key");
    if (empty($license_key) || strlen($license_key) < 5) {
        update_option('athswp_license_status', 'inactive');
        update_option('athswp_license_key', '');
        update_option('athswp_license_expires', '');
        update_option('athswp_license_message', 'License key too short or empty.');
        update_option('athswp_premium_status', 'no');
        update_option('athswp_last_checked', 0);
        error_log("ATHSWP License $license_key rejected: too short or empty");
        return false;
    }

    $api_key = 'ck_20551ed87d39acacbef2bf23a89926ae0c4e6794';
    $api_secret = 'cs_b81243b795cbce812308c13c0842de1a0f3fdd31';
    $endpoint = 'https://tulipemedia.com/wp-json/lmfwc/v2';
    $expected_product_id = '4595';
    $current_instance = home_url();

    $expires_at = get_option('athswp_license_expires', '');
    if ($expires_at && strtotime($expires_at) < time()) {
        update_option('athswp_license_status', 'inactive');
        update_option('athswp_license_key', '');
        update_option('athswp_license_expires', '');
        update_option('athswp_license_message', 'License expired on: ' . date('Y-m-d H:i:s', strtotime($expires_at)));
        update_option('athswp_premium_status', 'no');
        update_option('athswp_last_checked', 0);
        error_log("ATHSWP License $license_key expired locally: $expires_at");
        return false;
    }

    $max_retries = 3;
    $retry_delay = 2; // secondes
    $times_activated = 0;
    $times_activated_max = 0;

    // Étape 1 : Récupérer les informations de la licence (/licenses/)
    $attempt = 0;
    $success = false;
    $get_response = null;
    while ($attempt < $max_retries && !$success) {
        $attempt++;
        error_log("ATHSWP Tentative de récupération des infos de la licence $attempt/$max_retries pour la clé $license_key");
        $get_response = wp_remote_get("{$endpoint}/licenses/{$license_key}", [
            'headers' => ['Authorization' => 'Basic ' . base64_encode("$api_key:$api_secret")],
            'timeout' => 30, // Augmenté à 30s
        ]);

        if (!is_wp_error($get_response)) {
            $success = true;
        } elseif ($attempt < $max_retries) {
            error_log("ATHSWP Échec de récupération des infos de la licence (Attempt $attempt): " . $get_response->get_error_message());
            sleep($retry_delay);
        }
    }

    if (is_wp_error($get_response)) {
        update_option('athswp_license_status', 'inactive');
        update_option('athswp_license_message', 'Error connecting to license server: ' . $get_response->get_error_message());
        update_option('athswp_premium_status', 'no');
        update_option('athswp_last_checked', 0);
        error_log("ATHSWP License Get Error: " . $get_response->get_error_message());
        return false;
    }

    $get_body = wp_remote_retrieve_body($get_response);
    $get_data = json_decode($get_body, true);
    error_log("ATHSWP License Get Response pour $license_key: " . $get_body);
    if (isset($get_data['success']) && $get_data['success']) {
        $expires_at = $get_data['data']['expiresAt'] ?? $expires_at;
        $valid_for = $get_data['data']['validFor'] ?? 0;
        $created_at = $get_data['data']['createdAt'] ?? '';
        if (!$expires_at && $valid_for && $created_at) {
            $expires_at = date('Y-m-d H:i:s', strtotime($created_at) + ($valid_for * DAY_IN_SECONDS));
        }
        $times_activated = $get_data['data']['timesActivated'] ?? 0;
        $times_activated_max = $get_data['data']['timesActivatedMax'] ?? 0;
        update_option('athswp_license_expires', $expires_at);
    } else {
        update_option('athswp_license_status', 'inactive');
        update_option('athswp_license_key', '');
        update_option('athswp_license_expires', '');
        update_option('athswp_license_message', $get_data['message'] ?? 'Invalid license key.');
        update_option('athswp_premium_status', 'no');
        update_option('athswp_last_checked', 0);
        error_log("ATHSWP License $license_key invalid: $get_body");
        return false;
    }

    // Étape 2 : Valider la licence (/licenses/validate/)
    $attempt = 0;
    $success = false;
    $response = null;
    while ($attempt < $max_retries && !$success) {
        $attempt++;
        error_log("ATHSWP Tentative de validation de la licence $attempt/$max_retries pour la clé $license_key");
        $response = wp_remote_get("{$endpoint}/licenses/validate/{$license_key}", [
            'headers' => ['Authorization' => 'Basic ' . base64_encode("$api_key:$api_secret")],
            'timeout' => 30,
        ]);

        if (!is_wp_error($response)) {
            $success = true;
        } elseif ($attempt < $max_retries) {
            error_log("ATHSWP Échec de validation de la licence (Attempt $attempt): " . $response->get_error_message());
            sleep($retry_delay);
        }
    }

    if (is_wp_error($response)) {
        update_option('athswp_license_status', 'inactive');
        update_option('athswp_license_message', 'Error connecting to license server: ' . $response->get_error_message());
        update_option('athswp_premium_status', 'no');
        update_option('athswp_last_checked', 0);
        error_log("ATHSWP License Error (Validate): " . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    error_log("ATHSWP License Validate Response pour $license_key: " . print_r($data, true));

    $already_active = false;
    if (isset($data['data']['activationData'])) {
        foreach ($data['data']['activationData'] as $activation) {
            if ($activation['user_agent'] === "WordPress/" . get_bloginfo('version') . "; " . $current_instance && empty($activation['deactivated_at'])) {
                $already_active = true;
                error_log("ATHSWP License $license_key already active on this instance");
                break;
            }
        }
    }

    // Étape 3 : Activer la licence si nécessaire (/licenses/activate/)
    if (!$already_active && $times_activated < $times_activated_max) {
        $attempt = 0;
        $success = false;
        $activate_response = null;
        while ($attempt < $max_retries && !$success) {
            $attempt++;
            error_log("ATHSWP Tentative d'activation de la licence $attempt/$max_retries pour la clé $license_key");
            $activate_response = wp_remote_get("{$endpoint}/licenses/activate/{$license_key}?instance=" . urlencode($current_instance), [
                'headers' => ['Authorization' => 'Basic ' . base64_encode("$api_key:$api_secret")],
                'timeout' => 30,
            ]);

            if (!is_wp_error($activate_response)) {
                $success = true;
            } elseif ($attempt < $max_retries) {
                error_log("ATHSWP Échec d'activation de la licence (Attempt $attempt): " . $activate_response->get_error_message());
                sleep($retry_delay);
            }
        }

        if (!is_wp_error($activate_response)) {
            $activate_body = wp_remote_retrieve_body($activate_response);
            $activate_data = json_decode($activate_body, true);
            error_log("ATHSWP License Activate Response pour $license_key: $activate_body");
            if (isset($activate_data['success']) && $activate_data['success']) {
                $expires_at = $activate_data['data']['expiresAt'] ?? $expires_at;
                $times_activated = $activate_data['data']['timesActivated'] ?? $times_activated;
                update_option('athswp_license_expires', $expires_at);
            } else {
                update_option('athswp_license_status', 'inactive');
                update_option('athswp_license_key', '');
                update_option('athswp_license_expires', '');
                update_option('athswp_license_message', $activate_data['message'] ?? 'Failed to activate license.');
                update_option('athswp_premium_status', 'no');
                update_option('athswp_last_checked', 0);
                error_log("ATHSWP License $license_key failed to activate: $activate_body");
                return false;
            }
        }
    }

    if ($expires_at && strtotime($expires_at) > time()) {
        update_option('athswp_license_status', 'active');
        update_option('athswp_license_expires', $expires_at);
        update_option('athswp_license_message', 'License validated. Expires on: ' . date('Y-m-d H:i:s', strtotime($expires_at)));
        update_option('athswp_premium_status', 'yes');
        update_option('athswp_last_checked', time());
        error_log("ATHSWP License $license_key validated based on expiresAt: $expires_at");
        return true;
    }

    if ($already_active) {
        update_option('athswp_license_status', 'active');
        update_option('athswp_license_expires', $expires_at ?: get_option('athswp_license_expires', ''));
        update_option('athswp_license_message', 'License already active on this instance.');
        update_option('athswp_premium_status', 'yes');
        update_option('athswp_last_checked', time());
        error_log("ATHSWP License $license_key accepted as already active");
        return true;
    }

    update_option('athswp_license_status', 'inactive');
    update_option('athswp_license_key', '');
    update_option('athswp_license_expires', '');
    update_option('athswp_license_message', $data['message'] ?? 'Invalid license or no activations remaining.');
    update_option('athswp_premium_status', 'no');
    update_option('athswp_last_checked', 0);
    error_log("ATHSWP License $license_key invalid or no activations remaining: $body");
    return false;
}

function athswp_deactivate_license($license_key) {
    if (empty($license_key) || strlen($license_key) < 5) {
        error_log("ATHSWP License key $license_key rejected for deactivation: too short or empty");
        return false;
    }

    $api_key = 'ck_20551ed87d39acacbef2bf23a89926ae0c4e6794';
    $api_secret = 'cs_b81243b795cbce812308c13c0842de1a0f3fdd31';
    $endpoint = 'https://tulipemedia.com/wp-json/lmfwc/v2';
    $current_instance = home_url();

    $max_retries = 3;
    $retry_delay = 2; // secondes
    $attempt = 0;
    $success = false;
    $error_message = '';

    while ($attempt < $max_retries && !$success) {
        $attempt++;
        error_log("ATHSWP Tentative de désactivation $attempt/$max_retries pour la clé $license_key");

        $deactivate_response = wp_remote_get("{$endpoint}/licenses/deactivate/{$license_key}?instance=" . urlencode($current_instance), [
            'headers' => ['Authorization' => 'Basic ' . base64_encode("$api_key:$api_secret")],
            'timeout' => 30, // Augmenté à 30s
        ]);

        if (is_wp_error($deactivate_response)) {
            $error_message = $deactivate_response->get_error_message();
            error_log("ATHSWP License Deactivation Error for key $license_key (Attempt $attempt): $error_message");
            if ($attempt < $max_retries) {
                sleep($retry_delay);
                continue;
            }
            update_option('athswp_license_message', 'Failed to deactivate license: ' . $error_message);
            return false;
        }

        $deactivate_code = wp_remote_retrieve_response_code($deactivate_response);
        $deactivate_body = wp_remote_retrieve_body($deactivate_response);
        $deactivate_data = json_decode($deactivate_body, true);
        error_log("ATHSWP License Deactivation Response for key $license_key (Code $deactivate_code, Attempt $attempt): $deactivate_body");

        if ($deactivate_code === 200 && isset($deactivate_data['success']) && $deactivate_data['success'] === true) {
            error_log("ATHSWP Désactivation réussie sur l’API pour $license_key");
            update_option('athswp_license_status', 'inactive');
            update_option('athswp_license_key', '');
            update_option('athswp_license_expires', '');
            update_option('athswp_license_message', 'License deactivated successfully.');
            update_option('athswp_premium_status', 'no');
            update_option('athswp_last_checked', 0);
            return true;
        } elseif ($deactivate_code === 200 && isset($deactivate_data['message']) && strpos($deactivate_data['message'], 'No activation found') !== false) {
            error_log("ATHSWP Aucune activation trouvée pour $license_key sur $current_instance");
            update_option('athswp_license_status', 'inactive');
            update_option('athswp_license_key', '');
            update_option('athswp_license_expires', '');
            update_option('athswp_license_message', 'License deactivated successfully (no activation found).');
            update_option('athswp_premium_status', 'no');
            update_option('athswp_last_checked', 0);
            return true;
        }

        $error_message = $deactivate_data['message'] ?? 'Failed to deactivate license. Please try again.';
        error_log("ATHSWP Désactivation échouée pour $license_key (Attempt $attempt): $error_message");
        if ($attempt < $max_retries) {
            sleep($retry_delay);
        }
    }

    update_option('athswp_license_message', $error_message);
    return false;
}

function athswp_is_premium() {
    static $is_premium = null;
    if ($is_premium !== null) {
        return $is_premium;
    }
    error_log("ATHSWP Début athswp_is_premium");
    $license_key = get_option('athswp_license_key', '');
    error_log("ATHSWP License key: " . ($license_key ?: 'vide'));
    if (empty($license_key)) {
        update_option('athswp_premium_status', 'no');
        update_option('athswp_license_status', 'inactive');
        update_option('athswp_license_message', 'No license key provided.');
        error_log("ATHSWP is_premium: false (no key)");
        $is_premium = false;
        return false;
    }

    $license_status = get_option('athswp_license_status', 'inactive');
    $expires_at = get_option('athswp_license_expires', '');
    $last_checked = get_option('athswp_last_checked', 0);
    error_log("ATHSWP License status: " . $license_status . ", Expires at: " . ($expires_at ?: 'vide') . ", Last checked: " . ($last_checked ? date('Y-m-d H:i:s', $last_checked) : 'never'));

    // Check local prioritaire avec limite de vérification quotidienne
    if ($license_status === 'active' && $expires_at && strtotime($expires_at) > time() && (time() - $last_checked) < DAY_IN_SECONDS) {
        error_log("ATHSWP is_premium: true (local check)");
        $is_premium = true;
        return true;
    }

    $valid = athswp_validate_license($license_key);
    $is_premium = $valid;
    if ($valid) {
        error_log("ATHSWP is_premium: true (validated)");
        return true;
    }

    update_option('athswp_premium_status', 'no');
    error_log("ATHSWP is_premium: false (validation failed)");
    return false;
}

add_action('athswp_check_license_event', 'athswp_check_license');
function athswp_check_license() {
    $license_key = get_option('athswp_license_key', '');
    if (!empty($license_key)) {
        athswp_validate_license($license_key);
    }
}

if (!wp_next_scheduled('athswp_check_license_event')) {
    wp_schedule_event(time(), 'daily', 'athswp_check_license_event');
}

add_action('admin_init', 'athswp_force_license_check_test');
function athswp_force_license_check_test() {
    if (current_user_can('manage_options') && isset($_GET['athswp_check_license'])) {
        $license_key = get_option('athswp_license_key', '');
        if (!empty($license_key)) {
            $valid = athswp_validate_license($license_key);
            error_log("ATHSWP Vérification manuelle : Licence $license_key valide ? " . ($valid ? 'Oui' : 'Non'));
            update_option('athswp_premium_status', $valid ? 'yes' : 'no');
            wp_redirect(admin_url('options-general.php?page=add_to_home_screen_options'));
            exit;
        }
    }
}

// AJAX handler for license deactivation
add_action('wp_ajax_athswp_deactivate_license', 'athswp_deactivate_license_ajax');
function athswp_deactivate_license_ajax() {
    check_ajax_referer('athswp_validate_nonce', 'nonce');
    $license_key = sanitize_text_field($_POST['license_key'] ?? '');
    if (empty($license_key)) {
        wp_send_json_error(__('No license key provided.', 'add-to-home-screen-wp'));
        return;
    }

    $result = athswp_deactivate_license($license_key);
    if ($result) {
        wp_send_json_success(__('License deactivated successfully!', 'add-to-home-screen-wp'));
    } else {
        wp_send_json_error(get_option('athswp_license_message', __('Failed to deactivate license. Please try again.', 'add-to-home-screen-wp')));
    }
}
?>