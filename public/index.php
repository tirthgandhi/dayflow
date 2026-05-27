<?php
/**
 * Multi-Company HRMS API Entry Point
 * 
 * All API requests are routed through this file.
 * For XAMPP, configure Apache to point to this directory or use .htaccess
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Start session for authentication
session_start();

// Set timezone
date_default_timezone_set('UTC');

// Autoload classes
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
$config = require __DIR__ . '/../config/database.php';

// Set JSON content type for all responses
header('Content-Type: application/json; charset=utf-8');

// CORS Headers - Allow credentials for session cookies
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost:8081';
header("Access-Control-Allow-Origin: {$origin}");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

use HRMS\Core\Router;
use HRMS\Core\Request;
use HRMS\Core\Response;
use HRMS\Core\Database;

try {
    // Initialize database connection
    Database::init($config);
    
    // Create request object
    $request = new Request();
    
    // Load routes
    $router = new Router();
    require __DIR__ . '/../config/routes.php';
    
    // Dispatch request
    $response = $router->dispatch($request);
    
    // Send response
    $response->send();
    
} catch (\HRMS\Exceptions\ValidationException $e) {
    Response::error(400, 'VALIDATION_ERROR', $e->getMessage(), $e->getErrors())->send();
} catch (\HRMS\Exceptions\AuthException $e) {
    Response::error(401, 'AUTH_ERROR', $e->getMessage())->send();
} catch (\HRMS\Exceptions\ForbiddenException $e) {
    Response::error(403, 'FORBIDDEN', $e->getMessage())->send();
} catch (\HRMS\Exceptions\NotFoundException $e) {
    Response::error(404, 'NOT_FOUND', $e->getMessage())->send();
} catch (\PDOException $e) {
    error_log('Database Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    Response::error(500, 'DATABASE_ERROR', 'A database error occurred')->send();
} catch (\Exception $e) {
    error_log('Server Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    Response::error(500, 'SERVER_ERROR', 'An internal server error occurred: ' . $e->getMessage())->send();
}
