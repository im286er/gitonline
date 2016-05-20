<?php
namespace Merchant\Controller;

class PrintController extends MerchantController {
	//我的打印机列表
	public function printList() {
		$shops = D('auth')->getAuthShops($this->mid);
		if( !$_GET['sid'] && !empty($shops) ) {
			$firstShop = array_keys( $shops ); $_GET['sid']=$firstShop[0];
		}
		$sid = intval( $_GET['sid'] );
		
		//获取打印机列表
		$page = new \Common\Org\Page(M('print')->where(array('print_sid'=>$sid))->count(), 6);
		$printlist = M('print')->where( array('print_sid'=>$sid) )->order('print_id desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('printlist', $printlist);
		$this->assign('pages', $page->show());
		$this->assign('shops', $shops);
		$this->assign('sid', $sid);
		$this->display();	
	}

	//添加打印机
	public function addPrint() {
		if(IS_POST) {
			$print_name = I('post.name');
			$print_time = I('post.time');
			$print_time = join(',',$print_time);
			$print_sid = I('post.sid');
			$is_count = I('is_count');
			$is_balance = I('is_balance');
			$is_pay = I('is_pay');
			
			$data = array('print_sid'=>$print_sid, 'print_name'=>$print_name, 'print_time'=>$print_time, 'print_addtime'=>date('Y-m-d H:i:s'), 'is_count'=>$is_count, 'is_balance'=>$is_balance, 'is_pay'=>$is_pay);
			if( $print_id=M('print')->add( $data ) ) {
				$data['print_id'] = $print_id;
				exit( JSON( array('msg'=>'', 'info'=>$data) ) );
			} else {
				exit( JSON( array('msg'=>'添加失败', 'info'=>'') ) );
			}
		} else {
			$this->display();	
		}
	}
	
	//修改打印机
	public function editPrint() {
		if( IS_POST ) {
			$print_name = I('post.name');
			$printid = I('post.printid');
			$print_time = I('post.time');
			$print_time = join(',',$print_time);
			$is_count = I('is_count');
			$is_balance = I('is_balance');
			$is_pay = I('is_pay');
			
			$data = array('print_id'=>$printid, 'print_name'=>$print_name, 'print_time'=>$print_time, 'print_addtime'=>date('Y-m-d H:i:s'), 'is_count'=>$is_count, 'is_balance'=>$is_balance, 'is_pay'=>$is_pay);
			if( M('print')->save( $data ) !== false ) {
				$data['print_id'] = $printid ;
				exit( JSON( array('msg'=>'', 'info'=>$data) ) );
			} else {
				exit( JSON( array('msg'=>'修改失败', 'info'=>'') ) );
			}
		} else {
			$print_id = I('get.pid', 0, 'intval');
			$print_info = M('print')->where( array("print_id"=>$print_id) )->find();
			if( !is_array($print_info) || empty($print_info) ) E('你无权限对此操作');
			$this->assign('print_info', $print_info);			
			$this->display();	
		}
	}
	
	//删除打印机
	public function delPrint() {
		$printid = I('post.id', 0, 'intval');
		$printInfo = M('print')->where( array('print_id'=>$printid) )->find();
		if( !$printInfo ) exit('0');
		
		exit( M('print')->where( array('print_id'=>$printid) )->delete() ? "1" : "0" );
	}
}