<?php
/*
 * 系统管理
 */
namespace Agent\Controller;
use Common\Controller\ManagerController;

class SystemController extends ManagerController {
    public function staff(){//员工管理 员工列表
    	
        $this->display();
    }
    public function staff_add(){//员工管理 添加员工
    	 
    	$this->display();
    }
    public function staff_up(){//员工管理 修改员工
    	 
    	$this->display();
    }
    public function staff_del(){//员工管理 删除员工
    
    	$this->display();
    }
    
    public function auth_group(){//权限管理 组列表
    	$auth_group_obj=D('Auth');
    	$list=$auth_group_obj->select_group();
    
    	$this->assign('list',$list);
    	$this->display();
    }
    public function auth_group_add(){//权限管理 添加组
    	$this->display();
    }
    public function auth_group_up(){//权限管理 修改组
    	$this->display();
    }
    public function auth_group_del(){//权限管理 删除组
    	$this->display();
    }
    
    public function auth_group_rule(){//权限管理 节点列表
    	$this->display();
    }
    public function auth_group_rule_add(){//权限管理 添加节点
    	$this->display();
    }
    public function auth_group_rule_up(){//权限管理 修改节点
    	$this->display();
    }
    public function auth_group_rule_del(){//权限管理 删除节点
    	$this->display();
    }
	
	//修改个人信息
	public function publicEditInfo() {
		if( IS_POST ) {
			array_walk($_POST['member'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$mid = I('post.mid', 0, 'intval'); if( !$mid ) $this->display('Jump:error');
			
			if( isset($_POST['member']['mpwd']) && !empty($_POST['member']['mpwd']) ) {
				$_POST['member']['mpwd'] = md5(md5($_POST['member']['mpwd']));
				if( M('member')->where(array('mid'=>$mid))->save($_POST['member']) !== false ) {
					$this->success("密码修改成功稍后请重新登录", U('Public/logout@dl'));      
				} else {  $this->error('操作失败'); }
			} else {
            	unset( $_POST['member']['mpwd'] ); 	
				if( M('member')->where(array('mid'=>$mid))->save($_POST['member']) !== false ) {
					$this->success('操作成功');
				} else {  $this->error('操作失败'); }	
			}
		} else {
			$mid = \Common\Org\Cookie::get('mid');
			$this->assign('member', M('member')->where(array('mid'=>$mid))->find());
			$this->display();
		}
	}   
	
	    //上传图片
    public function publicKindeditorUpload() {
        $uploadPath = realpath(THINK_PATH.'../Public').'/Upload/';
        if(!file_exists($uploadPath)) mkdir($uploadPath, true);
        $attachment = new \Think\Upload( array('rootPath'=>$uploadPath) );
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        
        if($attachmentInfo && is_array($attachmentInfo)) {
            echo json_encode(array('error'=>0, 'url'=>'/Public/Upload/'.date('Y-m-d').'/'.$attachmentInfo['savename'], 'savename'=>basename($attachmentInfo['savename'])));
        } else {
            echo json_encode(array('error'=>1, 'message'=>$attachment->getError()));
        }
    }
	//AJAX验证账户是否存在
	public function publicCheckMname() {
		$member = M('member')->where(array('mname'=>I('get.mname')))->find();
		$status = !empty($member) && is_array($member) ? "0" : "1";
		$mid  = I('get.mid', '', 'intval');
		if( !$status && $mid) { if($mid == $member['mid']) $status = "1"; } 
		exit( $status );
	}
	//检测密码

 	public function memberCha(){
     $id =M("member")->where("mid=".$_SESSION['member']['mid'])->find();

     $mpwd=md5(md5(I('get.ympwd')));

     if($mpwd==$id['mpwd']){$data=1; }	  

	else{$data=0;}	

		

	echo $data;	

	}
}