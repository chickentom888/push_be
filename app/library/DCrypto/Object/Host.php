<?php
/**
 * Created by PhpStorm.
 * User: Unknown
 */

namespace DCrypto\Object;


class Host
{
	public $host;
	public $port;
	public $username;
	public $password;
	public $pass_phrase;
	public $cert_file;
	public $url;

	/**
	 * CoinHost constructor.
	 * @param $host
	 * @param $port
	 * @param $username
	 * @param $password
	 * @param $pass_phrase
	 * @param $cert_file
	 */
	public function __construct($host, $port, $username, $password, $pass_phrase = null, $cert_file = null, $url = null)
	{
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->pass_phrase = $pass_phrase;
		$this->cert_file = $cert_file;
		$this->url = $url;
	}
}
