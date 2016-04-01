<?php
namespace Think\Pay\Driver;

class Icbcpay extends \Think\Pay\Pay{
	protected $gateway = 'https://82.200.30.80:11491/servlet/NewB2cMerPayReqServlet';
	protected $config = array(
			'key' => '',
			'partner' => ''
	);
	
}