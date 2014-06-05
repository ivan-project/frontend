<?php
class Zend_View_Helper_InputCheckbox {
	function InputCheckbox($name, $placeholder, $datas, $checks) {
        $view = Zend_Layout::getMvcInstance()->getView();
        $lis = "";
        if(!is_array($checks)) $checks = array();
        foreach($datas as $key=>$val) {
        	$checked = in_array($key, $checks) ? 'checked' : '';
        	$lis .= "<li>
						<div>{$val}</div>
						<input type='checkbox' name='{$name}[]' value='{$key}' {$checked}>
					</li>";
        }
					
        echo "<div class='select-checkbox' style='margin-bottom: 30px;' data-select-checkbox>
				<div class='label'>{$placeholder}</div>
				<ul>{$lis}</ul>
			</div>";
	}
}
?>