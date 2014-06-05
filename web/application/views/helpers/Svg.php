<?php
class Zend_View_Helper_Svg {
    function svg($path='', $r=false) {
    	if($r) {
        	return file_get_contents('assets/'.$path);
    	} else {
        	echo file_get_contents('assets/'.$path);
    	}
    }
}
?>