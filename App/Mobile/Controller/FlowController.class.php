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
		$dtype = I('dtype', 0, 'intval');
		$o_id  = I('o_id');
		if ($dtype == 3) {
			$goods_arr = M('goodsSnapshot')->where(array('sp_oid'=>$o_id))->select();
			$string = '';
			foreach ($goods_arr as $k => $v) {
				$string .= $this->sid.'_'.$v['sp_gid'].'_'.$v['sp_number'].'|';
			}
			cookie('ProductList', $string);
		}
		//查询购物车商品
		$cart = $_COOKIE['ProductList'];
		
		if(!$cart || $cart == ''){
			$this->redirect('Index/index', array('jid' => $this->jid,'sid'=>$this->sid));
		}
		$cart_arr2 = explode('|', $cart);
		$cart_key = array();
		foreach($cart_arr2 as $k1=>$v1){
			if(!empty($v1)){
				$temp = explode('_', $v1);
				$cart_key[] = $this->isnewTheme ? $temp[1] : $temp[0];
			}
		}
				
		$opt = array(
				'gid' => array('in',join(',',$cart_key))
		);
		$goods_list = M('goods')->where($opt)->select();
					
		$total_number = 0;
		$total_price  = 0;
		$cart_arr = array();
		if($this->isnewTheme){
			foreach($cart_arr2 as $k=>$v){
				$temp2 = explode('_', $v);
				foreach($goods_list as $kk=>$vv){
					if($temp2[1] == $vv['gid']){
						$cart_arr[$k]['gname'] =  $vv['gname'];
						$cart_arr[$k]['gprice'] = $vv['gdprice']>0 ? $vv['gdprice'] : $vv['goprice'] ;
						$cart_arr[$k]['number'] = $temp2[2] ;
						$total_number += $temp2[2];
						$total_price  += $temp2[2] * $cart_arr[$k]['gprice'];
					}
				}
			}
		}else{
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
		}
		
		//洗衣的价格运算
		if(in_array($this->jid, array_keys(C('EXPRESS_JID')))){
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

			cookie('price'.$this->mid , $total_price);
		}


		//查询我的优惠券
		$coupon_user = M('voucher_user');
		$opt = array(
			'u.mid' => $this->mid,
			'u.vu_price' => array('gt',0),
			'v.vu_status' => 1,
			'v.vu_jid' => $this->jid,
			'v.vu_sid' => $this->sid,
			'v.vu_stime' => array('elt',date("Y-m-d H:i:s")),
			'v.vu_etime' => array('egt',date("Y-m-d H:i:s")),
			'v.vu_money'  => array('elt',$total_price),
		);
		$coupon_list = $coupon_user->alias('u')->join('azd_voucher v on u.vu_id=v.vu_id')->where($opt)->field('v.vu_name,u.vu_price,u.uvid')->select();
		//查询店铺座位信息
 		$sarr = M("table")->where(array("sid"=>$this->sid))->field("title,number")->select();
 		$defalte_set = '';
 		if(session('table') > 0){
 			$defalte_set = M('table')->field('title,number')->where(array("sid"=>$this->sid,'id'=>session('table')))->find();
 		}
 		$this->assign('defalte_set',$defalte_set);
 		
		//查询收货地址
		$receivingid = I('receivingid',0);
		if($receivingid > 0){
			$address_info = M('receiving')->where(array('userid'=>$this->mid,'receivingid'=>$receivingid))->find();
		}
		if(empty($address_info)){
			$address_info = M('receiving')->where(array('userid'=>$this->mid,'defaults'=>1))->find();
		}
		if(empty($address_info)){
			$address_info = M('receiving')->where(array('userid'=>$this->mid))->find();
		}
		$this->assign('receivingid',$receivingid);
		if ($this->mid) {
			$this->assign('address_info',$address_info);
		}
		
		$linkurl = U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('Mobile/Flow/confirm',array('jid'=>$this->jid,'sid'=>$this->sid)),'E'),'returnurl'=>url_param_encrypt(U('Mobile/Flow/confirm',array('jid'=>$this->jid,'sid'=>$this->sid)),'E')));
		if ($this->jid == 438){
			$linkurl = url_param_encrypt(U('Flow/confirm@Mobile',array('jid'=>$this->jid,'sid'=>$this->sid)),'E');
		}
		
		$this->assign('linkurl',$linkurl);
		
		$this->assign('page_name','订单确认');
		$this->assign('page_url',U('/Index/shopCart', array('jid' => $this->jid,'sid'=>$this->sid)));
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
		
		$this->assign('shopinfo', M('shop')->where(array('sid'=>$this->sid))->find());	
		$this->assign('setting', $setting);
		$this->assign('dtype', $dtype);
		$this->newdisplay();
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
				$cart_key[] = $this->isnewTheme ? $temp[1] : $temp[0];
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
		if($this->isnewTheme){
			foreach($goods_list as $k=>$v){
				$g_price = $v['gdprice'] > 0 ? $v['gdprice'] : $v['goprice'];
				foreach($cart_arr2 as $k2=>$v2){
					$temp2 = explode('_', $v2);
					if($v['gid'] == $temp2[1]){
						if($v['gstock']==-1)$v['gstock']=10000000;
						$g_number = $temp2[2] > $v['gstock'] ? $v['gstock'] : $temp2[2] ;
					}
				}
				$total_price += $g_price*$g_number;
				$goods_list[$k]["gnum"] = $g_number;
			}
		}else{
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
		}
		
		if(in_array($this->jid, array_keys(C('EXPRESS_JID')))){
			$total_price = cookie('price'.$this->mid);
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
		
		//如果使用余额支付 判断余额是否足够
		if(I('post.paytype') == 'yue'){
			$yue = M('fl_user')->where(array('flu_userid'=>$this->mid))->getField('flu_balance');
			if($yue < $oprice){
				$data = array(
						'msg' => 'noyue',
				);
				$this->ajaxReturn($data);
			}
		}
	
		//$otc = M("merchant")->where(array('jid'=>$this->jid))->getField("mtc");
		//$oemt = userAgent($_SERVER["HTTP_USER_AGENT"]);
		$ctype = I('ctype', 1, 'intval');
		$o_xftype  = $ctype == 3 ? 2 : 1;
		$o_seat    = I("o_seat");
		$o_name    = I("o_name");
		$o_phone   = I("o_phone");
		$o_address = I("o_address");
		$o_remarks = I("o_remarks");
		$o_gdate   = I("o_gdate");

		if($o_xftype == 1 && !in_array($this->jid, array_keys(C('EXPRESS_JID')))){
			$o_address = '';
		}else{
			$o_seat = '';
		}
		
		$o_type = 0;
		if(I('post.paytype')=='weixin'){
			$o_type = 2;
		}elseif(I('post.paytype')=='yue'){
			$o_type = 1;
		}
		
		$oid = orderNumber();
		$order = M('order');
		$opt = array(
				'o_id' => $oid,
				'o_sid' => $this->sid,
				'o_jid' => $this->jid,
				'o_uid' => $this->mid,
				'o_type' => $o_type,
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
				'o_pway'   => I('post.paytype'),
		);
		if($oprice == 0){
			$opt['o_pstatus'] = 1;
			$opt['o_pstime'] = date("Y-m-d H:i:s");
		}

		if ($ctype == 2) {
			$opt['o_gdate']   = $o_gdate;
			$opt['o_gtype']   = 'Seat';
		}

		if ($ctype == 3) {
			$opt['o_gdate']   = $o_gdate;
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
		//余额支付
		if(I('post.paytype')=='yue'){
			M('fl_user')->where(array('flu_userid'=>$this->mid))->setDec('flu_balance',$oprice);//用户余额更新
			$merchantmid = M('merchant')->where(array('jid'=>$this->jid))->getField('mid');
			M('member')->where(array('mid'=>$merchantmid))->setInc('money',$oprice);//商户余额更新
			M('order')->where(array('o_id'=>$oid))->save(array('o_pstatus'=>1,'o_pstime'=>date("Y-m-d H:i:s")));//订单状态更新
		}
		cookie('cart',null);
		cookie('ProductList', null);
		M('temp_cart')->where(array('tableid'=>session('table')))->delete();
		
		//订单打印
		D('Print')->doPrint($oid,1);
		
		$data = array("msg" => "true");
		if(I('post.paytype')=='alipay' && $oprice > 0){
			if($this->isApp){
				$data = array('msg' => 'yspay','oid' => $oid);
			}else{
				$data = array('msg' => 'pay','url' => U('Pay/request_alipay',array('o_id'=>$oid,'jump'=>1)));
				//$data = array('msg' => 'yspay','oid' => $oid);
			}
		}elseif(I('post.paytype')=='weixin' && $oprice > 0){
			if($this->isApp){
				$data = array('msg' => 'yspay','oid' => $oid);
			}else{
				$data = array('msg' => 'pay','url' => U('/Home/Wechat/dsWxJsPay@www').'?o_id='.$oid.'&jump=1');
				//$data = array('msg' => 'yspay','oid' => $oid);
			}
		}

		if (in_array($this->jid, array_keys(C('EXPRESS_JID')))){
			$data = array('msg' => 'yspay','oid' => $oid);
		}
		
		$this->ajaxReturn($data);
	}
	
	//判断是否符合订单合并条件
	private function canHB(){
		$table     = session('table');
		$o_seat    = I("o_seat");
		if($table && $o_seat){
			$defalte_set = M('table')->where(array("sid"=>$this->sid,'id'=>$table))->getField('title');
			if($defalte_set == $o_seat){
				$hb_time = M('shop')->where(array("sid"=>$this->sid))->getField('hb_time');
				$hb_time = empty($hb_time) ? 10 : $hb_time;
				$hb_date = date('Y-m-d H:i:s',(time()-($hb_time*60)));
				$hb_oid  = M('order')->where(array('hb_order'=>1,'o_jid'=>$this->jid,"o_sid"=>$this->sid,'o_seat'=>$o_seat,'o_pstatus'=>0,'o_dstatus'=>array('elt',3),'o_close'=>0,'o_dstime'=>array('gt',$hb_date)))->getField('o_id');
				if($hb_oid){
					return $hb_oid;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	private function HB($hb_oid,$goods_list){
		foreach($goods_list as $k=>$v){
			$r = M('goods_snapshot')->where(array('sp_oid'=>$hb_oid,'sp_gid'=>$v['gid']))->getField('sp_id');
			if($r){
				M('goods_snapshot')->where(array('sp_id'=>$r))->setInc('sp_number',$v['gnum']);	
			}else{
				$gt = array(
						'sp_gid' => $v['gid'],
						'sp_oid' => $hb_oid,
						'sp_name' => $v['gname'],
						'sp_gdescription' => $v['gdescription'],
						'sp_goprice' => $v['goprice'],
						'sp_gdprice' => $v['gdprice'] > 0 ? $v['gdprice'] : $v['goprice'],
						'sp_number' =>  $v['gnum'],
						'sp_img' => $v['gimg'],
				);
				M('goods_snapshot')->add($gt);
			}
		}
		D('Common/Order')->saveOrder($hb_oid);
		$m = M('order_user')->where(array('o_id'=>$hb_oid,'mid'=>$this->mid))->getField('id');
		if(!$m){
			M('order_user')->add(array('o_id'=>$hb_oid,'mid'=>$this->mid));
		}
		/*合并之后的订单需要重新生成订单号*/
		$oid = orderNumber();
		M('order')->where(array('o_id'=>$hb_oid))->save(array('o_id'=>$oid));
		M('goods_snapshot')->where(array('sp_oid'=>$hb_oid))->save(array('sp_oid'=>$oid));
		M('voucher_order')->where(array('o_id'=>$hb_oid))->save(array('o_id'=>$oid));
		M('order_user')->where(array('o_id'=>$hb_oid))->save(array('o_id'=>$oid,'tes'=>$hb_oid));
		cookie('cart',null);
		cookie('ProductList', null);
		$data = array("msg" => "true");
		$this->ajaxReturn($data);
	}



	//测试
	// public function test(){
	// 	$jid   = I('jid');
	// 	$data  = C('EXPRESS_JID');
	// 	$data  = $data[$jid];
	// 	//判断是否发快递
	// 	if (in_array($jid, array_keys(C('EXPRESS_JID')))){
	// 		// 导入快递
	// 		vendor('Express.SF.OrderService');
	// 		$tpl  = new \OrderService();
	// 		$info = $tpl->xmlserviceorder('1603301057388132' , 'join' , '13021992467' , '浙江省杭州市拱墅区上塘路41号' , $data['d_company'] , $data['d_contact'] , $data['d_telphone'] , $data['d_address']);

	// 		print_r($info);
	// 	}else return false;
	// }



	// public function test_back(){
	// 	$jid   = I('jid');
	// 	//判断是否发快递
	// 	if (in_array($jid, array_keys(C('EXPRESS_JID')))){
	// 		// 导入快递
	// 		vendor('Express.SF.OrderService');
	// 		$tpl  = new \OrderService();
	// 		$info = $tpl->xmlserviceback('1605040904027132');

	// 		print_r($info);
	// 	}else return false;
	// }




}