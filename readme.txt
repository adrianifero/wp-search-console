=== AT Search Console ===
Contributors: adrianifero
Tags: seo, google search console, gsc, admin bar, performance
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Open the current page in Google Search Console from the WordPress admin bar. One click. No copy-paste.

== Description ==

You are looking at a page in WordPress. You want that same URL in Google Search Console performance. Without this plugin you copy the link, open GSC, find the page filter, paste.

AT Search Console adds **View in Search Console** to the admin bar. One click opens that URL’s clicks, impressions, CTR, and position in GSC.

It does not install a tracking tag. It does not pull analytics into WordPress. It opens the right GSC screen for the page you are on.

= Features =

* Admin bar link on the front end for the current page
* Same link when editing a published post or page in wp-admin
* Works for users with the `manage_options` capability
* Setting for URL-prefix vs domain Search Console property
* Page filter uses “URLs containing” so UTM and other query variants still match

== Installation ==

1. Install from Plugins → Add New (search “AT Search Console”), or upload a zip whose top-level folder is `at-search-console`.
2. Activate AT Search Console.
3. Visit any front-end page while logged in as an administrator. Use **View in Search Console** in the admin bar.
4. If GSC opens the wrong property, go to Settings → AT Search Console and switch property type.

Keep a single copy of the plugin. If two “AT Search Console” rows appear under Plugins, deactivate and delete the extra folder.

== Frequently Asked Questions ==

= Do I need Google Search Console set up first? =

Yes. The site (or domain) must be a verified property in your Google account. If it is not, Google will ask you to verify when the link opens.

= Why does it open the wrong property? =

Your GSC property may be a domain property (`sc-domain:example.com`) while the plugin was set to URL prefix, or the other way around. Change it under Settings → AT Search Console.

= Why do I see two AT Search Console plugins? =

Each folder under `wp-content/plugins/` is a separate plugin. Keep one install in the `at-search-console` folder. WordPress.org updates replace that folder in place.

= Does this replace a Search Console connection plugin? =

No. This only deep-links to GSC for the current URL.

== Screenshots ==

1. View in Search Console in the WordPress admin bar.

== Changelog ==

= 1.1.0 =
* Rebuild: encoded URLs, fixed metrics parameter, admin-bar link when editing a published post or page.
* Settings for URL-prefix vs domain Search Console property, with migration from the old option.
* Page filter uses URLs containing so UTM and other query variants still match.
* Warn if two copies of the plugin are active, or if the folder is not `at-search-console`.
* Settings screen with icon and screenshot.
* Declares compatibility with WordPress 7.1.

= 1.0.1 =
* Select between a domain property or a regular property.

= 1.0.0 =
* First version.

== Upgrade Notice ==

= 1.1.0 =
Fixes the Search Console link, migrates the old property setting, and adds the admin-bar link when editing a published page. Update from 1.0.1.
