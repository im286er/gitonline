<?php
namespace Agent\Controller;
use Think\Controller;

class PublicController extends Controller {

    public function login() {
		if( !isset($_SESSION[C('USER_AUTH_KEY')]) || !\Common\Org\Cookie::get('mid') ) {
			$this->display('Index:login');
		} else {
			$this->redirect(U('Index/index@dl', '', false, true));
		}
    }

	public function checklogin() {
		$verify		 = new \Think\Verify();
		// mtype 类型 0:系统管理员 1:代理商   2:商家
		$member = M("member")->alias('AS m')->join('__AGENT__ AS a ON m.mid=a.mid')->where(array("m.mname"=>I("post.username"), "m.mtype"=>1))->find();
		
		if($member) {
			if( md5(md5(I('post.password'))) != $member['mpwd'] ) { exit('2'); }
			if($member['mstatus'] != 1) { exit('3'); }
			if(! $verify->check($_POST['verify'], 'loginVerify')) exit('1');
			
			\Common\Org\Cookie::set('agentid', $member['id']); 
			\Common\Org\Cookie::set('mid', $member['mid']); 
			\Common\Org\Cookie::set('mname', $member['mname']);
			
			$_SESSION[C('USER_AUTH_KEY')] = $member['mid'];
			$_SESSION['member']['mid']=\Common\Org\Cookie::get('mid');
			exit('4');
		} else{ exit('6'); }
    }

    public function logout(){
    	if(isset($_SESSION[C('USER_AUTH_KEY')]) && \Common\Org\Cookie::get('mid')) {
			unset($_SESSION[C('USER_AUTH_KEY')], $_SESSION); session_destroy();
			\Common\Org\Cookie::delete(array('mid', 'mname'));
			$this->success("退出成功", U('Public/login@dl', '', true, true));
		} else {
			$this->success("已经退出", U('Public/login@dl', '', true, true));	
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

	//上传图片
    public function kindeditorUpload() {
		if(stripos($_SERVER['HTTP_REFERER'], 'dishuos.com') === false ) E('你无权进行此操作');
	    $uploadPath = realpath(THINK_PATH.'../Public').'/Upload/';
        if(!file_exists($uploadPath)) mkdir($uploadPath, true);
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'exts'		=> 'jpg,jpeg,png',
			'maxSize'	=> 204800
		);

		$attachment = new \Think\Upload( $uploadConfig );
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        if($attachmentInfo && is_array($attachmentInfo)) {
            echo json_encode(array('error'=>0, 'url'=>'/Public/Upload/'.date('Y-m-d').'/'.$attachmentInfo['savename'], 'savename'=>basename($attachmentInfo['savename'])));
        } else {
            echo json_encode(array('error'=>1, 'message'=>$attachment->getError()));
        }
    }	

}