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
 * $Id: product.php 627 2012-01-20 22:05:57Z gekosale $
 */
namespace Gekosale;

use xajaxResponse;

// Class for all methods shared across subpages
class ProductListModel extends Component\Model
{

  // Returns products for this list
  // @param string $model - name of the model
  // @param string $controller - name of the controller
  // @param array $currentParams - array with current route params
  public function getProductsTemplate ($model, $controller, $currentParams, $boxAttributes)
	{
		$this->dataset = App::getModel($model)->getDataset();
		if ($boxAttributes['productsCount'] > 0){
			$this->dataset->setPagination($boxAttributes['productsCount']);
		}

    if($this->registry->router->getCurrentController() == $controller) {
      // only for product news page use datagrid custom parameters

      $producer = (strlen($currentParams['producers']) > 0) ? array_filter(array_values(explode('_', $currentParams['producers']))) : Array();
      $attributes = array_filter((strlen($currentParams['attributes']) > 0) ? array_filter(array_values(explode('_', $currentParams['attributes']))) : Array());
      
      $Products = App::getModel('layerednavigationbox')->getProductsForAttributes(0, $attributes);

      $sqlParams = Array(
        'clientid' => Session::getActiveClientid(),
        'producer' => $producer,
        'pricefrom' => (float) $currentParams['priceFrom'],
        'priceto' => (float) $currentParams['priceTo'],
        'filterbyproducer' => (! empty($producer)) ? 1 : 0,
        'enablelayer' => (! empty($Products) && (count($attributes) > 0)) ? 1 : 0,
        'products' => $Products
      );
      if(isset($currentParams['categoryid'])) {
        $sqlParams['categoryid'] = $currentParams['categoryid'];
      }
      if(!empty($currentParams['name'])) {
        $sqlParams['name'] = $currentParams['name'];
      }

      $this->dataset->setSQLParams($sqlParams);
      $this->dataset->setCurrentPage($currentParams['currentPage']);
      if($currentParams['orderBy'] == 'default') { // get order from box settings
        $this->dataset->setOrderBy('name', $boxAttributes['orderBy']);
        $this->dataset->setOrderDir('asc', $boxAttributes['orderDir']);
      }
      else { // get order from params
        $this->dataset->setOrderBy('name', $currentParams['orderBy']);
        $this->dataset->setOrderDir('asc', $currentParams['orderDir']);
      }
    }
    else {
      $this->dataset->setCurrentPage(1);
			$this->dataset->setOrderBy('name', $boxAttributes['orderBy']);
			$this->dataset->setOrderDir('asc', $boxAttributes['orderDir']);
    }

		return App::getModel($model)->getProductDataset();
	}
  // Returns view switcher
  // @param string $controller - name of the controller
  // @param array $currentParams - array with current route params
	public function createViewSwitcher ($controller, $currentParams)
	{
    $currentView = $currentParams['viewType'];

		$viewTypes = Array(
			0 => _('TXT_VIEW_GRID'),
			1 => _('TXT_VIEW_LIST')
		);
		
		$switcher = Array();
		
    unset($currentParams['categoryid']);
		foreach ($viewTypes as $view => $label){
			
			$currentParams['viewType'] = $view;
			
			$switcher[] = Array(
				'link' => $this->registry->router->generate('frontend.' . $controller, true, $currentParams),
				'label' => $label,
				'type' => $view,
				'active' => ($currentView == $view) ? true : false
			);
		}
		
		return $switcher;
	}

  // Returns sorting
  // @param string $controller - name of the controller
  // @param array $currentParams - array with current route params
	public function createSorting ($controller, $currentParams)
	{
    unset($currentParams['categoryid']);
    $currentOrderBy = $currentParams['orderBy'];
    $currentOrderDir = $currentParams['orderDir'];

		$columns = Array(
			'name' => _('TXT_NAME'),
			'price' => _('TXT_PRICE'),
			'rating' => _('TXT_AVERAGE_OPINION'),
			'opinions' => _('TXT_OPINIONS_QTY'),
			'adddate' => _('TXT_ADDDATE')
		);
		
		$directions = Array(
			'asc' => _('TXT_ASC'),
			'desc' => _('TXT_DESC')
		);
		
		$sorting = Array();
		
		$currentParams['orderBy'] = 'default';
		$currentParams['orderDir'] = 'asc';
		
		$sorting[] = Array(
			'link' => $this->registry->router->generate('frontend.' . $controller, true, $currentParams),
			'label' => _('TXT_DEFAULT'),
			'active' => ($currentOrderBy == 'default' && $currentOrderDir == 'asc') ? true : false
		);
		
		foreach ($columns as $orderBy => $orderByLabel){
			foreach ($directions as $orderDir => $orderDirLabel){
				
				$currentParams['orderBy'] = $orderBy;
				$currentParams['orderDir'] = $orderDir;
				
				$sorting[] = Array(
					'link' => $this->registry->router->generate('frontend.' . $controller, true, $currentParams),
					'label' => $orderByLabel . ' - ' . $orderDirLabel,
					'active' => ($currentOrderBy == $orderBy && $currentOrderDir == $orderDir) ? true : false
				);
			}
		}
		
		return $sorting;
	}

  // Returns pagination links
  // @param string $controller - name of the controller
  // @param array $currentParams - array with current route params
  // @param int $totalPages - total number of pages
	public function createPaginationLinks ($controller, $currentParams, $totalPages)
	{
    unset($currentParams['categoryid']);
    $currentPage = $currentParams['currentPage'];

		$paginationLinks = Array();
		
		if ($totalPages > 1){
			
			$currentParams['currentPage'] = $currentPage - 1;
			
			$paginationLinks['previous'] = Array(
				'link' => ($currentPage > 1) ? $this->registry->router->generate('frontend.' . $controller, true, $currentParams) : '',
				'class' => ($currentPage > 1) ? 'previous' : 'previous disabled',
				'label' => _('TXT_PREVIOUS')
			);
		}
		
		foreach ($totalPages as $page){
			
			$currentParams['currentPage'] = $page;
			
			$paginationLinks[$page] = Array(
				'link' => $this->registry->router->generate('frontend.' . $controller, true, $currentParams),
				'class' => ($currentPage == $page) ? 'active' : '',
				'label' => $page
			);
		}
		
		if ($totalPages > 1){
			
			$currentParams['currentPage'] = $currentPage + 1;
			
			$paginationLinks['next'] = Array(
				'link' => ($currentPage < end($totalPages)) ? $this->registry->router->generate('frontend.' . $controller, true, $currentParams) : '',
				'class' => ($currentPage < end($totalPages)) ? 'next' : 'next disabled',
				'label' => _('TXT_NEXT')
			);
		}
		
		return $paginationLinks;
	}
}
