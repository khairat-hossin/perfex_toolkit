<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$aColumns = [
    'id',
    'logo_for',
    'logo_number',
    'file_path',
    'description',
    'created_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'ptk_alternative_logos';
$join         = [];
$where        = [];

$logoFor = $CI->input->post('pk_alt_logo_for');
if ($logoFor !== null && $logoFor !== '') {
    $like    = '%' . $CI->db->escape_like_str($logoFor) . '%';
    $where[] = 'AND ' . db_prefix() . 'ptk_alternative_logos.logo_for LIKE ' . $CI->db->escape($like);
}

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where
);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = (int) $aRow['id'];

    $nameRow = e($aRow['logo_for']);
    if (staff_can('create', 'customers') || staff_can('delete', 'customers')) {
        $nameRow .= '<div class="row-options">';
        if (staff_can('create', 'customers')) {
            $nameRow .= '<a href="#" class="text-primary pk-alt-logo-edit" data-id="' . (int) $aRow['id'] . '" title="' . e(_l('edit')) . '">' . e(_l('edit')) . '</a>';
        }
        if (staff_can('delete', 'customers')) {
            $prefix = staff_can('create', 'customers') ? ' | ' : '';
            $nameRow .= $prefix . '<a href="' . admin_url('perfex_toolkit/alternative_logos/delete_logo/' . (int) $aRow['id']) . '" class="text-danger" onclick="return confirm(\'' . e(_l('perfex_toolkit_alternative_logos_delete_confirm')) . '\')" title="' . e(_l('delete')) . '">' . e(_l('delete')) . '</a>';
        }
        $nameRow .= '</div>';
    }
    $row[] = $nameRow;

    $row[] = (int) $aRow['logo_number'];

    $fp = $aRow['file_path'] ?? '';
    if ($fp !== '' && $fp !== null) {
        $url   = base_url('uploads/' . ltrim(str_replace('\\', '/', (string) $fp), '/'));
        $row[] = '<a href="' . e($url) . '" target="_blank" rel="noopener"><img src="' . e($url) . '" alt="" class="img-thumbnail" style="max-height:40px;max-width:80px" loading="lazy"></a>';
    } else {
        $row[] = '<span class="text-muted">—</span>';
    }

    $desc = (string) ($aRow['description'] ?? '');
    if (function_exists('mb_strlen') && mb_strlen($desc) > 120) {
        $desc = mb_substr($desc, 0, 120) . '…';
    } elseif (strlen($desc) > 120) {
        $desc = substr($desc, 0, 120) . '…';
    }
    $row[] = e($desc);

    $row[] = ! empty($aRow['created_at']) ? e(_dt($aRow['created_at'])) : '';

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
