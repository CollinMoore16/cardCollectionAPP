<?php
require_once 'db_connection.php'; 

header('Content-Type: application/json'); 
$response = [
    'status' => 'error',
    'message' => "I'm sorry, I don't understand that.",
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = htmlspecialchars(trim($_POST['user_input'] ?? '')); 
    $userId = $_SESSION['user_id']; 

    if (!empty($userInput)) {
        try {
            if (stripos($userInput, 'added recently') !== false) {
                $sql = "SELECT activity, timestamp FROM activity_log 
                        WHERE user_id = :user_id AND activity LIKE 'Inserted%' 
                        ORDER BY timestamp DESC LIMIT 5";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($activities) {
                    $response['status'] = 'success';
                    $response['message'] = "Here are the cards you added recently:<br>";
                    foreach ($activities as $activity) {
                        $response['message'] .= "- " . $activity['activity'] . " at " . $activity['timestamp'] . "<br>";
                    }
                } else {
                    $response['message'] = "You haven't added any cards recently.";
                }
            }
            elseif (stripos($userInput, 'deleted recently') !== false) {
                $sql = "SELECT activity, timestamp FROM activity_log 
                        WHERE user_id = :user_id AND activity LIKE 'Deleted%' 
                        ORDER BY timestamp DESC LIMIT 5";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($activities) {
                    $response['status'] = 'success';
                    $response['message'] = "Here are the cards you deleted recently:<br>";
                    foreach ($activities as $activity) {
                        $response['message'] .= "- " . $activity['activity'] . " at " . $activity['timestamp'] . "<br>";
                    }
                } else {
                    $response['message'] = "You haven't deleted any cards recently.";
                }
            }
            else {
                $sql = "SELECT response FROM chatbot_responses WHERE keyword LIKE :keyword LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['keyword' => '%' . $userInput . '%']);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    $response['status'] = 'success';
                    $response['message'] = $result['response'];
                }
            }
        } catch (Exception $e) {
            error_log("Chatbot error: " . $e->getMessage()); 
            $response['message'] = 'An unexpected error occurred. Please try again later.';
        }
    }
}

echo json_encode($response); 
