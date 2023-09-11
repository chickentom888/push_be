<?php
/**
 * Created by PhpStorm.
 * User: Unknown
 */

namespace DCrypto\Object;

class Account
{
	public $id;
	public $address;
	public $real_address;
	public $password;
	public $private_key;
	public $server_ip;
	public $ticker;
	public $info;

	/**
	 * Account constructor.
	 * @param $id
	 * @param null $accountAddress
	 * @param null $accountRealAddress
	 * @param null $accountPassword
	 * @param $server_ip
	 * @param null $ticker
	 * @param null $info
	 * @param null $privateKey
	 */
	public function __construct($id = null, $accountAddress = null, $accountRealAddress = null, $accountPassword = null, $server_ip = null, $ticker = null, $info = null, $privateKey = null)
	{
		$this->id = $id;
		$this->address = trim($accountAddress);
		$this->real_address = trim($accountRealAddress);
		$this->password = trim($accountPassword);
		$this->server_ip = $server_ip;
		$this->ticker = $ticker;
		$this->info = $info;
		$this->private_key = trim($privateKey);
	}

}
