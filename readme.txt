=== AT Search Console ===
Contributors: adrianifero
Tags: seo, google search console, gsc, admin bar, performance
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 1.2.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Open the current page in Google Search Console from the WordPress admin bar. One click. No copy-paste.

== Description ==

You are looking at a page in WordPress. You want that same URL in Google Search Console performance. Without this plugin you copy the link, open GSC, find the page filter, paste.

AT Search Console adds **View in Search Console** to the WordPress admin bar. One click opens that URL’s clicks, impressions, CTR, and position in GSC.

On desktop, the link appears in the admin bar like other plugins. On phones and tablets, WordPress hides custom top-level admin bar items, so the same link also appears under your **site name** menu on the front end (and under the **pencil** menu when editing a post).

It does not install a tracking tag. It does not pull analytics into WordPress. It opens the right GSC screen for the page you are on.

= Features =

* **View in Search Console** in the admin bar on the front end (top-level on desktop)
* Same link when editing a post or page in wp-admin (including drafts)
* Mobile-friendly duplicate under the site name or pencil menu where WordPress allows it
* Simple settings: pick your property, test it, save — custom property hidden under **Advanced »**
* Works for users with the `manage_options` capability
* Page filter uses “URLs containing” so UTM and other query variants still match

== Installation ==

1. Install from Plugins → Add New (search “AT Search Console”), or upload a zip whose top-level folder is `at-search-console`.
2. Activate AT Search Console.
3. Go to Settings → AT Search Console. Pick the property that matches Search Console, click **Test this property**, then save.
4. Visit any front-end page while logged in as an administrator. Click **View in Search Console** in the admin bar.

Keep a single copy of the plugin. If two “AT Search Console” rows appear under Plugins, deactivate and delete the extra folder.

== Frequently Asked Questions ==

= Where is View in Search Console on mobile? =

WordPress does not show custom top-level admin bar links on narrow screens. On mobile, open your **site name** menu at the top left of the admin bar. **View in Search Console** is listed there. When editing a post, use the **pencil (Edit)** menu instead.

= Do I need Google Search Console set up first? =

Yes. The site (or domain) must be a verified property in your Google account. If it is not, Google will ask you to verify when the link opens.

= Why does it say I do not have access to this property? =

The property selected in Settings does not match a property you verified in Search Console. Open Search Console, check the property list, pick the same entry under Settings → AT Search Console, and use **Test this property** before using the admin bar link.

= Why do I see two AT Search Console plugins? =

Each folder under `wp-content/plugins/` is a separate plugin. Keep one install in the `at-search-console` folder. WordPress.org updates replace that folder in place.

= Does this replace a Search Console connection plugin? =

No. This only deep-links to GSC for the current URL.

== Screenshots ==

1. View in Search Console in the WordPress admin bar.

== Changelog ==

= 1.2.2 =
* Restore top-level **View in Search Console** on desktop (same placement as 1.2.0).
* Mobile: keep the link available under the site name or pencil menu (WordPress standard workaround for custom admin bar items).
* Settings: hide custom property field behind **Advanced »** for a simpler default screen.

= 1.2.1 =
* **View in Search Console** lived under the site name menu on the front end and under the pencil menu in the post editor (WordPress hides custom top-level admin bar items, including on mobile).
* Admin bar link when editing draft, pending, or scheduled posts (uses the preview URL for the page filter).

= 1.2.0 =
* Property picker: choose the exact Search Console property (domain or URL prefix) instead of abstract type radios.
* **Test this property** on settings to verify access before using the admin bar; updates live when you change the dropdown (no save required to test).
* First-run setup notice; reload reminder after saving settings.
* Domain properties use path-based page filters; 16-month date range on performance links.
* Tested up to WordPress 7.1.

= 1.1.0 =
* Rebuild: encoded URLs, fixed metrics parameter, admin-bar link when editing a published post or page.
* Settings for URL-prefix vs domain Search Console property, with migration from the old option.
* Page filter uses URLs containing so UTM and other query variants still match.
* Warn if two copies of the plugin are active, or if the folder is not `at-search-console`.
* Settings screen with icon and screenshot.

= 1.0.1 =
* Select between a domain property or a regular property.

= 1.0.0 =
* First version.

== Upgrade Notice ==

= 1.2.2 =
Restores the desktop admin bar link placement from 1.2.0 with a proper mobile fallback. Simpler settings screen. Recommended update from 1.2.1.

= 1.2.1 =
Correct admin bar menu placement (site name / pencil menus), draft post support. Superseded by 1.2.2 for desktop placement.

= 1.2.0 =
Easier property setup with a picker and test link. Existing domain/URL-prefix choices migrate automatically. Update from 1.1.0.
