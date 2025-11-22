<?php
// FILE: /app/core/Model.php

/**
 * Base Model Class
 * All models extend this class
 */
class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find all records
     * @param array $conditions
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll($conditions = [], $limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY {$this->primaryKey} DESC";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Find record by ID
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        $stmt = $this->db->query($sql, [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find one record by conditions
     * @param array $conditions
     * @return array|null
     */
    public function findOne($conditions) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create a new record
     * @param array $data
     * @return int Last insert ID
     */
    public function create($data) {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->query($sql, array_values($data));
        return $this->db->lastInsertId();
    }

    /**
     * Update a record
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $set = [];
        $params = [];

        foreach ($data as $key => $value) {
            $set[] = "{$key} = ?";
            $params[] = $value;
        }

        $params[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) .
               " WHERE {$this->primaryKey} = ?";

        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Delete a record
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    /**
     * Count records
     * @param array $conditions
     * @return int
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Execute raw SQL query
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    protected function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
}
