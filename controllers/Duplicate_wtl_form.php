<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Duplicate_wtl_form extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (staff_cant('create', 'leads')) {
            access_denied('leads');
        }
        $this->load->model('perfex_toolkit/ptk_features_model');
        if (! $this->ptk_features_model->is_active('duplicate_wtl_form')) {
            set_alert('danger', _l('perfex_toolkit_feature_not_active'));
            redirect(admin_url('perfex_toolkit'));
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('perfex_toolkit', 'duplicate_wtl_form/table'));

            return;
        }

        $data['title'] = _l('perfex_toolkit_dup_wtl_title');
        $this->load->view('duplicate_wtl_form/manage', $data);
    }

    /**
     * Duplicate a web-to-lead form and redirect to its edit page.
     */
    public function duplicate($id)
    {
        $id     = (int) $id;
        $source = $this->db->where('id', $id)->get(db_prefix() . 'web_to_lead')->row();

        if (! $source) {
            set_alert('danger', _l('perfex_toolkit_dup_wtl_not_found'));
            redirect(admin_url('perfex_toolkit/duplicate_wtl_form'));
        }

        $copy = [
            'name'                       => $source->name . ' ' . _l('perfex_toolkit_dup_wtl_copy_suffix'),
            'form_key'                   => app_generate_hash(),
            'lead_source'                => $source->lead_source,
            'lead_status'                => $source->lead_status,
            'lead_name_prefix'           => $source->lead_name_prefix,
            'form_data'                  => $source->form_data,
            'allow_duplicate'            => $source->allow_duplicate,
            'track_duplicate_field'      => $source->track_duplicate_field,
            'track_duplicate_field_and'  => $source->track_duplicate_field_and,
            'create_task_on_duplicate'   => $source->create_task_on_duplicate,
            'recaptcha'                  => $source->recaptcha,
            'submit_btn_name'            => $source->submit_btn_name,
            'submit_btn_bg_color'        => $source->submit_btn_bg_color,
            'submit_btn_text_color'      => $source->submit_btn_text_color,
            'submit_action'              => $source->submit_action,
            'success_submit_msg'         => $source->success_submit_msg,
            'submit_redirect_url'        => $source->submit_redirect_url,
            'language'                   => $source->language,
            'notify_lead_imported'       => $source->notify_lead_imported,
            'notify_type'                => $source->notify_type,
            'notify_ids'                 => $source->notify_ids,
            'responsible'                => $source->responsible,
            'mark_public'                => $source->mark_public,
            'dateadded'                  => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix() . 'web_to_lead', $copy);
        $new_id = $this->db->insert_id();

        if ($new_id) {
            log_activity('Web to Lead Form Duplicated [Source ID: ' . $id . ' → New ID: ' . $new_id . ']');
            set_alert('success', _l('perfex_toolkit_dup_wtl_success'));
            redirect(admin_url('leads/form/' . $new_id));
        }

        set_alert('danger', _l('perfex_toolkit_dup_wtl_error'));
        redirect(admin_url('perfex_toolkit/duplicate_wtl_form'));
    }
}
