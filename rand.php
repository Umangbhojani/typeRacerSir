<?php 

    require 'db.php';

    try {
            $email = "umang1234@gmail.com";
            $stmt = $pdo->prepare("UPDATE writers SET email_verified = 1 WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $run = $stmt->execute();
        
            if ($run) {
                $success = "OTP verified successfully! You can now proceed.";
                // header("Location: dashboard.php"); // Redirect after verification
               echo $success;
                exit; // Always exit after a header redirect
            } else {
                echo "Error: data cannot be updated in the database.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
?>