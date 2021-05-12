<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('TYPE', 'application/pdf');
define('ENCODING', 'base64');
define('BODY_TEXT', 'This is the body in plain text for non-HTML mail clients');

function errorMsg($mail, $e) {
	return "Mailer Error: {$mail->ErrorInfo} " . showError($e);
}

function sendEmail($email, $name, $subject, $message, $file =null, $filename=null)
{
	try {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = getenv('SMTP_HOST');
		$mail->SMTPAuth = true;
		$mail->Username = USER_APP;
		$mail->Password = PASS;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(APP_EMAIL, APP_NAME);
		$mail->addAddress($email, $name);
		$mail->addBCC(APP_EMAIL);
		if($file){
			$mail->AddStringAttachment($file, $filename, ENCODING, TYPE);
			}

		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = BODY_TEXT;
		return $mail->send();
	
	} catch (Exception $e) {
		errorMsg($mail, $e);
	}
}


function send_email_pdf($email, $name, $subject, $message, $file, $filename)
{
	try {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = getenv('SMTP_HOST');
		$mail->SMTPAuth = true;
		$mail->Username = USER_APP;
		$mail->Password = PASS;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(APP_EMAIL, APP_NAME);
		$mail->addAddress($email, $name);
		$mail->addBCC(APP_EMAIL);
		$mail->AddStringAttachment($file, $filename, ENCODING, TYPE);
		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = BODY_TEXT;
		return $mail->send();
	} catch (Exception $e) {
		echo errorMsg($mail, $e);
	}
}


function send_email(string $email, string $name, string $subject, string $message)
{
	try {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = getenv('SMTP_HOST');
		$mail->SMTPAuth = true;
		$mail->Username = USER_APP;
		$mail->Password = PASS;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(APP_EMAIL, APP_NAME);
		$mail->addAddress($email, $name);
		//Content
		$mail->isHTML(true);
		$mail->CharSet = "utf-8";                               // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = BODY_TEXT;
		$mail->send();
	} catch (Exception $e) {
		echo errorMsg($mail, $e);
	}
}

function normal_email(string $email, string $name, $subject, string $message)
{
	try {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = getenv('SMTP_HOST');
		$mail->SMTPAuth = true;
		$mail->Username = USER_APP;
		$mail->Password = PASS;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(APP_EMAIL, APP_NAME);
		$mail->addAddress($email, $name);
		$mail->addBCC(APP_EMAIL);
		//Content
		$mail->isHTML(true);
		$mail->CharSet = "utf-8";                                   // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = BODY_TEXT;
		$mail->send();
	} catch (Exception $e) {
		echo errorMsg($mail, $e);
	}
}

function send_email_self(string $subject, string $message)
{
	try {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = getenv('SMTP_HOST');
		$mail->SMTPAuth = true;
		$mail->Username = getenv('SYSTEM_EMAIL');
		$mail->Password = getenv('APP_PASSWORD');
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(getenv('SYSTEM_EMAIL'), 'LOANEASY');
		$mail->addAddress(getenv('SYSTEM_EMAIL'));
		$mail->isHTML(true);
		$mail->CharSet = "utf-8";                              // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = BODY_TEXT;
		return $mail->send();
	} catch (Exception $e) {
		echo errorMsg($mail, $e);
	}
}

function send_to_self_pdf($subject, $message, $file, $filename)
{
	try {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = getenv('SMTP_HOST');
		$mail->SMTPAuth = true;
		$mail->Username = getenv('SYSTEM_EMAIL');
		$mail->Password = getenv('APP_PASSWORD');
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(getenv('SYSTEM_EMAIL'), 'LOANEASY');
		$mail->addAddress(getenv('SYSTEM_EMAIL'), 'LOANEASY');
		$mail->AddStringAttachment($file, $filename, ENCODING, TYPE);
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = BODY_TEXT;
		return $mail->send();
	} catch (\Exception $e) {
		echo errorMsg($mail, $e);
	}
}
