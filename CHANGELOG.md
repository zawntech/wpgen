# Changelog

## [1.1.0] - 2026-03-04

### Added
- `wpgen config` command — interactive walkthrough to set global default values (author, URL, email, etc.) that pre-populate during `create:plugin`. Defaults stored in `defaults.json` alongside the wpgen executable.
- Global defaults loading in `LoadOptions` — priority order: empty values → `defaults.json` → project `wpgen.config.json`.
- `composer_vendor_name` option — sets the composer package vendor name, used to auto-generate `composer_package_name`.
- Plugin identifier inference in `CreatePluginCommand` — automatically derives text domain, namespace, constants prefix, main class, filter prefix, and composer package name from the plugin name.
- `inferred` flag on plugin options — marks fields auto-derived from the plugin name (skipped during interactive prompts).
- `plugin_specific` flag on plugin options — marks fields excluded from global defaults (plugin name, description).
- `create:plugin` now runs `composer install` automatically in the new plugin directory and displays a `cd` hint on completion.
- `symfony/process` dependency for running shell commands.
- `create:admin` now prompts for settings page name, top-level vs sub-level placement, and parent menu page using `config/admin-options.php`.
- Settings page namespace derived from page name — tabs are generated under `Tabs\{PascalCaseName}\` (e.g., `Tabs\TppGroups\MainSettingsTab`).
- `create:admin` can be run multiple times to register additional settings pages. Subsequent runs append a new `AdminSettingsPageContainer` block to `AdminComponent::init()` and generate only the new tab files.
- Top-level admin pages automatically register tabs as submenu items via `$submenu` global, filterable with `{prefix}admin_settings_tabs`.

### Changed
- `QueryOptions::queryOptions()` skips inferred options and options that already have a value from defaults. Also syncs values to `$this->options` during iteration so conditional `if` checks work within the same query pass.
- `EnqueueAssets` stub simplified — removed third-party dependencies (select2, vue) and `register` method. Added `$allow_debug_assets` property and `get_plugin_version()` method that returns `time()` in debug mode.
- Stub `composer.json` updated `wp-admin-options` branch to `dev-dev`.
- `AdminSettingsPageContainer` constructor now accepts an options array (`title`, `slug`, `parent_slug`, `capability`, `tabs`) instead of hardcoded properties.
- `AdminSettingsPageTabAbstract` simplified — removed hook-based registration; the container now manages tabs directly via its `$tabs` array.
- `AdminSettingsPageTabAbstract::print_admin_notice()` now uses `{{ plugin_text_domain }}` instead of a hardcoded text domain.

### Removed
- Filter/action hook plumbing (`admin_settings_page_tabs`, `admin_settings_page_render_tab`, etc.) from tab abstract — replaced by direct tab management in the container.
