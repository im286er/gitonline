<?php
namespace Common\Model;
use Think\Model;

class AuthRuleModel extends Model {

	public function deleteRule( $id='' ) {
		static $authRuleIds = array();
		if( !$id ) return true; array_push($authRuleIds, $id);
		$this->where( array('id'=>$id) )->delete();
		$childRulesList = $this->where(array('pid'=>$id))->select();
		if(is_array($childRulesList) && !empty($childRulesList)) {
			foreach($childRulesList as $child) $this->deleteRule($child['id']);
		}
		return $authRuleIds;	
	}
	
	

}

?>