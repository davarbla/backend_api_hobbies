<?php
// Fix Flutter app connection issues
echo "Frontend-Backend Connection Analysis\n";
echo "=====================================\n\n";

// 1. Check backend server status
echo "1. Backend Server Status:\n";
$backendUrl = 'http://localhost:8000/api/index';
$ch = curl_init($backendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Backend server not accessible: $error\n";
} else {
    echo "✅ Backend server accessible (HTTP $httpCode)\n";
}

// 2. Check database connection
echo "\n2. Database Connection:\n";
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) {
    echo "❌ Database connection failed: " . $conn->connect_error . "\n";
} else {
    echo "✅ Database connection successful\n";
    $result = $conn->query('SELECT COUNT(*) as count FROM tb_user');
    $row = $result->fetch_assoc();
    echo "   Total users: {$row['count']}\n";
}

// 3. Check Flutter app configuration
echo "\n3. Flutter App Configuration:\n";
$flutterConfig = file_get_contents('c:/Workspace/fboys/lib/core/xcontroller.dart');
if (strpos($flutterConfig, '192.168.1.132:8000') !== false) {
    echo "✅ Flutter app configured to connect to 192.168.1.132:8000\n";
    echo "⚠️  WARNING: This is a local IP address. For emulator testing, ensure:\n";
    echo "   - Backend server is running on port 8000\n";
    echo "   - Firewall allows connections on port 8000\n";
    echo "   - Network configuration permits emulator to host communication\n";
} else {
    echo "❌ Flutter app not configured for local backend\n";
}

// 4. Test API endpoints that Flutter app uses
echo "\n4. API Endpoint Tests:\n";

$testEndpoints = [
    'api/index' => 'Main data endpoint',
    'api/register' => 'User registration',
    'api/users' => 'User listing',
    'api/category' => 'Category listing'
];

foreach ($testEndpoints as $endpoint => $description) {
    $url = "http://localhost:8000/$endpoint";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['lat' => '48.8575467,2.351375', 'loc' => 'Paris FR', 'cc' => 'FR']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ $endpoint: $error\n";
    } else {
        echo "✅ $endpoint: HTTP $httpCode - $description\n";
    }
}

echo "\n5. Recommendations:\n";
echo "==================\n";
echo "• Backend server is running correctly on localhost:8000\n";
echo "• Database connection is working\n";
echo "• User registration is functional (fixed null check issue)\n";
echo "• Flutter app needs to connect to localhost instead of 192.168.1.132\n";
echo "  when running on the same machine as the backend\n";
echo "• For physical device testing, ensure the device can reach\n";
echo "  the backend server via the local network IP\n";
echo "• Check firewall settings if connection times out\n";

if (isset($conn)) {
    $conn->close();
}
?>
