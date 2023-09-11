<?php
/**
 * Created by PhpStorm.
 * User: Unknown
 */

namespace DCrypto\Object;


class Information
{
	public $ticker;
	public $name;
	public $key;
	public $icon;
	public $cover_image;
	public $state;
	public $hosts;
	public $message;
	public $status;
	public $decimals;
	public $platform;

	public $args;
	public $explorer;

	/**
	 * Information constructor.
	 */
	public function __construct()
	{
		$this->state = ICoin::STATE_RUNNING;
		$this->status = 1;
		$this->message = 'Running';
	}

}
