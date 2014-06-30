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
 * $Revision: 583 $
 * $Author: gekosale $
 * $Date: 2011-10-28 22:19:07 +0200 (Pt, 28 paź 2011) $
 * $Id: clientloginbox.php 583 2011-10-28 20:19:07Z gekosale $
 */

namespace Gekosale;
use SimpleForm;

class ClientLoginBoxController extends Component\Controller\Box
{

	public function __construct ($registry, $box)
	{
		parent::__construct($registry, $box);
		$this->layer = $this->registry->loader->getCurrentLayer();
		$this->model = App::getModel('client');
	}

	public function index ()
	{
		$form = App::getFormModel('clientlogin')->initForm();
		
		if ($form->Validate()){
			$formData = $form->getSubmitValues();
			$clean = App::getModel('formprotection')->cropDangerousCodeSubmitedValues($formData);
			if ($clean == true){
				$result = App::getModel('clientlogin')->authProccess($formData['login'], $formData['password']);
				if ($result > 0){
					Session::setActiveClientid($result);
					App::getModel('clientlogin')->checkClientGroup();
					App::getModel('clientlogin')->setLoginTime();
					$this->model->saveClientData();
					$misingCart = App::getModel('missingcart')->checkMissingCartForClient($result);
					
					if (isset($formData['autologin']) && $formData['autologin'] == 1) {
						App::getModel('clientlogin')->setAutologinKey($result);
					}
					else {
						App::getModel('clientlogin')->destroyAutologinKey();
					}
					
					if (is_array($misingCart) && !empty($misingCart)){
						App::getModel('cart')->addProductsToCartFromMissingCart($misingCart);
						App::getModel('missingcart')->cleanMissingCart(Session::getActiveClientid());
					}
					if (($this->Cart = Session::getActiveCart()) != NULL){
						App::redirectUrl($this->registry->router->generate('frontend.cart', true));
					}
					else{
						App::redirectUrl($this->registry->router->generate('frontend.home', true));
					}
				}
				elseif ($result < 0){
					Session::setVolatileUserLoginError(2, false);
				}
				else{
					Session::setVolatileUserLoginError(1, false);
				}
			}
		}
		
		if($this->getParam() == 'changed'){
			$this->registry->template->assign('clientChangedMail', _('TXT_LOGGOUT_CHANGED_EMAIL'));
		}
		$error = Session::getVolatileUserLoginError();
		if ($error[0] == 1){
			$this->registry->template->assign('loginerror', _('ERR_BAD_LOGIN_OR_PASSWORD'));
		}
		elseif ($error[0] == 2){
			$this->registry->template->assign('loginerror', _('TXT_BLOKED_USER'));
		}
		$this->registry->template->assign('form', $form->getForm());
		return $this->registry->template->fetch($this->loadTemplate('index.tpl'));
	}
}
