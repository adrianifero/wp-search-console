# AT Search Console

Open the current page in Google Search Console from the WordPress admin bar. One click to that URL's performance.

**WordPress.org:** [AT Search Console](https://wordpress.org/plugins/at-search-console/)  
**Install folder (required):** `at-search-console`

## What it does

Admin bar → **View in Search Console** → Google Search Console performance for the current page URL.

## Install

Prefer **Plugins → Add New** from WordPress.org. That keeps the plugin in `wp-content/plugins/at-search-console/`, which is what directory updates replace.

If you upload a zip manually, the top-level folder inside the zip must be `at-search-console` (not the GitHub repo name). Otherwise WordPress installs a second plugin beside the first.

Build a correctly named zip from this repo:

```bash
./bin/build-release-zip.sh
```

## Upgrading from 1.0.1

Your saved property type migrates automatically. No reconfigure needed.

## Changelog

See `readme.txt`.
