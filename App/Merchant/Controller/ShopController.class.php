<?php
namespace Merchant\Controller;

class ShopController extends MerchantController {
	private $_AddressList = array();
	//分店列表
	public function index() {
		//if( $this->type != 1 ) E('你无权查看当前页面');
		//if( $this->type == 1 ) {
			//file_exists($this->path.'ShopName.php') && $modulename=file_get_contents($this->path.'ShopName.php');
			//file_exists($this->path.'ShopIcon.php') && $moduleicon=file_get_contents($this->path.'ShopIcon.php');
			//$this->assign('modulename', $modulename ? $modulename : '');
			//$this->assign('moduleicon', $moduleicon ? $moduleicon : '');
			//$this->assign('modulelink', 'http://yd.dishuos.com/Shop/index/mod/Choose/jid/'.$this->jid.'.html');
		//}
		//$where = array('jid'=>$this->jid, 'status'=>'1');
		$where = D('Auth')->getAuthWhere($this->mid);
		$page = new \Common\Org\Page(M('shop')->where($where)->count(), 9);
		$this->assign('shopsList', M('shop')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->display();
	}	
	
	//添加分店
	public function addShop() {
		//if( $this->type != 1 ) E('你无权查看当前页面');
		
		if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$_POST['info']['jid'] = $this->jid;
			$_POST['info']['img_url'] = '/Public/Merchant/images/topbg.png';
			$_POST['info']['theme'] = 'new2';
			
			$coordinates = explode(",",$_POST['coordinates']);
			$_POST['info']['lng'] = $coordinates[0];
			$_POST['info']['lat'] = $coordinates[1];
					
		    $sid = D('Shop')->insert($_POST['info']);
		    if($sid){
		    	D('service')->insertShopData($this->jid,$sid);
		    }
		    $this->success('添加成功', U('/Shop/editTable/sid/'.$sid.'.html', '', true));
		
		} else {
			//$this->_AddressList = F('AddressList');
			//if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $this->_AddressList);
			//$this->assign('addressList', $this->_AddressList);
			$this->assign('guide',I('guide'));
			$this->display();
		}	
	}
	
	//删除分店
	public function delShop() {
		//if( $this->type != 1 ) E('你无权查看当前页面');
		
		$sid = I('get.sid', ''); if(!$sid) E('你无权对此进行操作');
		$tmid=M("merchant_user")->where("tsid=$sid")->find();
		$mid=$tmid['tmid'];
		if(M('shop')->where(array('sid'=>$sid, 'jid'=>$this->jid))->setField('status', '0') !== false &&M('member')->where(array('mid'=>$mid))->setField('mstatus', '0') !== false) {
			$this->success('删除成功', U('/Shop/index', '', true));
		} else { $this->error( '删除失败' );  } 
	}
	
	//隐藏分店
	public function hidShop() {
		//if( $this->type != 1 ) E('你无权查看当前页面');
	
		$sid = I('get.sid', '');
		$is_show = I('get.is_show', 0);
		if(!$sid){
			E('你无权对此进行操作');
		}
		if(M('shop')->where(array('sid'=>$sid, 'jid'=>$this->jid))->setField('is_show', $is_show) !== false) {
			$this->success('设置成功', U('/Shop/index', '', true));
		} else { $this->error( '设置失败' );  }
	}
	
	//修改分店
	public function editShop() {
		//if( $this->type != 1 ) E('你无权查看当前页面');
		
		if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$_POST['info']['jid'] = $this->jid;
				
			$coordinates = explode(",",$_POST['coordinates']);
			$_POST['info']['lng'] = $coordinates[0];
			$_POST['info']['lat'] = $coordinates[1];
		
			$a = D('Shop')->update($_POST['info']);
			
			$this->success('修改成功', U('/Shop/index', '', true));
			
		} else {
			$shop = M('shop')->where(array('sid'=>I('get.sid', 0, 'intval'), 'jid'=>$this->jid))->find();
			if(!is_array($shop) || empty($shop)) { E('你无权操作此页面'); }
			
			//$this->_AddressList = F('AddressList');
			//if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $this->_AddressList);
			//$this->assign('addressList', $this->_AddressList);
			
			//if($shop['province']){
				//$address1 = M('address')->where(array('apid'=>$shop['province']))->select();
			//}
			
			//if($shop['province'] != $shop['city'] && $shop['city']){
				//$address2 = M('address')->where(array('apid'=>$shop['city']))->select();
			//}
			//$this->assign('address1', $address1);
			//$this->assign('address2', $address2);
			$this->assign('shop', $shop);
			$this->display();				
		}		
	}

	//查看详情
	public function infoShop() {
		//if( $this->type != 1 ) E('你无权查看当前页面');
		
		$shop = M('shop')->where(array('sid'=>I('get.sid', 0, 'intval'), 'jid'=>$this->jid))->find();
		if(!is_array($shop) || empty($shop)) { E('你无权操作此页面'); }
		//$merchantuser = M('MerchantUser')->where(array('type'=>2,'tsid'=>$shop['sid'], 'tjid'=>$this->jid))->find();
		//if($merchantuser['tmid'])$member = M('Member')->where(array('mid'=>$merchantuser['tmid']))->find();
		//print_r($member);
		//$this->assign('member', $member);
		$this->assign('shop', $shop);
		$this->display();
	} 
	
	//设置分店模块ICON
	public function resetModuleInfo() {
		$ModuleName = I('post.ModuleName', '');
		$ModuleIcon = I('post.ModuleIcon', '');

		if( $ModuleName ) {
			$s=file_put_contents($this->path.'ShopName.php', $ModuleName);
		}
		if( $ModuleIcon ) {
			$s=file_put_contents($this->path.'ShopIcon.php', $ModuleIcon);
		}
		exit( $s ? '1' : '0' );
	}

	//设置分店模块ICON
	public function resetPass() {
		//if( $this->type != 1 ) E('你无权查看当前页面');
		$sid = I('post.sid', '');
		$mid = I('post.mid', '');
		if(!$sid)exit('0');
		$merchantuser = M('MerchantUser')->where(array('type'=>2,'tsid'=>$sid, 'tjid'=>$this->jid))->find();
		if($merchantuser['tmid'] && $merchantuser['tmid'] == $mid){
			$result = M('Member')->where(array('mid'=>$merchantuser['tmid']))->setField('mpwd', md5(md5('111111')));	
		}
		exit( $result ? '1' : '0' );
	}
	
	//ajax获取市级地区列表
	public function publicGetaddress( $pid=0 ) {
		$this->_AddressList = F('AddressList');
		if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $this->_AddressList);
		$str = '';
		foreach($this->_AddressList as $address) {
			if($address['apid'] == $pid) $str .= '<option value="'.$address['aid'].'">'.$address['aname'].'</option>';
		}
		exit($str);
	}
	
	public function cx(){
		$root = I('root');
		$ext  = I('ext');
		$url = 'http://www.zj01.com/zj01ck.asp?root='.$root.'&ext='.$ext;
		$r = file_get_contents($url);
		$rr = explode(',', $r);
		if($rr[1] == '100'){
			exit('1');
		}elseif($rr[1] == '-100'){
			exit('2');
		}else{
			exit('3');
		}
	}
	
	public function editTable(){
		$sid = I('sid');
		$tl = M('table')->where(array('sid'=>$sid))->select();
		$this->assign('table',$tl);
		$this->assign('sid',$sid);
		$this->display();
	}
	
	public function addTable(){
		if( IS_POST ) {
			$title = I('title');
			$number = I('number');
			$sid = I('sid');
			$info = array(
				'title' => I('title'),
				'number' => intval($number),
				'sid' => I('sid'),
			);
			if(M('table')->add($info) !== false){
				exit( JSON( array('msg'=>'') ) );
			}else{
				exit( JSON( array('msg'=>'添加失败') ) );
			}
		}else{
			$sid = I('sid');
			$this->assign('sid',$sid);
			$this->display();
		}
	}
	
	public function edTable(){
		if( IS_POST ) {
			$title = I('title');
			$number = I('number');
			$id = I('id');
			$info = array(
					'title' => I('title'),
					'number' => intval($number),
					'id' => I('id'),
			);
			if(M('table')->save($info) !== false){
				exit( JSON( array('msg'=>'') ) );
			}else{
				exit( JSON( array('msg'=>'修改失败') ) );
			}
		}else{
			$tid = I('tid');
			$tinfo = M('table')->where(array('id'=>$tid))->find();
			$this->assign('tinfo',$tinfo);
			$this->display();
		}
	}
	
	public function delTable(){
		$id = I('id');
		M('table')->where(array('id'=>$id))->delete();
		exit('1');
	}
	public function makeQrcode(){
		$id = I('id');
		$sid = I('sid');
		$size = 3;
		if($sid > 0){
			$qcUrl = U('Index/index@yd',array('jid'=>$this->jid,'sid'=>$sid));
		}else{
			$ss = M('table')->where(array('id'=>$id))->find();
			$qcUrl = U('Index/index@yd',array('jid'=>$this->jid,'sid'=>$ss['sid'],'table'=>$id));
		}
		vendor("phpqrcode.phpqrcode");
		$QRcode = new \QRcode();
		echo $QRcode::png($qcUrl, false, 'H', $size);
	}
	public function makeQrcodeDown(){
		$id = I('id');
		$sid = I('sid');
		$size = 3;
		if($sid > 0){
			$qcUrl = U('Index/index@yd',array('jid'=>$this->jid,'sid'=>$sid));
		}else{
			$ss = M('table')->where(array('id'=>$id))->find();
			$sid = $ss['sid'];
			$qcUrl = U('Index/index@yd',array('jid'=>$this->jid,'sid'=>$ss['sid'],'table'=>$id));
		}
		vendor("phpqrcode.phpqrcode");
		$QRcode = new \QRcode();
		$im = $QRcode::png($qcUrl, false, 'H', $size);
		Header("Content-type: application/octet-stream");   
		Header("Accept-Ranges: bytes");   
		Header("Accept-Length:".filesize($im));   
		Header("Content-Disposition: attachment; filename=".$sid.".png"); 
		echo $im;
		exit;
	}
	public function editSInfo(){
		if(IS_POST){
			
			$sid = I('sid');
			
			$info = array(
				'mservetel'=>I('mservetel'),
				'qq'=>I('qq'),
				'weixin_name'=>I('weixin_name'),
			);
				
			$r = M('shop')->where(array('jid'=>$this->jid,'sid'=>$sid))->save($info);
			exit($r ? '1' : '0');
		}else{
			$sid = I('sid');
			$sinfo = M('shop')->where(array('sid'=>$sid))->find();
			$this->assign('sinfo',$sinfo);
			$this->display();
		}
	}
}