<?php
namespace Merchant\Model;
use Think\Model;

class AuthModel extends Model {
	//获取某个商户账号的菜单
	public function getUserMenu($mid){
		/*
		$a = array(
			'top'=>array('shop','renovation','order','finance','member','wifi','account'),
			'next'=>array('shop1','shop2')
		);
		echo serialize($a);exit;
		*/
		
		$my_auth = M('merchant_user')->where(array('tmid'=>$mid))->find();
		$menu = C('TOP_MENU');
		if($my_auth['role'] == 1){//超级管理员
			return $menu;
		}
		$my_auth = unserialize($my_auth['auth']);
		foreach($menu as $k=>$v){
			if(!in_array($v['code'], $my_auth['top'])){
				unset($menu[$k]);
			}else{
				foreach($v['next'] as $k2=>$v2){
					if(!in_array($v2['id'], $my_auth['next'])){
						unset($menu[$k]['next'][$k2]);
					}
				}
			}
		}
		return $menu;
	}
	
	//获取当前账户的商品列表
	public function getAuthShops($mid){
		$shopauth = M('merchant_user')->where(array('tmid'=>$mid))->getField('shopauth');
		$r = array();
		if($shopauth){
			$r = M('shop')->where(array('status'=>'1','sid'=>array('in',$shopauth)))->getField('sid,sname');
		}
		return $r;
	}
}
?>