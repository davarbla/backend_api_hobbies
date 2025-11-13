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

// Get table structure
$result = $conn->query("SHOW COLUMNS FROM `tb_category`");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

echo "Table columns: " . implode(", ", $columns) . "\n";

// Truncate table
$conn->query("TRUNCATE TABLE `tb_category`");
echo "Table truncated\n";

// Read CSV and import
$handle = fopen($csvFile, "r");
if ($handle) {
    // Skip header
    fgetcsv($handle, 0, ";");
    
    $imported = 0;
    $errors = 0;
    
    while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
        // Build INSERT query dynamically based on available columns
        $insertData = [];
        $insertColumns = [];
        $insertValues = [];
        $types = '';
        $params = [];
        
        // Map CSV data to table columns
        $csvColumns = ['id_category', 'title', 'description', 'image', 'total_interest', 'total_post', 
                       'total_like', 'total_trivia', 'flag', 'status', 'date_created', 'date_updated'];
        
        foreach ($csvColumns as $index => $col) {
            if (in_array($col, $columns) && isset($data[$index])) {
                $insertColumns[] = $col;
                $insertValues[] = '?';
                
                if (in_array($col, ['total_interest', 'total_post', 'total_like', 'total_trivia', 'flag', 'status'])) {
                    $types .= 'i';
                    $params[] = (int)$data[$index];
                } else {
                    $types .= 's';
                    $params[] = $data[$index];
                }
            }
        }
        
        // Add country if it exists
        if (in_array('country', $columns) && isset($data[22])) {
            $insertColumns[] = 'country';
            $insertValues[] = '?';
            $types .= 's';
            $params[] = $data[22];
        }
        
        if (!empty($insertColumns)) {
            $sql = "INSERT INTO `tb_category` (`" . implode("`, `", $insertColumns) . "`) VALUES (" . implode(", ", $insertValues) . ")";
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $bindParams = array_merge([$types], $params);
                call_user_func_array([$stmt, 'bind_param'], makeReferences($bindParams));
                
                if ($stmt->execute()) {
                    $imported++;
                } else {
                    $errors++;
                }
                $stmt->close();
            }
        }
        
        if ($imported % 100 === 0 && $imported > 0) {
            echo "Imported $imported records...\n";
        }
    }
    
    fclose($handle);
    
    echo "\nImport completed:\n";
    echo "- Successfully imported: $imported records\n";
    echo "- Failed imports: $errors\n";
    
    // Verify
    $result = $conn->query("SELECT COUNT(*) as total FROM `tb_category`");
    $row = $result->fetch_assoc();
    echo "- Total records in table: {$row['total']}\n";
    
    // Show sample
    $result = $conn->query("SELECT id_category, title FROM `tb_category` LIMIT 5");
    echo "\nSample categories:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['id_category']}: {$row['title']}\n";
    }
}

$conn->close();

function makeReferences($arr) {
    $refs = [];
    foreach ($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}
?>
