<?php
namespace Common\Model;
use Think\Model;

class MerchantModel extends Model {
	protected $_validate = array(
		array('mid', '/^[1-9]\d*$/', '商家账户信息出错', 1, 'regex', 1),
		array('vid', '/^[1-9]\d*$/', '商家所属行业出错', 1, 'regex'),
		array('mnickname', '1,100', '商家全称出错', 1, 'length'),
		array('magent', '/^[1-9]\d*$/', '商家所属代理商出错', 1, 'regex'),
		array('mlpname', '1,100', '商家法人名称出错', 1, 'length'),
		array('mlptel', '/^(\+?86-?)?(18|15|13|17)[0-9]{9}$|^(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$|^400(-\d{3,4}){2}$/', '商家法人电话出错', 1, 'regex'),
		array('mcertificates', 'cehckImages', '商家证件出错', 1, 'callback'),
		array('mcity', '/^[1-9]\d*$/', '商家所在城市出错', 1, 'regex'),
	);

	public function deleteMerchant( $jid, $status ) {
		$status = $status == 1 ? 0 : 1;
		$s = $this->query("UPDATE `{$this->tablePrefix}merchant`,`{$this->tablePrefix}member` SET `{$this->tablePrefix}member`.mstatus={$status} WHERE `{$this->tablePrefix}merchant`.mid=`{$this->tablePrefix}member`.mid AND `{$this->tablePrefix}merchant`.jid in ($jid)");
		return $s !== false ? true : false;
	}
	
	public function cehckImages($args) {
		$acertificates = unserialize($args);
		if(!is_array($acertificates)) return false;
		if( isset($acertificates['sfzz']) && !empty($acertificates['sfzz']) && isset($acertificates['sfzb']) && !empty($acertificates['sfzb']) ) {
			return true; 
		} else { return false; }
	}
	
	public function insert($data='', $options=array(), $replace=false) {
		$data = $this->create($data, 1);
		if( !$data ) return false;
		return $this->add($data, $options, $replace); 
	}

	public function update($data='',$options=array()) {
		$data = $this->create($data, 2);
		if( !$data ) return false;
		return $this->save($data);
	}
}
?>