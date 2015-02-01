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
    $this->controller = $this->registry->router->getCurrentController();
	}

	public function index ()
	{
    $this->dataset = $this->getProductsTemplate();

    $this->init();
	
		if ($this->controller != 'productpromotion'){
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

  protected function init ()
  {
		$this->_currentParams = Array(
			'currentPage' => $this->getParam('currentPage', 1),
			'viewType' => $this->getParam('viewType', $this->_boxAttributes['view']),
			'priceFrom' => $this->getParam('priceFrom', 0),
			'priceTo' => $this->getParam('priceTo', Core::PRICE_MAX),
			'orderBy' => $this->getParam('orderBy', 'default'),
			'orderDir' => $this->getParam('orderDir', 'asc'),
			'producers' => $this->getParam('producers', 0),
			'attributes' => $this->getParam('attributes', 0)
		);
  }

  protected function getProductsTemplate ()
	{
    return App::getModel('productlist')->getProductsTemplate('productpromotion', 'productpromotion', $this->_currentParams, $this->_boxAttributes);
	}

	protected function createPaginationLinks ()
	{
    return App::getModel('productlist')->createPaginationLinks('productpromotion', $this->_currentParams, $this->dataset['totalPages']);
	}

	protected function createSorting ()
	{
    return App::getModel('productlist')->createSorting('productpromotion', $this->_currentParams, 0);
	}

	protected function createViewSwitcher ()
	{
    return App::getModel('productlist')->createViewSwitcher('productpromotion', $this->_currentParams);
	}


  public function getBoxTypeClassname ()
  {
    return 'layout-box-type-product-list';
  }

  public function boxVisible ()
  {
    if ($this->controller == 'productpromotion'){
      return true;
    }
    return ($this->dataset['total'] > 0) ? true : false;
  }
}
