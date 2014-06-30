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
 * $Revision: 619 $
 * $Author: gekosale $
 * $Date: 2011-12-19 22:09:00 +0100 (Pn, 19 gru 2011) $
 * $Id: news.php 619 2011-12-19 21:09:00Z gekosale $ 
 */

namespace Gekosale;
use FormEngine;

class OrderStatusForm extends Component\Form
{
	
	protected $populateData;

	public function setPopulateData ($Data)
	{
		$this->populateData = $Data;
	}

	public function initForm ()
	{
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'orderstatus',
			'action' => '',
			'method' => 'post'
		));
		
		$requiredData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'required_data',
			'label' => _('TXT_MAIN_DATA')
		)));
		
		$languageData = $requiredData->AddChild(new FormEngine\Elements\FieldsetLanguage(Array(
			'name' => 'language_data',
			'label' => _('TXT_LANGUAGE_DATA')
		)));
		
		$languageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'name',
			'label' => _('TXT_NAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME')),
				new FormEngine\Rules\LanguageUnique(_('ERR_NAME_ALREADY_EXISTS'), 'orderstatustranslation', 'name', null, Array(
					'column' => 'orderstatusid',
					'values' => (int) $this->registry->core->getParam()
				))
			)
		)));
		
		$languageData->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'comment',
			'label' => _('TXT_DEFAULT_ORDER_COMMENT')
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'orderstatusgroupsid',
			'label' => _('TXT_ORDER_STATUS_GROUPS'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_ORDER_STATUS_GROUPS'))
			),
			'options' => FormEngine\Option::Make(App::getModel('orderstatusgroups/orderstatusgroups')->getOrderStatusGroupsAllToSelect(), $this->registry->core->getDefaultValueToSelect())
		)));
		
		$Data = Event::dispatch($this, 'admin.orderstatus.initForm', Array(
			'form' => $form,
			'id' => (int) $this->registry->core->getParam(),
			'data' => $this->populateData
		));
		
		if (! empty($Data)){
			$form->Populate($Data);
		}
		
		$form->AddFilter(new FormEngine\Filters\NoCode());
		$form->AddFilter(new FormEngine\Filters\Trim());
		$form->AddFilter(new FormEngine\Filters\Secure());
		
		return $form;
	}

}