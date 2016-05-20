<?php
/**
 * 作用：在商家的 在线消费、远程预订、本地视频..中，都会有根据 CID 来调用商家的商品信息，
 * 此行为的作用就是 判断 CID 是不是属于当前商家，防止商家在 URL 地址中，修改 CID 来查看别家的商品
 */

namespace Common\Behavior;

class CheckMerchantCidBehavior {
	
	public function run( &$cid ) {

		// $jid = M('class')->where(array('cid'=>$cid))->getField('jid');
		// if(!$jid || \Common\Org\Cookie::get( C('USER_COOKIE_JID') ) != $jid) {
		// 	E('你无权查看当前页面'); 
		// }

		//$jid = M('class')->where(array('cid'=>$cid))->getField('jid');
		//if(!$jid || \Common\Org\Cookie::get( C('USER_COOKIE_JID') ) != $jid) {
			//E('你无权查看当前页面'); 
		//}
		return true;
	}
		
}