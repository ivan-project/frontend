<?php
class App_Auth {
	
	private static $singleton = false;
	private static $user = false;

	private function App_Auth() {}
	
	public static function getInstance() {
        if(self::$singleton == false) {
            self::$singleton = new App_Auth();
        }
        return self::$singleton;
    }
    public function signIn($email, $pass) {
        $db_users = new Application_Model_MongoDB_User();
    	$object = $db_users->authenticate($email, $pass);
    	if($object) {
    		self::$user = $object;
    		$_SESSION['auth_user'] = $object;
    		return $object;
    	} else {
    		return false;
    	}
    }
    public function signOut() {
    	$_SESSION['auth_user'] = false;
    	self::$user = false;
    	return true;
    }
    public function isValid() {
    	if(isset($_SESSION['auth_user'])) {
        	$db_users = new Application_Model_MongoDB_User();
    		return self::$user = $db_users->tokenMatch($_SESSION['auth_user']['email'], $_SESSION['auth_user']['token']);
    	} else {
	    	return false;
	    }
    }
    public function isAdmin() {
        $identity = self::getIdentity();
        return ($identity && $identity['role']==3);
    }
    public function isSelf($id) {
        $identity = self::getIdentity();
        return ($identity && $identity['_id']==$id);
    }
    public function getIdentity() {
    	if(!self::$user) {
    		$this->isValid();
    	}
    	return self::$user;
    }
}