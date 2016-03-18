<?php
namespace Merchant\Controller;
use Think\Controller;
class SceneController extends Controller {
	//分店列表
	public function link() {
		$linkurl = I('get.url');
		redirect($linkurl);
	}


	//支付宝退款处理异步通知
	public function alipayrefund(){
		$batch_no = I('post.batch_no');
		$refundorder = D('RefundOrder')->where(array('batch_no'=>$batch_no))->find();

		if($refundorder && $refundorder['status'] < 1 && $refundorder['order_type'] == '1'){ //如果是普通订单，使用此流程
			$PayLog = D('PayLog');
			if($refundorder['pay_type']==1){
				$PayLog->verifySetConfig($refundorder['jid']);
			}else{
				$PayLog->config($PayLog->defult_config);
			}
			$refund = new \Org\Util\pay\alipayrefund($PayLog->alipay_config);
			$verify_result = $refund->verifyNotify();

			/**
			 * 2015-08-13 修改内容，系统后台如果退款成功，也走此通知
			 */
			if($verify_result) {//验证成功
				$success_num = I('post.success_num');
				$result_details = I('post.result_details');
				$detailsdata = explode("#", $result_details);

				if(is_array($detailsdata) && !empty($detailsdata)) {
					foreach($detailsdata as $key => $value) {
						$data = array();
						$data =  D('RefundOrder')->alipayrefundAnalyze($value);
						$data['batch_no'] = $batch_no;
						$data['batch_num'] = $success_num;
						$data['refund_date'] = date('Y-m-d H:i:s');
						$data['status'] = "1";
						$status_01=D('RefundOrder')->where(array('batch_no'=>$batch_no, 'pay_trade_no'=>$data['pay_trade_no']))->setField($data); //处理结果
						$status_02=M()->query("UPDATE azd_order AS o INNER JOIN azd_refund_order AS r ON o.o_id=r.o_id set o.o_pstatus=2 where r.batch_no='".$batch_no."'");
						file_put_contents(APP_PATH."/test2.php", (int)$status_02);
					}
					echo "success";		//请不要修改或删除
				}
			} else {
					echo "fail";//验证失败
			}
		}elseif($refundorder['order_type'] == '2'){
		
			$FlPaylog = D('FlPaylog');
			$FlPaylog->config($FlPaylog->defult_config);
			$refund = new \Org\Util\pay\alipayrefund($FlPaylog->alipay_config);
			$verify_result = $refund->verifyNotify();
			if($verify_result) {//验证成功
			
					$success_num = I('post.success_num');
					$result_details = I('post.result_details');
					$detailsdata = explode("#",$result_details);
					if($detailsdata)foreach($detailsdata as $key => $value){
						$data = array();
						$data =  D('RefundOrder')->alipayrefundAnalyze($value);
						$data['batch_no'] = $batch_no;
						$data['batch_num'] = $success_num;
						$data['refund_date'] = date('Y-m-d H:i:s');
						$data['status'] = "1";
						D('RefundOrder')->where(array('batch_no'=>$batch_no,'pay_trade_no'=>$data['pay_trade_no'],'status'=>'0'))->setField($data);//处理结果
						M()->query("UPDATE azd_fl_order AS o INNER JOIN azd_refund_order AS r ON o.flo_id=r.o_id set o.flo_pstatus=2 where r.batch_no='".$batch_no."'");
						/***这里可以写其他方法***/

						/***这里可以写其他方法***/
					}
					echo "success";		//请不要修改或删除
			}else{
					echo "fail";//验证失败
			}
		}

	}

}