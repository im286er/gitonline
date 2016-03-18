<?php
namespace Mobile\Model;
use Think\Model;

class VideoModel extends Model {

	protected $_validate = array(
    );

	//强制更新
	public function getVideo($condition=array()) {
		$data = $this->select($condition);
		return $data;
	}

	//强制更新
	public function getVideoList($jid,$condition=array(),$num=3,$order='gorder asc') {
		if(!$jid)return false;
		$data = array();
		$where = array('jid'=>$jid,'gstatus' => 1);
		if($condition)$where = array_merge($where,$condition);
		$data = $this->where($where)->order($order)->limit($num)->select();
		return $data;
	}

	public function getModule($jid){
		if(!$jid)return false;
		$path = APP_DIR.'/Public/Data/'.$jid.'/';
		$VideoModule = array('Name'=>'微视频','Icon'=>'/Public/Images/mobile/002.png');
		file_exists($path.'InfoMenu3Name.php') && $VideoModule['Name']=file_get_contents($path.'InfoMenu3Name.php');
		file_exists($path.'InfoMenu3Icon.php') && $VideoModule['Icon']=file_get_contents($path.'InfoMenu3Icon.php');
		$VideoModule['Link'] = U('Video/index@yd', array('jid'=>$jid));
		return $VideoModule;
	}






}

?>