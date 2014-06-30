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
 * $Revision: 593 $
 * $Author: gekosale $
 * $Date: 2011-11-04 11:52:58 +0100 (Pt, 04 lis 2011) $
 * $Id: spy.php 593 2011-11-04 10:52:58Z gekosale $ 
 */
namespace Gekosale;

class SpyModel extends Component\Model\Datagrid
{

	protected function initDatagrid ($datagrid)
	{
		$datagrid->setTableData('sessionhandler', Array(
			'id' => Array(
				'source' => 'SH.idsessionhandler'
			),
			'sessionid' => Array(
				'source' => 'SH.sessionid'
			),
			'client' => Array(
				'source' => 'CONCAT(AES_DECRYPT(CD.firstname,:encryptionkey),\' \',AES_DECRYPT(CD.surname,:encryptionkey))'
			),
			'client_session' => Array(
				'source' => 'SH.sessionid'
			),
			'ipaddress' => Array(
				'source' => 'SH.ipaddress'
			),
			'lastaddress' => Array(
				'source' => 'SH.url'
			),
			'client_status' => Array(
				'source' => 'IF( SH.expiredate >= NOW(), \'TXT_ACTIVE\', \'TXT_INACTIVE\')',
				'prepareForSelect' => true,
				'processLanguage' => true
			),
			'cart' => Array(
				'source' => 'CONCAT(SH.globalprice,\' \',SH.cartcurrency)'
			),
			'browser' => Array(
				'source' => 'SH.browser',
				'prepareForSelect' => true
			),
			'platform' => Array(
				'source' => 'SH.platform',
				'prepareForSelect' => true
			),
			'isbot' => Array(
				'source' => 'IF( SH.isbot = 1, \'TXT_YES\', \'TXT_NO\')',
				'prepareForSelect' => true,
				'processLanguage' => true
			),
			'ismobile' => Array(
				'source' => 'IF( SH.ismobile = 1, \'TXT_YES\', \'TXT_NO\')',
				'prepareForSelect' => true,
				'processLanguage' => true
			)
		));
		$datagrid->setFrom('
			sessionhandler SH
			LEFT JOIN clientdata CD ON CD.clientid = SH.clientid
		');
		
		$datagrid->setGroupBy('
			SH.sessionid
		');
		
		$datagrid->setAdditionalWhere('
			SH.viewid IN (' . Helper::getViewIdsAsString() . ')
		');
	}

	public function getDatagridFilterData ()
	{
		return $this->getDatagrid()->getFilterData();
	}

	public function getSpyForAjax ($request, $processFunction)
	{
		return $this->getDatagrid()->getData($request, $processFunction);
	}

	public function utf16_2_utf8 ($nowytekst)
	{
		$nowytekst = str_replace('u0104', '¥', $nowytekst); // ¥
		$nowytekst = str_replace('u0106', 'Æ', $nowytekst); // Æ
		$nowytekst = str_replace('u0118', 'Ê', $nowytekst); // Ê
		$nowytekst = str_replace('u0141', '£', $nowytekst); // £
		$nowytekst = str_replace('u0143', 'Ñ', $nowytekst); // Ñ
		$nowytekst = str_replace('u00D3', 'Ó', $nowytekst); // Ó
		$nowytekst = str_replace('u015A', 'Œ', $nowytekst); // Œ
		$nowytekst = str_replace('u0179', '', $nowytekst); // 
		$nowytekst = str_replace('u017B', '¯', $nowytekst); // ¯
		$nowytekst = str_replace('u0105', '¹', $nowytekst); // ¹
		$nowytekst = str_replace('u0107', 'æ', $nowytekst); // æ
		$nowytekst = str_replace('u0119', 'ê', $nowytekst); // ê
		$nowytekst = str_replace('u0142', '³', $nowytekst); // ³
		$nowytekst = str_replace('u0144', 'ñ', $nowytekst); // ñ
		$nowytekst = str_replace('u00F3', 'ó', $nowytekst); // ó
		$nowytekst = str_replace('u015B', 'œ', $nowytekst); // œ
		$nowytekst = str_replace('u017A', 'Ÿ', $nowytekst); // Ÿ
		$nowytekst = str_replace('u017C', '¿', $nowytekst); // ¿
		$nowytekst = str_replace('u017c', '¿', $nowytekst); // ¿
		return ($nowytekst);
	}

	public function getSessionData ($id)
	{
		$sql = 'SELECT clientid, cart FROM sessionhandler WHERE sessionid = :id';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->execute();
		$rs = $stmt->fetch();
		$Data = Array();
		if ($rs){
			$Data = Array(
				'clientid' => $rs['clientid'],
				'cart' => json_decode($rs['cart'], true)
			);
		}
		if (isset($Data['cart'])){
			foreach ($Data['cart'] as $key => $product){
				if (isset($product['attributes'])){
					foreach ($product['attributes'] as $k => $attr){
						$Data['cart'][$key]['attributes'][$k]['name'] = $this->utf16_2_utf8($Data['cart'][$key]['attributes'][$k]['name']);
					}
				}
				else{
					$Data['cart'][$key]['name'] = $this->utf16_2_utf8($product['name']);
				}
			}
		}
		return $Data;
	}
}