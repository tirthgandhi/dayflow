<?php
/**
 * Debug Registration Script
 * Tests the registration flow
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

use HRMS\Core\Database;
use HRMS\Services\AuthService;

header('Content-Type: text/plain');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load database config and initialize
$config = require __DIR__ . '/../config/database.php';
Database::init($config);

try {
    echo "Testing database connection...\n";
    $pdo = Database::getConnection();
    echo "Database connected!\n\n";
    
    // Test data
    $testData = [
        'company_name' => 'Test Company ' . time(),
        'registration_number' => 'REG-' . time(),
        'industry' => 'Technology',
        'company_size' => '1-10',
        'first_name' => 'Test',
        'last_name' => 'Admin',
        'email' => 'test' . time() . '@example.com',
        'password' => 'password123'
    ];
    
    echo "Test data:\n";
    print_r($testData);
    echo "\n";
    
    $authService = new AuthService();
    $result = $authService->register($testData);
    
    echo "Registration successful!\n";
    print_r($result);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
