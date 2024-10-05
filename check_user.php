<?php

require 'db.php';

// // CORS headers
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: POST");
// header("Access-Control-Allow-Headers: Content-Type");

$response = ['valid' => true]; // Set default valid status

// Check if username is provided
if (isset($_POST['username'])) { // Change GET to POST
    $username = $_POST['username'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM writers WHERE Username = ?");
    $stmt->execute([$username]);
    $usernameExists = $stmt->fetchColumn();

    if ($usernameExists) {
        $response['valid'] = false; // Set valid to false if the username exists
        $response['message'] = 'Username already exists.'; // Error message for username
    }
}

// Check if email is provided
if (isset($_POST['email'])) { // Change GET to POST
    $email = $_POST['email'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM writers WHERE email = ?");
    $stmt->execute([$email]);
    $emailExists = $stmt->fetchColumn();

    if ($emailExists) {
        $response['valid'] = false; // Set valid to false if the email exists
        $response['message'] = 'Email already exists.'; // Error message for email
    }
}

echo json_encode($response);

// Remove the following lines as they are not needed in this file
// Include Parsley.js for validation
// echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>';
// Add AJAX validation for username and email
?>