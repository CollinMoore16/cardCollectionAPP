<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
}

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: frame-ancestors 'none';");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self';");

require_once 'db_connection.php';

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("You must be logged in to insert a card.");
    }

    $user_id = $_SESSION['user_id']; 
    $name = sanitize_input($_POST['name']);
    $type = sanitize_input($_POST['type']);
    $qty = (int)$_POST['qty']; 
    $holo = isset($_POST['holo']) ? 1 : 0;
    $reverse_holo = isset($_POST['reverse_holo']) ? 1 : 0;
    $shadow = isset($_POST['shadow']) ? 1 : 0;
    $shadowless = isset($_POST['shadowless']) ? 1 : 0;

    $upload_dir = 'uploads/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_file_size = 2 * 1024 * 1024; // 2 MB
    $error = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];

        if ($image['size'] > $max_file_size) {
            $error = "File size must not exceed 2 MB.";
        } else {
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $image['tmp_name']);
            finfo_close($file_info);

            if (!in_array($mime_type, $allowed_types)) {
                $error = "Only JPG, PNG, and GIF files are allowed.";
            } else {
                $file_ext = pathinfo($image['name'], PATHINFO_EXTENSION);
                $unique_name = uniqid('img_', true) . '.' . $file_ext;
                $target_path = $upload_dir . $unique_name;

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                if (move_uploaded_file($image['tmp_name'], $target_path)) {
                    try {
                        $query = "INSERT INTO pokemon_cards (name, type, qty, holo, reverse_holo, shadow, shadowless, user_id, image_path) 
                                  VALUES (:name, :type, :qty, :holo, :reverse_holo, :shadow, :shadowless, :user_id, :image_path)";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute([
                            ':name' => $name,
                            ':type' => $type,
                            ':qty' => $qty,
                            ':holo' => $holo,
                            ':reverse_holo' => $reverse_holo,
                            ':shadow' => $shadow,
                            ':shadowless' => $shadowless,
                            ':user_id' => $user_id,
                            ':image_path' => $target_path
                        ]);

                        echo "Card inserted successfully!";
                    } catch (PDOException $e) {
                        $error = "Error inserting card: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                    }
                } else {
                    $error = "Failed to upload the image.";
                }
            }
        }
    } else {
        $error = "Please select an image to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Card</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Insert a New Card</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form action="insert_card.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label>Name: <input type="text" name="name" required></label><br>
        <label>Type: <input type="text" name="type" required></label><br>
        <label>Quantity: <input type="number" name="qty" required></label><br>
        <label>Holo: <input type="checkbox" name="holo"></label><br>
        <label>Reverse Holo: <input type="checkbox" name="reverse_holo"></label><br>
        <label>Shadow: <input type="checkbox" name="shadow"></label><br>
        <label>Shadowless: <input type="checkbox" name="shadowless"></label><br>
        <label>Image: <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif" required></label><br>
        <button type="submit">Insert Card</button>
    </form>
    <a href="index.php">HOME</a>
</body>
</html>
