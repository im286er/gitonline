<?php
namespace Common\Model;
use Think\Model;
class MemberPaterModel extends Model{
	public  $error='';
	protected $mtype=array(1=>'admin',2=>'agent',3=>'merchant');
	protected function get_PREFIX(){
		return C('DB_PREFIX');
	}
	/**
	 * 会员登录验证
	 * @param array $data 数组
	 * @return mixed
	 */
	public function login($data){
		if($data['mpwd']){
			$data['mpwd']=md5($data['mpwd']);
		}
		$res_one=$this->table($this->get_PREFIX().'member')->where(array('mname'=>$data['mname'],'mstatus'=>1))->find();
		if($res_one){
			if($data['mpwd']==$res_one['mpwd']){
				return $res_one;
			}else{
				$this->error='密码不正确';
				return false;
			}
		}else{
			$this->error='帐号不存在或者帐号被禁用';
			return false;
		}
	}
	/**
	 * 根据mid获取对应的会员详情
	 * @param integer $mid 数组
	 * @return mixed
	 */
	public function get_member($mid){
		$res_one=$this->table($this->get_PREFIX().'member')-where(array('mid'=>$mid))->find();
		$my_mtype=$this->mtype;
		$res_one2=$this->table($this->get_PREFIX().$res_one['mtype'])->where(array('mid'=>$mid))->find();
		return array_merge($res_one,$res_one2);
	}
	/**
	 * 更新会员信息，有mid代表修改,无则添加
	 * @param array $data 数组
	 * @return mixed
	 */
	public function save_member($data){
		if($data['mid']>0){ //更新
			$member_data=array();
			if($data['mpwd'])$member_data['mpwd']=md5(trim($data['mpwd']));
			if($data['mstatus'])$member_data['mstatus']=$data['mstatus'];
			if($data['mlogindate'])$member_data['mlogindate']=$data['mlogindate'];
			if(count($member_data)>0){ //更新主表
				$re1=$this->table($this->get_PREFIX().'member')->where(array('mid'=>$data['mid']))->save($member_data);
			}
			if($data['group_id']){
				$Auth_data=array('mid'=>$data['mid'],'group_id'=>$data['group_id']);
			}
			if(!$data['mtype']){
				$res_member=$this->table($this->get_PREFIX().'member')->where(array('mid'=>$data['mid']))->find();
				$data['mtype']=$res_member['mtype'];
				unset($res_member);
			}
			//更新附表
			$res_mtype=$this->table($this->get_PREFIX().$data['mtype'])->where(array('mid'=>$data['mid']))->find();
			unset($res_mtype['mid']);
			$mtype_data=array();
			foreach ($res_mtype as $key=>$val){
				if($data[$key])$mtype_data[$key]=$data[$key];
			}
			if(count($mtype_data)>0){
				$re2=$this->table($this->get_PREFIX().$data['mtype'])->where(array('mid'=>$data['mid']))->save($mtype_data);
			}
		}else{  //添加
			$member_data=array('mname'=>$data['mname'],'mpwd'=>md5(trim($data['mpwd'])),'mtype'=>$data['mtype'],'mstatus'=>$data['mstatus'],'mregdate'=>date('Y-m-d H:i:s'));
			$re1=$this->table($this->get_PREFIX().'member')->add($member_data);
			$Auth_data=array('mid'=>$data['mid'],'group_id'=>$data['group_id']);
			$data['mid']=$re1;
			$mtype_Fields=$this->table($this->get_PREFIX().$data['mtype'])->getDbFields();
			$mtype_data=array();
			foreach ($mtype_Fields as $key=>$val){
				if($data[$key])$mtype_data[$key]=$data[$key];
			}
			$re2=$this->table($this->get_PREFIX().$data['mtype'])->add($mtype_data);
		}
		$Auth_obj=D('Auth');
		$Auth_obj->save_group_access($Auth_data);
		return $data['mid'];
	}
	/**
	 * 查询多条会员记录
	 * @param array $data 数组  $data['where']=array()  $data['mtype']='admin|agent...' $data['limit']='N,N' 
	 * @return mixed
	 */
	public function select_member($data){
		$a=$this->get_PREFIX().'member';
		$b=$this->get_PREFIX().$data['mtype'];
		$list=$this->table($a)->join($b.'  on '.$a.'.mid='.$b.'.mid')->where($data['where'])->order('mid desc')->limit($data['limit'])->select();
		$Auth_obj=D('Auth');
		foreach ($list as $val){
			$temp=$Auth_obj->get_group_access($val['mid']);
			$val['auth_group_title']=$temp['title'];
			$new_list[]=$val;
		}
		return $new_list;
	}
	/**
	 * 根据mid删除单条会员信息
	 * @param array mid 数组
	 * @return mixed
	 */
	public function del_member(){
		
	}
	public function demo(){
		var_dump($this->table('azd_merchant')->getDbFields());
	}
}