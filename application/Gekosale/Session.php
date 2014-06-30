<?php

/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2013 WellCommerce sp. z o.o.. Zabronione jest usuwanie informacji o licencji i autorach.
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
 * $Id: session.class.php 438 2011-08-27 09:29:36Z gekosale $
 */
namespace Gekosale;

use Exception;

class Session
{
	protected $__SESSION_LIFETIME;
	protected $__SESSION_HANDLER;
	protected $registry;
	protected $__SESSION_ID;
	protected $isMemcache;

	public function __construct ($registry)
	{
		$this->registry = $registry;
		$config = App::getConfig();
		$this->isMemcache = isset($config['memcache']['active']) ? (boolean) $config['memcache']['active'] : false;
		$this->__SESSION_HANDLER = ini_get('session.save_handler');
		$this->__SESSION_LIFETIME = isset($config['session_gc_maxlifetime']) ? $config['session_gc_maxlifetime'] : ini_get('session.gc_maxlifetime');
		if (! isset($_SESSION)){
			if (isset($_POST[session_name()])){
				session_id($_POST[session_name()]);
			}
			ini_set('session.save_handler', 'user');
			session_set_save_handler(Array(
				&$this,
				'session_open'
			), Array(
				&$this,
				'session_close'
			), Array(
				&$this,
				'session_read'
			), Array(
				&$this,
				'session_write'
			), Array(
				&$this,
				'session_destroy'
			), Array(
				&$this,
				'session_gc'
			));
			register_shutdown_function("session_write_close");
			@session_start();
			$this->__SESSION_ID = session_id();
		}
	}

	public static function __callStatic ($name, $params)
	{
		if (substr($name, 0, 9) == "setActive" && strlen($name) > 9){
			$name = preg_replace('/setActive?/', '', $name);
			$_SESSION['CurrentState'][$name] = $params;
			return (true);
		}
		elseif (substr($name, 0, 9) == "getActive" && strlen($name) > 9){
			$name = preg_replace('/getActive?/', '', $name);
			if (isset($_SESSION['CurrentState'][$name])){
				if (count($_SESSION['CurrentState'][$name]) == 1)
					return ($_SESSION['CurrentState'][$name][0]);
				else 
					if (count($_SESSION['CurrentState'][$name]) > 1)
						return ($_SESSION['CurrentState'][$name]);
					else
						return (null);
			}
		}
		elseif (substr($name, 0, 11) == "setVolatile" && strlen($name) > 11){
			$name = preg_replace('/setVolatile?/', '', $name);
			$_SESSION['CurrentState']['temp'][$name] = $params;
			return (true);
		}
		elseif (substr($name, 0, 11) == "getVolatile" && strlen($name) > 11){
			$name = preg_replace('/getVolatile?/', '', $name);
			if (isset($_SESSION['CurrentState']['temp'][$name])){
				if (count($_SESSION['CurrentState']['temp'][$name]) == 1)
					return ($_SESSION['CurrentState']['temp'][$name][0]);
				else 
					if (count($_SESSION['CurrentState']['temp'][$name]) > 1)
						return ($_SESSION['CurrentState']['temp'][$name]);
					else
						return (null);
			}
		}
		elseif (substr($name, 0, 11) == "unsetActive" && strlen($name) > 11){
			$name = preg_replace('/unsetActive/', '', $name);
			if (isset($_SESSION['CurrentState'][$name])){
				unset($_SESSION['CurrentState'][$name]);
			}
		}
		else{
			throw new Exception('Undefined framework method: ' . $name);
		}
	}

	public function __call ($name, $params)
	{
		if (substr($name, 0, 9) == "setActive" && strlen($name) > 9){
			$name = preg_replace('/setActive?/', '', $name);
			$_SESSION['CurrentState'][$name] = $params;
			return (true);
		}
		elseif (substr($name, 0, 9) == "getActive" && strlen($name) > 9){
			$name = preg_replace('/getActive?/', '', $name);
			if (isset($_SESSION['CurrentState'][$name])){
				if (count($_SESSION['CurrentState'][$name]) == 1)
					return ($_SESSION['CurrentState'][$name][0]);
				else 
					if (count($_SESSION['CurrentState'][$name]) > 1)
						return ($_SESSION['CurrentState'][$name]);
					else
						return (null);
			}
		}
		elseif (substr($name, 0, 11) == "setVolatile" && strlen($name) > 11){
			$name = preg_replace('/setVolatile?/', '', $name);
			$_SESSION['CurrentState']['temp'][$name] = $params;
			return (true);
		}
		elseif (substr($name, 0, 11) == "getVolatile" && strlen($name) > 11){
			$name = preg_replace('/getVolatile?/', '', $name);
			if (isset($_SESSION['CurrentState']['temp'][$name])){
				if (count($_SESSION['CurrentState']['temp'][$name]) == 1)
					return ($_SESSION['CurrentState']['temp'][$name][0]);
				else 
					if (count($_SESSION['CurrentState']['temp'][$name]) > 1)
						return ($_SESSION['CurrentState']['temp'][$name]);
					else
						return (null);
			}
		}
		elseif (substr($name, 0, 11) == "unsetActive" && strlen($name) > 11){
			$name = preg_replace('/unsetActive/', '', $name);
			if (isset($_SESSION['CurrentState'][$name])){
				unset($_SESSION['CurrentState'][$name]);
			}
		}
		else{
			throw new Exception('Undefined framework method: ' . $name);
		}
	}

	public function flush ()
	{
		session_destroy();
		$this->__SESSION_ID = session_id();
		App::getRegistry()->core->setEnvironmentVariables();
	}

	public function getSessionId ()
	{
		return $this->__SESSION_ID;
	}

	public static function clearTemp ()
	{
		if (isset($_SESSION['CurrentState'])){
			if (isset($_SESSION['CurrentState']['temp'])){
				foreach ($_SESSION['CurrentState']['temp'] as $key => $value){
					if (is_array($value)){
						if (isset($value[1]) && $value[1] === true){
							unset($_SESSION['CurrentState']['temp'][$key]);
						}
						else{
							$_SESSION['CurrentState']['temp'][$key][1] = true;
						}
					}
					else{
						if ($value === true){
							unset($_SESSION['CurrentState']['temp'][$key]);
						}
						else{
							$_SESSION['CurrentState']['temp'][$key] = true;
						}
					}
				}
			}
		}
	}

	public function flushTemp ()
	{
		$_SESSION['CurrentState']['temp'] = Array();
	}

	public function killSession ()
	{
		session_destroy();
		$this->__SESSION_ID = session_id();
		App::getRegistry()->core->setEnvironmentVariables();
	}

	public function session_open ()
	{
		return true;
	}

	public function session_close ()
	{
		return true;
	}

	public function session_read ($sessionid)
	{
		if ($this->isMemcache){
			return $this->registry->cache->load('session_' . $sessionid);
		}
		
		$sql = 'SELECT sessioncontent FROM sessionhandler WHERE sessionid = :sessionid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('sessionid', $sessionid);
		try{
			$stmt->execute();
			$rs = $stmt->fetch();
		}
		catch (Exception $e){
			throw new Exception('Session: read broken while query');
		}
		if (! $rs){
			return false;
		}
		return $rs['sessioncontent'];
	}

	public function session_write ($sessionid, $sessioncontent)
	{
		$clientid = (isset($_SESSION['CurrentState']['Clientid'][0]) && $_SESSION['CurrentState']['Clientid'][0] > 0) ? $_SESSION['CurrentState']['Clientid'][0] : 0;
		$cart = (isset($_SESSION['CurrentState']['Cart'][0])) ? $_SESSION['CurrentState']['Cart'][0] : NULL;
		$viewid = (isset($_SESSION['CurrentState']['MainsideViewId'][0]) && $_SESSION['CurrentState']['MainsideViewId'][0] > 0) ? $_SESSION['CurrentState']['MainsideViewId'][0] : 0;
		$globalprice = isset($_SESSION['CurrentState']['GlobalPrice'][0]) ? (float) $_SESSION['CurrentState']['GlobalPrice'][0] : 0;
		$cartcurrency = isset($_SESSION['CurrentState']['CurrencySymbol'][0]) ? (string) $_SESSION['CurrentState']['CurrencySymbol'][0] : NULL;
		$ipaddress = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 15) : '000.000.000.000';
		
		$browser = (isset($_SESSION['CurrentState']['BrowserData'][0]['browser']) && $_SESSION['CurrentState']['BrowserData'][0]['browser'] != '') ? $_SESSION['CurrentState']['BrowserData'][0]['browser'] : NULL;
		$platform = (isset($_SESSION['CurrentState']['BrowserData'][0]['platform']) && $_SESSION['CurrentState']['BrowserData'][0]['platform'] != '') ? $_SESSION['CurrentState']['BrowserData'][0]['platform'] : NULL;
		$ismobile = (isset($_SESSION['CurrentState']['BrowserData'][0]['ismobile']) && $_SESSION['CurrentState']['BrowserData'][0]['ismobile'] == 1) ? 1 : 0;
		$isbot = (isset($_SESSION['CurrentState']['BrowserData'][0]['isbot']) && $_SESSION['CurrentState']['BrowserData'][0]['isbot'] == 1) ? 1 : 0;
		
		if ($this->isMemcache){
			$this->registry->cache->save('session_' . $sessionid, $sessioncontent, $this->__SESSION_LIFETIME);
		}
		
		$sql = 'REPLACE INTO sessionhandler(
					sessioncontent,
					sessionid,
					expiredate,
					clientid,
					globalprice,
					cartcurrency,
					ipaddress,
					browser,
					platform,
					ismobile,
					isbot,
					viewid,
					url,
					cart
				)
				VALUES (
					:sessioncontent,
					:sessionid,
					:expiredate,
					:clientid,
					:globalprice,
					:cartcurrency,
					:ipaddress,
					:browser,
					:platform,
					:ismobile,
					:isbot,
					:viewid,
					:url,
					:cart
				)';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('sessioncontent', $this->isMemcache ? '' : $sessioncontent);
		$stmt->bindValue('sessionid', $sessionid);
		$stmt->bindValue('clientid', $clientid);
		$stmt->bindValue('globalprice', $globalprice);
		$stmt->bindValue('cartcurrency', $cartcurrency);
		$stmt->bindValue('ipaddress', $ipaddress);
		$stmt->bindValue('browser', $browser);
		$stmt->bindValue('cart', json_encode($cart));
		$stmt->bindValue('platform', $platform);
		$stmt->bindValue('url', App::getURL());
		$stmt->bindValue('ismobile', $ismobile);
		$stmt->bindValue('isbot', $isbot);
		if ($viewid > 0){
			$stmt->bindValue('viewid', $viewid);
		}
		else{
			$stmt->bindValue('viewid', NULL);
		}
		$stmt->bindValue('expiredate', date('Y-m-d H:i:s', time() + $this->__SESSION_LIFETIME));
		try{
			return $stmt->execute();
		}
		catch (Exception $e){
			throw new Exception('Session: write broken while query');
		}
	}

	public function session_destroy ($sessionid)
	{
		if (isset($_SESSION['CurrentState']['Clientid'][0]) && $_SESSION['CurrentState']['Clientid'][0] > 0 && ! isset($_SESSION['CurrentState']['Userid'][0])){
			$checkDeletedSessionHasCart = App::getModel('missingcart')->checkMissingCartSessionid($sessionid);
			if (is_array($checkDeletedSessionHasCart) && $checkDeletedSessionHasCart != NULL){
				$contentHasCart = App::getModel('missingcart')->checkSessionHandlerHasCartData($checkDeletedSessionHasCart['cart']);
				if ($contentHasCart === true){
					App::getModel('missingcart')->saveMissingCartData($checkDeletedSessionHasCart['cart'], $sessionid);
				}
			}
		}
		
		if ($this->isMemcache){
			$this->registry->cache->delete('session_' . $sessionid);
		}
		
		$sql = 'DELETE FROM sessionhandler WHERE sessionid = :sessionid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('sessionid', $sessionid);
		try{
			return $stmt->execute();
		}
		catch (Exception $e){
			throw new Exception('Session: destroy broken while query');
		}
	}

	public function session_gc ($lifeTime)
	{
		$sql = 'DELETE FROM sessionhandler WHERE expiredate < :killtime';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('killtime', date('Y-m-d H:i:s', time()));
		try{
			return $stmt->execute();
		}
		catch (Exception $e){
			throw new Exception('Session: garbage collector broken while query');
		}
	}
}