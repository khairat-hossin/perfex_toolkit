<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (! function_exists('get_alternative_logo')) {
    /**
     * Return alternative logo HTML by logo number.
     * Falls back to the default dark company logo if not found.
     *
     * @param  int|string  $logo_number
     * @return void
     */
    function get_alternative_logo($logo_number)
    {
        $logoNumber = (int) $logo_number;
        if ($logoNumber < 2) {
            echo get_dark_company_logo();

            return;
        }

        $CI    = &get_instance();
        $table = db_prefix() . 'ptk_alternative_logos';

        if (! $CI->db->table_exists($table)) {
            echo get_dark_company_logo();

            return;
        }

        $CI->db->where('logo_number', $logoNumber);
        $row = $CI->db->get($table)->row();
        if (! $row || empty($row->file_path)) {
            echo get_dark_company_logo();

            return;
        }

        $relativePath = ltrim(str_replace('\\', '/', (string) $row->file_path), '/');
        $absolutePath = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (! is_file($absolutePath)) {
            echo get_dark_company_logo();

            return;
        }

        $logoUrl = base_url('uploads/' . $relativePath);
        $company = get_option('companyname');
        echo '<img src="' . html_escape($logoUrl) . '" class="logo img-responsive" alt="' . html_escape($company) . '">';
    }
}
