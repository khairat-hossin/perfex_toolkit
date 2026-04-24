# Perfex Toolkit — Technical Reference

This document covers the internal architecture, file layout, database schema, integration points, and extension guidelines for the Perfex Toolkit Perfex CRM module.

---

## File Structure

```
perfex_toolkit/
├── module.json                              # Module manifest (name, version, requirements)
├── perfex_toolkit.php                       # Entry point: hooks, menu registration, language/helper loading
├── install.php                              # Run on activation: creates DB table, upload directory
├── uninstall.php                            # Run on deactivation: drops DB table
│
├── controllers/
│   ├── Perfex_toolkit.php                   # Dashboard + delete-invoices feature
│   └── Alternative_logos.php               # Full CRUD for alternative logos
│
├── models/
│   └── Alternative_logos_model.php          # DB queries for ptk_alternative_logos table
│
├── helpers/
│   └── perfex_toolkit_helper.php            # get_alternative_logo() template helper
│
├── migrations/
│   └── 101_alternative_logo.php             # DB migration (run on version bump)
│
├── language/
│   └── english/
│       └── perfex_toolkit_lang.php          # ~70 language strings
│
└── views/
    ├── dashboard.php                        # Feature-card overview page
    ├── delete_invoices/
    │   └── invoices/
    │       ├── manage.php                   # Filters + DataTable + bulk-delete UI
    │       └── table.php                    # Server-side DataTables endpoint
    └── alternative_logos/
        ├── manage.php                       # Logo list + upload/edit modals
        └── table.php                        # Server-side DataTables endpoint
```

---

## Module Manifest

**File:** `module.json`

| Field | Value |
|---|---|
| `module_name` | `perfex_toolkit` |
| `version` | `1.0.0` |
| `minimum_perfex_version` | `2.3.0` |
| `description` | A growing collection of essential daily-use tools and tweaks missing from Perfex CRM |

---

## Entry Point & Hooks

**File:** `perfex_toolkit.php`

Loaded by Perfex CRM's module loader. Responsibilities:

1. **Defines constant** `PERFEX_TOOLKIT_MODULE_NAME = 'perfex_toolkit'`
2. **Registers lifecycle hooks:**
   - `register_activation_hook(...)` → runs `install.php`
   - `register_uninstall_hook(...)` → runs `uninstall.php`
3. **Loads language file** via `register_language_files()`
4. **Loads helper** `perfex_toolkit/perfex_toolkit` (makes `get_alternative_logo()` available globally)
5. **Registers `admin_init` action** → `perfex_toolkit_init_menu_items()`

**Menu registration (`perfex_toolkit_init_menu_items`):**

```
Sidebar parent:  perfex-toolkit          icon: fa-solid fa-bolt        position: 26
  ├── perfex-toolkit-dashboard            /admin/perfex_toolkit         position: 1
  ├── perfex-toolkit-delete-invoices      /admin/perfex_toolkit/delete_invoices
  │     (hidden if staff_cant('view', 'invoices'))                      position: 2
  └── perfex-toolkit-alternative-logos   /admin/perfex_toolkit/alternative_logos
        (admin only)                                                     position: 3
```

---

## Database

### Table: `{prefix}ptk_alternative_logos`

Created by `install.php` on activation and by migration `101_alternative_logo`.

| Column | Type | Notes |
|---|---|---|
| `id` | INT UNSIGNED AUTO_INCREMENT | Primary key |
| `logo_for` | VARCHAR(191) NOT NULL | Label / category (e.g. `email_header`) |
| `logo_number` | INT NOT NULL | Slot number; minimum 2 (slot 1 = default Perfex logo) |
| `logo_name` | VARCHAR(255) NOT NULL | Legacy mirror of `logo_for` |
| `description` | TEXT NULL | Optional notes |
| `file_path` | VARCHAR(500) NULL | Relative path under `uploads/` |
| `created_at` | DATETIME DEFAULT CURRENT_TIMESTAMP | |

**Constraints:**
- `UNIQUE KEY (logo_for, logo_number)` — prevents duplicate slot per label
- `INDEX (logo_for)` — fast lookup by label

**Uploaded files** are stored at:
```
{FCPATH}/uploads/perfex_toolkit/alternative_logos/ptk_{uniqid}.{ext}
```
An `index.html` file is placed in the directory to block directory listing.

---

## Controllers

### `Perfex_toolkit` (`controllers/Perfex_toolkit.php`)

Extends `AdminController`.

| Method | Route | Notes |
|---|---|---|
| `index()` | `GET /admin/perfex_toolkit` | Dashboard with feature cards |
| `delete_invoices()` | `GET /admin/perfex_toolkit/delete_invoices` | Requires `staff_can('view', 'invoices')` |
| `delete_invoices()` (AJAX) | DataTables XHR | Delegates to `views/delete_invoices/invoices/table.php` |
| `delete_invoices_action()` | `POST /admin/perfex_toolkit/delete_invoices_action` | Bulk delete; requires `staff_can('delete', 'invoices')` |

**`delete_invoices_action()` logic:**
1. Reads POST `ids[]` array.
2. Validates each entry is numeric.
3. Calls `user_can_view_invoice($id)` — skips inaccessible invoices.
4. Calls `invoices_model->delete($id)` for each valid ID.
5. Returns JSON: `{ success, message, deleted, skipped }`.

**`get_feature_definitions()` (private):**
Returns the feature card array shown on the dashboard. Each item:
```php
[
  'key'         => string,
  'name'        => string (lang key resolved),
  'description' => string (lang key resolved),
  'url'         => string,
  'icon'        => string (FA class),
  'available'   => bool,
]
```

---

### `Alternative_logos` (`controllers/Alternative_logos.php`)

Extends `AdminController`. Constructor denies access unless `is_admin()`.

| Method | Route | Notes |
|---|---|---|
| `index()` | `GET /admin/perfex_toolkit/alternative_logos` | List view; AJAX-aware |
| `upload_logo()` | `POST /admin/perfex_toolkit/alternative_logos/upload_logo` | Create record + file |
| `get_logo($id)` | `GET /admin/perfex_toolkit/alternative_logos/get_logo/{id}` | AJAX; returns JSON |
| `update_logo()` | `POST /admin/perfex_toolkit/alternative_logos/update_logo` | Update record, optional file replace |
| `delete_logo($id)` | `GET /admin/perfex_toolkit/alternative_logos/delete_logo/{id}` | Deletes record + file |

**File upload rules (`_pk_alt_process_uploaded_file`):**
- Max size: 5 MB
- Allowed extensions: `jpg`, `jpeg`, `png`, `gif`, `webp`, `svg`
- MIME validation via `finfo` / `mime_content_type()`; SVG allows `text/plain`, `text/xml`, `image/svg+xml`, `application/xml`
- Saved as `ptk_{uniqid}.{ext}` in the upload directory
- Returns relative path (from `uploads/`) or null on error

**Form validation callback `validate_alternative_logo_number`:**
- Rejects values < `MIN_LOGO_NUMBER` (2)
- Calls `alternative_logos_model->has_duplicate_number()`, excluding the current record ID when editing

**Error recovery flow (upload/update):**
- On any failure: sets flashdata with previous input values + error message
- Redirects to `?open=add` or `?open=edit&id=X`
- Views detect flashdata to reopen the correct modal pre-filled

---

## Model: `Alternative_logos_model`

**File:** `models/Alternative_logos_model.php` | Extends `App_Model`

| Method | Signature | Returns |
|---|---|---|
| `get` | `get($id = '')` | Single object (if numeric ID) or array of all rows |
| `get_by_id` | `get_by_id(int $id)` | Single object or null |
| `add` | `add(array $data)` | Inserted ID or false |
| `update` | `update(int $id, array $data)` | bool |
| `delete` | `delete(int $id)` | bool |
| `has_duplicate_number` | `has_duplicate_number(int $logoNumber, int $excludeId = 0)` | bool |
| `get_next_logo_number` | `get_next_logo_number()` | int (max + 1, min 2) |
| `get_table_name` | `get_table_name()` | string |

`MIN_LOGO_NUMBER` constant = `2`.

---

## Helper: `get_alternative_logo()`

**File:** `helpers/perfex_toolkit_helper.php`

```php
get_alternative_logo(int $logo_number): void
```

Echoes an `<img>` tag for the logo stored in the given slot. Falls back to the default Perfex dark company logo if:
- `$logo_number < 2`
- The `ptk_alternative_logos` table does not exist
- No record found for that number
- `file_path` is empty
- The file is missing from disk

**Usage in templates/views:**
```php
<?php get_alternative_logo(2); ?>
```

The `alt` attribute is set to the company name from Perfex settings.

---

## Views

### `views/dashboard.php`

Receives `$features` array from `Perfex_toolkit::get_feature_definitions()`. Renders a card grid (Bootstrap col-md-6 col-lg-4). Cards with `available = false` show a lock icon instead of an "Open" button.

### `views/delete_invoices/invoices/manage.php`

- Initializes a DataTable pointing to the same URL with `?dt=1` (handled by `app->get_table_data()`)
- Server parameters passed to DataTable: `pk_delete_invoices_status`, `pk_delete_invoices_date_from`, `pk_delete_invoices_date_to`
- Bulk-delete AJAX: `POST perfex_toolkit/delete_invoices_action` with `ids[]`
- On success: reloads DataTable, shows success message

### `views/delete_invoices/invoices/table.php`

DataTables server-side processing. Key query logic:
- Applies `get_invoices_where_sql_for_staff()` for staff visibility
- Filters: `status = ?` and/or `date BETWEEN ? AND ?`
- Columns: `checkbox`, `number`, `date`, `clientid` (joined company name), `total`, `status`

### `views/alternative_logos/manage.php`

- DataTable with search by `pk_alt_logo_for` (debounced 500ms)
- Upload modal: standard Bootstrap modal; JS validates before submit
- Edit modal: loads data via `GET get_logo/{id}` → populates fields and image preview
- Flashdata keys: `pk_alt_add_old`, `pk_alt_edit_old` for field repopulation

### `views/alternative_logos/table.php`

- Filter: `logo_for LIKE '%?%'`
- Thumbnail: inline `<img>` from `base_url('uploads/' . $row->file_path)`
- Action links: edit triggers JS modal load; delete uses direct link with confirmation

---

## Permission Matrix

| Action | Required Permission |
|---|---|
| View Perfex Toolkit menu | Any staff |
| Open Delete Invoices page | `staff_can('view', 'invoices')` |
| Select & delete invoices | `staff_can('delete', 'invoices')` + `user_can_view_invoice($id)` per invoice |
| Open Alternative Logos page | `is_admin()` |
| Upload / edit a logo | `staff_can('create', 'customers')` |
| Delete a logo | `staff_can('delete', 'customers')` |

---

## Perfex CRM Integration Points

| Perfex API | Where Used |
|---|---|
| `AdminController` | Base class for both controllers |
| `App_Model` | Base class for `Alternative_logos_model` |
| `App_module_migration` | Migration base class |
| `hooks()->add_action()` | `admin_init` for menu setup |
| `register_activation_hook()` / `register_uninstall_hook()` | Lifecycle |
| `register_language_files()` | Loads `perfex_toolkit_lang` |
| `$CI->app_menu->add_sidebar_menu_item()` | Sidebar parent menu |
| `add_sidebar_children_item()` | Sub-menu items |
| `app->get_table_data()` | DataTables AJAX dispatch |
| `data_tables_init()` | DataTables server-side init in table views |
| `invoices_model->delete()` | Core invoice deletion logic |
| `invoices_model->get_statuses()` | Populates status filter dropdown |
| `get_invoices_where_sql_for_staff()` | Staff visibility SQL fragment |
| `get_sql_select_client_company()` | Client name SQL fragment |
| `format_invoice_number()` | Displays formatted invoice number |
| `format_invoice_status()` | Displays status badge |
| `app_format_money()` | Formats currency totals |
| `user_can_view_invoice()` | Per-invoice access check |
| `staff_can()` / `staff_cant()` / `is_admin()` | Permission checks throughout |
| `set_alert()` | Flash success/error messages |
| `render_input()`, `render_textarea()`, `render_date_input()` | Perfex-styled form fields |
| `admin_url()` / `base_url()` | URL helpers |
| `_l()` | Language string function |
| `_d()` / `_dt()` | Date / datetime formatters |
| `e()` | HTML escaping |
| `db_prefix()` | DB table prefix |

---

## Adding a New Feature

1. **Create view(s)** under `views/{feature_name}/`
2. **Add controller logic** — either a new method in `Perfex_toolkit.php` or a new controller file in `controllers/`
3. **Register the sidebar link** in `perfex_toolkit_init_menu_items()` inside `perfex_toolkit.php`
4. **Add language strings** in `language/english/perfex_toolkit_lang.php`
5. **Register the dashboard card** in `Perfex_toolkit::get_feature_definitions()`
6. **If DB changes needed:** add a migration under `migrations/` and bump the version in `module.json`

---

## Migration Notes

- Migrations run automatically when Perfex detects the module version in `module.json` is newer than the last installed version.
- Migration class naming: `Migration_{PascalName}` extending `App_module_migration`.
- `install.php` must also reflect the latest schema so fresh activations and migration upgrades stay in sync.
