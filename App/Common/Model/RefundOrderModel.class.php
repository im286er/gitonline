<?php
namespace Common\Model;
use Think\Model;

class RefundOrderModel extends Model {

	/**创建退款订单**/
	public function createRefund($data){
		$batch_no = $this->max('batch_no');
		$datano = msubstr($batch_no,0,8,'utf-8',false);
		if(date('Ymd')==$datano){
			$batch_no = $batch_no+1;
		}else{
			$batch_no = date('Ymd').'00000001';
		}
		$refund = array();
		$refund['jid'] = $data['jid']?$data['jid']:'0';
		$refund['o_id'] = $data['o_id'];
		$refund['pay_type'] = $data['pay_type'];
		$refund['order_type'] = $data['order_type'];
		$refund['batch_no'] = $batch_no;
		$refund['cause'] = $data['cause'];
		$refund['batch_num'] = $data['batch_num'];
		$refund['pay_trade_no'] = $data['pay_trade_no'];
		$refund['money'] = $data['money'];

		$this->data($refund)->add();
		return $batch_no;
	}

	/**检测退款订单**/
	public function verifyRefundOrder($data=array()){
		if(!$data['o_id'] && !$data['order_type'])return false;
		$refundorder = $this->where(array('o_id'=>$data['o_id'],'order_type'=>$data['order_type']))->find();
		if($refundorder){
			$this->where(array('o_id'=>$data['o_id'],'order_type'=>$data['order_type']))->setField('cause',$data['cause']);
			return $refundorder['batch_no'];
		}else{
			return $this->createRefund($data);
		}
	}

	/**退款严格按照支付单号为单位**/
	public function alipayrefundAnalyze($detail){
		$data = array();
		$refunddata = explode('$',$detail);
		if(count($refunddata) > 1){
			$result_text = array();
			/***退款部分***/
			$refundinfo = explode("^",$refunddata[0]);//退款详情
			$data['pay_trade_no'] = $refundinfo['0'];
			$result_text['0'] = '支付交易号：'.$refundinfo['0'];
			$result_text['1'] = '退款金额：'.$refundinfo['1'];
			if($refundinfo['2']=='SUCCESS'){
				$data['status'] = '1';
				$result_text['2'] = '退款成功';
			}else{
				$result_text['2'] = '退款失败；原因：'.$refundinfo['2'];
				$data['status'] = '0';
			}
			/***退款部分***/
			/***退手续费部分***/
			$procedureinfo = explode("^",$refunddata[1]);//手续费详情
			$result_text['4'] = '退费账号：'.$procedureinfo['0'];
			$result_text['5'] = '退费账户ID'.$procedureinfo['1'];
			$result_text['6'] = '退款手续费金额：'.$procedureinfo['2'];
			if($procedureinfo['3']=='SUCCESS'){
				$result_text['7'] = '退款手续费成功';
			}else{
				$result_text['7'] = '退款手续费失败；原因：'.$procedureinfo['2'];
			}
			/***退手续费部分***/
			$data['money'] = $refundinfo['1']+$procedureinfo['2'];
			ksort($result_text);
			$data['result_details'] = implode(';',$result_text);
		}else{
			$result_text = array();
			$refundinfo = explode("^",$refunddata[0]);//退款详情
			$data['pay_trade_no'] = $refundinfo['0'];
			$result_text['0'] = '支付交易号：'.$refundinfo['0'];
			$result_text['1'] = '退款金额：'.$refundinfo['1'];
			if($refundinfo['2']=='SUCCESS'){
				$data['status'] = '1';
				$result_text['2'] = '退款成功';
			}else{
				$result_text['2'] = '退款失败；原因：'.$refundinfo['2'];
				$data['status'] = '0';
			}
			$data['money'] = $refundinfo['1'];
			ksort($result_text);
			$data['result_details'] = implode(';',$result_text);
		}	
		return $data;
	}

}

?>