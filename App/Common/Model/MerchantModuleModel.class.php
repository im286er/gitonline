<?php
namespace Common\Model;
use Think\Model;

class MerchantModuleModel extends Model {
	protected $ModuleCache = 'MerchantModuleCache';

	protected $_validate = array(
    );
	
	//强制更新
	public function runCache($force=false) {
		$data = array();
		$data = S($this->ModuleCache);
		$countModule = count($data);
		
		$count = $this->where(array('module_status'=>'1'))->count("module_id");
		
		if($countModule!=$count || $force==true){
			$data = array();
			$list = $this->where(array('module_status'=>'1'))->order('module_pid asc')->select();
			if($list)foreach($list as $value){
				if($value['module_pid']){
					$data[$value['module_sign']]['submodule'][] = $value;
				}else{
					$data[$value['module_sign']] = $value;
				}
			}
			S($this->ModuleCache,$data,1000000);
		}
		return $data;
	}

	//获取商家模块
	public function getMerchantModule($module=null){
		$data = $pids = $parentmap = $map = array();
		$modules = explode(',',$module);
		//if(count($modules)<1)return false;
		$map = array();
		$map['module_status'] = '1';
		$map['module_pid'] = array('gt',0);
		$map['module_sign']  = array('in',$modules);
		$modulesdata = $this->where($map)->order('module_pid asc')->select();
		if($modulesdata)foreach($modulesdata as $key => $value){
			$datas[$value['module_pid']][] = $value;
		}
		$parentmap = array();
		$parentmap['module_status'] = '1';
		$parentmap['module_id']  = array('in',array_keys($datas));
		$parents = $this->where($parentmap)->order('module_id asc')->select();
		foreach($parents as $key => $value){
			$data[$value['module_id']] = $value;
			$data[$value['module_id']]['children'] = $datas[$value['module_id']];
		}
		return $data;
	}


}

?>