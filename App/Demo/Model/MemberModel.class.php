<?php
namespace Demo\Model;
use Think\Model;

class MemberModel extends Model {
	protected $_validate = array(
        array('mname', '1,50', '账户长度格式不正确', 1, 'length'),
        array('mname', '', '帐号名称已经存在！', 1, 'unique'),
        array('mpwd', '1,50', '密码长度格式不正确', 1, 'length'),
    );
	
	public function insert($data='', $options=array(), $replace=false) {
		$data = $this->create($data, 1);
		if( !$data ) return false;
		return $this->add($data, $options, $replace); 
	}

}

?>