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
 * $Revision: 448 $
 * $Author: gekosale $
 * $Date: 2011-08-27 19:49:35 +0200 (So, 27 sie 2011) $
 * $Id: menu.php 448 2011-08-27 17:49:35Z gekosale $ 
 */
namespace Gekosale;

class MenuModel extends Component\Model
{

	protected function createMenu ()
	{
		$this->xmlParser = new XmlParser();
		$namespace = $this->registry->loader->getCurrentNamespace();
		if (is_file(ROOTPATH . 'config' . DS . $namespace . DS . 'admin_menu.xml')){
			$this->xmlParser->parseFast(ROOTPATH . 'config' . DS . $namespace . DS . 'admin_menu.xml');
		}
		else{
			$this->xmlParser->parseFast(ROOTPATH . 'config/admin_menu.xml');
		}
		$menuXML = $this->xmlParser->getValue('menu', false);
		$this->xmlParser->flush();
		$menu = Array();
		foreach ($menuXML->block as $block){
			if (is_object($block->element)){
				foreach ($block->element as $element){
					if ($this->registry->right->checkMenuPermission((string) $element->controller, 'index', App::getModel('users')->getLayerIdByViewId(Helper::getViewId())) !== false){
						if (is_object($element->subelement) && ! empty($element->subelement)){
							$sub = Array();
							$sort_subelement = Array();
							foreach ($element->subelement as $subelement){
								$sub[] = Array(
									'name' => _((string) $subelement->name),
									'link' => (string) $subelement->link,
									'sort_order' => (int) $subelement->sort_order,
									'controller' => (string) $subelement->controller
								);
								$sort_subelement[] = (int) $subelement->sort_order;
							}
						}
						else{
							$sub = Array();
							$sort_subelement = Array();
						}
						if (isset($element->subelement) && ! empty($element->subelement)){
							$elem[] = Array(
								'name' => _((string) $element->name),
								'link' => (string) $element->link,
								'sort_order' => (int) $element->sort_order,
								'controller' => (string) $element->controller,
								'subelement' => $sub
							);
							$sort_element[] = (int) $element->sort_order;
						}
						else{
							$elem[] = Array(
								'name' => _((string) $element->name),
								'link' => (string) $element->link,
								'sort_order' => (int) $element->sort_order,
								'controller' => (string) $element->controller
							);
							$sort_element[] = (int) $element->sort_order;
						}
					}
				}
				if (isset($elem)){
					if (isset($element->subelement) && ! empty($element->subelement)){
            array_multisort($sort_subelement, SORT_ASC, $sub);
						$menu[] = Array(
							'name' => _((string) $block->name),
							'link' => (string) $block->link,
							'icon' => (string) $block->icon,
							'sort_order' => (int) $block->sort_order,
							'elements' => $elem
						);
					}
					else{
            array_multisort($sort_element, SORT_ASC, $elem);
						$menu[] = Array(
							'name' => _((string) $block->name),
							'link' => (string) $block->link,
							'icon' => (string) $block->icon,
							'sort_order' => (int) $block->sort_order,
							'elements' => $elem
						);
					}
				}
				else{
					$menu[] = Array(
						'name' => _((string) $block->name),
						'link' => (string) $block->link,
						'icon' => (string) $block->icon,
						'sort_order' => (int) $block->sort_order
					);
				}
				
				$sort_block[] = (int) $block->sort_order;
				if (isset($elem)){
					unset($elem);
					unset($sort_element);
					unset($sub);
					unset($subelement);
					unset($sort_subelement);
				}
			}
		}
		array_multisort($sort_block, SORT_ASC, $menu);
		$Data = Array();
		foreach ($menu as $key => $val){
			$Data[] = $menu[$key];
		}
		
		Session::setActiveMenuData($Data);
	}

	public function getBlocks ($event, $request)
	{
// 		if (Session::getActiveMenuData() == NULL){
			$this->createMenu();
// 		}
		
		$event->setReturnValues(Array(
			'menu' => Session::getActiveMenuData()
		));
	}

	public function flushMenu ()
	{
		Session::setActiveMenuData(NULL);
	}
}
