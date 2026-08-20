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

        $allowed_keys = ['delete_invoices', 'alternative_logos', 'download_module', 'duplicate_wtl_form', 'preserve_lead_status', 'ticket_case_history'];
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
     * AJAX: return HTML list of all non-customer tickets sharing the given email.
     * Used by the case-history modal injected into the single ticket view.
     */
    public function ticket_case_history()
    {
        if (! is_staff_member()) {
            ajax_access_denied();
        }

        if (! $this->ptk_features_model->is_active('ticket_case_history')) {
            show_404();
        }

        $email   = $this->input->get('email', true);
        $exclude = (int) $this->input->get('exclude');

        if (! $email) {
            echo '<p class="text-center text-muted tw-py-6">' .
                 e(_l('perfex_toolkit_casehistory_no_email')) . '</p>';
            return;
        }

        $this->db->select(
            db_prefix() . 'tickets.ticketid, ' .
            db_prefix() . 'tickets.subject, ' .
            db_prefix() . 'tickets.date, ' .
            db_prefix() . 'tickets_status.name as status_name, ' .
            db_prefix() . 'tickets_status.statuscolor, ' .
            db_prefix() . 'departments.name as department_name'
        );
        $this->db->from(db_prefix() . 'tickets');
        $this->db->join(db_prefix() . 'tickets_status',
            db_prefix() . 'tickets_status.ticketstatusid = ' . db_prefix() . 'tickets.status', 'left');
        $this->db->join(db_prefix() . 'departments',
            db_prefix() . 'departments.departmentid = ' . db_prefix() . 'tickets.department', 'left');
        $this->db->where(db_prefix() . 'tickets.email', $email);
        $this->db->where(db_prefix() . 'tickets.userid', 0);
        $this->db->order_by(db_prefix() . 'tickets.date', 'desc');
        $tickets = $this->db->get()->result_array();

        $this->load->view('ticket_case_history/results', [
            'tickets'            => $tickets,
            'email'              => $email,
            'current_ticket_id'  => $exclude,
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
            [
                'key'         => 'duplicate_wtl_form',
                'name'        => _l('perfex_toolkit_feature_dup_wtl_name'),
                'description' => _l('perfex_toolkit_feature_dup_wtl_desc'),
                'url'         => admin_url('perfex_toolkit/duplicate_wtl_form'),
                'icon'        => 'fa-solid fa-copy',
                'available'   => ! staff_cant('create', 'leads'),
                'active'      => $statuses['duplicate_wtl_form'] ?? true,
            ],
            [
                'key'         => 'preserve_lead_status',
                'name'        => _l('perfex_toolkit_feature_preserve_lead_status_name'),
                'description' => _l('perfex_toolkit_feature_preserve_lead_status_desc'),
                'url'         => '',
                'icon'        => 'fa-solid fa-tag',
                'available'   => is_admin(),
                'active'      => $statuses['preserve_lead_status'] ?? true,
            ],
            [
                'key'         => 'ticket_case_history',
                'name'        => _l('perfex_toolkit_feature_casehistory_name'),
                'description' => _l('perfex_toolkit_feature_casehistory_desc'),
                'url'         => '',
                'icon'        => 'fa-solid fa-clock-rotate-left',
                'available'   => is_admin(),
                'active'      => $statuses['ticket_case_history'] ?? true,
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
