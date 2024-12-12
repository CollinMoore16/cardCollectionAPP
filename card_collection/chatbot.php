<?php
require_once 'db_connection.php'; 

header('Content-Type: application/json'); 
$response = [
    'status' => 'error',
    'message' => "I'm sorry, I don't understand that.",
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = htmlspecialchars(trim($_POST['user_input'] ?? ''));

    if (!empty($userInput)) {
        try {
            $sql = "SELECT response FROM chatbot_responses WHERE keyword LIKE :keyword LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['keyword' => '%' . $userInput . '%']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $response['status'] = 'success';
                $response['message'] = $result['response'];
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $response['message'] = 'An unexpected error occurred. Please try again later.';
        }
    }
}

echo json_encode($response);
