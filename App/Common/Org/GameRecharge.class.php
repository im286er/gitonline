<?php
namespace Common\Org;

class GameRecharge {
	const GAME_KEY  = '8555aac5b3b0428f9573f3935b77701b';
	const GAME_URL  = 'http://115.238.31.240:1006/';
	const GAME_USER = '1008';

	public function __construct() {

	}

	//获取游戏产品
	public function getGameList()
	{
		$sign = $this->_GetSign();
		$baseurl = self::GAME_URL."OpenAPI/Data/ProductByGame.ashx?SPID=".self::GAME_USER."&Sign={$sign}";

		$curl = curl_init($baseurl);
		curl_setopt($curl, CURLOPT_HEADER, 0 );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$responseText = curl_exec($curl);
		curl_close($curl);

		$xml_obj = simplexml_load_string( $responseText );
		$erron = (int) $xml_obj->code;
		if( $erron != 1 ) return array();

		$product_list = array();
		$product_list_obj = $xml_obj->xpath("products/product");
		while(list( , $node) = each($product_list_obj)) {
			$product_list[] = (array) $node;
		}
		return $product_list;
	}

	//给游戏充值
	public function setGameRecharge()
	{

	}

	//生成签名
	private function _GetSign( array $config=array() ) 
	{
		$string = self::GAME_USER;

		foreach( $config as $c ) 
		{
			$string .= $c;
		}
		$string .= self::GAME_KEY;

		return md5( $string );	
	}

}