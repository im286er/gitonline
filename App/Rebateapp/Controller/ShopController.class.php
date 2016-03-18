<?php
namespace Rebateapp\Controller;

/*商户
 * 
 * 
 * */
class ShopController extends RebateviewController {
	
	/**
	 * 商户列表页
	 */
	public function shopList() {
		
		$v_id = I('v_id');
		$city = I('city','330100');
		$coordinate = I('coordinate');
		$order = I('order',0);
		$source = I('get.source');
		if($source)cookie('source',$source);
		
		//$sql = "SELECT m.vid,s.district FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gvrebate>0 and g.gstatus=1 ";
		$sql = "SELECT m.vid,s.district FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gstatus=1 ";
		$sql .= " and s.status='1' and s.qmstatus = '1' and s.is_show = 1 ";
		$sql .= " and s.city='".$city."' ";
		$sql .= " GROUP BY s.sid ";
		
		$Model = new \Think\Model();
		$shops = $Model->query($sql);
		
		//获取城市下的区域
		$area = M('address')->where(array('apid'=>$city))->select();
		
		//获取行业列表
		$default_vname = '全部分类';
		$vocation = M('vocation')->where(array('v_id'=>array('not in' ,array('125','128','129'))))->select();
		$varr = array();
		foreach($vocation as $k=>$v){
			if($v['v_pid'] == 0){
				$varr[] = $v;
			}
			if($v_id == $v['v_id']){
				$default_vname = $v['v_title'];
			}
		}
		
		foreach($varr as $k2=>$v2){
			foreach($vocation as $k3=>$v3){
				if($v3['v_pid'] == $v2['v_id']){
					$varr[$k2]['child'][] = $v3;
				}
			}
		}
		
		foreach($shops as $k3=>$v3){
			foreach($area as $kk=>$vv){
				if($vv['aid'] == $v3['district']){
					$area[$kk]['count'] += 1;
				}
			}
			foreach($varr as $kk2=>$vv2){
				foreach($vv2['child'] as $kk3=>$vv3){
					if($vv3['v_id'] == $v3['vid']){
						$varr[$kk2]['child'][$kk3]['count'] += 1;
						$varr[$kk2]['count'] += 1;
					}
				}
				if($vv2['v_id'] == $v3['vid']){
					$varr[$kk2]['count'] += 1;
				}
			} 
		}
			
		$this->assign('total',count($shops));
		$this->assign('default_vname',$default_vname);
		$this->assign('vocation',$varr);
		$this->assign('area',$area);
		$this->assign('v_id',$v_id);
		$this->assign('city',$city);
		$this->assign('coordinate',$coordinate);
		$this->assign('order',$order);
		//if(cookie('source')=='merchant' && $this->msystem == 'android'){
			//$this->display('shopListandroid');
		//}else
			$this->display('shopList');
	}


	public function shopListApp(){
		$this->shopList();
	}
	
	/*
	 * 获取列表数据
	 * 
	 * */
	public function getShop(){
		$v_id = I('v_id',0);
		$city = I('city');
		$district = I('district');
		$coordinate = I('coordinate');
		$order = I('order');
		
		$org = C('ORDER_RETURN_GRADE');
		$org = $org[0]/100;
		
		$page = I('page');
		$page_size = 10;
		
		//查询行业列表
		$vocation = M('vocation')->where(array('v_pid'=>$v_id))->field('v_id')->select();
		$vocation_arr = array();
		foreach($vocation as $k=>$v){
			$vocation_arr[] = $v['v_id'];
		}
		if($v_id > 0){
			$vocation_arr[] = $v_id;
		}
		
		//地理位置排序
		if($order == 0 && $coordinate){
			//$sql = "SELECT s.sid,s.jid,s.lng,s.lat,s.sname,s.exterior,s.district,m.mabbreviation, max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gvrebate>0 and g.gstatus=1 and g.gtype=0 ";
			$sql = "SELECT s.saddress,s.sid,s.jid,s.lng,s.lat,s.sname,s.exterior,s.district,m.mabbreviation, max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gstatus=1 and g.gtype=0 ";
			$sql .= " and s.status='1' and s.qmstatus = '1' and s.is_show = 1 ";
			if($v_id > 0){
				$sql .= " and m.vid in (".join(',',$vocation_arr).") ";
			}
			if($district){
				$sql .= " and s.district='".$district."' ";
			}else{
				$sql .= " and s.city='".$city."' ";
			}
				
			$sql .= " GROUP BY s.sid ";
				
			$Model = new \Think\Model();
			$shops = $Model->query($sql);
			
			$result_all = array();
			$location = explode(',',$coordinate);
			foreach($shops as $k2=>$v2){
				$d = GetDistance($location[0],$location[1],$v2['lng'],$v2['lat']);
				$v2['distance'] = $d;
				$v2['v_fl'] = '最高返现'.D('Shop')->calculateRebate($v2['gfl']).'元';
				$v2['p_fl'] = '最高返现'.D('Shop')->calculateRebate($v2['gfl'],'general').'元';
				//$v2['district_name'] = M('address')->where(array('aid'=>$v2['district']))->getField('aname');
				$v2['url'] = U('Shop/shopInfo',array('sid'=>$v2['sid'],'v_id'=>$v_id,'city'=>$city,'source'=>cookie('source'),'superiorsource'=>'list'));
				//$result_all["$d"] = $v2;
				$result_all[] = $v2;
			}
			$result_ls = multi_array_sort($result_all,'distance');
			//ksort($result_all);
			//print_r($result_ls);exit;
			$result_ch = array_chunk($result_ls,$page_size);
			$result = $result_ch[$page-1];
			foreach($result as $k3=>$v3){
				$result[$k3]['district_name'] = M('address')->where(array('aid'=>$v3['district']))->getField('aname');
				if($v3['distance'] > 0){
					$result[$k3]['distance'] = $v3['distance'].'km';
				}
			}
		}else{
			//返现排序
			if($order == 0){
				$order = 1;//1:最高值由高到低  2:最低值由高到低
			}
				
			if($order == 1){
				//$sql = "SELECT s.sid,s.jid,s.lng,s.lat,s.sname,s.exterior,s.district,m.mabbreviation, max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gvrebate>0 and g.gstatus=1 and g.gtype=0 ";
				$sql = "SELECT s.saddress,s.sid,s.jid,s.lng,s.lat,s.sname,s.exterior,s.district,m.mabbreviation, max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gstatus=1 and g.gtype=0 ";
			}else{
				//$sql = "SELECT s.sid,s.jid,s.lng,s.lat,s.sname,s.exterior,s.district,m.mabbreviation, min(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gvrebate>0 and g.gstatus=1 and g.gtype=0 ";
				$sql = "SELECT s.saddress,s.sid,s.jid,s.lng,s.lat,s.sname,s.exterior,s.district,m.mabbreviation, min(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and  g.gstatus=1 and g.gtype=0 ";
			}
			$sql .= " and s.status='1' and s.qmstatus = '1' and s.is_show = 1 ";
			if($v_id > 0){
				$sql .= " and m.vid in (".join(',',$vocation_arr).") ";
			}
			if($district){
				$sql .= " and s.district='".$district."' ";
			}else{
				$sql .= " and s.city='".$city."' ";
			}
			$sql .= " GROUP BY s.sid order by gfl ";
			if($order == 1){
				$sql .= " desc ";
			}
			$sql .= " limit ".(($page-1)*$page_size).','.$page_size;
			
			$Model = new \Think\Model();
			$shops = $Model->query($sql);
		
			$result = array();
			$location = explode(',',$coordinate);
			foreach($shops as $k2=>$v2){
				if($coordinate){
					$d = GetDistance($location[0],$location[1],$v2['lng'],$v2['lat']);
					$v2['distance'] = $d.'km';
				}else{
					$v2['distance'] = '';
				}
				if($order == 1){
					$v2['v_fl'] = '最高返现'.D('Shop')->calculateRebate($v2['gfl']).'元';
					$v2['p_fl'] = '最高返现'.D('Shop')->calculateRebate($v2['gfl'],'general').'元';
				}else{
					$v2['v_fl'] = '最低返现'.D('Shop')->calculateRebate($v2['gfl']).'元';
					$v2['p_fl'] = '最低返现'.D('Shop')->calculateRebate($v2['gfl'],'general').'元';
				}
				$v2['district_name'] = M('address')->where(array('aid'=>$v2['district']))->getField('aname');
				$v2['url'] = U('Shop/shopInfo@flapp',array('sid'=>$v2['sid'],'v_id'=>$v_id,'city'=>$city,'source'=>cookie('source'),'superiorsource'=>'list'));
				$result[] = $v2;
			}
		}
		$data = array(
			"msg" => "true",
			"content" => $result,
			"page" => $page
		);
		$this->ajaxReturn($data);
	}
	/**
	 * 商户详情页
	 */
	public function shopInfo() {
		$org = C('ORDER_RETURN_GRADE');
		$org = $org[0]/100;
		
		$superiorsource = I('get.superiorsource');
		if($superiorsource){
			cookie('superiorsource',$superiorsource);
			I('get.v_id')?cookie('source_vid',I('get.v_id')):cookie('source_vid',null);
			I('get.city')?cookie('source_city',I('get.city')):cookie('source_city',null);
		}
		$sid = I('sid',0);
		$opt = array(
			'sid' => $sid
		);
		$shop = M('shop')->where($opt)->find();
		/****查找商家部分，跳转到移动端***/
		$merchant = M('merchant')->find($shop['jid']);
		//if( in_array($merchant['theme'],array('Default','clothes','fruit','jiudian','jshs','market','skincy')) ){
			$this->redirect('Index/index@yd', array('jid' => $shop['jid'],'flsid'=>$sid,'city'=>cookie('source_city'),'v_id'=>cookie('source_vid'),'superiorsource'=>$superiorsource,'opentype'=>'flapp'));
			exit;
		//}
		/*******/


		if($shop['jid'])
		$vid = M('merchant')->where(array('jid'=>$shop['jid']))->getField('vid');
		$vid = D("vocation")->topVid($vid);
		$this->assign('vid',$vid);
		

		//获取最高返现值
		//$max_fl = M('goods')->query("select max(case when gdprice>0 then gdprice*gvrebate else goprice*gvrebate end) as max_fl from `azd_goods` where gtype=0 and gstatus=1 and gvrebate>0 and sid='".$sid."'");
		$max_fl = M('goods')->query("select max(case when gdprice>0 then gdprice*gvrebate else goprice*gvrebate end) as max_fl from `azd_goods` where gtype=0 and gstatus=1 and sid='".$sid."'");
		$shop['v_max_fl'] = round($max_fl[0]['max_fl']/100*$org,2)?round($max_fl[0]['max_fl']/100*$org,2):10;
		$shop['p_max_fl'] = $shop['v_max_fl']?round($shop['v_max_fl']*C('USER_RATION_VIP'),2):1;
		$linkurl = url_param_encrypt(U('Shop/shopInfo@flapp',array('sid'=>$sid)),'E');
		/**验证是否收藏过**/
		$is_exist = M('fl_collection')->where(array('flc_userid'=>$this->userid,'flc_shopid'=>$sid))->find();
		$this->assign('is_exist',$is_exist);
		/**验证是否收藏过结束**/
		$this->assign('linkurl',$linkurl);
		
		$this->assign('shop',$shop);
		$this->display();
	}
	
	/*
	 * 收藏
	 * */
	public function shopFavorite(){
		$sid  = I('post.sid');
		$requestact = I('post.requestact','favorite');
		$shop = M('shop')->where(array('sid'=>$sid))->find();
		if(!$this->userid)$this->ajaxReturn(array("msg" => "false","content" => "操作失败，请先登录后再收藏"));
		if($shop){
			$is_exist = M('fl_collection')->where(array('flc_userid'=>$this->userid,'flc_shopid'=>$sid))->find();
			if($requestact == 'favorite'){
				if(!$is_exist){
					$flc_merchantname = M('merchant')->where(array('jid'=>$shop['jid']))->getField('mabbreviation');
					$flc_shopaddress  = M('address')->where(array('aid'=>$shop['district']))->getField('aname');
					$opt = array(
						'flc_userid' => $this->userid,
						'flc_merchantname' => $flc_merchantname,
						'flc_shopimg' => $shop['exterior'],
						'flc_shopname' => $shop['sname'],
						'flc_shopaddress' => $flc_shopaddress?$flc_shopaddress:'',
						'flc_shoplng' => $shop['lng'],
						'flc_shoplat' => $shop['lat'],
						'flc_addtime' => date("Y-m-d H:i:s"),
						'flc_shopid' => $shop['sid'],
					);
					M('fl_collection')->add($opt);
				}
				$this->ajaxReturn(array("msg" => "true","content" => "收藏成功"));
			}elseif($requestact == 'cancel'){
				if($is_exist)M('fl_collection')->where(array('flc_userid'=>$this->userid,'flc_shopid'=>$sid))->delete(); 
				$this->ajaxReturn(array("msg" => "true","content" => "取消收藏成功"));
			}
		}
		
	}

}