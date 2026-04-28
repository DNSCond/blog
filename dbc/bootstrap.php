<?php
ini_set('display_errors', '1');
require_once "{$_SERVER['DOCUMENT_ROOT']}/blog/helpers.php";
$pdo = getPDO();
echo 'nonce: ' . ('nonce-' . base64_encode(random_bytes(16))) . ';';
$stmt = $pdo->prepare('INSERT INTO antaccounts (username, password, createdAt, ownedBy) VALUES (:username, :password, :createdAT, null);');
$stmt->execute([':username' => 'car', ':createdAT' => gmdate('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
    ':password' => password_hash('', PASSWORD_DEFAULT)]);
echo 'success';
