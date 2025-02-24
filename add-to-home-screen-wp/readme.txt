=== Add to Home Screen WP ===
Contributors: tulipwork
Donate link: https://tulipemedia.com
Tags: iPhone, iOs, homescreen, webapp, PWA, push notifications
Requires at least: 6.7
Tested up to: 6.7
Stable tag: 2.6.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add to HomeScreen WordPress plugin invites your readers to add your blog as an icon / web app on the home screen of their iPhone, iPad, and iPod touch. Premium features unlock PWA support, Add to home screen button for Android users, loading indicators and other customization options.

== Description ==

This plugin uses [Add to Home Screen's Cubiq script](https://github.com/cubiq/add-to-homescreen "Add to home screen") to place a floating balloon inviting the user to add a website to their home screen as a standard iOS application.

It's a good way to retain visitors coming to your blog, especially if you don’t want to develop an expensive native application just to let them read your WordPress blog articles.

The floating balloon is compatible with all recent iPhones and iPads.

<strong>2025 UPDATE: The Add to Home Screen WP Plugin is back online, fully compliant with WordPress requirements, and now offers premium features!</strong>

### Free Features
- Floating balloon to invite users to add your blog to their iOS home screen.
- Customizable message, animations, delays, and touch icon.
- Compatible with iPhones, iPads, and iPod Touch.

### Premium Features

Unlock advanced features with a purchase at [tulipemedia.com](https://tulipemedia.com/en/product/aths-wordpress-premium/):

* *PWA Support*: Transform your blog into a Progressive Web App.
* *Android Install Prompt*: Display a custom "Add to Home Screen" button for Android users.
* *Loading Indicator*: Add a sleek spinner for smooth page transitions.
* *Customization Options*: Tailor your app with color settings and force the web app to launch on the homepage.

The free version adds the floating balloon, while the premium version enhances your blog with these powerful PWA features and more.

= Demo =
[Watch the Demo Video](https://www.youtube.com/watch?v=Bd4i4k_DhE4)

[Check a demo on my blog](https://tulipemedia.com) (load it on an iPhone).

[Read more and documentation](https://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/)

= Internationalisation =
This plugin supports translations. Help translate it by [notifying me in comments](https://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/) and I’ll add your mo/po files.

Available languages: English, French, German. Thanks to [Julian](https://profiles.wordpress.org/h3p315t05) for the German translation!

= Features =
*Free Features (Cubiq Script):*
- Custom message with HTML support.
- Animation: drop, bubble, or fade.
- Delays: start delay and lifespan.
- Expire: minutes before showing again.
- Touch icon support.
- Show to returning visitors only.

*Premium Features:*
- PWA Support.
- Android Install Prompt.
- Loading Indicator.
- Color Option.
- Force the web app to launch on the homepage.

= Follow me =
Keep in touch on:
- [Instagram](https://instagram.com/ziyadbachalany/)
- [LinkedIn](https://www.linkedin.com/in/ziyadbachalany/)
- [Twitter](https://twitter.com/ziyadbachalany)
- [Facebook](https://www.facebook.com/ziyadbachalany)
- [Tulipemedia](https://tulipemedia.com/en/add-to-home-screen-wordpress-plugin/)

== Installation ==
1. Upload the `add-to-home-screen-wp` folder to `/wp-content/plugins/` or install via the WordPress dashboard.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Settings > ATHS Options** for settings.

== Frequently Asked Questions ==
= If a visitor adds my blog to its home screen, will the balloon continue to appear? =
In the free version, the balloon may still appear when opening your blog in Safari, even after adding it to the homescreen. With the premium version, this behavior is prevented—once added to the homescreen, the balloon won’t load, ensuring a seamless experience. Alternatively, in the free version, you can set a long expire timeframe (e.g., one month or one year) in the options page to reduce its frequency.

= I made changes on options page but nothing's changed when I load my blog? =

Try to:

* Clear your cache if you're using a cache plugin
* Clear your Safari cache/cookies.
* Reboot Safari: on iPhone for instance, you have to double-click on the home button, then press Safari button several seconds and click on the close button.

= The blog title on my icon is cut? =

Application names on the home screen are limited to a <strong>maximum of 12 characters</strong>, so anything beyond that will be truncated. Try to keep the title of your application under 13 characters on the iPhone if you want to prevent it from being cut off. Fortunately, there is an option in the plugin to customize your application title, it can be very useful especially if the title of your blog is too long.

= Can I custom the text that will appear in the floating balloon? =

Of course, and it's highly suggested! You can add link, bold text and also add emojis to gamify your floating ballon and increase conversions. See screeenshots and videos to see examples.

= Can I translate the message that appears in the floating ballon? =

Yes sir! With WPML, download and install the "String translation" add-on, scroll down, click on "Translate texts in admin screens" and look for "Message" field with the search tool of your browser, you should find the field containing your custom message. Just check the left box and save settings. Now come back to String translation and you will find your custom message, ready to be translated!

= How do I activate premium features? =
Purchase a subscription at [tulipemedia.com](https://tulipemedia.com/en/product/aths-wordpress-premium/), enter your license key in **Settings > PWA Premium**, and unlock premium features.

== Screenshots ==
1. Example of a nice custom floating balloon
2. Example of the English floating balloon
3. Example of the French floating balloon
4. Free Plugin Option page
5. Premium PWA Settings page


== Changelog ==
= 2.6.3 =
* Changed: Replaced embedded YouTube demo video with a clickable link in readme.txt for reliability.

= 2.6.2 =
* Fixed: YouTube demo video not displaying on plugin page, now embedded correctly.

= 2.6.1 =
* Fixed: "Enable ATHS Premium Features" checkbox now saves correctly when unchecked.

= 2.6 =
* Improved: free option page settings
* Added: License key system to unlock premium features.
* Improved: Updated plugin structure for freemium model.

= 2.5 =
* Added: Simplified touch icon support to a single 180x180 size for modern iOS devices.
* Removed: Deprecated touch icon sizes (57x57, 72x72, 114x114, 144x144) from options.
* Updated: Replaced Universal Analytics tracking with Google Analytics 4 compatibility.
* Improved: Renamed main plugin file from "index.php" to "add-to-home-screen-wp.php" to follow WordPress conventions.
* Version 2.6 simplifies touch icon support to a single 180x180 size and updates tracking to Google Analytics 4.

= 2.4 =
Minor fix.

= 2.3 =
Minor fix.

= 2.2 =
Support for HTML formatting in the custom message balloon for richer styling.

= 2.1 =
Major fix.

= 2 =
Major fix.

= 1.9 =
Minor fix.

= 1.8 =
Minor fix.

= 1.7 =
Minor fix, tested up to WordPress 5.4.

= 1.6 =
Little  fix.

= 1.5 =
Little  fix.

= 1.4 =
New version tested up to WordPress 5.2.

= 1.3 =
Bugs fixed.

= 1.2 =
New version tested up to 4.8.1.
Some old functionnalities have been removed.

= 1.1 =
Fix for the iOS 7 web app status bar.
German translation added.

= 1.0 =
Floating balloon updated for iOS 7.

= 0.9 =
New home screen icons and startup screens for all iOs devices (ipad, iPhone 5, etc...).

= 0.8 =
Fix bug with "homepage only or all pages" option.
Some little performance improvements.

= 0.7 =
Improvement of the bottom navigation bar on Web App: added forward and reload buttons.
Allow using Safari mode in Web App.
Fix bug with the "returningVisitor" function.

= 0.6 =
Improvement CSS of the Web App.
Allow opening the balloon on homepage only or all pages.

= 0.5 =
Touch startup image that is displayed while the web application launches.
Prevent links switching to Safari browser.
Add navigation bar (back button) in the Web App.

= 0.4 =
Allow customizing Web App Title.

= 0.3 =
Ability to use device and icon tags when customizing the message.
Allow using apostrophe in custom message.

= 0.2 =
Display title of the page.

= 0.1 =
First version of the plugin.

== Upgrade Notice ==
= 2.6 =
Version 2.6 is improving the setting page and introducing new premium features.

= 2.5 =
* Added: Simplified touch icon support to a single 180x180 size for modern iOS devices.
* Removed: Deprecated touch icon sizes (57x57, 72x72, 114x114, 144x144) from options.
* Updated: Replaced Universal Analytics tracking with Google Analytics 4 compatibility.
* Improved: Renamed main plugin file from "index.php" to "add-to-home-screen-wp.php" to follow WordPress conventions.

= 2.4 =
Minor fix.

= 2.3 =
Minor fix.

= 2.2 =
Support for HTML formatting in the custom message balloon for richer styling.

= 1.2 =
New version tested up to 4.8.1.
Some old functionalities have been removed.

= 1.1 =
Fix for the iOS 7 web app status bar.
German translation added.

= 1.0 =
Floating balloon updated for iOS 7.

= 0.9 =
New home screen icons and startup screens for all iOs devices (ipad, iPhone 5, etc...).

= 0.8 =
Fix bug with "homepage only or all pages" option.
Some little performance improvements.

= 0.7 =
Improvement of the bottom navigation bar on Web App with forward and reload buttons.
Allow using Safari mode in Web App.
Fix bug with the "returningVisitor" function.

= 0.6 =
Improvement CSS of the Web App.
Allow opening the balloon on homepage only or all pages.

= 0.5 =
Touch startup image that is displayed while the web application launches.
Prevent links switching to Safari browser.
Add navigation bar (back and forward buttons) in the Web App.

= 0.4 =
Allow customizing Web App Title.

= 0.3 =
Allow using device and icon tags when customizing the message.
Function addslashes added to allow using apostrophe in the custom message field.

= 0.2 =
Retrieve the wp_title function to display real page title below the home screen icon.

= 0.1 =
First version of the plugin.

== Credits ==
This plugin has been written by [Ziyad Bachalany](https://tulipemedia.com) and uses the [Add to Home Screen Floating Layer script](https://github.com/cubiq/add-to-homescreen) by Matteo Spinelli that is released under the MIT License (see below).

## License

This software is released under the MIT License.

Copyright (c) 2013 Matteo Spinelli, https://cubiq.org/

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