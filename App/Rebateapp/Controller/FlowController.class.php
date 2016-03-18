<?php
namespace Rebateapp\Controller;

/*下单相关
 * 
 * 
 * */
class FlowController extends RebateviewController {
	
	/**
	 * 购物
	 */
	public function order() {
		$jid = I('jid',0);
		$sid = I('sid',0);
		
		$shop = M("shop");
			
		//商品分类列表   start
		$category = M('class');
		$opt = array(
				'g.sid'    => $sid,
				'g.gtype' => 0,
				'g.gstatus' => 1,
				//'g.gvrebate' => array('gt',0),
				'g.gstock' => array('neq',0),
				'c.jid' => $jid,
				'c.sid' => $sid,
				'c.ctype' => 1,
				'c.status' => 1
		);
		
		$category_list = $category->alias('c')->join('azd_goods g on g.cid=c.cid')->where($opt)->group('c.cid')->order('c.corder')->select();
		
		//商品分类列表   end		
		$default_cid = isset($category_list[0]['cid']) ? $category_list[0]['cid'] : 0;
		
		$shop_name = $shop->where(array('sid'=>$sid))->getField('sname');
		
		$this->assign('jid',$jid);
		$this->assign('sid',$sid);
		$this->assign('shop_name',$shop_name);
		$this->assign('default_cid',$default_cid);
		$this->assign('category_list',$category_list);
		$this->display();
	}
	
	/*点菜搜索
	 *
	*
	* */
	public function search(){
		$cid = I('post.cid');
		$key = I('post.key');
	
		$org = C('ORDER_RETURN_GRADE');
		$org = $org[0]/100;
		
		//获取商品列表 start
	
		$sid=I('get.sid');
	
		$goods = M('goods');
	
		$opt = array(
				'g.sid'    =>$sid,
				'g.gtype' => 0,
				'g.gstatus' => 1,
				'g.gname' =>array('like',"%$key%"),
				'g.gstock' => array('neq','0'),
				//'g.gvrebate' => array('gt',0),
				'c.ctype' => 1,
				'c.status' => 1,
	
		);
	
		$pro_list_a = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->where($opt)->order('g.gorder')->select();
		
		if($cid){
			$opt['g.cid'] = $cid;
		}
		$goods_list = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->where($opt)->order('g.gorder')->select();
	
		$result_list = array();
		$pro_list   = array();
		foreach($goods_list as $k=>$v){
			if($key != ''){
				if(!stristr($v['gname'],$key)){
					continue;
				}
			}
			$v['v_fl'] = round($v['gdprice']>0 ? $v['gdprice']*$v['gvrebate']/100*$org : $v['goprice']*$v['gvrebate']/100*$org,2);
			$v['p_fl'] = round($v['v_fl']*C('USER_RATION_VIP'),2);
			if(isset($result_list[$v['cid']])){
				$result_list[$v['cid']]['list'][] = $v;
			}else{
				$result_list[$v['cid']]['cid'] = $v["cid"];
				$result_list[$v['cid']]['cname'] = $v["cname"];
				$result_list[$v['cid']]['list'][] = $v;
			}
			//$pro_list[$v['gid']] = $v;
		}
	
		foreach($pro_list_a as $s=>$vv){
			$pro_list[$vv['gid']] = $vv;
		}
	
		//print_r($pro_list);exit;
	
		//获取商品列表 end
	
		$this->assign('result_list',$result_list);
	
		$content = $this->fetch('Choose_goods');
		$data = array(
				'msg' => 'true',
				'content' => $content,
				'product' => json_encode($pro_list)
		);
		$this->ajaxReturn($data);
	}
	//搜索框
	
	function searchText(){
		$jid = I('jid',0);
		$sid = I('sid',0);
		$key = I('get.key');
	
		$goods = M('goods');
		//商品分类列表   start
		$category = M('class');
		$opt = array(
				'g.sid'    => $sid,
				'g.gtype' => 0,
				'g.gstatus' => 1,
				//'g.gvrebate' => array('gt',0),
				'g.gstock' => array('neq','0'),
				'c.jid' => $jid,
				'c.sid' => $sid,
				'c.ctype' => 1,
				'c.status' => 1
		);
	
		$cidData=array();
		foreach($category->alias('c')->join('azd_goods g on g.cid=c.cid')->where($opt)->group('c.cid')->order('c.corder')->select() as $v){
			$cidData[]=$v['cid'];
				
		}
	
		//商品分类列表   end
		$opt1 = array(
				'gname' =>array('like',"%$key%"),
				'sid'   =>$sid,
				'cid'	=>array('in',$cidData),
				'gtype' => 0,
				'gstatus' => 1,
				//'gvrebate' => array('gt',0),
				'gstock' => array('neq','0')
	
		);
	
		$proData= $goods->where($opt1)->order('gorder')->select();
		if(empty($proData)){$proData[]['msg']="抱歉！没有找到你要搜的东西！";}
		echo json_encode($proData);
	}
	
	
	/*订单确认
	 *
	*
	* */
	public function confirm(){
		$jid = I('jid',0);
		$sid = I('sid',0);
		//查询购物车商品
		$cart = $_COOKIE['cart'];
	
		if(!$cart || $cart == '{}'){
			//$this->redirect('Choose/index', array('jid' => $this->jid,'sid'=>$this->sid));
		}
		$cart_arr = json_decode($cart,true);
		
		$total_number = 0;
		$total_price  = 0;
		$total_yprice  = 0;
		$vip_fl = 0;
		$p_fl = 0;
		$org = C('ORDER_RETURN_GRADE');
		$org = $org[0]/100;
		
		foreach($cart_arr as $k=>$v){
			$total_number += $v['number'];
			$total_price  += $v['number'] * $v['gprice'];
			$total_yprice += $v['number'] * $v['yprice'];
			$vip_fl       += $v['number'] * $v['gprice'] * $v['gvrebate'] * $org;
			//$cart_arr[$k]['t_price'] = $v['number'] * $v['gprice'];
			//$cart_arr[$k]['y_price'] = $v['number'] * $v['yprice'];
		}
		$vip_fl = round($vip_fl/100,2);
		$p_fl   = round($vip_fl*C('USER_RATION_VIP'),2);
		
		$flu_usertype = M('fl_user')->where(array('flu_userid'=>$this->userid))->getField('flu_usertype');
		if($flu_usertype == '1'){//vip会员
			$user_fl = $vip_fl;
			$is_vip = 1;
		}else{
			$user_fl = $p_fl;
			$is_vip = 0;
		}
		
		//查询我的优惠券
		$coupon_user = M('voucher_user');
		$opt = array(
				'u.mid' => $this->userid,
				'u.vu_price' => array('gt',0),
				'v.vu_status' => 1,
				'v.vu_jid' => $jid,
				'v.vu_sid' => array('like','%,'.$sid.',%'),
				'v.vu_stime' => array('elt',date("Y-m-d H:i:s")),
				'v.vu_etime' => array('egt',date("Y-m-d H:i:s")),
				'v.vu_money'  => array('elt',$total_price),
		);
		$coupon_list = $coupon_user->alias('u')->join('azd_voucher v on u.vu_id=v.vu_id')->where($opt)->field('v.vu_name,u.vu_price,u.uvid')->select();
		$this->assign('coupon_list',$coupon_list);
		
		//查询收货地址
		$receivingid = I('receivingid',0);
		if($receivingid > 0){
			$address_info = M('fl_receiving')->where(array('flr_userid'=>$this->userid,'flr_receivingid'=>$receivingid))->find();
		}
		if(empty($address_info)){
			$address_info = M('fl_receiving')->where(array('flr_userid'=>$this->userid,'flr_default'=>1))->find();
		}
		if(empty($address_info)){
			$address_info = M('fl_receiving')->where(array('flr_userid'=>$this->userid))->find();
		}
		$this->assign('receivingid',$receivingid);
		$this->assign('address_info',$address_info);
		
		$linkurl = url_param_encrypt(U('Flow/confirm@flapp',array('jid'=>$jid,'sid'=>$sid)),'E');
		
		$this->assign('linkurl',$linkurl);
		
		$this->assign('jid',$jid);
		$this->assign('sid',$sid);
		$this->assign('total_number',$total_number);
		$this->assign('total_price',$total_price);
		$this->assign('total_yprice',$total_yprice);
		$this->assign('vip_fl',$vip_fl);
		$this->assign('p_fl',$p_fl);
		$this->assign('user_fl',$user_fl);
		$this->assign('v2p',C('USER_RATION_VIP'));
		$this->assign('org',$org);
		$this->assign('goods_list',$cart_arr);
		$this->assign('is_vip',$is_vip);
		$this->display();
	}
	
	/**
	 * 预约
	 */
	public function reserve() {
		
		$jid = I('jid',0);
		$sid = I('sid',0);
		
		$shop = M("shop");
				
		$goods = M('goods');
		$opt = array(
				'g.sid'    => $sid,
				'g.gtype' => 1,
				'g.gstatus' => 1,
				'g.gstock' => array('gt',0),
				'c.ctype' => 2,
				'c.status' => 1,
		);
		
		$goods_list = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->where($opt)->order('g.gorder')->select();
		$result_list = array();
		foreach($goods_list as $k=>$v){
			if(isset($result_list[$v['cid']])){
				$result_list[$v['cid']]['list'][] = $v;
			}else{
				$result_list[$v['cid']]['cid'] = $v["cid"];
				$result_list[$v['cid']]['cname'] = $v["cname"];
				$result_list[$v['cid']]['list'][] = $v;
			}
		}
		
		$shop_name = $shop->where(array('sid'=>$sid))->getField('sname');
		
		$this->assign('sid',$sid);
		$this->assign('jid',$jid);
		$this->assign('shop_name',$shop_name);
		$this->assign('result_list',$result_list);
		$this->display();
	}
	
	/*订座确认
	 *
	*
	* */
	public function seatConfirm(){
		$jid = I('jid',0);
		$sid = I('sid',0);
		
		$seat = $_COOKIE['seat'];
		if(!$seat || $seat == '{}'){
			//$this->redirect('Seat/index', array('jid' => $this->jid,'sid'=>$this->sid));
		}
		$seat_arr = json_decode($seat,true);
		$seat_key = array_keys($seat_arr);
	
		$goods = M('goods');
		$opt = array(
				'gid' => array('in',join(',',$seat_key))
		);
	
		$goods_list = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->where($opt)->select();
	
		$shop = M("shop");
		$opt = array(
				'sid' => $sid,
		);
		$shop_info = $shop->where($opt)->find();
		
		$linkurl = url_param_encrypt(U('Flow/seatConfirm@flapp',array('jid'=>$jid,'sid'=>$sid)),'E');
		
		$this->assign('linkurl',$linkurl);
		
		$this->assign('sid',$sid);
		$this->assign('jid',$jid);
		$this->assign('shop_info',$shop_info);
		$this->assign('goods_list',$goods_list);
		
		$this->display();
	}
	
	/*订座提交
	 *
	*
	* */
	public function submitSeat(){
		$jid = I('get.jid',0);
		$sid = I('get.sid',0);
			
		$seat_date = I('seat_date');
		$seat_name = I('seat_name');
		$seat_tel =  I('seat_tel');
		$seat_code = I('seat_code');
	
		$seat = $_COOKIE['seat'];
		if(!$seat || $seat == '{}'){
			$data = array(
				'msg' => 'false',
			);
			$this->ajaxReturn($data);
		}
		$seat_arr = json_decode($seat,true);
		$seat_key = array_keys($seat_arr);
	
		//验证码
		
		$code = session('verify_code');
	
		if($code){
			$code = json_decode($code,true);
			if($seat_code != $code["verify_code"]){
				$data = array(
						'msg' => 'verify_err1',
				);
				$this->ajaxReturn($data);
			}
		}else{
			$data = array(
					'msg' => 'verify_err2',
			);
			$this->ajaxReturn($data);
		}
	
		$oprice = 0;
		$goods = M('goods');
		$opt = array(
				'gid' => array('in',join(',',$seat_key))
		);
	
		$goods_list = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->where($opt)->field('g.gid,g.gname,g.gimg,g.goprice,g.gdprice,g.gdescription')->select();
		if(empty($goods_list)){
			$data = array(
					'msg' => 'false',
			);
			$this->ajaxReturn($data);
		}
	
		foreach($goods_list as $k=>$v){
			$goods_list[$k]['date'] = $seat_date;
			$oprice += $v['goprice'] - $v['gdprice'];
		}
		
		$oid = orderNumber();
	
		$order = M('fl_order');
		$opt = array(
				'flo_id' => $oid,
				'flo_sid' => $sid,
				'flo_jid' => $jid,
				'flo_uid' => $this->userid,
				'flo_receivingid' => 0,
				'flo_dstime' => date("Y-m-d H:i:s"),
				'flo_dstatus' => 1,
				'flo_price'   => $oprice,
				'flo_pstatus' => 0,
				'flo_gtype' => 2,
				'flo_ptype' => 0,
				'flo_pway' => 'scancode',
		);
		$order->add($opt);
	
		//订单商品
		$app_con = array();
		foreach($goods_list as $k=>$v){
			$gt = array(
					'flg_gid' => $v['gid'],
					'flg_oid' => $oid,
					'flg_name' => $v['gname'],
					'flg_gdescription' => $v['gdescription'],
					'flg_goprice' => $v['goprice'],
					'flg_gdprice' => $v['goprice'] - $v['gdprice'],
					'flg_number' =>  1,
					'flg_img' => $v['gimg'],
					'flg_date' => $seat_date,
					'flg_grebate' => 0
			);
			M('fl_gsnapshot')->add($gt);
			$app_con[] = $v['gname'];
		}
		$appcontent = implode(',',$app_con);
	
		/*库存*/
	
		cookie('seat',null);
		session('verify_code',null);
	
		$data = array(
				'msg' => 'true',
		);
	
		$this->ajaxReturn($data);
	}
	
	/**
	 * 提交订单
	 */
	public function submit(){
		$jid = I('get.jid',0);
		$sid = I('get.sid',0);
		$remarks = I('remarks','');
		$o_name = I('o_name','');
		$o_phone = I('o_phone','');
		$o_address = I('o_address','');
		$used_coupon = I('used_coupon');
		
		$cart = $_COOKIE['cart'];
		if(!$cart || $cart == '{}'){
			$this->ajaxReturn(array('msg' => 'nogoods','u'=>U('My/order@flapp',array('returnurl'=>url_param_encrypt(U('Shop/shopInfo@flapp',array('sid'=>$sid)),'E')))));
		}
		$cart_arr = json_decode($cart,true);
		$cart_key = array_keys($cart_arr);
	
		$goods = M('goods');
		$opt = array(
				'gid' => array('in',join(',',$cart_key))
		);
		
		$goods_list = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->where($opt)->field('g.gid,g.gname,g.gimg,g.goprice,g.gdprice,g.gstock,g.gdescription,g.gvrebate')->select();
		
		if(empty($goods_list))$this->ajaxReturn(array('msg' => 'false','content'=>'您添加的商品不存在'));
		//if(!$this->userid)$this->ajaxReturn(array("msg" => "false","content" => "操作失败，请先登录"));
		$flo_backprice = 0;//返利金额
		$total_price  = 0;
		foreach($goods_list as $k=>$v){
			$g_price = $v['gdprice'] > 0 ? $v['gdprice'] : $v['goprice'];
			if($v['gstock']==-1)$v['gstock']=10000000;
			$g_number   = $cart_arr[$v['gid']]['number'] > $v['gstock'] ? $v['gstock'] : $cart_arr[$v['gid']]['number'] ;
			$total_price += $g_price*$g_number;
			$goods_list[$k]["gnum"] = $g_number;
			$flo_backprice += round($g_price*$g_number*$v['gvrebate']/100,2);
		//  返利比例修改成固定金额
			//$flo_backprice += $v['gvrebate'];
		}
		//$flu_usertype = M('fl_user')->where(array('flu_userid'=>$this->userid))->getField('flu_usertype');
		//if($flu_usertype == '0'){//普通会员
			//$flo_backprice = round($flo_backprice*C('USER_RATION_VIP'),2);
		//}
				
		$oprice = $total_price;
		if($used_coupon){
			$opt = array(
					'uvid' => $used_coupon,
					'mid'  => $this->userid
			);
			$coupon_price = M('voucher_user')->where($opt)->getField('vu_price');
			if($coupon_price > 0){
				$oprice = $total_price - $coupon_price;
				if($oprice < 0){
					$oprice = 0;
				}
			}
		}
		
		$oid = orderNumber();
		$order = M('fl_order');
		$opt = array(
				'flo_id' => $oid,
				'flo_sid' => $sid,
				'flo_jid' => $jid,
				'flo_uid' => $this->userid,
				'flo_receivingid' => 0,
				'flo_dstime' => date("Y-m-d H:i:s"),
				'flo_dstatus' => 1,
				'flo_price'   => $oprice,
				'flo_pstatus' => 0,
				'flo_gtype' => 1,
				'flo_ptype' => 1,//全民返利，都是 线上 支付
				'flo_pway' => 'scancode',
				'flo_remarks' => $remarks,
				'flo_backprice' => $flo_backprice,
				'flo_name'  => $o_name,
				'flo_phone'  => $o_phone,
				'flo_address'  => $o_address,
		);
		if($oprice == 0){
			$opt['flo_pstatus'] = 1;
			$opt['flo_pstime'] = date("Y-m-d H:i:s");
		}
		$o = $order->add($opt);
		if(!$o){
			$this->ajaxReturn(array('msg' => 'false','content'=>'订单创建失败'));
		}
		//订单商品
	
		$app_con = array();
		foreach($goods_list as $k=>$v){
			$gt = array(
					'flg_gid' => $v['gid'],
					'flg_oid' => $oid,
					'flg_name' => $v['gname'],
					'flg_gdescription' => $v['gdescription'],
					'flg_goprice' => $v['goprice'],
					'flg_gdprice' => $v['gdprice'] > 0 ? $v['gdprice'] : $v['goprice'],
					'flg_number' =>  $v['gnum'],
					'flg_img' => $v['gimg'],
					'flg_grebate' => $v['gvrebate']
			);
			M('fl_gsnapshot')->add($gt);
			
			$app_con[] = $v['gname'];
		}
		
		//库存
		D('Mobile/Goods')->reduceRepertory($oid,'setDec',2);
		/*优惠券*/
		if($coupon_price > 0){
			$o_price = $total_price >= $coupon_price ? $coupon_price : $total_price;
			$opt = array(
					'uvid' => $used_coupon,
					'mid'  => $this->userid
			);
			$vu_id = M('voucher_user')->where($opt)->getField('vu_id');
			$opt = array(
					'mid' => $this->userid,
					'vu_id' => $vu_id,
					'o_id'  => $oid,
					'o_price' => $o_price
			);
			M('voucher_order')->add($opt);
		
			//$vu_price = $coupon_price - $total_price > 0 ? $coupon_price - $total_price : 0;
			$vu_price = 0;
			M('voucher_user')->where(array('uvid' => $used_coupon,'mid'  => $this->userid))->save(array('vu_price'=>$vu_price));
		}
		
		cookie('cart',null);
		
		$appcontent = implode(',',$app_con);
	/*
		//提交成功,把消息发送到商家APP里
		$appmsg = array();
		$appmsg['jid'] = $jid;
		$appmsg['sid'] = $sid;
		$appmsg['avatar'] = M('user')->where("u_id=".$this->userid)->getField('u_avatar');
		$appmsg['title'] = \Org\Util\String::msubstr($appcontent, 0, 10);
		$appmsg['content'] = $appcontent;
		$appmsg['addtime'] = date('Y-m-d H:i:s');
		$appmsg['type'] = 2;
		M('appmsg')->add($appmsg);*/
		//订单打印
		D('Print')->doFlPrint($oid,1);
		
		$data = array(
				"msg" => "true",
				"oid" => $oid
		);
		
		$this->ajaxReturn($data);
	}
	
	/*发送验证码
	 * */
	public function verify(){
	
		$tel = I('tel');
		if(empty($tel)){
			$data = array(
					'msg' => 'false',
			);
			$this->ajaxReturn($data);
		}
		$code = session('verify_code');
	
		if($code){
			$code = json_decode($code,true);
			if(time() - $code["time"] < 60){
				$data = array(
						'msg' => 'false',
				);
				$this->ajaxReturn($data);
			}
		}
	
		$verify_code = rand(100000, 999999);
		$opt = array(
				'verify_code' => $verify_code,
				'time'  => time()
		);
		session('verify_code',json_encode($opt));
	
		flsendmsg($tel, $verify_code);
	
		$data = array(
				'msg' => 'true',
		);
		$this->ajaxReturn($data);
	}
	
	/*** 从app到H5页面的地址 ***/
	public function appToView(){
		$get = I('get.');//返回html的地址
		$skipurl = url_param_encrypt($get['linkurl'],'D');
		unset($get['linkurl']);
		if(count($get))$urlstr = http_build_query($get);
		if($skipurl){
			$tourl = $skipurl.'?'.$urlstr;
		}elseif($linkurl){
			$tourl =urldecode($get['linkurl']) . '?'.$urlstr;
		}
		if($tourl)header("location:".$tourl);
	}
}