<?php

class Application_Model_MongoDB_User extends Application_Model_MongoDB_Abstract
{
    /*
     * flag:
     * 0 = temp
     * 1 = mail_confirmed
     * 2 = user
     * 3 = admin
     */
    protected $_name = 'users';
    public function register($name, $email, $pwd, $flag=0) {
        $object = $this->c()->findOne(array(
        	'email' => $email
    	));
        if(!empty($object)) {
        	return false;
        } else {
			$salt = App_Methods::randomStr(32);
			$token = App_Methods::randomStr(32);
			$hash = password_hash($pwd.$salt, PASSWORD_BCRYPT);

        	return $this->c()->insert(array(
        		'name' => ucwords(strtolower($name)),
        		'email' => strtolower($email),
        		'hash' => $hash,
        		'salt' => $salt,
        		'flag' => $flag,
        		'token' => $token
        	));
        }
    }
    public function authenticate($email, $pwd) {
    	$object = $this->c()->findOne(array(
        	'email' => $email
    	));
    	if(!empty($object)) {
        	if(password_verify($pwd.$object['salt'], $object['hash'])) {
        		$object['token'] = $this->randomStr(32);
        		$this->c()->update(
        			array('email' => $object['email']), 
        			array('$set' => 
        				array('token' => $object['token'])
    				)
        		);
        		return $this->trimObject($object);
        	} else {
        		return false;
        	}
        } else {
        	return false;
        }
    }
    public function tokenMatch($email, $token) {
    	$object = $this->c()->findOne(array(
        	'email' => $email
    	));
    	if(!empty($object)) {
        	if(strcmp($token, $object['token'])==0) {
                return $this->trimObject($object);
        	} else {
        		return false;
        	}
        } else {
        	return false;
        }
    }
    public function welcomeMail($email) {
        $object = $this->c()->findOne(array(
            'email' => $email,
            'flag' => 0
        ));
        if(!empty($object)) {
            $subject = "Register confirmation";
            $mail = new App_Mail_PHPMailer();
            $mail->CharSet = "UTF-8";
            $mail->IsHTML(false);
            $mail->IsSendmail();
            $mail->AddReplyTo('karolkochan@gmail.com','TRUE DETECTION');
            $mail->From = 'karolkochan@gmail.com';
            $mail->FromName = 'TRUE DETECTION';
            $mail->AddAddress($email, $object['name']);
            $mail->Subject = sprintf("=?utf-8?B?%s?=", base64_encode($subject));
            $mail->Body = "This is only a test that passed.";
            if($mail->Send()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    private function trimObject($object) {
        unset($object['hash']);
        unset($object['salt']);
        return $object;
    }
    public function randomStr($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}
}
?>