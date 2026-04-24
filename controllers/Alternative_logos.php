<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Alternative_logos extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (! is_admin()) {
            access_denied();
        }
        $this->load->model('Alternative_logos_model', 'alternative_logos_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('perfex_toolkit', 'alternative_logos/table'));

            return;
        }

        $data['title']     = _l('perfex_toolkit_alternative_logos_title');
        $data['min_logo']  = Alternative_logos_model::MIN_LOGO_NUMBER;
        $data['next_logo'] = $this->alternative_logos_model->get_next_logo_number();

        $addOld = $this->session->flashdata('pk_alt_add_old');
        if (! is_array($addOld)) {
            $addOld = [];
        }
        $data['add_old'] = $addOld;
        if (! empty($addOld) || $this->input->get('open') === 'add') {
            $data['reopen_add'] = true;
        } else {
            $data['reopen_add'] = false;
        }

        $open  = (string) $this->input->get('open');
        $gId   = (int) $this->input->get('id');
        $edOld = $this->session->flashdata('pk_alt_edit_old');
        if (! is_array($edOld)) {
            $edOld = [];
        }

        $editState      = null;
        $rowFromDb      = null;
        $edIdForPreview = 0;
        if (! empty($edOld['id'])) {
            $edIdForPreview = (int) $edOld['id'];
            $rowFromDb      = $this->alternative_logos_model->get_by_id($edIdForPreview);
        } elseif ($open === 'edit' && $gId > 0) {
            $edIdForPreview = $gId;
            $rowFromDb      = $this->alternative_logos_model->get_by_id($gId);
        }

        if ($rowFromDb) {
            $editState = [
                'id'          => (int) $rowFromDb->id,
                'logo_for'    => (string) $rowFromDb->logo_for,
                'logo_number' => (int) $rowFromDb->logo_number,
                'description' => $rowFromDb->description === null ? '' : (string) $rowFromDb->description,
            ];
            if (! empty($edOld)) {
                if (isset($edOld['logo_for'])) {
                    $editState['logo_for'] = (string) $edOld['logo_for'];
                }
                if (isset($edOld['logo_number'])) {
                    $editState['logo_number'] = (int) $edOld['logo_number'];
                }
                if (array_key_exists('description', $edOld) && is_string($edOld['description'])) {
                    $editState['description'] = $edOld['description'];
                } elseif (isset($edOld['description'])) {
                    $editState['description'] = (string) $edOld['description'];
                }
            }
            if ($rowFromDb->file_path) {
                $editState['file_url'] = base_url('uploads/' . ltrim(str_replace('\\', '/', (string) $rowFromDb->file_path), '/'));
            } else {
                $editState['file_url'] = '';
            }
        }

        $data['edit_state']  = $editState;
        $data['reopen_edit'] = is_array($editState) && ! empty($editState['id']) && (! empty($edOld) || ($open === 'edit' && $gId > 0));

        $this->load->view('alternative_logos/manage', $data);
    }

    public function upload_logo()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST' || ! $this->input->post()) {
            show_404();
        }

        if (staff_cant('create', 'customers')) {
            access_denied('customers');
        }

        $this->form_validation->set_data($this->input->post());
        $this->form_validation->set_rules('a_logo_for', _l('pk_logo_for'), 'required|trim|max_length[191]');
        $this->form_validation->set_rules('a_logo_number', _l('pk_logo_number'), 'required|callback_validate_alternative_logo_number');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('pk_alt_add_old', $this->input->post());
            set_alert('danger', validation_errors());
            redirect(admin_url('perfex_toolkit/alternative_logos?open=add'));
        }

        if (empty($_FILES['a_logo_file']['name']) || (int) ($_FILES['a_logo_file']['error'] ?? 0) !== 0) {
            $this->session->set_flashdata('pk_alt_add_old', $this->input->post());
            set_alert('danger', _l('perfex_toolkit_alternative_logos_upload_error_file'));
            redirect(admin_url('perfex_toolkit/alternative_logos?open=add'));
        }

        $err      = '';
        $relative = $this->_pk_alt_process_uploaded_file('a_logo_file', $err);
        if ($relative === null) {
            $this->session->set_flashdata('pk_alt_add_old', $this->input->post());
            set_alert('danger', $err);
            redirect(admin_url('perfex_toolkit/alternative_logos?open=add'));
        }

        $fileBase  = pathinfo($relative, PATHINFO_BASENAME);
        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/')
            . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfex_toolkit' . DIRECTORY_SEPARATOR . 'alternative_logos' . DIRECTORY_SEPARATOR;
        $destFs = $uploadDir . $fileBase;

        $desc = $this->input->post('a_description', false);
        if (is_string($desc)) {
            $desc = trim($desc);
        } else {
            $desc = null;
        }
        if ($desc === '') {
            $desc = null;
        }

        $logoFor    = trim((string) $this->input->post('a_logo_for'));
        $logoNumber = (int) $this->input->post('a_logo_number');

        $row = [
            'logo_for'    => $logoFor,
            'logo_number' => $logoNumber,
            'logo_name'   => $logoFor,
            'description' => $desc,
            'file_path'   => $relative,
        ];

        $id = $this->alternative_logos_model->add($row);
        if (! $id) {
            if (is_file($destFs)) {
                @unlink($destFs);
            }
            $errDb = $this->db->error();
            $dup   = ! empty($errDb['code']) && ((int) $errDb['code'] === 1062 || (int) $errDb['code'] === 23000);
            if (! $dup && ! empty($errDb['message']) && stripos($errDb['message'], 'Duplicate') !== false) {
                $dup = true;
            }
            $this->session->set_flashdata('pk_alt_add_old', $this->input->post());
            set_alert('danger', $dup ? _l('perfex_toolkit_alternative_logos_upload_error_duplicate') : _l('perfex_toolkit_alternative_logos_upload_error_db'));
            redirect(admin_url('perfex_toolkit/alternative_logos?open=add'));
        }

        set_alert('success', _l('perfex_toolkit_alternative_logos_upload_success'));
        redirect(admin_url('perfex_toolkit/alternative_logos'));
    }

    public function get_logo($id = null)
    {
        $id = (int) $id;
        if ($id < 1) {
            $id = (int) $this->input->get('id');
        }
        if ($id < 1) {
            $this->output->set_content_type('application/json', 'utf-8');
            echo json_encode(['success' => false, 'message' => _l('perfex_toolkit_alternative_logos_update_error_not_found')], JSON_UNESCAPED_UNICODE);

            return;
        }

        if (staff_cant('create', 'customers')) {
            $this->output->set_content_type('application/json', 'utf-8');
            echo json_encode(['success' => false, 'message' => ''], JSON_UNESCAPED_UNICODE);

            return;
        }

        $row = $this->alternative_logos_model->get_by_id($id);
        if (! $row) {
            $this->output->set_content_type('application/json', 'utf-8');
            echo json_encode(['success' => false, 'message' => _l('perfex_toolkit_alternative_logos_update_error_not_found')], JSON_UNESCAPED_UNICODE);

            return;
        }

        $data = [
            'id'          => (int) $row->id,
            'logo_for'    => (string) $row->logo_for,
            'logo_number' => (int) $row->logo_number,
            'description' => $row->description === null || $row->description === '' ? '' : (string) $row->description,
            'file_path'   => (string) ($row->file_path ?? ''),
        ];
        if ($data['file_path'] !== '') {
            $data['file_url'] = base_url('uploads/' . ltrim(str_replace('\\', '/', $data['file_path']), '/'));
        } else {
            $data['file_url'] = '';
        }

        $this->output->set_content_type('application/json', 'utf-8');
        echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    }

    public function update_logo()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST' || ! $this->input->post()) {
            show_404();
        }

        if (staff_cant('create', 'customers')) {
            access_denied('customers');
        }

        $id = (int) $this->input->post('id');

        $this->form_validation->set_data($this->input->post());
        $this->form_validation->set_rules('id', 'id', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('logo_for', _l('pk_logo_for'), 'required|trim|max_length[191]');
        $this->form_validation->set_rules('logo_number', _l('pk_logo_number'), 'required|callback_validate_alternative_logo_number');
        $this->form_validation->set_rules('description', _l('pk_logo_description'), 'trim');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('pk_alt_edit_old', $this->input->post());
            set_alert('danger', validation_errors());
            redirect(admin_url('perfex_toolkit/alternative_logos?open=edit&id=' . max(1, $id)));
        }

        if ($id < 1) {
            set_alert('danger', _l('perfex_toolkit_alternative_logos_update_error_not_found'));
            redirect(admin_url('perfex_toolkit/alternative_logos'));
        }

        $row = $this->alternative_logos_model->get_by_id($id);
        if (! $row) {
            $this->session->set_flashdata('pk_alt_edit_old', $this->input->post());
            set_alert('danger', _l('perfex_toolkit_alternative_logos_update_error_not_found'));
            redirect(admin_url('perfex_toolkit/alternative_logos'));
        }

        $logoFor     = trim((string) $this->input->post('logo_for'));
        $logoNumber  = (int) $this->input->post('logo_number');
        $description = $this->input->post('description', false);
        if (is_string($description)) {
            $description = trim($description);
        } else {
            $description = null;
        }
        if ($description === '') {
            $description = null;
        }

        $newRel  = null;
        $oldPath = (string) ($row->file_path ?? '');
        $hasFile = ! empty($_FILES['logo_file']['name']) && (int) ($_FILES['logo_file']['error'] ?? 0) === 0;

        if ($hasFile) {
            $err   = '';
            $moved = $this->_pk_alt_process_uploaded_file('logo_file', $err);
            if ($moved === null) {
                $this->session->set_flashdata('pk_alt_edit_old', $this->input->post());
                set_alert('danger', $err);
                redirect(admin_url('perfex_toolkit/alternative_logos?open=edit&id=' . $id));
            }
            $newRel = $moved;
        }

        $update = [
            'logo_for'    => $logoFor,
            'logo_number' => $logoNumber,
            'logo_name'   => $logoFor,
            'description' => $description,
        ];
        if ($newRel !== null) {
            $update['file_path'] = $newRel;
        }

        $ok = $this->alternative_logos_model->update($id, $update);
        if (! $ok) {
            if ($newRel !== null) {
                $p = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $newRel);
                if (is_file($p)) {
                    @unlink($p);
                }
            }
            $this->session->set_flashdata('pk_alt_edit_old', $this->input->post());
            set_alert('danger', _l('perfex_toolkit_alternative_logos_upload_error_db'));
            redirect(admin_url('perfex_toolkit/alternative_logos?open=edit&id=' . $id));
        }

        if ($newRel !== null && $oldPath !== '' && $oldPath !== $newRel) {
            $p = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($oldPath, '/'));
            if (is_file($p)) {
                @unlink($p);
            }
        }

        set_alert('success', _l('perfex_toolkit_alternative_logos_update_success'));
        redirect(admin_url('perfex_toolkit/alternative_logos'));
    }

    public function delete_logo($id = null)
    {
        $id = (int) $id;
        if ($id < 1) {
            set_alert('warning', _l('perfex_toolkit_alternative_logos_update_error_not_found'));
            redirect(admin_url('perfex_toolkit/alternative_logos'));
        }

        if (staff_cant('delete', 'customers')) {
            access_denied('customers');
        }

        $row = $this->alternative_logos_model->get_by_id($id);
        if (! $row) {
            set_alert('warning', _l('perfex_toolkit_alternative_logos_update_error_not_found'));
            redirect(admin_url('perfex_toolkit/alternative_logos'));
        }

        $ok = $this->alternative_logos_model->delete($id);
        if (! $ok) {
            set_alert('danger', _l('perfex_toolkit_alternative_logos_delete_error'));
            redirect(admin_url('perfex_toolkit/alternative_logos'));
        }

        $oldPath = (string) ($row->file_path ?? '');
        if ($oldPath !== '') {
            $p = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($oldPath, '/'));
            if (is_file($p)) {
                @unlink($p);
            }
        }

        set_alert('success', _l('perfex_toolkit_alternative_logos_delete_success'));
        redirect(admin_url('perfex_toolkit/alternative_logos'));
    }

    public function validate_alternative_logo_number($value)
    {
        $n = (int) $value;
        if ($n < Alternative_logos_model::MIN_LOGO_NUMBER) {
            $this->form_validation->set_message('validate_alternative_logo_number', _l('perfex_toolkit_alternative_logos_upload_error_number', (string) Alternative_logos_model::MIN_LOGO_NUMBER));

            return false;
        }
        $exclude = (int) $this->input->post('id');
        if ($this->alternative_logos_model->has_duplicate_number($n, $exclude)) {
            $this->form_validation->set_message('validate_alternative_logo_number', _l('perfex_toolkit_alternative_logos_upload_error_duplicate'));

            return false;
        }

        return true;
    }

    private function _pk_alt_process_uploaded_file(string $fieldName, string &$errorMessage)
    {
        $errorMessage = _l('perfex_toolkit_alternative_logos_upload_error_file');
        if (empty($_FILES[$fieldName]['name']) || (int) ($_FILES[$fieldName]['error'] ?? 0) !== 0) {
            return null;
        }

        $tmp  = $_FILES[$fieldName]['tmp_name'] ?? '';
        $name = $_FILES[$fieldName]['name'] ?? '';
        $size = (int) ($_FILES[$fieldName]['size'] ?? 0);
        if ($tmp === '' || ! is_uploaded_file($tmp)) {
            return null;
        }

        $max = 5 * 1024 * 1024;
        if ($size > $max) {
            $errorMessage = _l('perfex_toolkit_alternative_logos_upload_error_size');

            return null;
        }

        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (! in_array($ext, $exts, true)) {
            $errorMessage = _l('perfex_toolkit_alternative_logos_upload_error_mime');

            return null;
        }

        if (class_exists('finfo', false)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($tmp);
        } else {
            $mime = @mime_content_type($tmp) ?: '';
        }
        $mimes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];
        if ($ext === 'svg') {
            $okSvg = $mime === '' || in_array($mime, ['image/svg+xml', 'text/plain', 'text/xml', 'application/xml', 'text/html'], true);
            if (! $okSvg) {
                $errorMessage = _l('perfex_toolkit_alternative_logos_upload_error_mime');

                return null;
            }
        } elseif (! isset($mimes[$ext]) || ($mime !== '' && $mime !== $mimes[$ext])) {
            $errorMessage = _l('perfex_toolkit_alternative_logos_upload_error_mime');

            return null;
        }

        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/')
            . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfex_toolkit' . DIRECTORY_SEPARATOR . 'alternative_logos' . DIRECTORY_SEPARATOR;
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        if (! is_dir($uploadDir) || ! is_writable($uploadDir)) {
            $errorMessage = _l('perfex_toolkit_alternative_logos_upload_error_dir');

            return null;
        }

        $fileBase = 'ptk_' . uniqid('', true) . '.' . $ext;
        $destFs   = $uploadDir . $fileBase;
        if (! @move_uploaded_file($tmp, $destFs)) {
            $errorMessage = _l('perfex_toolkit_alternative_logos_upload_error_move');

            return null;
        }

        return 'perfex_toolkit/alternative_logos/' . $fileBase;
    }
}
