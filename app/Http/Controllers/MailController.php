<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;

class MailController extends Controller
{
    public function sendEmail($subject,$body,$recipient_email,$recipient_name)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            // $mail->CharSet = "UTF-8";
            // $mail->isSMTP();
            // $mail->Host       = 'your.smtp.server.com'; // Your SMTP server (no auth required)
            // $mail->SMTPAuth   = false;                 // ðŸ”´ Disable SMTP authentication
            // $mail->SMTPAutoTLS = false;                // ðŸ”´ Disable TLS if server does not support it
            // $mail->Port       = 25;                    // Use port 25 for unauthenticated mail (default)

            // Recipients
            $mail->setFrom('leads@app.manageoa.com', 'Manageoa');
            $mail->addAddress($recipient_email, $recipient_name); // Add recipient
            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = 'Hello! This is a test email using PHPMailer.';

            $mail->send();

            return "Email sent successfully!";
        } catch (Exception $e) {
            dd($e->getMessage());
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
