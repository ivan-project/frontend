<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    //realpath(APPLICATION_PATH . '/../vendor/zendframework/zendframework1/library'),
    realpath(APPLICATION_PATH . '/../vendor'),///videlalvaro/php-amqplib/PhpAmqpLib
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../application'),
    realpath(APPLICATION_PATH . '/../application/controllers'),
    get_include_path(),
)));
/** Zend_Application */
//require_once 'Zend/Application.php';
/* Vendors Autoload */
require_once 'autoload.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
// Umożliwia ładowanie automatyczne klas
require_once "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);

Zend_Layout::startMvc('../application/layouts/scripts');
Zend_Controller_Front::getInstance()->setBaseUrl("");
$application->bootstrap()
            ->run();
?>