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
 * $Revision: 590 $
 * $Author: gekosale $
 * $Date: 2011-10-29 18:40:11 +0200 (So, 29 paź 2011) $
 * $Id: layoutbox.php 590 2011-10-29 16:40:11Z gekosale $ 
 */
namespace Gekosale;

use FormEngine;

class LayoutBoxController extends Component\Controller\Admin
{

	public function __construct ($registry)
	{
		parent::__construct($registry);
		$this->model = App::getModel('layoutbox');
		$this->categories = App::getModel('category')->getChildCategories();
		$this->categoryActive = 0;
	}

	public function index ()
	{
		if ($this->registry->core->getParam(0) == ''){
			if (Helper::getViewId() > 0){
				App::redirect(__ADMINPANE__ . '/layoutbox/edit/' . $this->registry->loader->getParam('pageschemeid'));
			}
			else{
				$tree = $this->model->getLayoutBoxTree();
				if (count($tree) > 0){
					App::redirect(__ADMINPANE__ . '/layoutbox/edit/' . current(array_keys($tree)));
				}
			}
		}
	}

	public function add ()
	{
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'layoutbox',
			'action' => '',
			'method' => 'post'
		));
		
		$contentTypes = $this->model->getLayoutBoxContentTypeOptions();
		
		$boxAdd = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'box',
			'label' => _('TXT_BOX_SETTINGS')
		)));
		
		$boxAdd->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'name',
			'label' => _('TXT_NAME'),
			'comment' => 'Wewnętrzna nazwa boksu, niewidoczna dla Klientów',
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME'))
			)
		)));
		
		$title = $boxAdd->AddChild(new FormEngine\Elements\FieldsetLanguage(Array(
			'name' => 'language_data'
		)));
		
		$title->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'title',
			'label' => _('TXT_BOX_TITLE'),
			'comment' => 'Tytuł boksu, który zobaczą Klienci',
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_BOX_TITLE'))
			)
		)));
		
		$boxContent = $boxAdd->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'box_content',
			'label' => _('TXT_BOX_CONTENT'),
			'options' => FormEngine\Option::Make($this->model->getLayoutBoxContentTypeOptionsAllToSelect()),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_BOX_CONTENT'))
			)
		)));
		
		$boxAdd->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'pageschemeid',
			'label' => _('TXT_PAGESCHEME'),
			'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + App::getModel('pagescheme')->getPageschemeAllToSelect()),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PAGESCHEME'))
			)
		)));
		
		$this->_addContentTypeSpecificFields($form, $boxContent, $contentTypes);
		
		$boxBehaviourEdit = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'behaviour',
			'label' => _('TXT_BOX_BEHAVOIUR')
		)));
		
		$boxBehaviourEdit->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'bNoHeader',
			'label' => 'Wyświetlaj nagłówek',
			'options' => Array(
				new FormEngine\Option('0', 'Tak'),
				new FormEngine\Option('1', 'Nie')
			)
		)));
		
		$boxBehaviourEdit->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'iDefaultSpan',
			'label' => 'Domyślne rozciągnięcie',
			'comment' => 'Wpisz liczbę kolumn',
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_VALUE_INVALID'), '/^(([0-9]{1,2})|(\0)?)$/')
			)
		)));
		
		$boxBehaviourEdit->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'iEnableBox',
			'label' => 'Wyświetlanie boksu',
			'options' => Array(
				new FormEngine\Option('0', 'Dla wszystkich'),
				new FormEngine\Option('1', 'Dla zalogowanych'),
				new FormEngine\Option('2', 'Dla niezalogowanych'),
				new FormEngine\Option('3', 'Nie wyświetlaj')
			)
		)));
		
		// /////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$populate = Array(
			'box' => Array(
				'box_content' => '1',
				'pageschemeid' => '0'
			),
			'behaviour' => Array(
				'bNoHeader' => 0,
				'iDefaultSpan' => '1',
				'iEnableBox' => 0
			)
		);
		
		$populate = $this->_populateContentTypeFields($contentTypes, $populate);
		
		$form->Populate($populate);
		
		$form->AddFilter(new FormEngine\Filters\Trim());
		$form->AddFilter(new FormEngine\Filters\Secure());
		
		// /////////////////////////////////////////////////////////////////////////////////////////////////////
		if ($form->Validate(FormEngine\FE::SubmittedData())){
			$this->model->addNewLayoutBox($this->_performArtificialMechanics($form->getSubmitValues(FormEngine\Elements\Form::FORMAT_FLAT)));
			App::redirect(__ADMINPANE__ . '/layoutbox');
		}
		$this->registry->xajax->processRequest();
		$this->registry->template->assign('xajax', $this->registry->xajax->getJavascript());
		$this->registry->template->assign('form', $form->Render());
		$this->registry->template->assign('id', 'new');
		$this->registry->template->display($this->loadTemplate('add.tpl'));
	}

	public function edit ()
	{
		$this->registry->xajax->registerFunction(Array(
			'DeleteLayoutBox',
			$this->model,
			'deleteLayoutbox'
		));
		
    $layoutboxes = $this->model->getLayoutBoxTree();
    $pagescheme_id = $this->id; // selected by user
    if (Helper::getViewId() > 0 && $pagescheme_id != $this->registry->core->getParam(0)) {
      // if it is not current view pagescheme change it
      App::redirect(__ADMINPANE__ . '/layoutbox/edit/' . $pagescheme_id . ',' . $this->model->getFirstLayoutBox($pagescheme_id));
    }
		$layoutbox_id = $this->registry->core->getParam(1); // id from URL
		if ((int) $layoutbox_id == 0 || empty($layoutboxes[$pagescheme_id . ',' . $layoutbox_id])){
      // change pagescheme if subpage is not inside active
      $pagescheme_id = App::getModel('view')->getViewPagescheme(Helper::getViewId());
			App::redirect(__ADMINPANE__ . '/layoutbox/edit/' . $pagescheme_id . ',' . $this->model->getFirstLayoutBox($pagescheme_id));
		}

		$layoutBox = $this->model->getLayoutBoxToEdit($layoutbox_id);

		$behaviourBoxArray = $this->model->getLayoutBoxJSValuesToEdit($layoutbox_id);
		$ctValues = $this->model->getLayoutBoxContentTypeSpecificValues($layoutbox_id);
		
		if (isset($ctValues['categoryId']) && ($ctValues['categoryId'] > 0)){
			$this->categoryActive = $ctValues['categoryId'];
			$this->categories = App::getModel('category')->getChildCategories(0, Array(
				$this->categoryActive
			));
		}
		
		$tree = new FormEngine\Elements\Form(Array(
			'name' => 'scheme_tree',
			'action' => '',
			'method' => 'post'
		));
		
		$tree->AddChild(new FormEngine\Elements\Tree(Array(
			'name' => 'pagescheme',
			'sortable' => false,
			'retractable' => false,
			'selectable' => false,
			'clickable' => true,
			'deletable' => true,
			'addable' => false,
			'items' => $layoutboxes,
			'onClick' => 'openLayoutBoxEditor',
			'active' => $pagescheme_id . ',' . $layoutbox_id
		)));
		
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'layoutbox',
			'action' => '',
			'method' => 'post'
		));
		
		$contentTypes = $this->model->getLayoutBoxContentTypeOptions();
		
		$boxEdit = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'box',
			'label' => _('TXT_BOX_SETTINGS')
		)));
		
		$boxEdit->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'name',
			'label' => _('TXT_NAME'),
			'comment' => 'Wewnętrzna nazwa boksu, niewidoczna dla Klientów',
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME'))
			)
		)));
		
		$title = $boxEdit->AddChild(new FormEngine\Elements\FieldsetLanguage(Array(
			'name' => 'language_data'
		)));
		
		$title->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'title',
			'label' => _('TXT_BOX_TITLE'),
			'comment' => 'Tytuł boksu, który zobaczą Klienci',
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_BOX_TITLE'))
			)
		)));
		
		$boxContent = $boxEdit->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'box_content',
			'label' => _('TXT_BOX_CONTENT'),
			'options' => FormEngine\Option::Make($this->model->getLayoutBoxContentTypeOptionsAllToSelect()),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_BOX_CONTENT'))
			)
		)));
		
		$this->_addContentTypeSpecificFields($form, $boxContent, $contentTypes);
		
		$boxBehaviourEdit = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'behaviour',
			'label' => _('TXT_BOX_BEHAVOIUR')
		)));
		
		$boxBehaviourEdit->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'bNoHeader',
			'label' => 'Wyświetlaj nagłówek',
			'options' => Array(
				new FormEngine\Option('0', 'Tak'),
				new FormEngine\Option('1', 'Nie')
			)
		)));
		
		$boxBehaviourEdit->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'iDefaultSpan',
			'label' => 'Domyślne rozciągnięcie',
			'comment' => 'Wpisz liczbę kolumn',
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_VALUE_INVALID'), '/^(([0-9]{1,2})|(\0)?)$/')
			)
		)));
		
		$boxBehaviourEdit->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'iEnableBox',
			'label' => 'Wyświetlanie boksu',
			'options' => Array(
				new FormEngine\Option('0', 'Dla wszystkich'),
				new FormEngine\Option('1', 'Dla zalogowanych'),
				new FormEngine\Option('2', 'Dla niezalogowanych'),
				new FormEngine\Option('3', 'Nie wyświetlaj')
			)
		)));
		
		$populate = Array(
			'box' => Array(
				'name' => $layoutBox['name'],
				'language_data' => Array(
					'title' => $layoutBox['title']
				),
				'box_content' => $layoutBox['controller']
			),
			'behaviour' => Array(
				'bNoHeader' => 0,
				'iDefaultSpan' => '1',
				'iEnableBox' => 0
			)
		);
		
		if (isset($behaviourBoxArray) && count($behaviourBoxArray) > 0){
			foreach ($behaviourBoxArray as $js => $value){
				$populate['behaviour'][$js] = $value;
			}
		}
		$populate = $this->_populateContentTypeFields($contentTypes, $populate, $ctValues, $layoutBox['controller']);
		
		$form->Populate($populate);
		
		$form->AddFilter(new FormEngine\Filters\Trim());
		$form->AddFilter(new FormEngine\Filters\Secure());
		
		if ($form->Validate(FormEngine\FE::SubmittedData())){
			$this->model->editLayoutBox($this->_performArtificialMechanics($form->getSubmitValues(FormEngine\Elements\Form::FORMAT_FLAT)), $layoutbox_id);
			if (FormEngine\FE::IsAction('continue')){
				App::redirect(__ADMINPANE__ . '/layoutbox/edit/' . $pagescheme_id . ',' . $layoutbox_id);
			}
			else{
				App::redirect(__ADMINPANE__ . '/layoutbox');
			}
		}
		
		$this->renderLayout(Array(
			'tree' => $tree->Render(),
			'id' => $layoutbox_id,
			'form' => $form->Render()
		));
	}

	protected function _performArtificialMechanics ($data)
	{
		if (isset($data['db_border-radius']['value'])){
			$value = max(0, substr($data['db_border-radius']['value'], 0, - 2) - 1);
			$data['db_content_border-radius'] = Array(
				'selector' => '#layout-box-__id__ .layout-box-content',
				'bottom-left' => Array(
					'value' => "{$value}px"
				),
				'bottom-right' => Array(
					'value' => "{$value}px"
				)
			);
			$data['db_header_border-radius'] = Array(
				'selector' => '#layout-box-__id__ .layout-box-header',
				'top-left' => Array(
					'value' => "{$value}px"
				),
				'top-right' => Array(
					'value' => "{$value}px"
				)
			);
			$data['db_header_collapsed_border-radius'] = Array(
				'selector' => '#layout-box-__id__.layout-box-collapsed .layout-box-header, #layout-box-__id__.layout-box-option-header-false .layout-box-content',
				'top-left' => Array(
					'value' => "{$value}px"
				),
				'top-right' => Array(
					'value' => "{$value}px"
				),
				'bottom-left' => Array(
					'value' => "{$value}px"
				),
				'bottom-right' => Array(
					'value' => "{$value}px"
				)
			);
		}
		if (isset($data['db_header_line-height'])){
			$data['db_icon_height'] = Array(
				'selector' => '#layout-box-__id__ .layout-box-icons .layout-box-icon',
				'value' => $data['db_header_line-height']['value']
			);
		}
		return $data;
	}

	protected function _addContentTypeSpecificFields ($form, $boxContent, $contentTypes)
	{
		foreach ($contentTypes as $controller => $contentType){
			if (file_exists(ROOTPATH . 'plugin' . DS . 'Gekosale' . DS . 'Admin' . DS . $this->getName() . DS . 'model' . DS . strtolower($controller) . '.php')){
				$function = Array(
					App::getModel('layoutbox/' . strtolower($controller)),
					"_addFieldsContentType{$controller}"
				);
				if (is_callable($function)){
					call_user_func($function, $form, $boxContent);
				}
			}
		}
	}

	protected function _populateContentTypeFields ($contentTypes, &$populate, $ctValues = Array(), $currentContentType = 0)
	{
		foreach ($contentTypes as $controller => $translation){
			if (file_exists(ROOTPATH . 'plugin' . DS . 'Gekosale' . DS . 'Admin' . DS . $this->getName() . DS . 'model' . DS . strtolower($controller) . '.php')){
				$function = Array(
					App::getModel('layoutbox/' . strtolower($controller)),
					"_populateFieldsContentType{$controller}"
				);
				if (is_callable($function)){
					$populate = call_user_func($function, $populate, ($controller == $currentContentType) ? $ctValues : Array());
				}
			}
		}
		return $populate;
	}
}
