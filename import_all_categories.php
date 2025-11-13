<?php
// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'hobbies';

// Create connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully\n\n";

// Files to import with their country codes
$filesToImport = [
    'tb_category_ZZ_1000.csv' => 'ZZ',
    'tb_category_ES_3000.csv' => 'ES',
    'tb_category_FR.csv' => 'FR',
    'tb_category_US_2000.csv' => 'US'
];

// Get table structure
$result = $conn->query("SHOW COLUMNS FROM `tb_category`");
$tableColumns = [];
while ($row = $result->fetch_assoc()) {
    $tableColumns[] = $row['Field'];
}

echo "Table columns: " . implode(", ", $tableColumns) . "\n\n";

// Truncate table first
$conn->query("TRUNCATE TABLE `tb_category`");
echo "Table truncated\n\n";

$totalImported = 0;
$totalErrors = 0;

foreach ($filesToImport as $filename => $countryCode) {
    echo "Importing $filename (Country: $countryCode)...\n";
    echo str_repeat("-", 50) . "\n";
    
    $csvFile = __DIR__ . '/root/mysql/scripts/' . $filename;
    
    if (!file_exists($csvFile)) {
        echo "File not found: $filename\n\n";
        continue;
    }
    
    // Read CSV header to map columns
    $handle = fopen($csvFile, "r");
    if (!$handle) {
        echo "Error opening file: $filename\n\n";
        continue;
    }
    
    $csvHeader = fgetcsv($handle, 0, ";");
    $csvColumns = array_map('trim', $csvHeader);
    
    // Find matching columns between CSV and table
    $mappedColumns = array_intersect($tableColumns, $csvColumns);
    $mappedColumns = array_values($mappedColumns);
    
    echo "Mapped columns: " . implode(", ", $mappedColumns) . "\n";
    
    $imported = 0;
    $errors = 0;
    
    // Read and import data
    while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
        // Build INSERT query
        $insertData = [];
        $insertColumns = [];
        $insertValues = [];
        $types = '';
        $params = [];
        
        // Map CSV data to table columns
        $csvColumns = ['id_category', 'title', 'description', 'image', 'total_interest', 'total_post', 
                       'total_like', 'total_trivia', 'flag', 'status', 'date_created', 'date_updated'];
        
        foreach ($csvColumns as $index => $col) {
            if (in_array($col, $tableColumns) && isset($data[$index])) {
                $insertColumns[] = $col;
                $insertValues[] = '?';
                
                if (in_array($col, ['total_interest', 'total_post', 'total_like', 'total_trivia', 'flag', 'status', 
                                  'id_category_up', 'private', 'group', 'id_owner', 'fun'])) {
                    $types .= 'i';
                    $params[] = (int)$data[$index];
                } elseif (in_array($col, ['lat', 'lng'])) {
                    $types .= 'd';
                    $params[] = (float)$data[$index];
                } else {
                    $types .= 's';
                    $params[] = $data[$index];
                }
            }
        }
        
        // Add country code
        if (in_array('country', $tableColumns)) {
            $insertColumns[] = 'country';
            $insertValues[] = '?';
            $types .= 's';
            $params[] = $countryCode;
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
                    if ($errors < 5) { // Show first 5 errors only
                        echo "Error inserting row: " . $stmt->error . "\n";
                    }
                }
                $stmt->close();
            }
        }
        
        if ($imported % 500 === 0 && $imported > 0) {
            echo "  Imported $imported records...\n";
        }
    }
    
    fclose($handle);
    
    echo "  Completed: $imported imported, $errors errors\n\n";
    $totalImported += $imported;
    $totalErrors += $errors;
}

// Final verification
echo "=== IMPORT SUMMARY ===\n";
echo "Total imported: $totalImported records\n";
echo "Total errors: $totalErrors\n\n";

$result = $conn->query("SELECT COUNT(*) as total FROM `tb_category`");
$row = $result->fetch_assoc();
echo "Total records in table: {$row['total']}\n\n";

// Show sample by country
$result = $conn->query("SELECT country, COUNT(*) as count FROM `tb_category` GROUP BY country ORDER BY country");
echo "Categories by country:\n";
while ($row = $result->fetch_assoc()) {
    echo "- {$row['country']}: {$row['count']} categories\n";
}

echo "\nSample categories:\n";
$result = $conn->query("SELECT id_category, title, country FROM `tb_category` ORDER BY country, id_category LIMIT 10");
while ($row = $result->fetch_assoc()) {
    echo "- {$row['id_category']}: {$row['title']} ({$row['country']})\n";
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
