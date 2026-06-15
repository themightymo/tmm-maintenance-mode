# TMM Maintenance Mode — AI Development Guide

This file provides context for AI-assisted development of this WordPress plugin. It is self-contained and does not assume prior conversation history.

---

## Plugin Identity

- **Plugin name:** Maintenance Mode by The Mighty Mo! Design Co.
- **Plugin slug:** `tmm-maintenance-mode`
- **Main file:** `tmm-maintenance-mode.php`
- **Plugin file constant:** `tmm-maintenance-mode/tmm-maintenance-mode.php` (value of `TMM_MAINTENANCE_MODE_PLUGIN_FILE`)
- **GitHub repo:** `themightymo/tmm-maintenance-mode` (branch: `master`)
- **Admin menu slug:** `tmm-maintenance-mode`
- **Admin page URL:** `/wp-admin/admin.php?page=tmm-maintenance-mode`

---

## File Structure

```
tmm-maintenance-mode/
├── tmm-maintenance-mode.php   # All core logic, hooks, admin UI
├── includes/
│   ├── constants.php          # Defines plugin-wide constants
│   └── github-updater.php     # GitHub-based auto-update logic
├── media-uploader.js          # JS for the WP media uploader on the settings page
└── agents.md                  # This file
```

---

## Constants (`includes/constants.php`)

| Constant | Value |
|---|---|
| `TMM_MAINTENANCE_MODE_PLUGIN_FILE` | `tmm-maintenance-mode/tmm-maintenance-mode.php` |
| `TMM_MAINTENANCE_MODE_GITHUB_REPO` | `themightymo/tmm-maintenance-mode` |
| `TMM_MAINTENANCE_MODE_GITHUB_BRANCH` | `master` |
| `TMM_MAINTENANCE_MODE_GITHUB_CACHE_KEY` | `tmm_maintenance_mode_github_data` |

---

## Settings

All settings are stored in a single WordPress option: `get_option('tmm_settings')` (an associative array).

| Key | Type | Purpose |
|---|---|---|
| `tmm_checkbox` | `1` or unset | Whether maintenance mode is enabled |
| `tmm_image` | URL string | Image shown on the maintenance page |
| `tmm_text` | string | Message shown on the maintenance page |
| `tmm_bar_color` | hex color string | Background color of the admin bar |

Settings are registered under the group `tmm_settings_group` and the settings page `tmm-maintenance-mode`.

Helper functions:
- `tmm_is_maintenance_mode_enabled()` — returns bool
- `tmm_get_maintenance_image()` — returns URL (falls back to bundled logo)
- `tmm_get_maintenance_text()` — returns string (falls back to default message)
- `tmm_get_admin_bar_color()` — returns hex color (falls back to `#ff0000`)

---

## GitHub Auto-Updater (`includes/github-updater.php`)

Fetches the remote plugin version from GitHub and integrates with WordPress's built-in plugin update system.

- **Cache transient:** `tmm_maintenance_mode_github_data` (site transient, 6-hour TTL)
- **Remote URL checked:** `https://raw.githubusercontent.com/themightymo/tmm-maintenance-mode/master/tmm-maintenance-mode.php`
- **Update package:** `https://github.com/themightymo/tmm-maintenance-mode/archive/refs/heads/master.zip`
- Hooks into `pre_set_site_transient_update_plugins` to inject update data.
- Hooks into `upgrader_source_selection` to rename the extracted zip folder to the correct plugin slug.
- Cache is auto-cleared on `upgrader_process_complete`.

### Clearing the Update Cache

There are two ways to clear the cache, both calling `tmm_clear_github_update_cache()` which deletes the site transient, plus `delete_site_transient('update_plugins')` to force WordPress to re-check.

1. **Settings page (POST):** Form on the plugin settings page posts `tmm_clear_github_cache=1` with nonce action `tmm_clear_github_cache_nonce`. Handled by `tmm_handle_clear_cache_action()` hooked to `admin_init`.

2. **Plugins list page (GET):** A "Clear Update Cache" link is injected into the plugin row via `plugin_action_links_tmm-maintenance-mode/tmm-maintenance-mode.php`. The link is a nonce URL pointing to `plugins.php?tmm_clear_github_cache=1` with nonce action `tmm_clear_github_cache_nonce`. Handled by `tmm_handle_clear_cache_link()` hooked to `admin_init`. On success, redirects to `plugins.php?tmm_cache_cleared=1` and shows an admin notice.

---

## Frontend Behavior

- On `init`, `tmm_maintenance_mode_updated()` checks if maintenance mode is enabled and the visitor is not logged in (and not on the login page). If so, it calls `wp_die()` with the maintenance image and text.
- `is_login_page()` checks `$GLOBALS['pagenow']` for `wp-login.php` or `wp-register.php`.

---

## Admin Bar

- `tmm_admin_alert()` is hooked to both `admin_head` and `wp_head`. It outputs CSS that sets `#wpadminbar` background to the stored color and appends `[THIS SITE IS IN DEVELOPMENT MODE]` via a CSS `::after` pseudo-element.
- The same color is used to generate a dynamic SVG favicon (injected via `prefix_favicon()` on `admin_head` and `wp_head`, priority 100). It overrides the site icon via `get_site_icon_url` returning false.
- If `get_option('blog_public') == '0'`, a red "You are blocking search engines." node is added to the admin bar via `admin_bar_menu` (priority 100).

---

## Multisite

`tmm_add_admin_menu()` uses `manage_network_options` capability on multisite and `manage_options` on single-site. It is hooked to both `admin_menu` and `network_admin_menu`.

---

## Version Bumping

A pre-commit hook automatically increments the patch version in `tmm-maintenance-mode.php` on every commit. Do not manually edit the `Version:` header in the plugin file header unless you intend to override the auto-bump.

---

## Key Hook Summary

| Hook | Function | Purpose |
|---|---|---|
| `init` | `tmm_maintenance_mode_updated` | Redirect non-logged-in visitors when maintenance mode is on |
| `admin_head`, `wp_head` | `tmm_admin_alert` | Inject admin bar color CSS |
| `admin_head`, `wp_head` (priority 100) | `prefix_favicon` | Inject dynamic SVG favicon |
| `admin_menu`, `network_admin_menu` | `tmm_add_admin_menu` | Register admin menu page |
| `admin_init` | `tmm_register_settings` | Register settings fields |
| `admin_init` | `tmm_handle_clear_cache_action` | Handle POST cache-clear from settings page |
| `admin_init` | `tmm_handle_clear_cache_link` | Handle GET cache-clear from plugins list |
| `admin_enqueue_scripts` | `tmm_enqueue_media_uploader` | Enqueue media uploader + color picker |
| `admin_bar_menu` (priority 100) | `tmm_add_admin_bar_notice_for_search_engines_blocked` | Warn when search engines are blocked |
| `pre_set_site_transient_update_plugins` | `tmm_push_github_update_to_wordpress` | Inject GitHub update into WP update system |
| `upgrader_source_selection` | `tmm_rename_github_zip_folder` | Fix extracted folder name after update |
| `upgrader_process_complete` | `tmm_clear_github_update_cache` | Clear cache after a plugin update |
| `plugin_action_links_{plugin}` | `tmm_plugin_action_links` | Add "Clear Update Cache" to plugins list row |
| `get_site_icon_url` | `__return_false` | Suppress default site icon so dynamic favicon takes over |
