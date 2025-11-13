<?php
$logDir = __DIR__ . '/writable/logs/';
$logFiles = glob($logDir . 'log-*.php');

if (empty($logFiles)) {
    die("No log files found in " . $logDir . "\n");
}

// Sort by modification time, newest first
usort($logFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

// Get the most recent log file
$latestLog = $logFiles[0];

echo "Checking log file: " . basename($latestLog) . "\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime($latestLog)) . "\n\n";

// Read the last 50 lines of the log file
$logContent = `tail -n 50 "$latestLog"`;
echo $logContent;

// Also check for error logs
$errorLog = __DIR__ . '/writable/logs/error.log';
if (file_exists($errorLog)) {
    echo "\nError log contents:\n";
    $errorContent = `tail -n 20 "$errorLog"`;
    echo $errorContent;
} else {
    echo "\nNo error.log file found.\n";
}
