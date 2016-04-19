<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class OrderController extends ManagerController{
    //订单列表
    public function ordersList() 
    {
       	$where = array();
		if( I('get.statime', '') && I('get.endtime', '') ) { 
			$statime = str_replace('+', '', I('get.statime'));
			$endtime = str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['o_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$statime = str_replace('+', '', I('get.statime'));
			$where['o_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( I('get.endtime', '') ) {
			$endtime = str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['o_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}

		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['mnickname|o_id'] = array('like', "%{$keyword}%", 'or'); 
		}

		if( isset($_GET['pstatus']) && $_GET['pstatus']!='' ) {
			$where['o_pstatus'] = intval( $_GET['pstatus'] ); 	
		}

		if( isset($_GET['dstatus']) && $_GET['dstatus']!='' ) {
			$where['o_dstatus'] = intval( $_GET['dstatus'] ); 	
		}
	
		$page = new \Think\Page(D('View')->view('order')->where($where)->count(), 15);
		$this->assign('orderlist', D('View')->where($where)->order('o_dstime desc')->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		
		$this->assign('dstatus', array(1=>'待发货', 3=>'待完成', 4=>'已完成', 5=>'已关闭'));
		$this->display();
    }
	
	//返利订单列表
	public function vordersList() 
	{
		$where = array();

		if( I('get.statime', '') && I('get.endtime', '') ) { 
			$statime = str_replace('+', '', I('get.statime'));
			$endtime = str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['flo_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$statime = str_replace('+', '', I('get.statime'));
			$where['flo_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( I('get.endtime', '') ) {
			$endtime = str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['flo_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}

		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['flo_id'] = array('like', "%{$keyword}%", 'or'); 
		}
		
		if( isset($_GET['pstatus']) && $_GET['pstatus']!='' ) {
			$where['flo_pstatus'] = intval( $_GET['pstatus'] );
		}
		
		if( isset($_GET['dstatus']) && $_GET['dstatus']!='' ) {
			$where['flo_dstatus'] = intval( $_GET['dstatus'] );
		}
	
		$page = new \Think\Page(D('View')->view('vorder')->where($where)->count(), 15); 
		$this->assign('orderlist', D('View')->where($where)->order('flo_dstime desc')->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->display();	
	}
	
	//退款
	public function agreeCancelOrder() 
	{
		//先把要取消的订单查询出来
		$orderid = I('get.oid', '');
		$orderinfo = M("order")->where( array('o_id'=>$orderid) )->find();
		
		if( !is_array($orderinfo) || empty($orderinfo) ) {
			$this->display('Jump:error'); exit;
		}

		//判断此订单是不是关闭状态
		if( $orderinfo['o_close'] != 2 ) { $this->display('Jump:error'); exit; }

		//判断此订单不是支付过，如果是 线上支付并且支付给平台成功，将把钱退给用户
		if( $orderinfo['o_type']==2 && $orderinfo['o_pstatus']==3 ) {
			$RefundModel = new \Common\Model\RefundOrderModel();

			//把此订单添加到退单表里
			$_info = $RefundModel->where( array("o_id"=>$orderid) )->find();
			if( is_array($_info) && !empty($_info) && $_info['status']==1 ) E('已经退款'); 

			$status_01 = $_info['batch_no'];
			//判断交易批次号是否为今天,如果不是今天转移到今天
			if($_info && substr($status_01,0,8)!=date('Ymd')){ 
				$batch_no = $RefundModel->max('batch_no');
				$datano = msubstr($batch_no,0,8,'utf-8',false);
				if(date('Ymd')==$datano){
					$status_01 = $batch_no+1;
				}else{
					$status_01 = date('Ymd').'00000001';
				}
				$RefundModel->where( array("o_id"=>$orderid) )->setField('batch_no',$status_01);
			}
		

			if( empty($_info) || !$_info ) {
				$data = array();
				$data['jid'] = $orderinfo['o_jid'];
				$data['o_id'] = $orderid;
				$data['pay_type'] = $orderinfo['o_type'];
				$data['order_type'] = '1';
				$data['cause'] = $orderinfo['o_close_reason'];
				$data['batch_num'] = 1;
				$data['pay_trade_no'] = M('payLog')->where(array('oid'=>$orderid))->getField('pay_trade_no');
				$data['money'] = $orderinfo['o_price'];
				$status_01 = $RefundModel->createRefund($data);
			}
			$this->_RefundOrderInfo($status_01);
			exit;
		}
		$this->display('Jump:error'); exit;
	}
	
	//退款
	public function agreeCancelvOrder()
	{
		//先把要取消的订单查询出来
		$orderid = I('get.oid', '');
		$orderinfo = M("fl_order")->where( array('flo_id'=>$orderid) )->find();
	
		if( !is_array($orderinfo) || empty($orderinfo) ) {
			$this->display('Jump:error'); exit;
		}
	
		//判断此订单不是支付过，如果是 线上支付并且支付给平台成功，将把钱退给用户
		if( $orderinfo['flo_pstatus']==3 && $orderinfo['flo_gtype']==1) {
			$RefundModel = new \Common\Model\RefundOrderModel();
	
			//把此订单添加到退单表里
			$_info = $RefundModel->where( array("o_id"=>$orderid) )->find();
			if( is_array($_info) && !empty($_info) && $_info['status']==1 ) E('已经退款');
	
			$status_01 = $_info['batch_no'];
			//判断交易批次号是否为今天,如果不是今天转移到今天
			if($_info && substr($status_01,0,8)!=date('Ymd')){
				$batch_no = $RefundModel->max('batch_no');
				$datano = msubstr($batch_no,0,8,'utf-8',false);
				if(date('Ymd')==$datano){
					$status_01 = $batch_no+1;
				}else{
					$status_01 = date('Ymd').'00000001';
				}
				$RefundModel->where( array("o_id"=>$orderid) )->setField('batch_no',$status_01);
			}
	
	
			if( empty($_info) || !$_info ) {
				$data = array();
				$data['jid'] = $orderinfo['flo_jid'];
				$data['o_id'] = $orderid;
				$data['pay_type'] = 2;
				$data['order_type'] = '2';
				$data['cause'] = '协商退款';
				$data['batch_num'] = 1;
				$data['pay_trade_no'] = M('fl_paylog')->where(array('pay_oid'=>$orderid))->getField('pay_trade_no');
				$data['money'] = $orderinfo['flo_price'];
				$status_01 = $RefundModel->createRefund($data);
			}
			$this->_RefundOrderInfo($status_01);
			exit;
		}
		$this->display('Jump:error'); exit;
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
			return false;
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
		
		if(!$status) { goto SENDMSG; }
		return $status ? true : false;
	}
	
	//退款操作
	private function _RefundOrderInfo( $batchno )
	{
		header("Content-type: text/html; charset=utf-8");
		$RefundOrderModel = new \Common\Model\RefundOrderModel();

		$refundorder = $RefundOrderModel->where(array('batch_no'=>$batchno))->find();
		if( !is_array($refundorder) || empty($refundorder) ) return false;
		
		if( $refundorder['status'] != 1) { //如果是普通订单，使用此流程
			$PayLog = new \Common\Model\PayLogModel();
			$refundData = array('o_id'=>$refundorder['o_id'],'money'=>$refundorder['money'],'cause'=>$refundorder['cause'],'order_type'=>$refundorder['order_type']);
			
			$parameter = $PayLog->promptlyRefund($refundData);
			$refund = new \Org\Util\pay\alipayrefund($PayLog->alipay_config);
			$s = $refund->buildRequestForm($parameter);
			return $parameter ? true : false;
		} 
		
		return false;
	}
	
	//下载订单
	public function downorder() 
	{
		if( IS_POST ) {
			$type = I("post.type", 0, "intval");
			if( !in_array($type, array(1, 2, 3)) ) E('参数出错');
			
			switch( $type )
			{
				case 1: $this->_DownOsorder($_POST); break;
				case 2: $this->_DownFlorder($_POST); break;
				case 3: $this->_DownTkorder($_POST); break;	
			}
			exit;
		} else {
			$this->display();
		}
	}

	//下载订单
	private function _DownOsorder( array $config=array() ) {
		$where = array();
		if( !empty($config['statime']) && !empty($config['endtime']) ) { 
			$statime = str_replace('+', '', $config['statime']);
			$endtime = str_replace('+', '', $config['endtime']);
			$where['o_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( !empty($config['statime']) ) {
			$statime = str_replace('+', '', $config['statime']);
			$where['o_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( !empty($config['endtime']) ) {
			$endtime = str_replace('+', '', $config['statime']);
			$where['o_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		$orderlist = D('View')->view('order')->where($where)->order('o_dstime desc')->select();
		
		//导入 PhpExcel
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		foreach($char_column_list as $char)
			$objPHPExcel->getActiveSheet()->getColumnDimension($char)->setWidth('30');
			
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'OS订单列表');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '交易号')
					->setCellValue('B2', '所属商家')
					->setCellValue('C2', '收货人姓名')
					->setCellValue('D2', '收货人手机')
					->setCellValue('E2', '下单时间')
					->setCellValue('F2', '订单总价')
					->setCellValue('G2', '订单状态')
					->setCellValue('H2', '支付状态');
		
		//查询所有的子代理
		if( is_array($orderlist) && !empty($orderlist) )
		{
			$i = 3;
			$order_dstatus = array(1=>'待发货', 3=>'待完成', 4=>'已完成', 5=>'已关闭');
			$order_pstatus = array(0=>'未支付', 1=>'已支付', 2=>'已退款', 3=>'待退款');
			foreach($orderlist as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:H{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:H{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $c['o_id'])
							->setCellValue('B'.$i, $c['j_name'])
							->setCellValue('C'.$i, $c['o_name'])
							->setCellValue('D'.$i, $c['o_phone'])
							->setCellValue('E'.$i, $c['o_dstime'])
							->setCellValue('F'.$i, $c['o_price'])
							->setCellValue('G'.$i, $order_dstatus[ $c['o_dstatus'] ])
							->setCellValue('H'.$i, $order_pstatus[ $c['o_pstatus'] ]);
				$i ++;
			endforeach;
		}
		$objPHPExcel->getActiveSheet()->setTitle('OS订单列表');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="OS订单列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	//返利订单
	private function _DownFlorder( array $config=array() ) {
		$where = array();
		if( !empty($config['statime']) && !empty($config['endtime']) ) { 
			$statime = str_replace('+', '', $config['statime']);
			$endtime = str_replace('+', '', $config['endtime']);
			$where['flo_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( !empty($config['statime']) ) {
			$statime = str_replace('+', '', $config['statime']);
			$where['flo_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( !empty($config['endtime']) ) {
			$endtime = str_replace('+', '', $config['statime']);
			$where['flo_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		$orderlist = D('View')->view('vorder')->where($where)->order('flo_dstime desc')->select();
		
		//导入 PhpExcel
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I');

		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth('30');
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '返利订单列表');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '订单号')
					->setCellValue('B2', '所属商家')
					->setCellValue('C2', '收货人姓名')
					->setCellValue('D2', '收货人手机')
					->setCellValue('E2', '下单时间')
					->setCellValue('F2', '订单总价')
					->setCellValue('G2', '返利金额')
					->setCellValue('H2', '订单状态')
					->setCellValue('I2', '支付状态 (1:已支付  0:未支付)');
		
		//查询所有的子代理
		if( is_array($orderlist) && !empty($orderlist) )
		{
			$i = 3;
			$order_dstatus = array(1=>'配货中', 2=>'待发货', 3=>'已发货', 4=>'已完成', 5=>'无效订单');

			foreach($orderlist as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:I{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:I{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("A".$i, $c['flo_id'], \PHPExcel_Cell_DataType::TYPE_STRING);
				$mnickname = $c['flo_gtype']==3 ? "升级VIP" : ( $c['flo_gtype']==4 ? "话费充值" : ( $c['flo_gtype']==5 ? "流量充值" : $c['mnickname']));

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('B'.$i, $mnickname)
							->setCellValue('C'.$i, $c['flu_nickname'])
							->setCellValue('D'.$i, $c['flu_phone'])
							->setCellValue('E'.$i, $c['flo_dstime'])
							->setCellValue('F'.$i, $c['flo_price'])
							->setCellValue('G'.$i, $c['flo_backprice'])
							->setCellValue('H'.$i, $order_dstatus[ $c['flo_dstatus'] ])
							->setCellValue('I'.$i, $c['flo_pstatus']==1 ? '1':'0');
				$i ++;
			endforeach;
		}
		$objPHPExcel->getActiveSheet()->setTitle('返利订单列表');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="返利订单列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	//待退款订单
	private function _DownTkorder( array $config=array() ) {
		$where = array(
			"o_close"	=> 2, //商家同意退款
			"o_dstatus"	=> 5, //订单是关闭状态
			"o_pstatus"	=> 3, //待退款订单
			"o_type"	=> 2  //线上支付到平台	
		);

		if( !empty($config['statime']) && !empty($config['endtime']) ) { 
			$statime = str_replace('+', '', $config['statime']);
			$endtime = str_replace('+', '', $config['endtime']);
			$where['o_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( !empty($config['statime']) ) {
			$statime = str_replace('+', '', $config['statime']);
			$where['o_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( !empty($config['endtime']) ) {
			$endtime = str_replace('+', '', $config['statime']);
			$where['o_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		$orderlist =  M('order o')->join("__MERCHANT__ AS m ON o.o_jid=m.jid")->field("o.o_id,m.mnickname as j_name,o.o_name,o.o_phone,o.o_dstime,o.o_price,o.o_dstatus,o.o_pstatus")->where($where)->order('o_dstime desc')->select();
		
		//导入 PhpExcel
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		foreach($char_column_list as $char)
			$objPHPExcel->getActiveSheet()->getColumnDimension($char)->setWidth('30');
			
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '返利订单列表');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '交易号')
					->setCellValue('B2', '所属商家')
					->setCellValue('C2', '收货人姓名')
					->setCellValue('D2', '收货人手机')
					->setCellValue('E2', '下单时间')
					->setCellValue('F2', '订单总价')
					->setCellValue('G2', '订单状态')
					->setCellValue('H2', '支付状态');
		
		//查询所有的子代理
		if( is_array($orderlist) && !empty($orderlist) )
		{
			$i = 3;
			$order_dstatus = array(1=>'待发货', 3=>'待完成', 4=>'已完成', 5=>'已关闭');
			$order_pstatus = array(0=>'未支付', 1=>'已支付', 2=>'已退款', 3=>'待退款');

			foreach($orderlist as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:H{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:H{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $c['o_id'])
							->setCellValue('B'.$i, $c['j_name'])
							->setCellValue('C'.$i, $c['o_name'])
							->setCellValue('D'.$i, $c['o_phone'])
							->setCellValue('E'.$i, $c['o_dstime'])
							->setCellValue('F'.$i, $c['o_price'])
							->setCellValue('G'.$i, $order_dstatus[ $c['o_dstatus'] ])
							->setCellValue('H'.$i, $order_pstatus[ $c['o_pstatus'] ]);
				$i ++;
			endforeach;
		}
		$objPHPExcel->getActiveSheet()->setTitle('待退款订单列表');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="待退款订单列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');		
	}
	
	public function doTuiKuan(){
		$oid = I('oid',0);
		if($oid){
			M('order')->where(array('o_id'=>$oid))->save(array('o_pstatus'=>2));
			die('1');
		}
		die('0');
	}
}