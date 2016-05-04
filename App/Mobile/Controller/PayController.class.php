<?php
namespace Mobile\Controller;
use Think\Controller;

class PayController extends Controller {
	
	public function order_success(){
		
	}
	
	/** *请求支付宝 * */
	public function request_alipay(){
		if(I('get.jump')){
			cookie('payjump',I('get.jump'));
		}else{
			cookie('payjump',null);
		}
		if(I('get.o_id'))$o_id = I('get.o_id');
		$opt = array('o_id'=>$o_id,'o_pstatus'=> 0,'o_price'   => array('gt',0));
		$order_info = M('order')->where($opt)->find();
		if(empty($order_info)){
			$this->error('支付失败',U('User/myorder',array('jid'=>$this->jid,'jump'=>cookie('payjump') )));
			//exit('无可支付订单');
		}
		/*根据平台载入不同接口*/
		$Mobile_Detect = new \Org\Util\Mobile_Detect();
		if($Mobile_Detect->isMobile()){
			$pay_type = 'alipaywap';
		}else{
			$pay_type = 'alipaypc';
		}
		/*验证支付方式有效性*/
		if(!in_array($pay_type, C('PAY_TYPE'))){exit('支付方式不存在');}
		$cl_name = "\\Org\\Util\\pay\\".$pay_type;
		$pay = new $cl_name();
		/** 这里查询商户是否有自己的账户，有就更新收款账户信息,同时更新order表o_type字段**/
		$extend = M('merchant_extend')->where(array('alipay_status'=>1,'jid'=>$order_info['o_jid']))->find($order_info['o_jid']);
		if($extend['alipay_no'] && $extend['alipay_email'] && $extend['alipay_key'] && $extend['alipay_status']==1){
			$pay->partner = $extend['alipay_no'];//支付宝商户号
			$pay->seller_email = $extend['alipay_email'];//支付宝商户号
			$pay->key = $extend['alipay_key'];//支付宝密匙
			D('Order')->where(array('o_id'=>$o_id))->setField('o_type','1');
		}else{
			D('Order')->where(array('o_id'=>$o_id))->setField('o_type','2');
		}
		$pay->notify_url = U('Pay/notify@yd',array('type'=>$pay_type,'jid'=>$order_info['o_jid']),false);
		$pay->call_back_url = U('Pay/call_back@yd',array('type'=>$pay_type,'jid'=>$order_info['o_jid']),false);
		$pay->return_url = $pay_type=='alipaypc'?$pay->call_back_url:U('Pay/merchant@yd',array('type'=>$pay_type,'jid'=>$order_info['o_jid']),false);
		//print_r($pay);exit;
		/** 结束**/
		$mnickname = M('merchant')->where(array('jid'=>$order_info['o_jid']))->getField('mnickname');
		$gtype = D('Order')->runGtype();
		$order_info['o_title'] = ($gtype[$order_info['o_gtype']]?$gtype[$order_info['o_gtype']]:'在线点餐').'-'.$mnickname;
		//print_r($pay);exit;
		$pay->buildRequest($order_info);
	}
	
	
	/***服务器异步通知页面* */
	public function notify(){
		//计算得出通知验证结果
		$pay_type = I('get.type');
		/*验证支付方式有效性*/
		if(!in_array($pay_type, C('PAY_TYPE'))){echo '支付方式不存在';exit;}
		$cl_name = "\\Org\\Util\\pay\\".$pay_type;
		$pay = new $cl_name();
		$payinfo = $pay->get_pay_info();
		$order_info = M('order')->where(array('o_id'=>$payinfo['out_trade_no']))->find();
		if(!$order_info)exit('支付订单不存在');
		$extend = M('merchant_extend')->where(array('alipay_status'=>'1','jid'=>$order_info['o_jid']))->find();
		if($extend['alipay_no'] && $extend['alipay_email'] && $extend['alipay_key'] && $extend['alipay_status']==1){
			$pay->partner = $extend['alipay_no'];//支付宝商户号
			$pay->seller_email = $extend['alipay_email'];//支付宝商户号
			$pay->key = $extend['alipay_key'];//支付宝密匙
		}
		
		$verify_result = $pay->verifyNotify();
		if($verify_result) {//验证成功
				$trade_status = I('post.trade_status');
				$pay->success();
				($trade_status=='TRADE_SUCCESS' || $trade_status=='TRADE_FINISHED') or die('success');//交易成功，否则直接退回，返回接受数据成功
				if($pay_type=='alipaypc'){
					$pay_info['pay_type'] = $pay_type;
					$pay_info['out_trade_no'] = $payinfo['out_trade_no'];
					$pay_info['trade_no'] = $payinfo['trade_no'];
					$result = $this->pay_success($pay_info);//支付成功处理
					if($result) {
						$pay->success();
					}else{
						$pay->fail();
					}
				}elseif($pay_type=='alipaywap'){
					$pay_info = $pay->get_pay_info();
					$pay_info['pay_type'] = $pay_type;
					if($pay->is_success()) {
						$this->pay_success($pay_info);
						$pay->success();
					}else{
						$pay->fail();
					}
				}
		}else {
				//验证失败
				$pay->fail();
				//调试用，写文本函数记录程序运行情况是否正常
				//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}
	
	/**页面跳转同步通知页面** */
	public function call_back(){
		//计算得出通知验证结果
		$pay_type = I('type');
		$get = I('get.');
		/*验证支付方式有效性*/
		if(!in_array($pay_type, C('PAY_TYPE')))exit('支付方式不存在');
		$cl_name = "\\Org\\Util\\pay\\".$pay_type;
		$pay = new $cl_name();
		$order_info = M('order')->where(array('o_id'=>$get['out_trade_no']))->find();
		if(!$order_info)exit('支付订单不存在');
		$extend = M('merchant_extend')->where(array('alipay_status'=>'1','jid'=>$order_info['o_jid']))->find();
		if($extend['alipay_no'] && $extend['alipay_email'] && $extend['alipay_key'] && $extend['alipay_status']==1){
			$pay->partner = $extend['alipay_no'];//支付宝商户号
			$pay->seller_email = $extend['alipay_email'];//支付宝商户号
			$pay->key = $extend['alipay_key'];//支付宝密匙
		}
		unset($get['jid'],$get['type']);
		$verify_result = $pay->verifyReturn($get);
		if($verify_result) {//验证成功
			
			$pay_info = array();
			$pay_info['pay_type'] = $pay_type;
			$pay_info['trade_no'] = $get['trade_no'];
			$pay_info['out_trade_no'] = $get['out_trade_no'];
			$trade_status = $get['trade_status'];
			//($trade_status=='TRADE_SUCCESS' || $trade_status=='TRADE_FINISHED') or die('success');//交易成功，否则直接退回，返回接受数据成功
			if($pay_type=='alipaypc'){
				$result = $this->pay_success($pay_info);//支付成功处理
				if($result) {
					$this->success('支付成功',U('User/myorder',array('jid'=>$this->jid,'jump'=>cookie('payjump') )));
				}else{
					$this->error('支付失败',U('User/myorder',array('jid'=>$this->jid,'jump'=>cookie('payjump') )));
				}
			}elseif($pay_type=='alipaywap'){
				if($get['result']=='success') {
					$re = $this->pay_success($pay_info);//支付成功处理
					$this->success('支付成功',U('User/myorder',array('jid'=>$this->jid,'jump'=>cookie('payjump') )));
				}else{
					$this->error('支付失败',U('User/myorder',array('jid'=>$this->jid,'jump'=>cookie('payjump') )));
				}
			}
		} else {
			//验证失败
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
			$this->error('验证失败，请联系客服处理，谢谢！',U('User/myorder',array('jid'=>$this->jid,'jump'=>cookie('payjump') )));
		}
	}
	
	/**支付成功后处理**/
	public function pay_success($pay_info){
		$order_info = M('order')->where(array('o_id'=>$pay_info['out_trade_no']))->find();
		if(!$order_info)return false;
		if($order_info['o_pstatus']>0)return true;
		$orderdata = array('o_pstatus' => '1','o_dstatus'=>3,'o_pstime'=>date("Y-m-d H:i:s"));
		M('order')->where(array('o_id'=>$pay_info['out_trade_no']))->save($orderdata);
		if($order_info['o_pstatus'] == '0'){
			$logdata =array();
			$logdata['oid'] = $order_info['o_id'];
			$logdata['jid'] = $order_info['o_jid'];
			$logdata['uid'] = $order_info['o_uid'];
			$logdata['gtype'] = $order_info['o_gtype'];
			$logdata['pay_price'] = $order_info['o_price'];
			$logdata['pay_time'] = date('Y-m-d H:i:s');
			$logdata['pay_status'] = '1';
			$logdata['pay_way'] = $pay_info['pay_type'];
			$logdata['pay_trade_no'] = $pay_info['trade_no'];
			$logdata['pay_type'] = $order_info['o_type'];
			$result = M('pay_log')->add($logdata);
			if($result && $order_info['o_type']==2){//如果支付款是汇入系统账户，更新商家的账户余额
				$merchant = M('merchant')->where(array('jid'=>$order_info['o_jid']))->find();
				M('member')->where(array('mid'=>$merchant['mid']))->setInc('money',$order_info['o_price']); 
			}
			D('Order')->where(array('o_id'=>$order_info['o_id']))->setField(array('o_seat'=>2));
			
				return true;
			}else return false;
	}
			
	//支付取消中断
	public function merchant(){
		$this->error('支付失败',U('User/myorder',array('jid'=>$this->jid,'jump'=>cookie('payjump') )));
	}




	
	


}