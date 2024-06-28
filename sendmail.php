<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\src\PHPMailer;
use PHPMailer\src\SMTP;
use PHPMailer\src\Exception;



//Load Composer's autoloader
//require './vendor/autoload.php';

//Create an instance; passing `true` enables exceptions


$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp-relay.sendinblue.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'noreply@villamart.in';                     //SMTP username
    $mail->Password   = 'MHgWSm1ky0E7p3at';                 //'TvbPkYcy1SJ2';                                //'zz0wXu5hFDPf';                         //'lsfbosnuwrfoyjwo';                               //SMTP password
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('noreply@villamart.in', 'Mailer');
    $mail->addAddress('info@villamart.in', 'Rakesh Sutar');     //Add a recipient
    //$mail->addAddress('rakeshsutar85@gmail.com');               //Name is optional
    //$mail->addReplyTo('rakeshsutar85@gmail.com', 'Information');
    //$mail->addCC('rakeshsutar85@gmail.com');
    //$mail->addBCC('rakeshsutar85@gmail.com');

    //Attachments
    //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
  ?>