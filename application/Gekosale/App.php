<?php

/**
     * Gekosale, Open Source E-Commerce Solution
     * http://www.gekosale.pl
     *
     * Copyright (c) 2008-2013 WellCommerce sp. z o.o.. Zabronione jest usuwanie informacji o
     * licencji i autorach.
     *
     * This library is free software; you can redistribute it and/or
     * modify it under the terms of the GNU Lesser General Public
     * License as published by the Free Software Foundation; either
     * version 2.1 of the License, or (at your option) any later version.
     *
     *
     * $Revision: 438 $
     * $Author: gekosale $
     * $Date: 2011-08-27 11:29:36 +0200 (So, 27 sie 2011) $
     * $Id: app.class.php 438 2011-08-27 09:29:36Z gekosale $
     */
namespace Gekosale;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xajax;

class App
{
	protected static $URI = Array();
	protected static $registry;
	protected static $request;
	protected static $config;

	public static function getModel ($index)
	{
		if (is_object(self::$registry->$index)){
			return self::$registry->$index;
		}
		if (strpos($index, 'Model') === false){
			$indexModel = $index . 'Model';
			if (is_object(self::$registry->$indexModel)){
				return self::$registry->$indexModel;
			}
		}
		try{
			return self::$registry->router->modelLoader($index);
		}
		catch (Exception $e){
			throw new CoreException($e->getMessage());
		}
	}

	public static function getFormModel ($index)
	{
		if (is_object(self::$registry->$index)){
			return self::$registry->$index;
		}
		if (strpos($index, 'Form') === false){
			$indexModel = $index . 'Form';
			if (is_object(self::$registry->$indexModel)){
				return self::$registry->$indexModel;
			}
		}
		try{
			return self::$registry->router->formModelLoader($index);
		}
		catch (Exception $e){
			throw new CoreException($e->getMessage());
		}
	}

	public static function redirect ($path = false)
	{
		if ($path == false){
			header('Location: ' . self::getURLAdress());
		}
		else{
			header('Location: ' . self::getURLAdress() . $path);
		}
		die();
	}

	public static function redirectSeo ($url)
	{
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $url);
		header('Connection: Close');
		die();
	}

	/**
	 * Redirect to specified URL
	 *
	 * @param string $url        	
	 */
	public static function redirectUrl ($url)
	{
		header('Location: ' . $url);
		header('Connection: Close');
		die();
	}

	public static function setRequest ()
	{
		$_SERVER['REQUEST_URI'] = rtrim($_SERVER['REQUEST_URI'], '/');
		self::$request = Request::createFromGlobals();
	}

	public static function getRequest ()
	{
		return self::$request;
	}

	public static function setUrl ()
	{
		$server_protocol = explode('/', self::$request->server->get('SERVER_PROTOCOL'));
		self::$URI = Array(
			'protocol' => strtolower($server_protocol[0]),
			'protocol_ver' => $server_protocol[1],
			'host' => self::$request->server->get('HTTP_HOST'),
			'script_name' => self::$request->server->get('SCRIPT_NAME'),
			'script' => self::$request->server->get('REQUEST_URI')
		);
	}

	public static function checkSSL ()
	{
		// $sslpages = array(
		// 'clientlogin',
		// 'registrationcart',
		// 'cart',
		// 'forgotpassword',
		// 'contact'
		// );
		
		// if (in_array(self::$registry->router->getCurrentController(),
		// $sslpages)){
		// if (! isset($_SERVER['HTTPS']) && SSLNAME == 'https'){
		// App::setHttps();
		// App::redirect(self::$registry->core->getControllerNameForSeo(self::$registry->router->getCurrentController()));
		// }
		// }
		// if (self::$registry->router->getCurrentController() == 'payment' &&
		// self::$registry->router->getAction() == 'accept'){
		// if (! isset($_SERVER['HTTPS']) && SSLNAME == 'https'){
		// App::setHttps();
		// App::redirect('payment/accept');
		// }
		// }
	}

	public static function getHost ($setProtocol = NULL)
	{
		if (! isset(self::$URI['host']))
			return 'cli';
		
		$host = self::$URI['host'];
		if (substr($host, - 2) == '//'){
			$host = substr($host, 0, - 1);
		}
		if ($setProtocol !== NULL){
			return strtolower(self::$URI['protocol']) . '://' . $host;
		}
		
		return $host;
	}

	public static function setHttps ()
	{
		self::$URI['protocol'] = SSLNAME;
	}

	public static function setHttp ()
	{
		self::$URI['protocol'] = 'http';
	}

	public static function getHttps ()
	{
		return self::$URI['protocol'];
	}

	public static function getURLAdress ()
	{
		if (LOCAL_CATALOG != ''){
			return App::getHost(1) . '/' . LOCAL_CATALOG . '/';
		}
		
		return App::getHost(1) . '/';
	}

	public static function getCurrentURLAdress ()
	{
		return (isset($_SERVER['SCRIPT_URI'])) ? $_SERVER['SCRIPT_URI'] : $_SERVER['PHP_SELF'];
	}

	public static function getURLAdressWithAdminPane ()
	{
		return self::getURLAdress() . App::getAdminPaneName();
	}

	public static function getURLForDesignDirectory ()
	{
		if (LOCAL_CATALOG != ''){
			return App::getHost(1) . '/' . LOCAL_CATALOG . '/design/';
		}
		
		return App::getHost(1) . '/design/';
	}

	public static function getURLForAssetDirectory ()
	{
		$host = App::getHost();
		
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'){
			$protocol = 'https://';
		}
		else{
			$protocol = 'http://';
		}
		
		if (LOCAL_CATALOG != ''){
			return $protocol . $host . '/' . LOCAL_CATALOG . '/themes/';
		}
		
		return $protocol . $host . '/themes/';
	}

	public static function getURL ()
	{
		return strtolower(self::$URI['protocol']) . '://' . self::$URI['host'] . self::$URI['script'];
	}

	public function getOfflineMessage ()
	{
		$sql = 'SELECT offlinetext FROM view WHERE idview = :viewid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->execute();
		$rs = $stmt->fetch();
		if ($rs){
			return $rs['offlinetext'];
		}
	}

	public static function getConfig ($node = '')
	{
		if ('' === $node){
			return self::$config;
		}
		
		return isset(self::$config[$node]) ? self::$config[$node] : self::$config;
	}

	/**
	 * Do some basic application inits
	 */
	public static function init ()
	{
		if (! @include_once (ROOTPATH . 'config' . DS . 'settings.php')){
			App::setRequest();
			App::setUrl();
			include (ROOTPATH . 'includes' . DS . 'install.php');
			die();
		}
		
		self::$registry = new Registry();
		@include_once ROOTPATH . 'config' . DS . 'settings.php';
		self::$config = $Settings;
		if (empty(self::$config)){
			App::setRequest();
			App::setUrl();
			self::$registry->cache = new Cache(new Cache\File());
			self::$registry->router = new Router(self::$registry);
			self::$registry->loader = new Loader(self::$registry, false);
		}
		else{
			if (! self::isCli()){
				App::setRequest();
				DEFINE('SSLNAME', (isset(self::$config['ssl']) && self::$config['ssl'] == 1) ? 'https' : 'http');
				DEFINE('__ADMINPANE__', self::$config['admin_panel_link']);
				App::setUrl();
			}
			
			if (! empty(self::$config['memcache']) && self::$config['memcache']['active'] == 1){
				self::$registry->cache = new Cache(new Cache\Memcache(self::$config['memcache']));
			}
			else{
				self::$registry->cache = new Cache(new Cache\File());
			}
			
			self::$registry->router = new Router(self::$registry);
			if (! self::isCli()){
				self::$registry->session = new Session(self::$registry);
			}
			
			self::$registry->loader = new Loader(self::$registry);
			
			if (! self::isCli()){
				Session::setActiveEncryptionKeyValue((string) self::$config['client_data_encription_string']);
			}
			
			self::$registry->core = new Core(self::$registry);
			
			Translation::loadTranslations();
			
			App::getModel('csrfprotection');
		}
	}

	/**
	 * Check if application is runing in CLI mode - for example command line
	 * tools
	 */
	public static function isCli ()
	{
		if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])){
			return true;
		}
		else{
			return false;
		}
	}

	public static function Run ()
	{
		if (isset(self::$config['profiler']) && self::$config['profiler'] == $_SERVER['REMOTE_ADDR']){
			Profiler::start();
		}
		App::init();
		self::$registry->router->setVariables();
		self::$registry->xajax = new Xajax();
		if (self::$registry->router->getMode() == 0){
			$url = preg_replace_callback('~[^a-zA-Z0-9:/\?,_-]+~', function  ($s)
			{
				return urlencode($s[0]);
			}, self::$registry->router->getUri());
			self::$registry->xajax->configure('requestURI', $url);
		}
    else { // force language in admin mode
      Session::unsetActiveLanguage();
      self::$registry->core->setLanguage('pl');
			Translation::loadTranslations();
    }
		self::$registry->xajaxInterface = new XajaxInterface();
		
		self::$registry->right = new Right(self::$registry);
		Session::clearTemp();
		DEFINE('URL', App::getHost(1) . '/' . LOCAL_CATALOG);
		App::checkSSL();
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'){
			DEFINE('DESIGNPATH', str_replace('http://', 'https://', App::getURLForDesignDirectory()));
		}
		else{
			DEFINE('DESIGNPATH', App::getURLForDesignDirectory());
		}
		
		// remember login and password
		if (self::$registry->router->getModeFromRoute() == 'admin'){
			if ((int) Session::getActiveUserid() === 0){
				App::getModel('login')->getAdminAutologinKey();
			}
		}
		else{
			if ((int) Session::getActiveClientid() === 0){
				App::getModel('clientlogin')->getAutologinKey();
			}
		}
		
		self::$registry->template = new Template(self::$registry, self::$registry->router->getMode());
		self::$registry->template->setStaticTemplateVariables();
		$content = self::$registry->router->controllerLoader();
		
		if (isset(self::$config['profiler']) && self::$config['profiler'] == $_SERVER['REMOTE_ADDR']){
			Profiler::stop();
			$content .= Profiler::getInfo();
		}
		$response = new Response($content);
		$response->send();
		
		App::getModel('csrfprotection')->reindex();
	}

	public static function getRegistry ()
	{
		return self::$registry;
	}

	public static function getAdminPaneName ()
	{
		return (self::$registry->router->getMode() != 1) ? '' : self::$registry->router->getAdminPaneName();
	}
}
