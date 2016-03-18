<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class AgenController extends ManagerController { 
	private $_AddressList = array();
	
	public function _initialize() {
		parent::_initialize();
		$this->_AddressList = F('AddressList');
		if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $this->_AddressList);
	}
	
	//代理商列表
	public function index() {
		//获取当前代理的所有子代理
		$agentid = \Common\Org\Cookie::get('agentid');
		$agentid_list = $this->_getAgentId( $agentid );

		//防止修改URL查看别人的子代理
		$where = array('m.mtype'=>1, 'a.pid'=>$agentid, 'm.mstatus'=>array("neq", "-1"));     
		if( I('get.pid', 0, 'intval') ) { 
			$pid = I('get.pid'); if( !in_array($pid, $agentid_list) ) E('你无权操作');
			$where['a.pid'] = $pid;
		}
		if( I('get.keyword', '') ) { $keyword=I('get.keyword', ''); $where['a.anickname'] = array('like', "%{$keyword}%"); }
		if( I('get.arank', '') ) { $where['a.arank'] = I('get.arank', 'g'); }

		//获取子代理列表
		$page = new \Think\Page(M('Agent')->alias('AS a')->where($where)->join('__MEMBER__ AS m ON a.mid=m.mid')->count(), 16);
		$agentsList = M('Agent')
					->field( 'a.id,a.pid,a.anickname,a.mid,a.arank,a.atype,a.aid,a.acontactsname,a.acontactstel,m.mregdate,m.mstatus,m.mpwd, (select count(*) from azd_agent as n where n.pid=a.id) as count')
					->alias('AS a')
					->where( $where )
					->join('__MEMBER__ AS m ON a.mid=m.mid')
					->limit($page->firstRow.','.$page->listRows)
					->order('a.id DESC')->select();
		$this->assign('agentsList', $agentsList ? $agentsList : array());
		$this->assign('pages', $page->show());
		
		//当前登录的代理商的权限
		$this->assign('addressApid', M("agent")->where("id=".$agentid)->getField('arank'));
			
		$this->display();
	}

	//添加子代理商
	public function add() {
		$agentid = \Common\Org\Cookie::get('agentid');
		$agentinfo = M("agent")->where("id=".$agentid)->find();
		if( $agentinfo['arank']=='g' || !$agentinfo['arank'] ) E('你无权添加子代理');
		
		if( IS_POST ) {
			$_POST['member']['mstatus'] = 0;
			$_POST['member']['mpwd'] = md5(md5($_POST['member']['mpwd']));
			$_POST['info']['acertificates'] = serialize($_POST['image']);
			$_POST['info']['pid'] = $agentid;
			
			$AgentMobel = D("Agent");
			$AgentMobel->startTrans();
			
			$status_01 = $_POST['info']['mid'] = D('Member')->insert($_POST['member']);
			$status_02 = $AgentMobel->insert($_POST['info']);
			
			if( $status_01 && $status_02 ) {
				$AgentMobel->commit(); $this->display('Jump:success');
			} else {
				$AgentMobel->rollback(); 
				$this->display('Jump:error');
			}
        } else {
			//判断当前的代理是不是还可以添加子代理
			if( $agentinfo['overnum'] >= $agentinfo['maxnum'] ) { E('你的最大可开商户数已经用过，无法再进行添加子代理了!'); }
            $addressList = array();
			foreach( $this->_AddressList as $a ) {
				if( $a['apid']==$agentinfo['aid'] ) { $addressList[] = $a;}
			}
			$this->assign('addressList', $addressList);
			$this->assign('addressApid', $agentinfo['arank']);
			$this->assign('addressAid', $agentinfo['aid']);
			$this->assign('maxnum', $agentinfo['maxnum']);
            $this->display();
        }
    }

    //修改子代理商
    public function edit(){ 
    	if( IS_POST ) {
            $_POST['info']['acertificates'] = serialize($_POST['image']);
            if( M('agent')->save($_POST['info']) !== false ) { 
                 $this->display('Jump:success');
            } else { 
				$this->assign('msg', M('agent')->getError());
				$this->display('Jump:error');
			}
        } else {
			$agent = M('agent')->alias('AS a')->where(array('a.id'=>I('get.id', 0, 'intval'), 'm.mtype'=>1))->join('__MEMBER__ as m ON a.mid=m.mid')->field('a.*,m.idcard')->find();
			if(!is_array($agent) || empty($agent)) { $this->assign('msg', '代理商信息不存在'); $this->display('Jump:error'); }
			$agent['acertificates'] = unserialize($agent['acertificates']);
            $this->assign('agent', $agent);
			$this->assign('addressArrs', explode(" ", get_address_byid($agent['aid'])));
			if( $agent['pid'] ) {
				$this->assign('pagent', M('agent')->where(array('id'=>$agent['pid']))->getField('anickname'));	
			}
			
			//当前登录的代理商的权限
			$agentid = \Common\Org\Cookie::get('agentid');
			$this->assign('addressApid', M("agent")->where("id=".$agentid)->getField('arank'));
			$this->display();
        }
    }
	
	//预览子代理商
	public function preview() {
		$agent_array = array('q'=>'省级代理', 'c'=>'市级代理', 'q'=>'区级代理', 'g'=>'个人代理');
		$agent = M('agent')->alias('AS a')->where(array('a.id'=>I('get.id', 0, 'intval'), 'm.mtype'=>1))->join('__MEMBER__ AS m ON m.mid=a.mid')->field('a.*,m.mstatus,m.mname,m.mregdate,m.mbdzh,m.idcard')->find();
		if(!is_array($agent) || empty($agent)) { $this->assign('msg', '代理商信息不存在'); $this->display('Jump:error'); }
		$agent['acertificates'] = unserialize($agent['acertificates']);
		$agent['arank'] = $agent_array[ $agent['arank'] ];
        $agent['mstatus'] = $agent['mstatus']==1 ? '正常' : '禁用'; 
        $this->assign('agent', $agent);
        $this->display();
    }

	
	//禁用代理商
    public function agentDel() { 
        $id = I('get.id', '') or exit('0'); 
		$mstatus = I('get.mstatus', '') or exit('0');
		
		$AgentMobel = D('Agent');
		$AgentMobel->starttrans();
		
		$status_01 = \Common\Org\Maxmerchant::SetMaxInc( $id );
		$status_02 = D('Agent')->deleteAgent( $id, $mstatus );
		
		if( $status_01 && $status_02 ) {
			$AgentMobel->commit(); exit('1');
		} else {
			$AgentMobel->rollback(); exit('0');
		}
    }
	
	 //重置密码
	public function updateMpwd() { 
        $id = I('get.mid', ''); if( !$id ) exit('0');
		$mmpwd=md5(md5('000000'));  
	  	exit(M('member')->where(array('mid'=>array('in', "$id")))->save(array('mpwd'=>$mmpwd)) !== false ? "1" : "0"); 
    }
    
	//ajax获取市级地区列表
    public function publicGetaddress( $pid=0 ) {
        $str = '';
        foreach($this->_AddressList as $address) {
            if($address['apid'] == $pid) $str .= '<option value="'.$address['aid'].'">'.$address['aname'].'</option>';
        }
        exit($str);   
    }

	//下载代理商数据
	public function downloadAgents()
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
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '子代理统计表');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
 		
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '代理商ID')
					->setCellValue('B2', '代理商全称')
					->setCellValue('C2', '代理商简称')
					->setCellValue('D2', '代理商地址')
					->setCellValue('E2', '联系人')
					->setCellValue('F2', '联系人电话')
					->setCellValue('G2', '入驻时间');
		
		
		//查询所有的子代理
		$child_agent_list = M('agent')->alias('AS a')->join("__MEMBER__ AS m ON a.mid=m.mid")->where("a.pid={$agentid} and m.mstatus=1")->field("a.*,m.mregdate")->select();
		if( is_array($child_agent_list) && !empty($child_agent_list) )
		{
			$i = 3;
			foreach($child_agent_list as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:G{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:G{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$i, $c['id'])->setCellValue('B'.$i, $c['anickname'])->setCellValue('C'.$i, $c['aabbreviation'])->setCellValue('D'.$i, $c['aaddress'])->setCellValue('E'.$i, $c['acontactsname'])->setCellValue('F'.$i, $c['acontactstel'])->setCellValue('G'.$i, $c['mregdate']);
				$i ++;
			endforeach;
		}
		
		$objPHPExcel->getActiveSheet()->setTitle('子代理统计表');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="子代理统计表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
  
	//获取此代理商下的所有子代理
	private function _getAgentId( $agentid ) {
		static $cagent_list_id = array();

		$cagent_list = M("agent")->where("pid=".$agentid)->field("id")->select();
		if( is_array($cagent_list) && !empty($cagent_list) ) {
			foreach($cagent_list as $c) {
				$cagent_list_id[] = $c['id']; self::_getAgentId($c['id']);
			}
		}
		return $cagent_list_id;
	}
}