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
 * $Id: banktransfer.php 438 2011-08-27 09:29:36Z gekosale $ 
 */
namespace Gekosale;

use FormEngine;

class BanktransferModel extends Component\Model
{
	protected $_name = 'Przelew bankowy';

	public function getPaymentMethod ($event, $request)
	{
		$Data[$this->getName()] = $this->_name;
		$event->setReturnValues($Data);
	}

	public function getPaymentMethodConfigurationForm ($event, $request)
	{
		if ($request['data']['paymentmethodmodel'] != $this->getName()){
			return false;
		}
		
		$banktransfer = &$request['form']->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'banktransfer_data',
			'label' => 'Konfiguracja'
		)));
		
		$banktransfer->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'bankacct',
			'label' => 'Numer rachunku bankowego',
			'rules' => Array(
				new FormEngine\Rules\Required('Musisz podać numer rachunku bankowego.')
			)
		)));
		
		$banktransfer->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'bankname',
			'label' => 'Nazwa banku',
			'rules' => Array(
				new FormEngine\Rules\Required('Musisz podać nazwę banku.')
			)
		)));
		
		$banktransfer->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'iban',
			'label' => 'IBAN'
		)));
		
		$settings = $this->registry->core->loadModuleSettings('banktransfer', Helper::getViewId());
		
		if (! empty($settings)){
			$populate = Array(
				'banktransfer_data' => Array(
					'bankacct' => $settings['bankacct'],
					'bankname' => $settings['bankname'],
				)
			);
			
			$event->setReturnValues($populate);
		}
	}

	public function saveSettings ($request)
	{
		if ($request['model'] != $this->getName()){
			return false;
		}
		
		$Settings = Array(
			'bankacct' => $request['data']['bankacct'],
			'bankname' => $request['data']['bankname'],
		);
		
		$this->registry->core->saveModuleSettings('banktransfer', $Settings, Helper::getViewId());
	}

	public function getPaymentData ($order)
	{
		return $this->registry->core->loadModuleSettings('banktransfer', Helper::getViewId());
	}
}