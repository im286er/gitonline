<?php
/**
 * 作用：在商家的 在线消费、远程预订、本地视频..中，都会有根据 CID 来调用商家的商品信息，
 * 此行为的作用就是 判断 CID 是不是属于当前商家，防止商家在 URL 地址中，修改 CID 来查看别家的商品
 */

namespace Common\Behavior;

class CreateAppCategoryBehavior {
	
	public function run( &$ReturnAppCategoryList ) {
		$appCategoryListArray = $appCategoryList = array();
        $appCategoryList = M('app_category')->order(' id Desc')->select();
        if( !is_array($appCategoryList) || empty($appCategoryList) ) $appCategoryList = array();
        foreach( $appCategoryList as $category ) {
            $appCategoryListArray[$category['id']] = $category;
        }
        F('appCategoryList', $appCategoryListArray);
        $ReturnAppCategoryList = $appCategoryListArray;
	}
		
}