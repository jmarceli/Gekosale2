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
 * $Id: searchresults.php 627 2012-01-20 22:05:57Z gekosale $
 */
namespace Gekosale;

class SearchResultsModel extends Component\Model\Dataset
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
			(LOWER(P.ean) LIKE :name OR LOWER(P.delivelercode) LIKE :name OR LOWER(PT.name) LIKE :name) AND
			P.enable = 1 AND
			IF(P.producerid IS NOT NULL, PV.viewid = :viewid, 1)
		');
		
		$dataset->setGroupBy('
			P.idproduct
		');
		
		$dataset->setSQLParams(Array(
			'name' => ''
		));
	}

	public function getProductDataset ()
	{
		return $this->getDataset()->getDatasetRecords();
	}

	public function addPhrase ($name)
	{
		try{
			if ($name != NULL){
				$result = $this->checkInsertingMostSearch($name);
				if ($result == NULL){
					$this->addPhraseAboutMostSearch($name);
				}
				else{
					$this->updatePhraseAboutMostSearch($result['idmostsearch'], $result['textcount']);
				}
			}
		}
		catch (Exception $fe){
			throw new FrontendException($e->getMessage());
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
}
