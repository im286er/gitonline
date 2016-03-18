<?php
namespace Mobile\Controller;

class BoutiqueController extends MobileController {
	public $action_name = 'Choose';

	public function index()
	{
		$shop = M("shop");
		$opt = array(
				'jid' 		=> $this->jid,
				'status' 	=> '1'
		);
		$shop_count = $shop->where($opt)->count();
		
		//判断是否有多家分店 start
		if($this->sid == 0) {
			//如果有多家 跳转到门店列表页
			if($shop_count == 1) {
				$sid = $shop->where($opt)->getField('sid');
				$this->sid = $sid > 0 ? $sid : 0;
				$this->assign('sid', $this->sid);
			} else {
				$this->redirect('Shop/index', array('jid' => $this->jid, 'mod'=>'Boutique'));
				exit;
			}
		}
		//判断是否有多家分店 end
		
		//商品分类列表   start
		$category = M('class');
		$category_list = $category->where(array('jid'=>$this->jid, 'status'=>1, 'sid'=>$this->sid, 'ctype'=>1))->order('corder asc,cid asc')->select();
		//商品分类列表   end
			
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'BoutiqueModuleName.php') && $module1name=file_get_contents($path.'BoutiqueModuleName.php');
		$this->assign('page_name', $module1name ? $module1name : '秒杀活动');	
		if($shop_count == 1) {
			$page_url = U('Index/index',array('jid'=>$this->jid));
		} else {
			$page_url = U('Shop/index', array('jid' => $this->jid,'mod'=>'Boutique'));
		}
		$this->assign('page_url', $page_url);
		$default_cid = isset($category_list[0]['cid']) ? $category_list[0]['cid'] : 0;
		
		$this->assign('rcid', cookie($this->jid.'_rcid_'.$this->sid));
		$this->assign('mid', $this->mid);
		$this->assign('default_cid', cookie($this->jid.'_rcid_'.$this->sid) > 0 ? cookie($this->jid.'_rcid_'.$this->sid) : $default_cid);
		$this->assign('category_list', $category_list);
		$this->mydisplay();
	}
	
	public function search()
	{
		$cid = I('get.cid');
        $sid = I('get.sid');

		$goods = M('goods');
		$where = array(
			'g.sid'    	=> $sid,
			'g.gtype' 	=> 0,
			'g.gstatus' => 1,
			'g.gstock' 	=> array('neq', 0),
			'c.ctype' 	=> 1,
			'c.status' 	=> 1,
			'g.cid'		=> $cid,
			'g.isboutique'	=> 1
		);

		$result_list = $goods->alias('g')->join('azd_class c on g.cid=c.cid')->field('g.cid,g.gid,g.gimg,c.cname,g.goprice,g.gdprice')->where($where)->order('g.gorder')->select();
		$this->assign('result_list', $result_list);
		cookie($this->jid.'_rcid_'.$this->sid, null);
		cookie($this->jid.'_rcid_'.$this->sid, $cid);
		$this->display('Boutique_list');
	}
	
}