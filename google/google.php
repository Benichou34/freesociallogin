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

require_once(__DIR__.'/../classes/tools.php');
require_once(__DIR__.'/../classes/socialnetwork.php');
require_once(__DIR__.'/../vendor/google/apiclient/src/Google/autoload.php');
require_once(__DIR__.'/customer.php');

class GooglePlus implements SocialNetwork
{
	/** @var string Module web path (eg. '/shop/modules/modulename/') */
	protected $_path = null;

	/** @var Parent Module */
	protected $_module;

	/** @var Context */
	protected $context;

	/** @var Google client ID */
	protected $_client_id;

	/** @var Client secret key */
	protected $_client_secret;

	protected $_client_scope;

	/**
	 * Constructor
	 *
	 * @param string $module Parent module object
	 * @param Context $context
	 */
	public function __construct($module, Context $context = null)
	{
		// Load context
		$this->context = $context ? $context : Context::getContext();
		$this->_module = $module;
		$this->_path = $module->getPathUri().'google/';

		$this->_client_id = Configuration::get('FSL_GOOGLE_APPID');
		$this->_client_secret = Configuration::get('FSL_GOOGLE_APPKEY');
		$this->_client_scope = "profile email ".Configuration::get('FSL_GOOGLE_SCOPE');
	}

	public function install()
	{
		return GoogleCustomer::createDb('customer_google');
	}

	public function uninstall()
	{
		Configuration::deleteByName('FSL_GOOGLE_APPID');
		Configuration::deleteByName('FSL_GOOGLE_APPKEY');
		Configuration::deleteByName('FSL_GOOGLE_SCOPE');

		return GoogleCustomer::deleteDb('customer_google');
	}

	public function newCustomer()
	{
		return new GoogleCustomer();
	}

	public function hookHeader($params)
	{
		if(!$this->_client_id || !$this->_client_secret)
			return;

		Media::addJsDef(array(
			'google_appid' => $this->_client_id,
			'google_scope' => $this->_client_scope
		));

		$this->context->controller->addJs($this->_path.'views/js/login.js', 'all');
		$this->context->controller->addCss($this->_path.'views/css/login.css', 'all');
	}

	private function smartyAssignGoogleCustomer()
	{
		$social_customer = new GoogleCustomer();
		if(!$social_customer->getByCustomerId($this->context->customer->id))
			return false;

		$this->context->smarty->assign(array(
				'google_id' => $social_customer->id_user,
				'google_username' => $social_customer->username,
				'google_picture_url' => $social_customer->picture_url,
				'google_like' => $social_customer->like
		));

		return true;
	}	

	public function hookTop($params)
	{
		if(!$this->_client_id || !$this->_client_secret)
			return null;

		$this->smartyAssignGoogleCustomer();

		return 'hook_top.tpl';
	}

	public function hookDisplayNav($params)
	{
		if(!$this->_client_id || !$this->_client_secret)
			return null;

		$this->smartyAssignGoogleCustomer();

		return 'hook_nav.tpl';
	}

	public function hookLeftColumn($params)
	{
		if(!$this->_client_id || !$this->_client_secret)
			return null;

		$this->smartyAssignGoogleCustomer();

		return 'hook_left.tpl';
	}

	public function hookCustomerAccount($params)
	{
		if(!$this->_client_id || !$this->_client_secret)
			return null;

		$this->smartyAssignGoogleCustomer();

		return 'my-account.tpl';
	}

	public function hookAdminCustomers($params)
	{
		if(!$this->smartyAssignGoogleCustomer())
			return null;

		return 'admin_customers.tpl';
	}

	public function hookDisplaySocialLogin($params)
	{
		if(!$this->_client_id || !$this->_client_secret)
			return null;

		return 'hook_sl.tpl';
	}

	public function getContent()
	{
		$google_id = Tools::getValue('FSL_GOOGLE_APPID');
		$google_appkey = Tools::getValue('FSL_GOOGLE_APPKEY');

		if ($google_id && !$google_appkey)
			return $this->_module->displayError('Invalid Google App Key');

		Configuration::updateValue('FSL_GOOGLE_APPID', $google_id);
		Configuration::updateValue('FSL_GOOGLE_APPKEY', $google_appkey);
		Configuration::updateValue('FSL_GOOGLE_SCOPE', Tools::getValue('FSL_GOOGLE_SCOPE'));

		return $this->_module->displayConfirmation($this->_module->l('Google settings updated successfully'));
	}

	public function renderForm($helper)
	{
		// Init Fields form array
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->_module->l('Google'),
					'icon' => 'icon-google'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->_module->l('Google client ID'),
						'name' => 'FSL_GOOGLE_APPID',
						'size' => 40,
						'required' => false,
						'hint' => $this->_module->l('This information is available in your Google Developers Console.')
					),
					array(
						'type' => 'text',
						'label' => $this->_module->l('Google App Secret code'),
						'name' => 'FSL_GOOGLE_APPKEY',
						'size' => 40,
						'required' => false,
						'hint' => $this->_module->l('This information is available in your Google Developers Console.')
					)
				)
			)
		);

		// Load current value
		$helper->fields_value['FSL_GOOGLE_APPID'] = Configuration::get('FSL_GOOGLE_APPID');
		$helper->fields_value['FSL_GOOGLE_APPKEY'] = Configuration::get('FSL_GOOGLE_APPKEY');
		$helper->fields_value['FSL_GOOGLE_SCOPE'] = Configuration::get('FSL_GOOGLE_SCOPE');

		return $fields_form;
	}

	private function initClient($accessToken)
	{
		$googleClient = new Google_Client();

		try
		{
			$access_token = json_decode($accessToken, true);
			$access_token['created'] = time();

			$googleClient->setClientId($this->_client_id);
			$googleClient->setClientSecret($this->_client_secret);
			$googleClient->setAccessToken(json_encode($access_token));
		}
		catch(Google_Exception $e)
		{
			// When validation fails or other local issues
			FSLTools::returnError(sprintf(Tools::displayError('Google SDK returned an error: %s'), $e->getMessage()));
		}

		return $googleClient;
	}

	public function processSubmitLogin()
	{
		$googleClient = $this->initClient(Tools::getValue('accessToken'));

		try
		{
			$service = new Google_Service_Oauth2($googleClient);
			$user = $service->userinfo_v2_me->get(); // Google_Service_Oauth2_Userinfoplus
 
			$social_customer = new GoogleCustomer();
			if(!$social_customer->getByUserId($user->getId()))
			{
				$social_customer->id_user = $user->getId();
				$social_customer->username = $user->getName();
				$social_customer->firstname = $user->getGivenName(); // first_name
				$social_customer->lastname = $user->getFamilyName(); // last_name
				$social_customer->email =  $user->getEmail();
				$social_customer->picture_url = $user->getPicture();

				if(!$social_customer->email)
				{
					// Revoke permissions
					$googleClient->revokeToken();
					FSLTools::returnError(Tools::displayError('Email authorization must be granted.'), true);
				}

				if($user->getGender())
					$social_customer->id_gender = ($user->getGender() == 'female')?2:1; // gender
			}

			return $social_customer;
		}
		catch(Google_Exception $e)
		{
			// When validation fails or other local issues
			FSLTools::returnError(sprintf(Tools::displayError('Google SDK returned an error: %s'), $e->getMessage()));
		}
	}

	public function processSubmitRevoke()
	{
		$social_customer = new GoogleCustomer();
		if(!$social_customer->getByUserId(Tools::getValue('user_id')))
			FSLTools::returnError(Tools::displayError('This Google user is not registered.'));

		$notAuthorized = Tools::getValue('notAuthorized');
		if (!($notAuthorized == 'on' || $notAuthorized == 'true' || $notAuthorized == '1'))
		{
			$googleClient = $this->initClient(Tools::getValue('accessToken'));
			try
			{
				$googleClient->revokeToken();
			}
			catch(Google_Exception $e)
			{
				// When validation fails or other local issues
				FSLTools::returnError(sprintf(Tools::displayError('Google SDK returned an error: %s'), $e->getMessage()));
			}
		}
			
		return $social_customer;
	}
}
