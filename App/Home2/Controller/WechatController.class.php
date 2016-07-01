<?php
namespace Home2\Controller;
use Think\Controller;
use Vendor\Weixin\tuoer;
//$AccessToken = $wechat->checkAuth();获取全局变量
//$this->wechat->valid();验证部分
/**微信授权**/
class WechatController extends Controller {
	private $wechat;
	private $wxconfig;
	public function _initialize() {
		header("Content-type: text/html; charset=utf-8");
		vendor('Weixin.'.C('WXCONFIGPATH').'.WxPayConfig');//找到配置文件的路径
        $options = array(
            'token' => \WxPayConfig::TOKEN, // 填写你设定的key
            'appid' => \WxPayConfig::APPID, // 填写高级调用功能的appid
            'appsecret' => \WxPayConfig::APPSECRET // 填写高级调用功能的密钥
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



  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  public function getJsApiTicket($type=null) {
	vendor('Weixin.'.C('WXCONFIGPATH').'.WxPayConfig');//找到配置文件的路径
    $jsapiTicket = $this->wechat->getJsApiTicket();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $timestamp = time();
    $nonceStr = $this->createNonceStr();
    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    $signature = sha1($string);
    $signPackage = array(
      "appId"     => \WxPayConfig::APPID,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
	if($type=='return')return $signPackage;
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
	public function verifyAuth($returnurl){
		$wechat = $this->wechat();
	 	$AccessToken = $wechat->checkAuth();
		if(!$returnurl)$returnurl = I('get.returnurl');
		if($returnurl)cookie('returnurl',$returnurl);
		if(cookie('openid'))return true;
		redirect($wechat->getOauthRedirect(U('Home/Wechat/authLogin',array(),null,false),'1','snsapi_base'));
	}

	/* 授权登录*/
	public function authLogin(){
		$wechat = $this->wechat();
		$json = $wechat->getOauthAccessToken();
		cookie('openid',$json['openid']);
		redirect(cookie('returnurl'));
	}

	/***帝鼠微信支付创建订单***/
	public function dsWxJsPay(){
		if(I('get.jump')){
			cookie('payjump',I('get.jump'));
		}else{
			cookie('payjump',null);
		}
		if(I('get.o_id'))$o_id = I('get.o_id');
		$opt = array('o_id'=>$o_id,'o_pstatus'=> 0,'o_price'   => array('gt',0));
		$order_info = M('order')->where($opt)->find();
		if(empty($order_info)){
			$this->error('支付失败',U('User/myorder@yd',array('jump'=>cookie('payjump') )));
		}
		$mnickname = M('merchant')->where(array('jid'=>$order_info['o_jid']))->getField('mnickname');
		$gtype = D('Mobile/Order')->runGtype();
		$order_info['o_title'] = ($gtype[$order_info['o_gtype']]?$gtype[$order_info['o_gtype']]:'在线点餐').'-'.$mnickname;
		vendor("Weixin.JsApiPay");
		$logHandler= new \CLogFileHandler(\WxPayConfig::Log());
		$log = \Log::Init($logHandler, 15);
		$tools = new \JsApiPay();
		$openId = $tools->GetOpenid();
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($order_info['o_title']);
		$input->SetAttach($mnickname);
		$input->SetOut_trade_no($o_id);
		$input->SetTotal_fee($order_info['o_price']*100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag($mnickname);
		$input->SetNotify_url(U('Wechat/dsWxNotify@ho'));
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = \WxPayApi::unifiedOrder($input);
		$jsApiParameters = $tools->GetJsApiParameters($order);
		//获取共享收货地址js函数参数
		//$editAddress = $tools->GetEditAddressParameters();
		$this->assign('order_info', $order_info);
		//$this->assign('editAddress', $editAddress);
		$this->assign('jsApiParameters', $jsApiParameters);
		$this->assign('returnurl', U('User/myorder@yd',array('jid'=>$order_info['o_jid'],'jump'=>cookie('payjump'))) );//更改url地址
		$this->display();
	}


	/***微信支付接受订单***/
	public function dsWxNotify(){
		vendor("Weixin.JsApiPay");
		vendor("Weixin.lib.WxPayNotify");
		vendor("Weixin.lib.PayNotifyCallBack");
		$logHandler= new \CLogFileHandler(\WxPayConfig::Log());
		$log = \Log::Init($logHandler, 15);	
		\Log::DEBUG("begin notify");
		$notify = new \PayNotifyCallBack();
		$notify->Handle(false);
		$result = $notify->callback;
		if($result['transaction_id']){ //根据交易号判断支付是否成功，
			$order_info = M('order')->where(array('o_id'=>$result['out_trade_no']))->find();
			$pay_info = array();
			$pay_info['pay_type'] = 'weixin';
			$pay_info['trade_no'] = $result['transaction_id'];
			$pay_info['out_trade_no'] = $result['out_trade_no'];
			$this->dsWxPaySuccess($pay_info);//支付成功处理
		}
	}


	/**帝鼠OS支付成功后处理**/
	public function dsWxPaySuccess($pay_info){
		$order_info = M('order')->where(array('o_id'=>$pay_info['out_trade_no']))->find();
		if(!$order_info)return false;
		if($order_info['o_pstatus']>0)return true;
		$orderdata = array('o_pstatus' => '1','o_dstatus' => '3','o_type'=>'2','o_pway'=>$pay_info['pay_type'],'o_pstime'=>date("Y-m-d H:i:s"));
		M('order')->where(array('o_id'=>$pay_info['out_trade_no']))->save($orderdata);
		if($order_info['o_pstatus'] == '0'){
			$logdata =array();
			$logdata['oid'] = $order_info['o_id'];
			$logdata['jid'] = $order_info['o_jid'];
			$logdata['uid'] = $order_info['o_uid'];
			$logdata['gtype'] = $order_info['o_gtype'];
			$logdata['pay_price'] = $order_info['o_price'];
			$logdata['pay_time'] = date('Y-m-d H:i:s');
			$logdata['pay_status'] = '1';
			$logdata['pay_way'] = $pay_info['pay_type'];
			$logdata['pay_trade_no'] = $pay_info['trade_no'];
			$logdata['pay_type'] = $order_info['o_type'];
			$result = M('pay_log')->add($logdata);
			if($result){//如果支付款是汇入系统账户，更新商家的账户余额
				$merchant = M('merchant')->where(array('jid'=>$order_info['o_jid']))->find();
				M('member')->where(array('mid'=>$merchant['mid']))->setInc('money',$order_info['o_price']); 
			}
			return true;
		}else return false;
	}

}