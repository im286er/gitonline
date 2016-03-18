<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class OpinionController extends ManagerController{

    //意见列表
    public function opinionsList() {
       	//$where = array('mtype'=>3, 'op_type'=>'0');
		$where = array('op_status'=>'1');
		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['op_content|sname'] = array('like', "%{$keyword}%", 'or');
		}
		$page = new \Think\Page(D('View')->view('opinion')->where($where)->count(), 10);
    	$this->assign('opinionlist', D('View')->where($where)->order('op_addtime desc')->limit($page->firstRow.','.$page->listRows)->select());
		//print_r(D('View')->getlastsql());
		//print_r(D('View')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
        $this->display();
    }
	
	//查看记录
	public function opinionsInfoList() {
        $opid=I('get.opid', 0, 'intval');
		$opinion = D('View')->view('opinfo')->where("op_id=".$opid)->order('op_addtime DESC')->select();     
		//print_r($opinion);exit;
		if(!is_array($opinion) || empty($opinion)) $this->display('Jump:error'); 
		$this->assign('opinion', \Common\Org\Tree::ItreeInitialize()->initialize($opinion)->getSortList(0)); 
		$this->display();	
	}


}