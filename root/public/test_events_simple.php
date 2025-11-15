<?php
// Simple test without CodeIgniter - direct database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials from Config/Database.php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'hobbies';

// Connect to database
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Test parameters (adjust these)
$testCountry = 'FR'; // Change to your country
$testUserId = 2; // Change to your user ID

echo "<h1>Test Home API Events</h1>";
echo "<p>Testing the same query used by the home page API</p>";

echo "<h2>Test Parameters</h2>";
echo "<ul>";
echo "<li>Country: $testCountry</li>";
echo "<li>User ID: $testUserId</li>";
echo "<li>Database: $database</li>";
echo "</ul>";

// Run the same query as the API (with COALESCE for null safety)
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
    LIMIT 0, 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $testCountry);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

echo "<h2>Results</h2>";
echo "<p><strong>Total posts/events returned: " . count($posts) . "</strong></p>";

if (count($posts) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>
            <th>ID</th>
            <th>Title</th>
            <th>Country</th>
            <th>Age Min</th>
            <th>Age Max</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Type</th>
          </tr>";

    $eventCount = 0;
    $postCount = 0;

    foreach ($posts as $post) {
        $type = ($post['age_min'] == -1) ? 'POST' : 'EVENT';
        $rowColor = ($type == 'EVENT') ? 'background-color: #e6ffe6;' : '';
        
        if ($type == 'EVENT') {
            $eventCount++;
        } else {
            $postCount++;
        }
        
        echo "<tr style='$rowColor'>";
        echo "<td>" . htmlspecialchars($post['id_post']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['title'], 0, 40)) . "...</td>";
        echo "<td>" . htmlspecialchars($post['country'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($post['age_min']) . "</td>";
        echo "<td>" . htmlspecialchars($post['age_max']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['start_date'] ?? 'NULL', 0, 10)) . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['end_date'] ?? 'NULL', 0, 10)) . "</td>";
        echo "<td>" . htmlspecialchars($post['status']) . "</td>";
        echo "<td><strong>" . $type . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Summary</h3>";
    echo "<ul>";
    echo "<li><strong style='color: green;'>Events (age_min != -1): $eventCount</strong></li>";
    echo "<li>Posts (age_min = -1): $postCount</li>";
    echo "</ul>";
    
    if ($eventCount > 0) {
        echo "<div style='background-color: #d4edda; padding: 20px; border: 2px solid #28a745; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='color: #155724;'>✓ SUCCESS!</h3>";
        echo "<p>The API is returning <strong>$eventCount event(s)</strong> for country '$testCountry'!</p>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ol>";
        echo "<li><strong>Restart your Flutter app</strong> (full restart, not hot reload)</li>";
        echo "<li>Pull down to refresh on the home page</li>";
        echo "<li>Check the 'Events nearby' and 'Upcoming' sections</li>";
        echo "</ol>";
        echo "<p><em>If events still don't appear, check that the country in your Flutter app matches '$testCountry'</em></p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #fff3cd; padding: 20px; border: 2px solid #ffc107; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='color: #856404;'>⚠ No Events Found</h3>";
        echo "<p>All returned items are POSTS (age_min = -1), not EVENTS.</p>";
        echo "<p><strong>To create an event that will appear:</strong></p>";
        echo "<ul>";
        echo "<li>Set <code>age_min</code> to a valid age (e.g., 18, not -1)</li>";
        echo "<li>Set <code>age_max</code> to a valid age (e.g., 99)</li>";
        echo "<li>Set <code>country</code> = '$testCountry'</li>";
        echo "<li>Set <code>end_date</code> to a future date</li>";
        echo "<li>Set <code>status</code> >= 1</li>";
        echo "</ul>";
        echo "</div>";
    }
} else {
    echo "<div style='background-color: #f8d7da; padding: 20px; border: 2px solid #dc3545; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24;'>✗ No Results</h3>";
    echo "<p>The query returned no posts/events at all.</p>";
    echo "<p><strong>Possible reasons:</strong></p>";
    echo "<ul>";
    echo "<li>No posts/events in database with country = '$testCountry'</li>";
    echo "<li>All posts/events have end_date older than 30 days ago</li>";
    echo "<li>All posts/events have status = 0</li>";
    echo "</ul>";
    echo "<p><strong>Try creating a new event with these settings:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    echo "Country: $testCountry\n";
    echo "Age Min: 18\n";
    echo "Age Max: 99\n";
    echo "End Date: " . date('Y-m-d H:i:s', strtotime('+7 days')) . "\n";
    echo "Status: 1";
    echo "</pre>";
    echo "</div>";
}

// Show the SQL query for reference
echo "<h3>SQL Query Used</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
echo htmlspecialchars($sql);
echo "\n\nWith parameters:\n";
echo "country = '$testCountry'";
echo "</pre>";

$stmt->close();
$conn->close();
?>
