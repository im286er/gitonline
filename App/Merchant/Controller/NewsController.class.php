<?php
namespace Merchant\Controller;

class NewsController extends MerchantController {

    //相关资讯
    public function index() {
		$where = array('new_jid'=>$this->jid, 'new_status'=>1);
		$page = new \Common\Org\Page(M('new')->where($where)->count(), 5);
		$this->assign('newlist', M('new')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		if( IS_POST ){
			$Module['Name'] = I('post.HdModuleName', '');
			$Module['Icon'] = I('post.HdModuleIcon', '');
			if( $Module) {
				$this->writeFile($this->path.'NewModule.php',serialize($Module)); exit("1");
			}
		}
		file_exists($this->path.'NewModule.php') && $NewModule=unserialize($this->readsFile($this->path.'NewModule.php'));
		$this->assign('NewModule', $NewModule);
		$this->assign('CurrentUrl', "Messagehdlist");
		$this->display();
    }
	
	//添加资讯
	public function addnew() {
		if( IS_POST ) {
			$data = array();
			$data['new_title'] = I('post.t', '');
			$data['new_img'] = I('post.i', '');
			$data['new_con'] = preg_replace("/<[^><]*script[^><]*>/i", '', $_POST['c']);
			$data['new_jid'] = $this->jid;
			$data['new_time'] = date('Y-m-d H:i:s');
			if(!$data['new_title'] || !$data['new_img'] || !$data['new_jid']) exit('0');
			exit( M('new')->add($data) ? "1" : "0" );
		} else {
			$this->display();
		}
	}
	
	//删除资讯
	public function delnew() {
		$newinfo = M('new')->where(array('new_id'=>I('post.id', 0, 'intval')))->find();
		if( !is_array($newinfo) || empty($newinfo) || $newinfo['new_jid'] != $this->jid ) exit("0");
		exit( M('new')->where(array('new_id'=>I('post.id', 0, 'intval')))->setField('new_status', '0') !== false ? "1" : "0");
	}
	
	//修改资讯
	public function editnew() {
		if( IS_POST ) {
			$data = array();
			$data['new_title'] = I('post.t', '');
			$data['new_img'] = I('post.i', '');
			$data['new_con'] = preg_replace("/<[^><]*script[^><]*>/i", '', $_POST['c']);
			if(!$data['new_title'] || !$data['new_img']) exit('0');
			exit( M('new')->where(array('new_id'=>I('post.id', 0, 'intval')))->save($data) !== false ? "1" : "0" );
		} else {
			$newinfo = M('new')->where(array('new_id'=>I('get.id', 0, 'intval')))->find();
			if( !is_array($newinfo) || empty($newinfo) || $newinfo['new_jid'] != $this->jid ) E('你无权查看当前页面！');
			$this->assign('newinfo', $newinfo);
			$this->display();
		}
	}
	
    public function writeFile($filename, $str) {  
        if (function_exists(file_put_contents)) {  
            file_put_contents($filename, $str);  
        } else {  
            $fp = fopen($filename, "wb");  
            fwrite($fp, $str);  
            fclose($fp);  
        }  
    }
    public function readsFile($filename) {  
        if (function_exists(file_get_contents)) {  
            return file_get_contents($filename);  
        } else {  
            $fp = fopen($filename, "rb");  
            $str = fread($fp, filesize($filename));  
            fclose($fp);  
            return $str;  
        }  
    }



}