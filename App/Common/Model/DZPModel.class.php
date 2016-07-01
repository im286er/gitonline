<?php
namespace Common\Model;
use Think\Model;

class DZPModel extends Model {
	
	/* 抽奖
	 * 
	 * */	
	public function doPrize($userid,$jid,$sid) {
		//验证抽奖资格
		$r = $this->checkPrize($userid,$jid,$sid);
		if($r == false){
			return false;
		}
		$shop = M('dazhuanpan')->where(array('z_jid'=>$jid,'z_sid'=>$sid))->find();
		$set = unserialize($shop['set']);
		$gailv = $set['gailv'];
		$key = get_rand($gailv);
		$result = array(
			'key' => $key,
			'ptype' => $set['ptype'][$key],
			'pname' => $set['pname'][$key],
			'pvid' => $set['pvid'][$key],
		);
		
		//记录抽奖记录
		$s = $this->savePrize($userid,$jid,$result,$sid);
		
		return $s ? $result : false;
	}
	
	/* 验证抽奖资格
	 * 
	 * 
	 * */
	public function checkPrize($userid,$jid,$sid){
		$n1 = $this->userCount($jid,$userid,$sid);
		if($n1 > 0){
			return true;
		}else{
			return false;
		}
	}
	
	
	/*保存抽奖记录*/
	public function savePrize($userid,$jid,$result,$sid){
		$f = $this->getFreeCount($jid,$sid);
		$u = $this->getUsedCount($jid,$userid,$sid);
		if($f - $u > 0){
			$ptype = 0;//使用免费次数
		}else{
			$ptype = 1;//使用不免费次数
		}
		$o = 0;
		if($result['ptype'] == 0){
			$isget = 2;
		}elseif($result['ptype'] == 1){
			$isget = 0;
		}elseif($result['ptype'] == 2){
			$o = $this->sendVoucher($userid,$result['pvid']);
			$isget = 1;
		}
		$opt = array(
				'jid' => $jid,
				'sid' => $sid,
				'userid' => $userid,
				'addtime' => date("Y-m-d H:i:s"),
				'rtype' => $result['ptype'],
				'rname' =>  $result['pname'],
				'rvid' => $result['pvid'],
				'ptype' => $ptype,
				'isget' => $isget,
				'rkey'  => $result['key'],
				'uvid'  => $o ? $o : 0
 		);
		$r = M('dzp_prize')->add($opt);
		return $r;
	}
	
	//发送优惠券
	public function sendVoucher($userid,$vid){
		$coupon = M('voucher');
		$opt = array(
				'vu_id'    => $vid,
		);
		$r = false;
		$coupon_info = $coupon->where($opt)->find();
		if($coupon_info){
			$opt = array(
					'vu_id' => $vid,
					'mid'  => $userid,
					'vu_price' => $coupon_info["vu_price"]
			);
			$r = M('voucher_user')->add($opt);
		}
		return $r;
	}
	
	/* 获取用户在某商户的剩余抽奖次数
	 * 
	 * */
	public function userCount($jid,$userid,$sid){
		$num_free  = $this->getFreeCount($jid,$sid);//当天免费次数
		$num_pay  = $this->getPayCount($jid,$userid,$sid);//总的剩余不免费次数
		$num_used  = $this->getUsedCount($jid,$userid,$sid);//当天已使用的免费次数
		$num = $num_free - $num_used + $num_pay;
		return $num;
	}
	
	//每日免费次数
	public function getFreeCount($jid,$sid){
		$r = M('dazhuanpan')->where(array('z_jid'=>$jid,'z_sid'=>$sid))->getField('freetime');
		return $r;
	}
	//不免费的次数
	public function getPayCount($jid,$userid,$sid){
		$minmoney = M('dazhuanpan')->where(array('z_jid'=>$jid,'z_sid'=>$sid))->getField('minmoney');
		//$num_share = M('dzp_share')->where(array('jid'=>$jid,'userid'=>$userid))->count();//分享得到的次数
		$num_share = 0;
		$ot1 = M('order')->where(array('o_sid'=>$sid,'o_jid'=>$jid,'o_uid'=>$userid,'o_pstatus'=>1))->sum('o_price');//os订单总额
		$ot2 = M('fl_order')->where(array('flo_sid'=>$sid,'flo_jid'=>$jid,'flo_uid'=>$userid,'flo_pstatus'=>1))->sum('flo_price');//返利订单总额
		$num_order = floor(($ot1+$ot2)/$minmoney);
		$num_userd = M('dzp_prize')->where(array('sid'=>$sid,'jid'=>$jid,'userid'=>$userid,'ptype'=>1))->count();
		$num = $num_share + $num_order - $num_userd;
		return $num;
	}
	//当天已使用的免费次数
	public function getUsedCount($jid,$userid,$sid){
		$opt = array(
			'addtime' => array(array('egt',date("Y-m-d")),array('elt',date("Y-m-d 23:59:59"))),
			'jid' => $jid,
			'userid' => $userid,
			'ptype' => 0,
			'sid' => $sid,
		);
		$num = M('dzp_prize')->where($opt)->count();
		return $num;
	}
}
function get_rand($proArr) {
	$result = '';

	//概率数组的总概率精度
	$proSum = array_sum($proArr);

	//概率数组循环
	foreach ($proArr as $key => $proCur) {
		$randNum = mt_rand(1, $proSum);
		if ($randNum <= $proCur) {
			$result = $key;
			break;
		} else {
			$proSum -= $proCur;
		}
	}
	unset ($proArr);

	return $result;
}
?>