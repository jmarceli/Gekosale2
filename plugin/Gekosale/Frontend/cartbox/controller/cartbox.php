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
 * $Revision: 612 $
 * $Author: gekosale $
 * $Date: 2011-11-28 21:02:10 +0100 (Pn, 28 lis 2011) $
 * $Id: cartbox.php 612 2011-11-28 20:02:10Z gekosale $
 */
namespace Gekosale;

use FormEngine;
use xajaxResponse;

class CartBoxController extends Component\Controller\Box
{

	public function __construct ($registry, $box)
	{
		parent::__construct($registry, $box);
		$this->clientModel = App::getModel('client');
		$this->cartModel = App::getModel('cart');
		$this->paymentModel = App::getModel('payment');
		$this->deliveryModel = App::getModel('delivery');
		$this->dispatchMethods = $this->deliveryModel->getDispatchmethodPrice();
	}

	public function index ()
	{
		$this->registry->xajax->registerFunction(array(
			'setDispatchmethodChecked',
			$this->deliveryModel,
			'setAJAXDispatchmethodChecked'
		));
		
		$this->registry->xajax->registerFunction(array(
			'setPeymentChecked',
			$this->paymentModel,
			'setAJAXPaymentMethodChecked'
		));
		
		$this->registry->xajax->registerFunction(array(
			'changeQuantity',
			$this->cartModel,
			'changeQuantity'
		));
		
		$globalprice = $this->cartModel->getGlobalPrice();
		
		$checkRulesCart = App::getModel('cart')->checkRulesCart();
		if (is_array($checkRulesCart) && count($checkRulesCart) > 0){
			$this->registry->template->assign('checkRulesCart', $checkRulesCart);
		}
		if (Session::getActiveDispatchmethodChecked() == NULL){
			usort($this->dispatchMethods, function  ($a, $b)
			{
				return $a['dispatchmethodcost'] - $b['dispatchmethodcost'];
			});
			$default = current($this->dispatchMethods);
			App::getModel('delivery')->setDispatchmethodChecked($default['dispatchmethodid']);
		}
		
		$paymentMethods = App::getModel('payment')->getPaymentMethods();
		if (Session::getActivePaymentMethodChecked() == 0){
			if (isset($paymentMethods[0])){
				App::getModel('payment')->setPaymentMethodChecked($paymentMethods[0]['idpaymentmethod'], $paymentMethods[0]['name']);
			}
		}
		
		$minimumordervalue = App::getModel('cart')->getMinimumOrderValue();
		
		$order = App::getModel('finalization')->setClientOrder();
		
		$assignData = Array(
			'deliverymethods' => $this->dispatchMethods,
			'checkedDelivery' => Session::getActiveDispatchmethodChecked(),
			'checkedPayment' => Session::getActivePaymentMethodChecked(),
			'checkedDeliveryOption' => Session::getActiveDispatchmethodOption(),
			'payments' => $paymentMethods,
			'minimumordervalue' => $minimumordervalue,
			'priceWithDispatchMethod' => Session::getActiveglobalPriceWithDispatchmethod(),
			'summary' => App::getModel('finalization')->getOrderSummary(),
			'order' => Session::getActiveClientOrder(),
		);
		
		foreach ($assignData as $key => $assign){
			$this->registry->template->assign($key, $assign);
		}
		
		return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}
}