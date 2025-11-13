<?php
// Test the install endpoint to ensure it returns user data
$url = 'http://localhost:8000/install/saveUpdate';
$data = [
    'id' => '',
    'tk' => 'eslFc4PmRGO_Newp-kkKYt:APA91bECQ8koWYKOs5GFmu3JiCo__w7SAcVvI_VAgV0WrKlB06ZUqZ6fc8caRaUp23TsjJNolepscYv_EcJGp3AHEusCiTqU5tj4igEZlO6FWPb2SXcb2W8',
    'uuid' => '2dce1e20-c068-11f0-b557-1df91d44d900',
    'os' => 'Android'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Authentication: ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Testing Install SaveUpdate with User\n";
echo "====================================\n";
echo "HTTP Status: $httpCode\n\n";

$responseData = json_decode($response, true);

if ($httpCode == 200) {
    echo "✅ SUCCESS: Install saved/updated\n";
    if (isset($responseData['result'][0])) {
        $install = $responseData['result'][0];
        echo "Install ID: {$install['id_install']}\n";
        
        if (isset($install['user'])) {
            echo "\n✅ User data returned:\n";
            echo "User ID: {$install['user']['id_user']}\n";
            echo "Email: {$install['user']['email']}\n";
            echo "Username: {$install['user']['username']}\n";
        } else {
            echo "\n❌ No user data in response\n";
        }
    }
} else {
    echo "❌ ERROR: " . ($responseData['message'] ?? 'Unknown error') . "\n";
}
?>
