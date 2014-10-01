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
 * $Id: productbuyalsobox.php 438 2011-08-27 09:29:36Z gekosale $
 */
namespace Gekosale;

class ProductBuyAlsoBoxModel extends Component\Model\Dataset
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
      LEFT JOIN category C ON PC.categoryid= C.idcategory
      LEFT JOIN viewcategory VC ON PC.categoryid = VC.categoryid
      LEFT JOIN product P ON PC.productid = P.idproduct
      LEFT JOIN productnew PN ON P.idproduct = PN.productid
    ' . $dataset->queryFrom;

    $dataset->setAdditionalWhere('
			OP.qty IS NOT NULL AND 
			OP.name IS NOT NULL AND 
			P.idproduct IS NOT NULL AND 
			VC.viewid = :viewid AND 
			OP.orderid IN (:ids) AND 
			OP.productid != :productid AND
			P.enable = 1
		');
		
		$dataset->setGroupBy('
				P.idproduct
			');
		
		$dataset->setSQLParams(Array(
			'ids' => App::getModel('product')->getAlsoProduct((int) $this->registry->core->getParam()),
			'productid' => (int) $this->registry->core->getParam()
		));
	}

	public function getProductDataset ()
	{
		return $this->getDataset()->getDatasetRecords();
	}
}
?>
