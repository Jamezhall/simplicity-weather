# Simplicity Weather

Reusable WordPress plugin for pulling current weather data from specific geolocations using Open-Meteo.

## Features

- Current weather only for v1
- Custom admin pages for locations, status, and settings
- Custom database tables for locations, cache, and logs
- Shortcode output via `[simplicity_weather location="your-slug"]`
- Template helpers via `simplicity_weather_get()` and `simplicity_weather_render()`
- Built-in GitHub Releases updater for public repositories

## Setup

1. Install and activate the plugin.
2. Go to `Simplicity Weather > Settings` and set your public GitHub repository as `owner/repo`.
3. Go to `Simplicity Weather > Locations` and add one or more locations.
4. Use the shortcode or template functions anywhere in your theme.

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

## Notes

- Plugin data is preserved on uninstall by default.
- Enable `Cleanup on Uninstall` in settings if you want tables and options removed.
