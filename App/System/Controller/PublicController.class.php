<?php
namespace System\Controller;
use Think\Controller;

class PublicController extends Controller {
    public function login() {
		if( !isset($_SESSION[C('USER_AUTH_KEY')]) || !\Common\Org\Cookie::get('mid') ) {
			$this->display('Index:login');
		} else {
			$this->redirect(U('Index/index@xt', '', false, true));
		}
    }

    public function checklogin() {
		$verify		 = new \Think\Verify();
		// mtype 类型 0:系统管理员 1:代理商   2:商家   3:会员
		$member = M("member")->alias('AS m')->join('__AUTH_GROUP__ AS g ON m.gid=g.id', 'left')->field('m.mid,m.gid,m.mpwd,m.mstatus,m.mname,g.status as gstatus')->where(array("m.mname"=>I("post.username"), "m.mtype"=>0))->find();
		if($member) {
			if(! $verify->check($_POST['verify'], 'loginVerify')) { exit('1'); }
			if( md5(md5(I('post.password'))) != $member['mpwd'] ) { exit('2'); }
			if($member['mstatus']==0){ exit('3'); }
			if( $member['gid']!=0 && $member['gstatus']!=1 ) { exit('6'); }
			$_SESSION[C('USER_AUTH_KEY')] = $member['mid'];
			\Common\Org\Cookie::set('mid', $member['mid']);
            \Common\Org\Cookie::set('mname', $member['mname']);
			\Common\Org\Cookie::set('groupid', $member['gid']);
			if($member['mname'] == 'admin') { $_SESSION['administrator'] = true; }
			exit('4');
		} else {
			exit('5');
		}
    }

    public function logout(){
    	if(isset($_SESSION[C('USER_AUTH_KEY')]) && \Common\Org\Cookie::get('mid')) {
			unset($_SESSION[C('USER_AUTH_KEY')], $_SESSION); session_destroy();
			\Common\Org\Cookie::delete(array('mid', 'mname'));
			$this->success("退出成功", U('Public/login@xt', '', true, true));
		} else {
			$this->success("已经退出", U('Public/login@xt', '', true, true));	
		}
    }
	
	//上传图片

    public function kindeditorUpload() {

		//if(stripos($_SERVER['HTTP_REFERER'], 'dishuos.com') === false ) E('你无权进行此操作');

		$uploadROOT = realpath(THINK_PATH.'../Public/');//上传地址的根目录

		
		if(urldecode(I('get.custompath'))){
			$uploadSubPath = str_replace('|','/',I('get.custompath'));//上传地址的子目录
			$subName = null;
		}else{
			$uploadSubPath = '/Upload/';//上传地址的子目录
			$subName = array('date','Y-m-d');
		}
		$uploadPath =$uploadROOT.$uploadSubPath;
        if(!file_exists($uploadPath)) mkdirs($uploadPath,  0775);
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'subName'	=> $subName,
			'exts'		=> 'jpg,jpeg,png,apk,ipa',
			'maxSize'	=> 30720000
		);

		if( isset($_GET['conf']) && !empty($_GET['conf']) ) {

			$_UploadConfig = C( "UPLOAD_".strtoupper(trim($_GET['conf'])) );

			if( isset($_UploadConfig['rootPath']) && file_exists($_UploadConfig['rootPath']) ) {

				$uploadConfig['rootPath'] = $_UploadConfig['rootPath'];	

			}

			if( isset($_UploadConfig['exts']) && !empty($_UploadConfig['exts']) ) {

				$uploadConfig['exts'] = $_UploadConfig['exts'];

			}

			if( isset($_UploadConfig['maxSize']) && !empty($_UploadConfig['maxSize']) ) {

				$uploadConfig['maxSize'] = $_UploadConfig['maxSize'];

			}

		}

		$attachment = new \Think\Upload( $uploadConfig );
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        if($attachmentInfo && is_array($attachmentInfo)) {
            echo json_encode(array('error'=>0, 'url'=>'/Public'.$uploadSubPath.($subName?date('Y-m-d').'/':'').$attachmentInfo['savename'], 'savename'=>basename($attachmentInfo['savename'])));
        } else {
            echo json_encode(array('error'=>1, 'message'=>$attachment->getError()));
        }

    }
	
	// 验证码
	public function verify() {
		$verify = new \Think\Verify(array(
				'fontSize'			=> I('get.fontSize', 15, 'intval'),
				'imageH'			=> I('get.height', 25, 'intval'),
				'imageW'			=> I('get.width', 59, 'intval'),
				'length'			=> I('get.lenth', 4, 'intval'),
				'useNoise'			=> false,
				'fontttf'			=> '7.ttf',
				'codeSet'			=> '1234567890123456789012345678901234567890'
		));
		$verify->entry('loginVerify');
	}

}