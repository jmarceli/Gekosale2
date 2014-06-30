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
 * $Id: db.class.php 438 2011-08-27 09:29:36Z gekosale $ 
 */
namespace Gekosale;

class Db
{
	private static $instance = NULL;

	private function __construct ()
	{
	}

	public static function getInstance ()
	{
		if (! self::$instance){
			try{
				$configuration = new \Doctrine\DBAL\Configuration();
				$configuration->setSQLLogger(new \Doctrine\DBAL\Logging\ProfileSQLLogger());
				self::$instance = \Doctrine\DBAL\DriverManager::getConnection(App::getConfig('database'), $configuration);
				self::$instance->query('set names "utf8"');
				self::$instance->query('set character_set_server="utf8"');
				self::$instance->query('set collation_connection="utf8_general_ci"');
			}
			catch (\PDOException $e){
				require_once (ROOTPATH . 'design' . DS . 'error.php');
				die();
			}
		}
		return self::$instance;
	}

	private function __clone ()
	{
	}
}