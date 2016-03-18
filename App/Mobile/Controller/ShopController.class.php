<?php
namespace Mobile\Controller;
/*分店
 *
*
* */
class ShopController extends MobileController {
	
	public $action_name = 'Choose';
	/*分店列表
	 *
	*
	* */
	public function index(){
		
		$mod = I('mod');
		if(!$mod)$mod = I('post.mod');
		$mod = !empty($mod) ? $mod : 'Choose';
		//查询分店列表 start
		$shop = M("shop");
		$opt = array(
				'jid' => $this->jid,
				'status' => '1',
				'is_show' => 1
		);
		
		$shop_list_1 = $shop->where($opt)->select();
		$shop_list = array();
		$shop_list_2 = array();
		$shop_list_3 = array();
		//if(I('post.location') && strpos(I('post.location'),',')==false)die(); 
		$location = I('post.location')?I('post.location'):cookie('location');
		if(I('post.location')){
			cookie('location',$location,1000000);
		}
		foreach($shop_list_1 as $key=>$value){
			if($location && $value['lng'] && $value['lat']){
				$local = explode(',',$location);
				if(strpos($location,','))$value["dis"] = GetDistance($local[0],$local[1],$value['lng'],$value['lat']);
				$shop_list_2[] = $value;
			}else{
				$value["dis"] = '';
				$shop_list_3[$key] = $value;
			}
		}
		$shop_list_22 = multi_array_sort($shop_list_2,'dis');
		foreach($shop_list_22 as $v2){
			$shop_list[] = $v2;
		}
		foreach($shop_list_3 as $v3){
			$shop_list[] = $v3;
		}
		
		/*
		if(cookie('location')){ //地理位置排序
			$location = explode(',',cookie('location'));
			foreach($shop_list as $key=>$value){
				if($value['lng'] && $value['lat']){
					$shopids[$value['sid']] = GetDistance($location[0],$location[1],$value['lng'],$value['lat']);
				}
			}
		}
		
	
		
		
		if(cookie('location')){ //地理位置排序
			$shopids = array();
			$location = explode(',',cookie('location'));
			$shoplocation = $shop->field('sid,lng,lat')->where($map)->order($order)->select();
			if($shoplocation)foreach($shoplocation as $value){
				if($value['lng'] && $value['lat']){
					$shopids[$value['sid']] = GetDistance($location[0],$location[1],$value['lng'],$value['lat']);
				}
			}
			asort($shopids);
			$ids = array_slice($shopids,0,$num,true);
			if(count($ids)>0){
				$ids = array_keys($ids);
				$opt['sid'] = array('in',implode(',',$ids));
				$shop_list = $shop->where($opt)->order('field(sid,'.implode(',',$ids).')')->select();
			}else{
				$shop_list = $shop->where($opt)->select();
			}
		}else{
			$shop_list = $shop->where($opt)->select();
		}*/
		
		
		if($shop_list)foreach($shop_list as $k=>$v){
			$shop_list[$k]['url'] = U('Mobile/'.$mod.'/index',array('jid'=>$this->jid,'sid'=>$v['sid']));
		}
		
		if(count($shop_list) == 1){
			redirect(U('Mobile/'.$mod.'/index',array('jid'=>$this->jid,'sid'=>$shop_list[0]['sid'])));
		}
		if(I('from_user') == 1){
			$page_url = U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid));
		}else{
			$page_url = U('Index/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid));
		}
		//查询分店列表 end
		$this->assign('page_url',$page_url);
		//$this->assign('shopids',$shopids);
		$this->assign('shop_list',$shop_list);
		if(I('post.location')){
			$content = $this->fetch('Shop:list');
			$this->ajaxReturn($content);
		}
		$page_name = '选择分店';
		$this->assign('page_name',$page_name);
		$this->mydisplay();
	}

	/* 品牌文化
	 *
	* */
	public function brand(){
		$m_name = M("merchant")->where(array('jid'=>$this->jid))->find();
		$app = M("merchant_app")->where(array('jid'=>$this->jid))->find();
		//$app['appjs'] = str_replace(chr(32), "&nbsp;",$app['appjs']);
		//$app['appjs'] = "&nbsp;&nbsp;".$app['appjs'];
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'ShopName.php') && $ShopMenuName=file_get_contents($path.'ShopName.php');
		$this->assign('ShopMenuName', $ShopMenuName ? $ShopMenuName : '品牌文化');
		//print_r($app);
		$this->assign('m_app',$app);
		$this->assign('m_name',$m_name);
		$this->assign('page_url',U('Index/index',array('jid'=>$this->jid)));
		$page_name = '品牌分店';
		$this->assign('page_name',$ShopMenuName?$ShopMenuName:$page_name);
		$this->mydisplay($this->action_name);
	}

}