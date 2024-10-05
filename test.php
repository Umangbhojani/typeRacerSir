<?php
require 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $verifypassword = $_POST['verifypassword'];
    $role = 0; // Assuming a default role of 'writer'

    // Input validation
    if (empty($username) || empty($email) || empty($password) || empty($verifypassword)) {
        $error = "All fields are required.";
    } elseif ($password !== $verifypassword) {
        $error = "Passwords do not match.";
    } else {
        // Check for existing username
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM writers WHERE Username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username already exists.";
        } else {
            // Check for existing email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM writers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Email already exists.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into the database
                $stmt = $pdo->prepare("INSERT INTO writers (Username, email, password, role,verifypassword ) VALUES (?, ?, ?, ?,?)"); // Removed verifypassword
                if ($stmt->execute([$username, $email, $hashedPassword, $role, $verifypassword])) {
                    $success = "Registration successful. You can log in now.";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <title>Signup</title>

    <style>
        input {
            border: 2px solid #007bff;
        }

        input:invalid {
            border: 2px solid #e3342f;
        }

        input:valid {
            border: 2px solid #38c172;
        }

        .parsley-error {
            border: 2px solid #e3342f;
        }

        .parsley-success {
            border: 2px solid #38c172;
        }

        .error-message {
            color: #e3342f;
            font-size: 0.9em;
            margin-top: 0.25rem;
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
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <form id="signupForm" method="POST" data-parsley-validate>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required
                                   data-parsley-required-message="Username is required."
                                   data-parsley-remote="check_user.php"
                                   data-parsley-remote-options='{"type":"POST", "data": {"username": $(this).val()}}' 
                                   data-parsley-remote-message="Username already exists. Please choose another."
                                   data-parsley-trigger="keyup"
                                   > <!-- Validate on focus out -->
                            <div class="error-message" id="usernameMessage" data-parsley-remote-error="username"></div> <!-- Error container -->
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email" required
                                   data-parsley-required-message="Email is required."
                                   data-parsley-type-message="Please enter a valid email address."
                                   data-parsley-remote="check_user.php"
                                   data-parsley-remote-options='{"type":"POST", "data": {"email": $(this).val()}}' 
                                   data-parsley-remote-message="Email already exists. Please use another email."
                                   data-parsley-trigger="focusout"> <!-- Validate on focus out -->
                            <div class="error-message" id="emailMessage" data-parsley-remote-error="email"></div> <!-- Error container -->
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
            $errorContainer.text($input.data('parsley-remote-error'));
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

        // Check username availability
        $.post('check_user.php', { username: $('#username').val() }, function(response) {
            if (!response.valid) {
                $('#usernameMessage').text(response.message).css('color', 'red');
                return; // Exit if the username is invalid
            } else {
                $('#usernameMessage').text('Username is available.').css('color', 'green');
            }

            // Check email availability
            $.post('check_user.php', { email: $('#email').val() }, function(response) {
                if (!response.valid) {
                    $('#emailMessage').text(response.message).css('color', 'red');
                    return; // Exit if the email is invalid
                } else {
                    $('#emailMessage').text('Email is available.').css('color', 'green');
                }

                // If both checks pass, submit the form
                $('#signupForm')[0].submit();
            }, 'json');
        }, 'json');
    });

    $('#username').on('blur', function() {
        var username = $(this).val();
        $.post('check_user.php', { username: username }, function(response) {
            if (!response.valid) {
                $('#usernameMessage').text(response.message).css('color', 'red');
            } else {
                $('#usernameMessage').text('Username is available.').css('color', 'green');
            }
        }, 'json');
    });

    $('#email').on('blur', function() {
        var email = $(this).val();
        $.post('check_user.php', { email: email }, function(response) {
            if (!response.valid) {
                $('#emailMessage').text(response.message).css('color', 'red');
            } else {
                $('#emailMessage').text('Email is available.').css('color', 'green');
            }
        }, 'json');
    });
});
</script>

</body>
</html>