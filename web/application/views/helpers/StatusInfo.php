<?php
class Zend_View_Helper_StatusInfo {
    function statusInfo($percent, $tt_simple=false, $fast=false) {
        $view = Zend_Layout::getMvcInstance()->getView();
        $status = 'ok';
		if($percent >= $view->config_['appconfig_warning']) $status = 'warning';
		if($percent >= $view->config_['appconfig_alert']) $status = 'alert';
        $ttip = $percent.'%';
        $ttime = $fast ? 0 : 500;
        $perc_info = "";
        if(!$tt_simple) $perc_info = "<span>".$percent."</span>";
        switch($status) {
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
    }
}
?>