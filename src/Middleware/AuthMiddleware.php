<?php
/**
 * Authentication Middleware
 * 
 * Validates user session and attaches user context to request.
 */

namespace HRMS\Middleware;

use HRMS\Core\Request;
use HRMS\Core\Response;
use HRMS\Core\Database;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Check if user is logged in via session
        if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            return Response::unauthorized('Authentication required');
        }
        
        $userId = $_SESSION['user']['id'];
        
        // Verify user still exists and is active
        $user = Database::fetchOne(
            'SELECT u.id, u.company_id, u.role_id, u.email, u.status, r.name as role_name, e.id as employee_id
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN employees e ON e.user_id = u.id
             WHERE u.id = ? AND u.status = ?',
            [$userId, 'active']
        );
        
        if (!$user) {
            unset($_SESSION['user']);
            return Response::unauthorized('Session expired or user inactive');
        }
        
        // Load user permissions
        $permissions = Database::fetchAll(
            'SELECT p.name FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = ?',
            [$user['role_id']]
        );
        
        $permissionNames = array_column($permissions, 'name');
        
        // Attach user context to request
        $request->user = [
            'id' => (int) $user['id'],
            'company_id' => (int) $user['company_id'],
            'role_id' => (int) $user['role_id'],
            'role_name' => $user['role_name'],
            'email' => $user['email'],
            'employee_id' => $user['employee_id'] ? (int) $user['employee_id'] : null
        ];
        
        $request->companyId = (int) $user['company_id'];
        $request->permissions = $permissionNames;
        
        // Update session with fresh data
        $_SESSION['user'] = $request->user;
        $_SESSION['permissions'] = $permissionNames;
        
        return $next($request);
    }
}
