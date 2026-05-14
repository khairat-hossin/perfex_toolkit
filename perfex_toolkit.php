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
hooks()->add_action('lead_converted_to_customer', 'perfex_toolkit_copy_lead_files_to_customer');

register_language_files(PERFEX_TOOLKIT_MODULE_NAME, [PERFEX_TOOLKIT_MODULE_NAME]);
$CI->load->helper(PERFEX_TOOLKIT_MODULE_NAME . '/perfex_toolkit');

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

    if (is_admin() && ($statuses['lead_files_to_customer'] ?? true)) {
        $CI->app_menu->add_sidebar_children_item('perfex-toolkit', [
            'slug'     => 'perfex-toolkit-lead-files-to-customer',
            'name'     => _l('perfex_toolkit_nav_lftc'),
            'href'     => admin_url('perfex_toolkit/lead_files_to_customer'),
            'position' => 6,
            'icon'     => 'fa-solid fa-file-export',
        ]);
    }
}

/**
 * Hook: fired by Perfex core when a lead is converted to a customer.
 * Copies all local lead files to the new customer profile when the setting is enabled.
 *
 * @param array{lead_id:int, customer_id:int} $data
 */
function perfex_toolkit_copy_lead_files_to_customer($data)
{
    if (get_option('ptk_lead_files_to_customer') != '1') {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('perfex_toolkit/ptk_features_model');
    if (! $CI->ptk_features_model->is_active('lead_files_to_customer')) {
        return;
    }

    $lead_id     = (int) $data['lead_id'];
    $customer_id = (int) $data['customer_id'];

    if ($lead_id <= 0 || $customer_id <= 0) {
        return;
    }

    $CI = &get_instance();

    // Fetch only local lead files (skip external/cloud-linked files)
    $files = $CI->db
        ->where('rel_id', $lead_id)
        ->where('rel_type', 'lead')
        ->group_start()
            ->where('external', null)
            ->or_where('external', '')
        ->group_end()
        ->get(db_prefix() . 'files')
        ->result_array();

    if (empty($files)) {
        return;
    }

    $src_dir  = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'leads'   . DIRECTORY_SEPARATOR . $lead_id     . DIRECTORY_SEPARATOR;
    $dst_dir  = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'clients' . DIRECTORY_SEPARATOR . $customer_id . DIRECTORY_SEPARATOR;

    if (! is_dir($dst_dir)) {
        @mkdir($dst_dir, 0755, true);
    }

    foreach ($files as $file) {
        $src_file = $src_dir . $file['file_name'];
        if (! file_exists($src_file)) {
            continue;
        }

        // Avoid overwriting an existing file — append a short unique suffix
        $dst_file  = $dst_dir . $file['file_name'];
        if (file_exists($dst_file)) {
            $info     = pathinfo($file['file_name']);
            $ext      = isset($info['extension']) ? '.' . $info['extension'] : '';
            $dst_file = $dst_dir . $info['filename'] . '_' . substr(uniqid(), -5) . $ext;
        }

        if (! @copy($src_file, $dst_file)) {
            continue;
        }

        $CI->db->insert(db_prefix() . 'files', [
            'rel_id'              => $customer_id,
            'rel_type'            => 'customer',
            'file_name'           => basename($dst_file),
            'filetype'            => $file['filetype'],
            'visible_to_customer' => $file['visible_to_customer'],
            'attachment_key'      => app_generate_hash(),
            'staffid'             => $file['staffid'],
            'contact_id'          => 0,
            'dateadded'           => date('Y-m-d H:i:s'),
        ]);
    }
}
