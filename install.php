<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Table: {prefix}ptk_alternative_logos
 * @see migrations/101_alternative_logo.php
 */
$CI = &get_instance();
$CI->load->dbforge();

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
} elseif (! $CI->db->field_exists('file_path', $logo_table)) {
    $CI->db->query('ALTER TABLE `' . $logo_table . '` ADD `file_path` varchar(500) NULL DEFAULT NULL AFTER `description`');
}

$ptk_logo_uploads = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfex_toolkit' . DIRECTORY_SEPARATOR . 'alternative_logos';
if (! is_dir($ptk_logo_uploads)) {
    @mkdir($ptk_logo_uploads, 0755, true);
}
if (is_dir($ptk_logo_uploads) && ! file_exists($ptk_logo_uploads . DIRECTORY_SEPARATOR . 'index.html')) {
    @file_put_contents($ptk_logo_uploads . DIRECTORY_SEPARATOR . 'index.html', '');
}
