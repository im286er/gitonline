<?php
namespace Mobile\Controller;
/*订座
 *
*
* */
class SeatController extends MobileController {
	
	public $action_name = 'Choose';
	
	/*订座主页
	 *
	*
	* */
	public function index(){
		
		//$this->redirect('Seat/confirm', array('jid' => $this->jid));
		//exit;
		
		$gstock = I('gstock',3);
		
		$shop = M("shop");
		$opt = array(
				'jid' => $this->jid,
				'status' => '1'
		);
		$shop_count = $shop->where($opt)->count();
		
		//判断是否有多家分店 start
		if($this->sid == 0){
			//如果有多家 跳转到门店列表页
			if($shop_count == 1){
				//单一门店直接显示
				$sid = $shop->where($opt)->getField('sid');
				$this->sid = $sid > 0 ? $sid : 0;
				$this->assign('sid',$this->sid);
			
			}else{
				$this->redirect('Shop/index', array('jid' => $this->jid,'mod'=>'Seat'));
			}
		}
		//判断是否有多家分店 end
		

		//判断是全民返利过来的就从回到全民返利网页部分
		if(cookie('opentype')=='flapp'){
			$this->redirect('Flow/reserve@flapp', array('jid' => $this->jid,'sid'=>$this->sid));
			exit;
		}

		$goods = M('goods');
		$opt = array(
				'g.sid'    => $this->sid,
				'g.gtype' => 1,
				'g.gstatus' => 1,
				'c.ctype' => 2,
				'c.status' => 1,
		);
		if($gstock != 3){
			$opt['g.gstock'] = $gstock;
		}

		$goods_list = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->where($opt)->order('g.gorder asc,gid asc')->select();
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
		
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'InfoMenu2Name.php') && $module2name=file_get_contents($path.'InfoMenu2Name.php');
		$page_name = $module2name ? $module2name : '在线订座';
		$this->assign('page_name',$page_name);
		
		if($shop_count == 1){
			$page_url = U('Index/index',array('jid'=>$this->jid));
		}else{
			$page_url = U('Shop/index', array('jid' => $this->jid,'mod'=>'Seat'));
		}
		$this->assign('page_url',$page_url);
		$this->assign('gstock',$gstock);
		$this->assign('result_list',$result_list);
		$this->mydisplay();
	}
	
	/*订座确认
	 *
	*
	* */
	public function confirm(){
			
		$shop = M("shop");
		$opt_list = array(
				'jid' => $this->jid,
				'status' => '1'
		);
		$shop_list = $shop->where($opt_list)->select();
		$this->assign('shop_list',$shop_list);
		$shop_info = $shop_list[0];
		$this->sid = $shop_info['sid'];
		
		$goods_list = M('goods')->where(array('sid'=>$this->sid,'gstatus'=>1,'gtype'=>1))->select();
		
		$linkurl = $this->isApp == 1 ? url_param_encrypt(U('Seat/confirm@yd',array('jid'=>$this->jid,'sid'=>$this->sid)),'E') : U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('Mobile/Seat/confirm',array('jid'=>$this->jid,'sid'=>$this->sid)),'E'),'returnurl'=>url_param_encrypt(U('Mobile/Seat/confirm',array('jid'=>$this->jid,'sid'=>$this->sid)),'E')));
		$this->assign('linkurl',$linkurl);
		
		$location = explode(",",cookie('location'));
		//$map_distance = GetDistance($shop_info['lat'],$shop_info['lng'],$location[1],$location[0]);
		$map_distance = GetDistance($location[0],$location[1],$shop_info['lng'],$shop_info['lat']);
		$this->assign('map_distance',$map_distance.'km');
		
		$day_arr 	= array('0'=>'星期日','1'=>'星期一','2'=>'星期二','3'=>'星期三','4'=>'星期四','5'=>'星期五','6'=>'星期六');
		$time 		= time();
		$day_m 		= 24*60*60;
		$html_date 	= '';
		for($i = 0; $i < 7; $i++ ){
			$day_name = date("w",$time + $day_m*$i);
			if($i<1){
				$html_date .= '<div class="swiper-slide" date_format="'.date("Y-m-d",$time + $day_m*$i).'">今天<br>'.$day_arr[$day_name].'</div>';
			}else{
				$html_date .= '<div class="swiper-slide" date_format="'.date("Y-m-d",$time + $day_m*$i).'">'.date("m月d日",$time + $day_m*$i).'<br>'.$day_arr[$day_name].'</div>';
			}			
		}
		$this->assign('html_date',$html_date);
		
		$this->assign('page_url',U('Seat/index', array('jid' => $this->jid)));
		$this->assign('user',M('FlUser')->find($this->mid));

		$this->assign('shop_info',$shop_info);
		$this->assign('goods_list',$goods_list);
		$page_name = '提交订单';
		$this->assign('page_name',$page_name);
		$this->mydisplay();
	}
	
	/*订座提交
	 *
	*
	* */
	public function submitSeat(){
		
		//没有登录跳转到登录页
		if(empty($this->mid)){
			$data = array(
					'msg' => 'false1',
			);
			$this->ajaxReturn($data);
		}
		
		$seat_date = I('seat_date');
		$seat_name = I('seat_name');
		$seat_tel =  I('seat_tel');
		$seat_code = I('seat_code');
		$o_remarks = I('o_remarks');
		$seat_number =  I('seat_number');
		$sid =  I('shop_id',0);
		$seat_goods = I('seat_goods');
		
		//验证码
		$code = session('verify_code');
		/*
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
		*/
		$oprice = 0;
		$goods = M('goods');
		$opt = array(
				'gid' => $seat_goods,
				'gtype'   => 1, 
				'gstatus' => 1
		);
		
		$goods_list = $goods->where($opt)->field('gid,gname,gimg,goprice,gdprice,gdescription')->select();
		if(empty($goods_list)){
			$data = array(
					'msg' => 'false3',
			);
			$this->ajaxReturn($data);
		}
		
		foreach($goods_list as $k=>$v){
			$goods_list[$k]['date'] = $seat_date;
			$oprice += $v['goprice'] - $v['gdprice'];
		}
		
		//$otc = M("merchant")->where(array('jid'=>$this->jid))->getField("mtc");
		//$oemt = userAgent($_SERVER["HTTP_USER_AGENT"]);
		
		$oid = orderNumber();
		
		$order = M('order');
		$opt = array(
				'o_id' => $oid,
				'o_sid' => $sid,
				'o_jid' => $this->jid,
				'o_uid' => $this->mid,
				'o_type' => 0,
				'o_name' => $seat_name,
				'o_phone' => $seat_tel,
				'o_dstime' => date("Y-m-d H:i:s"),
				'o_dstatus' => 1,
				'o_pstatus' => 0,
				'o_price'   => $oprice,
				'o_gtype'   =>  'Seat',
				'o_table'   => 'goods_snapshot',
				'o_remarks' => $o_remarks
		);
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
					'sp_gdprice' => $v['goprice'] - $v['gdprice'],
					'sp_number' =>  $seat_number >= 1 ? $seat_number : 1,
					'sp_img' => $v['gimg'],
					'sp_date' => $seat_date,
			);
			M('goods_snapshot')->add($gt);
			$app_con[] = $v['gname'];
			$total_num += $seat_number;
		}
		$appcontent = implode(',',$app_con);
		
		/*库存*/
		
		cookie('seat',null);
		session('verify_code',null);
		
		$data = array(
				'msg' => 'true',
		);
		
		
		//提交成功,把消息发送到商家APP里
		$appmsg = array();
		$appmsg['jid'] = $this->jid;
		$appmsg['sid'] = $sid;
		$appmsg['avatar'] = M('FlUser')->where("flu_userid=".$this->mid)->getField('flu_avatar');
		$appmsg['title'] = \Org\Util\String::msubstr($appcontent, 0, 10);
		$appmsg['content'] = $appcontent;
		$appmsg['addtime'] = date('Y-m-d H:i:s');
		$appmsg['type'] = 2;
		$appmsg_extend = array(
				'type' => 2,
				'number' => $total_num
		);
		$appmsg['extend'] = serialize($appmsg_extend);
		M('appmsg')->add($appmsg);
		//推送消息到商家app
		D('Tsbind')->send($oid);
		//订单打印
		D('Print')->doPrint($oid,1);
		
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
		
		sendmsg($tel, $verify_code);
		
		$data = array(
				'msg' => 'true',
		);
		$this->ajaxReturn($data);
	}

	/*计算坐标相隔距离*/
	public function get_distance(){
		$location = explode(",",cookie('location'));
		//$map_distance = GetDistance(I('latitude'),I('longitude'),$location[1],$location[0]);
		$map_distance = GetDistance($location[0],$location[1],I('longitude'),I('latitude'));
		$sid = I('sid',0);
		$goods_list = M('goods')->where(array('sid'=>$sid,'gstatus'=>1,'gtype'=>1))->select();
		$m = '<div class="swiper-wrapper">';
		foreach($goods_list as $k=>$v){
			$m .= '<div class="swiper-slide" gid="'.$v['gid'].'">'.$v['gname'].'</div>';
		}
		$m .= '</div>';
		
		$r = array(
			'l' => $map_distance.'km',
			'm' => $m
		);
		$this->ajaxReturn($r);
	}
}