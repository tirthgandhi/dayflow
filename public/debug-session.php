<?php
/**
 * Debug Session Script
 * Check current session and permissions
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

session_start();

$config = [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'hrms_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

$result = [];

// Check session
$result['session'] = [
    'id' => session_id(),
    'user' => $_SESSION['user'] ?? 'NOT SET',
    'permissions' => $_SESSION['permissions'] ?? 'NOT SET'
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
    
    // Check if user is in session
    if (isset($_SESSION['user']['id'])) {
        $userId = $_SESSION['user']['id'];
        $roleId = $_SESSION['user']['role_id'] ?? null;
        
        // Get user from DB
        $stmt = $pdo->prepare('SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $result['db_user'] = $user ? [
            'id' => $user['id'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'],
            'status' => $user['status']
        ] : 'NOT FOUND';
        
        // Get permissions for this role
        if ($roleId) {
            $stmt = $pdo->prepare('SELECT p.name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = ?');
            $stmt->execute([$roleId]);
            $perms = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $result['db_permissions'] = $perms;
            $result['db_permission_count'] = count($perms);
        }
    }
    
    // Check role_permissions table
    $stmt = $pdo->query('SELECT role_id, COUNT(*) as cnt FROM role_permissions GROUP BY role_id');
    $result['role_permission_counts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check total permissions
    $stmt = $pdo->query('SELECT COUNT(*) as cnt FROM permissions');
    $result['total_permissions'] = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    // Check roles
    $stmt = $pdo->query('SELECT * FROM roles');
    $result['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
