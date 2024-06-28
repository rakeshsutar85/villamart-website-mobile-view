<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.sendinblue.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@villamart.in';
    $mail->Password   = 'MHgWSm1ky0E7p3at';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('noreply@villamart.in', $_POST['email']);
    $mail->addAddress('info@villamart.in', 'Villamart Pvt. Ltd.');
    
    $mail->isHTML(true);
    $mail->Subject = strtoupper($_POST['subject']);
    $mail->Body    = '<b>Name:</b> ' . $_POST['fullname'] . '<br>'  . '<b>Email:</b> ' . $_POST['email'] . '<br>'  . '<b>Address:</b> ' . $_POST['address'] .'<br>'  . '<b>Phone:</b> ' . $_POST['mobile'] . '<br>'  . '<b>Message:</b> ' . $_POST['message'];
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo '<script>alert("Message has been sent"); location.href="../contact.html"</script>';
} catch (Exception $e) {
    echo '<script>alert("Sorry for inconvenience. Message could not be sent.")</script>';
}

?>