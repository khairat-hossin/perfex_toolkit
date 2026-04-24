<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row tw-mb-2">
            <div class="col-md-12">
                <div class="tw-mb-6">
                    <div class="tw-mb-3">
                        <h4 class="tw-my-0 tw-font-bold tw-text-xl">
                            <i class="fa-solid fa-download tw-mr-2"></i>
                            <?= e(_l('perfex_toolkit_download_module_title')); ?>
                        </h4>
                        <p class="text-muted tw-mb-0 tw-mt-2">
                            <?= e(_l('perfex_toolkit_download_module_intro')); ?>
                        </p>
                    </div>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center tw-gap-x-6">
                    <div class="tw-flex tw-justify-between tw-items-center tw-gap-x-1 tw-w-full">
                        <input type="search" class="form-control mbot15 mtop15" id="ptk_download_module_for" name="ptk_download_module_for" autocomplete="off" style="max-width:280px" placeholder="<?= e(_l('search')); ?>…">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            _l('the_number_sign'),
                            _l('perfex_toolkit_download_module_col_name'),
                            _l('perfex_toolkit_download_module_col_folder'),
                            _l('perfex_toolkit_download_module_col_version'),
                            _l('perfex_toolkit_download_module_col_status'),
                            [
                                'name'     => _l('perfex_toolkit_download_module_col_action'),
                                'th_attrs' => [
                                    'class' => 'text-center',
                                    'style' => 'min-width: 140px; width: 1%; white-space: nowrap;',
                                ],
                            ],
                        ];
                        render_datatable($table_data, 'ptk_download_modules', [], [
                            'id' => 'ptk-download-modules-dt',
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function () {
    var ptkDmServerParams = {
        ptk_download_module_for: '#ptk_download_module_for',
    };
    var notSearchable = [0, 5];
    var notSortable = [0, 5];
    var $table = $('.table-ptk_download_modules');
    initDataTable('.table-ptk_download_modules', window.location.href, notSearchable, notSortable, ptkDmServerParams, [1, 'asc']);

    var filterTimer;
    $('#ptk_download_module_for').on('keyup change', function () {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function () {
            $table.DataTable().ajax.reload().columns.adjust();
        }, 500);
    });
});
</script>
</body>
</html>
