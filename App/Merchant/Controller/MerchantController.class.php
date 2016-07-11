<?php
namespace Merchant\Controller;
use Common\Controller\ManagerController;

class MerchantController extends ManagerController {
    protected $jid, $mid, $path, $type, $tsid, $sidlist=array(),$role,$shift;

	public function _initialize() {
		//parent::_initialize();
		$this->jid = \Common\Org\Cookie::get(C('USER_COOKIE_JID'));	
		$this->mid = \Common\Org\Cookie::get('mid');
		$this->path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		if( !file_exists($this->path) ) mkdir($this->path);
		if(strtolower(CONTROLLER_NAME) != 'scene'){
			if(!$this->jid || !$this->mid) 
			//$this->error('请先登录管理中心', U('/Public/login@sj', '', true, true));
			$this->redirect('/Public/login@sj');
		}

		//这里做一个默认配置，如果商家没有设置配置，则调用此文件
		$settingpath = APP_DIR.'/Public/Data/'.$this->jid.'/setting.conf';
		if( !file_exists($settingpath) ) {
			$setting['consume_type'] = 3;
			$setting['pay_type'] = 3;
			$setting['consume_title_1'] = '店内消费';
			$setting['consume_title_2'] = '外送上门';
			file_put_contents($settingpath, serialize( $setting ));
		}
		
		//判断此账号是商家还是门店
		//$this->tsid = \Common\Org\Cookie::get( C('USER_COOKIE_SID') );
		//$this->type = \Common\Org\Cookie::get( C('USER_COOKIE_TPE') );
		//$this->type = $this->tsid==0 && $this->type==1 ? 1 : 2;	

		//if( $this->type==1 ) { 
			//$_splist=$this->sidlist; $shopinfo=array_shift($_splist);
			//( isset($shopinfo['sid']) && is_numeric($shopinfo['sid']) && $this->tsid != $shopinfo['sid'] ) {
				//$this->tsid = $shopinfo['sid'];
			//}
		//}
		$merchant = M('merchant')->where(array('jid'=>$this->jid))->find();
		
		$muser = M('merchant_user')->where(array('tmid'=>$this->mid))->find();
		$this->role = $muser['role'];
		$this->assign('role',$this->role);
		if($this->role == 1){
			$this->shift = 1;
		}else{
			$this->shift = $muser['shift'];
		}
		
		//$this->assign('modules', D('MerchantModule')->getMerchantModule($merchant['modules']));
		//print_r(D('MerchantModule')->getMerchantModule($merchant['modules']));

		$this->assign('mnickname', $merchant['mnickname']);
		\Common\Org\Cookie::set('mnickname', $merchant['mnickname']);
		
		//if( $this->type == 2 ) {
			//$this->assign('sname', M('shop')->where(array('sid'=>$this->tsid, 'jid'=>$this->jid))->getField('sname'));
		//}
		$this->assign('CurrentUrl', CONTROLLER_NAME.ACTION_NAME);	
		//$this->assign('type', $this->type);	
		$this->assign('jid', $this->jid);
	/*	
		//第一步判断绑定手机是否已验证
		$md_arr = array('Index.bindtel','Index.sendsms');
		if( ( empty($merchant['mlptel']) || $merchant['mlptel_verified'] == 0) && !in_array(CONTROLLER_NAME.'.'.ACTION_NAME,$md_arr) && strtolower(CONTROLLER_NAME) != 'scene'){
			$this->redirect('Index/bindtel', array('guide'=>1));
		}
		
		//第二步判断密码是否已修改
		
		$md_arr = array('Index.editpwd','Index.bindtel','Index.sendsms');
		if($merchant['mlptel_verified'] == 1){
			unset($md_arr[1]);
		}
		if( $merchant['pwd_changed'] == 0 && !in_array(CONTROLLER_NAME.'.'.ACTION_NAME,$md_arr) && strtolower(CONTROLLER_NAME) != 'scene'){
			$this->redirect('Index/editpwd', array('guide'=>1));
		}
		
		//第三步查询是否有分店
		$md_arr = array(
				'Index.editpwd',
				'Index.bindtel',
				'Index.sendsms',
				'Shop.addShop',
				'Shop.publicGetaddress',
		);
		if($merchant['mlptel_verified'] == 1){
			unset($md_arr[1]);
		}
		if($merchant['pwd_changed'] == 1){
			unset($md_arr[0]);
		}
		$shop_count = M('shop')->where(array('jid'=>$this->jid))->count();
		if($shop_count == 0 && !in_array(CONTROLLER_NAME.'.'.ACTION_NAME,$md_arr) && strtolower(CONTROLLER_NAME) != 'scene'){
			$this->redirect('Shop/addShop', array('guide'=>1));
		}*/
		
		
		$tt = D('Auth')->getUserMenu($this->mid);
		$t0 = array_keys($tt);
		$tm = I('menucode');
		$menucode = empty($tm) ? session('menucode') : $tm;
		$menucode = empty($menucode) ? $t0[0] : $menucode;
		session('menucode',$menucode);
		$nextmenu = $tt[$menucode]['next'];
		//洗衣信息模块
		if ($tt[$menucode]['code'] == 'message' && $this->jid == 438){
			$nu = C('MESSAGE_B');
			$nextmenu = $nu['next'];
		}
		
		$this->assign('menucode',$menucode);
		$this->assign('nextmenu',$nextmenu);
		$this->assign('top_menu',$tt);
		
		$mtapp2 = M('merchantApp')->where(array('jid'=>$this->jid))->find();
		$this->assign('mtapp2', $mtapp2);
		$this->assign('countNotice', $this->countNotice($merchant));
		$this->assign('orderCount', $this->numOrder());
		$this->assign('reserveCount', $this->numReserve());
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

	//统计订单消息
	public function numOrder(){
    $where = $this->type == 1 ? array('o_jid'=>$this->jid) : array('o_sid'=>$this->tsid); 
	$orderCount=M('order')->where(array_merge($where, array('o_dstatus'=>1,'o_gtype'=>'Choose') ))->count();
	 return $orderCount;
  }
	//统计预定消息
	public function numReserve(){
    $where = $this->type == 1 ? array('o_jid'=>$this->jid) : array('o_sid'=>$this->tsid); 
	$reserveCount=M('order')->where(array_merge($where, array('o_dstatus'=>1,'o_gtype'=>'Seat') ))->count();
	 return $reserveCount;
  }
}