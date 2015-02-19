<?php

/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2012 Gekosale. Zabronione jest usuwanie informacji o licencji i autorach.
 *
 * This library is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version. 
 * 
 * 
 * $Revision: 627 $
 * $Author: gekosale $
 * $Date: 2012-01-20 23:05:57 +0100 (Pt, 20 sty 2012) $
 * $Id: index.php 627 2012-01-20 22:05:57Z gekosale $ 
 */

ini_set('display_errors', true);
(defined('E_DEPRECATED')) ? error_reporting(E_ALL & ~ E_DEPRECATED & ~ E_STRICT) : error_reporting(E_ALL);
$__LOCAL_CATALOG = '';
$__SERVER_DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
if (substr($_SERVER['DOCUMENT_ROOT'], - 1) == '/'){
	$__SERVER_DOCUMENT_ROOT = substr($_SERVER['DOCUMENT_ROOT'], 0, - 1);
}
else{
	$__SERVER_DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
}

$__SCRIPT_FILENAME = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : str_replace($__SERVER_DOCUMENT_ROOT, '', $_SERVER['SCRIPT_FILENAME']);
if (($indexPosition = strpos($__SCRIPT_FILENAME, '/index.php')) > 0){
	$__LOCAL_CATALOG = substr($_SERVER['REQUEST_URI'], 0, $indexPosition);
	if (strpos($__LOCAL_CATALOG, '/') == 0){
		$__LOCAL_CATALOG = substr($__LOCAL_CATALOG, 1);
	}
}

if (strlen($__LOCAL_CATALOG) > 0){
	if (substr($__LOCAL_CATALOG, - 2) == '//'){
		$__LOCAL_CATALOG = substr($__LOCAL_CATALOG, 0, - 1);
	}
}
DEFINE('LOCAL_CATALOG', $__LOCAL_CATALOG);
DEFINE('__ENABLE_DEBUG__', 1);
DEFINE('DS', DIRECTORY_SEPARATOR);
DEFINE('ROOTPATH', dirname(__FILE__) . DS);
DEFINE('__PHPMAILER_CLASS__', ROOTPATH . 'lib' . DS . 'phpmailer' . DS);
DEFINE('__IMAGE_CLASS__', ROOTPATH . 'lib' . DS . 'imageGD' . DS);
DEFINE('__DISPATCHER_CLASS__', ROOTPATH . 'lib' . DS . 'dispatcher' . DS);
set_include_path(ROOTPATH . 'lib' . DS . PATH_SEPARATOR . get_include_path());
include_once (ROOTPATH . 'lib' . DS . 'xajax' . DS . 'xajax_core' . DS . 'xajax.inc.php');
date_default_timezone_set('Europe/Warsaw');
require_once ROOTPATH . 'lib' . DS . 'Symfony' . DS . 'Component' . DS . 'ClassLoader' . DS . 'UniversalClassLoader.php';
use Symfony\Component\ClassLoader\UniversalClassLoader;
$loader = new UniversalClassLoader();
$loader->register();
$loader->registerNamespaces(array(
	'Symfony\\Component\\HttpFoundation' => ROOTPATH . 'lib',
	'Symfony\\Component\\Routing' => ROOTPATH . 'lib',
	'Gekosale' => ROOTPATH . 'application',
	'FormEngine' => ROOTPATH . 'lib',
	'SimpleForm' => ROOTPATH . 'lib',
	'Doctrine' => ROOTPATH . 'lib',
	'PasswordHash' => ROOTPATH . 'lib'
));
Gekosale\Autoloader::register();

try{
	Gekosale\App::Run();
}
catch (Exception $e){
	echo $e->getMessage();
	die();
}
