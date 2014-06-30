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
 * $Id: productprintbox.php 438 2011-08-27 09:29:36Z gekosale $
 */

namespace Gekosale;

class ProductPrintController extends Component\Controller
{

	public function index ()
	{
		if (strlen($this->getParam()) == 0){
			App::redirectUrl($this->registry->router->generate('frontend.home', true));
		}
		$product = App::getModel('product/product')->getProductAndAttributesById((int) $this->registry->core->getParam());
		$range = App::getModel('product/product')->getRangeType((int) $this->registry->core->getParam());
		App::getModel('product/product')->getPhotos($product);
		
		if (empty($product)){
			App::redirectUrl($this->registry->router->generate('frontend.home', true));
		}
		
		$product['photo']['normal'][0] = 'design/' . str_replace(DESIGNPATH, '', $product['photo']['normal'][0]);
		
		$selectAttributes = App::getModel('product/product')->getProductAttributeGroups($product);
		$attset = App::getModel('product/product')->getProductVariant($product);
		$this->registry->template->assign('product', $product);
		$this->registry->template->assign('attributes', $selectAttributes);
		$this->registry->template->assign('attset', $attset);
		$this->registry->template->assign('ROOTPATH', ROOTPATH);
		$htmlcontent = $this->registry->template->fetch($this->loadTemplate('index.tpl'));
		$pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Gekosale');
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		$pdf->setHeaderFont(Array(
			PDF_FONT_NAME_MAIN,
			'',
			PDF_FONT_SIZE_MAIN
		));
		$pdf->setFooterFont(Array(
			PDF_FONT_NAME_DATA,
			'',
			PDF_FONT_SIZE_DATA
		));
		
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray(1);
		$pdf->SetFont('dejavusans', '', 10);
		$pdf->AddPage();
		$pdf->writeHTML($htmlcontent, true, 0, true, 0);
		@ob_clean();
		$pdf->Output($product['seo'], 'D');
	}
}
