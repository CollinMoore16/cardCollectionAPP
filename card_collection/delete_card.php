<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
}

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self'; frame-ancestors 'none';");

require_once 'db_connection.php';
require_once 'log_activity.php';

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $card_name = sanitize_input($_POST['card_name']);

    if (empty($card_name)) {
        echo "<p>Error: Card name cannot be empty.</p>";
    } else {
        try {
            $sql = "DELETE FROM pokemon_cards WHERE name = :name";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $card_name, PDO::PARAM_STR);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    echo "<p>Card '$card_name' deleted successfully.</p>";

                    $log_message = log_activity($user_id, "Deleted card: $card_name");
                    echo $log_message;
                } else {
                    echo "<p>Error: No card found with the name '$card_name'.</p>";
                }
            } else {
                throw new Exception("Execution failed.");
            }
        } catch (Exception $e) {
            error_log("Error in delete_card.php: " . $e->getMessage());
            echo "<p>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Delete Card</title>
</head>
<body>
    <h1>Delete a Card</h1>
    <form action="delete_card.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label for="card_name">Enter Card Name to Delete:</label><br>
        <input type="text" id="card_name" name="card_name" required><br><br>
        <button type="submit">Delete Card</button>
    </form>

    <a href="index.php" class="button">Go Back to Home</a>
</body>
</html>
