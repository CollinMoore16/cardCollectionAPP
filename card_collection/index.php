<?php
session_start();
include 'db_connection.php';
include 'fetch_cards.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

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
    <div class="cards-container">
        <?php foreach ($all_cards as $card): ?>
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
    </div>

    <a href="insert_card.php">Add a New Card</a>
    <a href="logout.php">Logout</a>
</body>
</html>
