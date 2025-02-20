<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="wrap">
 <style type="text/css">

	input {
    border:1px solid #aaa;
    background: #fff;
}
input:focus {
    background: #fff;
    border:1px solid #555;
    box-shadow: 0px 0px 3px #ccc, 0 10px 15px #eee inset;
    border-radius:2px;
}
label {
width:100%;
   margin-bottom: 18px;
    display:inline-block;
	font-size:12px;
}
p, h2, h3 {
font-family: "Lucida Sans", "Lucida Grande", "Lucida Sans Unicode", sans-serif;
}
h3 {
margin-top:0px;
}

.adhs_description_field {
width:470px;
float: left;
margin-right:50px;
margin-bottom:20px;
line-height: 1.5em;
text-align: justify;
}
.adhs_description_field_touch {
width:280px;
float: left;
margin-right:30px;
margin-bottom:-5px;
}

.adhs_description_field span {
font-size:13px;
}

 </style>
	<form action="options.php" method="post" id="<?php echo esc_attr($plugin_id); ?>_options_form" name="<?php echo esc_attr($plugin_id); ?>_options_form">

	<?php settings_fields($plugin_id.'_options'); ?>

    <h2><?php esc_html_e('ATHS Options &raquo; Settings', 'add-to-home-screen-wp'); ?></h2>
<div style="width:780px; background-color: #F2FBFD; margin: 20px auto; padding: 16px; border: 1px solid #B7E9E9; text-align: center;">
    <h3><?php esc_html_e('Keep in touch with me.', 'add-to-home-screen-wp'); ?></h3>
    
    <div style="display: flex; justify-content: center; gap: 20px; align-items: center; flex-wrap: wrap;">
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
    
    <h4 style="margin-top: 20px;"> <?php esc_html_e('Let me know that you are using my plugin!', 'add-to-home-screen-wp'); ?> </h4>
<a href="https://twitter.com/intent/tweet?text=<?php echo esc_url(urlencode(__('Using the Add to home screen #WordPress #plugin by @ziyadbachalany! http://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/ #iPhone #iPad #Apple #iOS', 'add-to-home-screen-wp'))); ?>" 
   target="_blank" 
   class="twitter-share-button">
    <?php esc_html_e('Spread the word!', 'add-to-home-screen-wp'); ?>
</a>

</div>
    <table class="widefat">
		<thead>
		   <tr>
			 <th><input type="submit" name="submit" value="<?php esc_attr_e('Save Settings', 'add-to-home-screen-wp'); ?>" class="button-primary" /></th>
		   </tr>
		</thead>
		<tfoot>
		   <tr>
			 <th><input type="submit" name="submit" value="<?php esc_attr_e('Save Settings', 'add-to-home-screen-wp'); ?>" class="button-primary"></th>
		   </tr>
		</tfoot>
		<tbody>
		   <tr>
			 <td style="padding:25px; font-size: 25px;">
			 <h2 style="margin-bottom:15px;"><?php esc_html_e('Floating bubble options', 'add-to-home-screen-wp'); ?></h2>
				 
<label for="returningvisitor">
    <h3><?php esc_html_e('Show to returning visitors only', 'add-to-home-screen-wp'); ?></h3>
    <div class="adhs_description_field">
        <?php 
        // translators: This message explains the setting for returning visitors. 
        $description = __('Set this to true and the message won\'t be shown the first time one user visits your blog. It can be useful to target only returning visitors and not irritate first time visitors.', 'add-to-home-screen-wp'); 
        ?>
        <span><?php echo wp_kses_post($description . ' <i>' . __('I recommend to check this value', 'add-to-home-screen-wp') . '</i>'); ?></span>
    </div>
    <input type="checkbox" name="returningvisitor" <?php checked( esc_attr(get_option('returningvisitor')) == 'on', true ); ?> />
</label>


                 <label for="message">
				 <h3><?php esc_html_e('Custom message', 'add-to-home-screen-wp'); ?></h3>
                    <div class="adhs_description_field">
						<span><?php esc_html_e('Type the custom message that you want appearing in the balloon. You can also display default message in the language of your choice by typing the locale (e.g: en_us).', 'add-to-home-screen-wp'); ?></span>
						<span><br />
<?php // translators: %device will be replaced by the user's device, %icon by the first add icon, and %add by the second add to home screen icon.
$message = sprintf(
    __('Use %1$s to show user\'s device on message, %2$s to display the first add icon, and %3$s to display the second add to home screen icon.', 'add-to-home-screen-wp'),
    '<i>%device</i>',
    '<i>%icon</i>',
    '<i>%add</i>'
);
echo wp_kses_post($message); ?></span>
					</div>
                    <textarea style="width:380px" rows="3" cols="50" name="message"><?php echo esc_textarea(get_option('message')); ?></textarea>
                 </label>
				 <label for="animationin">
				  <h3><?php esc_html_e('Animation in', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span><?php esc_html_e('The animation the balloon appears with.', 'add-to-home-screen-wp'); ?></span>
					</div>
					<select name="animationin" id="animationin">
						<option value="drop"<?php echo selected(esc_attr(get_option('animationin')), 'drop', false); ?>>drop</option>
						<option value="bubble"<?php echo selected(esc_attr(get_option('animationin')), 'bubble', false); ?>>bubble</option>
						<option value="fade"<?php echo selected(esc_attr(get_option('animationin')), 'fade', false); ?>>fade</option>
					</select>
                 </label>
				 <label for="animationout">
					<h3><?php esc_html_e('Animation out', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span><?php esc_html_e('The animation the balloon exits with.', 'add-to-home-screen-wp'); ?></span>
					</div>
					<select name="animationout" id="animationout">
						<option value="drop"<?php echo selected(esc_attr(get_option('animationout')), 'drop', false); ?>>drop</option>
						<option value="bubble"<?php echo selected(esc_attr(get_option('animationout')), 'bubble', false); ?>>bubble</option>
						<option value="fade"<?php echo selected(esc_attr(get_option('animationout')), 'fade', false); ?>>fade</option>
					</select>
                 </label>
                 <label for="startdelay">
					<h3><?php esc_html_e('Start delay', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span><?php esc_html_e('Milliseconds to wait before showing the message. Default: 2000', 'add-to-home-screen-wp'); ?></span>
					</div>
                     <input type="text" name="startdelay" value="<?php echo esc_attr(get_option('startdelay')); ?>"  />
                 </label>
                 <label for="lifespan">
					<h3><?php esc_html_e('Lifespan', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span><?php esc_html_e('Milliseconds to wait before hiding the message. Default: 20000', 'add-to-home-screen-wp'); ?></span>
					</div>
					<input type="text" name="lifespan" value="<?php echo esc_attr(get_option('lifespan')); ?>"  />
                 </label>
                 <label for="bottomoffset">
					<h3><?php esc_html_e('Bottom offset', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span><?php esc_html_e('Distance in pixels from the bottom (iPhone) or the top (iPad). Default: 14', 'add-to-home-screen-wp'); ?></span>
                    </div>
					<input type="text" name="bottomoffset" value="<?php echo esc_attr(get_option('bottomoffset')); ?>"  />
                 </label>
                 <label for="expire">
					<h3><?php esc_html_e('Expire timeframe', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span><?php esc_html_e('Minutes before displaying the message again. Default: 0 (=always show). It\'s highly recommended to set a timeframe in order to prevent showing message at each and every page load for those who didn\'t add the Web App to their homescreen or those who added it but load the blog on Safari!<br /><i>Recommended values: 43200 for one month or 525600 for one year.</i>', 'add-to-home-screen-wp'); ?></span>
					</div>
                    <input type="text" name="expire" value="<?php echo esc_attr(get_option('expire')); ?>"  />
                 </label>
				 <hr style="color:#F2F3F3; background-color:#F2F3F3">
				 <h2 style="margin-bottom:15px;"><?php esc_html_e('iOs touch icons', 'add-to-home-screen-wp'); ?></h2>
                 <label for="touchicon">
					<h3><?php esc_html_e('Touch icon', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span><?php esc_html_e('If checked, the script checks for link rel="apple-touch-icon" in the page HEAD and displays the application icon next to the message.', 'add-to-home-screen-wp'); ?></span>
					</div>
                    <input type="checkbox" name="touchicon" <?php checked( esc_attr(get_option('touchicon')) == 'on', true ); ?> />
                 </label>
                <label for="aths_touchicon_precomposed">
					<h3><?php esc_html_e('Precomposed icons', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span><?php esc_html_e('If checked, icons will display without the Apple gloss effect.', 'add-to-home-screen-wp'); ?></span>
					</div>
                    <input type="checkbox" name="aths_touchicon_precomposed" <?php checked( esc_attr(get_option('aths_touchicon_precomposed')) == 'on', true ); ?> />
                </label>
				<label style="margin-bottom:-5px;">
				<h3><?php esc_html_e('Touch icons URLs', 'add-to-home-screen-wp'); ?></h3>
				<div class="adhs_description_field">
				<span><?php esc_html_e('If mentionned, those fields add <i>link rel="apple-touch-icon"</i> in the page HEAD (convenient for those who have no touch icon). Just paste the URLs of your icons.', 'add-to-home-screen-wp'); ?></span>
				</div>
				</label>
				<label for="touchicon_url">
					<div class="adhs_description_field_touch">
						<span><?php esc_html_e('57x57 touch icon URL (for iPhone 3GS and 2011 iPod Touch).', 'add-to-home-screen-wp'); ?></span>
					</div>
					<input type="url" size="60" name="touchicon_url" value="<?php echo esc_url(get_option('touchicon_url')); ?>"  />
                </label>
				<label for="touchicon_url72">
					<div class="adhs_description_field_touch">
						<span><?php esc_html_e('72x72 touch icon URL (for 1st generation iPad, iPad 2 and iPad mini).', 'add-to-home-screen-wp'); ?></span>
					</div>
					<input type="url" size="60" name="touchicon_url72" value="<?php echo esc_url(get_option('touchicon_url72')); ?>"  />
                </label>
				<label for="touchicon_url114">
					<div class="adhs_description_field_touch">
						<span><?php esc_html_e('114x114 touch icon URL (for iPhone 4, 4S, 5 and 2012 iPod Touch).', 'add-to-home-screen-wp'); ?></span>
					</div>
					<input type="url" size="60" name="touchicon_url114" value="<?php echo esc_url(get_option('touchicon_url114')); ?>"  />
                </label>
				<label for="touchicon_url144">
					<div class="adhs_description_field_touch">
						<span><?php esc_html_e('144x144 touch icon URL (for iPad 3rd and 4th generation).', 'add-to-home-screen-wp'); ?></span>
					</div>
					<input type="url" size="60" name="touchicon_url144" value="<?php echo esc_url(get_option('touchicon_url144')); ?>"  />
                </label>
				
				<label for="addmetawebcapabletitle" style="margin-top:15px">
				<h3><?php esc_html_e('Title of your Web App', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span class="adhs_description_field"><?php esc_html_e('Type the name of your blog (max: 12 characters !). Default: it takes the default title of the page.', 'add-to-home-screen-wp'); ?></span>
					</div>
					 <input type="text" name="addmetawebcapabletitle" value="<?php echo esc_attr(get_option('addmetawebcapabletitle')); ?>"  />
                </label>
				<label for="pagetarget">
				<h3><?php esc_html_e('On which page the balloon should appear?', 'add-to-home-screen-wp'); ?></h3>
					<div class="adhs_description_field">
						<span class="adhs_description_field"><?php esc_html_e('Keep in mind that if someone adds your blog to home screen from a single article page for instance, the web app will load this page and not the homepage of the blog. That\'s why you could choose to open the floating balloon on homepage only and not on all pages of your blog.', 'add-to-home-screen-wp'); ?></span>
					</div>
					<select name="pagetarget" id="pagetarget">
						<option value="homeonly"<?php echo selected(esc_attr(get_option('pagetarget')), 'homeonly', false); ?>><?php esc_html_e('Home only', 'add-to-home-screen-wp'); ?></option>
						<option value="allpages"<?php echo selected(esc_attr(get_option('pagetarget')), 'allpages', false); ?>><?php esc_html_e('All pages', 'add-to-home-screen-wp'); ?></option>
					</select>
                </label>
             </td>
		   </tr>
		</tbody>
	</table>

	</form>
</div>