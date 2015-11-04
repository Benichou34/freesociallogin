<?php
/*
* The MIT License (MIT)
*
* Copyright (c) 2015 Benichou
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*
*  @author    Benichou <benichou.software@gmail.com>
*  @copyright 2015 Benichou
*  @license   http://opensource.org/licenses/MIT  The MIT License (MIT)
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once(__DIR__.'/facebook/facebook.php');
include_once(__DIR__.'/google/google.php');

class FreeSocialLogin extends Module
{
	private $socialNetworkList = array();
	
	public function __construct()
	{
		$this->bootstrap = true;
		$this->name = 'freesociallogin';
		$this->tab = 'social_networks';
		$this->author = 'Benichou';
		$this->version = '1.0';

		parent::__construct();
		$this->displayName = $this->l('Free Social Login');
		$this->description = $this->l('Allows customers to login/signup using a Facebook or Google account');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

		// Here we add the supported social networks
		$this->socialNetworkList['facebook'] = new Facebook($this);
		$this->socialNetworkList['google'] = new GooglePlus($this);
	}

	public function install()
	{
		if (!parent::install()
		|| !$this->registerHook('header')                // displayHeader
		|| !$this->registerHook('top')                   // displayTop
		|| !$this->registerHook('displayNav')            // displayNav
		|| !$this->registerHook('leftColumn')            // displayLeftColumn
		|| !$this->registerHook('rightColumn')           // displayRightColumn
		|| !$this->registerHook('customerAccount')       // displayCustomerAccount
		|| !$this->registerHook('displayMyAccountBlock') // displayMyAccountBlock
		|| !$this->registerHook('adminCustomers')        // displayAdminCustomers
		|| !$this->registerHook('displaySocialLogin')
		|| !$this->registerHook('actionObjectCustomerDeleteAfter'))
			return false;

		foreach ($this->socialNetworkList as $key => $value)
		{
			if(!$value->install())
				return false;
		}

		Configuration::updateValue('FSL_CUSTOMER_CREATION_EMAIL', 1);
		Configuration::updateValue('FSL_CUSTOMER_NWSL', 1);
		Configuration::updateValue('FSL_CUSTOMER_OPTIN', 0);

		return true;
	}

	public function uninstall()
	{
		$this->unregisterHook('header');
		$this->unregisterHook('top');
		$this->unregisterHook('displayNav');
		$this->unregisterHook('leftColumn');
		$this->unregisterHook('rightColumn');
		$this->unregisterHook('customerAccount');
		$this->unregisterHook('displayMyAccountBlock');
		$this->unregisterHook('adminCustomers');
		$this->unregisterHook('displaySocialLogin');
		$this->unregisterHook('actionObjectCustomerDeleteAfter');

		Configuration::deleteByName('FSL_CUSTOMER_CREATION_EMAIL');
		Configuration::deleteByName('FSL_CUSTOMER_NWSL');
		Configuration::deleteByName('FSL_CUSTOMER_OPTIN');

		foreach ($this->socialNetworkList as $key => $value)
			!$value->uninstall();

		return parent::uninstall();
	}
	

	public function getContent()
	{
		// If form has been sent
		$output = '';

		if (Tools::isSubmit('submit'.$this->name))
		{
			foreach ($this->socialNetworkList as $key => $value)
				$output .= $value->getContent();

			Configuration::updateValue('FSL_CUSTOMER_CREATION_EMAIL', Tools::getValue('FSL_CUSTOMER_CREATION_EMAIL_on'));
			Configuration::updateValue('FSL_CUSTOMER_NWSL', Tools::getValue('FSL_CUSTOMER_NWSL_on'));
			Configuration::updateValue('FSL_CUSTOMER_OPTIN', Tools::getValue('FSL_CUSTOMER_OPTIN_on'));
			$output .= $this->displayConfirmation($this->l('General Settings updated successfully'));
		}

		return $output.$this->renderForm();
	}

	public function renderForm()
	{
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->submit_action = 'submit'.$this->name;

		$fields_forms = array();
		foreach ($this->socialNetworkList as $key => $value)
			$fields_forms[] = $value->renderForm($helper);

		$fields_forms[] = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('General settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'checkbox',
						'name' => 'FSL_CUSTOMER_CREATION_EMAIL',
						'desc' => $this->l('Send an email with summary of the account information (email, password) after registration.'),
						'values' => array(
							'query' => array(
								array(
										'id' => 'on',
									'name' => $this->l('Send an email after registration'),
									'val' => '1'
								),
							),
							'id' => 'id',
							'name' => 'name'
						)
					),
					array(
						'type' => 'checkbox',
						'name' => 'FSL_CUSTOMER_NWSL',
						'desc' => $this->l('New customers are automatically registered to newsletter.'),
						'values' => array(
							'query' => array(
								array(
									'id' => 'on',
									'name' => $this->l('Set newsletter registration'),
									'val' => '1'
								),
							),
							'id' => 'id',
							'name' => 'name'
						)
					),
					array(
						'type' => 'checkbox',
						'name' => 'FSL_CUSTOMER_OPTIN',
						'desc' => $this->l('New customers are automatically registered to receive offers from the store\'s partners.'),
						'values' => array(
							'query' => array(
								array(
									'id' => 'on',
									'name' => $this->l('Set opt-in registration'),
									'val' => '1'
								),
							),
							'id' => 'id',
							'name' => 'name'
						)
					)
				),
				'submit' => array(
					'title' => $this->l('Save')
				)
			)
		);

		// Load current value
		$helper->fields_value['FSL_CUSTOMER_CREATION_EMAIL_on'] = Configuration::get('FSL_CUSTOMER_CREATION_EMAIL');
		$helper->fields_value['FSL_CUSTOMER_NWSL_on'] = Configuration::get('FSL_CUSTOMER_NWSL');
		$helper->fields_value['FSL_CUSTOMER_OPTIN_on'] = Configuration::get('FSL_CUSTOMER_OPTIN');

		return $helper->generateForm($fields_forms);
	}
	
	// This source code come from processSubmitLogin in AuthControllerCore
	private function processLogin($customer)
	{
		if (!Validate::isLoadedObject($customer))
			FSLTools::returnError(Tools::displayError('Bad customer object.'));

		Hook::exec('actionBeforeAuthentication');
		$context = $this->context;

		$context->cookie->id_compare = isset($context->cookie->id_compare) ? $context->cookie->id_compare: CompareProduct::getIdCompareByIdCustomer($customer->id);
		$context->cookie->id_customer = (int)($customer->id);
		$context->cookie->customer_lastname = $customer->lastname;
		$context->cookie->customer_firstname = $customer->firstname;
		$context->cookie->logged = 1;
		$customer->logged = 1;
		$context->cookie->is_guest = $customer->isGuest();
		$context->cookie->passwd = $customer->passwd;
		$context->cookie->email = $customer->email;

		// Add customer to the context
		$context->customer = $customer;

		if(isset($context->cart))
		{
			if (Configuration::get('PS_CART_FOLLOWING') && (empty($context->cookie->id_cart) || Cart::getNbProducts($context->cookie->id_cart) == 0) && $id_cart = (int)Cart::lastNoneOrderedCart($context->customer->id))
				$context->cart = new Cart($id_cart);
			else
			{
				$id_carrier = (int)$context->cart->id_carrier;
				$context->cart->id_carrier = 0;
				$context->cart->setDeliveryOption(null);
				$context->cart->id_address_delivery = (int)Address::getFirstCustomerAddressId((int)($customer->id));
				$context->cart->id_address_invoice = (int)Address::getFirstCustomerAddressId((int)($customer->id));
			}
			$context->cart->id_customer = (int)$customer->id;
			$context->cart->secure_key = $customer->secure_key;

			if (isset($id_carrier) && $id_carrier && Configuration::get('PS_ORDER_PROCESS_TYPE'))
			{
				$delivery_option = array($context->cart->id_address_delivery => $id_carrier.',');
				$context->cart->setDeliveryOption($delivery_option);
			}

			$context->cart->save();
			$context->cookie->id_cart = (int)$context->cart->id;
			$context->cart->autosetProductAddress();
		}
		
		$context->cookie->write();

		Hook::exec('actionAuthentication');

		// Login information have changed, so we check if the cart rules still apply
		CartRule::autoRemoveFromCart($context);
		CartRule::autoAddToCart($context);
	}

	public function processSubmitLogin($provider)
	{
		$social_customer = $this->socialNetworkList[$provider]->processSubmitLogin();

		if(!$social_customer || !$social_customer->id_user)
			FSLTools::returnError(Tools::displayError('Invalid social account'));
			
		$customer = null;

		if($social_customer->id_customer)
		{
			// If social customer already exist, just login
			$customer = new Customer($social_customer->id_customer);
		}
		else if (Tools::getValue('createAccount') == 'on' || Tools::getValue('createAccount') == 'true' || Tools::getValue('createAccount') == '1')
		{
			if (Customer::customerExists($social_customer->email))
			{
				// Social customer not exist, but customer prestashop already exist. Update it.
				$customer = new Customer();
				$authentication = $customer->getByEmail($social_customer->email);
				if (isset($authentication->active) && !$authentication->active)
					FSLTools::returnError(Tools::displayError('Your account isn\'t available at this time, please contact us'));
				else if (!$authentication || !$customer->id)
					FSLTools::returnError(Tools::displayError('Authentication failed.'));
				else if($this->context->customer->isLogged() && $customer->id != $this->context->customer->id)
					FSLTools::returnError(Tools::displayError('Your current Prestashop account not corresponding to your Social account.'));
				else if(!$customer->birthday && $social_customer->birthday)
				{
					// Update customer if needed
					$customer->birthday = $social_customer->birthday;
					$customer->update();
				}
			}
			else
			{
 				// Create both social and prestashop customers.
				$customer = new Customer();
				$customer->id_shop = $this->context->shop->id;
				$customer->firstname = $social_customer->firstname;
				$customer->lastname = $social_customer->lastname;
				$customer->email = $social_customer->email;
				$customer->id_gender = $social_customer->id_gender;
				$customer->newsletter = (bool)Configuration::get('FSL_CUSTOMER_NWSL');
				$customer->optin = (bool)Configuration::get('FSL_CUSTOMER_OPTIN');

				$passwd = Tools::passwdGen();
				$customer->passwd = Tools::encrypt($passwd);

				if ($social_customer->birthday)
					$customer->birthday = $social_customer->birthday;

				if(!$customer->add())
					FSLTools::returnError(Tools::displayError('Error during account creation.'));

				if ($customer->newsletter)
					FSLTools::processCustomerNewsletter($customer);

				Hook::exec('actionCustomerAccountAdd', array(
					'_POST' => $_POST,
					'newCustomer' => $customer
				));

				if (!FSLTools::sendConfirmationMail($social_customer, $passwd))
					FSLTools::returnError(Tools::displayError('The email cannot be sent.'));
			}

			if($customer != null && $customer->id)
			{
				$social_customer->id_customer = $customer->id;
				$social_customer->id_shop = $customer->id_shop;
				$social_customer->add(); // Add social customer
			}
		}
		else
			FSLTools::returnAjax();

		if(!$this->context->customer->isLogged() && $customer != null)
			$this->processLogin($customer);

		if (($back = Tools::getValue('back')) && $back == Tools::secureReferrer($back))
			$redirect_url = html_entity_decode($back);
		// redirection: if cart is not empty : redirection to the cart
		if (isset(Context::getContext()->cart) && count(Context::getContext()->cart->getProducts(true)) > 0)
			$redirect_url = Context::getContext()->link->getPageLink('order'.($multi = (int)Tools::getValue('multi-shipping') ? '&multi-shipping='.$multi : ''));
		// else : redirection to the account
		else
			$redirect_url = Context::getContext()->link->getPageLink('my-account');
		FSLTools::returnAjax($redirect_url, $social_customer);
	}

	public function processSubmitRevoke($provider)
	{
		$social_customer = $this->socialNetworkList[$provider]->processSubmitRevoke();
		if (!Validate::isLoadedObject($social_customer))
			FSLTools::returnError(Tools::displayError('Bad customer object.'));

		$social_customer->delete();
		FSLTools::returnAjax(Context::getContext()->link->getPageLink('my-account'), $social_customer);
	}

	private function getSubTemplatePath($provider, $template)
	{
		if (!$template)
			return null;

		$tempPath = $this->name.'/'.$provider.'/views/templates/hook/'.$template;

		if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$tempPath))
			return _PS_THEME_DIR_.'modules/'.$tempPath;
		elseif (Tools::file_exists_cache(_PS_MODULE_DIR_.$tempPath))
			return _PS_MODULE_DIR_.$tempPath;
		else
			return null;
	}

	private function hookDefault($hook, $params, $template)
	{
		$subTemplates = array();

		foreach ($this->socialNetworkList as $key => $value)
		{
			$subTemplate = $this->getSubTemplatePath($key, $value->$hook($params));
			if(!!$subTemplate)
				$subTemplates[] = $subTemplate;
		}

		$this->context->smarty->assign(array(
			'sub_templates' => $subTemplates
		));
		
		return $this->display(__FILE__, $template);
	}

	public function hookHeader($params)
	{
		Media::addJsDef(array(
			'fsl_login_url' => $this->context->link->getModuleLink($this->name, 'login'),
			'fsl_revoke_url' => $this->context->link->getModuleLink($this->name, 'revoke'),
			'customer_logged' => $this->context->customer->isLogged()
		));

		$this->context->smarty->assign(array(
			'customer_logged' => $this->context->customer->isLogged()
		));

		foreach ($this->socialNetworkList as $key => $value)
			$value->hookHeader($params);

		$this->context->controller->addJs($this->_path.'views/js/login.js', 'all');
		$this->context->controller->addCss($this->_path.'views/css/login.css', 'all');
		$this->context->controller->addCss($this->_path.'views/css/bootstrap-social.css', 'all');
		$this->context->controller->addjQueryPlugin(array('fancybox' ));
	}

	public function hookTop($params)
	{
		return $this->hookDefault(__FUNCTION__, $params, 'views/templates/hook/hook_top.tpl');
	}

	public function hookDisplayNav($params)
	{
		return $this->hookDefault(__FUNCTION__, $params, 'views/templates/hook/hook_nav.tpl');
	}

	public function hookLeftColumn($params)
	{
		return $this->hookDefault(__FUNCTION__, $params, 'views/templates/hook/hook_left.tpl');
	}

	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}

	/**
	* Hook display on customer account page
	*/
	public function hookCustomerAccount($params)
	{
		return $this->hookDefault(__FUNCTION__, $params, 'views/templates/hook/my-account.tpl');
	}

	public function hookDisplayMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}

	/**
	* Hook display in tab AdminCustomers on BO
	*/
	public function hookAdminCustomers($params)
	{
		return $this->hookDefault(__FUNCTION__, $params, 'views/templates/hook/admin_customers.tpl');
	}

	public function hookDisplaySocialLogin($params)
	{
		return $this->hookDefault(__FUNCTION__, $params, 'views/templates/hook/hook_sl.tpl');
	}

	public function hookActionObjectCustomerDeleteAfter($params)
	{
		$customer_id = $params['object']->id;

		foreach ($this->socialNetworkList as $key => $value)
		{
			$social_customer = $value->newCustomer();
			if( $social_customer->getByCustomerId($customer_id))
				$social_customer->delete();
		}
	}
}
