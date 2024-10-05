<?php
session_start();

// Check if the user is coming from the signup page
if (!isset($_SERVER['HTTP_REFERER']) || !strpos($_SERVER['HTTP_REFERER'], 'signup.php')) {
    header("Location: signup.php");
    exit;
}

// Check if OTP is set in session (ensure user has completed signup)
if (!isset($_SESSION['otp'])) {
    header("Location: signup.php");
    exit; 
}

// Initialize error and success messages
$error = '';
$success = '';

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

// Handle OTP verification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        $input_otp = $_POST['otp'];
        
        // Validate the OTP input
        if (empty($input_otp) || !preg_match('/^\d{6}$/', $input_otp)) {
            $error = "Please enter a valid 6-digit OTP.";
        } else {
            // Compare with the OTP stored in the session
            if ($input_otp == $_SESSION['otp']) {
                // OTP is correct, proceed with account activation
                $success = "OTP verified successfully!";
                // Here, you can implement further actions such as updating the database status
                // For example: marking the user as verified in the database
                // Unset the OTP after successful verification
                unset($_SESSION['otp']);
            } else {
                $error = "Invalid OTP. Please try again.";
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
    <title>Verify OTP</title>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center">Verify OTP</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert"><?= $error ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success" role="alert"><?= $success ?></div>
                    <?php endif; ?>
                    <form method="POST" action="check_otp.php" autocomplete="off">
                        <!-- <input type="hidden" name="csrf_token" value="<?php // htmlspecialchars($_SESSION['csrf_token']) ?>"> -->
                        <div class="form-group">
                            <label for="otp">Enter OTP</label>
                            <input type="text" class="form-control" name="otp" id="otp" required
                                   placeholder="Enter your 6-digit OTP" maxlength="6" pattern="\d{6}"
                                   title="Please enter a valid 6-digit OTP.">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Verify OTP</button>
                    </form>
                    <p class="mt-3 text-center">Didn't receive the OTP? <a href="resend_otp.php">Resend OTP</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
