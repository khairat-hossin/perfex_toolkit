# Perfex Toolkit — Perfex CRM Module

A growing collection of essential daily-use tools and tweaks that are missing from Perfex CRM out of the box. Each tool is self-contained and accessible from a single **Dashboard** in the sidebar.

**Version:** 1.0.0 | **Requires:** Perfex CRM 2.3.0+

---

## Features

### 1. Delete Invoices (Bulk)

Filter your invoices by status and date range, select any number of them, and permanently delete them in one action.

**Why it's useful:** Perfex CRM does not offer a native bulk-delete UI for invoices. This tool fills that gap — useful for cleaning test data, removing old unpaid drafts, or purging cancelled invoices en masse.

**How to use:**
1. Open **Perfex Toolkit → Delete Invoices** in the sidebar.
2. Use the **Status** dropdown and **Date From / Date To** filters to narrow the list.
3. Check the invoices you want to remove (or use "Select All").
4. Click **Bulk Actions**, tick the **Mass delete** confirmation checkbox, then confirm.

**Permissions required:**
- **View Invoices** — to open the page and see the list.
- **Delete Invoices** — to see checkboxes and perform deletions.

> Deletion calls Perfex CRM's own `invoices_model->delete()` logic, so any core deletion rules (e.g. restrictions on deleting the latest invoice) are respected.

---

### 2. Alternative Logos

Upload and manage multiple logo images and reference them by a slot number inside templates, PDFs, or email layouts — without touching the default company logo.

**Why it's useful:** Some businesses need different logos for different documents — for example, a company logo for invoices vs. a lighter logo for email headers, or separate logos for different brands/departments managed from one Perfex install.

**How it works:**
- Slot **1** is always reserved for the default Perfex company logo.
- You upload logos starting from slot **2** onwards, tagged with a "Logo for" label (e.g. `email_header`, `invoice_footer`, `subsidiary_a`).
- Call the helper function anywhere in your templates to render a logo by slot number:

```php
// Display logo in slot 2
get_alternative_logo(2);
// Falls back to the default company logo if slot 2 is not configured
```

**How to use:**
1. Open **Perfex Toolkit → Alternative Logos** in the sidebar (admin-only).
2. Click **Upload Alternative Logo**.
3. Fill in:
   - **Logo For** — a short label describing where/what this logo is for (e.g. `email_header`).
   - **Logo Number** — the slot number (minimum 2). Pre-filled with the next available number.
   - **Description** — optional notes.
   - **File** — JPG, PNG, GIF, WebP, or SVG. Max 5 MB.
4. Save. The logo is stored in `uploads/perfex_toolkit/alternative_logos/`.
5. To update a logo, click the edit (pencil) icon on the row. Leave the file field empty to keep the existing image.
6. To remove a logo, click the delete icon.

**Permissions required:** Admin only (to open the page). Create/Delete Customers permission is used for individual upload and delete actions.

---

## Installation

1. Copy the `perfex_toolkit` folder into your Perfex `modules/` directory.
2. Go to **Setup → Modules**, find **Perfex Toolkit**, and click **Activate**.
3. The **Perfex Toolkit** menu item appears in the left sidebar with sub-items: **Dashboard**, **Delete Invoices**, **Alternative Logos**.

## Upgrading

After replacing the module files with a newer version:

1. Go to **Setup → Modules**.
2. If an upgrade prompt appears next to Perfex Toolkit, click it to run database migrations automatically.

## Uninstalling

Deactivating the module from **Setup → Modules** will **drop the `ptk_alternative_logos` table** and remove all stored logo metadata. Uploaded image files in `uploads/perfex_toolkit/` are **not** automatically deleted — remove them manually if needed.

---

## Adding New Features

The module is designed to grow. Each new tool follows the same pattern:

- Add a view under `views/{feature_name}/`
- Add a controller method in `controllers/Perfex_toolkit.php` (or a new controller)
- Register the sidebar link in `perfex_toolkit.php`
- Add language strings in `language/english/perfex_toolkit_lang.php`
- Register the feature card in `Perfex_toolkit::get_feature_definitions()`

---

## License

Use and modify freely for your Perfex CRM installation.
