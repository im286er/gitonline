<?php
namespace Merchantapp\Controller;

class IndexController extends MerchantappController {
	public function index() {
		$serverid = \Common\Org\Cookie::get('serverid');
		$this->assign('serverid', $serverid );
		$this->assign('title', '帝鼠os商户版');	
		$this->assign('jid',$this->jid);			
		$this->display();
	}


	//设置商家信息
	public function setinfo() {
		//获取APP ICON
		$this->assign('appicon', M('merchantApp')->where("jid=".$this->jid)->getField('applogo'));
		
		//获取门店名称
		if( $this->type==1 ) {
			$this->assign('title', M('merchant')->where("jid=".$this->jid)->getField('mnickname'));			
		} else {
			$this->assign('title', M('shop')->where("sid=".$this->sid)->getField('sname'));					
		}

		if( $this->type==1 ) {
				$phone = M('merchant')->where("jid=".$this->jid)->getField('mlptel');			
		} else {
				$phone = M('shop')->where("sid=".$this->sid)->getField('scontactstel');					
		}
		$this->assign("phone", $phone);

		
		//获取账户信息
		$this->assign('member', M('member')->where("mid=".$this->mid)->find());
		$this->display();	
	}
	
	//绑定手机
	public function bindphone() {
		if( IS_POST ) {
			$phone=I('post.tel'); $code=I('post.code');
			if( session('SendSms')!=$code ) exit("2");
			
			if( preg_match("/^13[0-9]{9}$|^15[0-9]{9}$|^18[0-9]{9}$|^147[0-9]{9}$/", $phone) ) {
				if( $this->type==1 ) {
					M('merchant')->where("jid=".$this->jid)->setField(array('mlptel'=>$phone,'mlptel_verified'=>1));			
				} else {
					M('shop')->where("sid=".$this->sid)->setField(array('scontactstel'=>$phone));					
				}
				$s = M("member")->where("mid=".$this->mid)->setField('mphone', $phone);	
			}
			exit( $s ? "1" : "0" );			
		} else {
			//获取门店名称
			if( $this->type==1 ) {
				$phone = M('merchant')->where("jid=".$this->jid)->getField('mlptel');			
			} else {
				$phone = M('shop')->where("sid=".$this->sid)->getField('scontactstel');					
			}
			$this->assign("phone", $phone);
			$template = $phone ? "Index_bindphonet" : "Index_bindphoneo";
			if( isset($_GET['s']) && intval($_GET['s'])==1 ) $template = 'Index_bindphoneo';
			$this->display($template);	
		}
	}
	
	//发送短信验证码
	public function pushmsg() {
		$phone = I('get.tel', '');
		if( preg_match("/^13[0-9]{9}$|^15[0-9]{9}$|^18[0-9]{9}$|^147[0-9]{9}$/", $phone) ) {
			$content = \Org\Util\String::randString(4, 1);
			session('SendSms', $content); exit(sendmsg( $phone, $content) ? "1" : "0");
		}
		exit("0");	
	}
	
	//验证原手机号输入是否正确
	public function checkPhone() {
		$new_phone = I("get.tel");
		$old_phone = M("member")->where("mid=".$this->mid)->getField("mphone");
		exit( $new_phone==$old_phone ? "1" : "0"); 	
	}
	
	//验证原来密码
	public function changepwd() {
		if( IS_POST ) {
			$code=I('post.code'); if( session('SendSms')!=$code ) exit("2");
			$old_password = M('member')->where('mid='.$this->mid)->getField('mpwd');
			if( $old_password != md5(md5(I('post.pwd'))) ) exit("3");
			exit("1");			
		} else {
			if( $this->type==1 ) {
				$phone = M('merchant')->where("jid=".$this->jid)->getField('mlptel');			
			} else {
				$phone = M('shop')->where("sid=".$this->sid)->getField('scontactstel');					
			}
			$this->assign('phone', $phone);
			$this->display();	
		}
	}
	
	//修改密码
	public function changepwdt() {
		if( IS_POST ) {
			$newpassword = I('post.newpassword');
			$compassword = I('post.compassword');
			
			if( !$newpassword || !$compassword || $newpassword!=$compassword ) exit("1");
			exit( M("member")->where('mid='.$this->mid)->setField("mpwd", md5(md5($newpassword))) ? "2" : "3" );
		} else {			
			$this->display();
		}
	}
	
}