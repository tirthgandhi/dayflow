<?php
/**
 * Tenant Middleware
 * 
 * Ensures multi-tenant data isolation by setting company context.
 */

namespace HRMS\Middleware;

use HRMS\Core\Request;
use HRMS\Core\Response;

class TenantMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Ensure user is authenticated (AuthMiddleware should run first)
        if (!$request->isAuthenticated()) {
            return Response::unauthorized('Authentication required');
        }
        
        // Ensure company_id is set
        if ($request->companyId === null) {
            return Response::serverError('Company context not available');
        }
        
        // Company context is already set by AuthMiddleware
        // This middleware can add additional tenant-specific logic
        
        return $next($request);
    }
}
