<?php
// Simple test to see what headers are being received
header('Content-Type: application/json');

$headers = getallheaders();
$input = file_get_contents('php://input');

$response = [
    'all_headers' => $headers,
    'x_authentication' => isset($headers['X-Authentication']) ? $headers['X-Authentication'] : 'NOT SET',
    'x_authentication_lower' => isset($headers['x-authentication']) ? $headers['x-authentication'] : 'NOT SET',
    'post_body' => $input,
    'parsed_body' => json_decode($input, true)
];

echo json_encode($response, JSON_PRETTY_PRINT);
