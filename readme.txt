=== Simplicity Weather ===
Contributors: Jamezhall
Tags: weather, shortcode, geolocation, open-meteo, github-updater
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pull current weather data for specific geolocations using Open-Meteo, with custom admin management, shortcode output, template functions, and GitHub-based plugin updates.

== Description ==

Simplicity Weather is a reusable WordPress plugin for pulling current weather data from saved latitude/longitude locations.

Features include:

* Current weather data powered by Open-Meteo
* Custom admin pages for locations, status, and settings
* Custom database tables for locations, cache, and logs
* Shortcode output with `[simplicity_weather location="your-slug"]`
* PHP template helpers for themes
* Built-in GitHub Releases updater for public repositories

This version focuses on current weather only for a clean, maintainable v1 foundation.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the zip through WordPress.
2. Activate the plugin through the `Plugins` screen.
3. Go to `Simplicity Weather > Settings` and confirm the GitHub repository value.
4. Go to `Simplicity Weather > Locations` and add one or more saved locations.
5. Use the shortcode or template functions where needed.

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

= 0.1.0 =

* Initial plugin scaffold
* Open-Meteo current weather provider
* Custom admin pages for locations, weather status, and settings
* Shortcode and template function rendering
* Custom database tables for locations, cache, and logs
* Built-in GitHub Releases updater

== Upgrade Notice ==

= 0.1.0 =

Initial release.
