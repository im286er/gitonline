<?php
namespace Common\Model;
use Think\Model;

class OrderModel extends Model {
	
	/* 删除订单内的商品
	 * $sp_id:订单商品号
	 * 
	 * */
	public function deleteOrderGoods($sp_id,$sp_reason='',$sp_cause=''){
		$snapshot = M('goods_snapshot')->where(array('sp_id'=>$sp_id))->find();
		
		if(!$this->cadEdit($snapshot['sp_oid'])){
			return false;
		}
		M()->startTrans();
		$r1 = M('goods_snapshot')->where(array('sp_id'=>$sp_id))->save(array('sp_status'=>0));
		$original_order = $this->where(array('o_id'=>$snapshot['sp_oid']))->find();
		
		$r2 = $this->saveOrder($snapshot['sp_oid']);
		$r3 = $this->goodsRecede($original_order,$snapshot,$sp_reason,$sp_cause);
		if($r1 && $r2 && $r3){
			M()->commit();
			return true;
		}else{
			M()->rollback();
			return false;
		}
	}


	/* 记录退款信息及退款原因
	 * $original_order 原来订单的信息
	 * $snapshot 退货的商品快照
	 * */	
	public function goodsRecede($original_order,$snapshot,$sp_reason='',$sp_cause=''){
		$new_order = $this->where(array('o_id'=>$snapshot['sp_oid']))->find();
		//print_r($new_order);
		$deductible = $snapshot['sp_gdprice']*$snapshot['sp_number'];
		//if(($original_order['o_price']-$new_order['o_price'])==$deductible){
			$recede = array();
			$recede['sp_jid'] = $new_order['o_jid'];
			$recede['sp_sid'] = $new_order['o_sid'];
			$recede['sp_oid'] = $new_order['o_id'];
			$recede['sp_uid'] = $new_order['o_uid'];
			$recede['sp_otype'] = $new_order['o_type'];
			$recede['sp_gid'] = $snapshot['sp_gid'];
			$recede['sp_name'] = $snapshot['sp_name'];
			$recede['sp_gdprice'] = $snapshot['sp_gdprice'];
			$recede['sp_number'] = $snapshot['sp_number'];
			$recede['sp_price'] = $deductible;
			$recede['sp_img'] = $snapshot['sp_img'];
			$recede['sp_date'] = date('Y-m-d H:i:s');
			$recede['sp_reason'] = $sp_reason;
			$recede['sp_cause'] = $sp_cause;
			$result = M('goods_recede')->add($recede);
		//}
		return $result?true:false;
	}







	/* 编辑订单商品数量
	 * $oid:订单号
	 * $goods:需要更新的订单商品列表
	 * */
	public function editOrderGoods($oid,$goods){
		if(!$this->cadEdit($oid)){
			return false;
		}
		$goods_arr = explode('|', $goods);
		M()->startTrans();
		foreach($goods_arr as $k=>$v){
			if(!empty($v)){
				$g_arr = explode(':', $v);
				$sp_gid = $g_arr[0];
				$sp_num = intval($g_arr[1]);
				if($sp_num > 0){
					$rr = M('goods_snapshot')->where(array('sp_id'=>$sp_gid))->save(array('sp_number'=>$sp_num));
				}
			}
		}
		$r2 = $this->saveOrder($oid);
		if($r2){
			M()->commit();
			return true;
		}else{
			M()->rollback();
			return false;
		}
	}
	
	/* 添加商品到订单
	 * $oid:订单号
	 * $gid:商品id
	 * $number:商品数量
	 * */
	public function addOrderGoods($oid,$gid,$number){
		if(!$this->cadEdit($oid)){
			return false;
		}
		if(!$this->cadAdd($oid,$gid)){
			return false;
		}
		$v = M('goods')->where(array('gid'=>$gid))->find();
		M()->startTrans();
		$gt = array(
				'sp_gid' => $v['gid'],
				'sp_oid' => $oid,
				'sp_name' => $v['gname'],
				'sp_gdescription' => $v['gdescription'],
				'sp_goprice' => $v['goprice'],
				'sp_gdprice' => $v['gdprice'] > 0 ? $v['gdprice'] : $v['goprice'],
				'sp_number' =>  $number > 0 ? $number : 1,
				'sp_img' => $v['gimg'],
		);
		$r1 = M('goods_snapshot')->add($gt);
		$r2 = $this->saveOrder($oid);
		if($r2){
			M()->commit();
			return true;
		}else{
			M()->rollback();
			return false;
		}
	}
	
	//查询订单商品是否可添加
	public function cadAdd($oid,$gid){
		$r = M('goods_snapshot')->where(array('sp_oid'=>$oid,'sp_gid'=>$gid))->find();
		if($r){
			return false;
		}else{
			return true;
		}
	}
	
	//订单是否可编辑
	public function cadEdit($oid){
		$s = M('order')->where(array('o_id'=>$oid))->find();
		if($s['o_dstatus'] != 1){
    		return false;
    	}
    	return true;
	}
	
	//重新计算订单金额
	public function saveOrder($oid){
		$orderGoods = M('goods_snapshot')->where(array('sp_oid'=>$oid,'sp_status'=>1))->select();
		$o_price = 0;
		foreach($orderGoods as $k=>$v){
			$o_price += $v['sp_gdprice']*$v['sp_number'];
		}
		//查询优惠券金额
		$voucher_price = M('voucher_order')->where(array('o_id'=>$oid))->getField('o_price');
		$o_price = $o_price - $voucher_price;
		$r = M('order')->where(array('o_id'=>$oid))->save(array('o_price'=>$o_price));
		return $r;
	}
	
	//订单完成是计算消费投资
	public function doTz($oid){
		$tj = $this->checkTz($oid);//判断是否满足条件
		if($tj){
			$r = $this->creatTz($oid);
			return $r;
		}else{
			return false;
		}
	}
	//判断是否满足条件
	public function checkTz($oid){
		$order = M('order')->where(array('o_id'=>$oid))->field('o_sid,o_price')->find();
		$tz    = M('touzi')->where(array('sid'=>$order['o_sid'],'status'=>1))->find();
		if(empty($tz)){
			return false;
		}
		if($tz['tz_type'] == 1){//店铺投资
			$r = $order['o_price'] >= $tz['money'] ? true : false;
			return $r;
		}elseif($tz['tz_type'] == 2 && $tz['tz_goods']){//商品投资
			$sql = "select sum(sp_number*sp_gdprice) as total from azd_goods_snapshot where sp_gid in (".$tz['tz_goods'].") and sp_oid='".$oid."'";
			$total = M()->query($sql);
			$r = $total[0]['total'] >= $tz['money'] ? true : false;
			return $r;
		}else{
			return false;
		}
	}
	//投资返利
	public function creatTz($oid){
		$order = M('order')->where(array('o_id'=>$oid))->field('o_sid,o_price,o_uid')->find();
		$tz    = M('touzi')->where(array('sid'=>$order['o_sid'],'status'=>1))->find();
		$total = $order['o_price'] * $tz['fanli'] / 100;//总共要返利的钱
		$set1 = $tz['set1'];
		$set2 = $tz['set2'];
		$set3 = $tz['set3'];
		if($set1 == 0 && $set2 == 0 && $set3 == 0){
			$set1 = 100;
		}
		$user2 = M('fl_user')->where(array('flu_userid'=>$order['o_uid']))->getField('flu_puserid');
		$user3 = M('fl_user')->where(array('flu_userid'=>$user2))->getField('flu_puserid');
		if($set1 > 0){
			$opt1 = array(
				'oid' => $oid,
				'uid' => $order['o_uid'],
				'money' => round($total * $set1 / 100,2),
				'time'  => date("Y-m-d H:i:s",mktime(date("H"),date("i"),date("s"),date("m")+intval($tz['time']),date("d"),date("Y"))),
				'addtime' => date("Y-m-d H:i:s"),
			);
			M('touzi_hb')->add($opt1);//一级

		}
		if($set2 > 0 && $user2 > 0){
			$opt2 = array(
				'oid' => $oid,
				'uid' => $user2,
				'money' => round($total * $set2 / 100,2),
				'time'  => date("Y-m-d H:i:s",mktime(date("H"),date("i"),date("s"),date("m")+intval($tz['time']),date("d"),date("Y"))),
				'addtime' => date("Y-m-d H:i:s"),
			);
			M('touzi_hb')->add($opt2);//二级	
		}
		if($set3 > 0 && $user3 > 0){
			$opt3 = array(
				'oid' => $oid,
				'uid' => $user3,
				'money' => round($total * $set3 / 100,2),
				'time'  => date("Y-m-d H:i:s",mktime(date("H"),date("i"),date("s"),date("m")+intval($tz['time']),date("d"),date("Y"))),
				'addtime' => date("Y-m-d H:i:s"),
			);
			M('touzi_hb')->add($opt3);//三级	
		}
		return true;
	}
	
	//添加消息
	public function addUserMsg($jid,$sid,$text,$type,$userid,$title){
		$opt = array(
			'jid' => $jid,
			'sid' => $sid,
			'userid' => $userid,
			'msgtype' => $type,
			'msgtext' => $text,
			'msgtitle' => $title,
			'add_time' => date("Y-m-d H:i:s"),
			'status' => 0,
		);
		M('user_msg')->add($opt);
		return true;
	}
}

?>