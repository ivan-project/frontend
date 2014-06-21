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
        $this->view->title = 'Panel użytkownika';
        $this->view->config_['state']='';

        $db_users = new Application_Model_MongoDB_User();
        $this->view->users_pending = $db_users->getPendings();
        $this->view->users_admin_count = $db_users->getByRole(3)->count();
        $this->view->users_all_count = $db_users->getAll()->count();

        $db_documents = new Application_Model_MongoDB_Document();
        $this->view->documents_my_count = $db_documents->getByOwner($this->view->user_['_id'])->count();
        $this->view->documents_all_count = $db_documents->getAll()->count();
    }
}