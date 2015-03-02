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
 * $Revision: 438 $
 * $Author: gekosale $
 * $Date: 2011-08-27 11:29:36 +0200 (So, 27 sie 2011) $
 * $Id: loader.class.php 438 2011-08-27 09:29:36Z gekosale $ 
 */
namespace Gekosale;

class Loader
{
	const SYSTEM_NAMESPACE = 'Gekosale';
	protected $registry;
	protected $events;
	protected $layer = Array();
	protected $namespace = 'core';
	protected $_viewid = 3;

	public function __construct (&$registry, $load = true)
	{
		$this->registry = $registry;
		if ($load){
			$this->loadView();
		}
	}

	public function normalizeHost ($host)
	{
		$host = trim(strtolower($host));
		return (substr($host, 0, 4) == 'www.') ? substr($host, 4) : $host;
	}

	public function determineViewId ()
	{
		$sql = "SELECT viewid FROM viewurl WHERE url = :url";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('url', $this->normalizeHost(App::getHost()));
		$stmt->execute();
		$rs = $stmt->fetch();
		if ($rs){
			return $rs['viewid'];
		}
		return $this->_viewid;
	}

	public function loadView ()
	{
		$sql = 'SELECT
					V.idview,
					V.name as shopname,
					V.namespace,
					C.idcurrency, 
					C.currencysymbol,
					C.decimalseparator,
					C.decimalcount,
					C.thousandseparator,
					C.positivepreffix,
					C.positivesuffix,
					C.negativepreffix,
					C.negativesuffix,
					S.countryid,
					V.taxes,
					V.showtax,
					V.offline,
					cartredirect,
          terms,
					photoid,
					favicon,
					forcelogin,
					apikey,
					watermark,
					confirmregistration,
					enableregistration,
					invoicenumerationkind,
					V.pageschemeid,
					V.contactid,
					PS.templatefolder
				FROM view V
				LEFT JOIN viewcategory VC ON VC.viewid = V.idview
				LEFT JOIN store S ON V.storeid = S.idstore
				LEFT JOIN pagescheme PS ON PS.idpagescheme = V.pageschemeid
				LEFT JOIN currency C ON C.idcurrency = IF(:currencyid > 0, :currencyid, V.currencyid)
				WHERE V.idview = :viewid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', $this->determineViewId());
		$stmt->bindValue('currencyid', Session::getActiveCurrencyId());
		$stmt->execute();
		$rs = $stmt->fetch();
		if ($rs){
			$this->layer = Array(
        'terms' => $rs['terms'],
				'idview' => $rs['idview'],
				'namespace' => $rs['namespace'],
				'cartredirect' => $rs['cartredirect'],
				'offline' => $rs['offline'],
				'taxes' => $rs['taxes'],
				'showtax' => $rs['showtax'],
				'shopname' => $rs['shopname'],
				'photoid' => $rs['photoid'],
				'favicon' => $rs['favicon'],
				'watermark' => $rs['watermark'],
				'idcurrency' => $rs['idcurrency'],
				'currencysymbol' => $rs['currencysymbol'],
				'decimalseparator' => $rs['decimalseparator'],
				'decimalcount' => $rs['decimalcount'],
				'thousandseparator' => $rs['thousandseparator'],
				'positivepreffix' => $rs['positivepreffix'],
				'positivesuffix' => $rs['positivesuffix'],
				'negativepreffix' => $rs['negativepreffix'],
				'negativesuffix' => $rs['negativesuffix'],
				'countryid' => $rs['countryid'],
				'forcelogin' => $rs['forcelogin'],
				'confirmregistration' => $rs['confirmregistration'],
				'enableregistration' => $rs['enableregistration'],
				'apikey' => $rs['apikey'],
				'invoicenumerationkind' => $rs['invoicenumerationkind'],
				'pageschemeid' => $rs['pageschemeid'],
				'theme' => $rs['templatefolder'],
				'pageschemeid' => $rs['pageschemeid'],
				'contactid' => $rs['contactid']
			);
			Session::setActiveShopName($this->layer['shopname']);
			if (is_null($this->layer['photoid'])){
				$this->layer['photoid'] = 'logo.png';
			}
			if (is_null($this->layer['favicon'])){
				$this->layer['favicon'] = 'favicon.ico';
			}
			Session::setActiveShopCurrencyId($this->layer['idcurrency']);
			Session::setActiveForceLogin($this->layer['forcelogin']);
			
			if (Session::getActiveBrowserData() == NULL){
				$browser = new Browser();
				$Data = Array(
					'browser' => $browser->getBrowser(),
					'platform' => $browser->getPlatform(),
					'ismobile' => $browser->isMobile(),
					'isbot' => $browser->isRobot()
				);
				Session::setActiveBrowserData($Data);
			}
		}
	}

	public function getParam ($param)
	{
		return (isset($this->layer[$param])) ? $this->layer[$param] : NULL;
	}

	public function getCurrentLayer ()
	{
		return $this->layer;
	}

	public function getLayerViewId ()
	{
		return (isset($this->layer['idview'])) ? $this->layer['idview'] : 0;
	}

	public function getCurrentNamespace ()
	{
		return (isset($this->layer['namespace'])) ? $this->layer['namespace'] : 'core';
	}

	public function getSystemNamespace ()
	{
		return self::SYSTEM_NAMESPACE;
	}

	public function isOffline ()
	{
		return (boolean) $this->layer['offline'];
	}

	public function getNamespaces ()
	{
		if (isset($this->layer['namespace'])){
			return array_unique(Array(
				self::SYSTEM_NAMESPACE,
				ucfirst(strtolower($this->layer['namespace']))
			));
		}
		return Array(
			self::SYSTEM_NAMESPACE
		);
	}
}
