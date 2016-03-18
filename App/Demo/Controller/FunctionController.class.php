<?php
namespace Demo\Controller;

class FunctionController extends MerchantController {
    //自动回复——首次打开APP自动回复
    public function autoreply() {
		$path = $this->path.'AutoReply'.I('type', 0, 'intval').'.php';
		if( IS_POST ) {
			$ModuleInfo = I('post.content', '');
			if( $ModuleInfo ) { file_put_contents($path, $ModuleInfo); }
			$this->success('操作成功', U('/Function/autoreply', array('type'=>I('type', 1, 'intval')), true));
		} else {
			file_exists($path) && $autoreply=file_get_contents($path);
			$this->assign('autoreply', $autoreply ? $autoreply : '');
			$this->display();	
		}
    }

    public function module(){
	  $merchant = M('merchant')->where(array('jid'=>$this->jid))->find();
	  if( IS_POST ) {
			$action = I('post.action');
			$module_id = I('post.module_id');
			if(!$action || !$module_id)exit('0');
			$modulesdata = array();
			if($merchant['modules'])
			$modulesdata = explode(',',$merchant['modules']);
			if($action=='install'){
				$modulesdata[] = $module_id;
			}elseif($action == 'uninstall'){
				$ismodule = D('MerchantModule')->where(array('module_sign'=>$module_id))->field('module_id,module_name,module_system')->find();
				if($ismodule['module_system']=='1')exit('2');
				$key = array_search($module_id,$modulesdata);
				if($modulesdata[$key])$modulesdata[$key] = null;
			}
			$modulesdata = array_filter($modulesdata);
			array_unique($modulesdata);
			$moduleids = implode(',',$modulesdata);
			$result = M('merchant')->where(array('jid'=>$this->jid))->setField('modules',$moduleids);
			exit($result?'1':'0');
	  } else {
			$type = I('get.type','0','intval');
		  	$where = array();
			$where['module_status'] = '1';
			$where['module_pid'] = array('gt',0);
			$where['_string'] = "  FIND_IN_SET(0,module_industry)  OR ( FIND_IN_SET('{$merchant[vid]}',module_industry) ) ";
			
			if($merchant['modules'] && $type)
			$where['module_sign']  = array('in',explode(',',$merchant['modules']));
			if($type && !$merchant['modules']){
				$modules = array();
			}else{
				$page = new \Demo\Org\Page( D('MerchantModule')->where($where)->count(), 10);
				$this->assign('pages', $page->show());
				$datalist = D('MerchantModule')->where($where)->order('module_system asc,module_order asc,module_id asc')->limit($page->firstRow.','.$page->listRows)->select();
			}
			$this->assign('datalist',$datalist);
			$this->assign('merchant',$merchant);
			$this->display();	
	  }
    }	

	/**模块模版选择**/
	public function moduletemp(){
		$templates = $moduletemps = array();
		$merchant = M('merchant')->where(array('jid'=>$this->jid))->find();
		$moduletemps = $merchant['moduletemp'] = unserialize($merchant['moduletemp']);
		if(IS_POST ) {
			$module_sign = I('post.module_sign');
			$str = I('post.txt');
			$moduletemps[$module_sign] = $str;
			$result = M('merchant')->where(array('jid'=>$this->jid))->setField('moduletemp',serialize($moduletemps));
			exit($result?'1':'0');
		}
		$module_id = I('get.module_id');
		$moduleinfo = D('MerchantModule')->where(array('module_sign'=>$module_id))->find();
		if(!$moduleinfo)exit('0');
		$templates = unserialize($moduleinfo['module_template']);
		$this->assign('module_filed',$moduletemps[$moduleinfo['module_sign']]);
		$this->assign('templates',$templates);
		$this->assign('moduleinfo',$moduleinfo);
		$this->display();	
	}

}