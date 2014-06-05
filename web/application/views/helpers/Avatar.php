<?php
class Zend_View_Helper_Avatar {
	function avatar($user) {
        $view = Zend_Layout::getMvcInstance()->getView();

        $size = 128;
		$email = $user['email'];
		if(file_exists('assets/contents/users/'.$user['_id'].'/avatar.png')) {
			return $view->baseUrl('assets/contents/users/'.$user['_id'].'/avatar.png?'.microtime(true));
		} else {
			return $view->baseUrl('assets/graphics/avatar/backgrounds/'.substr(strtolower($user['name']), 0, 1).'.png');
		}
	}
}
?>