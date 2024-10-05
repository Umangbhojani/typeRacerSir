<?php

  // Load PHPMailer classes
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;


function sendMail($to = null,$subject = "",$message =""){
  

    // Create an instance of PHPMailer
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'codetechflutter@gmail.com';               // SMTP username
        $mail->Password   = 'fkae anzg opbh jjka';                       // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;      // Enable TLS encryption
        $mail->Port       = 587;                                   // TCP port to connect to

        //Recipients
        $mail->setFrom('codetechflutter@gmail.com', 'TypeRacer');
        // $mail->addAddress('pradipghetiya@gmail.com');             // Add a recipient
        $mail->addAddress($to);             // Add a recipient

        // Content
        $mail->isHTML(true);                                      // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        // echo 'Email has been sent';
        return true;
    } catch (Exception $e) {
        // echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        // die;
        return false;
    }

}
?>
