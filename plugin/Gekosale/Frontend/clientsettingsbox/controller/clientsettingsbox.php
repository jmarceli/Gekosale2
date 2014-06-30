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
 * $Revision: 484 $
 * $Author: gekosale $
 * $Date: 2011-09-07 13:42:04 +0200 (Śr, 07 wrz 2011) $
 * $Id: clientsettingsbox.php 484 2011-09-07 11:42:04Z gekosale $
 */
namespace Gekosale;

use SimpleForm;

class ClientSettingsBoxController extends Component\Controller\Box
{

	public function __construct ($registry, $box)
	{
		parent::__construct($registry, $box);
		$this->model = App::getModel('client');
	}

	public function index ()
	{
		$formPass = new SimpleForm\Form(Array(
			'name' => 'changePassword',
			'action' => '',
			'method' => 'post'
		));
		
		$oldPassword = $formPass->AddChild(new SimpleForm\Elements\Password(Array(
			'name' => 'password',
			'label' => _('TXT_PASSWORD'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_PASSWORD'))
			)
		)));
		
		$newPassword = $formPass->AddChild(new SimpleForm\Elements\Password(Array(
			'name' => 'newpassword',
			'label' => _('TXT_PASSWORD_NEW'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_PASSWORD')),
				new SimpleForm\Rules\MinLength(_('ERR_PASSWORD_NEW_INVALID'), 6)
			)
		)));
		
		$formPass->AddChild(new SimpleForm\Elements\Password(Array(
			'name' => 'confirmpassword',
			'label' => _('TXT_PASSWORD_REPEAT'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_CONFIRM_PASSWORD')),
				new SimpleForm\Rules\Compare(_('ERR_PASSWORDS_NOT_COMPATIBILE'), $newPassword)
			)
		)));
		
		if ($formPass->Validate()){
			$formData = $formPass->getSubmitValues();
			$BaseTable = $this->model->getClientPass();
			$PostValidatePass = $formData['password'];
			
			$hash = new \PasswordHash\PasswordHash();
			if ($hash->CheckPassword($PostValidatePass, $BaseTable['password'])){
				$this->model->updateClientPass($formData['newpassword']);
				$email = Session::getActiveClientEmail();
				$this->registry->template->assign('PASS_NEW', $formData['newpassword']);
				
				App::getModel('mailer')->sendEmail(Array(
					'template' => 'editPassword',
					'email' => Array(
						$email
					),
					'bcc' => false,
					'subject' => _('TXT_PASSWORD_EDIT'),
					'viewid' => Helper::getViewId()
				));
				
				Session::setVolatileChangePassOk(1, false);
				App::redirectUrl($this->registry->router->generate('frontend.clientsettings', true));
			}
			else{
				Session::setVolatileOldPassError(1, false);
			}
		}
		
		$this->registry->template->assign('formPass', $formPass->getForm());
		
		$erroroldpass = Session::getVolatileOldPassError();
		if ($erroroldpass[0] == 1){
			$this->registry->template->assign('error', _('TXT_ERROR_OLD_PASSWORD'));
		}
		
		$changepassok = Session::getVolatileChangePassOk();
		if ($changepassok[0] == 1){
			$this->registry->template->assign('success', _('TXT_DATA_CHANGED_MAIL_SEND'));
		}
		
		$formUserEmail = new SimpleForm\Form(Array(
			'name' => 'changeEmail',
			'action' => '',
			'method' => 'post'
		));
		
		$newEmail = $formUserEmail->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'email',
			'label' => _('TXT_EMAIL'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_EMAIL')),
				new SimpleForm\Rules\Email(_('ERR_WRONG_EMAIL'))
			)
		)));
		
		$formUserEmail->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'phone',
			'label' => _('TXT_PHONE'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_PHONE')),
				new SimpleForm\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));
		
		$formUserEmail->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'phone2',
			'label' => _('TXT_ADDITIONAL_PHONE'),
			'rules' => Array(
				new SimpleForm\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));		
		
		$clientData = App::getModel('client')->getClient();
		
		$formUserEmail->Populate(Array(
			'email' => $clientData['email'],
			'phone' => $clientData['phone'],
			'phone2' => $clientData['phone2']
		));
		
		if ($formUserEmail->Validate()){
			$formData = $formUserEmail->getSubmitValues();
			$this->model->updateClientPhone($formData['phone'], $formData['phone2']);
			if ($clientData['email'] != $formData['email']){
				
				$result = $this->model->checkClientNewMail($formData);
				if ($result == 0){
					$changedMail = $this->model->updateClientEmail($formData);
					$changedLogin = $this->model->updateClientLogin($formData['email']);
					Session::killSession();
					App::redirectUrl($this->registry->router->generate('frontend.clientlogin', true, Array(
						'param' => 'changed'
					)));
				}
				else{
					Session::setVolatileUserEmailDuplicateError(1, false);
				}
			}
			else{
				Session::setVolatileUserSettingsSaved(1, false);
			}
			
			App::redirectUrl($this->registry->router->generate('frontend.clientsettings', true));
		}
		
		$registrationok = Session::getVolatileRegistrationOk();
		
		if ($registrationok[0] == 1){
			$this->registry->template->assign('registrationok', _('TXT_REGISTER_USER_OK'));
		}
		
		$errorMail = Session::getVolatileUserEmailDuplicateError();
		
		if ($errorMail[0] == 1){
			$this->registry->template->assign('duplicateMailError', _('ERR_DUPLICATE_EMAIL'));
		}
		
		$settingsChanged = Session::getVolatileUserSettingsSaved();
		
		if ($settingsChanged[0] == 1){
			$this->registry->template->assign('settingsSaved', _('TXT_SETTINGS_SAVED'));
		}
		
		$this->registry->template->assign('formEmail', $formUserEmail->getForm());
		
		$changedPasswd = Session::getVolatilePasswordChanged();
		if ($changedPasswd[0] == 1){
			$this->registry->template->assign('changedPasswd', _('TXT_PASSWORD_CHANGED'));
		}
		
		return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}
}
