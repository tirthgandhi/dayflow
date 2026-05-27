<?php
/**
 * Setup Admin Users Script
 * Creates admin users with known credentials
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
    
    $result['actions'][] = ['action' => 'Database connected', 'status' => 'OK'];
    
    // Password hash for 'password'
    $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Check if admin user already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['admin1@company1.com']);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $result['actions'][] = ['action' => 'Admin user exists', 'user_id' => $existing['id']];
    } else {
        // Create admin user
        $stmt = $pdo->prepare('INSERT INTO users (company_id, role_id, email, password_hash, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([1, 1, 'admin1@company1.com', $passwordHash, 'active']);
        $adminUserId = $pdo->lastInsertId();
        $result['actions'][] = ['action' => 'Created admin user', 'user_id' => $adminUserId];
        
        // Create employee record for admin
        $stmt = $pdo->prepare('INSERT INTO employees (company_id, user_id, employee_code, first_name, last_name, email, phone, date_of_birth, gender, address, hire_date, department, designation, employment_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            1, $adminUserId, 'TC-ADMIN-001', 'System', 'Administrator', 
            'admin1@company1.com', '+1-555-0001', '1985-01-15', 'male',
            '123 Admin Street', '2020-01-01', 'Administration', 
            'System Administrator', 'full_time', 'active'
        ]);
        $result['actions'][] = ['action' => 'Created admin employee record', 'status' => 'OK'];
    }
    
    // Check if HR user exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['hr1@company1.com']);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $result['actions'][] = ['action' => 'HR user exists', 'user_id' => $existing['id']];
    } else {
        // Create HR user
        $stmt = $pdo->prepare('INSERT INTO users (company_id, role_id, email, password_hash, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([1, 2, 'hr1@company1.com', $passwordHash, 'active']);
        $hrUserId = $pdo->lastInsertId();
        $result['actions'][] = ['action' => 'Created HR user', 'user_id' => $hrUserId];
        
        // Create employee record for HR
        $stmt = $pdo->prepare('INSERT INTO employees (company_id, user_id, employee_code, first_name, last_name, email, phone, date_of_birth, gender, address, hire_date, department, designation, employment_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            1, $hrUserId, 'TC-HR-001', 'HR', 'Manager', 
            'hr1@company1.com', '+1-555-0002', '1988-05-20', 'female',
            '456 HR Avenue', '2020-02-01', 'Human Resources', 
            'HR Manager', 'full_time', 'active'
        ]);
        $result['actions'][] = ['action' => 'Created HR employee record', 'status' => 'OK'];
    }
    
    // Verify the users were created
    $stmt = $pdo->query("SELECT u.id, u.email, u.status, r.name as role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email IN ('admin1@company1.com', 'hr1@company1.com') ORDER BY u.id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result['created_users'] = $users;
    $result['status'] = 'SUCCESS';
    $result['login_credentials'] = [
        ['email' => 'admin1@company1.com', 'password' => 'password', 'role' => 'Admin'],
        ['email' => 'hr1@company1.com', 'password' => 'password', 'role' => 'HR']
    ];
    $result['next_step'] = 'Go to login page and use: admin1@company1.com / password';
    
} catch (PDOException $e) {
    $result['status'] = 'ERROR';
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
