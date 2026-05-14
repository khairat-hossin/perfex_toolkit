<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$aColumns = [
    db_prefix() . 'web_to_lead.name as form_name',
    db_prefix() . 'leads_sources.name as source_name',
    db_prefix() . 'leads_status.name as status_name',
    db_prefix() . 'web_to_lead.dateadded as dateadded',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'web_to_lead';

$join = [
    'LEFT JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'web_to_lead.lead_source',
    'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'web_to_lead.lead_status',
];

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    [],
    [db_prefix() . 'web_to_lead.id as id']
);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $nameCell = '<a href="' . admin_url('leads/form/' . (int) $aRow['id']) . '" target="_blank" class="tw-font-medium">'
        . e($aRow['form_name'])
        . ' <i class="fa-solid fa-arrow-up-right-from-square tw-text-xs tw-opacity-50 tw-ml-1"></i></a>';

    $nameCell .= '<div class="row-options">'
        . '<a href="' . admin_url('perfex_toolkit/duplicate_wtl_form/duplicate/' . (int) $aRow['id']) . '"'
        . ' class="text-primary"'
        . ' onclick="return confirm(\'' . e(_l('perfex_toolkit_dup_wtl_confirm')) . '\')">'
        . '<i class="fa-solid fa-copy tw-mr-1"></i>' . e(_l('perfex_toolkit_dup_wtl_btn_duplicate'))
        . '</a>'
        . '</div>';

    $row[] = $nameCell;
    $row[] = e($aRow['source_name'] ?? '—');
    $row[] = e($aRow['status_name'] ?? '—');
    $row[] = ! empty($aRow['dateadded']) ? e(_dt($aRow['dateadded'])) : '—';

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
