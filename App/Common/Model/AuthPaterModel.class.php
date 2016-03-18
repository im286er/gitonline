<?php
namespace Common\Model;
use Think\Model;
class AuthPaterModel extends Model{
	private function get_PREFIX(){
		return C('DB_PREFIX');
	}
	/**
	 * 更新auth_rule表 传主键则是修改否则是添加
	 * @param array $data 数组
	 * @return mixed
	 */
	public function save_rule($data){
		if(is_array($data)){
			if($data['id']>0){
				$key=$data['id'];
				unset($data['id']);
				return $this->table($this->get_PREFIX()."auth_rule")->where(array("id"=>$key))->save($data);
			}else{
				return $this->table("auth_rule")->add($data);
			}
		}
	}
	/**
	 * 删除auth_rule表 传$data则根据条件否则全部删除
	 * @param array $data 数组
	 * @return mixed
	 */
	public function del_rule(array $data=array()){
		if(count($data)>0){
			return $this->table($this->get_PREFIX()."auth_rule")->where($data)->delete();
		}else{
			return $this->table($this->get_PREFIX()."auth_rule")->delete();
		}
	}
	/**
	 * 查询auth_rule表的记录数 传$data则根据条件否则全部查询
	 * @param array $data 数组
	 * @return mixed
	 */
	public function count_rule(array $data=array()){
		if(count($data)>0){
			return $this->table($this->get_PREFIX()."auth_rule")->where($data)->count();
		}else{
			return $this->table($this->get_PREFIX()."auth_rule")->count();
		}
	}
	/**
	 * 查询auth_rule表 传$data则根据条件否则全部查询
	 * @return mixed
	 */
	public function get_rule(){
		return $this->table($this->get_PREFIX().'auth_rule');
	}
	
	
	/**
	 * 更新auth_group表 传主键则是修改否则是添加
	 * @param array $data 数组
	 * @return mixed
	 */
	public function save_group($data){
		if(is_array($data)){
			if($data['id']>0){
				$key=$data['id'];
				unset($data['id']);
				return $this->table($this->get_PREFIX()."auth_group")->where(array("id"=>$key))->save($data);
			}else{
				return $this->table($this->get_PREFIX()."auth_group")->add($data);
			}
		}
	}
	/**
	 * 删除auth_group表 传$data则根据条件否则全部删除
	 * @param array $data 数组
	 * @return mixed
	 */
	public function del_group($data){
		if(count($data)>0){
			return $this->table($this->get_PREFIX()."auth_group")->where($data)->delete();
		}else{
			return $this->table($this->get_PREFIX()."auth_group")->delete();
		}
	}
	/**
	 * 查询auth_group多条记录 
	 * @return mixed
	 */
	public function select_group(){
		$list=$this->table($this->get_PREFIX()."auth_group")->select();
		return $list;
	}
	
	
	/**
	 * 更新auth_group表 传主键则是修改否则是添加
	 * @param array $data 数组
	 * @return mixed
	 */
	public function save_group_access($data){
		if(is_array($data)){
			$res_one=$this->table($this->get_PREFIX()."auth_group_access")->where(array('mid'=>$data['mid']))->find();
			if($res_one){
				$key=$data['mid'];
				unset($data['mid']);
				return $this->table($this->get_PREFIX()."auth_group_access")->where(array("mid"=>$key))->save($data);
			}else{
				return $this->table($this->get_PREFIX()."auth_group_access")->add($data);
			}
		}
	}
	/**
	 * 根据mid查询会员的权限组 
	 * @param array $mid 数组
	 * @return mixed
	 */
	public function get_group_access($mid){
		$a=$this->get_PREFIX()."auth_group_access";
		$b=$this->get_PREFIX()."auth_group";
		$res_one=$this->table($a)->join($b.' on '.$a.'.group_id='.$b.'.id')->where(array('mid'=>$mid))->find();
		return $res_one;
	}
}