<?php
namespace Rebateapp\Controller;
use Think\Controller;
use Vendor\Weixin\tuoerapp;
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
		$AccessToken = $this->wechat->checkAuth();//获取全局变量
		print_r($this->wechat);
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

	/***托儿【全民返利】微信支付APP创建订单***/
	public function dsWxJsPay(){
		$oid = I('post.oid','','trim');
		if(!$oid)die(JSON(array('errcode'=>81201,'errmsg'=>'订单号不能为空')));
		$orderinfo = D('FlOrder')->field('flo_id,flo_price,flo_sid,flo_gtype')->where(array('flo_id'=>$oid,'flo_pstatus'=>'0'))->find();
		$orderinfo or die(JSON(array('errcode'=>81202,'errmsg'=>'订单不存在')));
		if($orderinfo)$orderinfo['sname'] = D('Shop')->where(array('sid'=>$orderinfo['flo_sid']))->getField('sname');
		//unset($orderinfo['flo_sid']);
		switch ($orderinfo['flo_gtype']){
			case '1':
			 	$orderinfo['title'] = $orderinfo['sname'].'-在线下单';
				$linkurl = url_param_encrypt(U('Shop/shopInfo@flapp',array('sid'=>$orderinfo['flo_sid'])),'E');
			  break;  
			case '2':
				$linkurl = url_param_encrypt(U('Shop/shopInfo@flapp',array('sid'=>$orderinfo['flo_sid'])),'E');
				$orderinfo['title'] = $orderinfo['sname'].'-在线预约';
			  break;
			case '3': 
				$linkurl = url_param_encrypt(U('Recharge/calls@flapp'),'E');
				$orderinfo['title'] = '升级VIP';
			  break;
			case '4':
				$linkurl = url_param_encrypt(U('Recharge/calls@flapp'),'E');
				$orderinfo['title'] = '话费充值';
			  break;
			case '5':
				$linkurl = url_param_encrypt(U('Recharge/calls@flapp'),'E');
				$orderinfo['title'] = '流量充值';
			  break;
			default:
				$linkurl = url_param_encrypt(U('Recharge/calls@flapp'),'E');
				$orderinfo['title'] = '在线支付';
		}
		if(!$orderinfo['sname'])$orderinfo['sname'] = $orderinfo['title'];
		vendor("Weixin.JsApiPay");
		$logHandler= new \CLogFileHandler(\WxPayConfig::Log());
		$log = \Log::Init($logHandler, 15);
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($orderinfo['title']);
		$input->SetAttach($orderinfo['sname']);
		$input->SetOut_trade_no($oid);
		$input->SetTotal_fee($orderinfo['flo_price']*100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag($orderinfo['sname']);
		$input->SetNotify_url(U('Wechat/dsWxNotify@flapp'));
		$input->SetTrade_type("APP");
		$order = \WxPayApi::unifiedOrder($input);
		//print_r($order);
		$order['timestamp'] = time();
		$order['oid'] = $oid;
		$order['errcode'] = 'ok';
		$order['paykey'] = \WxPayConfig::KEY;
		$order['errmsg'] = $order['return_msg'];
		$order['success_url'] = U('My/order@flapp',array('returnurl'=>$linkurl));//支付成功返回的url
		$order['fail_url'] = U('My/order@flapp',array('returnurl'=>$linkurl));//支付失败返回的url
		$order?die(JSON($order)):die(JSON(array('errcode'=>81202,'errmsg'=>'订单号不存在订单!')));
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
			$order_info = M('FlOrder')->where(array('flo_id'=>$result['out_trade_no']))->find();
			$pay_info = array();
			$pay_info['pay_type'] = 'weixin';
			$pay_info['trade_no'] = $result['transaction_id'];
			$pay_info['out_trade_no'] = $result['out_trade_no'];
			$this->dsWxPaySuccess($pay_info);//支付成功处理
		}
	}


	/**帝鼠OS支付成功后处理**/
	public function dsWxPaySuccess($pay_info){
		$order_info = M('FlOrder')->where(array('flo_id'=>$pay_info['out_trade_no']))->find();
		if(!$order_info)return false;
		if($order_info['flo_pstatus']>0)return true;
		$orderdata = array('flo_pstatus' => '1','flo_ptype'=>'1','flo_pway'=>$pay_info['pay_type'],'flo_pstime'=>date("Y-m-d H:i:s"));
		M('FlOrder')->where(array('flo_id'=>$pay_info['out_trade_no']))->save($orderdata);
		if($order_info['flo_pstatus'] == '0'){
			$logdata =array();
			$logdata['pay_oid'] = $order_info['flo_id'];
			$logdata['pay_uid'] = $order_info['flo_uid'];
			$logdata['pay_type'] = $order_info['flo_gtype'];
			$logdata['pay_price'] = $order_info['flo_price'];
			$logdata['pay_time'] = date('Y-m-d H:i:s');
			$logdata['pay_way'] = $pay_info['pay_type'];
			$logdata['pay_trade_no'] = $pay_info['trade_no'];
			$result = M('FlPaylog')->add($logdata);
			if($result && $order_info['flo_ptype']==2){//如果支付款是汇入系统账户，更新商家的账户余额
				$merchant = M('merchant')->where(array('jid'=>$order_info['flo_jid']))->find();
				if($merchant)M('member')->where(array('mid'=>$merchant['mid']))->setInc('money',$order_info['flo_price']); 
			}
			return true;
		}else return false;
	}

}