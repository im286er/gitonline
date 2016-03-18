<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class UserController extends ManagerController {
	public $agentid, $tjids=array();
	
	public function _initialize() {
		parent::_initialize();	
		$this->agentid = $agentid = \Common\Org\Cookie::get('agentid');	
		
		//查所所有的子代理
		$child_agent_list_array = array(
			$agentid => '自身代理'
		);
		$child_agent_list = M('agent')->alias('AS a')->join("__MEMBER__ AS m ON a.mid=m.mid")->where("a.pid={$agentid} and m.mstatus>=0")->field("a.id,a.anickname")->select();
		foreach($child_agent_list as $c) $child_agent_list_array[$c['id']] = $c['anickname'];
		
		//获取所有的商家
		$agentid_list = array_keys($child_agent_list_array);
		$merchant_list = M('merchant')->alias('AS j')->join("__MEMBER__ AS m ON j.mid=m.mid")->where(array("j.magent"=>array("in", $agentid_list), "m.mstatus"=>array('egt',0)))->field("j.jid")->select();
		
		foreach($merchant_list as $merchant) $this->tjids[] = $merchant['jid'];
	}
	
    //会员列表
    public function usersList() {
		$where['u_jid'] = array('in', $this->tjids);
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$stime=I('get.statime'); $etime=I('get.endtime');
			$where['u_regtime'] = array(array('egt', date('Y-m-d 00:00:00', strtotime($stime))), array('elt', date('Y-m-d 23:59:59', strtotime($etime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$stime=I('get.statime');
			$where['u_regtime'] = array('egt', date('Y-m-d 00:00:00', strtotime($stime)));	
		} elseif( I('get.endtime', '') ) {
			$etime=I('get.endtime');
			$where['u_regtime'] = array('elt', date('Y-m-d 23:59:59', strtotime($etime)));          	
		}

		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['u_name|u_source|mnickname'] = array('like', "%{$keyword}%", 'or'); 
		}
		$page = new \Think\Page(D('View')->view('user')->where($where)->count(), 10);
        $this->assign('usersList',D('View')->view('user')->where($where)->order('u_regtime DESC')->limit($page->firstRow.','.$page->listRows)->select());
	    $this->assign('pages',$page->show()); 
		$this->display();
    }
	
	//返利会员
	public function vusersList() {
		$count = M('flUser')->where("`flu_sagentid`=".$this->agentid)->count();
		
		$stime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
		$etime = date('Y-m-d H:i:s', mktime(59, 59, 59, date('m'), date('d')-1, date('Y')));
		$ycount = M('flUser')->where("`flu_sagentid`=".$this->agentid." and `flu_regtime`>='{$stime}' and `flu_regtime`<='{$etime}'")->count();
		
		$stime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
		$tcount = M('flUser')->where("`flu_sagentid`=".$this->agentid." and `flu_regtime`>='{$stime}'")->count();
		
		$stime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-date('w'), date('Y')));
		$wcount = M('flUser')->where("`flu_sagentid`=".$this->agentid." and `flu_regtime`>='{$stime}'")->count();

		$this->assign('count', $count);
		$this->assign('ycount', $ycount);
		$this->assign('tcount', $tcount);
		$this->assign('wcount', $wcount);
		
		$where['flu_puserid'] = I('get.puserid', 0, 'intval');
		$where['flu_sagentid'] = $this->agentid;
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$stime=I('get.statime'); $etime=I('get.endtime');
			$where['flu_regtime'] = array(array('egt', date('Y-m-d 00:00:00', strtotime($stime))), array('elt', date('Y-m-d 23:59:59', strtotime($etime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$stime=I('get.statime');
			$where['flu_regtime'] = array('egt', date('Y-m-d 00:00:00', strtotime($stime)));	
		} elseif( I('get.endtime', '') ) {
			$etime=I('get.endtime');
			$where['flu_regtime'] = array('elt', date('Y-m-d 23:59:59', strtotime($etime)));          	
		}

		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['flu_nickname'] = array('like', "%{$keyword}%", 'or'); 
		}
		
		$page = new \Think\Page(D('View')->view('vuser')->where($where)->count(), 10);
        $this->assign('usersList',D('View')->view('vuser')->where($where)->order('flu_regtime DESC')->limit($page->firstRow.','.$page->listRows)->select());
	    $this->assign('pages',$page->show()); 
		$this->display();
	}

	//查看会员信息
	public function usersInfo() {
		$userInfo = D('View')->view('user')->where(array('u_id'=>I('get.uid', 0, 'intval')))->find(); 
		if( !is_array($userInfo) && empty($userInfo) ) $this->display('Jump:error');
		$this->assign('user', $userInfo);
		$this->display();	
	}
	
	//查看V会员的信息
	public function vusersInfo() {
		$userInfo = D('View')->view('vuser')->where(array('flu_userid'=>I('get.uid', 0, 'intval')))->find(); 
		if( !is_array($userInfo) && empty($userInfo) ) $this->display('Jump:error');
		$this->assign('count', M('flUser')->where("flu_puserid=".$userInfo['flu_userid'])->count());		
		$this->assign('user', $userInfo);
		$this->display();
	}

	//查看会员消费记录
	public function consumption() {
		$orderInfo = M('order')->where(array('o_uid'=>I('get.uid', 0, 'intval')))->select();
		$countNum = 0;
		foreach($orderInfo as $key=>$value) {
			//此处的效率极低，先这样，以后好好优化
			$orderInfo[$key]['ogoods'] = M($value['o_table'])->where('sp_oid='.$value['o_id'])->select();
			$countNum += $value['o_price'];	
		}
		$this->assign('countNum', number_format($countNum, 2));
		$this->assign('orderInfo', $orderInfo);
		$this->display();
	}
	
	//查看V会员消息记录
	public function vconsumption() {
		$orderInfo = M('flOrder')->alias('AS o')->join("__MERCHANT__ AS m ON o.flo_jid=m.jid", "left")->field("o.*,m.mnickname")->where(array('flo_uid'=>I('get.uid', 0, 'intval')))->select();
		$countNum = 0;
		foreach($orderInfo as $key=>$value) { $countNum += $value['o_price']; }
		$this->assign('countNum', number_format($countNum, 2));
		$this->assign('orderInfo', $orderInfo);
		$this->display();	
	}

	//下载会员
	public function downloadUsers()
	{
		$agentid = \Common\Org\Cookie::get('agentid');
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
		
		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		foreach($char_column_list as $char)
			$objPHPExcel->getActiveSheet()->getColumnDimension($char)->setWidth('30');
		
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '会员统计表');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
 		
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '会员ID')
					->setCellValue('B2', '会员昵称')
					->setCellValue('C2', '会员姓名')
					->setCellValue('D2', '会员手机')
					->setCellValue('E2', '注册来源')
					->setCellValue('F2', '所属商家')
					->setCellValue('G2', '注册时间');
		
		
		$where['u_jid'] = array('in', $this->tjids);
        $user_list = D('View')->view('user')->where($where)->order('u_regtime DESC')->select();
		
		if( is_array($user_list) && !empty($user_list) )
		{
			$i = 3;
			foreach($user_list as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:G{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:G{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$i, $c['u_id'])->setCellValue('B'.$i, $c['u_name'])->setCellValue('C'.$i, $c['u_ename'])->setCellValue('D'.$i, $c['u_phone'])->setCellValue('E'.$i, $c['u_source'])->setCellValue('F'.$i, $c['mnickname'])->setCellValue('G'.$i, $c['u_regtime']);
				$i ++;
			endforeach;
		}
		
		$objPHPExcel->getActiveSheet()->setTitle('会员统计表');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="会员统计表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;		
	}
	
}