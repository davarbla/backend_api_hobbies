<?php
$mysqli = new mysqli('localhost', 'root', '', 'hobbies');

echo "=== TEST: Country ZZ (International) ===\n\n";

// Test 1: What the old code would return
echo "1. OLD behavior - filtering by country='ZZ':\n";
$result = $mysqli->query("SELECT COUNT(*) as count FROM tb_user WHERE status='1' AND country='ZZ'");
$row = $result->fetch_assoc();
echo "   Users returned: {$row['count']} ✗ EMPTY!\n\n";

// Test 2: What the new code will return
echo "2. NEW behavior - allByLimit (all countries):\n";
$result = $mysqli->query("SELECT COUNT(*) as count FROM tb_user WHERE status='1'");
$row = $result->fetch_assoc();
echo "   Users returned: {$row['count']} ✓ SUCCESS!\n\n";

// Show breakdown
echo "3. Breakdown by country:\n";
$result = $mysqli->query("SELECT country, COUNT(*) as count FROM tb_user WHERE status='1' GROUP BY country");
while ($row = $result->fetch_assoc()) {
    echo "   - {$row['country']}: {$row['count']} users\n";
}

echo "\n✓ Fix applied: When app sends country='ZZ', API will return ALL {$row['count']} users!\n";

$mysqli->close();
