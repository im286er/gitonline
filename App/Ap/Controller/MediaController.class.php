<?php
namespace Ap\Controller;
use Think\Controller;

class MediaController extends Controller {
	protected $appsecret = 'azd57fed7593c362';
	protected $mac = '';

	protected function _initialize(){
		$key = strtolower( I('get.key') );
		$expiretime = intval(I('get.expiretime'));
		if(!$expiretime && !$key) exit(JSON(array('errcode'=>'40002','errmsg'=>'授权参数错误')));
		if($expiretime < time()) exit(JSON(array('errcode'=>'40003','errmsg'=>'授权已过期')));
		$authkey = md5($this->appsecret.$expiretime);
		if($key!=$authkey) exit(JSON(array('errcode'=>'40004','errmsg'=>'密匙口令错误')));
		$mac = I('get.mac');
		if( !$mac ) exit(JSON(array('errcode'=>'40005','errmsg'=>'mac参数出错')));
		$this->mac = $mac;
    }
	
	//下载视频到设备里
	public function resourceService() {
		$router = M('router')->where(array("rcode"=>$this->mac))->find();
		if( !is_array($router) || empty($router) ) exit(JSON(array('errcode'=>'40006','errmsg'=>'服务器端出错')));
		if( !isset($router['rmerchant']) || !$router['rmerchant'] ) exit(JSON(array('errcode'=>'40007','errmsg'=>'设备信息出错')));

		$filePath = APP_DIR.'/Public/Media/'.$router['rmerchant']."/*.*";
		$fileInfo = glob($filePath);
		
		foreach($fileInfo as $k=>$file) {
			if( (filemtime($file)+3600*3) < time() ) unset($fileInfo[$k]);
		}

		$string  = '{"path":"/Public/Media/'.$router['rmerchant'].'/",';
		$string .= '"data":[';
		foreach($fileInfo as $file){
			$string .= '"http://www.dishuos.com/'.ltrim(str_replace(APP_DIR, '', $file), '/').'",';
		}
		$string  = substr($string, 0, -1);
		$string .= ']}';
		echo $string;
	}
	
	//反馈当前文件是否下载到了设备里
	public function isdownload() {
		$downloadurl = I('post.filepath');

		file_put_contents(APP_PATH."/test.php", serialize( $_POST ) );
		$downloadurl_array = explode("#", $downloadurl);
		if( !is_array($downloadurl_array) || empty($downloadurl_array) ) return false;

		foreach($downloadurl_array as $url) {
			$string = strstr( $url, "/Public/");
			if( !$string ) return false;

			if( stripos($string, ".mp4") !== false )
			{
				M("video")->where( array("gfile"=>$string) )->setField("isdown", 1);	
			}
			else if( stripos($string, ".plist") !== false )
			{
				M("merchantApp")->where( array("iosurl"=>$string) )->setField("isdown", 1);	
			}
			else if( stripos($string, ".apk") !== false )
			{
				M("merchantApp")->where( array("appurl"=>$string) )->setField("isdown", 1);	
			}
		}
	}


}