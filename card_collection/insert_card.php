<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? null;
    $type = $_POST['type'] ?? null;
    $qty = $_POST['qty'] ?? 1;

    $holo = isset($_POST['holo']) && $_POST['holo'] === "1" ? true : false;
    $reverse_holo = isset($_POST['reverse_holo']) && $_POST['reverse_holo'] === "1" ? true : false;
    $shadow = isset($_POST['shadow']) && $_POST['shadow'] === "1" ? true : false;
    $shadowless = isset($_POST['shadowless']) && $_POST['shadowless'] === "1" ? true : false;

    $user_id = $_SESSION['user_id'];

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $image_path = $upload_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    try {
        $query = "INSERT INTO pokemon_cards (name, type, qty, holo, reverse_holo, shadow, shadowless, image_path, user_id) 
                  VALUES (:name, :type, :qty, :holo, :reverse_holo, :shadow, :shadowless, :image_path, :user_id)";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        $stmt->bindValue(':qty', $qty, PDO::PARAM_INT);
        $stmt->bindValue(':holo', $holo, PDO::PARAM_BOOL);
        $stmt->bindValue(':reverse_holo', $reverse_holo, PDO::PARAM_BOOL);
        $stmt->bindValue(':shadow', $shadow, PDO::PARAM_BOOL);
        $stmt->bindValue(':shadowless', $shadowless, PDO::PARAM_BOOL);
        $stmt->bindValue(':image_path', $image_path, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        header('Location: index.php?action=view');
        exit;
    } catch (PDOException $e) {
        echo "Error inserting card: " . $e->getMessage();
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add New Pokémon Card</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <h1>Add a New Pokémon Card</h1>
        <form action="insert_card.php" method="post" enctype="multipart/form-data">
            <label>Name: <input type="text" name="name" required></label><br>
            <label>Type: <input type="text" name="type"></label><br>
            <label>Quantity: <input type="number" name="qty" min="1"></label><br>
            <label>Holo: <input type="checkbox" name="holo" value="1"></label><br>
            <label>Reverse Holo: <input type="checkbox" name="reverse_holo" value="1"></label><br>
            <label>Shadow: <input type="checkbox" name="shadow" value="1"></label><br>
            <label>Shadowless: <input type="checkbox" name="shadowless" value="1"></label><br>
            <label>Image: <input type="file" name="image"></label><br>
            <button type="submit">Save Card</button>
        </form>
        <a href="index.php">Back to Collection</a>
    </body>
    </html>
    <?php
}
?>
