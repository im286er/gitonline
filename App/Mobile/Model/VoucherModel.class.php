<?php
namespace Mobile\Model;
use Think\Model;

class VoucherModel extends Model {

	protected $_validate = array(
    );

	//强制更新
	public function getVoucher($jid,$condition=array(),$num=3,$order='v.vu_id desc') {
		if(!$jid)return false;
		$data = array();
		$where = array('v.vu_jid' => $jid,'v.vu_status' => 1,'v.vu_etime' => array('egt',date("Y-m-d H:i:s")), );
		if($condition)$where = array_merge($where,$condition);
		$data = $this->alias('v')->field('v.*, (v.vu_cum - (SELECT count(*) FROM azd_voucher_user where vu_id=v.vu_id)) as vu_sum')->where($where)->having('vu_sum>0')->order($order)->limit( $num )->select();
		return $data;
	}

	public function getModule($jid){
		if(!$jid)return false;
		$path = APP_DIR.'/Public/Data/'.$jid.'/';
		$VoucherModule = array('Name'=>'优惠券','Icon'=>'');
		file_exists($path.'VoucherModule.php') && $VoucherModule=unserialize(file_get_contents($path.'VoucherModule.php'));
		$VoucherModule['Link'] = U('Coupon/index@yd', array('jid'=>$jid));
		return $VoucherModule;
	}

}

?>