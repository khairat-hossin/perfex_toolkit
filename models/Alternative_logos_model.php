<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Alternative_logos_model extends App_Model
{
    /** @var string */
    protected $table;

    public const MIN_LOGO_NUMBER = 2;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'ptk_alternative_logos';
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', (int) $id);

            return $this->db->get($this->table)->row();
        }

        $this->db->order_by('logo_for', 'asc');
        $this->db->order_by('logo_number', 'asc');

        return $this->db->get($this->table)->result_array();
    }

    public function get_table_name()
    {
        return $this->table;
    }

    /**
     * @param  array{logo_for:string,logo_number:int,logo_name:string,description?:string|null,file_path:string}  $data (logo_name mirrors logo_for for the legacy not-null column)
     * @return int|false
     */
    public function add(array $data)
    {
        if (! array_key_exists('description', $data)) {
            $data['description'] = null;
        }
        if (! isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $ok = $this->db->insert($this->table, $data);

        return $ok ? (int) $this->db->insert_id() : false;
    }

    public function get_by_id(int $id)
    {
        $this->db->where('id', $id);
        $row = $this->db->get($this->table)->row();

        return $row;
    }

    public function has_duplicate_number(int $logoNumber, int $excludeId = 0): bool
    {
        $this->db->where('logo_number', $logoNumber);
        if ($excludeId > 0) {
            $this->db->where('id !=', $excludeId);
        }
        $q = $this->db->get($this->table);

        return $q->num_rows() > 0;
    }

    public function update(int $id, array $data): bool
    {
        $this->db->where('id', $id);

        return (bool) $this->db->update($this->table, $data);
    }

    public function delete(int $id): bool
    {
        $this->db->where('id', $id);

        return (bool) $this->db->delete($this->table);
    }

    public function get_next_logo_number(): int
    {
        $this->db->select_max('logo_number', 'max_logo_number');
        $row = $this->db->get($this->table)->row();
        $max = isset($row->max_logo_number) ? (int) $row->max_logo_number : 0;

        return max(self::MIN_LOGO_NUMBER, $max + 1);
    }
}
