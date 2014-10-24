<?php

/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2012 Gekosale. Zabronione jest usuwanie informacji o
 * licencji i autorach.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 *
 * $Revision: 602 $
 * $Author: gekosale $
 * $Date: 2011-11-07 22:45:33 +0100 (Pn, 07 lis 2011) $
 * $Id: productpromotionsbox.php 602 2011-11-07 21:45:33Z gekosale $
 */
namespace Gekosale;

class ProductPromotionsBoxController extends Component\Controller\Box
{

	public function __construct ($registry, $box)
	{
		parent::__construct($registry, $box);
		$this->model = App::getModel('productpromotion');

		$this->orderBy = $this->getParam('orderBy', 'default');
		$this->orderDir = $this->getParam('orderDir', 'asc');
    $this->currentPage = 1;
		$this->view = $this->getParam('viewType', $this->_boxAttributes['view']);
		
		$this->producers = $this->getParam('producers', 0);
		$this->attributes = $this->getParam('attributes', 0);
		
		$this->priceFrom = $this->getParam('priceFrom', 0);
		$this->priceTo = $this->getParam('priceTo', Core::PRICE_MAX);
		
		$this->_currentParams = Array(
			'currentPage' => $this->currentPage,
			'viewType' => $this->view,
			'priceFrom' => $this->priceFrom,
			'priceTo' => $this->priceTo,
			'producers' => $this->producers,
			'orderBy' => $this->orderBy,
			'orderDir' => $this->orderDir,
			'attributes' => $this->attributes
		);

		$this->getProductsTemplate();
	}

	public function index ()
	{
		if ($this->registry->router->getCurrentController() != 'productpromotion'){
			$this->_boxAttributes['pagination'] = 0;
      $this->registry->template->assign('view', $this->_boxAttributes['view']);
		}
    else {
      $this->registry->template->assign('view', $this->view);
    }
		$this->registry->template->assign('pagination', $this->_boxAttributes['pagination']);
		$this->registry->template->assign('dataset', $this->dataset);
    $this->registry->template->assign('viewSwitcher', $this->createViewSwitcher());
    $this->registry->template->assign('sorting', $this->createSorting());
		$this->registry->template->assign('paginationLinks', $this->createPaginationLinks());
		return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}

  protected function getProductsTemplate ()
	{
		$this->dataset = App::getModel('productpromotion')->getDataset();
		if ($this->_boxAttributes['productsCount'] > 0){
			$this->dataset->setPagination($this->_boxAttributes['productsCount']);
		}

    if($this->registry->router->getCurrentController() == 'productpromotion') {
      // only for product promotion page use datagrid custom parameters

      $producer = (strlen($this->producers) > 0) ? array_filter(array_values(explode('_', $this->producers))) : Array();
      $attributes = array_filter((strlen($this->attributes) > 0) ? array_filter(array_values(explode('_', $this->attributes))) : Array());
      
      $Products = App::getModel('layerednavigationbox')->getProductsForAttributes(0, $attributes);
      $this->dataset->setSQLParams(Array(
        'clientid' => Session::getActiveClientid(),
        'producer' => $producer,
        'pricefrom' => (float) $this->priceFrom,
        'priceto' => (float) $this->priceTo,
        'filterbyproducer' => (! empty($producer)) ? 1 : 0,
        'enablelayer' => (! empty($Products) && (count($attributes) > 0)) ? 1 : 0,
        'products' => $Products
      ));
      $this->dataset->setCurrentPage($this->currentPage);
      $this->dataset->setOrderBy('name', $this->orderBy);
      $this->dataset->setOrderDir('asc', $this->orderDir);
    }
    else {
      $this->dataset->setCurrentPage(1);
			$this->dataset->setOrderBy($this->_boxAttributes['orderBy'], $this->_boxAttributes['orderBy']);
			$this->dataset->setOrderDir($this->_boxAttributes['orderDir'], $this->_boxAttributes['orderDir']);
    }

		$products = App::getModel('productpromotion')->getProductDataset();
		$this->dataset = $products;
		$this->registry->template->assign('items', $products['rows']);
		$this->registry->template->assign('view', $this->view);
	}

	protected function createPaginationLinks ()
	{
		$currentParams = $this->_currentParams;
		
		$paginationLinks = Array();
		
		if ($this->dataset['totalPages'] > 1){
			
			$currentParams['currentPage'] = $this->currentPage - 1;
			
			$paginationLinks['previous'] = Array(
				'link' => ($this->currentPage > 1) ? $this->registry->router->generate('frontend.productpromotion', true, $currentParams) : '',
				'class' => ($this->currentPage > 1) ? 'previous' : 'previous disabled',
				'label' => _('TXT_PREVIOUS')
			);
		}
		
		foreach ($this->dataset['totalPages'] as $page){
			
			$currentParams['currentPage'] = $page;
			
			$paginationLinks[$page] = Array(
				'link' => $this->registry->router->generate('frontend.productpromotion', true, $currentParams),
				'class' => ($this->currentPage == $page) ? 'active' : '',
				'label' => $page
			);
		}
		
		if ($this->dataset['totalPages'] > 1){
			
			$currentParams['currentPage'] = $this->currentPage + 1;
			
			$paginationLinks['next'] = Array(
				'link' => ($this->currentPage < end($this->dataset['totalPages'])) ? $this->registry->router->generate('frontend.productpromotion', true, $currentParams) : '',
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
			'link' => $this->registry->router->generate('frontend.productpromotion', true, $currentParams),
			'label' => _('TXT_DEFAULT'),
			'active' => ($this->orderBy == 'default' && $this->orderDir == 'asc') ? true : false
		);
		
		foreach ($columns as $orderBy => $orderByLabel){
			foreach ($directions as $orderDir => $orderDirLabel){
				
				$currentParams['orderBy'] = $orderBy;
				$currentParams['orderDir'] = $orderDir;
				
				$sorting[] = Array(
					'link' => $this->registry->router->generate('frontend.productpromotion', true, $currentParams),
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
				'link' => $this->registry->router->generate('frontend.productpromotion', true, $currentParams),
				'label' => $label,
				'type' => $view,
				'active' => ($this->view == $view) ? true : false
			);
		}
		
		return $switcher;
	}


  public function getBoxTypeClassname ()
  {
    if ($this->dataset['total'] > 0){
      return 'layout-box-type-product-list';
    }
  }

  public function boxVisible ()
  {
    if ($this->registry->router->getCurrentController() == 'productpromotion'){
      return true;
    }
    return ($this->dataset['total'] > 0) ? true : false;
  }
}
