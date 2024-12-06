<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self';");
header("Content-Security-Policy: frame-ancestors 'none';");

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
require_once 'db_connection.php';
include 'fetch_cards.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Guest';

$all_cards = fetchAllCards($pdo, $user_id);
$random_cards = fetchRandomCards($pdo, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pokémon Cards</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>

    <h2>5 Random Pokémon Cards</h2>
    <div class="cards-container">
        <?php if (!empty($random_cards)): ?>
            <?php foreach ($random_cards as $card): ?>
                <div class="card">
                    <?php if (!empty($card['image_path'])): ?>
                        <img src="<?= htmlspecialchars($card['image_path']) ?>" alt="<?= htmlspecialchars($card['name']) ?>">
                    <?php endif; ?>
                    <p><strong><?= htmlspecialchars($card['name']) ?></strong></p>
                    <p>Type: <?= htmlspecialchars($card['type']) ?></p>
                    <p>Quantity: <?= htmlspecialchars($card['qty']) ?></p>
                    <p>Holo: <?= $card['holo'] ? 'Yes' : 'No' ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No cards available. Add some to your collection!</p>
        <?php endif; ?>
    </div>

    <h2>Your Full Pokémon Card Collection</h2>
    <br>
    <br>
    <a href="all_cards.php" class="button">View all cards here!</a>
    <a href="insert_card.php">Add a New Card</a>
    <a href="logout.php">Logout</a>
</body>
</html>
