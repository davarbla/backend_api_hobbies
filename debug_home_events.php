<?php
// Debug script to check why events don't appear on home page
require_once 'root/app/Config/Database.php';

$config = new Config\Database();
$db = \Config\Database::connect();

// Test parameters (adjust these to match your user)
$testCountry = 'FR'; // Change this to your country code
$testUserId = 2; // Change this to your user ID
$limit = 10;
$offset = 0;
$status = 1;

echo "<h1>Home Page Events Debug</h1>";
echo "<p><strong>Test Parameters:</strong></p>";
echo "<ul>";
echo "<li>Country: $testCountry</li>";
echo "<li>User ID: $testUserId</li>";
echo "<li>Limit: $limit</li>";
echo "<li>Status: >= $status</li>";
echo "</ul>";

// 1. Check all recent events (not filtered)
echo "<h2>1. All Recent Events (Last 10, no filters)</h2>";
$query1 = $db->query("
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
        DATEDIFF(end_date, NOW()) as days_until_end
    FROM tb_post 
    ORDER BY id_post DESC 
    LIMIT 10
");

$allRecent = $query1->getResultArray();
echo "<table border='1' cellpadding='5'>";
echo "<tr>
        <th>ID</th>
        <th>Title</th>
        <th>User ID</th>
        <th>Country</th>
        <th>End Date</th>
        <th>Age Min</th>
        <th>Status</th>
        <th>Days Until End</th>
        <th>Type</th>
        <th>Will Appear?</th>
      </tr>";

foreach ($allRecent as $row) {
    $type = ($row['age_min'] == -1 || $row['age_min'] === null) ? 'POST' : 'EVENT';
    $willAppear = 'NO';
    $reason = [];
    
    if ($type == 'POST') {
        $reason[] = "Is a post, not event";
    }
    if ($row['status'] < $status) {
        $reason[] = "Status too low";
    }
    if ($row['country'] != $testCountry) {
        $reason[] = "Wrong country";
    }
    if ($row['end_date'] && strtotime($row['end_date']) <= strtotime('-30 days')) {
        $reason[] = "End date too old";
    }
    
    if (empty($reason) && $type == 'EVENT') {
        $willAppear = 'YES';
    }
    
    $reasonText = empty($reason) ? 'All OK!' : implode(', ', $reason);
    
    echo "<tr>";
    echo "<td>" . $row['id_post'] . "</td>";
    echo "<td>" . htmlspecialchars(substr($row['title'], 0, 30)) . "</td>";
    echo "<td>" . $row['id_user'] . "</td>";
    echo "<td>" . ($row['country'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['end_date'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['age_min'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>" . ($row['days_until_end'] ?? 'N/A') . "</td>";
    echo "<td><strong>" . $type . "</strong></td>";
    echo "<td style='color: " . ($willAppear == 'YES' ? 'green' : 'red') . "'><strong>$willAppear</strong><br><small>$reasonText</small></td>";
    echo "</tr>";
}

echo "</table>";

// 2. Check what the API would return
echo "<h2>2. Events That Match Home Page Query (Same as API)</h2>";
$getlimit = "$offset,$limit";
$query2 = $db->query("
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
        DATEDIFF(end_date, NOW()) as days_until_end
    FROM tb_post a 
    WHERE a.status >= '$status' 
    AND a.end_date > DATE_ADD(NOW(), INTERVAL -30 DAY) 
    AND a.country = '$testCountry' 
    ORDER BY a.id_post DESC, a.total_like DESC, a.total_comment DESC, a.title ASC 
    LIMIT $getlimit
");

$apiResults = $query2->getResultArray();
echo "<p><strong>Query returned " . count($apiResults) . " results</strong></p>";

if (count($apiResults) > 0) {
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
            <th>Type</th>
          </tr>";

    foreach ($apiResults as $row) {
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
        echo "<td><strong>" . $type . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red; font-weight: bold;'>NO EVENTS FOUND!</p>";
    echo "<p>Possible reasons:</p>";
    echo "<ul>";
    echo "<li>No events exist with status >= $status</li>";
    echo "<li>No events with country = '$testCountry'</li>";
    echo "<li>All events have end_date < NOW() - 30 days</li>";
    echo "</ul>";
}

// 3. Check events by country
echo "<h2>3. Events Count by Country</h2>";
$query3 = $db->query("
    SELECT 
        country,
        COUNT(*) as event_count,
        SUM(CASE WHEN age_min != -1 AND age_min IS NOT NULL THEN 1 ELSE 0 END) as actual_events,
        SUM(CASE WHEN age_min = -1 OR age_min IS NULL THEN 1 ELSE 0 END) as posts
    FROM tb_post 
    WHERE status >= 1
    AND end_date > DATE_ADD(NOW(), INTERVAL -30 DAY)
    GROUP BY country
    ORDER BY event_count DESC
");

$countryCounts = $query3->getResultArray();
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Country</th><th>Total</th><th>Events</th><th>Posts</th></tr>";
foreach ($countryCounts as $row) {
    $highlight = ($row['country'] == $testCountry) ? ' style="background-color: yellow;"' : '';
    echo "<tr$highlight>";
    echo "<td>" . ($row['country'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['event_count'] . "</td>";
    echo "<td>" . $row['actual_events'] . "</td>";
    echo "<td>" . $row['posts'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Summary
echo "<h2>4. Summary & Recommendations</h2>";
$totalEvents = 0;
foreach ($apiResults as $row) {
    if ($row['age_min'] != -1 && $row['age_min'] !== null) {
        $totalEvents++;
    }
}

echo "<p><strong>Events that should appear on home page for country '$testCountry': $totalEvents</strong></p>";

if ($totalEvents == 0) {
    echo "<div style='background-color: #ffe6e6; padding: 15px; border: 2px solid red;'>";
    echo "<h3>ISSUE IDENTIFIED: No events match the criteria!</h3>";
    echo "<p>Your newly created events are likely not appearing because:</p>";
    echo "<ol>";
    echo "<li><strong>Country Mismatch:</strong> Check if your events have country = '$testCountry'</li>";
    echo "<li><strong>Age Min Not Set:</strong> Events must have age_min != -1</li>";
    echo "<li><strong>End Date Too Old:</strong> Events must have end_date > (NOW() - 30 days)</li>";
    echo "<li><strong>Status Issue:</strong> Events must have status >= 1</li>";
    echo "</ol>";
    echo "<p><strong>Action:</strong> Create a new event and ensure these fields are set correctly.</p>";
    echo "</div>";
} else {
    echo "<div style='background-color: #e6ffe6; padding: 15px; border: 2px solid green;'>";
    echo "<h3>Events found!</h3>";
    echo "<p>The API is returning $totalEvents events. If they're not showing in the app, the issue is on the Flutter side (filtering).</p>";
    echo "</div>";
}
?>
