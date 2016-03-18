<?php
namespace Common\Model;
use Think\Model;

class AppCategoryModel extends Model {

	public function deleteAppCategory( $vid='' ) {
		static $appCategoryIds = array();
		if( !$vid ) return true; array_push($appCategoryIds, $vid);
		$this->where( array('id'=>$vid) )->delete();
		$childAppCategoryList = $this->where(array('pid'=>$vid))->select();
		if(is_array($childAppCategoryList) && !empty($childAppCategoryList)) {
			foreach($childAppCategoryList as $child) $this->deleteAppCategory($child['id']);
		}
		return $appCategoryIds;	
	}
	
	

}

?>