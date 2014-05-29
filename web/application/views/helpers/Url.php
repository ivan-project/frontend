<?php
class Zend_View_Helper_Url {
    function url($path='') {
    	if(substr($path,0,1)=="#") {
    		if($path=="#") {
				return "#_";
			} else {
				return $path;
			}
    	} else {
	        $view = Zend_Layout::getMvcInstance()->getView();
	        return $view->baseUrl($view->c_lang.$path);
    	}
    }
}
?>