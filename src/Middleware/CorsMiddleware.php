<?php
/**
 * CORS Middleware
 * 
 * Handles Cross-Origin Resource Sharing headers.
 */

namespace HRMS\Middleware;

use HRMS\Core\Request;
use HRMS\Core\Response;

class CorsMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // CORS headers are already set in index.php
        // This middleware can add additional logic if needed
        
        return $next($request);
    }
}
