<?php
/**
 * Setup Default Leave Types
 * 
 * Run this script to add default leave types to companies that don't have any.
 * Access: http://localhost/Dayflow---Human-Resource-Management-System/public/setup-leave-types.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load database config
$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h2>Setting up Default Leave Types</h2>";
    
    // Get all companies
    $companies = $pdo->query("SELECT id, name FROM companies")->fetchAll();
    
    if (empty($companies)) {
        echo "<p>No companies found.</p>";
        exit;
    }
    
    $defaultLeaveTypes = [
        ['Annual Leave', 20, 1],
        ['Sick Leave', 10, 1],
        ['Personal Leave', 5, 1],
        ['Unpaid Leave', 0, 0]
    ];
    
    $insertStmt = $pdo->prepare("
        INSERT IGNORE INTO leave_types (company_id, name, annual_allocation, is_paid)
        VALUES (?, ?, ?, ?)
    ");
    
    $totalAdded = 0;
    
    foreach ($companies as $company) {
        // Check if company has leave types
        $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM leave_types WHERE company_id = ?");
        $checkStmt->execute([$company['id']]);
        $count = $checkStmt->fetch()['count'];
        
        if ($count == 0) {
            echo "<p>Adding leave types for company: <strong>{$company['name']}</strong> (ID: {$company['id']})</p>";
            
            foreach ($defaultLeaveTypes as $leaveType) {
                $insertStmt->execute([
                    $company['id'],
                    $leaveType[0],
                    $leaveType[1],
                    $leaveType[2]
                ]);
                $totalAdded++;
            }
        } else {
            echo "<p>Company <strong>{$company['name']}</strong> already has {$count} leave types.</p>";
        }
    }
    
    echo "<hr>";
    echo "<p><strong>Done!</strong> Added {$totalAdded} leave types.</p>";
    echo "<p><a href='../frontend/index.html'>Go to Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
