<?php
namespace Mobile\Model;
use Think\Model;

class ActiveModel extends Model {

	protected $_validate = array(
    );

	//强制更新
	public function getActive($jid,$condition=array(),$num=5,$order='av_id desc',$filed=true) {
		if(!$jid)return false;
		$data = array();
		$where = array('av_jid'=>$jid,'av_status'=>1);
		if($condition)$where = array_merge($where,$condition);
		$data = $this->field($filed)->where($where)->order('av_id desc')->limit($num)->select();
		return $data;
	}

	public function getModule($jid){
		if(!$jid)return false;
		$path = APP_DIR.'/Public/Data/'.$jid.'/';
		$HdModule = array('Name'=>'最新活动','Icon'=>'');
		file_exists($path.'HdModule.php') && $HdModule=unserialize(file_get_contents($path.'HdModule.php'));
		$HdModule['Link'] = U('Active/index@yd', array('jid'=>$jid));
		return $HdModule;
	}

}

?>