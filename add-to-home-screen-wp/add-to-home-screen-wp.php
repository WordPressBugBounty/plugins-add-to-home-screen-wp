<?php if ( ! defined( 'ABSPATH' ) ) exit;
/*
    Plugin Name: Add to Home Screen WP
    Plugin URI: https://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/
    Description: Allow your visitors to add your WordPress blog to their iOS home screen (iPhone, iPod Touch, iPad) with a floating balloon. Premium features include full PWA support, forced homepage start, PWA toggle, and loading indicator.
    Version: 2.6.4
    Author: Ziyad Bachalany
    Author URI: https://tulipemedia.com
    License: GPL-2.0-or-later
    License URI: https://www.gnu.org/licenses/gpl-2.0.html
    Text Domain: add-to-home-screen-wp
*/

// Load plugin text domain for translation
function athswp_load_textdomain() {
    load_plugin_textdomain('add-to-home-screen-wp', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'athswp_load_textdomain');

if (!class_exists('adhsOptions')) :

// DEFINE PLUGIN ID
define('adhsOptions_ID', 'add_to_home_screen');
// DEFINE PLUGIN NICK
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
        // Options gratuites
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
        register_setting(adhsOptions_ID.'_options', 'aths_increaseslot', array('sanitize_callback' => 'absint'));

        // Options premium
        register_setting(adhsOptions_ID.'_options', 'athswp_license_key', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting(adhsOptions_ID.'_options', 'athswp_premium_status', array('sanitize_callback' => 'sanitize_key')); // Statut premium
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
        $plugin_id = adhsOptions_ID;
        wp_enqueue_script('jquery');
        wp_enqueue_media(); // Pour le bouton d’upload
        include(self::file_path('options.php'));
    }
}

if (is_admin()) {
    add_action('admin_init', array('adhsOptions', 'register'));
    add_action('admin_menu', array('adhsOptions', 'menu'));
}

// Check premium status using stored option
function athswp_is_premium() {
    return get_option('athswp_premium_status', 'no') === 'yes';
}

// AJAX handler for license validation
add_action('wp_ajax_athswp_validate_license', 'athswp_validate_license_callback');
function athswp_validate_license_callback() {
    check_ajax_referer('athswp_validate_nonce', 'nonce');
    $license_key = sanitize_text_field($_POST['license_key'] ?? '');

    if (empty($license_key)) {
        update_option('athswp_premium_status', 'no');
        update_option('athswp_license_key', '');
        wp_send_json_success(__('License key cleared.', 'add-to-home-screen-wp'));
        return;
    }

    $is_valid = athswp_verify_license($license_key);
    update_option('athswp_license_key', $license_key);
    update_option('athswp_premium_status', $is_valid ? 'yes' : 'no');

    if ($is_valid) {
        wp_send_json_success(__('License activated successfully!', 'add-to-home-screen-wp'));
    } else {
        wp_send_json_error(__('Invalid license key. Please check and try again.', 'add-to-home-screen-wp'));
    }
}

// Verify license (called explicitly via AJAX)
function athswp_verify_license($key) {
    if (empty($key) || strlen($key) < 5) {
        return false;
    }

    $api_key = 'ck_ccf0d0edd9ea99ecc2c6b252c6285d20512d5b0c';
    $api_secret = 'cs_67ed4168c40f836dd0608d36bf3dc17ad5f0117b';
    $response = wp_remote_get("https://tulipemedia.com/wp-json/lmfwc/v2/licenses/validate/{$key}", [
        'headers' => ['Authorization' => 'Basic ' . base64_encode("$api_key:$api_secret")],
        'timeout' => 10,
    ]);

    if (is_wp_error($response)) {
        error_log("License API error for key $key: " . $response->get_error_message());
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    error_log("License API response for key $key: Code $response_code, Body: $body");
    $body = json_decode($body);
    return isset($body->success) && $body->success === true;
}

// Add plugin settings link
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

// Générer le manifeste PWA avec touchicon_url
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

// Forcer la page d'accueil uniquement au lancement initial
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

// Indicateur de chargement : uniquement pour les clics explicites
function add_pwa_loading_indicator() {
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on' && get_option('pwa_show_loading', 'off') === 'on') {
        echo '<style>
            #pwa-loading-indicator {
                display: none;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid ' . esc_attr(get_option('pwa_theme_color', '#000000')) . ';
                border-radius: 50%;
                animation: spin 1s linear infinite;
                z-index: 9999;
            }
            @keyframes spin {
                0% { transform: translate(-50%, -50%) rotate(0deg); }
                100% { transform: translate(-50%, -50%) rotate(360deg); }
            }
        </style>';
        echo '<div id="pwa-loading-indicator"></div>';
        echo '<script>
            (function() {
                if (window.matchMedia("(display-mode: standalone)").matches || ("standalone" in window.navigator && window.navigator.standalone)) {
                    const spinner = document.getElementById("pwa-loading-indicator");

                    // Afficher le spinner uniquement pour les clics explicites sur les liens internes
                    document.addEventListener("click", function(e) {
                        const link = e.target.closest("a");
                        if (link && link.href && link.href.indexOf(window.location.host) !== -1) {
                            spinner.style.display = "block";
                            setTimeout(() => { spinner.style.display = "none"; }, 500);
                        }
                    });

                    // Masquer le spinner quand la page est complètement chargée
                    window.addEventListener("load", function() {
                        spinner.style.display = "none";
                    });
                }
            })();
        </script>';
    }
}
add_action('wp_footer', 'add_pwa_loading_indicator', 20);

// Bouton d’installation pour Android
function add_android_install_button() {
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on' && get_option('pwa_show_install_button', 'off') === 'on') {
        echo '<style>
            #pwa-install-button {
                display: none;
                position: fixed;
                bottom: 20px;
                right: 20px;
                background-color: ' . esc_attr(get_option('pwa_theme_color', '#000000')) . ';
                color: white;
                padding: 10px 20px;
                border-radius: 25px;
                border: none;
                font-size: 16px;
                cursor: pointer;
                z-index: 1000;
                box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            }
            #pwa-install-button:hover {
                opacity: 0.9;
            }
        </style>';
        echo '<button id="pwa-install-button">' . esc_html__('Add to Home Screen', 'add-to-home-screen-wp') . '</button>';
        echo '<script>
            (function() {
                let deferredPrompt;
                const installButton = document.getElementById("pwa-install-button");

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

// Activer les fonctionnalités premium
function enable_premium_features() {
    if (athswp_is_premium() && get_option('pwa_enable_features', 'on') === 'on') {
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">';
        echo '<link rel="manifest" href="' . add_query_arg('action', 'pwa_manifest', site_url()) . '">';
    }
}
add_action('wp_head', 'enable_premium_features', 9);

// Fonctionnalités gratuites existantes
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
    if (get_option('returningvisitor')) {
        echo 'returningVisitor: true,';
    }
    echo 'animationIn: "' . esc_js(get_option('animationin', 'fade')) . '",';
    echo 'animationOut: "' . esc_js(get_option('animationout', 'fade')) . '",';
    echo 'startdelay: ' . (get_option('startdelay') ? absint(get_option('startdelay')) : 2000) . ',';
    echo 'lifespan: ' . (get_option('lifespan') ? absint(get_option('lifespan')) : 20000) . ',';
    echo 'expire: ' . (get_option('expire') ? absint(get_option('expire')) : 0) . ',';
    echo 'touchIcon: ' . (get_option('touchicon') == 'on' ? 'true' : 'false') . ',';
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

function addmetawebcapable() { ?>
    <meta name="apple-mobile-web-app-capable" content="yes">
<?php }
if (get_option('browseraths') == 'fullscreenmode') {
    add_action('wp_head', 'addmetawebcapable', 3);
}

function addmetawebcapable_title() { ?>
    <meta name="apple-mobile-web-app-title" content="<?php if (get_option('addmetawebcapabletitle')) { echo esc_html(get_option('addmetawebcapabletitle')); } else { echo wp_title(''); } ?>">
<?php }
add_action('wp_head', 'addmetawebcapable_title', 2);

// Icône unifiée avec touchicon_url
function addtouchicon_url() {
    $icon_url = get_option('touchicon_url', plugins_url('assets/icons/default-icon.png', __FILE__));
    echo '<link rel="apple-touch-icon';
    if (get_option('aths_touchicon_precomposed')) {
        echo '-precomposed';
    }
    echo '" sizes="180x180" href="';
    echo esc_url($icon_url);
    echo '">';
}
if (get_option('touchicon_url') || get_option('touchicon')) {
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
if ((get_option('browseraths') == 'fullscreenmode') && (!get_option('webappnavbar'))) {
    add_action('wp_footer', 'addbottommenu', 15);
}

endif;
?>