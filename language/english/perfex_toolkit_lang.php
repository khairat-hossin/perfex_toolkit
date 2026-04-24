<?php

defined('BASEPATH') or exit('No direct script access allowed');

$lang['perfex_toolkit_menu']                   = 'Perfex Toolkit';
$lang['perfex_toolkit_nav_dashboard']          = 'Dashboard';
$lang['perfex_toolkit_nav_delete_invoices']    = 'Delete invoices';
$lang['perfex_toolkit_nav_alternative_logos']  = 'Alternative logos';

$lang['perfex_toolkit_dashboard']       = 'Perfex Toolkit';
$lang['perfex_toolkit_dashboard_intro'] = 'Essential tools in one place. Open a feature below for filters, actions, and help text.';
$lang['perfex_toolkit_back_dashboard']  = 'Back to dashboard';
$lang['perfex_toolkit_open_feature']    = 'Open';
$lang['perfex_toolkit_feature_no_access']       = 'You do not have permission to use this feature.';
$lang['perfex_toolkit_feature_no_access_short'] = 'Not available for your role';

$lang['perfex_toolkit_feature_status_active']   = 'Active';
$lang['perfex_toolkit_feature_status_inactive'] = 'Inactive';
$lang['perfex_toolkit_feature_btn_activate']    = 'Activate';
$lang['perfex_toolkit_feature_btn_deactivate']  = 'Deactivate';
$lang['perfex_toolkit_feature_deactivate_confirm']          = 'Deactivate this feature? It will be hidden from the menu until re-activated.';
$lang['perfex_toolkit_feature_toggle_activate_success']     = 'Feature activated successfully.';
$lang['perfex_toolkit_feature_toggle_deactivate_success']   = 'Feature deactivated successfully.';
$lang['perfex_toolkit_feature_toggle_error']                = 'Could not update feature status. Please try again.';
$lang['perfex_toolkit_feature_toggle_invalid']              = 'Invalid request.';
$lang['perfex_toolkit_feature_not_active']                  = 'This feature is not active. Activate it from the Toolkit dashboard.';

$lang['perfex_toolkit_feature_delete_invoices_name'] = 'Delete invoices';
$lang['perfex_toolkit_feature_delete_invoices_desc'] = 'Filter by status and date, select invoices, and delete in bulk. Uses the same delete rules as the rest of Perfex (e.g. last invoice, payments).';
$lang['perfex_toolkit_feature_alternative_logos_name'] = 'Alternative logos';
$lang['perfex_toolkit_feature_alternative_logos_desc'] = 'Manage extra logo slots (number 2+; slot 1 is the main company logo).';

$lang['perfex_toolkit_delete_invoices_title']        = 'Delete invoices';
$lang['perfex_toolkit_delete_invoices_warn']         = 'This will permanently delete selected invoices and related data (payments, items, etc.) according to your CRM settings.';
$lang['perfex_toolkit_delete_invoices_filters']      = 'Filters';
$lang['perfex_toolkit_delete_invoices_date_from']    = 'Date from';
$lang['perfex_toolkit_delete_invoices_date_to']      = 'Date to';
$lang['perfex_toolkit_delete_invoices_modal_title']  = 'Delete selected invoices';
$lang['perfex_toolkit_delete_invoices_modal_body']   = 'Confirm mass delete: all selected invoices will be removed according to your CRM rules (including related payments where applicable).';
$lang['perfex_toolkit_delete_invoices_deleted']      = 'Deleted: %s';
$lang['perfex_toolkit_delete_invoices_skipped']      = 'Skipped: %s';
$lang['perfex_toolkit_delete_invoices_none_selected'] = 'No invoices selected.';

$lang['perfex_toolkit_alternative_logos_title']   = 'Alternative logos';
$lang['perfex_toolkit_alternative_logos_intro']   = 'Records for extra logo slots. Logo number 1 is reserved for the default company logo; use 2 and above here.';
$lang['perfex_toolkit_alternative_logos_filters'] = 'Filters';
$lang['pk_logo_for']               = 'Logo for';
$lang['pk_logo_number']            = 'Logo Number';
$lang['logo_name']                 = 'Logo Name';
$lang['pk_logo_description']       = 'Description';
$lang['pk_logo_filter_placeholder'] = 'Filter by "logo for"…';

$lang['perfex_toolkit_alternative_logos_btn_upload']       = 'Upload alternative logo';
$lang['perfex_toolkit_alternative_logos_btn_save']         = 'Save & upload';
$lang['pk_logo_file']                                      = 'Image file';
$lang['perfex_toolkit_alternative_logos_upload_modal_title'] = 'Upload alternative logo';
$lang['perfex_toolkit_alternative_logos_upload_modal_hint']  = 'Slot 1 is reserved for the main company logo. Use number %s or higher. Logo # must be unique.';
$lang['perfex_toolkit_alternative_logos_upload_ph_for']    = 'e.g. company, email_header, estimate_pdf';
$lang['perfex_toolkit_alternative_logos_upload_file_help'] = 'JPG, PNG, GIF, WebP or SVG. Max. 5 MB.';

$lang['perfex_toolkit_alternative_logos_edit_modal_title']      = 'Edit alternative logo';
$lang['perfex_toolkit_alternative_logos_file_replace_optional'] = 'Leave empty to keep the current image.';
$lang['perfex_toolkit_alternative_logos_update_success']        = 'Logo updated successfully.';
$lang['perfex_toolkit_alternative_logos_update_error_not_found'] = 'Record not found.';

$lang['perfex_toolkit_alternative_logos_current_file'] = 'Current image';

$lang['perfex_toolkit_alternative_logos_upload_success']           = 'Logo uploaded successfully.';
$lang['perfex_toolkit_alternative_logos_upload_error_required']    = 'Please fill in logo for and logo #.';
$lang['perfex_toolkit_alternative_logos_upload_error_number']      = 'Logo # must be %s or higher (1 is the default company logo).';
$lang['perfex_toolkit_alternative_logos_upload_error_file']        = 'Please choose an image file.';
$lang['perfex_toolkit_alternative_logos_upload_error_size']        = 'The file is too large (max. 5 MB).';
$lang['perfex_toolkit_alternative_logos_upload_error_mime']        = 'Invalid file type. Use JPG, PNG, GIF, WebP, or SVG.';
$lang['perfex_toolkit_alternative_logos_upload_error_dir']         = 'Upload folder is not writable. Check server permissions.';
$lang['perfex_toolkit_alternative_logos_upload_error_move']        = 'Could not save the file. Try again.';
$lang['perfex_toolkit_alternative_logos_upload_error_duplicate']   = 'This logo # is already used. Please choose another number.';
$lang['perfex_toolkit_alternative_logos_upload_error_db']          = 'Could not save the record.';
$lang['perfex_toolkit_nav_download_module']                        = 'Download module';

$lang['perfex_toolkit_feature_download_module_name'] = 'Download Module';
$lang['perfex_toolkit_feature_download_module_desc'] = 'Browse all installed Perfex CRM modules and download any of them as a ZIP file.';

$lang['perfex_toolkit_download_module_title']          = 'Download Module';
$lang['perfex_toolkit_download_module_intro']          = 'Browse all installed modules and download their source code as a ZIP file.';
$lang['perfex_toolkit_download_module_col_name']       = 'Module Name';
$lang['perfex_toolkit_download_module_col_folder']     = 'Folder';
$lang['perfex_toolkit_download_module_col_version']    = 'Version';
$lang['perfex_toolkit_download_module_col_status']     = 'Status';
$lang['perfex_toolkit_download_module_col_action']     = 'Action';
$lang['perfex_toolkit_download_module_btn_download']   = 'Download';
$lang['perfex_toolkit_download_module_not_found']      = 'Module not found.';
$lang['perfex_toolkit_download_module_zip_unavailable'] = 'ZipArchive is not available on this server.';
$lang['perfex_toolkit_download_module_zip_error']      = 'Could not create ZIP file. Check server permissions.';

$lang['perfex_toolkit_alternative_logos_delete_confirm']           = 'Are you sure you want to delete this logo?';
$lang['perfex_toolkit_alternative_logos_delete_success']           = 'Logo deleted successfully.';
$lang['perfex_toolkit_alternative_logos_delete_error']             = 'Could not delete the logo.';
