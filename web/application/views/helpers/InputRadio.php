<?php
class Zend_View_Helper_InputRadio {
	function InputRadio($name, $placeholder, $datas, $check) {
        $view = Zend_Layout::getMvcInstance()->getView();
        $lis = "";
        foreach($datas as $key=>$val) {
        	$checked = strcmp($key, $check)==0 ? 'checked' : '';
        	if(is_array($val)) {
	        	$lis .= "<li class='{$val[1]}'>
							<div><span>{$val[0]}</span>{$view->svg($val[2], true)}</div>
							<input type='radio' name='{$name}' value='{$key}' {$checked}>
						</li>";
        	} else {
	        	$lis .= "<li>
							<div><span>{$val}</span></div>
							<input type='radio' name='{$name}' value='{$key}' {$checked}>
						</li>";
        	}
        }
					
        echo "<div class='select-radio' data-select-radio='radio' style='margin-bottom: 30px;'>
				<div class='label' data-placeholder='{$placeholder}'><span>{$placeholder}</span> {$view->svg('graphics/icons/dropdown.svg', true)}</div>
				<div class='options'>
					<ul>{$lis}</ul>
				</div>
			</div>";
	}
}
?>