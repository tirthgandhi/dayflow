<?php
/**
 * Request Wrapper
 * 
 * Parses and provides access to HTTP request data.
 */

namespace HRMS\Core;

class Request
{
    public string $method;
    public string $uri;
    public string $path;
    public array $query = [];
    public array $body = [];
    public array $params = [];
    public array $headers = [];
    
    // Set by middleware
    public ?int $companyId = null;
    public ?array $user = null;
    public array $permissions = [];
    
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->path = $this->parsePath();
        $this->query = $_GET;
        $this->body = $this->parseBody();
        $this->headers = $this->parseHeaders();
    }
    
    /**
     * Parse the request path (without query string)
     */
    private function parsePath(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove base path if running in subdirectory
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            $path = substr($path, strlen($scriptName));
        }
        
        return '/' . trim($path, '/');
    }
    
    /**
     * Parse request body (JSON)
     */
    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            return is_array($data) ? $data : [];
        }
        
        // Fall back to POST data
        return $_POST;
    }
    
    /**
     * Parse request headers
     */
    private function parseHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        
        // Handle Authorization header
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers['AUTHORIZATION'] = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $apacheHeaders = apache_request_headers();
            if (isset($apacheHeaders['Authorization'])) {
                $headers['AUTHORIZATION'] = $apacheHeaders['Authorization'];
            }
        }
        
        return $headers;
    }
    
    /**
     * Get a body parameter
     */
    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $default;
    }
    
    /**
     * Get a query parameter
     */
    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }
    
    /**
     * Get a route parameter
     */
    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
    
    /**
     * Get a header
     */
    public function header(string $key, $default = null): ?string
    {
        $key = strtoupper(str_replace('-', '_', $key));
        return $this->headers[$key] ?? $default;
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->user !== null;
    }
    
    /**
     * Get authenticated user ID
     */
    public function userId(): ?int
    {
        return $this->user['id'] ?? null;
    }
    
    /**
     * Get authenticated user's employee ID
     */
    public function employeeId(): ?int
    {
        return $this->user['employee_id'] ?? null;
    }
    
    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }
    
    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return !empty(array_intersect($permissions, $this->permissions));
    }
    
    /**
     * Get pagination parameters
     */
    public function pagination(int $defaultPerPage = 20): array
    {
        $page = max(1, (int) ($this->query['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($this->query['per_page'] ?? $defaultPerPage)));
        $offset = ($page - 1) * $perPage;
        
        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => $offset
        ];
    }
}
