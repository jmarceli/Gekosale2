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
 * $Id: sitemap.php 619 2011-12-19 21:09:00Z gekosale $
 */

namespace Gekosale;

class sitemapModel extends Component\Model
{
	
	protected $_rawData;

	public function __construct ($registry, $modelFile)
	{
		parent::__construct($registry, $modelFile);
		$this->viewid = Helper::getViewId();
		$this->languageid = Helper::getLanguageId();
	}

	public function getCategories ($levels)
	{
		$sql = "SELECT 
				CONCAT(:url,:seo,'/',CT.seo) as loc,
				DATE_FORMAT(C.adddate,'%Y-%m-%d') as lastmod,
				COUNT(CP.`order`) AS levels
				FROM category C
				LEFT JOIN viewcategory VC ON VC.categoryid = C.idcategory
				LEFT JOIN categorypath CP ON C.idcategory = CP.categoryid
				LEFT JOIN categorytranslation CT ON C.idcategory = CT.categoryid AND CT.languageid = :languageid
				WHERE VC.viewid = :viewid
				GROUP BY CP.categoryid
				HAVING levels < :levels
				ORDER BY C.distinction ASC";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('url', URL);
		$stmt->bindValue('seo', Seo::getSeo('categorylist'));
		$stmt->bindValue('levels', $levels);
		$stmt->bindValue('viewid', $this->viewid);
		$stmt->bindValue('languageid', $this->languageid);
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'loc' => $rs['loc'],
				'lastmod' => $rs['lastmod']
			);
		}
		return $Data;
	}

	public function getProducts ($limit)
	{
		$limit = (int) $limit;
		if ($limit < 0) {
			$limit = 1000;
		}

		$sql = "SELECT 
				CONCAT(:url,:seo,'/',PT.seo) as loc,
				DATE_FORMAT(P.adddate,'%Y-%m-%d') as lastmod
				FROM product P
				LEFT JOIN producttranslation PT ON P.idproduct = PT.productid AND PT.languageid = :languageid
				LEFT JOIN productcategory PC ON PC.productid = P.idproduct
				LEFT JOIN viewcategory VC ON PC.categoryid = VC.categoryid 
				WHERE P.enable = 1 AND VC.viewid = :viewid
				GROUP BY P.idproduct LIMIT " . $limit;
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('url', URL);
		$stmt->bindValue('viewid', $this->viewid);
		$stmt->bindValue('seo', Seo::getSeo('productcart'));
		$stmt->bindValue('languageid', $this->languageid);
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'loc' => $rs['loc'],
				'lastmod' => $rs['lastmod']
			);
		}
		return $Data;
	}

	public function getNews ()
	{
		$sql = "SELECT 
				CONCAT(:url,:seo,'/',N.idnews,'/',NT.seo) as loc,
				DATE_FORMAT(N.adddate,'%Y-%m-%d') as lastmod
				FROM news N
				LEFT JOIN newstranslation NT ON N.idnews = NT.newsid AND NT.languageid = :languageid
				WHERE N.publish = 1
				GROUP BY N.idnews";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('url', URL);
		$stmt->bindValue('languageid', Helper::getLanguageId());
		$stmt->bindValue('seo', Seo::getSeo('news'));
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'loc' => $rs['loc'],
				'lastmod' => $rs['lastmod']
			);
		}
		return $Data;
	}

	public function getPages ()
	{
		$sql = "SELECT CONCAT(:url,:seo,'/',C.idcontentcategory) as loc,CT.name,
				DATE_FORMAT(C.adddate,'%Y-%m-%d') as lastmod
				FROM contentcategory C
				LEFT JOIN contentcategoryview CCV ON CCV.contentcategoryid = C.idcontentcategory
				LEFT JOIN contentcategorytranslation CT ON CT.contentcategoryid = C.idcontentcategory AND CT.languageid = :languageid
				WHERE CCV.viewid = :viewid ";

    $sql = "SELECT 
					C.idcontentcategory AS id, 
          CCT.name,					
          CCT.name,
          C.redirect_route,
          C.redirect,
          C.redirect_url,
          DATE_FORMAT(C.adddate,'%Y-%m-%d') as lastmod
				FROM contentcategory C
				LEFT JOIN contentcategoryview CCV ON CCV.contentcategoryid = C.idcontentcategory
				LEFT JOIN contentcategorytranslation CCT ON CCT.contentcategoryid = C.idcontentcategory AND CCT.languageid = :languageid
				WHERE CCV.viewid=:viewid";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('languageid', $this->languageid);
		$stmt->bindValue('viewid', $this->viewid);
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
      $category = array(
        'redirect' => $rs['redirect'], 
        'redirect_url' => $rs['redirect_url'], 
        'redirect_route' => $rs['redirect_route'],
				'id' => $rs['id'],
				'name' => $rs['name'],
        'lastmod' => $rs['lastmod'],
        'seo' => strtolower(Core::clearSeoUTF($rs['name'])));
      $category['loc'] = App::getModel('StaticContent')->generateUrl($category);
      array_push($Data, $category);
		}
		return $Data;
	}

	public function generateSitemap ($id)
	{
		
		$sql = "SELECT * 
				FROM sitemaps 
				WHERE idsitemaps = :id
				";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('id', $id);
		$stmt->execute();
		$rs = $stmt->fetch();
		
		if ($rs){
			
			$xml = new \SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
			
			if ($rs['publishforcategories']){
				$Categories = $this->getCategories(4);
				foreach ($Categories as $category){
					$node = $xml->addChild('url');
					$node->addChild('loc', $category['loc']);
					$node->addChild('lastmod', $category['lastmod']);
					$node->addChild('changefreq', 'weekly');
					$node->addChild('priority', $rs['priorityforcategories']);
				}
			}
			
			if ($rs['publishforproducts']){
				$Products = $this->getProducts(1000);
				foreach ($Products as $product){
					$node = $xml->addChild('url');
					$node->addChild('loc', $product['loc']);
					$node->addChild('lastmod', $product['lastmod']);
					$node->addChild('changefreq', 'weekly');
					$node->addChild('priority', $rs['priorityforproducts']);
				}
			}
			
			if ($rs['publishforproducers']){
			
			}
			
			if ($rs['publishfornews']){
				$News = $this->getNews();
				foreach ($News as $news){
					$node = $xml->addChild('url');
					$node->addChild('loc', $news['loc']);
					$node->addChild('lastmod', $news['lastmod']);
					$node->addChild('changefreq', 'weekly');
					$node->addChild('priority', $rs['priorityfornews']);
				}
			}
			
			if ($rs['publishforpages']){
				$Pages = $this->getPages();
				foreach ($Pages as $page){
					$node = $xml->addChild('url');
					$node->addChild('loc', $page['loc']);
					$node->addChild('lastmod', $page['lastmod']);
					$node->addChild('changefreq', 'weekly');
					$node->addChild('priority', $rs['priorityforpages']);
				}
			
			}
 		}
		else {
			App::redirectUrl($this->registry->router->generate('frontend.sitemap', true));
		}
		header('Content-type: text/xml; charset=utf-8');
		header('Cache-Control: max-age=0');
		$doc = new \DOMDocument('1.0', 'UTF-8');
		$doc->formatOutput = true;
		$domnode = dom_import_simplexml($xml);
		$domnode = $doc->importNode($domnode, true);
		$domnode = $doc->appendChild($domnode);
		echo $doc->saveXML();
	}

}
