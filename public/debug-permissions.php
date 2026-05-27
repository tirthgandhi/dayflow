<?php
/**
 * Debug Permissions Script
 * Shows what permissions each role has
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$config = [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'hrms_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

$result = [];

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
    
    // Get all roles
    $stmt = $pdo->query('SELECT * FROM roles');
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['roles'] = $roles;
    
    // Get all permissions
    $stmt = $pdo->query('SELECT * FROM permissions ORDER BY module, name');
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['all_permissions'] = $permissions;
    
    // Get permissions for each role
    foreach ($roles as $role) {
        $stmt = $pdo->prepare('
            SELECT p.name, p.module 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
            ORDER BY p.module, p.name
        ');
        $stmt->execute([$role['id']]);
        $rolePerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['role_permissions'][$role['name']] = array_column($rolePerms, 'name');
    }
    
    // Check role_permissions table
    $stmt = $pdo->query('SELECT COUNT(*) as cnt FROM role_permissions');
    $result['total_role_permissions'] = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    // Check if Employee role has _own permissions
    $stmt = $pdo->query("
        SELECT p.name 
        FROM permissions p 
        WHERE p.name LIKE '%_own' OR p.name IN ('attendance.clock', 'leave.request')
    ");
    $result['expected_employee_permissions'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
    
    $result['status'] = 'SUCCESS';
    
} catch (PDOException $e) {
    $result['status'] = 'ERROR';
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
