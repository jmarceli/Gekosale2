<?php

namespace Gekosale;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Loader_String;
use Twig_Filter_Function;
use Twig_Function_Function;
use Twig_Extensions_Extension_I18n;
use Twig_Extension_Optimizer;
use Twig_NodeVisitor_Optimizer;
// DEBUG ONLY
//use Twig_Extension_Debug;
//

class Template
{
	private static $translations = Array();
	private static $generator = NULL;
	private static $layerData;

	public function __construct ($registry, $mode)
	{
		$this->registry = $registry;
		$this->parameters = array();
		$this->themePath = $registry->loader->getParam('theme');
		$namespaces = array_reverse(array_unique($registry->loader->getNamespaces()));
		$designPaths = Array();
		
		if ($mode == 1){
			foreach ($namespaces as $namespace){
				if (! is_dir(ROOTPATH . 'design' . DS . 'admin' . DS . ucfirst($namespace))){
					mkdir(ROOTPATH . 'design' . DS . 'admin' . DS . ucfirst($namespace), 0755);
				}
				$designPaths[] = ROOTPATH . 'design' . DS . 'admin' . DS . ucfirst($namespace) . DS;
				$designPaths[] = ROOTPATH . 'themes' . DS;
			}
		}
		else{
			if (strlen($this->themePath) > 0 && is_dir(ROOTPATH . 'themes' . DS . $this->themePath . DS . 'templates' . DS)){
				$designPaths[] = ROOTPATH . 'themes' . DS . $this->themePath . DS . 'templates' . DS;
			}
			$designPaths[] = ROOTPATH . 'themes' . DS;
			$designPaths[] = ROOTPATH . 'design' . DS . 'frontend';
		}
		
		$this->template = new Twig_Environment(new Twig_Loader_Filesystem($designPaths), array(
			'cache' => ROOTPATH . 'cache' . DS,
			'auto_reload' => true,
			'autoescape' => false,
      // DEBUG ONLY
      //'debug' => true
      //
		));
		
		$this->template->addFilter('priceFormat', new Twig_Filter_Function('Gekosale\Template::priceFormat'));
		$this->template->addFunction('path', new Twig_Function_Function('Gekosale\Template::getPathFromRoute'));
		$this->template->addFunction('css_layout', new Twig_Function_Function('Gekosale\Template::getLayoutCSS'));
		$this->template->addFunction('css_namespace', new Twig_Function_Function('Gekosale\Template::getNamespaceCSS'));
		$this->template->addFunction('css_asset', new Twig_Function_Function('Gekosale\Template::getCSSAsset'));
		$this->template->addExtension(new Twig_Extensions_Extension_I18n());
    // DEBUG ONLY
    //$this->template->addExtension(new Twig_Extension_Debug());
    //
		$optimizer = new Twig_Extension_Optimizer(Twig_NodeVisitor_Optimizer::OPTIMIZE_ALL);
		$this->template->addExtension($optimizer);
	}

	public static function getPathFromRoute ($route, $params = Array())
	{
		if (NULL === self::$generator){
			self::$generator = App::getRegistry()->router->getGenerator();
		}
		return self::$generator->generate($route, $params, true);
	}

	public static function priceFormat ($price, $currency = Array())
	{
		if (NULL === self::$layerData){
			self::$layerData = App::getRegistry()->loader->getCurrentLayer();
		}
		
		if ($price < 0){
			return (self::$layerData['negativepreffix'] . number_format(abs($price), self::$layerData['decimalcount'], self::$layerData['decimalseparator'], self::$layerData['thousandseparator']) . self::$layerData['negativesuffix']);
		}
		return (self::$layerData['positivepreffix'] . number_format($price, self::$layerData['decimalcount'], self::$layerData['decimalseparator'], self::$layerData['thousandseparator']) . self::$layerData['positivesuffix']);
	}

	public static function getLayoutCSS ()
	{
		$viewid = Helper::getViewId();
		$namespaces = array_unique(App::getRegistry()->loader->getNamespaces());
		foreach ($namespaces as $namespace){
			if (file_exists(ROOTPATH . 'design' . DS . '_css_frontend' . DS . $namespace . DS . $viewid . '.css')){
				return DESIGNPATH . '_css_frontend' . '/' . $namespace . '/' . $viewid . '.css';
			}
		}
		return DESIGNPATH . '_css_frontend' . '/Gekosale/' . 'gekosale.css';
	}

	public static function getNamespaceCSS ($css_file, $mode)
	{
		switch ($mode) {
			case 'admin':
				$mode = '_css_panel';
				break;
			case 'frontend':
			default:
				$mode = '_css_frontend';
				break;
		}
		
		$namespace = App::getRegistry()->loader->getCurrentNamespace();
		if (file_exists(ROOTPATH . DS . 'design' . DS . $mode . DS . $namespace . DS . $css_file)){
			return DESIGNPATH . $mode . '/' . $namespace . '/' . $css_file;
		}
		return DESIGNPATH . $mode . '/core/' . $css_file;
	}

	public static function getCSSAsset ($css_file)
	{
		$theme = App::getRegistry()->loader->getParam('theme');
		return App::getURLForAssetDirectory() . $theme . '/assets/' . $css_file;
	}

	public function assign ($key, $value)
	{
		$this->parameters[$key] = $value;
	}

	public function display ($template)
	{
		$this->execTemplateEvent('display');
		
		return $this->template->display($template, $this->parameters);
	}

	public function fetch ($template)
	{
		$this->execTemplateEvent('fetch');
		
		return $this->template->render($template, $this->parameters);
	}

	public function parse ($content)
	{
		$twig = new Twig_Environment(new Twig_Loader_String());
		$twig->addFilter('priceFormat', new Twig_Filter_Function('Gekosale\Template::priceFormat'));
		$twig->addFunction('path', new Twig_Function_Function('Gekosale\Template::getPathFromRoute'));
		$twig->addExtension(new Twig_Extensions_Extension_I18n());
		return $twig->render($content, $this->parameters);
	}

	public function render ($parameters = array())
	{
		$this->execTemplateEvent('render');
		
		return $this->template->render(array_merge($this->parameters, $parameters));
	}

	public function setStaticTemplateVariables ()
	{
		if ($this->registry->router->getAdministrativeMode() == 0){
			$link = $this->_adminPane = '';
		}
		else{
			$link = $this->_adminPane = __ADMINPANE__ . '/';
		}
		$languageModel = App::getModel('language');
		$languages = $languageModel->getLanguages();
		
		$this->layer = $this->registry->loader->getCurrentLayer();
		
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'){
			$this->assign('SSLNAME', 'https://');
		}
		else{
			$this->assign('SSLNAME', 'http://');
		}
		
		$theme = App::getRegistry()->loader->getParam('theme');
		$this->assign('URL', App::getURLAdress() . $link);
		$this->assign('CURRENT_URL', App::getCurrentURLAdress());
		$this->assign('DESIGNPATH', DESIGNPATH);
		$this->assign('ASSETSPATH', App::getURLForAssetDirectory() . $theme . '/assets/');
		$this->assign('THEMESPATH', App::getURLForAssetDirectory());
		$this->assign('CURRENT_CONTROLLER', $this->registry->router->getCurrentController());
		$this->assign('CURRENT_ACTION', $this->registry->router->getAction());
		$this->assign('CURRENT_PARAM', $this->registry->core->getParam());
		
		$this->assign('SHOP_NAME', Session::getActiveShopName());
		
		$templateData = Array();
		
		if ($this->registry->router->getAdministrativeMode() == 0){
			$cartModel = App::getModel('cart');
			$client = App::getModel('client')->getClient();
			$productCart = $cartModel->getShortCartList();
			$productCart = $cartModel->getProductCartPhotos($productCart);
			$this->assign('SHOP_LOGO', $this->layer['photoid']);
			$this->assign('FAVICON', $this->layer['favicon']);
			$this->assign('enableregistration', $this->layer['enableregistration']);
			$this->assign('client', $client);
			$this->assign('clientdata', $client);
			$this->assign('showtax', $this->layer['showtax']);
			$this->assign('currencySymbol', Session::getActiveCurrencySymbol());
			$this->assign('count', $cartModel->getProductAllCount());
			$this->assign('globalPrice', $cartModel->getGlobalPrice());
			$this->assign('productCart', $productCart);
			$this->assign('language', Session::getActiveLanguageId());
			$this->assign('languageCode', Session::getActiveLanguage());
			$this->assign('languageFlag', $languageModel->getLanguages());
			$this->assign('currencies', $languageModel->getAllCurrenciesForView());
			$this->assign('breadcrumb', App::getModel('breadcrumb')->getPageLinks());
			$this->assign('contentcategory', App::getModel('staticcontent')->getContentCategoriesTree());
			$this->assign('defaultcontact', App::getModel('contact')->getContactById($this->layer['contactid']));
			$this->assign('newsletterButton', App::getModel('newsletter')->isNewsletterButton());
			$this->assign('cartpreview', $cartModel->getCartPreviewTemplate());
			if ($this->layer['cartredirect'] != ''){
				$this->assign('cartredirect', App::getURLAdress() . Seo::getSeo($this->layer['cartredirect']));
			}
			else{
				$this->assign('cartredirect', '');
			}
			$this->assign('modulesettings', $this->registry->core->getModuleSettingsForView());
			$message = Session::getVolatileMessage();
			if (isset($message) && ! empty($message)){
				$this->assign('message', $message[0]);
			}
			
			$this->assign('categories', App::getModel('CategoriesBox')->getCategoriesTree(2));
			
			$methods = $this->getXajaxMethodsForFrontend();
			foreach ($methods as $xajaxMethodName => $xajaxMethodParams){
				$this->registry->xajax->registerFunction(array(
					$xajaxMethodName,
					App::getModel($xajaxMethodParams['model']),
					$xajaxMethodParams['method']
				));
			}
			$message = Session::getVolatileMessage();
			if (isset($message) && ! empty($message)){
				$this->assign('message', $message[0]);
			}
			
			$this->assign('view', Helper::getViewId());
			$this->assign('viewid', Helper::getViewId());
		}
		else{
			
			$this->registry->core->setAdminStoreConfig();
			
			$templateData = Array(
				'user_name' => App::getModel('users')->getUserFullName(),
				'user_id' => App::getModel('users')->getActiveUserid(),
				'language' => Session::getActiveLanguageId(),
// 				'daysremaining' => Session::getActiveAccountDaysRemaining(),
				'languages' => json_encode($languages),
				'globalsettings' => Session::getActiveGlobalSettings(),
				'views' => App::getModel('view')->getViews(),
				'vatvalues' => json_encode(App::getModel('vat/vat')->getVATValuesAll()),
				'FRONTEND_URL' => (Session::getActiveShopUrl() != '') ? 'http://' . Session::getActiveShopUrl() : App::getURLAdress(),
				'appversion' => Session::getActiveAppVersion(),
				'view' => Helper::getViewId(),
				'viewid' => Helper::getViewId()
			);
			
			$message = Session::getVolatileMessage();
			if (isset($message) && ! empty($message)){
				$templateData['message'] = $message[0];
			}
			
			$methods = $this->getXajaxMethodsForAdmin();
			foreach ($methods as $xajaxMethodName => $xajaxMethodParams){
				$this->registry->xajax->registerFunction(array(
					$xajaxMethodName,
					App::getModel($xajaxMethodParams['model']),
					$xajaxMethodParams['method']
				));
			}
			
			$Data = Event::dispatch($this, 'template.setStaticTemplateVariables', Array(
				'data' => $templateData
			));
			
			foreach ($Data as $param => $value){
				$this->assign($param, $value);
			}
			
			$this->assign('view', Helper::getViewId());
			$this->assign('viewid', Helper::getViewId());
		}
	}

	protected function execTemplateEvent ($key, $data = null)
	{
		$eventHandles = array(
			sprintf('template.%s.%s.%s.%s', strtolower($this->registry->router->getModeName()), strtolower($this->registry->router->getCurrentController()), strtolower($this->registry->router->getAction()), $key),
			sprintf('template.%s', $key)
		);
		foreach ($eventHandles as $eventHandle){
			
			$data = Event::dispatch($this, $eventHandle, $data);
			
			foreach ($data as $param => $value){
				$this->assign($param, $value);
			}
		}
	}

	protected function getXajaxMethodsForFrontend ()
	{
		$Data = Array(
			'changeLanguage' => Array(
				'model' => 'language',
				'method' => 'changeAJAXLanguageAboutView'
			),
			'changeCurrency' => Array(
				'model' => 'language',
				'method' => 'changeAJAXCurrencyView'
			),
			'updateCartPreview' => Array(
				'model' => 'cart',
				'method' => 'updateCartPreview'
			),
			'deleteProductFromCart' => Array(
				'model' => 'cart',
				'method' => 'deleteAJAXProductFromCart'
			),
			'addNewsletter' => Array(
				'model' => 'newsletter',
				'method' => 'addAJAXClientAboutNewsletter'
			),
			'doQuickLogin' => Array(
				'model' => 'clientlogin',
				'method' => 'authProccessQuick'
			),
			'doQuickAddCart' => Array(
				'model' => 'cart',
				'method' => 'doQuickAddCart'
			),
			'doSearchQuery' => Array(
				'model' => 'productsearch',
				'method' => 'doSearchQuery'
			),
			'addProductToCart' => Array(
				'model' => 'cart',
				'method' => 'addAJAXProductToCart'
			),
		);
		return $Data;
	}

	protected function getXajaxMethodsForAdmin ()
	{
		$Data = Array(
			'ChangeInterfaceLanguage' => Array(
				'model' => 'language',
				'method' => 'changeLanguage'
			),
			'ChangeActiveView' => Array(
				'model' => 'view',
				'method' => 'changeActiveView'
			),
			'doAJAXCreateSeo' => Array(
				'model' => 'seo',
				'method' => 'doAJAXCreateSeo'
			)
		);
		
		return $Data;
	}
}
