<?php

class Application_Model_MongoDB_Document extends Application_Model_MongoDB_Abstract
{
    /*
     * role:
     * 1 = praca
     * 2 = ksiazka
     */
    protected $_name = 'documents';
    public function create($owner, $title, $author, $email, $type=1) {
    	$object = array(
            '_owner' => new MongoId($owner),
            'title' => ucfirst($title),
            'author' => ucwords($author),
            'email' => strtolower($email),
            'type' => new MongoInt32($type),
            'created_at' => new MongoDate()
        );
    	$this->c()->insert($object);
        return $object;
    }
    public function destroy($id) {
        $this->c()->remove(
            array('_id' => new MongoId($id))
        );
    }
    public function getAll() {
        $object = $this->c()->find()->sort(array('title' => 1));
        return $object;
    }
    public function filter($filter) {
        $object = $this->c()->find(
            $filter
        )->sort(array('title' => 1));
        return $object;
    }
    public function getByType($type) {
        $object = $this->c()->find(
            array('type' => $type)
        )->sort(array('title' => 1));
        return $object;
    }
    public function getByOwner($owner) {
        $object = $this->c()->find(
            array('_owner' => new MongoId($owner))
        )->sort(array('title' => 1));
        return $object;
    }
    public function getById($id) {
        $object = $this->c()->findOne(array('_id' => new MongoId($id)));
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
?>