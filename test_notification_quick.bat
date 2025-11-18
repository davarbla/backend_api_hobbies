@echo off
echo ==========================================
echo NOTIFICATION SYSTEM QUICK TEST
echo ==========================================
echo.

echo [1/3] Testing notification send script...
php test_send_notification.php
echo.
echo.

echo [2/3] Checking database for users with FCM tokens...
echo.
php -r "
$mysqli = new mysqli('localhost', 'root', '', 'hobbiesapp');
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

$result = $mysqli->query(\"
    SELECT 
        u.fullname,
        u.email,
        CASE 
            WHEN i.token_fcm IS NULL THEN '❌ NULL'
            WHEN i.token_fcm = '' THEN '❌ EMPTY'
            WHEN LENGTH(i.token_fcm) < 100 THEN '⚠️ TOO SHORT'
            ELSE '✅ VALID'
        END as token_status
    FROM tb_user u
    LEFT JOIN tb_install i ON u.id_install = i.id_install
    WHERE u.status = '1'
    ORDER BY i.date_updated DESC
    LIMIT 10
\");

if ($result) {
    echo \"\\nTop 10 Users Token Status:\\n\";
    echo \"----------------------------------------\\n\";
    while ($row = $result->fetch_assoc()) {
        printf(\"%-30s %-30s %s\\n\", $row['fullname'], $row['email'], $row['token_status']);
    }
} else {
    echo 'Query failed: ' . $mysqli->error;
}
$mysqli->close();
"

echo.
echo.
echo [3/3] Test complete!
echo.
echo ==========================================
echo NEXT STEPS:
echo ==========================================
echo 1. Check if users have valid tokens (✅ VALID)
echo 2. If tokens are NULL, users need to update/reinstall app
echo 3. Check PHP error logs for FCM responses
echo 4. Test notification from Flutter app
echo ==========================================
pause
