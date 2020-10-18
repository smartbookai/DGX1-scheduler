<?php

require __BASE_PATH__ . '/PHPMailer/src/Exception.php';
require __BASE_PATH__ . '/PHPMailer/src/PHPMailer.php';
require __BASE_PATH__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


function sendEmail($to, $subject, $message, $attachment=NULL) {
//    try {
        $mail = new PHPMailer(true);
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->SMTPDebug = SMTP::DEBUG_OFF;


        $mail->Host = EMAIL_SERVER;
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        /* SMTP authentication username. */
        $mail->Username = EMAIL_ACCOUNT;
        /* SMTP authentication password. */
        $mail->Password = EMAIL_PASSWORD;

        $mail->Port = 587;
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->MsgHTML($message);
        $mail->setFrom(EMAIL_ACCOUNT);
        $mail->addAddress($to);
        if ($attachment) {
            $mail->addAttachment($attachment);
        }

        if ($mail->send()) {
            return 1;
        }
//    } catch (Exception $e) {
//        return 0;
//    }

    return 0;
}
