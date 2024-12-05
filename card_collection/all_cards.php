<?php
require 'db_connection.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM pokemon_cards WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $user_id]);
    $all_cards = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error fetching cards: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your All Cards</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h1>Your Pokémon Card Collection</h1>
    <?php if (!empty($all_cards)): ?>
        <?php foreach ($all_cards as $card): ?>
            <div class="card">
                <p><strong>Name:</strong> <?= htmlspecialchars($card['name']) ?></p>
                <p><strong>Quantity:</strong> <?= htmlspecialchars($card['qty']) ?></p>
                <p><strong>Type:</strong> <?= htmlspecialchars($card['type']) ?></p>
                <p><strong>Holo:</strong> <?= $card['holo'] ? 'Yes' : 'No' ?></p>
                <p><strong>Reverse Holo:</strong> <?= $card['reverse_holo'] ? 'Yes' : 'No' ?></p>
                <p><strong>Shadow:</strong> <?= $card['shadow'] ? 'Yes' : 'No' ?></p>
                <p><strong>Shadowless:</strong> <?= $card['shadowless'] ? 'Yes' : 'No' ?></p>

                <?php if ($card['image_path']): ?>
                    <img src="<?= htmlspecialchars($card['image_path']) ?>" alt="Card Image" width="100">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You don't have any cards in your collection yet!</p>
    <?php endif; ?>

    <p><a href="index.php">Go back to the Main Page</a></p>

</body>
</html>
