<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if (empty($tickets)) { ?>

<div class="text-center tw-py-8">
    <i class="fa fa-check-circle fa-2x text-success tw-block tw-mb-2"></i>
    <p class="text-muted tw-mb-0"><?= e(_l('perfex_toolkit_casehistory_none_found')); ?></p>
</div>

<?php } else { ?>

<div class="table-responsive">
    <table class="table table-striped table-hover tw-mb-0">
        <thead>
            <tr>
                <th style="width:1%;white-space:nowrap;">#</th>
                <th><?= e(_l('ticket_dt_subject')); ?></th>
                <th><?= e(_l('ticket_dt_department')); ?></th>
                <th><?= e(_l('ticket_dt_status')); ?></th>
                <th style="white-space:nowrap;"><?= e(_l('perfex_toolkit_casehistory_col_date')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets as $t) {
                $is_current = isset($current_ticket_id) && (int) $t['ticketid'] === (int) $current_ticket_id;
            ?>
            <tr<?= $is_current ? ' class="active"' : ''; ?>>
                <td>
                    <a href="<?= admin_url('tickets/ticket/' . (int) $t['ticketid']); ?>" target="_blank">
                        #<?= (int) $t['ticketid']; ?>
                    </a>
                    <?php if ($is_current) { ?>
                        <span class="label label-default tw-ml-1">current</span>
                    <?php } ?>
                </td>
                <td>
                    <a href="<?= admin_url('tickets/ticket/' . (int) $t['ticketid']); ?>" target="_blank">
                        <?= e($t['subject']); ?>
                    </a>
                </td>
                <td><?= e($t['department_name'] ?? '—'); ?></td>
                <td>
                    <?php $color = $t['statuscolor'] ?? '#888'; ?>
                    <span class="label" style="color:<?= e($color); ?>;border:1px solid <?= e($color); ?>;background:transparent;">
                        <?= e($t['status_name'] ?? '—'); ?>
                    </span>
                </td>
                <td><?= e(_dt($t['date'])); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<p class="text-muted tw-text-xs tw-mt-3 tw-mb-0 tw-px-1">
    <?= e(sprintf(_l('perfex_toolkit_casehistory_showing'), count($tickets))); ?>
</p>

<?php } ?>
