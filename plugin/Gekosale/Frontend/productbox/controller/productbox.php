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
 * $Id: productbox.php 619 2011-12-19 21:09:00Z gekosale $
 */
namespace Gekosale;

use xajaxResponse;

class ProductBoxController extends Component\Controller\Box
{

	public function __construct ($registry, $box)
	{
		parent::__construct($registry, $box);
		$this->productid = App::getModel('product')->getProductIdBySeo($this->getParam());
		$this->productModel = App::getModel('product/product');
		$this->product = $this->productModel->getProductAndAttributesById((int) $this->productid);
		if (empty($this->product)){
			App::redirectUrl($this->registry->router->generate('frontend.home', true));
		}
		$this->heading = $this->product['productname'];
	}

	public function index ()
	{
		$clientData = App::getModel('client')->getClient();
		
		$this->registry->xajax->registerFunction(array(
			'addOpinion',
			$this->productModel,
			'addAJAXOpinionAboutProduct'
		));
		
		//$this->registry->xajax->registerFunction(array(
			//'addProductRangeOpinion',
			//$this->productModel,
			//'addAJAXProductRangeOpinion'
		//));
		
		if (isset($this->_boxAttributes['tabbed'])){
			$tabbed = $this->_boxAttributes['tabbed'];
		}
		else{
			$tabbed = 1;
		}
		
		if (isset($this->product['idproduct'])){
			$range = $this->productModel->getRangeType((int) $this->productid);
			$this->productModel->getPhotos($this->product);
			$this->productModel->getOtherPhotos($this->product);
			$selectAttributes = $this->productModel->getProductAttributeGroups($this->product);
			
			foreach ($selectAttributes as $key => $val){
				natsort($val['attributes']);
				$selectAttributes[$key]['attributes'] = $val['attributes'];
			}
			
			$attset = $this->productModel->getProductVariant($this->product);
			
			$Data = Array();
			foreach ($attset as $group => $data){
				$keys = array_keys($data['variant']);
				natsort($keys);
				$Data[implode(',', $keys)] = Array(
					'setid' => $group,
					'stock' => $data['stock'],
					'sellprice' => $this->registry->core->processPrice($data['sellprice']),
					'sellpricenetto' => $this->registry->core->processPrice($data['sellpricenetto']),
					'sellpriceold' => $this->registry->core->processPrice($data['attributepricegrossbeforepromotion']),
					'sellpricenettoold' => $this->registry->core->processPrice($data['attributepricenettobeforepromotion']),
					'availablity' => $data['availablity'],
					'photos' => $data['photos']
				);
			}
			
			$productreview = App::getModel('productreview')->getProductReviews((int) $this->productid);
			
			$delivery = App::getModel('delivery')->getDispatchmethodPriceForProduct($this->product['price'], $this->product['weight']);
			
			$deliverymin = PHP_INT_MAX;
			foreach ($delivery as $i){
				$deliverymin = min($deliverymin, $i['dispatchmethodcost']);
			}
			
			$files = App::getModel('product')->getFilesByProductId((int) $this->productid);
      $warranty = App::getModel('product')->getWarrantyByProductId((int) $this->productid);
			
			$tabs = $this->registry->template->assign('tabbed', $tabbed);
			
			$eventData = Event::filter($this, 'frontend.productbox.assign', Array(), NULL);
			
			foreach ($eventData as $Data){
				foreach ($Data as $tab => $values){
					$this->registry->template->assign($tab, $values);
				}
			}
			
			$opinion = Session::getVolatileOpinionAdded();
			if ($opinion[0] == 1){
				$this->registry->template->assign('opinionadded', _('TXT_CLIENT_OPINION_ADDED'));
			}
			elseif ($opinion[0] == 2){
				$this->registry->template->assign('opinionadded', _('TXT_GUEST_OPINION_ADDED'));
			}
			if ($this->product['enable'] == 0 && (int) Session::getActiveUserid() > 0){
				$this->registry->template->assign('draft', _('TXT_PRODUCT_DRAFT'));
			}
			
			$this->registry->template->assign('range', $range);
			$this->registry->template->assign('files', $files);
      $this->registry->template->assign('warranty', $warranty);
			$this->registry->template->assign('variants', json_encode($Data));
			$this->registry->template->assign('product', $this->product);
			$this->registry->template->assign('attributes', $selectAttributes);
			$this->registry->template->assign('attset', $attset);
			$this->registry->template->assign('humanProductReviewCount', App::getModel('productreview')->getHumanOpinionsCount(count($productreview)));
			$this->registry->template->assign('productreview', $productreview);
			$this->registry->template->assign('delivery', $delivery);
			$this->registry->template->assign('deliverymin', $deliverymin);
			$this->productModel->updateViewedCount((int) $this->productid);
		}
		else{
			App::redirectSeo(App::getURLAdress());
		}
		return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}

	public function getBoxHeading ()
	{
		return $this->heading;
	}
}
