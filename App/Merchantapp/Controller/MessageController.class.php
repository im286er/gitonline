<?php
namespace Merchantapp\Controller;

class MessageController extends MerchantappController {

	//消息列表
	public function msglist() {
		$where = array();
		!$_GET['type'] && $_GET['type']=1;
		//jid = 0 表示系统消息，所有商家
		if( $this->type==1) {
			$where['_string'] = "jid=".$this->jid." or jid=0";
		} else {
			$sid = $this->sid ? $this->sid : $this->sidlist[0]['sid'];
			$where['sid'] = $sid;
		}

		$type = I('get.type', 1, 'intval');
		$msglist = M('appmsg')->where(array_merge($where, array('type'=>$type)))->order('status asc, msid Desc')->select();
		foreach($msglist as $k=>$v){
			if(!empty($v['extend'])){
				$extend = unserialize($v['extend']);
				if($extend['type'] == 1){
					$msglist[$k]['oid'] = $extend['oid'];
					$msglist[$k]['extend1'] = '￥'.$extend['price'];
					$msglist[$k]['extend2'] = '共'.$extend['number'].'件';
				}else{
					$msglist[$k]['extend1'] = $extend['number'].'人';
				}
			}
		}
		$this->assign('msglist', $msglist);
		$this->display();
	}
	
	//消息详情页
	public function showmsg(){
		header("Content-type: text/html; charset=utf-8");  
		$msid = I('get.msid', 0, 'intval');
		M('appmsg')->where('msid='.$msid)->setField('status', 1);
		$msginfo = M('appmsg')->where( array("msid"=>$msid) )->find();
		$sname = M('shop')->where( array("sid"=>$msginfo['sid']) )->getField('sname');
		$this->assign('sname', $sname);
		$this->assign('msginfo', $msginfo);
		if(I('get.oid')){
			$where = array();
			$where = array('o_id'=>I('get.oid'));
			$oinfo = M('Order')->where($where)->order('o_dstime desc')->find();
			if( $oinfo ) {
				if($oinfo['o_table']) $oinfo['ogoods'] = M($oinfo['o_table'])->where(array('sp_oid'=>$oinfo['o_id']))->field('sp_name,sp_gdprice,sp_number')->select();
				$oinfo['voucher'] = M('VoucherOrder')->where('o_id='.$oinfo['oid'])->find();
			}
			$this->assign('o', $oinfo);
			$this->display('Message_ordershow');	
			
			//支付类型
			$order_type = array(
				0=>'<span style="color:#339900;">线下支付到商家</span>', 
				1=>'<span style="color:#ff9900;">线上支付到商家</span>', 
				2=>'<span style="color:#000000;">线上支付到平台</span>'
			);
			$this->assign('order_type', $order_type);
			
			//支付状态
			$order_pstatus = array(
				0=>'<span style="color:red;">未支付</span>',
				1=>'<span style="color:#339900;">已支付</span>',
				2=>'<span style="color:#ff9900;">已退款</span>',
			);
			$this->assign('order_pstatus', $order_pstatus);
			
			//处理状态
			$order_dstatus = array(
				1=>'<span style="color:red;">待处理</span>',
				3=>'<span style="color:#339900;">待完成</span>',
				4=>'<span style="color:#ff9900;">已完成</span>',
				5=>'<span style="color:#ff9900;">已关闭</span>'
			);
			$this->assign('order_dstatus', $order_dstatus);

		}else
		$this->display();	
	}
	

	//这里采用每隔5S读取一次, appmsg的引擎是 MEMORY，以后会采用 redis替换掉
	public function checkmsg() {
		$where = array('status'=>0);
		if( $this->type==1) {
			$where['_string'] = "jid=".$this->jid." or jid=0";
		} else {
			$where['sid'] = $this->sid ? $this->sid : $this->sidlist[0]['sid'];
		}
		$count = M('appmsg')->where($where)->count();
		exit( $count ? $count : "0");
	}
}