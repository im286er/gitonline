<?php
namespace Common\Org;
vendor('Igetui.IGt', '', '.class.php');

class IGPushMsg {
	const APIEX_HOST		= 'http://sdk.open.api.igexin.com/apiex.htm';
	const API_HOST 			= 'http://sdk.open.api.igexin.com/api.htm';
	
	private $_igt;
	private $_tpl;
	private $_msg;
	private $appid 			= '';
	private $appkey 		= '';
	private $appsecret  	= '';
	private $mastersecret 	= '';
	
	public static $IGPush;

	private function __construct($appid, $appkey, $appsecret, $mastersecret) {
		$this->appid 		= $appid;
		$this->appkey 		= $appkey;
		$this->appsecret 	= $appsecret;
		$this->mastersecret = $mastersecret;

		$this->_igt = new \IGeTui(self::APIEX_HOST, $appkey, $mastersecret);	
	}
	
	public static function getIGPushMsg($bool=false, $gtinfo=array()) {
		if($bool==true || !self::$IGPush || !self::$IGPush instanceof self) {
			self::$IGPush = new self($gtinfo['gt_appid'], $gtinfo['gt_appkey'], $gtinfo['gt_appsecret'], $gtinfo['gt_mastersecret']);
		}
		return self::$IGPush;
	}
	
	//按CID发送
	public function pushMessageToCid($cid, $type=1, $args='', $msg='') {
		putenv("needDetails=true");
		//模板设置
		switch( $type ) {
			case 1: $this->_IGtNotificationTemplate($args); break;
			case 4: $this->_IGtTransmissionTemplate($args); break;
		}

		$msg = array_merge(json_decode('{"isOffline":true, "offlineExpireTime":7200000, "netWorkType":1}', true), (array)json_decode($msg, true));
		$message = new \IGtListMessage();
		$message->set_isOffline($msg['isOffline']);
		$message->set_offlineExpireTime((int)$msg['offlineExpireTime']);
		$message->set_data($this->tpl);
		$message->set_PushNetWorkType((int)$msg['netWorkType']);

		//接收设置
		$target = new \IGtTarget();
		$target->set_appId($this->appid);
		$target->set_clientId( $cid );
	
		return $this->_igt->pushMessageToSingle($message, $target);
	}

	/**
	 * 按APP群发 可以按指定条件发送
	 * @param  string $type 模板样式    1 透传功能模板（点击通知打开应用） 2 点击通知打开网页  3 点击通知弹出下载 4 透传消息 
	 * @param  string $args 模板参数 同 pushMessageToSingle 方法参数一样
	 * @param  string $msg 消息参数 同 pushMessageToSingle 方法参数一样 
	 * @param  array $province 要发送的省份（最多选择三个）
	 * @param  array $tag 应用标签
	 * @return [type]
	 */
	public function pushMessageToApp($type=1, $args='', $msg='', $province=array(), $tag=array()) {
		switch( $type ) {
			case 1: $this->_IGtNotificationTemplate($args); break;//通知 只有ANDROID
			case 4: $this->_IGtTransmissionTemplate($args); break;//透传
		}

		$msg = array_merge(json_decode('{"isOffline":true, "offlineExpireTime":7200000, "netWorkType":0}', true), (array)json_decode($msg, true));
		$message = new \IGtAppMessage();
		$message->set_isOffline($msg['isOffline']);
		$message->set_offlineExpireTime((int)$msg['offlineExpireTime']);
		$message->set_data($this->tpl);
		$message->set_PushNetWorkType((int)$msg['netWorkType']);

		//设置APPID
		$message->set_appIdList(array($this->appid));
		if( !empty($province) ) $message->set_provinceList( $province );
		if( !empty($tag) ) $message->set_tagList( $tag );

		return $this->_igt->pushMessageToApp($message);
	}

	/**
	 * 根据任务识别号来获取推送结果
	 * @param string $taskid 任务识别号
	 */
	public function getPushResult( $taskid ) {
		$params = array();
		$params["action"] = "getPushMsgResult";
		$params["appkey"] = $this->appkey;
		$params["taskId"] = $taskid;
		$params["sign"]   = $this->_IGtCreateSign($params, $this->mastersecret);
		return $this->_IGtSendHttpData(self::API_HOST, json_encode($params));
	}

	//查看用户状态
	public function getUserStatus($cid) {
		return $this->_igt->getClientIdStatus($this->appid, $cid);
	}

	//ClientID与别名绑定
	public function aliasBind($cid, $alias='') {
		return $this->_igt->bindAlias($this->appid, $alias, $cid);
	}

	//多个 ClientID 与别名绑定
	public function aliasBatch($cid=array(), $alias='') {
		$targetList = array();
		foreach($cid as $k=>$c) {
			$target = new IGtTarget();
			$target->set_clientId( $c );
			$target->set_alias( $alias );
			array_push($targetList, $target);
		}
        return $this->_igt->bindAliasBatch($this->appid, $targetList);
	}

	//根据别名查 clentid
	public function queryCid( $alias='' ) {
		return $this->_igt->queryClientId($this->appid, $alias);
	}

	//根据 clientid 查别名
	public function queryAlias( $cid ) {
		return $this->_igt->queryAlias($this->appid, $cid);
	}

	//解除ClientId别名绑定
	public function aliasUnBind($alias, $cid) {
		return $this->_igt->unBindAlias($this->appid, $alias, $cid);
	}

	//解除所有ClientId别名绑定
	public function aliasUnBindAll($alias, $cid) {
		return $this->_igt->unBindAliasAll($this->appid, $alias, $cid);
	}

	//按APP发送通知
	private function _IGtNotificationTemplate($args) {
		$args_default = json_decode('{"title":"", "text":"", "logo":"", "isRing":true, "isVibrate":true, "isClearable":true, "transmissionType":2, "transmissionContent":""}', true);
		$args_procues = (array)json_decode($args, true);
		$args_contues = array_merge($args_default, $args_procues);
		$this->tpl = new \IGtNotificationTemplate();
		$this->tpl->set_appId($this->appid);
		$this->tpl->set_appkey($this->appkey);
		$this->tpl->set_title($args_contues['title']);
		//$this->tpl->set_text($args_contues['text']);
		$this->tpl->set_isRing(true);//是否响铃
		$this->tpl->set_isVibrate(true);//是否震动

		$this->tpl->set_transmissionType($args_contues['transmissionType']);
		$this->tpl->set_transmissionContent($args_contues['transmissionContent']);
		
		//define('BEGINTIME','2015-03-06 13:18:00');
		//define('ENDTIME','2015-03-06 13:24:00');
        //$this->tpl->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息

    	//iOS推送需要设置的pushInfo字段
		$contents = json_decode($args_contues['transmissionContent'], true);
		$apn = new \IGtAPNPayload();
		$alertmsg = new \DictionaryAlertMsg();
		$alertmsg->locKey = $contents['title'];
		$alertmsg->title = $contents['title'];
		$alertmsg->titleLocKey = $contents['title'];
		$apn->alertMsg = $alertmsg;
		$apn->badge = 1;
		$apn->contentAvailable = 1;
		$this->tpl->set_apnInfo($apn);
	}
	
	//按APP发送透传
	private function _IGtTransmissionTemplate($args) {
		$args_default = json_decode('{"transmissionContent":"", "transmissionType":"2"}', true);
		$args_procues = (array)json_decode($args, true);
		$args_contues = array_merge($args_default, $args_procues);

		$this->tpl = new \IGtTransmissionTemplate();
		$this->tpl->set_appId($this->appid);
		$this->tpl->set_appkey($this->appkey);
		$this->tpl->set_transmissionType($args_contues['transmissionType']);
		$this->tpl->set_transmissionContent($args_contues['transmissionContent']);
		
		//IOS推送
		$contents = json_decode($args_contues['transmissionContent'], true);
		$apn = new \IGtAPNPayload();
		$alertmsg = new \DictionaryAlertMsg();
		$alertmsg->locKey = $contents['title'];
		$alertmsg->title = $contents['title'];
		$alertmsg->titleLocKey = $contents['title'];
		$apn->alertMsg = $alertmsg;
		$apn->badge = 1;
		$apn->contentAvailable = 1;
		$this->tpl->set_apnInfo($apn);
	}

	private function _IGtCreateSign( array $params, $masterSecret ) {
		$sign = $masterSecret;
		foreach ($params as $key => $val) {
			if(is_string($val) || is_numeric($val) ) { 
				$sign .= $key.$val; 
			}
		}
		return md5($sign);
	}

	private function _IGtSendHttpData( $url, array $prams ) {
		$curl = curl_init( $url );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, 'GeTui PHP/1.0');
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);  
		$result = curl_exec( $curl );
		curl_close( $curl );
		return $result;
	}
}