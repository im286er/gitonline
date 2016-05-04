<?php

namespace Merchant\Controller;

use Think\Controller;



class PublicController extends Controller {

    public function login() {

		if( !isset($_SESSION[C('USER_AUTH_KEY')]) || !\Common\Org\Cookie::get('mid') ) {

			$this->display('Index:login');

		} else {

			$this->redirect(U('Index/index@sj', '', false, true));

		}

    }


    public function checklogin() {

			$verify		 = new \Think\Verify(); 

	

		// mtype 类型 0:系统管理员 1:代理商   2:商家   3:会员

		$member = M("member")->alias('m')->where(array("m.mname"=>I("post.username"), "m.mtype"=>2))

							 ->join('__MERCHANT_USER__ AS u ON m.mid=u.tmid')

							 ->join('__MERCHANT__ AS j ON j.jid=u.tjid')

							 ->field('m.mid,m.mname,m.mpwd,m.mstatus,j.jid')->find();

		
	
	

		if($member) {

	     $merchantmid=M("merchant")->where("jid=".$member['jid'])->find();

		$merchantStatus=M("member")->where("mid=".$merchantmid['mid'])->find();

		if($merchantStatus['mstatus']==1){

			if( md5(md5(I('post.password'))) != $member['mpwd'] ) { $data=2;echo $data;exit;}

			if($member['mstatus']==0) 	{$data=3;echo $data;exit;}

			if(! $verify->check($_POST['verify'], 'loginVerify')) {$data=1;echo $data;exit;}

            $_SESSION[C('USER_AUTH_KEY')] = $member['mid'];

		    \Common\Org\Cookie::set('mid', $member['mid']);

			\Common\Org\Cookie::set(C('USER_COOKIE_JID'), $member['jid']);

			//\Common\Org\Cookie::set(C('USER_COOKIE_SID'), $member['tsid']);

			//\Common\Org\Cookie::set(C('USER_COOKIE_TPE'), $member['type']);

			

			if($member['mname'] == 'admin') { $_SESSION['administrator'] = true; }

		   $data=4; echo $data;exit;}else{$data=5; echo $data;exit;}

		           }

		else {

			$data=6; echo $data;exit;

		}

    }

	

	//退出管理中心

    public function logout(){

    	if(isset($_SESSION[C('USER_AUTH_KEY')]) && \Common\Org\Cookie::get('mid')) {

			unset($_SESSION[C('USER_AUTH_KEY')], $_SESSION); session_destroy();

			\Common\Org\Cookie::delete(array('mid', C('USER_COOKIE_JID'), C('USER_COOKIE_SID'), C('USER_COOKIE_TPE')));

			$this->success("退出成功", U('Public/login@sj', '', true, true));

		} else {

			$this->success("已经退出", U('Public/login@sj', '', true, true)); 	

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
        if(!file_exists($uploadPath)) mkdirs($uploadPath,  0777);
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'subName'	=> $subName,
			'exts'		=> 'jpg,jpeg,png',
			'maxSize'	=> 256000
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
            echo json_encode(array('error'=>0, 'url'=>'/Public'.$uploadSubPath.($subName?date('Y-m-d').'/':'').$attachmentInfo['savename'], 'imglink'=>I('post.imglink'), 'imgtext'=>I('post.imgtext'), 'savename'=>basename($attachmentInfo['savename'])));
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