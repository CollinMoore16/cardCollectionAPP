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

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$cards_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $cards_per_page;

$total_cards_query = "SELECT COUNT(*) FROM pokemon_cards WHERE user_id = :user_id";
$total_cards_stmt = $pdo->prepare($total_cards_query);
$total_cards_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$total_cards_stmt->execute();
$total_cards = $total_cards_stmt->fetchColumn();

$cards_query = "SELECT * FROM pokemon_cards WHERE user_id = :user_id LIMIT :limit OFFSET :offset";
$cards_stmt = $pdo->prepare($cards_query);
$cards_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$cards_stmt->bindValue(':limit', $cards_per_page, PDO::PARAM_INT);
$cards_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$cards_stmt->execute();
$cards = $cards_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_pages = ceil($total_cards / $cards_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Pokémon Cards</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>All Pokémon Cards</h1>
    <div class="cards-container">
        <?php foreach ($cards as $card): ?>
            <div class="card">
                <?php if (!empty($card['image_path'])): ?>
                    <img src="<?= htmlspecialchars($card['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>
                <p><strong><?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                <p>Type: <?= htmlspecialchars($card['type'], ENT_QUOTES, 'UTF-8') ?></p>
                <p>Quantity: <?= (int)$card['qty'] ?></p>
                <p>Holo: <?= $card['holo'] ? 'Yes' : 'No' ?></p>
                <p>Reverse Holo: <?= $card['reverse_holo'] ? 'Yes' : 'No' ?></p>
                <p>Shadow: <?= $card['shadow'] ? 'Yes' : 'No' ?></p>
                <p>Shadowless: <?= $card['shadowless'] ? 'Yes' : 'No' ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="?page=<?= $current_page - 1 ?>" class="button">Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="button <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($current_page < $total_pages): ?>
            <a href="?page=<?= $current_page + 1 ?>" class="button">Next</a>
        <?php endif; ?>
    </div>

    <a href="index.php" class="button">Back to Home</a>
</body>
</html>
