<?php
namespace Merchant\Controller;

class RefundController extends MerchantController {
	public function index(){
		header("Content-type: text/html; charset=utf-8");
		$o_id = I('get.o_id');//发起退款的订单号，必须提交
		$money = I('get.money');//发起退款的退款金额，不能超过支付金额，可以不填写
		$cause = I('get.cause');//退款原因，可以不填写
		if(!$o_id)die('error,o_id is empty');
		$refundData = array('o_id'=>$o_id,'money'=>$money,'cause'=>$cause);
		$parameter = D('PayLog')->promptlyRefund($refundData);
		$refund = new \Org\Util\pay\alipayrefund(D('PayLog')->alipay_config);
		$refund->buildRequestForm($parameter);
	}


	public function flrefund(){
		header("Content-type: text/html; charset=utf-8");
		$o_id = I('get.o_id');//发起退款的订单号，必须提交
		$money = I('get.money');//发起退款的退款金额，不能超过支付金额，可以不填写
		$cause = I('get.cause');//退款原因，可以不填写
		if(!$o_id)die('error,o_id is empty');
		$refundData = array('o_id'=>$o_id,'money'=>$money,'cause'=>$cause);
		$parameter = D('FlPaylog')->promptlyRefund($refundData);
		$refund = new \Org\Util\pay\alipayrefund(D('FlPaylog')->alipay_config);
		$refund->buildRequestForm($parameter);
	}


}