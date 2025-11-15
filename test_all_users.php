<?php
// Direct database connection
$mysqli = new mysqli('localhost', 'root', '', 'hobbies');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected to database successfully\n\n";

// Test 1: Count total users
echo "=== TEST 1: Total Users ===\n";
$result = $mysqli->query("SELECT COUNT(*) as total FROM tb_user");
$row = $result->fetch_assoc();
echo "Total users in database: " . $row['total'] . "\n\n";

// Test 2: Count active users (status=1)
echo "=== TEST 2: Active Users ===\n";
$result = $mysqli->query("SELECT COUNT(*) as total FROM tb_user WHERE status='1'");
$row = $result->fetch_assoc();
echo "Active users (status=1): " . $row['total'] . "\n\n";

// Test 3: Check users with location data
echo "=== TEST 3: Users with Location ===\n";
$result = $mysqli->query("SELECT COUNT(*) as total FROM tb_user WHERE status='1' AND lat IS NOT NULL AND lng IS NOT NULL");
$row = $result->fetch_assoc();
echo "Active users with lat/lng: " . $row['total'] . "\n\n";

// Test 4: Sample users with location
echo "=== TEST 4: Sample Users ===\n";
$result = $mysqli->query("SELECT id_user, fullname, country, lat, lng, status FROM tb_user WHERE status='1' AND lat IS NOT NULL AND lng IS NOT NULL LIMIT 5");
echo "Sample users:\n";
while ($row = $result->fetch_assoc()) {
    echo "  ID: {$row['id_user']}, Name: {$row['fullname']}, Country: {$row['country']}, Lat: {$row['lat']}, Lng: {$row['lng']}\n";
}
echo "\n";

// Test 5: Test the distance query with sample coordinates
echo "=== TEST 5: Distance Query Test ===\n";
// Using Paris coordinates as example
$testLat = 48.8566;
$testLng = 2.3522;
$miles = 1000/1.6;
$country = 'FR';

// Calculate bounding box
$latRadian = deg2rad($testLat);
$degLatKm = 110.574235;
$degLongKm = 110.572 * cos($latRadian);
$deltaLat = $miles / $degLatKm;
$deltaLong = $miles / $degLongKm;

$minLat = $testLat - $deltaLat;
$maxLat = $testLat + $deltaLat;
$minLon = $testLng - $deltaLong;
$maxLon = $testLng + $deltaLong;

echo "Test coordinates: Lat=$testLat, Lng=$testLng\n";
echo "Bounding box: Lat[$minLat to $maxLat], Lng[$minLon to $maxLon]\n";
echo "Country filter: $country\n";
echo "Distance: $miles miles\n\n";

$query = "SELECT a.id_user, a.fullname, a.country, a.lat, a.lng 
          FROM tb_user a 
          WHERE a.status='1' 
          AND a.lat IS NOT NULL 
          AND a.lng IS NOT NULL
          ORDER BY (abs(a.lng-$testLng)/2) + (abs(a.lat-$testLat)/2) 
          LIMIT 10";

echo "Running query without country/distance filter:\n";
$result = $mysqli->query($query);
echo "Found " . $result->num_rows . " users\n";
while ($row = $result->fetch_assoc()) {
    $distance = sqrt(pow($row['lat'] - $testLat, 2) + pow($row['lng'] - $testLng, 2));
    echo "  ID: {$row['id_user']}, Name: {$row['fullname']}, Country: {$row['country']}, Distance: " . number_format($distance, 4) . "\n";
}
echo "\n";

$mysqli->close();
echo "\n=== TEST COMPLETE ===\n";
