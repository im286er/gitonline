<?php
namespace Demo\Model;
use Think\Model;

class ShopModel extends Model {
	protected $_validate = array(
        array('jid', '/^[1-9]\d*$/', '所属商家不能为空', 1, 'regex'),
        array('sname', '1,50', '分店名称不能为空', 1, 'length'),
        array('scontactsname', '1,20', '分店联系人名称不能为空', 1, 'length'),
        array('scontactstel', '/^(\+?86-?)?(18|15|13)[0-9]{9}$|^(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$|^400(-\d{3,4}){2}$/', '分店联系人电话不能为空', 1, 'regex'),
        array('mservetel', '/^(\+?86-?)?(18|15|13)[0-9]{9}$|^(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$|^400(-\d{3,4}){2}$/', '分店服务电话不能为空', 1, 'regex'),
        array('msaletel', '/^(\+?86-?)?(18|15|13)[0-9]{9}$|^(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$|^400(-\d{3,4}){2}$/', '分店销售电话不能为空', 1, 'regex'),
    );

	public function insert($data='', $options=array(), $replace=false) {
        $data = $this->create($data, 1);
        if( !$data ) return false;
        return $this->add($data, $options, $replace); 
    }
	
	public function update($data='', $options=array()) {
		$data = $this->create($data, 2);
		if( !$data ) return false;
		return $this->save($data, $options);	
	}
}

?>