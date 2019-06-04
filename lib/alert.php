<?php

namespace Alert;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class SendAlert
{
    public static function sendMail($email, $subject, $body)
    {
        $dotenv = \Dotenv\Dotenv::create(__DIR__ . '/..');
        $dotenv->load();

        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = $_ENV['MAIL_DEBUG'];         // Enable verbose debug output
            $mail->isSMTP();                                // Set mailer to use SMTP
            $mail->Host = $_ENV['MAIL_HOST'];               // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                         // Enable SMTP authentication
            $mail->Username = $_ENV['MAIL_USERNAME'];       // SMTP username
            $mail->Password = $_ENV['MAIL_PASSWORD'];       // SMTP password
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];   // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $_ENV['MAIL_PORT'];               // TCP port to connect to

            //Recipients
            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($email);               // Name is optional
            $mail->addReplyTo($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);


            // Content
            $mail->isHTML(true);        // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $body;

            $mail->send();

            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
