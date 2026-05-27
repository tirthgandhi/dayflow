<?php
/**
 * PHPUnit Bootstrap File
 * Multi-Company HRMS Test Suite
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load database configuration
$config = require __DIR__ . '/../config/database.php';

// Set up test database connection
define('DB_HOST', $config['host']);
define('DB_PORT', $config['port']);
define('DB_NAME', $config['database']);
define('DB_USER', $config['username']);
define('DB_PASS', $config['password']);
define('DB_CHARSET', $config['charset']);
