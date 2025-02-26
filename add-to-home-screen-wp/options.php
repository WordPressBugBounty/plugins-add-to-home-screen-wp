<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <style type="text/css">
        .aths-container { max-width: 800px; margin: 20px 0; }
        .aths-tabs { padding-bottom: 0; margin-bottom: 0; }
        .aths-tabs .nav-tab { display: inline-block; padding: 10px 20px; margin: 0 5px 0 0; cursor: pointer; background: #f7f7f7; border: 1px solid #ddd; border-bottom: none; border-radius: 4px 4px 0 0; font-size: 14px; font-weight: 500; color: #555; transition: all 0.2s; }
        .aths-tabs .nav-tab:hover { background: #e5e5e5; color: #333; }
        .aths-tabs .nav-tab-active { background: #fff; border-bottom: 2px solid #fff; color: #0073aa; font-weight: 600; }
        .aths-tab-content { display: none; padding: 20px; background: #fff; border: 1px solid #ddd; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); clear: both; }
        .aths-tab-content.active { display: block; }
        .aths-option { margin-bottom: 25px; }
        .aths-option-label { font-size: 14px; font-weight: 500; color: #333; margin-bottom: 5px; }
        .aths-option-field input[type="text"], .aths-option-field input[type="url"], .aths-option-field textarea, .aths-option-field select { width: 100%; max-width: 400px; padding: 6px; font-size: 14px; }
        .aths-option-field input[type="checkbox"] { margin-top: 2px; vertical-align: middle; }
        .aths-option-field .description { font-size: 12px; color: #666; margin-top: 5px; line-height: 1.5; }
        .premium-disabled .aths-option:not(:first-child) input, .premium-disabled .aths-option:not(:first-child) button { opacity: 0.5; pointer-events: none; }
        .premium-lock { color: #f39c12; margin-left: 5px; font-size: 16px; vertical-align: middle; }
        .aths-footer { max-width: 800px; margin: 20px auto; padding: 20px; background: #F2FBFD; border: 1px solid #B7E9E9; text-align: center; border-radius: 4px; }
        .aths-footer h3 { margin-top: 0; font-size: 18px; }
        .aths-footer .social-links { display: flex; justify-content: center; gap: 20px; align-items: center; flex-wrap: wrap; margin: 15px 0; }
        #athswp_license_status.success { color: green; font-weight: bold; }
        #athswp_license_status.error { color: red; }
        #athswp_license_status.checking { color: #666; font-style: italic; }
    </style>

    <h2><?php esc_html_e('ATHS Options » Settings', 'add-to-home-screen-wp'); ?></h2>
    <?php wp_enqueue_script('jquery'); ?>

    <form action="options.php" method="post" id="<?php echo esc_attr($plugin_id); ?>_options_form" name="<?php echo esc_attr($plugin_id); ?>_options_form">
        <?php settings_fields(adhsOptions_ID.'_options'); ?>
        <?php do_settings_sections(adhsOptions_ID.'_options'); ?>

        <div class="aths-container">
            <div class="aths-tabs">
                <span class="nav-tab nav-tab-active" data-tab="free"><?php esc_html_e('Free Options', 'add-to-home-screen-wp'); ?></span>
                <span class="nav-tab" data-tab="premium"><?php esc_html_e('ATHS Premium Settings', 'add-to-home-screen-wp'); ?></span>
            </div>

            <!-- Onglet Free -->
            <div id="free" class="aths-tab-content active">
                <div class="aths-option">
                    <label class="aths-option-label" for="returningvisitor"><?php esc_html_e('Show to returning visitors only', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="checkbox" name="returningvisitor" id="returningvisitor" <?php checked(esc_attr(get_option('returningvisitor')) == 'on', true); ?> />
                        <div class="description">
                            <?php 
                            $description = __('Set this to true and the message won\'t be shown the first time one user visits your blog. It can be useful to target only returning visitors and not irritate first time visitors.', 'add-to-home-screen-wp'); 
                            echo wp_kses_post($description . ' <i>' . __('I recommend to check this value', 'add-to-home-screen-wp') . '</i>'); ?>
                        </div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="message"><?php esc_html_e('Custom message', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <textarea rows="3" cols="50" name="message" id="message"><?php echo esc_textarea(get_option('message')); ?></textarea>
                        <div class="description">
                            <?php esc_html_e('Type the custom message that you want appearing in the balloon. You can also display default message in the language of your choice by typing the locale (e.g: en_us).', 'add-to-home-screen-wp'); ?><br />
                            <?php
                            $message = sprintf(
                                __('Use %1$s to show user\'s device on message, %2$s to display the first add icon, and %3$s to display the second add to home screen icon. You can also use HTML tags like %4$s, %5$s, %6$s, or %7$s for formatting.', 'add-to-home-screen-wp'),
                                '<i>%device</i>',
                                '<i>%icon</i>',
                                '<i>%add</i>',
                                '<code>&lt;center&gt;</code>',
                                '<code>&lt;h3&gt;</code>',
                                '<code>&lt;strong&gt;</code>',
                                '<code>&lt;i&gt;</code>'
                            );
                            echo wp_kses_post($message); ?>
                        </div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="animationin"><?php esc_html_e('Animation in', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <select name="animationin" id="animationin">
                            <option value="drop" <?php selected(esc_attr(get_option('animationin', 'fade')), 'drop'); ?>>drop</option>
                            <option value="bubble" <?php selected(esc_attr(get_option('animationin', 'fade')), 'bubble'); ?>>bubble</option>
                            <option value="fade" <?php selected(esc_attr(get_option('animationin', 'fade')), 'fade'); ?>>fade</option>
                        </select>
                        <div class="description"><?php esc_html_e('The animation the balloon appears with.', 'add-to-home-screen-wp'); ?></div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="animationout"><?php esc_html_e('Animation out', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <select name="animationout" id="animationout">
                            <option value="drop" <?php selected(esc_attr(get_option('animationout', 'fade')), 'drop'); ?>>drop</option>
                            <option value="bubble" <?php selected(esc_attr(get_option('animationout', 'fade')), 'bubble'); ?>>bubble</option>
                            <option value="fade" <?php selected(esc_attr(get_option('animationout', 'fade')), 'fade'); ?>>fade</option>
                        </select>
                        <div class="description"><?php esc_html_e('The animation the balloon exits with.', 'add-to-home-screen-wp'); ?></div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="startdelay"><?php esc_html_e('Start delay', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="text" name="startdelay" id="startdelay" value="<?php echo esc_attr(get_option('startdelay', 2000)); ?>" />
                        <div class="description"><?php esc_html_e('Milliseconds to wait before showing the message. Default: 2000', 'add-to-home-screen-wp'); ?></div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="lifespan"><?php esc_html_e('Lifespan', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="text" name="lifespan" id="lifespan" value="<?php echo esc_attr(get_option('lifespan', 20000)); ?>" />
                        <div class="description"><?php esc_html_e('Milliseconds to wait before hiding the message. Default: 20000', 'add-to-home-screen-wp'); ?></div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="bottomoffset"><?php esc_html_e('Bottom offset', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="text" name="bottomoffset" id="bottomoffset" value="<?php echo esc_attr(get_option('bottomoffset', 14)); ?>" />
                        <div class="description"><?php esc_html_e('Distance in pixels from the bottom (iPhone) or the top (iPad). Default: 14', 'add-to-home-screen-wp'); ?></div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="expire"><?php esc_html_e('Expire timeframe', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="text" name="expire" id="expire" value="<?php echo esc_attr(get_option('expire', 0)); ?>" />
                        <div class="description">
                            <?php 
                            $expire_description = __('Minutes before displaying the message again. Default: 0 (=always show). It\'s highly recommended to set a timeframe in order to prevent showing message at each and every page load for those who didn\'t add the Web App to their homescreen or those who added it but load the blog on Safari!<br /><i>Recommended values: 43200 for one month or 525600 for one year.</i>', 'add-to-home-screen-wp');
                            echo wp_kses($expire_description, array('br' => array(), 'i' => array())); ?>
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid #ddd; margin: 20px 0;">

                <div class="aths-option">
                    <label class="aths-option-label" for="touchicon"><?php esc_html_e('Enable touch icon', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="checkbox" name="touchicon" id="touchicon" <?php checked(esc_attr(get_option('touchicon')) == 'on', true); ?> />
                        <div class="description"><?php esc_html_e('If checked, displays the application icon next to the message using the URL provided below.', 'add-to-home-screen-wp'); ?></div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="aths_touchicon_precomposed"><?php esc_html_e('Precomposed icon', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="checkbox" name="aths_touchicon_precomposed" id="aths_touchicon_precomposed" <?php checked(esc_attr(get_option('aths_touchicon_precomposed')) == 'on', true); ?> />
                        <div class="description"><?php esc_html_e('If checked, the icon will display without the Apple gloss effect.', 'add-to-home-screen-wp'); ?></div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="touchicon_url"><?php esc_html_e('Touch icon URL', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="url" name="touchicon_url" id="touchicon_url" value="<?php echo esc_url(get_option('touchicon_url')); ?>" />
                        <button type="button" class="button upload-icon-button" data-input="touchicon_url"><?php esc_html_e('Upload Icon', 'add-to-home-screen-wp'); ?></button>
                        <div class="description">
                            <?php 
                            $icon_description = __('Paste the URL or upload an icon (ideally 192x192 or 512x512 pixels, PNG format) for iOS and Android home screens. This will be used as <i>link rel="apple-touch-icon"</i> and in the PWA manifest.', 'add-to-home-screen-wp');
                            echo wp_kses($icon_description, array('i' => array())); ?>
                        </div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="addmetawebcapabletitle"><?php esc_html_e('Title of your Web App', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="text" name="addmetawebcapabletitle" id="addmetawebcapabletitle" value="<?php echo esc_attr(get_option('addmetawebcapabletitle')); ?>" />
                        <div class="description"><?php esc_html_e('Type the name of your blog (max: 12 characters!). Default: it takes the default title of the page.', 'add-to-home-screen-wp'); ?></div>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="pagetarget"><?php esc_html_e('On which page the balloon should appear?', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <select name="pagetarget" id="pagetarget">
                            <option value="homeonly" <?php selected(esc_attr(get_option('pagetarget', 'allpages')), 'homeonly'); ?>><?php esc_html_e('Home only', 'add-to-home-screen-wp'); ?></option>
                            <option value="allpages" <?php selected(esc_attr(get_option('pagetarget', 'allpages')), 'allpages'); ?>><?php esc_html_e('All pages', 'add-to-home-screen-wp'); ?></option>
                        </select>
                        <div class="description">
                            <?php esc_html_e('Keep in mind that if someone adds your blog to home screen from a single article page for instance, the web app will load this page and not the homepage of the blog. That\'s why you could choose to open the floating balloon on homepage only and not on all pages of your blog. In Premium mode, you can override this by forcing the homepage to launch.', 'add-to-home-screen-wp'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglet Premium -->
            <div id="premium" class="aths-tab-content">
                <?php
                $license_key = get_option('athswp_license_key', '');
                $is_premium = athswp_is_premium();

                $top_color = get_option('pwa_theme_color') ?: '#000000';
                $enable_features = get_option('pwa_enable_features', 'on');
                $force_homepage = get_option('pwa_force_homepage') ?: 'off';
                $show_loading = get_option('pwa_show_loading') ?: 'off';
                $show_install_button = get_option('pwa_show_install_button') ?: 'off';

                // Gérer explicitement la case à cocher pwa_enable_features
                if (isset($_POST['submit'])) {
                    $enable_features = isset($_POST['pwa_enable_features']) && $_POST['pwa_enable_features'] === 'on' ? 'on' : 'off';
                    update_option('pwa_enable_features', $enable_features);
                }
                ?>
                <div class="aths-option">
                    <label class="aths-option-label" for="athswp_license_key"><?php esc_html_e('License Key', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="text" id="athswp_license_key" name="athswp_license_key" value="<?php echo esc_attr($license_key); ?>" style="width:300px;" placeholder="ex: ATHSUS2W-HFII-5495" />
                        <button type="button" id="athswp_validate_license" class="button"><?php esc_html_e('Validate License', 'add-to-home-screen-wp'); ?></button>
                        <p class="description">
                            <span id="athswp_license_status" class="<?php echo $is_premium ? 'success' : ''; ?>">
                                <?php
                                if ($is_premium) {
                                    echo '<strong>' . esc_html__('License active! All premium features are unlocked.', 'add-to-home-screen-wp') . '</strong>';
                                } else {
                                    printf(
                                        esc_html__('Enter your premium license key and click "Validate License" to unlock all features. %sGet your license now%s.', 'add-to-home-screen-wp'),
                                        '<a href="https://tulipemedia.com/produit/aths-wordpress-premium/" target="_blank">',
                                        '</a>'
                                    );
                                }
                                ?>
                            </span>
                        </p>
                        <script>
                        document.getElementById('athswp_validate_license').addEventListener('click', function() {
                            var licenseKey = document.getElementById('athswp_license_key').value;
                            var statusElement = document.getElementById('athswp_license_status');
                            var validateButton = document.getElementById('athswp_validate_license');

                            statusElement.innerHTML = '<?php esc_html_e('Checking license...', 'add-to-home-screen-wp'); ?>';
                            statusElement.className = 'checking';
                            validateButton.disabled = true;

                            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'action=athswp_validate_license&license_key=' + encodeURIComponent(licenseKey) + '&nonce=<?php echo wp_create_nonce('athswp_validate_nonce'); ?>'
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    statusElement.innerHTML = '<strong><?php esc_html_e('License activated successfully!', 'add-to-home-screen-wp'); ?></strong>';
                                    statusElement.className = 'success';
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    statusElement.innerHTML = data.data;
                                    statusElement.className = 'error';
                                    validateButton.disabled = false;
                                }
                            })
                            .catch(error => {
                                statusElement.innerHTML = '<?php esc_html_e('Error validating license. Please try again.', 'add-to-home-screen-wp'); ?>';
                                statusElement.className = 'error';
                                validateButton.disabled = false;
                                console.error('Validation error:', error);
                            });
                        });
                        </script>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="pwa_enable_features"><?php esc_html_e('Enable ATHS Premium Features', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="checkbox" id="pwa_enable_features" name="pwa_enable_features" value="on" <?php checked($enable_features === 'on'); ?> <?php echo !$is_premium ? 'disabled' : ''; ?> />
                        <div class="description">
                            <?php esc_html_e('Check to enable premium features (manifest, etc.). Uncheck to disable them without losing your settings. This turns your blog into a Web App, making it faster, giving it a native app feel on mobile devices, and allowing customization of the options below.', 'add-to-home-screen-wp'); ?>
                        </div>
                        <?php if (!$is_premium) : ?>
                            <input type="hidden" name="pwa_enable_features" value="<?php echo esc_attr($enable_features); ?>" />
                        <?php endif; ?>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="pwa_theme_color"><?php esc_html_e('Top Color', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="color" id="pwa_theme_color" name="pwa_theme_color" value="<?php echo esc_attr($top_color); ?>" <?php echo !$is_premium ? 'disabled' : ''; ?> />
                        <button type="button" id="reset_top_color" class="button" <?php echo !$is_premium ? 'disabled' : ''; ?>><?php esc_html_e('Reset to Default', 'add-to-home-screen-wp'); ?></button>
                        <div class="description"><?php esc_html_e('The color of the top bar in your Web App. Default: #000000 (black).', 'add-to-home-screen-wp'); ?></div>
                        <?php if (!$is_premium) : ?>
                            <input type="hidden" name="pwa_theme_color" value="<?php echo esc_attr($top_color); ?>" />
                        <?php endif; ?>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="pwa_force_homepage"><?php esc_html_e('Force Homepage on Launch', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="checkbox" id="pwa_force_homepage" name="pwa_force_homepage" <?php checked($force_homepage === 'on'); ?> <?php echo !$is_premium ? 'disabled' : ''; ?> />
                        <div class="description"><?php esc_html_e('If checked, the Web App will always launch on the homepage, even if added from another page.', 'add-to-home-screen-wp'); ?></div>
                        <?php if (!$is_premium) : ?>
                            <input type="hidden" name="pwa_force_homepage" value="<?php echo esc_attr($force_homepage); ?>" />
                        <?php endif; ?>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="pwa_show_loading"><?php esc_html_e('Show Loading Indicator', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="checkbox" id="pwa_show_loading" name="pwa_show_loading" <?php checked($show_loading === 'on'); ?> <?php echo !$is_premium ? 'disabled' : ''; ?> />
                        <div class="description"><?php esc_html_e('If checked, a loading spinner will appear when navigating between pages in the Web App.', 'add-to-home-screen-wp'); ?></div>
                        <?php if (!$is_premium) : ?>
                            <input type="hidden" name="pwa_show_loading" value="<?php echo esc_attr($show_loading); ?>" />
                        <?php endif; ?>
                    </div>
                </div>

                <div class="aths-option">
                    <label class="aths-option-label" for="pwa_show_install_button"><?php esc_html_e('Show Install Button for Android', 'add-to-home-screen-wp'); ?></label>
                    <div class="aths-option-field">
                        <input type="checkbox" id="pwa_show_install_button" name="pwa_show_install_button" value="on" <?php checked($show_install_button === 'on'); ?> <?php echo !$is_premium ? 'disabled' : ''; ?> />
                        <div class="description"><?php esc_html_e('If checked, a button will appear on Android devices to prompt users to add the Web App to their home screen.', 'add-to-home-screen-wp'); ?></div>
                        <?php if (!$is_premium) : ?>
                            <input type="hidden" name="pwa_show_install_button" value="<?php echo esc_attr($show_install_button); ?>" />
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php submit_button(__('Save Settings', 'add-to-home-screen-wp'), 'primary'); ?>

        <!-- Restons en contact -->
        <div class="aths-footer">
            <h3><?php esc_html_e('Keep in touch with me.', 'add-to-home-screen-wp'); ?></h3>
            <div class="social-links">
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
        $('.aths-tabs .nav-tab').click(function() {
            $('.aths-tabs .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.aths-tab-content').removeClass('active');
            $('#' + $(this).data('tab')).addClass('active');
        });

        $('#reset_top_color').click(function(e) {
            e.preventDefault();
            $('#pwa_theme_color').val('#000000');
        });

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
    });
    </script>
</div>