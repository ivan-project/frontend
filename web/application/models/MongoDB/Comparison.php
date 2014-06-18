<?php

class Application_Model_MongoDB_Comparison extends Application_Model_MongoDB_Abstract
{
    protected $_collection = 'comparisons';
    public function destroyByDocumentId($id) {
        $this->c()->remove(
            array('compared' => new MongoId($id))
        );
    }
}

?>

