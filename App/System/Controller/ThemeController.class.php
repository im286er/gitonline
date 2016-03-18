<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class ThemeController extends ManagerController {
    //模板列表
    public function themesList() {
		$mid= \Common\Org\Cookie::get('mid');
		$gidData=M("member")->where("mid=".$mid)->find(); 
		$gid=$gidData['gid'];
		$where = array();
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$time=str_replace('+','',I('get.statime'));
			$endtime=str_replace('+','',I('get.endtime'));
			$where['taddtime'] = array(array('egt', date('Y-m-d H:i:s', strtotime($time))), array('elt', date('Y-m-d H:i:s', strtotime($endtime))), 'and');					
		} elseif( I('get.statime', '') ) {
			$where['taddtime'] = array('egt', date('Y-m-d H:i:s', strtotime($time)));	
		} elseif( I('get.endtime', '') ) {
			$where['taddtime'] = array('elt', date('Y-m-d H:i:s', strtotime($endtime)));   	
		}
		if( I('get.keywords', '') ) {
			$keywords = I('get.keywords', ''); $where['t_name|t_desc'] = array('like', "%{$keywords}%", 'or');
		}
		$page = new \Think\Page(M('theme')->where($where)->count(), 10);
    	$this->assign('themelist', M('theme')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->assign('gid', $gid);
		$this->display();
    }

    //添加模板
    public function themeAdd() {
        if( IS_POST ){
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$_POST['info']['t_vid'] = in_array(0, $_POST['t_vid']) ? ",0," : ','.implode(',', $_POST['t_vid']).',';	
			
            if( M('theme')->add($_POST['info']) ) {
                $this->display('Jump:success');
            } else { $this->display('Jump:error'); };
        } else {
			$vocationList = F('VocationList');
            if( !is_array($vocationlist) || empty($vocationList) ) B('\Common\Behavior\CreateVocation', '', $vocationList);
            
            foreach($vocationList as $vid=>$vocation) {
				$vocation['pid'] = $vocation['v_pid'];
                $rulesListArray[$vid] = $vocation; 
            }
            $str = "<option value=\$v_id \$selected>\$spacer \$v_title</option>"; 
            $this->assign('vocationList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, $str));
            
			$this->display();
        }
    }

    //删除模板
    public function themeDel() {
		$tid = I('get.tid'); if( !$tid ) exit('');
		exit( M('theme')->where(array('t_id'=>array('in', "$tid")))->setField('t_status', "0") !== false ? "1" : "0" );
    }
    
    //修改模板
    public function themeEdit() {
        if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); }); 
			$_POST['info']['t_vid'] = in_array(0, $_POST['t_vid']) ? ",0," : ','.implode(',', $_POST['t_vid']).',';	
			
            if( M('theme')->save($_POST['info']) !== false ) {
                $this->display('Jump:success');
            } else { $this->display('Jump:error'); };
        } else {
            $themeInfo = M('theme')->where(array('t_id'=>I('get.tid', 0, 'intval')))->find();
            if( !$themeInfo || !is_array($themeInfo) ) { $this->display('Jump:error'); }
			$v_id = explode(',', trim($themeInfo['t_vid'], ','));
			$vocationList = F('VocationList');
            if( !is_array($vocationlist) || empty($vocationList) ) B('\Common\Behavior\CreateVocation', '', $vocationList);
            foreach($vocationList as $vid=>$vocation) {
				$vocation['pid'] = $vocation['v_pid'];
				$vocation['selected'] = in_array($vid, $v_id) ? "selected='selected'" : '';
                $rulesListArray[$vid] = $vocation; 
            }
			if( in_array(0, $v_id) ) $this->assign('all', 1);
            $str = "<option value=\$v_id \$selected>\$spacer \$v_title</option>"; 
            $this->assign('vocationList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, $str));
            $this->assign('themeInfo', $themeInfo);
            $this->display();   
        }
    }
	
	//Ajax验证模板名称是否存在
	public function publicCheckTname() {
		$themeinfo = M('theme')->where(array('t_sign'=>I('get.tname')))->find();
		$status    = !empty($themeinfo) && is_array($themeinfo) ? "0" : "1";
		$tid 	   = I('get.t_id', '', 'intval');
		if( !$status && $tid && $tid==$themeinfo['t_id'] ) { $status = "1"; }
		exit( $status );	
	}
	
}