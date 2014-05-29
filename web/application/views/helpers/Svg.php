<?php
class Zend_View_Helper_Svg {
    function svg($path='') {
        echo file_get_contents('assets/'.$path);
    }
}
?>