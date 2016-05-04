<?php
namespace Mobile\Controller;
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






	/***地鼠微信支付APP创建订单***/
	public function dsWxJsPay(){
		$oid = I('post.oid','','trim');
		if(!$oid)die(JSON(array('errcode'=>81201,'errmsg'=>'订单号不能为空')));
		$orderinfo = D('Order')->field('o_id,o_price,o_sid,o_gtype')->where(array('o_id'=>$oid,'o_pstatus'=>'0'))->find();
		$orderinfo or die(JSON(array('errcode'=>81202,'errmsg'=>'订单不存在')));
		if($orderinfo)$orderinfo['sname'] = D('Shop')->where(array('sid'=>$orderinfo['o_sid']))->getField('sname');
		//unset($orderinfo['flo_sid']);
		switch ($orderinfo['o_gtype']){
			case '1':
			 	$orderinfo['title'] = $orderinfo['sname'].'-在线下单';
				$linkurl = url_param_encrypt(U('Shop/index@mobile',array('sid'=>$orderinfo['o_sid'])),'E');
			  break;  
			case '2':
				$linkurl = url_param_encrypt(U('Shop/index@mobile',array('sid'=>$orderinfo['o_sid'])),'E');
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
		$input->SetTotal_fee($orderinfo['o_price']*100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag($orderinfo['sname']);
		$input->SetNotify_url(U('Wechat/dsWxNotify@mobile'));
		$input->SetTrade_type("APP");
		$order = \WxPayApi::unifiedOrder($input);
		//print_r($order);
		$order['timestamp'] = time();
		$order['oid'] = $oid;
		$order['errcode'] = 'ok';
		$order['paykey'] = \WxPayConfig::KEY;
		$order['errmsg'] = $order['return_msg'];
		$order['success_url'] = U('User/myorder@mobile',array('returnurl'=>$linkurl));//支付成功返回的url
		$order['fail_url'] = U('User/myorder@mobile',array('returnurl'=>$linkurl));//支付失败返回的url
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
			$order_info = M('Order')->where(array('o_id'=>$result['out_trade_no']))->find();
			$pay_info = array();
			$pay_info['pay_type'] = 'weixin';
			$pay_info['trade_no'] = $result['transaction_id'];
			$pay_info['out_trade_no'] = $result['out_trade_no'];
			$this->dsWxPaySuccess($pay_info);//支付成功处理
		}
	}


	/**帝鼠OS支付成功后处理**/
	public function dsWxPaySuccess($pay_info){
		$order_info = M('Order')->where(array('o_id'=>$pay_info['out_trade_no']))->find();
		if(!$order_info)return false;
		if($order_info['o_pstatus']>0)return true;
		$orderdata = array('o_pstatus' => '1','o_type'=>'1','o_pway'=>$pay_info['pay_type'],'o_pstime'=>date("Y-m-d H:i:s"));
		M('Order')->where(array('o_id'=>$pay_info['out_trade_no']))->save($orderdata);
		if($order_info['o_pstatus'] == '0'){
			$logdata =array();
			$logdata['oid'] = $order_info['o_id'];
			$logdata['uid'] = $order_info['o_uid'];
			$logdata['pay_type'] = $order_info['o_gtype'];
			$logdata['pay_price'] = $order_info['o_price'];
			$logdata['pay_time'] = date('Y-m-d H:i:s');
			$logdata['pay_way'] = $pay_info['pay_type'];
			$logdata['pay_trade_no'] = $pay_info['trade_no'];
			$result = M('Paylog')->add($logdata);
			if($result && $order_info['o_type']==2){//如果支付款是汇入系统账户，更新商家的账户余额
				$merchant = M('merchant')->where(array('jid'=>$order_info['o_jid']))->find();
				if($merchant)M('member')->where(array('mid'=>$merchant['mid']))->setInc('money',$order_info['o_price']); 
			}
			return true;
		}else return false;
	}
	

}