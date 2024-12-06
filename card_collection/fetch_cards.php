<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connection.php';

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self';");
header("Content-Security-Policy: frame-ancestors 'none';");

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized access.']));
}

function fetchAllCards($pdo, $user_id) {
    if (!is_int($user_id)) {
        throw new InvalidArgumentException('Invalid user ID.');
    }

    $query = "SELECT * FROM pokemon_cards WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchRandomCards($pdo, $user_id) {
    if (!is_int($user_id)) {
        throw new InvalidArgumentException('Invalid user ID.');
    }

    $query = "SELECT * FROM pokemon_cards WHERE user_id = :user_id ORDER BY RANDOM() LIMIT 5";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || 
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
    try {
        $user_id = (int) $_SESSION['user_id'];

        $cards = fetchAllCards($pdo, $user_id);
        $random_cards = fetchRandomCards($pdo, $user_id);

        header('Content-Type: application/json');
        echo json_encode([
            'cards' => $cards,
            'random_cards' => $random_cards
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

?>
