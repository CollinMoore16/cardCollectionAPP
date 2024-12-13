<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self'; frame-ancestors 'none';");

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
    <a href="logout.php">Logout</a>
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
    <a href="all_cards.php" class="button">View all cards here!</a>
    <h2>Welcome to Card Management</h2>
    <div class="navigation">
        <a href="insert_card.php">Add a New Card</a>
        <a href="delete_card.php">Delete Card</a>
    </div>

    <h2>Chat with the Bot</h2>
    <div class="chat-container">
        <div id="chat" class="chat-box">
            <p><em>Welcome! Type a message to start chatting with the bot.</em></p>
        </div>
        <form id="chat-form" class="input-area">
            <input type="text" id="user-input" name="user_input" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
        document.getElementById('chat-form').addEventListener('submit', async function(event) {
            event.preventDefault();

            const userInput = document.getElementById('user-input').value.trim();
            if (!userInput) {
                alert('Please enter a valid message!');
                return;
            }

            const chatBox = document.getElementById('chat');
            chatBox.innerHTML += `<p><strong>You:</strong> ${userInput}</p>`;

            const loadingMessage = document.createElement('p');
            loadingMessage.textContent = 'Bot is typing...';
            loadingMessage.id = 'loading';
            chatBox.appendChild(loadingMessage);

            try {
                const response = await fetch('chatbot.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ user_input: userInput })
                });
                const result = await response.json();
                document.getElementById('loading').remove();

                const botMessage = document.createElement('p');
                botMessage.innerHTML = `<strong>Bot:</strong> ${result.message}`;
                chatBox.appendChild(botMessage);
            } catch (error) {
                document.getElementById('loading').remove();
                chatBox.innerHTML += `<p style="color:red;">Error: Unable to fetch a response.</p>`;
            }

            document.getElementById('user-input').value = ''; 
            chatBox.scrollTop = chatBox.scrollHeight; 
        });
    </script>
</body>
</html>
