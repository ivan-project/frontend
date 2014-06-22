<?php
class Zend_View_Helper_StatusInfo {
    function statusInfo($percent, $tt_simple=false, $fast=false, $status=30) {
        $status = (int)$status;
        $view = Zend_Layout::getMvcInstance()->getView();
        $ttime = $fast ? 0 : 500;
        $info = 'ok';
        $perc_info = '';
        $ttip = '';

        if($status>=30) {
            if($percent >= $view->config_['appconfig_warning']) $info = 'warning';
            if($percent >= $view->config_['appconfig_alert']) $info = 'alert';
            if(!$tt_simple) $perc_info = "<span>".$percent."</span>";
            $ttip = $percent.'%';
            
            switch($info) {
            	case 'alert': 
            		if(!$tt_simple) $ttip = 'Plagiat! ('.$percent.'% podobieństwa)';
            		echo '<div class="status alert" data-ttip="'.$ttip.'" data-ttip-time="'.$ttime.'">'.$view->svg('graphics/icons/document-alert.svg', true).$perc_info.'</div>';
            		break;
            	case 'warning': 
            		if(!$tt_simple) $ttip = 'Możliwy plagiat ('.$percent.'%)';
            		echo '<div class="status warning" data-ttip="'.$ttip.'" data-ttip-time="'.$ttime.'">'.$view->svg('graphics/icons/document-warning.svg', true).$perc_info.'</div>';
            		break;
            	case 'ok': 
            	default: 
            		if(!$tt_simple) $ttip = 'Nie wykryto plagiatu';
            		echo '<div class="status ok" data-ttip="'.$ttip.'" data-ttip-time="'.$ttime.'">'.$view->svg('graphics/icons/document-ok.svg', true).$perc_info.'</div>';
            		break;
            }
        } else {
            if($status==0) {
                $ttip = 'Oczekuje na przetworzenie';
            } else if($status==10) {
                $ttip = 'Oczekuje na lematyzacje';
            } else if($status==20) {
                $ttip = 'W trakcie sprawdzania';
            }
            echo '<div class="status loader" data-ttip="'.$ttip.'" data-ttip-time="'.$ttime.'"><img src="'.$view->url('assets/graphics/icons/loader.gif').'" alt="">'.$perc_info.'</div>';
        }
    }
}
?>