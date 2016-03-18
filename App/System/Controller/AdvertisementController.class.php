<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class AdvertisementController extends ManagerController{

    //广告列表
    public function adsList() {
		!$_GET['type'] && $_GET['type']=1;
		if( intval($_GET['type'])==1 ) {
			$where['_string'] = "`btype`<>'4'";	
		} else {
			$where['_string'] = "`btype`='4'";	
		}
		if( I('get.keyword', '') ) {
			$keyword=I('get.keyword', ''); $where['b.btitle|m.mnickname']=array('like', "%{$keyword}%", 'or');
		}
		$page = new \Think\Page(M('banner')->where($where)->count(), 10);
		$this->assign('adslist', M('banner')->alias('AS b')->where($where)->join('__MERCHANT__ AS m ON b.jid=m.jid', 'left')->field('b.bid,b.btitle,b.burl,b.bimg,b.btype,m.mnickname')->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
        $this->display();
    }
	
    //添加广告
    public function adAdd() {
       	if( IS_POST ) {
			$_POST['info']['btype'] = isset($_POST['type']) && intval($_POST['type'])==2 ? '4' : '1';
		 	if( M('banner')->add($_POST['info']) ) {
				$this->display('Jump:success');	   
			} else { $this->display('Jump:error'); }		   
		} else {
			$this->assign('mlist', M('Merchant')->field('jid,mnickname')->select());
			$this->display();
		}
    }
	
    //删除广告
    public function adDel() {
        $bid = I('get.bid', ''); if( !$bid ) exit(''); 
        exit(D('banner')->where( array('bid'=>array('in', "$bid")) )->delete() ? "1" : "0");
    }

	//修改广告
	public function adEdit() {
		if( IS_POST ) {
			$_POST['info']['btype'] = isset($_POST['type']) && intval($_POST['type'])==2 ? '4' : '1';
		 	if( M('banner')->save($_POST['info']) !== false ) {
				$this->display('Jump:success');	   
			} else { $this->display('Jump:error'); }		   
		} else {
			$banner = M('banner')->where(array('bid'=>I('get.bid', 0, 'intval')))->find();
			if(!is_array($banner) || empty($banner)) { $this->assign('msg', '广告信息不存在'); $this->display('Jump:error'); }
			$this->assign('banner', $banner);
			$this->assign('mlist', M('Merchant')->field('jid,mnickname')->select());
			$this->display();
		}
	}

}