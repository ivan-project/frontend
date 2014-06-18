<?php

/*

// return mime type all mimetype extension
$finfo = finfo_open(FILEINFO_MIME_TYPE);
foreach (glob("*") as $filename) {
    echo finfo_file($finfo, $filename) . "\n";
}
finfo_close($finfo);

*/
 
/*

// PHP has no native SMTP support, we recommend the
//SwiftMail library to add SMTP support. http://swiftmailer.org/
//require_once '../vendor/swiftmailer/swiftmailer/lib/swift_required.php'; 
    
// Set message recipients
$send_to = array('karol@kochan.com.pl' => 'Bob Karol Kochan');
    
// Set sender
$sent_from = array('karolkochan@gmail.com' => 'TRUE DETECTION');
 
// Set subject and body
$subject = "Test message from EasySMTP";
$body = "This is a test message.";

// Set SMTP connection details
$transport = Swift_SmtpTransport::newInstance()
		    ->setHost('smtp.gmail.com')
		    ->setPort(465)
		    ->setUsername('truedetection@gmail.com')
		    ->setPassword('truePASS')
		    ->setEncryption('ssl');
    // ->setHost('ssrs.reachmail.net')
    // ->setPort(465)
    // ->setUsername('KAROL\\admin')
    // ->setPassword('DPwAjzvU')
    // ->setEncryption('ssl');

// Create a Mailer
$mailer = Swift_Mailer::newInstance($transport);
$message = Swift_Message::newInstance()
    ->setSubject($subject) // Set Subject line here
    ->setContentType('text/html') // Sets the Content-Type
    ->setFrom($sent_from) // Sets the sender address specified at the top
    ->setTo($send_to) // Sets the recipient addresses sprecified at the top
    ->setBody($body) // Sets the body of the email
    ;

// This method should be used to add addistional custom headers.
// Use the format: $headers->addTextHeader('X-Tracking', '1'); to set other 
// headers like X-Campaign or X-Address.
$headers = $message->getHeaders();  
$headers->addTextHeader('X-Tracking', '1');

// $result will be an integer representing the number of successful recipients
$result = $mailer->send($message);

print $result . "\n";

*/

?>