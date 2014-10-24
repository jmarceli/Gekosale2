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
 * $Revision: 627 $
 * $Author: gekosale $
 * $Date: 2012-01-20 23:05:57 +0100 (Pt, 20 sty 2012) $
 * $Id: productsincategorybox.php 627 2012-01-20 22:05:57Z gekosale $
 */
namespace Gekosale;

class ProductsInCategoryBoxController extends Component\Controller\Box
{
	protected $_currentParams = Array();

	public function __construct ($registry, $box)
	{
		parent::__construct($registry, $box);
		$this->category = App::getModel('categorylist')->getCurrentCategory();
		$this->dataset = Array();
	}

	public function index ()
	{
		$this->_currentParams = Array(
			'param' => $this->category['seo'],
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
		
		$subcategories = App::getModel('categorylist')->getCategoryMenuTop($this->category['id']);
		
		if ($this->dataset['total'] > 0 || count($subcategories) > 0){
			$this->registry->template->assign('subcategories', array_chunk($subcategories, 3));
			$this->registry->template->assign('currentCategory', $this->category);
			$this->registry->template->assign('view', (int) $this->_currentParams['viewType']);
			$this->registry->template->assign('currentPage', $this->_currentParams['currentPage']);
			$this->registry->template->assign('orderBy', $this->_currentParams['orderBy']);
			$this->registry->template->assign('orderDir', $this->_currentParams['orderDir']);
			$this->registry->template->assign('currentProducers', $this->_currentParams['producers']);
			$this->registry->template->assign('currentAttributes', $this->_currentParams['attributes']);
			$this->registry->template->assign('sorting', $this->createSorting());
			$this->registry->template->assign('viewSwitcher', $this->createViewSwitcher());
			$this->registry->template->assign('dataset', $this->dataset);
			$this->registry->template->assign('pagination', $this->_boxAttributes['pagination']);
			$this->registry->template->assign('paginationLinks', $this->createPaginationLinks());
			$this->registry->template->assign('categoryPromotions', $this->getCategoryPromotions());
			$this->registry->template->assign('categoryNews', $this->getCategoryNews());
			return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
		}
		else{
			$this->registry->template->assign('currentCategory', $this->category);
			return $this->registry->template->fetch($this->loadTemplate('index_no_products.tpl'));
		}
	}

	public function getBoxHeading ()
	{
		return $this->category['name'];
	}

	public function getBoxTypeClassname ()
	{
		return 'layout-box-type-product-list';
	}

	protected function getCategoryPromotions ()
	{
		$dataset = App::getModel('productpromotion')->getDataset();
		$dataset->setPagination(4);
		$dataset->setOrderBy('random', 'random');
		$dataset->setCurrentPage(1);
		return App::getModel('productpromotion')->getProductDataset();
	}

	protected function getCategoryNews ()
	{
		$dataset = App::getModel('productnews')->getDataset();
		$dataset->setPagination(4);
		$dataset->setOrderBy('random', 'random');
		$dataset->setCurrentPage(1);
		return App::getModel('productnews')->getProductDataset();
	}

	protected function getProductsTemplate ()
	{
		$producer = (strlen($this->_currentParams['producers']) > 0) ? array_filter(array_values(explode('_', $this->_currentParams['producers']))) : Array();
		$attributes = array_filter((strlen($this->_currentParams['attributes']) > 0) ? array_filter(array_values(explode('_', $this->_currentParams['attributes']))) : Array());
		
		$Products = App::getModel('layerednavigationbox')->getProductsForAttributes((int) $this->category['id'], $attributes);
		
		$dataset = App::getModel('product')->getDataset();
		if ($this->_boxAttributes['productsCount'] > 0){
			$dataset->setPagination($this->_boxAttributes['productsCount']);
		}
		$dataset->setCurrentPage($this->_currentParams['currentPage']);
		$dataset->setOrderBy('name', $this->_currentParams['orderBy']);
		$dataset->setOrderDir('asc', $this->_currentParams['orderDir']);
		$dataset->setSQLParams(Array(
			'categoryid' => (int) $this->category['id'],
			'clientid' => Session::getActiveClientid(),
			'producer' => $producer,
			'pricefrom' => (float) $this->_currentParams['priceFrom'],
			'priceto' => (float) $this->_currentParams['priceTo'],
			'filterbyproducer' => (! empty($producer)) ? 1 : 0,
			'enablelayer' => (! empty($Products) && (count($attributes) > 0)) ? 1 : 0,
			'products' => $Products
		));
		$products = App::getModel('product')->getProductDataset();
		$this->dataset = $products;
		$this->registry->template->assign('items', $products['rows']);
		$this->registry->template->assign('view', $this->_currentParams['viewType']);
	}

	protected function createPaginationLinks ()
	{
    return App::getModel('productlist')->createPaginationLinks('categorylist', $this->_currentParams, $this->dataset['totalPages']);
	}

	protected function createSorting ()
	{
    return App::getModel('productlist')->createSorting('categorylist', $this->_currentParams);
	}

	protected function createViewSwitcher ()
	{
    return App::getModel('productlist')->createViewSwitcher('categorylist', $this->_currentParams);
	}
}
