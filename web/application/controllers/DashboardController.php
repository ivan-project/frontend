<?php

class DashboardController extends AppController
{
    public function init() {
        $this->init_();
    	if(!App_Auth::getInstance()->isValid()) {
			$this->redirect_('user/sign-in');
    	} else {
            $this->view->user_=App_Auth::getInstance()->getIdentity();
            $this->view->config_['logo']=false;
        }
    }
    public function indexAction() {
        $this->view->config_['state']='';
        $db_users = new Application_Model_MongoDB_User();
        $this->view->users_pending = $db_users->getPendings();
        $this->view->users_admin_count = $db_users->getByRole(3)->count();
        $this->view->users_all_count = $db_users->getAll()->count();
    }
}