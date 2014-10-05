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
 * $Id: payment.php 619 2011-12-19 21:09:00Z gekosale $
 */
namespace Gekosale;

use xajaxResponse;

class PaymentModel extends Component\Model
{

	public function getPaymentMethods ()
	{
		$iddispatchmethod = Session::getActiveDispatchmethodChecked();
		$sql = "SELECT 
					PMT.name, 
					PM.idpaymentmethod, 
					PM.controller
				FROM paymentmethod PM
				LEFT JOIN paymentmethodtranslation PMT ON PMT.paymentmethodid = PM.idpaymentmethod AND PMT.languageid = :languageid
				LEFT JOIN paymentmethodview PV ON PV.paymentmethodid = idpaymentmethod
				LEFT JOIN dispatchmethodpaymentmethod DMPM ON PM.idpaymentmethod = DMPM.paymentmethodid
				LEFT JOIN dispatchmethod DM ON DM.iddispatchmethod = DMPM.dispatchmethodid
				WHERE DM.iddispatchmethod = :iddispatchmethod AND PM.active = 1 AND PV.viewid = :viewid
				ORDER BY PM.hierarchy ASC";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('iddispatchmethod', $iddispatchmethod['dispatchmethodid']);
		try{
			$stmt->execute();
			$Data = Array();
			while ($rs = $stmt->fetch()){
				$controller = $rs['controller'];
				$Data[] = Array(
					'name' => $rs['name'],
					'idpaymentmethod' => $rs['idpaymentmethod']
				);
			}
		}
		catch (Exception $e){
			throw new FrontendException('Error while doing sql query- getPaymentMethods- paymentModel.');
		}
		return $Data;
	}

	public function setAJAXPaymentMethodChecked ($idpaymentmethod, $paymentmethodname)
	{
		$objResponse = new xajaxResponse();
		$this->setPaymentMethodChecked($idpaymentmethod, $paymentmethodname);
		Session::setActiveClientOrder(0);
		$objResponse->clear("cart-contents", "innerHTML");
		$objResponse->append("cart-contents", "innerHTML", App::getModel('cart')->getCartTableTemplate());
		$objResponse->script("qtySpinner();");
		return $objResponse;
	}

	public function setPaymentMethodChecked ($idpaymentmethod, $paymentmethodname)
	{
		if ($idpaymentmethod != NULL){
			$activePayment = Array(
				'idpaymentmethod' => $idpaymentmethod,
				'paymentmethodname' => $paymentmethodname
			);
			Session::setActivePaymentMethodChecked($activePayment);
		}
		else{
			Session::setActivePaymentMethodChecked(0);
		}
	}

	public function getPaymentMethodById ($id)
	{
		$sql = 'SELECT controller 
					FROM paymentmethod 
					LEFT JOIN paymentmethodview PV ON PV.paymentmethodid = idpaymentmethod
					WHERE idpaymentmethod = :idpaymentmethod AND PV.viewid=:viewid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->bindValue('idpaymentmethod', $id);
		$stmt->execute();
		$rs = $stmt->fetch();
		$Data = array();
		if ($rs){
			return $rs['controller'];
		}
	}

	public function checkEraty ($idpaymentmethod)
	{
		$price = Session::getActiveglobalPriceWithDispatchmethod();
		if ($price > 0){
			if ($price < 100){
				return 0;
			}
		}
		else{
			$order = Session::getActiveClientOrder();
			if (! isset($order['priceWithDispatchMethod']) || $order['priceWithDispatchMethod'] < 100){
				return 0;
			}
		}
		$sql = "SELECT ES.wariantsklepu, ES.numersklepu, ES.`char`
					FROM eratysettings ES
						LEFT JOIN paymentmethodview PV ON  ES.paymentmethodid  = PV.paymentmethodid
					WHERE PV.viewid = :viewid
					AND ES.paymentmethodid = :idpaymentmethod";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->bindValue('idpaymentmethod', $idpaymentmethod);
		$stmt->execute();
		$rs = $stmt->fetch();
		if ($rs){
			$Data = Array(
				'wariantsklepu' => $rs['wariantsklepu'],
				'numersklepu' => $rs['numersklepu'],
				'char' => $rs['char']
			);
			return $Data;
		}
		return 0;
	}
}
?>
