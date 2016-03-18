<?php
namespace Common\Model;
use Think\Model;

class AppModel extends Model {

	//同步
	//$appid:同步的AppId
	//$sync:同步的条件
	public function sync($appid,$sync){
		if(!$appid)return false;
		if($sync==1){
			$list = $dataList = array();
			$list = M('merchant_extend')->where(array('app_down'=>'1'))->getField('jid',true);
			if($list)foreach($list as $value){
				$dataList[] = array('jid'=>$value,'appid'=>$appid,'status'=>'1');
			}
			M('app_merchant')->addAll($dataList);
			$this->where(array('id'=>$appid))->setField('sync','1');//更新操作为同步成功
			return true;
		}
	}
	
	//取消同步
	//$appid:同步的AppId
	//$sync:取消同步前的Id
	public function cancel_sync($appid,$sync){
		if(!$appid)return false;
		if($sync==1){
			$result = M('app_merchant')->where(array('appid'=>$appid))->delete();
			$this->where(array('id'=>$appid))->setField('sync','0');//更新操作为同步成功
			return true;
		}
	}

	//给商家同步
	public function merchant_sync($jid){
		if(!$jid)return false;
		if($this->open_app_sync($jid)==false)return false;
		$list = $dataList = array();
		$list = $this->field('id,status')->where(array('status'=>array('in','1,2')))->select();
		if($list)foreach($list as $value){
			$dataList[] = array('jid'=>$jid,'appid'=>$value['id'],'status'=>$value['status']);
		}
		M('app_merchant')->addAll($dataList);
		return $list?true:false;
	}
	//给商家开启同步
	public function open_app_sync($jid){
		if(!$jid)return false;
		if(M('merchant_extend')->where(array('jid'=>$jid))->find())
			$result = M('merchant_extend')->where(array('jid'=>$jid))->setField('app_down','1');//更新操作为同步成功
		else
			$result = M('merchant_extend')->data(array('jid'=>$jid,'app_down'=>'1'))->add();//更新操作为同步成功
		return $result;
	}


	//$jid:商家的Id
	//$appid:同步的AppId
	//$orders:排序
	public function updateorder($jid,$appid,$orders){
		if(!$jid && !is_numeric($appid) && !is_numeric($orders))return false;
		$result = M('app_merchant')->where(array('jid'=>$jid,'orders'=>$orders))->find();
		if($result){
			if($result['appid']==$appid)return false;
			M('app_merchant')->where(array('jid'=>$jid,'orders'=>array(array('egt',$orders),array('lt',9999),'and')))->setInc('orders');
		}
		M('app_merchant')->where(array('jid'=>$jid,'appid'=>$appid))->setField('orders',$orders);
		return true;
	}

}
?>