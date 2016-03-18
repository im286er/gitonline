<?php
namespace Mobile\Model;
use Think\Model;

class GoodsModel extends Model {
	public $order_id;


	protected $_validate = array(
    );

	//去库存 $order_id d订单号
	// $type :setDec 减少，setInc增加
	// $otype :1为普通订单，2为返利订单
	public function reduceRepertory($order_id=null,$type='setDec',$otype=null){
		if(!$order_id && !$otype)return false;
		$this->order_id=$order_id;
		if($otype=='1')
			return $this->generalOrder($type);
		else
			return $this->rebateOrder($type);
		return false;
	}

	//去掉普通订单库存
	public function generalOrder($type){
		//$order = M('order')->where(array('o_id'=>$this->order_id))->find();
		//if($order['o_pstatus']!=1)return false;
		$order_data = M('goods_snapshot')->where(array('sp_oid'=>$this->order_id))->select();
		if($order_data)foreach($order_data as $key => $value){
			$this->where(array('gid'=>$value['sp_gid'],'gstock'=>array('gt',0)))->$type('gstock',$value['sp_number']);
		}
		return true;
	}


	//去掉返现订单库存
	public function rebateOrder($type){
		//$order = M('fl_order')->where(array('flo_id'=>$this->order_id))->find();
		//if($order['flo_pstatus']!=1)return false;
		$order_data = M('fl_gsnapshot')->where(array('flg_oid'=>$this->order_id))->select();
		if($order_data)foreach($order_data as $key => $value){
			$this->where(array('gid'=>$value['flg_gid'],'gstock'=>array('gt',0)))->$type('gstock',$value['flg_number']);
		}
		return true;
	}

	//获取订单信息
	public function getGoods($jid,$condition=array(),$num=4,$order='g.gorder'){
		$shop = M("shop");
		$sid = $shop->where(array('jid' => $jid,'status' => '1'))->getField('sid');
		$where = array('g.gtype' => 0,'g.gstatus' => 1,'g.gstock' => array('neq', 0),'c.ctype' => 1,'c.status' => 1,);
		if($sid)$where['g.sid'] = array('in',$sid);
		if($condition)$where = array_merge($where,$condition);
		
		$goods_list = $this->alias('g')->join('azd_class c on g.cid=c.cid')->where($where)->order($order)->limit($num)->select();
		return $goods_list;
	}

}

?>