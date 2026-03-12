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

## Release Checklist

1. Update the plugin version in `simplicity-weather.php`.
2. Review `readme.txt` and update the changelog if needed.
3. Commit and push your changes to GitHub.
4. Create a tag like `v0.1.1` that matches the plugin version.
5. Publish a GitHub Release for that tag.
6. Confirm GitHub Actions uploads `simplicity-weather.zip` to the release.
7. Verify the WordPress site detects the new version in the Plugins screen.

## First Release

1. Initialize the repository if needed with `git init`.
2. Add the GitHub remote: `git remote add origin https://github.com/Jamezhall/simplicity-weather.git`
3. Commit the plugin files.
4. Push the default branch to GitHub.
5. Create and publish your first release.

## Notes

- Plugin data is preserved on uninstall by default.
- Enable `Cleanup on Uninstall` in settings if you want tables and options removed.
