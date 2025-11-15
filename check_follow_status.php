<?php
$pdo = new PDO('mysql:host=localhost;dbname=hobbies', 'root', '');

echo "User 30 followings in tb_follow:\n";
$stmt = $pdo->prepare('SELECT id_follow, id_user_to, status, counter_follow, (SELECT fullname FROM tb_user WHERE id_user = tb_follow.id_user_to) as name FROM tb_follow WHERE id_user = 30 AND flag = 1 ORDER BY id_follow DESC LIMIT 5');
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $r) {
    echo "  ID: {$r['id_follow']}, To: {$r['id_user_to']} ({$r['name']}), Status: {$r['status']}, Counter: {$r['counter_follow']}\n";
}

echo "\nUser 30 total_following: ";
$stmt = $pdo->prepare('SELECT total_following FROM tb_user WHERE id_user = 30');
$stmt->execute();
echo $stmt->fetchColumn() . "\n";
