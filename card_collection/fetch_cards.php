<?php
include 'db_connection.php';

function fetchAllCards($pdo, $user_id) {
    $query = "SELECT * FROM pokemon_cards WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchRandomCards($pdo, $user_id) {
    $query = "SELECT * FROM pokemon_cards WHERE user_id = :user_id ORDER BY RANDOM() LIMIT 5";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
