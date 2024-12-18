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
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self';");
header("Content-Security-Policy: frame-ancestors 'none';");

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $query = "INSERT INTO users (username, password) VALUES (:username, :password)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashed_password,
            ]);
            $success = "Registration successful! <a href='login.php'>Login here</a>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23505) {
                $error = "Username already exists. Please choose a different username.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Register</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?= $success ?></p>
    <?php endif; ?>
    <form action="register.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label>Username: <input type="text" name="username" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <label>Confirm Password: <input type="password" name="confirm_password" required></label><br>
        <button type="submit">Register</button>
    </form>
    <a href="login.php">Already have an account? Login</a>
</body>
</html>
