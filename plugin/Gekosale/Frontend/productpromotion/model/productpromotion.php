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
 * $Id: productpromotion.php 619 2011-12-19 21:09:00Z gekosale $
 */

namespace Gekosale;

class ProductPromotionModel extends Component\Model\Dataset
{
  public function initDataset ($dataset)
  {
    App::getModel('product')->productsDataset($dataset);

    $dataset->queryFrom = '
      productcategory PC
      LEFT JOIN viewcategory VC ON PC.categoryid = VC.categoryid
      LEFT JOIN product P ON PC.productid = P.idproduct
      LEFT JOIN productnew PN ON P.idproduct = PN.productid
    ' . $dataset->queryFrom;
    
			if ($this->registry->router->getCurrentController() == 'categorylist'){
				
				$dataset->setAdditionalWhere('
					IF(PGP.promotion = 1 AND IF(PGP.promotionstart IS NOT NULL, PGP.promotionstart <= CURDATE(), 1) AND IF(PGP.promotionend IS NOT NULL, PGP.promotionend >= CURDATE(), 1),
				 	PGP.discountprice IS NOT NULL,
				 	IF(PGP.groupprice IS NULL AND P.promotion = 1 AND IF(P.promotionstart IS NOT NULL, P.promotionstart <= CURDATE(), 1) AND IF(P.promotionend IS NOT NULL, P.promotionend >= CURDATE(), 1), P.discountprice IS NOT NULL, NULL)
					) AND
					VC.viewid = :viewid AND
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
					IF(PGP.promotion = 1 AND IF(PGP.promotionstart IS NOT NULL, PGP.promotionstart <= CURDATE(), 1) AND IF(PGP.promotionend IS NOT NULL, PGP.promotionend >= CURDATE(), 1),
				 	PGP.discountprice IS NOT NULL,
				 	IF(PGP.groupprice IS NULL AND P.promotion = 1 AND IF(P.promotionstart IS NOT NULL, P.promotionstart <= CURDATE(), 1) AND IF(P.promotionend IS NOT NULL, P.promotionend >= CURDATE(), 1), P.discountprice IS NOT NULL, NULL)
					) AND
					VC.viewid = :viewid AND
					P.enable = 1 AND
					P.producerid = :id
				');
				
				$dataset->setSQLParams(Array(
					'id' => $producer['id']
				));
			
			}
			else{
				$dataset->setAdditionalWhere('
				IF(PGP.promotion = 1 AND IF(PGP.promotionstart IS NOT NULL, PGP.promotionstart <= CURDATE(), 1) AND IF(PGP.promotionend IS NOT NULL, PGP.promotionend >= CURDATE(), 1),
				 	PGP.discountprice IS NOT NULL,
				 	IF(PGP.groupprice IS NULL AND P.promotion = 1 AND IF(P.promotionstart IS NOT NULL, P.promotionstart <= CURDATE(), 1) AND IF(P.promotionend IS NOT NULL, P.promotionend >= CURDATE(), 1), P.discountprice IS NOT NULL, NULL)
				) AND
				VC.viewid = :viewid AND
				P.enable = 1
			');
			}
		
		$dataset->setGroupBy('
			P.idproduct
		');
  }

	public function getProductDataset ()
	{
		return $this->getDataset('productpromotion')->getDatasetRecords();
	}

}
