<?php
/**
 * Base Test Case for HRMS Tests
 */

namespace HRMS\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PDO;
use PDOException;

abstract class TestCase extends BaseTestCase
{
    protected static ?PDO $pdo = null;
    
    /**
     * Get database connection
     */
    protected static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );
                
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                self::markTestSkipped('Database connection failed: ' . $e->getMessage());
            }
        }
        
        return self::$pdo;
    }
    
    /**
     * Execute a query and return results
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute a query and return single row
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Execute an insert/update/delete query
     */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * Get last insert ID
     */
    protected function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    protected function beginTransaction(): void
    {
        self::getConnection()->beginTransaction();
    }
    
    /**
     * Rollback transaction
     */
    protected function rollback(): void
    {
        if (self::getConnection()->inTransaction()) {
            self::getConnection()->rollBack();
        }
    }
    
    /**
     * Generate random string
     */
    protected function randomString(int $length = 10): string
    {
        return substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz', $length)), 0, $length);
    }
    
    /**
     * Generate random email
     */
    protected function randomEmail(): string
    {
        return $this->randomString(8) . '@test.com';
    }
    
    /**
     * Generate random phone
     */
    protected function randomPhone(): string
    {
        return '+1-555-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
