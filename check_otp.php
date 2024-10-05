<?php
session_start();

require 'db.php';

$error = '';
$success = '';

// Check if the session variables for OTP and email are set
if (!isset($_SESSION['otp']) || !isset($_SESSION['email'])) {
    header("Location: signup.php");
    exit;
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otpInput = filter_input(INPUT_POST, 'otp', FILTER_SANITIZE_NUMBER_INT);
   
    if ($_SESSION['otp'] == $otpInput) {
        $email = $_SESSION['email'];
       
        try {
            $stmt = $pdo->prepare("UPDATE writers SET email_verified = 1 WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $run = $stmt->execute();
        
            if ($run) {
                $success = "OTP verified successfully! You can now proceed.";
                echo $success;
                unset($_SESSION['otp']);
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Error: Unable to update the database. Please try again.";
            }
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            $error = "An unexpected error occurred. Please try again later.";
        }
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>