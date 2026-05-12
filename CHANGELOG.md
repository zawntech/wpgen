# Changelog

## 2026-05-12

### Added
- `assets/scripts/build-zip.js` stub -- node script that reads the version header from the main plugin file and writes `{plugin-dir}-{version}.zip` to the parent directory, excluding `node_modules`, dotfiles, and dot-dirs. Uses `archiver` for streaming zip creation. Filename derives from `path.basename(pluginRoot)` so it stays correct if the plugin folder is renamed.
- `buildZip` npm script in the webpack `package.json` stub -- runs `npm run build && node assets/scripts/build-zip.js`.
- `archiver ^7.0.1` added to the webpack stub's dependencies.
- `assets/scripts/` directory now created by both `create:plugin` and `create:webpack`.

### Changed
- `create:plugin` and `create:webpack` both emit `assets/scripts/build-zip.js` (the stub lives under `stubs/webpack/` since it is bundled with the webpack package.json that declares its dependency and npm script).

## 2026-04-27

### Added
- `component:user-list-table` command -- generates `AbstractUserListTable` into `src/Abstract/` and a concrete `UserListTable.php` into the component, then registers it in the component constructor. Mirrors `component:post-type-list-table` for the wp-admin Users page (`users.php`).
- `AbstractUserListTable` stub -- hooks `manage_users_columns`, `manage_users_sortable_columns`, `manage_users_custom_column`, `pre_get_users`, and `restrict_manage_users`. Convention-based `render_{column}()` and `orderby_{column}()` methods, plus overridable `render_filters()` / `process_filters()` extension points. Includes a column whitelist guard (necessary because `manage_users_custom_column` is a single global filter, not post-type-scoped) and an `applies()` check against `?role=` for role-scoped customizations.
- `AbstractUserListTable::restrict_manage_users` wrapper closes core's `.alignleft.actions` div, renders filter UI in a sibling div, and reopens an empty one for core's trailing `</div>` to match -- yielding distinct inline-block groups so plugin filter UI doesn't visually merge with the "Change role" controls.
- `UserListTable.php` concrete stub -- demonstrates every extension point: two columns with renderers, one sortable column with `orderby_*`, role scoping (commented example), `unset_columns`, plus a working filter dropdown wired to a `meta_query` via `process_filters()`.
- `create:email` command -- generates an Emails subsystem: `src/Abstract/AbstractEmail.php`, `src/Emails/EmailsComponent.php`, `src/Emails/ExampleEmail.php`, `assets/templates/emails/_layout.php`, and `assets/templates/emails/example.php`. Registers `Emails\EmailsComponent` in the main plugin class.
- `AbstractEmail` stub -- transactional email base class with `subject()` and `template()` abstract methods, configurable `from_name()` / `from_address()` / `headers()`, and a `render()` flow that wraps the body template with `_layout.php`.
- Email layout stub uses a text-based site-name header (no logo image), neutral grey/blue palette, and generic placeholder copy so the developer customizes from a clean slate.
- `CreateEmailCommand` prints per-file `Created:` vs `Preserved (already exists):` so re-runs make it visible exactly which files were generated and which were left alone.

### Fixed
- `RegisterClassInConstructor::addToComponentConstructor` is now idempotent -- guards against appending duplicate `new $class;` lines when a `component:*` command is re-run. Mirrors the existing guard in `RegisterComponentInMainClass`. Affects every `component:*` command that registers a class in a component constructor.
- `AbstractPostTypeListTable::orderby` stub now also checks `is_string($orderby)` before calling `ltrim()` -- defends against `WP_Query` setting `orderby` to an array (e.g. multi-column ordering), which would otherwise trigger a TypeError.

## 2026-04-06

### Added
- `create:webpack` command — scaffolds `webpack.config.js`, `package.json`, and starter asset files (`assets/js/src/frontend/index.js`, `assets/js/src/admin/index.js`, `assets/sass/frontend.scss`, `assets/sass/admin.scss`, `assets/sass/_variables.scss`) for existing plugins.
- `create:plugin` now automatically scaffolds webpack configuration and asset directories alongside the plugin files.
- Webpack config auto-discovers `bb-modules/` SCSS files and compiles them in-place, with a `CleanBbModulesPlugin` that removes empty JS stubs and empty CSS files.
- Separate frontend and admin build entry points — outputs to `assets/build/{text-domain}.frontend.{js,css}` and `assets/build/{text-domain}.admin.{js,css}`.
- JS entry points structured as modules: `assets/js/src/frontend/index.js` and `assets/js/src/admin/index.js`.
- `EnqueueAssets` stub now wires up `wp_enqueue_style` and `wp_enqueue_script` for both frontend and admin webpack bundles.

### Changed
- `create:bb-module` no longer copies `build-scss.js`, `package.json`, or creates `assets/scripts/` — webpack handles SCSS compilation.
- `create:bb-module` now auto-derives `module_dir` (kebab-case) and `module_class` (PascalCase) from the module name, reducing prompts from 6 to 4.
- SCSS stubs use `@use` instead of deprecated `@import` syntax.
- `component:post-type-list-table` now infers the post type singular name from `*PostType.php` in the CWD, removing the `post_type_key` prompt.
- `component:post-type-list-table` now generates `AbstractPostTypeListTable` into `src/Abstract/` if it doesn't exist.
- `AbstractPostTypeListTable` refactored — `columns()`, `sortable_columns()`, and `column_content()` are now driven by `$columns`, `$unset_columns`, and `$sortable_columns` arrays with convention-based `render_{column}()` and `orderby_{column}()` methods instead of switch statements and commented-out examples.
- `AbstractPostType` default `map_meta_cap` changed from `false` to `true`.
- Concrete `PostTypeListTableFilter` stub now declares `$columns`, `$unset_columns`, and `$sortable_columns` property overrides.

## 2026-03-16

### Added
- `create:theme-component` command — generates `src/Theme/Theme.php` with static template-part resolution helpers and creates `assets/templates/` recursively. Uses `{{ plugin_filter_prefix }}` for the WP filter hook and debug CSS class, and `{{ plugin_text_domain }}` for the theme override path.
- `AbstractMetaBox` — generated into `src/Abstract/` by `component:meta-box`. Provides `POST_TYPES` constant, `stringy_keys`/`json_keys` arrays, `sanitize_string()`, constructor that registers `add_meta_boxes_*` and `save_post_*` hooks, and nonce helpers (`nonce_field`, `verify_nonce`, `get_nonce_key`, `get_nonce_action`).
- `AbstractMetaBox::render_meta_box()` calls overrideable `render()` then appends `nonce_field()` automatically — prevents accidental omission in subclasses.
- `component:meta-box` now scans the CWD for `*PostType.php` and infers `post_type_class` from the file containing `const KEY`. Shown as an info message; prompt is skipped when inferred.
- `component:meta-box` derives `meta_box_id` (snake_case) and `meta_box_class` (PascalCase) from the title — only the title needs to be entered at the prompt.
- Post type stubs refactored to use abstract base classes (`AbstractPostType`, `AbstractPostTypeModel`, `AbstractPostTypeListTable`) generated into `src/Abstract/`. Concrete classes generated by `component:post-type` now extend these.
- `AbstractPostType::validate()` — throws exception on construct if `KEY`, `SINGULAR`, or `PLURAL` constants are not defined.
- `component:post-type` now generates the list table filter by default. A `create_list_table` boolean option (default: yes) is shown in the confirmation prompt and can be toggled off.
- `admin-main-settings-tab.php` stub now wraps fields in `OptionsContainer` instead of a hard-coded `<table class="form-table">`.
- `AbstractPostType::get_args()` updated with missing `register_post_type` arguments: `show_in_rest`, `map_meta_cap`, `query_var`, `delete_with_user`.
- `AbstractPostType::get_labels()` updated with missing labels: `item_published`, `item_published_privately`, `item_reverted_to_draft`, `item_scheduled`, `item_updated`, `item_link`, `item_link_description`.
- `component:http-api` command — generates `AbstractHttpClient` into `src/Abstract/` and a concrete `{{ api_class }}Api` extending it with example `get_something()` and `post_something()` methods.
- `component:ajax-controller` command — generates `AbstractAjaxController` (with `verify_nonce_token()`) into `src/Abstract/` and a concrete `{{ controller_class }}AjaxController` with an example action. Auto-registers in component constructor.
- `component:taxonomy` refactored to abstract base class pattern — `AbstractTaxonomy` generated into `src/Abstract/`, concrete stub is a thin extension. Full `register_taxonomy` args and labels coverage including `back_to_items`.
- `create:admin` refactored — `AbstractSettings` and `AbstractAdminSettingsPageTab` now generated into `src/Abstract/`. `AdminSettingsPageContainer` moved to `src/Admin/`.

### Changed
- `meta-box.php` stub refactored to extend `AbstractMetaBox`. Now declares `POST_TYPES`, `stringy_keys`, `json_keys`, and overrides `render()` only.
- `post-type-model.php` stub — `POST_TYPE_KEY` constant now references `PostType::KEY` instead of a string literal.
- `post-type-list-table.php` stub — `$post_types` array now references `PostType::KEY` instead of a string literal.
- `AdminSettingsPageContainer` stub — added missing `use AbstractAdminSettingsPageTab` import; `filter_admin_submenu` result is now passed through `apply_filters( '{prefix}admin_settings_submenu', $items, $slug )`.

### Removed
- `create:api` and `create:api-resource` commands and all associated stubs removed.
- `post_type_slug` config option marked as optional — can be left empty at the CLI prompt.

### Fixed
- Stub `composer.json` updated to require `allegedwizard/wp-admin-options` from Packagist.
- Replaced deprecated `FILTER_SANITIZE_STRING` with `sanitize_text_field( wp_unslash( ... ) )` in `admin-settings-page-tab-abstract.php` and `meta-box.php` stubs (deprecated in PHP 8.1).
- `AbstractPostType::get_args()` corrected `capability_type` default from `'page'` to `'post'`.

## 2026-03-04

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
- `AbstractSettings` base class for `Settings` — validates `OPTION_KEY` and `PREFIX` constants, supports `$encrypted_keys` with AES-256-CBC encrypt/decrypt via configurable `ENCRYPTION_CONSTANT`.
- `Admin/Abstract/` directory — `AdminSettingsPageContainer`, `AdminSettingsPageTabAbstract`, and `AbstractSettings` moved into dedicated abstract namespace.

### Fixed
- `AdminSettingsPageTabAbstract` stub missing `use Settings` import after move to `Admin\Abstract` namespace — caused fatal error on `Settings::get()` calls.

### Changed
- `composer.json` type changed from `library` to `project`, added `MIT` license, `php ^8.1` requirement, and `bin` entry for `wpgen`.
- `wpgen` entry point searches multiple autoloader paths to support global Composer installs.
- `QueryOptions::queryOptions()` skips inferred options and options that already have a value from defaults. Also syncs values to `$this->options` during iteration so conditional `if` checks work within the same query pass.
- `EnqueueAssets` stub simplified — removed third-party dependencies (select2, vue) and `register` method. Added `$allow_debug_assets` property and `get_plugin_version()` method that returns `time()` in debug mode.
- Stub `composer.json` updated `wp-admin-options` branch to `dev-dev`.
- `AdminSettingsPageContainer` constructor now accepts an options array (`title`, `slug`, `parent_slug`, `capability`, `tabs`) instead of hardcoded properties.
- `AdminSettingsPageTabAbstract` simplified — removed hook-based registration; the container now manages tabs directly via its `$tabs` array.
- `AdminSettingsPageTabAbstract::print_admin_notice()` now uses `{{ plugin_text_domain }}` instead of a hardcoded text domain.
- `QueryOptions` boolean prompt now displays the option label instead of generic "Is everything correct?" text.
- Fixed PHP 8.1+ `trim(null)` deprecation in `QueryOptions::validateValue()`.
- `QueryOptions::showSelectedOptionValues()` renders boolean values as "Yes"/"No" and casts all values to string.
- `QueryOptions::mergeOptions()` uses `array_key_exists` to preserve `false` values.

### Removed
- Filter/action hook plumbing (`admin_settings_page_tabs`, `admin_settings_page_render_tab`, etc.) from tab abstract — replaced by direct tab management in the container.
