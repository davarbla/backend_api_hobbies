<?php
// Test the home API endpoint with sample data
require_once '../app/Config/Database.php';

echo "<h1>Test Home API Endpoint</h1>";

// Simulate API request
$testCountry = 'FR'; // Change to your country
$testUserId = 2; // Change to your user ID
$testLat = '48.8566,2.3522'; // Paris coordinates

echo "<h2>Test Parameters</h2>";
echo "<ul>";
echo "<li>Country: $testCountry</li>";
echo "<li>User ID: $testUserId</li>";
echo "<li>Latitude: $testLat</li>";
echo "</ul>";

// Call the model directly
$config = new Config\Database();
$db = \Config\Database::connect();

// Load PostModel
require_once '../app/Models/PostModel.php';
$postModel = new App\Models\PostModel();

// Get posts using the same method as API
$limit = 10;
$offset = 0;
$posts = $postModel->allByLimitByIdUserCountry($testUserId, $limit, $offset, $testCountry);

echo "<h2>Results from allByLimitByIdUserCountry()</h2>";
echo "<p><strong>Total posts/events returned: " . count($posts) . "</strong></p>";

if (count($posts) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
            <th>ID</th>
            <th>Title</th>
            <th>Country</th>
            <th>Age Min</th>
            <th>Age Max</th>
            <th>Max People</th>
            <th>Price</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Type</th>
          </tr>";

    foreach ($posts as $post) {
        $type = ($post['age_min'] == -1 || $post['age_min'] === null) ? 'POST' : 'EVENT';
        $rowColor = ($type == 'EVENT') ? 'style="background-color: #e6ffe6;"' : '';
        
        echo "<tr $rowColor>";
        echo "<td>" . $post['id_post'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['title'], 0, 30)) . "</td>";
        echo "<td>" . ($post['country'] ?? 'NULL') . "</td>";
        echo "<td>" . ($post['age_min'] ?? 'NULL') . "</td>";
        echo "<td>" . ($post['age_max'] ?? 'NULL') . "</td>";
        echo "<td>" . ($post['max_people'] ?? 'NULL') . "</td>";
        echo "<td>" . ($post['price'] ?? 'NULL') . "</td>";
        echo "<td>" . ($post['start_date'] ?? 'NULL') . "</td>";
        echo "<td>" . ($post['end_date'] ?? 'NULL') . "</td>";
        echo "<td><strong>" . $type . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count events vs posts
    $eventCount = 0;
    $postCount = 0;
    foreach ($posts as $post) {
        if ($post['age_min'] == -1 || $post['age_min'] === null) {
            $postCount++;
        } else {
            $eventCount++;
        }
    }
    
    echo "<h3>Summary</h3>";
    echo "<ul>";
    echo "<li><strong>Events: $eventCount</strong></li>";
    echo "<li>Posts: $postCount</li>";
    echo "</ul>";
    
    if ($eventCount > 0) {
        echo "<div style='background-color: #e6ffe6; padding: 15px; border: 2px solid green;'>";
        echo "<h3>✓ SUCCESS!</h3>";
        echo "<p>The API is returning <strong>$eventCount events</strong>. They should now appear in your Flutter app!</p>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ol>";
        echo "<li>Restart your Flutter app</li>";
        echo "<li>Pull to refresh on the home page</li>";
        echo "<li>Check 'Events nearby' and 'Upcoming' sections</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #fff3cd; padding: 15px; border: 2px solid #ffc107;'>";
        echo "<h3>⚠ No Events Found</h3>";
        echo "<p>The API is working but no events match the criteria. Create a new event with:</p>";
        echo "<ul>";
        echo "<li>Country = '$testCountry'</li>";
        echo "<li>Age Min = 18 (or any value != -1)</li>";
        echo "<li>End Date = future date (> now - 30 days)</li>";
        echo "<li>Status = 1</li>";
        echo "</ul>";
        echo "</div>";
    }
} else {
    echo "<div style='background-color: #f8d7da; padding: 15px; border: 2px solid #dc3545;'>";
    echo "<h3>✗ No Results</h3>";
    echo "<p>The API returned no posts/events at all. Check:</p>";
    echo "<ul>";
    echo "<li>Database has posts with country = '$testCountry'</li>";
    echo "<li>Posts have end_date > (NOW() - 30 days)</li>";
    echo "<li>Posts have status >= 1</li>";
    echo "</ul>";
    echo "</div>";
}

// Show the SQL query that was executed
echo "<h2>Debug Info</h2>";
echo "<pre>";
echo "Query executed:\n";
echo "SELECT a.*, COALESCE(a.age_min, -1) as age_min, COALESCE(a.age_max, 99) as age_max, ";
echo "COALESCE(a.max_people, 0) as max_people, COALESCE(a.price, 0) as price\n";
echo "FROM tb_post a\n";
echo "WHERE a.status >= 1\n";
echo "AND a.end_date > DATE_ADD(now(), INTERVAL -30 DAY)\n";
echo "AND a.country = '$testCountry'\n";
echo "ORDER BY a.id_post DESC, a.total_like DESC, a.total_comment DESC, a.title ASC\n";
echo "LIMIT $offset,$limit";
echo "</pre>";
?>
