<?php
// Direct test of the home API response
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate the API request
$_SERVER['REQUEST_METHOD'] = 'POST';

// Test user data (change these to match your user)
$testUserId = '30'; // From your logs: id_user: 30
$testCountry = 'FR';
$testLatitude = '48.8575467,2.351375'; // From your logs

$postData = json_encode([
    'lat' => $testLatitude,
    'cc' => $testCountry,
    'iu' => $testUserId
]);

// Set up the request
file_put_contents('php://input', $postData);

echo "<h1>Direct Home API Test</h1>";
echo "<p>Testing what the actual API endpoint returns</p>";

echo "<h2>Request Parameters</h2>";
echo "<ul>";
echo "<li>User ID: $testUserId</li>";
echo "<li>Country: $testCountry</li>";
echo "<li>Latitude: $testLatitude</li>";
echo "</ul>";

// Database connection
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'hobbies';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Test the exact query used by the API
$limit = 10;
$offset = 0;

$sql = "SELECT a.*,
    COALESCE(a.age_min, -1) as age_min,
    COALESCE(a.age_max, 99) as age_max,
    COALESCE(a.max_people, 0) as max_people,
    COALESCE(a.price, 0) as price
    FROM tb_post a 
    WHERE a.status >= 1
    AND a.end_date > DATE_ADD(NOW(), INTERVAL -30 DAY) 
    AND a.country = ?
    ORDER BY a.id_post DESC, a.total_like DESC, a.total_comment DESC, a.title ASC 
    LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $testCountry, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

echo "<h2>API Response - latest_post</h2>";
echo "<p><strong>Total items: " . count($posts) . "</strong></p>";

if (count($posts) > 0) {
    $eventCount = 0;
    $postCount = 0;
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #333; color: white;'>
            <th>ID</th>
            <th>Title</th>
            <th>Age Min</th>
            <th>Age Max</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Country</th>
            <th>Type</th>
            <th>Will Show?</th>
          </tr>";
    
    foreach ($posts as $post) {
        $ageMin = intval($post['age_min']);
        $type = ($ageMin == -1) ? 'POST' : 'EVENT';
        
        if ($type == 'EVENT') {
            $eventCount++;
            $rowColor = '#e6ffe6';
            $willShow = 'YES';
        } else {
            $postCount++;
            $rowColor = '#ffe6e6';
            $willShow = 'NO (Post)';
        }
        
        echo "<tr style='background-color: $rowColor;'>";
        echo "<td>" . htmlspecialchars($post['id_post']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['title'], 0, 30)) . "</td>";
        echo "<td><strong>" . htmlspecialchars($ageMin) . "</strong></td>";
        echo "<td>" . htmlspecialchars($post['age_max']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['start_date'] ?? 'NULL', 0, 16)) . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['end_date'] ?? 'NULL', 0, 16)) . "</td>";
        echo "<td>" . htmlspecialchars($post['country']) . "</td>";
        echo "<td><strong>" . $type . "</strong></td>";
        echo "<td><strong>" . $willShow . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='margin-top: 30px;'>Summary</h3>";
    echo "<div style='padding: 15px; background: #f0f0f0; border-radius: 5px;'>";
    echo "<ul style='font-size: 16px; margin: 0;'>";
    echo "<li><strong style='color: green; font-size: 18px;'>EVENTS (age_min != -1): $eventCount</strong></li>";
    echo "<li><strong style='color: red; font-size: 18px;'>POSTS (age_min = -1): $postCount</strong></li>";
    echo "</ul>";
    echo "</div>";
    
    if ($eventCount > 0) {
        echo "<div style='background-color: #d4edda; padding: 20px; border-left: 5px solid #28a745; margin: 20px 0;'>";
        echo "<h3 style='color: #155724; margin-top: 0;'>✓ BACKEND IS WORKING!</h3>";
        echo "<p><strong>$eventCount event(s) found in database and returned by API.</strong></p>";
        echo "<p style='color: #721c24; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "<strong>⚠ Since events are in the backend but NOT showing in Flutter app, the issue is in the Flutter side:</strong><br>";
        echo "Possible causes:<br>";
        echo "1. Flutter filtering is removing them (check filterPost, filterEventsTooFar, filterEventsFinished)<br>";
        echo "2. Distance calculation removing them (events too far)<br>";
        echo "3. Date filtering removing them (events considered finished)<br>";
        echo "4. App needs full restart (not just hot reload)";
        echo "</p>";
        echo "</div>";
        
        // Show specific event details
        echo "<h3>Event Details for Debugging:</h3>";
        foreach ($posts as $post) {
            if (intval($post['age_min']) != -1) {
                echo "<div style='background: #fff; padding: 15px; margin: 10px 0; border: 1px solid #ddd;'>";
                echo "<h4>Event ID: " . $post['id_post'] . " - " . htmlspecialchars($post['title']) . "</h4>";
                echo "<pre style='background: #f5f5f5; padding: 10px;'>";
                echo "age_min: " . $post['age_min'] . " (EVENT marker)\n";
                echo "age_max: " . $post['age_max'] . "\n";
                echo "start_date: " . ($post['start_date'] ?? 'NULL') . "\n";
                echo "end_date: " . ($post['end_date'] ?? 'NULL') . "\n";
                echo "latitude: " . ($post['latitude'] ?? 'NULL') . "\n";
                echo "country: " . $post['country'] . "\n";
                echo "status: " . $post['status'];
                echo "</pre>";
                echo "</div>";
            }
        }
    } else {
        echo "<div style='background-color: #fff3cd; padding: 20px; border-left: 5px solid #ffc107; margin: 20px 0;'>";
        echo "<h3 style='color: #856404; margin-top: 0;'>⚠ NO EVENTS IN DATABASE</h3>";
        echo "<p>All $postCount items are POSTS (age_min = -1), not EVENTS.</p>";
        echo "<p><strong>To create an event that will appear:</strong></p>";
        echo "<ol>";
        echo "<li>Create a new event in the app</li>";
        echo "<li>Make sure age_min is set to 18 (or any valid age, NOT -1)</li>";
        echo "<li>Set end_date to a future date</li>";
        echo "<li>Set country to '$testCountry'</li>";
        echo "</ol>";
        echo "</div>";
    }
} else {
    echo "<div style='background-color: #f8d7da; padding: 20px; border-left: 5px solid #dc3545; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>✗ NO DATA RETURNED</h3>";
    echo "<p>The API returned nothing for country '$testCountry'.</p>";
    echo "</div>";
}

echo "<h3 style='margin-top: 40px;'>SQL Query Used</h3>";
echo "<pre style='background: #2d2d2d; color: #f8f8f2; padding: 15px; overflow-x: auto;'>";
echo htmlspecialchars($sql);
echo "\n\nParameters:\ncountry = '$testCountry'\noffset = $offset\nlimit = $limit";
echo "</pre>";

$stmt->close();
$conn->close();
?>
