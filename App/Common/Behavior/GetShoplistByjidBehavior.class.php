<?php
namespace Common\Behavior;

class GetShoplistByjidBehavior {
	
	public function run( &$shoplist ) {
		$_shoplist = F('ShopList_'.\Common\Org\Cookie::get(C('USER_COOKIE_JID')));
		if( !is_array($_shoplist) || empty($_shoplist) ) {
			$_shop = M('shop')->where(array('status'=>'1', 'jid'=>\Common\Org\Cookie::get(C('USER_COOKIE_JID'))))->select();	
			foreach($_shop as $_k=>$_s) $shoplist[$_s['sid']] = $_s;
			F('ShopList_'.\Common\Org\Cookie::get(C('USER_COOKIE_JID')), $shoplist);
		}
		$shoplist = $_shoplist;
	}
		
}