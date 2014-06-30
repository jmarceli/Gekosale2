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
 * $Id: controller.class.php 438 2011-08-27 09:29:36Z gekosale $ 
 */
namespace Gekosale\Component;

abstract class Controller extends \Gekosale\Component
{
	protected $registry;
	protected $designPath;

	public function __construct ($registry, $designPath = NULL)
	{
		$this->registry = $registry;
		if ($designPath != NULL){
			$this->setDesignPath($designPath);
		}
	}

	public function setDesignPath ($path)
	{
		$this->designPath = $path;
	}

	abstract function index ();

	public function getDesignPath ()
	{
		return $this->designPath;
	}

	public function loadTemplate ($fileName)
	{
		return $this->getDesignPath() . $fileName;
	}

	/**
	 * To avoid "this->registry->template"
	 */
	protected function getTemplate ()
	{
		return $this->registry->template;
	}

	/**
	 * To avoid "this->registry->core"
	 */
	protected function getCore ()
	{
		return $this->registry->core;
	}

	/**
	 * To avoid this->registry->router
	 */
	protected function getRouter ()
	{
		return $this->registry->router;
	}

	protected function getRequest ()
	{
		return \Gekosale\App::getRequest();
	}

	public function getName ()
	{
		return str_replace('controller', '', strtolower(end(explode('\\', get_class($this)))));
	}
}