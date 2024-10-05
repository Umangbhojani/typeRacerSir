<?php
session_start();
require 'db.php';

// Function to prevent XSS
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST['email']);

    if (empty($email)) {
        $error = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if the email exists in the database
        $stmt = $pdo->prepare("SELECT * FROM writers WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate a token and expiration date (e.g., 1 hour)
            $token = bin2hex(random_bytes(50));
            $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Insert reset token into the database
            $stmt = $pdo->prepare("UPDATE writers SET reset_token = :token, reset_expires = :expires_at WHERE email = :email");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expires_at);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Send the reset link to the user's email
            $resetLink = $project_url."reset_password.php?token=" . $token;
            $subject = "Password Reset Request";
            $message = "Click the following link to reset your password: <a href='$resetLink'>Reset Password</a>";

            require 'sendMail.php';
            $sendMail = sendMail($email, $subject, $message);

            if ($sendMail) {
                $success = "Password reset link has been sent to your email.";
            } else {
                $error = "Failed to send the email.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Request Password Reset</title>
    <style>
        input {
            border: 2px solid #007bff;
        }
        input:valid {
            border: 2px solid #38c172;
        }
        .alert {
            margin-bottom: 1rem; /* Space between alerts */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center">Request Password Reset</h2>
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
                    <form method="POST" id="resetForm" data-parsley-validate>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email" required
                                data-parsley-required-message="Email is required."
                                data-parsley-trigger="keyup"
                                data-parsley-type="email"
                                data-parsley-type-message="Please enter a valid email address.">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                    </form>
                    <p class="mt-3 text-center"><a href="login.php">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/parsley.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Parsley for form validation
    var $form = $('#resetForm').parsley();

    // Handle form submission
    $('#resetForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        $('#resetForm')[0].submit(); // Submit the form if validations pass
    });
});
</script>

</body>
</html>
