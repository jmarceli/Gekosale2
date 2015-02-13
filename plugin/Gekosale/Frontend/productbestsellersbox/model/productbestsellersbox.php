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
 * $Revision: 616 $
 * $Author: gekosale $
 * $Date: 2011-12-05 10:13:27 +0100 (Pn, 05 gru 2011) $
 * $Id: productbestsellersbox.php 616 2011-12-05 09:13:27Z gekosale $
 */
namespace Gekosale;

class ProductBestsellersBoxModel extends Component\Model\Dataset
{

	public function initDataset ($dataset)
	{
    App::getModel('product')->productsDataset($dataset);
    $dataset->queryColumns['count'] = Array(
      'source' => 'SUM(DISTINCT OP.qty)'
    );

    $dataset->queryFrom = '
      orderproduct OP
      LEFT JOIN productcategory PC ON PC.productid = OP.productid
      LEFT JOIN viewcategory VC ON PC.categoryid = VC.categoryid
      LEFT JOIN product P ON PC.productid = P.idproduct
      LEFT JOIN productnew PN ON P.idproduct = PN.productid
    ' . $dataset->queryFrom;
		
		$dataset->setAdditionalWhere('
			P.enable = 1
			AND VC.viewid  = :viewid AND
			IF(P.producerid IS NOT NULL, PV.viewid = :viewid, 1)
		');
		
		$dataset->setGroupBy('
			P.idproduct
		');
	}

	public function getProductDataset ()
	{
		return $this->getDataset()->getDatasetRecords();
	}
}
