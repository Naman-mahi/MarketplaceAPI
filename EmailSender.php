<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Function to send email
function sendEmail($recipientEmail, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
         // Server settings
         $mail->isSMTP();
         $mail->Host = 'smtp.gmail.com';
         $mail->SMTPAuth = true;
         $mail->Username = 'naman.intelcode@gmail.com'; // Your Gmail address
         $mail->Password = 'peav zved njcd ropg'; // Your Gmail 
         $mail->Port = 587;
 
         // Recipients
         $mail->setFrom('your_email@example.com', 'Your Name');
         $mail->addAddress($recipientEmail); // Add a recipient
 
         // Content
         $mail->isHTML(true); // Set email format to HTML
         $mail->Subject = $subject;
         $mail->Body    = $body;
         $mail->AltBody = strip_tags($body); // Plain text version
 
         $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
