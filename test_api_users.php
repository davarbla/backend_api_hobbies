<?php
// Test the API endpoint for users
$url = 'http://localhost/hobbies/AllSource_Code_HobbiesApp_v102/backend_api_hobbies/root/public/index.php/api/index?lt=0,100';

// Simulate API request
$data = array(
    'lat' => '48.8566,2.3522',  // Paris coordinates
    'cc' => 'FR',
    'iu' => '',  // No user ID for testing
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    die('Error calling API');
}

$response = json_decode($result, true);

echo "API Response:\n";
echo "============\n\n";
echo "Code: " . $response['code'] . "\n";
echo "Message: " . $response['message'] . "\n\n";

if (isset($response['result']['all_user'])) {
    $users = $response['result']['all_user'];
    echo "Total users returned: " . count($users) . "\n\n";
    
    if (count($users) > 0) {
        echo "Sample users:\n";
        foreach (array_slice($users, 0, 5) as $user) {
            $lat = isset($user['lat']) ? $user['lat'] : 'NULL';
            $lng = isset($user['lng']) ? $user['lng'] : 'NULL';
            echo "  - {$user['fullname']} (ID: {$user['id_user']}) - Country: {$user['country']}, Lat: $lat, Lng: $lng\n";
        }
    } else {
        echo "No users found!\n";
    }
} else {
    echo "No 'all_user' field in response\n";
    echo "Response: " . json_encode($response) . "\n";
}
