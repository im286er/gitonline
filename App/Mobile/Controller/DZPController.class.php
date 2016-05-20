<?php
namespace Mobile\Controller;

class DZPController extends MobileController {
	public function index(){
		$jid = I('jid',$this->jid);
		if(cookie('opentype')=='flapp'){
			$utk = I('utoken');
			if($utk){
				$userid = M('fl_usertoken')->where(array('utoken'=>$utk))->getField('userid');
			}else{
				$userid = 0;
			}
			
			$back_url = U('Index/index@yd',array('jid'=>$jid));
			$linkurl = url_param_encrypt(U('DZP/index@yd',array('jid'=>$jid)),'E');
			$linkurl2 = url_param_encrypt(U('DZP/myPrize@yd',array('jid'=>$jid)),'E');
			$this->assign('opentype','flapp');
			$this->assign('linkurl',$linkurl);
			$this->assign('linkurl2',$linkurl2);
		}else{
			$userid = $this->mid;
			$loginurl = U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('DZP/index',array('jid'=>$this->jid)),'E'),'returnurl'=>url_param_encrypt(U('DZP/index',array('jid'=>$this->jid)),'E')));
			$loginurl2 = U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('DZP/index',array('jid'=>$this->jid)),'E'),'returnurl'=>url_param_encrypt(U('DZP/myPrize',array('jid'=>$this->jid)),'E')));
			$back_url = U('Index/index@yd',array('jid'=>$jid));
			$this->assign('opentype','yd');
			$this->assign('loginurl',$loginurl);
			$this->assign('loginurl2',$loginurl2);
		}
		$r = array();
		$prize = M('dazhuanpan')->where(array('z_jid'=>$jid))->find();
		if($prize['set']){
			$a =  unserialize($prize['set']);
			foreach($a['ptype'] as $k=>$v){
				if($v==0){
					$r[] = '谢谢参与';
				}elseif($v==1){
					$r[] = $a['pname'][$k];
				}elseif($v==2){
					$r[] = M('voucher')->where(array('vu_id'=>$a['pvid'][$k]))->getField('vu_name');
				}
			}
		}
		$status = empty($r) ? 0 : 1;
		if($prize['status'] == 0){
			$status = 0;
		}
		if(date("Y-m-d H:i:s",time()) < $prize['stime'] || date("Y-m-d H:i:s",time()) > $prize['etime']){
			$status = 0;
		}
		
		$user_agent = I('server.HTTP_USER_AGENT');
		if(stristr($user_agent,'iPhone') || stristr($user_agent, 'ipa')){
			$this->msystem = 'ios';
			$this->assign('msystem','ios');
		}else{
			$this->msystem = 'android';
			$this->assign('msystem','android');
		}
		$last_num = D('DZP')->userCount($jid,$userid);
		$this->assign('jid',$jid);
		$this->assign('last_num',$last_num);
		$this->assign('status',$status);
		$this->assign('prize_list',json_encode($r));
		$this->assign('prize',$prize);
		$this->assign('back_url',$back_url);
		$this->assign('mid',$userid);
		$this->display();
	}
	//我的奖品
	public function myPrize(){
		$jid = I('jid');
		if(cookie('opentype')=='flapp'){
			$utk = I('utoken');
			if($utk){
				$userid = M('fl_usertoken')->where(array('utoken'=>$utk))->getField('userid');
			}else{
				$userid = 0;
			}
			$back_url = U('DZP/index@yd',array('jid'=>$jid,'utoken'=>$utk));
		}else{
			$userid = $this->mid;
			$back_url = U('DZP/index@yd',array('jid'=>$jid));
		}
		$opt = array(
			'p.jid' => $jid,
			'p.userid'  =>  $userid,
			'p.rtype' =>  array('neq',0)
		);
		$rs = M('dzp_prize')->alias('p')->join('azd_voucher as v on p.rvid=v.vu_id ','LEFT')->where($opt)->select();
		$r1=$r2=$r3=array();
		foreach($rs as $k=>$v){
			if($v['rtype'] == 1){
				if($v['isget'] == 1){
					$r2[] = array('id'=>$v['id'],'type'=>1,'name'=>$v['rname'],'dt'=>'','status'=>'2');
				}else{
					$dt = M('dazhuanpan')->where(array('z_jid'=>$jid))->find();
					if(time() > strtotime($dt['etime'])){
						$r3[] = array('id'=>$v['id'],'type'=>1,'name'=>$v['rname'],'dt'=>date("Y-m-d",strtotime($dt['etime'])),'status'=>'3');
					}else{
						$r1[] = array('id'=>$v['id'],'type'=>1,'name'=>$v['rname'],'dt'=>date("Y-m-d",strtotime($dt['etime'])),'status'=>'1');
					}
				}
			}else{
				$vu_price = M('voucher_user')->where(array('uvid'=>$v['uvid']))->getField('vu_price');
				if($vu_price == 0){
					$r2[] = array('id'=>$v['id'],'type'=>2,'name'=>$v['vu_name'],'dt'=>'','status'=>'2');
				}else{
					if(time() > strtotime($v['vu_etime'])){
						$r3[] = array('id'=>$v['id'],'type'=>2,'name'=>$v['vu_name'],'dt'=>date("Y-m-d",strtotime($v['vu_etime'])),'status'=>'3');
					}else{
						$r1[] = array('id'=>$v['id'],'type'=>2,'name'=>$v['vu_name'],'dt'=>date("Y-m-d",strtotime($v['vu_etime'])),'status'=>'1');
					}
				}
			}
		}
		$this->assign('list1',$r1);
		$this->assign('list2',$r2);
		$this->assign('list3',$r3);
		$this->assign('back_url',$back_url);
		$this->display();
	}
	
	//抽奖
	public function getPrize(){
		$jid = I('jid');
		$utk = I('utk');
		$r = array(
				'code' => 0,
				'last_num' => 0,
		);
		if(cookie('opentype')=='flapp'){
			if($utk){
				$userid = M('fl_usertoken')->where(array('utoken'=>$utk))->getField('userid');
			}else{
				$userid = 0;
			}
		}else{
			$userid = $this->mid;
		}
		if(empty($userid)){
			die(json_encode($r));
		}
		$pp = D('DZP')->doPrize($userid,$jid);
		$last_num = D('DZP')->userCount($jid,$userid);
		$r['last_num'] = $last_num;
		if($pp){
			$r['code'] = 1;
			$r['k']    = $pp['key'];
			$r['ptype'] = $pp['ptype'];
			if($pp['ptype']==1){
				$r['pname'] = $pp['pname'];
			}elseif($pp['ptype']==2){
				$r['pname'] = M('voucher')->where(array('vu_id'=>$pp['pvid']))->getField('vu_name');
			}
		}
		die(json_encode($r));
	}
	
	public function myQrcode(){
		$id = I('id');
		$jid = I('jid');
		if(cookie('opentype')=='flapp'){
			$utk = I('utoken');
			if($utk){
				$userid = M('fl_usertoken')->where(array('utoken'=>$utk))->getField('userid');
			}else{
				$userid = 0;
			}
			$back_url = U('DZP/myPrize@yd',array('jid'=>$jid,'utoken'=>$utk));
		}else{
			$userid = $this->mid;
			$back_url = U('DZP/myPrize@yd',array('jid'=>$jid));
		}
		$prize = M('dzp_prize')->where(array('rtype'=>1,'jid'=>$jid,'userid'=>$userid,'id'=>$id))->find();
		$end_time = M('dazhuanpan')->where(array('z_jid'=>$prize['jid']))->getField('etime');
		$shop = M('shop')->where(array('jid'=>$jid,'status'=>'1'))->field('sname,mservetel')->select();
		$prize['end_time'] = $end_time;
		$this->assign('prize',$prize);
		$this->assign('back_url',$back_url);
		$this->assign('shop',$shop);
		$this->display();
	}
	
	public function shareDzp(){
		$jid = I('dzp_jid',0);
		$utk = I('utoken','');
		if($utk){
			$userid = M('fl_usertoken')->where(array('utoken'=>$utk))->getField('userid');
		}else{
			$userid = I('dzp_mid',0);
		}
		$type = I('type',0);
		if(empty($jid) || empty($userid) || empty($type)){
			die('0');
		}
		$opt = array(
			'userid' => $userid,
			'jid' => $jid,
			'type' => $type,
			'stime' => array(array('egt',date("Y-m-d")),array('elt',date("Y-m-d 23:59:59"))),
		);
		$r = M('dzp_share')->where($opt)->find();
		if(empty($r)){
			$data = array(
					'userid' => $userid,
					'jid' => $jid,
					'type' => $type,
					'stime' => date("Y-m-d H:i:s"),
			);
			M('dzp_share')->add($data);
			die('1');
		}else{
			die('0');
		}
	}
}