<?php
// Check if events have valid coordinates
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'hobbies';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$testCountry = 'FR';

echo "<h1>Event Coordinates Check</h1>";

$sql = "SELECT 
    id_post, 
    title,
    age_min,
    latitude,
    lat,
    lng,
    start_date,
    end_date
    FROM tb_post 
    WHERE country = ?
    AND status >= 1
    AND age_min != -1
    AND end_date > DATE_ADD(NOW(), INTERVAL -30 DAY)
    ORDER BY id_post DESC 
    LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $testCountry);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo "<h2>Events with Coordinates</h2>";
echo "<p><strong>Total events: " . count($events) . "</strong></p>";

if (count($events) > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #333; color: white;'>
            <th>ID</th>
            <th>Title</th>
            <th>Age Min</th>
            <th>Latitude (old)</th>
            <th>Lat</th>
            <th>Lng</th>
            <th>Has Coords?</th>
          </tr>";
    
    $withCoords = 0;
    $withoutCoords = 0;
    
    foreach ($events as $event) {
        $hasCoords = !empty($event['lat']) && !empty($event['lng']);
        if ($hasCoords) {
            $withCoords++;
            $bgColor = '#e6ffe6';
            $coordStatus = '✓ YES';
        } else {
            $withoutCoords++;
            $bgColor = '#ffe6e6';
            $coordStatus = '✗ NO';
        }
        
        echo "<tr style='background-color: $bgColor;'>";
        echo "<td>" . $event['id_post'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($event['title'], 0, 30)) . "</td>";
        echo "<td>" . $event['age_min'] . "</td>";
        echo "<td>" . htmlspecialchars($event['latitude'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($event['lat'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($event['lng'] ?? 'NULL') . "</td>";
        echo "<td><strong>" . $coordStatus . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Summary</h3>";
    echo "<ul>";
    echo "<li style='color: green;'><strong>Events with coordinates: $withCoords</strong></li>";
    echo "<li style='color: red;'><strong>Events without coordinates: $withoutCoords</strong></li>";
    echo "</ul>";
    
    if ($withoutCoords > 0) {
        echo "<div style='background-color: #fff3cd; padding: 20px; border-left: 5px solid #ffc107; margin: 20px 0;'>";
        echo "<h3>⚠ WARNING</h3>";
        echo "<p>$withoutCoords event(s) don't have lat/lng coordinates!</p>";
        echo "<p>Events without coordinates will be filtered out by distance calculations.</p>";
        echo "<p><strong>When creating events, make sure to set a location with coordinates.</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #d4edda; padding: 20px; border-left: 5px solid #28a745; margin: 20px 0;'>";
        echo "<h3>✓ All events have coordinates!</h3>";
        echo "<p>Distance filtering should work properly.</p>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>No events found!</p>";
}

$stmt->close();
$conn->close();
?>
