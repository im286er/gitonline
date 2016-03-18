<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class CaseController extends ManagerController {
    //展示模板
    public function sysList() {
		$_scene = M('yqxScene');
		$scenetype = intval(I('get.tagId',0));
		
		$where['userid_int']  = 0;
		
		if($scenetype > 0) {
			$where['tagid_int']  = $scenetype;
		}
		
		if( I('get.keyword') )
		{
			$where['scenename_varchar'] = array("like", "%".I('get.keyword', '', 'trim')."%");
		}
		
		$page = new \Think\Page($_scene->where($where)->count(), 6);
		$list=$_scene->where($where)->order('sceneid_bigint desc')->limit($page->firstRow, $page->listRows)->select();
		$this->assign('select', $list); 
		$this->assign('pages', $page->show());  
		$this->assign('flag', I('get.flag','sys')); 
		$this->assign('type', 1);
		$this->display('Case_list');
    }

	//用户展示
	public function userList() {
		$_scene = M('yqxScene');
		$scenetype = intval(I('get.tagId',0));
		$keyword   = trim(I('get.keyword'));
		
		$where['userid_int']  = array('gt', 0);
		
		if($scenetype > 0) {
			$where['tagid_int']  = $scenetype;
		}
		if($keyword != ''){
			$where['scenename_varchar']  = array('like','%'.$keyword.'%');
		}
		
		$page = new \Think\Page($_scene->where($where)->count(), 10);
		$list=$_scene->where($where)->order('sceneid_bigint desc')->limit($page->firstRow, $page->listRows)->select();
		$this->assign('select', $list); 
		$this->assign('pages', $page->show());  
		$this->assign('flag', I('get.flag','sys'));  
		$this->assign('type', 2);
		$this->display('Case_list');
	}
	
	//修改模板
	public function editCase() {
		if(IS_POST){
			M('yqxScene')->save( $_POST['info'] );
			$this->display('Jump:success'); 
		} else {
			$userinfo=	M('yqxScene')->where( array('sceneid_bigint'=>I('get.id')) )->find();
			$this->assign('user', $userinfo ? $userinfo : array()); 
		 	$this->display();
		}
	}
	
	//上传缩略图
	public function updateScene() {
		$uploadROOT = realpath(THINK_PATH.'../Public/');
		$uploadSubPath = '/Yqx/';
		$subName = array('date','Y-m-d');

		$uploadPath =$uploadROOT.$uploadSubPath;
        if(!file_exists($uploadPath)) mkdirs($uploadPath,  0775);
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'subName'	=> $subName,
			'exts'		=> 'jpg,jpeg,png',
			'maxSize'	=> 102400
		);

		$attachment = new \Think\Upload( $uploadConfig );
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        if($attachmentInfo && is_array($attachmentInfo)) {
            echo json_encode(array('error'=>0, 'url'=>($subName?date('Y-m-d').'/':'').$attachmentInfo['savename'], 'savename'=>basename($attachmentInfo['savename'])));
        } else {
            echo json_encode(array('error'=>1, 'message'=>$attachment->getError()));
        }

    }

	//删除模板
	public function delCase() {
		$id = I('post.id', ''); if( !$id ) exit('0'); 
        exit(M('yqxScene')->where( array('sceneid_bigint'=>array('in', "$id")) )->delete() ? "1" : "0");
	}
	
	//设置状态
	public function setCase() {
		$type = I('post.type') or exit('0');
		$id = I('post.id') or exit('0');
		$status = I('post.status', 0, 'intval') == 1 ? 0 : 1;
		exit(M('yqxScene')->where( array('sceneid_bigint'=>array('in', "$id")) )->setField('delete_int', $status) ? "1" : "0");		
	}
}