<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row tw-mb-2">
            <div class="col-md-12">
                <a href="<?= admin_url('perfex_toolkit'); ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left tw-mr-1"></i><?= e(_l('perfex_toolkit_back_dashboard')); ?>
                </a>
            </div>
        </div>
        <div class="row tw-mb-3">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-mb-2 tw-font-bold tw-text-xl">
                    <?= _l('perfex_toolkit_delete_invoices_title'); ?>
                </h4>
                <p class="text-muted tw-mb-0">
                    <?= _l('perfex_toolkit_delete_invoices_warn'); ?>
                </p>
            </div>
        </div>

        <div class="row tw-mb-3">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5 class="tw-font-semibold tw-mb-3"><?= _l('perfex_toolkit_delete_invoices_filters'); ?></h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pk_delete_invoices_status"><?= _l('invoice_dt_table_heading_status'); ?></label>
                                    <select id="pk_delete_invoices_status" class="selectpicker" data-width="100%"
                                        data-none-selected-text="<?= e(_l('dropdown_non_selected_tex')); ?>">
                                        <option value=""><?= _l('all'); ?></option>
                                        <?php foreach ($this->invoices_model->get_statuses() as $st) { ?>
                                        <option value="<?= (int) $st; ?>"><?= format_invoice_status($st, '', false); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <?= render_date_input('pk_delete_invoices_date_from', _l('perfex_toolkit_delete_invoices_date_from')); ?>
                            </div>
                            <div class="col-md-4">
                                <?= render_date_input('pk_delete_invoices_date_to', _l('perfex_toolkit_delete_invoices_date_to')); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-2">
                    <?php if (staff_can('delete', 'invoices')) { ?>
                    <a href="#" data-toggle="modal" data-target="#pk_delete_invoices_modal"
                        class="hide bulk-actions-btn table-btn"
                        data-table=".table-pk_delete_invoices"><?= _l('bulk_actions'); ?></a>
                    <?php } ?>
                    <div class="clearfix"></div>
                </div>

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            [
                                'name'     => '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="pk_delete_invoices_mass_select_all" data-to-table="pk_delete_invoices"><label></label></div>',
                                'th_attrs' => ['class' => staff_can('delete', 'invoices') ? '' : 'not_visible'],
                            ],
                            _l('invoice_dt_table_heading_number'),
                            _l('invoice_dt_table_heading_date'),
                            _l('invoice_dt_table_heading_client'),
                            _l('invoice_total'),
                            _l('invoice_dt_table_heading_status'),
                        ];
                        render_datatable($table_data, 'pk_delete_invoices', [], [
                            'id' => 'pk-delete-invoices-dt',
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (staff_can('delete', 'invoices')) { ?>
<div class="modal fade bulk_actions" id="pk_delete_invoices_modal" tabindex="-1" role="dialog"
    data-table=".table-pk_delete_invoices">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <?= _l('perfex_toolkit_delete_invoices_modal_title'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <p><?= _l('perfex_toolkit_delete_invoices_modal_body'); ?></p>
                <div class="checkbox checkbox-danger">
                    <input type="checkbox" name="pk_delete_invoices_mass_delete" id="pk_delete_invoices_mass_delete">
                    <label for="pk_delete_invoices_mass_delete"><?= _l('mass_delete'); ?></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <a href="#" class="btn btn-danger" id="pk_delete_invoices_confirm"><?= _l('confirm'); ?></a>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php init_tail(); ?>
<script>
    $(function() {
        var pkDeleteInvoicesServerParams = {
            pk_delete_invoices_status: '#pk_delete_invoices_status',
            pk_delete_invoices_date_from: '#pk_delete_invoices_date_from',
            pk_delete_invoices_date_to: '#pk_delete_invoices_date_to',
        };
        var notSortable = [0];
        var $pkDeleteInvoicesTable = $('.table-pk_delete_invoices');
        initDataTable('.table-pk_delete_invoices', window.location.href, notSortable, notSortable, pkDeleteInvoicesServerParams, [1, 'desc']);

        function reloadPkDeleteInvoicesTable() {
            $pkDeleteInvoicesTable.DataTable().ajax.reload().columns.adjust();
        }

        $('body').on('change', '#pk_delete_invoices_mass_select_all', function() {
            var to = $(this).data('to-table');
            var rows = $('.table-' + to).find('tbody tr');
            var checked = $(this).prop('checked');
            $.each(rows, function() {
                $($(this).find('td').eq(0)).find('input').prop('checked', checked);
            });
        });

        $('#pk_delete_invoices_status').on('change', reloadPkDeleteInvoicesTable);
        $('#pk_delete_invoices_date_from,#pk_delete_invoices_date_to').on('change', reloadPkDeleteInvoicesTable);

        <?php if (staff_can('delete', 'invoices')) { ?>
        $('#pk_delete_invoices_confirm').on('click', function(e) {
            e.preventDefault();
            if (!$('#pk_delete_invoices_mass_delete').prop('checked')) {
                return false;
            }
            if (typeof confirm_delete === 'function' && !confirm_delete()) {
                return false;
            }
            var ids = [];
            $($('#pk_delete_invoices_modal').attr('data-table')).find('tbody tr').each(function() {
                var $cb = $($(this).find('td').eq(0)).find('input[type="checkbox"]');
                if ($cb.prop('checked')) {
                    ids.push($cb.val());
                }
            });
            if (ids.length === 0) {
                alert_float('warning', <?= json_encode(_l('perfex_toolkit_delete_invoices_none_selected')); ?>);
                return false;
            }
            var $btn = $(this);
            $btn.addClass('disabled');
            var postData = {
                ids: ids
            };
            if (typeof csrfData !== 'undefined') {
                postData[csrfData.token_name] = csrfData.hash;
            }
            $.post(admin_url + 'perfex_toolkit/delete_invoices_action', postData).done(function(resp) {
                var r = typeof resp === 'string' ? JSON.parse(resp) : resp;
                alert_float(r.success ? 'success' : 'warning', r.message);
                $('#pk_delete_invoices_modal').modal('hide');
                $('#pk_delete_invoices_mass_delete').prop('checked', false);
                $('#pk_delete_invoices_mass_select_all').prop('checked', false);
                reloadPkDeleteInvoicesTable();
            }).always(function() {
                $btn.removeClass('disabled');
            });
        });
        <?php } ?>
    });
</script>
</body>

</html>
