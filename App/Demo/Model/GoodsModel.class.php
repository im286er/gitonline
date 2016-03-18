<?php
namespace Demo\Model;
use Think\Model;

class GoodsModel extends Model {
	protected $_validate = array(
        array('gname', '1,50', '商品名称不能为空', 1, 'length'),
        array('cid', '/^[1-9]\d*$/', '商品分类不能为空', 1, 'regex'),
        array('goprice', '/^[0-9]+(.[0-9]{1,2})?$/', '商品原价格出现错误', 1, 'regex'),
		array('gdprice', '/^[0-9]+(.[0-9]{1,2})?$/', '商品优惠价出现错误', 2, 'regex'),
		//array('gstock', '/^[1-9]\d*$/', '商品库存不能为空', 1, 'regex'),
    );
	
	public function insert($data='', $options=array(), $replace=false) {
		$data = $this->create($data, 1);
		if( !$data ) return false;
		return $this->add($data, $options, $replace); 
	}
	
	public function update($data='', $options=array()) {
		$data = $this->create($data, 1);
		if( !$data ) return false;
		return $this->save($data, $options); 
	}
	
}

?>