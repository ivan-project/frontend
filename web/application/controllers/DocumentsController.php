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
        $this->view->title = 'Dokumenty';
        $form = array();

        $db_documents = new Application_Model_MongoDB_Document();
        
        $filter = array();
        $types = array();
        $owner = array();
        $form['owner'] = strcmp($this->_request->getParam('owner'),'true')==0 ? 'true' : 'false';
        if(strcmp($form['owner'],'false')!=0) {
            $owner = array('_owner' => new MongoId($this->view->user_['_id']));
        }
        if($this->_request->getParam('type')) {
            $form['type'] = $this->_request->getParam('type');
            $types_tmp = array();
            foreach($form['type'] as $type) {
                array_push($types_tmp, $type);
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
        $this->view->title = 'Dokumenty - Nowy dokument';
        $this->view->config_['back'] = array("/dashboard/documents", "Spis dokumentów");
        $form = array();
        if($this->_request->isPost()) {
            $form['title'] = $this->_request->getPost('title');
            $form['author'] = $this->_request->getPost('author');
            $form['email'] = $this->_request->getPost('email');
            $form['type'] = $this->_request->getPost('type');
            $owner = $this->view->user_['_id'];

            $doc = $_FILES['document']['tmp_name'];
            $mime = $this->isAllowedDocument($doc);

            if($mime) {//true) {//
                $db_documents = new Application_Model_MongoDB_Document();
                $object = $db_documents->create($owner, 'document', $mime, $form['title'], $form['author'], $form['email'], $form['type']);
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
        $this->view->config_['back'] = array("/dashboard/documents", "Spis dokumentów");
        $id = $this->_request->getParam('param1');

        $this->showDataRetrieve($id);
        //$this->view->document['status'] = 0;

        if(!$this->view->document) {
            $this->redirect_('dashboard');
        }
    }
    public function refreshAction() {
        if($this->_request->isPost() && $this->_request->getPost('id')) {
            $this->_helper->layout()->disableLayout();
            $id = $this->_request->getPost('id');
            $this->showDataRetrieve($id);
            $this->render('show-content');
        } else {
            $this->redirect_('dashboard/documents');
        }
    }
    private function showDataRetrieve($id) {
        $db_documents = new Application_Model_MongoDB_Document();
        $this->view->document = $db_documents->getById($id);
        $this->view->document_shorts = $db_documents->getShorts();
        $this->view->title = 'Dokumenty - '.$this->view->document['title'];

        $db_comparisons = new Application_Model_MongoDB_Comparison();
        $this->view->comparisons = $db_comparisons->getByDocument($this->view->document['_id']);
        $this->view->comparisons_count_alert = $db_comparisons->getStatsDocument($id, $this->view->config_['appconfig_alert']);
        $this->view->comparisons_count_warning = $db_comparisons->getStatsDocument($id, $this->view->config_['appconfig_warning']) - $this->view->comparisons_count_alert;
        $this->view->comparisons_count_clean = $db_comparisons->getStatsDocument($id, $this->view->config_['appconfig_warning'], true);
        if(isset($this->view->document['comparison'])) {
            $this->view->comparisons_count_left = $this->view->document['comparison']['total']-$this->view->document['comparison']['completed'];
        } else {
            $this->view->comparisons_count_left = 0;
        }

        $db_users = new Application_Model_MongoDB_User();
        $this->view->owner = $db_users->getById($this->view->document['_owner']);
    }
    public function compareAction() {
        $id = $this->_request->getParam('doc');
        $id_c = $this->_request->getParam('compare');

        $db_documents = new Application_Model_MongoDB_Document();
        $this->view->document = $db_documents->getById($id);
        $this->view->document_c = $db_documents->getById($id_c);
        $this->view->title = 'Dokumenty - Porównaj '.$this->view->document['title'];
        $this->view->config_['back'] = array("/dashboard/documents/show/".$id, "Statystyki dokumentu");

        $db_comparisons = new Application_Model_MongoDB_Comparison();
        $this->view->comparisons = $db_comparisons->getByDocumentsFull($this->view->document['_id'], $this->view->document_c['_id']);

        if(!$this->view->comparisons) {
            $this->redirect_('dashboard');
        }

        $this->view->config_['state'] = 'expanded';
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
        $allowed = array("application/pdf", "application/vnd.openxmlformats-officedocument.wordprocessingml.document");

        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        $mime = strtolower(finfo_file($finfo, $file));
        $fp = fopen('./a.txt','w');
        fwrite($fp, $mime);
        fclose($fp);
        return in_array($mime, $allowed) ? $mime : false;
    }
}