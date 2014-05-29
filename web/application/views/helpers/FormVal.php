<?php
class Zend_View_Helper_FormVal {
    function formVal($val) {
        $view = Zend_Layout::getMvcInstance()->getView();
        if(isset($view->form[$val])) echo $view->form[$val];
    }
}
?>