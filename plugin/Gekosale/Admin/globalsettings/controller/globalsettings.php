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
 * $Revision: 111 $
 * $Author: gekosale $
 * $Date: 2011-05-06 21:54:00 +0200 (Pt, 06 maj 2011) $
 * $Id: store.php 111 2011-05-06 19:54:00Z gekosale $
 */

namespace Gekosale;
use FormEngine;
use sfEvent;

class GlobalsettingsController extends Component\Controller\Admin
{

	public function index ()
	{
		$Config = App::getConfig();

		$configData = Array(
			'config_data' => Array(
				'admin_panel_link' => __ADMINPANE__,
				'ssl' => (string) (isset($Config['ssl']) && $Config['ssl'] == 1) ? 1 : 0
			),
			'gallerysettings_data' => App::getModel('globalsettings')->getGallerySettings()
		);

		$settingsData = App::getModel('globalsettings')->getSettings();
		$colour = $settingsData['gallerysettings_data']['colour'];
		unset($settingsData['gallerysettings_data']);

		$settingsData['gallerysettings_data']['colour'] = array(
			'type' => 1,
			'start' => $colour
		);

		$this->formModel->setPopulateData(array_merge_recursive($configData, $settingsData));

		$form = $this->formModel->initForm();

		if ($form->Validate(FormEngine\FE::SubmittedData())){
			try{
				$Data = $form->getSubmitValues(FormEngine\Elements\Form::FORMAT_FLAT);
				$Settings = $form->getSubmitValues();
				Event::notify($this, 'admin.globalsettings.model.save', Array(
					'id' => 1,
					'data' => $Data
				));
				App::getModel('globalsettings')->updateGallerySettings($Data);
				App::getModel('globalsettings')->updateGlobalSettings($Settings['interface'], 'interface');
				App::getModel('globalsettings')->updateGlobalSettings(array('colour' => $Settings['gallerysettings_data']['colour']['start']), 'gallerysettings_data');
				Session::setActiveGlobalSettings(NULL);
				App::getModel('globalsettings')->configWriter($Data);
				if (__ADMINPANE__ != $Data['admin_panel_link']){
					Session::flush();
					App::redirect('');
				}
				else{
					$sUrl = __ADMINPANE__ . '/globalsettings';
					echo "<script>window.location.href({$sUrl});</script>";
				}
			}
			catch (Exception $e){
				$this->registry->template->assign('error', $e->getMessage());
			}

		}

		$this->renderLayout(array(
			'form' => $form->Render()
		));
	}

}