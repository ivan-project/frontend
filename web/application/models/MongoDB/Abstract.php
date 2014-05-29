<?php
abstract class Application_Model_MongoDB_Abstract
{
    public function __construct() {
    }
    public function db() {	// database
    	$config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		$mongo = $config->getOption('mongo');

		$client = new MongoClient();
    	return $client->{$mongo['dbname']};
    }
    public function c() {	// collection
    	return $this->db()->{$this->_name};
    }
}
?>