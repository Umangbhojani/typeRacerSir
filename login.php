<?php
session_start();
require 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to prevent XSS
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    // Input validation
    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check for the user in the database
        $stmt = $pdo->prepare("SELECT * FROM writers WHERE Username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Valid credentials, create session
            $_SESSION['username'] = $user['Username'];
            $_SESSION['email'] = $user['email'];

            // Redirect to dashboard or main page
            header("location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/src/parsley.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <title>Login</title>
    <style>
        input {
            border: 2px solid #007bff;
        }
        input:valid {
            border: 2px solid #38c172;
        }
        .error-message {
            display: none; /* Initially hidden */
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
                    <h2 class="card-title text-center">Login</h2>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?= $error ?>
                        </div>
                    <?php endif; ?>
                    <form id="loginForm" method="POST" data-parsley-validate>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required
                                data-parsley-required-message="Username is required."
                                data-parsley-trigger="keyup">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" required
                                data-parsley-required-message="Password is required."
                                data-parsley-minlength="6"
                                data-parsley-minlength-message="Password must be at least 6 characters long.">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                    <p class="mt-3 text-center">
                        <a href="request_password_reset.php">Forgot Password?</a>
                    </p>
                    <p class="mt-3 text-center">Don't have an account? <a href="signup.php">Signup here</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Parsley for form validation
    var $form = $('form').parsley();

    // Handle form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        $('#loginForm')[0].submit(); // Submit the form if validations pass
    });
});
</script>
</body>
</html>
