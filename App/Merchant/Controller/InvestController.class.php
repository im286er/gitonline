<?php
namespace Merchant\Controller;

class InvestController extends MerchantController {
	public function index(){
		$sid = I('sid','');
		if(IS_POST){
			if(empty($sid)){
				exit('请先选择店铺');
			}
			$info = array();
			$info['status'] = I('status','');
			$info['tz_type'] = I('tz_type','');
			$info['tz_goods'] = join(',',I('tz_goods',''));
			$info['money'] = I('money','');
			$info['fanli'] = I('fanli','');
			$info['set1'] = I('set1','');
			$info['set2'] = I('set2','');
			$info['set3'] = I('set3','');
			$info['time'] = I('time','');
			$info['sid']  = $sid;
			$s = M('touzi')->where(array('sid'=>$sid))->getField('id');
			if($s){
				M('touzi')->where(array('id'=>$s))->save($info);
			}else{
				M('touzi')->add($info);
			}
			exit('保存成功');
		}else{
			$tz_goods = array();
			if($sid > 0){
				$opt = array(
						'sid' => $sid,
						'gtype' => 0,
						'gstatus' => 1,
				);
				$tz_goods = M('goods')->where($opt)->field('gid,gname')->select();
			}
			$tz = M('touzi')->where(array('sid'=>$sid))->find();
			$shops = D('auth')->getAuthShops($this->mid);
			$this->assign('shops', $shops);
			$this->assign('tz_goods',$tz_goods);
			$this->assign('sid',$sid);
			$this->assign('tz',$tz);
			$this->display();
		}
	}
}