<?php
abstract class Application_Model_MongoDB_Abstract
{
    public function __construct() {
    }
    public function db($dbname='dbmain') {	// database
        $dbname = isset($this->_db) ? $this->_db : $dbname;
    	$config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		$mongo = $config->getOption('mongo');

		$client = new MongoClient();
    	return $client->{$mongo[$dbname]};
    }
    public function c($collection=null) {   // collection
        $collection = is_null($collection) ? $this->_collection : $collection;
        $db = isset($this->_db) ? $this->_db : 'dbmain';
        return $this->db($db)->{$collection};
    }
    public function fs() {   // collection
        $db = isset($this->_db) ? $this->_db : 'dbmain';
        return $this->db($db)->getGridFS('uploaded_files');
    }
}
?>