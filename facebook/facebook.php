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
require_once(__DIR__.'/../vendor/facebook/php-sdk-v4/src/Facebook/autoload.php');
require_once(__DIR__.'/customer.php');

class Facebook implements SocialNetwork
{
	/** @var string Module web path (eg. '/shop/modules/modulename/') */
	protected $_path = null;

	/** @var Parent Module */
	protected $_module;

	/** @var Context */
	protected $context;

	/** @var Facebook client ID */
	protected $_appid;

	/** @var Client secret key */
	protected $_appkey;

	protected $_appscope;

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
		$this->_path = $module->getPathUri().'facebook/';
	
		$this->_appid = Configuration::get('FSL_FACEBOOK_APPID');
		$this->_appkey = Configuration::get('FSL_FACEBOOK_APPKEY');
		$this->_appscope = "public_profile,email,".Configuration::get('FSL_FACEBOOK_SCOPE');
	}

	public function install()
	{
		return FacebookCustomer::createDb('customer_facebook');
	}

	public function uninstall()
	{
		Configuration::deleteByName('FSL_FACEBOOK_APPID');
		Configuration::deleteByName('FSL_FACEBOOK_APPKEY');
		Configuration::deleteByName('FSL_FACEBOOK_SCOPE');

		return FacebookCustomer::deleteDb('customer_facebook');
	}

	public function newCustomer()
	{
		return new FacebookCustomer();
	}

	public function hookHeader($params)
	{
		if (!$this->_appid || !$this->_appkey)
			return;

		Media::addJsDef(array(
			'facebook_appid' => $this->_appid,
			'facebook_scope' => $this->_appscope
		));

		$this->context->controller->addJs($this->_path.'views/js/login.js', 'all');
		$this->context->controller->addCss($this->_path.'views/css/login.css', 'all');
	}

	private function smartyAssignFacebookCustomer()
	{
		$social_customer = new FacebookCustomer();
		if(!$social_customer->getByCustomerId($this->context->customer->id))
			return false;

		$this->context->smarty->assign(array(
				'facebook_id' => $social_customer->id_user,
				'facebook_username' => $social_customer->username,
				'facebook_picture_url' => $social_customer->picture_url,
				'facebook_like' => $social_customer->like
		));

		return true;
	}	

	public function hookTop($params)
	{
		if (!$this->_appid || !$this->_appkey)
			return null;

		$this->smartyAssignFacebookCustomer();

		return 'hook_top.tpl';
	}

	public function hookDisplayNav($params)
	{
		if (!$this->_appid || !$this->_appkey)
			return null;

		$this->smartyAssignFacebookCustomer();

		return 'hook_nav.tpl';
	}

	public function hookLeftColumn($params)
	{
		if (!$this->_appid || !$this->_appkey)
			return null;

		$this->smartyAssignFacebookCustomer();

		return 'hook_left.tpl';
	}

	public function hookCustomerAccount($params)
	{
		if (!$this->_appid || !$this->_appkey)
			return null;

		$this->smartyAssignFacebookCustomer();

		return 'my-account.tpl';
	}

	public function hookAdminCustomers($params)
	{
		if(!$this->smartyAssignFacebookCustomer())
			return null;

		return 'admin_customers.tpl';
	}

	public function hookDisplaySocialLogin($params)
	{
		if (!$this->_appid || !$this->_appkey)
			return null;

		return 'hook_sl.tpl';
	}

	public function getContent()
	{
		$facebook_id = Tools::getValue('FSL_FACEBOOK_APPID');
		$facebook_appkey = Tools::getValue('FSL_FACEBOOK_APPKEY');

		if ($facebook_id && !$facebook_appkey)
			return $this->_module->displayError('Invalid Facebook App Key');

		Configuration::updateValue('FSL_FACEBOOK_APPID', $facebook_id);
		Configuration::updateValue('FSL_FACEBOOK_APPKEY', $facebook_appkey);
		Configuration::updateValue('FSL_FACEBOOK_SCOPE', Tools::getValue('FSL_FACEBOOK_SCOPE'));

		return $this->_module->displayConfirmation($this->_module->l('Facebook settings updated successfully'));
	}

	public function renderForm($helper)
	{
		// Init Fields form array
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->_module->l('Facebook'),
					'icon' => 'icon-facebook'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->_module->l('Facebook App ID'),
						'name' => 'FSL_FACEBOOK_APPID',
						'size' => 40,
						'required' => false,
						'hint' => $this->_module->l('This information is available in your Facebook Developers Dashboard.')
					),
					array(
						'type' => 'text',
						'label' => $this->_module->l('Facebook App Secret'),
						'name' => 'FSL_FACEBOOK_APPKEY',
						'size' => 40,
						'required' => false,
						'hint' => $this->_module->l('This information is available in your Facebook Developers Dashboard.')
					)
				)
			)
		);

		// Load current value
		$helper->fields_value['FSL_FACEBOOK_APPID'] = Configuration::get('FSL_FACEBOOK_APPID');
		$helper->fields_value['FSL_FACEBOOK_APPKEY'] = Configuration::get('FSL_FACEBOOK_APPKEY');
		$helper->fields_value['FSL_FACEBOOK_SCOPE'] = Configuration::get('FSL_FACEBOOK_SCOPE');

		return $fields_form;
	}

	private function initApi($accessToken = null)
	{
		$facebookApi = new Facebook\Facebook([
			'app_id' => $this->_appid,
			'app_secret' => $this->_appkey,
			'default_graph_version' => 'v2.2'
		]);

		if(!$accessToken)
		{
			try
			{
				$jsHelper = $facebookApi->getJavaScriptHelper();
				$accessToken = $jsHelper->getAccessToken();
			}
			catch(Facebook\Exceptions\FacebookResponseException $e)
			{
				// When Graph returns an error
				FSLTools::returnError(sprintf(Tools::displayError('Facebook Graph returned an error: %s'), $e->getMessage()));
			}
			catch(Facebook\Exceptions\FacebookSDKException $e)
			{
				// When validation fails or other local issues
				FSLTools::returnError(sprintf(Tools::displayError('Facebook SDK returned an error: %s'), $e->getMessage()));
			}
		}

		$facebookApi->setDefaultAccessToken($accessToken);
		return $facebookApi;
	}

	public function processSubmitLogin()
	{
		$facebookApi = $this->initApi(Tools::getValue('accessToken'));

		try
		{
			$response = $facebookApi->get('/me');	
			$user = $response->getGraphUser();

			$social_customer = new FacebookCustomer();
			if(!$social_customer->getByUserId($user->getId()))
			{
				$social_customer->id_user = $user->getId();
				$social_customer->username = $user->getName(); // name
				$social_customer->firstname = $user->getFirstName(); // first_name
				$social_customer->lastname = $user->getLastName(); // last_name
				$social_customer->email = $user->getEmail();

				if($user->getPicture() && $user->getPicture()->getUrl())
					$social_customer->picture_url = $user->getPicture()->getUrl();
				else
					$social_customer->picture_url = "https://graph.facebook.com/".$user->getId()."/picture";//?type=square";

				if(!$social_customer->email)
				{
					if (!Tools::isSubmit("rerequest"))
						FSLTools::returnError(Tools::displayError('Email authorization must be granted.'), true);

					// Revoke permissions
					$facebookApi->delete('/'.$user->getId().'/permissions');
					FSLTools::returnErrors(array()); // No error message
				}

				if($user->getGender())
					$social_customer->id_gender = ($user->getGender() == 'female')?2:1; // gender
				
				if($user->getBirthday())
				{
					$birth_date = explode('/', $user->getBirthday()); // birthday : MM/DD/YYYY
					$birth_year = $birth_date[2];
					$birth_month = $birth_date[0];
					$birth_day = $birth_date[1];
					$social_customer->birthday = $birth_year.'-'.$birth_month.'-'.$birth_day;
				}

				$location = $user->getLocation();
				if($location) { /* TODO */ }
			}

			return $social_customer;
		}
		catch(Facebook\Exceptions\FacebookResponseException $e)
		{
			// When Graph returns an error
			FSLTools::returnError(sprintf(Tools::displayError('Facebook Graph returned an error: %s'), $e->getMessage()));
		}
		catch(Facebook\Exceptions\FacebookSDKException $e)
		{
			// When validation fails or other local issues
			FSLTools::returnError(sprintf(Tools::displayError('Facebook SDK returned an error: %s'), $e->getMessage()));
		}
	}

	public function processSubmitRevoke()
	{
		$user_id = Tools::getValue('user_id');

		$social_customer = new FacebookCustomer();
		if(!$social_customer->getByUserId($user_id))
			FSLTools::returnError(Tools::displayError('This Facebook user is not registered.'));

		$notAuthorized = Tools::getValue('notAuthorized');
		if (!($notAuthorized == 'on' || $notAuthorized == 'true' || $notAuthorized == '1'))
		{
			$facebookApi = $this->initApi(Tools::getValue('accessToken'));
			try
			{
				$facebookApi->delete('/'.$user_id.'/permissions');
			}
			catch(Facebook\Exceptions\FacebookResponseException $e)
			{
				// When Graph returns an error
				FSLTools::returnError(Tools::displayError('Graph returned an error: ') . $e->getMessage());
			}
			catch(Facebook\Exceptions\FacebookSDKException $e)
			{
				// When validation fails or other local issues
				FSLTools::returnError(Tools::displayError('Facebook SDK returned an error: ') . $e->getMessage());
			}
		}
			
		return $social_customer;
	}
}
