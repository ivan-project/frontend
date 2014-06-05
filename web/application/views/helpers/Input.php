<?php
class Zend_View_Helper_Input {
	function Input($type, $name, $placeholder='', $value='', $required=false, $classes='', $autofocus=false) {
        $view = Zend_Layout::getMvcInstance()->getView();
        $required = $required ? "required" : "";
        $autofocus = $autofocus ? "autofocus" : "";
        switch($type) {
        	case "document":
		        echo "<div class='file-browse' data-file-browse='true'>
					<input type='file' name='{$name}' accept='application/pdf,application/msword' data-file-trigger {$required}>
				    <div class='txt' data-placeholder='{$placeholder}'><span>{$placeholder}</span>{$view->svg('graphics/icons/document-create.svg',true)}</div>
				    <div class='overview document'></div>
				    <a></a>
					</div>";
				break;
			case "image":
		        echo "<div class='file-browse file-avatar' data-file-browse='true'>
					<input type='file' name='{$name}' accept='image/gif,image/jpeg,image/png' data-file-trigger {$required}>
				    <div class='txt photo' data-placeholder='{$placeholder}'><span>{$placeholder}</span>{$view->svg('graphics/icons/photo.svg',true)}</div>
				    <div class='overview image'></div>
				    <a></a>
					</div>";
				break;
			case "email":
			case "text":
			case "password":
				echo "<input class='{$classes}' type='{$type}' name='{$name}' placeholder='{$placeholder}' value='{$value}' {$required} {$autofocus}/>";
				break;
			case "submit":
			case "button":
				echo "<button class='{$classes}' type='{$type}' name='{$name}' value='{$value}' {$autofocus}>{$placeholder}</button>";
				break;
			default:
				break;
        }
	}
}
?>