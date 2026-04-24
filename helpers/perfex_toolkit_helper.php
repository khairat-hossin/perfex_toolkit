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

if (! function_exists('ptk_get_installed_modules')) {
    /**
     * List installed custom modules (folders under /modules with a matching {slug}.php file).
     *
     * @return array[] Each item: slug, name, version
     */
    function ptk_get_installed_modules()
    {
        $modules_path = FCPATH . 'modules' . DIRECTORY_SEPARATOR;
        if (! is_dir($modules_path)) {
            return [];
        }

        $entries = scandir($modules_path);
        $modules   = [];

        foreach ($entries as $slug) {
            if ($slug === '.' || $slug === '..') {
                continue;
            }
            $dir       = $modules_path . $slug;
            $main_file = $dir . DIRECTORY_SEPARATOR . $slug . '.php';
            if (! is_dir($dir) || ! file_exists($main_file)) {
                continue;
            }

            $header  = (string) file_get_contents($main_file, false, null, 0, 2048);
            $name    = $slug;
            $version = '-';

            if (preg_match('/Module Name:\s*(.+)/i', $header, $m)) {
                $name = trim($m[1]);
            }
            if (preg_match('/Version:\s*(.+)/i', $header, $m)) {
                $version = trim($m[1]);
            }

            $modules[] = [
                'slug'    => $slug,
                'name'    => $name,
                'version' => $version,
            ];
        }

        usort($modules, static function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $modules;
    }
}
