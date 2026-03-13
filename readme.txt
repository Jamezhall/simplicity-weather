=== Simplicity Weather ===
Contributors: Jamezhall
Tags: weather, shortcode, geolocation, open-meteo, github-updater
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.1.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pull current weather data for specific geolocations using Open-Meteo, with custom admin management, shortcode output, template functions, and GitHub-based plugin updates.

== Description ==

Simplicity Weather is a reusable WordPress plugin for pulling current weather data from saved latitude/longitude locations.

Features include:

* Current weather data powered by Open-Meteo
* Custom admin pages for locations, status, settings, and logs
* Custom database tables for locations, cache, and logs
* Refresh-all tools, cron diagnostics, and log retention controls
* Shortcode output with `[simplicity_weather location="your-slug"]`
* Selective shortcode fields and plain text output mode
* AJAX badge mode with skeleton loading for cached pages
* Global badge font sizing and before-text support for text-based output
* PHP template helpers for themes
* Built-in GitHub Releases updater for public repositories

This version focuses on current weather only for a clean, maintainable v1 foundation.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the zip through WordPress.
2. Activate the plugin through the `Plugins` screen.
3. Go to `Simplicity Weather > Locations` and add one or more saved locations.
4. Use the shortcode or template functions where needed.

== Frequently Asked Questions ==

= How do I display a location? =

Use the shortcode:

`[simplicity_weather location="miami-office"]`

Or use the template function:

`echo simplicity_weather_render( 'miami-office' );`

= Does the plugin call the weather API on every page load? =

No. Weather data is cached in the database and refreshed by WP-Cron or manual admin refresh.

= How do updates work? =

The plugin checks the latest public GitHub Release for `Jamezhall/simplicity-weather`. Attach an installable zip asset to each release for WordPress updates to work.

== Changelog ==

= 0.1.7 =

* Fixed AJAX badge whitespace handling so regular spaces work correctly in `before` text and separators

= 0.1.6 =

* Added global badge font-size control
* Added `before` text support for plain text shortcode output and AJAX badge mode
* Improved badge skeleton visibility on light backgrounds

= 0.1.5 =

* Added AJAX badge shortcode mode for cached pages and Elementor-style layouts
* Added a pill-shaped skeleton loader while badge data is loading
* Added global badge appearance settings for text color, background, font, padding, and border radius
* Added frontend JS and CSS assets for badge hydration and loading animation

= 0.1.4 =

* Added shortcode field selection for location, temp, condition, wind, and updated values
* Added plain text shortcode output with configurable separators
* Added shortcode and template usage examples beside the Locations admin form

= 0.1.3 =

* Added a Refresh All Locations action and status diagnostics for cron health and next run timing
* Added a Logs admin page with filtering, prune-old, and clear-all tools
* Added configurable log retention with 30 days as the default
* Switched the location timezone field to a dropdown and defaulted new locations to Europe/London
* Removed GitHub repository editing from the settings page

= 0.1.2 =

* Improved the plugin details modal to load the description from `readme.txt`
* Kept changelog content sourced from GitHub Release notes for update-specific messaging

= 0.1.1 =

* Added GitHub Actions CI and release workflows
* Added repository housekeeping with a project gitignore
* Added release checklist documentation and changelog tracking

= 0.1.0 =

* Initial plugin scaffold
* Open-Meteo current weather provider
* Custom admin pages for locations, weather status, and settings
* Shortcode and template function rendering
* Custom database tables for locations, cache, and logs
* Built-in GitHub Releases updater

== Upgrade Notice ==

= 0.1.7 =

Fixes AJAX badge spacing so normal spaces work in `before` text and separators.

= 0.1.6 =

Adds badge font-size control, before-text support, and a more visible loader on light backgrounds.

= 0.1.5 =

Adds a cached-page AJAX badge mode with skeleton loading and global badge styling controls.

= 0.1.4 =

Adds selective shortcode fields, plain text output mode, and built-in usage examples in wp-admin.

= 0.1.3 =

Adds log management, diagnostics, bulk refresh tools, and improved timezone handling.

= 0.1.2 =

Improves the plugin details modal by showing the readme description alongside GitHub release changelog notes.

= 0.1.1 =

Adds GitHub release automation and release documentation polish.

= 0.1.0 =

Initial release.
