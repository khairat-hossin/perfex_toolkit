<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->dbforge();

foreach (['ptk_alternative_logos', 'ptk_features'] as $t) {
    if ($CI->db->table_exists(db_prefix() . $t)) {
        $CI->dbforge->drop_table(db_prefix() . $t, true);
    }
}

// Remove any options added by this module
$CI->db->where_in('name', ['ptk_lead_files_to_customer'])->delete(db_prefix() . 'options');
