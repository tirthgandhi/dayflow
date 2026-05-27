<?php
/**
 * Base Repository
 * 
 * Provides common database operations for all repositories.
 */

namespace HRMS\Repositories;

use HRMS\Core\Database;

abstract class BaseRepository
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected bool $tenantScoped = true;
    
    /**
     * Find a record by ID
     */
    public function find(int $id, ?int $companyId = null): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = [$id];
        
        if ($this->tenantScoped && $companyId !== null) {
            $sql .= " AND company_id = ?";
            $params[] = $companyId;
        }
        
        return Database::fetchOne($sql, $params);
    }
    
    /**
     * Find all records
     */
    public function findAll(?int $companyId = null, array $options = []): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        $where = [];
        
        if ($this->tenantScoped && $companyId !== null) {
            $where[] = "company_id = ?";
            $params[] = $companyId;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if (isset($options['order_by'])) {
            $sql .= " ORDER BY " . $options['order_by'];
        }
        
        if (isset($options['limit'])) {
            $sql .= " LIMIT " . (int) $options['limit'];
            if (isset($options['offset'])) {
                $sql .= " OFFSET " . (int) $options['offset'];
            }
        }
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Count records
     */
    public function count(?int $companyId = null, array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        $where = [];
        
        if ($this->tenantScoped && $companyId !== null) {
            $where[] = "company_id = ?";
            $params[] = $companyId;
        }
        
        foreach ($conditions as $field => $value) {
            $where[] = "{$field} = ?";
            $params[] = $value;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = Database::fetchOne($sql, $params);
        return (int) ($result['count'] ?? 0);
    }
    
    /**
     * Create a new record
     */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        Database::execute($sql, array_values($data));
        
        return (int) Database::lastInsertId();
    }
    
    /**
     * Update a record
     */
    public function update(int $id, array $data, ?int $companyId = null): bool
    {
        $sets = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = ?";
        $params[] = $id;
        
        if ($this->tenantScoped && $companyId !== null) {
            $sql .= " AND company_id = ?";
            $params[] = $companyId;
        }
        
        return Database::execute($sql, $params) > 0;
    }
    
    /**
     * Delete a record
     */
    public function delete(int $id, ?int $companyId = null): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $params = [$id];
        
        if ($this->tenantScoped && $companyId !== null) {
            $sql .= " AND company_id = ?";
            $params[] = $companyId;
        }
        
        return Database::execute($sql, $params) > 0;
    }
    
    /**
     * Find by conditions
     */
    public function findBy(array $conditions, ?int $companyId = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        $where = [];
        
        if ($this->tenantScoped && $companyId !== null) {
            $where[] = "company_id = ?";
            $params[] = $companyId;
        }
        
        foreach ($conditions as $field => $value) {
            $where[] = "{$field} = ?";
            $params[] = $value;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Find one by conditions
     */
    public function findOneBy(array $conditions, ?int $companyId = null): ?array
    {
        $results = $this->findBy($conditions, $companyId);
        return $results[0] ?? null;
    }
}
