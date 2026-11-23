<?php
require_once __DIR__ . '/security.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $dbUser=getenv('DB_USER');
    if (!$dbUser) throw new RuntimeException('Database configuration missing');
    $conn=new mysqli(getenv('DB_HOST') ?: '127.0.0.1', $dbUser, getenv('DB_PASSWORD') ?: '', getenv('DB_NAME') ?: 'online_store', (int)(getenv('DB_PORT') ?: 3306));
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) { error_log('Store database connection failed'); http_response_code(503); exit('Database unavailable. Check the local configuration.'); }
