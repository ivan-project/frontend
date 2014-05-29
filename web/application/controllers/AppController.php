<?php

class AppController extends Zend_Controller_Action
{
    public function init_($special_view = null) {
        Zend_Layout::getMvcInstance()->setLayout("layout");
        $this->_helper->Redirector->setUseAbsoluteUri(true);
        $this->view->head_keys="";
        $this->view->head_desc="";
        //$this->view->config_ = array();
        $this->view->config_['active_path'] = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();

        $this->view->by_ajax = false;
        $this->view->by_frame = false;
        $this->view->view_="sign-in";
        $this->view->config_['state'] = 'colapsed';
        if($this->_request->getParam('ajax') && $this->_request->getParam('ajax')==true) {
            $this->_helper->layout()->disableLayout();
            $this->view->by_ajax = true;
        }
        if($this->_request->isPost() && $this->_request->getPost('data_remote_form')==true) {
            Zend_Layout::getMvcInstance()->setLayout("layout-remote");
            $this->view->by_frame = true;
        }
    }
    public function redirect_($path) {
        if(strcmp(substr($path,0,1),"/")!=0) $path = "/".$path;
        $this->getRequest()->setActionName(null);
        $this->view->config_['redirect'] = $path;
        if($this->view->by_frame) {
            Zend_Layout::getMvcInstance()->setLayout("layout-redirect");
            $this->_helper->viewRenderer->setNoRender();
        } else if($this->view->by_ajax) {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $this->_response->appendBody(json_encode(array('redirect' => $path)));
        } else {
            $this->redirect($path);
        }
    }
}