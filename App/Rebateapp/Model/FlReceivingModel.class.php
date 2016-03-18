<?php
namespace Rebateapp\Model;
use Think\Model;

class FlReceivingModel extends Model {
	public $expiration = 7200;

	protected $_validate = array(

    );
	public function setDefault($userid=null,$receivingid=null){
		if(!$userid && !$receivingid)return false;
		$this->where(array('flr_userid'=>$userid))->setField('flr_default','0');
		$this->where(array('flr_userid'=>$userid,'flr_default'=>$receivingid))->setField('flr_default','0');
		return true;
	}
}

?>