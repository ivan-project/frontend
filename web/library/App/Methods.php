<?php

class App_Methods 
{
	public static function randomStr($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}
	public static function makeAvatar($img_from, $img_to, $desired = 128) {
		$thumb = new Imagick($img_from);
		/*$attrs = @getimagesize($img_from);
        $w = $attrs[0];
        $h = $attrs[1];
		if($w>$h) {
        	$thumb->scaleImage(0,128);
		} else {
        	$thumb->scaleImage(128,0);
		}*/
		$thumb->cropThumbnailImage($desired, $desired);
        $thumb->writeImage($img_to);
        $thumb->destroy(); 
        unlink($img_from);
	}
    public static function ext($file_name) {
        return strtolower(substr(strrchr($file_name,'.'),1));
    }
}

?>