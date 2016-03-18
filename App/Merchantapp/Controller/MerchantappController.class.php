<?php
namespace Merchantapp\Controller;
use Think\Controller;

class MerchantappController extends Controller {
    protected $jid, $mid, $path, $type, $sid, $sidlist=array();

	public function _initialize() {
		$this->jid = \Common\Org\Cookie::get(C('USER_COOKIE_JID'));	
		$this->mid = \Common\Org\Cookie::get('mid');

		if(stristr(I('server.HTTP_USER_AGENT'), 'iPhone')){
			$this->assign('runmode', 'macapp');
		}

		if( !$this->jid || !$this->mid ) redirect('/Public/login');

		//判断此账号是商家还是门店
		$this->sid  = \Common\Org\Cookie::get( C('USER_COOKIE_SID') );
		$this->type = \Common\Org\Cookie::get( C('USER_COOKIE_TPE') );
		$this->type = $this->sid==0 && $this->type==1 ? 1 : 2;	
		
		if( $this->type==1 ) { //获取所有门店的SID
			$sidlist = array();
			foreach(M('shop')->where('jid='.$this->jid." and status='1'")->field('sid')->select() as $sid) {
				if( $sid ) $sidlist[] = $sid['sid'];
			}
			$this->sidlist = $sidlist;
		} else {
			$this->sidlist[] = $this->sid;
		}

		$user_agent = I('server.HTTP_USER_AGENT');
		if(stristr($user_agent,'iPhone') || stristr($user_agent, 'ipa')){
			$this->assign('msystem','ios');
		}else{
			$this->assign('msystem','android');
		}


		$this->assign('type', $this->type);	
		$this->assign('jid', $this->jid);
		$this->assign('sid', $this->sid);
	}
}