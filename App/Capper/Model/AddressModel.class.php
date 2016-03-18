<?php
namespace Capper\Model;
use Think\Model;

class AddressModel extends Model {

	protected $cacheName = 'arealist';//序列化关联后的缓存名
	protected $cacheData = 'areadata';//数据缓存

	public function cacheData($force=false){
		$data = S($this->cacheData);
		if(!$data || $force==true){
			$data = array();
			$data = $this->getField('aid,aname');
			S($this->cacheData,$data,1000000);
		}
		return $data;	
	}

	//缓存所有地区信息
	public function cacheArea($force=false){ 
		$data = S($this->cacheName);
		if(!$data || $force==true){
			$data = array();
			$list = $this->getField('aid,aname,apid');
			$data = $this->generateTree($list);
			S($this->cacheName,$data,1000000);
		}
		return $data;
	}

    public function generateTree($items){
        $tree = array();
        foreach($items as $item){
            if(isset($items[$item['apid']])){
                $items[$item['apid']]['children'][$item['aid']] = &$items[$item['aid']];
            }else{
                $tree[$item['aid']] = &$items[$item['aid']];
            }
        }
        return $tree;
    }
}

?>