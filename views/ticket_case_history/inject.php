<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- PTK: Non-Customer Case History Modal -->
<div class="modal fade" id="ptk-case-history-modal" tabindex="-1" role="dialog" aria-labelledby="ptk-case-history-title">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="ptk-case-history-title">
                    <i class="fa fa-clock-rotate-left tw-mr-2"></i>
                    <?= e(_l('perfex_toolkit_casehistory_modal_title')); ?>
                    <small class="text-muted">&nbsp;<?= e($email); ?></small>
                </h4>
            </div>
            <div class="modal-body" id="ptk-case-history-body">
                <div class="text-center tw-py-8">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= e(_l('close')); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    'use strict';

    var ptk_ch_count   = <?= (int) $other_count; ?>;
    var ptk_ch_email   = <?= json_encode($email); ?>;
    var ptk_ch_exclude = <?= (int) $ticket->ticketid; ?>;
    var ptk_ch_url     = admin_url + 'perfex_toolkit/ticket_case_history?email=' +
                         encodeURIComponent(ptk_ch_email) + '&exclude=' + ptk_ch_exclude;
    var ptk_ch_loaded  = false;

    $(function () {

        /* 1 ─ Count badge on the existing "Other Tickets" tab */
        var $tab = $('[href="#othertickets"]');
        if ($tab.length && $tab.find('.ptk-ch-badge').length === 0) {
            var badgeHtml = ptk_ch_count > 0
                ? ' <span class="badge ptk-ch-badge" style="background:#e74c3c;">' + ptk_ch_count + '</span>'
                : ' <span class="badge ptk-ch-badge" style="background:#aaa;">0</span>';
            $tab.append(badgeHtml);
        }

        /* 2 ─ Link below the email field in the right-side settings panel */
        var $emailInput = $('#settings').find('input[name="email"]');
        if ($emailInput.length && !$emailInput.data('ptk-ch-init')) {
            $emailInput.data('ptk-ch-init', true);

            var $link = $('<a href="#" class="help-block tw-text-sm tw-mt-1 tw-mb-0 ptk-ch-trigger"></a>');

            if (ptk_ch_count > 0) {
                $link
                    .addClass('text-warning')
                    .html('<i class="fa fa-exclamation-triangle tw-mr-1"></i>' +
                          <?= json_encode(_l('perfex_toolkit_casehistory_has_previous')); ?>.replace('{n}', ptk_ch_count));
            } else {
                $link
                    .addClass('text-muted')
                    .html('<i class="fa fa-search tw-mr-1"></i>' +
                          <?= json_encode(_l('perfex_toolkit_casehistory_no_previous')); ?>);
            }

            $emailInput.closest('.form-group').append($link);
        }

        /* 3 ─ Open modal and lazy-load results */
        $(document).on('click', '.ptk-ch-trigger', function (e) {
            e.preventDefault();
            var $modal = $('#ptk-case-history-modal');
            var $body  = $('#ptk-case-history-body');

            if (!ptk_ch_loaded) {
                $body.html('<div class="text-center tw-py-8"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>');
                $modal.modal('show');

                $.get(ptk_ch_url)
                    .done(function (html) {
                        ptk_ch_loaded = true;
                        $body.html(html);
                    })
                    .fail(function () {
                        $body.html('<p class="text-danger text-center tw-py-4">' +
                                   <?= json_encode(_l('perfex_toolkit_casehistory_load_error')); ?> + '</p>');
                    });
            } else {
                $modal.modal('show');
            }
        });
    });
}(jQuery));
</script>
