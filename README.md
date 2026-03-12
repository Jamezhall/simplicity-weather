# Simplicity Weather

Reusable WordPress plugin for pulling current weather data from specific geolocations using Open-Meteo.

Current version: `0.1.3`

## Features

- Current weather only for v1
- Custom admin pages for locations, status, settings, and logs
- Custom database tables for locations, cache, and logs
- Refresh-all tools, cron diagnostics, log retention, and log cleanup actions
- Shortcode output via `[simplicity_weather location="your-slug"]`
- Template helpers via `simplicity_weather_get()` and `simplicity_weather_render()`
- Built-in GitHub Releases updater for public repositories

## Setup

1. Install and activate the plugin.
2. Go to `Simplicity Weather > Locations` and add one or more locations.
3. Use the shortcode or template functions anywhere in your theme.

## Template Functions

```php
$weather = simplicity_weather_get( 'miami-office' );

echo simplicity_weather_render( 'miami-office' );
```

## GitHub Releases Updater

To deliver plugin updates through WordPress:

1. Create a GitHub release tag such as `v1.0.0`.
2. Attach an installable plugin zip named like `simplicity-weather.zip`.
3. Make sure the zip extracts to a single `simplicity-weather` plugin folder.

## Release Checklist

1. Update the plugin version in `simplicity-weather.php`.
2. Update `readme.txt` and `CHANGELOG.md`.
3. Commit and push your changes to GitHub.
4. Create a tag like `v0.1.3` that matches the plugin version.
5. Publish a GitHub Release for that tag.
6. Confirm GitHub Actions uploads `simplicity-weather.zip` to the release.
7. Verify the WordPress site detects the new version in the Plugins screen.

## Changelog

See `CHANGELOG.md` for release history.

## Notes

- Plugin data is preserved on uninstall by default.
- Enable `Cleanup on Uninstall` in settings if you want tables and options removed.
