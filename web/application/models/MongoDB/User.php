<?php

class Application_Model_MongoDB_User extends Application_Model_MongoDB_Abstract
{
    /*
     * role:
     * 0 = temp
     * 1 = mail_confirmed
     * 2 = user
     * 3 = admin
     */
    protected $_name = 'users';
    public function create($name, $email, $pwd, $role=1) {
        $object = $this->c()->find(
            array('email' => strtolower($email)),
            array('_id' => 1)
        )->limit(1);
        if($object->count()>0) {
        	return false;
        } else {
			$salt = App_Methods::randomStr(32);
			$token = App_Methods::randomStr(32);
			$hash = password_hash($pwd.$salt, PASSWORD_BCRYPT);

            $object = array(
                'name' => ucwords(strtolower($name)),
                'email' => strtolower($email),
                'hash' => $hash,
                'salt' => $salt,
                'role' => new MongoInt32($role),
                'token' => $token,
                'created_at' => new MongoDate()
            );
        	$this->c()->insert($object);
            return $this->trimObject($object);
        }
    }
    public function destroy($id) {
        $this->c()->remove(
            array('_id' => new MongoId($id))
        );
    }
    public function getAll() {
        $object = $this->c()->find(
            array('role' => array('$gte' => 1)),
            array('email' => 1, 'name' => 1, 'role' => 1)
        )->sort(array('name' => 1));
        return $object;
    }
    public function getByRole($role) {
        $object = $this->c()->find(
            array('role' => $role),
            array('email' => 1, 'name' => 1, 'role' => 1)
        )->sort(array('name' => 1));
        return $object;
    }
    public function getById($id) {
        $object = $this->c()->findOne(array('_id' => new MongoId($id)));
        return $object;
    }
    public function getPendings() {
        $object = $this->c()->find(
            array('role' => 1),
            array('email' => 1, 'name' => 1, 'role' => 1)
        )->sort(array('created_at' => 1));
        return $object;
    }
    public function updateRole($id, $role) {
        $this->c()->update(
            array('_id' => new MongoId($id)),
            array('$set' => 
                array('role' => new MongoInt32($role))
            )
        );
    }
    public function update($id, $name, $email) {
        $this->c()->update(
            array('_id' => new MongoId($id)),
            array('$set' => 
                array(
                    'email' => strtolower($email),
                    'name' => $name
                )
            )
        );
    }

    public function authenticate($email, $pwd) {
    	$object = $this->c()->find(
            array(
                'email' => strtolower($email),
                'role' => array('$gte' => 2)
            )
        )->limit(1);
    	if($object->count()>0) {
            $object->next();
        	$object = $object->current();
            if(password_verify($pwd.$object['salt'], $object['hash'])) {
        		$object['token'] = App_Methods::randomStr(32);
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
    	$object = $this->c()->find(
            array(
                'email' => strtolower($email),
                'role' => array('$gte' => 2)
            )
        )->limit(1);
    	if($object->count()>0) {
            $object->next();
            $object = $object->current();
        	if(strcmp($token, $object['token'])==0) {
                return $this->trimObject($object);
        	} else {
        		return false;
        	}
        } else {
        	return false;
        }
    }
    // public function welcomeMail($email) {
    //     $object = $this->c()->findOne(array(
    //         'email' => $email,
    //         'role' => 0
    //     ));
    //     if(!empty($object)) {
    //         $subject = "Register confirmation";
    //         $mail = new App_Mail_PHPMailer();
    //         $mail->CharSet = "UTF-8";
    //         $mail->IsHTML(false);
    //         $mail->IsSendmail();
    //         $mail->AddReplyTo('karolkochan@gmail.com','TRUE DETECTION');
    //         $mail->From = 'karolkochan@gmail.com';
    //         $mail->FromName = 'TRUE DETECTION';
    //         $mail->AddAddress($email, $object['name']);
    //         $mail->Subject = sprintf("=?utf-8?B?%s?=", base64_encode($subject));
    //         $mail->Body = "This is only a test that passed.";
    //         if($mail->Send()) {
    //             return true;
    //         } else {
    //             return false;
    //         }
    //     } else {
    //         return false;
    //     }
    // }
    // public function passwordMail($email, $pwd) {
    // }


    private function trimObject($object) {
        unset($object['hash']);
        unset($object['salt']);
        return $object;
    }
}
?>