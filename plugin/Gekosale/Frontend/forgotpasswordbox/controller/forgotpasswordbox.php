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
 * $Id: forgotpasswordbox.php 484 2011-09-07 11:42:04Z gekosale $
 */
namespace Gekosale;

use SimpleForm;

class ForgotPasswordBoxController extends Component\Controller\Box
{

	public function index ()
	{
		$form = new SimpleForm\Form(Array(
			'name' => 'forgotpassword',
			'action' => '',
			'method' => 'post'
		));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'email',
			'label' => _('TXT_EMAIL'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_EMAIL')),
				new SimpleForm\Rules\Email(_('ERR_WRONG_EMAIL'))
			)
		)));
		
		if ($form->Validate()){
			$formData = $form->getSubmitValues();
			$result = App::getModel('forgotpassword')->authProccess($formData['email']);
			
			if ($result > 0){
				
				$hash = App::getModel('forgotpassword')->generateLink($formData['email']);
				
				$link = $this->registry->router->generate('frontend.forgotpassword', true, Array(
					'action' => 'confirm',
					'param' => $hash
				));
				
				$this->registry->template->assign('link', $link);
				
				App::getModel('mailer')->sendEmail(Array(
					'template' => 'forgotPassword',
					'email' => Array(
						$formData['email']
					),
					'bcc' => false,
					'subject' => _('TXT_PASSWORD_FORGOT'),
					'viewid' => Helper::getViewId()
				));
				
				Session::setVolatileSendPassword(1, false);
				Session::setVolatileForgotPasswordError();
			}
			elseif ($result < 0){
				Session::setVolatileForgotPasswordError(2, false);
			}
			else{
				Session::setVolatileSendPassword();
				Session::setVolatileForgotPasswordError(1, false);
			}
		}
		
		$error = Session::getVolatileForgotPasswordError();
		if ($error[0] == 1){
			$this->registry->template->assign('emailerror', _('ERR_EMAIL_NO_EXIST'));
		}
		elseif ($error[0] == 2){
			$this->registry->template->assign('emailerror', _('TXT_BLOKED_USER'));
		}
		$sendPasswd = Session::getVolatileSendPassword();
		if ($sendPasswd[0] == 1){
			$this->registry->template->assign('sendPasswd', _('TXT_CHECK_PRIVATE_MAIL_WITH_NEW_PASSWD'));
		}
		$this->registry->template->assign('form', $form->getForm());
		return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}

	public function confirm ()
	{
		$result = App::getModel('forgotpassword')->validateLink($this->getParam());
		
		if ($result > 0){
			
			$form = new SimpleForm\Form(Array(
				'name' => 'forgotpassword',
				'action' => '',
				'method' => 'post'
			));
			
			$newPassword = $form->AddChild(new SimpleForm\Elements\Password(Array(
				'name' => 'newpassword',
				'label' => _('TXT_PASSWORD_NEW'),
				'rules' => Array(
					new SimpleForm\Rules\Required(_('ERR_EMPTY_PASSWORD')),
					new SimpleForm\Rules\MinLength(_('ERR_PASSWORD_NEW_INVALID'), 6)
				)
			)));
			
			$form->AddChild(new SimpleForm\Elements\Password(Array(
				'name' => 'confirmpassword',
				'label' => _('TXT_PASSWORD_REPEAT'),
				'rules' => Array(
					new SimpleForm\Rules\Required(_('ERR_EMPTY_CONFIRM_PASSWORD')),
					new SimpleForm\Rules\Compare(_('ERR_PASSWORDS_NOT_COMPATIBILE'), $newPassword)
				)
			)));
			
			if ($form->Validate()){
				$formData = $form->getSubmitValues();
				
				App::getModel('forgotpassword')->forgotPassword($result, $formData['newpassword']);
				Session::setActiveClientid($result);
				App::getModel('clientlogin')->checkClientGroup();
				App::getModel('client')->saveClientData();
				Session::setVolatilePasswordChanged(1, false);
				App::redirectUrl($this->registry->router->generate('frontend.clientsettings', true));
			}
			
			$this->registry->template->assign('form', $form->getForm());
			return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
		}
		else{
			return $this->registry->template->fetch($this->loadTemplate('error.tpl'));
		}
	}
}
