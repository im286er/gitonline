<?php
namespace Merchantapp\Controller;
use Think\Controller;

class PublicController extends Controller {
	public $appid = 2;//在app应用下载里面是Id为2

    public function login() {
		if(stristr(I('server.HTTP_USER_AGENT'), 'iPhone')){
			$this->assign('runmode', 'macapp');
		}
		$appinfo = M('App')->find($this->appid);//查找商家app
		$this->assign('appinfo', $appinfo);
		if( !isset($_SESSION[C('USER_AUTH_KEY')]) || !\Common\Org\Cookie::get('mid') ) {
			$this->assign('username', \Common\Org\Cookie::get('username'));
			$this->assign('password', \Common\Org\Cookie::get('password'));
			$this->display('Index:login');
		} else {
			$this->redirect('Index/index');
		}
    }

    public function checklogin() {
		$username = I("post.username");
		if(stristr($username,':')){
			$mname = explode(':',$username);
			$username = $mname[0];
			$serverid = intval($mname[1]);
		}
		$member = M("member")->alias('AS m')->where(array("m.mname"=>$username, "m.mtype"=>2))
							 ->join('__MERCHANT_USER__ AS u ON m.mid=u.tmid')
							 ->join('__MERCHANT__ AS j ON j.jid=u.tjid')
							 ->field('m.mid,m.mname,m.mpwd,m.mstatus,j.jid,u.tsid,u.type')->find();
		
		
		if(!$member['tsid'] && $serverid)exit("2");//如果是商家登录，输入了客服编号，直接返回密码错误
		if( is_array($member) && !empty($member) && $member['mpwd'] == md5(md5(I("post.password"))) ) {
			if( $member['mstatus'] != 1 ) exit("2");
			if($serverid && $member['tsid']){
				$servernum = M("shop")->where(array('sid'=>$member['tsid']))->getField('servernum');
				if($servernum > 0 && $serverid > 0 && $servernum >= $serverid){
					\Common\Org\Cookie::set('serverid', $serverid, $expire);
				}else exit("1");
			}	
			$_SESSION[C('USER_AUTH_KEY')] = $member['mid'];
			$expire = 3600 * 24 * 7;
			\Common\Org\Cookie::set('mid', $member['mid'], $expire);
			\Common\Org\Cookie::set(C('USER_COOKIE_JID'), $member['jid'], $expire);
			\Common\Org\Cookie::set(C('USER_COOKIE_SID'), $member['tsid'], $expire);
			\Common\Org\Cookie::set(C('USER_COOKIE_TPE'), $member['type'], $expire);

			//把用户名和密码保存 7 天
			\Common\Org\Cookie::set('username', $username, $expire);
			\Common\Org\Cookie::set('password', I('post.password'), $expire);
			
			exit("3");
		}
		exit("1");
    }

	//退出管理中心
    public function logout(){
    	if(isset($_SESSION[C('USER_AUTH_KEY')]) && \Common\Org\Cookie::get('mid')) {
    		//解除当前账户的cid绑定
    		D('Tsbind')->unbind();
    		
			unset($_SESSION[C('USER_AUTH_KEY')], $_SESSION); session_destroy();
			\Common\Org\Cookie::delete(array('mid', C('USER_COOKIE_JID'), C('USER_COOKIE_SID'), C('USER_COOKIE_TPE'),'serverid'));
		} 
		redirect('/Index/login');
    }

	public function is_update(){
		if(IS_POST){
			sleep(1);
			$appversions = I('post.appversions');//当前版本
			$appinfo = M('App')->find($this->appid);//查找商家app
			if(!$appversions)$this->ajaxReturn(array('status'=>'0' ,'msg'=>'当前版本为最新版本!'));
			if($appinfo['versions'] > $appversions){
				$msg = '有最新版本 v'.$appinfo['versions'].'，现在需要更新吗？';
				$this->ajaxReturn(array('status'=>'1' ,'msg'=>$msg,'url'=>U('App/down@yd',array('jid'=>70,'appid'=>$appinfo['id'])) ));
			}else{
				$this->ajaxReturn(array('status'=>'0' ,'msg'=>'当前版本为最新版本!'));
			}
		}
		exit;
	}
	
	//保存cid与登录账号的绑定关系
	public function checkbind(){
		//$clientid = I('cid');
		//if($clientid){
			//D('Tsbind')->unbindBycid($clientid);
		//}
		//exit("1");
		
		$jid  = \Common\Org\Cookie::get(C('USER_COOKIE_JID'));
		$sid  = \Common\Org\Cookie::get( C('USER_COOKIE_SID') );
		$type = \Common\Org\Cookie::get( C('USER_COOKIE_TPE') );
		
		$clientid = I('cid');
		if($clientid){
			D('Tsbind')->bind($jid,$sid,$type,$clientid);
		}
		exit("1");
	}

}