<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        $table = db_prefix() . 'ptk_features';
        if ($this->db->table_exists($table)) {
            $this->db->query("INSERT IGNORE INTO `{$table}`
                (feature_key, feature_name, feature_description, category, is_active, activated_at)
                VALUES
                ('preserve_lead_status', 'Preserve Lead Status on Conversion',
                 'When converting a lead to a customer, keep the lead\\'s current status instead of resetting it to the Perfex default.',
                 'leads', 1, NOW())"
            );
        }
    }

    public function down()
    {
        $table = db_prefix() . 'ptk_features';
        if ($this->db->table_exists($table)) {
            $this->db->where('feature_key', 'preserve_lead_status');
            $this->db->delete($table);
        }
    }
}
