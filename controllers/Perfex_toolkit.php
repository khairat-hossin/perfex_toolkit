<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Perfex_toolkit extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('perfex_toolkit/ptk_features_model');
    }

    /**
     * Feature overview (all tools with name + short intro).
     */
    public function index()
    {
        $data['title']    = _l('perfex_toolkit_dashboard');
        $data['features'] = $this->get_feature_definitions();

        $this->load->view('dashboard', $data);
    }

    /**
     * AJAX: activate or deactivate a feature. Admin-only.
     */
    public function toggle_feature()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        if (! is_admin()) {
            ajax_access_denied();
        }

        $key    = $this->input->post('feature_key');
        $action = $this->input->post('action'); // 'activate' | 'deactivate'

        $allowed_keys = ['delete_invoices', 'alternative_logos', 'download_module'];
        if (! in_array($key, $allowed_keys, true) || ! in_array($action, ['activate', 'deactivate'], true)) {
            echo json_encode(['success' => false, 'message' => _l('perfex_toolkit_feature_toggle_invalid')]);

            return;
        }

        if ($action === 'activate') {
            $result = $this->ptk_features_model->activate($key);
        } else {
            $result = $this->ptk_features_model->deactivate($key);
        }

        echo json_encode([
            'success'   => (bool) $result,
            'is_active' => $action === 'activate',
            'message'   => $result
                ? _l('perfex_toolkit_feature_toggle_' . $action . '_success')
                : _l('perfex_toolkit_feature_toggle_error'),
        ]);
    }

    /**
     * Delete invoices: filters, datatable, mass delete UI.
     */
    public function delete_invoices()
    {
        if (staff_cant('view', 'invoices')) {
            access_denied('invoices');
        }

        if (! $this->ptk_features_model->is_active('delete_invoices')) {
            set_alert('danger', _l('perfex_toolkit_feature_not_active'));
            redirect(admin_url('perfex_toolkit'));
        }

        $this->load->model('invoices_model');

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('perfex_toolkit', 'delete_invoices/invoices/table'));
        }

        $data['title'] = _l('perfex_toolkit_delete_invoices_title');
        $this->load->view('delete_invoices/invoices/manage', $data);
    }

    /**
     * AJAX: perform mass delete for delete_invoices screen.
     */
    public function delete_invoices_action()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        if (staff_cant('delete', 'invoices')) {
            ajax_access_denied();
        }

        $this->load->model('invoices_model');

        $ids = $this->input->post('ids');
        if (! is_array($ids) || count($ids) === 0) {
            echo json_encode([
                'success' => false,
                'message' => _l('perfex_toolkit_delete_invoices_none_selected'),
            ]);

            return;
        }

        $deleted = 0;
        $skipped = 0;

        foreach ($ids as $rawId) {
            $id = (int) $rawId;
            if ($id <= 0) {
                $skipped++;

                continue;
            }

            if (! user_can_view_invoice($id)) {
                $skipped++;

                continue;
            }

            if ($this->invoices_model->delete($id)) {
                $deleted++;
            } else {
                $skipped++;
            }
        }

        $message = sprintf(_l('perfex_toolkit_delete_invoices_deleted'), (string) $deleted);
        if ($skipped > 0) {
            $message .= ' ' . sprintf(_l('perfex_toolkit_delete_invoices_skipped'), (string) $skipped);
        }

        echo json_encode([
            'success' => true,
            'message' => $message,
            'deleted' => $deleted,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Register each feature for the dashboard (add new items here as you add tools).
     *
     * @return array<int, array{key:string,name:string,description:string,url:string,icon:string,available:bool,active:bool}>
     */
    private function get_feature_definitions()
    {
        $statuses = $this->ptk_features_model->get_statuses_keyed();

        $all = [
            [
                'key'         => 'delete_invoices',
                'name'        => _l('perfex_toolkit_feature_delete_invoices_name'),
                'description' => _l('perfex_toolkit_feature_delete_invoices_desc'),
                'url'         => admin_url('perfex_toolkit/delete_invoices'),
                'icon'        => 'fa-solid fa-file-invoice',
                'available'   => ! staff_cant('view', 'invoices'),
                'active'      => $statuses['delete_invoices'] ?? true,
            ],
            [
                'key'         => 'alternative_logos',
                'name'        => _l('perfex_toolkit_feature_alternative_logos_name'),
                'description' => _l('perfex_toolkit_feature_alternative_logos_desc'),
                'url'         => admin_url('perfex_toolkit/alternative_logos'),
                'icon'        => 'fa-solid fa-image',
                'available'   => is_admin(),
                'active'      => $statuses['alternative_logos'] ?? true,
            ],
            [
                'key'         => 'download_module',
                'name'        => _l('perfex_toolkit_feature_download_module_name'),
                'description' => _l('perfex_toolkit_feature_download_module_desc'),
                'url'         => admin_url('perfex_toolkit/download_module'),
                'icon'        => 'fa-solid fa-download',
                'available'   => is_admin(),
                'active'      => $statuses['download_module'] ?? true,
            ],
        ];

        // Non-admins only see features that are active AND available to them
        if (! is_admin()) {
            $all = array_values(array_filter($all, static function ($f) {
                return ! empty($f['active']) && ! empty($f['available']);
            }));
        }

        return $all;
    }
}
