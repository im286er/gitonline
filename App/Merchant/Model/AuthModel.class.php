<?php
namespace Merchant\Model;
use Think\Model;

class AuthModel extends Model {
	//获取某个商户账号的菜单
	public function getUserMenu($mid){
		
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
	
	//获取当前账户的分店列表
	public function getAuthShops($mid){
		$where = $this->getAuthWhere($mid);
		$r = M('shop')->where($where)->getField('sid,sname');
		return $r;
	}
	
	//获取当前账户的分店列表查询条件
	public function getAuthWhere($mid){
		$my_auth = M('merchant_user')->where(array('tmid'=>$mid))->find();
		if($my_auth['role'] == 1){//超级管理员
			$r = array('status'=>'1','jid'=>$my_auth['tjid']);
		}else{
			$r = array('status'=>'1','sid'=>array('in',$my_auth['shopauth']),'jid'=>$my_auth['tjid']);
		}
		return $r;
	}
}
?>