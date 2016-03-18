<?php
namespace Mobile\Controller;
/*资讯控制器
 *
*
* */

class NewsController extends MobileController {
	
	public $action_name = 'News';
	/*资讯列表
	 *
	*
	* */
	public function index(){
		
		$active = M('new');
		$opt = array(
				'new_jid'    => $this->jid,
				'new_status' => 1
		);
		$active_list = $active->where($opt)->order('new_id desc')->select();
		
		$this->assign('page_url',U('Index/index',array('jid'=>$this->jid)));
		
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'NewModule.php') && $NewModule=file_get_contents($path.'NewModule.php');
		$NewModuleC = unserialize($NewModule);
		$this->assign('page_name', $NewModuleC['Name'] ? $NewModuleC['Name'] : '最新资讯');

		$this->assign('active_list',$active_list);
		$this->mydisplay();
	}
	
	/*资讯详情
	 *
	*
	* */
	public function info(){
		
		$av_id = I("new_id");
		$active = M('new');
		$opt = array(
				'new_jid'    => $this->jid,
				'new_status' => 1,
				'new_id' => $av_id
		);
		$active_info = $active->where($opt)->find();
				$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'NewModule.php') && $NewModule=file_get_contents($path.'NewModule.php');
		$NewModuleC = unserialize($NewModule);
		$this->assign('page_name', $NewModuleC['Name'] ? $NewModuleC['Name'] : '最新资讯');
		$page_url = I("from_index") == 1 ? U('Index/index',array('jid'=>$this->jid)) : U('News/index',array('jid'=>$this->jid));
		$this->assign('page_url',$page_url);
		$this->assign('active_info',$active_info);
		$this->mydisplay();
	}
}