<?php
/**
 * 作用：在商家的 在线消费、远程预订、本地视频..中，都会有根据 CID 来调用商家的商品信息，
 * 此行为的作用就是 判断 CID 是不是属于当前商家，防止商家在 URL 地址中，修改 CID 来查看别家的商品
 */

namespace Common\Behavior;

class CreateVocationBehavior {
	
	public function run( &$ReturnVocationList ) {
		$vocationListArray = $vocationList = array();

        $vocationList = M('vocation')->order('v_id Desc')->select();
        if( !is_array($vocationList) || empty($vocationList) ) $vocationList = array();

        foreach( $vocationList as $vocation ) {
            $vocationListArray[$vocation['v_id']] = $vocation;
        }

        F('VocationList', $vocationListArray);
        $ReturnVocationList = $vocationListArray;
	}
		
}