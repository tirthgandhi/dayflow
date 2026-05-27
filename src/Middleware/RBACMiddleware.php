<?php
/**
 * RBAC Middleware
 * 
 * Enforces role-based access control by checking user permissions.
 */

namespace HRMS\Middleware;

use HRMS\Core\Request;
use HRMS\Core\Response;

class RBACMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Get required permission from route params
        $requiredPermission = $request->params['_required_permission'] ?? null;
        
        // If no permission required, allow access
        if ($requiredPermission === null) {
            return $next($request);
        }
        
        // Ensure user is authenticated
        if (!$request->isAuthenticated()) {
            return Response::unauthorized('Authentication required');
        }
        
        // Admin role has all permissions
        if ($request->user['role_name'] === 'Admin') {
            return $next($request);
        }
        
        // Check if user has the required permission
        if (!$request->hasPermission($requiredPermission)) {
            return Response::forbidden(
                "You don't have permission to perform this action. Required: {$requiredPermission}"
            );
        }
        
        return $next($request);
    }
}
