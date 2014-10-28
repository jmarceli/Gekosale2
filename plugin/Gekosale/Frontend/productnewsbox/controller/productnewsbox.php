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
//  * version 2.1 of the License, or (at your option) any later version.
 *
 *
 * $Revision: 627 $
 * $Author: gekosale $
 * $Date: 2012-01-20 23:05:57 +0100 (Pt, 20 sty 2012) $
 * $Id: productnewsbox.php 627 2012-01-20 22:05:57Z gekosale $
 */
namespace Gekosale;

class ProductNewsBoxController extends Component\Controller\Box
{
  public function __construct ($registry, $box)
	{
		parent::__construct($registry, $box);
		$this->model = App::getModel('productnews');
    $this->controller = $this->registry->router->getCurrentController();
		
		$this->_currentParams = Array(
			'currentPage' => $this->getParam('currentPage', 1),
			'viewType' => $this->getParam('viewType', $this->_boxAttributes['view']),
			'priceFrom' => $this->getParam('priceFrom', 0),
			'priceTo' => $this->getParam('priceTo', Core::PRICE_MAX),
			'producers' => $this->getParam('producers', 0),
			'orderBy' => $this->getParam('orderBy', 'default'),
			'orderDir' => $this->getParam('orderDir', 'asc'),
			'attributes' => $this->getParam('attributes', 0)
		);

		$this->getProductsTemplate();
	}

	public function index ()
	{
		if ($this->controller != 'productnews'){
			$this->_boxAttributes['pagination'] = 0;
      $this->registry->template->assign('view', $this->_boxAttributes['view']);
		}
    else {
      $this->registry->template->assign('view', $this->_currentParams['viewType']);
    }
		$this->registry->template->assign('pagination', $this->_boxAttributes['pagination']);
		$this->registry->template->assign('dataset', $this->dataset);
		$this->registry->template->assign('items', $this->dataset['rows']);
    $this->registry->template->assign('viewSwitcher', $this->createViewSwitcher());
    $this->registry->template->assign('sorting', $this->createSorting());
		$this->registry->template->assign('paginationLinks', $this->createPaginationLinks());
		return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}

  protected function getProductsTemplate ()
	{
		$this->dataset = App::getModel('productnews')->getDataset();
		if ($this->_boxAttributes['productsCount'] > 0){
			$this->dataset->setPagination($this->_boxAttributes['productsCount']);
		}

    if($this->controller == 'productnews') {
      // only for product news page use datagrid custom parameters

      $producer = (strlen($this->_currentParams['producers']) > 0) ? array_filter(array_values(explode('_', $this->_currentParams['producers']))) : Array();
      $attributes = array_filter((strlen($this->_currentParams['attributes']) > 0) ? array_filter(array_values(explode('_', $this->_currentParams['attributes']))) : Array());
      
      $Products = App::getModel('layerednavigationbox')->getProductsForAttributes(0, $attributes);
      $this->dataset->setSQLParams(Array(
        'clientid' => Session::getActiveClientid(),
        'producer' => $producer,
        'pricefrom' => (float) $this->_currentParams['priceFrom'],
        'priceto' => (float) $this->_currentParams['priceTo'],
        'filterbyproducer' => (! empty($producer)) ? 1 : 0,
        'enablelayer' => (! empty($Products) && (count($attributes) > 0)) ? 1 : 0,
        'products' => $Products
      ));
      $this->dataset->setCurrentPage($this->_currentParams['currentPage']);
      if($this->_currentParams['orderBy'] == 'default') {
        $this->dataset->setOrderBy('name', $this->_boxAttributes['orderBy']);
        $this->dataset->setOrderDir('asc', $this->_boxAttributes['orderDir']);
      }
      $this->dataset->setOrderBy('name', $this->_currentParams['orderBy']);
      $this->dataset->setOrderDir('asc', $this->_currentParams['orderDir']);
    }
    else {
      $this->dataset->setCurrentPage(1);
			$this->dataset->setOrderBy($this->_boxAttributes['orderBy'], $this->_boxAttributes['orderBy']);
			$this->dataset->setOrderDir($this->_boxAttributes['orderDir'], $this->_boxAttributes['orderDir']);
    }

		$this->dataset = App::getModel('productnews')->getProductDataset();
	}

	protected function createPaginationLinks ()
	{
    return App::getModel('productlist')->createPaginationLinks('productnews', $this->_currentParams, $this->dataset['totalPages']);
	}

	protected function createSorting ()
	{
    return App::getModel('productlist')->createSorting('productnews', $this->_currentParams, 0);
	}

	protected function createViewSwitcher ()
	{
    return App::getModel('productlist')->createViewSwitcher('productnews', $this->_currentParams);
	}


  public function getBoxTypeClassname ()
  {
    if ($this->dataset['total'] > 0){
      return 'layout-box-type-product-list';
    }
  }

  public function boxVisible ()
  {
    if ($this->controller == 'productnews'){
      return true;
    }
    return ($this->dataset['total'] > 0) ? true : false;
  }
}
