<?php
namespace Demo\Controller;
use Common\Controller\ManagerController;

class MerchantController extends ManagerController {
    protected $jid, $mid, $path, $type, $tsid, $sidlist=array();

	public function _initialize() {
		parent::_initialize();
		$this->jid = \Common\Org\Cookie::get(C('USER_COOKIE_JID'));	
		$this->mid = \Common\Org\Cookie::get('mid');
		$this->path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		if( !file_exists($this->path) ) mkdir($this->path);
		if( !$this->jid || !$this->mid ) $this->error('请先登录管理中心', U('/Public/login@ce', '', true, true));
		
		//判断此账号是商家还是门店
		$this->tsid = \Common\Org\Cookie::get( C('USER_COOKIE_SID') );
		$this->type = \Common\Org\Cookie::get( C('USER_COOKIE_TPE') );
		$this->type = $this->tsid==0 && $this->type==1 ? 1 : 2;	

		if( $this->type==1 ) { 
			$_splist=$this->sidlist; $shopinfo=array_shift($_splist);
			if( isset($shopinfo['sid']) && is_numeric($shopinfo['sid']) && $this->tsid != $shopinfo['sid'] ) {
				$this->tsid = $shopinfo['sid'];
			}
		}
		$merchant = M('merchant')->where(array('jid'=>$this->jid))->find();
		$this->assign('modules', D('MerchantModule')->getMerchantModule($merchant['modules']));
		//print_r(D('MerchantModule')->getMerchantModule($merchant['modules']));

		$this->assign('mnickname', $merchant['mnickname']);
		if( $this->type == 2 ) {
			$this->assign('sname', M('shop')->where(array('sid'=>$this->tsid, 'jid'=>$this->jid))->getField('sname'));
		}
		$this->assign('CurrentUrl', CONTROLLER_NAME.ACTION_NAME);	
		$this->assign('type', $this->type);	
		$this->assign('jid', $this->jid);
		
		//第一步判断绑定手机是否已验证
		$md_arr = array('Index.bindtel','Index.sendsms');
		if( ( empty($merchant['mlptel']) || $merchant['mlptel_verified'] == 0) && !in_array(CONTROLLER_NAME.'.'.ACTION_NAME,$md_arr)){
			$this->redirect('Index/bindtel', array('guide'=>1));
		}
		
		//第二步判断密码是否已修改
		$md_arr = array('Index.editpwd','Index.bindtel','Index.sendsms');
		if($merchant['mlptel_verified'] == 1){
			unset($md_arr[1]);
		}
		if( $merchant['pwd_changed'] == 0 && !in_array(CONTROLLER_NAME.'.'.ACTION_NAME,$md_arr)){
			$this->redirect('Index/editpwd', array('guide'=>1));
		}
		
		//第三步查询是否有分店
		$md_arr = array(
				'Index.editpwd',
				'Index.bindtel',
				'Index.sendsms',
				'Shop.addShop',
		);
		if($merchant['mlptel_verified'] == 1){
			unset($md_arr[1]);
		}
		if($merchant['pwd_changed'] == 1){
			unset($md_arr[0]);
		}
		$shop_count = M('shop')->where(array('jid'=>$this->jid))->count();
		if($shop_count == 0 && !in_array(CONTROLLER_NAME.'.'.ACTION_NAME,$md_arr)){
			$this->redirect('Shop/addShop', array('guide'=>1));
		}
		$this->assign('countNotice', $this->countNotice($merchant));
	}

	//统计消息
	public function countNotice($merchant=array()){
		if(!$merchant['mid'] && !$this->mid)return false;
		$where = $map = $nids = $mids = array();
	    $where['tmid']  = array('in',array('-1','0',$this->mid,0-$merchant['mid']));
		$notice = M('Notice')->field('nid,fmid,tmid')->where($where)->select();
		if($notice)foreach($notice as $value){
				  $nids[] =  $value['nid'];
				  $mids[] =  $value['fmid'];
	    }
	  if($nids){
		  $map['nid'] = array('in',$nids);
		  $map['mid'] = $this->mid;
		  $viewnids = M('NoticeData')->field('nid,mid')->where($map)->select();
		  $ids = array_column($viewnids,'mid','nid');
	  }
	 $mumstr = 0;
	 if($notice)foreach($notice as $value){
		if(!$ids[$value['nid']]){$mumstr+=1;}
	 }
	 return $mumstr;
  }


}