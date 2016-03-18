<?php
namespace Common\Model;
use Think\Model;

class PayLogModel extends Model {
	public $defult_config = array(
		'alipay_no' => '2088911647217055',
		'alipay_key' => '3xc76qzgfa8v87afldkox5ffzv5qvtek',
		'alipay_email' => 'dishuos@azding.com',
	);
	public $alipay_config = array();
	
	public function config($config){
		$this->alipay_config = array(
			'partner' => $config['alipay_no'],
			'key' => $config['alipay_key'],
			'email' => $config['alipay_email'],
			'sign_type' => strtoupper('MD5'),
			'input_charset' => strtolower('utf-8'),
			'cacert' => getcwd().'\\cacert.pem',
			'transport' => 'http',
		);
	}

	public function parameter($payinfo=array(),$detail_data=array()){
		$refund = array();
		$refund['jid'] = $payinfo['jid'];
		$refund['o_id'] = $payinfo['oid'];
		$refund['pay_type'] = $payinfo['pay_type'];
		$refund['order_type'] = $payinfo['order_type'];
		$refund['batch_num'] = 1;
		$refund['pay_trade_no'] = $payinfo['pay_trade_no'];
		$refund['cause'] = $detail_data['cause'];
		$RefundOrderModel = new \Common\Model\RefundOrderModel();
		
		$batch_no = $RefundOrderModel->verifyRefundOrder($refund);
		if(!$batch_no)return false;
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "refund_fastpay_by_platform_pwd",
				"partner" => $this->alipay_config['partner'],
				"notify_url"	=> U('Scene/alipayrefund@sj'), //异步处理地址
				"seller_email"	=> $this->alipay_config['email'],
				"refund_date"	=> date('Y-m-d H:i:s'),
				"batch_no"	=> $batch_no,
				"batch_num"	=> 1,//批量退款数量
				"detail_data"	=> implode('^',$detail_data),
				"_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
		);
		return $parameter;
	}

	public function verifySetConfig($jid){
		$merchant = M('merchant_extend')->where(array('jid'=>$jid))->find();
		if(!$merchant['alipay_no']  || !$merchant['alipay_key'] || !$merchant['alipay_email'])return false;
		$this->config($merchant);
		return true;
	}

	/***启动退款***/
	public function promptlyRefund($refundData=array()){
		$detail_data = array();
		if(!$refundData['o_id'])return false;
		if($refundData['order_type'] == 2){
			$payinfo = M('fl_paylog')->where(array('pay_oid'=>$refundData['o_id']))->find();
		}else{
			$payinfo = $this->where(array('oid'=>$refundData['o_id']))->find();
		}
		if(!$payinfo)return false;
		if($refundData['order_type'] == 2){
			$result = $this->config($this->defult_config);
		}else{
			if($payinfo['pay_type']==1){
				$result = $this->verifySetConfig($payinfo['jid']);
				if(!$result)return false;
			}else{
				$result = $this->config($this->defult_config);
			}
		}
		$detail_data['pay_trade_no'] = $payinfo['pay_trade_no'];
		if($refundData['money'] && $refundData['money'] > $payinfo['pay_price']){
			return false;//退款金额大于订单金额
		}elseif(!$refundData['money']){
			$refundData['money'] = $payinfo['pay_price'];//退款金额为空时，全额退款
		}
		$detail_data['money'] = $refundData['money'];
		if(!$refundData['cause'])$refundData['cause'] = '协商退款';
		$detail_data['cause'] = $refundData['cause'];
		$payinfo['order_type'] = $refundData['order_type'];
		$result = $this->parameter($payinfo,$detail_data);
		return $result;
	}
}

?>