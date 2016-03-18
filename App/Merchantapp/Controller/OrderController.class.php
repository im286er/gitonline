<?php
namespace Merchantapp\Controller;

class OrderController extends MerchantappController {
	
	//订单首页
	public function index() {
		//如果品牌登录并且品牌下的分店总数超过1个，则显示列表
		if( $this->type==1 ) { 
			$this->assign("shoplist", M('shop')->where("status='1' and jid=".$this->jid)->select());			
		} else {
			$sid = $this->sid ? $this->sid : $this->sidlist[0];
			redirect('/Order/orderlist/sid/'.$sid);
		}
		$this->display();
	}
	
	//查个某个分店的订单列表
	public function orderlist() {
		$sid = I('get.sid', 0, 'intval');
		if( !$sid || !in_array($sid, $this->sidlist) ) E('你无权进行此操作');
		$shopinfo = M('shop')->where("sid=".$sid)->find();
		$this->assign('shopinfo', $shopinfo);
		
		!$_GET['type'] && $_GET['type']=0;
		$where['o_sid'] = $sid;
		if( I('get.type', 0, 'intval') != 0 )
		{
			$where['o_dstatus'] = I('get.type', 0, 'intval');
		}
		
		$datalist = M('Order')->where($where)->order('o_dstime desc')->limit(10)->select();
		if( is_array($datalist) && !empty($datalist) ) {
			foreach( $datalist as $_key=>$_value ) {
				if($_value['o_table']) 
					$datalist[$_key]['ogoods'] = M($_value['o_table'])->where(array('sp_oid'=>$_value['o_id']))->field('sp_name,sp_gdprice,sp_number')->select();
				$datalist[$_key]['voucher'] = M('VoucherOrder')->where('o_id='.$_value['oid'])->find();
				$userids[] = $_value['o_uid'];
			}
		}
		$this->assign('datalist', $datalist);
		
		
		//支付类型
		$order_type = array(
			0=>'<span style="color:#339900;">线下支付到商家</span>', 
			1=>'<span style="color:#ff9900;">线上支付到商家</span>', 
			2=>'<span style="color:#000000;">线上支付到平台</span>'
		);
		$this->assign('order_type', $order_type);
		
		//支付状态
		$order_pstatus = array(
			0=>'<span style="color:red;">未支付</span>',
			1=>'<span style="color:#339900;">已支付</span>',
			2=>'<span style="color:#ff9900;">已退款</span>',
			3=>'<span style="color:#ff9900;">待退款</span>',
		);
		$this->assign('order_pstatus', $order_pstatus);
		
		//处理状态
		$order_dstatus = array(
			1=>'<span style="color:red;">待处理</span>',
			3=>'<span style="color:#339900;">待完成</span>',
			4=>'<span style="color:#ff9900;">已完成</span>',
			5=>'<span style="color:#ff9900;">已关闭</span>'
		);
		$this->assign('order_dstatus', $order_dstatus);
		
		//五个统计
		$this->assign("count_o", M('order')->where("o_sid=".$sid)->count());
		$this->assign("count_t", M('order')->where("o_dstatus=1 and o_sid=".$sid)->count());
		$this->assign("count_s", M('order')->where("o_dstatus=3 and o_sid=".$sid)->count());
		$this->assign("count_f", M('order')->where("o_dstatus=4 and o_sid=".$sid)->count());
		$this->assign("count_i", M('order')->where("o_dstatus=5 and o_sid=".$sid)->count());
		$this->display();
	}
	
	
	################################################################
	#                     以下是对新的订单流程操作                      #
	################################################################
	
	//同意取消订单
	public function AjaxagreeCancelOrder()
	{
		//先把要取消的订单查询出来
		$orderid = I('post.oid', '');
		$orderinfo = M("order")->where( array('o_jid'=>$this->jid, 'o_id'=>$orderid) )->find();
		if( !is_array($orderinfo) || empty($orderinfo) ) exit('0');
		
		//判断此订单是不是关闭状态
		if( $orderinfo['o_close'] != 1 ) exit('0');
		
		$message_content = '';
		$field_array = array();
		
		//判断此订单不是支付过，如果是 线上支付并且支付给商家成功，将把钱退给用户
		if( $orderinfo['o_type']==1 && $orderinfo['o_pstatus']==1 ) {
			//把此订单添加到退单表里
			$data = array();
			$data['jid'] = $orderinfo['o_jid'];
			$data['o_id'] = $orderid;
			$data['pay_type'] = $orderinfo['o_type'];
			$data['order_type'] = '1';
			$data['cause'] = $orderinfo['o_close_reason'];
			$data['batch_num'] = 1;
			$data['pay_trade_no'] = M('payLog')->where(array('jid'=>$this->jid, 'oid'=>$orderid))->getField('pay_trade_no');
			$batch_no = D('RefundOrder')->createRefund($data);
			
			//如果是把钱打给了商家，则商家退款流程
			//$status = $this->_RefundOrderInfo($batch_no);
			if( $status ) D('RefundOrder')->where( array("batch_no"=>$batch_no) )->setField( array('refund_date'=>date('Y-m-d H:i:s'), 'status'=>1) );
			$message_content = $status ? "你的订单取消成功，退款已经打入您的账号内，请查询" : "你的订单取消成功，退款失败，商家会重新操作"; 
			$field_array['o_pstatus'] = $status ? 2 : 3;//2 退款成功 3 待退款
			
		//如果订单的钱是支付到系统平台，将有系统平台通一退款
		} elseif( $orderinfo['o_type']==2 && $orderinfo['o_pstatus']==1 )	{
			$message_content = '你的订单取消成功，商家将在48小时内进行退款操作！';
			$field_array['o_pstatus'] = 3;
			
		//线下支付或未支付成功
		} else {
			$message_content = '你的订单取消成功！';
		}

		//关闭订单
		$field_array['o_dstatus'] = 5;
		$field_array['o_close'] = 2;
		$_close_status = M('order')->where(array('o_id'=>$orderid))->setField( $field_array );
		
		//给用户发送消息
		$status=$this->_SendMessageToUser($orderinfo['o_uid'], $orderid, $this->jid, '取消订单', $message_content);
		exit( $_close_status ? "1" : "0" );
	}
	
	
	//提醒用户进行支付
	public function setMessageForUser() 
	{
		$orderid = I('post.o', '');
		$userid = I('post.u', 0, 'intval');
		$contentmsg = '亲~ 欢迎光临！您的订单('.$orderid.')我们已经收到咯，还需要您选择付款或者线下支付，我们才能受理哦~';
		if( !$orderid || !$userid ) exit("0");
		
		//给用户发送通知
		$status = $this->_SendMessageToUser($userid, $orderid, $this->jid, "订单提醒", $contentmsg);
		exit( $status ? "1" : "0" );
	}

	//接受订单
	public function setOrderStatus() 
	{
		$order_id = I('post.o', '');
		
		//更新订单的状态
		$status = M("order")->where(array('o_id'=>$order_id))->setField("o_dstatus", 3);
		if( !$status ) exit("0");
		
		//把订单的商品查询出来，进行打印
		$orderInfo = M("order")->where(array('o_id'=>$order_id))->find();
		
		$printList = $printListArr = array();
		$printList = M('print')->where("print_sid=".$orderInfo['o_sid'])->select();
		foreach($printList as $p) $printListArr[$p['print_id']]  = $p;
		
		//循环把商品按打印机分类
		$goodsprint = array();
		foreach($goods_list as $g) $goodsprint[$g['printid']][] = $g;

		$sname = M('shop')->where("sid=".$orderInfo['o_sid'])->getField('sname');
		
		$goodsprint = array();
		foreach(M($orderInfo['o_table'])->where(array("sp_oid"=>$order_id))->select() as $g) $goodsprint[$g['printid']][] = $g;
		//循环把订单打印出来（主要是不同商品用不同打印机）
		foreach( $goodsprint as $p=>$g) {
			$print_info = array();
			$print_info['ShangHuID'] = $orderInfo['o_sid'];
			$print_info['ShangHuName'] = $sname;
			$print_info['Printer'] = $printListArr[$p]['print_name'];
			
			$print_info['DingDan'] = array(
				'Time' 		=> $opt['o_dstime'],
				'DingDanHao'=> $opt['o_id']
			);
			
			$print_info['CaiDan'] = array(
				'Items'	=> count($g),
			);
			
			$i = 1;
			$count_num = 0;
			$o_price = 0.00;
			foreach($g as $k=>$v){
				$print_info['CaiDan'][$i] = array(
					'Name'	=> $v['gname'],
					'Price'	=> $v['gdprice'] > 0 ? $v['gdprice'] : $v['goprice'],
					'Count'	=> $v['gnum']
				);
				$count_num += $v['gnum'];
				$i ++;
				$o_price += $v['gdprice'] > 0 ? $v['gdprice'] : $v['goprice'];
			}
			$print_info['CountNum'] = number_format($count_num, 2);
			$print_info['CountPrice'] = number_format($o_price, 2);
			$string = JSON($print_info)."@@";
			\Common\Org\PInterface::SprintPort( iconv('UTF-8', 'GBK//IGNORE', $string) );
		}
		
		exit("1");
	}
	
	//拒绝订单
	public function refuseOrderStatus()
	{
		$orderid = I('post.oid', '');
		$userid = I('post.uid', 0, 'intval');
		$content = I('post.reason', '');
		if(!$orderid || !$userid || !$content ) exit('0');
		
		$orderInfo = M("order")->where(array('o_id'=>$orderid))->find();

		//判断订单的支付状态(如果支付成功，则退款)
		$message_content = '';
		$field_array = array();
		
		//判断此订单是不是支付过，如果是 线上支付并且支付给商家成功，将把钱退给用户
		if( $orderInfo['o_type']==1 && $orderInfo['o_pstatus']==1 ) {
			//把此订单添加到退单表里
			$data = array();
			$data['jid'] = $orderInfo['o_jid'];
			$data['o_id'] = $orderid;
			$data['pay_type'] = $orderInfo['o_type'];
			$data['order_type'] = '1';
			$data['cause'] = $orderInfo['o_close_reason'];
			$data['batch_num'] = 1;
			$data['pay_trade_no'] = M('payLog')->where(array('jid'=>$this->jid, 'oid'=>$orderid))->getField('pay_trade_no');
			$batch_no = D('RefundOrder')->createRefund($data);
			
			//如果是把钱打给了商家，则商家退款流程
			$_status = $this->_RefundOrderInfo($batch_no);
			//if( $_status ) D('RefundOrder')->where( array("batch_no"=>$batch_no) )->setField( array('refund_date'=>date('Y-m-d H:i:s'), 'status'=>1) );
			$message_content = $_status ? "您单号[{$orderInfo[o_id]}]的订单因{$content}已取消，退款已经打入您的账号内，请查询" : "您单号[{$orderInfo[o_id]}]的订单因{$content}已取消，退款失败，商家会重新操作"; 
			//$field_array['o_pstatus'] = $_status ? 2 : 3;

		//如果订单的钱是支付到系统平台，将有系统平台通一退款
		} elseif( $orderInfo['o_type']==2 && $orderInfo['o_pstatus']==1 ) {
			$message_content = "您单号[{$orderInfo[o_id]}]的订单因{$content}已取消，商家将在48小时内进行退款操作。";
			$field_array['o_pstatus'] = 3;
		
		//线下支付或未支付成功
		} else {
			$message_content = "您单号[{$orderInfo[o_id]}]的订单因{$content}已取消。";
		}
		
		//关闭订单
		$field_array['o_dstatus'] = 5;
		$_update_status = M('order')->where(array('o_id'=>$orderid))->setField( $field_array );
	
		//发送通知给用户
		$status = $this->_SendMessageToUser($userid, $orderid, $jid, "拒绝订单", $message_content);
		exit( $_update_status !== false ? "1" : "0" );
	}
	
	//拒绝取消
	public function refuseCancleOrder()
	{
		$orderid = I('post.o', '');
		$userid = I('post.u', 0, 'intval');
		$sid = I('post.s', 0, 'intval');
		if( !$orderid || !$userid || !$sid ) exit('0');
		 
		$mservetel = M('shop')->where('sid='.$sid)->getField('mservetel');
		$contentmsg = '不好意思！您的订单已接受，欢迎享用~ 或者拨打电话：{$mservetel}，提醒商户取消';

		//给用户发送通知
		$status = $this->_SendMessageToUser($userid, $orderid, $this->jid, "订单提醒", $contentmsg);
		exit( $status ? "1" : "0" );	
	}
	
	//确认订单
	public function confirmOrderStatus() 
	{
		$orderid = I('post.o', '');
		if( !$orderid ) exit('0');	
		
		$OrderModel = M('order');
		$OrderModel->startTrans();
		
		$orderInfo = $OrderModel->where(array('o_id'=>$orderid))->find();
		
		$field_array['o_dstatus'] = 4;
		if( $orderInfo['o_type']==0 )//如果是线下支付，则把支付状态修改成 成功
		{
			$field_array['o_pstime'] = date('Y-m-d H:i:s');
			$field_array['o_pstatus'] = 1;
		}
		$status = $OrderModel->where(array('o_id'=>$orderid))->setField( $field_array );
		
		//如果是线上支付，并且把钱支付给了系统平台, 把钱打给商家设置的账号里
		if( is_array($orderInfo) && !empty($orderInfo) && $orderInfo['o_type']==2 && $orderInfo['o_pstatus']==1) 
		{
			$_member_id = \Common\Org\Cookie::get('mid');
		//	M("member")->where("mid=".$_member_id)->setInc('money', $orderInfo['o_price']);
		}
		
		if( is_array($orderInfo) && !empty($orderInfo) && $status ) {
			$OrderModel->commit(); exit('1');	
		} else {
			$OrderModel->rollback(); exit('0');	
		}
	}
	
	//给用户发送消息
	private function _SendMessageToUser($userid, $orderid, $jid, $title, $content)
	{
		$userInfo = M("user")->where("u_id=".$userid)->find();
		$u_clientid = $userInfo['u_clientid'];

		if( !$u_clientid ) {//如果不存在，则用短信通知
			SENDMSG:;
			$user_phone = M("order")->where(array("o_id"=>$orderid))->getField("o_phone");
			if( !$user_phone ) $user_phone = $userInfo['u_phone'];
			if( $user_phone ) {
				return sendmsg( $user_phone,  $content) ? true : false;
			}
			return true;
		}
		
		//获取商家的appid
		$appinfo = M('merchantApp')->where("jid=".$jid)->field("gt_appid,gt_appkey,gt_appsecret,gt_mastersecret")->find();
		if( !is_array($appinfo) || empty($appinfo) ) goto SENDMSG;
		
		$info = array();
		$info['title'] = $title;
		$info['time'] = date('Y-m-d H:i:s');
		$info['content'] = $content;
		$info['pid'] = 0;
		
		$args = array(
			'transmissionContent'	=> JSON($info),
		);
		$mesg = array(
			'offlineExpireTime'		=> 7200,
			'netWorkType'			=> 1
		);
		$status = \Common\Org\IGPushMsg::getIGPushMsg(true, $appinfo)->pushMessageToCid($u_clientid, 4, $args, $mesg);
		goto SENDMSG;
	}
	
	/*订单扫描确认*/
	public function orderScan(){
		
		//header("Content-type: text/html; charset=utf-8");
		$err = '';
		
		$oid = I('oid',0);
				
		$order = M('fl_order')->where(array('flo_id'=>$oid))->find();
		if(empty($order)){
				$err = '订单不存在';
		}elseif($order['flo_jid'] != $this->jid){
				$err = '这是其他商户的订单';
		}elseif($this->sid > 0 && $order['flo_sid'] != $this->sid){
				$err = '这不是本店的订单';
		}elseif($order['flo_gtype'] != 1){
				$err = '只有实物订单才能进行此操作';
		}elseif($order['flo_pstatus'] == 0){
				$err = '订单未支付';
		}elseif($order['flo_dstatus'] == 5){
				$err = '订单状态无效';
		}elseif($order['flo_dstatus'] == 4){
				$err = '订单已经确认过了';
		}else{
			
			M('')->startTrans();
			
			$mid = M("merchant")->where( "jid=".$this->jid )->getField("mid");
			$status_01 = M("member")->where( "mid=".$mid )->setInc("money", ($order['flo_price']-$order['flo_backprice']));
			
			if($order['flo_isback'] == 0){
				$commission = \Common\Org\Commission::translation()->insertInfo( $oid );
				$commission = json_decode( $commission, true );
				$status_02 = $commission['erron']==0 ? true : false;
			}else{
				$status_02 = true;
			}
			
			$status_03 = M('fl_order')->where( array("flo_id"=>$oid) )->setField( array("flo_dstatus"=>4, "flo_isback"=>1) );
			
			if( $status_01 && $status_02 && $status_03) {
				M('')->commit();
				$err =  '订单确认成功';
			} else {
				M('')->rollback();
				$err = '订单确认出现错误';
			}
		}
		
		$this->assign('msg',$err);
		$this->display();
	}
	/*领取大转盘礼物*/
	public function prizeScan(){
		$err = '';
		
		$pz_id = I('pz_id',0);
		$r = M('dzp_prize')->where(array('jid'=>$this->jid,'id'=>$pz_id))->save(array('isget'=>1));
		if($r){
			$err =  '领取成功';
		}else{
			$err = '出现错误';
		}
		$this->assign('msg',$err);
		$this->display('orderScan');
	}
}