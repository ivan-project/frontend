<?php

class UserController extends AppController
{
    public function init() {
        $this->init_();
        $any = array('sign-out');
        if(!in_array($this->_request->getParam('action'), $any)) {
            if(App_Auth::getInstance()->isValid()) {
                $this->redirect_('dashboard');
            }
        }
    }
    public function signInAction() {
        $form = array();
        if($this->_request->isPost() && $this->_request->getPost('email') && !App_Auth::getInstance()->isValid()) {
            $form['email'] = $this->_request->getPost('email');
            $pwd = $this->_request->getPost('pwd');

            $auth = App_Auth::getInstance()->signIn($form['email'], $pwd);
            if($auth) {
                $this->redirect_('dashboard');
            }
        }
        $this->view->form = $form;
    }
    public function signOutAction() {
        App_Auth::getInstance()->signOut();
        $this->redirect_('user/sign-in');
    }
    public function signUpAction() {
        $form = array();
        if($this->_request->isPost()) {
            $form['name'] = $this->_request->getPost('name');
            $form['email'] = $this->_request->getPost('email');
            $pwd = $this->_request->getPost('pwd');
            $pwd_repeat = $this->_request->getPost('pwd_repeat');
            if(strcmp($pwd,$pwd_repeat)==0) {
                $db_users = new Application_Model_MongoDB_User();
                $object = $db_users->register($form['name'], $form['email'], $pwd);
                if($object) {
                    // if($db_users->welcomeMail($form['email']))
                    // $this->_forward('sign-up-confirm','user');
                    $auth = App_Auth::getInstance()->signIn($form['email'], $pwd);
                    $this->redirect_('dashboard');
                }
            }
        }
        $this->view->form = $form;
    }
    /*public function signUpConfirmAction() {
        $this->view->register_mail = $this->_request->getPost('email');
    }
    public function welcomeMailAction() {
        $this->_helper->layout()->disableLayout();
        $this->render('user/welcome-mail');
    }*/
}