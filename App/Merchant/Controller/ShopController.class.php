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
		$where = array('jid'=>$this->jid, 'status'=>'1');
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
			array_walk($_POST['memb'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$_POST['memb']['mtype'] = 2;
			$_POST['memb']['mregdate'] = $_POST['memb']['mlogindate'] = date('Y-m-d H:i:s');
			$_POST['info']['jid'] = $this->jid;
			$_POST['memb']['mpwd'] = md5(md5( $_POST['memb']['mpwd'] ));
			
			$seatlist = $_POST['info']['seatlist'];
			$seatlist_array = explode(",", $seatlist);
			array_filter($seatlist_array, function(&$value) { $value=trim($value); });
			$_POST['info']['seatlist'] = implode(",", $seatlist_array);

			$MemberModel = D('Member');
			$MemberModel->startTrans();
			
			$status_01 = $mid = $MemberModel->insert($_POST['memb']);
			
			if($_POST['coordinates']){
				$coordinates = explode(",",$_POST['coordinates']);
				$_POST['info']['lng'] = $coordinates[0];
				$_POST['info']['lat'] = $coordinates[1];
			}
			if(empty($_POST['info']['district'])){
				$city = $_POST['info']['city'];
				$_POST['info']['city'] = $_POST['info']['province'];
				$_POST['info']['district'] = $city;
			}
			
			$status_02 = $sid = D('Shop')->insert($_POST['info']);
			$status_03 = M('MerchantUser')->add(array('tmid'=>$mid, 'tjid'=>$this->jid, 'tsid'=>$sid, 'type'=>2));
			
			if( $status_01 && $status_02 && $status_03 ) {
				$MemberModel->commit();
				$shop_count = M('shop')->where(array('jid'=>$this->jid))->count();
				if($shop_count == 1){
					$this->success('添加成功', U('/Sales/goods/',array('ctype'=>1,'guide'=>1), true));
				}else{
					$this->success('添加成功', U('/Shop/index', '', true));
				}
			} else {
				$MemberModel->rollback();
				$error_string = '';
				if( $MemberModel->getError() ) $error_string = $MemberModel->getError();
				if( D('Shop')->getError() ) $error_string = $error_string ? $error_string.";".D('Shop')->getError() : D('Shop')->getError();
				if( !$error_string ) $error_string = '操作失败';
				$this->error( $error_string );
			}			
		} else {
			$this->_AddressList = F('AddressList');
			if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $this->_AddressList);
			$this->assign('addressList', $this->_AddressList);
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
		  	
			if(empty($_POST['info']['district'])){
				$city = $_POST['info']['city'];
				$_POST['info']['city'] = $_POST['info']['province'];
				$_POST['info']['district'] = $city;
			}
			if($_POST['coordinates']){
				$coordinates = explode(",",$_POST['coordinates']);
				$_POST['info']['lng'] = $coordinates[0];
				$_POST['info']['lat'] = $coordinates[1];
			}
			
			$seatlist = $_POST['info']['seatlist'];
			$seatlist_array = explode(",", $seatlist);
			array_filter($seatlist_array, function(&$value) { $value=trim($value); });
			$_POST['info']['seatlist'] = implode(",", $seatlist_array);
			
			if( D('Shop')->update($_POST['info']) === false) {
				$this->error(D('Shop')->getError() ? D('Shop')->getError() : '修改失败' );
			} else { $this->success('修改成功', U('/Shop/index', '', true)); }		
		} else {
			$shop = M('shop')->where(array('sid'=>I('get.sid', 0, 'intval'), 'jid'=>$this->jid))->find();
			if(!is_array($shop) || empty($shop)) { E('你无权操作此页面'); }
			
			$this->_AddressList = F('AddressList');
			if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $this->_AddressList);
			$this->assign('addressList', $this->_AddressList);
			
			if($shop['province']){
				$address1 = M('address')->where(array('apid'=>$shop['province']))->select();
			}
			
			if($shop['province'] != $shop['city'] && $shop['city']){
				$address2 = M('address')->where(array('apid'=>$shop['city']))->select();
			}
			$this->assign('address1', $address1);
			$this->assign('address2', $address2);
			$this->assign('shop', $shop);
			$this->display();				
		}		
	}

	//查看详情
	public function infoShop() {
		//if( $this->type != 1 ) E('你无权查看当前页面');
		
		$shop = M('shop')->where(array('sid'=>I('get.sid', 0, 'intval'), 'jid'=>$this->jid))->find();
		if(!is_array($shop) || empty($shop)) { E('你无权操作此页面'); }
		$merchantuser = M('MerchantUser')->where(array('type'=>2,'tsid'=>$shop['sid'], 'tjid'=>$this->jid))->find();
		if($merchantuser['tmid'])$member = M('Member')->where(array('mid'=>$merchantuser['tmid']))->find();
		//print_r($member);
		$this->assign('member', $member);
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
}