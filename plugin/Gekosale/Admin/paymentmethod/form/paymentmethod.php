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

class PaymentMethodForm extends Component\Form
{
	protected $populateData;

	public function setPopulateData ($Data)
	{
		$this->populateData = $Data;
	}

	public function initForm ($addForm = 0)
	{
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'paymentmethod',
			'action' => '',
			'method' => 'post'
		));
		
		$requiredData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'required_data',
			'label' => _('TXT_MAIN_DATA')
		)));
		
		$basicLanguageData = $requiredData->AddChild(new FormEngine\Elements\FieldsetLanguage(Array(
			'name' => 'language_data',
			'label' => _('TXT_LANGUAGE_DATA')
		)));

		$basicLanguageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'name',
			'label' => _('TXT_NAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME')),
				new FormEngine\Rules\Unique(_('ERR_NAME_ALREADY_EXISTS'), 'paymentmethodtranslation', 'name', null, Array(
					'column' => 'paymentmethodid',
					'values' => (int) $this->registry->core->getParam()
				))
			)
		)));
				
    if ($addForm) {
      $Data = Event::dispatch($this, 'admin.paymentmethod.getPaymentMethods', Array(
        'data' => Array()
      ));
      
      $requiredData->AddChild(new FormEngine\Elements\Select(Array(
        'name' => 'controller',
        'label' => _('TXT_PAYMENT_CONTROLLER'),
        'rules' => Array(
          new FormEngine\Rules\Required(_('ERR_EMPTY_PAYMENT_CONTROLLER'))
        ),
        'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + $Data)
      )));
    }
    else {
      $requiredData->AddChild(new FormEngine\Elements\Constant(Array(
        'name' => 'controller',
        'label' => _('TXT_PAYMENT_CONTROLLER'),
      )));
    }

		$layerData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'view_data',
			'label' => _('TXT_STORES')
		)));
		
		$layerData->AddChild(new FormEngine\Elements\LayerSelector(Array(
			'name' => 'view',
			'label' => _('TXT_VIEW')
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\MultiSelect(Array(
			'name' => 'dispatchmethod',
			'label' => _('TXT_DISPATCHMETHOD'),
			'options' => FormEngine\Option::Make(App::getModel('dispatchmethod')->getDispatchmethodToSelect())
		)));
		
		$Data = Event::dispatch($this, 'admin.paymentmethod.initForm', Array(
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
		/*
		$requiredData->AddChild(new FormEngine\Elements\MultiSelect(Array(
			'name' => 'dispatchmethod',
			'label' => _('TXT_DISPATCHMETHOD'),
			'options' => FormEngine\Option::Make(App::getModel('dispatchmethod')->getDispatchmethodToSelect())
		)));
		
		$layerData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'view_data',
			'label' => _('TXT_STORES')
		)));
		
		$layerData->AddChild(new FormEngine\Elements\LayerSelector(Array(
			'name' => 'view',
			'label' => _('TXT_VIEW')
		)));
		*/
