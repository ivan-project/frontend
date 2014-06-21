<?php

class Application_Model_MongoDB_Comparison extends Application_Model_MongoDB_Abstract
{
    protected $_collection = 'comparisons';
    public function destroyByDocumentId($id) {
        $this->c()->remove(
            array('compared' => new MongoId($id))
        );
    }
    public function getByDocument($id, $limit=false) {
        $object = $this->c()->find(
            array('compared' => new MongoId($id))
        )->sort(array('result.percentageSimilarity' => -1));
        if($limit) {
            $object = $object->limit(1);
            $object->next();
            $object = $object->current();
        }
        return $object;
    }
    public function getByDocumentsFull($id, $id_c) {
        $object = $this->c()->find(
            array(
                '$and' => array(
                    array('compared' => new MongoId($id)),
                    array('compared' => new MongoId($id_c))
                )
            )
        )->limit(1);
        if($object->count()>0) {
            $object->next();
            $object = $object->current();
            return $object;
        } else {
            return false;
        }
    }
    public function getStatsDocument($id, $percent, $reverse=false) {
        if($reverse) {
            $count = $this->c()->find(
                array(
                    '$or' => array(
                        array(
                            'compared' => new MongoId($id),
                            'result.percentageSimilarity' => array(
                                '$lt' => (int)$percent
                            )
                        ),
                        array(
                            'compared' => new MongoId($id),
                            'result.percentageSimilarity' => array(
                                '$exists' => false
                            )
                        )
                    )
                )
            );
            return $count->count();
        } else {
            $count = $this->c()->find(
                array(
                    'compared' => new MongoId($id),
                    'result.percentageSimilarity' => array(
                        '$gte' => (int)$percent
                    )
                )
            );
            return $count->count();
        }
    }
}

?>

