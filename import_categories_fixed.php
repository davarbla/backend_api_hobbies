<?php
// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'hobbies';

// CSV file path
$csvFile = __DIR__ . '/root/mysql/scripts/tb_category_PROD.csv';

// Create connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully\n";

// Get table structure to verify columns
$result = $conn->query("DESCRIBE `tb_category`");
if (!$result) {
    die("Error describing table: " . $conn->error . "\n");
}

$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[$row['Field']] = $row['Type'];
}

echo "Table columns: " . implode(", ", array_keys($columns)) . "\n\n";

// Truncate table
$truncateSql = "TRUNCATE TABLE `tb_category`";
if ($conn->query($truncateSql) === TRUE) {
    echo "Table tb_category truncated successfully\n";
} else {
    echo "Error truncating table: " . $conn->error . "\n";
}

// Prepare insert statement with only existing columns
$sql = "INSERT INTO `tb_category` (
    `id_category`, `title`, `description`, `image`, `total_interest`, `total_post`,
    `total_like`, `total_trivia`, `flag`, `status`, `date_created`, `date_updated`,
    `private`, `group`, `latitude`, `location`, `id_owner`,
    `fun`, `subscribe_fcm`, `lat`, `lng`, `country`
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error . "\n");
}

// Open the CSV file
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Skip the header row
    fgetcsv($handle, 0, ";");
    
    $imported = 0;
    $errors = 0;
    
    // Read the file line by line
    while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
        // Map CSV columns to database fields
        $id_category = $data[0] ?? 0;
        $title = $data[1] ?? '';
        $description = $data[2] ?? '';
        $image = $data[3] ?? '';
        $total_interest = $data[4] ?? 0;
        $total_post = $data[5] ?? 0;
        $total_like = $data[6] ?? 0;
        $total_trivia = $data[7] ?? 0;
        $flag = $data[8] ?? 1;
        $status = $data[9] ?? 1;
        $date_created = $data[10] ?? date('Y-m-d H:i:s');
        $date_updated = $data[11] ?? date('Y-m-d H:i:s');
        $private = $data[13] ?? 0;
        $group = $data[14] ?? 0;
        $latitude = $data[15] ?? '';
        $location = $data[16] ?? '';
        $id_owner = $data[17] ?? 0;
        $fun = $data[18] ?? 0;
        $subscribe_fcm = $data[19] ?? '';
        $lat = $data[20] ?? 0.0;
        $lng = $data[21] ?? 0.0;
        $country = $data[22] ?? '';
        
        // Bind parameters
        $stmt->bind_param(
            "isssiiiiisssiisssissss",
            $id_category,
            $title,
            $description,
            $image,
            $total_interest,
            $total_post,
            $total_like,
            $total_trivia,
            $flag,
            $status,
            $date_created,
            $date_updated,
            $private,
            $group,
            $latitude,
            $location,
            $id_owner,
            $fun,
            $subscribe_fcm,
            $lat,
            $lng,
            $country
        );
        
        // Execute the statement
        if ($stmt->execute()) {
            $imported++;
        } else {
            $errors++;
            echo "Error importing row: " . $stmt->error . "\n";
        }
        
        // Show progress
        if ($imported % 50 === 0) {
            echo "Imported $imported records...\n";
        }
    }
    
    fclose($handle);
    
    echo "\nImport completed.\n";
    echo "Successfully imported: $imported records\n";
    echo "Failed imports: $errors\n";
    
    // Show sample of imported data
    $result = $conn->query("SELECT COUNT(*) as total FROM `tb_category`");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "\nTotal records in tb_category: " . $row['total'] . "\n";
    }
    
    // Show first 5 categories as sample
    $result = $conn->query("SELECT id_category, title, country FROM `tb_category` ORDER BY id_category DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "\nLatest 5 categories:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['id_category']}: {$row['title']} ({$row['country']})\n";
        }
    }
    
} else {
    echo "Error opening CSV file\n";
}

// Close statement and connection
$stmt->close();
$conn->close();

echo "\nScript execution completed.\n";
?>
