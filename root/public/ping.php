<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'status' => 'ok',
    'message' => 'Backend is reachable',
    'timestamp' => time(),
    'server' => $_SERVER['SERVER_ADDR'] ?? 'unknown'
]);
