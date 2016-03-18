<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class AdvertisementController extends ManagerController {
 	
    public function index(){
        $id =M("agent")->where("mid=".$_SESSION['member']['mid'])->find(); 
    	$where['_string']="a.pid='$id[id]' or a.mid=".$_SESSION['member']['mid'];
		if( I('get.keyword', '') ) {
			$keyword=I('get.keyword', ''); $where['b.btitle|s.sname']=array('like', "%{$keyword}%", 'or');
		}
		$page = new \Think\Page(M('banner')->alias('AS b')->where($where)->join('__MERCHANT__ AS m ON b.jid=m.jid', 'left')->join('__AGENT__ AS a ON m.magent=a.id', 'left')->join('__MEMBER__ AS e ON e.mid=a.mid', 'left')->count(), 10);
		$this->assign('adslist', M('banner')->alias('AS b')->where($where)->join('__MERCHANT__ AS m ON b.jid=m.jid', 'left')->join('__AGENT__ AS a ON m.magent=a.id', 'left')->join('__MEMBER__ AS e ON e.mid=a.mid', 'left')->field('b.bid,b.btitle,b.burl,b.bimg,b.btype,m.mnickname')->limit($page->firstRow.','.$page->listRows)->select());
      
		$this->assign('pages', $page->show());
        $this->display();
	}
	
	public function add(){
       if( IS_POST ) {
		 	if( M('banner')->add($_POST['info']) ) {
				$this->display('Jump:success');	   
			} else { $this->display('Jump:error'); }		   
		} else {
		$pid=M("agent as ag")->where("ag.mid=".$_SESSION['member']['mid'])->find();
		$where="ag.pid='$pid[id]' or ag.mid=".$_SESSION['member']['mid'];
        $this->assign('mlist',M("merchant as az")->join("azd_member as am on am.mid=az.mid")->join("azd_agent as ag on ag.id=az.magent")->where($where)->field('jid,mnickname')->select());
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