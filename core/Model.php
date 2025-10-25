<?php
/**
 * Base Model Class
 * All models extend from this class
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    /**
     * Find all records
     */
    public function all($conditions = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . $this->buildWhereClause($conditions);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, $conditions);
    }
    
    /**
     * Find first record matching conditions
     */
    public function findWhere($conditions) {
        $sql = "SELECT * FROM {$this->table} WHERE " . $this->buildWhereClause($conditions) . " LIMIT 1";
        return $this->db->fetchOne($sql, $conditions);
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Count records
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . $this->buildWhereClause($conditions);
        }
        
        $result = $this->db->fetchOne($sql, $conditions);
        return $result['count'] ?? 0;
    }
    
    /**
     * Paginate records
     */
    public function paginate($page = 1, $perPage = 20, $conditions = [], $orderBy = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . $this->buildWhereClause($conditions);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $data = $this->db->fetchAll($sql, $conditions);
        $total = $this->count($conditions);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];
    }
    
    /**
     * Build WHERE clause from conditions array
     */
    private function buildWhereClause($conditions) {
        $clauses = [];
        foreach (array_keys($conditions) as $key) {
            $clauses[] = "{$key} = :{$key}";
        }
        return implode(' AND ', $clauses);
    }
    
    /**
     * Filter data to only fillable fields
     */
    private function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Execute raw query
     */
    public function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->db->rollback();
    }
}

