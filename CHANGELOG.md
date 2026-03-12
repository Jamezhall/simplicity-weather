# Changelog

All notable changes to this project will be documented in this file.

## [0.1.3] - 2026-03-12

- Added a `Refresh All Locations` admin action.
- Added cron diagnostics, next-run visibility, and richer weather status metadata.
- Added a `Logs` admin page with filters plus prune/clear tools.
- Added configurable log retention with a default of 30 days.
- Changed the timezone field to a dropdown and defaulted new locations to `Europe/London`.
- Removed GitHub repository editing from the settings page.

## [0.1.2] - 2026-03-12

- Updated the plugin details modal description to load from `readme.txt`.
- Kept the modal changelog sourced from GitHub Release notes.

## [0.1.1] - 2026-03-12

- Added GitHub Actions CI workflow for PHP linting on pushes and pull requests.
- Added GitHub Actions release workflow to lint, package, and upload `simplicity-weather.zip` to GitHub Releases.
- Added `.gitignore` for local/editor/build artifacts.
- Added release process documentation updates in `README.md`.

## [0.1.0] - 2026-03-12

- Initial plugin scaffold.
- Added Open-Meteo current weather integration.
- Added custom database tables for locations, cache, and logs.
- Added admin pages for locations, weather status, and settings.
- Added shortcode and template function rendering.
- Added built-in GitHub Releases updater.
