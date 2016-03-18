<?php
namespace Common\Org;

class PInterface {
	public static $printIp = '120.26.89.187';
	public static $printPort = '10025';
	public static $devicport = 10019;
	
	
	//打印订单
	public static function SprintPort( $string ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$printPort, 0.3, 0);
		return $client->send( $string ) ? true : false;
	}

	//获取白名单
	public static function getymlist( $rid ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$client->send( "sz,".$rid.",,,4" );
		
		return $client->recv(1024);
	}
	
	//设置白名单
	public static function setymlist( $rid, $string ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$string  = "sz,".$rid.",".$string.",,5";
		return $client->send( $string ) ? true : false;
	}
	
	//获取SSID
	public static function getssid( $rid ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$client->send( "sz,".$rid.",,,6" );
		
		return $client->recv(1024);
	}
	
	//批量获取SSID
	public static function getssidarray( array $rid=array() ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.5, 0);
		
		$inf_array = array();
		foreach( $rid as $r ) {
			$client->send( "sz,".$r.",,,6" );
			array_push($inf_array, trim( $client->recv(1024) ) );
		}
		return $inf_array ? $inf_array : array();
	}

	
	//设置SSID
	public static function setssid( $rid, $string ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$string  = "sz,".$rid.",".$string.",,7";
		return $client->send( $string ) ? true : false;
	}
	
	//获取WIFI状态
	public function getwifistatus() {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$client->send( "sz,,,,11" );
		
		return $client->recv(1024);
	}
	
	//设置设备状态和SSID
	public static function setstatus( $rid=array() ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$client->send( "sz,,,,9" );
		
		if( $data=$client->recv(1024*5) ) {
			$string_array = explode(",", $data);
			$string_array = array_filter(explode(";", $string_array[2]));
			
			foreach($rid as $r) {
				if(in_array($r['rcode'], $string_array)) {
					M('router')->where(array('rcode'=>$r['rcode']))->setField('rstatus', 1);
				} else {
					M('router')->where(array('rcode'=>$r['rcode']))->setField('rstatus', 0);
				}
			}
		}
		
		$inf_arr = self::getssidarray($rid);
		if( $inf_arr ) foreach($inf_arr as $ssid) {
			$string = explode(",", $ssid);
			if( $string[2] == '0' ) {
				M('router')->where(array('rcode'=>$r['rcode']))->setField('rwifistatus', '0');	
			} else {
				$ssname = trim(substr($string[2], 0, strpos($string[2], "|")));
				M('router')->where(array('rcode'=>$r['rcode']))->setField( array('rname'=>$ssname, 'rwifistatus'=>1));
			}
		} 
		
		return $inf_arr ? true : false;
	}
	
	
	//关闭或开启WIFI 1开  0半
	public static function setwifistatus($rid,  $status ) {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); 
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$string  = "sz,".$rid.",".$status.",,10";
		return $client->send( $string ) ? true : false;
	}
	
	public function setStatusOnly($rid = array()){
		if(class_exists('swoole_client')){
			$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
		}else{
			return false;
		}
		$client->connect(self::$printIp, self::$devicport, 0.3, 0);
		$client->send( "sz,,,,9" );
		
		if( $data=$client->recv(1024*5) ) {
			$string_array = explode(",", $data);
			$string_array = array_filter(explode(";", $string_array[2]));
			$a = array();
			$b = array();
			foreach($rid as $r) {
				if(in_array($r['rcode'], $string_array)) {
					$a[] = $r['rcode'];
				} else {
					$b[] = $r['rcode'];
				}
			}
			$aa = join(',',$a);
			$bb = join(',',$b);
			M('router')->where(array('rcode'=>array('in',$aa)))->setField('rstatus', 1);
			M('router')->where(array('rcode'=>array('in',$bb)))->setField('rstatus', 0);
		}
		return true;
	}
}