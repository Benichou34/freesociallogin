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

abstract class SocialCustomer extends ObjectModel
{
	public $id = 0;

	/** @var string Social network ID */
	public $id_user = 0;

	public $id_customer = 0;

	public $id_shop = 1;

	/** @var string Username */
	public $username;

	/** @var integer Gender ID */
	public $id_gender = 0;

	/** @var string Lastname */
	public $lastname;

	/** @var string Firstname */
	public $firstname;

	/** @var string Birthday (yyyy-mm-dd) */
	public $birthday = null;

	/** @var string e-mail */
	public $email;

	/** @var string picture url */
	public $picture_url;

	/** @var boolean Newsletter subscription */
	public $newsletter = true;

	/** @var boolean Social Like */
	public $like = false;

	public function __construct($id = null)
	{
		parent::__construct($id);
	}

	public static function createDb($table)
	{
		return Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$table.'` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_user` varchar(30) NOT NULL,
				`id_customer` int(10) unsigned NOT NULL,
				`id_shop` int(11) NOT NULL DEFAULT \'1\',
				`username` varchar(50) NOT NULL,
				`picture_url` varchar(255) NOT NULL,
				`like` TINYINT(1) NOT NULL DEFAULT \'0\',
				PRIMARY KEY (`id`)
			) ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8');
	}

	public static function deleteDb($table)
	{
		return Db::getInstance()->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.$table);
	}

	/**
	 * Return social customer instance from its social user id
	 *
	 * @param string $id_user user id
	 * @return SocialCustomer instance
	 */
	public function getByUserId($id_user)
	{
		$definition = ObjectModel::getDefinition($this);
		$result = Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.bqSQL($definition['table']).'`
			WHERE `id_user` = \''.pSQL($id_user).'\'');

		if (!$result)
			return false;

		$this->id = $result['id'];
		foreach ($result as $key => $value)
			if (array_key_exists($key, $this))
				$this->{$key} = $value;

		return $this;
	}

	/**
	 * Return social customer instance from its customer id
	 *
	 * @param string $id_customer customer id
	 * @return SocialCustomer instance
	 */
	public function getByCustomerId($id_customer)
	{
		$definition = ObjectModel::getDefinition($this);
		$result = Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.bqSQL($definition['table']).'`
			WHERE `id_customer` = \''.pSQL($id_customer).'\'');

		if (!$result)
			return false;

		$this->id = $result['id'];
		foreach ($result as $key => $value)
			if (array_key_exists($key, $this))
				$this->{$key} = $value;

		return $this;
	}

	/**
	 * Return customers list
	 *
	 * @return array Customers
	 */
	public static function getCustomers()
	{
		$definition = ObjectModel::getDefinition($this);
		$sql = 'SELECT *
				FROM `'._DB_PREFIX_.bqSQL($definition['table']).'`
				ORDER BY `id_customer` ASC';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}

	/**
	 * Check if user is already registered in database
	 *
	 * @param string $id_user user id
	 * @param $return_id boolean
	 * @return Customer ID if found, false otherwise
	 */
	public static function customerExists($id_user, $return_id = false)
	{
		$definition = ObjectModel::getDefinition($this);
		$result = Db::getInstance()->getValue('
			SELECT id_customer
			FROM `'._DB_PREFIX_.bqSQL($definition['table']).'`
			WHERE `id_user` = \''.pSQL($id_user).'\'');

		return ($return_id ? (int)$result : (bool)$result);
	}
 
	abstract public function getProvider();
}

