<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Download_module extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (! is_admin()) {
            access_denied();
        }
        $this->load->model('perfex_toolkit/ptk_features_model');
        if (! $this->ptk_features_model->is_active('download_module')) {
            set_alert('danger', _l('perfex_toolkit_feature_not_active'));
            redirect(admin_url('perfex_toolkit'));
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('perfex_toolkit', 'download_module/table'));

            return;
        }

        $data['title'] = _l('perfex_toolkit_download_module_title');
        $this->load->view('download_module/manage', $data);
    }

    public function download($slug)
    {
        $slug = basename($slug);
        $dir  = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $slug;

        if (! is_dir($dir)) {
            set_alert('danger', _l('perfex_toolkit_download_module_not_found'));
            redirect(admin_url('perfex_toolkit/download_module'));
        }

        if (! class_exists('ZipArchive')) {
            set_alert('danger', _l('perfex_toolkit_download_module_zip_unavailable'));
            redirect(admin_url('perfex_toolkit/download_module'));
        }

        $tmp  = tempnam(sys_get_temp_dir(), 'ptk_mod_');
        $zip  = new ZipArchive();

        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            set_alert('danger', _l('perfex_toolkit_download_module_zip_error'));
            redirect(admin_url('perfex_toolkit/download_module'));
        }

        $this->add_folder_to_zip($zip, $dir, $slug);
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $slug . '.zip"');
        header('Content-Length: ' . filesize($tmp));
        header('Pragma: no-cache');
        readfile($tmp);
        unlink($tmp);
        exit;
    }

    private function add_folder_to_zip(ZipArchive $zip, $folder, $base)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = $base . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
            $relative = str_replace('\\', '/', $relative);

            if ($item->isDir()) {
                $zip->addEmptyDir($relative);
            } else {
                $zip->addFile($item->getPathname(), $relative);
            }
        }
    }
}
