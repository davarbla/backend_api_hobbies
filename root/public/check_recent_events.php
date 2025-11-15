<?php
// Database connection
require_once '../app/Config/Database.php';

$config = new Config\Database();
$db = \Config\Database::connect();

// Get recent posts with event-related fields
$query = $db->query("
    SELECT 
        id_post, 
        title, 
        id_user,
        country,
        start_date, 
        end_date, 
        age_min, 
        age_max,
        status,
        date_created
    FROM tb_post 
    ORDER BY id_post DESC 
    LIMIT 10
");

$results = $query->getResultArray();

echo "<h2>Recent Posts/Events (Last 10)</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr>
        <th>ID</th>
        <th>Title</th>
        <th>User ID</th>
        <th>Country</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Age Min</th>
        <th>Age Max</th>
        <th>Status</th>
        <th>Created</th>
        <th>Type</th>
      </tr>";

foreach ($results as $row) {
    $type = ($row['age_min'] == -1 || $row['age_min'] === null) ? 'POST' : 'EVENT';
    echo "<tr>";
    echo "<td>" . $row['id_post'] . "</td>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td>" . $row['id_user'] . "</td>";
    echo "<td>" . $row['country'] . "</td>";
    echo "<td>" . ($row['start_date'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['end_date'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['age_min'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['age_max'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>" . $row['date_created'] . "</td>";
    echo "<td><strong>" . $type . "</strong></td>";
    echo "</tr>";
}

echo "</table>";

// Check if end_date column exists
$query2 = $db->query("SHOW COLUMNS FROM tb_post LIKE 'end_date'");
$columnExists = $query2->getResultArray();

echo "<h3>Table Structure Check:</h3>";
if (count($columnExists) > 0) {
    echo "<p style='color: green;'>✓ end_date column exists</p>";
} else {
    echo "<p style='color: red;'>✗ end_date column MISSING!</p>";
}

// Check for events that should appear on home page
echo "<h3>Events that should appear (end_date > now - 30 days, status >= 1):</h3>";
$query3 = $db->query("
    SELECT 
        id_post, 
        title, 
        country,
        end_date, 
        age_min,
        status,
        DATEDIFF(end_date, NOW()) as days_until_end
    FROM tb_post 
    WHERE status >= 1 
    AND end_date > DATE_ADD(NOW(), INTERVAL -30 DAY)
    AND age_min != -1
    ORDER BY id_post DESC 
    LIMIT 10
");

$events = $query3->getResultArray();
echo "<table border='1' cellpadding='5'>";
echo "<tr>
        <th>ID</th>
        <th>Title</th>
        <th>Country</th>
        <th>End Date</th>
        <th>Age Min</th>
        <th>Status</th>
        <th>Days Until End</th>
      </tr>";

foreach ($events as $event) {
    echo "<tr>";
    echo "<td>" . $event['id_post'] . "</td>";
    echo "<td>" . htmlspecialchars($event['title']) . "</td>";
    echo "<td>" . $event['country'] . "</td>";
    echo "<td>" . $event['end_date'] . "</td>";
    echo "<td>" . $event['age_min'] . "</td>";
    echo "<td>" . $event['status'] . "</td>";
    echo "<td>" . $event['days_until_end'] . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<p><em>Total events found: " . count($events) . "</em></p>";
?>
