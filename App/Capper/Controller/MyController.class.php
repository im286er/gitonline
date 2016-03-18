<?php
namespace Capper\Controller;

/* * *用户中心 * * */
class MyController extends CapperController {
	
	//public $myId;//当前用户的id
	public $orderStatus = array(
		'1'=>array('name'=>'待处理','css'=>'status-gray'),
		'2'=>array('name'=>'待处理','css'=>'status-gray'),
		'3'=>array('name'=>'已接受','css'=>'status-green'),
		'4'=>array('name'=>'已完成','css'=>'status-green'),
		'5'=>array('name'=>'已取消','css'=>'status-red'),
	);
	
	//public function _initialize(){
		//header('content-type:text/html;charset=utf-8');
		//parent::_initialize();
		//$this->myId = 111;
		//$this->assign('myId',$this->myId);
	//}
	
	public function index(){
		
	}
	
	public function invite(){
		$inviter = I('inviter');
		$inviterInfo = D('FlUser')->find($inviter);
		if(!$inviterInfo)redirect(U('Token/inviteDown@flapp'));
		if(IS_POST){
			$mobile = I('post.mymobile','','trim');
			$mobile or die( JSON( array('error'=>'请输入您的手机号码') ) );
			if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[012356789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $mobile) ) {
				die( JSON( array('error'=>'手机号格式不正确') ) );
			}
			if($mobile==$inviterInfo['flu_phone'])die( JSON( array('error'=>'不能自己邀请自己哦') ) );
			$data = array();
			$data['inviter'] = $inviter;
			$data['jid'] = I('post.jid','','trim');
			$data['ip'] = get_client_ip();
			$data['marking'] = $mobile;
			$data['addtime'] = time();
			$data['type'] = I('get.type','','intval')==1?1:0;//邀请类型
			$data['status'] = 0;
			if($inviter && $inviterInfo)D('FlInvite')->add($data);
			die(JSON(array('url'=>U('Token/inviteDown@flapp'))));
		}
		$this->assign('inviterInfo',$inviterInfo);
		$this->display();
	}
	
	/***我的活动**/
	public function activity(){
		$activityList = M('fl_active')->where(array('fla_userid'=>$this->userid))->select();
		$this->assign(array('activityList'=>$activityList));
		$this->display();
	}
	
	/***我的收获地址**/
	public function address(){
		$addressList = M('fl_receiving')->where(array('flr_userid'=>$this->userid))->order('flr_default desc')->select();
		$this->assign(array('addressList'=>$addressList));
		$this->display();
	}
	
	/***添加收获地址**/
	public function addressAdd(){
		print_r('添加收货地址');
	}
	
	/***编辑收获地址**/
	public function addressEdit(){
		print_r('修改收货地址');
	}
	
	/***我的预定**/
	public function booking(){
		$returnurl = '';
		if(I('sid') > 0){
			$returnurl = U('Shop/shopInfo',array('sid'=>I('sid')));
		}
		//预约订单
		$bookingList = M('fl_order')->where(array('flo_uid'=>$this->userid,'flo_gtype'=>2))->order("flo_dstime desc")->select();
		//预约订单增加snapshot
		if(!empty($bookingList)){
			$orderId = array();
			foreach($bookingList as $v){
				$orderId[] = $v['flo_id'];
			}
			$snapshotList = $this->_getSnapshot($orderId);
			foreach($bookingList as $k=>$v){
				if(!isset($snapshotList[$v['flo_id']])){
					$bookingList[$k]['snapshot'] = array();
				}else{
					$bookingList[$k]['snapshot'] = $snapshotList[$v['flo_id']];
				}
				$bookingList[$k]['mservetel'] = M('shop')->where(array('sid'=>$v['flo_sid']))->getField('mservetel');
			}
		}
		$this->assign('returnurl',$returnurl);
		$this->assign(array('bookingList'=>$bookingList));
		$this->assign(array('orderStatus'=>$this->orderStatus));
		$this->display();
	}
	
	/***我的订单**/
	public function order(){
		
		$org = C('ORDER_RETURN_GRADE');
		$org = $org[0]/100;
		
		//购买订单
		$orderList = M('fl_order')->where(array('flo_uid'=>$this->userid,'flo_gtype'=>1))->order("flo_dstime desc")->select();
		$utype     = M('fl_user')->where(array('flu_userid'=>$this->userid))->getField('flu_usertype');
		//购买订单增加snapshot
		if(!empty($orderList)){
			$orderId = array();
			foreach($orderList as $v){
				$orderId[] = $v['flo_id'];
			}
			$snapshotList = $this->_getSnapshot($orderId);
			foreach($orderList as $k=>$v){
				if(!isset($snapshotList[$v['flo_id']])){
					$orderList[$k]['snapshot'] = array();
				}else{
					$orderList[$k]['snapshot'] = $snapshotList[$v['flo_id']];
				}
				if($v['flo_pstatus'] == 0){
					$orderList[$k]['stu'] = '未支付';
					$orderList[$k]['css'] = 'status-gray';
				}else{
					if(in_array($v['flo_dstatus'],array(1,2,3))){
						$orderList[$k]['stu'] = '待消费';
						$orderList[$k]['css'] = 'status-green';
					}elseif($v['flo_dstatus'] == 4){
						$orderList[$k]['stu'] = '已消费';
						$orderList[$k]['css'] = 'status-green';
					}else{
						$orderList[$k]['stu'] = '已取消';
						$orderList[$k]['css'] = 'status-red';
					}
				}
				//$orderList[$k]['flo_backprice'] = round($v['flo_backprice']*$org,2);
				$orderList[$k]['flo_backprice_user'] = M('fl_translation')->where(array('flt_uid'=>$v['flo_uid'],'flt_oid'=>$v['flo_id'],'flt_type'=>0))->getField('flt_balance');
				if(empty($orderList[$k]['flo_backprice_user'])){
					
					$orderList[$k]['flo_backprice_user'] = $utype=='1' ?  round($v['flo_backprice']*$org,2) : round($v['flo_backprice']*$org*C('USER_RATION_VIP'),2);
					
				}
			}
		}
		
		$this->assign(array('orderList'=>$orderList));
		$this->display();
	}
	
	/***我的收藏**/
	public function favorite(){
		$org = C('ORDER_RETURN_GRADE');
		$org = $org[0]/100;
		
		$coordinate = I('coordinate');
		$sql = "SELECT f.*,max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_goods as g,azd_fl_collection as f where f.flc_shopid=g.sid and g.gvrebate>0 AND f.flc_userid = ".$this->userid." and g.gstatus=1 GROUP BY f.flc_shopid order by f.flc_addtime desc ";
		$Model = new \Think\Model();
		$shops = $Model->query($sql);
		
		$location = explode(',',$coordinate);
		foreach($shops as $k2=>$v2){
			if($coordinate){
				$d = GetDistance($location[0],$location[1],$v2['flc_shoplng'],$v2['flc_shoplat']);
				$shops[$k2]['distance'] = $d.'km';
			}else{
				$shops[$k2]['distance'] = '';
			}
			$shops[$k2]['v_fl'] = intval($v2['gfl']/100*$org);
			$shops[$k2]['p_fl'] = intval($shops[$k2]['v_fl']*C('USER_RATION_VIP'));
		}
		$this->assign('shops',$shops);
		$this->display();
	}
	
	/***我的消费码**/
	public function qrcode(){
		$orderId = I('id');
		$sid = M('fl_order')->where(array('flo_id'=>$orderId))->getField('flo_sid');
		$mservetel = M('shop')->where(array('sid'=>$sid))->getField('mservetel');
		//$qcUrl = 'www.baidu.com';
		//$qcImg = $this->createQrcode($qcUrl);
		$orderInfo = array(
			'id'=>$orderId,
			'dstime'=>date('Y-m-d H:i:s',I('dstime',0)),
			'mservetel'=>$mservetel
			//'qcImg'=>$qcImg,
		);
		
		$this->assign('orderInfo',$orderInfo);
		$this->display();
	}
	
	
	public function makeQrcode(){
		$orderId = I('orderId');
		$size = 10;
		$qcUrl = U('Order/orderScan@app',array('oid'=>$orderId));
		vendor("phpqrcode.phpqrcode");
		$QRcode = new \QRcode();
		echo $QRcode::png($qcUrl, false, 'H', $size);
	}
	
	public function changeOrder(){
		$order_id = I('order_id',0);
		$opt = array(
			'flo_id' => $order_id,
			'flo_uid'       =>  $this->userid
		);
		M('fl_order')->where($opt)->save(array('flo_dstatus'=>5));
		$data = array(
			'msg' => 'true',
		);
		$this->ajaxReturn($data);
	}
	
	/***我的钱包**/
	public function wallet(){
		$walletInfo = M('fl_user')
		->field('flu_userid,flu_balance')
		->where(array('flu_userid'=>$this->userid))
		->find();
		$this->assign(array('walletInfo'=>$walletInfo));
		$this->display();
	}
	
	/**
	 * 获取订单内商品内容(名称,数量,返利比例...)
	 * 内部调用
	 * @param array $order
	 * @return array
	 */
	private function _getSnapshot($order=array()){
		$snapshotList = array();
		$result = M('fl_gsnapshot')
		->field('flg_oid,flg_name,flg_gdprice,flg_number,flg_grebate')
		->where(array("flg_oid in (".implode(',', $order).")"))
		->select();
		foreach((array)$result as $v){
			$v['rebateAmount'] = $v['flg_gdprice']*$v['flg_number']*$v['flg_grebate'];
			$snapshotList[$v['flg_oid']][] = $v;
		}
		return $snapshotList;
	}
	
	/**
	 * 生成二维码图片返回图片服务器路径
	 * @param string $url
	 * @param string $storeDir
	 * @param number $size
	 * @return string
	 */
	public function createQrcode($url='',$storeDir='',$size=10){
		$storeDir=='' && $storeDir = 'Public/Rebateapp/payQrcode/';
		!is_dir($storeDir) && mkdirs($storeDir);
		$file = $storeDir.md5($url).'.png';
		if(!file_exists($file)) {
			vendor("phpqrcode.phpqrcode");
			$QRcode = new \QRcode();
			$QRcode::png($url, $file, 'H', $size);
		}
		return 'http://'.I('server.HTTP_HOST').'/'.$file;
	}
	
	
	/** vip页面 **/
	public function vip(){
		$user_info = M('fl_user')->where(array('flu_userid'=>$this->userid))->find();
		if(I('post.type')=='is_vip'){
			$user_info['flu_usertype']==1?$this->ajaxReturn(array('code'=>1,'msg'=>'您已经升级为VIP会员了')):$this->ajaxReturn(array('code'=>0,'msg'=>'正在创建订单，请稍后...'));
		}
		$this->assign('user_info',$user_info);
		if($user_info['flu_usertype'] == '1'){
			$tpl = "vipyes";
		}else{
			$this->assign('linkurl',$linkurl);
			$tpl = "vipno";
		}
		$this->display($tpl);
	}
	
	/***任务奖励**/
	public function quest(){
		$userid = $this->userid;
		$FlQuest = D('FlQuest');
		$inviteRewards = $FlQuest->inviteRewards();
		$this->assign('inviteRewards',$inviteRewards);
		$consumptionRewards = $FlQuest->consumptionRewards();
		$this->assign('consumptionRewards',$consumptionRewards);
		$uquest = $FlQuest->where("fl_uid = {$userid}")->getField('id,questname');//查找已经完成的任务
		$cinvite = M('FlTranslation')->where(array('flt_uid'=>$userid,'flt_type'=>2,'flt_tuserid'=>array('gt',0)))->count();//统计邀请成功的人数
		$corder = M('FlTranslation')->alias('ft')->join('azd_fl_order fo on ft.flt_oid=fo.flo_id')->where(array('ft.flt_type'=>'0','ft.flt_uid'=>$userid))->order('ft.flt_tid asc')->field('ft.flt_tid,fo.flo_backprice')->count();//成功返利订单数
		if(IS_POST){
			$user_info = M('fl_user')->where(array('flu_userid'=>$userid))->find();
			if($user_info['flu_usertype']<1)$this->ajaxReturn(array('msg'=>'请先升级为VIP会员再领取任务','status'=>0),'JSON');
			$questname =  I('post.questname');
			$questid =  I('post.questid');
			if(!$questname || !$questid)$this->ajaxReturn(array('msg'=>'请选择领取的任务','status'=>0),'JSON');
			if(in_array($questname,$uquest) && $questname != 'consumption3')$this->ajaxReturn(array('msg'=>'任务已经领取','status'=>0),'JSON');
			$configQuest = $FlQuest->configQuest($questid);
			if($configQuest==false)$this->ajaxReturn(array('msg'=>'任务未找到','status'=>0),'JSON');
			if($configQuest['maxman']){
				if($configQuest['maxman'] > $cinvite)$this->ajaxReturn(array('msg'=>'对不起您邀请的人数未达到要求','status'=>0),'JSON');
				$result = $FlQuest->inviteQuest($userid,$questid);//处理邀请人数的任务
			}elseif($configQuest['rate']){
				$result = $FlQuest->consumptionQuest($userid,$questid,$corder);//处理返利订单的任务
			}
			$this->ajaxReturn($result,'JSON');
		}
		if(in_array('consumption3',$uquest) && $corder > 3)$consumption3 = D('FlQuest')->verifyAward($userid);
		$this->assign('consumption3',$consumption3);
		$this->assign('uquest',$uquest);
		$this->assign('cinvite',$cinvite);
		$this->assign('corder',$corder);
		$this->display();
	}


	/* * 邀请好友* */
	public function share(){
		$this->display();
	}
	
	/*faq*/
	public function faq(){
		$id = I('id');
		$tpl = 'faq'.$id;
		$this->display($tpl);
	}
	
	//业务员
	public function yewu(){
		$this->display();
	}
	
	/*关于我们*/
	public function abouts(){
		$this->display();
	}
}