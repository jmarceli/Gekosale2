<?php

/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.com
 *
 * Copyright (c) 2009 Gekosale
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
 * $Id: order.php 627 2012-01-20 22:05:57Z gekosale $
 */
namespace Gekosale;

class OrderModel extends Component\Model\Datagrid
{

	protected function initDatagrid ($datagrid)
	{
		$datagrid->setTableData('order', Array(
			'idorder' => Array(
				'source' => 'O.idorder'
			),
			'client' => Array(
				'source' => 'CONCAT(\'<strong>\',CONVERT(LOWER(AES_DECRYPT(OC.surname, :encryptionkey)) USING utf8),\' \',CONVERT(LOWER(AES_DECRYPT(OC.firstname, :encryptionkey)) USING utf8),\'</strong><br />\',CONVERT(LOWER(AES_DECRYPT(OC.email, :encryptionkey)) USING utf8))',
				'prepareForAutosuggest' => true
			),
			'delivery' => Array(
				'source' => 'CONCAT(
								CONVERT(LOWER(AES_DECRYPT(OCD.surname, :encryptionkey)) USING utf8),
								\' \',
								CONVERT(LOWER(AES_DECRYPT(OCD.firstname, :encryptionkey)) USING utf8),
								\'<br />\',
								CONVERT(LOWER(AES_DECRYPT(OCD.street, :encryptionkey)) USING utf8),
								\' \',
								CONVERT(LOWER(AES_DECRYPT(OCD.streetno, :encryptionkey)) USING utf8),
								\' \',
								CONVERT(LOWER(AES_DECRYPT(OCD.placeno, :encryptionkey)) USING utf8),
								\'<br />\',
								CONVERT(LOWER(AES_DECRYPT(OCD.postcode, :encryptionkey)) USING utf8),
								\' \',
								CONVERT(LOWER(AES_DECRYPT(OCD.place, :encryptionkey)) USING utf8)
							)',
			),
			'price' => Array(
				'source' => 'O.price'
			),
			'currencysymbol' => Array(
				'source' => 'O.currencysymbol'
			),
			'globalprice' => Array(
				'source' => 'O.globalprice'
			),
			'dispatchmethodprice' => Array(
				'source' => 'O.dispatchmethodprice'
			),
			'orderstatusname' => Array(
				'source' => 'OST.name'
			),
			'products' => Array(
				'source' => 'GROUP_CONCAT(SUBSTRING(CONCAT(\' \', ROUND(OP.qty,2),\' x \', OP.name), 1) SEPARATOR \'<br/>\')',
				'filter' => 'having'
			),
			'orderstatusid' => Array(
				'source' => 'O.orderstatusid',
				'prepareForTree' => true,
				'first_level' => $this->getStatuses()
			),
			'dispatchmethodname' => Array(
				'source' => 'O.dispatchmethodname',
				'prepareForSelect' => true
			),
			'paymentmethodname' => Array(
				'source' => 'O.paymentmethodname',
				'prepareForSelect' => true
			),
			'adddate' => Array(
				'source' => 'O.adddate'
			),
			'clientid' => Array(
				'source' => 'O.clientid'
			),
			'colour' => Array(
				'source' => 'OSG.colour'
			),
			'view' => Array(
				'source' => 'V.name',
				'prepareForSelect' => true
			),
			'comments' => Array(
				'source' => 'O.customeropinion',
				'processFunction' => Array(
					$this,
					'parseComments'
				)
			)
		));

		$datagrid->setFrom('
			`order` O
			LEFT JOIN orderstatus OS ON OS.idorderstatus=O.orderstatusid
			LEFT JOIN orderstatusorderstatusgroups OSOSG ON O.orderstatusid = OSOSG.orderstatusid
			LEFT JOIN orderstatusgroups OSG ON OSG.idorderstatusgroups = OSOSG.orderstatusgroupsid
			LEFT JOIN orderstatustranslation OST ON OS.idorderstatus = OST.orderstatusid AND OST.languageid = :languageid
			LEFT JOIN orderclientdata OC ON OC.orderid=O.idorder
			LEFT JOIN orderclientdeliverydata OCD ON OCD.orderid = O.idorder
			LEFT JOIN orderproduct OP ON OP.orderid = O.idorder
			LEFT JOIN view V ON V.idview = O.viewid
		');

		$datagrid->setGroupBy('
			O.idorder
		');

		$datagrid->setAdditionalWhere('
			O.viewid IN (' . Helper::getViewIdsAsString() . ')
		');
  }

	public function parseComments ($string)
	{
		$string = str_replace(Array(
			"\n",
			"\r",
			"\r\n",
			"\n\r",
			"\t"
		), '', $string);
		return $string;
	}

	public function getClientForAjax ($request, $processFunction)
	{
		return $this->getDatagrid()->getFilterSuggestions('client', $request, $processFunction);
	}

	public function getFirstnameForAjax ($request, $processFunction)
	{
		return $this->getDatagrid()->getFilterSuggestions('firstname', $request, $processFunction);
	}

	public function getSurnameForAjax ($request, $processFunction)
	{
		return $this->getDatagrid()->getFilterSuggestions('surname', $request, $processFunction);
	}

	public function getStatusesAll ()
	{
		$sql = 'SELECT
					OST.orderstatusid,
					OST.name
				FROM `orderstatustranslation` OST
				LEFT JOIN orderstatus OS ON OST.orderstatusid = OS.idorderstatus
				WHERE OST.languageid = :id
				ORDER BY OST.name ASC';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', Session::getActiveLanguageId());
		$stmt->execute();
		$Data = Array();
		$i = 0;
		while ($rs = $stmt->fetch()){
			$i ++;
			$Data[$rs['orderstatusid']] = Array(
				'id' => $rs['orderstatusid'],
				'name' => $rs['name'],
				'hasChildren' => false,
				'parent' => null,
				'weight' => $i
			);
		}
		return $Data;
	}

	public function getStatuses ()
	{
		$statuses = $this->getStatusesAll();
		usort($statuses, Array(
			$this,
			'sortStatuses'
		));
		return $statuses;
	}

	protected function sortStatuses ($a, $b)
	{
		return $a['weight'] - $b['weight'];
	}

	public function calculateDeliveryCostEdit ($request)
	{
		$rulesCart = Array();
		$cost = 0.00;
		$rate = 0.00;
		if (isset($request['price_for_deliverers']) && isset($request['delivery_method'])){
			$sql = 'SELECT type FROM dispatchmethod WHERE iddispatchmethod = :dipatchmethodid';
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('dipatchmethodid', $request['delivery_method']);
			$stmt->execute();
			$rs = $stmt->fetch();
			if ($rs){
				$type = $rs['type'];
			}

			if ($type == 1){
				$sql = "SELECT
							IF(DP.vat IS NOT NULL, ROUND(DP.dispatchmethodcost+(DP.dispatchmethodcost*(V.`value`/100)),4), DP.dispatchmethodcost) as dispatchmethodcost,
							CASE
			  					WHEN (`from`<>0 AND `from`< :price AND `to`=0 AND DP.dispatchmethodcost =0) THEN DMT.name
			 				 	WHEN ( :price BETWEEN `from` AND `to`) THEN DMT.name
			  					WHEN (`to` = 0 AND `from`< :price AND DP.dispatchmethodcost <> 0) THEN DMT.name
			  					WHEN (`from`=0 AND `to`=0 AND DP.dispatchmethodcost =0) THEN DMT.name
							END as name
						FROM dispatchmethodprice DP
						LEFT JOIN dispatchmethod D ON D.iddispatchmethod = DP.dispatchmethodid
            LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
						LEFT JOIN vat V ON V.idvat = DP.vat
		       			WHERE DP.dispatchmethodid = :dipatchmethodid
						HAVING name IS NOT NULL";
				$stmt = Db::getInstance()->prepare($sql);
        $stmt->bindValue('languageid', Helper::getLanguageId());
				$stmt->bindValue('price', $request['price_for_deliverers']);
				$stmt->bindValue('dipatchmethodid', $request['delivery_method']);
				$stmt->execute();
				$rs = $stmt->fetch();
				if ($rs){
					$cost = $rs['dispatchmethodcost'];
				}
			}
			else{
				$sql = "SELECT
							IF(DW.vat IS NOT NULL, ROUND(DW.cost+(DW.cost*(V.`value`/100)),4), DW.cost) as cost,
							CASE
			  					WHEN (`from`<>0 AND `from`< :weight AND `to`=0 AND DW.cost =0) THEN DMT.name
			 				 	WHEN ( :weight BETWEEN `from` AND `to`) THEN DMT.name
			  					WHEN (`to` = 0 AND `from`< :weight AND DW.cost <> 0) THEN DMT.name
			  					WHEN (`from`=0 AND `to`=0 AND DW.cost =0) THEN DMT.name
							END as name
						FROM dispatchmethodweight DW
						LEFT JOIN dispatchmethod D ON D.iddispatchmethod = DW.dispatchmethodid
            LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
						LEFT JOIN vat V ON V.idvat = DW.vat
		       			WHERE DW.dispatchmethodid = :dipatchmethodid
						HAVING name IS NOT NULL";
				$stmt = Db::getInstance()->prepare($sql);
        $stmt->bindValue('languageid', Helper::getLanguageId());
				$stmt->bindValue('weight', $request['weight']);
				$stmt->bindValue('dipatchmethodid', $request['delivery_method']);
				$stmt->execute();
				$rs = $stmt->fetch();
				if ($rs){
					$cost = $rs['cost'];
				}
			}
		}
		$order = $this->getOrderById($request['idorder']);

		if (isset($request['rules_cart']) && $request['rules_cart'] > 0){
			$rulesCart = $this->calculateRulesCatalog($request['rules_cart'], $order['clientgroupid']);

      if ($request['rules_cart'] == $order['rulescartid']){
        if ($order['total'] > $order['pricebeforepromotion']){
          $rulesCart = Array(
            'discount' => abs($order['total'] - $order['pricebeforepromotion']),
            'suffixtypeid' => 2,
            'symbol' => '+'
          );
        }
        else{
          $rulesCart = Array(
            'discount' => abs($order['pricebeforepromotion'] - $order['total']),
            'suffixtypeid' => 3,
            'symbol' => '-'
          );
        }
      }
      if ($order['totalnetto'] == $request['net_total'] && $request['delivery_method'] == $order['delivery_method']['dispatchmethodid']) {
        $cost = $order['delivery_method']['delivererprice'];
      }
      if (!empty($rulesCart['freedelivery'])) {
        $cost = 0;
      }
		}
		return Array(
			'cost' => $cost,
			'rulesCart' => $rulesCart,
			'rate' => $rate,
		);
	}

	public function calculateDeliveryCostAdd ($request)
	{
		$rulesCart = Array();
		$cost = 0.00;
		$rate = 0.00;
		if (isset($request['price_for_deliverers']) && isset($request['delivery_method'])){
			$sql = 'SELECT type FROM dispatchmethod WHERE iddispatchmethod = :dipatchmethodid';
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('dipatchmethodid', $request['delivery_method']);
			$stmt->execute();
			$rs = $stmt->fetch();
			if ($rs){
				$type = $rs['type'];
			}

			if ($type == 1){
				$sql = "SELECT
							IF(DP.vat IS NOT NULL, ROUND(DP.dispatchmethodcost+(DP.dispatchmethodcost*(V.`value`/100)),4), DP.dispatchmethodcost) as dispatchmethodcost,
							CASE
			  					WHEN (`from`<>0 AND `from`< :price AND `to`=0 AND DP.dispatchmethodcost =0) THEN DMT.name
			 				 	WHEN ( :price BETWEEN `from` AND `to`) THEN DMT.name
			  					WHEN (`to` = 0 AND `from`< :price AND DP.dispatchmethodcost <> 0) THEN DMT.name
			  					WHEN (`from`=0 AND `to`=0 AND DP.dispatchmethodcost =0) THEN DMT.name
							END as name
						FROM dispatchmethodprice DP
						LEFT JOIN dispatchmethod D ON D.iddispatchmethod = DP.dispatchmethodid
            LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
						LEFT JOIN vat V ON V.idvat = DP.vat
		       			WHERE DP.dispatchmethodid = :dipatchmethodid
						HAVING name IS NOT NULL";
				$stmt = Db::getInstance()->prepare($sql);
				$stmt->bindValue('price', $request['price_for_deliverers']);
        $stmt->bindValue('languageid', Helper::getLanguageId());
				$stmt->bindValue('dipatchmethodid', $request['delivery_method']);
				$stmt->execute();
				$rs = $stmt->fetch();
				if ($rs){
					$cost = $rs['dispatchmethodcost'];
				}
			}
			else{
				$sql = "SELECT
							IF(DW.vat IS NOT NULL, ROUND(DW.cost+(DW.cost*(V.`value`/100)),4), DW.cost) as cost,
							CASE
			  					WHEN (`from`<>0 AND `from`< :weight AND `to`=0 AND DW.cost =0) THEN DMT.name
			 				 	WHEN ( :weight BETWEEN `from` AND `to`) THEN DMT.name
			  					WHEN (`to` = 0 AND `from`< :weight AND DW.cost <> 0) THEN DMT.name
			  					WHEN (`from`=0 AND `to`=0 AND DW.cost =0) THEN DMT.name
							END as name
						FROM dispatchmethodweight DW
						LEFT JOIN dispatchmethod D ON D.iddispatchmethod = DW.dispatchmethodid
            LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
						LEFT JOIN vat V ON V.idvat = DW.vat
		       			WHERE DW.dispatchmethodid = :dipatchmethodid
						HAVING name IS NOT NULL";
				$stmt = Db::getInstance()->prepare($sql);
        $stmt->bindValue('languageid', Helper::getLanguageId());
				$stmt->bindValue('weight', $request['weight']);
				$stmt->bindValue('dipatchmethodid', $request['delivery_method']);
				$stmt->execute();
				$rs = $stmt->fetch();
				if ($rs){
					$cost = $rs['cost'];
				}
			}
		}

		return Array(
			'cost' => $cost,
			'rulesCart' => $rulesCart
		);
	}

	public function calculateRulesCatalog ($rulesCartId, $clientGroupId = null)
	{
		$rulesCart = Array();
		if (isset($rulesCartId) && !empty($rulesCartId)){
      if (!empty($clientGroupId)) {
				$sql = "SELECT 
							RCCG.discount, 
							RCCG.suffixtypeid, 
              RCCG.freeshipping,
							S.symbol
						FROM rulescartclientgroup RCCG
							LEFT JOIN rulescart RC ON RCCG.rulescartid = RC.idrulescart
							LEFT JOIN suffixtype S ON RCCG.suffixtypeid = S.idsuffixtype
						WHERE
							RCCG.clientgroupid= :clientgroupid
							AND IF(RC.datefrom is not null, (cast(RC.datefrom as date) <= curdate()), 1)
							AND IF(RC.dateto is not null, (cast(RC.dateto as date)>= curdate()),1)
              AND RCCG.rulescartid = :rulescartid";
				$stmt = Db::getInstance()->prepare($sql);
				$stmt->bindValue('clientgroupid', $clientGroupId);
        $stmt->bindValue('rulescartid', $rulesCartId);
			}
			else {
        $sql = "SELECT
              RC.discount,
              RC.suffixtypeid,
              RC.freeshipping,
              ST.symbol
            FROM rulescart RC
            LEFT JOIN suffixtype ST ON ST.idsuffixtype = RC.suffixtypeid
            WHERE RC.idrulescart = :rulescartid";
        $stmt = Db::getInstance()->prepare($sql);
        $stmt->bindValue('rulescartid', $rulesCartId);
      }
      $stmt->execute();
      $rs = $stmt->fetch();
      if ($rs){
        $rulesCart = Array(
          'freedelivery' => $rs['freeshipping'],
          'discount' => $rs['discount'],
          'suffixtypeid' => $rs['suffixtypeid'],
          'symbol' => $rs['symbol']
        );
      }
		}
		return $rulesCart;
	}

	public function getDatagridFilterData ()
	{
		return $this->getDatagrid()->getFilterData();
	}

	public function getOrderForAjax ($request, $processFunction)
	{
		return $this->getDatagrid()->getData($request, $processFunction);
	}

	public function doAJAXDeleteOrder ($id, $datagrid)
	{
		return $this->getDatagrid()->deleteRow($datagrid, $id, Array(
			$this,
			'deleteOrder'
		), $this->getName());
	}

	public function deleteOrder ($id)
	{
		DbTracker::deleteRows('order', 'idorder', $id);
	}

	public function getProducts ($id)
	{
		$sql = "SELECT
					OP.idorderproduct,
					OP.productid as id,
					OP.productattributesetid AS variant,
					OP.name,
					OP.pricenetto as net_price,
					ROUND(OP.pricenetto * (OP.vat / 100), 2) as gross_price,
					OP.qty as quantity,
					(OP.pricenetto*OP.qty) as net_subtotal,
					OP.vat,
					ROUND(((OP.pricenetto*OP.qty)*OP.vat/100 )+(OP.pricenetto*OP.qty), 2) as subtotal,
					OP.photoid,
					OP.ean,
                    P.weight,
                    P.height,
                    P.width,
                    P.deepth as deep
				FROM orderproduct OP
                LEFT JOIN product P on OP.productid = P.idproduct
				WHERE OP.orderid=:id";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'id' => $rs['id'],
				'name' => $rs['name'],
				'ean' => $rs['ean'],
				'net_price' => $rs['net_price'],
				'gross_price' => $rs['gross_price'],
				'quantity' => $rs['quantity'],
				'net_subtotal' => $rs['net_subtotal'],
				'vat' => $rs['vat'],
				'vat_value' => $rs['subtotal'] - $rs['net_subtotal'],
				'subtotal' => $rs['subtotal'],
				'width' => $rs['width'],
				'height' => $rs['height'],
				'deep' => $rs['deep'],
				'weight' => $rs['weight'],
				'photo' => ((int) $rs['photoid'] > 0) ? App::getModel('product')->getThumbPathForId($rs['photoid']) : '',
				'attributes' => $this->getOrderProductAttributes($rs['id'], $rs['variant'])
			);
		}
		return $Data;
	}

	public function getOrderById ($id)
	{
		$sql = 'SELECT
					O.clientid,
					CD.clientgroupid,
					O.customeropinion,
					O.adddate as order_date,
					O.idorder as order_id,
					OS.idorderstatus as current_status_id,
					OST.name as current_status,
					O.dispatchmethodprice as delivererprice,
					O.dispatchmethodname as deliverername,
					O.dispatchmethodid,
					O.paymentmethodid,
					O.paymentmethodname as paymentname,
					PM.controller AS paymentmethodcontroller,
					O.price as vat_value,
					O.globalpricenetto as totalnetto,
					O.globalprice as total,
					O.orderstatusid,
					V.name as view,
					O.viewid,
					O.currencyid,
					O.currencysymbol,
					O.currencyrate,
					O.rulescartid,
					O.pricebeforepromotion ,
					(SELECT idorder FROM `order` WHERE idorder < :id ORDER BY idorder DESC LIMIT 1) AS previous,
					(SELECT idorder FROM `order` WHERE idorder > :id LIMIT 1) AS next
				FROM `order` O
				LEFT JOIN clientdata CD ON CD.clientid = O.clientid
				LEFT JOIN view V ON O.viewid = V.idview
				LEFT JOIN orderstatus OS ON OS.idorderstatus=O.orderstatusid
				LEFT JOIN orderstatustranslation OST ON OS.idorderstatus = OST.orderstatusid AND OST.languageid = :languageid
				LEFT JOIN paymentmethod PM ON PM.idpaymentmethod = O.paymentmethodid
				WHERE O.idorder=:id AND O.viewid IN (' . implode(',', Helper::getViewIds()) . ')';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->bindValue('encryptionKey', Session::getActiveEncryptionKeyValue());
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->execute();
		$rs = $stmt->fetch();
		$Data = Array();
		if ($rs){
			$Data = Array(
				'clientid' => $rs['clientid'],
				'clientgroupid' => $rs['clientgroupid'],
				'customeropinion' => $rs['customeropinion'],
				'order_id' => $rs['order_id'],
				'previous' => $rs['previous'],
				'next' => $rs['next'],
				'viewid' => $rs['viewid'],
				'view' => $rs['view'],
				'orderstatusid' => $rs['orderstatusid'],
				'order_date' => $rs['order_date'],
				'current_status' => $rs['current_status'],
				'current_status_id' => $rs['current_status_id'],
				'clients_ip_address' => Core::getRealIpAddress(),
				'vat_value' => $rs['vat_value'],
				'totalnetto' => $rs['totalnetto'],
				'total' => $rs['total'],
				'currencyid' => $rs['currencyid'],
				'currencysymbol' => $rs['currencysymbol'],
				'currencyrate' => $rs['currencyrate'],
				'pricebeforepromotion' => $rs['pricebeforepromotion'],
				'rulescartid' => $rs['rulescartid'],
				'billing_address' => $this->getBillingAddress($id),
				'delivery_address' => $this->getDeliveryAddress($id),
				'products' => $this->getProducts($id),
				'order_history' => $this->getOrderHistory($id),
				'invoices' => $this->getOrderInvoices($id),
			);
			$dispatchmethodVat = App::getModel('dispatchmethod')->getDispatchmethodForOrder($rs['dispatchmethodid']);

			$delivererpricenetto = $rs['delivererprice'] / (1 + ($dispatchmethodVat / 100));

			$Data['delivery_method'] = Array(
				'delivererprice' => $rs['delivererprice'],
				'deliverername' => $rs['deliverername'],
				'dispatchmethodid' => $rs['dispatchmethodid'],
				'delivererpricenetto' => $delivererpricenetto,
				'deliverervat' => sprintf('%01.2f', $dispatchmethodVat),
				'deliverervatvalue' => $rs['delivererprice'] - $delivererpricenetto
			);
			$Data['payment_method'] = Array(
				'paymentname' => $rs['paymentname'],
				'paymentmethodcontroller' => $rs['paymentmethodcontroller'],
				'paymentmethodid' => $rs['paymentmethodid']
			);
		}
		else{
			App::redirect(__ADMINPANE__ . '/order');
		}
		return $Data;
	}

	public function getOrderInvoices ($id)
	{
		$sql = "SELECT
					idinvoice,
					symbol,
					invoicedate,
					comment,
					salesperson,
					paymentduedate,
					totalpayed
				FROM invoice
				WHERE orderid=:id";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getDeliveryAddress ($id)
	{
		$sql = "SELECT
					AES_DECRYPT(OCDD.firstname, :encryptionKey) firstname,
					AES_DECRYPT(OCDD.surname, :encryptionKey) surname,
					AES_DECRYPT(OCDD.place, :encryptionKey) city,
					AES_DECRYPT(OCDD.postcode, :encryptionKey) postcode,
					AES_DECRYPT(OCDD.phone, :encryptionKey) phone,
					AES_DECRYPT(OCDD.phone2, :encryptionKey) phone2,
					AES_DECRYPT(OCDD.street, :encryptionKey) street,
					AES_DECRYPT(OCDD.streetno, :encryptionKey) streetno,
					AES_DECRYPT(OCDD.placeno, :encryptionKey) placeno,
					AES_DECRYPT(OCDD.email, :encryptionKey) email,
					AES_DECRYPT(OCDD.nip, :encryptionKey) nip,
					AES_DECRYPT(OCDD.companyname, :encryptionKey) companyname,
					OCDD.countryid
				FROM orderclientdeliverydata OCDD
				WHERE orderid=:id";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->bindValue('encryptionKey', Session::getActiveEncryptionKeyValue());
		$stmt->execute();
		$rs = $stmt->fetch();
		$Data = Array();
		if ($rs){
			$Data = Array(
				'firstname' => $rs['firstname'],
				'surname' => $rs['surname'],
				'city' => $rs['city'],
				'postcode' => $rs['postcode'],
				'phone' => $rs['phone'],
				'phone2' => $rs['phone2'],
				'street' => $rs['street'],
				'streetno' => $rs['streetno'],
				'placeno' => $rs['placeno'],
				'countryid' => $rs['countryid'],
				'companyname' => $rs['companyname'],
				'email' => $rs['email'],
				'nip' => $rs['nip']
			);
		}
		else{
			throw new CoreException(_('ERR_DELIVERY_ADDRESS_NO_EXIST'));
		}
		return $Data;
	}

	public function getBillingAddress ($id)
	{
		$sql = "SELECT
					AES_DECRYPT(OCD.firstname, :encryptionKey) AS firstname,
					AES_DECRYPT(OCD.surname, :encryptionKey) AS surname,
					AES_DECRYPT(OCD.place, :encryptionKey) AS city,
					AES_DECRYPT(OCD.postcode, :encryptionKey) AS postcode,
					AES_DECRYPT(OCD.phone, :encryptionKey) AS phone,
					AES_DECRYPT(OCD.phone2, :encryptionKey) AS phone2,
					AES_DECRYPT(OCD.street, :encryptionKey) AS street,
					AES_DECRYPT(OCD.streetno, :encryptionKey) AS streetno,
					AES_DECRYPT(OCD.placeno, :encryptionKey) AS placeno,
					AES_DECRYPT(OCD.email, :encryptionKey) AS email,
					AES_DECRYPT(OCD.nip, :encryptionKey) AS nip,
					AES_DECRYPT(OCD.companyname, :encryptionKey) AS companyname,
					OCD.countryid
				FROM orderclientdata OCD
				WHERE orderid=:id";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->bindValue('encryptionKey', Session::getActiveEncryptionKeyValue());
		$stmt->execute();
		$rs = $stmt->fetch();
		$Data = Array();
		if ($rs){
			$Data = Array(
				'firstname' => $rs['firstname'],
				'surname' => $rs['surname'],
				'city' => $rs['city'],
				'postcode' => $rs['postcode'],
				'phone' => $rs['phone'],
				'phone2' => $rs['phone2'],
				'street' => $rs['street'],
				'streetno' => $rs['streetno'],
				'placeno' => $rs['placeno'],
				'countryid' => $rs['countryid'],
				'companyname' => $rs['companyname'],
				'email' => $rs['email'],
				'nip' => $rs['nip']
			);
		}
		else{
			throw new CoreException(_('ERR_BILLING_ADDRESS_NO_EXIST'));
		}
		return $Data;
	}

	public function checkProductWithAttributes ($id)
	{
		$sql = "SELECT
					COUNT(orderid) AS total
				FROM orderproduct
				WHERE productid = :id AND productattributesetid IS NOT NULL";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->execute();
		$rs = $stmt->fetch();
		if ($rs){
			if ($rs['total'] > 0)
				return 0;
		}
		else{
			return 1;
		}
		return 1;
	}

	public function getDispatchMethodForPriceForAjaxEdit ($request)
	{
		$order = $this->getOrderById($request['idorder']);
		$methodsRaw = $this->getDispatchmethodForPrice($request['gross_total'], $request['idorder'], $order['currencyid'], $request['weight_total']);
		$methods = Array();

		foreach ($methodsRaw as $method){
			$methods[] = Array(
				'sValue' => $method['id'],
				'sLabel' => $method['namewithprice']
			);
		}
		foreach ($methods as $key => $m){
			if ($order['totalnetto'] == $request['net_total'] && $m['sValue'] == $order['delivery_method']['dispatchmethodid']){
				$name = $order['delivery_method']['deliverername'] . ' (' . $order['delivery_method']['delivererprice'] . ')';
				$methods[$key] = Array(
					'sValue' => $m['sValue'],
					'sLabel' => $name
				);
			}
		}
		return Array(
			'options' => $methods
		);
	}

	public function getDispatchMethodForPriceForAjaxAdd ($request)
	{
		$methodsRaw = $this->getDispatchmethodForPriceAdd($request['gross_total'], $request['weight_total']);
		$methods = Array();

		foreach ($methodsRaw as $method){
			$methods[] = Array(
				'sValue' => $method['id'],
				'sLabel' => $method['namewithprice']
			);
		}
		return Array(
			'options' => $methods
		);
	}

	public function getDispatchmethodForPrice ($price = 0, $idorder = 0, $currencyid = 0, $globalweight = 0)
	{
		$Data = Array();
		$sql = "SELECT
					DP.dispatchmethodid as id,
					DP.`from`,
					DP.`to`,
					IF(DP.vat IS NOT NULL, ROUND((DP.dispatchmethodcost+(DP.dispatchmethodcost*(V.`value`/100))) * CR.exchangerate,4), ROUND(DP.dispatchmethodcost * CR.exchangerate,4)) as dispatchmethodcost,
					CASE
  						WHEN (`from`<>0 AND `from`< :price AND `to`=0 AND DP.dispatchmethodcost =0) THEN DMT.name
 					 	WHEN ( :price BETWEEN `from` AND `to`) THEN DMT.name
  						WHEN (`to` = 0 AND `from`< :price AND DP.dispatchmethodcost <> 0) THEN DMT.name
  						WHEN (`from`=0 AND `to`=0 AND DP.dispatchmethodcost =0) THEN DMT.name
					END as name
				FROM dispatchmethodprice DP
				LEFT JOIN dispatchmethod D ON D.iddispatchmethod = DP.dispatchmethodid
        LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
				LEFT JOIN vat V ON V.idvat = DP.vat
				LEFT JOIN dispatchmethodview DV ON DV.dispatchmethodid = D.iddispatchmethod
        LEFT JOIN currencyrates CR ON CR.currencyfrom = :currencyfrom AND CR.currencyto = (SELECT O.currencyid FROM `order` O WHERE O.idorder= :idorder)
				WHERE
					(DV.viewid = (SELECT O.viewid FROM `order` O WHERE O.idorder= :idorder) OR DP.dispatchmethodid = (SELECT O.dispatchmethodid FROM `order` O WHERE O.idorder= :idorder)) AND
					D.type = 1 AND
					IF(D.maximumweight IS NOT NULL, D.maximumweight >= :globalweight, 1)";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('price', $price);
		$stmt->bindValue('currencyfrom', 28); // dispatchmethod price is always in PLN
		$stmt->bindValue('idorder', $idorder);
		$stmt->bindValue('globalweight', $globalweight);
		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				$cost = $rs['dispatchmethodcost'];
				$name = $rs['name'];
				if (! empty($name)){
					$Data[] = Array(
						'id' => $rs['id'],
						'from' => $rs['from'],
						'to' => $rs['to'],
						'dispatchmethodcost' => $cost,
						'name' => $name,
						'namewithprice' => ($cost >= .01) ? sprintf('%s (%.2f)', $rs['name'], $cost) : $rs['name']
					);
				}
			}
		}
		catch (Exception $e){
			throw new Exception($e->getMessage());
		}

		$sql = "SELECT
					DW.dispatchmethodid as id,
					DW.`from`,
					DW.`to`,
					IF(DW.vat IS NOT NULL, ROUND(DW.cost+(DW.cost*(V.`value`/100)),4), DW.cost) as cost,
					D.freedelivery,
					CASE
  						WHEN (`from`<>0 AND `from`< :globalweight AND `to`=0 AND DW.cost =0) THEN DMT.name
 					 	WHEN ( :globalweight BETWEEN `from` AND `to`) THEN DMT.name
  						WHEN (`to` = 0 AND `from`< :globalweight AND DW.cost <> 0) THEN DMT.name
  						WHEN (`from`=0 AND `to`=0 AND DW.cost =0) THEN DMT.name
					END as name
				FROM dispatchmethodweight DW
				LEFT JOIN vat V ON V.idvat = DW.vat
				LEFT JOIN dispatchmethod D ON D.iddispatchmethod = DW.dispatchmethodid
        LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
				LEFT JOIN dispatchmethodview DV ON DV.dispatchmethodid = D.iddispatchmethod
				WHERE
					(DV.viewid = (SELECT O.viewid FROM `order` O WHERE O.idorder= :idorder) OR DW.dispatchmethodid = (SELECT O.dispatchmethodid FROM `order` O WHERE O.idorder= :idorder)) AND
					D.type = 2";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('price', $price);
		$stmt->bindValue('idorder', $idorder);
		$stmt->bindValue('globalweight', $globalweight);
		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				$cost = $rs['cost'];
				if (($rs['freedelivery'] > 0) && ($rs['freedelivery'] <= $price)){
					$cost = 0.00;
				}
				else{
					$cost = $rs['cost'];
				}
				$name = $rs['name'];
				if (! empty($name)){
					$Data[] = Array(
						'id' => $rs['id'],
						'from' => $rs['from'],
						'to' => $rs['to'],
						'dispatchmethodcost' => $cost,
						'name' => $name,
						'namewithprice' => ($cost >= .01) ? sprintf('%s (%.2f)', $rs['name'], $cost) : $rs['name']
					);
				}
			}
		}
		catch (Exception $e){
			throw new Exception($e->getMessage());
		}
		return $Data;
	}

	public function getDispatchmethodForPriceAdd ($price = 0, $globalweight = 0)
	{
		$Data = Array();
		$sql = "SELECT
					DP.dispatchmethodid as id,
					DP.`from`,
					DP.`to`,
					IF(DP.vat IS NOT NULL, ROUND(DP.dispatchmethodcost+(DP.dispatchmethodcost*(V.`value`/100)),4), DP.dispatchmethodcost) as dispatchmethodcost,
					CASE
  						WHEN (`from`<>0 AND `from`< :price AND `to`=0 AND DP.dispatchmethodcost =0) THEN DMT.name
 					 	WHEN ( :price BETWEEN `from` AND `to`) THEN DMT.name
  						WHEN (`to` = 0 AND `from`< :price AND DP.dispatchmethodcost <> 0) THEN DMT.name
  						WHEN (`from`=0 AND `to`=0 AND DP.dispatchmethodcost =0) THEN DMT.name
					END as name
				FROM dispatchmethodprice DP
				LEFT JOIN dispatchmethod D ON D.iddispatchmethod = DP.dispatchmethodid
        LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
				LEFT JOIN vat V ON V.idvat = DP.vat
				LEFT JOIN dispatchmethodview DV ON DV.dispatchmethodid = D.iddispatchmethod
				WHERE
					DV.viewid = :viewid AND
					D.type = 1 AND
					IF(D.maximumweight IS NOT NULL, D.maximumweight >= :globalweight, 1)";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('price', $price);
		$stmt->bindValue('globalweight', $globalweight);
		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				$cost = $rs['dispatchmethodcost'];
				$name = $rs['name'];
				if (! empty($name)){
					$Data[] = Array(
						'id' => $rs['id'],
						'from' => $rs['from'],
						'to' => $rs['to'],
						'dispatchmethodcost' => $cost,
						'name' => $name,
						'namewithprice' => ($cost >= .01) ? sprintf('%s (%.2f)', $rs['name'], $cost) : $rs['name']
					);
				}
			}
		}
		catch (Exception $e){
			throw new Exception($e->getMessage());
		}

		$sql = "SELECT
					DW.dispatchmethodid as id,
					DW.`from`,
					DW.`to`,
					IF(DW.vat IS NOT NULL, ROUND(DW.cost+(DW.cost*(V.`value`/100)),4), DW.cost) as cost,
					D.freedelivery,
					CASE
  						WHEN (`from`<>0 AND `from`< :globalweight AND `to`=0 AND DW.cost =0) THEN DMT.name
 					 	WHEN ( :globalweight BETWEEN `from` AND `to`) THEN DMT.name
  						WHEN (`to` = 0 AND `from`< :globalweight AND DW.cost <> 0) THEN DMT.name
  						WHEN (`from`=0 AND `to`=0 AND DW.cost =0) THEN DMT.name
					END as name
				FROM dispatchmethodweight DW
				LEFT JOIN vat V ON V.idvat = DW.vat
				LEFT JOIN dispatchmethod D ON D.iddispatchmethod = DW.dispatchmethodid
        LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
				LEFT JOIN dispatchmethodview DV ON DV.dispatchmethodid = D.iddispatchmethod
				WHERE
					DV.viewid = :viewid AND
					D.type = 2";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('price', $price);
		$stmt->bindValue('globalweight', $globalweight);
		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				$cost = $rs['cost'];
				if (($rs['freedelivery'] > 0) && ($rs['freedelivery'] <= $price)){
					$cost = 0.00;
				}
				else{
					$cost = $rs['cost'];
				}
				$name = $rs['name'];
				if (! empty($name)){
					$Data[] = Array(
						'id' => $rs['id'],
						'from' => $rs['from'],
						'to' => $rs['to'],
						'dispatchmethodcost' => $cost,
						'name' => $name,
						'namewithprice' => ($cost >= .01) ? sprintf('%s (%.2f)', $rs['name'], $cost) : $rs['name']
					);
				}
			}
		}
		catch (Exception $e){
			throw new Exception($e->getMessage());
		}
		return $Data;
	}

	public function getDispatchmethodAllToSelect ($price = 0, $idorder = 0, $weight = 0)
	{
		$Data = $this->getDispatchmethodForPrice($price, $idorder, 0, $weight);
		$tmp = Array();
		foreach ($Data as $key){
			if (! empty($key['name']) && $key['name'] !== NULL){
				$tmp[$key['id']] = $key['name'];
			}
		}
		return $tmp;
	}

	public function getPaymentmethodAll ($idorder = 0)
	{
		$Data = Array();

		$sql = "SELECT
					PM.idpaymentmethod AS id,
					PMT.name
				FROM paymentmethod PM
          LEFT JOIN paymentmethodtranslation PMT ON PMT.paymentmethodid = PM.idpaymentmethod AND PMT.languageid = :languageid
					LEFT JOIN paymentmethodview PMV ON PMV.paymentmethodid =PM.idpaymentmethod
					WHERE
						IF (:idorder>0, PMV.viewid= (SELECT O.viewid FROM `order` O WHERE O.idorder= :idorder),
							IF(:viewid>0, PMV.viewid= :viewid, 0))";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('idorder', $idorder);
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'id' => $rs['id'],
				'name' => $rs['name']
			);
		}
		return $Data;
	}

	public function getPaymentmethodAllToSelect ($idorder = 0)
	{
		$Data = $this->getPaymentmethodAll($idorder);
		$tmp = Array();
		foreach ($Data as $key){
			$tmp[$key['id']] = $key['name'];
		}
		return $tmp;
	}

	public function getAllRules ($orderid)
	{
		$Data = Array();
		$Data[0] = _('TXT_CHOOSE_SELECT');
		$sql = "SELECT
					R.idrulescart, RT.name
				FROM rulescart R
				LEFT JOIN rulescarttranslation RT ON RT.rulescartid = R.idrulescart AND RT.languageid = :languageid
				LEFT JOIN rulescartview RV ON RV.rulescartid = R.idrulescart
				WHERE IF(:viewid >0, RV.viewid = :viewid, 0)";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$Data[$rs['idrulescart']] = $rs['name'];
		}
		return $Data;
	}

	public function getAllRulesForOrder ($orderid)
	{
		$Data = Array();
		$Data[0] = _('TXT_CHOOSE_SELECT');
		$sql = "SELECT R.idrulescart, RT.name
					FROM rulescart R
					LEFT JOIN rulescarttranslation RT ON RT.rulescartid = R.idrulescart AND RT.languageid = :languageid
					LEFT JOIN rulescartview RV ON RV.rulescartid = R.idrulescart
					WHERE RV.viewid = (SELECT O.viewid FROM `order` O WHERE O.idorder = :orderid)";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('orderid', $orderid);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$Data[$rs['idrulescart']] = $rs['name'];
		}
		return $Data;
	}

	public function getProductForOrder ($id)
	{
		$sql = "SELECT O.idorder as id, OP.name as productname, OP.price, OP.qty, OP.idorderproduct
					FROM `order` O
					LEFT JOIN orderproduct OP ON OP.orderid = O.idorder
					WHERE idorder=:id";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'productname' => $rs['productname'],
				'price' => $rs['price'],
				'qty' => $rs['qty'],
				'attributes' => $this->getProductAttributes($rs['idorderproduct'])
			);
		}
		return $Data;
	}

	public function getProductAttributes ($attrId)
	{
		$sql = 'SELECT
					OP.idorderproduct as attrId,
					OPA.name as attributename
				FROM orderproduct OP
				LEFT JOIN orderproductattribute OPA ON OPA.orderproductid=OP.idorderproduct
				WHERE orderproductid = :attrId';
		$Data = Array();
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('attrId', $attrId);
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'attributename' => $rs['attributename']
			);
		}
		return $Data;
	}

	public function addOrderHistory ($Data, $orderid)
	{
		$sql = 'INSERT INTO orderhistory(content, orderstatusid, orderid, inform, user)
					VALUES (:content, :orderstatusid, :orderid, :inform, :user)';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('content', $Data['comment']);
		$stmt->bindValue('user', App::getModel('users')->getUserFullName());
		$stmt->bindValue('orderstatusid', $Data['status']);
		$stmt->bindValue('orderid', $orderid);
		if (($Data['inform']) == 1){
			$stmt->bindValue('inform', $Data['inform']);
		}
		else{
			$stmt->bindValue('inform', 0);
		}
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new Exception(_('ERR_ORDER_HISTORY_ADD'));
		}
	}

	public function getOrderHistory ($id)
	{
		$sql = "SELECT
					OH.content,
					OST.name as orderstatusname,
					OH.inform,
					OH.adddate as date,
					OH.user
				FROM orderhistory OH
				LEFT JOIN orderstatus OS ON OS.idorderstatus = OH.orderstatusid
				LEFT JOIN orderstatustranslation OST ON OS.idorderstatus = OST.orderstatusid AND OST.languageid = :languageid
				WHERE OH.orderid=:id";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'content' => $rs['content'],
				'date' => $rs['date'],
				'inform' => $rs['inform'],
				'orderstatusname' => $rs['orderstatusname'],
				'user' => $rs['user']
			);
		}
		return $Data;
	}

	/**
	 * Notify user about progress
	 *
	 * @param array $orderData
	 *        	order data - from $this->getOrderById()
	 */
	public function notifyUser (array $orderData, $status = -1, $template = 'orderhistory')
	{
		if ($status < 0)
			$status = $orderData['current_status_id']; // get last message in
				                                           // current order status

		$orderhistory = $this->getLastOrderHistory((int) $orderData['order_id'], $status);
		App::getRegistry()->template->assign('orderhistory', $orderhistory);

		App::getModel('mailer')->sendEmail(Array(
			'template' => $template,
			'email' => Array(
				$orderhistory['email']
			),
			'bcc' => false,
			'subject' => _('TXT_CHANGE_ORDER_STATUS_NR') . $orderhistory['ids'],
			'viewid' => $orderData['viewid']
		));

		return;
	}

	public function getLastOrderHistory ($id, $status)
	{
		$sql = "SELECT
					AES_DECRYPT(OCD.firstname, :encryptionKey) firstname,
					AES_DECRYPT(OCD.email, :encryptionKey) email,
					AES_DECRYPT(OCD.surname, :encryptionKey) surname,
					OH.orderid as ids, OH.content, OST.name as orderstatusname
				FROM orderhistory OH
				LEFT JOIN orderstatus OS ON OS.idorderstatus = OH.orderstatusid
				LEFT JOIN orderstatustranslation OST ON OS.idorderstatus = OST.orderstatusid AND OST.languageid = :languageid
				LEFT JOIN orderclientdata OCD ON OCD.orderid=OH.orderid
				WHERE OH.orderid=:id and OH.orderstatusid=:status ORDER BY OH.adddate DESC LIMIT 1";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->bindValue('status', $status);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('encryptionKey', Session::getActiveEncryptionKeyValue());
		$stmt->execute();
		$rs = $stmt->fetch();
		$Data = Array();
		if ($rs){
			$Data = Array(
				'ids' => $rs['ids'],
				'email' => $rs['email'],
				'firstname' => $rs['firstname'],
				'surname' => $rs['surname'],
				'content' => $rs['content'],
				'orderstatusname' => $rs['orderstatusname']
			);
		}
		return $Data;
	}

	public function updateOrderById ($Data, $id)
	{
		Db::getInstance()->beginTransaction();
		try{
			$this->updateOrderDeliveryAddress($Data['address_data']['shipping_data'], $id);
			$this->updateOrderBillingAddress($Data['address_data']['billing_data'], $id);
			if (isset($Data['products_data'])){
				$this->updateOrderProduct($Data['products_data'], $id);
			}
			$this->updateOrder($Data, $id);
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_ORDER_EDIT'), 125, $e->getMessage());
		}

		Db::getInstance()->commit();
		return true;
	}

	public function updateOrderDeliveryAddress ($Data, $id)
	{
		$sql = 'UPDATE orderclientdeliverydata SET
					firstname = AES_ENCRYPT(:firstname, :encryptionKey),
					surname = AES_ENCRYPT(:surname, :encryptionKey),
					street = AES_ENCRYPT(:street, :encryptionKey),
					streetno = AES_ENCRYPT(:streetno, :encryptionKey),
					placeno = AES_ENCRYPT(:placeno, :encryptionKey),
					postcode = AES_ENCRYPT(:postcode, :encryptionKey),
					place = AES_ENCRYPT(:place, :encryptionKey),
					phone = AES_ENCRYPT(:phone, :encryptionKey),
					phone2 = AES_ENCRYPT(:phone2, :encryptionKey),
					email = AES_ENCRYPT(:email, :encryptionKey),
					nip = AES_ENCRYPT(:nip, :encryptionKey),
					companyname = AES_ENCRYPT(:companyname, :encryptionKey),
					countryid = :countryid
				WHERE orderid = :id';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('firstname', $Data['firstname']);
		$stmt->bindValue('surname', $Data['surname']);
		$stmt->bindValue('street', $Data['street']);
		$stmt->bindValue('streetno', $Data['streetno']);
		$stmt->bindValue('placeno', $Data['placeno']);
		$stmt->bindValue('postcode', $Data['postcode']);
		$stmt->bindValue('place', $Data['place']);
		$stmt->bindValue('phone', $Data['phone']);
		$stmt->bindValue('phone2', $Data['phone2']);
		$stmt->bindValue('email', $Data['email']);
		$stmt->bindValue('companyname', $Data['companyname']);
		$stmt->bindValue('nip', $Data['nip']);
		$stmt->bindValue('encryptionKey', Session::getActiveEncryptionKeyValue());
		$stmt->bindValue('id', $id);
		$stmt->bindValue('countryid', $Data['countryid']);
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_ORDER_DELIVERY_ADDRESS_EDIT'), 13, $e->getMessage());
		}
		return true;
	}

	public function updateOrderBillingAddress ($Data, $id)
	{
		$sql = 'UPDATE orderclientdata SET
					firstname = AES_ENCRYPT(:firstname, :encryptionKey),
					surname = AES_ENCRYPT(:surname, :encryptionKey),
					street = AES_ENCRYPT(:street, :encryptionKey),
					streetno = AES_ENCRYPT(:streetno, :encryptionKey),
					placeno = AES_ENCRYPT(:placeno, :encryptionKey),
					postcode = AES_ENCRYPT(:postcode, :encryptionKey),
					place = AES_ENCRYPT(:place, :encryptionKey),
					phone = AES_ENCRYPT(:phone, :encryptionKey),
					phone2 = AES_ENCRYPT(:phone2, :encryptionKey),
					email = AES_ENCRYPT(:email, :encryptionKey),
					nip = AES_ENCRYPT(:nip, :encryptionKey),
					companyname = AES_ENCRYPT(:companyname, :encryptionKey),
					countryid = :countryid
				WHERE orderid = :id';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('firstname', $Data['firstname']);
		$stmt->bindValue('surname', $Data['surname']);
		$stmt->bindValue('street', $Data['street']);
		$stmt->bindValue('streetno', $Data['streetno']);
		$stmt->bindValue('placeno', $Data['placeno']);
		$stmt->bindValue('postcode', $Data['postcode']);
		$stmt->bindValue('place', $Data['place']);
		$stmt->bindValue('phone', $Data['phone']);
		$stmt->bindValue('phone2', $Data['phone2']);
		$stmt->bindValue('email', $Data['email']);
		$stmt->bindValue('companyname', $Data['companyname']);
		$stmt->bindValue('nip', $Data['nip']);
		$stmt->bindValue('encryptionKey', Session::getActiveEncryptionKeyValue());
		$stmt->bindValue('id', $id);
		$stmt->bindValue('countryid', $Data['countryid']);
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_ORDER_BILLING_ADDRESS_EDIT'), 13, $e->getMessage());
		}
		return true;
	}

	public function getOrderProductAttributes ($productId, $variantId)
	{
		if ($variantId != NULL){
			$sql = '
				SELECT
					A.idproductattributeset AS id,
					A.`value`,
					A.stock AS qty,
					A.weight,
					A.symbol,
					A.photoid,
					A.suffixtypeid AS prefix_id,
					GROUP_CONCAT(SUBSTRING(CONCAT(\' \', CONCAT(AP.name,\': \',C.name)), 1) SEPARATOR \'<br />\') AS name
				FROM
					productattributeset A
					LEFT JOIN productattributevalueset B ON A.idproductattributeset = B.productattributesetid
					LEFT JOIN attributeproductvalue C ON B.attributeproductvalueid = C.idattributeproductvalue
					LEFT JOIN attributeproduct AP ON C.attributeproductid = AP.idattributeproduct
					LEFT JOIN product D ON A.productid = D.idproduct
					LEFT JOIN suffixtype E ON A.suffixtypeid = E.idsuffixtype
					LEFT JOIN vat V ON V.idvat = D.vatid
				WHERE
					productid = :productid AND
					A.idproductattributeset = :variantid
			';
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('productid', $productId);
			$stmt->bindValue('variantid', $variantId);
			$stmt->execute();
			$Data = Array();
			while ($rs = $stmt->fetch()){
				$Data[] = Array(
					'id' => $rs['id'],
					'value' => $rs['value'],
					'qty' => $rs['qty'],
					'weight' => $rs['weight'],
					'ean' => $rs['symbol'],
					'prefix_id' => $rs['prefix_id'],
					'name' => $rs['name'],
					'photo' => App::getModel('product')->getThumbPathForId($rs['photoid'])
				);
			}
			return $Data;
		}
		else{
			return Array();
		}
	}

	public function updateOrderProduct ($Data, $id)
	{
		$sql = 'DELETE FROM orderproductattribute WHERE orderproductid IN (SELECT idorderproduct FROM orderproduct WHERE orderid = :id)';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new Exception($e->getMessage());
		}

		$sql = 'DELETE FROM orderproduct WHERE orderid = :id';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new Exception($e->getMessage());
		}

		$stringData = Array();

		foreach ($Data['products'] as $value){
			$ids = $value['idproduct'];
			$sql = "SELECT
						P.sellprice,
						V.`value` as vat,
						PT.name as productname,
						P.idproduct,
						ROUND((P.sellprice + P.sellprice*(V.`value`/100)),2) as pricebrutto
					FROM product P
					LEFT JOIN producttranslation PT ON P.idproduct = PT.productid AND PT.languageid = :languageid
					LEFT JOIN vat V ON V.idvat = vatid
					WHERE P.idproduct=:ids";
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('ids', $ids);
			$stmt->bindValue('languageid', Helper::getLanguageId());
			$stmt->execute();
			$rs = $stmt->fetch();
			if ($rs){
				$productid = $rs['idproduct'];
				$stringData[$productid] = Array(
					'idproduct' => $productid,
					'pricebrutto' => $rs['pricebrutto'],
					'vat' => $rs['vat'],
					'productname' => $rs['productname']
				);
			}
		}
		foreach ($Data['products'] as $value){
			$sql = 'INSERT INTO orderproduct SET
						name = :name,
						price = :price,
						qty = :qty,
						qtyprice = :qtyprice,
						orderid = :orderid,
						productid = :productid,
						productattributesetid = :productattributesetid,
						vat = :vat,
						pricenetto = :pricenetto';
			$stmt = Db::getInstance()->prepare($sql);
			if (substr('' . $value['idproduct'], 0, 3) != 'new'){
				$stmt->bindValue('name', $stringData[$value['idproduct']]['productname']);
				$stmt->bindValue('price', floatval($value['sellprice']) * (1 + floatval($stringData[$value['idproduct']]['vat']) / 100));
				$stmt->bindValue('orderid', $id);
				$stmt->bindValue('productid', $value['idproduct']);
				$stmt->bindValue('vat', $stringData[$value['idproduct']]['vat']);
			}
			else{
				$stmt->bindValue('name', $value['name']);
				$stmt->bindValue('price', floatval($value['sellprice']) * (1 + floatval($value['vat']) / 100));
				$stmt->bindValue('orderid', $id);
				$stmt->bindValue('productid', NULL);
				$stmt->bindValue('vat', $value['vat']);
			}
			$stmt->bindValue('qty', $value['quantity']);
			$stmt->bindValue('qtyprice', ($value['quantity'] * $value['sellprice']));
			if ($value['variant'] > 0){
				$stmt->bindValue('productattributesetid', $value['variant']);
			}
			else{
				$stmt->bindValue('productattributesetid', NULL);
			}
			$stmt->bindValue('pricenetto', $value['sellprice']);

			if ($value['trackstock'] == 1){
				$decrease = $value['quantity'] - $value['previousquantity'];
				if ($decrease > $value['stock']){
					$decrease = $value['stock'];
				}
				if ($value['variant'] > 0){
					$this->decreaseProductAttributeStock($value['idproduct'], $value['variant'], $decrease);
				}
				else{
					$this->decreaseProductStock($value['idproduct'], $decrease);
				}
			}

			try{
				$stmt->execute();
			}
			catch (Exception $e){
				throw new CoreException(_('ERR_PRODUCT_TO_ORDER_ADD'), 112, $e->getMessage());
			}
		}

		App::getModel('product')->syncStock();
	}

	protected function decreaseProductAttributeStock ($productid, $idproductattribute, $qty)
	{
		$sql = 'UPDATE productattributeset SET stock = stock-:qty
				WHERE productid = :productid
				AND idproductattributeset = :idproductattribute';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('qty', $qty);
		$stmt->bindValue('productid', $productid);
		$stmt->bindValue('idproductattribute', $idproductattribute);
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new Exception($e->getMessage());
		}
	}

	protected function decreaseProductStock ($productid, $qty)
	{
		$sql = 'UPDATE product SET stock = stock-:qty
				WHERE idproduct = :idproduct';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('qty', $qty);
		$stmt->bindValue('idproduct', $productid);
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new Exception($e->getMessage());
		}
	}

	public function updateOrder ($Data, $id)
	{
		$dispatchmethodId = $Data['additional_data']['payment_data']['delivery_method'];

		$sql = "SELECT
					DMT.name as dispatchmethodname,
					D.iddispatchmethod
				FROM dispatchmethod D
        LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
				WHERE iddispatchmethod = :dispatchmethodId";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('dispatchmethodId', $dispatchmethodId);
		$stmt->execute();
		$rs = $stmt->fetch();
		$dispatchData = Array();
		if ($rs){
			$dispatchmethodname = $rs['dispatchmethodname'];
		}

		$paymentmethodId = $Data['additional_data']['payment_data']['payment_method'];

		$sql = "SELECT
					PMT.name as paymentmethodname,
					idpaymentmethod
				FROM paymentmethod P
        LEFT JOIN paymentmethodtranslation PMT ON PMT.paymentmethodid = P.idpaymentmethod AND PMT.languageid = :languageid
				WHERE idpaymentmethod=:paymentmethodId";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('paymentmethodId', $paymentmethodId);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->execute();
		$rs = $stmt->fetch();
		$paymentData = Array();
		if ($rs){
			$paymenId = $rs['idpaymentmethod'];
			$paymentData[$paymenId] = Array(
				'paymentmethodname' => $rs['paymentmethodname']
			);
		}
		if (isset($Data['additional_data']['payment_data']['rules_cart']) && $Data['additional_data']['payment_data']['rules_cart'] > 0){
      $oldOrderData = $this->getOrderById($id);
			$ruleCart = $this->calculateRulesCatalog($Data['additional_data']['payment_data']['rules_cart'], $oldOrderData['clientgroupid']);
      if (! empty($ruleCart)) {
        if (!empty($ruleCart['freedelivery'])) {
          $Data['dispatchmethodprice'] = 0;
         }
         if ($ruleCart['discount'] > 0){
          $symbol = $ruleCart['symbol'];
          switch ($symbol) {
            case '%':
              $pricePromo = abs($Data['pricebrutto'] * ($ruleCart['discount'] / 100));
              $globalpricePromo = abs(($Data['pricebrutto'] + $Data['dispatchmethodprice']) * ($ruleCart['discount'] / 100));
              $globalpricenettoPromo = abs($Data['pricenetto'] * ($ruleCart['discount'] / 100));
              break;
            case '+':
              $pricePromo = $Data['pricebrutto'] + $ruleCart['discount'];
              $globalpricePromo = ($Data['pricebrutto'] + $Data['dispatchmethodprice']) + $ruleCart['discount'];
              $globalpricenettoPromo = $Data['pricenetto'] + $ruleCart['discount'];
              break;
            case '-':
              $pricePromo = $Data['pricebrutto'] - $ruleCart['discount'];
              $globalpricePromo = ($Data['pricebrutto'] + $Data['dispatchmethodprice']) - $ruleCart['discount'];
              $globalpricenettoPromo = $Data['pricenetto'] - $ruleCart['discount'];
              break;
          }
        }
      }
		}

    if($Data['pricebrutto'] > 0 || $Data['pricenetto'] > 0 || $price || $globalpricePromo) {
      $sql = 'UPDATE `order` SET
            price=:price,
            dispatchmethodprice=:dispatchmethodprice,
            globalprice=:globalprice,
            dispatchmethodname=:dispatchmethodname,
            dispatchmethodid=:dispatchmethodid,
            paymentmethodname=:paymentmethodname,
            paymentmethodid=:paymentmethodid,
            globalpricenetto=:globalpricenetto,
            pricebeforepromotion= :pricebeforepromotion,
            rulescartid= :rulescartid
          WHERE idorder= :id';
      $stmt = Db::getInstance()->prepare($sql);
      if (isset($pricePromo) && $pricePromo > 0){
        $stmt->bindValue('price', $pricePromo);
        $stmt->bindValue('globalprice', $globalpricePromo);
        $stmt->bindValue('dispatchmethodprice', $Data['dispatchmethodprice']);
        $stmt->bindValue('globalpricenetto', $globalpricenettoPromo);
        $stmt->bindValue('pricebeforepromotion', ($Data['pricebrutto'] + $Data['dispatchmethodprice']));
        $stmt->bindValue('rulescartid', $Data['additional_data']['payment_data']['rules_cart']);
      }
      else{
        $stmt->bindValue('price', $Data['pricebrutto']);
        $stmt->bindValue('globalprice', ($Data['pricebrutto'] + $Data['dispatchmethodprice']));
        $stmt->bindValue('dispatchmethodprice', $Data['dispatchmethodprice']);
        $stmt->bindValue('globalpricenetto', $Data['pricenetto']);
        $stmt->bindValue('pricebeforepromotion', NULL);
        $stmt->bindValue('rulescartid', NULL);
      }
      $stmt->bindValue('id', $id);
      $stmt->bindValue('dispatchmethodname', $dispatchmethodname);
      $stmt->bindValue('dispatchmethodid', $dispatchmethodId);
      $stmt->bindValue('paymentmethodname', $paymentData[$Data['additional_data']['payment_data']['payment_method']]['paymentmethodname']);
      $stmt->bindValue('paymentmethodid', $Data['additional_data']['payment_data']['payment_method']);
      try{
        $stmt->execute();
      }
      catch (Exception $e){
        throw new CoreException(_('ERR_ORDER_EDIT'), 13, $e->getMessage());
      }
    }
		return true;
	}

	public function getProductsDataGrid ($id)
	{
		$sql = "SELECT
					OP.productid as idproduct,
					OP.pricenetto as sellprice,
					OP.productattributesetid as variant,
					OP.qty as quantity,
					OP.ean,
          COALESCE((SELECT weight FROM productattributeset WHERE productid = OP.productid AND idproductattributeset = OP.productattributesetid), P.weight) as weight,
					IF(OP.photoid IS NULL, PP.photoid, OP.photoid) AS photoid,
					P.trackstock
 				FROM orderproduct OP
 				LEFT JOIN product P ON P.idproduct = OP.productid
				LEFT JOIN productphoto PP ON PP.productid = P.idproduct AND PP.mainphoto = 1
        WHERE orderid = :id
        GROUP BY idorderproduct"; // fix for duplicated products in order editor
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'idproduct' => $rs['idproduct'],
				'quantity' => $rs['quantity'],
				'ean' => $rs['ean'],
				'thumb' => App::getModel('product')->getThumbPathForId($rs['photoid']),
				'previousquantity' => $rs['quantity'],
				'trackstock' => (int) $rs['trackstock'],
				'sellprice' => $rs['sellprice'],
				'variant' => $rs['variant'],
        'weight' => $rs['weight'],
				'stock' => $this->getCurrentStock($rs['idproduct'], $rs['variant'])
			);
			if ((int) $rs['photoid'] > 0){
			}
		}
		return $Data;
	}

	public function getCurrentStock ($idproduct, $variantid)
	{
		if ($variantid != NULL && $variantid > 0){
			$sql = "SELECT
						stock
	 				FROM productattributeset
					WHERE productid = :productid AND idproductattributeset = :variant";
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('productid', $idproduct);
			$stmt->bindValue('variant', $variantid);
			$stmt->execute();
			$rs = $stmt->fetch();
			if ($rs){
				return $rs['stock'];
			}
		}
		else{
			$sql = "SELECT
						stock
	 				FROM product
					WHERE idproduct = :productid";
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('productid', $idproduct);
			$stmt->execute();
			$rs = $stmt->fetch();
			if ($rs){
				return $rs['stock'];
			}
		}
		return 0;
	}

	public function addNewOrder ($Data)
	{
		Db::getInstance()->beginTransaction();
		try{
			$newOrderId = $parentOrderId = $this->addOrder($Data);
			$this->addOrderClientData($Data['address_data']['billing_data'], $newOrderId);
			$this->addOrderClientDeliveryData($Data['address_data']['shipping_data'], $newOrderId);
			$this->addOrderProduct($Data['products_data']['products'], $newOrderId);
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_ORDER_ADD'), 112, $e->getMessage());
		}

		Db::getInstance()->commit();
		return $newOrderId;
	}

	public function addOrderProduct ($array, $newOrderId)
	{
		$Data = Array();
		foreach ($array as $value){
			$id = $value['idproduct'];
			$sql = "SELECT
						P.sellprice,
						V.`value` as vat,
						PT.name as productname,
						P.idproduct,
						ROUND((P.sellprice + P.sellprice*(V.`value`/100)),2) as pricebrutto,
						P.trackstock
					FROM product P
					LEFT JOIN producttranslation PT ON P.idproduct = PT.productid AND PT.languageid = :languageid
					LEFT JOIN vat V ON V.idvat = vatid
					WHERE P.idproduct=:id";
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('id', $id);
			$stmt->bindValue('languageid', Helper::getLanguageId());
			$stmt->execute();
			$rs = $stmt->fetch();
			if ($rs){
				$productid = $rs['idproduct'];
				$Data[$productid] = Array(
					'idproduct' => $rs['idproduct'],
					'pricebrutto' => $rs['pricebrutto'],
					'vat' => $rs['vat'],
					'trackstock' => $rs['trackstock'],
					'productname' => $rs['productname']
				);
			}
		}
		foreach ($array as $value){
			$sql = 'INSERT INTO orderproduct SET
						name = :name,
						price = :price,
						qty = :qty,
						qtyprice = :qtyprice,
						orderid = :orderid,
						productid = :productid,
						productattributesetid = :productattributesetid,
						variant = :variant,
						vat = :vat,
						pricenetto = :pricenetto';
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('name', $Data[$value['idproduct']]['productname']);
			$stmt->bindValue('price', $Data[$value['idproduct']]['pricebrutto']);
			$stmt->bindValue('qty', $value['quantity']);
			$stmt->bindValue('qtyprice', ($value['quantity'] * $value['sellprice']));
			$stmt->bindValue('orderid', $newOrderId);
			$stmt->bindValue('productid', $value['idproduct']);
			if ($value['variant'] > 0){
				$stmt->bindValue('productattributesetid', $value['variant']);
        if (!empty($value['variantcaption'])) {
          $stmt->bindValue('variant', $value['variantcaption']);
        }
        else {
          $stmt->bindValue('variant', '');
        }
			}
			else{
				$stmt->bindValue('productattributesetid', NULL);
				$stmt->bindValue('variant', NULL);
			}
			$stmt->bindValue('vat', $Data[$value['idproduct']]['vat']);
			$stmt->bindValue('pricenetto', $value['sellprice']);
			try{
				$stmt->execute();
			}
			catch (Exception $e){
				throw new CoreException(_('ERR_PRODUCT_TO_ORDER_ADD'), 112, $e->getMessage());
			}

			if ($Data[$value['idproduct']]['trackstock'] == 1){
				$decrease = $value['quantity'];
				if ($value['variant'] > 0){
					$this->decreaseProductAttributeStock($value['idproduct'], $value['variant'], $decrease);
				}
				else{
					$this->decreaseProductStock($value['idproduct'], $decrease);
				}
			}

		}

		App::getModel('product')->syncStock();
	}

	public function addOrderClientDeliveryData ($Data, $newOrderId)
	{
		$sql = 'INSERT INTO orderclientdeliverydata SET
					orderid = :orderid,
					firstname = AES_ENCRYPT(:firstname, :encryptionKey),
					surname = AES_ENCRYPT(:surname, :encryptionKey),
					place = AES_ENCRYPT(:place, :encryptionKey),
					postcode = AES_ENCRYPT(:postcode, :encryptionKey),
					phone = AES_ENCRYPT(:phone, :encryptionKey),
					phone2 = AES_ENCRYPT(:phone2, :encryptionKey),
					email = AES_ENCRYPT(:email, :encryptionKey),
					street = AES_ENCRYPT(:street, :encryptionKey),
					streetno = AES_ENCRYPT(:streetno, :encryptionKey),
					placeno = AES_ENCRYPT(:placeno, :encryptionKey),
					nip = AES_ENCRYPT(:nip, :encryptionKey),
					companyname = AES_ENCRYPT(:companyname, :encryptionKey)
				';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('orderid', $newOrderId);
		$stmt->bindValue('firstname', $Data['firstname']);
		$stmt->bindValue('surname', $Data['surname']);
		$stmt->bindValue('place', $Data['place']);
		$stmt->bindValue('postcode', $Data['postcode']);
		$stmt->bindValue('phone', $Data['phone']);
		$stmt->bindValue('phone2', $Data['phone2']);
		$stmt->bindValue('email', $Data['email']);
		$stmt->bindValue('street', $Data['street']);
		$stmt->bindValue('streetno', $Data['streetno']);
		$stmt->bindValue('placeno', $Data['placeno']);
		$stmt->bindValue('nip', $Data['nip']);
		$stmt->bindValue('companyname', $Data['companyname']);
		$stmt->bindValue('encryptionKey', Session::getActiveEncryptionKeyValue());
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_ORDER_CLIENT_DELIVERY_DATA_ADD'), 112, $e->getMessage());
		}
	}

	public function addOrderClientData ($Data, $newOrderId)
	{
		$sql = 'INSERT INTO orderclientdata SET
					orderid = :orderid,
					firstname = AES_ENCRYPT(:firstname, :encryptionKey),
					surname = AES_ENCRYPT(:surname, :encryptionKey),
					place = AES_ENCRYPT(:place, :encryptionKey),
					postcode = AES_ENCRYPT(:postcode, :encryptionKey),
					phone = AES_ENCRYPT(:phone, :encryptionKey),
					phone2 = AES_ENCRYPT(:phone2, :encryptionKey),
					email = AES_ENCRYPT(:email, :encryptionKey),
					street = AES_ENCRYPT(:street, :encryptionKey),
					streetno = AES_ENCRYPT(:streetno, :encryptionKey),
					placeno = AES_ENCRYPT(:placeno, :encryptionKey),
					nip = AES_ENCRYPT(:nip, :encryptionKey),
					companyname = AES_ENCRYPT(:companyname, :encryptionKey)
				';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('orderid', $newOrderId);
		$stmt->bindValue('firstname', $Data['firstname']);
		$stmt->bindValue('surname', $Data['surname']);
		$stmt->bindValue('place', $Data['place']);
		$stmt->bindValue('postcode', $Data['postcode']);
		$stmt->bindValue('phone', $Data['phone']);
		$stmt->bindValue('phone2', $Data['phone2']);
		$stmt->bindValue('email', $Data['email']);
		$stmt->bindValue('street', $Data['street']);
		$stmt->bindValue('streetno', $Data['streetno']);
		$stmt->bindValue('placeno', $Data['placeno']);
		$stmt->bindValue('nip', $Data['nip']);
		$stmt->bindValue('companyname', $Data['companyname']);
		$stmt->bindValue('encryptionKey', Session::getActiveEncryptionKeyValue());
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_ORDER_CLIENT_DATA_ADD'), 112, $e->getMessage());
		}
	}

	public function addOrder ($Data)
	{
		$dispatchmethodId = $Data['additional_data']['payment_data']['delivery_method'];

		$sql = "SELECT
					DMT.name as dispatchmethodname,
					iddispatchmethod
				FROM dispatchmethod D
        LEFT JOIN dispatchmethodtranslation DMT ON DMT.dispatchmethodid = D.iddispatchmethod AND DMT.languageid = :languageid
				WHERE iddispatchmethod=:dispatchmethodId";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('dispatchmethodId', $dispatchmethodId);
		$stmt->execute();
		$rs = $stmt->fetch();
		$dispatchData = Array();
		if ($rs){
			$dispatchmethodname = $rs['dispatchmethodname'];
		}

		$paymentmethodId = $Data['additional_data']['payment_data']['payment_method'];

		$sql = "SELECT
					PMT.name as paymentmethodname,
					idpaymentmethod
				FROM paymentmethod P
        LEFT JOIN paymentmethodtranslation PMT ON PMT.paymentmethodid = P.idpaymentmethod AND PMT.languageid = :languageid
				WHERE idpaymentmethod=:paymentmethodId";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('paymentmethodId', $paymentmethodId);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->execute();
		$rs = $stmt->fetch();
		$paymentData = Array();
		if ($rs){
			$paymentmethodname = $rs['paymentmethodname'];
		}

		$sql = 'INSERT INTO `order` SET
					clientid = :clientid,
					orderstatusid = (SELECT idorderstatus FROM orderstatus WHERE `default` = 1),
					price = :price,
					dispatchmethodprice = :dispatchmethodprice,
					globalprice = :globalprice,
					dispatchmethodid = :dispatchmethodid,
					dispatchmethodname = :dispatchmethodname,
					paymentmethodid = :paymentmethodid,
					paymentmethodname = :paymentmethodname,
					globalpricenetto = :globalpricenetto,
					viewid = :viewid,
					pricebeforepromotion = :pricebeforepromotion,
					rulescartid = :rulescartid,
					currencyid = :currencyid,
					currencysymbol = :currencysymbol,
					currencyrate = :currencyrate,
					sessionid = :sessionid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('sessionid', session_id());
		if (isset($Data['client_data']['client']) && (int) $Data['client_data']['client'] > 0){
			$stmt->bindValue('clientid', $Data['client_data']['client']);
		}
		else{
			$stmt->bindValue('clientid', NULL);
		}
		$stmt->bindValue('currencyid', Session::getActiveCurrencyId());
		$stmt->bindValue('currencysymbol', Session::getActiveCurrencySymbol());
		$stmt->bindValue('currencyrate', Session::getActiveCurrencyRate());
		$stmt->bindValue('price', $Data['pricebrutto']);
		$stmt->bindValue('globalprice', ($Data['pricebrutto'] + $Data['dispatchmethodprice']));
		$stmt->bindValue('dispatchmethodprice', $Data['dispatchmethodprice']);
		$stmt->bindValue('globalpricenetto', $Data['pricenetto']);
		$stmt->bindValue('pricebeforepromotion', NULL);
		$stmt->bindValue('rulescartid', NULL);

		$stmt->bindValue('dispatchmethodname', $dispatchmethodname);
		$stmt->bindValue('dispatchmethodid', $dispatchmethodId);
		$stmt->bindValue('paymentmethodname', $paymentmethodname);
		$stmt->bindValue('paymentmethodid', $paymentmethodId);

		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_ORDER_ADD'), 112, $e->getMessage());
		}
		return Db::getInstance()->lastInsertId();
	}

	public function updateOrderStatus ($Data, $id)
	{
		$sql = 'UPDATE `order` SET orderstatusid=:orderstatusid	WHERE idorder = :id';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->bindValue('orderstatusid', $Data['status']);
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_ORDER_STATUS_EDIT'), 13, $e->getMessage());
		}
		return true;
	}

	public function getOrderDeliveryData ($idorder)
	{
		$sql = "SELECT
					AES_DECRYPT(firstname, :encryptionKey) AS firstname,
					AES_DECRYPT(surname, :encryptionKey) AS surname,
					AES_DECRYPT(street, :encryptionKey) AS street,
					AES_DECRYPT(streetno, :encryptionKey) AS streetno,
					AES_DECRYPT(placeno, :encryptionKey) AS placeno,
					AES_DECRYPT(postcode, :encryptionKey) AS postcode,
					AES_DECRYPT(place, :encryptionKey) AS place,
        			O.dispatchmethodname
 				FROM orderclientdeliverydata ODC
				LEFT JOIN `order`O ON ODC.orderid = O.idorder
				WHERE ODC.orderid = :idorder";
		$Data = Array();
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('idorder', $idorder);
		$stmt->bindValue('encryptionKey', Session::getActiveEncryptionKeyValue());
		$stmt->execute();
		$rs = $stmt->fetch();
		if ($rs){
			$Data = Array(
				'firstname' => $rs['firstname'],
				'surname' => $rs['surname'],
				'street' => $rs['street'],
				'streetno' => $rs['streetno'],
				'placeno' => $rs['placeno'],
				'postcode' => $rs['postcode'],
				'place' => $rs['place'],
				'placename' => $rs['place'],
				'dispatchmethodname' => $rs['dispatchmethodname']
			);
		}
		return $Data;
	}

	public function getOrderProductListByClientForDatagrid ($id)
	{
		$Data = $this->getOrderProductListByClient($id);
		$Html = '';
		foreach ($Data as $key => $product){
			if (count($product['attributes']) > 0){
				$Html .= '<strong>' . $product['productname'] . '</strong><br />';
				$Html .= $product['attributes']['name'] . '<br />';
			}
			else{
				$Html .= $product['productname'] . '<br />';
			}
		}
		return $Html;
	}

	public function getOrderProductListByClient ($idorder)
	{
		$sql = 'SELECT
					O.idorder,
					OP.name as productname,
					OP.qty,
					OP.qtyprice,
					OP.price,
					OP.pricenetto,
					OP.vat,
					OP.productid,
					OP.idorderproduct,
					OP.productattributesetid AS variant
				FROM `order` O
				LEFT JOIN orderclientdata OCD ON OCD.orderid=O.idorder
				LEFT JOIN orderproduct OP ON OP.orderid=O.idorder
				WHERE idorder=:idorder
				ORDER BY productname';
		$Data = Array();
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('idorder', $idorder);
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'idproduct' => $rs['productid'],
				'qty' => $rs['qty'],
				'productid' => $rs['productid'],
				'qtyprice' => $rs['qtyprice'],
				'price' => $rs['price'],
				'pricenetto' => $rs['pricenetto'],
				'vat' => $rs['vat'],
				'productname' => $rs['productname'],
				'attributes' => $this->getOrderProductAttributes($rs['productid'], $rs['variant'])
			);
		}
		return $Data;
	}

	public function addOrderNotes ($Data, $orderid)
	{
		$sql = 'INSERT INTO ordernotes (content, orderid, user)
				VALUES (:content, :orderid, :user)';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('content', $Data['contents']);
		$stmt->bindValue('orderid', $orderid);
		$stmt->bindValue('user', App::getModel('users')->getUserFullName());
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_ORDER_NOTES_ADD'), 112, $e->getMessage());
		}
	}

	public function getOrderNotes ($orderid)
	{
		$sql = "SELECT * FROM ordernotes WHERE orderid = :orderid";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('orderid', $orderid);
		try{
			$stmt->execute();
			$Data = Array();
			while ($rs = $stmt->fetch()){
				$Data[] = Array(
					'content' => $rs['content'],
					'user' => $rs['user'],
					'adddate' => $rs['adddate'],
					'orderid' => $rs['orderid']
				);
			}
		}
		catch (Exception $e){
			throw new Exception(_('ERR_ORDER_NOTES_NO_EXIST'), 11, $e->getMessage());
		}
		return $Data;
	}

	public function getclientOrderHistory ($clientid)
	{
		$sql = "SELECT
					O.idorder,
					O.`adddate`,
					O.globalprice,
					OST.name as orderstatusname
				FROM `order` O
				LEFT JOIN orderstatustranslation OST ON O.orderstatusid = OST.orderstatusid AND OST.languageid = :languageid
				WHERE O.clientid = :clientid";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('clientid', $clientid);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		try{
			$stmt->execute();
			$Data = Array();
			while ($rs = $stmt->fetch()){
				$Data[] = Array(
					'idorder' => $rs['idorder'],
					'adddate' => $rs['adddate'],
					'status' => $rs['orderstatusname'],
					'globalprice' => $rs['globalprice']
				);
			}
		}
		catch (Exception $e){
			throw new Exception(_('ERR_ORDER_HISTORY_NO_EXIST'), 11, $e->getMessage());
		}
		return $Data;
	}

	public function doAJAXChangeOrderStatus ($id, $datagrid, $status)
	{
		$ids = (is_array($id)) ? $id : (array) $id;

		$sql = 'UPDATE `order` SET orderstatusid = :status
				WHERE idorder IN (' . implode(',', $ids) . ')';
		$stmt = Db::getInstance()->prepare($sql);
		if ($status > 0){
			$stmt->bindValue('status', $status);
		}
		else{
			$stmt->bindValue('status', NULL);
		}
		$stmt->execute();

		$Data['inform'] = 0;
		$Data['status'] = $status;
		$Data['comment'] = '';

		foreach ($ids as $orderid){
			$this->addOrderHistory($Data, $orderid);
		}

		return $this->getDatagrid()->refresh($datagrid);
	}

	public function doAJAXChangeOrderStatusNotify ($id, $datagrid, $status)
	{
		$ids = (is_array($id)) ? $id : (array) $id;

		$sql = 'UPDATE `order` SET orderstatusid = :status
				WHERE idorder IN (' . implode(',', $ids) . ')';
		$stmt = Db::getInstance()->prepare($sql);
		if ($status > 0){
			$stmt->bindValue('status', $status);
		}
		else{
			$stmt->bindValue('status', NULL);
		}
		$stmt->execute();

		$Data['inform'] = 1;
		$Data['status'] = $status;
		$Data['comment'] = '';

		foreach ($ids as $orderid){
			$this->addOrderHistory($Data, $orderid);
             $orderData = $this->getOrderById($orderid);

		    $orderhistory = $this->getLastOrderHistory((int) $orderData['order_id'], $status);
			App::getRegistry()->template->assign('orderhistory', $orderhistory);

			App::getModel('mailer')->sendEmail(Array(
			'template' => 'orderhistory',
			'email' => Array(
				$orderhistory['email']
			),
			'bcc' => false,
			'subject' => _('TXT_CHANGE_ORDER_STATUS_NR') . $orderhistory['ids'],
			'viewid' => $orderData['viewid']
            ));
		}

		return $this->getDatagrid()->refresh($datagrid);
	}	
	
	public function getClientDataWithAddresses ($request)
	{
		foreach ($request as $id){
			$sql = 'SELECT
						AES_DECRYPT(CD.firstname, :encryptionkey) AS firstname,
						AES_DECRYPT(CD.surname, :encryptionkey) AS surname,
						AES_DECRYPT(CD.email, :encryptionkey) AS email,
						AES_DECRYPT(CD.phone, :encryptionkey) AS phone,
						AES_DECRYPT(CD.phone2, :encryptionkey) AS phone2,
						CGT.name as `group`
					FROM clientdata CD
					LEFT JOIN clientgrouptranslation CGT ON CGT.clientgroupid = CD.clientgroupid AND languageid=:languageid
					WHERE CD.clientid=:id';
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('languageid', Helper::getLanguageId());
			$stmt->bindValue('id', $id);
			$stmt->bindValue('encryptionkey', $this->registry->session->getActiveEncryptionKeyValue());
			$stmt->execute();
			$rs = $stmt->fetch();
			$Data = Array();
			if ($rs){
				$Data = Array(
					'name' => $rs['firstname'] . ' ' . $rs['surname'],
					'group' => $rs['group'],
					'email' => $rs['email'],
					'phone' => $rs['phone'],
					'phone2' => $rs['phone2'],
					'billing_address' => App::getModel('client')->getClientAddress($id, 1),
					'delivery_address' => App::getModel('client')->getClientAddress($id, 0)
				);
			}
			else{
				throw new CoreException($this->registry->core->getMessage('ERR_ADDRESS_NO_EXIST'));
			}
			return $Data;
		}
	}

	public function getPrintableOrderById ($id, $tpl)
	{
		$pdf = new \Gekosale\Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8');
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Gekosale');
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		$pdf->setHeaderFont(Array(
			PDF_FONT_NAME_MAIN,
			'',
			PDF_FONT_SIZE_MAIN
		));
		$pdf->setFooterFont(Array(
			PDF_FONT_NAME_DATA,
			'',
			PDF_FONT_SIZE_DATA
		));

		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray(1);
		$pdf->SetFont('dejavusans', '', 10);

		$order = $this->getOrderById($id);
		$lp = 1;
		foreach ($order['products'] as $key => $val){
			$order['products'][$key]['lp'] = $lp;
      if(!empty($order['products'][$key]['photo'])) {
        $order['products'][$key]['photo'] = 'design/' . str_replace(DESIGNPATH, '', $order['products'][$key]['photo']);
      }
			$lp ++;
		}

    $order['products'][] = Array(
      'name' => $order['delivery_method']['deliverername'],
      'net_price' => sprintf('%01.2f', $order['delivery_method']['delivererpricenetto']),
      'quantity' => 1,
      'net_subtotal' => sprintf('%01.2f', $order['delivery_method']['delivererpricenetto']),
      'vat' => sprintf('%01.2f', $order['delivery_method']['deliverervat']),
      'vat_value' => sprintf('%01.2f', $order['delivery_method']['deliverervatvalue']),
      'subtotal' => sprintf('%01.2f', $order['delivery_method']['delivererprice']),
      'lp' => $lp
    );

		if ($order['pricebeforepromotion'] > 0 && ($order['pricebeforepromotion'] > $order['total'])){
			$rulesCostGross = $order['total'] - $order['pricebeforepromotion'];
			$rulesCostNet = ($order['total'] - $order['pricebeforepromotion']);
			$rulesVat = $rulesCostGross - $rulesCostNet;
      if (!empty($order['rulescartid'])) {
        $discountName = App::getModel('rulescart')->getRulesCartTranslation($order['rulescartid']);
        $discountName = $discountName[Helper::getLanguageId()]['name'];
      }
      else {
        $discountName = _('TXT_DISCOUNT');
      }
			$order['products'][] = Array(
        'name' => $discountName,
				'net_price' => sprintf('%01.2f', $rulesCostNet),
				'quantity' => 1,
				'net_subtotal' => sprintf('%01.2f', $rulesCostNet),
        'vat' => '0.00',
				'vat_value' => sprintf('%01.2f', $rulesVat),
				'subtotal' => sprintf('%01.2f', $rulesCostGross),
				'lp' => $lp
			);
		}

		$summary = Array();
		foreach ($order['products'] as $key => $val){
      if(!isset($summary[$val['vat']]))
        $summary[$val['vat']] = array('vat' => $val['vat'], 'netto' => 0, 'brutto' => 0, 'vatvalue' => 0);
			$summary[$val['vat']]['vat'] = $val['vat'];
			$summary[$val['vat']]['netto'] += $val['net_subtotal'];
			$summary[$val['vat']]['brutto'] += $val['subtotal'];
			$summary[$val['vat']]['vatvalue'] += $val['vat_value'];
		}

		$Total = Array(
			'netto' => 0,
			'vatvalue' => 0
		);
		foreach ($summary as $key => $group){
			$Total['netto'] += $group['netto'];
			$Total['vatvalue'] += $group['vatvalue'];
		}
		$Total['brutto'] = sprintf('%01.2f', $Total['netto'] + $Total['vatvalue']);
		$companyaddress = App::getModel('invoice')->getMainCompanyAddress($order['viewid']);
		$this->registry->template->assign('order', $order);
		$this->registry->template->assign('companyaddress', $companyaddress);
		$this->registry->template->assign('summary', $summary);
		$this->registry->template->assign('total', $Total);
		$html = $this->registry->template->fetch($tpl);
		$pdf->AddPage();
		$pdf->writeHTML($html, true, 0, true, 0);
		@ob_clean();
		$pdf->Output(Core::clearUTF(_('TXT_ORDER') . '_' . $order['order_id']) . '.pdf', 'D');
	}

	public function getOrderTotals ($idorder, $withDelivery = true)
	{
		$summary = $this->getOrderSummary($idorder, $withDelivery);

		$totals = Array(
			'netto' => 0,
			'brutto' => 0,
			'vatvalue' => 0
		);
		foreach ($summary as $key => $group){
			$totals['netto'] += $group['netto'];
			$totals['brutto'] += $group['brutto'];
			$totals['vatvalue'] += $group['vatvalue'];
		}

		return $totals;
	}

	public function getOrderSummary ($idorder, $withDelivery = false)
	{
		$sql = "SELECT
					ROUND(OP.vat,2) AS vat,
					ROUND(SUM(OP.pricenetto * OP.qty) * (1 + (OP.vat / 100)),2) as brutto,
            		ROUND(SUM(OP.pricenetto * OP.qty),2) as netto
				FROM `order` O
				LEFT JOIN orderclientdata OCD ON OCD.orderid=O.idorder
				LEFT JOIN orderproduct OP ON OP.orderid=O.idorder
				WHERE idorder=:idorder
				GROUP BY OP.vat";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('idorder', $idorder);
		$summary = Array();
		try{
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				$summary[] = Array(
					'vat' => $rs['vat'],
					'netto' => $rs['netto'],
					'brutto' => $rs['brutto'],
					'vatvalue' => ($rs['brutto'] - $rs['netto'])
				);
			}
		}
		catch (Exception $e){
			throw new CoreException(_('ERR_GET_COMPANYADDRESS'));
		}

		if ($withDelivery == true){
			$orderData = $this->getOrderById($idorder);

			$bDelivererVatExists = false;
			foreach ($summary as $key => $group){
				if ($group['vat'] == $orderData['delivery_method']['deliverervat']){
					$summary[$key]['netto'] = $group['netto'] + $orderData['delivery_method']['delivererpricenetto'];
					$summary[$key]['brutto'] = $group['brutto'] + $orderData['delivery_method']['delivererprice'];
					$summary[$key]['vatvalue'] = $group['vatvalue'] + $orderData['delivery_method']['deliverervatvalue'];
					$bDelivererVatExists = true;
					break;
				}
			}
			if ($bDelivererVatExists == false){
				$summary[] = Array(
					'vat' => $orderData['delivery_method']['deliverervat'],
					'netto' => $orderData['delivery_method']['delivererpricenetto'],
					'brutto' => $orderData['delivery_method']['delivererprice'],
					'vatvalue' => $orderData['delivery_method']['deliverervatvalue']
				);
			}
		}

		return $summary;
	}
}
