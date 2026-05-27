<?php
/**
 * Router
 * 
 * Handles route registration, matching, and dispatching with middleware support.
 */

namespace HRMS\Core;

class Router
{
    private array $routes = [];
    private array $globalMiddleware = [];
    private static ?Router $instance = null;
    
    public function __construct()
    {
        self::$instance = $this;
    }
    
    /**
     * Get router instance
     */
    public static function getInstance(): ?Router
    {
        return self::$instance;
    }
    
    /**
     * Add global middleware
     */
    public function addMiddleware(string $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }
    
    /**
     * Register a GET route
     */
    public function get(string $path, $handler, array $options = []): self
    {
        return $this->addRoute('GET', $path, $handler, $options);
    }
    
    /**
     * Register a POST route
     */
    public function post(string $path, $handler, array $options = []): self
    {
        return $this->addRoute('POST', $path, $handler, $options);
    }
    
    /**
     * Register a PUT route
     */
    public function put(string $path, $handler, array $options = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $options);
    }
    
    /**
     * Register a DELETE route
     */
    public function delete(string $path, $handler, array $options = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $options);
    }
    
    /**
     * Add a route
     */
    private function addRoute(string $method, string $path, $handler, array $options): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $this->pathToPattern($path),
            'handler' => $handler,
            'middleware' => $options['middleware'] ?? [],
            'permission' => $options['permission'] ?? null,
            'auth' => $options['auth'] ?? true
        ];
        
        return $this;
    }
    
    /**
     * Convert path to regex pattern
     */
    private function pathToPattern(string $path): string
    {
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Dispatch the request
     */
    public function dispatch(Request $request): Response
    {
        // Find matching route
        $route = $this->matchRoute($request);
        
        if ($route === null) {
            return Response::notFound('Endpoint not found');
        }
        
        // Extract route parameters
        preg_match($route['pattern'], $request->path, $matches);
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $request->params[$key] = $value;
            }
        }
        
        // Build middleware stack
        $middleware = array_merge($this->globalMiddleware, $route['middleware']);
        
        // Add auth middleware if route requires authentication
        if ($route['auth'] && !in_array('HRMS\\Middleware\\AuthMiddleware', $middleware)) {
            array_unshift($middleware, 'HRMS\\Middleware\\AuthMiddleware');
        }
        
        // Add permission to request for RBAC middleware
        if ($route['permission']) {
            $request->params['_required_permission'] = $route['permission'];
        }
        
        // Execute middleware stack
        $response = $this->executeMiddleware($middleware, $request, function($request) use ($route) {
            return $this->executeHandler($route['handler'], $request);
        });
        
        return $response;
    }
    
    /**
     * Find matching route
     */
    private function matchRoute(Request $request): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $request->path)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Execute middleware stack
     */
    private function executeMiddleware(array $middleware, Request $request, callable $final): Response
    {
        if (empty($middleware)) {
            return $final($request);
        }
        
        $middlewareClass = array_shift($middleware);
        $instance = new $middlewareClass();
        
        return $instance->handle($request, function($request) use ($middleware, $final) {
            return $this->executeMiddleware($middleware, $request, $final);
        });
    }
    
    /**
     * Execute route handler
     */
    private function executeHandler($handler, Request $request): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return $controller->$method($request);
        }
        
        if (is_callable($handler)) {
            return $handler($request);
        }
        
        throw new \RuntimeException('Invalid route handler');
    }
}
