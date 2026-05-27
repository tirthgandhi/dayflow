<?php
/**
 * Setup Permissions Script
 * Ensures all permissions and role mappings exist
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

$result = ['actions' => []];

// Check if reset is requested
$reset = isset($_GET['reset']) && $_GET['reset'] === '1';

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
    
    // Check if permissions exist
    $stmt = $pdo->query('SELECT COUNT(*) as cnt FROM permissions');
    $permCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    $result['actions'][] = ['action' => 'Check permissions', 'count' => $permCount];
    
    // Reset if requested
    if ($reset) {
        $pdo->exec('DELETE FROM role_permissions');
        $pdo->exec('DELETE FROM permissions');
        $permCount = 0;
        $result['actions'][] = ['action' => 'Reset permissions', 'status' => 'OK'];
    }
    
    if ($permCount == 0) {
        // Insert permissions
        $permissions = [
            // Employee Module
            ['employee.view', 'employee', 'View employee profiles'],
            ['employee.create', 'employee', 'Create new employees'],
            ['employee.update', 'employee', 'Update employee information'],
            ['employee.delete', 'employee', 'Delete employees'],
            ['employee.view_own', 'employee', 'View own profile'],
            ['employee.update_own', 'employee', 'Update own profile'],
            // Attendance Module
            ['attendance.view', 'attendance', 'View all attendance records'],
            ['attendance.create', 'attendance', 'Create attendance records'],
            ['attendance.update', 'attendance', 'Update attendance records'],
            ['attendance.delete', 'attendance', 'Delete attendance records'],
            ['attendance.view_own', 'attendance', 'View own attendance'],
            ['attendance.clock', 'attendance', 'Clock in/out'],
            // Leave Module
            ['leave.view', 'leave', 'View all leave requests'],
            ['leave.create', 'leave', 'Create leave requests for others'],
            ['leave.approve', 'leave', 'Approve/reject leave requests'],
            ['leave.delete', 'leave', 'Delete leave requests'],
            ['leave.view_own', 'leave', 'View own leave requests'],
            ['leave.request', 'leave', 'Submit own leave requests'],
            // Payroll Module
            ['payroll.view', 'payroll', 'View all payroll records'],
            ['payroll.create', 'payroll', 'Process payroll'],
            ['payroll.update', 'payroll', 'Update payroll records'],
            ['payroll.delete', 'payroll', 'Delete payroll records'],
            ['payroll.view_own', 'payroll', 'View own salary/payroll'],
            // Company Module
            ['company.view', 'company', 'View company settings'],
            ['company.update', 'company', 'Update company settings'],
            // User Module
            ['user.view', 'user', 'View all users'],
            ['user.create', 'user', 'Create new users'],
            ['user.update', 'user', 'Update user accounts'],
            ['user.delete', 'user', 'Delete users'],
        ];
        
        $stmt = $pdo->prepare('INSERT INTO permissions (name, module, description) VALUES (?, ?, ?)');
        foreach ($permissions as $perm) {
            $stmt->execute($perm);
        }
        $result['actions'][] = ['action' => 'Inserted permissions', 'count' => count($permissions)];
    }
    
    // Check role_permissions
    $stmt = $pdo->query('SELECT COUNT(*) as cnt FROM role_permissions');
    $rpCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    $result['actions'][] = ['action' => 'Check role_permissions', 'count' => $rpCount];
    
    // Reset role_permissions if requested
    if ($reset && $rpCount > 0) {
        $pdo->exec('DELETE FROM role_permissions');
        $rpCount = 0;
        $result['actions'][] = ['action' => 'Reset role_permissions', 'status' => 'OK'];
    }
    
    if ($rpCount == 0) {
        // Admin (role_id=1) gets all permissions
        $pdo->exec('INSERT INTO role_permissions (role_id, permission_id) SELECT 1, id FROM permissions');
        $result['actions'][] = ['action' => 'Added Admin permissions', 'status' => 'OK'];
        
        // HR (role_id=2) gets most permissions except company settings and delete
        $pdo->exec("INSERT INTO role_permissions (role_id, permission_id) 
                    SELECT 2, id FROM permissions 
                    WHERE module IN ('employee', 'attendance', 'leave', 'payroll', 'user')
                    AND name NOT LIKE '%delete%'");
        $result['actions'][] = ['action' => 'Added HR permissions', 'status' => 'OK'];
        
        // Employee (role_id=3) gets self-service permissions
        $employeePerms = [
            'employee.view_own',
            'employee.update_own',
            'attendance.view_own',
            'attendance.clock',
            'leave.view_own',
            'leave.request',
            'payroll.view_own'
        ];
        $stmt = $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) SELECT 3, id FROM permissions WHERE name = ?');
        foreach ($employeePerms as $perm) {
            $stmt->execute([$perm]);
        }
        $result['actions'][] = ['action' => 'Added Employee permissions', 'status' => 'OK'];
    }
    
    // Verify admin has permissions
    $stmt = $pdo->query('SELECT COUNT(*) as cnt FROM role_permissions WHERE role_id = 1');
    $adminPerms = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    $result['admin_permissions'] = $adminPerms;
    
    // List admin permissions
    $stmt = $pdo->query('SELECT p.name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = 1');
    $result['admin_permission_list'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
    
    $result['status'] = 'SUCCESS';
    $result['message'] = 'Permissions setup complete. Please logout and login again.';
    
} catch (PDOException $e) {
    $result['status'] = 'ERROR';
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
