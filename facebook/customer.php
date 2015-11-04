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

require_once(dirname(__FILE__).'/../classes/customer.php');

class FacebookCustomer extends SocialCustomer
{
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'customer_facebook',
		'primary' => 'id',
		'fields' => array(
			'id_user' => 		array('type' => self::TYPE_STRING, 'required' => true, 'size' => 30),
			'id_customer' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false, 'required' => true),
			'id_shop' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
			'username' => 		array('type' => self::TYPE_STRING, 'required' => true, 'size' => 50),
			'picture_url' => 	array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
			'like' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
		)
	);

	public function getProvider()
	{
		return "Facebook";
	}
}
