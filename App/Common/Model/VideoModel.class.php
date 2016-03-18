<?php
namespace Common\Model;
use Think\Model;

class VideoModel extends Model {
	protected $_validate = array(
        array('gname', '1,50', '视频名称不能为空', 1, 'length'),
        array('cid', '/^[1-9]\d*$/', '视频分类不能为空', 1, 'regex'),
        array('gfile', 'checkFile', '视频文件不能为空', 1, 'callback'),
    );
	
	public function insert($data='', $options=array(), $replace=false) {
		$data = $this->create($data, 1);
		if( !$data ) return false;
		return $this->add($data, $options, $replace); 
	}
	
	public function update($data='', $options=array()) {
		$data = $this->create($data, 1);
		if( !$data ) return false;
		return $this->save($data, $options); 
	}
	
	public function checkFile( $args ) {
		if(strstr($args,'iframe'))return true;
		$filePath = rtrim(APP_DIR, '/').$args;
		return file_exists($filePath) ? true : false;
	}

}

?>