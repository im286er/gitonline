<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class AgentController extends ManagerController {
    private $_AddressList = array();

    public function _initialize() {
        parent::_initialize();
        $this->_AddressList = F('AddressList');
        if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $this->_AddressList);
    }

	//代理商列表
    public function agentsList() {
        $where = array('m.mtype'=>1, 'a.pid'=>0, 'm.mstatus'=>array("neq", "-1"));     
        if( I('get.pid', 0, 'intval') ) { $where['a.pid'] = I('get.pid', 0, 'intval'); }
        if( I('get.keyword', '') ) { unset($where['a.pid']); $keyword=I('get.keyword', ''); $where['a.anickname'] = array('like', "%{$keyword}%"); }
        if( I('get.arank', '') ) { unset($where['a.pid']); $where['a.arank'] = I('get.arank', 'g'); }
        $page = new \Think\Page(M('Agent')->alias('AS a')->where($where)->join('__MEMBER__ AS m ON a.mid=m.mid')->count(), 16);
		$agentsList = M('Agent')->field( 'a.id,a.pid,a.anickname,a.mid,a.arank,a.atype,a.aid,a.acontactsname,a.acontactstel,m.mregdate,m.mstatus,m.mpwd, (select count(*) from azd_agent as n inner join azd_member as m ON n.mid=m.mid where n.pid=a.id and m.mstatus<>-1) as count')->alias('AS a')->where( $where )->join('__MEMBER__ AS m ON a.mid=m.mid')->limit($page->firstRow.','.$page->listRows)->order('a.id DESC')->select();
		$this->assign('agentsList', $agentsList);
        $this->assign('pages', $page->show());
		
		$groupid = \Common\Org\Cookie::get('groupid');
		$this->assign('groupid', $groupid);
		$this->display();
    }

    //添加代理商
    public function agentAdd() {
        if( IS_POST ) {
			$_POST['member']['mpwd'] = md5(md5($_POST['member']['mpwd']));
			$_POST['info']['acertificates'] = serialize($_POST['image']);

			$AgentMobel = D('Agent');
			$AgentMobel->starttrans();
			
			$status_01 = $_POST['info']['mid'] = D('Member')->insert($_POST['member']);
			$status_02 = $AgentMobel->insert($_POST['info']);
			$status_03 = \Common\Org\Maxmerchant::SetMaxDec( $status_02 );
			
			if( $status_01 && $status_02 && $status_03 ) {
				$AgentMobel->commit(); $this->display('Jump:success');
			} else {
				$this->assign('msg', D('Member')->getError()." ".$AgentMobel->getError());
				$AgentMobel->rollback(); $this->display('Jump:error'); 
			}
        } else {
			$addressList = array();
			foreach( $this->_AddressList as $a ) {
				if( $a['apid']==0 && $a['aid']!=0 ) $addressList[] = $a;	
			}
			$this->assign('addressList', $addressList);
            $this->display();
        }
    }
	
   	//删除代理商和解禁代理商
    public function agentDel() { 
        $id = I('get.id', ''); if( !$id ) exit('0');
		$mstatus = I('get.mstatus', 0, 'intval');
		
		$AgentMobel = D('Agent');
		$AgentMobel->starttrans();
		
		if( $mstatus==1 ) {//关闭
			$status_01 = \Common\Org\Maxmerchant::SetMaxInc( $id );
		} else {//开启
			$status_01 = \Common\Org\Maxmerchant::SetMaxDec( $id );
		}		
		$status_02 = $AgentMobel->deleteAgent( $id, $mstatus );

		if( $status_01 && $status_02 ) {
			$AgentMobel->commit(); exit('1');
		} else {
			$AgentMobel->rollback(); exit('0');
		}
    }
	
	public function agentMpwd() {
		$id = I('get.mid', '0', 'intval');
		exit(M('member')->where(array('mid'=>$id))->setField('mpwd', md5(md5('000000'))) !== false ? "1" : "0");
	}
	
    //修改代理商 
    public function agentEdit() {
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
			$this->display();
        }
    }
    
    //预览代理商
    public function agentPriv() {
		$agent_array = array('q'=>'省级代理', 'c'=>'市级代理', 'q'=>'区级代理', 'g'=>'个人代理');
		$agent = M('agent')->alias('AS a')->where(array('a.id'=>I('get.id', 0, 'intval'), 'm.mtype'=>1))->join('__MEMBER__ AS m ON m.mid=a.mid')->field('a.*,m.mstatus,m.mname,m.mregdate,m.mbdzh,m.idcard')->find();
		if(!is_array($agent) || empty($agent)) { $this->assign('msg', '代理商信息不存在'); $this->display('Jump:error'); }
		$agent['acertificates'] = unserialize($agent['acertificates']);
		$agent['arank'] = $agent_array[ $agent['arank'] ];
        $agent['mstatus'] = $agent['mstatus']==1 ? '正常' : '禁用'; 
        $this->assign('agent', $agent);
        $this->display();
    }
    
    //ajax获取市级地区列表
    public function publicGetaddress( $pid=0 ) {
		$addressList = array();
        $str = '';
        foreach($this->_AddressList as $address) {
            if($address['apid'] == $pid) $str .= '<option value="'.$address['aid'].'">'.$address['aname'].'</option>';
        }
        exit($str);   
    }
	
	//删除代理商
	public function truncateAgent() {
		$mid = I('post.mid') or die('0');	
		exit( M("member")->where("mid=".$mid)->setField("mstatus", -1) !== false ? "1" : "0" );
	}
}
