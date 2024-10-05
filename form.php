<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
   require 'mail.php';

    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);

    $subject = "Hello !".$name;
    $message = "Hi ".ucfirst($name)."! You have successfully registed in our application." ;
   
    // Send email
    if (sendMail($email,$subject,$message)) {
        echo 'Email sent successfully to ' . $email;
    } else {
        echo 'Failed to send email.';
    }
} 
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email Form</title>
</head>
<body>
    <h1>Send Email</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <input type="submit" value="Send Email">
    </form>
</body>
</html>
