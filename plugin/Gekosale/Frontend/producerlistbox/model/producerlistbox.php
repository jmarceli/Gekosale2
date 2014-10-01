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
 * $Revision: 6 $
 * $Author: gekosale $
 * $Date: 2011-03-27 21:01:27 +0200 (N, 27 mar 2011) $
 * $Id: productsincategorybox.php 6 2011-03-27 19:01:27Z gekosale $
 */
namespace Gekosale;

class ProducerListBoxModel extends Component\Model\Dataset
{

	public function initDataset ($dataset)
	{
    App::getModel('product')->productsDataset($dataset);
			
    $dataset->queryFrom = '
      productcategory PC
      LEFT JOIN category C ON PC.categoryid= C.idcategory
      LEFT JOIN viewcategory VC ON C.idcategory= VC.categoryid AND VC.viewid = :viewid
      LEFT JOIN product P ON PC.productid = P.idproduct
      LEFT JOIN productnew PN ON P.idproduct = PN.productid
    ' . $dataset->queryFrom;
		
		$dataset->setAdditionalWhere('
			P.producerid = :producer AND
			ROUND((P.sellprice + (P.sellprice * V.`value`)/100), 2) BETWEEN IF(:pricefrom > 0, :pricefrom, 0) AND IF( :priceto > 0, :priceto, 999999) AND
			P.enable = 1 AND 
			VC.viewid  = :viewid
		');
		
		$dataset->setGroupBy('
			P.idproduct 
		');
		
		$dataset->setSQLParams(Array(
			'producer' => 0,
			'pricefrom' => 0,
			'priceto' => 0,
		));
	}

	public function getProductDataset ()
	{
		return $this->getDataset()->getDatasetRecords();
	}

	public function getProducerBySeo ($seo)
	{
		$sql = "SELECT
					P.photoid,
					PT.producerid,
					PT.name,
					PT.seo,
					PT.description,
					PT.keyword_title,
					PT.keyword,
					PT.keyword_description
				FROM producertranslation PT
				LEFT JOIN producer P ON P.idproducer = PT.producerid
				WHERE PT.seo =:seo AND PT.languageid = :languageid";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('seo', $seo);
		try{
			$stmt->execute();
			$rs = $stmt->fetch();
		}
		catch (Exception $e){
			throw new FrontendException($e->getMessage());
		}
		$Data = Array();
		if ($rs){
			$Data = Array(
				'id' => $rs['producerid'],
				'name' => $rs['name'],
				'description' => $rs['description'],
				'seo' => $rs['seo'],
				'photo' => $this->getImagePath($rs['photoid']),
				'keyword_title' => ($rs['keyword_title'] == NULL || $rs['keyword_title'] == '') ? $rs['name'] : $rs['keyword_title'],
				'keyword' => $rs['keyword'],
				'keyword_description' => $rs['keyword_description']
			);
		}
		return $Data;
	}

	public function getImagePath ($id)
	{
		if ($id > 0){
			return App::getModel('gallery')->getImagePath(App::getModel('gallery')->getSmallImageById($id));
		}
	}
}
