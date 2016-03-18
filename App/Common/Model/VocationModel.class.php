<?php
namespace Common\Model;
use Think\Model;

class VocationModel extends Model {

	public function deleteVocation( $vid='' ) {
		static $vocationIds = array();
		if( !$vid ) return true; array_push($vocationIds, $vid);
		$this->where( array('v_id'=>$vid) )->delete();
		$childVocationsList = $this->where(array('v_pid'=>$vid))->select();
		if(is_array($childVocationsList) && !empty($childVocationsList)) {
			foreach($childVocationsList as $child) $this->deleteVocation($child['v_id']);
		}
		return $vocationIds;	
	}
	
	

}

?>