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

class FSLTools
{
	public static function returnErrors($errors, $rerequest = false)
	{
		$return = array(
			'hasError' => !empty($errors),
			'errors' => $errors,
			'rerequest' => $rerequest
		);

		die(Tools::jsonEncode($return));
	}

	public static function returnError($mesg, $rerequest = false)
	{
		FSLTools::returnErrors(array($mesg), $rerequest);
	}

	public static function returnAjax($redirect = null, $social_customer = null)
	{
		$return = array(
			'hasError'  => false,
		);
		
		if($redirect)
			$return['redirect'] = $redirect;
		
		if($social_customer)
		{
			$return['customer'] = array(
				'provider'  => $social_customer->getProvider(),
				'username'  => $social_customer->username,
				'lastname'  => $social_customer->lastname,
				'firstname' => $social_customer->firstname,
				'email'     => $social_customer->email,
				'gender'    => $social_customer->id_gender,
				'like'      => $social_customer->like
			);
		}
		
		die(Tools::jsonEncode($return));
	}

	/**
	 * sendConfirmationMail (This source code come from AuthControllerCore) 
	 * @param Customer $customer
	 * @return bool
	 */
	public static function sendConfirmationMail($social_customer, $passwd)
	{
		if (!Configuration::get('PS_CUSTOMER_CREATION_EMAIL') || !Configuration::get('FSL_CUSTOMER_CREATION_EMAIL'))
			return true;

		return Mail::Send(
			Context::getContext()->language->id,
			'social_account',
			Mail::l('Welcome!'),
			array(
				'{provider}' => $social_customer->getProvider(),
				'{username}' => $social_customer->username,
				'{firstname}' => $social_customer->firstname,
				'{lastname}' => $social_customer->lastname,
				'{email}' => $social_customer->email,
				'{passwd}' => $passwd
			),
			$social_customer->email,
			$social_customer->firstname.' '.$social_customer->lastname,
			null,
			null,
			null,
			null,
			dirname(__FILE__).'/../mails/'
		);
	}

	/**
	 * Process the newsletter settings and set the customer infos.
	 * @param Customer $customer Reference on the customer Object.
	 * @note At this point, the email has been validated.
	 */
	public static function processCustomerNewsletter(&$customer)
	{
		$customer->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
		$customer->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));

		if ($module_newsletter = Module::getInstanceByName('blocknewsletter'))
			if ($module_newsletter->active)
				$module_newsletter->confirmSubscription($customer->email);
	}
}
?>
