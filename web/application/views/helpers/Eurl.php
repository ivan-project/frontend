<?php
class Zend_View_Helper_Eurl {
    function eurl($path='') {
        $view = Zend_Layout::getMvcInstance()->getView();
        echo $view->Url($path);
    }
}
?>