@echo off
echo Checking users in database...
"C:\xampp\php\php.exe" -r "
try {
    $db = new PDO('mysql:host=localhost;dbname=hobbies;charset=utf8mb4', 'root', '');
    $stmt = $db->query('SELECT id_user, email, fullname, status FROM tb_user');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'Total users: ' . count($users) . "\n";
    if (count($users) > 0) {
        echo 'Users in tb_user table:\n';
        print_r($users);
    } else {
        echo 'No users found in tb_user table.\n';
    }
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage() . "\n";
}
"
pause
