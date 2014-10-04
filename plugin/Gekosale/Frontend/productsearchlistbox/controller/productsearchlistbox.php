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
 * $Revision: 616 $
 * $Author: gekosale $
 * $Date: 2011-12-05 10:13:27 +0100 (Pn, 05 gru 2011) $
 * $Id: productsearchlistbox.php 616 2011-12-05 09:13:27Z gekosale $
 */
namespace Gekosale;

class ProductSearchListBoxController extends Component\Controller\Box
{
	protected $_currentParams = Array();

	public function __construct ($registry, $box)
	{
		parent::__construct($registry, $box);
		$this->dataset = Array();
	}

	public function index ()
	{
		$this->searchPhrase = App::getModel('formprotection')->cropDangerousCode($this->getParam());

		$this->view = $this->getParam('viewType', $this->_boxAttributes['view']);
		
    $this->orderBy = $this->getParam('orderBy', 'default');
    $this->orderDir = $this->getParam('orderDir', 'asc');
    $this->currentPage = $this->getParam('currentPage', 1);
		$this->producers = $this->getParam('producers', 0);
		$this->attributes = $this->getParam('attributes', 0);
		
		$this->priceFrom = $this->getParam('priceFrom', 0);
		$this->priceTo = $this->getParam('priceTo', Core::PRICE_MAX);
		
    $this->_currentParams = Array(
      'action' => 'index',
      'param' => $this->searchPhrase,
      'currentPage' => $this->currentPage,
      'viewType' => $this->view,
      'priceFrom' => $this->priceFrom,
      'priceTo' => $this->priceTo,
      'producers' => $this->producers,
      'orderBy' => $this->orderBy,
      'orderDir' => $this->orderDir
    );
		
    $this->dataset = App::getModel('productsearch')->search($this->searchPhrase, $this->producers, $this->attributes, $this->priceFrom, $this->priceTo, $this->_boxAttributes['productsCount'], $this->currentPage);
		
		$this->registry->template->assign('phrase', $this->searchPhrase);
		$this->registry->template->assign('showpagination', $this->_boxAttributes['pagination']);
		$this->registry->template->assign('sorting', $this->createSorting());
		$this->registry->template->assign('viewSwitcher', $this->createViewSwitcher());
		$this->registry->template->assign('view', $this->view);
		$this->registry->template->assign('orderBy', $this->orderBy);
		$this->registry->template->assign('orderDir', $this->orderDir);
		$this->registry->template->assign('producers', App::getModel('product')->getProducerAll());
		$this->registry->template->assign('dataset', $this->dataset);
		$this->registry->template->assign('pagination', $this->_boxAttributes['pagination']);
		$this->registry->template->assign('paginationLinks', $this->createPaginationLinks());
		
		//if ($this->dataset['total'] == 0){
			//App::redirectUrl($this->registry->router->generate('frontend.productsearch', true, Array(
				//'action' => 'noresults',
				//'param' => $this->searchPhrase
			//)));
		//}
		
		return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}

	public function getBoxTypeClassname ()
	{
		return 'layout-box-type-product-list';
	}

	protected function createPaginationLinks ()
	{
		$currentParams = $this->_currentParams;
		
		$paginationLinks = Array();
		
		if ($this->dataset['totalPages'] > 1){
			
			$currentParams['currentPage'] = $this->currentPage - 1;
			
			$paginationLinks['previous'] = Array(
				'link' => ($this->currentPage > 1) ? $this->registry->router->generate('frontend.productsearch', true, $currentParams) : '',
				'class' => ($this->currentPage > 1) ? 'previous' : 'previous disabled',
				'label' => _('TXT_PREVIOUS')
			);
		}
		
		foreach ($this->dataset['totalPages'] as $page){
			
			$currentParams['currentPage'] = $page;
			
			$paginationLinks[$page] = Array(
				'link' => $this->registry->router->generate('frontend.productsearch', true, $currentParams),
				'class' => ($this->currentPage == $page) ? 'active' : '',
				'label' => $page
			);
		}
		
		if ($this->dataset['totalPages'] > 1){
			
			$currentParams['currentPage'] = $this->currentPage + 1;
			
			$paginationLinks['next'] = Array(
				'link' => ($this->currentPage < end($this->dataset['totalPages'])) ? $this->registry->router->generate('frontend.productsearch', true, $currentParams) : '',
				'class' => ($this->currentPage < end($this->dataset['totalPages'])) ? 'next' : 'next disabled',
				'label' => _('TXT_NEXT')
			);
		}
		
		return $paginationLinks;
	}

	protected function createSorting ()
	{
		$columns = Array(
			'name' => _('TXT_NAME'),
			'price' => _('TXT_PRICE'),
			'rating' => _('TXT_AVERAGE_OPINION'),
			'opinions' => _('TXT_OPINIONS_QTY'),
			'adddate' => _('TXT_ADDDATE')
		);
		
		$directions = Array(
			'asc' => _('TXT_ASC'),
			'desc' => _('TXT_DESC')
		);
		
		$sorting = Array();
		
		$currentParams = $this->_currentParams;
		
		$currentParams['orderBy'] = 'default';
		$currentParams['orderDir'] = 'asc';
		
		$sorting[] = Array(
			'link' => $this->registry->router->generate('frontend.productsearch', true, $currentParams),
			'label' => _('TXT_DEFAULT'),
			'active' => ($this->orderBy == 'default' && $this->orderDir == 'asc') ? true : false
		);
		
		foreach ($columns as $orderBy => $orderByLabel){
			foreach ($directions as $orderDir => $orderDirLabel){
				
				$currentParams['orderBy'] = $orderBy;
				$currentParams['orderDir'] = $orderDir;
				
				$sorting[] = Array(
					'link' => $this->registry->router->generate('frontend.productsearch', true, $currentParams),
					'label' => $orderByLabel . ' - ' . $orderDirLabel,
					'active' => ($this->orderBy == $orderBy && $this->orderDir == $orderDir) ? true : false
				);
			}
		}
		
		return $sorting;
	}

	protected function createViewSwitcher ()
	{
		$viewTypes = Array(
			0 => _('TXT_VIEW_GRID'),
			1 => _('TXT_VIEW_LIST')
		);
		
		$switcher = Array();
		
		$currentParams = $this->_currentParams;
		
		foreach ($viewTypes as $view => $label){
			
			$currentParams['viewType'] = $view;
			
			$switcher[] = Array(
				'link' => $this->registry->router->generate('frontend.productsearch', true, $currentParams),
				'label' => $label,
				'type' => $view,
				'active' => ($this->view == $view) ? true : false
			);
		}
		
		return $switcher;
	}
}
