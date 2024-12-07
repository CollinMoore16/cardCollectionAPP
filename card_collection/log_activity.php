<?php

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self'; frame-ancestors 'none';");

require_once 'db_connection.php';

function log_activity($user_id, $activity) {
    global $pdo; 
    if ($user_id && $activity) {
        $sql = "INSERT INTO activity_log (user_id, activity) VALUES (:user_id, :activity)";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':user_id' => $user_id,
                ':activity' => $activity
            ]);
            return "Activity logged successfully.";
        } catch (PDOException $e) {
            error_log("Error in log_activity.php: " . $e->getMessage());
            return "Error: Unable to log activity.";
        }
    }
}
