<?php
namespace Mobile\Controller;
//下单流程控制器

class FlowController extends MobileController {
	
	public $action_name = 'Choose';
	
	//购物车
	public function cart(){
	
	}
	
	//订单确认
	public function confirm(){
		//if(!$this->mid){		
			//redirect(U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('Mobile/Choose/index',array('jid'=>$this->jid,'sid'=>$this->sid)),'E'),'returnurl'=>url_param_encrypt(U('Mobile/Flow/confirm',array('jid'=>$this->jid,'sid'=>$this->sid)),'E'))));		
		//}
		
		//查询购物车商品
		$cart = $_COOKIE['ProductList'];
		
		if(!$cart || $cart == ''){
			$this->redirect('Choose/index', array('jid' => $this->jid,'sid'=>$this->sid));
		}
		$cart_arr2 = explode('|', $cart);
		$cart_key = array();
		foreach($cart_arr2 as $k1=>$v1){
			if(!empty($v1)){
				$temp = explode('_', $v1);
				$cart_key[] = $temp[0];
			}
		}
				
		$opt = array(
				'gid' => array('in',join(',',$cart_key))
		);
		$goods_list = M('goods')->where($opt)->select();
					
		$total_number = 0;
		$total_price  = 0;
		$cart_arr = array();
		foreach($cart_arr2 as $k=>$v){
			$temp2 = explode('_', $v);
			foreach($goods_list as $kk=>$vv){
				if($temp2[0] == $vv['gid']){
					$cart_arr[$k]['gname'] =  $vv['gname'];
					$cart_arr[$k]['gprice'] = $vv['gdprice']>0 ? $vv['gdprice'] : $vv['goprice'] ;
					$cart_arr[$k]['number'] = $temp2[1] ;
					$total_number += $temp2[1];
					$total_price  += $temp2[1] * $cart_arr[$k]['gprice'];
				}
			}
		}
		//洗衣的价格运算
		if($this->jid == '438'){
			//判断是否特级会员
			$privilege   = M('FlUser')->where(array('flu_userid'=>$this->mid))->getField('flu_privilege');
			if ( $privilege ) {
				//文件路径
				$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
				//获取特权参数
				$parammsg    = 1; 
				file_exists($path.'ParamMsg.php') && $parammsg=file_get_contents($path.'ParamMsg.php');
				//计算价格
				$total_price = $total_number * $parammsg;
			}else{
				$total_price = $total_price + 18 - 50;
				$total_price = ($total_price > 18) ? $total_price : 18;
			}
		}

		//查询我的优惠券
		$coupon_user = M('voucher_user');
		$opt = array(
			'u.mid' => $this->mid,
			'u.vu_price' => array('gt',0),
			'v.vu_status' => 1,
			'v.vu_jid' => $this->jid,
			'v.vu_sid' => array('like','%,'.$this->sid.',%'),
			'v.vu_stime' => array('elt',date("Y-m-d H:i:s")),
			'v.vu_etime' => array('egt',date("Y-m-d H:i:s")),
			'v.vu_money'  => array('elt',$total_price),
		);
		$coupon_list = $coupon_user->alias('u')->join('azd_voucher v on u.vu_id=v.vu_id')->where($opt)->field('v.vu_name,u.vu_price,u.uvid')->select();
		//查询店铺座位信息
		$sarr = array();
 		$sinfo = M("shop")->where(array("sid"=>$this->sid))->getField("seatlist");
		if($sinfo){
			$sarr = explode(',', $sinfo);
		}else{
			$sarr = '';
		}
		//查询收货地址
		$receivingid = I('receivingid',0);
		if($receivingid > 0){
			$address_info = M('fl_receiving')->where(array('flr_userid'=>$this->mid,'flr_receivingid'=>$receivingid))->find();
		}
		if(empty($address_info)){
			$address_info = M('fl_receiving')->where(array('flr_userid'=>$this->mid,'flr_default'=>1))->find();
		}
		if(empty($address_info)){
			$address_info = M('fl_receiving')->where(array('flr_userid'=>$this->mid))->find();
		}
		$this->assign('receivingid',$receivingid);
		$this->assign('address_info',$address_info);
		
		$linkurl = U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('Mobile/Flow/confirm',array('jid'=>$this->jid,'sid'=>$this->sid)),'E'),'returnurl'=>url_param_encrypt(U('Mobile/Flow/confirm',array('jid'=>$this->jid,'sid'=>$this->sid)),'E')));
		$this->assign('linkurl',$linkurl);
		
		$this->assign('page_name','订单确认');
		$this->assign('page_url',U('Choose/index', array('jid' => $this->jid,'sid'=>$this->sid)));
		$this->assign('coupon_list',$coupon_list);	
		$this->assign('total_number',$total_number);
		$this->assign('total_price',$total_price);
		$this->assign('goods_list',$cart_arr);
		$this->assign('sarr',$sarr);
		
		//查询用户名 手机号
		$user_info = M('fl_user')->where(array('flu_userid'=>$this->mid))->find();
		$this->assign('user_info',$user_info);

		//调用配置，来显示消费方式和支付方式
		$settingpath = APP_DIR.'/Public/Data/'.$this->jid.'/setting.conf';
		$setting = @unserialize( file_get_contents($settingpath) );
		if( !is_array( $setting ) || empty( $setting ) ) $setting = array();
		$this->assign('setting', $setting);

		$this->mydisplay();
	}
	
	//订单提交
	public function submit(){
		//没有登录跳转到登录页
		if(empty($this->mid)){
			$data = array(
					'msg' => 'false3',
			);
			$this->ajaxReturn($data);
		}
		
		$used_coupon = I('used_coupon');
		//$seat_name = I('seat_name');
		//$seat_tel =  I('seat_tel');
		$cart = $_COOKIE['ProductList'];
		if(!$cart || $cart == ''){
			$data = array(
					'msg' => 'false1',
			);
			$this->ajaxReturn($data);
			//$this->redirect('Choose/index', array('jid' => $this->jid,'sid'=>$this->sid));
		}
		$cart_arr2 = explode('|', $cart);
		$cart_key = array();
		foreach($cart_arr2 as $k1=>$v1){
			if(!empty($v1)){
				$temp = explode('_', $v1);
				$cart_key[] = $temp[0];
			}
		}
		
		$goods = M('goods');
		$opt = array(
				'gstatus'    => 1,
				'gstock'    => array('neq',0),
				'gid' => array('in',join(',',$cart_key))
		);

		$goods_list = $goods->where($opt)->field('gid,gname,gimg,goprice,gdprice,gstock,gdescription')->select();


		if(empty($goods_list)){
			$data = array(
					'msg' => 'false2',
			);
			$this->ajaxReturn($data);
		}
			
		$total_price  = 0;
		foreach($goods_list as $k=>$v){
			$g_price = $v['gdprice'] > 0 ? $v['gdprice'] : $v['goprice'];
			foreach($cart_arr2 as $k2=>$v2){
				$temp2 = explode('_', $v2);
				if($v['gid'] == $temp2[0]){
					if($v['gstock']==-1)$v['gstock']=10000000;
					$g_number = $temp2[1] > $v['gstock'] ? $v['gstock'] : $temp2[1] ;
				}
			}
			$total_price += $g_price*$g_number;
			$goods_list[$k]["gnum"] = $g_number;
		}
		
		$oprice = $total_price;
		
		if($used_coupon){
			$opt = array(
				'uvid' => $used_coupon,
				'mid'  => $this->mid
			);
			$coupon_price = M('voucher_user')->where($opt)->getField('vu_price');
			if($coupon_price > 0){
				$oprice = $total_price - $coupon_price;
				if($oprice < 0){
					$oprice = 0;
				}
			}
		}

	
		//$otc = M("merchant")->where(array('jid'=>$this->jid))->getField("mtc");
		//$oemt = userAgent($_SERVER["HTTP_USER_AGENT"]);
		$o_xftype  = I("o_xftype");
		$o_seat    = I("o_seat");
		$o_name    = I("o_name");
		$o_phone   = I("o_phone");
		$o_address = I("o_address");
		$o_remarks = I("o_remarks");

		if($o_xftype == 1){
			$o_address = '';
		}else{
			$o_seat = '';
		}
		
		$oid = orderNumber();
		$order = M('order');
		$opt = array(
				'o_id' => $oid,
				'o_sid' => $this->sid,
				'o_jid' => $this->jid,
				'o_uid' => $this->mid,
				'o_type' => 0,
				'o_name' => $o_name,
				'o_phone' => $o_phone,
				'o_address' => $o_address,
				'o_seat' => $o_seat,
				'o_dstime' => date("Y-m-d H:i:s"),
				'o_dstatus' => 1,
				'o_pstatus' => 0,
				'o_price'   => $oprice,
				'o_gtype'   =>  'Choose',
				'o_table'   => 'goods_snapshot',
				'o_remarks'   => $o_remarks,
				'o_xftype'   => $o_xftype,
		);
		if($oprice == 0){
			$opt['o_pstatus'] = 1;
			$opt['o_pstime'] = date("Y-m-d H:i:s");
		}
		$order->add($opt);
		//订单商品
		
		$app_con = array();
		$total_num = 0;
		foreach($goods_list as $k=>$v){
			$gt = array(
				'sp_gid' => $v['gid'],
				'sp_oid' => $oid,
				'sp_name' => $v['gname'],
				'sp_gdescription' => $v['gdescription'],
				'sp_goprice' => $v['goprice'],
				'sp_gdprice' => $v['gdprice'] > 0 ? $v['gdprice'] : $v['goprice'],
				'sp_number' =>  $v['gnum'],
				'sp_img' => $v['gimg'],
			);
			M('goods_snapshot')->add($gt);
			
			$app_con[] = $v['gname'];
			$total_num += $v['gnum'];
		}
		$appcontent = implode(',',$app_con);
		
		//提交成功,把消息发送到商家APP里
		$appmsg = array();
		$appmsg['jid'] = $this->jid;
		$appmsg['sid'] = $this->sid;
		$appmsg['avatar'] = M('FlUser')->where("flu_userid=".$this->mid)->getField('flu_avatar');
		$appmsg['title'] = \Org\Util\String::msubstr($appcontent, 0, 10);
		$appmsg['content'] = $appcontent;
		$appmsg['addtime'] = date('Y-m-d H:i:s');
		$appmsg['type'] = 2;
		$appmsg_extend = array(
			'oid'=>$oid,
			'type' => 1,
			'price' => $oprice,
			'number' => $total_num
 		);
		$appmsg['extend'] = serialize($appmsg_extend);
		M('appmsg')->add($appmsg);
		//推送消息到商家app
		D('Tsbind')->send($oid);

		//库存
		D('Goods')->reduceRepertory($oid,'setDec',1);

		
		/*优惠券*/
		if($coupon_price > 0){
			$o_price = $total_price >= $coupon_price ? $coupon_price : $total_price;
			$opt = array(
					'uvid' => $used_coupon,
					'mid'  => $this->mid
			);
			$vu_id = M('voucher_user')->where($opt)->getField('vu_id');
			$opt = array(
				'mid' => $this->mid,
				'vu_id' => $vu_id,
				'o_id'  => $oid,
				'o_price' => $o_price
 			);
			M('voucher_order')->add($opt);
			
			//$vu_price = $coupon_price - $total_price > 0 ? $coupon_price - $total_price : 0;
			$vu_price = 0;
			M('voucher_user')->where(array('uvid' => $used_coupon,'mid'  => $this->mid))->save(array('vu_price'=>$vu_price));
		}
		cookie('cart',null);
		cookie('ProductList', null);
		
		//订单打印
		D('Print')->doPrint($oid,1);
		
		$data = array("msg" => "true");
		if(I('post.paytype')=='alipay' && $oprice > 0){
			if($this->isApp){
				$data = array('msg' => 'yspay','oid' => $oid);
			}else{
				$data = array('msg' => 'pay','url' => U('Pay/request_alipay',array('o_id'=>$oid,'jump'=>1)));
			}
		}elseif(I('post.paytype')=='weixin' && $oprice > 0){
			if($this->isApp){
				$data = array('msg' => 'yspay','oid' => $oid);
			}else{
				$data = array('msg' => 'pay','url' => U('/Home/Wechat/dsWxJsPay@www').'?o_id='.$oid.'&jump=1');
			}
		}
		
		$this->ajaxReturn($data);
	}
}