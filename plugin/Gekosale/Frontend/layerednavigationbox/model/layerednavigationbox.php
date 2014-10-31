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
 * $Revision: 222 $
 * $Author: gekosale $
 * $Date: 2011-06-25 15:20:08 +0200 (So, 25 cze 2011) $
 * $Id: categoriesbox.php 222 2011-06-25 13:20:08Z gekosale $
 */
namespace Gekosale;

class LayeredNavigationBoxModel extends Component\Model
{
  protected $controller; // current controller
  protected $category; // current category

  public function __construct ($registry, $modelFile = NULL)
  {
    parent::__construct($registry, $modelFile);
    $this->controller = $this->registry->router->getCurrentController();

    $this->init();
   
    if (isset($_POST['layered_submitted']) && $_POST['layered_submitted'] == 1){
      App::redirectUrl($this->generateRedirectUrl());
    }

    $this->productIds = $this->getProducts();
  }

  protected function init()
  {
    $this->args = array(
      'orderBy' => $this->getParam('orderBy', 'default'),
      'orderDir' => $this->getParam('orderDir', 'asc'),
      'currentPage' => 1,
      'viewType' => $this->getParam('viewType', 1),
      'priceFrom' => $this->getParam('priceFrom', 0),
      'priceTo' => $this->getParam('priceTo', Core::PRICE_MAX),
      'producers' => $this->getParam('producers', 0),
      'attributes' => $this->getParam('attributes', 0)
    );


    if($this->controller == 'categorylist') {
      $this->category = App::getModel('categorylist')->getCurrentCategory();
      $this->args['param'] = $this->category['seo'];
    }
    elseif ($this->controller == 'productsearch') {
      $this->args['action'] = 'index';
      $this->args['param'] = $this->getParam();
    }
    else {
      $this->category = array('id' => 0);
    }
  }

  // Returns array of products IDs on this page "by default" (without filtering)
  public function getProducts()
  {
    // Perform SEARCH
    if($this->controller == 'productsearch') {
      $this->searchPhrase = str_replace('_', '', App::getModel('formprotection')->cropDangerousCode($this->getParam()));
    }

    if($this->controller == 'productsearch' || 
      $this->controller == 'productnews' ||
      $this->controller == 'productpromotion')
    {
      $controller = $this->controller;
    }
    elseif($this->controller == 'categorylist') {
      $controller = 'product'; // categorylist is handled by product dataset
    }
    else { // return no products for other controllers (required by livesearch)
      return array();
    }

    $dataset = App::getModel($controller)->getDataset();
    $dataset->setPagination(0);
    $dataset->setCurrentPage(1);
    $dataset->setOrderBy('name', 'name');
    $dataset->setOrderDir('desc', 'desc');

    $params = Array(
      'clientid' => Session::getActiveClientid(),
      'producer' => 0,
      'filterbyproducer' => 0,
      'pricefrom' => 0,
      'priceto' => Core::PRICE_MAX,
      'enablelayer' => 0,
      'products' => 0,
    );

    if($this->controller == 'productsearch') {
      $params['categoryid'] = 0;
      $params['name'] = '%' . $this->searchPhrase . '%';
    }
    elseif($this->controller == 'categorylist') {
      $params['categoryid'] = $this->category['id'];
    }
    // get promotion products (no additional params needed
    //elseif($this->controller == 'productpromotion') {
    //}

    $dataset->setSQLParams($params);
    $products = App::getModel($controller)->getProductDataset();

    // array with id 0 product (non existing)
    $productIds = Array(0);

    foreach ($products['rows'] as $key => $product){
      $productIds[] = $product['id'];
    }

    return $productIds;
  }

  public function getAttributesLinks ()
  {
    // ATTRIBUTES FILTER
    $Data = $this->getLayeredAttributesByProductIds($this->productIds);
    
    $paramAttributes = (strlen($this->args['attributes']) > 0) ? array_filter(array_values(explode('_', $this->args['attributes']))) : Array();
    
    foreach ($Data as $groupId => $groupData){
      foreach ($groupData['attributes'] as $attributeId => $attributeData){
        
        if (! empty($paramAttributes)){
          $attr = array_merge($paramAttributes, (array) $attributeId);
        }
        else{
          $attr = (array) $attributeId;
        }
        
        $args = $this->args;
        $args['attributes'] = implode('_', array_unique($attr));

        $url = $this->registry->router->generate('frontend.' . $this->controller, true, $args);
        
        $Data[$groupId]['attributes'][$attributeId]['link'] = $url;
        $Data[$groupId]['attributes'][$attributeId]['active'] = in_array($attributeId, $paramAttributes);
      }
    }

    return $Data;
  }

  public function getProducersLinks ()
  {
    // PRODUCERS FILTER
    $producers = App::getModel('product')->getProducerAllByProducts($this->productIds);

    $paramProducers = (strlen($this->args['producers']) > 0) ? array_filter(array_values(explode('_', $this->args['producers']))) : Array();
    
    foreach ($producers as $key => $producer){
      
      if (! empty($paramProducers)){
        $prod = array_merge($paramProducers, (array) $producer['id']);
      }
      else{
        $prod = (array) $producer['id'];
      }

      $args = $this->args;
      $args['producers'] = implode('_', array_unique($prod));

      $url = $this->registry->router->generate('frontend.' . $this->controller, true, $args);
      
      $producers[$key]['link'] = $url;
      $producers[$key]['active'] = in_array($producer['id'], $paramProducers);
    }

    return $producers;
  }

  // Generates redirect URL after form submission
	public function generateRedirectUrl ()
	{
		$priceFrom = App::getModel('formprotection')->cropDangerousCode($_POST['priceFrom']);
		$priceTo = App::getModel('formprotection')->cropDangerousCode($_POST['priceTo']);
		$producer = (! empty($_POST['producer'])) ? App::getModel('formprotection')->filterArray($_POST['producer']) : Array(
			0
		);
		$attribute = (! empty($_POST['attribute'])) ? App::getModel('formprotection')->filterArray($_POST['attribute']) : Array(
			0
		);
    $args = $this->args;
    $args['priceFrom'] = ($priceFrom > 0) ? $priceFrom : 0;
    $args['priceTo'] = ($priceTo > 0) ? $priceTo : Core::PRICE_MAX;
    $args['producers'] = implode('_', array_unique($producer));
    $args['attributes'] = implode('_', array_unique($attribute));
		
		switch ($this->controller) {
			case 'categorylist':
        $args['param'] = $this->category['seo'];
				break;
			case 'productsearch':
        $args['action'] = 'index';
        $args['param'] = $this->getParam();
				break;
			//case 'productpromotion':
        //$args['action'] = 'index';
        //$args['param'] = $this->getParam();
				//break;
		}
    $url = $this->registry->router->generate('frontend.' . $this->controller, true, $args);
		
		return $url;
	}

  // NOT REQUIRED ANYMORE
	//public function getLayeredAttributesForCategory ($id)
	//{
		//$Data = Array();
		//$sql = 'SELECT 
					//AP.name AS attributegroupname, 
					//AP.idattributeproduct AS attributegroupid,
					//APV.name AS attributename, 
					//APV.idattributeproductvalue AS attributeid
				//FROM productattributeset AS PAS 
				//INNER JOIN productattributevalueset PAVS ON PAVS.productattributesetid = PAS.idproductattributeset 
				//LEFT JOIN attributeproductvalue APV ON PAVS.attributeproductvalueid = APV.idattributeproductvalue 
				//LEFT JOIN attributeproduct AS AP ON APV.attributeproductid = AP.idattributeproduct 
				//LEFT JOIN product AS P ON PAS.productid = P.idproduct 
				//LEFT JOIN productcategory PC ON PC.productid = P.idproduct
				//WHERE PC.categoryid = :id AND P.enable = 1 AND IF(P.trackstock = 1, PAS.stock > 0, 1) AND PAS.status = 1
				//ORDER BY APV.name ASC
			//';
		//$stmt = Db::getInstance()->prepare($sql);
		//$stmt->bindValue('id', $id);
		//$stmt->execute();
		//while ($rs = $stmt->fetch()){
			//$Data[$rs['attributegroupid']]['name'] = $rs['attributegroupname'];
			//$Data[$rs['attributegroupid']]['attributes'][$rs['attributeid']] = Array(
				//'id' => $rs['attributeid'],
				//'name' => $rs['attributename']
			//);
		//}
		//foreach ($Data as $key => $val){
			//$Data[$key]['attributes'] = $val['attributes'];
		//}
		//return $Data;
	//}

	public function getLayeredAttributesByProductIds ($ids)
	{
		$Data = Array();
		$sql = 'SELECT 
					AP.name AS attributegroupname, 
					AP.idattributeproduct AS attributegroupid,
					APV.name AS attributename, 
					APV.idattributeproductvalue AS attributeid
				FROM productattributeset AS PAS 
				INNER JOIN productattributevalueset PAVS ON PAVS.productattributesetid = PAS.idproductattributeset 
				LEFT JOIN attributeproductvalue APV ON PAVS.attributeproductvalueid = APV.idattributeproductvalue 
				LEFT JOIN attributeproduct AS AP ON APV.attributeproductid = AP.idattributeproduct 
				LEFT JOIN product AS P ON PAS.productid = P.idproduct 
				LEFT JOIN productcategory PC ON PC.productid = P.idproduct
				WHERE FIND_IN_SET(CAST(P.idproduct as CHAR), :ids) AND P.enable = 1 AND PAS.status = 1
			';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('ids', implode(',', $ids));
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$Data[$rs['attributegroupid']]['name'] = $rs['attributegroupname'];
			$Data[$rs['attributegroupid']]['attributes'][$rs['attributeid']] = Array(
				'id' => $rs['attributeid'],
				'name' => $rs['attributename']
			);
		}
		return $Data;
	}

	public function getProductsForAttributes ($categoryid, $attributes)
	{
		$sql = 'SELECT
					PAS.productid
				FROM productattributeset PAS
				LEFT JOIN productattributevalueset PAVS ON PAVS.productattributesetid = PAS.idproductattributeset
				WHERE FIND_IN_SET(CAST(PAVS.attributeproductvalueid as CHAR), :attributes) AND PAS.status = 1
				GROUP BY PAS.productid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('attributes', implode(',', $attributes));
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
			$Data[] = $rs['productid'];
		}
		return $Data;
	}
}
