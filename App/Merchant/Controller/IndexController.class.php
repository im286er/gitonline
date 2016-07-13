<?php
namespace Merchant\Controller;

class IndexController extends MerchantController {

	//管理中心首页
	public function index() {
		$stime = date('Y-m-d');
		$etime = date('Y-m-d',strtotime('+1 day'));
		$sid = I('sid',0);
		$where = $sid > 0 ? array('o_sid'=>$sid) : array('o_jid'=>$this->jid);
		$this->assign('corder', M('order')->where(array_merge($where, array('o_dstatus'=>array('egt', '1'), 'o_dstime'=>array(array('egt', $stime), array('elt', $etime), 'and'))))->count());
		$this->assign('iorder', M('order')->where(array_merge($where, array('o_pstatus'=>'1', 'o_pstime'=>array(array('egt', $stime), array('elt', $etime), 'and'))))->sum('o_price'));
		$this->assign('sumprice', M('order')->where(array_merge($where, array('o_pstatus'=>array('egt', '1')) ))->sum('o_price'));
		$sbwb = 0;
		if($sid > 0){
			$sinfo = M('shop')->where(array('sid'=>$sid))->find();
			if($sinfo['bb_stime'] && $sinfo['bb_etime'] && $sinfo['wb_stime'] && $sinfo['wb_etime']){
				$this->assign('bprice', M('order')->where(array('o_sid'=>$sid,'o_pstatus'=>array('egt', '1'),'o_dstime'=>array('exp'," >='".date("Y-m-d")."' and DATE_FORMAT(o_dstime,'%H.%i') between ".$sinfo['bb_stime'].' and '.$sinfo['bb_etime'])) )->sum('o_price'));
				$this->assign('wprice', M('order')->where(array('o_sid'=>$sid,'o_pstatus'=>array('egt', '1'),'o_dstime'=>array('exp'," >='".date("Y-m-d")."' and DATE_FORMAT(o_dstime,'%H.%i') between ".$sinfo['wb_stime'].' and '.$sinfo['wb_etime'])) )->sum('o_price'));
				$sbwb = 1;
			}
		}
		$this->assign('sbwb',$sbwb);
		
		$merchant = M('merchant')->where(array('jid'=>$this->jid))->find();
		$member = M('member')->where(array('mid'=>$this->mid))->find();
		$this->assign('merchant', $merchant);
		$this->assign('member', $member);
		$this->assign('mnickname', $merchant['mnickname']);
		$shops = D('auth')->getAuthShops($this->mid);
		$this->assign('shops', $shops);
		
		$is_add = 0; 
		if($this->role == 1){
			$is_add = 1;
		}else{
			$my_auth = M('merchant_user')->where(array('tmid'=>$this->mid))->find();
			$my_auth2 = unserialize($my_auth['auth']);
			if(in_array('ShopaddShop',$my_auth2['next'])){
				$is_add = 1;
			}
		}
		
		$this->assign('is_add',$is_add);
		
		$this->display();
	}


	//消息处理
	public function maessage(){
		$where = array();
		$this->type == 1 ? $where['o_jid']=$this->jid : $where['o_sid']=$this->tsid;
		$where['o_dstatus'] = '1'; 
		$order = M('Order')->where($where)->getField('o_gtype');
		if($order)$orders = array_count_values($order);
		print_r($orders);
	}

	//创建二维码
	public function createQrCode($mtapp,$size=10,$domain=true){
		$qrcodefile = 'qrcodehome'.$size.'.png';
		//if(!file_exists($this->path.$qrcodefile)) {
			vendor("phpqrcode.phpqrcode");
			$QRcode = new \QRcode();
			//$qrcodefile = $qrcodefile;
			$codetxt = U('Index/index@yd',array('jid'=>$this->jid));
			
			$QRcode::png($codetxt, $this->path.$qrcodefile, 'H', $size);
			$applogo = (APP_DIR.$mtapp['applogo']);
			$QR = $this->path.$qrcodefile;
			if(file_exists($applogo)) {
				$QR = imagecreatefromstring(file_get_contents($QR)); 
				$applogo = imagecreatefromstring(file_get_contents($applogo)); 
				$QR_width = imagesx($QR); 
				$QR_height = imagesy($QR); 
				$logo_width = imagesx($applogo); 
				$logo_height = imagesy($applogo); 
				$logo_qr_width = $QR_width / 5; 
				$scale = $logo_width / $logo_qr_width; 
				$logo_qr_height = $logo_height / $scale; 
				$from_width = ($QR_width - $logo_qr_width) / 2; 
				imagecopyresampled($QR,$applogo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height); 
			} 
			imagepng($QR,$this->path.$qrcodefile); 
		//}
		$file = '/Public/Data/'.$this->jid.'/'.$qrcodefile;
		if($domain)$file ='http://'.I('server.HTTP_HOST').$file;
		return $file;
	}

	//下载图片二维码
	public function wxdown(){
		$size = I('get.size','','intval');
		$mtapp = M('merchantApp')->where(array('jid'=>$this->jid))->find();
		$file = $this->createQrCode($mtapp,$size,false);
		$filename = APP_DIR.$file;
		if (file_exists($filename)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($filename));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filename));
			ob_clean();
			flush();
			readfile($filename);
			exit;
		} 
	}


	//设置LOGO
    public function setlogo() {
		if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			if( M('merchantApp')->where(array('jid'=>$this->jid))->save($_POST['info']) ) {
				$this->success('修改成功', U('/Index/mains', '', true));
			} else { $this->error('操作失败'); }			
		} else {
			$this->assign('data', M('merchantApp')->where(array('jid'=>$this->jid))->field('appname,applogo')->find());
			$this->display();
		}
    }
	
	//查看账户
	public function userinfo() {
		$data = M('merchant')->alias('AS m')->where(array('m.jid'=>$this->jid))
				->join('__MERCHANT_APP__ AS p ON m.jid=p.jid')
				->join('__VOCATION__ AS v on m.vid=v.v_id', 'left')
				->field('m.mnickname,m.mabbreviation,p.endmakedate,p.applogo,p.appzt,p.appjs,p.appdz,v.v_title,m.mcity')->find();

		if( !is_array($data) || empty($data) ) $data = array();
		$data['mcity'] = get_address_byid($data['mcity']);
		if( $this->type==2 || count($this->sidlist)==1 ) {
			$data['mcity'] = M('shop')->where(array('sid'=>$this->tsid))->getField('saddress');
			$this->assign('address', 1);
		}
		$this->assign('member', M('member')->where(array('mid'=>$this->mid))->field('mname,mbdzh')->find());	
		$this->assign('data', $data);
    	$this->display();
    }
	
	//微信关注
	public function weixin() {
		if( IS_POST ) {
			$ModuleName = I('post.ModuleName', '');
			if( $ModuleName ) { file_put_contents($this->path.'WeiXinModuleName.php', $ModuleName); }
			$ModuleIcon = I('post.ModuleIcon', '');
			if( $ModuleIcon ) { file_put_contents($this->path.'WeiXinModuleIcon.php', $ModuleIcon); }
			$ModuleLink = I('post.ModuleLink', '');
			if( $ModuleLink ) { file_put_contents($this->path.'WeiXinModuleLink.php', $ModuleLink); }
		} else {
			file_exists($this->path.'WeiXinModuleName.php') && $modulename=file_get_contents($this->path.'WeiXinModuleName.php');
			file_exists($this->path.'WeiXinModuleIcon.php') && $moduleicon=file_get_contents($this->path.'WeiXinModuleIcon.php');
			file_exists($this->path.'WeiXinModuleLink.php') && $modulelink=file_get_contents($this->path.'WeiXinModuleLink.php');
			$this->assign('modulename', $modulename ? $modulename : '');
			$this->assign('moduleicon', $moduleicon ? $moduleicon : '');
			$this->assign('modulelink', $modulelink ? $modulelink : '');
			$this->display();	
		}
	}
	
	//微商城
	public function weishop() {
		if( IS_POST ) {
			if( $_POST['type']=='w' ) {
				$ModuleName = I('post.ModuleName', '');
				if( $ModuleName ) { file_put_contents($this->path.'WshopModuleName.php', $ModuleName); }
				$ModuleLink = I('post.ModuleLink', '');
				if( $ModuleLink ) { file_put_contents($this->path.'WshopModuleLink.php', $ModuleLink); }	
				$ModuleIcon = I('post.ModuleIcon', '');
				if( $ModuleIcon ) { file_put_contents($this->path.'WshopModuleIcon.php', $ModuleIcon); }
			} else if( $_POST['type']=='b' ) {
				$ModuleName = I('post.ModuleName', '');
				if( $ModuleName ) { file_put_contents($this->path.'BoutiqueModuleName.php', $ModuleName); }
				$ModuleIcon = I('post.ModuleIcon', '');
				if( $ModuleIcon ) { file_put_contents($this->path.'BoutiqueModuleIcon.php', $ModuleIcon); }
			}
		} else {
			file_exists($this->path.'WshopModuleName.php') && $modulename=file_get_contents($this->path.'WshopModuleName.php');
			file_exists($this->path.'WshopModuleLink.php') && $modulelink=file_get_contents($this->path.'WshopModuleLink.php');
			file_exists($this->path.'WshopModuleIcon.php') && $moduleicon=file_get_contents($this->path.'WshopModuleIcon.php');
			$this->assign('modulename', $modulename ? $modulename : '');
			$this->assign('modulelink', $modulelink ? $modulelink : '');
			$this->assign('moduleicon', $moduleicon ? $moduleicon : '');

			file_exists($this->path.'BoutiqueModuleName.php') && $modulename_b=file_get_contents($this->path.'BoutiqueModuleName.php');
			file_exists($this->path.'BoutiqueModuleIcon.php') && $moduleicon_b=file_get_contents($this->path.'BoutiqueModuleIcon.php');
			$this->assign('modulename_b', $modulename_b ? $modulename_b : '');
			$this->assign('moduleicon_b', $moduleicon_b ? $moduleicon_b : '');
			$this->assign('modulelink_b', 'http://yd.dishuos.com/Boutique/index/jid/'.$this->jid.'.html');
			$this->assign('CurrentUrl', 'Salesgoods');
			$this->display();	
		}	
	}
	
	//修改密码
	public function editpwd() {
		if( IS_POST ) {
			$password=I('post.password', ''); $smscode=I('post.smscode');
			if( !$password || !$smscode ) { $this->error('请把信息填写完整'); }
			if( session('SendSms') != $smscode ) { $this->error('验证码输入错误，请重新修改'); }
			$member = M('member')->where('mid='.$this->mid)->find();
			if( md5(md5($password)) == $member["mpwd"] ) { $this->error('新密码和旧密码不能相同'); }
			if( M('member')->where('mid='.$this->mid)->setField('mpwd', md5(md5($password))) !== false ) {	
				unset($_SESSION[C('USER_AUTH_KEY')], $_SESSION); session_destroy();
				\Common\Org\Cookie::delete(array('mid', C('USER_COOKIE_JID'), C('USER_COOKIE_SID'), C('USER_COOKIE_TPE')));
				$this->success('修改成功',U('/Public/login@sj', '', true, true));
			} else { $this->error('修改失败，请重新修改'); }			
		} else {
			$tel = M('merchant_user')->where(array('tmid'=>$this->mid))->getField('tphone');
			
			$this->assign('linkmobile', $tel);
			
			$this->assign('guide',I('guide'));
			$this->display();
		}	
	}
	
	//发送验证码 
	public function sendsms() {
		$tpl = I('get.val', ''); if( !$tpl ) exit("0");
		$content = \Org\Util\String::randString(4, 1);
		session('SendSms', $content);
		session('SendSmsTel', $tpl);
		$merchant = M('merchant')->where(array('jid'=>$this->jid))->find();
		$foottxt  = '【'.$merchant['mabbreviation'].'】';
		exit(sendmsg( $tpl, $content,$foottxt,false) ? "1" : "0");		
	}
	
	
	//修改表中的字段
	public function editFie() {
		$tableName=I('post.table'); $setField=I('post.sfie'); $whereField=I('post.wfie'); $data=I('post.data');
		if( !$tableName || !$setField || !$whereField ) exit('0');
		
		if($setField=='appjs') $data = urldecode( $data ); 
		if( $whereField == 'jid' ) {
			$result = M($tableName)->where(array('jid'=>$this->jid))->setField($setField, $data);						
		} else if( $whereField=='sid' ) {
			$result = M($tableName)->where(array('sid'=>$this->tsid))->setField($setField, $data);	
		}
		echo $result?'1':'0';
	}
	
	//验证手机号
	public function bindtel() {
		if( IS_POST ) {
			$mlptel=I('post.mlptel');
			$smscode=I('post.smscode');
			if( !$mlptel || !$smscode ) { $this->error('请把信息填写完整'); }
			if( session('SendSms') != $smscode ||  session('SendSmsTel') != $mlptel) { $this->error('验证码输入错误，请重新修改'); }
				
			M('member')->where('mid='.$this->mid)->setField(array('mphone'=>$mlptel));
			M('merchant_user')->where('tmid='.$this->mid)->setField(array('tphone'=>$mlptel));
			
			session('SendSms', null);
			session('SendSmsTel', null);
			$this->success('验证成功');
			
		} else {
			$tel = M('merchant_user')->where(array('tmid'=>$this->mid))->getField('tphone');
			$this->assign('guide',I('guide'));
			$this->assign('tel',$tel);
			$this->display();
		}
	}
}