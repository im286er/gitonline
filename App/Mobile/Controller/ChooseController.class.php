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
		
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'InfoMenu1Name.php') && $module1name=file_get_contents($path.'InfoMenu1Name.php');
		$this->assign('module1name', $module1name ? $module1name : '在线点菜');
		$this->assign('page_name',$module1name ? $module1name : '在线点菜');	
	
		$this->assign('rcid',$goods_info['cid']);
		$this->assign('goods_info',$goods_info);
		$this->assign('mid',$this->mid);
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
	
}