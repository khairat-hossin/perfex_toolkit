<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->dbforge();

$table = db_prefix() . 'ptk_alternative_logos';
if ($CI->db->table_exists($table)) {
    $CI->dbforge->drop_table($table, true);
}
