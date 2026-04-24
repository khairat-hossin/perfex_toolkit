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

        <div class="row">
            <?php foreach ($features as $feature) { ?>
            <div class="col-md-6 col-lg-4 tw-mb-4">
                <div class="panel_s tw-h-full tw-flex tw-flex-col">
                    <div class="panel-body tw-flex-1">
                        <div class="tw-flex tw-items-start tw-gap-3">
                            <span
                                class="tw-inline-flex tw-h-10 tw-w-10 tw-items-center tw-justify-center tw-rounded-lg tw-bg-neutral-100 tw-text-lg tw-text-neutral-600">
                                <i class="<?= e($feature['icon']); ?>"></i>
                            </span>
                            <div class="tw-min-w-0 tw-flex-1">
                                <h5 class="tw-font-semibold tw-mt-0 tw-mb-2">
                                    <?= e($feature['name']); ?>
                                </h5>
                                <p class="text-muted tw-text-sm tw-mb-0 tw-leading-relaxed">
                                    <?= e($feature['description']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="panel-footer tw-border-t tw-border-neutral-200/80 tw-bg-neutral-50/50 tw-px-4 tw-py-3">
                        <?php if (! empty($feature['available'])) { ?>
                        <a href="<?= e($feature['url']); ?>"
                            class="btn btn-primary">
                            <?= e(_l('perfex_toolkit_open_feature')); ?>
                            <i class="fa fa-arrow-right tw-ml-1"></i>
                        </a>
                        <?php } else { ?>
                        <span class="text-muted tw-text-sm" data-toggle="tooltip"
                            data-title="<?= e(_l('perfex_toolkit_feature_no_access')); ?>">
                            <i class="fa fa-lock tw-mr-1"></i>
                            <?= e(_l('perfex_toolkit_feature_no_access_short')); ?>
                        </span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>
