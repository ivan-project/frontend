<?php
class Zend_View_Helper_Avatar {
	function avatar($user) {
        $view = Zend_Layout::getMvcInstance()->getView();

        $size = 128;
		$email = $user['email'];
		
		$uri = 'http://www.gravatar.com/avatar/' . md5( strtolower( trim( $email ) ) ) . '?d=404';
		$headers = @get_headers($uri);
		if (!preg_match("|200|", $headers[0])) {
	    	$name = strtolower($user['name']);
	    	$pieces = explode(' ', $name);
			$last = array_pop($pieces);
			return $view->baseUrl('assets/graphics/avatar/backgrounds/'.substr($last, 0, 1).'.png');
		} else {
			return "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?s=" . $size;
		}
	}
    /*function avatar($user) {
        $view = Zend_Layout::getMvcInstance()->getView();
    	$path = 'assets/contents/users/'.$user['_id'].'/avatar.png';

    	if(!file_exists($path)) {
	    	$name = strtolower($user['name']);
	    	$pieces = explode(' ', $name);
			$last = array_pop($pieces);
			$letter = substr($last, 0, 1);
    		$path = 'assets/graphics/avatar/backgrounds/'.$letter.'.png';
    	}
    	return $view->baseUrl($path);
    }*/
}
?>