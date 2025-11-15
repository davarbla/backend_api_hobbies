<?php
// Test the actual /api/index endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test API Index Endpoint</h1>";

// Simulate the request that Flutter sends
$testUserId = '30'; // Your user ID
$testCountry = 'FR';
$testLat = '48.8575467,2.351375';

echo "<h2>Request Parameters</h2>";
echo "<ul>";
echo "<li>User ID (iu): $testUserId</li>";
echo "<li>Country (cc): $testCountry</li>";
echo "<li>Latitude (lat): $testLat</li>";
echo "<li>Limit (lt): 0,10</li>";
echo "</ul>";

// Set up a POST request to the API
$url = 'http://localhost:8000/api/index?lt=0,10';

$postData = json_encode([
    'lat' => $testLat,
    'loc' => 'Paris FR',
    'cc' => $testCountry,
    'iu' => $testUserId
]);

echo "<h2>Making API Call...</h2>";
echo "<pre>URL: $url</pre>";
echo "<pre>POST Data: $postData</pre>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h2>API Response</h2>";
echo "<p><strong>HTTP Code: $httpCode</strong></p>";

if ($response) {
    $result = json_decode($response, true);
    
    echo "<div style='background: #f5f5f5; padding: 15px; margin: 10px 0;'>";
    echo "<h3>Response Code: " . ($result['code'] ?? 'N/A') . "</h3>";
    echo "<h3>Message: " . ($result['message'] ?? 'N/A') . "</h3>";
    echo "</div>";
    
    if (isset($result['result']['latest_post'])) {
        $latestPosts = $result['result']['latest_post'];
        echo "<h3>Latest Posts Count: " . count($latestPosts) . "</h3>";
        
        if (count($latestPosts) > 0) {
            $eventCount = 0;
            $postCount = 0;
            
            foreach ($latestPosts as $post) {
                if (isset($post['age_min']) && $post['age_min'] != -1) {
                    $eventCount++;
                } else {
                    $postCount++;
                }
            }
            
            echo "<div style='background: #d4edda; padding: 20px; border-left: 5px solid #28a745; margin: 20px 0;'>";
            echo "<h3>✓ SUCCESS!</h3>";
            echo "<p><strong>Events: $eventCount</strong></p>";
            echo "<p>Posts: $postCount</p>";
            echo "</div>";
            
            // Show first few items
            echo "<h3>First 3 Items:</h3>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Title</th><th>Age Min</th><th>Type</th></tr>";
            for ($i = 0; $i < min(3, count($latestPosts)); $i++) {
                $post = $latestPosts[$i];
                $type = (isset($post['age_min']) && $post['age_min'] != -1) ? 'EVENT' : 'POST';
                echo "<tr>";
                echo "<td>" . ($post['id_post'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars(substr($post['title'] ?? 'N/A', 0, 30)) . "</td>";
                echo "<td>" . ($post['age_min'] ?? 'NULL') . "</td>";
                echo "<td><strong>$type</strong></td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<div style='background: #fff3cd; padding: 20px; border-left: 5px solid #ffc107; margin: 20px 0;'>";
            echo "<h3>⚠ No Posts Returned</h3>";
            echo "<p>API returned empty latest_post array</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-left: 5px solid #dc3545; margin: 20px 0;'>";
        echo "<h3>✗ ERROR</h3>";
        echo "<p>latest_post not found in response</p>";
        echo "</div>";
    }
    
    echo "<h3>Full Response (first 1000 chars):</h3>";
    echo "<pre style='background: #2d2d2d; color: #f8f8f2; padding: 15px; overflow-x: auto;'>";
    echo htmlspecialchars(substr($response, 0, 1000));
    echo "\n...</pre>";
    
} else {
    echo "<div style='background: #f8d7da; padding: 20px;'>";
    echo "<h3>✗ API Call Failed</h3>";
    echo "<p>No response from server</p>";
    echo "</div>";
}
?>
