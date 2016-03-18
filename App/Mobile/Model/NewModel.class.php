<?php
namespace Mobile\Model;
use Think\Model;

class NewModel extends Model {

	protected $_validate = array(
    );

	//强制更新
	public function getNew($jid,$condition=array(),$num=3,$order='new_id desc') {
		if(!$jid)return false;
		$data = array();
		$where = array('new_jid'=>$jid,'new_status' => 1);
		if($condition)$where = array_merge($where,$condition);
		$data = $this->where($where)->order($order)->limit($num)->select();
		return $data;
	}

	public function getModule($jid){
		if(!$jid)return false;
		$NewModule = array('Name'=>'最新资讯','Icon'=>'/Public/Mobile/default/img/ico_mart.png');
		$path = APP_DIR.'/Public/Data/'.$jid.'/';
		file_exists($path.'NewModule.php') && $NewModule=unserialize(file_get_contents($path.'NewModule.php'));
		$NewModule['Link'] = U('News/index@yd', array('jid'=>$jid));
		return $NewModule;
	}

}

?>