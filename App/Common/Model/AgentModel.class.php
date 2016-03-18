<?php
namespace Common\Model;
use Think\Model;

class AgentModel extends Model {
	private $arank = '';
	protected $_validate = array(
		array('mid', '/^[1-9]\d*$/', '代理商账户信息出错', 1, 'regex'),
        array('aid', 'cehckAid', '代理区域不能为空', 1, 'callback'),
        array('anickname', '1,100', '代理商全称不能为空', 1, 'length'),
		array('acontactsname', '1,20', '联系人名称不能为空', 1, 'length'),
		array('acontactstel', '/^(\+?86-?)?(18|15|13)[0-9]{9}$|^(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$|^400(-\d{3,4}){2}$/', '联系人电话不能为空', 1, 'regex'),
		array('acertificates', 'cehckImages', '代理商证件不能为空', 1, 'callback'),
    );
	
    public function deleteAgent( $id, $status ) {
		$status = $status==1 ? 0 : 1;
		$status = $this->query("UPDATE `{$this->tablePrefix}agent`,`{$this->tablePrefix}member` SET `{$this->tablePrefix}member`.mstatus={$status} WHERE `{$this->tablePrefix}agent`.mid=`{$this->tablePrefix}member`.mid AND `{$this->tablePrefix}agent`.id in ($id)");
		return $status !== false ? true : false;
    }
	
	public function cehckImages($args) {
		$acertificates = unserialize($args);
		if(!is_array($acertificates)) return false;
		if( isset($acertificates['sfzz']) && !empty($acertificates['sfzz']) && isset($acertificates['sfzb']) && !empty($acertificates['sfzb']) ) {
			return true; 
		} else { return false; }
	}
	
	public function insert($data='', $options=array(), $replace=false) {
		$this->arank = $data['arank'];
		$data = $this->create($data, 1);
		if( !$data ) return false;
		return $this->add($data, $options, $replace); 
	}
	
	public function cehckAid( $args )
	{
		$info = M('agent')->alias('AS a')->where("m.mstatus=1 and a.aid={$args}")->join('__MEMBER__ AS m ON m.mid=a.mid')->find();
		return is_array( $info) && !empty($info) && $this->arank!='g' ? false : true;	
	}
	
}

?>