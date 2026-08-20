<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        $table = db_prefix() . 'ptk_features';
        if ($this->db->table_exists($table)) {
            $this->db->query("INSERT IGNORE INTO `{$table}`
                (feature_key, feature_name, feature_description, category, is_active, activated_at)
                VALUES
                ('ticket_case_history', 'Non-Customer Case History',
                 'On non-customer tickets, shows a case count badge and a link to view all previous tickets from the same email.',
                 'tickets', 1, NOW())"
            );
        }
    }

    public function down()
    {
        $table = db_prefix() . 'ptk_features';
        if ($this->db->table_exists($table)) {
            $this->db->where('feature_key', 'ticket_case_history');
            $this->db->delete($table);
        }
    }
}
