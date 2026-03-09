<?php
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'logged_in' => true,
        'user_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '',
        'user_email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>

