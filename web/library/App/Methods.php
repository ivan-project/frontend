<?php

class App_Methods 
{
	public static function randomStr($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}
	public static function makeAvatar($img_from, $img_to, $desired = 128) {
		$thumb = new Imagick($img_from);
		$thumb->cropThumbnailImage($desired, $desired);
        $thumb->writeImage($img_to);
        $thumb->destroy(); 
        unlink($img_from);
	}
    public static function ext($file_name) {
        return strtolower(substr(strrchr($file_name,'.'),1));
    }
    public static function welcomeMail($email_to, $name_to, $email_by, $name_by, $tmp_pwd) {
    	// GET HTML TEMPLATE
		$view = new Zend_view();
		$view->setScriptPath(APPLICATION_PATH . '/views/scripts/user');
		$view->email_to = $email_to;
		$view->name_to = $name_to;
		$view->email_by = $email_by;
		$view->name_by = $name_by;
		$view->tmp_pwd = $tmp_pwd;
		$htmlBody = $view->render('welcome-mail.phtml');

		// SEND EMAIL
		$send_to = array($email_to => $name_to);
		$sent_from = array('noreplay@truedetection.com' => 'TRUE DETECTION');
		
		$subject = "Twoje dane autoryzacyjne";

		$transport = Swift_SmtpTransport::newInstance()
		    ->setHost('ssrs.reachmail.net')
		    ->setPort(465)
		    ->setUsername('KAROL\\admin')
		    ->setPassword('DPwAjzvU')
		    ->setEncryption('ssl');

		// Create a Mailer
		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance()
		    ->setSubject($subject)
		    ->setContentType('text/html')
		    ->setFrom($sent_from)
		    ->setTo($send_to)
		    ->setBody($htmlBody, 'text/html');

		$headers = $message->getHeaders();  
		$headers->addTextHeader('X-Tracking', '1');

		// $result will be an integer representing the number of successful recipients
		$result = $mailer->send($message);
    }
}

?>