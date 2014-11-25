<?php
/**
 * 
 *  Copyright 2013 Aktsiamaailm OÜ
 *  Litsentsitingimused on saadaval http://www.e-abi.ee/litsents.
 *  

 */
$_GET['fc'] = 'module';
$_GET['module'] = 'banklinkmaksekeskus';
$_GET['controller'] = 'return';
foreach ($_SERVER as $k => $v) {
	$_SERVER[$k] = str_replace('/modules/banklinkmaksekeskus/validatePayment', '/index', $_SERVER[$k]);
}
chdir(dirname(dirname(dirname(__FILE__))));
require_once('index.php');
