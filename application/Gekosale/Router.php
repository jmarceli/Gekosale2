<?php

/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2012 Gekosale. Zabronione jest usuwanie informacji o
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
 * $Id: router.class.php 438 2011-08-27 09:29:36Z gekosale $
 */
namespace Gekosale;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\TraceableUrlMatcher;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegExIterator;
use FilesystemIterator;

class Router
{
	const FRONTEND_PANE = 'Frontend';
	const ADMIN_PANE = 'Admin';
	const SUPER_PANE = 'Super';
	protected $registry;
	protected $path;
	public $modelFile;
	protected $model;
	protected $action = 'index';
	protected $param = Array();
	protected $parsedURL;
	protected $adminitrativeMode = 0;
	protected $_adminPane = '';
	protected $exceptionModel;
	protected $baseController;
	protected $baseControllerFullName;
	protected $context;
	protected $route;
	protected $routes;
	protected $request;
	protected $matcher;
	protected $generator;
	protected $classesMap = FALSE;

	public function __construct ($registry)
	{
		$this->registry = $registry;
		$this->path = ROOTPATH . 'plugin';
		$this->pathExtended = ROOTPATH . 'plugin';
		
		$classmapPath = ROOTPATH . 'serialization' . DS . 'classesmap.reg';
		
		$this->classesMap = @ file_get_contents($classmapPath);
		
		if (PHP_SAPI == 'cli' || strlen($this->classesMap) < 100){
			$dir = new RegExiterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path, FilesystemIterator::FOLLOW_SYMLINKS)), '~.+\.php\z~');
			
			$this->classesMap = array();
			$pathLen = strlen($this->path) + 1;
			foreach ($dir as $file){
				if ($file->isFile() && ! preg_match('~migrate_\d+\z~', $file->getBasename('.php'))){
					$this->classesMap[substr($file->getPathname(), $pathLen, - 4)] = $file->getPathname();
				}
			}
			
			if (PHP_SAPI != 'cli'){
				file_put_contents($classmapPath, serialize($this->classesMap), LOCK_EX);
			}
		}
		else{
			$this->classesMap = unserialize($this->classesMap);
		}
	}

	public function controllerLoader ()
	{
		$namespaces = $this->registry->loader->getNamespaces();
		$mode = $this->getModeName();
		
		if ($this->route['mode'] == 'admin' && Session::getActiveUserid() > 0 && $this->baseController == 'login'){
			App::redirect(__ADMINPANE__ . '/mainside');
		}
		
		if ($this->route['mode'] == 'admin' && NULL == Session::getActiveUserid() && $this->baseController != 'login'){
			App::redirect(__ADMINPANE__);
		}
		
		if ($this->route['mode'] != 'admin' && $this->registry->loader->getParam('forcelogin') == 1 && NULL == Session::getActiveClientid() && ! in_array($this->baseController, array(
			'clientlogin',
			'forgotpassword',
			'registration'
		))){
			App::redirectUrl($this->generate('frontend.clientlogin', true));
		}
		if ($this->getAdministrativeMode() && ! in_array($this->baseController, array(
			'logout',
			'permissionerror'
		)) && in_array($this->action, array_flip($this->registry->right->getRights()))){
			try{
				$this->registry->right->checkPermission($this->baseController, $this->action, App::getModel('users')->getLayerIdByViewId(Helper::getViewId()));
			}
			catch (Exception $e){
				App::redirect(__ADMINPANE__ . '/permissionerror');
			}
		}
		
		$lastNs = '';
		foreach ($namespaces as $namespace){
			$ns = $namespace . DS . $mode . DS . strtolower($this->baseController . DS . 'controller' . DS . $this->baseController);
			if (isset($this->classesMap[$ns])){
				require_once $this->classesMap[$ns];
				$lastNs = $namespace;
			}
		}
		
		if (! empty($lastNs)){
			$controllerFullName = $lastNs . '\\' . $this->baseController . 'Controller';
			$controllerObject = new $controllerFullName($this->registry);
			$controllerObject->setDesignPath(strtolower($this->baseController . DS . $this->action . DS));
		}
		if (isset($controllerObject) && is_object($controllerObject) && is_callable(Array(
			$controllerObject,
			$this->action
		))){
			// force offline
			if (! ($controllerObject instanceof \Gekosale\ErrorController) && ! ($controllerObject instanceof \Gekosale\InstanceReportController) && $this->route['mode'] != 'admin' && $this->registry->loader->isOffline() && ! (Session::getActiveUserid() > 0)){
				require_once ROOTPATH . 'plugin' . DS . 'Gekosale' . DS . 'Frontend' . DS . 'offline' . DS . 'controller' . DS . 'offline.php';
				$controllerObject = new OfflineController($this->registry);
				$controllerObject->setDesignPath(strtolower('offline' . DS . 'index' . DS));
				$this->action = 'index';
			}
			
			$content = call_user_func(Array(
				$controllerObject,
				$this->action
			));
			$response = new Response($content);
			$response->send();
		}
		else{
			
			$exception = new \Gekosale\Router\InvalidUrlException('Niepoprawny adres: ' . App::getUrl());
			
			if ($this->route['mode'] == 'admin'){
				App::redirectUrl($this->generate('admin', true, Array(
					'controller' => 'error',
					'param' => '404'
				)));
			}
		}
	}

	public function modelLoader ($name = NULL, $includeOnly = false)
	{
		$name = explode('/', $name);
		$mode = null;
		
		if (isset($name[2])){
			$mode = $name[0];
			$controller = $name[1];
			$model = $name[2];
		}
		else 
			if (isset($name[1])){
				$controller = $name[0];
				$model = $name[1];
			}
			else{
				$controller = $this->baseController;
				$model = $name[0];
			}
		$modelFile = $this->getModelFile($controller, $model, $mode);
		if ($modelFile === array()){
			return false;
		}
		
		if (count($modelFile) === 2){
			$file = array_shift($modelFile);
			require_once $file;
		}
		
		include_once current($modelFile);
		
		if ($includeOnly){
			return;
		}
		
		$class = key($modelFile);
		
		if (in_array($class, $this->classesMap)){
			return;
		}
		
		$objClassName = end(explode(DS, $class)) . '/' . $model . 'Model';
		if (class_exists($class)){
			try{
				return $this->registry->$objClassName = new $class($this->registry, current($modelFile));
			}
			catch (Exception $e){
				throw new CoreException($e->getMessage());
			}
		}
		else{
			throw new CoreException('Class doesn\'t exists: ' . $class);
		}
	}

	protected function getModelFile ($controller, $model, $mode = null)
	{
		if (! $mode){
			$mode = $this->getModeName();
		}
		
		$modes = array(
			$mode,
			$mode = self::SUPER_PANE
		);
		
		$files = array();
		
		foreach ($this->registry->loader->getNamespaces() as $ns){
			foreach ($modes as $mode){
				$path = $this->path . DS . $ns . DS . $mode . DS . strtolower($model) . DS . 'model' . DS . strtolower($model) . '.php';
				
				if (in_array($path, $this->classesMap)){
					$files['\\' . $ns . '\\' . $model . 'Model'] = $path;
					break;
				}
				
				$path = $this->path . DS . $ns . DS . $mode . DS . strtolower($controller) . DS . 'model' . DS . strtolower($model) . '.php';
				
				if (in_array($path, $this->classesMap)){
					$files['\\' . $ns . '\\' . $model . 'Model'] = $path;
					break;
				}
			}
		}
		
		return $files;
	}

	public function formModelLoader ($name = NULL)
	{
		$name = explode('/', $name);
		if (isset($name[1])){
			$controller = $name[0];
			$model = $name[1];
		}
		else{
			$controller = $this->baseController;
			$model = $name[0];
		}
		$modelFile = $this->getFormModelFile($controller, $model);
		if ($modelFile === array()){
			return false;
		}
		
		if (count($modelFile) === 2){
			$file = array_shift($modelFile);
			require_once $file;
		}
		
		require_once current($modelFile);
		
		$class = key($modelFile);
		
		if (in_array($class, $this->classesMap)){
			return;
		}
		
		$objClassName = end(explode(DS, $class)) . '/' . $model . 'Form';
		if (class_exists($class)){
			try{
				return $this->registry->$objClassName = new $class($this->registry, current($modelFile));
			}
			catch (Exception $e){
				throw new CoreException($e->getMessage());
			}
		}
		else{
			throw new CoreException('Class doesn\'t exists: ' . $class);
		}
	}

	protected function getFormModelFile ($controller, $model)
	{
		$mode = $this->getModeName();
		
		$files = array();
		
		foreach ($this->registry->loader->getNamespaces() as $ns){
			$path = $this->path . DS . $ns . DS . $mode . DS . strtolower($model) . DS . 'form' . DS . strtolower($model) . '.php';
			
			if (in_array($path, $this->classesMap)){
				$files['\\' . $ns . '\\' . $model . 'Form'] = $path;
				continue;
			}
			
			$path = $this->path . DS . $ns . DS . $mode . DS . strtolower($controller) . DS . 'form' . DS . strtolower($model) . '.php';
			
			if (in_array($path, $this->classesMap)){
				$files['\\' . $ns . '\\' . $model . 'Form'] = $path;
				continue;
			}
		}
		
		return $files;
	}

	public function setVariables ()
	{
		$this->request = App::getRequest();
		$this->setRoutes();
		$this->context = new RequestContext();
		
		$this->context->fromRequest($this->request);
		$this->matcher = new UrlMatcher($this->routes, $this->context);
		$this->generator = new UrlGenerator($this->routes, $this->context);
		try{
			$this->route = $this->matcher->match($this->request->getPathInfo());
		}
		catch (\Exception $e){
			
			$this->checkSSLPattern();
			
			$exception = new \Gekosale\Router\InvalidUrlException('Niepoprawny adres: ' . App::getUrl());
			
			header('HTTP/1.1 404 Not Found');
			
			$this->route = Array(
				'controller' => 'error',
				'action' => 'index',
				'param' => 404,
				'mode' => $this->route['mode']
			);
		}
		$this->baseController = $this->route['controller'];
		$this->baseControllerFullName = 'Gekosale\\' . ucfirst($this->baseController) . 'Controller';
		$this->setAction($this->route['action']);
		$this->setParams($this->route['param']);
		$this->setAdministrativeMode($this->route['mode'] == 'admin' ? 1 : 0);
	}

	protected function checkSSLPattern ()
	{
		$trace = new TraceableUrlMatcher($this->routes, $this->context);
		$paths = $trace->getTraces($this->request->getPathInfo());
		foreach ($paths as $path){
			if ($path['level'] == 1){
				App::redirectUrl($this->generate($path['name'], true));
			}
		}
	}

	public function getUri ()
	{
		return $this->request->getUri();
	}

	public function generate ($route, $absolute = false, $params = Array())
	{
		return $this->generator->generate($route, $params, $absolute);
	}

	public function getParamFromRoute ($param, $defaultValue)
	{
		return isset($this->route[$param]) ? $this->route[$param] : $defaultValue;
	}

	public function getRoutes ()
	{
		return $this->routes;
	}

	public function getRoute ()
	{
		return $this->route;
	}

	public function getModeFromRoute ()
	{
		return $this->route['mode'];
	}

	public function setRoutes ()
	{
		$namespaces = $this->registry->loader->getNamespaces();
		foreach ($namespaces as $namespace){
			$path = ROOTPATH . 'config' . DS . ucfirst($namespace) . DS . 'routes.php';
			if (is_file($path)){
				require $path;
			}
		}
	}

	public function getGenerator ()
	{
		return $this->generator;
	}

	public function getAdministrativeMode ()
	{
		return $this->adminitrativeMode;
	}

	protected function setAdministrativeMode ($value = 0)
	{
		$this->adminitrativeMode = (0 == $value || NULL == Session::getActiveUserid()) ? 0 : 1;
	}

	public function getModeName ()
	{
		return ($this->adminitrativeMode == 1) ? self::ADMIN_PANE : self::FRONTEND_PANE;
	}

	public function getMode ()
	{
		return $this->adminitrativeMode;
	}

	public function getAdminPaneName ()
	{
		return __ADMINPANE__ . '/';
	}

	public function getParams ()
	{
		return $this->param;
	}

	public function setParams ($params)
	{
		$this->param = $params;
	}

	public function getCurrentController ()
	{
		return (isset($this->baseController) && $this->baseController != '') ? $this->baseController : NULL;
	}

	public function setCurrentController ($controller)
	{
		$this->baseController = $controller;
	}

	public function getAction ()
	{
		return (isset($this->action) && $this->action != '') ? $this->action : 'index';
	}

	public function setAction ($action)
	{
		$this->action = $action;
	}

	/**
	 * Return URL address
	 *
	 * @param string $route
	 *        	route name - for example "admin"
	 * @param string $controller
	 *        	controller name to override in route
	 * @param string $param
	 *        	param to override in route
	 * @param string $action
	 *        	action name
	 */
	public function url ($route, $controller, $action = 'index', $param = null)
	{
		return $this->generator->generate($route, array(
			'controller' => $controller,
			'action' => $action,
			'param' => $param
		));
	}
}
