<?php
namespace Common\Model;
use Think\Model;
class AgentPaterModel extends Model{
	/**
	 * 更新代理商会员信息，有mid代表修改, 无则添加
	 * @param array $data 数组
	 * @return mixed
	 */
	public function save_agent($data){
		$member_obj=D('Member');
		return $member_obj->save_member($data);
	}

	/**
	 * 根据mid获取单条代理商会员信息
	 * @param array mid 数组
	 * @return mixed
	 */
	public function get_agent($mid){
		$member_obj=D('Member');
		return $member_obj->get_member($mid);
	}
	
	/**
	 * 根据mid删除单条代理商会员信息
	 * @param array mid 数组
	 * @return mixed
	 */
	public function del_agent(){
		
	}
}