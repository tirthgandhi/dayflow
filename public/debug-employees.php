<?php
/**
 * Debug Employees Script
 * Tests the employees endpoint
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

use HRMS\Core\Database;
use HRMS\Services\EmployeeService;
use HRMS\Repositories\EmployeeRepository;

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
    
    // Check session
    echo "Session data:\n";
    print_r($_SESSION);
    echo "\n";
    
    // Get a company ID to test with
    $company = Database::fetchOne("SELECT id FROM companies LIMIT 1");
    if (!$company) {
        echo "No companies found in database!\n";
        exit;
    }
    
    $companyId = (int) $company['id'];
    echo "Testing with company ID: {$companyId}\n\n";
    
    // Check employees table structure
    echo "Employees table structure:\n";
    $columns = Database::fetchAll("DESCRIBE employees");
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";
    
    // Test EmployeeRepository
    echo "Testing EmployeeRepository...\n";
    $repo = new EmployeeRepository();
    
    echo "Calling getPaginated...\n";
    $result = $repo->getPaginated($companyId, [], 1, 20);
    echo "Found {$result['total']} employees\n";
    echo "Data:\n";
    print_r($result['data']);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
