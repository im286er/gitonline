<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class ModularController extends ManagerController {
 
    public function index(){
		$result=M("opinion as op")->join("azd_member as am on op.op_mid=am.mid")
								->join("azd_shop as sh on op.op_sid=sh.sid")
								->where("op.op_type='0'")
								->select();
		$this->assign("result",$result);
        $this->display();
    }
	public function record(){
		
		$opid=I('opid',0,'intval');
		$dataresult=M("opinion as op")->join("azd_member as am on op.op_mid=am.mid")
									->join("azd_shop as sh on op.op_sid=sh.sid")
									->where(" op.opid=$opid")
									->select();
		$this->assign("dataresult",$dataresult);
		$this->display();
	}
    
  
}