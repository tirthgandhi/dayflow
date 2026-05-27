<?php
/**
 * Database Connection Test Script
 * Run this to verify MySQL connection is working
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration for XAMPP
$config = [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'hrms_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

$result = [
    'test' => 'Database Connection Test',
    'config' => [
        'host' => $config['host'],
        'port' => $config['port'],
        'database' => $config['database'],
        'username' => $config['username'],
        'password' => '(blank)'
    ]
];

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $result['connection'] = 'SUCCESS';
    
    // Test query - count companies
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM companies');
    $companies = $stmt->fetch(PDO::FETCH_ASSOC);
    $result['companies_count'] = $companies['count'];
    
    // Test query - count users
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $users = $stmt->fetch(PDO::FETCH_ASSOC);
    $result['users_count'] = $users['count'];
    
    // Test query - count employees
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM employees');
    $employees = $stmt->fetch(PDO::FETCH_ASSOC);
    $result['employees_count'] = $employees['count'];
    
    // Check if admin user exists and verify password
    $stmt = $pdo->prepare('SELECT id, email, company_id, password_hash FROM users WHERE email = ?');
    $stmt->execute(['admin1@company1.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $result['admin_user'] = [
            'id' => $admin['id'],
            'email' => $admin['email'],
            'company_id' => $admin['company_id']
        ];
        
        // Test password verification
        $testPasswords = ['password', 'password123'];
        foreach ($testPasswords as $testPwd) {
            if (password_verify($testPwd, $admin['password_hash'])) {
                $result['correct_password'] = $testPwd;
                break;
            }
        }
        
        if (!isset($result['correct_password'])) {
            $result['password_issue'] = 'Neither "password" nor "password123" works';
            $result['hash_sample'] = substr($admin['password_hash'], 0, 20) . '...';
        }
    } else {
        $result['admin_user'] = 'NOT FOUND - Need to run seed.sql';
    }
    
    $result['status'] = 'Database is connected!';
    $result['login_hint'] = 'Try: admin1@company1.com with password: password';
    
} catch (PDOException $e) {
    $result['connection'] = 'FAILED';
    $result['error'] = $e->getMessage();
    $result['status'] = 'Database connection failed!';
    
    // Common issues
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        $result['fix'] = 'Database "hrms_db" does not exist. Run the schema.sql file in phpMyAdmin.';
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        $result['fix'] = 'Check username/password. For XAMPP default is root with blank password.';
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        $result['fix'] = 'MySQL server is not running. Start XAMPP MySQL service.';
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);
