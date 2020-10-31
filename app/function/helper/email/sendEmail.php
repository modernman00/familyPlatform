<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($email, $name, $subject, $message, $file =null, $filename=null)
{
	try {
		$mail = new PHPMailer(true);
		//	$mail->SMTPDebug = 2;
		$mail->isSMTP();
		$mail->Host = 'send.one.com';
		$mail->SMTPAuth = true;
		$mail->Username = USER_APP;
		$mail->Password = PASS;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(APP_EMAIL, APP_NAME);
		// $mail->addAddress($email, $name);
		$mail->addAddress($email, $name);
		$mail->addBCC(APP_EMAIL);
		if($file){
			$mail->AddStringAttachment($file, $filename, 'base64', 'application/pdf');}
		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		$send = $mail->send();
		return $send;	
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}


function send_email_pdf($email, $name, $subject, $message, $file, $filename)
{
	try {
		$mail = new PHPMailer(true);
		//	$mail->SMTPDebug = 2;
		$mail->isSMTP();
		$mail->Host = 'send.one.com';
		$mail->SMTPAuth = true;
		$mail->Username = USER_APP;
		$mail->Password = PASS;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(APP_EMAIL, APP_NAME);
		// $mail->addAddress($email, $name);
		$mail->addAddress($email, $name);
		$mail->addBCC(APP_EMAIL);
		$mail->AddStringAttachment($file, $filename, 'base64', 'application/pdf');
		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		$send = $mail->send();
		return $send;	
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}


function send_email(string $email, string $name, string $subject, string $message)
{
	try {
		$mail = new PHPMailer(true);
		//	$mail->SMTPDebug = 2;
		$mail->isSMTP();
		$mail->Host = 'send.one.com';
		$mail->SMTPAuth = true;
		$mail->Username = USER_APP;
		$mail->Password = PASS;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(APP_EMAIL, APP_NAME);
		// $mail->addAddress($email, $name);
		$mail->addAddress($email, $name);
		//Content
		$mail->isHTML(true);
		$mail->CharSet = "utf-8";                               // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		$mail->send();
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}

function normal_email(string $email, string $name, $subject, string $message)
{
	try {
		$mail = new PHPMailer(true);
		//$mail->SMTPDebug = 2;
		$mail->isSMTP();
		$mail->Host = 'send.one.com';
		$mail->SMTPAuth = true;
		$mail->Username = USER_APP;
		$mail->Password = PASS;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(APP_EMAIL, APP_NAME);
		// $mail->addAddress($email, $name);
		$mail->addAddress($email, $name);
		$mail->addBCC(APP_EMAIL);
		//Content
		$mail->isHTML(true);
		$mail->CharSet = "utf-8";                                   // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		$mail->send();
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}

function send_email_self(string $subject, string $message)
{
	try {
		$mail = new PHPMailer(true);
		//$mail->SMTPDebug = 2;
		$mail->isSMTP();
		$mail->Host = 'send.one.com';
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
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		$send = $mail->send();
		return $send;
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo} {$e->getMessage()}";
	}
}

function send_to_self_pdf($subject, $message, $file, $filename)
{
	try {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = 'send.one.com';
		$mail->SMTPAuth = true;
		$mail->Username = getenv('SYSTEM_EMAIL');
		$mail->Password = getenv('APP_PASSWORD');
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->setFrom(getenv('SYSTEM_EMAIL'), 'LOANEASY');
		$mail->addAddress(getenv('SYSTEM_EMAIL'), 'LOANEASY');
		$mail->AddStringAttachment($file, $filename, 'base64', 'application/pdf');
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		return $mail->send();
	} catch (\Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo} {$e->getMessage()}";
	}
}
