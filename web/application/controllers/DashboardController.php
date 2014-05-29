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
    }
}