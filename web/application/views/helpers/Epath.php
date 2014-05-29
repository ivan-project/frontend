<?php
class Zend_View_Helper_Epath {
    function epath($path='') {
        $view = Zend_Layout::getMvcInstance()->getView();
        echo $view->Path($path);
    }
}
?>