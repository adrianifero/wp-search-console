# AT Search Console

Open the current page in Google Search Console from the WordPress admin bar. One click to that URL's performance.

**WordPress.org:** [AT Search Console](https://wordpress.org/plugins/at-search-console/)
**SVN:** `https://plugins.svn.wordpress.org/at-search-console`

Directory slug is `at-search-console`. This GitHub repo is `wp-search-console`. Deploy must pass `SLUG: at-search-console`.

## What it does

Admin bar → **View in Search Console** → Google Search Console performance for the current page URL.

## Install

Install from **Plugins → Add New**, or upload a zip whose top-level folder is `at-search-console`.

Keep one copy of the plugin. WordPress.org updates replace the `at-search-console` folder in place.

To build a release zip from this repository:

```bash
./bin/build-release-zip.sh
```

## WordPress.org updates

GitHub is not the directory. A version ships when a **git tag named exactly like the version** (`1.1.0`) is pushed, the Actions deploy job succeeds, and `trunk/readme.txt` Stable tag matches that tag.

Directory assets (banner, icon, screenshots) live in `.wordpress-org/`. They are not the same files as `img/` inside the plugin zip.

Secrets (once, on this repo): `SVN_USERNAME` and `SVN_PASSWORD` (WordPress.org username `adrianifero`, password or application password).

Playbook: `wordpress.org/PLAYBOOK.md` in [agent-wordpress-plugins](https://github.com/adrianifero/agent-wordpress-plugins).

## Changelog

See `readme.txt`.
