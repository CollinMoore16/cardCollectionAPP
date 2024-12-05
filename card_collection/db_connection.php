<?php
$host = 'localhost';
$dbname = 'card_collection';
$user = 'postgres'; 
$password = '1!'; 

try {
    $pdo = new PDO('pgsql:host=localhost;dbname=card_collection', 'postgres', '1!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
