<?php
$mysqli = new mysqli('localhost', 'root', '', 'hobbies');

echo "Users by country in database:\n";
echo "============================\n";
$result = $mysqli->query("SELECT country, COUNT(*) as count FROM tb_user WHERE status='1' GROUP BY country");

while ($row = $result->fetch_assoc()) {
    echo "  Country '{$row['country']}': {$row['count']} users\n";
}

echo "\nChecking for 'ZZ' country:\n";
$result = $mysqli->query("SELECT COUNT(*) as count FROM tb_user WHERE status='1' AND country='ZZ'");
$row = $result->fetch_assoc();
echo "  Users with country 'ZZ': {$row['count']}\n";

$mysqli->close();
