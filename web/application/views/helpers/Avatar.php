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
		/*
		$uri = 'http://www.gravatar.com/avatar/' . md5( strtolower( trim( $email ) ) ) . '?d=404';
		$headers = @get_headers($uri);
		if (!preg_match("|200|", $headers[0])) {
	    	$name = strtolower($user['name']);
	    	$pieces = explode(' ', $name);
			$last = $name;//array_pop($pieces);
			return $view->baseUrl('assets/graphics/avatar/backgrounds/'.substr($last, 0, 1).'.png');
			//return $view->baseUrl('assets/graphics/avatar/user.png');
		} else {
			return "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?s=" . $size;
		}
		*/
	}
}
?>