<?php
/**
 * Debug Login Script
 * Tests the login process step by step
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/database.php';

use HRMS\Core\Database;

$result = ['steps' => []];

try {
    // Step 1: Initialize database
    Database::init($config);
    $result['steps'][] = ['step' => 1, 'action' => 'Database init', 'status' => 'OK'];
    
    // Step 2: Test connection
    $pdo = Database::getConnection();
    $result['steps'][] = ['step' => 2, 'action' => 'Get connection', 'status' => 'OK'];
    
    // Step 3: Find user by email
    $email = 'admin1@company1.com';
    $user = Database::fetchOne(
        'SELECT u.*, r.name as role_name, e.id as employee_id
         FROM users u
         JOIN roles r ON u.role_id = r.id
         LEFT JOIN employees e ON e.user_id = u.id
         WHERE u.email = ?',
        [$email]
    );
    
    if ($user) {
        $result['steps'][] = [
            'step' => 3, 
            'action' => 'Find user', 
            'status' => 'FOUND',
            'user_id' => $user['id'],
            'email' => $user['email'],
            'status_field' => $user['status'],
            'role' => $user['role_name'],
            'company_id' => $user['company_id']
        ];
        
        // Step 4: Check password hash
        $result['steps'][] = [
            'step' => 4,
            'action' => 'Password hash check',
            'hash_length' => strlen($user['password_hash']),
            'hash_prefix' => substr($user['password_hash'], 0, 7),
            'hash_sample' => substr($user['password_hash'], 0, 30) . '...'
        ];
        
        // Step 5: Test password verification
        $testPasswords = ['password', 'password123', 'Password', 'Password123'];
        $passwordResults = [];
        
        foreach ($testPasswords as $pwd) {
            $passwordResults[$pwd] = password_verify($pwd, $user['password_hash']) ? 'MATCH' : 'NO MATCH';
        }
        
        $result['steps'][] = [
            'step' => 5,
            'action' => 'Password verification tests',
            'results' => $passwordResults
        ];
        
        // Step 6: Check if user is active
        $result['steps'][] = [
            'step' => 6,
            'action' => 'User status check',
            'status' => $user['status'],
            'is_active' => $user['status'] === 'active' ? 'YES' : 'NO'
        ];
        
    } else {
        $result['steps'][] = [
            'step' => 3, 
            'action' => 'Find user', 
            'status' => 'NOT FOUND',
            'email_searched' => $email
        ];
        
        // Check if any users exist
        $userCount = Database::fetchOne('SELECT COUNT(*) as cnt FROM users');
        $result['steps'][] = [
            'step' => '3b',
            'action' => 'Check users table',
            'total_users' => $userCount['cnt']
        ];
        
        // Get sample emails
        $sampleUsers = Database::fetchAll('SELECT email FROM users LIMIT 5');
        $result['steps'][] = [
            'step' => '3c',
            'action' => 'Sample emails in database',
            'emails' => array_column($sampleUsers, 'email')
        ];
    }
    
    $result['status'] = 'Debug complete';
    
} catch (Exception $e) {
    $result['error'] = $e->getMessage();
    $result['trace'] = $e->getTraceAsString();
}

echo json_encode($result, JSON_PRETTY_PRINT);
