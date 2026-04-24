<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->dbforge();

// --- alternative logos table ---
$logo_table = db_prefix() . 'ptk_alternative_logos';

if (! $CI->db->table_exists($logo_table)) {
    $CI->db->query("CREATE TABLE `{$logo_table}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `logo_for` varchar(191) NOT NULL DEFAULT '',
        `logo_number` int(11) NOT NULL,
        `logo_name` varchar(255) NOT NULL DEFAULT '',
        `description` text NULL,
        `file_path` varchar(500) NULL DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_logo_for_number` (`logo_for`, `logo_number`),
        KEY `idx_logo_for` (`logo_for`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

$ptk_logo_uploads = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfex_toolkit' . DIRECTORY_SEPARATOR . 'alternative_logos';
if (! is_dir($ptk_logo_uploads)) {
    @mkdir($ptk_logo_uploads, 0755, true);
}
if (is_dir($ptk_logo_uploads) && ! file_exists($ptk_logo_uploads . DIRECTORY_SEPARATOR . 'index.html')) {
    @file_put_contents($ptk_logo_uploads . DIRECTORY_SEPARATOR . 'index.html', '');
}

// --- features table ---
$features_table = db_prefix() . 'ptk_features';

if (! $CI->db->table_exists($features_table)) {
    $CI->db->query("CREATE TABLE `{$features_table}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `feature_key` varchar(100) NOT NULL,
        `feature_name` varchar(150) NOT NULL DEFAULT '',
        `feature_description` text NULL,
        `category` varchar(100) NOT NULL DEFAULT 'general',
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `activated_at` datetime NULL DEFAULT NULL,
        `deactivated_at` datetime NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_feature_key` (`feature_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    // seed all known features as active
    $now = date('Y-m-d H:i:s');
    $CI->db->insert_batch($features_table, [
        [
            'feature_key'         => 'delete_invoices',
            'feature_name'        => 'Delete Invoices',
            'feature_description' => 'Filter by status and date, select invoices, and delete in bulk. Uses the same delete rules as the rest of Perfex (e.g. last invoice, payments).',
            'category'            => 'invoices',
            'is_active'           => 1,
            'activated_at'        => $now,
            'deactivated_at'      => null,
        ],
        [
            'feature_key'         => 'alternative_logos',
            'feature_name'        => 'Alternative Logos',
            'feature_description' => 'Manage extra logo slots (number 2+; slot 1 is the main company logo).',
            'category'            => 'branding',
            'is_active'           => 1,
            'activated_at'        => $now,
            'deactivated_at'      => null,
        ],
        [
            'feature_key'         => 'download_module',
            'feature_name'        => 'Download Module',
            'feature_description' => 'Browse all installed Perfex CRM modules and download any of them as a ZIP file.',
            'category'            => 'developer',
            'is_active'           => 1,
            'activated_at'        => $now,
            'deactivated_at'      => null,
        ],
    ]);
}
