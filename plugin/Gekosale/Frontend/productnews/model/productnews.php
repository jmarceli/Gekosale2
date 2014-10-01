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
 * $Id: productnews.php 619 2011-12-19 21:09:00Z gekosale $
 */
namespace Gekosale;

class ProductNewsModel extends Component\Model\Dataset
{

	public function initDataset ($dataset)
	{
    App::getModel('product')->productsDataset($dataset);

    $dataset->queryFrom = '
      product P
      INNER JOIN productnew PN ON PN.productid = P.idproduct AND (PN.startdate IS NULL OR PN.startdate < :today) AND (PN.enddate IS NULL OR PN.enddate >= :today) AND PN.active = 1
      INNER JOIN productcategory PC ON PC.productid = P.idproduct
      INNER JOIN viewcategory VC ON PC.categoryid = VC.categoryid AND VC.viewid = :viewid
    ' . $dataset->queryFrom;

		if ($this->registry->router->getCurrentController() == 'categorylist'){
			$dataset->setAdditionalWhere('
					P.enable = 1 AND
					CT.seo = :seo
				');

			$dataset->setSQLParams(Array(
				'seo' => $this->getParam()
			));
		}
		elseif ($this->registry->router->getCurrentController() == 'producerlist'){
			$producer = App::getModel('producerlistbox')->getProducerBySeo($this->getParam());

			$dataset->setAdditionalWhere('
					P.enable = 1 AND
					P.producerid = :id
				');

			$dataset->setSQLParams(Array(
				'id' => $producer['id']
			));
		}
		else{
			$dataset->setAdditionalWhere('
				P.enable = 1
			');
		}

		$dataset->setGroupBy('
			P.idproduct
		');
	}

	public function getProductDataset ()
	{
		return $this->getDataset('productnews')->getDatasetRecords();
	}
}
