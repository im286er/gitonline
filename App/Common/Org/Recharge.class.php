<?php
namespace Common\Org;

define('RECHARGE_TIMES', 	strtotime("+8 hours"));
define('RECHARGE_MACID', 	'dishuos');
define('RECHARGE_KEY_PATH', dirname(__FILE__)."/privkey.pem");
define('RECHARGE_BASE_URL', 'http://port.365xs.cn/shop/buyunit/');

class Recharge {
	private $config = array();
	public $error = '';
	
	//流量充值接口
	public function PflowRecharge( $orderid, $deno, $phone, $arsid ) {
		$this->setArges( $orderid, $deno, $phone, $arsid );
		@extract( $this->config );
		$baseurl = RECHARGE_BASE_URL."orderpayforflow.do?arsid={$arsid}&deno={$deno}&macid={$macid}&orderid={$orderid}&phone={$phone}&sign={$sign}&time={$time}";

		$curl = curl_init($baseurl);
		curl_setopt($curl, CURLOPT_HEADER, 0 );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$responseText = curl_exec($curl);
		curl_close($curl);
		$this->error = $responseText;
		
		$responseObject = simplexml_load_string($responseText);
		$errcode = strtolower( (string)$responseObject->errcode );
		if( $errcode=='ordersended' || $errcode=='ordersuccess' ){
			return true;
		}
		return false;
	}
	
	//话费充值接口
	public function PcallRecharge( $orderid, $deno, $phone ) {
		$this->setArges( $orderid, $deno, $phone );
		@extract( $this->config );
		$baseurl = RECHARGE_BASE_URL."orderpay.do?deno={$deno}&macid={$macid}&orderid={$orderid}&phone={$phone}&sign={$sign}&time={$time}";

		$curl = curl_init($baseurl);
		curl_setopt($curl, CURLOPT_HEADER, 0 );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$responseText = curl_exec($curl);
		curl_close($curl);
		$this->error = $responseText;
		
		$responseObject = simplexml_load_string($responseText);
		$errcode = strtolower( (string)$responseObject->errcode );
		if( $errcode=='ordersended' || $errcode=='ordersuccess' ){
			return true;
		}
		return false;
	}
	
	//获取当前平台的余额
	public function Pbackprice() {
		$start = microtime(true);
		$config = array();
		$config['time']	= $time = RECHARGE_TIMES;
		$config['macid'] = $macid = RECHARGE_MACID;
		$sign = $this->rsaSign( $config );
		$baseurl = RECHARGE_BASE_URL."balance.do?macid={$macid}&sign={$sign}&time={$time}";
		$curl = curl_init($baseurl);
		curl_setopt($curl, CURLOPT_HEADER, 0 );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$responseText = curl_exec($curl);
		curl_close($curl);
		$this->error = $responseText;
		$responseObject = simplexml_load_string($responseText);
		$balance = strtolower( (string)$responseObject->balance );
		return $balance;
	}
	
	
	//获取结果信息
	public function getError() {
		return $this->error;	
	}
	
	//配置参数
	private function setArges( $orderid, $deno, $phone, $arsid='' ) {
		$config = array();
		$config['time']		= RECHARGE_TIMES;
		$config['macid']	= RECHARGE_MACID;
		$config['phone']	= $phone;
		$config['orderid']	= $orderid;
		$config['deno']		= $deno;
		if($arsid) $config['arsid']	= $arsid;
		$config['sign']		= $this->rsaSign( $config );
		//验证签名
		//$sign = $config['sign']; unset($config['sign']);
		//$this->rsaVerify($config, $sign); exit;
		$this->config	= $config;
	}	

	//RSA签名
	private function rsaSign( $config ) {
		$para_filter = $this->argSort($config);
		$prestr = $this->createLinkstring($para_filter);

		$publickey = file_get_contents(RECHARGE_KEY_PATH);
		$res = openssl_get_privatekey($publickey);
		$out = false;
		if (openssl_sign($prestr, $ret, $res)) {  
			$out = base64_encode($ret);
		} 
		openssl_free_key($res);
		 
		return $this->myurlencode( $out );
	}
	
	//验证签名
	private function rsaVerify( $config , $sign )
	{
		$para_filter = $this->argSort($config);
		$prestr = $this->createLinkstring($para_filter);
		
		$publickey = file_get_contents( dirname(__FILE__).'/pubkey.pem' );
		
		$res = openssl_get_publickey($publickey);
		$bool = (bool)openssl_verify($prestr, base64_decode(urldecode($sign)), $res);
		var_dump($bool);
	}
	
	//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	private function createLinkstring($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg .= $key.$val;
		}
		if(get_magic_quotes_gpc()) { $arg = stripslashes($arg); }
		return $arg;
	}

	//对数组排序
	private function argSort($para) {
		ksort($para); reset($para); return $para;
	}
	
	//自定义urlencode
	private function myurlencode( $string ) {
		foreach( range("A", "Z") as $a ) {
			$pstring[] = "/%([0-9]){$a}/";
			$rstring[] = strtolower("%\\1{$a}");
		}
		return preg_replace($pstring, $rstring, urlencode( $string ));	
	}
}
