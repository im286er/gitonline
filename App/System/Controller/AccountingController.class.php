<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class AccountingController extends ManagerController{
    //收入明细
    public function incomeInfo() {
		$where = array('o_dstatus'=>array('NEQ', 5));
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$statime=str_replace('+', '', I('get.statime'));
			$endtime=str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['o_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) { 
			$statime=str_replace('+', '', I('get.statime'));
			$where['o_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( I('get.endtime', '') ) {
			$endtime=str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['o_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		   
		if( isset($_GET['keyword']) && !empty($_GET['keyword']) ) {
			$keyword=trim(I('get.keyword')); $where['mnickname|o_id|sname'] = array('like', "%{$keyword}%", 'or'); 
		}
		
		if( isset($_GET['pstatus']) && $_GET['pstatus']!='' ) {
			$where['o_pstatus'] = intval( $_GET['pstatus'] ); 	
		}

		//收入列表（也就是订单列表）
		$page = new \Think\Page(M('order')->where($where)->count(), 13);
		$accountlist = M('order')->alias('AS o')->join("__MERCHANT__ AS m ON o.o_jid=m.jid")->join("__SHOP__ AS s ON o.o_sid=s.sid")->join("__MEMBER__ AS u ON m.mid=u.mid", 'left')->field("o.*,m.mnickname,u.money,s.sname")->where($where)->order('o_dstime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('accountlist', $accountlist);
		$this->assign('pages', $page->show());
		
		//三个统计数据（日周月）
		$c_d = date("Y-m-d");
		$c_w = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')-date('N')+1, date('Y')));
		$c_m = date("Y-m-d", mktime(0, 0, 0, date('m'), 1, date('Y')));
		$cpriced = M('order')->alias('AS o')->join("__MERCHANT__ AS m ON o.o_jid=m.jid")->where(array('o.o_dstatus'=>array('NEQ', 5), 'o.o_dstime'=>array('egt', $c_d)))->sum('o.o_price');
		$cpricew = M('order')->alias('AS o')->join("__MERCHANT__ AS m ON o.o_jid=m.jid")->where(array('o.o_dstatus'=>array('NEQ', 5), 'o.o_dstime'=>array('egt', $c_w)))->sum('o.o_price');
		$cpricem = M('order')->alias('AS o')->join("__MERCHANT__ AS m ON o.o_jid=m.jid")->where(array('o.o_dstatus'=>array('NEQ', 5), 'o.o_dstime'=>array('egt', $c_m)))->sum('o.o_price');
		$this->assign("cpriced", $cpriced);
		$this->assign("cpricew", $cpricew);
		$this->assign("cpricem", $cpricem);
		$this->assign("c_d", $c_d);
		$this->assign("c_w", $c_w);
		$this->assign("c_m", $c_m);

		//所有的流水金额
		$account_price = M('order')->alias('AS o')->join("__MERCHANT__ AS m ON o.o_jid=m.jid")->where( "o_dstatus<>5" )->sum('o.o_price');
		$account_moeny = M('member')->alias('AS m')->distinct(true)->join("__MERCHANT__ AS j ON j.mid=m.mid")->where("m.mstatus=1")->sum('m.money');

		$this->assign('account_price', $account_price);
		$this->assign('account_moeny', $account_moeny);
		$this->display();	
    }
	
	//返利收支明细
	public function vincomeInfo() {
		$where = array('flo_dstatus'=>array('NEQ', 5));
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$statime=str_replace('+', '', I('get.statime'));
			$endtime=str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['flo_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) { 
			$statime=str_replace('+', '', I('get.statime'));
			$where['flo_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( I('get.endtime', '') ) {
			$endtime=str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['flo_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		   
		if( isset($_GET['keyword']) && !empty($_GET['keyword']) ) {
			$keyword=trim(I('get.keyword')); $where['mnickname|flo_id'] = array('like', "%{$keyword}%", 'or'); 
		}
		
		if( isset($_GET['pstatus']) && $_GET['pstatus']!='' ) {
			if( intval($_GET['pstatus'])==1 )
				$where['_string'] = "flo_pstatus <> 0";
			else
				$where['_string'] = "flo_pstatus = 0";
			//	$where['flo_pstatus'] = intval( $_GET['pstatus'] ); 
		}
		
		//收入列表（也就是订单列表）
		$page = new \Think\Page(M('flOrder')->where($where)->count(), 13);
		$accountlist = M('flOrder')->alias('AS f')->join("__MERCHANT__ AS m ON f.flo_jid=m.jid", "left")->field("f.*,m.mnickname")->where($where)->order('flo_dstime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('accountlist', $accountlist);
		$this->assign('pages', $page->show());


		//三个统计数据（日周月）
		$c_d = date("Y-m-d");
		$c_w = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')-date('N')+1, date('Y')));
		$c_m = date("Y-m-d", mktime(0, 0, 0, date('m'), 1, date('Y')));
		$cpriced = M('flOrder')->alias('AS f')->join("__MERCHANT__ AS m ON f.flo_jid=m.jid", "left")->where(array("flo_pstatus"=>array(in, "1,3"), "f.flo_dstime"=>array('egt', $c_d)))->sum('f.flo_price');
		$cpricew = M('flOrder')->alias('AS f')->join("__MERCHANT__ AS m ON f.flo_jid=m.jid", "left")->where(array("flo_pstatus"=>array(in, "1,3"), "f.flo_dstime"=>array('egt', $c_w)))->sum('f.flo_price');
		$cpricem = M('flOrder')->alias('AS f')->join("__MERCHANT__ AS m ON f.flo_jid=m.jid", "left")->where(array("flo_pstatus"=>array(in, "1,3"), "f.flo_dstime"=>array('egt', $c_m)))->sum('f.flo_price');
		$this->assign("cpriced", $cpriced);
		$this->assign("cpricew", $cpricew);
		$this->assign("cpricem", $cpricem);
		$this->assign("c_d", $c_d);
		$this->assign("c_w", $c_w);
		$this->assign("c_m", $c_m);

		//所有的流水金额
		$account_price = M('flOrder')->alias('AS f')->join("__MERCHANT__ AS m ON f.flo_jid=m.jid", "left")->where($where)->sum('f.flo_price');
		$this->assign('account_price', $account_price);

		$this->display();	
	}

	//提现明细 
	public function mentionInfo() {
		$where = array('b.btype'=>array('NEQ', "2"), 'b.butype'=>array('NEQ', 1));

		if( I('get.statime', '') && I('get.endtime', '') ) {
			$stime=str_replace('+','',I('get.statime'));
			$etime=str_replace('+','',I('get.endtime')) . " 23:59:59";
			$where['bstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($stime))), array('elt', date('Y-m-d H:i:s', strtotime($etime))), 'and');					
		} elseif( I('get.statime', '') ) { 
			$stime=str_replace('+','',I('get.statime'));
			$where['bstime'] = array('egt', date('Y-m-d H:i:s', strtotime($stime)));	
		} elseif( I('get.endtime', '') ) {
			$etime=str_replace('+','',I('get.endtime')) . " 23:59:59";
			$where['bstime'] = array('elt', date('Y-m-d H:i:s', strtotime($etime))); 	
		} 
		
		if( isset($_GET['keyword']) && !empty($_GET['keyword']) ) {
			$keyword = trim(I('get.keyword')); $where['m.mnickname|b.bls|b.bdzh|m.mlpname'] = array('like', "%{$keyword}%", 'or'); 
		}
		
		if( isset($_GET['pstatus']) && $_GET['pstatus']!='' ) {
			$where['b.btype'] = intval( $_GET['pstatus'] ); 	
		}

		$page = new \Think\Page(M('bookkeeping')->alias('AS b')->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->where($where)->count(), 15);
		$bookkeepinglist = $bookkeepingListArray = array();
		$this->assign('bookkeepinglist', M('bookkeeping')->alias('AS b')->field('b.*,m.mnickname,m.mlpname,u.money')->where($where)->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->join('__MEMBER__ AS u ON m.mid=u.mid', 'left')->order('b.bstime DESC')->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());

		//三个统计数据（日周月）
		$c_d = date("Y-m-d");
		$c_w = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')-date('N')+1, date('Y')));
		$c_m = date("Y-m-d", mktime(0, 0, 0, date('m'), 1, date('Y')));
		$cpriced = M('bookkeeping')->alias('AS b')->field('b.*,m.mnickname,m.mlpname')->where( array('b.btype'=>array('NEQ', "2"), 'b.butype'=>array('NEQ', 1), 'bstime'=>array('egt', $c_d)) )->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->sum('bmention');
		$cpricew = M('bookkeeping')->alias('AS b')->field('b.*,m.mnickname,m.mlpname')->where( array('b.btype'=>array('NEQ', "2"), 'b.butype'=>array('NEQ', 1), 'bstime'=>array('egt', $c_w)) )->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->sum('bmention');
		$cpricem = M('bookkeeping')->alias('AS b')->field('b.*,m.mnickname,m.mlpname')->where( array('b.btype'=>array('NEQ', "2"), 'b.butype'=>array('NEQ', 1), 'bstime'=>array('egt', $c_m)) )->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->sum('bmention');
		$this->assign("cpriced", $cpriced);
		$this->assign("cpricew", $cpricew);
		$this->assign("cpricem", $cpricem);
		$this->assign("c_d", $c_d);
		$this->assign("c_w", $c_w);
		$this->assign("c_m", $c_m);

		//所有流水金额
		$account_money = M('bookkeeping')->alias('AS b')->where($where)->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->join('__MEMBER__ AS u ON m.mid=u.mid', 'left')->sum('u.money');
		$this->assign('account_money_a', $account_money);

		$account_price = M('bookkeeping')->alias('AS b')->where($where)->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->sum('bmention');
		$this->assign('account_price_a', $account_price);

		$account_price = M('bookkeeping')->alias('AS b')->where($where)->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->sum('bjs');
		$this->assign('account_price_b', $account_price);

		$account_price = M('bookkeeping')->alias('AS b')->where($where)->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->sum('bjy');
		$this->assign('account_price_c', $account_price);

		$account_price = M('bookkeeping')->alias('AS b')->where($where)->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->sum('bsj');
		$this->assign('account_price_d', $account_price);

		$this->display();
	}
	
	//返利支出明细
	public function vmentionInfo() {
		$where = array('b.btype'=>array('NEQ', "2"), 'b.butype'=>1);
		
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$stime=str_replace('+', '', I('get.statime'));
			$etime=str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['bstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($stime))), array('elt', date('Y-m-d H:i:s', strtotime($etime))), 'and');					
		} elseif( I('get.statime', '') ) { 
			$stime=str_replace('+', '', I('get.statime'));
			$where['bstime'] = array('egt', date('Y-m-d H:i:s', strtotime($stime)));	
		} elseif( I('get.endtime', '') ) {
			$etime=str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['bstime'] = array('elt', date('Y-m-d H:i:s', strtotime($etime))); 	
		}
		if( isset($_GET['jid']) && intval($_GET['jid']) ) {
			$where['b.bjid'] = I('get.jid', 0, 'intval');
		}
		
		$page = new \Think\Page(M('bookkeeping')->alias('AS b')->where($where)->count(), 8);
		$bookkeepinglist = $bookkeepingListArray = array();
		$this->assign('bookkeepinglist', M('bookkeeping')->alias('AS b')->field('b.*,u.flu_nickname,u.flu_gjid,u.flu_gagentid')->where($where)->join('__FL_USER__ AS u ON b.bmid=u.flu_userid', 'left')->order('b.bstime DESC')->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->display();	
	}
	
	//查看明细
	public function accountingInfo() {
		if(isset($_GET['type']) && intval($_GET['type'])==1) {//OS收入明细
			$orderInfo = M('order')->where(array('o_id'=>I('bls')))->find();
			$tableInfo = M($orderInfo['o_table'])->where('sp_oid='.I('get.bls'))->select();
			if( !is_array($orderInfo) || !is_array($tableInfo) ) { 
				$this->assign('msg', '收支信息不存在'); $this->display('Jump:error');
			}
			$this->assign('order', $orderInfo);
			$this->assign('goods', $tableInfo);
			$tplname = 'income'.$orderInfo['o_table'];
		} else if(isset($_GET['type']) && intval($_GET['type'])==3) {//返利收入明细
			$orderInfo = M('flOrder')->where(array("flo_id"=>I('get.bls')))->find();
			$tableInfo = M('flGsnapshot')->where("flg_oid='".$orderInfo['flo_id']."'")->select();
			if( !is_array($orderInfo) || empty($orderInfo) ) { 
				$this->assign('msg', '收支信息不存在'); $this->display('Jump:error');
			}
			$this->assign('order', $orderInfo);
			$this->assign('goods', $tableInfo);
			
			//查询订单返利明细
			$sinfo = \Common\Org\Commission::translation()->insertInfo( $orderInfo['flo_id'], true);
			$userid = array();
			if( is_array($sinfo) && !empty($sinfo) )
			{
				foreach($sinfo as $s) $userid[] = $s[0];
			}
			
			//获取所有的用户昵称
			$user_list = $user_list_array = array();
			$user_list = M("flUser")->where( array("flu_userid"=>array("in", $userid)) )->field("flu_userid,flu_nickname,flu_phone")->select();
			foreach($user_list as $u) $user_list_array[$u['flu_userid']] = $u['flu_nickname'] ? $u['flu_nickname'] : $u['flu_phone'];
			
			$user_info_list = array();
			foreach( $sinfo as $s ) {
				$_s['userid'] = $s[0];
				$_s['username'] = $user_list_array[$s[0]];
				$_s['price'] = $s[1];
				$user_info_list[] = $_s;
			}
			
			$this->assign('user_info_list', $user_info_list);

			$tplname = 'Accounting_snapshot';
		} else if(isset($_GET['type']) && intval($_GET['type'])==4) {//返利支出明细
		   	$this->assign('account', M('bookkeeping')->alias('AS b')->field('b.*,u.flu_username,u.flu_withdrawname,u.flu_withdrawzh')->where('b.bls='.I('get.bls'))->join('__FL_USER__ AS u ON b.bmid=u.flu_userid', 'left')->order('b.bstime DESC')->find());				
			$tplname = 'Accounting_vmention';
		} else {//OS支出明细
		   	$this->assign('account',M('bookkeeping')->alias('AS b')->field('b.*,m.mnickname,m.mlpname')->where('b.bls='.I('get.bls'))->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->order('b.bstime DESC')->find());				
			$tplname = 'Accounting_mention';
		}
		$this->display( $tplname );
	}
	
	//确实提现
	public function updateMentions() {
		$status=I('post.status', 0, 'intval'); $bls=I('post.bls');
		$bmarker=I('post.bmarker');
		if($status == 1 && $bls ) {
			if(M('bookkeeping')->where(array('bls'=>$bls))->save(array('btype'=>'1', 'betime'=>date('Y-m-d H:i:s'),'bmarker'=>$bmarker)) !== false) {
				$this->display('Jump:success');
			} else { $this->display('Jump:error'); }
		}
	}

	//下载订单
	public function downorder() {
		if( IS_POST ) {
			$type = I("post.type", 0, "intval");
			if( !in_array($type, array(1, 2, 3, 4)) ) E('参数出错');
			
			switch( $type ) 
			{
				case 1: $this->_DownOsiorder($_POST); break;
				case 2: $this->_DownFliorder($_POST); break;
				case 3: $this->_DownOsmorder($_POST); break;
				case 4: $this->_DownFlmorder($_POST); break;	
			}
			exit;
		} else {
			$this->display();
		}
	}
	
	private function _DownOsiorder( array $config=array() ) {
		$where = array('o_dstatus'=>array('NEQ', 5));
		if( !empty($config['statime']) && !empty($config['endtime']) ) {
			$statime = str_replace('+', '', $config['statime']);
			$endtime = str_replace('+', '', $config['endtime']) . " 23:59:59";
			$where['o_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( $config['statime'] ) { 
			$statime = str_replace('+', '', $config['statime']);
			$where['o_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( $config['endtime'] ) {
			$endtime=str_replace('+', '', $config['endtime']) . " 23:59:59";
			$where['o_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		$orderlist = M('order')->alias('AS o')->join("__MERCHANT__ AS m ON o.o_jid=m.jid")->join("__SHOP__ AS s ON o.o_sid=s.sid")->field("o.*,m.mnickname,s.sname")->where($where)->order('o_dstime DESC')->select();
		
		//导入 PhpExcel
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F');

		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth('30');
			
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '帝鼠OS收入明细');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '创建日期')
					->setCellValue('B2', '流水号')
					->setCellValue('C2', '收入金额')
					->setCellValue('D2', '支付状态 ( 1已支付 0未支付 )')
					->setCellValue('E2', '所属商家')
					->setCellValue('F2', '所属分店');
		
		//查询所有的子代理
		if( is_array($orderlist) && !empty($orderlist) )
		{
			$i = 3;
			foreach($orderlist as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:F{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:F{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$i, $c['o_id'], \PHPExcel_Cell_DataType::TYPE_STRING);
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $c['o_dstime'])
							->setCellValue('C'.$i, $c['o_price'])
							->setCellValue('D'.$i, $c['o_pstatus']==1 ? "1" : "0")
							->setCellValue('E'.$i, $c['mnickname'])
							->setCellValue('F'.$i, $c['sname']);
				$i ++;
			endforeach;
		}
		$objPHPExcel->getActiveSheet()->setTitle('帝鼠OS收入明细');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="帝鼠OS收入明细.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	private function _DownFliorder( array $config=array() ) {
		$where = array('flo_dstatus'=>array('NEQ', 5));
		if( !empty($config['statime']) && !empty($config['endtime']) ) {
			$statime = str_replace('+', '', $config['statime']);
			$endtime = str_replace('+', '', $config['endtime']) . " 23:59:59";
			$where['flo_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( $config['statime'] ) { 
			$statime = str_replace('+', '', $config['statime']);
			$where['flo_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} elseif( $config['endtime'] ) {
			$endtime=str_replace('+', '', $config['endtime']) . " 23:59:59";
			$where['flo_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		$orderlist = M('flOrder')->alias('AS f')->join("__MERCHANT__ AS m ON f.flo_jid=m.jid", "left")->field("f.*,m.mnickname")->where($where)->order('flo_dstime DESC')->select();
		
		//导入 PhpExcel
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")->setLastModifiedBy("Maarten Balliauw")->setTitle("Office 2007 XLSX Test Document")->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F');

		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth('50');
		$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth('30');
			
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '返利收入明细');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '创建日期')
					->setCellValue('B2', '流水号')
					->setCellValue('C2', '收入金额')
					->setCellValue('D2', '支付状态 ( 1已支付 0未支付 )')
					->setCellValue('E2', '所属商家')
					->setCellValue('F2', '返利金额');
		
		//查询所有的子代理
		if( is_array($orderlist) && !empty($orderlist) )
		{
			$i = 3;
			foreach($orderlist as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:H{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:H{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$i, $c['flo_id'], \PHPExcel_Cell_DataType::TYPE_STRING);
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $c['flo_dstime'])
							->setCellValue('C'.$i, $c['flo_price'])
							->setCellValue('D'.$i, $c['flo_pstatus']==1 ? "1" : "0")
							->setCellValue('E'.$i, $c['flo_gtype']==3 ? "升级充值" : ($c['flo_gtype']==4 ? "话费充值" : ( $c['flo_gtype']==5 ? "流量充值" : $c['mnickname']) ))
							->setCellValue('F'.$i, $c['flo_backprice']);
				$i ++;
			endforeach;
		}
		$objPHPExcel->getActiveSheet()->setTitle('返利收入明细');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="返利收入明细.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	private function _DownOsmorder( array $config=array() ) {
		$where = array('b.btype'=>array('NEQ', "2"), 'b.butype'=>array('NEQ', 1));
		if( !empty($config['statime']) && !empty($config['endtime']) ) {
			$stime = str_replace('+','',$config['statime']);
			$etime = str_replace('+','',$config['endtime']) . " 23:59:59";
			$where['bstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($stime))), array('elt', date('Y-m-d H:i:s', strtotime($etime))), 'and');					
		} elseif( !empty($config['statime']) ) { 
			$stime=str_replace('+','',$config['statime']);
			$where['bstime'] = array('egt', date('Y-m-d H:i:s', strtotime($stime)));	
		} elseif( !empty($config['endtime']) ) {
			$etime=str_replace('+','',$config['endtime']) . " 23:59:59";
			$where['bstime'] = array('elt', date('Y-m-d H:i:s', strtotime($etime))); 	
		} 
		$orderlist = M('bookkeeping')->alias('AS b')->field('b.*,m.mnickname,m.mlpname')->where($where)->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->order('b.bstime DESC')->select();
		
		//导入 PhpExcel
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');

		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth('40');
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth('30');
			
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '帝鼠OS支出明细');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '申请日期')
					->setCellValue('B2', '提现对象')
					->setCellValue('C2', '流水号')
					->setCellValue('D2', '提现金额')
					->setCellValue('E2', '技术服务器费')
					->setCellValue('F2', '交易手续费')
					->setCellValue('G2', '实际提现金额')
					->setCellValue('H2', '提现账号')
					->setCellValue('I2', '法人名称')
					->setCellValue('J2', '打款状态 ( 1已打款 0未打款 )');
		
		//查询所有的子代理
		if( is_array($orderlist) && !empty($orderlist) )
		{
			$i = 3;
			foreach($orderlist as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:J{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:J{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$i, $c['bls'], \PHPExcel_Cell_DataType::TYPE_STRING);
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $c['bstime'])
							->setCellValue('B'.$i, $c['mnickname'])
							->setCellValue('D'.$i, $c['bmention'])
							->setCellValue('E'.$i, $c['bjs'])
							->setCellValue('F'.$i, $c['bjy'])
							->setCellValue('G'.$i, $c['bsj'])
							->setCellValue('H'.$i, $c['bdzh'])
							->setCellValue('I'.$i, $c['mlpname'])
							->setCellValue('J'.$i, $c['btype']==0 ? "0" : "1");
				$i ++;
			endforeach;
		}
		$objPHPExcel->getActiveSheet()->setTitle('帝鼠OS支出明细');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="帝鼠OS支出明细.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	private function _DownFlmorder( array $config=array() ) {
		$where = array('b.btype'=>array('NEQ', "2"), 'b.butype'=>1);
		if( !empty($config['statime']) && !empty($config['endtime']) ) {
			$stime = str_replace('+','',$config['statime']);
			$etime = str_replace('+','',$config['endtime']) . " 23:59:59";
			$where['bstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($stime))), array('elt', date('Y-m-d H:i:s', strtotime($etime))), 'and');					
		} elseif( !empty($config['statime']) ) { 
			$stime=str_replace('+','',$config['statime']);
			$where['bstime'] = array('egt', date('Y-m-d H:i:s', strtotime($stime)));	
		} elseif( !empty($config['endtime']) ) {
			$etime=str_replace('+','',$config['endtime']) . " 23:59:59";
			$where['bstime'] = array('elt', date('Y-m-d H:i:s', strtotime($etime))); 	
		} 
		
		
		$orderlist = M('bookkeeping')->alias('AS b')->field('b.*,u.flu_nickname,u.flu_gjid,u.flu_gagentid')->where($where)->join('__FL_USER__ AS u ON b.bmid=u.flu_userid', 'left')->order('b.bstime DESC')->select();
		
		//导入 PhpExcel
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');

		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth('40');
		$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth('30');
		$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth('20');
		$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth('30');
			
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '帝鼠OS支出明细');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '申请日期')
					->setCellValue('B2', '提现对象')
					->setCellValue('C2', '流水号')
					->setCellValue('D2', '提现金额')
					->setCellValue('E2', '技术服务器费')
					->setCellValue('F2', '交易手续费')
					->setCellValue('G2', '实际提现金额')
					->setCellValue('H2', '提现账号')
					->setCellValue('I2', '绑定名称')
					->setCellValue('J2', '打款状态 ( 1已打款 0未打款 )');
		
		//查询所有的子代理
		if( is_array($orderlist) && !empty($orderlist) )
		{
			$i = 3;
			foreach($orderlist as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:J{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:J{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$i, $c['bls'], \PHPExcel_Cell_DataType::TYPE_STRING);
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $c['bstime'])
							->setCellValue('B'.$i, $c['flu_nickname'])
							->setCellValue('D'.$i, $c['bmention'])
							->setCellValue('E'.$i, $c['bjs'])
							->setCellValue('F'.$i, $c['bjy'])
							->setCellValue('G'.$i, $c['bsj'])
							->setCellValue('H'.$i, $c['bdzh'])
							->setCellValue('I'.$i, $c['mlpname'])
							->setCellValue('J'.$i, $c['btype']==0 ? "0" : "1");
				$i ++;
			endforeach;
		}
		$objPHPExcel->getActiveSheet()->setTitle('帝鼠OS支出明细');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="帝鼠OS支出明细.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	//在线生成报表图
	public function CreateStatisticalReport() {
		$where = array();
		if( isset($_GET['statime']) && !empty($_GET['statime']) && isset($_GET['endtime']) && !empty($_GET['endtime']) ) 
		{
			$statime = str_replace('+', '', I('get.statime'));
			$endtime = str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['o_dstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($statime))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} 
		elseif( isset($_GET['statime']) && !empty($_GET['statime']) ) 
		{ 
			$statime = str_replace('+', '', I('get.statime'));
			$where['o_dstime'] = array('egt', date('Y-m-d H:i:s', strtotime($statime)));	
		} 
		elseif( isset($_GET['endtime']) && !empty($_GET['endtime']) ) 
		{
			$endtime = str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['o_dstime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime))); 	
		}
		
		if( isset($_GET['keyword']) && !empty($_GET['keyword']) ) 
		{
			$keyword = trim(I('get.keyword')); $where['mnickname|o_id|sname'] = array('like', "%{$keyword}%", 'or'); 
		}
		
		if( isset($_GET['pstatus']) && $_GET['pstatus']!='' ) 
		{
			$where['o_pstatus'] = intval( $_GET['pstatus'] ); 	
		}
		$accountlist = $accountListArray = array();
		$accountlist = M('order')->alias('AS o')->join("__MERCHANT__ AS m ON o.o_jid=m.jid")->join("__SHOP__ AS s ON o.o_sid=s.sid")->field("o_type,o_dstatus,o_dstime,o_pstatus,o_price,o_pstime,o_close")->where($where)->order('o_dstime DESC')->select();
		
		if( is_array($accountlist) && !empty($accountlist) )
		{
			foreach( $accountlist as $order ) 
			{
				$key = date('Y-m-d', strtotime($order['o_dstime']));
				if( !isset($accountListArray[$key]) ) 
				{
					$accountListArray[$key] = array(0=>$key, 'a'=>0, 'b'=>0, 'c'=>0, 'd'=>0, 'e'=>0, 'f'=>0, 'g'=>0, 'h'=>0, 'i'=>0, 'j'=>0, 'k'=>0, 'l'=>0);
				}
				$this->_SetOrderClassPrice( & $accountListArray[$key], $order );
			}
		}
		
		if( I('get.type', 1, 'intval') == 2 ) { //生成图
			$keys = array_keys( $accountListArray ); sort( $keys );
			$xAxis_data = implode("','", $keys);
			$this->assign('xAxis_data', $xAxis_data);
			sort($accountListArray);
			
			$yAxis_a = $yAxis_b = $yAxis_c = $yAxis_d = $yAxis_e = $yAxis_f =
			$yAxis_g = $yAxis_h = $yAxis_i = $yAxis_j = $yAxis_k = $yAxis_l = "";
			foreach( $accountListArray as $a ):
				$yAxis_a .= $a['a'].","; $yAxis_b .= $a['b'].","; $yAxis_c .= $a['c'].",";
				$yAxis_d .= $a['d'].","; $yAxis_e .= $a['e'].","; $yAxis_f .= $a['f'].",";
				$yAxis_g .= $a['g'].","; $yAxis_h .= $a['h'].","; $yAxis_i .= $a['i'].",";
				$yAxis_j .= $a['j'].","; $yAxis_k .= $a['k'].","; $yAxis_l .= $a['l'].",";
			endforeach;
			$this->assign('yAxis_a', $yAxis_a);
			$this->assign('yAxis_b', $yAxis_b);
			$this->assign('yAxis_c', $yAxis_c);
			$this->assign('yAxis_d', $yAxis_d);
			$this->assign('yAxis_e', $yAxis_e);
			$this->assign('yAxis_f', $yAxis_f);
			$this->assign('yAxis_g', $yAxis_g);
			$this->assign('yAxis_h', $yAxis_h);
			$this->assign('yAxis_i', $yAxis_i);
			$this->assign('yAxis_j', $yAxis_j);
			$this->assign('yAxis_k', $yAxis_k);
			$this->assign('yAxis_l', $yAxis_l);
			
			$this->display("StatisticalReportImage");
		} else { //文字
			$this->assign('accountListArray', $accountListArray);
			$this->display("StatisticalReportText");
		}
	}
	
	//在线生成报表图(支出)
	public function CreateStatisticalReportByMention() {
		$where = array('b.btype'=>array('NEQ', "2"), 'b.butype'=>array('NEQ', 1));

		if( isset($_GET['statime']) && !empty($_GET['statime']) && isset($_GET['endtime']) && !empty($_GET['endtime']) ) 
		{
			$stime = str_replace('+', '', I('get.statime'));
			$etime = str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['bstime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($stime))), array('elt', date('Y-m-d H:i:s', strtotime($etime))), 'and');				
		} 
		elseif( isset($_GET['statime']) && !empty($_GET['statime']) )
		{ 
			$stime = str_replace('+', '', I('get.statime'));
			$where['bstime'] = array('egt', date('Y-m-d H:i:s', strtotime($stime)));	
		} 
		elseif( isset($_GET['endtime']) && !empty($_GET['endtime']) )
		{
			$etime = str_replace('+', '', I('get.endtime')) . " 23:59:59";
			$where['bstime'] = array('elt', date('Y-m-d H:i:s', strtotime($etime))); 	
		} 
		
		if( isset($_GET['keyword']) && !empty($_GET['keyword']) ) 
		{
			$keyword = trim(I('get.keyword')); $where['m.mnickname|b.bls|b.bdzh|m.mlpname'] = array('like', "%{$keyword}%", 'or'); 
		}
		
		if( isset($_GET['pstatus']) && $_GET['pstatus']!='' ) 
		{
			$where['b.btype'] = intval( $_GET['pstatus'] ); 	
		}

		$bookkeepinglist = $bookkeepingListArray = array();
		$bookkeepinglist = M('bookkeeping')->alias('AS b')->field('b.*,m.mnickname,m.mlpname')->where($where)->join('__MERCHANT__ AS m ON b.bmid=m.mid', 'left')->order('b.bstime DESC')->select();
		
		if( is_array($bookkeepinglist) && !empty($bookkeepinglist) )
		{
			foreach( $bookkeepinglist as $order ) 
			{
				$key = date('Y-m-d', strtotime($order['bstime']));
				if( !isset($bookkeepingListArray[$key]) ) 
				{
					$bookkeepingListArray[$key] = array(0=>$key, 'a'=>0, 'b'=>0, 'c'=>0, 'd'=>0, 'e'=>0, 'f'=>0);
				}
				
				$bookkeepingListArray[$key]['a'] += $order['bmention'];
				$bookkeepingListArray[$key]['b'] += $order['bjs'];
				$bookkeepingListArray[$key]['c'] += $order['bjy'];
				$bookkeepingListArray[$key]['d'] += $order['bsj'];

				if( $order['btype'] ) 
					$bookkeepingListArray[$key]['e'] += $order['bmention'];
				else
					$bookkeepingListArray[$key]['f'] += $order['bmention'];
			}
		}

		if( I('get.type', 1, 'intval') == 2 ) { //生成图
			$keys = array_keys( $bookkeepingListArray ); sort( $keys );
			$xAxis_data = implode("','", $keys);
			$this->assign('xAxis_data', $xAxis_data);
			sort($bookkeepingListArray);
			
			$yAxis_a = $yAxis_b = $yAxis_c = $yAxis_d = $yAxis_e = $yAxis_f = "";
			foreach( $bookkeepingListArray as $a ):
				$yAxis_a .= $a['a'].","; $yAxis_b .= $a['b'].","; $yAxis_c .= $a['c'].",";
				$yAxis_d .= $a['d'].","; $yAxis_e .= $a['e'].","; $yAxis_f .= $a['f'].",";
			endforeach;
			$this->assign('yAxis_a', $yAxis_a);
			$this->assign('yAxis_b', $yAxis_b);
			$this->assign('yAxis_c', $yAxis_c);
			$this->assign('yAxis_d', $yAxis_d);
			$this->assign('yAxis_e', $yAxis_e);
			$this->assign('yAxis_f', $yAxis_f);
			
			$this->display("StatisticalReportImageMention");
		} else { //文字
			$this->assign('bookkeepingListArray', $bookkeepingListArray);
			$this->display("StatisticalReportTextMention");
		}
	}

	//处理订单归哪一类
	private function _SetOrderClassPrice( array $array_price, array $order ) {
		$price = $order['o_price'];
		
		//预计收入，线上线下，不包含已退款
		if (
			( $order['o_type']==0 && $order['o_dstatus']!=5 && in_array($order['o_pstatus'], array(0, 1)) ) || //线下支付，并且没有关闭订单
			( $order['o_type']!=0 && $order['o_dstatus']!=5 && $order['o_pstatus']==0 ) || //线上支付，未支付成功，订单没有关闭
			( $order['o_type']!=0 && in_array($order['o_pstatus'], array(1, 3)) ) //线上支付，已支付或待退款
		) $array_price['a'] += $price;
		
		//预计收入，线上商家，不包含已退款
		if (
			( $order['o_type']==1 && $order['o_dstatus']!=5 && $order['o_pstatus']==0 ) || 
			( $order['o_type']==1 && in_array($order['o_pstatus'], array(1, 3)) )
		) $array_price['b'] += $price;
		
		//预计收入，线上平台，不包含已退款
		if (
			( $order['o_type']==2 && $order['o_dstatus']!=5 && $order['o_pstatus']==0 ) || 
			( $order['o_type']==2 && in_array($order['o_pstatus'], array(1, 3)) )
		) $array_price['c'] += $price;
		
		//预计收入，线下商家
		if (
			$order['o_type']==0 && $order['o_dstatus']!=5 && in_array($order['o_pstatus'], array(0, 1))
		) $array_price['d'] += $price;
		
		
		//实际收入，线上线下，不包含已退款
		if (
			( $order['o_type']==0 && $order['o_dstatus']==4 && $order['o_pstatus']==1 ) || //线下支付，忆完成
			( $order['o_type']!=0 && in_array($order['o_pstatus'], array(1, 3)) ) //线上支付，已支付或待退款
		) $array_price['e'] += $price;
		
		//实际收入，线上商家，不包含已退款
		if (
			$order['o_type']==1 && in_array($order['o_pstatus'], array(1, 3)) //线上支付，已支付或待退款
		) $array_price['f'] += $price;
		
		//实际收入，线上平台，不包含已退款
		if (
			$order['o_type']==2 && in_array($order['o_pstatus'], array(1, 3)) //线上支付，已支付或待退款
		) $array_price['g'] += $price;
		
		//实际收入，线下商家
		if (
			$order['o_type']==0 && $order['o_dstatus']==4 && $order['o_pstatus']==1 //线上支付，已支付或待退款
		) $array_price['h'] += $price;
		
		//商家，待退款
		if (
			$order['o_type']==1 && $order['o_pstatus']==3
		) $array_price['i'] += $price;
		
		//商家，已退款
		if (
			$order['o_type']==1 && $order['o_pstatus']==2 
		) $array_price['j'] += $price;
		
		//平台，待退款
		if (
			$order['o_type']==2 && $order['o_pstatus']==3
		) $array_price['k'] += $price;
		
		//平台，已退款
		if (
			$order['o_type']==2 && $order['o_pstatus']==2
		) $array_price['l'] += $price;
	}


	
	
	
}