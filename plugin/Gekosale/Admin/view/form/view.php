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

class ViewForm extends Component\Form
{
	protected $registry;
	protected $populateData;

	public function __construct ($registry)
	{
		parent::__construct($registry);
		$this->registry = $registry;
	}

	public function setPopulateData ($Data)
	{
		$this->populateData = $Data;
	}

	public function initForm ()
	{
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'view',
			'action' => '',
			'method' => 'post'
		));
		
		$requiredData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'required_data',
			'label' => _('TXT_MAIN_DATA')
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'name',
			'label' => _('TXT_NAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME'))
			)
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Wybierz domyślny szablon sklepu. Szablonami możesz zarządzać na stronie <a href="' . App::getURLAdressWithAdminPane() . 'templateeditor' . '" target="_blank">Szablony stylów &raquo; Biblioteka szablonów</a>.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'pageschemeid',
			'label' => _('TXT_PAGESCHEME'),
			'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + App::getModel('pagescheme')->getPageschemeAllToSelect()),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PAGESCHEME'))
			)
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Wybierz firmę/podmiot obsługujący sklep. Firmami możesz zarządzać na stronie <a href="' . App::getURLAdressWithAdminPane() . 'store' . '" target="_blank">Konfiguracja &raquo; Firmy</a>.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'store',
			'label' => _('TXT_STORE'),
			'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + App::getModel('store')->getStoreToSelect()),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STORE'))
			)
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Hidden(Array(
			'name' => 'currencyid',
    )));
		
		$requiredData->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Wybierz domyślny kontakt dla sklepu. Kontaktami możesz zarządzać na stronie <a href="' . App::getURLAdressWithAdminPane() . 'contact' . '" target="_blank">Konfiguracja &raquo; Kontakt</a>.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'contactid',
			'label' => _('TXT_DEFAULT_CONTACT'),
			'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + App::getModel('contact')->getContactToSelect())
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'showtax',
			'label' => _('TXT_SHOW_TAX_VALUE'),
			'options' => FormEngine\Option::Make(App::getModel('suffix/suffix')->getPrice()),
			'default' => 1
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'defaultvatid',
			'label' => _('TXT_DEFAULT_VAT'),
			'options' => FormEngine\Option::Make(App::getModel('vat')->getVATAll()),
			'default' => 2
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'namespace',
			'label' => _('TXT_NAMESPACE')
		)));
		
		$offline = $requiredData->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'offline',
			'label' => _('TXT_SHOP_OFFLINE'),
			'comment' => _('TXT_OFFLINE_INSTRUCTION')
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'offlinetext',
			'label' => _('TXT_OFFLINE_MESSAGE'),
			'comment' => _('TXT_MAX_LENGTH') . ' 5000',
			'rows' => 50,
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::HIDE, $offline, new FormEngine\Conditions\Not(new FormEngine\Conditions\Equals('1')))
			)
		)));
		
		$metaData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'meta_data',
			'label' => _('TXT_META_INFORMATION')
		)));
		
		$languageData = $metaData->AddChild(new FormEngine\Elements\FieldsetLanguage(Array(
			'name' => 'language_data',
			'label' => _('TXT_LANGUAGE_DATA')
		)));
		
		$languageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'keyword_title',
			'label' => _('TXT_KEYWORD_TITLE')
		)));
		
		$languageData->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'keyword_description',
			'label' => _('TXT_KEYWORD_DESCRIPTION')
		)));
		
		$languageData->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'keyword',
			'label' => _('TXT_KEYWORDS'),
			'comment' => _('TXT_KEYWORDS_HELP')
		)));
		
		$languageData->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Możesz dodać dowolne znaczniki do sekcji HEAD szablonu sklepu. Wykorzystaj to pole np. do przeprowadzenia weryfikacji domeny dla Google Apps.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$languageData->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'additionalmeta',
			'label' => _('TXT_ADDITIONAL_META'),
			'rows' => 10
		)));
		
		$url = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'url_pane',
			'label' => _('TXT_WWW')
		)));
		
		$url->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'url',
			'label' => _('TXT_URL')
		)));
		
		$categoryPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'category_pane',
			'label' => _('TXT_CATEGORY')
		)));
		
		$categories = App::getModel('view')->getChildCategories();
		
		if (count($categories) > 0){
			$categoryPane->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => '<p>' . _('TXT_VIEW_CATEGORY_INSTRUCTION') . '</p>'
			)));
		}
		else{
			$categoryPane->AddChild(new FormEngine\Elements\StaticText(Array(
				'text' => '<p>' . _('TXT_VIEW_CATEGORY_EMPTY_INSTRUCTION') . '</p>'
			)));
		}
		$category = $categoryPane->AddChild(new FormEngine\Elements\Tree(Array(
			'name' => 'category',
			'label' => _('TXT_CATEGORY'),
			'sortable' => false,
			'selectable' => true,
			'clickable' => false,
			'items' => $categories,
			'load_children' => Array(
				App::getModel('view'),
				'getChildCategories'
			)
		)));
		
		$dispatchmethodPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'dispatchmethod_pane',
			'label' => _('TXT_DISPATCHMETHOD_PANE')
		)));
		
		$dispatchmethodPane->AddChild(new FormEngine\Elements\MultiSelect(Array(
			'name' => 'dispatchmethod',
			'label' => _('TXT_DISPATCHMETHOD'),
			'options' => FormEngine\Option::Make(App::getModel('dispatchmethod')->getDispatchmethodToSelect())
		)));
		
		$paymentmethodPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'paymentmethod_pane',
			'label' => _('TXT_PAYMENTMETHOD_PANE')
		)));
		
		$paymentmethodPane->AddChild(new FormEngine\Elements\MultiSelect(Array(
			'name' => 'paymentmethod',
			'label' => _('TXT_PAYMENTMETHOD'),
			'options' => FormEngine\Option::Make(App::getModel('paymentmethod')->getPaymentmethodToSelect())
		)));
		
		$assignToGroupData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'assigntogroup_data',
			'label' => _('TXT_AUTOMATICLY_ASSIGN_TO_GROUP')
		)));
		
		$assignToGroupData->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Automatyczny awans umożliwia przechodzenie klientom Twojego sklepu do wyższych grup rabatowych w zależności od tego ile zakupów zrealizują w określonym czasie.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$assignToGroupData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'taxes',
			'label' => _('TXT_TAKE_THE_VALUE'),
			'options' => Array(
				new FormEngine\Option('0', _('TXT_NETTO')),
				new FormEngine\Option('1', _('TXT_PRICE_GROSS'))
			),
			'suffix' => _('TXT_CLIENT_ORDERS')
		)));
		
		$assignToGroupData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'periodid',
			'label' => _('TXT_PERIOD'),
			'options' => FormEngine\Option::Make(App::getModel('period/period')->getPeriod())
		)));
		
		$assignToGroupData->AddChild(new FormEngine\Elements\RangeEditor(Array(
			'name' => 'table',
			'label' => _('TXT_DISPATCHMETHOD_TABLE_PRICE'),
			'suffix' => _('TXT_CURRENCY'),
			'range_suffix' => _('TXT_CURRENCY'),
			'options' => FormEngine\Option::Make(App::getModel('clientgroup')->getClientGroupToRangeEditor())
		)));
		
		$registrationData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'registration_data',
			'label' => _('TXT_REGISTRATION_SETTINGS')
		)));
		
		$registrationData->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'forcelogin',
			'label' => _('TXT_FORCE_CLIENT_LOGIN'),
			'comment' => _('TXT_FORCE_CLIENT_LOGIN_HELP')
		)));
		
		$registrationData->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'enableregistration',
			'label' => _('TXT_ENABLE_REGISTRATION'),
			'comment' => _('TXT_ENABLE_REGISTRATION_HELP')
		)));
		
		$registrationData->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'confirmregistration',
			'label' => _('TXT_REGISTRATION_CONFIRM'),
			'comment' => _('TXT_REGISTRATION_CONFIRM_HELP')
		)));
		
		$cartData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'cart_data',
			'label' => _('TXT_CART_SETTINGS')
		)));
		
		$cartData->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'cartredirect',
			'label' => _('TXT_CART_REDIRECT')
		)));
		
		$cartData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'minimumordervalue',
			'label' => _('TXT_MINIMUM_ORDER_VALUE'),
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
			),
			'default' => 0
		)));
		
		$photosPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'logo',
			'label' => _('TXT_LOGO')
		)));
		
		$photosPane->AddChild(new FormEngine\Elements\LocalFile(Array(
			'name' => 'photo',
			'label' => _('TXT_LOGO'),
			'file_source' => 'design/_images_frontend/core/logos/',
			'file_types' => Array(
				'png',
				'jpg',
				'gif'
			)
		)));
		
		$photosPane->AddChild(new FormEngine\Elements\LocalFile(Array(
			'name' => 'favicon',
			'label' => _('TXT_FAVICON'),
			'file_source' => 'design/_images_frontend/core/logos/',
			'file_types' => Array(
				'ico'
			)
		)));
		
		$photosPane->AddChild(new FormEngine\Elements\LocalFile(Array(
			'name' => 'watermark',
			'label' => _('TXT_WATERMARK'),
			'file_source' => 'design/_images_frontend/core/logos/',
			'file_types' => Array(
				'png'
			)
		)));
		
		$mailerdata = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'mailer_data',
			'label' => _('TXT_MAIL_SETTINGS')
		)));
		
		$mailerType = $mailerdata->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'mailer',
			'label' => _('TXT_MAIL_TYPE'),
			'options' => FormEngine\Option::Make(Array(
				'mail' => 'mail',
				'sendmail' => 'sendmail',
				'smtp' => 'smtp'
			))
		)));
		
		$mailerdata->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'server',
			'label' => _('TXT_MAIL_SERVER'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::HIDE, $mailerType, new FormEngine\Conditions\Not(new FormEngine\Conditions\Equals('smtp')))
			),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_MAIL_SERVER'))
			)
		)));
		
		$mailerdata->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'port',
			'label' => _('TXT_MAIL_SERVER_PORT'),
			'default' => 587,
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::HIDE, $mailerType, new FormEngine\Conditions\Not(new FormEngine\Conditions\Equals('smtp')))
			),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_MAIL_SERVER_PORT'))
			)
		)));
		
		$mailerdata->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'smtpsecure',
			'label' => _('TXT_MAIL_SMTP_SECURE'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::HIDE, $mailerType, new FormEngine\Conditions\Not(new FormEngine\Conditions\Equals('smtp')))
			),
			'options' => FormEngine\Option::Make(Array(
				'' => 'brak',
				'ssl' => 'ssl',
				'tls' => 'tls'
			)),
			'value' => ''
		)));
		
		$mailerdata->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'smtpauth',
			'label' => _('TXT_MAIL_SMTP_AUTH'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::HIDE, $mailerType, new FormEngine\Conditions\Not(new FormEngine\Conditions\Equals('smtp')))
			)
		)));
		
		$mailerdata->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'smtpusername',
			'label' => _('TXT_MAIL_SMTP_USERNAME'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::HIDE, $mailerType, new FormEngine\Conditions\Not(new FormEngine\Conditions\Equals('smtp')))
			)
		)));
		
		$mailerdata->AddChild(new FormEngine\Elements\Password(Array(
			'name' => 'smtppassword',
			'label' => _('TXT_MAIL_SMTP_PASSWORD'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::HIDE, $mailerType, new FormEngine\Conditions\Not(new FormEngine\Conditions\Equals('smtp')))
			)
		)));
		
		$mailerdata->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'fromname',
			'label' => _('TXT_MAIL_FROMNAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_MAIL_FROMNAME'))
			)
		)));
		
		$mailerdata->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'fromemail',
			'label' => _('TXT_MAIL_FROMEMAIL'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_MAIL_FROMEMAIL'))
			)
		)));

		$mailerdata->AddChild(new FormEngine\Elements\LocalFile(Array(
			'name' => 'terms',
      'label' => 'Plik regulaminu',
      'comment' => 'Zostanie załączony do każdego maila z potwierdzeniem zamówienia',
			'file_source' => 'upload/',
			'file_types' => Array(
				'pdf'
			)
		)));
		
		$invoicedata = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'invoice_data',
			'label' => _('TXT_INVOICE')
		)));
		
		$invoicedata->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'invoicenumerationkind',
			'label' => _('TXT_INVOICE_NUMERATION'),
			'options' => FormEngine\Option::Make(App::getModel('invoice')->getInvoiceNumerationTypes())
		)));
		
		$invoicedata->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'invoicedefaultpaymentdue',
			'label' => _('TXT_INVOICE_DEFAULT_PAYMENT_DUE'),
			'suffix' => _('TXT_DAYS'),
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
			)
		)));
		
		$Data = Event::dispatch($this, 'admin.view.initForm', Array(
			'form' => $form,
			'id' => (int) $this->registry->core->getParam(),
			'data' => $this->populateData
		));
		
		if (! empty($Data)){
			$form->Populate($Data);
		}
		
		return $form;
	}
}
