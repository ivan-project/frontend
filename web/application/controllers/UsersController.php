<?php

class UsersController extends AppController
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
    public function indexAction() {
        $form = array();

        $db_users = new Application_Model_MongoDB_User();
        
        $filter = array();
        if($this->_request->getParam('role')) {
            $form['role'] = $this->_request->getParam('role');
            $roles = array();
            foreach($form['role'] as $role) {
                array_push($roles, new MongoInt32($role));
            }
            $filter = array(
                'role' => array(
                    '$in' => $roles
                )
            );
        }
        if($this->_request->getParam('search')) {
            $form['search'] = $this->_request->getParam('search');
            $regex = new MongoRegex("/{$form['search']}/i");
            $roles = $filter;
            $filter = array(
                '$or' => array(
                    array_merge(array('name' => $regex), $roles),
                    array_merge(array('email' => $regex), $roles)
                )
            );
        }
        $this->view->users = $db_users->filter($filter);
        $this->view->form = $form;
    }
    public function createAction() {
        if(App_Auth::getInstance()->isAdmin()) {
            $form = array();
            if($this->_request->isPost()) {
                $form['name'] = $this->_request->getPost('name');
                $form['email'] = $this->_request->getPost('email');
                $form['role'] = (int)$this->_request->getPost('role');
                $db_users = new Application_Model_MongoDB_User();
                $tmp_pwd = App_Methods::randomStr(12);
                $object = $db_users->create($form['name'], $form['email'], $tmp_pwd, $form['role']);
                if($object) {
                    if(is_uploaded_file($_FILES['avatar']['tmp_name'])) {
                        $id = $object['_id'];
                        $user_dir = 'assets/contents/users/'.$id;
                        $avatar_temp = $user_dir.'/_tmp_avatar.'.App_Methods::ext($_FILES["avatar"]["name"]);
                        $avatar_final = $user_dir.'/avatar.png';
                        if(!is_dir($user_dir)) {
                            mkdir($user_dir);
                        }
                        move_uploaded_file($_FILES["avatar"]["tmp_name"], $avatar_temp);
                        App_Methods::makeAvatar($avatar_temp, $avatar_final);
                    }
                    // $db_users->passwordMail($object['email'],$tmp_pwd);
                    $this->redirect_('dashboard/users/show/'.$object['_id']);
                }
            }
            $this->view->form = $form;
        } else {
            $this->redirect_('dashboard/users');
        }
    }
    public function showAction() {
        $id = $this->_request->getParam('param1');
        $db_users = new Application_Model_MongoDB_User();
        $this->view->user = $db_users->getById($id);
        if(!$this->view->user) {
            $this->redirect_('dashboard');
        }
    }
    public function updateAction() {
        $id = $this->_request->getParam('param1');
        if(App_Auth::getInstance()->isAdmin() || App_Auth::getInstance()->isSelf($id)) {
            if($this->_request->isPost()) {
                $db_users = new Application_Model_MongoDB_User();
                $name = $this->_request->getPost('name');
                $email = $this->_request->getPost('email');

                if(is_uploaded_file($_FILES['avatar']['tmp_name'])) {
                    $user_dir = 'assets/contents/users/'.$id;
                    $avatar_temp = $user_dir.'/_tmp_avatar.'.App_Methods::ext($_FILES["avatar"]["name"]);
                    $avatar_final = $user_dir.'/avatar.png';
                    if(!is_dir($user_dir)) {
                        mkdir($user_dir);
                    }
                    move_uploaded_file($_FILES["avatar"]["tmp_name"], $avatar_temp);
                    App_Methods::makeAvatar($avatar_temp, $avatar_final);
                }
                
                if(App_Auth::getInstance()->isAdmin()) {
                    $role = (int)$this->_request->getPost('role');

                    if($role==0) {
                        $db_users->destroy($id);
                        $this->redirect_('dashboard/users');
                    } else {
                        $db_users->update($id, $name, $email, $role);
                        $this->showAction();
                        $this->render('show');
                    }
                } else if(App_Auth::getInstance()->isSelf($id)) {
                    $action = (int)$this->_request->getPost('action');

                    if($action==0) {
                        $db_users->destroy($id);
                        $this->redirect_('dashboard/users');
                    } else {
                        $db_users->update($id, $name, $email, $role);
                        $this->showAction();
                        $this->render('show');
                    }
                } else {
                    $db_users->update($id, $name, $email);
                    $this->showAction();
                    $this->render('show');
                }
            } else {
                $this->showAction();
                $this->render('show');
            }
        } else {
            $this->redirect_('dashboard/users');
        }
    }
}