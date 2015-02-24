<?php

/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2013 WellCommerce sp. z o.o.. Zabronione jest usuwanie informacji o
 * licencji i autorach.
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
 * $Id: cart.php 627 2012-01-20 22:05:57Z gekosale $
 */
namespace Gekosale;

use Doctrine\DBAL\Schema\View;
use xajaxResponse;

class CartModel extends Component\Model
{
	protected $Cart;
	protected $globalPrice;
	protected $globalWeight;
	protected $globalPriceWithoutVat;
	protected $globalPriceWithDispatchmethod;
	protected $globalPriceWithDispatchmethodNetto;
	protected $count;
	protected $product;

	public function __construct ($registry)
	{
		parent::__construct($registry);
		if (($this->Cart = Session::getActiveCart()) === NULL){
			$this->Cart = Array();
		}
		if (($this->globalPrice = Session::getActiveGlobalPrice()) === NULL){
			$this->globalPrice = 0.00;
		}
		if (($this->globalWeight = Session::getActiveGlobalWeight()) === NULL){
			$this->globalWeight = 0.00;
		}
		if (($this->globalPriceWithoutVat = Session::getActiveGlobalPriceWithoutVat()) === NULL){
			$this->globalPriceWithoutVat = 0.00;
		}
		if (($this->globalPriceWithDispatchmethod = Session::getActiveGlobalPriceWithDispatchmethod()) === NULL){
			$this->globalPriceWithDispatchmethod = 0.00;
		}
		if (($this->globalPriceWithDispatchmethodNetto = Session::getActiveGlobalPriceWithDispatchmethodNetto()) === NULL){
			$this->globalPriceWithDispatchmethodNetto = 0.00;
		}
		if ($this->count = Session::getActiveCount() === NULL){
			$this->count = 0;
		}
	}

	public function addAJAXProductToCart ($idproduct, $attr = NULL, $qty)
	{
		$objResponse = new xajaxResponse();
		$this->product = App::getModel('product')->getProductAndAttributesById($idproduct);
		
		if (empty($this->product)){
			$objResponse->script('GError("' . _('ERR_SHORTAGE_OF_STOCK') . '")');
			return $objResponse;
		}
		
		$attr = ($attr == 0) ? NULL : $attr;
		$qty = ((int) $qty > 0) ? $qty : 1;
		$trackstock = $this->product['trackstock'];
		
		if (NULL !== $attr){
			foreach ($this->product['attributes'] as $variant){
				if ($variant['idproductattributeset'] == $attr){
					$stock = $variant['stock'];
					break;
				}
			}
		}
		else{
			$stock = $this->product['stock'];
		}
		
		$maxQty = $this->checkProductQuantity($trackstock, $qty, $stock);
		
		if ($maxQty == 0){
			$objResponse->script('GError("' . _('ERR_SHORTAGE_OF_STOCK') . '")');
			return $objResponse;
		}
		
		if ($trackstock == 1 && ($stock < $qty)){
			$objResponse->script('GError("' . _('ERR_SHORTAGE_OF_STOCK') . '", "' . sprintf(_('ERR_LOW_OF_STOCK'), $maxQty) . '")');
			$objResponse->assign('product-qty', 'value', $maxQty);
			return $objResponse;
		}
		
		if (NULL !== $attr){
			if (isset($this->Cart[$idproduct]['attributes'][$attr])){
				$oldqty = $this->Cart[$idproduct]['attributes'][$attr]['qty'];
				$newqty = $this->Cart[$idproduct]['attributes'][$attr]['qty'] + $qty;
				if (($oldqty == $stock) && $trackstock == 1){
					$objResponse->assign('product-qty', 'value', 1);
					$objResponse->script('GError("' . _('ERR_STOCK_LESS_THAN_QTY') . '", "' . _('ERR_MAX_STORAGE_STATE_ON_CART') . ' (' . $stock . ' ' . _('TXT_QTY') . ')' . '")');
					return $objResponse;
				}
				else 
					if (($newqty > $stock) && $trackstock == 1){
						$this->Cart[$idproduct]['attributes'][$attr]['qty'] = $stock;
						$this->Cart[$idproduct]['attributes'][$attr]['qtyprice'] = $this->$this->Cart[$idproduct]['attributes'][$attr]['newprice'] * ($this->Cart[$idproduct]['attributes'][$attr]['qty']);
						$this->updateSession();
					}
					else{
						$this->Cart[$idproduct]['attributes'][$attr]['qty'] = $newqty;
						$this->Cart[$idproduct]['attributes'][$attr]['qtyprice'] = $this->Cart[$idproduct]['attributes'][$attr]['newprice'] * $this->Cart[$idproduct]['attributes'][$attr]['qty'];
						$this->updateSession();
					}
			}
			elseif (isset($this->Cart[$idproduct])){
				$this->cartAddProductWithAttr($idproduct, $qty, $attr);
				$this->getProductFeatures($idproduct, $attr);
			}
			else{
				$this->cartAddProductWithAttr($idproduct, $qty, $attr);
				$this->getProductFeatures($idproduct, $attr);
			}
		}
		else{
			if (isset($this->Cart[$idproduct]) && isset($this->Cart[$idproduct]['standard'])){
				$oldqty = $this->Cart[$idproduct]['qty'];
				$newqty = $this->Cart[$idproduct]['qty'] + $qty;
				if (($oldqty >= $stock) && $trackstock == 1){
					$objResponse->assign('product-qty', 'value', 1);
					$objResponse->script('GError("' . _('ERR_STOCK_LESS_THAN_QTY') . '", "' . _('ERR_MAX_STORAGE_STATE_ON_CART') . ' (' . $stock . ' ' . _('TXT_QTY') . ')' . '")');
					return $objResponse;
				}
				else 
					if (($newqty >= $stock) && $trackstock == 1){
						$this->Cart[$idproduct]['qty'] = $stock;
						$this->Cart[$idproduct]['qtyprice'] = $this->Cart[$idproduct]['newprice'] * $this->Cart[$idproduct]['qty'];
						$this->updateSession();
					}
					else{
						$this->Cart[$idproduct]['qty'] = $newqty;
						$this->Cart[$idproduct]['qtyprice'] = $this->Cart[$idproduct]['newprice'] * $this->Cart[$idproduct]['qty'];
						$this->updateSession();
					}
			}
			else{
				$this->cartAddStandardProduct($idproduct, $qty);
			}
		}
		
		$objResponse->clear("topBasket", "innerHTML");
		$objResponse->append("topBasket", "innerHTML", $this->getCartPreviewTemplate());
		
		$objResponse->clear("basketModal", "innerHTML");
		$objResponse->append("basketModal", "innerHTML", $this->getBasketModalTemplate());
		
		if ($this->registry->loader->getParam('cartredirect') == 0){
			$objResponse->script("$('#basketModal').modal('show');");
		}
		else{
			$url = $this->registry->router->generate('frontend.cart', true);
			$objResponse->script("window.location.href = '{$url}'");
		}
		return $objResponse;
	}

	public function doQuickAddCart ($id)
	{
		$this->product = App::getModel('product')->getProductAndAttributesById($id);
		if (empty($this->product['attributes'])){
			return $this->addAJAXProductToCart($id, 0, 1);
		}
		else{
			$objResponse = new xajaxResponse();
			
			App::getModel('product/product')->getPhotos($this->product);
			$selectAttributes = App::getModel('product/product')->getProductAttributeGroups($this->product);
			$attset = App::getModel('product/product')->getProductVariant($this->product);
			
			foreach ($selectAttributes as $key => $val){
				natsort($val['attributes']);
				$selectAttributes[$key]['attributes'] = $val['attributes'];
			}
			
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
			
			$delivery = App::getModel('delivery')->getDispatchmethodPriceForProduct($this->product['price'], $this->product['weight']);
			
			$deliverymin = PHP_INT_MAX;
			foreach ($delivery as $i){
				$deliverymin = min($deliverymin, $i['dispatchmethodcost']);
			}
			
			$variants = json_encode($Data);
			
			$this->registry->template->assign('product', $this->product);
			$this->registry->template->assign('attributes', $selectAttributes);
			$this->registry->template->assign('attset', $attset);
			$this->registry->template->assign('deliverymin', $deliverymin);
			$result = $this->registry->template->fetch('product_modal.tpl');
			$objResponse->clear("productModal", "innerHTML");
			$objResponse->append("productModal", "innerHTML", $result);
			$objResponse->script("GProductAttributes({aoVariants: {$variants}, bTrackStock: {$this->product['trackstock']}});");
			$objResponse->script("$('#productModal').modal('show');");
			$objResponse->script("qtySpinner();");
			return $objResponse;
		}
	}

	public function addProductsToCartFromMissingCart ($Data)
	{
		foreach ($Data as $idproduct => $values){
			$product = App::getModel('product')->getProductAndAttributesById($idproduct);
			if (isset($values['standard']) && $values['standard'] == 1){
				$qty = $this->checkProductQuantity($product['trackstock'], $values['qty'], $product['stock']);
				if ($qty > 0){
					$this->cartAddStandardProduct($idproduct, $qty);
				}
			}
			else{
				if (isset($values['attributes'])){
					foreach ($values['attributes'] as $attr => $variant){
						if (isset($product['attributes'])){
							foreach ($product['attributes'] as $k => $v){
								if ($v['idproductattributeset'] == $attr){
									$qty = $this->checkProductQuantity($product['trackstock'], $variant['qty'], $v['stock']);
									if ($qty > 0){
										$this->cartAddProductWithAttr($idproduct, $qty, $attr);
										$this->getProductFeatures($idproduct, $attr);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function checkProductQuantity ($trackStock, $qty, $stock)
	{
		if ($trackStock == 0){
			return $qty;
		}
		else{
			if ($qty > $stock){
				return $stock;
			}
			else{
				return $qty;
			}
		}
		return 0;
	}

	public function deleteAJAXProductFromCart ($idproduct, $attr = NULL)
	{
		$objResponseDel = new xajaxResponse();
		try{
			// product without attributes- simple product
			if (! isset($this->Cart[$idproduct]['attributes']) && $attr == NULL){
				$this->deleteProductCart($idproduct);
				// product with attributes and standard product
			}
			elseif ($this->Cart[$idproduct]['attributes'] != NULL && $attr != NULL){
				// if standard product
				if (isset($this->Cart[$idproduct]['standard'])){
					// then delete chosen attribute only and leave standard
					// product
					$this->deleteProductAttributeCart($idproduct, $attr);
				}
				else{
					// first- delete attributes of this product
					$this->deleteProductAttributeCart($idproduct, $attr);
					if ($this->Cart[$idproduct]['attributes'] == NULL){
						// if there isnt other prodcut attributes or isnt set-up
						// standard product
						// delete product from cart
						$this->deleteProductAtributesCart($idproduct);
					}
				}
				// if there are product attributes on cart
			}
			elseif ($this->Cart[$idproduct]['attributes'] != NULL && $attr == NULL){
				if (isset($this->Cart[$idproduct])){
					// then delete only product standard
					$this->deleteProductAttributeCart($idproduct, NULL);
				}
				// if there arent attributes of product on cart
			}
			elseif ($this->Cart[$idproduct]['attributes'] == NULL && $attr == NULL){
				// then delete only product standard
				unset($this->Cart[$idproduct]);
			}
			else{
				throw new Exception('No such product (id=' . $idproduct . ') on cart');
			}
		}
		catch (Exception $e){
			$objResponseDel->alert($e->getMessage());
		}
		
		$this->updateSession();
		$objResponseDel->script('window.location.reload( false )');
		return $objResponseDel;
	}

	public function checkPackageQty ($qty, $packagesize)
	{
		$qty = floatval($qty);
		$modulo = number_format(fmod($qty, $packagesize), 4);
		if ($modulo > 0){
			$newqty = $qty - $modulo;
		}
		else{
			$newqty = $qty;
		}
		return $newqty;
	}

	public function changeQuantity ($idproduct, $attr = NULL, $newqty)
	{
		$objResponseInc = new xajaxResponse();
		if ($newqty == 0){
			$this->deleteAJAXProductFromCart($idproduct, $attr);
		}
		else{
			try{
				if (isset($this->Cart[$idproduct])){
					// standard product (of product with attributes)
					if (isset($this->Cart[$idproduct]['standard']) && $this->Cart[$idproduct]['standard'] == 1 && $attr == NULL){
						$newqty = $this->checkPackageQty($newqty, $this->Cart[$idproduct]['packagesize']);
						$oldQty = $this->Cart[$idproduct]['stock'];

            // check for trackstocked products
						if (($newqty > $this->Cart[$idproduct]['stock']) && $this->Cart[$idproduct]['trackstock'] == 1){
							$this->Cart[$idproduct]['qty'] = $this->Cart[$idproduct]['stock'];
							$objResponseInc->script('GError("' . _('ERR_COULDNT_INCREASE_QTY') . _('ERR_MAX_STORAGE_STATE_ON_CART') . '");');
						}
            else {
              // update price and weight
              $this->Cart[$idproduct]['qty'] = $newqty;
            }
            $this->Cart[$idproduct]['qtyprice'] = $this->Cart[$idproduct]['newprice'] * $this->Cart[$idproduct]['qty'];
            $this->Cart[$idproduct]['weighttotal'] = $this->Cart[$idproduct]['weight'] * $this->Cart[$idproduct]['qty'];

            $this->updateSessionSoft();
					}
					// product with attributes
					if ($this->Cart[$idproduct]['attributes'] != NULL && $attr != NULL){
						$newqty = $this->checkPackageQty($newqty, $this->Cart[$idproduct]['attributes'][$attr]['packagesize']);
						$oldQty = $this->Cart[$idproduct]['attributes'][$attr]['qty'];

            // check for trackstocked products
            if ($this->Cart[$idproduct]['attributes'][$attr]['trackstock'] == 1 
              && $newqty > $this->Cart[$idproduct]['attributes'][$attr]['stock']) {
								$this->Cart[$idproduct]['attributes'][$attr]['qty'] = $this->Cart[$idproduct]['attributes'][$attr]['stock'];
								$objResponseInc->script('GError("' . _('ERR_COULDNT_INCREASE_QTY') . '<br />' . _('ERR_MAX_STORAGE_STATE_ON_CART') . '");');
            }
            else {
              // update price and weight
              $this->Cart[$idproduct]['attributes'][$attr]['qty'] = $newqty;
            }
						$this->Cart[$idproduct]['attributes'][$attr]['qtyprice'] = $this->Cart[$idproduct]['attributes'][$attr]['newprice'] * $this->Cart[$idproduct]['attributes'][$attr]['qty'];
						$this->Cart[$idproduct]['attributes'][$attr]['weighttotal'] = $this->Cart[$idproduct]['attributes'][$attr]['weight'] * $this->Cart[$idproduct]['attributes'][$attr]['qty'];

            $this->updateSessionSoft();
					}
				}
			}
			catch (Exception $e){
				$objResponseInc->alert($e->getMessage());
			}
		}
		$objResponseInc->clear("cart-contents", "innerHTML");
		$objResponseInc->append("cart-contents", "innerHTML", $this->getCartTableTemplate());
		$objResponseInc->script("qtySpinner();");
    $objResponseInc->clear("topBasket", "innerHTML");
		$objResponseInc->append("topBasket", "innerHTML", $this->getCartPreviewTemplate());
    return $objResponseInc;
	}

  public function getCartVariables() {
    $method = Session::getActiveDispatchmethodChecked();
    $payment = Session::getActivePaymentMethodChecked();

		$this->clientModel = App::getModel('client');
		$this->paymentModel = App::getModel('payment');
		$this->deliveryModel = App::getModel('delivery');
    $this->dispatchMethods = $this->deliveryModel->getDispatchmethodPrice();
    
    // check rules
		$checkRulesCart = App::getModel('cart')->checkRulesCart();
		if (is_array($checkRulesCart) && count($checkRulesCart) > 0){
			$this->registry->template->assign('checkRulesCart', $checkRulesCart);
		}

    // DON'T CHECK DISPATCH METHOD BY DEFAULT
		//if ($method == NULL || !isset($this->dispatchMethods[$method['dispatchmethodid']])){
      // set dispatch method if not selected or not available
			//$method = current($this->dispatchMethods);
			//App::getModel('delivery')->setDispatchmethodChecked($method['dispatchmethodid']);
		//}
    if ($method != NULL && isset($this->dispatchMethods[$method['dispatchmethodid']])){
      App::getModel('delivery')->setDispatchmethodChecked($method['dispatchmethodid']);
    }
		
    // check payment
		$paymentMethods = App::getModel('payment')->getPaymentMethods();
    $paymentAvailable = false;
    foreach($paymentMethods as $method) {
      if($method['idpaymentmethod'] == $payment['idpaymentmethod'])
        $paymentAvailable = true;
    }
		if ($payment == 0 || !$paymentAvailable){
			if (isset($paymentMethods[0])){
				App::getModel('payment')->setPaymentMethodChecked($paymentMethods[0]['idpaymentmethod'], $paymentMethods[0]['name']);
			}
		}
    else {
      App::getModel('payment')->setPaymentMethodChecked($payment['idpaymentmethod'], $payment['paymentmethodname']);
    }

		$minimumordervalue = $this->getMinimumOrderValue();
		
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
	}

	public function getCartTableTemplate ()
	{
    $this->getCartVariables();
		$productCart = $this->getShortCartList();
		$productCart = $this->getProductCartPhotos($productCart);

    $this->registry->template->assign('productCart', $productCart);
    $this->registry->template->assign('globalPrice', $this->getGlobalPrice());

		return $this->registry->template->fetch('cartbox/index/table.tpl');
	}
  
  protected function updateSessionSoft() {
    $this->setGlobalPrice();
    $this->setGlobalPriceWithoutVat();
    $this->setGlobalWeight();
    $this->setCartForDelivery();
          
    Session::setActiveCart($this->Cart);
    Session::setActiveGlobalPrice($this->globalPrice);
    Session::setActiveGlobalWeight($this->globalWeight);
    Session::setActiveGlobalPriceWithoutVat($this->globalPriceWithoutVat);
    Session::setActiveglobalPriceWithDispatchmethod($this->globalPrice);
    Session::setActiveglobalPriceWithDispatchmethodNetto($this->globalPriceWithoutVat);
		Session::unsetActiveClientOrder();
  }

	public function deleteProductCart ($idproduct)
	{
		try{
			if (isset($this->Cart[$idproduct])){
				unset($this->Cart[$idproduct]);
			}
		}
		catch (Exception $e){
			throw new Exception('No such product on cart');
		}
	}

	public function deleteProductAttributeCart ($idproduct, $attr = NULL)
	{
		try{
			if (isset($this->Cart[$idproduct]['attributes']) && $this->Cart[$idproduct]['attributes'] != NULL && $attr == NULL){
				unset($this->Cart[$idproduct]['standard']);
				unset($this->Cart[$idproduct]['qty']);
				unset($this->Cart[$idproduct]['qtyprice']);
				unset($this->Cart[$idproduct]['weight']);
				unset($this->Cart[$idproduct]['weighttotal']);
				unset($this->Cart[$idproduct]['newprice']);
				unset($this->Cart[$idproduct]['vat']);
				unset($this->Cart[$idproduct]['pricewithoutvat']);
				unset($this->Cart[$idproduct]['mainphotoid']);
				unset($this->Cart[$idproduct]['shortdescription']);
				unset($this->Cart[$idproduct]['name']);
				unset($this->Cart[$idproduct]['stock']);
			}
			elseif ($this->Cart[$idproduct]['attributes'] == NULL && $attr == NULL){
				$this->deleteProductCart($idproduct);
			}
			else{
				if (isset($this->Cart[$idproduct]['attributes'][$attr]) && $attr != NULL){
					unset($this->Cart[$idproduct]['attributes'][$attr]);
				}
			}
		}
		catch (Exception $e){
			throw new Exception('There is not product with attributes on cart.');
		}
	}

	public function deleteProductAtributesCart ($idproduct)
	{
		try{
			if ($this->Cart[$idproduct]['attributes'] == NULL && ! isset($this->Cart[$idproduct]['standard'])){
				unset($this->Cart[$idproduct]);
			}
		}
		catch (Exception $e){
			throw new Exception('There are not attributes for this' . $idproduct . ' product');
		}
	}

	public function cartAddStandardProduct ($idproduct, $qty)
	{
		$product = (empty($this->product)) ? App::getModel('product')->getProductAndAttributesById($idproduct) : $this->product;
		
		if (is_null($product['discountpricenetto'])){
			$price = $product['price'];
			$priceWithoutVat = $product['pricewithoutvat'];
			$pricebeforepromotionnetto = NULL;
			$pricebeforepromotiongross = NULL;
		}
		else{
			$price = $product['discountprice'];
			$priceWithoutVat = $product['discountpricenetto'];
			$pricebeforepromotionnetto = $product['pricewithoutvat'];
			$pricebeforepromotiongross = $product['price'];
		}
		
		$qtyprice = $qty * $price;
		$weighttotal = $qty * $product['weight'];
		
		$this->Cart[$idproduct] = Array(
			'idproduct' => $idproduct,
			'ean' => $product['ean'],
			'seo' => $product['seo'],
			'name' => $product['productname'],
			'mainphotoid' => $product['mainphotoid'],
			'shortdescription' => $product['shortdescription'],
			'stock' => $product['stock'],
			'trackstock' => $product['trackstock'],
			'newprice' => $price,
			'pricewithoutvat' => $priceWithoutVat,
			'pricebeforepromotionnetto' => $pricebeforepromotionnetto,
			'pricebeforepromotiongross' => $pricebeforepromotiongross,
			'unit' => $product['unit'],
			'packagesize' => $product['packagesize'],
			'qty' => $qty,
			'qtyprice' => $qtyprice,
			'weight' => $product['weight'],
			'weighttotal' => $weighttotal,
			'vat' => $product['vatvalue'],
			'standard' => 1,
			'attributes' => isset($this->Cart[$idproduct]['attributes']) ? $this->Cart[$idproduct]['attributes'] : null
		);
		
		$this->updateSession();
	}

	public function cartAddProductWithAttr ($idproduct, $qty, $attr)
	{
		$product = (empty($this->product)) ? App::getModel('product')->getProductAndAttributesById($idproduct) : $this->product;
		
		foreach ($product['attributes'] as $key => $variant){
			if ($variant['idproductattributeset'] == $attr){
				$priceWithoutVat = $variant['attributeprice'];
				$price = $variant['price'];
				$weight = $variant['weight'];
				$ean = $variant['symbol'];
				$photo = ((int) $variant['photoid'] > 0) ? $variant['photoid'] : $product['mainphotoid'];
				$stock = $variant['stock'];
				if ($variant['attributeprice'] > $variant['attributepricenettobeforepromotion']){
					$pricebeforepromotionnetto = $variant['attributepricenettobeforepromotion'];
					$pricebeforepromotiongross = $variant['attributepricegrossbeforepromotion'];
				}
				else{
					$pricebeforepromotionnetto = NULL;
					$pricebeforepromotiongross = NULL;
				}
				break;
			}
		}
		
		if (! (isset($this->Cart[$idproduct]))){
			$this->Cart[$idproduct] = Array(
				'idproduct' => $product['idproduct']
			);
		}
		$qtyprice = $price * $qty;
		$weighttotal = $weight * $qty;
		
		$this->Cart[$idproduct]['attributes'][$attr] = Array(
			'attr' => $attr,
			'idproduct' => $product['idproduct'],
			'seo' => $product['seo'],
			'ean' => $ean,
			'name' => $product['productname'],
			'mainphotoid' => $photo,
			'stock' => $stock,
			'unit' => $product['unit'],
			'packagesize' => $product['packagesize'],
			'trackstock' => $product['trackstock'],
			'newprice' => $price,
			'pricewithoutvat' => $priceWithoutVat,
			'pricebeforepromotionnetto' => $pricebeforepromotionnetto,
			'pricebeforepromotiongross' => $pricebeforepromotiongross,
			'qty' => $qty,
			'qtyprice' => $qtyprice,
			'weight' => $weight,
			'weighttotal' => $weighttotal,
			'vat' => $product['vatvalue']
		);
		
		$this->updateSession();
	}

	public function getProductFeatures ($idproduct, $attr)
	{
		$sql = "SELECT
					PAVS.idproductattributevalueset as idfeature, 
					PAVS.attributeproductvalueid as feature, 
					AP.name AS groupname,
					APV.name AS attributename
				FROM productattributeset AS PAS
			    LEFT JOIN productattributevalueset AS PAVS ON PAS.idproductattributeset = PAVS.productattributesetid
				LEFT JOIN attributeproductvalue AS APV ON PAVS.attributeproductvalueid = APV.idattributeproductvalue
			    LEFT JOIN attributeproduct AS AP ON APV.attributeproductid = AP.idattributeproduct
			    WHERE PAS.productid= :idproduct	AND PAVS.productattributesetid = :attr";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('idproduct', $idproduct);
		$stmt->bindValue('attr', $attr);
		try{
			$rs = $stmt->execute();
			while ($rs = $stmt->fetch()){
				$this->Cart[$idproduct]['attributes'][$attr]['features'][$rs['idfeature']] = Array(
					'feature' => $rs['feature'],
					'group' => $rs['groupname'],
					'attributename' => $rs['attributename']
				);
			}
			$this->updateSession();
		}
		catch (Exception $e){
			throw new Exception('Error while doing sql query- product features (cartModel).');
		}
	}

	public function setGlobalPrice ()
	{
		$price = 0.00;
		$priceWithoutVat = 0.00;
		foreach ($this->Cart as $key => $product){
			if ((! isset($product['attributes']) || $product['attributes'] == NULL)){
				$price += $product['newprice'] * $product['qty'];
			}
			else{
				if (isset($product['standard'])){
					$price += $product['newprice'] * $product['qty'];
					foreach ($product['attributes'] as $attrtab){
						$price += $attrtab['newprice'] * $attrtab['qty'];
					}
				}
				else{
					foreach ($product['attributes'] as $attrtab){
						$price += $attrtab['newprice'] * $attrtab['qty'];
					}
				}
			}
		}
		$this->globalPrice = $price;
	}

	public function setGlobalWeight ()
	{
		$weight = 0.00;
		foreach ($this->Cart as $product){
			if ((! isset($product['attributes']) || $product['attributes'] == NULL)){
				$weight += $product['weight'] * $product['qty'];
			}
			else{
				if (isset($product['standard'])){
					$weight += $product['weight'] * $product['qty'];
					foreach ($product['attributes'] as $attrtab){
						$weight += $attrtab['weight'] * $attrtab['qty'];
					}
				}
				else{
					foreach ($product['attributes'] as $attrtab){
						$weight += $attrtab['weight'] * $attrtab['qty'];
					}
				}
			}
		}
		$this->globalWeight = $weight;
	}

	public function setCartForDelivery ()
	{
		$weight = 0.00;
		$price = 0.00;
		$priceWithoutVat = 0.00;
		$shippingCost = 0.00;
		foreach ($this->Cart as $product){
			if ((! isset($product['attributes']) || $product['attributes'] == NULL)){
				$weight += $product['weight'] * $product['qty'];
				$price += $product['newprice'] * $product['qty'];
			}
			else{
				if (isset($product['standard'])){
					$weight += $product['weight'] * $product['qty'];
					$price += $product['newprice'] * $product['qty'];
					
					foreach ($product['attributes'] as $attrtab){
						$weight += $attrtab['weight'] * $attrtab['qty'];
						$price += $attrtab['newprice'] * $attrtab['qty'];
					}
				}
				else{
					foreach ($product['attributes'] as $attrtab){
						$weight += $attrtab['weight'] * $attrtab['qty'];
						$price += $attrtab['newprice'] * $attrtab['qty'];
					}
				}
			}
		}
		$Data = Array(
			'weight' => $weight,
			'price' => $price
		);
		Session::setActiveCartForDelivery($Data);
	}

	public function setGlobalPriceWithoutVat ()
	{
		$priceWithoutVat = 0.00;
		foreach ($this->Cart as $product){
			if (! isset($product['attributes']) || $product['attributes'] == NULL){
				$priceWithoutVat += $product['pricewithoutvat'] * $product['qty'];
			}
			else{
				if (isset($product['standard'])){
					$priceWithoutVat += $product['pricewithoutvat'] * $product['qty'];
					foreach ($product['attributes'] as $attrtab){
						$priceWithoutVat += $attrtab['pricewithoutvat'] * $attrtab['qty'];
					}
				}
				else{
					foreach ($product['attributes'] as $attrtab){
						$priceWithoutVat += $attrtab['pricewithoutvat'] * $attrtab['qty'];
					}
				}
			}
		}
		$this->globalPriceWithoutVat = $priceWithoutVat;
	}

	public function getGlobalPrice ()
	{
		return $this->globalPrice;
	}

	public function getGlobalWeight ()
	{
		return $this->globalWeight;
	}

	public function getGlobalPriceWithoutVat ()
	{
		return $this->globalPriceWithoutVat;
	}

	public function getShortCartList ()
	{
		return $this->Cart;
	}

	public function getCount ()
	{
		return $this->count;
	}

	public function updateSession ()
	{
		$this->setGlobalPrice();
		$this->setGlobalPriceWithoutVat();
		$this->setGlobalWeight();
		$this->setCartForDelivery();
		
		Session::setActiveCart($this->Cart);
		Session::setActiveGlobalPrice($this->globalPrice);
		Session::setActiveGlobalWeight($this->globalWeight);
		Session::setActiveGlobalPriceWithoutVat($this->globalPriceWithoutVat);
		Session::setActiveDispatchmethodChecked(0);
		Session::setActiveglobalPriceWithDispatchmethod($this->globalPrice);
		Session::setActiveglobalPriceWithDispatchmethodNetto($this->globalPriceWithoutVat);
		Session::setActivePaymentMethodChecked(0);
		Session::unsetActiveClientOrder();
	}

	public function getProductAllCount ()
	{
		$count = 0;
		foreach ($this->Cart as $product){
			if (isset($product['standard']) && $product['standard'] > 0){
				$count += $product['qty'];
				if (isset($product['attributes']) && $product['attributes'] != NULL){
					foreach ($product['attributes'] as $attrtab){
						$count += $attrtab['qty'];
					}
				}
			}
			else{
				if (isset($product['attributes']) && $product['attributes'] != NULL){
					foreach ($product['attributes'] as $attrtab){
						$count += $attrtab['qty'];
					}
				}
			}
		}
		return $count;
	}

	public function getProductIds ()
	{
		$Data = Array(
			0
		);
		foreach ($this->Cart as $product){
			if (isset($product['standard']) && $product['standard'] > 0){
				$Data[] = $product['idproduct'];
				if (isset($product['attributes']) && $product['attributes'] != NULL){
					foreach ($product['attributes'] as $attrtab){
						$Data[] = $attrtab['idproduct'];
					}
				}
			}
			else{
				if (isset($product['attributes']) && $product['attributes'] != NULL){
					foreach ($product['attributes'] as $attrtab){
						$Data[] = $attrtab['idproduct'];
					}
				}
			}
		}
		return $Data;
	}

	public function getProductCartPhotos (&$productCart)
	{
		if (! is_array($productCart)){
			throw new FrontendException('Wrong array given.');
		}
		foreach ($productCart as $index => $key){
			if ((isset($key['mainphotoid']) && $key['mainphotoid'] > 0)){
				$productCart[$index]['smallphoto'] = App::getModel('gallery')->getImagePath(App::getModel('gallery')->getSmallImageById($key['mainphotoid']), App::getURLAdress());
			}
			if (isset($key['attributes']) && $key['attributes'] != NULL){
				foreach ($key['attributes'] as $attrindex => $attrkey){
					if ($attrkey['mainphotoid'] > 0){
						$productCart[$index]['attributes'][$attrindex]['smallphoto'] = App::getModel('gallery')->getImagePath(App::getModel('gallery')->getSmallImageById($attrkey['mainphotoid']), App::getURLAdress());
					}
				}
			}
		}
		return $productCart;
	}

	public function checkRulesCart ()
	{
		$Data = Array();
		$condition = Array();
		if ($this->globalPriceWithoutVat > 0){
			$clientGroupId = Session::getActiveClientGroupid();
			if ($clientGroupId > 0){
				$sql = "SELECT 
							RCCG.rulescartid, 
							RCR.ruleid, 
							RCR.pkid, 
							RCR.pricefrom, 
							RCR.priceto,
							RCCG.suffixtypeid, 
							RCCG.discount, 
							RCCG.freeshipping, 
							S.symbol,
							RCCG.clientgroupid,
							RCT.name,
							RCT.description
						FROM rulescartclientgroup RCCG
							LEFT JOIN rulescart RC ON RCCG.rulescartid = RC.idrulescart
							LEFT JOIN rulescarttranslation RCT ON RCT.rulescartid = RC.idrulescart AND RCT.languageid = :languageid
							LEFT JOIN rulescartrule RCR ON RCR.rulescartid = RC.idrulescart
							LEFT JOIN rulescartview RCV ON RCV.rulescartid = RC.idrulescart
							LEFT JOIN suffixtype S ON RCCG.suffixtypeid = S.idsuffixtype
						WHERE
							RCV.viewid= :viewid
							AND RCCG.clientgroupid= :clientgroupid
							AND IF(RC.datefrom is not null, (cast(RC.datefrom as date) <= curdate()), 1)
							AND IF(RC.dateto is not null, (cast(RC.dateto as date)>= curdate()),1)
						ORDER BY RCR.rulescartid";
				$stmt = Db::getInstance()->prepare($sql);
				$stmt->bindValue('clientgroupid', $clientGroupId);
				$stmt->bindValue('viewid', Helper::getViewId());
				$stmt->bindValue('languageid', Helper::getLanguageId());
			}
			else{
				$sql = "SELECT 
							RCR.rulescartid, 
							RCR.ruleid, 
							RCR.pkid, 
							RCR.pricefrom, 
							RCR.priceto,
							RC.suffixtypeid, 
							RC.discount, 
							RC.freeshipping, 
							S.symbol,
							'clientgroupid'=NULL as clientgroupid,
							RCT.name,
							RCT.description
						FROM  rulescart RC
							LEFT JOIN rulescarttranslation RCT ON RCT.rulescartid = RC.idrulescart AND RCT.languageid = :languageid
							LEFT JOIN rulescartrule RCR ON RCR.rulescartid = RC.idrulescart
							LEFT JOIN rulescartview RCV ON RCV.rulescartid = RC.idrulescart
							LEFT JOIN suffixtype S ON RC.suffixtypeid = S.idsuffixtype
	      				WHERE
	      					RC.discountforall =1
	        				AND RCV.viewid= :viewid
	        				AND IF(RC.datefrom is not null, (cast(RC.datefrom as date) <= curdate()), 1)
							AND IF(RC.dateto is not null, (cast(RC.dateto as date)>= curdate()),1)
						ORDER BY RCR.rulescartid";
				$stmt = Db::getInstance()->prepare($sql);
				$stmt->bindValue('viewid', Helper::getViewId());
				$stmt->bindValue('languageid', Helper::getLanguageId());
			}
			try{
				$stmt->execute();
				while ($rs = $stmt->fetch()){
					$rulescartid = $rs['rulescartid'];
					$ruleid = $rs['ruleid'];
					$currencySymbol = Session::getActiveCurrencySymbol();
					if ($rs['symbol'] == '%'){
						$Data[$rulescartid]['discount'] = abs($rs['discount'] - 100) . $rs['symbol'];
						$type = ($rs['discount'] > 100) ? 1 : 0;
					}
					else{
						$Data[$rulescartid]['discount'] = $rs['symbol'] . $rs['discount'];
						$type = ($rs['symbol'] == '+') ? 1 : 0;
					}
					$Data[$rulescartid]['freeshipping'] = $rs['freeshipping'];
					$Data[$rulescartid]['name'] = $rs['name'];
					$Data[$rulescartid]['description'] = $rs['description'];
					
					switch ($ruleid) {
						case 9: // delivery
							if (isset($Data[$rulescartid][$ruleid])){
								$Data[$rulescartid][$ruleid]['condition'] = $Data[$rulescartid][$ruleid]['condition'] . " " . _('TXT_OR') . " " . $this->getDeliveryToCondition($rs['pkid']);
							}
							else{
								$Data[$rulescartid][$ruleid] = Array(
									'is' => 0,
									'ruleid' => $ruleid,
									'condition' => _('TXT_DELIVERY_TYPE') . ": " . $this->getDeliveryToCondition($rs['pkid'])
								);
							}
							break;
						case 10: // paymentmethod
							if (isset($Data[$rulescartid][$ruleid])){
								$Data[$rulescartid][$ruleid]['condition'] = $Data[$rulescartid][$ruleid]['condition'] . " " . _('TXT_OR') . " " . $this->getPaymentToCondition($rs['pkid']);
							}
							else{
								$Data[$rulescartid][$ruleid] = Array(
									'is' => 0,
									'ruleid' => $ruleid,
									'condition' => _('TXT_PAYMENT_TYPE') . ": " . $this->getPaymentToCondition($rs['pkid'])
								);
							}
							break;
						case 11: // final cart price
							if (isset($Data[$rulescartid][$ruleid])){
								$Data[$rulescartid][$ruleid]['condition'] = $Data[$rulescartid][$ruleid]['condition'] . " " . _('TXT_OR') . " " . $rs['pricefrom'];
							}
							else{
								$Data[$rulescartid][$ruleid] = Array(
									'is' => 0,
									'ruleid' => $ruleid,
									'condition' => _('TXT_CART_VALUE_AMOUNT_EXCEED') . ": " . $rs['pricefrom'] . $currencySymbol
								);
							}
							break;
						case 12: // final cart price
							if (isset($Data[$rulescartid][$ruleid])){
								$Data[$rulescartid][$ruleid]['condition'] = $Data[$rulescartid][$ruleid]['condition'] . " " . _('TXT_OR') . " " . $rs['priceto'] . $currencySymbol;
							}
							else{
								$Data[$rulescartid][$ruleid] = Array(
									'is' => 0,
									'ruleid' => $ruleid,
									'condition' => _('TXT_CART_VALUE_NOT_GREATER_THAN') . ": " . $rs['priceto'] . $currencySymbol
								);
							}
							break;
						case 13: // final cart price with dispatch method
							if (isset($Data[$rulescartid][$ruleid])){
								$Data[$rulescartid][$ruleid]['condition'] = $Data[$rulescartid][$ruleid]['condition'] . " " . _('TXT_OR') . " " . $rs['pricefrom'] . $currencySymbol;
							}
							else{
								$Data[$rulescartid][$ruleid] = Array(
									'is' => 0,
									'ruleid' => $ruleid,
									'condition' => _('TXT_CART_DELIVERY_VALUE_AMOUNT') . ": " . $rs['pricefrom'] . $currencySymbol
								);
							}
							break;
						case 14: // final cart price with dispatch method
							if (isset($Data[$rulescartid][$ruleid])){
								$Data[$rulescartid][$ruleid]['condition'] = $Data[$rulescartid][$ruleid]['condition'] . " " . _('TXT_OR') . " " . $rs['priceto'] . $currencySymbol;
							}
							else{
								$Data[$rulescartid][$ruleid] = Array(
									'is' => 0,
									'ruleid' => $ruleid,
									'condition' => _('TXT_CART_DELIVERY_VALUE_NOT_GREATER_THAN') . ": " . $rs['priceto'] . Session::getActiveCurrencySymbol()
								);
							}
							break;
					}
				}
				
				if (count($Data) > 0){
					foreach ($Data as $rulescart => $rules){
						
						foreach ($rules as $rule){
							if (is_array($rule) && $rule['is'] == 0){
								$condition[$rulescart]['conditions'][$rule['ruleid']] = $rule['condition'];
							}
						}
						$condition[$rulescart]['discount'] = $rules['discount'];
						$condition[$rulescart]['freeshipping'] = $rules['freeshipping'];
						$condition[$rulescart]['name'] = $rules['name'];
						$condition[$rulescart]['description'] = $rules['description'];
						$condition[$rulescart]['type'] = $type;
					}
				}
				else{
					$condition = 0;
				}
			}
			catch (Exception $e){
				throw new FrontendException(_('ERR_RULES_CART'));
			}
		}
		else{
			$condition = 0;
		}
		return $condition;
	}

	public function getDeliveryToCondition ($iddispatchmethod)
	{
		$dispatchmethodname = '';
		$sql = "SELECT 
        name
        FROM dispatchmethodtranslation 
				WHERE dispatchmethodid = :iddispatchmethod AND languageid = :languageid";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('iddispatchmethod', $iddispatchmethod);
		try{
			$stmt->execute();
			$rs = $stmt->fetch();
			if ($rs){
				$dispatchmethodname = $rs['name'];
			}
		}
		catch (Exception $e){
			throw new FrontendException(_('ERR_DELIVERER_CHECK'));
		}
		return $dispatchmethodname;
	}

	public function getPaymentToCondition ($idpaymentmethod)
	{
		$paymentname = '';
		$sql = "SELECT 
					PMT.name as paymentname
				FROM paymentmethodtranslation PMT
				WHERE PMT.paymentmethodid = :idpaymentmethod AND PMT.languageid = :languageid";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('idpaymentmethod', $idpaymentmethod);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		try{
			$stmt->execute();
			$rs = $stmt->fetch();
			if ($rs){
				$paymentname = $rs['paymentname'];
			}
		}
		catch (Exception $e){
			throw new FrontendException(_('ERR_PAYMENT_CHECK'));
		}
		return $paymentname;
	}

	public function setTempCartAfterCurrencyChange ()
	{
		$cart = Session::getActiveCart();
		Session::setActiveCart(NULL);
		if (is_array($cart)){
			foreach ($cart as $product){
				$productid = $product['idproduct'];
				if ($productid > 0){
					if (isset($product['standard']) && $product['standard'] == 1){
						$this->cartAddStandardProduct($productid, $product['qty']);
					}
					if (isset($product['attributes']) || ! empty($product['attributes'])){
						foreach ($product['attributes'] as $attributes){
							$attr = $attributes['attr'];
							$this->cartAddProductWithAttr($productid, $attributes['qty'], $attributes['attr']);
						}
					}
				}
			}
		}
	}

	public function getBasketModalTemplate ()
	{
		$this->registry->template->assign('product', $this->product);
		return $this->registry->template->fetch('basket_modal.tpl');
	}

	public function getProductModalTemplate ($product)
	{
		App::getModel('product/product')->getPhotos($product);
		$selectAttributes = App::getModel('product/product')->getProductAttributeGroups($product);
		$attset = App::getModel('product/product')->getProductVariant($product);
		
		foreach ($selectAttributes as $key => $val){
			natsort($val['attributes']);
			$selectAttributes[$key]['attributes'] = $val['attributes'];
		}
		
		$Data = Array();
		foreach ($attset as $group => $data){
			$Data[implode(',', array_keys($data['variant']))] = Array(
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
		
		$this->registry->template->assign('product', $product);
		$this->registry->template->assign('attributes', $selectAttributes);
		$this->registry->template->assign('variants', json_encode($Data));
		$this->registry->template->assign('attset', $attset);
		return $this->registry->template->fetch('product_modal.tpl');
	}

	public function getCartPreviewTemplate ()
	{
		$productCart = $this->getShortCartList();
    $productCart = $this->getProductCartPhotos($productCart);

		$this->registry->template->assign('count', $this->getProductAllCount());
		$this->registry->template->assign('globalPrice', $this->getGlobalPrice());
		$this->registry->template->assign('productCart', $productCart);
		return $this->registry->template->fetch('cart_preview.tpl');
	}

	public function getMinimumOrderValue ()
	{
		$sql = 'SELECT
					ROUND((V.minimumordervalue * CR.exchangerate) - :globalprice, 2) AS required
				FROM view V
				LEFT JOIN currencyrates CR ON CR.currencyfrom = V.currencyid AND CR.currencyto = :currencyto
				WHERE V.idview = :viewid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('globalprice', $this->getGlobalPrice());
		$stmt->bindValue('currencyto', Session::getActiveCurrencyId());
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->execute();
		$rs = $stmt->fetch();
		if ($rs){
			return $rs['required'];
		}
		return 0;
	}
} 
