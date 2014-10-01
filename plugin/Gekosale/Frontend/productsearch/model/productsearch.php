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
 * $Revision: 623 $
 * $Author: gekosale $
 * $Date: 2012-01-20 20:47:46 +0100 (Pt, 20 sty 2012) $
 * $Id: productsearch.php 623 2012-01-20 19:47:46Z gekosale $
 */
namespace Gekosale;

use xajaxResponse;

class ProductSearchModel extends Component\Model\Dataset
{

	public function initDataset ($dataset)
	{
    App::getModel('product')->productsDataset($dataset);
			
    $dataset->queryFrom = '
      product P
      LEFT JOIN productcategory PC ON P.idproduct = PC.productid
      INNER JOIN category C ON PC.categoryid = C.idcategory AND C.enable = 1
      INNER JOIN viewcategory VC ON PC.categoryid = VC.categoryid AND VC.viewid = :viewid
      LEFT JOIN productnew PN ON P.idproduct = PN.productid
    ' . $dataset->queryFrom;

		$dataset->setAdditionalWhere('
			IF(:categoryid > 0, PC.categoryid = :categoryid, 1) AND 
			IF(:filterbyproducer > 0, FIND_IN_SET(CAST(P.producerid as CHAR), :producer), 1) AND
			(LOWER(P.ean) LIKE :name OR LOWER(P.delivelercode) LIKE :name OR LOWER(PT.name) LIKE :name) AND
			IF(:enablelayer > 0, FIND_IN_SET(CAST(P.idproduct as CHAR), :products), 1) AND
			P.enable = 1 AND
			IF(P.producerid IS NOT NULL, PV.viewid = :viewid, 1)
		');
		
		$dataset->setGroupBy('
			P.idproduct
		');
		
		$dataset->setHavingString('
			finalprice BETWEEN IF(:pricefrom > 0, :pricefrom, 0) AND IF( :priceto > 0, :priceto, 999999)
		');
		
		$dataset->setSQLParams(Array(
			'categoryid' => (int) $this->registry->core->getParam(),
			'producer' => 0,
			'pricefrom' => 0,
			'priceto' => 0,
			'name' => '',
			'enablelayer' => 0,
			'products' => Array()
		));
	}

	public function getProductDataset ()
	{
		return $this->getDataset()->getDatasetRecords();
	}

	public function search ($phrase = '', $categoryid = 0, $type = 0, $sellpricemin = 0, $sellpricemax = 0, $producer = 0, $client = 0)
	{
		$sql = 'SELECT 
						PS.name, 
						PS.description, 
						PS.shortdescription, 
						PS.productid, 
						P.sellprice as pricewithoutvat, 
						P.stock, 
						(SELECT ROUND(P.sellprice+(P.sellprice*vat.`value`)/100, 2)) AS price,
						PROD.idproducer, 
						PRODT.seo AS producerwww, 
						PRODT.name AS producername, 
						vat.`value`, 
						PHOTO.photoid AS mainphotoid
				FROM
					productsearch PS
					LEFT JOIN product P ON PS.productid = P.idproduct
					LEFT JOIN productcategory PC ON PC.productid = P.idproduct
					LEFT JOIN producer AS PROD ON PROD.idproducer = P.producerid
					LEFT JOIN producertranslation AS PRODT ON PRODT.producerid = PROD.idproducer
					LEFT JOIN vat AS vat ON vat.idvat = P.vatid
					LEFT JOIN productphoto PHOTO ON PHOTO.productid = P.idproduct
					LEFT JOIN clientdata AS CD ON CD.clientid = :clientid
					LEFT JOIN clientgroup AS CG ON CG.idclientgroup = CD.clientgroupid
				WHERE
					PHOTO.mainphoto = 1
					AND IF(:categoryid > 0, PC.categoryid = :categoryid, 1)
					AND IF(:producerid > 0, idproducer = :producerid, 1)
					AND PS.enable = 1
					AND PS.languageid = :languageid
					AND MATCH(PS.name, PS.description, PS.shortdescription, PS.producername, attributes) AGAINST(:phrase) > 0
					AND P.sellprice BETWEEN :sellpricemin AND :sellpricemax
				GROUP BY
					P.idproduct
				ORDER BY MATCH(PS.name, PS.description, PS.shortdescription, PS.producername, attributes) AGAINST(:phrase) DESC
				';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('clientid', Session::getActiveClientid());
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('phrase', $phrase);
		$stmt->bindValue('categoryid', $categoryid);
		$stmt->bindValue('type', $type);
		$stmt->bindValue('sellpricemin', $sellpricemin);
		$stmt->bindValue('sellpricemax', $sellpricemax);
		$stmt->bindValue('producerid', $producer);
		$stmt->execute();
		$Data = $stmt->fetchAll();
		foreach ($Data as $key => $value){
			try{
				$Image = App::getModel('gallery')->getSmallImageById($value['mainphotoid']);
				$Data[$key]['photo'] = App::getModel('gallery')->getImagePath($Image);
			}
			catch (Exception $e){
				echo $e->getMessage();
			}
		}
		return $Data;
	}

	public function getAllMostSearch ()
	{
		$Data = Array();
		$sql = "SELECT name, idmostsearch, textcount
					FROM mostsearch
					WHERE viewid=:viewid
					GROUP BY name ORDER BY textcount DESC LIMIT 10";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				$Data[] = Array(
					'idmostsearch' => $rs['name'],
					'name' => $rs['name'],
					'textcount' => $rs['textcount']
				);
			}
		}
		catch (Exception $e){
			throw new FrontendException($e->getMessage());
		}
		return $Data;
	}

	public function getMostSearchbyId ($id)
	{
		$Data = Array();
		$sql = "SELECT idmostsearch as id, name  
					FROM mostsearch 
					WHERE idmostsearch=:id AND viewid=:viewid";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				$Data[] = Array(
					'id' => $rs['id'],
					'name' => $rs['name'],
					'viewid' => $rs['viewid']
				);
			}
		}
		catch (Exception $e){
			throw new FrontendException($e->getMessage());
		}
		return $Data;
	}

	public function addPhrase ($name)
	{
		$result = $this->checkInsertingMostSearch($name);
		if ($result == NULL){
			$this->addPhraseAboutMostSearch($name);
		}
		else{
			$this->updatePhraseAboutMostSearch($result['idmostsearch'], $result['textcount']);
		}
	}

	public function checkInsertingMostSearch ($phrase)
	{
		$Data = Array();
		$sql = "SELECT MS.idmostsearch, MS.textcount 
					FROM mostsearch MS 
					WHERE MS.name= :phrase";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('phrase', $phrase);
		try{
			$stmt->execute();
			$rs = $stmt->fetch();
			if ($rs){
				$Data = Array(
					'idmostsearch' => $rs['idmostsearch'],
					'textcount' => $rs['textcount']
				);
			}
		}
		catch (Exception $e){
			throw new FrontendException($e->getMessage());
		}
		return $Data;
	}

	public function addPhraseAboutMostSearch ($name, $counter = 0)
	{
		$sql = 'INSERT INTO mostsearch (name, viewid)
				VALUES (:name, :viewid)';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('name', $name);
		$stmt->bindValue('viewid', Helper::getViewId());
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new FrontendException($e->getMessage());
		}
		return Db::getInstance()->lastInsertId();
	}

	public function updatePhraseAboutMostSearch ($id, $counter = 0)
	{
		$counter = $counter + 1;
		$sql = 'UPDATE mostsearch MS SET MS.textcount = :counter
					WHERE MS.idmostsearch = :id';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->bindValue('counter', $counter);
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new FrontendException($e->getMessage());
		}
	}

	public function doSearchQuery ($phrase)
	{
		$objResponse = new xajaxResponse();
		
		$phrase = str_replace('_', '', App::getModel('formprotection')->cropDangerousCode($phrase));
		
		$phrase = App::getModel('formprotection')->cropDangerousCode($phrase);
		
		if (strlen($phrase) > 0){
			$this->addPhrase($phrase);
			
			$url = $this->registry->router->generate('frontend.productsearch', true, Array(
				'action' => 'index',
				'param' => $phrase
			));
		}
		else{
			$url = $this->registry->router->generate('frontend.productsearch', true, Array(
				'action' => 'noresults',
				'param' => ''
			));
		}
		
		$objResponse->script("window.location.href = '{$url}'");
		return $objResponse;
	}
}
