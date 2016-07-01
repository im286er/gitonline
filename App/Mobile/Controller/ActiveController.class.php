<?php
namespace Mobile\Controller;
/*活动控制器
 *
*
* */

class ActiveController extends MobileController {
	
	public $action_name = 'Active';
	/*活动列表
	 *
	*
	* */
	public function index(){
		
		$active = M('active');
		$opt = array(
				'av_jid'    => $this->jid,
				'av_sid'    => $this->sid,
				'av_status' => 1
		);
		$active_list = $active->where($opt)->order('av_id desc')->select();
		
		$this->assign('page_url',U('Index/index',array('jid'=>$this->jid)));
		
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'HdModule.php') && $HdModule=unserialize(file_get_contents($path.'HdModule.php'));
		$this->assign('page_name', $HdModule ? $HdModule['Name'] : '最新活动');

		$this->assign('active_list',$active_list);
		$this->mydisplay();
	}
	
	/*活动详情
	 *
	*
	* */
	public function info(){
		
		$av_id = I("av_id");
		$active = M('active');
		$opt = array(
				'av_jid'    => $this->jid,
				'av_status' => 1,
				'av_id' => $av_id
		);
		$active_info = $active->where($opt)->find();
				//$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		//file_exists($path.'HdModule.php') && $HdModule=unserialize(file_get_contents($path.'HdModule.php'));
		$this->assign('page_name', $active_info['av_title']);
		$page_url = U('Index/new2Activity',array('jid'=>$this->jid,'sid'=>$this->sid,'cid'=>$active_info['av_cid']));
		$this->assign('page_url',$page_url);
		$this->assign('active_info',$active_info);
		$this->mydisplay();
	}
}