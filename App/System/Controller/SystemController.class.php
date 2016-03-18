<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class SystemController extends ManagerController {
	//节点列表
    public function rulesList() {
    	$pid  = I('get.pid', 0, 'intval');
		$where = " pid=" . $pid;
		if( isset($_GET['keyword']) && !empty($_GET['keyword']) )
		{
			$where .= " and title like '%".I('get.keyword')."%'";
		}
		if( isset($_GET['ruletype']) && !empty($_GET['ruletype']) )
		{
			$where .= " and type=".intval( $_GET['ruletype'] );
		}

		$auth_list = M()->query("SELECT o.*, ( select count(*) from azd_auth_rule as i where i.pid = o.id) as count FROM `azd_auth_rule` as o where {$where} order by o.sort");
		$this->assign('rulelist', $auth_list);
		$this->assign('rulename', array('系统节点', '一级节点', '二级节点', '三级节点', '普通节点') );
		$this->assign('pid', $pid);
		$this->display();
    }
	
	//添加节点
    public function ruleAdd() {
    	if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			if( M('authRule')->add($_POST['info']) ) {
				$this->display('Jump:success');
			} else { $this->display('Jump:error'); };
    	} else {
			$pid = I('get.pid', 0, 'intval');
			if( $pid ) $this->assign('pruleInfo', M('authRule')->where(array('id'=>$pid))->find());
			foreach(M('authRule')->where(array('type'=>array('lt', 4), 'status'=>1))->order('id')->select() as $r) { 
				$r["selected"] = $pid && $pid==$r["id"] ? "selected=\"selected\"" : ""; $rulesListArray[$r["id"]] = $r; 
			}
			$this->assign('rulesList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, "<option value=\$id \$selected>\$spacer \$title</option>"));
			$this->display();
    	}
    }
	
	//递归删除节点
    public function ruleDel() {
		$id = I('get.id', 0, 'intval'); if( !$id ) exit('');
		if( isset($_GET['status']) ) {
			exit(M('authRule')->where(array('id'=>$id))->setField('status', I('get.status', 0, 'intval')== 1 ? 0 : 1) ? '1' : '0'); 
		} else {
			exit(implode(',', D('AuthRule')->deleteRule($id)));
		}
    }

	//修改节点
    public function ruleEdit() {
		if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			if( M('authRule')->save($_POST['info']) !== false ) {
				$this->display('Jump:success');
			} else { $this->display('Jump:error'); };
		} else {
			$ruleInfo = M('authRule')->where(array('id'=>I('get.rid', 0, 'intval')))->find();
			if( !$ruleInfo || !is_array($ruleInfo) ) { $this->assign('msg', '节点信息出现错误'); $this->display('Jump:error'); }
			foreach(M('authRule')->where(array('type'=>array('lt', 4), 'status'=>1))->order('id')->select() as $r) { 
				$r["selected"] = $ruleInfo['pid']==$r["id"] ? "selected=\"selected\"" : ""; $rulesListArray[$r["id"]] = $r; 
			}
			$this->assign('rulesList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, "<option value=\$id \$selected>\$spacer \$title</option>"));
			$this->assign('ruleInfo', $ruleInfo);
			$this->display();	
		}		
    }

	//权限组列表
    public function groupsList() {
        $where = array();
        if( I('get.keywords') ) { $where['title'] = array('like', "%".I('get.keywords', '')."%"); }
		if( isset($_GET['status']) && $_GET['status'] != '' ) $where['status'] = I('get.status', 0, 'intval'); 
		$page = new \Think\Page( M('authGroup')->where($where)->count(), 10);
    	$this->assign('grouplist', M('authGroup')->where($where)->limit($page->firstRow.','.$page->listRows)->order('id')->select());
		$this->assign('pages', $page->show());
    	$this->display();
    }

	//添加权限组
    public function groupAdd() {
    	if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			if( M('authGroup')->add($_POST['info']) ) { $this->display('Jump:success'); } else { $this->display('Jump:error'); };
		} else {
    		$this->display();
    	}
    }
	
	//禁用权限组
	public function groupDel() {
		$gid = I('get.gid', ''); if( !$gid ) exit('0'); $status = I('get.status', '0') == 1 ? 0 : 1;
		exit( M('authGroup')->where(array('id'=>array('in', "$gid")))->setField('status', $status) ? "1" : "0" );
    }
	
	//修改权限组
    public function groupEdit() {
		if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			if( M('authGroup')->save($_POST['info']) !== false ) { $this->display('Jump:success'); } else { $this->display('Jump:error'); }
		} else {
			$groupInfo = M('authGroup')->where(array('id'=>I('get.gid', 0, 'intval')))->find();
			if( !is_array($groupInfo) || empty($groupInfo)) { $this->assign('msg', '权限组信息不存在'); $this->display('Jump:error'); }
			$this->assign('groupInfo', $groupInfo);
			$this->display();			
		}
    }
	
	//设置权限组权限
	public function groupPriv() {		
		if( IS_POST ) {
			$rules = implode(',', $_POST['rules']);
			if( M('authGroup')->where(array('id'=>I('post.gid', 0, 'intval')))->save(array('rules'=>$rules)) !== false) {
				$this->display('Jump:success');
			} else { $this->display('Jump:error'); }
		} else {
			$rulesInfo = M('authGroup')->where(array('id'=>I('get.gid', 0, 'intval')))->find();
			if( !$rulesInfo || !is_array($rulesInfo) ) { $this->assign('msg', '权限组信息不存在'); $this->display('Jump:error'); }
    		$result = M('authRule')->field('id,pid,title')->where(array('status'=>1))->order('id')->select();
			foreach ($result as $n=>$t) {
    			$result[$n]['checked'] = in_array($t['id'], explode(',', $rulesInfo['rules'])) ? ' checked' : '';
    			$result[$n]['level'] = \Common\Org\Tree::ItreeInitialize()->getLevel($t['id'], $result);
    			$result[$n]['pid_node'] = $t['pid'] ? ' class="child-of-node-'.$t['pid'].'"' : '';
    		}
			$str  = "<tr id='node-\$id' \$pid_node><td style='padding-left:30px;'>\$spacer <input type='checkbox' name='rules[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$title</td></tr>";
    		$this->assign('rulelist', \Common\Org\Tree::ItreeInitialize()->initialize($result)->treeView(0, $str));
			$this->display();			
		}					
	}
	
	//AJAX验证权限组名称是否存在
	public function publicCheckGroupName() {
		$groupinfo = M('authGroup')->where(array('title'=>I('get.groupname')))->find();
		$status    = !empty($groupinfo) && is_array($groupinfo) ? "0" : "1";
		$groupid   = I('get.groupid', '', 'intval');
		if( !$status && $groupid && $groupid==$groupinfo['id'] ) { $status = "1"; }
		exit( $status );
	}
	
	//员工列表
    public function staffersList() { 
		$where = array('mtype'=>0, 'mid'=>array('neq', 1));
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$time=str_replace('+','',I('get.statime'));
			$endtime=str_replace('+','',I('get.endtime'));
			$where['mregdate'] = array(array('egt', date('Y-m-d H:i:s', strtotime($time))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$where['mregdate'] = array('egt', date('Y-m-d H:i:s', strtotime($time)));	
		} elseif( I('get.endtime', '') ) {
			$where['mregdate'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime)));   	
		}
		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['mname|msurname|title'] = array('like', "%{$keyword}%", 'or');
		}
		$page = new \Think\Page(D('View')->view('staffer')->where($where)->count(), 10);
		
    	$this->assign('staffersList', D('View')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
    	$this->display();
    }
	
	//添加员工
    public function stafferAdd() {
    	if(IS_POST){
			array_walk($_POST['member'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$_POST['member']['mpwd'] = md5(md5($_POST['member']['mpwd']));
			if( D('Member')->insert($_POST['member']) ) {
				$this->display('Jump:success');
			} else { $this->display('Jump:error'); }
    	} else {
    		$this->assign('authgroup', M('authGroup')->where(array('status'=>1))->select());
    		$this->display();
    	} 
    }
	
	//修改员工
    public function stafferEdit() {
		if( IS_POST ) {
			array_walk($_POST['member'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$mid = I('post.mid', 0, 'intval'); if( !$mid ) $this->display('Jump:error');
			if( isset($_POST['member']['mpwd']) && !empty($_POST['member']['mpwd']) ) {
				$_POST['member']['mpwd'] = md5(md5($_POST['member']['mpwd']));
			} elseif( isset($_POST['member']['mpwd']) ) { unset( $_POST['member']['mpwd'] ); }
			
			if( M('member')->where(array('mid'=>$mid))->save($_POST['member']) !== false ) {
				$this->display('Jump:success');
			} else {  $this->display('Jump:error'); }			
		} else {
			$member = M('Member')->where(array('mid'=>I('get.mid', 0, 'intval'), 'mtype'=>0))->find();
			if(!isset($member) || empty($member)) { $this->assign('msg', '账户信息不存在'); $this->display('Jump:error'); }
			$this->assign('authgroup', M('authGroup')->where(array('status'=>1))->select());
			$this->assign('member', $member);
			$this->display();	
		} 
    }
	
	//删除员工
    public function stafferDel() {
		$mid = I('get.mid', ''); if( !$mid ) exit('0');
		exit( M('Member')->where(array('mid'=>array('in', "$mid"), 'mtype'=>0))->delete()===false ? "0" : "1" );
    }
	
	//禁用或解禁员工
	public function publicHosStaffer() {
		$mid = I('get.mid', ''); if( !$mid ) exit('0'); $status=I('get.status', 0, 'intval'); $status = $status ? 0 : 1;
		exit( M('Member')->where(array('mid'=>$mid, 'mtype'=>0))->setField('mstatus', $status)===false ? "0" : "1" );
	}
	
	//AJAX验证账户是否存在
	public function publicCheckMname() {
		$member = M('member')->where(array('mname'=>I('get.mname')))->find();
		$status = !empty($member) && is_array($member) ? "0" : "1";
		$mid  = I('get.mid', '', 'intval');
		if( !$status && $mid) { if($mid == $member['mid']) $status = "1"; }
		exit( $status );
	}
	
	//修改个人信息
	public function publicEditInfo() {
		if( IS_POST ) {
			array_walk($_POST['member'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$mid = I('post.mid', 0, 'intval'); if( !$mid ) $this->display('Jump:error');
			if( isset($_POST['member']['mpwd']) && !empty($_POST['member']['mpwd']) ) {
			$_POST['member']['mpwd'] = md5(md5($_POST['member']['mpwd']));
			if( M('member')->where(array('mid'=>$mid))->save($_POST['member']) !== false ) {
			$this->success("密码修改成功稍后请重新登录", U('Public/logout@xt'));       
			} else {  $this->error('操作失败'); }
			} 
			else{
             unset( $_POST['member']['mpwd'] );	
			if( M('member')->where(array('mid'=>$mid))->save($_POST['member']) !== false ) {
				$this->success('操作成功');
			} else {  $this->error('操作失败'); }	
				
			}
			
	
		} else {
			$mid = \Common\Org\Cookie::get('mid');
			$this->assign('member', M('member')->where(array('mid'=>$mid))->find());
			$this->display();
		}
	}   
	
}
