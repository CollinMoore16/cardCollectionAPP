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

require_once 'db_connection.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Delete Card by Name</title>
</head>
<body>
    <h1>Delete a Card</h1>
    <form action="delete_card.php" method="POST">
        <label for="card_name">Enter Card Name to Delete:</label><br>
        <input type="text" id="card_name" name="card_name" required><br><br>
        <button type="submit">Delete Card</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
                    } else {
                        echo "<p>Error: No card found with the name '$card_name'.</p>";
                    }
                } else {
                    throw new Exception("Execution failed.");
                }
            } catch (Exception $e) {
                error_log("Error in delete_card.php: " . $e->getMessage());

                echo "<p>Error: " . $e->getMessage() . "</p>";
            }
        }
    }
    ?>

    <a href="index.php" class="button">Go Back to Home</a>
</body>
</html>