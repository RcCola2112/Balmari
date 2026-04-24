<?php
// Example send script using PHPMailer files present in the repo (no Composer).
// Usage: adjust config.php values, then run from PHP or web: email_setup/send_email.php

$config = require __DIR__ . '/config.php';

// Require the PHPMailer classes from the PHPMailer-master/src directory
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // Optional: enable verbose debug output via ?debug=1
    $debug = (isset($_GET['debug']) && $_GET['debug'] == '1') ? 2 : 0;
    $mail->SMTPDebug = $debug;
    $mail->Debugoutput = 'html';

    // SMTP settings
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['encryption'];
    $mail->Port = $config['port'];

    // From and recipient
    $mail->setFrom($config['from_email'], $config['from_name']);
    // Replace with a real recipient for testing
    $mail->addAddress('recipient@example.com', 'Recipient Name');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test email from Balmari (PHPMailer)';
    $mail->Body    = '<p>This is a <strong>test</strong> message sent using PHPMailer via Hostinger SMTP.</p>';
    $mail->AltBody = 'This is a test message sent using PHPMailer via Hostinger SMTP.';

    if ($mail->send()) {
        echo 'Message sent successfully.';
    } else {
        echo 'Message could not be sent. Error: ' . $mail->ErrorInfo;
    }
} catch (Exception $e) {
    echo 'Mailer Error: ' . $e->getMessage();
}
