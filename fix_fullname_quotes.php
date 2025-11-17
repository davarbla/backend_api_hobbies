<?php
// Fix fullname field with embedded quotes that break JSON encoding

// Database connection
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'hobbies';

$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully.\n\n";

// Find users with quotes in fullname
$sql = "SELECT id_user, fullname, username FROM tb_user WHERE fullname LIKE '%\"%' OR username LIKE '%\"%'";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$users = $result->fetch_all(MYSQLI_ASSOC);
echo "Found " . count($users) . " users with quotes in fullname/username:\n\n";

foreach ($users as $user) {
    echo "User ID: {$user['id_user']}\n";
    echo "  Fullname: {$user['fullname']}\n";
    echo "  Username: {$user['username']}\n";
    
    // Remove quotes from fullname and username
    $cleanFullname = str_replace('"', '', $user['fullname']);
    $cleanUsername = str_replace('"', '', $user['username']);
    
    // Update the database using prepared statement
    $stmt = $conn->prepare("UPDATE tb_user SET fullname = ?, username = ? WHERE id_user = ?");
    $stmt->bind_param("ssi", $cleanFullname, $cleanUsername, $user['id_user']);
    
    if ($stmt->execute()) {
        echo "  -> Fixed fullname to: $cleanFullname\n";
        echo "  -> Fixed username to: $cleanUsername\n\n";
    } else {
        echo "  -> Error updating: " . $stmt->error . "\n\n";
    }
    
    $stmt->close();
}

$conn->close();
echo "Done! Fixed " . count($users) . " users.\n";
