<?php
namespace Merchant\Model;
use Think\Model;

class ServiceModel extends Model {
	public function insertShopData($jid,$sid){
		//id小于6的模块生成演示栏目
		$mod = M('module')->where(array('module_status'=>1,'id'=>array('lt',6)))->field('module_sign,default_img,default_name')->select();
		foreach($mod as $k=>$v){
			$info = array(
				'jid' => $jid,
				'sid' => $sid,
				'cname' => $v['default_name'],
				'cimg' => $v['default_img'],
				'model' => $v['module_sign'],
				'corder' => $k,
				'status' => 1,
			);
			M('category')->add($info);//插入分类
		}
		
		$info2 = array(
				'jid' => $jid,
				'sid' => $sid,
				'cname' => '新品推荐',
				'cimg' =>  '/Public/Merchant/images/ico_menu1.png',
				'model' => 'goods',
				'corder' => 99,
				'status' => 1,
		);
		$cid = M('category')->add($info2);
		
		$info3 = array(
			'g_num' => 4,
			'g_cid' => $cid,
			'g_sort' => 1,
			'g_jid' => $jid,
			'g_sid' => $sid,
			'g_date' => date("Y-m-d H:i:s"),
		);
		M('ghome')->add($info3);
		
		for($i=0;$i<3;$i++){
			$info4 = array(
				'gname' => '演示商品',
				'gdescription' => '这是系统生成的演示商品,请勿购买', 
				'cid' => $cid,
				'sid' => $sid,
				'goprice' => '99',
				'gdprice' => '88',
				'gstock' => '100',
				'gimg' => '/Public/Merchant/images/pro_1.jpg',
				'gtype' => 0,
				'gstatus' => 1,
			);
			M('goods')->add($info4);
		}
		
		return true;
	}
}
?>