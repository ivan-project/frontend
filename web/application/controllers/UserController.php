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
        if($this->_request->isPost() && $this->_request->getPost('email')) {
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
                $object = $db_users->create($form['name'], $form['email'], $pwd);
                if($object) {
                    if(is_uploaded_file($_FILES['avatar']['tmp_name'])) {
                        $id = $object['_id'];
                        $avatars_dir = 'assets/contents/avatars';
                        $avatar_temp = $avatars_dir.'/_tmp_'.$id.'.'.App_Methods::ext($_FILES["avatar"]["name"]);
                        $avatar_final = $avatars_dir.'/'.$id.'.png';
                        if(!is_dir($avatars_dir)) {
                            mkdir($avatars_dir);
                        }
                        move_uploaded_file($_FILES["avatar"]["tmp_name"], $avatar_temp);
                        App_Methods::makeAvatar($avatar_temp, $avatar_final);
                    }
                    // $db_users->welcomeMail($object['email']);
                    $auth = App_Auth::getInstance()->signIn($form['email'], $pwd);
                    $this->redirect_('dashboard');
                }
            }
        }
        $this->view->form = $form;
    }
    public function welcomeMailAction() {
        
    }
}