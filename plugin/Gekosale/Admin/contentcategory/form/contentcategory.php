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

class ContentCategoryForm extends Component\Form
{
	protected $populateData;

	public function setPopulateData ($Data)
	{
		$this->populateData = $Data;
	}

	public function initForm ()
	{
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'contentcategory',
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
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME'))
			)
		)));
		
		$languageData->AddChild(new FormEngine\Elements\RichTextEditor(Array(
			'name' => 'description',
			'label' => _('TXT_CONTENT')
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'header',
			'label' => _('TXT_ENABLE_IN_HEADER'),
			'default' => '1'
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'footer',
			'label' => _('TXT_ENABLE_IN_FOOTER'),
			'default' => '1'
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Tip(Array(
			'direction' => FormEngine\Elements\Tip::DOWN,
			'tip' => '<p>' . _('TXT_PARENT_CATEGORY_EXAMPLE') . '</p>'
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Tree(Array(
			'name' => 'contentcategoryid',
			'label' => _('TXT_CATEGORY'),
			'choosable' => true,
			'selectable' => false,
			'sortable' => false,
			'clickable' => false,
			'restrict' => (int) $this->registry->core->getParam(),
			'items' => App::getModel('contentcategory')->getContentCategoryALL($this->registry->core->getParam())
		)));
		
		$metaData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'meta_data',
			'label' => _('TXT_META_INFORMATION')
		)));
		
		$metaData->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">W przypadku braku informacji META system wygeneruje je automatycznie. W każdej chwili możesz je zmienić edytując dane poniżej.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
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
			'comment' => _('TXT_KEYWORDS_HELP'),
		)));
		
		$redirectData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'redirect_data',
			'label' => _('TXT_CONTENTCATEGORY_REDIRECT')
		)));
		
		$redirect = $redirectData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'redirect',
			'label' => _('TXT_REDIRECT_TYPE'),
			'options' => Array(
				new FormEngine\Option(0, _('TXT_REDIRECT_NONE')),
				new FormEngine\Option(1, _('TXT_REDIRECT_INTERNAL')),
				new FormEngine\Option(2, _('TXT_REDIRECT_EXTERNAL'))
			),
			'default' => 1
		)));
		
		$redirectData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'redirect_route',
			'label' => _('TXT_REDIRECT_INTERNAL_URL'),
			'options' => Array(
				new FormEngine\Option('frontend.mainside', _('TXT_CONTROLLER_MAINSIDE')),
				new FormEngine\Option('frontend.conditions', _('TXT_CONDITIONS')),
				new FormEngine\Option('frontend.clientlogin', _('TXT_CONTROLLER_CLIENTLOGIN')),
				new FormEngine\Option('frontend.registration', _('TXT_CONTROLLER_REGISTRATION')),
				new FormEngine\Option('frontend.contact', _('TXT_CONTROLLER_CONTACT')),
				new FormEngine\Option('frontend.sitemap', _('TXT_CONTROLLER_SITEMAP')),
				new FormEngine\Option('frontend.news', _('TXT_CONTROLLER_NEWS')),
				new FormEngine\Option('frontend.forgotpassword', _('TXT_CONTROLLER_FORGOTPASSWORD')),
				new FormEngine\Option('frontend.producerlist', _('TXT_CONTROLLER_PRODUCERLIST')),
				new FormEngine\Option('frontend.categorylist', _('TXT_CONTROLLER_CATEGORYLIST')),
			),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::SHOW, $redirect, new FormEngine\Conditions\Equals(1))
			),
			'default' => 1
		)));
		
		$redirectData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'redirect_url',
			'label' => _('TXT_REDIRECT_EXTERNAL_URL'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::SHOW, $redirect, new FormEngine\Conditions\Equals(2))
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
		
		$Data = Event::dispatch($this, 'admin.contentcategory.initForm', Array(
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