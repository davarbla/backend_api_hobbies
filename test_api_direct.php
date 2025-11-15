<?php
// Direct test of the API logic without HTTP
define('FCPATH', __DIR__ . '/root/public/');
define('APPPATH', __DIR__ . '/root/app/');
define('ROOTPATH', __DIR__ . '/root/');
define('SYSTEMPATH', __DIR__ . '/root/system/');

// Set environment
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

// Bootstrap CodeIgniter
require_once SYSTEMPATH . 'bootstrap.php';

// Create API controller instance
$api = new \App\Controllers\Api();

// Simulate request data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

$postData = [
    'lat' => '48.8566,2.3522',
    'cc' => 'FR',
    'iu' => '28'  // Use user ID 28 from test
];

// Set raw input for CodeIgniter
$GLOBALS['mockInput'] = json_encode($postData);

// Capture output
ob_start();
try {
    $api->index();
    $output = ob_get_clean();
    
    echo "API Response:\n";
    echo "=============\n";
    echo $output . "\n";
    
    $response = json_decode($output, true);
    if ($response && isset($response['result']['all_user'])) {
        echo "\n\nUsers count: " . count($response['result']['all_user']) . "\n";
        
        if (count($response['result']['all_user']) > 0) {
            echo "\nFirst 3 users:\n";
            foreach (array_slice($response['result']['all_user'], 0, 3) as $user) {
                echo "  - " . $user['fullname'] . " (ID: " . $user['id_user'] . ")\n";
            }
        }
    } else {
        echo "\nNo users in response or invalid response format\n";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
