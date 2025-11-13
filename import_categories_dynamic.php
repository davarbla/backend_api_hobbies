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
$result = $conn->query("SHOW COLUMNS FROM `tb_category`");
if (!$result) {
    die("Error describing table: " . $conn->error . "\n");
}

$tableColumns = [];
while ($row = $result->fetch_assoc()) {
    $tableColumns[] = $row['Field'];
}

echo "Table columns: " . implode(", ", $tableColumns) . "\n\n";

// Read CSV header to map columns
if (($handle = fopen($csvFile, "r")) === FALSE) {
    die("Error opening CSV file\n");
}

$csvHeader = fgetcsv($handle, 0, ";");
$csvColumns = array_map('trim', $csvHeader);
fclose($handle);

echo "CSV columns: " . implode(", ", $csvColumns) . "\n\n";

// Find matching columns between CSV and table
$mappedColumns = array_intersect($tableColumns, $csvColumns);
$mappedColumns = array_values($mappedColumns); // Re-index array

if (empty($mappedColumns)) {
    die("No matching columns found between CSV and database table\n");
}

echo "Mapped columns for import: " . implode(", ", $mappedColumns) . "\n\n";

// Truncate table
$truncateSql = "TRUNCATE TABLE `tb_category`";
if ($conn->query($truncateSql) === TRUE) {
    echo "Table tb_category truncated successfully\n";
} else {
    echo "Error truncating table: " . $conn->error . "\n";
}

// Prepare insert statement with only existing columns
$placeholders = implode(', ', array_fill(0, count($mappedColumns), '?'));
$columns = '`' . implode('`, `', $mappedColumns) . '`';
$sql = "INSERT INTO `tb_category` ($columns) VALUES ($placeholders)";

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
    $batchSize = 50;
    $batch = [];
    
    // Read the file line by line
    while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
        // Map CSV data to columns
        $rowData = [];
        foreach ($mappedColumns as $column) {
            $index = array_search($column, $csvColumns);
            $value = ($index !== false && isset($data[$index])) ? $data[$index] : '';
            
            // Convert empty strings to appropriate defaults based on column type
            if ($value === '' && in_array($column, ['total_interest', 'total_post', 'total_like', 'total_trivia', 'flag', 'status'])) {
                $value = 0;
            } elseif ($value === '' && in_array($column, ['date_created', 'date_updated'])) {
                $value = date('Y-m-d H:i:s');
            }
            
            $rowData[] = $value;
        }
        
        $batch[] = $rowData;
        
        // Execute batch
        if (count($batch) >= $batchSize) {
            if (importBatch($conn, $sql, $batch, $mappedColumns)) {
                $imported += count($batch);
                echo "Imported $imported records...\n";
            } else {
                $errors += count($batch);
                echo "Error importing batch. Continuing...\n";
            }
            $batch = [];
        }
    }
    
    // Import remaining records in the last batch
    if (!empty($batch)) {
        if (importBatch($conn, $sql, $batch, $mappedColumns)) {
            $imported += count($batch);
        } else {
            $errors += count($batch);
        }
    }
    
    fclose($handle);
    
    echo "\nImport completed.\n";
    echo "Successfully imported: $imported records\n";
    echo "Failed imports: $errors\n";
    
    // Show summary
    $result = $conn->query("SELECT COUNT(*) as total FROM `tb_category`");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "\nTotal records in tb_category: " . $row['total'] . "\n";
    }
    
    // Show sample data
    $result = $conn->query("SELECT id_category, title, country FROM `tb_category` ORDER BY id_category DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "\nLatest 5 categories:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['id_category']}: {$row['title']} ({$row['country'] ?? 'N/A'})\n";
        }
    }
    
} else {
    echo "Error opening CSV file\n";
}

$conn->close();
echo "\nScript execution completed.\n";

/**
 * Import a batch of records
 */
function importBatch($conn, $sql, $batch, $columns) {
    $conn->begin_transaction();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        $conn->rollback();
        return false;
    }
    
    $types = '';
    $params = [];
    
    // Prepare types and params
    foreach ($batch as $row) {
        $rowTypes = '';
        $rowParams = [];
        
        foreach ($row as $index => $value) {
            $colName = $columns[$index];
            
            // Determine parameter type
            if (in_array($colName, ['total_interest', 'total_post', 'total_like', 'total_trivia', 'flag', 'status'])) {
                $rowTypes .= 'i'; // integer
                $rowParams[] = (int)$value;
            } elseif (in_array($colName, ['lat', 'lng'])) {
                $rowTypes .= 'd'; // double
                $rowParams[] = (float)$value;
            } else {
                $rowTypes .= 's'; // string
                $rowParams[] = $value;
            }
        }
        
        $types .= $rowTypes;
        $params = array_merge($params, $rowParams);
    }
    
    // Flatten the params array for bind_param
    $bindParams = [&$types];
    $n = count($params);
    for ($i = 0; $i < $n; $i++) {
        $bindParams[] = &$params[$i];
    }
    
    // Bind parameters and execute
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
    
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $conn->commit();
        return true;
    } else {
        $conn->rollback();
        return false;
    }
}
?>
