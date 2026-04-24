<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('invoices_model');

$aColumns = [
    '1',
    db_prefix() . 'invoices.number as invoice_number',
    db_prefix() . 'invoices.date as invoice_date',
    get_sql_select_client_company(),
    db_prefix() . 'invoices.total as invoice_total',
    db_prefix() . 'invoices.status as invoice_status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'invoices';

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency',
];

$where = [];

if (staff_cant('view', 'invoices')) {
    $where[] = 'AND ' . get_invoices_where_sql_for_staff(get_staff_user_id());
}

$statusFilter = $CI->input->post('pk_delete_invoices_status');
if ($statusFilter !== null && $statusFilter !== '' && is_numeric($statusFilter)) {
    $where[] = 'AND ' . db_prefix() . 'invoices.status=' . (int) $statusFilter;
}

$dateFrom = $CI->input->post('pk_delete_invoices_date_from');
if ($dateFrom !== null && $dateFrom !== '') {
    $where[] = 'AND ' . db_prefix() . 'invoices.date >= ' . $CI->db->escape($dateFrom);
}

$dateTo = $CI->input->post('pk_delete_invoices_date_to');
if ($dateTo !== null && $dateTo !== '') {
    $where[] = 'AND ' . db_prefix() . 'invoices.date <= ' . $CI->db->escape($dateTo);
}

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    [
        db_prefix() . 'invoices.id as id',
        db_prefix() . 'invoices.clientid as clientid',
        db_prefix() . 'currencies.name as currency_name',
        'formatted_number',
        'deleted_customer_name',
    ]
);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = staff_can('delete', 'invoices')
        ? '<div class="checkbox"><input type="checkbox" value="' . (int) $aRow['id'] . '"><label></label></div>'
        : '';

    $formattedNumber = format_invoice_number($aRow['id']);
    if (empty($aRow['formatted_number']) || $formattedNumber !== $aRow['formatted_number']) {
        $CI->invoices_model->save_formatted_number($aRow['id']);
    }

    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" onclick="init_invoice(' . (int) $aRow['id'] . '); return false;" class="tw-font-medium">' . e($formattedNumber) . '</a>';

    $row[] = e(_d($aRow['invoice_date']));

    if (empty($aRow['deleted_customer_name'])) {
        $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . e($aRow['company']) . '</a>';
    } else {
        $row[] = e($aRow['deleted_customer_name']);
    }

    $row[] = '<span class="tw-font-medium">' . e(app_format_money($aRow['invoice_total'], $aRow['currency_name'])) . '</span>';

    $row[] = format_invoice_status($aRow['invoice_status']);

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
