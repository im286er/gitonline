<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class OperateController extends ManagerController {
	public function _initialize()
	{
		/* if(!isset($_SESSION['member']) || empty($_SESSION['member'])) {
			header("Location:".__ROOT__.'index.php/Public/login');
			exit;
		} */
	}
    public function index(){
    	$count =M("notice")->count();// 查询满足要求的总记录数
    	$Page = new \Think\Page($count,1);// 实例化分页类 传入总记录数和每页显示的记录数(25)
    	$show = $Page->show();// 分页显示输出
    	$result=M("notice no")->join("azd_member am on no.mid = am.mid")
    					->order('no.nid DESC')
    					->limit($Page->firstRow.','.$Page->listRows)->select();
    	$this->assign('result',$result);
    	$this->assign('count',$count);
    	$this->assign('page',$show);
        $this->display();
    }
    public function add(){ 
    	$this->display();
    }
    public function insert(){ 
    	$data=M("notice")->create();
    	$data['ndate']=date('Y-m-d H:i:s');
    	$data['mid']=$_SESSION['member']['mid'];
    	$result=M("notice")->add($data);
    	if($result){ 
    		$this->success('发布成功', U('Agent/Operate/index'));
    	}else{ 
    		$this->success('发布失败', U('Agent/Operate/add'));
    	}
    }
}