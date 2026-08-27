=== AT Search Console ===
Contributors: adrianifero
Tags: seo, google search console, gsc, admin bar, performance
Requires at least: 6.0
Tested up to: 6.8
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

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the zip from Plugins → Add New.
2. Activate AT Search Console.
3. Visit any front-end page while logged in as an administrator. Use **View in Search Console** in the admin bar.
4. If GSC opens the wrong property, go to Settings → AT Search Console and switch property type.

== Frequently Asked Questions ==

= Do I need Google Search Console set up first? =

Yes. The site (or domain) must be a verified property in your Google account. If it is not, Google will ask you to verify when the link opens.

= Why does it open the wrong property? =

Your GSC property may be a domain property (`sc-domain:example.com`) while the plugin was set to URL prefix, or the other way around. Change it under Settings → AT Search Console.

= Does this replace a Search Console connection plugin? =

No. This only deep-links to GSC for the current URL.

== Changelog ==

= 1.1.0 =
* Fix broken metrics parameter in the GSC URL (stray `)`).
* Encode the page URL correctly for the performance filter.
* Use exact page match (`page=!…`) instead of a loose prefix.
* Add Settings for URL-prefix vs domain property.
* Show the admin bar link when editing a published post or page.
* Remove unused Yoast focus-keyword code path.
* Refresh readme for current WordPress.

= 1.0.0 =
* First version.

== Upgrade Notice ==

= 1.1.0 =
Fixes the Search Console link and adds a property-type setting. Update if the admin bar link mis-filters or opens the wrong property.
