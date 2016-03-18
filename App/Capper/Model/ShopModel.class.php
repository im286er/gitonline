<?php
namespace Capper\Model;
use Think\Model;

class ShopModel extends Model {

	protected $shopLocation = 'shopLocation';

	//详情页面
	public function runInfo($sid=null){
		if(!$sid)return false;
		$shopinfo = $this->where(array('sid'=>$sid,'status'=>'1'))->find();
		return $shopinfo?$shopinfo:false;
	}



	//基于地理位置的商户
	public function locationStore($coordinate,$offset=0,$length=100){
		$shopids = array();
		$location = explode(',',$coordinate);
		$shopGeo = $this->cacheShopLocation();//获取所有商户的地理位置
		if($shopGeo && $location)foreach($shopGeo as $key => $value){
			if($value['lng'] && $value['lat']){
				$shopids[$key] = GetDistance($location[0],$location[1],$value['lng'],$value['lat']);
			}
		}
		asort($shopids);
		return array_slice($shopids,$offset,$length,true);
	}

	//缓存所有商家的经纬度
	public function cacheShopLocation($force=false){ //缓存商户位置
		$data = S($this->shopLocation);
		$countShop = count($data);
		$count = $this->where(array('status'=>'1'))->count("sid");
		if($countShop!=$count || $force==true){
			$data = array();
			$shop = $this->where(array('status'=>'1','lng'=>array('neq',''),'lat'=>array('neq','')))->field('sid,lng,lat')->order('sid asc')->select();
			if($shop)foreach($shop as $value){
				$data[$value['sid']] = array('lng'=>$value['lng'],'lat'=>$value['lat']);
			}
			S($this->shopLocation,$data,1000000);
		}
		return $data;
	}

}

?>