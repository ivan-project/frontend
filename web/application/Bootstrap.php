<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initDoctype() {
        $view = new Zend_View($this->getOptions());
        $view->headTitle()->setSeparator(' - ')->append('TRUE DETECTION');
        $view->headMeta()->appendHttpEquiv('Content-Type','text/html; charset=utf-8');
        return $view;
    }
    public function _initStorage() {
        Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('truedetection.com'));
        if (!isset($_SESSION['init'])) {
            session_regenerate_id();
            $_SESSION['init'] = true;
        }
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->config_ = array();
        $view->config_['logo']=true;
    }
    public function _initCustomRoute()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $router->addRoute(
            'index',
            new Zend_Controller_Router_Route("", array('module' => 'default', 'controller' => 'dashboard', 'action' => 'index'))
        );
        $router->addRoute(
            'dashboard_more',
            new Zend_Controller_Router_Route("dashboard/:controller/:action/:param1/:param2", array('module' => 'default', 'controller' => null, 'action' => null, 'param1' => null, 'param2' => null))
        );
        $router->addRoute(
            'deshboard',
            new Zend_Controller_Router_Route("dashboard", array('module' => 'default', 'controller' => 'dashboard', 'action' => 'index'))
        );
        $router->addRoute(
            'user',
            new Zend_Controller_Router_Route("user/:action", array('module' => 'default', 'controller' => 'user', 'action' => null))
        );
    }
}