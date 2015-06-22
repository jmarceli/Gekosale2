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
 * $Revision: 612 $
 * $Author: gekosale $
 * $Date: 2011-11-28 21:02:10 +0100 (Pn, 28 lis 2011) $
 * $Id: cartbox.php 612 2011-11-28 20:02:10Z gekosale $
 */
namespace Gekosale;

use SimpleForm;
use xajaxResponse;

class CheckoutBoxController extends Component\Controller\Box
{

	public function __construct ($registry, $box)
	{
		parent::__construct($registry, $box);
		$this->clientModel = App::getModel('client');
	}

	public function index ()
	{
		$clientorder = App::getModel('finalization')->setClientOrder();
		
		if (empty($clientorder['cart'])){
			App::redirectUrl($this->registry->router->generate('frontend.cart', true));
		}
		
		if (Session::getActiveClientid() == NULL){
			
			$formLogin = App::getFormModel('clientlogin')->initForm();
			
			if ($formLogin->Validate()){
				$formLoginData = $formLogin->getSubmitValues();
				$result = App::getModel('clientlogin')->authProccess($formLoginData['login'], $formLoginData['password']);
				if ($result > 0){
					Session::setActiveClientid($result);
					App::getModel('clientlogin')->checkClientGroup();
					App::getModel('clientlogin')->setLoginTime();
					$this->clientModel->saveClientData();
					$misingCart = App::getModel('missingcart')->checkMissingCartForClient(Session::getActiveClientid());
					if (is_array($misingCart) && $misingCart != 0){
						App::getModel('cart')->addProductsToCartFromMissingCart($misingCart);
						App::getModel('missingcart')->cleanMissingCart(Session::getActiveClientid());
					}
					if (($this->Cart = Session::getActiveCart()) != NULL){
						App::redirectUrl($this->registry->router->generate('frontend.checkout', true));
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
			
			$error = Session::getVolatileUserLoginError();
			if ($error[0] == 1){
				$this->registry->template->assign('loginerror', _('ERR_BAD_LOGIN_OR_PASSWORD'));
			}
			elseif ($error[0] == 2){
				$this->registry->template->assign('loginerror', _('TXT_BLOKED_USER'));
			}
			$this->registry->template->assign('formLogin', $formLogin->getForm());
		}
		
		$form = new SimpleForm\Form(Array(
			'name' => 'order',
			'action' => '',
			'method' => 'post'
		));
		
		$billingClientType = $form->AddChild(new SimpleForm\Elements\Radio(Array(
			'name' => 'billing_clienttype',
			'label' => _('TXT_CLIENT_TYPE'),
			'options' => Array(
				'1' => _('TXT_INDIVIDUAL_CLIENT'),
				'2' => _('TXT_COMPANY_CLIENT')
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'billing_firstname',
			'label' => _('TXT_FIRSTNAME'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_FIRSTNAME'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'billing_surname',
			'label' => _('TXT_SURNAME'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_SURNAME'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'billing_companyname',
			'label' => _('TXT_COMPANYNAME'),
			'rules' => Array(
				new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_COMPANYNAME'), $billingClientType, new SimpleForm\Conditions\Equals('2'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'billing_nip',
			'label' => _('TXT_NIP'),
			'rules' => Array(
				new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_NIP'), $billingClientType, new SimpleForm\Conditions\Equals('2'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'billing_street',
			'label' => _('TXT_STREET'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_STREET'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'billing_streetno',
			'label' => _('TXT_STREETNO'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_STREETNO'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'billing_placeno',
			'label' => _('TXT_PLACENO')
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'billing_placename',
			'label' => _('TXT_PLACE'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_PLACE'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'billing_postcode',
			'label' => _('TXT_POSTCODE'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_POSTCODE'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\Select(Array(
			'name' => 'billing_country',
			'label' => _('TXT_NAME_OF_COUNTRY'),
			'options' => App::getModel('lists')->getCountryForSelect(),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_NAME_OF_COUNTRY'))
			)
		)));
		
		$otherAddress = $form->AddChild(new SimpleForm\Elements\Checkbox(Array(
			'name' => 'other_address',
			'label' => _('TXT_OTHER_DELIVERY_ADRESS'),
			'default' => 0
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'shipping_firstname',
			'label' => _('TXT_FIRSTNAME'),
			'rules' => Array(
				new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_FIRSTNAME'), $otherAddress, new SimpleForm\Conditions\Equals('1'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'shipping_surname',
			'label' => _('TXT_SURNAME'),
			'rules' => Array(
				new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_SURNAME'), $otherAddress, new SimpleForm\Conditions\Equals('1'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'shipping_companyname',
			'label' => _('TXT_COMPANYNAME')
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'shipping_street',
			'label' => _('TXT_STREET'),
			'rules' => Array(
				new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_STREET'), $otherAddress, new SimpleForm\Conditions\Equals('0'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'shipping_streetno',
			'label' => _('TXT_STREETNO'),
			'rules' => Array(
				new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_STREETNO'), $otherAddress, new SimpleForm\Conditions\Equals('0'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'shipping_placeno',
			'label' => _('TXT_PLACENO')
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'shipping_placename',
			'label' => _('TXT_PLACE'),
			'rules' => Array(
				new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_PLACE'), $otherAddress, new SimpleForm\Conditions\Equals('0'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'shipping_postcode',
			'label' => _('TXT_POSTCODE'),
			'rules' => Array(
				new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_POSTCODE'), $otherAddress, new SimpleForm\Conditions\Equals('0'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\Select(Array(
			'name' => 'shipping_country',
			'label' => _('TXT_NAME_OF_COUNTRY'),
			'options' => App::getModel('lists')->getCountryForSelect(),
			'rules' => Array(
				new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_NAME_OF_COUNTRY'), $otherAddress, new SimpleForm\Conditions\Equals('0'))
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'phone',
			'label' => _('TXT_PHONE'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_PHONE')),
				new SimpleForm\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'phone2',
			'label' => _('TXT_ADDITIONAL_PHONE'),
			'rules' => Array(
				new SimpleForm\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));		
		
		$form->AddChild(new SimpleForm\Elements\TextField(Array(
			'name' => 'email',
			'label' => _('TXT_EMAIL'),
			'rules' => Array(
				new SimpleForm\Rules\Required(_('ERR_EMPTY_EMAIL')),
				new SimpleForm\Rules\Email(_('ERR_WRONG_EMAIL'))
			)
		)));
		
		if ((int) Session::getActiveClientid() == 0){
			
			$createAccount = $form->AddChild(new SimpleForm\Elements\Checkbox(Array(
				'name' => 'create_account',
				'label' => _('TXT_CHECKOUT_CREATE_ACCOUNT'),
				'default' => 1
			)));
			
			$newPassword = $form->AddChild(new SimpleForm\Elements\Password(Array(
				'name' => 'password',
				'label' => _('TXT_PASSWORD'),
				'rules' => Array(
					new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_PASSWORD'), $createAccount, new SimpleForm\Conditions\Equals('1'))
				)
			)));
			
			$form->AddChild(new SimpleForm\Elements\Password(Array(
				'name' => 'confirmpassword',
				'label' => _('TXT_PASSWORD_REPEAT'),
				'rules' => Array(
					new SimpleForm\Rules\RequiredDependency(_('ERR_EMPTY_CONFIRM_PASSWORD'), $createAccount, new SimpleForm\Conditions\Equals('1')),
					new SimpleForm\Rules\Compare(_('ERR_PASSWORDS_NOT_COMPATIBILE'), $newPassword)
				)
			)));
			
			$form->AddChild(new SimpleForm\Elements\Checkbox(Array(
				'name' => 'confirmterms',
				'label' => sprintf(_('TXT_ACCEPT_TERMS_AND_POLICY_OF_PRIVATE'), App::getModel('staticcontent')->getConditionsLink(), Session::getActiveShopName()),
				'rules' => Array(
					new SimpleForm\Rules\Required(_('ERR_TERMS_NOT_AGREED'))
				),
				'default' => 0
			)));
			
			$form->AddChild(new SimpleForm\Elements\Checkbox(Array(
				'name' => 'newsletter',
				'label' => _('TXT_NEWSLETTER_SIGNUP'),
				'default' => 0
			)));
		}
		
		$clientData = $this->clientModel->getClient();
		$clientBillingAddress = $this->clientModel->getClientAddress(1);
		$clientShippingAddress = $this->clientModel->getClientAddress(0);
		
		$form->Populate(Array(
			'billing_clienttype' => $clientBillingAddress['clienttype'],
			'other_address' => 0,
			'create_account' => 1,
			'phone' => isset($clientData['phone']) ? $clientData['phone'] : '',
			'phone2' => isset($clientData['phone2']) ? $clientData['phone2'] : '',			
			'email' => isset($clientData['email']) ? $clientData['email'] : '',
			'billing_firstname' => $clientBillingAddress['firstname'],
			'billing_surname' => $clientBillingAddress['surname'],
			'billing_companyname' => $clientBillingAddress['companyname'],
			'billing_nip' => $clientBillingAddress['nip'],
			'billing_street' => $clientBillingAddress['street'],
			'billing_streetno' => $clientBillingAddress['streetno'],
			'billing_placeno' => $clientBillingAddress['placeno'],
			'billing_placename' => $clientBillingAddress['placename'],
			'billing_postcode' => $clientBillingAddress['postcode'],
			'billing_country' => $clientBillingAddress['countryid'],
			'shipping_firstname' => $clientShippingAddress['firstname'],
			'shipping_surname' => $clientShippingAddress['surname'],
			'shipping_companyname' => $clientShippingAddress['companyname'],
			'shipping_nip' => $clientShippingAddress['nip'],
			'shipping_street' => $clientShippingAddress['street'],
			'shipping_streetno' => $clientShippingAddress['streetno'],
			'shipping_placeno' => $clientShippingAddress['placeno'],
			'shipping_placename' => $clientShippingAddress['placename'],
			'shipping_postcode' => $clientShippingAddress['postcode'],
			'shipping_country' => $clientShippingAddress['countryid']
		));
		
		if ($form->Validate()){
			$formData = $form->getSubmitValues();
			
			$Data['clientaddress'] = Array(
				'firstname' => $formData['billing_firstname'],
				'surname' => $formData['billing_surname'],
				'companyname' => ($formData['billing_clienttype'] == 2) ? $formData['billing_companyname'] : '',
				'nip' => ($formData['billing_clienttype'] == 2) ? $formData['billing_nip'] : '',
				'street' => $formData['billing_street'],
				'streetno' => $formData['billing_streetno'],
				'placeno' => $formData['billing_placeno'],
				'placename' => $formData['billing_placename'],
				'postcode' => $formData['billing_postcode'],
				'countryid' => $formData['billing_country'],
				'clienttype' => $formData['billing_clienttype']
			);
			
			if (!empty($formData['other_address'])){
				$Data['deliveryAddress'] = Array(
					'firstname' => $formData['shipping_firstname'],
					'surname' => $formData['shipping_surname'],
					'companyname' => $formData['shipping_companyname'],
					'street' => $formData['shipping_street'],
					'streetno' => $formData['shipping_streetno'],
					'placeno' => $formData['shipping_placeno'],
					'placename' => $formData['shipping_placename'],
					'postcode' => $formData['shipping_postcode'],
					'countryid' => $formData['shipping_country']
				);
			}
			else{
				$Data['deliveryAddress'] = $Data['clientaddress'];
			}

			$recurMail = 0;
      if (!empty($formData['create_account'])) {
				$recurMail = $this->clientModel->checkClientNewMail($formData);
				if ($recurMail == 0){
					$clientData = $Data['clientaddress'];
					$clientData['email'] = $formData['email'];
					$clientData['password'] = $formData['password'];
					$clientData['newsletter'] = $formData['newsletter'];
					$clientData['phone'] = $formData['phone'];
					$clientData['phone2'] = $formData['phone2'];					
					$clientId = $this->clientModel->addNewClient($clientData);
					$result = App::getModel('clientlogin')->authProccess($formData['email'], $formData['password']);
					if ($result > 0){
						Session::setActiveClientid($result);
						App::getModel('clientlogin')->checkClientGroup();
						$this->clientModel->saveClientData();
						$this->clientModel->updateClientAddress($Data['clientaddress'], 1);
						$this->clientModel->updateClientAddress($Data['deliveryAddress'], 0);
					}
				}
				else {
					$result = App::getModel('clientlogin')->authProccess($formData['email'], $formData['password']);
					if ($result > 0){
						Session::setActiveClientid($result);
						App::getModel('clientlogin')->checkClientGroup();
						App::getModel('clientlogin')->setLoginTime();
						App::getModel('client')->saveClientData();
						$misingCart = App::getModel('missingcart')->checkMissingCartForClient($result);
						if (is_array($misingCart) && ! empty($misingCart)){
							App::getModel('cart')->addProductsToCartFromMissingCart($misingCart);
							App::getModel('missingcart')->cleanMissingCart(Session::getActiveClientid());
						}
						$recurMail = 0;
					}
					else {
						$recurMail = -1;
					}
				}
			}
      // add support for newsletter subscription without registration
      else if(!empty($formData['newsletter'])) {
        $newId = App::getModel('newsletter')->addClientAboutNewsletter($formData['email']);
        if ($newId > 0){
					App::getModel('newsletter')->changeNewsletterStatus($newId);
				}
      }

			if ((int) Session::getActiveClientid() > 0){
				$this->clientModel->updateClientAddress($Data['clientaddress'], 1);
				$this->clientModel->updateClientAddress($Data['deliveryAddress'], 0);
			}

			if ($recurMail == -1) {
				$this->registry->template->assign('error', 'Podany adres e-mail jest już przypisany do innego konta użytkownika. Proszę skorzystaj z opcji <a href="' . $this->registry->router->generate('frontend.forgotpassword', true) . '" style="font-size: inherit">przypomnienia hasła</a> jeśli chcesz odzyskać dostęp do konta.');
			}
			else if ($recurMail == 1) {
				$this->registry->template->assign('error', _('ERR_DUPLICATE_EMAIL'));
			}
			else if ( !$recurMail){
				Session::setActiveOrderClientAddress($Data['clientaddress']);
				Session::setActiveOrderDeliveryAddress($Data['deliveryAddress']);
				Session::setActiveOrderContactData(Array(
					'phone' => $formData['phone'],
					'phone2' => $formData['phone2'],
					'email' => $formData['email']
				));

				App::getModel('finalization')->setClientOrder();
				App::redirectUrl($this->registry->router->generate('frontend.finalization', true));
			}
		}
		
		$assignData = Array(
			'form' => $form->getForm()
		);
		
		foreach ($assignData as $key => $assign){
			$this->registry->template->assign($key, $assign);
		}
		
		if (Session::getActiveClientid() > 0){
			return $this->registry->template->fetch($this->loadTemplate('client.tpl'));
		}
		else{
			return $this->registry->template->fetch($this->loadTemplate('guest.tpl'));
		}
	}
}
