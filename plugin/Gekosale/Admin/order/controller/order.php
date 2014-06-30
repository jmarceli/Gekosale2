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
 * $Id: order.php 619 2011-12-19 21:09:00Z gekosale $ 
 */
namespace Gekosale;

use FormEngine;
use sfEvent;

class OrderController extends Component\Controller\Admin
{

	public function __construct ($registry)
	{
		parent::__construct($registry);
		$this->model = App::getModel('order');
		$this->layer = $this->registry->loader->getCurrentLayer();
	}

	public function index ()
	{
		$this->registry->xajax->registerFunction(array(
			'doChangeOrderStatus',
			$this->model,
			'doAJAXChangeOrderStatus'
		));
		
		$this->registry->xajax->registerFunction(array(
			'doDeleteOrder',
			$this->model,
			'doAJAXDeleteOrder'
		));
		
		$this->registry->xajax->registerFunction(array(
			'LoadAllOrder',
			$this->model,
			'getOrderForAjax'
		));
		
		$this->registry->xajax->registerFunction(array(
			'GetClientSuggestions',
			$this->model,
			'getClientForAjax'
		));
		
		$this->renderLayout(Array(
			'datagrid_filter' => $this->model->getDatagridFilterData(),
			'order_statuses' => json_encode(App::getModel('orderstatus')->getOrderStatusToSelect())
		));
	}

	public function add ()
	{
		if (Helper::getViewId() == 0){
			App::redirect(__ADMINPANE__ . '/order/');
		}
		$currencyid = Session::getActiveShopCurrencyId();
		
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'order',
			'action' => '',
			'class' => 'editOrder',
			'method' => 'post'
		));
		
		$productsData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'products_data',
			'label' => _('TXT_EDIT_ORDER_ORDERED_PRODUCTS')
		)));
		
		$products = $productsData->AddChild(new FormEngine\Elements\OrderEditor(Array(
			'name' => 'products',
			'label' => _('TXT_EDIT_ORDER_ORDERED_PRODUCTS'),
			'advanced_editor' => true,
			'repeat_min' => 1,
			'repeat_max' => FormEngine\FE::INFINITE,
			'viewid' => Helper::getViewId(),
			'on_change' => 'OnProductListChanged'
		)));
		
		$clientData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'client_data',
			'label' => _('TXT_EDIT_ORDER_CLIENT')
		)));
		
		$client = $clientData->AddChild(new FormEngine\Elements\ClientSelect(Array(
			'name' => 'client',
			'label' => _('TXT_EDIT_ORDER_CLIENT'),
			'default' => (string) $this->registry->core->getParam()
		)));
		
		$addressData = $form->AddChild(new FormEngine\Elements\Columns(Array(
			'name' => 'address_data'
		)));
		
		$billingData = $addressData->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'billing_data',
			'label' => _('TXT_EDIT_ORDER_BILLING_DATA')
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'firstname',
			'label' => _('TXT_FIRSTNAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_FIRSTNAME'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'surname',
			'label' => _('TXT_SURNAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_SURNAME'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'street',
			'label' => _('TXT_STREET'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STREET'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'streetno',
			'label' => _('TXT_STREETNO'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STREETNO'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'placeno',
			'label' => _('TXT_PLACENO')
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'place',
			'label' => _('TXT_PLACE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PLACE'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'postcode',
			'label' => _('TXT_POSTCODE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_POSTCODE'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'companyname',
			'label' => _('TXT_COMPANYNAME')
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'nip',
			'label' => _('TXT_NIP')
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'phone',
			'label' => _('TXT_PHONE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PHONE')),
				new FormEngine\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'phone2',
			'label' => _('TXT_ADDITIONAL_PHONE'),
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'email',
			'label' => _('TXT_EMAIL'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_EMAIL'))
			)
		)));
		
		$shippingData = $addressData->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'shipping_data',
			'label' => _('TXT_EDIT_ORDER_SHIPPING_DATA')
		)));
		
		$copy = $shippingData->AddChild(new FormEngine\Elements\StaticText(Array(
			'text' => '<a href="#" id="copy" style="margin: 10px;line-height: 46px;">' . _('TXT_JS_ADDRESS_COPY_FROM') . '</a>'
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'firstname',
			'label' => _('TXT_FIRSTNAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_FIRSTNAME'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'surname',
			'label' => _('TXT_SURNAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_SURNAME'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'street',
			'label' => _('TXT_STREET'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STREET'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'streetno',
			'label' => _('TXT_STREETNO'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STREETNO'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'placeno',
			'label' => _('TXT_PLACENO')
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'place',
			'label' => _('TXT_PLACE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PLACE'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'postcode',
			'label' => _('TXT_POSTCODE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_POSTCODE'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'companyname',
			'label' => _('TXT_COMPANYNAME')
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'nip',
			'label' => _('TXT_NIP')
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'phone',
			'label' => _('TXT_PHONE')
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'phone2',
			'label' => _('TXT_ADDITIONAL_PHONE')
		)));		
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'email',
			'label' => _('TXT_EMAIL'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_EMAIL'))
			)
		)));
		
		$additionalData = $form->AddChild(new FormEngine\Elements\Columns(Array(
			'name' => 'additional_data'
		)));
		
		$paymentData = $additionalData->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'payment_data',
			'label' => _('TXT_EDIT_ORDER_PAYMENT_METHOD')
		)));
		
		$paymentData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'payment_method',
			'label' => _('TXT_EDIT_ORDER_PAYMENT_METHOD'),
			'options' => FormEngine\Option::Make($this->model->getPaymentmethodAllToSelect($this->registry->core->getParam()))
		)));
		
		$paymentData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'delivery_method',
			'label' => _('TXT_EDIT_ORDER_DELIVERY_METHOD'),
			'options' => FormEngine\Option::Make(App::getModel('dispatchmethod')->getDispatchmethodToSelect())
		)));
		
		$paymentData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'currency',
			'label' => _('TXT_KIND_OF_CURRENCY'),
			'default' => Session::getActiveCurrencySymbol()
		)));
		
		$summaryData = $additionalData->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'summary_data',
			'label' => _('TXT_VIEW_ORDER_SUMMARY')
		)));
		
		$summaryData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'total_net_total',
			'label' => _('TXT_NETTO_AMOUNT')
		)));
		
		$summaryData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'total_vat_value',
			'label' => _('TXT_VIEW_ORDER_TAX')
		)));
		
		$summaryData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'total_delivery',
			'label' => _('TXT_DELIVERERPRICE')
		)));
		
		$summaryData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'total_total',
			'label' => _('TXT_VIEW_ORDER_TOTAL')
		)));
		
		$form->AddFilter(new FormEngine\Filters\NoCode());
		$form->AddFilter(new FormEngine\Filters\Trim());
		$form->AddFilter(new FormEngine\Filters\Secure());
		
		if ($form->Validate(FormEngine\FE::SubmittedData())){
			try{
				$this->model->addNewOrder($_POST, $this->registry->core->getParam());
				App::redirect(__ADMINPANE__ . '/order/');
			}
			catch (Exception $e){
				$this->registry->template->assign('error', $e->getMessage());
			}
		}
		$this->registry->template->assign('viewid', Helper::getViewId());
		$this->registry->xajaxInterface->registerFunction(array(
			'CalculateDeliveryCost',
			$this->model,
			'calculateDeliveryCostAdd'
		));
		$this->registry->xajaxInterface->registerFunction(array(
			'GetDispatchMethodForPrice',
			$this->model,
			'getDispatchMethodForPriceForAjaxAdd'
		));
		
		$this->registry->template->assign('xajax', $this->registry->xajax->getJavascript());
		$this->registry->xajax->processRequest();
		$this->registry->template->assign('currencyid', Session::getActiveCurrencyId());
		$this->registry->template->assign('currencysymbol', Session::getActiveCurrencySymbol());
		$this->registry->template->assign('form', $form->Render());
		$this->registry->template->display($this->loadTemplate('add.tpl'));
	}

	public function edit ()
	{
		Event::dispatch($this, 'admin.order.checkPaymentStatus', Array(
			'id' => (int) $this->registry->core->getParam(),
		));

		$rawOrderData = $this->model->getOrderById($this->registry->core->getParam());
		if (isset($rawOrderData['currencyid']) && ! empty($rawOrderData['currencyid'])){
			$currencyid = $rawOrderData['currencyid'];
		}
		else{
			$currencyid = Session::getActiveShopCurrencyId();
		}
		try{
			$order = $this->model->getOrderById((int) $this->registry->core->getParam());
			$order['id'] = (int) $this->registry->core->getParam();
			$orderNotes = $this->model->getOrderNotes($order['id']);
			$clientOrderHistory = $this->model->getclientOrderHistory($order['clientid']);
		}
		catch (Exception $e){
			throw new CoreException($e->getMessage());
		}
		
		$addNotes = new FormEngine\Elements\Form(Array(
			'name' => 'add_notes',
			'class' => 'statusChange',
			'action' => '',
			'method' => 'post'
		));
		
		$addNotes->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'contents',
			'label' => _('TXT_CONTENT'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_CONTENT'))
			)
		)));
		
		$addNotes->AddChild(new FormEngine\Elements\Submit(Array(
			'name' => 'add',
			'label' => _('TXT_ADD'),
			'icon' => '_images_panel/icons/buttons/add.png'
		)));
		
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'order',
			'action' => '',
			'class' => 'editOrder',
			'method' => 'post'
		));
		
		$productsData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'products_data',
			'label' => _('TXT_EDIT_ORDER_ORDERED_PRODUCTS')
		)));
		
		$products = $productsData->AddChild(new FormEngine\Elements\OrderEditor(Array(
			'name' => 'products',
			'label' => _('TXT_EDIT_ORDER_ORDERED_PRODUCTS'),
			'advanced_editor' => true,
			'repeat_min' => 1,
			'repeat_max' => FormEngine\FE::INFINITE,
			'clientgroupid' => (int) $rawOrderData['clientgroupid'],
			'currencyid' => (int) $rawOrderData['currencyid'],
			'viewid' => $rawOrderData['viewid'],
			'on_change' => 'OnProductListChanged'
		)));
		
		$addressData = $form->AddChild(new FormEngine\Elements\Columns(Array(
			'name' => 'address_data'
		)));
		
		$billingData = $addressData->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'billing_data',
			'label' => _('TXT_EDIT_ORDER_BILLING_DATA')
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'firstname',
			'label' => _('TXT_FIRSTNAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_FIRSTNAME'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'surname',
			'label' => _('TXT_SURNAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_SURNAME'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'street',
			'label' => _('TXT_STREET'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STREET'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'streetno',
			'label' => _('TXT_STREETNO'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STREETNO'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'placeno',
			'label' => _('TXT_PLACENO')
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'place',
			'label' => _('TXT_PLACE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PLACE'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'postcode',
			'label' => _('TXT_POSTCODE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_POSTCODE'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'countryid',
			'label' => _('TXT_NAME_OF_COUNTRY'),
			'options' => FormEngine\Option::Make(App::getModel('countrieslist')->getCountryForSelect()),
			'default' => 0,
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME_OF_COUNTRY'))
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'companyname',
			'label' => _('TXT_COMPANYNAME')
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'nip',
			'label' => _('TXT_NIP')
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'phone',
			'label' => _('TXT_PHONE'),
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'phone2',
			'label' => _('TXT_ADDITIONAL_PHONE'),
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));		
		
		$billingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'email',
			'label' => _('TXT_EMAIL'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_EMAIL'))
			)
		)));
		
		$shippingData = $addressData->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'shipping_data',
			'label' => _('TXT_EDIT_ORDER_SHIPPING_DATA')
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'firstname',
			'label' => _('TXT_FIRSTNAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_FIRSTNAME'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'surname',
			'label' => _('TXT_SURNAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_SURNAME'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'street',
			'label' => _('TXT_STREET'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STREET'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'streetno',
			'label' => _('TXT_STREETNO'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_STREETNO'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'placeno',
			'label' => _('TXT_PLACENO')
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'place',
			'label' => _('TXT_PLACE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PLACE'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'postcode',
			'label' => _('TXT_POSTCODE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_POSTCODE'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'countryid',
			'label' => _('TXT_NAME_OF_COUNTRY'),
			'options' => FormEngine\Option::Make(App::getModel('countrieslist')->getCountryForSelect()),
			'default' => 0,
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME_OF_COUNTRY'))
			)
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'companyname',
			'label' => _('TXT_COMPANYNAME')
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'nip',
			'label' => _('TXT_NIP')
		)));
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'phone',
			'label' => _('TXT_PHONE'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_PHONE')),
				new FormEngine\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));

		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'phone2',
			'label' => _('TXT_ADDITIONAL_PHONE'),
			'rules' => Array(
				new FormEngine\Rules\Format(_('ERR_WRONG_FORMAT'), '/^[0-9 -+]+$/')
			)
		)));		
		
		$shippingData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'email',
			'label' => _('TXT_EMAIL'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_EMAIL'))
			)
		)));
		
		$additionalData = $form->AddChild(new FormEngine\Elements\Columns(Array(
			'name' => 'additional_data'
		)));
		
		$paymentData = $additionalData->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'payment_data',
			'label' => _('TXT_EDIT_ORDER_PAYMENT_METHOD')
		)));
		
		$paymentData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'payment_method',
			'label' => _('TXT_EDIT_ORDER_PAYMENT_METHOD'),
			'options' => FormEngine\Option::Make($this->registry->core->getDefaultValueToSelect() + $this->model->getPaymentmethodAllToSelect($this->registry->core->getParam()))
		)));
		
		$paymentData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'delivery_method',
			'label' => _('TXT_EDIT_ORDER_DELIVERY_METHOD'),
			'options' => FormEngine\Option::Make($this->model->getDispatchmethodAllToSelect($order['total'], $this->registry->core->getParam(), $currencyid))
		)));
		
		$paymentData->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'rules_cart',
			'label' => _('TXT_RULES_CART'),
			'options' => FormEngine\Option::Make($this->model->getAllRulesForOrder($this->registry->core->getParam()))
		)));
		
		$paymentData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'currency',
			'label' => _('TXT_KIND_OF_CURRENCY')
		)));
		
		$summaryData = $additionalData->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'summary_data',
			'label' => _('TXT_VIEW_ORDER_SUMMARY')
		)));
		
		$summaryData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'total_net_total',
			'label' => _('TXT_NETTO_AMOUNT')
		)));
		
		$summaryData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'total_vat_value',
			'label' => _('TXT_VIEW_ORDER_TAX')
		)));
		
		$summaryData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'total_delivery',
			'label' => _('TXT_DELIVERERPRICE')
		)));
		
		$summaryData->AddChild(new FormEngine\Elements\Constant(Array(
			'name' => 'total_total',
			'label' => _('TXT_VIEW_ORDER_TOTAL')
		)));
		
		$form->AddFilter(new FormEngine\Filters\NoCode());
		$form->AddFilter(new FormEngine\Filters\Trim());
		$form->AddFilter(new FormEngine\Filters\Secure());
		
		$orderData = Array(
			'address_data' => Array(
				'billing_data' => Array(
					'firstname' => $rawOrderData['billing_address']['firstname'],
					'surname' => $rawOrderData['billing_address']['surname'],
					'street' => $rawOrderData['billing_address']['street'],
					'streetno' => $rawOrderData['billing_address']['streetno'],
					'placeno' => $rawOrderData['billing_address']['placeno'],
					'place' => $rawOrderData['billing_address']['city'],
					'postcode' => $rawOrderData['billing_address']['postcode'],
					'countryid' => $rawOrderData['billing_address']['countryid'],
					'companyname' => $rawOrderData['billing_address']['companyname'],
					'nip' => $rawOrderData['billing_address']['nip'],
					'phone' => $rawOrderData['billing_address']['phone'],
					'phone2' => $rawOrderData['billing_address']['phone2'],
					'email' => $rawOrderData['billing_address']['email']
				),
				'shipping_data' => Array(
					'firstname' => $rawOrderData['delivery_address']['firstname'],
					'surname' => $rawOrderData['delivery_address']['surname'],
					'street' => $rawOrderData['delivery_address']['street'],
					'streetno' => $rawOrderData['delivery_address']['streetno'],
					'placeno' => $rawOrderData['delivery_address']['placeno'],
					'place' => $rawOrderData['delivery_address']['city'],
					'postcode' => $rawOrderData['delivery_address']['postcode'],
					'countryid' => $rawOrderData['delivery_address']['countryid'],
					'companyname' => $rawOrderData['delivery_address']['companyname'],
					'nip' => $rawOrderData['delivery_address']['nip'],
					'phone' => $rawOrderData['delivery_address']['phone'],
					'phone2' => $rawOrderData['billing_address']['phone2'],					
					'email' => $rawOrderData['delivery_address']['email']
				)
			),
			'additional_data' => Array(
				'payment_data' => Array(
					'delivery_method' => $rawOrderData['delivery_method']['dispatchmethodid'],
					'payment_method' => $rawOrderData['payment_method']['paymentmethodid'],
					'rules_cart' => $rawOrderData['rulescartid'],
					'currency' => $rawOrderData['currencysymbol'],
				),
				'summary_data' => Array(
					'total_net_total' => 132,
				)
			),
			'products_data' => Array(
				'products' => $this->model->getProductsDataGrid((int) $this->registry->core->getParam())
			)
		);
		
		$form->Populate($orderData);
		
		$statusChange = new FormEngine\Elements\Form(Array(
			'name' => 'add_status_change',
			'class' => 'statusChange',
			'action' => '',
			'method' => 'post'
		));
		
		$idstatus = $statusChange->AddChild(new FormEngine\Elements\Select(Array(
			'name' => 'status',
			'label' => _('TXT_VIEW_ORDER_CHANGE_STATUS'),
			'options' => FormEngine\Option::Make(App::getModel('orderstatus')->getOrderStatusToSelect())
		)));
		
		$statusChange->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'comment',
			'label' => _('TXT_VIEW_ORDER_CHANGE_COMMENT'),
			'dependencies' => Array(
				new FormEngine\Dependency(FormEngine\Dependency::SUGGEST, $idstatus, Array(
					App::getModel('orderstatus'),
					'getDefaultComment'
				))
			)
		)));
		
		$statusChange->AddChild(new FormEngine\Elements\Checkbox(Array(
			'name' => 'inform',
			'label' => _('TXT_VIEW_ORDER_CHANGE_INFORM_CLIENT')
		)));
		
		$statusChange->AddChild(new FormEngine\Elements\Submit(Array(
			'name' => 'update',
			'label' => _('TXT_VIEW_ORDER_CHANGE_UPDATE'),
			'icon' => '_images_panel/icons/buttons/flag-green.png'
		)));
		
		$statusChange->Populate(Array(
			'status' => $order['current_status_id']
		));
		
		$form->AddFilter(new FormEngine\Filters\Trim());
		$form->AddFilter(new FormEngine\Filters\Secure());
		
		$form->AddFilter(new FormEngine\Filters\Trim());
		$form->AddFilter(new FormEngine\Filters\Secure());
		
		if ($addNotes->Validate(FormEngine\FE::SubmittedData())){
			try{
				$notes = $addNotes->getSubmitValues(FormEngine\Elements\Form::FORMAT_FLAT);
				$this->model->addOrderNotes($addNotes->getSubmitValues(FormEngine\Elements\Form::FORMAT_FLAT), $order['id']);
				Session::setVolatileMessage("Dodano notatkę do zamówienia {$this->id}");
				App::redirect(__ADMINPANE__ . '/order/edit/' . (int) $this->registry->core->getParam());
			}
			catch (Exception $e){
				$this->registry->template->assign('error', $e->getMessage());
			}
		}
		
		if ($statusChange->Validate(FormEngine\FE::SubmittedData())){
			$this->model->addOrderHistory($statusChange->getSubmitValues(FormEngine\Elements\Form::FORMAT_FLAT), $this->registry->core->getParam());
			try{
				$email = $statusChange->getSubmitValues(FormEngine\Elements\Form::FORMAT_FLAT);
				if ($email['inform'] == 1){
					$this->model->notifyUser($order, $email['status']);
				}
				Session::setVolatileMessage("Zaktualizowano status zamówienia {$this->id}");
				$this->model->updateOrderStatus($_POST, $this->registry->core->getParam());
				App::redirect(__ADMINPANE__ . '/order/edit/' . (int) $this->registry->core->getParam());
			}
			catch (Exception $e){
				$this->registry->template->assign('error', $e->getMessage());
			}
		}
		
		if ($form->Validate(FormEngine\FE::SubmittedData())){
			try{
				$this->model->updateOrderById($_POST, $this->registry->core->getParam());
				App::redirect(__ADMINPANE__ . '/order/');
			}
			catch (Exception $e){
				$this->registry->template->assign('error', $e->getMessage());
			}
		}
		$this->registry->template->assign('viewid', Helper::getViewId());
		$this->registry->xajaxInterface->registerFunction(array(
			'CalculateDeliveryCost',
			$this->model,
			'calculateDeliveryCostEdit'
		));
		$this->registry->xajaxInterface->registerFunction(array(
			'GetDispatchMethodForPrice',
			$this->model,
			'getDispatchMethodForPriceForAjaxEdit'
		));
		
		$this->registry->template->assign('xajax', $this->registry->xajax->getJavascript());
		$this->registry->xajax->processRequest();
		$this->registry->template->assign('statusChange', $statusChange->Render());
		$this->registry->template->assign('addNotes', $addNotes->Render());
		$this->registry->template->assign('orderNotes', $orderNotes);
		$this->registry->template->assign('clientOrderHistory', $clientOrderHistory);
		$this->registry->template->assign('order', $order);
		$this->registry->template->assign('currencyid', Session::getActiveCurrencyId());
		$this->registry->template->assign('currencysymbol', Session::getActiveCurrencySymbol());
		$this->registry->template->assign('form', $form->Render());
		$this->registry->template->display($this->loadTemplate('edit.tpl'));
	}

	public function confirm ()
	{
		$tpl = $this->loadTemplate('confirm.tpl');
		App::getModel('order')->getPrintableOrderById((int) $this->registry->core->getParam(), $tpl);
	}
}