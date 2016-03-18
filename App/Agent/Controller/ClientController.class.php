<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class ClientController extends ManagerController {
 
    public function index(){
    	
        $this->display();
    }
    public function  download(){
    	$jid=I('jid',0,'intval');
    	$this->assign('jid',$jid);
    	$this->display();
    }
    
    /**
     * 更新点击次数
     */
    Public function clickNum(){

    	$id = I('id', 0, 'intval');
    	$jid=I('jid',0,'intval');
    
    	$jid = M("merchant_app map")->join("azd_merchant am on map.jid = am.jid")
								    	->where("map.jid=".$jid)//
								    	->find();
    	if($jid['jid']){
    		//$id值0,1,2分别是扫码,苹果,安卓
    		$where = array('jid'=>$jid['jid']);
    		if($id=='2'){
    			$data = array('android_downloads'=>$jid['android_downloads']+1,'endmakedate'=>date('Y-m-d H:i:s'));
    			$result = M("merchant_app")->where($where)->setField($data);
    			redirect('http://t2.fanwe.net:82/soumeiwei.apk', 1, '页面跳转中...');
    		}else if($id=='1'){
    			$data = array('ios_downloads'=>$jid['ios_downloads']+1,'endmakedate'=>date('Y-m-d H:i:s'));
    			$result = M("merchant_app")->where($where)->setField($data);
    			redirect('http://t2.fanwe.net:82/soumeiwei.ipa', 1, '页面跳转中...');
    		}
    	}else{ 
    		$result=M("merchant am")->where("am.mid=5")->find();
    		$data=array(
    				'jid'=>$result['jid'],
    				'android_downloads'=>'1',
    				'ios_downloads'=>'1',
    				'endmakedate'=>date('Y-m-d H:i:s'),
    				'appname'=>$result['mnickname'],
    				'applogo'=>'logo'
    		);
    		$resultdata = M("merchant_app")->add($data);
    		if($resultdata==false){
    			exit(M(merchant_app)->getError());
    			break;
    		}else{
    			$this->redirect("Agent/Client/index");
    		}	
    	}
    	
    	//echo 'document.write(' . $result . ')';
    }
  
}