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
 * $Revision: 438 $
 * $Author: gekosale $
 * $Date: 2011-08-27 11:29:36 +0200 (So, 27 sie 2011) $
 * $Id: showcasebox.php 438 2011-08-27 09:29:36Z gekosale $
 */
namespace Gekosale;

class ShowcaseBoxModel extends Component\Model\Dataset
{

	protected function initDataset ($dataset)
	{
    App::getModel('product')->productsDataset($dataset);
    $dataset->queryColumns['photo'] = Array(
      'source' => 'Photo.photoid',
      'processFunction' => Array(
        App::getModel('showcasebox'),
        'getImagePath'
      ),
    );

    $dataset->queryFrom = '
      product P
      LEFT JOIN productcategory PC ON PC.productid = P.idproduct
      LEFT JOIN viewcategory VC ON PC.categoryid = VC.categoryid
      LEFT JOIN productnew PN ON P.idproduct = PN.productid
      LEFT JOIN productstatuses PS ON P.idproduct = PS.productid
    ' . $dataset->queryFrom;
		
		$dataset->setAdditionalWhere('
			IF(:category > 0, PC.categoryid = :category, PC.categoryid > 0) AND 
			PS.productstatusid = :statusid AND 
			VC.viewid = :viewid AND
			P.enable = 1
		');
		
		$dataset->setGroupBy('
			P.idproduct
		');
	}

	public function getImagePath ($id)
	{
		$Image = App::getModel('gallery')->getNormalImageById($id);
		return App::getModel('gallery')->getImagePath($Image);
	}

	public function getProductDataset ()
	{
		return $this->getDataset('ShowcaseBox')->getDatasetRecords();
	}

	public function getCategories ($params)
	{
		$sql = 'SELECT
					PC.categoryid AS id,
					CT.name AS caption,
					CT.seo
				FROM productcategory PC
				LEFT JOIN product P ON PC.productid = P.idproduct
				LEFT JOIN categorytranslation CT ON PC.categoryid = CT.categoryid AND CT.languageid = :languageid
				LEFT JOIN viewcategory VC ON PC.categoryid = VC.categoryid
				LEFT JOIN productstatuses PS ON PC.productid = PS.productid
				WHERE VC.viewid = :viewid AND PS.productstatusid = :statusid AND P.enable = 1
				GROUP BY PC.categoryid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->bindValue('statusid', $params['statusId']);
		$stmt->execute();
		$Data = Array();
		$Data[] = Array(
			'id' => 0,
			'caption' => _('TXT_ALL')
		);
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'id' => $rs['id'],
				'caption' => $rs['caption'],
				'link' => $this->registry->router->generate('frontend.categorylist', true, Array(
					'param' => $rs['seo']
				))
			);
		}
		return $Data;
	}
}
