<?php

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('front', 'dev', true);

$accesGranted = false;
$ips = sfConfig::get('app_controllers-access_ips-allowed');

foreach ($ips as $key => $ip) {
    if (substr($_SERVER['REMOTE_ADDR'], 0, strlen($ip)) == $ip) {
        $accesGranted = true;
        break;
    }
}
if ($accesGranted) {
    dm::checkServer();
} else {
	exit('Access denied. Thanks to contact the administrator with your ip adress'.' <b>'.$_SERVER['REMOTE_ADDR'].'</b>');
}




