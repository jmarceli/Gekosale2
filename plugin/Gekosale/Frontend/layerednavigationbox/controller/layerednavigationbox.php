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
 */
namespace Gekosale;

class LayeredNavigationBoxController extends Component\Controller\Box
{
  public function __construct ($registry, $modelFile = NULL)
  {
    parent::__construct($registry, $modelFile);
    $this->model = App::getModel('layerednavigationbox');
    $this->controller = $this->registry->router->getCurrentController();

    //$this->args = array(
      //'orderBy' => $this->getParam('orderBy', 'default'),
      //'orderDir' => $this->getParam('orderDir', 'asc'),
      //'currentPage' => 1,
      //'viewType' => $this->getParam('viewType', 0),
      //'priceFrom' => $this->getParam('priceFrom', 0),
      //'priceTo' => $this->getParam('priceTo', Core::PRICE_MAX),
      //'producers' => $this->getParam('producers', 0),
      //'attributes' => $this->getParam('attributes', 0)
    //);

    //$this->controller = $this->registry->router->getCurrentController();

    //if($this->controller == 'categorylist') {
      //$this->category = App::getModel('categorylist')->getCurrentCategory();
      //$this->args['param'] = $this->category['seo'];
    //}
    //elseif ($this->controller == 'productsearch') {
      //$this->args['action'] = 'index';
      //$this->args['param'] = $this->getParam();
    //}
    //else {
      //$this->category = array('id' => 0);
    //}
    
    if (isset($_POST['layered_submitted']) && $_POST['layered_submitted'] == 1){
      App::redirectUrl($this->model->generateRedirectUrl());
    }

    //$this->productIds = $this->getProducts();
  }

	public function index ()
	{
    $producers = $this->model->getProducersLinks();
    $attributes = $this->model->getAttributesLinks();

    $this->registry->template->assign('priceFrom', $this->getParam('priceFrom', 0));
    $this->registry->template->assign('priceTo', $this->getParam('priceTo', 0));
    $this->registry->template->assign('producers', $producers);
    if($this->controller == 'categorylist') {
      $this->registry->template->assign('currentCategory', App::getModel('categorylist')->getCurrentCategory());
      $this->registry->template->assign('current', (int) $this->registry->core->getParam());
    }
    $this->registry->template->assign('groups', $attributes);
					
		return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}

  public function getBoxTypeClassname ()
	{
		return 'layout-box-type-layered-navigation';
	}
}
