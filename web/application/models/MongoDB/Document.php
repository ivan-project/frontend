<?php

class Application_Model_MongoDB_Document extends Application_Model_MongoDB_Abstract
{
    /*
     * role:
     * 1 = praca
     * 2 = ksiazka
     */
    protected $_collection = 'documents';
    protected $_comparisons = 'comparisons';
    public function create($owner, $file, $mime, $title, $author, $email, $type='thesis', $status=0) {
        $fs = $this->fs();
        $fileID = $fs->storeUpload($file, array("contentType" => $mime));

    	$object = array(
            '_owner' => new MongoId($owner),
            'title' => ucfirst($title),
            'author' => ucwords($author),
            'email' => strtolower($email),

            'type' => strtolower($type),
            'status' => new MongoInt32($status),
            'fileDocument' => $fileID,
            /*'plaintext' => '',
            'lemmatized' => '',
            'comparisons' => array(
                    'completed' => new MongoInt32(0),
                    'completed' => new MongoInt32(0)
                ),*/
            
            'created_at' => new MongoDate()
        );
    	$this->c()->insert($object);
        return $object;
    }
    public function destroy($id) {
        $doc = $this->getById($id);

        $db_comparisons = new Application_Model_MongoDB_Comparison();
        $db_comparisons->destroyByDocumentId($id);

        if($doc) {
            $this->fs()->remove(
                array('_id' => new MongoId($doc['fileDocument']))
            );
        }

        $this->c()->remove(
            array('_id' => new MongoId($id))
        );
    }
    public function destroyByOwner($id) {
        $object = $this->filter(
            array('_owner' => new MongoId($id)),
            array('plaintext' => 0, 'lemmatized' => 0)
        );
        foreach($object as $document) {
            $this->destroy($document['_id']);
        }
    }
    public function getAll() {
        $object = $this->c()->find(
            array(),
            array('plaintext' => 0, 'lemmatized' => 0)
        )->sort(array('title' => 1));
        return $object;
    }
    public function getShorts() {
        $object = $this->c()->find(
            array(),
            array('_id' => 1, 'title' => 1)
        )->sort(array('_id' => 1));
        $shorts = array();
        foreach($object as $item) {
            $shorts[$item['_id'].""] = $item['title'];
        }
        return $shorts;
    }
    public function filter($filter) {
        $object = $this->c()->find(
            $filter, 
            array('plaintext' => 0, 'lemmatized' => 0)
        )->sort(array('title' => 1));
        return $object;
    }
    public function getByType($type) {
        $object = $this->c()->find(
            array('type' => $type),
            array('plaintext' => 0, 'lemmatized' => 0)
        )->sort(array('title' => 1));
        return $object;
    }
    public function getByOwner($owner) {
        $object = $this->c()->find(
            array('_owner' => new MongoId($owner)),
            array('plaintext' => 0, 'lemmatized' => 0)
        )->sort(array('title' => 1));
        return $object;
    }
    public function getById($id) {
        $object = $this->c()->findOne(
            array('_id' => new MongoId($id))
        );
        return $object;
    }
    public function update($id, $title, $author, $email) {
        $update = array(
                    'email' => strtolower($email),
                    'title' => $title,
                    'author' => $author
                );
        $this->c()->update(
            array('_id' => new MongoId($id)),
            array('$set' => 
                $update
            )
        );
    }
}

// sprawdzanie tasków: rabbitmqadmin get queue=tasks

?>

