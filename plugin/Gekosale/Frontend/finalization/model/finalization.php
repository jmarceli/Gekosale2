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
 * $Id: finalization.php 627 2012-01-20 22:05:57Z gekosale $
 */
namespace Gekosale;

use xajaxResponse;

class FinalizationModel extends Component\Model
{

	public function __construct ($registry, $modelFile)
	{
		parent::__construct($registry, $modelFile);
		$this->layer = $this->registry->loader->getCurrentLayer();
	}

	public function saveOrder ($Data)
	{
		$objResponse = new xajaxResponse();
		
		try{
			$order = $this->setClientOrder($Data);
			if ($order != NULL){
        if (empty($order['dispatchmethod'])) {
          $objResponse->script("GError('" . _('ERR_ORDER_SAVE') . "', '" . _('ERR_NO_DELIVERY_SELECTED') . "');");
          return $objResponse;
        }
        if (empty($order['payment'])) {
          $objResponse->script("GError('" . _('ERR_ORDER_SAVE') . "', '" . _('ERR_NO_PAYMENT_SELECTED') . "');");
          return $objResponse;
        }
				$saveOrder = App::getModel('order')->saveOrder($order);
				$clientid = Session::getActiveClientid();
				Session::setActiveorderid($saveOrder);
				$email = $order['contactData']['email'];
				$orderid = Session::getActiveorderid();
				$orderlink = App::getModel('order')->generateOrderLink($orderid);
				if ($orderlink != NULL && $orderid != NULL){
					App::getModel('order')->changeOrderLink($orderid, $orderlink);
          $banktransfer = $this->registry->core->loadModuleSettings('banktransfer', Helper::getViewId());
          $payment_model = App::getModel('Admin/paymentmethod/paymentmethod')->getPaymentmethodModelById($order['payment']['idpaymentmethod']);
					
					$this->registry->template->assign('order', $order);
					$this->registry->template->assign('orderId', $orderid);
					$this->registry->template->assign('orderlink', $orderlink);
					$this->registry->template->assign('paymentmodel', $payment_model);
					$this->registry->template->assign('bankdata', $banktransfer);

          $mailer = App::getModel('mailer');
          if (!empty($this->layer['terms'])) {
            $mailer->AddAttachment('./upload/' . $this->layer['terms'], Core::clearUTF(_('TXT_CONDITIONS')) . '.pdf');
          }
					
					$mailer->sendEmail(Array(
						'template' => 'orderClient',
						'email' => Array(
							$email
						),
						'bcc' => true,
						'subject' => _('TXT_ORDER_CLIENT') . ': ' . $orderid,
						'viewid' => Helper::getViewId()
					));
					
					Session::unsetActiveCart();
					Session::unsetActiveglobalPriceWithDispatchmethod();
					Session::unsetActiveglobalPriceWithDispatchmethodNetto();
					Session::unsetActiveDispatchmethodChecked();
					Session::unsetActivePaymentMethodChecked();
					Session::unsetActiveGlobalPrice();
					Session::unsetActiveGlobalPriceWithoutVat();
					
					$paymentMethodData = Array(
						'orderId' => Session::getActiveorderid(),
						'orderData' => Session::getActiveClientOrder()
					);
					
					Session::setActivePaymentData($paymentMethodData);
					Session::unsetActiveorderid();
					Session::unsetActiveClientOrder();
					
					App::getModel('dataset')->flushCache();
					
					$url = $this->registry->router->generate('frontend.payment', true, Array(
						'action' => 'accept'
					));
					
					$objResponse->script("window.location.href = '{$url}';");
				}
			}
      else {
        $objResponse->script("GError('" . _('ERR_ORDER_SAVE') . "','" . _('ERR_ORDER_NOT_DEFINED') . "');");
      }
		}
		catch (Exception $e){
			$objResponse->script("GError('" . _('ERR_ORDER_SAVE') . "','" . _('ERR_ORDER_SAVE_MSG') . "');");
		}
		
		return $objResponse;
	}

	public function setClientOrder ($Data = Array())
	{
		$customerOpinion = isset($Data['customeropinion']) ? App::getModel('formprotection')->cropDangerousCode($Data['customeropinion']) : '';
		
		Event::notify($this, 'frontend.finalization.setActiveClientOrder', Array(
			'dispatchmethod' => Session::getActiveDispatchmethodChecked(),
			'cart' => Session::getActiveCart()
		));
		
		$clientOrder = Array(
			'cart' => Session::getActiveCart(),
			'globalPrice' => App::getModel('cart')->getGlobalPrice(),
			'globalPriceWithoutVat' => App::getModel('cart')->getGlobalPriceWithoutVat(),
			'priceWithDispatchMethod' => Session::getActiveglobalPriceWithDispatchmethod(),
			'priceWithDispatchMethodNetto' => Session::getActiveglobalPriceWithDispatchmethodNetto(),
			'count' => App::getModel('cart/cart')->getProductAllCount(),
			'clientdata' => App::getModel('client')->getClient(),
			'clientaddress' => Session::getActiveOrderClientAddress(),
			'deliveryAddress' => Session::getActiveOrderDeliveryAddress(),
			'contactData' => Session::getActiveOrderContactData(),
			'dispatchmethod' => Session::getActiveDispatchmethodChecked(),
			'payment' => Session::getActivePaymentMethodChecked(),
			'clientid' => Session::getActiveClientid(),
			'customeropinion' => $customerOpinion
		);
		
		$rulesDiscount = $this->getRulesCart($clientOrder);
		if (is_array($rulesDiscount) && count($rulesDiscount) > 0 && $clientOrder['dispatchmethod'] > 0 && $clientOrder['payment'] > 0){
			
			if ($rulesDiscount['freeshipping'] == 1){
				$clientOrder['priceWithDispatchMethod'] = $clientOrder['priceWithDispatchMethod'] - $clientOrder['dispatchmethod']['dispatchmethodcost'];
				$clientOrder['priceWithDispatchMethodNetto'] = $clientOrder['priceWithDispatchMethodNetto'] - $clientOrder['dispatchmethod']['dispatchmethodcostnetto'];
				$clientOrder['dispatchmethod']['dispatchmethodcost'] = 0;
				$clientOrder['dispatchmethod']['dispatchmethodcostnetto'] = 0;
			}
			
			if ($rulesDiscount['symbol'] == '+'){
				$globalPricePromo = sprintf('%01.2f', $clientOrder['globalPrice'] + $rulesDiscount['discount']);
				$globalPriceWithoutVatPromo = sprintf('%01.2f', $clientOrder['globalPriceWithoutVat'] + $rulesDiscount['discount']);
				$priceWithDispatchMethodPromo = sprintf('%01.2f', $clientOrder['priceWithDispatchMethod'] + $rulesDiscount['discount']);
				$priceWithDispatchMethodNettoPromo = sprintf('%01.2f', $clientOrder['priceWithDispatchMethodNetto'] + $rulesDiscount['discount']);
				$message = $rulesDiscount['symbol'] . $this->registry->core->processPrice($rulesDiscount['discount']);
			}
			elseif ($rulesDiscount['symbol'] == '-'){
				$globalPricePromo = sprintf('%01.2f', $clientOrder['globalPrice'] - $rulesDiscount['discount']);
				$globalPriceWithoutVatPromo = sprintf('%01.2f', $clientOrder['globalPriceWithoutVat'] - $rulesDiscount['discount']);
				$priceWithDispatchMethodPromo = sprintf('%01.2f', $clientOrder['priceWithDispatchMethod'] - $rulesDiscount['discount']);
				$priceWithDispatchMethodNettoPromo = sprintf('%01.2f', $clientOrder['priceWithDispatchMethodNetto'] - $rulesDiscount['discount']);
				$message = $rulesDiscount['symbol'] . $this->registry->core->processPrice($rulesDiscount['discount']);
			}
			elseif ($rulesDiscount['symbol'] == '%'){
				$globalPricePromo = sprintf('%01.2f', abs($clientOrder['globalPrice'] * ($rulesDiscount['discount']) / 100));
				$globalPriceWithoutVatPromo = sprintf('%01.2f', abs($clientOrder['globalPriceWithoutVat'] * ($rulesDiscount['discount']) / 100));
				$priceWithDispatchMethodPromo = sprintf('%01.2f', abs($clientOrder['priceWithDispatchMethod'] * ($rulesDiscount['discount']) / 100));
				$priceWithDispatchMethodNettoPromo = sprintf('%01.2f', abs($clientOrder['priceWithDispatchMethodNetto'] * ($rulesDiscount['discount']) / 100));
				$message = ((($rulesDiscount['discount'] - 100) > 0) ? '+' : '-') . abs($rulesDiscount['discount'] - 100) . $rulesDiscount['symbol'];
			}
			if ($globalPricePromo > 0 && $globalPriceWithoutVatPromo > 0 && $priceWithDispatchMethodPromo > 0 && $priceWithDispatchMethodNettoPromo > 0){
				$clientOrder['globalPricePromo'] = $globalPricePromo;
				$clientOrder['globalPriceWithoutVatPromo'] = $globalPriceWithoutVatPromo;
				$clientOrder['priceWithDispatchMethodPromo'] = $priceWithDispatchMethodPromo;
				$clientOrder['priceWithDispatchMethodNettoPromo'] = $priceWithDispatchMethodNettoPromo;
				$clientOrder['rulescart'] = $rulesDiscount['name'];
				$clientOrder['rulescartdescription'] = $rulesDiscount['description'];
				$clientOrder['rulescartid'] = $rulesDiscount['rulescartid'];
				$clientOrder['rulescartmessage'] = $message;
				$clientOrder['rulescartfreeshipping'] = $rulesDiscount['freeshipping'];
			}
		}
		Session::setActiveClientOrder($clientOrder);
		return $this->getClientOrder();
	}

	public function getClientOrder ()
	{
		$Data = Array();
		$Data = Session::getActiveClientOrder();
		return $Data;
	}

	public function getOrderSummary ()
	{
		$order = $this->getClientOrder();
		$Summary = Array();
		if (isset($order['rulescart']) && $order['rulescart'] != NULL){
			$Summary[] = Array(
				'label' => $order['rulescart'],
				'value' => $order['rulescartmessage']
			);
		}
		return $Summary;
	}

	/**
	 * Chcek cart rules.
	 * If there is any rule, reduce global price. Otherwise return 0.
	 *
	 * @param
	 *        	array client order
	 * @return array discount (rulescartid, suffixtypeid, symbol, discount) or 0
	 * @access public
	 */
	public function getRulesCart ($clientOrder)
	{
		$clientGroupId = Session::getActiveClientGroupid();
		$Data = Array();
		$discount = Array();
		$check = true;
		$allRules = $this->getAllCartRules();
		if (is_array($allRules) && ! empty($allRules)){
			foreach ($allRules as $rule){
				$ruleid = $rule['idrulescart'];
				$ruleName = $rule['name'];
				$ruleDescription = $rule['description'];
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
								RCCG.clientgroupid
							FROM rulescartclientgroup RCCG
							LEFT JOIN rulescart RC ON RCCG.rulescartid = RC.idrulescart
							LEFT JOIN rulescartrule RCR ON RCR.rulescartid = RC.idrulescart
							LEFT JOIN rulescartview RCV ON RCV.rulescartid = RC.idrulescart
							LEFT JOIN suffixtype S ON RCCG.suffixtypeid = S.idsuffixtype
							WHERE RCR.rulescartid = :ruleid
						    AND RCV.viewid= :viewid
							AND RCCG.clientgroupid= :clientgroupid
							AND IF(RC.datefrom is not null, (cast(RC.datefrom as date) <= curdate()), 1)
							AND IF(RC.dateto is not null, (cast(RC.dateto as date)>= curdate()),1)";
					$stmt = Db::getInstance()->prepare($sql);
					$stmt->bindValue('clientgroupid', $clientGroupId);
					$stmt->bindValue('viewid', Helper::getViewId());
					$stmt->bindValue('ruleid', $ruleid);
				}
				else{
					$sql = "SELECT 
								RCR.rulescartid, 
								RCR.ruleid, 
								RCR.pkid, 
								RCR.pricefrom, 
								RCR.priceto,
								RC.suffixtypeid, 
								RC.freeshipping, 
								RC.discount, 
								S.symbol,
								'clientgroupid' = NULL as clientgroupid
							FROM  rulescart RC
							LEFT JOIN rulescartrule RCR ON RCR.rulescartid = RC.idrulescart
							LEFT JOIN rulescartview RCV ON RCV.rulescartid = RC.idrulescart
							LEFT JOIN suffixtype S ON RC.suffixtypeid = S.idsuffixtype
		      				WHERE RCR.rulescartid = :ruleid
		                 	AND RC.discountforall =1
		          			AND RCV.viewid= :viewid
		          			AND IF(RC.datefrom is not null, (cast(RC.datefrom as date) <= curdate()), 1)
							AND IF(RC.dateto is not null, (cast(RC.dateto as date)>= curdate()),1)";
					$stmt = Db::getInstance()->prepare($sql);
					$stmt->bindValue('viewid', Helper::getViewId());
					$stmt->bindValue('ruleid', $ruleid);
				}
				try{
					$stmt->execute();
					while ($rs = $stmt->fetch()){
						$ruleid = $rs['ruleid'];
						$rulescartid = $rs['rulescartid'];
						switch ($ruleid) {
							case 9: // delivery (dispatchmethod)
								if ((isset($Data[$rulescartid][$ruleid]) && $Data[$rulescartid][$ruleid] == 0) || ! isset($Data[$rulescartid][$ruleid])){
									if (isset($clientOrder['dispatchmethod']['dispatchmethodid']) && $clientOrder['dispatchmethod']['dispatchmethodid'] == $rs['pkid']){
										$Data[$rulescartid][$ruleid] = 1;
									}
									else{
										$Data[$rulescartid][$ruleid] = 0;
									}
								}
								break;
							case 10: // paymentmethod
								if ((isset($Data[$rulescartid][$ruleid]) && $Data[$rulescartid][$ruleid] == 0) || ! isset($Data[$rulescartid][$ruleid])){
									if (isset($clientOrder['payment']['idpaymentmethod']) && $clientOrder['payment']['idpaymentmethod'] == $rs['pkid']){
										$Data[$rulescartid][$ruleid] = 1;
									}
									else{
										$Data[$rulescartid][$ruleid] = 0;
									}
								}
								break;
							case 11: // final cart price
								if ((isset($Data[$rulescartid][$ruleid]) && $Data[$rulescartid][$ruleid] == 0) || ! isset($Data[$rulescartid][$ruleid])){
									if (isset($clientOrder['globalPrice']) && $clientOrder['globalPrice'] >= $rs['pricefrom']){
										$Data[$rulescartid][$ruleid] = 1;
									}
									else{
										$Data[$rulescartid][$ruleid] = 0;
									}
								}
								break;
							case 12: // final cart price
								if ((isset($Data[$rulescartid][$ruleid]) && $Data[$rulescartid][$ruleid] == 0) || ! isset($Data[$rulescartid][$ruleid])){
									if (isset($clientOrder['globalPrice']) && $clientOrder['globalPrice'] <= $rs['priceto']){
										$Data[$rulescartid][$ruleid] = 1;
									}
									else{
										$Data[$rulescartid][$ruleid] = 0;
									}
								}
								break;
							case 13: // final cart price with dispatch method
								if ((isset($Data[$rulescartid][$ruleid]) && $Data[$rulescartid][$ruleid] == 0) || ! isset($Data[$rulescartid][$ruleid])){
									if (isset($clientOrder['priceWithDispatchMethod']) && $clientOrder['priceWithDispatchMethod'] >= $rs['pricefrom']){
										$Data[$rulescartid][$ruleid] = 1;
									}
									else{
										$Data[$rulescartid][$ruleid] = 0;
									}
								}
								break;
							case 14: // final cart price with dispatch method
								if ((isset($Data[$rulescartid][$ruleid]) && $Data[$rulescartid][$ruleid] == 0) || ! isset($Data[$rulescartid][$ruleid])){
									if ($clientOrder['priceWithDispatchMethod'] <= $rs['priceto']){
										$Data[$rulescartid][$ruleid] = 1;
									}
									else{
										$Data[$rulescartid][$ruleid] = 0;
									}
								}
								break;
						}
						$discountValue = $rs['discount'];
						$discountSymbol = $rs['symbol'];
						if ($discountSymbol !== '%' && $discountValue > 0){
							$shopCurrency = Session::getActiveShopCurrencyId();
							$currentCurrency = Session::getActiveCurrencyId();
							if ($shopCurrency != $currentCurrency){
								$rate = Session::getActiveCurrencyRate();
								if (! empty($rate) && $rate > 0){
									$discountValue = $rate * $discountValue;
								}
							}
						}
						$discount[$rs['rulescartid']]['rulescartid'] = $rs['rulescartid'];
						$discount[$rs['rulescartid']]['name'] = $ruleName;
						$discount[$rs['rulescartid']]['description'] = $ruleDescription;
						$discount[$rs['rulescartid']]['suffixtypeid'] = $rs['suffixtypeid'];
						$discount[$rs['rulescartid']]['discount'] = $discountValue;
						$discount[$rs['rulescartid']]['symbol'] = $discountSymbol;
						$discount[$rs['rulescartid']]['freeshipping'] = $rs['freeshipping'];
					}
				}
				catch (Exception $e){
					throw new FrontendException(_('ERR_RULES_CART'));
				}
			} // end foreach
		} // end if is_array rulesAll
		
		if (count($Data) > 0){
			foreach ($Data as $rulescart => $rules){
				foreach ($rules as $rule){
					if ($rule == 0){
						unset($Data[$rulescart]);
					}
				}
			}
		}
		foreach ($Data as $rulescart => $rules){
			return $discount[$rulescart];
		}
	}

	public function getAllCartRules ()
	{
		$rules = Array();
		$sql = "SELECT 
					RC.idrulescart,
					RCT.name,
					RCT.description
				FROM rulescart RC
				LEFT JOIN rulescarttranslation RCT ON RCT.rulescartid = RC.idrulescart AND RCT.languageid = :languageid
				LEFT JOIN rulescartview RCV ON RCV.rulescartid = RC.idrulescart
				WHERE RCV.viewid= :viewid
				AND IF(RC.datefrom IS NOT NULL, (CAST(RC.datefrom AS DATE) <= curdate()), 1)
				AND IF(RC.dateto IS NOT NULL, (CAST(RC.dateto AS DATE) >= curdate()),1)";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				$rules[] = Array(
					'idrulescart' => $rs['idrulescart'],
					'name' => $rs['name'],
					'description' => $rs['description']
				);
			}
		}
		catch (Exception $e){
			throw new FrontendException(_('ERR_RULES_CART_SELECT'));
		}
		return $rules;
	}
}
