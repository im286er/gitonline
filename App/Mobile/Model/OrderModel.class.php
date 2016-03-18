<?php
namespace Mobile\Model;
use Think\Model;

class OrderModel extends Model {

	protected $_validate = array(
    );

	//强制更新
	public function runGtype($force=false) {
		$gtype = array(
			'Choose'=>'在线订餐',
			'Seat'=>'远程预定',
		);
		return $gtype;
	}


}

?>