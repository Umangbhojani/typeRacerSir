<?php
session_start();
require 'db.php';

// Function to prevent XSS
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Get token from URL
if (isset($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);

    // Check if token exists and is still valid
    $stmt = $pdo->prepare("SELECT * FROM writers WHERE reset_token = :token AND reset_expires > NOW()");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = sanitizeInput($_POST['password']);
            $verifypassword = sanitizeInput($_POST['verifypassword']);

            if (empty($password) || empty($verifypassword)) {
                $error = "All fields are required.";
            } elseif ($password !== $verifypassword) {
                $error = "Passwords do not match.";
            } else {
                // Hash the new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update the user's password
                $stmt = $pdo->prepare("UPDATE writers SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':id', $user['id']);
                $stmt->execute();

                $success = "Your password has been reset. You can now <a href='login.php'>login</a>.";
            }
        }
    } else {
        $error = "Invalid or expired token.";
    }
} else {
    $error = "No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Reset Password</title>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center">Reset Password</h2>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?= $error ?>
                        </div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?= $success ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($success)): ?>
                        <form method="POST" id="resetPasswordForm" data-parsley-validate>
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" class="form-control" name="password" id="password" required
                                    data-parsley-required-message="Password is required."
                                    data-parsley-minlength="6"
                                    data-parsley-minlength-message="Password must be at least 6 characters long.">
                            </div>
                            <div class="form-group">
                                <label for="verifypassword">Verify Password</label>
                                <input type="password" class="form-control" name="verifypassword" id="verifypassword" required
                                    data-parsley-required-message="Please verify your password."
                                    data-parsley-equalto="#password"
                                    data-parsley-equalto-message="Passwords do not match.">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/parsley.min.js"></script>
</body>
</html>
