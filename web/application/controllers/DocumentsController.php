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
    public function indexAction() {
        $form = array();

        $db_documents = new Application_Model_MongoDB_Document();
        
        $filter = array();
        $types = array();
        $owner = array();
        $form['owner'] = strcmp($this->_request->getParam('owner'),'true')==0 ? 'true' : 'false';
        if(strcmp($form['owner'],'false')) {
            $owner = array('_owner' => new MongoId($this->view->user_['_id']));
        }
        if($this->_request->getParam('type')) {
            $form['type'] = $this->_request->getParam('type');
            $types_tmp = array();
            foreach($form['type'] as $type) {
                array_push($types_tmp, new MongoInt32($type));
            }
            $types = array(
                'type' => array(
                    '$in' => $types_tmp
                )
            );
        }
        if($this->_request->getParam('search')) {
            $form['search'] = $this->_request->getParam('search');
            $regex = new MongoRegex("/{$form['search']}/i");
            $filter = array(
                '$or' => array(
                    array_merge(array('title' => $regex), $types, $owner),
                    array_merge(array('author' => $regex), $types, $owner)
                )
            );
        } else {
            $filter = array_merge($types, $owner);
        }
        $this->view->documents = $db_documents->filter($filter);
        $this->view->form = $form;
    }
    public function createAction() {
        $form = array();
        if($this->_request->isPost()) {
            $form['title'] = $this->_request->getPost('title');
            $form['author'] = $this->_request->getPost('author');
            $form['email'] = $this->_request->getPost('email');
            $form['type'] = $this->_request->getPost('type');
            $owner = $this->view->user_['_id'];

            $doc = $_FILES['document']['tmp_name'];

            if($this->isAllowedDocument($doc)) {//true) {//
                $db_documents = new Application_Model_MongoDB_Document();
                $object = $db_documents->create($owner, 'document', $form['title'], $form['author'], $form['email'], $form['type']);
                if($object) {
                    $queue = new App_Queue();
                    $queue->queueFile($object['_id']);
                    $this->redirect_('dashboard/documents/show/'.$object['_id']);
                }
            }
        }
        $this->view->form = $form;
    }
    public function showAction() {
        $id = $this->_request->getParam('param1');
        $db_documents = new Application_Model_MongoDB_Document();
        $this->view->document = $db_documents->getById($id);
        if(!$this->view->document) {
            $this->redirect_('dashboard');
        }
    }
    public function updateAction() {
        $id = $this->_request->getParam('param1');
        $db_documents = new Application_Model_MongoDB_Document();
        $document = $db_documents->getById($id);

        if(App_Auth::getInstance()->isAdmin() || App_Auth::getInstance()->isSelf($document['_owner'])) {
            if($this->_request->isPost()) {
                $title = $this->_request->getPost('title');
                $author = $this->_request->getPost('author');
                $email = $this->_request->getPost('email');
                
                if(App_Auth::getInstance()->isAdmin() || App_Auth::getInstance()->isSelf($document['_owner'])) {
                    $action = (int)$this->_request->getPost('action');

                    if($action==0) {
                        $db_documents->destroy($id);
                        $this->redirect_('dashboard/documents');
                    } else {
                        $db_documents->update($id, $title, $author, $email);
                        $this->showAction();
                        $this->render('show');
                    }
                } else {
                    $db_documents->update($id, $title, $author, $email);
                    $this->showAction();
                    $this->render('show');
                }
            } else {
                $this->showAction();
                $this->render('show');
            }
        } else {
            $this->redirect_('dashboard/documents');
        }
    }

    private function isAllowedDocument($file) {
        $allowed = array("application/pdf", "application/msword");

        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        $mime = strtolower(finfo_file($finfo, $file));
        /*$fp = fopen('./a.txt','w');
        fwrite($fp,var_dump($finfo,true));
        fclose($fp);*/
        return in_array($mime, $allowed);
    }
}