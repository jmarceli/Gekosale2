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

class ProductForm extends Component\Form
{
	protected $populateData;

	public function setPopulateData ($Data)
	{
		$this->populateData = $Data;
	}

	public function initForm ()
	{
		$set = App::getModel('attributegroup')->getSugestVariant((int) $this->registry->core->getParam());
		
		$availablity = $this->registry->core->getDefaultValueToSelect() + App::getModel('availablity')->getAvailablityToSelect();
		
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'product',
			'action' => '',
			'method' => 'post'
		));
		
		$basicPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'basic_pane',
			'label' => _('TXT_BASIC_INFORMATION')
		)));
		
		$basicLanguageData = $basicPane->AddChild(new FormEngine\Elements\FieldsetLanguage(Array(
			'name' => 'language_data',
			'label' => _('TXT_LANGUAGE_DATA')
		)));
		
		$seoname = $basicLanguageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'name',
			'label' => _('TXT_PRODUCT_NAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PRODUCT_NAME')),
        new FormEngine\Rules\LanguageUnique(_('ERR_NAME_ALREADY_EXISTS'), 'producttranslation', 'name', null, Array(
          'column' => 'productid',
          'values' => (int) $this->registry->core->getParam()
        ))
			)
		)));
		
		$basicLanguageData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'seo',
			'label' => _('TXT_PRODUCT_SEO'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PRODUCT_SEO')),
        new FormEngine\Rules\Format(_('ERR_ALPHANUMERIC_INVALID'), '/^[A-Za-z0-9-_\",\'\s]+$/'),
				new FormEngine\Rules\LanguageUnique('Wybierz inny adres URL, podany już istnieje', 'producttranslation', 'seo', null, Array(
					'column' => 'productid',
					'values' => (int) $this->registry->core->getParam()
				))
      )
		)));
		
		$basicPane->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'enable',
			'label' => _('TXT_ENABLE_PRODUCT'),
			'default' => '0'
		)));
		
		$basicPane->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'ean',
			'label' => _('TXT_EAN')
		)));
		
		$basicPane->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'delivelercode',
			'label' => _('TXT_DELIVELERCODE')
		)));
		
		$producerid = $basicPane->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'producerid',
			'label' => _('TXT_PRODUCER'),
			'addable' => true,
			'onAdd' => 'xajax_AddProducer',
			'add_item_prompt' => 'Podaj nazwę producenta',
			'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + App::getModel('producer')->getProducerToSelect())
		)));
		
		$basicPane->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'delivererid',
			'label' => _('TXT_DELIVERER'),
			'addable' => true,
			'onAdd' => 'xajax_AddDeliverer',
			'add_item_prompt' => 'Podaj nazwę dostawcy',
			'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + App::getModel('deliverer')->getDelivererToSelect())
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
			'name' => 'keywordtitle',
			'label' => _('TXT_KEYWORD_TITLE')
		)));
		
		$languageData->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'keyworddescription',
			'label' => _('TXT_KEYWORD_DESCRIPTION'),
			'comment' => _('TXT_MAX_LENGTH') . ' 1000',
			'max_length' => 1000
		)));
		
		$languageData->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'keyword',
			'label' => _('TXT_KEYWORDS'),
			'comment' => _('TXT_KEYWORDS_HELP')
		)));
		
		$stockPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'stock_pane',
			'label' => _('TXT_SHIPPING_STOCK_SETTINGS')
		)));
		
		if ((int) $set > 0){
			$stockPane->AddChild(new FormEngine\Elements\Tip(Array(
				'tip' => '<p><strong style="color: red;">UWAGA:</strong>Ten produkt posiada warianty produktu. Jego stan magazynowy zostanie określony automatycznie na podstawie sumy stanów magazynowych wszystkich wariantów. Przejdź do zakładki Warianty produktu aby edytować stany.</p>',
				'direction' => FormEngine\Elements\Tip::DOWN
			)));
			
			$stockPane->AddChild(new FormEngine\Elements\Constant(Array(
				'name' => 'stock',
				'label' => _('TXT_STOCK'),
				'suffix' => _('TXT_QTY'),
				'default' => 0
			)));
			
			$stockPane->AddChild(new FormEngine\Elements\Hidden(Array(
				'name' => 'stock',
				'label' => _('TXT_STOCK'),
				'default' => 0
			)));
		}
		else{
			$stockPane->AddChild(new FormEngine\Elements\TextField(Array(
				'name' => 'stock',
				'label' => _('TXT_STOCK'),
				'rules' => Array(
					new FormEngine\Rules\Required(_('ERR_EMPTY_STOCK')),
					new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
				),
				'suffix' => _('TXT_QTY'),
				'default' => 0
			)));
		}
		
		$stockPane->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Śledzenie stanu magazynowego spowoduje, że stan magazynowy będzie się zmieniał automatycznie w przypadku zamówień. Produkty z włączonym śledzeniem i ilością 0 nie będą mogły być zamówione przez klientów.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$stockPane->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'trackstock',
			'label' => _('TXT_TRACKSTOCK')
		)));
		
		$disableatstockenabled = $stockPane->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'disableatstockenabled',
			'label' => _('TXT_DISABLEATSTOCK_ENABLE'),
			'default' => '0'
		)));
		
		$stockPane->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'disableatstock',
			'label' => _('TXT_DISABLEATSTOCK'),
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
			),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::SHOW, $disableatstockenabled, new FormEngine\Conditions\Equals('1'))
			),
			'default' => '0'
		)));
		
		$stockPane->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'availablityid',
			'label' => _('TXT_AVAILABLITY'),
			'options' => FormEngine\Option::Make($availablity)
		)));
		
		$categoryPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'category_pane',
			'label' => _('TXT_CATEGORY')
		)));
		
		$categoryPane->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Wybierz kategorie w jakich ma pojawić się produkt. Kategoriami możesz zarządzać na stronie <a href="' . $this->registry->router->generate('admin', true, Array(
				'controller' => 'category'
			)) . '" target="_blank">Katalog &raquo; Kategorie</a>.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$category = $categoryPane->AddChild(new FormEngine\Elements\Tree(Array(
			'name' => 'category',
			'label' => _('TXT_CATEGORY'),
			'choosable' => false,
			'selectable' => true,
			'sortable' => false,
			'clickable' => false,
			'items' => App::getModel('view')->getChildCategories(),
			'load_children' => Array(
				App::getModel('view'),
				'getChildCategories'
			)
		)));
		
		$pricePane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'price_pane',
			'label' => _('TXT_PRICE')
		)));
		
		$vat = $pricePane->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'vatid',
			'label' => _('TXT_VAT'),
			'options' => FormEngine\Option::Make(App::getModel('vat')->getVATAll()),
			'addable' => true,
			'onAdd' => 'xajax_AddVat',
			'add_item_prompt' => 'Podaj wartość stawki VAT'
		)));
		
		$currencies = App::getModel('currencieslist')->getCurrencyForSelect();
		
		$sellcurrency = $pricePane->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'sellcurrencyid',
			'label' => _('TXT_SELL_CURRENCY'),
			'options' => FormEngine\Option::Make($currencies),
			'default' => Session::getActiveShopCurrencyId()
		)));
		
		$buycurrency = $pricePane->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'buycurrencyid',
			'label' => _('TXT_BUY_CURRENCY'),
			'options' => FormEngine\Option::Make($currencies),
			'default' => Session::getActiveShopCurrencyId()
		)));
		
		$pricePane->AddChild(new FormEngine\Elements\Price(Array(
			'name' => 'buyprice',
			'label' => _('TXT_BUYPRICE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_BUYPRICE')),
				new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
			),
			'filters' => Array(
				new FormEngine\Filters\CommaToDotChanger()
			),
			'vat_field' => $vat
		)));
		
		$standardPrice = $pricePane->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'standard_price',
			'label' => _('TXT_STANDARD_SELLPRICE'),
			'class' => 'priceGroup'
		)));
		
		$price = $standardPrice->AddChild(new FormEngine\Elements\Price(Array(
			'name' => 'sellprice',
			'label' => _('TXT_SELLPRICE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_SELLPRICE')),
				new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
			),
			'vat_field' => $vat
		)));
		
		$enablePromotion = $standardPrice->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'promotion',
			'label' => _('TXT_ENABLE_PROMOTION'),
			'default' => '0'
		)));
		
		$standardPrice->AddChild(new FormEngine\Elements\Price(Array(
			'name' => 'discountprice',
			'label' => _('TXT_DISCOUNTPRICE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_SELLPRICE')),
				new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
			),
			'vat_field' => $vat,
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::SHOW, $enablePromotion, new FormEngine\Conditions\Equals(1))
			)
		)));
		
		$standardPrice->AddChild(new FormEngine\Elements\Date(Array(
			'name' => 'promotionstart',
			'label' => _('TXT_START_DATE'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::SHOW, $enablePromotion, new FormEngine\Conditions\Equals(1))
			)
		)));
		
		$standardPrice->AddChild(new FormEngine\Elements\Date(Array(
			'name' => 'promotionend',
			'label' => _('TXT_END_DATE'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::SHOW, $enablePromotion, new FormEngine\Conditions\Equals(1))
			)
		)));
		
		$pricePane->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center"><strong>Ceny dla grup klientów</strong><br />Jeżeli chcesz aby dana grupa klientów posiadała inne ceny, uzupełnij wybrane sekcje poniżej.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$clientGroups = App::getModel('clientgroup/clientgroup')->getClientGroupAll();
		
		foreach ($clientGroups as $clientGroup){
			$group = $pricePane->AddChild(new FormEngine\Elements\Fieldset(Array(
				'name' => 'field_' . $clientGroup['id'],
				'label' => $clientGroup['name'],
				'class' => 'priceGroup'
			)));
			
			$groups[$clientGroup['id']] = $group->AddChild(new FormEngine\Elements\Checkbox(Array(
				'name' => 'groupid_' . $clientGroup['id'],
				'label' => _('TXT_ENABLE_GROUP_PRICE'),
				'default' => '0'
			)));
			
			$group->AddChild(new FormEngine\Elements\Price(Array(
				'name' => 'sellprice_' . $clientGroup['id'],
				'label' => _('TXT_SELLPRICE'),
				'rules' => Array(
					new FormEngine\Rules\Required(_('ERR_EMPTY_SELLPRICE')),
					new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
				),
				'vat_field' => $vat,
				'dependencies' => Array(
					new FormEngine\Dependency(FormEngine\Dependency::HIDE, $groups[$clientGroup['id']], new FormEngine\Conditions\Not(new FormEngine\Conditions\Equals(1)))
				)
			)));
			
			$promotion[$clientGroup['id']] = $group->AddChild(new FormEngine\Elements\Checkbox(Array(
				'name' => 'promotion_' . $clientGroup['id'],
				'label' => _('TXT_ENABLE_CLIENTGROUP_PROMOTION'),
				'default' => '0'
			)));
			
			$group->AddChild(new FormEngine\Elements\Price(Array(
				'name' => 'discountprice_' . $clientGroup['id'],
				'label' => _('TXT_DISCOUNTPRICE'),
				'rules' => Array(
					new FormEngine\Rules\Required(_('ERR_EMPTY_SELLPRICE')),
					new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
				),
				'vat_field' => $vat,
				'dependencies' => Array(
					new FormEngine\Dependency(FormEngine\Dependency::SHOW, $promotion[$clientGroup['id']], new FormEngine\Conditions\Equals(1))
				)
			)));
			
			$group->AddChild(new FormEngine\Elements\Date(Array(
				'name' => 'promotionstart_' . $clientGroup['id'],
				'label' => _('TXT_START_DATE'),
				'dependencies' => Array(
					new FormEngine\Dependency(FormEngine\Dependency::SHOW, $promotion[$clientGroup['id']], new FormEngine\Conditions\Equals(1))
				)
			)));
			
			$group->AddChild(new FormEngine\Elements\Date(Array(
				'name' => 'promotionend_' . $clientGroup['id'],
				'label' => _('TXT_END_DATE'),
				'dependencies' => Array(
					new FormEngine\Dependency(FormEngine\Dependency::SHOW, $promotion[$clientGroup['id']], new FormEngine\Conditions\Equals(1))
				)
			)));
		}
		
		$weightPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'weight_pane',
			'label' => _('TXT_WEIGHT_DATA')
		)));
		
		$weightPane->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'weight',
			'label' => _('TXT_WEIGHT'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_WEIGHT')),
				new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
			),
			'suffix' => 'kg',
			'filters' => Array(
				new FormEngine\Filters\CommaToDotChanger()
			),
			'default' => 0
		)));
		
		$weightPane->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'width',
			'label' => _('TXT_WIDTH'),
			'suffix' => 'cm',
			'filters' => Array(
				new FormEngine\Filters\CommaToDotChanger()
			)
		)));
		
		$weightPane->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'height',
			'label' => _('TXT_HEIGHT'),
			'suffix' => 'cm',
			'filters' => Array(
				new FormEngine\Filters\CommaToDotChanger()
			)
		)));
		
		$weightPane->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'deepth',
			'label' => _('TXT_DEEPTH'),
			'suffix' => 'cm',
			'filters' => Array(
				new FormEngine\Filters\CommaToDotChanger()
			)
		)));
		
		$weightPane->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Wybierz miarę produktu w jakiej sprzedawany jest produkt. Jednostkami możesz zarządzać na stronie <a href="' . $this->registry->router->generate('admin', true, Array(
				'controller' => 'unitmeasure'
			)) . '" target="_blank">Katalog &raquo; Jednostka miary</a>. Jeżeli chcesz tylko dodać nową opcję, użyj ikony dodawania obok listy wyboru.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$weightPane->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'unit',
			'label' => _('TXT_UNIT_MEASURE'),
			'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + App::getModel('unitmeasure')->getUnitMeasureToSelect()),
			'addable' => true,
			'onAdd' => 'xajax_AddUnitMeasure',
			'add_item_prompt' => 'Podaj nazwę jednostki miary',
			'default' => 0
		)));
		
		$weightPane->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Wpisz ilość sztuk w opakowaniu. Podczas dodawania produktu do koszyka ilość zostanie zaokrąglona do pełnych opakowań</p>'
		)));
		
		$weightPane->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'packagesize',
			'label' => _('TXT_PACKAGE_SIZE'),
			'comment' => _('TXT_PACKAGE_SIZE_HELP'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PACKAGE_SIZE')),
				new FormEngine\Rules\Format(_('ERR_NUMERIC_INVALID'), '/[0-9]{1,}/')
			),
			'filters' => Array(
				new FormEngine\Filters\CommaToDotChanger()
			),
			'default' => 1
		)));
		
		$descriptionPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'description_pane',
			'label' => _('TXT_DESCRIPTION')
		)));
		
		$descriptionLanguageData = $descriptionPane->AddChild(new FormEngine\Elements\FieldsetLanguage(Array(
			'name' => 'language_data',
			'label' => _('TXT_LANGUAGE_DATA')
		)));
		
		$descriptionLanguageData->AddChild(new FormEngine\Elements\RichTextEditor(Array(
			'name' => 'shortdescription',
			'label' => _('TXT_SHORTDESCRIPTION'),
			'comment' => _('TXT_MAX_LENGTH') . ' 1000',
			'max_length' => 1000,
			'rows' => 20
		)));
		
		$descriptionLanguageData->AddChild(new FormEngine\Elements\RichTextEditor(Array(
			'name' => 'description',
			'label' => _('TXT_DESCRIPTION'),
			'comment' => _('TXT_MAX_LENGTH') . ' 5000',
			'max_length' => 5000,
			'rows' => 30
		)));
		
		$descriptionLanguageData->AddChild(new FormEngine\Elements\RichTextEditor(Array(
			'name' => 'longdescription',
			'label' => _('TXT_ADDITIONAL_INFO'),
			'rows' => 30
		)));
		
		$photosPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'photos_pane',
			'label' => _('TXT_PHOTOS')
		)));
		
		$photosPane->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Wybierz zdjęcia dla produktu z biblioteki lub wgraj je z dysku komputera. Zdjęcie oznaczone jako główne będzie wyświetlane w listach produktów oraz w karcie produktu jako pierwsze.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$photosPane->AddChild(new FormEngine\Elements\Image(Array(
			'name' => 'photo',
			'label' => _('TXT_PHOTOS'),
			'repeat_min' => 0,
			'repeat_max' => FormEngine\FE::INFINITE,
			'upload_url' => App::getURLAdressWithAdminPane() . 'files/add',
			'main_id' => isset($this->populateData['photos_pane']['mainphotoid']) ? $this->populateData['photos_pane']['mainphotoid'] : ''
		)));
		
		$filePane = $form->addChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'files_pane',
			'label' => _('TXT_FILES')
		)));
		
		$filePane->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Wybierz pliki z biblioteki, które chcesz przypisać do tego produktu.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$filePane->AddChild(new FormEngine\Elements\Downloader(Array(
			'name' => 'file',
			'label' => _('TXT_FILES'),
			'repeat_min' => 0,
			'repeat_max' => FormEngine\FE::INFINITE,
			'upload_url' => App::getURLAdressWithAdminPane() . 'virtualproduct/add'
		)));
    
    $warrantyPane = $form->addChild(new FormEngine\Elements\Fieldset(Array(
      'name' => 'warranty_pane',
      'label' => _('TXT_WARRANTY')
    )));
    
    $warrantyPane->AddChild(new FormEngine\Elements\Tip(Array(
      'tip' => '<p align="center">Wybierz pliki z biblioteki, które chcesz przypisać do tego produktu.</p>',
      'direction' => FormEngine\Elements\Tip::DOWN
    )));
    
    $warrantyPane->AddChild(new FormEngine\Elements\Downloader(Array(
      'name' => 'warranty',
      'label' => _('TXT_WARRANTY'),
      'repeat_min' => 0,
      'repeat_max' => FormEngine\FE::INFINITE,
      'upload_url' => App::getURLAdressWithAdminPane() . 'virtualproduct/add'
    )));
		
		$upsellProducts = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'upsell_products',
			'label' => _('TXT_UPSELL')
		)));
		
		$upsellProducts->AddChild(new FormEngine\Elements\ProductSelectRelated(Array(
			'name' => 'upsell',
			'repeat_min' => 1,
			'repeat_max' => FormEngine\FE::INFINITE,
			'exclude' => Array(
				(int) $this->registry->core->getParam()
			)
		)));
		
		$similarProducts = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'similar_products',
			'label' => _('TXT_SIMILAR_PRODUCT_LIST')
		)));
		
		$similarProducts->AddChild(new FormEngine\Elements\ProductSelectRelated(Array(
			'name' => 'similar',
			'repeat_min' => 1,
			'repeat_max' => FormEngine\FE::INFINITE,
			'exclude' => Array(
				(int) $this->registry->core->getParam()
			)
		)));
		
		$crosssellProducts = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'crosssell_products',
			'label' => _('TXT_CROSSSELL')
		)));
		
		$crosssellProducts->AddChild(new FormEngine\Elements\ProductSelectRelated(Array(
			'name' => 'crosssell',
			'repeat_min' => 1,
			'repeat_max' => FormEngine\FE::INFINITE,
			'exclude' => Array(
				(int) $this->registry->core->getParam()
			)
		)));
		
		$statusProductPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'statusproduct_pane',
			'label' => _('TXT_PRODUCT_STATUS')
		)));
		
		$statusProductPane->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Statusy produktów są używane do wyświetlania specjalnych oznaczeń na listach i kartach produktów.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$statusProductPane->AddChild(new FormEngine\Elements\MultiSelect(Array(
			'name' => 'productstatuses',
			'label' => _('TXT_PRODUCT_STATUS'),
			'addable' => true,
			'onAdd' => 'xajax_AddProductStatus',
			'add_item_prompt' => 'Podaj nazwę statusu',
			'options' => FormEngine\Option::Make(App::getModel('productstatus')->getProductstatusAll(false))
		)));
		
		$idnew = $statusProductPane->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'newactive',
			'label' => _('TXT_PRODUCT_IS_NEW')
		)));
		
		$newData = $statusProductPane->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'new_data',
			'label' => _('TXT_NEW_DATA'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::HIDE, $idnew, new FormEngine\Conditions\Not(new FormEngine\Conditions\Equals('1')))
			)
		)));
		
		$newData->AddChild(new FormEngine\Elements\Date(Array(
			'name' => 'startnew',
			'label' => _('TXT_START_DATE')
		)));
		
		$newData->AddChild(new FormEngine\Elements\Date(Array(
			'name' => 'endnew',
			'label' => _('TXT_END_DATE')
		)));
		
		$groups = App::getModel('attributegroup/attributegroup')->getGroupsForCategory(0);
		
		if (! empty($groups)){
			
			$variantsPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
				'name' => 'variants_pane',
				'label' => _('TXT_PRODUCT_VARIANTS')
			)));
			
			$variantsPane->AddChild(new FormEngine\Elements\ProductVariantsEditor(Array(
				'name' => 'variants',
				'label' => _('TXT_PRODUCT_VARIANTS'),
				'category' => $category,
				'price' => $price,
				'set' => $set,
				'vat_field' => $vat,
				'availablity' => $availablity,
				'photos' => App::getModel('product')->productSelectedPhotos((int) $this->registry->core->getParam()),
				'allow_generate' => App::getModel('order')->checkProductWithAttributes((int) $this->registry->core->getParam())
			)));
		}
		
		$Data = Event::dispatch($this, 'admin.product.initForm', Array(
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
