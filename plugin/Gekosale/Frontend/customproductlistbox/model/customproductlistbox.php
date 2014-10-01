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
 * $Id: productbestsellersbox.php 438 2011-08-27 09:29:36Z gekosale $
 */
namespace Gekosale;

class CustomProductListBoxModel extends Component\Model\Dataset
{

	public function initDataset ($dataset)
	{
    App::getModel('product')->productsDataset($dataset);

    $dataset->queryFrom = '
      product P
      LEFT JOIN orderproduct OP ON OP.productid = P.idproduct
      LEFT JOIN productcategory PC ON PC.productid = P.idproduct
      LEFT JOIN viewcategory VC ON PC.categoryid = VC.categoryid
      LEFT JOIN productnew PN ON P.idproduct = PN.productid
    ' . $dataset->queryFrom;

		$dataset->setAdditionalWhere('
			P.enable = 1 AND
			FIND_IN_SET(CAST(P.idproduct as CHAR), :ids) AND
			VC.viewid  = :viewid
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
