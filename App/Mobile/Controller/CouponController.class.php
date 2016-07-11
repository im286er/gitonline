<?php
namespace Mobile\Controller;
/*优惠券控制器
 *
*
* */

class CouponController extends MobileController {
	
	public $action_name = 'Coupon';
	
	/*优惠券列表
	 *
	*
	* */
	public function index(){
				
		$coupon = M('voucher');
		$opt = array(
				'v.vu_jid'    => $this->jid,
				'v.vu_sid'   => $this->sid,
				'v.vu_status' => 1,
				'v.vu_etime' => array('egt',date("Y-m-d H:i:s")),
		);
		$coupon_list = $coupon->alias('v')->field('v.*, (v.vu_cum - (SELECT count(*) FROM azd_voucher_user where vu_id=v.vu_id)) as vu_sum')->where($opt)->having('vu_sum>0')->order('v.vu_id desc')->select();
		
		
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'VoucherModule.php') && $VoucherModule=unserialize(file_get_contents($path.'VoucherModule.php'));
		$this->assign('page_name', $VoucherModule ? $VoucherModule['Name'] : '优惠券');
		
		$this->assign('page_url',U('Index/index',array('jid'=>$this->jid)));
		
		$this->assign('coupon_list',$coupon_list );
		$this->mydisplay();
	}
	
	
	/*优惠券详情
	 **/
	public function info(){
		$vu_id = I('vu_id');

		$linkurl = U('User/login',array('jid'=>$this->jid,'backurl'=>url_param_encrypt(U('Mobile/Coupon/info',array('jid'=>$this->jid,'vu_id'=>$vu_id)),'E'),'returnurl'=>url_param_encrypt(U('Mobile/Coupon/info',array('jid'=>$this->jid,'vu_id'=>$vu_id)),'E')));
		$this->assign('linkurl',$linkurl);
		
		$coupon = M('voucher');
		$opt = array(
				'vu_id'    => $vu_id,
		);
		$coupon_info = $coupon->where($opt)->find();
		
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'VoucherModule.php') && $VoucherModule=unserialize(file_get_contents($path.'VoucherModule.php'));
		$this->assign('page_name', $VoucherModule ? $VoucherModule['Name'] : '优惠券');
		
		$page_url =  U('Index/new2Coupon',array('jid'=>$this->jid, 'sid'=>$this->sid,'cid'=>$coupon_info['vu_cid']));
		$this->assign('page_url',$page_url);
		
		$this->assign('coupon_info', $coupon_info);
		$this->assign('count_num', M('voucherUser')->where("vu_id=".$vu_id)->count());
		$this->mydisplay();
	}
	
	/*优惠券领取
	 *
	*
	* */
	public function getCoupon(){
		$vu_id = I('vu_id');
		
		$coupon = M('voucher');
		$opt = array(
				'vu_id'    => $vu_id,
				'vu_status' => 1
		);
		
		$msg = "领取成功";
		$coupon_info = $coupon->where($opt)->find();
		
		if(!$this->mid){
			$data = array(
					'msg' => '请先登录',
			);
			$this->ajaxReturn($data);
		}
		if(time() < strtotime($coupon_info["vu_stime"]) ){
			$msg = "优惠券开始领取时间未到";
			$data = array(
					'msg' => $msg,
			);
			$this->ajaxReturn($data);
		
		}elseif(time() > strtotime($coupon_info["vu_etime"])){
			$msg = "优惠券已过期";
			$data = array(
					'msg' => $msg,
			);
			$this->ajaxReturn($data);
		}else{
			$opt = array(
					'vu_id' => $vu_id
			);
			$count = M('voucher_user')->where($opt)->count();
			if($count >= $coupon_info["vu_cum"]){
				$msg = "优惠券已领完";
				$data = array(
						'msg' => $msg,
				);
				$this->ajaxReturn($data);
			}else{
				if($coupon_info["vu_num"] > 0){
					$opt = array(
							'vu_id' => $vu_id,
							'mid'  => $this->mid
					);
					$count_u = M('voucher_user')->where($opt)->count();
					if($count_u >= $coupon_info["vu_num"]){
						$msg = "您已经领取过了";
						$data = array(
								'msg' => $msg,
						);
						$this->ajaxReturn($data);
					}
				}
			}
		}
		
		$opt = array(
				'vu_id' => $vu_id,
				'mid'  => $this->mid,
				'vu_price' => $coupon_info["vu_price"]
		);
		M('voucher_user')->add($opt);
		
		$data = array(
				'msg' => $msg,
		);
		$this->ajaxReturn($data);
		
	}
}