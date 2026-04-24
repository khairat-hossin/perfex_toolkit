<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ptk_features_model extends App_Model
{
    const TABLE = 'ptk_features';

    private function table()
    {
        return db_prefix() . self::TABLE;
    }

    /**
     * Returns all feature rows keyed by feature_key => is_active (bool).
     *
     * @return array<string, bool>
     */
    public function get_statuses_keyed()
    {
        $rows = $this->db->get($this->table())->result_array();
        $out  = [];
        foreach ($rows as $row) {
            $out[$row['feature_key']] = (bool) $row['is_active'];
        }

        return $out;
    }

    /**
     * @return bool
     */
    public function is_active($key)
    {
        $row = $this->db->where('feature_key', $key)->get($this->table())->row();

        return $row ? (bool) $row->is_active : false;
    }

    /**
     * @return bool
     */
    public function activate($key)
    {
        $this->db->where('feature_key', $key);

        return $this->db->update($this->table(), [
            'is_active'    => 1,
            'activated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return bool
     */
    public function deactivate($key)
    {
        $this->db->where('feature_key', $key);

        return $this->db->update($this->table(), [
            'is_active'      => 0,
            'deactivated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
