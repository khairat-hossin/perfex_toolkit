<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Perfex_toolkit extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Feature overview (all tools with name + short intro).
     */
    public function index()
    {
        $data['title'] = _l('perfex_toolkit_dashboard');
        $data['features'] = $this->get_feature_definitions();

        $this->load->view('dashboard', $data);
    }

    /**
     * Delete invoices: filters, datatable, mass delete UI.
     */
    public function delete_invoices()
    {
        if (staff_cant('view', 'invoices')) {
            access_denied('invoices');
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
     * @return array<int, array{key:string,name:string,description:string,url:string,icon:string,available:bool}>
     */
    private function get_feature_definitions()
    {
        $features = [
            [
                'key'         => 'delete_invoices',
                'name'        => _l('perfex_toolkit_feature_delete_invoices_name'),
                'description' => _l('perfex_toolkit_feature_delete_invoices_desc'),
                'url'         => admin_url('perfex_toolkit/delete_invoices'),
                'icon'        => 'fa-solid fa-file-invoice',
                'available'   => ! staff_cant('view', 'invoices'),
            ],
        ];

        if (is_admin()) {
            $features[] = [
                'key'         => 'alternative_logos',
                'name'        => _l('perfex_toolkit_feature_alternative_logos_name'),
                'description' => _l('perfex_toolkit_feature_alternative_logos_desc'),
                'url'         => admin_url('perfex_toolkit/alternative_logos'),
                'icon'        => 'fa-solid fa-image',
                'available'   => true,
            ];
        }

        return $features;
    }
}
