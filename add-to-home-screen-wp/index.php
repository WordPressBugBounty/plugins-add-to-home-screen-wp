<?php if ( ! defined( 'ABSPATH' ) ) exit;
/*
    Plugin Name: Add to home screen WP
    Plugin URI: https://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/
    Description: Allow your visitors to add your WordPress blog on their iOS home screen (iPhone, iPod touch, iPad).
    Version: 2.4
    Author: Ziyad Bachalany
    Author URI: https://tulipemedia.com
    License: GPL-2.0-or-later
    License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
if(!class_exists('adhsOptions')) :
// DEFINE PLUGIN ID
define('adhsOptions_ID', 'add_to_home_screen');
// DEFINE PLUGIN NICK
define('adhsOptions_NICK', 'ATHS Options');
function athswp_load_textdomain() {
    load_plugin_textdomain( 'add-to-home-screen-wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'athswp_load_textdomain' );

class adhsOptions
{
    /** function/method
    * Usage: return absolute file path
    * Arg(1): string
    * Return: string
    */
    public static function file_path($file)
    {
        return plugin_dir_path(__FILE__) . $file;
    }

    /** function/method
    * Usage: Sanitization callback for the message field
    * Arg(1): string (input value)
    * Return: string (sanitized value)
    */
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

    /** function/method
    * Usage: hooking the plugin options/settings
    * Arg(0): null
    * Return: void
    */
    public static function register()
    {
        register_setting(adhsOptions_ID.'_options', 'returningvisitor', array(
            'sanitize_callback' => 'sanitize_key'
        ));

        register_setting(adhsOptions_ID.'_options', 'message', array(
            'sanitize_callback' => array('adhsOptions', 'sanitize_message') // Correction ici
        ));

        register_setting(adhsOptions_ID.'_options', 'animationin', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));

        register_setting(adhsOptions_ID.'_options', 'animationout', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));

        register_setting(adhsOptions_ID.'_options', 'startdelay', array(
            'sanitize_callback' => 'absint'
        ));

        register_setting(adhsOptions_ID.'_options', 'lifespan', array(
            'sanitize_callback' => 'absint'
        ));

        register_setting(adhsOptions_ID.'_options', 'bottomoffset', array(
            'sanitize_callback' => 'absint'
        ));

        register_setting(adhsOptions_ID.'_options', 'expire', array(
            'sanitize_callback' => 'absint'
        ));

        register_setting(adhsOptions_ID.'_options', 'touchicon', array(
            'sanitize_callback' => 'sanitize_key'
        ));

        register_setting(adhsOptions_ID.'_options', 'touchicon_url', array(
            'sanitize_callback' => 'esc_url_raw'
        ));

        register_setting(adhsOptions_ID.'_options', 'touch_startup_url', array(
            'sanitize_callback' => 'esc_url_raw'
        ));

        register_setting(adhsOptions_ID.'_options', 'addmetawebcapabletitle', array(
            'sanitize_callback' => 'sanitize_key'
        ));

        register_setting(adhsOptions_ID.'_options', 'pagetarget', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));

        register_setting(adhsOptions_ID.'_options', 'aths_touchicon_precomposed', array(
            'sanitize_callback' => 'sanitize_key'
        ));

        register_setting(adhsOptions_ID.'_options', 'touchicon_url72', array(
            'sanitize_callback' => 'esc_url_raw'
        ));

        register_setting(adhsOptions_ID.'_options', 'touchicon_url114', array(
            'sanitize_callback' => 'esc_url_raw'
        ));

        register_setting(adhsOptions_ID.'_options', 'touchicon_url144', array(
            'sanitize_callback' => 'esc_url_raw'
        ));

        register_setting(adhsOptions_ID.'_options', 'aths_increaseslot', array(
            'sanitize_callback' => 'absint'
        ));
    }

    /** function/method
    * Usage: hooking (registering) the plugin menu
    * Arg(0): null
    * Return: void
    */
    public static function menu()
    {
        // Create menu tab
        add_options_page(adhsOptions_NICK.' Plugin Options', adhsOptions_NICK, 'manage_options', adhsOptions_ID.'_options', array('adhsOptions', 'options_page'));
    }

    /** function/method
    * Usage: show options/settings form page
    * Arg(0): null
    * Return: void
    */
    public static function options_page()
    {
        if (!current_user_can('manage_options'))
        {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'add-to-home-screen-wp' ) );
        }

        $plugin_id = adhsOptions_ID;
        // display options page
        include(self::file_path('options.php'));
    }
}

if ( is_admin() )
{
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
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=add_to_home_screen_options">Settings</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

//custom script in header
function add2homecustom() {
    // Définir les balises HTML autorisées pour le message
    $allowed_html = array(
        'center' => array(),
        'h4'     => array(),
        'strong' => array(),
        'br'     => array(),
        'p'      => array(),
        'b'      => array(),
        'i'      => array(),
    );

    // Démarrer le script
    echo '<script type="text/javascript">';
    echo 'var addToHomeConfig = {';

    // Traiter le message
    if (get_option('message')) { 
        $str = get_option('message');
        // Remplacer les retours à la ligne par des espaces
        $str = preg_replace("(\r\n|\n|\r)", " ", $str);
        // Filtrer le HTML pour ne garder que les balises autorisées
        $safe_message = wp_kses($str, $allowed_html);
        // Encoder le message pour JavaScript avec wp_json_encode
        echo 'message: ' . wp_json_encode($safe_message) . ',';
    }

    // Ajouter les autres options
    if (get_option('returningvisitor')) { 
        echo 'returningVisitor: true,'; 
    }
    echo 'animationIn: "' . esc_js(get_option('animationin', 'fade')) . '",';
    echo 'animationOut: "' . esc_js(get_option('animationout', 'fade')) . '",';
    echo 'startdelay: ' . (get_option('startdelay') ? absint(get_option('startdelay')) : 2000) . ',';
    echo 'lifespan: ' . (get_option('lifespan') ? absint(get_option('lifespan')) : 20000) . ',';
    echo 'expire: ' . (get_option('expire') ? absint(get_option('expire')) : 0) . ',';
    echo 'touchIcon: ' . (get_option('touchicon') == 'on' ? 'true' : 'false') . ',';

    // Terminer le script
    echo '};';
    echo '</script>';
}
add_action('wp_head', 'add2homecustom', 8);

//add css file
/*
Loading of the Add to Home Screen Floating Layer by Matteo Spinelli.
[Official homepage](http://cubiq.org/add-to-home-screen)
## License

This software is released under the MIT License.

Copyright (c) 2013 Matteo Spinelli, http://cubiq.org/

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/
add_action( 'wp_enqueue_scripts', 'addtohomecss' );
function addtohomecss() {
    if ((get_option('pagetarget') == 'homeonly') ) {
        if ( is_home() || is_front_page() ) {
            wp_register_style( 'adhs', plugins_url('add2home.css', __FILE__) );
            wp_enqueue_style( 'adhs' );
        }
    } elseif (get_option('pagetarget') == 'allpages') {
        wp_register_style( 'adhs', plugins_url('add2home.css', __FILE__) );
        wp_enqueue_style( 'adhs' );
    }
    else {
        wp_register_style( 'adhs', plugins_url('add2home.css', __FILE__) );
        wp_enqueue_style( 'adhs' );
    }
}

//add js file
add_action( 'wp_enqueue_scripts', 'addtohomejs', 10 );
function addtohomejs()
{
    if ((get_option('pagetarget') == 'homeonly') ) {
        if ( is_home() || is_front_page() ) {
            // Register the script:
            wp_register_script( 'adhs', plugins_url('add2home.js', __FILE__) );
            // Enqueue the script:
            wp_enqueue_script( 'adhs' );
        }
    } elseif (get_option('pagetarget') == 'allpages') {
        // Register the script:
        wp_register_script( 'adhs', plugins_url('add2home.js', __FILE__) );
        // Enqueue the script:
        wp_enqueue_script( 'adhs' );
    }
    else {
        // Register the script:
        wp_register_script( 'adhs', plugins_url('add2home.js', __FILE__) );
        // Enqueue the script:
        wp_enqueue_script( 'adhs' );
    }
}

function addmetawebcapable() { ?>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <?php }
    if (get_option('browseraths') == 'fullscreenmode')
    {
    add_action('wp_head', 'addmetawebcapable', 3);
    }

function addmetawebcapable_title() { ?>
    <meta name="apple-mobile-web-app-title" content="<?php if (get_option('addmetawebcapabletitle')) { echo esc_html(get_option('addmetawebcapabletitle')); } else { echo wp_title(''); } ?>">
    <?php }
    add_action('wp_head', 'addmetawebcapable_title', 2);

function addtouchicon_url() {
    echo'<link rel="apple-touch-icon'; if (get_option('aths_touchicon_precomposed')) { echo'-precomposed'; } echo'" href="';
    echo esc_html(get_option('touchicon_url'));
    echo'">';
}
function addtouchicon_url72() {
    echo'<link rel="apple-touch-icon'; if (get_option('aths_touchicon_precomposed')) { echo'-precomposed'; } echo'" sizes="72x72" href="';
    echo esc_html(get_option('touchicon_url72'));
    echo'">';
}
function addtouchicon_url114() {
    echo'<link rel="apple-touch-icon'; if (get_option('aths_touchicon_precomposed')) { echo'-precomposed'; } echo'" sizes="114x114" href="';
    echo esc_html(get_option('touchicon_url114'));
    echo'">';
}
function addtouchicon_url144() {
    echo'<link rel="apple-touch-icon'; if (get_option('aths_touchicon_precomposed')) { echo'-precomposed'; } echo'" sizes="144x144" href="';
    echo esc_html(get_option('touchicon_url144'));
    echo'">';
}
if (get_option('touchicon_url')) {
    add_action('wp_head', 'addtouchicon_url', 4);
}
if (get_option('touchicon_url72')) {
    add_action('wp_head', 'addtouchicon_url72', 4);
}
if (get_option('touchicon_url114')) {
    add_action('wp_head', 'addtouchicon_url114', 4);
}
if (get_option('touchicon_url144')) {
    add_action('wp_head', 'addtouchicon_url144', 4);
}

function touch_startup_url() { 
    echo'<link rel="apple-touch-startup-image" href="';
    echo esc_html(get_option('touch_startup_url'));
    echo'" media="screen and (max-device-width : 320px)">';
}
function touch_startup_url920() { 
    echo'<link rel="apple-touch-startup-image" href="';
    echo esc_html(get_option('touch_startup_url920'));
    echo'" media="(max-device-width : 480px) and (-webkit-min-device-pixel-ratio : 2)">';
}
function touch_startup_url1096() { 
    echo'<link rel="apple-touch-startup-image" href="';
    echo esc_html(get_option('touch_startup_url1096'));
    echo'" media="(max-device-width : 548px) and (-webkit-min-device-pixel-ratio : 2)">';
}
function touch_startup_url748() { 
    echo'<link rel="apple-touch-startup-image" sizes="1024x748" href="';
    echo esc_html(get_option('touch_startup_url748'));
    echo'" media="screen and (min-device-width : 481px) and (max-device-width : 1024px) and (orientation : landscape)">';
}
function touch_startup_url1004() { 
    echo'<link rel="apple-touch-startup-image" sizes="768x1004" href="';
    echo esc_html(get_option('touch_startup_url1004'));
    echo'" media="screen and (min-device-width : 481px) and (max-device-width : 1024px) and (orientation : portrait)">';
}
if (get_option('touch_startup_url')) {
    add_action('wp_head', 'touch_startup_url', 5);
}
if (get_option('touch_startup_url920')) {
    add_action('wp_head', 'touch_startup_url920', 5);
}
if (get_option('touch_startup_url1096')) {
    add_action('wp_head', 'touch_startup_url1096', 5);
}
if (get_option('touch_startup_url748')) {
    add_action('wp_head', 'touch_startup_url748', 5);
}
if (get_option('touch_startup_url1004')) {
    add_action('wp_head', 'touch_startup_url1004', 5);
}

function addmetawebcapablelinks() { ?>
    <script type="text/javascript">
    (function(document,navigator,standalone) {
        // prevents links from apps from oppening in mobile safari
        // this javascript must be the first script in your <head>
        if ((standalone in navigator) && navigator[standalone]) {
            var curnode, location=document.location, stop=/^(a|html)$/i;
            document.addEventListener('click', function(e) {
                curnode=e.target;
                while (!(stop).test(curnode.nodeName)) {
                    curnode=curnode.parentNode;
                }
                // Condidions to do this only on links to your own app
                // if you want all links, use if('href' in curnode) instead.
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
        _gaq.push(['_setCustomVar', 5, 'webapp', 'yes', 2 ]);
    }
    else {
        _gaq.push(['_setCustomVar', 5, 'webapp', 'no', 2 ]);
    }
    </script>
    <?php }
    if (get_option('aths_track')) {
    add_action('wp_head', 'aths_track', 4);
    }

function addbottommenu() { ?>    
    <script>
    if (window.navigator.standalone == true) {
        document.write('<div id="backforward"><div id="backnav"><a href="javascript:history.back();"><span> </span></a></div><div id="nextnav"><a href="javascript:history.forward();"><span></span></a></div><div id="refreshnav"><A HREF="javascript:history.go(0)"><span>&#x21bb;</span></A></div></div>');
    }else{
        document.write('');
    }
    </script>
    <?php }
    if ((get_option('browseraths') == 'fullscreenmode') AND(!get_option('webappnavbar')))
    {
    add_action('wp_footer', 'addbottommenu', 15);
    }

endif;
?>