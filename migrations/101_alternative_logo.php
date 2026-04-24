<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Alternative_logo extends App_module_migration
{
    public function up()
    {
        $CI    = &get_instance();
        $table = db_prefix() . 'ptk_alternative_logos';

        if (! $CI->db->table_exists($table)) {
            $CI->db->query("CREATE TABLE `{$table}` (
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

        $dir = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfex_toolkit' . DIRECTORY_SEPARATOR . 'alternative_logos';
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (is_dir($dir) && ! file_exists($dir . DIRECTORY_SEPARATOR . 'index.html')) {
            @file_put_contents($dir . DIRECTORY_SEPARATOR . 'index.html', '');
        }
    }

    public function down()
    {
        $CI    = &get_instance();
        $table = db_prefix() . 'ptk_alternative_logos';

        if ($CI->db->table_exists($table)) {
            $CI->db->query('DROP TABLE `' . $table . '`');
        }
    }
}
