<?php
$mysqli = new mysqli('localhost', 'root', '', 'hobbies');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== VERIFY API WILL RETURN USERS ===\n\n";

// Test the exact query the API uses
$country = 'FR';
$limit = 100;
$offset = 0;

$query = "SELECT * FROM tb_user 
          WHERE status='1' 
          AND country='$country' 
          ORDER BY total_post DESC, total_comment DESC, fullname ASC 
          LIMIT $offset, $limit";

echo "Query: $query\n\n";

$result = $mysqli->query($query);

if ($result) {
    echo "✓ Query executed successfully\n";
    echo "✓ Users returned: " . $result->num_rows . "\n\n";
    
    if ($result->num_rows > 0) {
        echo "Sample users that will be sent to app:\n";
        $count = 0;
        while ($row = $result->fetch_assoc()) {
            if ($count < 10) {
                $hasLocation = (!empty($row['lat']) && !empty($row['lng'])) ? '✓ Location' : '✗ No location';
                echo "  " . ($count + 1) . ". {$row['fullname']} (ID: {$row['id_user']}) - $hasLocation\n";
                $count++;
            }
        }
        
        echo "\n✓ API is configured to return " . $result->num_rows . " users for country '$country'\n";
    } else {
        echo "✗ NO USERS FOUND for country '$country'\n";
        echo "\nChecking other countries:\n";
        
        $countryQuery = "SELECT country, COUNT(*) as count FROM tb_user WHERE status='1' GROUP BY country";
        $countryResult = $mysqli->query($countryQuery);
        
        while ($row = $countryResult->fetch_assoc()) {
            echo "  - Country '{$row['country']}': {$row['count']} users\n";
        }
    }
} else {
    echo "✗ Query failed: " . $mysqli->error . "\n";
}

$mysqli->close();
echo "\n=== VERIFICATION COMPLETE ===\n";
