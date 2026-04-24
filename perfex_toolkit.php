<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

/*
Module Name: Perfex Toolkit
Description: A growing collection of essential daily-use tools and tweaks missing from Perfex CRM.
Version: 1.0.0
Requires at least: 2.3.*
Author: Khairat Hossin
Author URI: https://www.fiverr.com/khairathossin/expertly-install-customize-and-fix-your-perfex-crm
 */

define('PERFEX_TOOLKIT_MODULE_NAME', 'perfex_toolkit');

register_activation_hook(PERFEX_TOOLKIT_MODULE_NAME, 'perfex_toolkit_activation_hook');
register_uninstall_hook(PERFEX_TOOLKIT_MODULE_NAME, 'perfex_toolkit_uninstall_hook');

hooks()->add_action('admin_init', 'perfex_toolkit_init_menu_items');

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

    if (! staff_cant('view', 'invoices') && ! empty($statuses['delete_invoices'])) {
        $CI->app_menu->add_sidebar_children_item('perfex-toolkit', [
            'slug'     => 'perfex-toolkit-delete-invoices',
            'name'     => _l('perfex_toolkit_nav_delete_invoices'),
            'href'     => admin_url('perfex_toolkit/delete_invoices'),
            'position' => 2,
            'icon'     => 'fa-solid fa-file-invoice',
        ]);
    }

    if (is_admin() && ! empty($statuses['alternative_logos'])) {
        $CI->app_menu->add_sidebar_children_item('perfex-toolkit', [
            'slug'     => 'perfex-toolkit-alternative-logos',
            'name'     => _l('perfex_toolkit_nav_alternative_logos'),
            'href'     => admin_url('perfex_toolkit/alternative_logos'),
            'position' => 3,
            'icon'     => 'fa-solid fa-image',
        ]);
    }

    if (is_admin() && ! empty($statuses['download_module'])) {
        $CI->app_menu->add_sidebar_children_item('perfex-toolkit', [
            'slug'     => 'perfex-toolkit-download-module',
            'name'     => _l('perfex_toolkit_nav_download_module'),
            'href'     => admin_url('perfex_toolkit/download_module'),
            'position' => 4,
            'icon'     => 'fa-solid fa-download',
        ]);
    }
}
