<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row tw-mb-4">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-mb-1 tw-font-bold tw-text-xl">
                    <?= e(_l('perfex_toolkit_dashboard')); ?>
                </h4>
                <p class="text-muted tw-mb-0">
                    <?= e(_l('perfex_toolkit_dashboard_intro')); ?>
                </p>
            </div>
        </div>

        <div class="row tw-flex tw-flex-wrap" id="ptk-feature-cards">
            <?php foreach ($features as $feature) { ?>
            <div class="col-md-4 col-lg-3 tw-mb-4 tw-flex tw-flex-col" id="ptk-card-<?= e($feature['key']); ?>">
                <div class="panel_s tw-h-full tw-flex tw-flex-col">
                    <div class="panel-body tw-flex-1">
                        <div class="tw-flex tw-items-start tw-gap-3">
                            <span class="tw-inline-flex tw-h-10 tw-w-10 tw-items-center tw-justify-center tw-rounded-lg tw-text-lg
                                <?= ! empty($feature['active']) ? 'tw-bg-neutral-100 tw-text-neutral-600' : 'tw-bg-neutral-50 tw-text-neutral-400'; ?>">
                                <i class="<?= e($feature['icon']); ?>"></i>
                            </span>
                            <div class="tw-min-w-0 tw-flex-1">
                                <div class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                                    <h5 class="tw-font-semibold tw-mt-0 tw-mb-0 <?= empty($feature['active']) ? 'tw-text-neutral-400' : ''; ?>">
                                        <?= e($feature['name']); ?>
                                    </h5>
                                    <?php if (! empty($feature['active'])) { ?>
                                    <span class="label label-success tw-text-xs"><?= e(_l('perfex_toolkit_feature_status_active')); ?></span>
                                    <?php } else { ?>
                                    <span class="label label-default tw-text-xs"><?= e(_l('perfex_toolkit_feature_status_inactive')); ?></span>
                                    <?php } ?>
                                </div>
                                <p class="text-muted tw-text-sm tw-mb-0 tw-leading-relaxed <?= empty($feature['active']) ? 'tw-opacity-60' : ''; ?>">
                                    <?= e($feature['description']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer tw-border-t tw-border-neutral-200/80 tw-bg-neutral-50/50 tw-px-4 tw-py-3 tw-flex tw-items-center tw-gap-2">
                        <?php if (! empty($feature['available']) && ! empty($feature['active'])) { ?>
                            <a href="<?= e($feature['url']); ?>" class="btn btn-primary btn-sm">
                                <?= e(_l('perfex_toolkit_open_feature')); ?>
                                <i class="fa fa-arrow-right tw-ml-1"></i>
                            </a>
                        <?php } elseif (empty($feature['available'])) { ?>
                            <span class="text-muted tw-text-sm" data-toggle="tooltip"
                                data-title="<?= e(_l('perfex_toolkit_feature_no_access')); ?>">
                                <i class="fa fa-lock tw-mr-1"></i>
                                <?= e(_l('perfex_toolkit_feature_no_access_short')); ?>
                            </span>
                        <?php } ?>

                        <?php if (is_admin()) { ?>
                            <?php if (! empty($feature['active'])) { ?>
                            <button type="button"
                                class="btn btn-default btn-sm ptk-toggle-feature tw-ml-auto"
                                data-key="<?= e($feature['key']); ?>"
                                data-action="deactivate"
                                data-confirm="<?= e(_l('perfex_toolkit_feature_deactivate_confirm')); ?>">
                                <i class="fa fa-toggle-on tw-mr-1"></i>
                                <?= e(_l('perfex_toolkit_feature_btn_deactivate')); ?>
                            </button>
                            <?php } else { ?>
                            <button type="button"
                                class="btn btn-default btn-sm ptk-toggle-feature <?= empty($feature['available']) ? 'tw-ml-auto' : ''; ?>"
                                data-key="<?= e($feature['key']); ?>"
                                data-action="activate">
                                <i class="fa fa-toggle-off tw-mr-1"></i>
                                <?= e(_l('perfex_toolkit_feature_btn_activate')); ?>
                            </button>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function () {
    $(document).on('click', '.ptk-toggle-feature', function () {
        var $btn    = $(this);
        var key     = $btn.data('key');
        var action  = $btn.data('action');
        var confirm_msg = $btn.data('confirm');

        if (action === 'deactivate' && confirm_msg) {
            if (! confirm(confirm_msg)) return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url  : '<?= admin_url('perfex_toolkit/toggle_feature'); ?>',
            type : 'POST',
            data : {
                feature_key : key,
                action      : action,
                <?= $this->security->get_csrf_token_name(); ?> : '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType : 'json',
            success  : function (resp) {
                if (resp.success) {
                    alert_float(resp.is_active ? 'success' : 'warning', resp.message);
                    // reload the page so card state, menu, etc. all update consistently
                    window.location.reload();
                } else {
                    alert_float('danger', resp.message);
                    $btn.prop('disabled', false);
                }
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
