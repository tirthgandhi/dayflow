<?php
/**
 * Response Builder
 * 
 * Builds and sends JSON API responses with consistent format.
 */

namespace HRMS\Core;

class Response
{
    private int $statusCode;
    private array $data;
    private array $headers = [];
    
    public function __construct(int $statusCode, array $data)
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
    }
    
    /**
     * Create a success response
     */
    public static function success($data = null, string $message = null, int $statusCode = 200): self
    {
        $response = [
            'success' => true,
            'data' => $data
        ];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        return new self($statusCode, $response);
    }
    
    /**
     * Create a success response with pagination
     */
    public static function paginated(array $data, int $total, int $page, int $perPage): self
    {
        $totalPages = (int) ceil($total / $perPage);
        
        return new self(200, [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $totalPages
            ]
        ]);
    }
    
    /**
     * Create an error response
     */
    public static function error(int $statusCode, string $code, string $message, array $details = null): self
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
        
        if ($details !== null) {
            $response['error']['details'] = $details;
        }
        
        return new self($statusCode, $response);
    }
    
    /**
     * Create a 201 Created response
     */
    public static function created($data, string $message = 'Resource created successfully'): self
    {
        return self::success($data, $message, 201);
    }
    
    /**
     * Create a 204 No Content response
     */
    public static function noContent(): self
    {
        return new self(204, []);
    }
    
    /**
     * Create a 400 Bad Request response
     */
    public static function badRequest(string $message, array $details = null): self
    {
        return self::error(400, 'BAD_REQUEST', $message, $details);
    }
    
    /**
     * Create a 401 Unauthorized response
     */
    public static function unauthorized(string $message = 'Authentication required'): self
    {
        return self::error(401, 'UNAUTHORIZED', $message);
    }
    
    /**
     * Create a 403 Forbidden response
     */
    public static function forbidden(string $message = 'Access denied'): self
    {
        return self::error(403, 'FORBIDDEN', $message);
    }
    
    /**
     * Create a 404 Not Found response
     */
    public static function notFound(string $message = 'Resource not found'): self
    {
        return self::error(404, 'NOT_FOUND', $message);
    }
    
    /**
     * Create a 409 Conflict response
     */
    public static function conflict(string $message): self
    {
        return self::error(409, 'CONFLICT', $message);
    }
    
    /**
     * Create a 422 Unprocessable Entity response
     */
    public static function validationError(array $errors): self
    {
        return self::error(422, 'VALIDATION_ERROR', 'Validation failed', $errors);
    }
    
    /**
     * Create a 500 Internal Server Error response
     */
    public static function serverError(string $message = 'An internal server error occurred'): self
    {
        return self::error(500, 'SERVER_ERROR', $message);
    }
    
    /**
     * Add a header to the response
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Send the response
     */
    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        if ($this->statusCode !== 204 && !empty($this->data)) {
            echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        exit;
    }
    
    /**
     * Get response data (for testing)
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Get status code (for testing)
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
