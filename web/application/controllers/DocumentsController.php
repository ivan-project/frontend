<?php

class DocumentsController extends AppController
{
    public function init() {
        $this->init_();
        $this->view->config_['state']='';
        if(!App_Auth::getInstance()->isValid()) {
            $this->redirect_('user/sign-in');
        } else {
            $this->view->user_=App_Auth::getInstance()->getIdentity();
            $this->view->config_['logo']=false;
        }
    }
    public function createAction() {
        $form = array();
        if($this->_request->isPost()) {
            $form['title'] = $this->_request->getPost('title');
            $form['author'] = $this->_request->getPost('author');
            $form['email'] = $this->_request->getPost('email');
        }
        $this->view->form = $form;
    }
}