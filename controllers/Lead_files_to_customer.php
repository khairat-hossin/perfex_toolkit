<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_files_to_customer extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (! is_admin()) {
            access_denied();
        }
        $this->load->model('perfex_toolkit/ptk_features_model');
        if (! $this->ptk_features_model->is_active('lead_files_to_customer')) {
            set_alert('danger', _l('perfex_toolkit_feature_not_active'));
            redirect(admin_url('perfex_toolkit'));
        }
    }

    public function index()
    {
        $data['title']   = _l('perfex_toolkit_lftc_title');
        $data['enabled'] = get_option('ptk_lead_files_to_customer') == '1';
        $this->load->view('lead_files_to_customer/settings', $data);
    }

    public function save()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $value = $this->input->post('enabled') ? '1' : '0';
        update_option('ptk_lead_files_to_customer', $value);

        echo json_encode([
            'success' => true,
            'message' => _l('perfex_toolkit_lftc_saved'),
        ]);
    }
}
