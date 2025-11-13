<?php
// Path to the CodeIgniter log file
$logFile = __DIR__ . '/root/writable/logs/log-' . date('Y-m-d') . '.log';

// Check if the log file exists
if (file_exists($logFile)) {
    // Read and display the last 50 lines of the log file
    $logContent = `tail -n 50 "$logFile"`;
    echo "<pre>" . htmlspecialchars($logContent) . "</pre>";
} else {
    echo "Log file not found: " . htmlspecialchars($logFile) . "<br>";
    echo "Current directory: " . __DIR__ . "<br>";
    echo "Log directory contents:<br>";
    $files = glob(__DIR__ . '/root/writable/logs/*');
    foreach ($files as $file) {
        echo htmlspecialchars(basename($file)) . "<br>";
    }
}
?>
