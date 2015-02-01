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
		$this->model = App::getModel('productsearchlist');
    $this->controller = $this->registry->router->getCurrentController();
	}

	public function index ()
	{
    $this->init();
		
		$this->dataset = $this->getProductsTemplate();

		if ($this->controller != 'productsearch'){
			$this->_boxAttributes['pagination'] = 0;
      $this->registry->template->assign('view', $this->_boxAttributes['view']);
		}
    else {
      $this->registry->template->assign('view', $this->_currentParams['viewType']);
    }
		$this->registry->template->assign('pagination', $this->_boxAttributes['pagination']);
		$this->registry->template->assign('dataset', $this->dataset);
    $this->registry->template->assign('viewSwitcher', $this->createViewSwitcher());
    $this->registry->template->assign('sorting', $this->createSorting());
		$this->registry->template->assign('paginationLinks', $this->createPaginationLinks());

    $this->registry->template->assign('phrase', $this->searchPhrase);
    $this->registry->template->assign('producers', App::getModel('product')->getProducerAll());
    return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}

	public function getBoxTypeClassname ()
	{
		return 'layout-box-type-product-list';
	}

  protected function init()
  {
    $this->searchPhrase = App::getModel('formprotection')->cropDangerousCode($this->getParam());

		$this->_currentParams = Array(
      'categoryid' => 0,
      'name' => '%' . $this->searchPhrase . '%',
      'currentPage' => $this->getParam('currentPage', 1),
      'viewType' => $this->getParam('viewType', $this->_boxAttributes['view']),
			'priceFrom' => $this->getParam('priceFrom', 0),
			'priceTo' => $this->getParam('priceTo', Core::PRICE_MAX),
			'producers' => $this->getParam('producers', 0),
			'orderBy' => $this->getParam('orderBy', 'default'),
			'orderDir' => $this->getParam('orderDir', 'asc'),
			'attributes' => $this->getParam('attributes', 0),
      'param' => $this->getParam()
		);
    $this->_boxAttributes['orderBy'] = $this->_currentParams['orderBy'];
    $this->_boxAttributes['orderDir'] = $this->_currentParams['orderDir'];
  }

	protected function getProductsTemplate ()
	{
    return App::getModel('productlist')->getProductsTemplate('productsearch', 'productsearch', $this->_currentParams, $this->_boxAttributes);
	}

	protected function createPaginationLinks ()
	{
    return App::getModel('productlist')->createPaginationLinks('productsearch', $this->_currentParams, $this->dataset['totalPages']);
	}

	protected function createSorting ()
	{
    return App::getModel('productlist')->createSorting('productsearch', $this->_currentParams);
	}

	protected function createViewSwitcher ()
	{
    return App::getModel('productlist')->createViewSwitcher('productsearch', $this->_currentParams);
	}
}
