<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$minLogoNum  = (int) ($min_logo ?? 2);
$nextLogoNum = (int) ($next_logo ?? $minLogoNum);
$addOld      = (isset($add_old) && is_array($add_old)) ? $add_old : [];
$reopenAdd   = ! empty($reopen_add);
$reopenEdit  = ! empty($reopen_edit);
$editSt      = (isset($edit_state) && is_array($edit_state) && ! empty($edit_state['id'])) ? $edit_state : null;

$addFor  = (string) ($addOld['a_logo_for'] ?? '');
$addNum  = (int) (isset($addOld['a_logo_number']) ? $addOld['a_logo_number'] : $nextLogoNum);
$addDesc = (string) ($addOld['a_description'] ?? '');

$editId          = $editSt['id'] ?? 0;
$editFor         = (string) ($editSt['logo_for'] ?? '');
$editNum         = isset($editSt['logo_number']) ? (int) $editSt['logo_number'] : $minLogoNum;
$editDesc        = (string) ($editSt['description'] ?? '');
$editFileU       = (string) ($editSt['file_url'] ?? '');
$showEditPreview = $editFileU !== '';
?>
<div id="wrapper">
    <div class="content">
        <div class="row tw-mb-2">
            <div class="col-md-12">
                <div class="tw-mb-6">
                    <div class="tw-mb-3">
                        <h4 class="tw-my-0 tw-font-bold tw-text-xl">
                            <?= _l('perfex_toolkit_alternative_logos_title'); ?>
                        </h4>
                    </div>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center tw-gap-x-6">
                    <div class="tw-flex tw-justify-between tw-items-center tw-gap-x-1 tw-w-full">
                        <input type="search" class="form-control mbot15 mtop15" id="pk_alt_logo_for" name="pk_alt_logo_for_filter" autocomplete="off" style="max-width:280px" placeholder="<?= e(_l('search')); ?>…">
                        <?php if (staff_can('create', 'customers')) { ?>
                        <button type="button" class="btn btn-primary" data-toggle="modal"
                            data-target="#pk_alt_logo_upload_modal">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?= _l('perfex_toolkit_alternative_logos_btn_upload'); ?>
                        </button>
                        <?php } ?>
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
                            _l('pk_logo_for'),
                            _l('pk_logo_number'),
                            _l('logo_name'),
                            _l('pk_logo_description'),
                            _l('created_at'),
                        ];
                        render_datatable($table_data, 'pk_alternative_logos', [], [
                            'id' => 'pk-alternative-logos-dt',
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pk_alt_logo_upload_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= e(_l('perfex_toolkit_alternative_logos_upload_modal_title')); ?></h4>
            </div>
            <?php
            echo form_open_multipart(admin_url('perfex_toolkit/alternative_logos/upload_logo'), [
                'id' => 'pk_alt_logo_upload_form',
            ]);
            ?>
                <div class="modal-body">
                    <p class="text-muted tw-text-sm tw-mb-3"><?= e(_l('perfex_toolkit_alternative_logos_upload_modal_hint', $minLogoNum)); ?></p>
                    <?php
                    $value = set_value('a_logo_for', $addFor);
                    $attr  = ['placeholder' => _l('perfex_toolkit_alternative_logos_upload_ph_for')];
                    echo render_input('a_logo_for', 'pk_logo_for', $value, 'text', $attr);

                    $value = (string) set_value('a_logo_number', (string) $addNum);
                    $attr  = ['placeholder' => (string) $minLogoNum . '+'];
                    echo render_input('a_logo_number', 'pk_logo_number', $value, 'number', $attr);

                    $value = $addDesc;
                    $attr  = ['rows' => '2'];
                    echo render_textarea('a_description', 'pk_logo_description', $value, $attr);

                    $value = '';
                    $attr  = ['accept' => 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml,.svg'];
                    echo render_input('a_logo_file', 'pk_logo_file', $value, 'file', $attr);
                    ?>
                    <p class="help-block tw-mb-0 tw-mt-1"><?= e(_l('perfex_toolkit_alternative_logos_upload_file_help')); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= e(_l('close')); ?></button>
                    <button type="submit" class="btn btn-primary" id="pk_alt_upload_submit">
                        <i class="fa fa-upload tw-mr-1"></i><?= e(_l('perfex_toolkit_alternative_logos_btn_save')); ?>
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php if (staff_can('create', 'customers')) { ?>
<div class="modal fade" id="pk_alt_logo_edit_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= e(_l('perfex_toolkit_alternative_logos_edit_modal_title')); ?></h4>
            </div>
            <?php
            echo form_open_multipart(admin_url('perfex_toolkit/alternative_logos/update_logo'), [
                'id' => 'pk_alt_logo_edit_form',
            ]);
            ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="pk_alt_edit_id" value="<?= (int) set_value('id', (string) $editId); ?>">
                    <p class="text-muted tw-text-sm tw-mb-3"><?= e(_l('perfex_toolkit_alternative_logos_upload_modal_hint', $minLogoNum)); ?></p>
                    <div id="pk_alt_edit_file_preview" class="tw-mb-3" style="<?= $showEditPreview ? '' : 'display:none;'; ?>">
                        <p class="text-muted tw-text-xs tw-mb-1"><?= e(_l('perfex_toolkit_alternative_logos_current_file')); ?></p>
                        <img id="pk_alt_edit_file_prev_img" src="<?= e($editFileU); ?>" alt="" class="img-thumbnail" style="max-height:64px" loading="lazy">
                    </div>
                    <?php
                    $value = set_value('logo_for', $editFor);
                    $attr  = ['placeholder' => _l('perfex_toolkit_alternative_logos_upload_ph_for')];
                    echo render_input('logo_for', 'pk_logo_for', $value, 'text', $attr);

                    $value = (string) set_value('logo_number', (string) $editNum);
                    $attr  = ['placeholder' => (string) $minLogoNum . '+'];
                    echo render_input('logo_number', 'pk_logo_number', $value, 'number', $attr);

                    $value = $editDesc;
                    $attr  = ['rows' => '2'];
                    echo render_textarea('description', 'pk_logo_description', $value, $attr);

                    $value = '';
                    $attr  = ['accept' => 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml,.svg'];
                    echo render_input('logo_file', 'pk_logo_file', $value, 'file', $attr);
                    ?>
                    <p class="help-block tw-mb-0 tw-mt-1"><?= e(_l('perfex_toolkit_alternative_logos_file_replace_optional')); ?></p>
                    <p class="help-block tw-mb-0 tw-mt-1"><?= e(_l('perfex_toolkit_alternative_logos_upload_file_help')); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= e(_l('close')); ?></button>
                    <button type="submit" class="btn btn-primary" id="pk_alt_edit_submit">
                        <i class="fa fa-check tw-mr-1"></i><?= e(_l('submit')); ?>
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php } ?>

<?php init_tail(); ?>
<script>
    $(function() {
        var pkAltLogoServerParams = {
            pk_alt_logo_for: '#pk_alt_logo_for',
        };
        var notSortable = [3, 4];
        var $table = $('.table-pk_alternative_logos');
        initDataTable('.table-pk_alternative_logos', window.location.href, notSortable, notSortable, pkAltLogoServerParams, [0, 'desc']);

        function reloadPkAltLogos() {
            $table.DataTable().ajax.reload().columns.adjust();
        }

        var filterTimer;
        $('#pk_alt_logo_for').on('keyup change', function() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(reloadPkAltLogos, 500);
        });

        <?php if ($reopenAdd) { ?>
        $('#pk_alt_logo_upload_modal').modal('show');
        <?php } ?>

        var pkMinLogo = <?= (int) $minLogoNum; ?>;
        appValidateForm($('#pk_alt_logo_upload_form'), {
            a_logo_for: { required: true, maxlength: 191 },
            a_logo_number: { required: true, number: true, min: pkMinLogo },
            a_logo_file: { required: true },
        });

        <?php if (staff_can('create', 'customers')) { ?>
        appValidateForm($('#pk_alt_logo_edit_form'), {
            logo_for: { required: true, maxlength: 191 },
            logo_number: { required: true, number: true, min: pkMinLogo },
        });

        function resetEditModal() {
            var $f = $('#pk_alt_logo_edit_form');
            if ($f.length) {
                $f[0].reset();
                $('#pk_alt_edit_id').val('');
                $('#pk_alt_edit_file_prev_img').attr('src', '');
                $('#pk_alt_edit_file_preview').hide();
            }
        }

        <?php if ($reopenEdit) { ?>
        $('#pk_alt_logo_edit_modal').modal('show');
        <?php } ?>

        $('#pk_alt_logo_edit_modal').on('hidden.bs.modal', function() {
            resetEditModal();
        });

        $(document).on('click', '.pk-alt-logo-edit', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (!id) {
                return;
            }
            $.getJSON(admin_url + 'perfex_toolkit/alternative_logos/get_logo/' + id)
                .done(function(r) {
                    if (!r.success || !r.data) {
                        alert_float('warning', (r && r.message) ? r.message : <?= json_encode(_l('perfex_toolkit_alternative_logos_update_error_not_found')); ?>);
                        return;
                    }
                    var d = r.data;
                    var $ef = $('#pk_alt_logo_edit_form');
                    $ef.find('#pk_alt_edit_id').val(d.id);
                    $ef.find('#logo_for').val(d.logo_for);
                    $ef.find('#logo_number').val(d.logo_number);
                    $ef.find('#description').val(d.description);
                    $ef.find('#logo_file').val('');
                    if (d.file_url) {
                        $('#pk_alt_edit_file_prev_img').attr('src', d.file_url);
                        $('#pk_alt_edit_file_preview').show();
                    } else {
                        $('#pk_alt_edit_file_preview').hide();
                    }
                    $('#pk_alt_logo_edit_modal').modal('show');
                })
                .fail(function() {
                    alert_float('danger', <?= json_encode(_l('perfex_toolkit_alternative_logos_update_error_not_found')); ?>);
                });
        });
        <?php } ?>
    });
</script>
</body>
</html>
