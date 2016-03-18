<?php
namespace Home\Controller;
use Think\Controller;

class TestController extends Controller {
	public static $printIp = '120.26.89.187';
	public static $printPort = '10025';
	public static $devicport = 10019;

	public function setymlist( ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$string  = "sz,MF771507054E5FCA9E,http://www.dishuos.com/Public/Data/driver_system.trx,0,14";
		$s = $client->send( $string ) ? true : false;
		var_dump($s);
	}

	public function getymlist( ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$client->send( "sz,MF771507054E5FCA9E,,,4" );
		$s = $client->recv(1024);
		var_dump($s);
	}

	public function demo()
	{
		$s = sendmsg('15195996121', '1234');
		var_dump($s);
	}

}
