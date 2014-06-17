<?php

class Application_Model_MongoDB_Document extends Application_Model_MongoDB_Abstract
{
    /*
     * role:
     * 1 = praca
     * 2 = ksiazka
     */
    protected $_collection = 'documents';
    public function create($owner, $file, $title, $author, $email, $type='thesis', $status=0) {
        $fs = $this->fs();
        $fileID = $fs->storeUpload($file, array("contentType" => "application/pdf"));

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

        if($doc) {
            $this->fs()->remove(
                array('_id' => new MongoId($doc['fileDocument']))
            );
        }

        $this->c()->remove(
            array('_id' => new MongoId($id))
        );
    }
    public function getAll() {
        $object = $this->c()->find(
            array(),
            array('plaintext' => 0, 'lemmatized' => 0)
        )->sort(array('title' => 1));
        return $object;
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
            array('_id' => new MongoId($id)),
            array('plaintext' => 0, 'lemmatized' => 0)
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

// różnica w mongo między plikiem dodanym przez frontend/php a plikiem example.pdf dodanym przez skrypt jj'a - inne typy danych

// { "_id" : ObjectId("53a09a9730d0aa9b0d438c22"), "filename" : "996bc7fea209bb4bffd61bc770752cfea0e6cc4d.pdf", "contentTyp
// e" : "application/pdf", "length" : 86253, "chunkSize" : 261120, "uploadDate" : ISODate("2014-06-17T19:44:23.958Z"), "ali
// ases" : null, "metadata" : null, "md5" : "6a1d15e0ee9d407588522cca499e704b" } // EXAMPLE/JS


// { "_id" : ObjectId("53a0a28bfa4634b1028b4568"), "metadata" : { "contentType" : "application/pdf" }, "filename" : "lab2.p
// df", "uploadDate" : ISODate("2014-06-17T20:18:19.146Z"), "length" : NumberLong(86253), "chunkSize" : NumberLong(261120),
//  "md5" : "6a1d15e0ee9d407588522cca499e704b" } // FRONTEND/PHP

// sprawdzanie tasków: rabbitmqadmin get queue=tasks

?>

