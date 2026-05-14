<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row tw-mb-4">
            <div class="col-md-12">
                <a href="<?= admin_url('perfex_toolkit'); ?>" class="tw-text-sm text-muted">
                    <i class="fa fa-arrow-left tw-mr-1"></i>
                    <?= e(_l('perfex_toolkit_back_dashboard')); ?>
                </a>
                <h4 class="tw-mt-2 tw-mb-1 tw-font-bold tw-text-xl">
                    <i class="fa-solid fa-copy tw-mr-2"></i>
                    <?= e(_l('perfex_toolkit_dup_wtl_title')); ?>
                </h4>
                <p class="text-muted tw-mb-0">
                    <?= e(_l('perfex_toolkit_dup_wtl_intro')); ?>
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <table class="table table-striped" id="ptk-dup-wtl-table">
                            <thead>
                                <tr>
                                    <th><?= e(_l('perfex_toolkit_dup_wtl_col_name')); ?></th>
                                    <th><?= e(_l('perfex_toolkit_dup_wtl_col_source')); ?></th>
                                    <th><?= e(_l('perfex_toolkit_dup_wtl_col_status')); ?></th>
                                    <th><?= e(_l('perfex_toolkit_dup_wtl_col_created')); ?></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    initDataTable('#ptk-dup-wtl-table', '<?= admin_url('perfex_toolkit/duplicate_wtl_form'); ?>', [], []);
</script>
</body>

</html>
