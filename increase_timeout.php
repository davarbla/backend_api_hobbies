<?php
// Script to increase PHP timeout and memory limits for uploads
echo "Current PHP settings:\n";
echo "====================\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";

// Set higher limits for testing
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);
ini_set('memory_limit', '256M');
ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');

echo "\nUpdated PHP settings:\n";
echo "====================\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
?>
