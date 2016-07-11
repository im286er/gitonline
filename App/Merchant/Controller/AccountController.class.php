<?php
namespace Merchant\Controller;

class AccountController extends MerchantController {
	
	//个人资料修改
	public function index() {
		if( IS_POST ) {
			//$tphone  = I('tphone');
			$birthday  = I('birthday');
			$address  = I('address');
			$sex  = I('sex');
			$idcard  = I('idcard');
			
			$merchant_user = array(		
					//'tphone' => $tphone,			
					'sex' => $sex,
					'birthday' => $birthday,
					'address' => $address,
					'idcard' => $idcard,
			);
			$r = M('merchant_user')->where(array('tmid'=>$this->mid))->save($merchant_user);
			if($r){
				$this->success('修改成功');
			}else{
				$this->error('修改失败');
			}
		}else{
			$m_info = M('member')->join('azd_merchant_user on azd_member.mid=azd_merchant_user.tmid')->where(array('mid'=>$this->mid))->field('mname,azd_merchant_user.*')->find();
			$m_info['birthday'] = date("Y-m-d",strtotime($m_info['birthday']));
			$this->assign('m_info',$m_info);
			$this->assign('CurrentUrl', 'Accountindex2');
			$this->display();
		}
	}
	
	//添加账户
	public function add() {
		if( IS_POST ) {
			$mname  = I('mname');
			$tname  = I('tname');
			$mpwd  = I('mpwd');
			$cpwd  = I('cpwd');
			$job  = I('job');
			$tphone  = I('tphone');
			$birthday  = I('birthday');
			$address  = I('address');
			$sex  = I('sex');
			$idcard  = I('idcard');		
			$top = I('top');
			$next = I('next');
			$shopauth = I('shopauth');
			$shift = I('shift');
			$role = I('role');
			
			$auth = array();
			$auth['top'] = $top;
			$auth['next'] = $next;
					
			$member = array(
				'mname' => $mname,
				'mpwd'  => md5(md5($mpwd)),
				'mtype'  => 2,
				'mregdate'  =>   date("Y-m-d H:i:s"),
				'msurname' => $tname,
				'mphone'  => $tphone,
				'idcard'  => $idcard
			);
			$mid = D('Member')->insert($member);
			$r = 0;
			if($mid){
				$merchant_user = array(
					'tmid' => $mid,
					'tjid' => $this->jid,
					'tname' => $tname,
					'tphone' => $tphone,
					'job' => $job,
					'sex' => $sex,
					'birthday' => $birthday,
					'address' => $address,
					'idcard' => $idcard,
					'auth' => serialize($auth),
					'shopauth' => $shopauth ? join(',',$shopauth) : '',
					'shift' => $shift,
					'role' => $role,
				);
				$r = M('merchant_user')->add($merchant_user);
			}
			if($r){
				$this->success('添加成功', 'accountList');
			}else{
				$this->error('添加失败');
			}
		}else{
			$shopList = M('shop')->where(array('jid'=>$this->jid,'status'=>'1'))->select();
			$this->assign('shopList',$shopList);
			$this->display();
		}
	}
	
	//编辑账户
	public function edit(){
		if( IS_POST ) {
			$tmid = I('tmid');
			$tphone  = I('tphone');
			$birthday  = I('birthday');
			$address  = I('address');
			$sex  = I('sex');
			$idcard  = I('idcard');
			$top = I('top');
			$next = I('next');
			$shopauth = I('shopauth');
			$shift = I('shift');
			$role = I('role');
			$mpwd = I('mpwd');
						
			$auth = array();
			$auth['top'] = $top;
			$auth['next'] = $next;
			
			$merchant_user = array(
					'tphone' => $tphone,
					'sex' => $sex,
					'birthday' => $birthday,
					'address' => $address,
					'idcard' => $idcard,
					'auth' => serialize($auth),
					'shopauth' => join(',',$shopauth),
					'shift' => $shift,
					'role' => $role,
			);
			$r = M('merchant_user')->where(array('tmid'=>$tmid,'tjid'=>$this->jid))->save($merchant_user);

			//判断是否更改密码
			if ($mpwd) {
				$t = M('member')->where('mid='.$tmid)->setField('mpwd', md5(md5($mpwd)));
			}
			if($r || $t){
				$this->success('修改成功');
			}else{
				$this->error('修改失败');
			}
		}else{
			$tmid = I('tmid');
			$m_info = M('member')->join('azd_merchant_user on azd_member.mid=azd_merchant_user.tmid')->where(array('mid'=>$tmid))->field('mname,azd_merchant_user.*')->find();
			$m_info['birthday'] = date("Y-m-d",strtotime($m_info['birthday']));
			$m_info['auth'] = unserialize($m_info['auth']);
			$m_info['shopauth'] = explode(',',$m_info['shopauth']);
			$shopList = M('shop')->where(array('jid'=>$this->jid,'status'=>'1'))->select();
			$this->assign('shopList',$shopList);
			$this->assign('m_info',$m_info);
			$this->assign('CurrentUrl', 'AccountaccountList');
			$this->display();
		}
	}
	
	
	//账户列表
	public function accountList() {
		$page = new \Common\Org\Page(M('member')->join('azd_merchant_user on azd_member.mid=azd_merchant_user.tmid')->where(array('tjid'=>$this->jid))->count(), 10);
		$account_list = M('member')->join('azd_merchant_user on azd_member.mid=azd_merchant_user.tmid')->where(array('tjid'=>$this->jid))->order('mregdate desc')->field('mstatus,mname,azd_merchant_user.*')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('account_list',$account_list);
		$this->assign('pages', $page->show());
		$this->display();
	}
	
	//禁用启用某个账号
	public function jinyong(){
		$tmid = I('tmid');
		$type = I('type');
		$mid  = M('merchant_user')->where(array('tmid'=>$tmid,'tjid'=>$this->jid))->getField('tmid');
		if($mid){
			$r = M('member')->where(array('mid'=>$mid))->save(array('mstatus'=>$type));
			if($r){
				exit('1');
			}else{
				exit('0');
			}
		}else{
			exit('0');
		}
	}
}