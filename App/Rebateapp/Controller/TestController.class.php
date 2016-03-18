<?php
namespace Rebateapp\Controller;
use Think\Controller;
/* * *我的 * * */
class TestController extends Controller {

    public function index(){
		print_r(C('default_theme'));
    }
    //后置操作方法

    public function index1(){
		exit();
		header("Content-type: text/html; charset=utf-8");
		$is_check = M('Class')->where(array('jid'=>425,'sid'=>'0','status'=>'1'))->order('cid asc')->find();
		if($is_check){echo '已经操作过此项步';exit;}
		$class = M('Class')->where(array('jid'=>388,'sid'=>'0','status'=>'1'))->order('cid asc')->select();
		$jids = array('425','426','427','428','429','430','431','432','433','434','435','436','437'
		);
		foreach($jids as $value){
			$dataList =  array();
			foreach($class as $k => $v){
				$dataList[] = array('cname'=>$v['cname'],'jid'=>$value,'sid'=>0,'corder'=>$v['corder'],'ctype'=>$v['ctype'],'status'=>$v['status'],'print_id'=>$v['print_id']);
			}
			//$result	= M('Class')->addAll($dataList);
			echo $result?$value.'第一步成功<br/>':$value.'第一步失败<br/>';
		}
    }
	
	public function index2(){
		exit();
		header("Content-type: text/html; charset=utf-8");
		$class = M('Class')->where(array('jid'=>388,'sid'=>'0','status'=>'1'))->getField('cid,cname');
	 	$goods = M('Goods')->alias('g')->join('__CLASS__ c ON g.cid= c.cid')->where(array('g.cid'=>array('in',array_keys($class) ),'g.sid'=>'0','g.gstatus'=>'1'))->select();
		$jids = array('425','426','427','428','429','430','431','432','433','434','435','436','437');
		foreach($jids as $value){
			$dataList =  array();
			$newclass = M('Class')->where(array('jid'=>$value,'sid'=>'0','status'=>'1'))->getField('cid,cname,ctype');
			foreach($newclass as $k => $v){
				foreach($goods  as $val){
						if($v['cname']==$val['cname'] && $v['ctype']==$val['ctype'] ){	
							$datas = array();
							$datas['gname'] = $val['gname'];
							$datas['gdescription'] = $val['gdescription'];
							$datas['gcontent'] = $val['gcontent'];
							$datas['cid'] =$k;
							$datas['sid'] = 0;
							$datas['goprice'] = $val['goprice'];
							$datas['gdprice'] = $val['gdprice'];
							$datas['gstock'] = $val['gstock'];
							$datas['gimg'] = $val['gimg'];
							$datas['pictureset'] = $val['pictureset'];
							$datas['gorder'] = $val['gorder'];
							$datas['gtype'] = $val['gtype'];
							$datas['gstatus'] = $val['gstatus'];
							$datas['gvrebate'] = $val['gvrebate'];
							$datas['printid'] = $val['printid'];
							$datas['isboutique'] = $val['isboutique'];
							$dataList[] = $datas;
						}
				}
			}
			//$result = M('Goods')->addAll($dataList);
			//($dataList);
			echo $result?$value.'第二步成功<br/>':$value.'第二步失败<br/>';
		}
	
	
	}




}


