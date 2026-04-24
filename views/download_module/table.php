<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->helper('perfex_toolkit/perfex_toolkit');

$data     = $CI->input->post();
$draw     = isset($data['draw']) ? (int) $data['draw'] : 0;
$start    = isset($data['start']) ? (int) $data['start'] : 0;
$length   = isset($data['length']) ? (int) $data['length'] : 25;
$searchQ  = isset($data['search']['value']) ? trim((string) $data['search']['value']) : '';
$extraQ   = isset($data['ptk_download_module_for']) ? trim((string) $data['ptk_download_module_for']) : '';
$filter   = trim($searchQ . ' ' . $extraQ);

if ($length < 1) {
    $length = 25;
}

$all = ptk_get_installed_modules();
foreach ($all as $k => $m) {
    $all[$k]['is_active'] = $CI->app_modules->is_active($m['slug']);
}

$filtered = $all;
if ($filter !== '') {
    $filtered = array_values(array_filter($all, static function ($m) use ($filter) {
        return stripos($m['name'], $filter) !== false
            || stripos($m['slug'], $filter) !== false
            || stripos($m['version'], $filter) !== false;
    }));
}

$total         = count($all);
$filteredTotal = count($filtered);

$orderCol = 1;
$orderDir = 'asc';
if (! empty($data['order'][0])) {
    $orderCol = (int) ($data['order'][0]['column'] ?? 1);
    $dir      = strtolower((string) ($data['order'][0]['dir'] ?? 'asc'));
    $orderDir = $dir === 'desc' ? 'desc' : 'asc';
}
if (! in_array($orderCol, [1, 2, 3, 4], true)) {
    $orderCol = 1;
}

$sortKey = 'name';
if ($orderCol === 2) {
    $sortKey = 'slug';
} elseif ($orderCol === 3) {
    $sortKey = 'version';
} elseif ($orderCol === 4) {
    $sortKey = 'is_active';
} elseif ($orderCol === 1) {
    $sortKey = 'name';
}

usort($filtered, static function ($a, $b) use ($sortKey, $orderDir) {
    if ($sortKey === 'is_active') {
        $ac = ! empty($a['is_active']) ? 1 : 0;
        $bc = ! empty($b['is_active']) ? 1 : 0;
        if ($ac !== $bc) {
            $cmp = $ac - $bc;
        } else {
            $cmp = strcasecmp((string) $a['name'], (string) $b['name']);
        }
    } elseif ($sortKey === 'version') {
        $cmp = strnatcasecmp((string) $a['version'], (string) $b['version']);
    } else {
        $cmp = strcasecmp((string) $a[$sortKey], (string) $b[$sortKey]);
    }

    return $orderDir === 'desc' ? -$cmp : $cmp;
});

$page = array_slice($filtered, $start, $length);

$aaData = [];
foreach ($page as $i => $m) {
    $row   = [];
    $row[] = $start + $i + 1;
    $row[] = e($m['name']);
    $row[] = '<code>' . e($m['slug']) . '</code>';
    $row[] = e($m['version']);
    if (! empty($m['is_active'])) {
        $row[] = '<span class="label label-success">' . e(_l('perfex_toolkit_feature_status_active')) . '</span>';
    } else {
        $row[] = '<span class="label label-default">' . e(_l('perfex_toolkit_feature_status_inactive')) . '</span>';
    }
    $row[] = '<a href="' . admin_url('perfex_toolkit/download_module/download/' . rawurlencode($m['slug'])) . '"'
        . ' class="btn btn-success btn-sm text-nowrap"'
        . ' style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;max-width:none">'
        . '<i class="fa fa-download" aria-hidden="true"></i>'
        . '<span>' . e(_l('perfex_toolkit_download_module_btn_download')) . '</span>'
        . '</a>';

    $aaData[] = $row;
}

$output = [
    'draw'                 => $draw,
    'iTotalRecords'        => $total,
    'iTotalDisplayRecords' => $filteredTotal,
    'aaData'               => $aaData,
];
