<?php
// Test what the API query should return
$mysqli = new mysqli('localhost', 'root', '', 'hobbies');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Testing API user queries\n";
echo "========================\n\n";

// Test 1: allByLimitCountry
$country = 'FR';
$limit = 100;
$offset = 0;

echo "TEST 1: allByLimitCountry (fallback query)\n";
echo "-------------------------------------------\n";
$query = "SELECT * FROM tb_user 
          WHERE status='1' 
          AND country='$country' 
          ORDER BY total_post DESC, total_comment DESC, fullname ASC 
          LIMIT $offset, $limit";

$result = $mysqli->query($query);
echo "Query returned: " . $result->num_rows . " users\n\n";

if ($result->num_rows > 0) {
    echo "Sample users:\n";
    $count = 0;
    while ($row = $result->fetch_assoc() && $count < 5) {
        $lat = $row['lat'] ?? 'NULL';
        $lng = $row['lng'] ?? 'NULL';
        echo "  - {$row['fullname']} (ID: {$row['id_user']}) - Lat: $lat, Lng: $lng\n";
        $count++;
    }
}

echo "\n\n";

// Test 2: Check the actual API endpoint
echo "TEST 2: Simulating API index endpoint\n";
echo "--------------------------------------\n";

$lat = '48.8566';
$lng = '2.3522';
$miles = 1000/1.6;

// Bounding box calculation
$latRadian = deg2rad($lat);
$degLatKm = 110.574235;
$degLongKm = 110.572 * cos($latRadian);
$deltaLat = $miles / $degLatKm;
$deltaLong = $miles / $degLongKm;

$minLat = $lat - $deltaLat;
$maxLat = $lat + $deltaLat;
$minLon = $lng - $deltaLong;
$maxLon = $lng + $deltaLong;

echo "Distance query parameters:\n";
echo "  Lat: $lat, Lng: $lng\n";
echo "  Miles: $miles\n";
echo "  Bounding box: [$minLat to $maxLat], [$minLon to $maxLon]\n\n";

$query = "SELECT * FROM tb_user 
          WHERE status='1' 
          AND country='$country' 
          AND lat BETWEEN $minLat AND $maxLat 
          AND lng BETWEEN $minLon AND $maxLon 
          ORDER BY (abs(lng-$lng)/2) + (abs(lat-$lat)/2) 
          LIMIT $offset, $limit";

$result = $mysqli->query($query);
echo "Distance query returned: " . $result->num_rows . " users\n\n";

if ($result->num_rows > 0) {
    echo "Users with location:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['fullname']} (ID: {$row['id_user']}) - Lat: {$row['lat']}, Lng: {$row['lng']}\n";
    }
} else {
    echo "No users with valid location data found.\n";
    echo "Fallback to country query should be triggered.\n";
}

echo "\n\n";

// Test 3: Verify the API logic flow
echo "TEST 3: API Logic Flow\n";
echo "----------------------\n";
echo "1. Check if lat/lng valid: ";
if (!empty($lat) && !empty($lng) && $lat != '0' && $lng != '0') {
    echo "YES ✓\n";
    echo "2. Try distance query: ";
    
    $result = $mysqli->query("SELECT * FROM tb_user 
          WHERE status='1' 
          AND country='$country' 
          AND lat BETWEEN $minLat AND $maxLat 
          AND lng BETWEEN $minLon AND $maxLon 
          LIMIT $offset, $limit");
    
    $distanceUsers = $result->num_rows;
    echo "$distanceUsers users found\n";
    
    if ($distanceUsers == 0) {
        echo "3. Distance query empty, use fallback: ";
        $result = $mysqli->query("SELECT * FROM tb_user 
              WHERE status='1' 
              AND country='$country' 
              ORDER BY total_post DESC 
              LIMIT $offset, $limit");
        echo $result->num_rows . " users found ✓\n";
    } else {
        echo "3. Using distance query results\n";
    }
} else {
    echo "NO ✗\n";
    echo "2. Use fallback country query directly\n";
}

$mysqli->close();
echo "\n=== TEST COMPLETE ===\n";
