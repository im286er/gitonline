<?php
namespace Rebateapp\Model;
use Think\Model;

class VocationModel extends Model {

	protected $cachename = 'VocationCache';
	
	/****返回行业的一级分类***/
	public function topVid($vid=null){
		if(!$vid)return false;
		$data = $this->cacheVocation(true);
		if(!$data[$vid])return false;
		return $data[$vid]['v_pid']>0?$data[$vid]['v_pid']:$data[$vid]['v_id'];
	
	}

	/*****组成行业树数组*********/
	public function generateTree($items){
		$items = $this->cacheVocation(true);
		foreach($items as $item)
		$items[$item['v_pid']]['children'][$item['v_id']] = &$items[$item['v_id']];
		return isset($items[0]['children']) ? $items[0]['children'] : array();
	}

	//缓存所有商家的经纬度
	public function cacheVocation($force=false){ //缓存商户位置
		$data = S($this->cachename);
		if(!$data || $force==true){
			$data = array();
			$data = $this->getField('v_id,v_title,v_pid,v_img');
			S($this->cachename,$data,1000000);
		}
		return $data;
	}

}

?>