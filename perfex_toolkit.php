<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

/*
Module Name: Perfex Toolkit
Description: A growing collection of essential daily-use tools and tweaks missing from Perfex CRM.
Version: 1.0.1
Requires at least: 2.3.*
Author: Khairat Hossin
Author URI: https://www.fiverr.com/khairathossin/expertly-install-customize-and-fix-your-perfex-crm
 */

define('PERFEX_TOOLKIT_MODULE_NAME', 'perfex_toolkit');

register_activation_hook(PERFEX_TOOLKIT_MODULE_NAME, 'perfex_toolkit_activation_hook');
register_uninstall_hook(PERFEX_TOOLKIT_MODULE_NAME, 'perfex_toolkit_uninstall_hook');

hooks()->add_action('admin_init', 'perfex_toolkit_init_menu_items');
hooks()->add_action('admin_init', 'ptk_preserve_lead_status_boot');

register_language_files(PERFEX_TOOLKIT_MODULE_NAME, [PERFEX_TOOLKIT_MODULE_NAME]);
$CI->load->helper(PERFEX_TOOLKIT_MODULE_NAME . '/perfex_toolkit');

/**
 * Boot the "Preserve Lead Status on Conversion" feature.
 * Fires in the admin controller constructor — before any method runs.
 * If the request is a lead-conversion POST, snapshot the lead's current
 * status so we can restore it after Perfex resets it to default.
 */
function ptk_preserve_lead_status_boot()
{
    $CI = &get_instance();
    $CI->load->model(PERFEX_TOOLKIT_MODULE_NAME . '/ptk_features_model');
    if (! $CI->ptk_features_model->is_active('preserve_lead_status')) {
        return;
    }

    // Register the restore callback for later in this same request.
    hooks()->add_action('lead_converted_to_customer', '_ptk_restore_lead_status');

    // If this request is the actual conversion POST, grab the original status now.
    if (strtolower($CI->router->fetch_class()) === 'leads'
        && $CI->router->fetch_method() === 'convert_to_customer'
        && $CI->input->server('REQUEST_METHOD') === 'POST'
        && $CI->input->post('leadid')) {
        $lead_id = (int) $CI->input->post('leadid');
        if ($lead_id > 0) {
            $CI->db->select('status');
            $CI->db->where('id', $lead_id);
            $row = $CI->db->get(db_prefix() . 'leads')->row();
            if ($row) {
                _ptk_lead_status_store($lead_id, (int) $row->status);
            }
        }
    }
}

/**
 * Static key-value store scoped to this request.
 * Pass $status to write; omit (or pass null) to read.
 */
function _ptk_lead_status_store($lead_id, $status = null)
{
    static $store = [];
    if ($status !== null) {
        $store[$lead_id] = $status;
    }
    return $store[$lead_id] ?? null;
}

/**
 * Callback for 'lead_converted_to_customer' hook.
 * Restores the lead status to whatever it was before Perfex reset it.
 */
function _ptk_restore_lead_status($params)
{
    $lead_id = isset($params['lead_id']) ? (int) $params['lead_id'] : 0;
    if ($lead_id <= 0) {
        return;
    }
    $original = _ptk_lead_status_store($lead_id);
    if ($original === null) {
        return;
    }
    $CI = &get_instance();
    $CI->db->where('id', $lead_id);
    $CI->db->update(db_prefix() . 'leads', ['status' => $original]);
}

function perfex_toolkit_activation_hook()
{
    require __DIR__ . '/install.php';
}

function perfex_toolkit_uninstall_hook()
{
    require __DIR__ . '/uninstall.php';
}

/**
 * Collapsible parent with Dashboard + feature children.
 * Feature menu items only appear when the feature is active.
 */
function perfex_toolkit_init_menu_items()
{
    $CI = &get_instance();
    $CI->load->model(PERFEX_TOOLKIT_MODULE_NAME . '/ptk_features_model');
    $statuses = $CI->ptk_features_model->get_statuses_keyed();

    $CI->app_menu->add_sidebar_menu_item('perfex-toolkit', [
        'collapse' => true,
        'name'     => _l('perfex_toolkit_menu'),
        'position' => 26,
        'icon'     => 'fa-solid fa-bolt',
    ]);

    $CI->app_menu->add_sidebar_children_item('perfex-toolkit', [
        'slug'     => 'perfex-toolkit-dashboard',
        'name'     => _l('perfex_toolkit_nav_dashboard'),
        'href'     => admin_url('perfex_toolkit'),
        'position' => 1,
        'icon'     => 'fa fa-tachometer',
    ]);

    if (! staff_cant('view', 'invoices') && ($statuses['delete_invoices'] ?? true)) {
        $CI->app_menu->add_sidebar_children_item('perfex-toolkit', [
            'slug'     => 'perfex-toolkit-delete-invoices',
            'name'     => _l('perfex_toolkit_nav_delete_invoices'),
            'href'     => admin_url('perfex_toolkit/delete_invoices'),
            'position' => 2,
            'icon'     => 'fa-solid fa-file-invoice',
        ]);
    }

    if (is_admin() && ($statuses['alternative_logos'] ?? true)) {
        $CI->app_menu->add_sidebar_children_item('perfex-toolkit', [
            'slug'     => 'perfex-toolkit-alternative-logos',
            'name'     => _l('perfex_toolkit_nav_alternative_logos'),
            'href'     => admin_url('perfex_toolkit/alternative_logos'),
            'position' => 3,
            'icon'     => 'fa-solid fa-image',
        ]);
    }

    if (is_admin() && ($statuses['download_module'] ?? true)) {
        $CI->app_menu->add_sidebar_children_item('perfex-toolkit', [
            'slug'     => 'perfex-toolkit-download-module',
            'name'     => _l('perfex_toolkit_nav_download_module'),
            'href'     => admin_url('perfex_toolkit/download_module'),
            'position' => 4,
            'icon'     => 'fa-solid fa-download',
        ]);
    }

    if (! staff_cant('create', 'leads') && ($statuses['duplicate_wtl_form'] ?? true)) {
        $CI->app_menu->add_sidebar_children_item('perfex-toolkit', [
            'slug'     => 'perfex-toolkit-duplicate-wtl-form',
            'name'     => _l('perfex_toolkit_nav_duplicate_wtl_form'),
            'href'     => admin_url('perfex_toolkit/duplicate_wtl_form'),
            'position' => 5,
            'icon'     => 'fa-solid fa-copy',
        ]);
    }
}
