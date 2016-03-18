<?php
namespace Mobile\Controller;

class VideoController extends MobileController {
	public $action_name = 'Video';
	
	/*视频列表
	 *
	*
	* */
	public function index(){
		
		//判断是否有多家分店 start
		/*
		if($this->sid == 0){
			$shop = M("shop");
			$opt = array(
					'jid' => $this->jid,
			);
			$shop_count = $shop->where($opt)->count();
			//如果有多家 跳转到门店列表页
			if($shop_count == 1){
				//单一门店直接显示
				$sid = $shop->where($opt)->getField('sid');
				$this->sid = $sid > 0 ? $sid : 0;
				$this->assign('sid',$this->sid);
			
			}else{
				$this->redirect('Shop/index', array('jid' => $this->jid,'mod'=>'Video'));
			}
		}*/
		//判断是否有多家分店 end
		
		//商品分类列表   start
		$category = M('class');
		$opt = array(
				'jid' => $this->jid,
				'sid' => 0,
				'ctype' => 3,
				'status' => 1
		);
		$category_list = $category->where($opt)->order('corder')->select();
		//商品分类列表   end
		$re = $cids = array();
		foreach($category_list as $k=>$v){
			$cids[$v['cid']] = $v['cname'];
			$re[] = $v['cid'];
		}
		//视频
		$video = M('video');
		$where = array();
		$where['gstatus'] = '1';
		if(I('get.cid','','intval')){
			$where['cid'] = I('get.cid');
		}else{
			$where['cid'] = array('in',join(',',$re));
		}
		$video_list = $video->where($where)->order('gorder')->select();
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'InfoMenu3Name.php') && $InfoMenu3Name=file_get_contents($path.'InfoMenu3Name.php');
		$this->assign('page_name', $InfoMenu3Name ? $InfoMenu3Name : '微视频');
		if(I('get.cid') && $cids[I('get.cid')])$this->assign('page_name',$cids[I('get.cid')]);
		$this->assign('page_url',U('Index/index',array('jid'=>$this->jid)));
		$this->assign('category_list',$category_list);
		$this->assign('video_list',$video_list);
		$this->mydisplay();
	}



	public function show(){
		$video = M('video');
		I('get.gid') or exit();
		$opt = array(
				'gid' => I('get.gid'),
				'gstatus' => '1'
		);
		$info = $video->where($opt)->find();
		$this->assign('info',$info);
		$this->assign('page_name',msubstr($info['gname'],0,20));
		$this->assign('page_url',U('Video/index',array('jid'=>$this->jid)));
		$this->mydisplay();
	}
}