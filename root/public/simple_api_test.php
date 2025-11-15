<?php
// Simple direct API test
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../app/Config/Database.php';

echo "<h1>Simple API Test - Direct Database Query</h1>";

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'hobbies';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$testCountry = 'FR';
$testUserId = '30';

echo "<h2>Testing with User ID: $testUserId, Country: $testCountry</h2>";

// Test the exact query from PostModel::allByLimitByIdUserCountry
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

echo "<h2>Query Results</h2>";
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
            <th>Country</th>
            <th>End Date</th>
            <th>Type</th>
          </tr>";
    
    foreach ($posts as $post) {
        $ageMin = intval($post['age_min']);
        $type = ($ageMin == -1) ? 'POST' : 'EVENT';
        
        if ($type == 'EVENT') {
            $eventCount++;
            $bgColor = '#e6ffe6';
        } else {
            $postCount++;
            $bgColor = '#ffe6e6';
        }
        
        echo "<tr style='background-color: $bgColor;'>";
        echo "<td>" . $post['id_post'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['title'], 0, 40)) . "</td>";
        echo "<td><strong>" . $ageMin . "</strong></td>";
        echo "<td>" . $post['age_max'] . "</td>";
        echo "<td>" . $post['country'] . "</td>";
        echo "<td>" . substr($post['end_date'], 0, 10) . "</td>";
        echo "<td><strong>" . $type . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Summary</h3>";
    echo "<div style='padding: 20px; margin: 20px 0;'>";
    echo "<p style='font-size: 18px;'><strong style='color: green;'>EVENTS: $eventCount</strong></p>";
    echo "<p style='font-size: 18px;'>Posts: $postCount</p>";
    echo "</div>";
    
    if ($eventCount > 0) {
        echo "<div style='background: #d4edda; padding: 20px; border-left: 5px solid #28a745;'>";
        echo "<h3>✓ Database has $eventCount events!</h3>";
        echo "<p><strong>The backend query is working correctly.</strong></p>";
        echo "<p>If events still don't show in Flutter:</p>";
        echo "<ul>";
        echo "<li>Check that Flutter is calling <code>/api/index</code> endpoint</li>";
        echo "<li>Check that the API is returning these events</li>";
        echo "<li>Check Flutter filtering logic</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 20px; border-left: 5px solid #ffc107;'>";
        echo "<h3>⚠ No Events Found</h3>";
        echo "<p>All items are posts (age_min = -1)</p>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-left: 5px solid #dc3545;'>";
    echo "<h3>✗ No Results</h3>";
    echo "<p>Query returned nothing for country '$testCountry'</p>";
    echo "</div>";
}

$stmt->close();
$conn->close();
?>
