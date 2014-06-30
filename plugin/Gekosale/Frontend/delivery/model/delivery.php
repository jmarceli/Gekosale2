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
 * $Id: delivery.php 627 2012-01-20 22:05:57Z gekosale $
 */
namespace Gekosale;

use xajaxResponse;

class DeliveryModel extends Component\Model
{
	protected $priceFormat = '%01.2f';

	public function priceFormatParser ($price)
	{
		return sprintf($this->priceFormat, $price);
	}

	public function getDispatchmethod ()
	{
		$sql = "SELECT 
					DP.name, 
					DP.iddispatchmethod
				FROM dispatchmethod DP 
				LEFT JOIN dispatchmethodview DV ON DV.dispatchmethodid = iddispatchmethod
				WHERE DV.viewid=:viewid
				ORDER BY DP.hierarchy ASC";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
			$Data = Array();
			while ($rs = $stmt->fetch()){
				$Data[] = Array(
					'name' => _($rs['name']),
					'iddispatchmethod' => $rs['iddispatchmethod']
				);
			}
		}
		catch (Exception $e){
			throw new FrontendException('Error while doing sql query- getDispatchmethod- deliveryModel');
		}
		return $Data;
	}

	public function getDispatchmethodPrice ()
	{
		if (Session::getActiveShippingCountryId() == NULL){
			$shippingCountryId = $this->registry->loader->getParam('countryid');
		}
		else{
			$shippingCountryId = Session::getActiveShippingCountryId();
		}
		$cartData = Session::getActiveCartForDelivery();
		$globalprice = isset($cartData['price']) ? $cartData['price'] : 0;
		$globalweight = isset($cartData['weight']) ? $cartData['weight'] : 0;
		
		$Data = Array();
		
		$sql = "SELECT 
					DP.dispatchmethodid, 
					DP.`from`, 
					DP.`to`, 
					V.value, 
					DP.iddispatchmethodprice,
					ROUND(DP.dispatchmethodcost * CR.exchangerate, 4) AS dispatchmethodcostnetto,
					IF(DP.vat IS NOT NULL, ROUND((DP.dispatchmethodcost + (DP.dispatchmethodcost*(V.`value`/100))) * CR.exchangerate,4), DP.dispatchmethodcost * CR.exchangerate) as dispatchmethodcost,
					CASE
  						WHEN (`from`<>0 AND `from`<:globalprice AND `to`=0 AND DP.dispatchmethodcost =0) THEN D.name
 					 	WHEN (:globalprice BETWEEN `from` AND `to`) THEN D.name
  						WHEN (`to` = 0 AND `from`<:globalprice AND DP.dispatchmethodcost <> 0) THEN D.name
  						WHEN (`from`=0 AND `to`=0 AND DP.dispatchmethodcost =0) THEN D.name
					END as name,
					D.description,
					D.photo,
					D.countryids
				FROM dispatchmethodprice DP
				LEFT JOIN dispatchmethod D ON D.iddispatchmethod = dispatchmethodid
				LEFT JOIN currencyrates CR ON CR.currencyfrom = D.currencyid AND CR.currencyto = :currencyto
				LEFT JOIN vat V ON V.idvat = DP.vat
				LEFT JOIN dispatchmethodview DV ON DV.dispatchmethodid = D.iddispatchmethod
				WHERE DV.viewid= :viewid AND IF(D.maximumweight IS NOT NULL, D.maximumweight >= :globalweight, 1) AND D.type = 1
				ORDER BY D.hierarchy ASC";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('globalprice', $globalprice);
		$stmt->bindValue('globalweight', $globalweight);
		$stmt->bindValue('currencyto', Session::getActiveCurrencyId());
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$dispatchmethodid = $rs['dispatchmethodid'];
			if ($rs['name'] != NULL){
				$dispatchmethodcost = $rs['dispatchmethodcost'];
				$dispatchmethodcostnetto = $rs['dispatchmethodcostnetto'];
				$countryIds = explode(',', $rs['countryids']);
				if (in_array($shippingCountryId, $countryIds) || $rs['countryids'] == ''){
					$Data[$dispatchmethodid] = Array(
						'dispatchmethodid' => $dispatchmethodid,
						'name' => _($rs['name']),
						'from' => $rs['from'],
						'to' => $rs['to'],
						'countryids' => $countryIds,
						'vatvalue' => $rs['value'],
						'dispatchmethodcost' => $dispatchmethodcost,
						'dispatchmethodcostnetto' => $dispatchmethodcostnetto,
						'description' => $rs['description'],
						'photo' => App::getModel('gallery')->getImagePath(App::getModel('gallery')->getMediumImageById($rs['photo'])),
					);
				}
			}
		}
		
		$sql = "SELECT
					DW.dispatchmethodid, 
					DW.`from`, 
					DW.`to`, 
					DW.iddispatchmethodweight,
					ROUND(DW.cost * CR.exchangerate, 4) AS dispatchmethodcostnetto,
					IF(DW.vat IS NOT NULL, ROUND(DW.cost+(DW.cost*(V.`value`/100)) * CR.exchangerate,4), DW.cost * CR.exchangerate) as dispatchmethodcost, 
					D.freedelivery,
					CASE
  						WHEN (`from`<>0 AND `from`<:globalweight AND `to`=0 AND DW.cost =0) THEN D.name
 					 	WHEN (:globalweight BETWEEN `from` AND `to`) THEN D.name
  						WHEN (`to` = 0 AND `from`<:globalweight AND DW.cost <> 0) THEN D.name
  						WHEN (`from`=0 AND `to`=0 AND DW.cost = 0) THEN D.name
					END as name,
					D.description,
					D.photo,
					V.value,
					D.countryids
				FROM dispatchmethodweight DW
				LEFT JOIN dispatchmethod D ON D.iddispatchmethod = dispatchmethodid
				LEFT JOIN currencyrates CR ON CR.currencyfrom = D.currencyid AND CR.currencyto = :currencyto
				LEFT JOIN vat V ON V.idvat = DW.vat
				LEFT JOIN dispatchmethodview DV ON DV.dispatchmethodid = D.iddispatchmethod
				WHERE DV.viewid= :viewid AND D.type = 2
				ORDER BY D.hierarchy ASC";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('globalprice', $globalprice);
		$stmt->bindValue('globalweight', $globalweight);
		$stmt->bindValue('currencyto', Session::getActiveCurrencyId());
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$dispatchmethodid = $rs['dispatchmethodid'];
			if ($rs['name'] != NULL){
				if (($rs['freedelivery'] > 0) && ($rs['freedelivery'] <= $globalprice)){
					$dispatchmethodcost = 0.00;
					$dispatchmethodcostnetto = 0;
				}
				else{
					$dispatchmethodcost = $rs['dispatchmethodcost'];
					$dispatchmethodcostnetto = $rs['dispatchmethodcostnetto'];
				}
				
				$countryIds = explode(',', $rs['countryids']);
				if (in_array($shippingCountryId, $countryIds) || $rs['countryids'] == ''){
					$Data[$dispatchmethodid] = Array(
						'dispatchmethodid' => $dispatchmethodid,
						'name' => _($rs['name']),
						'from' => $rs['from'],
						'to' => $rs['to'],
						'countryids' => $countryIds,
						'vatvalue' => $rs['value'],
						'dispatchmethodcost' => $dispatchmethodcost,
						'dispatchmethodcostnetto' => $dispatchmethodcostnetto,
						'description' => $rs['description'],
						'photo' => App::getModel('gallery')->getImagePath(App::getModel('gallery')->getMediumImageById($rs['photo'])),
					);
				}
			}
		}
		return $Data;
	}

	public function getDispatchmethodPriceForProduct ($globalprice, $globalweight)
	{
		$Data = Array();
		
		$sql = "SELECT 
					DP.dispatchmethodid,
					DP.`from`, 
					DP.`to`, 
					V.value, 
					DP.iddispatchmethodprice,
					IF(DP.vat IS NOT NULL, ROUND(DP.dispatchmethodcost + (DP.dispatchmethodcost * (V.`value`/100)), 4), DP.dispatchmethodcost) * CR.exchangerate as dispatchmethodcost,
					CASE
  						WHEN (`from` <> 0 AND `from` < :globalprice AND `to`= 0 AND DP.dispatchmethodcost = 0) THEN D.name
 					 	WHEN (:globalprice BETWEEN `from` AND `to`) THEN D.name
  						WHEN (`to` = 0 AND `from` < :globalprice AND DP.dispatchmethodcost <> 0) THEN D.name
  						WHEN (`from` = 0 AND `to`=0 AND DP.dispatchmethodcost =0) THEN D.name
					END as name
				FROM dispatchmethodprice DP
				LEFT JOIN dispatchmethod D ON D.iddispatchmethod = dispatchmethodid
				LEFT JOIN vat V ON V.idvat = DP.vat
				LEFT JOIN dispatchmethodview DV ON DV.dispatchmethodid = D.iddispatchmethod
				LEFT JOIN currencyrates CR ON CR.currencyfrom = D.currencyid AND CR.currencyto = :currencyto
				WHERE DV.viewid= :viewid AND IF(D.maximumweight IS NOT NULL, D.maximumweight >= :globalweight, 1) AND D.type = 1
				ORDER BY D.hierarchy ASC";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('globalprice', $globalprice);
		$stmt->bindValue('globalweight', $globalweight);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->bindValue('currencyto', Session::getActiveCurrencyId());
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$dispatchmethodid = $rs['dispatchmethodid'];
			if ($rs['name'] != NULL){
				$dispatchmethodcost = $rs['dispatchmethodcost'];
				
				$Data[] = Array(
					'dispatchmethodid' => $dispatchmethodid,
					'name' => _($rs['name']),
					'from' => $rs['from'],
					'to' => $rs['to'],
					'vatvalue' => $rs['value'],
					'dispatchmethodcost' => $this->priceFormatParser($dispatchmethodcost)
				);
			}
		}
		
		$sql = "SELECT
					DW.dispatchmethodid, 
					DW.`from`, 
					DW.`to`, 
					V.value, 
					DW.iddispatchmethodweight,
					IF(DW.vat IS NOT NULL, ROUND(DW.cost+(DW.cost*(V.`value`/100)),4), DW.cost) * CR.exchangerate as dispatchmethodcost, 
					D.freedelivery,
					CASE
  						WHEN (`from`<>0 AND `from`<:globalweight AND `to`=0 AND DW.cost =0) THEN D.name
 					 	WHEN (:globalweight BETWEEN `from` AND `to`) THEN D.name
  						WHEN (`to` = 0 AND `from`<:globalweight AND DW.cost <> 0) THEN D.name
  						WHEN (`from`=0 AND `to`=0 AND DW.cost = 0) THEN D.name
					END as name
				FROM dispatchmethodweight DW
				LEFT JOIN vat V ON V.idvat = DW.vat
				LEFT JOIN dispatchmethod D ON D.iddispatchmethod = dispatchmethodid
				LEFT JOIN dispatchmethodview DV ON DV.dispatchmethodid = D.iddispatchmethod
				LEFT JOIN currencyrates CR ON CR.currencyfrom = D.currencyid AND CR.currencyto = :currencyto
				WHERE DV.viewid= :viewid AND D.type = 2
				ORDER BY D.hierarchy ASC";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('globalprice', $globalprice);
		$stmt->bindValue('globalweight', $globalweight);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->bindValue('currencyto', Session::getActiveCurrencyId());
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$dispatchmethodid = $rs['dispatchmethodid'];
			if ($rs['name'] != NULL){
				if (($rs['freedelivery'] > 0) && ($rs['freedelivery'] <= $globalprice)){
					$dispatchmethodcost = 0.00;
				}
				else{
					$dispatchmethodcost = $rs['dispatchmethodcost'];
				}
				
				$Data[] = Array(
					'dispatchmethodid' => $dispatchmethodid,
					'name' => _($rs['name']),
					'from' => $rs['from'],
					'to' => $rs['to'],
					'vatvalue' => $rs['value'],
					'dispatchmethodcost' => $this->priceFormatParser($dispatchmethodcost)
				);
			}
		}
		return $Data;
	}

	public function setAJAXDispatchmethodChecked ($dispatchmethodid)
	{
		$objResponseDispatchmethod = new xajaxResponse();
		try{
			$this->setDispatchmethodChecked($dispatchmethodid);
			Session::setActivePaymentMethodChecked(0);
			Session::setActivePaymentData(NULL);
			$objResponseDispatchmethod->clear("cart-contents", "innerHTML");
			$objResponseDispatchmethod->append("cart-contents", "innerHTML", App::getModel('cart')->getCartTableTemplate());
			$objResponseDispatchmethod->script("qtySpinner();");
		}
		catch (Exception $e){
			throw new FrontendException('Error while checking deliver: getAJAXDelivererChecked- dispatchmethodModel');
		}
		return $objResponseDispatchmethod;
	}

	public function setDispatchmethodChecked ($dispatchmethodid)
	{
		Session::setActiveDispatchmethodChecked(0);
		Session::setActiveglobalPriceWithDispatchmethod(Session::getActiveGlobalPrice());
		Session::setActiveglobalPriceWithDispatchmethodNetto(Session::getActiveGlobalPriceWithoutVat());
		if ($dispatchmethodid != NULL){
			$dispatchmethods = $this->getDispatchmethodPrice();
			
			$dispatchmethodcost = $dispatchmethods[$dispatchmethodid]['dispatchmethodcost'];
			$dispatchmethodcostnetto = $dispatchmethods[$dispatchmethodid]['dispatchmethodcostnetto'];
			
			$activeDispatchmethod = Array(
				'dispatchmethodid' => $dispatchmethodid,
				'dispatchmethodcost' => $this->priceFormatParser($dispatchmethodcost),
				'dispatchmethodcostnetto' => $this->priceFormatParser($dispatchmethodcostnetto),
				'dispatchmethodname' => $dispatchmethods[$dispatchmethodid]['name']
			);
			
			Session::setActiveDispatchmethodChecked($activeDispatchmethod);
			Session::setActiveglobalPriceWithDispatchmethod(Session::getActiveGlobalPrice() + $dispatchmethodcost);
			Session::setActiveglobalPriceWithDispatchmethodNetto(Session::getActiveGlobalPriceWithoutVat() + $dispatchmethodcostnetto);
		}
	}

	public function selectDeliveryOption ($dispatchmethodid, $option)
	{
		$objResponseDispatchmethod = new xajaxResponse();
		$active = Array(
			'id' => $dispatchmethodid,
			'option' => $option
		);
		Session::setActiveDispatchmethodOption($active);
		$objResponseDispatchmethod->clear("cart-contents", "innerHTML");
		$objResponseDispatchmethod->append("cart-contents", "innerHTML", App::getModel('cart')->getCartTableTemplate());
		$objResponseDispatchmethod->script("$('.spinnerhide').spinner({min: 1, max: 100, width: 20}).width(50)");
		return $objResponseDispatchmethod;
	}

	public function setAjaxShippingCountryId ($countryid)
	{
		$objResponse = new xajaxResponse();
		Session::setActiveDispatchmethodChecked(NULL);
		Session::setActiveShippingCountryId($countryid);
		$objResponse->script('window.location.reload(false);');
		return $objResponse;
	}
}