<?php
namespace Mobile\Controller;
/*在线点菜控制器
 *
*
* */

class ChooseController extends MobileController {
	
	public $action_name = 'Choose';
	/*点菜主页
	 *
	*
	* */
	public function index(){
		$shop = M("shop");
		$opt = array(
				'jid' => $this->jid,
				'status' => '1',
				'is_show' => 1
		);
		$shop_count = $shop->where($opt)->count();
		
		//判断是否有多家分店 start
		if($this->sid == 0){
			//如果有多家 跳转到门店列表页
			if($shop_count == 1){
				//单一门店直接显示
				$sid = $shop->where($opt)->getField('sid');
				$this->sid = $sid > 0 ? $sid : 0;
				$this->assign('sid',$this->sid);
				
			}else{
				$this->redirect('Shop/index', array('jid' => $this->jid,'mod'=>'Choose'));
				exit;
			}
		}
		//判断是否有多家分店 end
		
		//判断是全民返利过来的就从回到全民返利网页部分
		if(cookie('opentype')=='flapp'){
			$this->redirect('Flow/order@flapp', array('jid' => $this->jid,'sid'=>$this->sid));
			exit;
		}

		
		//商品分类列表   start
		
		$category = M('class');
		/*
		$opt = array(
				'g.sid'    => $this->sid,
				'g.gtype' => 0,
				'g.gstatus' => 1,
				'c.jid' => $this->jid,
				'c.sid' => $this->sid,
				'c.ctype' => 1,
				'c.status' => 1
		);
		//$category_list = $category->where($opt)->order('corder')->select();
		$category_list = $category->alias('c')->join('azd_goods g on g.cid=c.cid')->where($opt)->group('c.cid')->order('c.corder')->select();
		*/
		$category_list = $category->where(array('jid'=>$this->jid, 'status'=>1, 'sid'=>$this->sid, 'ctype'=>1))->order('corder asc,cid asc')->select();
		//商品分类列表   end		
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'InfoMenu1Name.php') && $module1name=file_get_contents($path.'InfoMenu1Name.php');
		$this->assign('module1name', $module1name ? $module1name : '在线点菜');
		$this->assign('page_name',$module1name ? $module1name : '在线点菜');	
		if($shop_count == 1){
			$page_url = U('Index/index',array('jid'=>$this->jid));
		}else{
			$page_url = U('Shop/index', array('jid' => $this->jid,'mod'=>'Choose'));
		}
		$this->assign('page_url',$page_url);
		$default_cid = isset($category_list[0]['cid']) ? $category_list[0]['cid'] : 0;
		//print_r($category_list);die;
		$this->assign('rcid',cookie($this->jid.'_rcid_'.$this->sid));
		$this->assign('mid',$this->mid);
		$this->assign('default_cid',cookie($this->jid.'_rcid_'.$this->sid) > 0 ? cookie($this->jid.'_rcid_'.$this->sid) : $default_cid);
		$this->assign('category_list',$category_list);
		$this->mydisplay();
	}
	
	/*详情页*/
	public function detail(){
		$gid = I('get.gid');
		$sid = I('get.sid');
		$opt = array(
			'gid' 		=> $gid,
			'sid'    	=> $sid,
			'gtype' 	=> 0,
			'gstatus' 	=> 1,
			'gstock' 	=> array('neq', 0),
		);
		$goods = M('goods');
		$goods_info = $goods->where($opt)->find();
		//模板
		$theme  = M('shop')->where(array('sid'=>$sid))->getField('theme');
		//判断新旧模板
		$dtype  = in_array($theme, C('NEW_THEMES')) ? '1' : '2';
		
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'InfoMenu1Name.php') && $module1name=file_get_contents($path.'InfoMenu1Name.php');
		$this->assign('module1name', $module1name ? $module1name : '在线点菜');
		$this->assign('page_name',$module1name ? $module1name : '在线点菜');	
		//查询已选商品数量
		$cart = $_COOKIE['ProductList'];

		//if(!$cart || $cart == ''){
			//$this->redirect('Index/new2', array('jid' => $this->jid));
		//}
		$cart_arr2 = explode('|', $cart);
		$cart_key = array();
		foreach($cart_arr2 as $k1=>$v1){
			if(!empty($v1)){
				$temp = explode('_', $v1);
				if ($temp[0] == $sid && $temp[1] == $gid) {
					$cart_key[] = $temp[2];
				}
			}
		}

		$cart_num = $cart_key[0] ? $cart_key[0] : 0;
		$this->assign('cart_key', $cart_num);
		$this->assign('rcid',$goods_info['cid']);
		$this->assign('goods_info',$goods_info);
		$this->assign('mid',$this->mid);
		$this->assign('dtype', $dtype);
		$page_url = I("from_index") == 1 ? U('Index/index',array('jid'=>$this->jid)) : "";
		$this->assign('page_url',$page_url);
		$this->mydisplay();
	}
	
	/*点菜搜索
	 *
	*
	* */
	public function search(){
		$cid = I('post.cid');
		$key = I('post.key');
		//获取商品列表 start
        $sid=I('get.sid');
		$goods = M('goods');
		$opt = array(
			'g.sid'    =>$sid,
			'g.gtype' => 0,
			'g.gstatus' => 1,
			'g.gname' =>array('like',"%$key%"),  
			'g.gstock' => array('neq', 0),
			'c.ctype' => 1,
			'c.status' => 1,
		);
		$pro_list_a = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->where($opt)->order('g.gorder')->select();
		if($cid){
			$opt['g.cid'] = $cid;
		}
		$goods_list = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->where($opt)->order('g.gorder')->select();
		$result_list = array();
		$pro_list   = array();
		foreach($goods_list as $k=>$v){
			if($key != ''){
				if(!stristr($v['gname'],$key)){
					continue;
				}
			}
			if(isset($result_list[$v['cid']])){
				$result_list[$v['cid']]['list'][] = $v;
			}else{
				$result_list[$v['cid']]['cid'] = $v["cid"];
				$result_list[$v['cid']]['cname'] = $v["cname"];
				$result_list[$v['cid']]['list'][] = $v;
			}
			//$pro_list[$v['gid']] = $v;
		}
		foreach($pro_list_a as $s=>$vv){
			$pro_list[$vv['gid']] = $vv;
		}
		//print_r($pro_list);exit;
		//获取商品列表 end
		$this->assign('result_list',$result_list);
		cookie($this->jid.'_rcid_'.$this->sid,null);
		cookie($this->jid.'_rcid_'.$this->sid,$cid);
		$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');
		$content = $this->theme($tpl_name)->fetch('Choose_goods');
		$data = array(
			'msg' => 'true',
			'content' => $content,
			'product' => json_encode($pro_list)
		);
		$this->ajaxReturn($data);
	}


	/*点菜搜索
	 *
	*
	* */
	public function new_search(){
		$cid = I('post.cid');
		$key = I('post.key');
		//获取商品列表 start
        $sid=I('get.sid');
		$goods = M('goods');
		$opt = array(
			'g.sid'    =>$sid,
			'g.gtype' => 0,
			'g.gstatus' => 1,
			'g.gname' =>array('like',"%$key%"),  
			'g.gstock' => array('neq', 0),
			'c.status' => 1,
		);
		$pro_list_a = $goods->alias('g')->join('azd_category c on g.cid=c.id')->where($opt)->order('g.gorder')->select();
		if($cid){
			$opt['g.cid'] = $cid;
		}
		$goods_list = $goods->alias('g')->join('azd_category c on g.cid=c.id')->where($opt)->order('g.gorder')->select();
		$result_list = array();
		$pro_list   = array();
		foreach($goods_list as $k=>$v){
			if($key != ''){
				if(!stristr($v['gname'],$key)){
					continue;
				}
			}
			if(isset($result_list[$v['cid']])){
				$result_list[$v['cid']]['list'][] = $v;
			}else{
				$result_list[$v['cid']]['cid'] = $v["cid"];
				$result_list[$v['cid']]['cname'] = $v["cname"];
				$result_list[$v['cid']]['list'][] = $v;
			}
			//$pro_list[$v['gid']] = $v;
		}
		foreach($pro_list_a as $s=>$vv){
			$pro_list[$vv['gid']] = $vv;
		}
		//print_r($pro_list);exit;
		//获取商品列表 end
		$this->assign('result_list',$result_list);
		cookie($this->jid.'_rcid_'.$this->sid,null);
		cookie($this->jid.'_rcid_'.$this->sid,$cid);
		$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');
		$content = $this->theme($tpl_name)->fetch('Choose_goods');
		$data = array(
			'msg' => 'true',
			'content' => $content,
			'product' => json_encode($pro_list)
		);
		$this->ajaxReturn($data);
	}


	//搜索框
	
	function searchText(){

		$key = I('get.key'); 

       $goods = M('goods');
		//商品分类列表   start
		$category = M('class');
		$opt = array(
				'g.sid'    => $this->sid,
				'g.gtype' => 0,
				'g.gstatus' => 1,
				'c.jid' => $this->jid,
				'c.sid' => $this->sid,
				'c.ctype' => 1,
				'c.status' => 1
		);

		$cidData=array();
		foreach($category->alias('c')->join('azd_goods g on g.cid=c.cid')->where($opt)->group('c.cid')->order('c.corder')->select() as $v){
		$cidData[]=$v['cid'];	
			
		} 
	
		//商品分类列表   end
		$opt1 = array(
			'gname' =>array('like',"%$key%"), 
			'sid'   =>$this->sid,
            'cid'	=>array('in',$cidData),	
			'gtype' => 0, 
            'gstatus' => 1,
 
			'gstock' => array('neq', 0)

		); 
		
		$proData= $goods->where($opt1)->order('gorder')->select();
        if(empty($proData)){$proData[]['msg']="抱歉！没有找到你要搜的东西！";}
      echo json_encode($proData); 
     }


	/**
	 * 新版首页显示
	 */
	public function new1(){
		$shop = M("shop");
		$opt = array(
			'jid' => $this->jid,
			'status' => '1',
			'is_show' => 1
		);
		$shop_count = $shop->where($opt)->count();
		$sid		= $shop->where($opt)->getField('sid');
		//判断是否有多家分店 start
		if($this->sid == 0){
			//如果有多家 跳转到门店列表页
			if($shop_count == 1){
				//单一门店直接显示
				$sid = $shop->where($opt)->getField('sid');
				$this->sid = $sid > 0 ? $sid : 0;
				$this->assign('sid',$this->sid);
			}else{
				$this->redirect('Shop/index', array('jid' => $this->jid,'mod'=>'Choose'));
				exit;
			}
		}
		//判断是否有多家分店 end

		//商品分类列表   start
		$category = M('class');
		$category_list = $category->where(array('jid'=>$this->jid, 'status'=>1, 'sid'=>$this->sid, 'ctype'=>1))->order('corder asc,cid asc')->select();
		//商品分类列表   end
		if($shop_count == 1){
			$page_url = U('Index/index',array('jid'=>$this->jid));
		}else{
			$page_url = U('Shop/index', array('jid' => $this->jid,'mod'=>'Choose'));
		}
		$this->assign('page_url',$page_url);
		$default_cid = isset($category_list[0]['cid']) ? $category_list[0]['cid'] : 0;
		//商品列表
		$goods_list = M('goods')->where(array('sid'=>$sid))->select();

		$this->assign('goods_info',$goods_list);
		$this->assign('rcid',cookie($this->jid.'_rcid_'.$this->sid));
		$this->assign('mid',$this->mid);
		$this->assign('default_cid',cookie($this->jid.'_rcid_'.$this->sid) > 0 ? cookie($this->jid.'_rcid_'.$this->sid) : $default_cid);
		$this->assign('category_list',$category_list);
		$this->mydisplay();
	}


	/**
	 * 购物车
	 */
	public function shopCart(){
		$shop = M("shop");
		$opt = array(
			'jid' => $this->jid,
			'status' => '1',
			'is_show' => 1
		);
		$sid		= $shop->where($opt)->getField('sid');

		$cart = $_COOKIE['ProductList'];

		if(!$cart || $cart == ''){
			$this->redirect('Choose/new1', array('jid' => $this->jid,'sid'=>$sid));
		}
		$cart_arr2 = explode('|', $cart);
		$cart_key = array();
		foreach($cart_arr2 as $k1=>$v1){
			if(!empty($v1)){
				$temp = explode('_', $v1);
				$cart_key[] = $temp[0];
			}
		}

		$opt = array(
			'gid' => array('in',join(',',$cart_key))
		);
		$goods_list = M('goods')->where($opt)->select();

		$total_number = 0;
		$total_price  = 0;
		$cart_arr = array();
		foreach($cart_arr2 as $k=>$v){
			$temp2 = explode('_', $v);
			foreach($goods_list as $kk=>$vv){
				if($temp2[0] == $vv['gid']){
					$cart_arr[$k]['gname']  =  $vv['gname'];
					$cart_arr[$k]['gprice'] = $vv['gdprice']>0 ? $vv['gdprice'] : $vv['goprice'] ;
					$cart_arr[$k]['number'] = $temp2[1] ;
					$cart_arr[$k]['gid']    = $vv['gid'];
					$cart_arr[$k]['gimg']    = $vv['gimg'];
					$total_number += $temp2[1];
					$total_price  += $temp2[1] * $cart_arr[$k]['gprice'];
				}
			}
		}

		$this->assign('sid',$sid);
		$this->assign('cart_arr',$cart_arr);
		$this->assign('total_number',$total_number);
		$this->assign('total_price',$total_price);
		$this->assign('page_name','购物车');
		$this->assign('default_cid',cookie($this->jid.'_rcid_'.$sid) > 0 ? cookie($this->jid.'_rcid_'.$sid) : 0);
		$this->mydisplay();
	}
	
}