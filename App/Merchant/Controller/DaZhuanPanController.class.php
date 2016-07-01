<?php
namespace Merchant\Controller;

class DaZhuanPanController extends MerchantController {
	//基本设置
	public function index() {
		$sid   = I('sid', 0, 'intval');
		if( IS_POST ) {
			$data = array();
			$data['status'] = I('status',0);
			$data['stime'] = I('stime');
			$data['etime'] = I('etime');
			$data['freetime'] = I('freetime',0);
			$data['minmoney'] = I('minmoney',0);
			$data['z_jid'] = $this->jid;
			$data['z_sid'] = $sid;
			
			if(empty($sid)){
				die("3");
			}
			if(empty($data['stime']) || empty($data['etime'])){
				die("0");
			}
			$dzp = M('dazhuanpan')->where(array('z_jid'=>$this->jid, 'z_sid'=>$sid))->find();
			if($dzp){
				$r = M('dazhuanpan')->where(array('z_jid'=>$this->jid, 'z_sid'=>$sid))->save($data);
				die('1');
			}else{
				$r = M('dazhuanpan')->add($data);
				die('2');
			}
		}else{
			$shops = D('auth')->getAuthShops($this->mid);
			$dzp   = M('dazhuanpan')->where(array('z_sid'=>$sid, 'z_jid'=>$this->jid))->find();

			$this->assign('shops', $shops);
			$this->assign('dzp',$dzp);
			$this->assign('sid', $sid);
			$this->assign('CurrentUrl', 'Salesgoods');
			$this->display();
		}
	}
	//奖品设置
	public function prize() {
		$sid   = I('sid', 0, 'intval');
		$default = array(0,1,2,3,4,5,6,7,8,9);
		if( IS_POST ) {
			$prize = $_POST['prize'];
			$gailv = $prize['gailv'];
			$t = array_sum($gailv);
			if($t != 100){
				die(json_encode(array('code'=>'error1')));
			}
			if(empty($sid)){
				die(json_encode(array('code'=>'error2','msg'=>'未选择分店')));
			}
			for($i=0;$i<count($default);$i++){
				if($prize['ptype'][$i] ==1 && empty($prize['pname'][$i])){
					die(json_encode(array('code'=>'error2','msg'=>'请填写区域'.($i+1).'实物奖品对应的奖品名称')));
				}elseif($prize['ptype'][$i] ==2 && empty($prize['pvid'][$i])){
					die(json_encode(array('code'=>'error2','msg'=>'请选择区域'.($i+1).'对应的优惠券奖品')));
				}				
			}
			$data['set'] = serialize($_POST['prize']);
			$r = M('dazhuanpan')->where(array('z_jid'=>$this->jid,'z_sid'=>$sid))->save($data);
			if($r){
				die(json_encode(array('code'=>'success')));
			}else{
				die(json_encode(array('code'=>'error2','msg'=>'请先进行基础设置再设置奖品')));
			}
		}else{
			$prize_set = M('dazhuanpan')->where(array('z_jid'=>$this->jid,'z_sid'=>$sid))->getField('set');
			$prize = unserialize($prize_set);
			//优惠券
			$voucher = M('voucher')->where(array('vu_sid'=>$sid,'vu_jid'=>$this->jid,'vu_status'=>1,'vu_etime'=>array('gt',date("Y-m-d H:i:s"))))->select();
			$this->assign('voucher',$voucher);
			$this->assign('prize',$prize);
			$this->assign('default',$default);
			$this->assign('CurrentUrl', 'DaZhuanPan');
			$this->assign('sid',$sid);
			$this->display();
		}
	}
	//中奖列表
	public function user() {
		$sid = I('sid',0);
		$where = array(
			'jid'=>$this->jid,
			'sid'=>$sid,
		);
		$page = new \Common\Org\Page(M('dzp_prize')->where($where)->count(), 10);
		$datalist = M('dzp_prize')->where($where)->order('addtime desc')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($datalist as $k=>$v){
			$datalist[$k]['uname'] = M('fl_user')->where(array('flu_userid'=>$v['userid']))->getField('flu_nickname');
			$datalist[$k]['addtime'] = date("Y-m-d",strtotime($v['addtime']));
			if($v['rvid'] > 0){
				$datalist[$k]['vname'] = M('voucher')->where(array('vu_id'=>$v['rvid']))->getField('vu_name');
			}
		}
		$this->assign('datalist',$datalist);
		$this->assign('pages', $page->show());
		$this->assign('CurrentUrl', 'DaZhuanPan');
		$this->assign('sid',$sid);
		$this->display();
	}
	
	public function lj(){
		$id = I('id',0);
		M('dzp_prize')->where(array('jid'=>$this->jid,'id'=>$id))->save(array('isget'=>1));
		die('1');
	}
}