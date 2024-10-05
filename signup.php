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
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);
    $verifypassword = sanitizeInput($_POST['verifypassword']);
    $role = 0; // Assuming a default role of 'writer'

    // Input validation
    if (empty($username) || empty($email) || empty($password) || empty($verifypassword)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $verifypassword) {
        $error = "Passwords do not match.";
    } else {
        // Check for existing username
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM writers WHERE Username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $error = "Username already exists.";
        } else {
            // Check for existing email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM writers WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                $error = "Email already exists.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into the database
                $stmt = $pdo->prepare("INSERT INTO writers (Username, email, password, role) VALUES (:username, :email, :password, :role)"); 
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':role', $role);

                if ($stmt->execute()) {
                    // Send verification email
                    require 'sendMail.php';
                    $otp = rand(100000,999999);
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['otp'] = $otp;
                    $subject = "TypeRacer - Email Verification";
                    $message = "Hey ".$username."! We are glad to have you as our user. Please verify your email for authenticity.<br />Here is your One Time Password (OTP) for that ".$otp;
                    $sendMail = sendMail($email,$subject,$message);
                    if($sendMail == true){
                        header("location:verify_email.php");
                        exit; // Prevent further execution
                    }else{
                        $error = "Whoops! Something went wrong with verification.";
                    }
                } else {
                    $error = "Registration failed.";
                }
            }
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
    <title>Signup</title>
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
                    <h2 class="card-title text-center">Signup</h2>
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
                    <form id="signupForm" method="POST" data-parsley-validate>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required
                                data-parsley-required-message="Username is required."
                                data-parsley-remote="check_user.php"
                                data-parsley-remote-options='{"type":"POST", "data": {"username": $(this).val()}}' 
                                data-parsley-remote-message="Username already exists. Please choose another."
                                data-parsley-trigger="keyup">
                            <div class="error-message" id="usernameMessage"></div> <!-- Error container -->
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email" required
                                data-parsley-required-message="Email is required."
                                data-parsley-type-message="Please enter a valid email address."
                                data-parsley-remote="check_user.php"
                                data-parsley-remote-options='{"type":"POST", "data": {"email": $(this).val()}}' 
                                data-parsley-remote-message="Email already exists. Please use another email."
                                data-parsley-trigger="focusout">
                            <div class="error-message" id="emailMessage"></div> <!-- Error container -->
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" required
                                data-parsley-required-message="Password is required."
                                data-parsley-minlength="6"
                                data-parsley-minlength-message="Password must be at least 6 characters long.">
                        </div>
                        <div class="form-group">
                            <label for="verifypassword">Verify Password</label>
                            <input type="password" class="form-control" name="verifypassword" required
                                data-parsley-required-message="Please verify your password."
                                data-parsley-equalto="[name='password']"
                                data-parsley-equalto-message="Passwords do not match.">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
                    </form>
                    <p class="mt-3 text-center">Already have an account? <a href="login.php">Login here</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Parsley for form validation
    var $form = $('form').parsley();

    // Handle error messages for remote validation
    $form.on('field:error', function() {
        var $input = $(this.$element);
        var $errorContainer = $input.siblings('.error-message');

        // Check if there are any remote validation errors
        if ($input.data('parsley-remote-error')) {
            $errorContainer.html('<div class="alert alert-danger d-flex align-items-center" role="alert">' +
                '<i class="fas fa-exclamation-circle mr-2"></i>' + $input.data('parsley-remote-error') + '</div>');
        }
    });

    $form.on('field:success', function() {
        var $input = $(this.$element);
        var $errorContainer = $input.siblings('.error-message');

        // Clear the error message on success
        $errorContainer.text('');
    });

    // Prevent form submission
    $('#signupForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Check username availability only if the input is not empty
        var username = $('#username').val();
        if (username) {
            $.post('check_user.php', { username: username }, function(response) {
                if (!response.valid) {
                    $('#usernameMessage').html('<div class="alert alert-danger d-flex align-items-center" role="alert">' +
                        '<i class="fas fa-exclamation-circle mr-2"></i>' + response.message + '</div>').show();
                    return; // Exit if username is not valid
                }
                // Proceed with form submission if all validations are successful
                $('#signupForm')[0].submit(); // Submit the form
            }, 'json');
        } else {
            // Proceed with form submission if all validations are successful
            $('#signupForm')[0].submit(); // Submit the form
        }
    });
});
</script>
</body>
</html>
