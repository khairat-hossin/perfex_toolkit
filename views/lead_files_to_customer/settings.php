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
                    <i class="fa-solid fa-file-export tw-mr-2"></i>
                    <?= e(_l('perfex_toolkit_lftc_title')); ?>
                </h4>
                <p class="text-muted tw-mb-0">
                    <?= e(_l('perfex_toolkit_lftc_intro')); ?>
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-lg-6">
                <div class="panel_s">
                    <div class="panel-body">

                        <h5 class="tw-font-semibold tw-mt-0 tw-mb-4 tw-border-b tw-pb-3 tw-border-neutral-200">
                            <?= e(_l('perfex_toolkit_lftc_settings_heading')); ?>
                        </h5>

                        <div class="row mtop10">
                            <div class="col-md-9">
                                <p class="tw-font-medium tw-mb-1 tw-mt-0">
                                    <?= e(_l('perfex_toolkit_lftc_option_label')); ?>
                                </p>
                                <p class="text-muted tw-text-sm tw-mb-0">
                                    <?= e(_l('perfex_toolkit_lftc_option_help')); ?>
                                </p>
                            </div>
                            <div class="col-md-3 mtop10">
                                <div class="onoffswitch">
                                    <input type="checkbox"
                                        name="onoffswitch"
                                        class="onoffswitch-checkbox"
                                        id="ptk-lftc-enabled"
                                        <?= $enabled ? 'checked' : ''; ?>>
                                    <label class="onoffswitch-label" for="ptk-lftc-enabled"></label>
                                </div>
                            </div>
                        </div>

                        <div class="tw-mt-4 tw-pt-3 tw-border-t tw-border-neutral-200">
                            <div class="alert alert-info tw-text-sm tw-mb-0">
                                <i class="fa fa-info-circle tw-mr-1"></i>
                                <?= e(_l('perfex_toolkit_lftc_note')); ?>
                            </div>
                        </div>

                        <div class="tw-mt-4">
                            <button type="button" id="ptk-lftc-save" class="btn btn-primary">
                                <i class="fa fa-save tw-mr-1"></i>
                                <?= e(_l('perfex_toolkit_lftc_save_btn')); ?>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php init_tail(); ?>
<script>
$(function () {
    $('#ptk-lftc-save').on('click', function () {
        var $btn = $(this).prop('disabled', true);

        $.ajax({
            url      : '<?= admin_url('perfex_toolkit/lead_files_to_customer/save'); ?>',
            type     : 'POST',
            dataType : 'json',
            data     : {
                enabled : $('#ptk-lftc-enabled').is(':checked') ? 1 : 0,
                <?= $this->security->get_csrf_token_name(); ?> : '<?= $this->security->get_csrf_hash(); ?>'
            },
            success : function (resp) {
                if (resp.success) {
                    alert_float('success', resp.message);
                } else {
                    alert_float('danger', resp.message);
                }
                $btn.prop('disabled', false);
            },
            error : function () {
                alert_float('danger', '<?= e(_l('perfex_toolkit_feature_toggle_error')); ?>');
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
</body>
</html>
