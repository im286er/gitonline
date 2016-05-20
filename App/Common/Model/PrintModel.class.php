<?php
namespace Common\Model;
use Think\Model;

class PrintModel extends Model {
	
	/* 打印订单
	 * $oid:订单号
	 * $type:打印时机  1:下单后  2:确认后 3:完成后 4:后台结算
	 * */
	public function doPrint($oid,$type){
		$orderInfo = M("order")->where(array('o_id'=>$oid))->find();
		$sname = M('shop')->where("sid=".$orderInfo['o_sid'])->getField('sname');
		$goods = M('goods_snapshot')->where(array('sp_oid'=>$oid,'sp_status'=>1))->select();
		
		foreach($goods as $k=>$v){
			$goods[$k]['print_id'] = $this->getPrintId($v['sp_gid']);
		}
		if($type == 4){
			$print_list = M('print')->where(array('is_pay'=>1,'print_status'=>1,'print_sid'=>$orderInfo['o_sid']))->select();
		}else{
			$print_list = M('print')->where(array('print_time'=>array('like','%'.$type.'%'),'print_status'=>1,'print_sid'=>$orderInfo['o_sid']))->select();
		}
		
		foreach($print_list as $kk=>$vv){
			$print_info = array();
			$print_info['ShangHuID'] = $orderInfo['o_sid'];
			$print_info['ShangHuName'] = $sname;
			$print_info['Printer'] = $vv['print_name'];
			$print_info['is_balance'] = $vv['is_balance'];
			$print_info['is_pay'] = $orderInfo['o_dstatus']==4  ? 1 : 0;
			$print_info['o_seat'] = $orderInfo['o_seat'];
				
			$print_info['DingDan'] = array(
					'Time' 		=> $orderInfo['o_dstime'],
					'DingDanHao'=> $orderInfo['o_id'],
					'OrderType' => $orderInfo['o_gtype'], //Choose表示下单  Seat表示预约
					'PayType' =>  $orderInfo['o_type'],     //0表示线下  1或2表示线上
					'o_name' => $orderInfo['o_name'],
					'o_phone' => $orderInfo['o_phone'],
					'o_remarks' => $orderInfo['o_remarks'],
			);
						
			$i = 1;
			$count_num = 0;
			$o_price = 0.00;
			foreach($goods as $kkk=>$vvv){
				$print_id = explode(',', $vvv['print_id']);
				if(in_array($vv['print_id'], $print_id) || $type == 4){
					if($orderInfo['o_gtype'] == 'Choose'){
						$print_info['CaiDan'][$i] = array(
								'Name'	=> $vvv['sp_name'],
								'Price'	=> $vvv['sp_gdprice'],
								'Count'	=> $vvv['sp_number']
						);
						$count_num += $vvv['sp_number'];
						$o_price += $vvv['sp_gdprice']*$vvv['sp_number'];
					}elseif($orderInfo['o_gtype'] == 'Seat'){
						$print_info['CaiDan'][$i] = array(
								'Name'	=> $vvv['sp_name'],
								'Price'	=> $vvv['sp_gdprice'],
								'Count'	=> $vvv['sp_number'],
								'sp_date' => $vvv['sp_date'],
						);
						$count_num++;
						$o_price += $vvv['sp_gdprice'];
					}					
					$i ++;
				}
			}
			
			if($vv['is_count'] == 1 || $type == 4){//打印合计
				$print_info['CountNum'] = $count_num;
				$print_info['CountPrice'] = number_format($o_price, 2);
			}
			
			
			$print_info['CaiDan']['Items'] = $i-1;
			if( ($i-1) > 0 && !empty($print_info['Printer'])){
				//print_r($print_info);exit;
				$string = JSON($print_info)."@@";
				\Common\Org\PInterface::SprintPort( iconv('UTF-8', 'GBK//IGNORE', $string) );
			}
		}
		return true;
	}
	
	/* 获取商品对应的打印机列表
	 * $gid:商品id
	 * */
	public function getPrintId($gid){
		//优先使用商品里的打印机设置 ,如果没有使用分类的
		$goods_info = M('goods')->field("cid,printid")->where(array('gid'=>$gid))->find();
		if(empty($goods_info['printid'])){
			$print = M('category')->where(array('id'=>$goods_info['cid']))->getField('print_id');
		}else{
			$print = $goods_info['printid'];
		}
		return $print;
	}
	
	
	/* 打印订单
	 * $oid:订单号
	* $type:打印时机  1:下单后  2:确认后 3:完成后 4:后台结算
	* */
	public function doFlPrint($oid,$type){
		$orderInfo = M("fl_order")->where(array('flo_id'=>$oid))->find();
		$sname = M('shop')->where("sid=".$orderInfo['flo_sid'])->getField('sname');
		$goods = M('fl_gsnapshot')->where(array('flg_oid'=>$oid))->select();
	
		foreach($goods as $k=>$v){
			$goods[$k]['print_id'] = $this->getPrintId($v['flg_gid']);
		}
		if($type == 4){
			$print_list = M('print')->where(array('is_pay'=>1,'print_status'=>1,'print_sid'=>$orderInfo['flo_sid']))->select();
		}else{
			$print_list = M('print')->where(array('print_time'=>array('like','%'.$type.'%'),'print_status'=>1,'print_sid'=>$orderInfo['flo_sid']))->select();
		}
	
		foreach($print_list as $kk=>$vv){
			$print_info = array();
			$print_info['ShangHuID'] = $orderInfo['flo_sid'];
			$print_info['ShangHuName'] = $sname;
			$print_info['Printer'] = $vv['print_name'];
			$print_info['is_balance'] = $vv['is_balance'];
			$print_info['is_pay'] = $orderInfo['flo_dstatus']==4 ? 1 : 0;
			
			$user = M('fl_user')->where(array('flu_userid'=>$orderInfo['flo_uid']))->find();
			
			$print_info['DingDan'] = array(
					'Time' 		=> $orderInfo['flo_dstime'],
					'DingDanHao'=> $orderInfo['flo_id'],
					'OrderType' => $orderInfo['flo_gtype']==1 ? 'Choose' : 'Seat', //Choose表示下单  Seat表示预约
					'PayType' =>  $orderInfo['flo_ptype'],     //0表示线下  1或2表示线上
					'o_name' => $user['flu_nickname'],
					'o_phone' => $user['flu_phone'],
					//'o_remarks' => $orderInfo['o_remarks'],
					//'o_seat' => $orderInfo['o_seat'],
			);
	
			$i = 1;
			$count_num = 0;
			$o_price = 0.00;
			foreach($goods as $kkk=>$vvv){
				$print_id = explode(',', $vvv['print_id']);
				if(in_array($vv['print_id'], $print_id) || $type == 4){
					if($orderInfo['flo_gtype'] == '1'){
						$print_info['CaiDan'][$i] = array(
								'Name'	=> $vvv['flg_name'],
								'Price'	=> $vvv['flg_gdprice'],
								'Count'	=> $vvv['flg_number']
						);
						$count_num += $vvv['flg_number'];
						$o_price += $vvv['flg_gdprice']*$vvv['flg_number'];
					}elseif($orderInfo['flo_gtype'] == '2'){
						$print_info['CaiDan'][$i] = array(
								'Name'	=> $vvv['flg_name'],
								'Price'	=> $vvv['flg_gdprice'],
								'Count'	=> $vvv['flg_number'],
								'sp_date' => $vvv['flg_date'],
						);
						$count_num++;
						$o_price += $vvv['flg_gdprice'];
					}
					$i ++;
				}
			}
				
			if($vv['is_count'] == 1 || $type == 4){//打印合计
				$print_info['CountNum'] = $count_num;
				$print_info['CountPrice'] = number_format($o_price, 2);
			}
				
				
			$print_info['CaiDan']['Items'] = $i-1;
			if( ($i-1) > 0 && !empty($print_info['Printer'])){
				$string = JSON($print_info)."@@";
				\Common\Org\PInterface::SprintPort( iconv('UTF-8', 'GBK//IGNORE', $string) );
			}
		}
		return true;
	}
}

?>