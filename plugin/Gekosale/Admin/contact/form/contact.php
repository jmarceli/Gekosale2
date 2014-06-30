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

class ContactForm extends Component\Form
{
	
	protected $populateData;

	public function setPopulateData ($Data)
	{
		$this->populateData = $Data;
	}

	public function initForm ()
	{
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'contact',
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
				new FormEngine\Rules\LanguageUnique(_('ERR_NAME_ALREADY_EXISTS'), 'contacttranslation', 'name', null, Array(
					'column' => 'contactid',
					'values' => (int) $this->registry->core->getParam()
				))
			)
		)));
		
		$languageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'email',
			'label' => _('TXT_EMAIL'),
			'comment' => _('TXT_EMAIL_FORM'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_EMAIL')),
				new FormEngine\Rules\Email(_('ERR_WRONG_EMAIL'))
			)
		)));
		
		$languageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'phone',
			'label' => _('TXT_PHONE'),
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_WRONG_FORMAT_PHONE'), '/^(\d{1,}(-|\s)?\d{1,})*$/')
			)
		)));
		
		$languageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'fax',
			'label' => _('TXT_FAX'),
			'comment' => _('TXT_FAX_FORM'),
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_WRONG_FORMAT_FAX'), '/^(\d{1,}(-|\s)?\d{1,})*$/')
			)
		)));
		
		$languageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'businesshours',
			'label' => _('TXT_BUSINESS_HOURS'),
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'publish',
			'label' => _('TXT_PUBLISH'),
			'options' => Array(
				new FormEngine\Option('1', _('TXT_YES')),
				new FormEngine\Option('0', _('TXT_NO'))
			),
			'default' => '1'
		)));
		
		$addressData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'address_data',
			'label' => _('TXT_CONTACT_ADDRESS_DATA')
		)));
		
		$addressLanguageData = $addressData->AddChild(new FormEngine\Elements\FieldsetLanguage(Array(
			'name' => 'language_data',
			'label' => _('TXT_LANGUAGE_DATA')
		)));
		
		$addressLanguageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'street',
			'label' => _('TXT_STREET'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STREET'))
			)
		)));
		
		$addressLanguageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'streetno',
			'label' => _('TXT_STREETNO')
		)));
		
		$addressLanguageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'placeno',
			'label' => _('TXT_PLACENO')
		)));
		
		$place = $addressLanguageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'placename',
			'label' => _('TXT_PLACE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PLACE'))
			)
		)));
		
		$addressLanguageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'postcode',
			'label' => _('TXT_POSTCODE'),
			'comment' => _('TXT_POSTCODE_FORM'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_POSTCODE')),
				new FormEngine\Rules\Format(_('ERR_WRONG_FORMAT_POSTCODE'), '/^\d{2}-\d{3}?$/')
			)
		)));
		
		$addressLanguageData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'countryid',
			'label' => _('TXT_NAME_OF_COUNTRY'),
			'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + App::getModel('countrieslist')->getCountryForSelect()),
			'default' => 0,
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME_OF_COUNTRY'))
			)
		)));
		
		$layerData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'view_data',
			'label' => _('TXT_STORES')
		)));
		
		$layerData->AddChild(new FormEngine\Elements\LayerSelector(Array(
			'name' => 'view',
			'label' => _('TXT_VIEW'),
			'default' => Helper::getViewIdsDefault()
		)));
		
		$Data = Event::dispatch($this, 'admin.contact.initForm', Array(
			'form' => $form,
			'id' => (int) $this->registry->core->getParam(),
			'data' => $this->populateData
		));
		
		if (! empty($Data)){
			$form->Populate($Data);
		}
		
		$form->AddFilter(new FormEngine\Filters\Trim());
		$form->AddFilter(new FormEngine\Filters\Secure());
		
		return $form;
	}

}