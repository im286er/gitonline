<?php
namespace Capper\Controller;
use Think\Controller;
//$AccessToken = $wechat->checkAuth();获取全局变量
//$this->wechat->valid();验证部分
/**微信授权**/
class WechatController extends Controller {
	private $wechat;
	public function _initialize() {
		header("Content-type: text/html; charset=utf-8");
        $options = array(
            'token' => C('WECHAT.apptoken'), // 填写你设定的key
            'appid' => C('WECHAT.appid'), // 填写高级调用功能的appid
            'appsecret' => C('WECHAT.appsecret') // 填写高级调用功能的密钥
        );
        $wechat = new \Vendor\Weixin\Wechat($options);
		$this->wechat =  $wechat;
	}

	private function wechat(){
		return $this->wechat;
	}

    public function index() {
		$wechat = $this->wechat();
		$wechat->getRev();
		switch ($wechat->getRevType())
		{
		case 'text'://文字消息
		  /**处理过程**/
			 $wechat->text('已接受到您发送的文字：'.$wechat->getRevContent())->reply();
		   /**处理过程**/
		  break;  
		case 'image'://图片消息
		  /**处理过程**/
		   /**处理过程**/
		  break;
		case 'voice'://语音消息
		  /**处理过程**/
		   /**处理过程**/
		  break;
		case 'video'://视频消息
		  /**处理过程**/
		   /**处理过程**/
		  break;
		case 'shortvideo'://微视频消息
		  /**处理过程**/
		   /**处理过程**/
		  break;
		case 'location':  //地理位置消息
		  /**处理过程**/
		   /**处理过程**/
		  break;
		case 'link':	//连接消息
		  /**处理过程**/
		   /**处理过程**/
		  break;
		case 'event':  //事件消息
		  /**处理过程**/
			$this->eventExecute();
		   /**处理过程**/
		  break;
		default:
			$wechat->text('欢迎关注我们的公众号')->reply();
		}
		
    }

	private function eventExecute(){
		$wechat = $this->wechat();
		$event = $wechat->getRevEvent();
		
		switch ($event['event']){
		case 'subscribe':  //关注事件
			if($event['key']){
				'二维码关注事件';
			}else{
				$openid = $wechat->getRevFrom();
				D('TrWxuser')->subscribe($openid);//关注新增粉丝
				D('TrWxuser')->getInfo($wechat->getUserInfo($openid));//关注后获取数据
				$wechat->text('欢迎关注我们的公众号！')->reply();
			}
		  break;
		case 'unsubscribe':  //地理定位
			$openid = $wechat->getRevFrom();
			D('TrWxuser')->unsubscribe($openid);	
		  break;
		case 'LOCATION':  //地理定位
			$openid = $wechat->getRevFrom();
			$revGeo	= $wechat->getRevEventGeo();
			D('TrWxuser')->revGeo($openid,$revGeo);	
		  break;  
		default:
		 
		}
		return;
	}

	/***获取微信的AccessToke值***/
	public function getAccessToke(){
		$AccessToken = $wechat->checkAuth();//获取全局变量
		die($AccessToken);
	}


	/***获取微信的支付Id***/
	public function getPayId(){
	
	
	
	
	
	}

	/***创建菜单***/
	public function createMenu(){
		$wechat = $this->wechat();
		$app_menu = array(
			'button' => array(
				array(
                    'type' => 'click',
                    'name' => '关于托儿',
                    'key' => 'abouttuoer',
                ),			
			   array(
                    'type' => 'click',
                    'name' => '加入托儿',
                    'key' => 'jointuoer',
                ),
			   array(
                    'name' => '我的托儿',
                    'sub_button' => array(
                            array(
                                    'type' => 'view',
                                    'name' => '我的托儿',
                                    'url' => 'http://www.dishuos.com/',
                                ),
                            array
                                (
                                    'type' => 'click',
                                    'name' => '我的邀请',
                                    'key' => 'V1001_GOOD',
                                ),
                        )
                ),
			)	
		);
		$this->wechat->createMenu($app_menu);
		echo $this->wechat->errCode;
		die();
	}


	/* 用户登录检测 */
	public function verifyAuth(){
		$wechat = $this->wechat();
	 	$AccessToken = $wechat->checkAuth();
		$returnurl = I('get.returnurl');
		if($returnurl)cookie('returnurl',$returnurl);
		if(cookie('openid'))return true;
		$wxcodelink = $wechat::OAUTH_PREFIX.$wechat::OAUTH_AUTHORIZE_URL.'appid='.C('WECHAT.appid').'&redirect_uri='.urlencode(U('Wechat/authLogin@tr',array(),null,false)).'&response_type=code&scope=snsapi_base&state=0#wechat_redirect';
		$this->redirect($wxcodelink);
	}

	/* 授权登录*/
	public function authLogin(){
		$wechat = $this->wechat();
		 $code = I('get.code');//微信返回的CODE
		 if(!$code)die('授权错误，请回到微信！');
		 $httpurl = $wechat::OAUTH_TOKEN_PREFIX."appid=".C('WECHAT.appid')."&secret=".C('WECHAT.appsecret')."&code=".$code."&grant_type=authorization_code";
		 $obj = $wechat->http_get($httpurl);
		 $json = json_decode($obj,true);
		 cookie('openid',$json['openid']);
		 $this->redirect(url_param_encrypt(cookie('returnurl'),'D'));
	}

	
	public function login(){
		$openid = $openid->openid;
	}
	

}