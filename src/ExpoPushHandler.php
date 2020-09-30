<?php

use ExponentPhpSDK\Expo;

class ExpoPushHandler
{
	private $expo = null;

	public function __construct()
	{
		$this->expo = Expo::normalSetup();
	}

	public function send($unique_id, $data)
	{
		return $this->expo->notify($unique_id, $data);
	}
	
	public function subscribe($unique_id, $device_id)
	{
		return $this->expo->subscribe($unique_id, $device_id);
	}
	
	public function unsubscribe($unique_id, $device_id = null)
	{
		return $this->expo->unsubscribe($unique_id, $device_id);
	}
}

?>