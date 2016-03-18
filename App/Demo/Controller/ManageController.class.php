<?php
namespace Demo\Controller;

class ManageController extends MerchantController {
	//广告管理
	public function advert() {
		$where['jid'] = $this->jid;
		if( isset($_POST['keywords']) && !empty($_POST['keywords']) ) $where['btitle'] = array('like', "%{$_POST['keywords']}%");
		$page = new \Demo\Org\Page(M('banner')->where($where)->count(), 12);
		$this->assign('bdlist', M('banner')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->display();	
	}
	
	//添加广告
	public function adinse() {
		if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$_POST['info']['jid'] = $this->jid;
			if( M('banner')->add( $_POST['info'] ) ) {
				$this->success('添加成功', U('/Manage/advert', '', true));	
			} else { $this->error('添加失败'); }		
		} else {
			$this->display();	
		}
	}
	
	//删除广告
	public function adupde() {
		$banner = M('banner')->where(array('bid'=>I('get.bid'), 'btype'=>'3'))->find();
		if( !$banner || $banner['jid'] != $this->jid) E('你无权查看当前页面');

		if( M('banner')->where(array('bid'=>I('get.bid')))->delete() ) {
			$this->success('删除成功', U('/Manage/advert', '', true));		
		} else { $this->error('操作失败'); }
	}
	
	//修改广告
	public function adedit() {
		if( IS_POST ) {
			$banner = M('banner')->where(array('bid'=>intval($_POST['info']['bid']), 'btype'=>'3'))->find();
			if( !$banner || $banner['jid'] != $this->jid) E('你无权查看当前页面');

			if( M('banner')->save( $_POST['info'] ) ) {
				$this->success('修改成功', U('/Manage/advert', '', true));	
			} else { $this->error('修改失败'); }		

		} else {
			$banner = M('banner')->where(array('bid'=>I('get.bid'), 'btype'=>'3'))->find();
			if( !$banner || $banner['jid'] != $this->jid) E('你无权查看当前页面');
			$this->assign('banner', $banner);
			$this->display();
		}
	}
	
	//APP首页的图片设置
	public function setFigure() {
		if( IS_POST ) {
			$data = I('post.data', '');
			$fileName = $this->path . 'FigureImage.php';
			
			if( file_exists(APP_DIR.$data) && file_put_contents($fileName, $data)) {
				exit('1');
			} { exit('0'); }
		} else {
			$fileName = $this->path . 'FigureImage.php';
			$this->assign('FileSrc', file_exists($fileName) ? file_get_contents($fileName) : '');
			$this->display();
		}
	}

	//APP模版选择
	public function template(){
		if( $this->type != 1 ) E('你无权查看当前页面');
		$merchant=M('merchant')->where(array('jid'=>$this->jid))->find();
		$this->assign('merchant',$merchant);
		if( IS_POST ) {
			$t_sign = I('post.t_sign', '');
			$result = M('merchant')->where(array('jid'=>$this->jid))->setField('theme',$t_sign);
			if($result)
				$this->ajaxReturn(array('status'=>1,'msg'=>'修改成功'));
			else
				$this->ajaxReturn(array('status'=>0,'msg'=>'修改失败'));
		} 
		$where = "t_status=1 AND (FIND_IN_SET({$merchant['vid']},t_vid) OR FIND_IN_SET(0,t_vid) )";
		$page = new \Demo\Org\Page(M('Theme')->where($where)->count(), 4);
		$themes = M('Theme')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('pages', $page->show());
		$this->assign('themes',$themes);
		$this->display();
	}
	
	//设备列表之前先判断，设备是不是启动状态
	public function _before_device() {
		$this->type == 1 ? $where['rmerchant']=$this->jid : $where['rshop']=$this->tsid;
		$deviceList = M('Router')->where($where)->select();
		\Common\Org\PInterface::setstatus( $deviceList );
	}
	
	//设备管理
	public function device(){
		$where = array();
		$this->type == 1 ? $where['rmerchant']=$this->jid : $where['rshop']=$this->tsid;
		if( isset($_GET['status']) && $_GET['status'] != '' ) {
			$where['rstatus'] = I('get.status', 0, 'intval');	
		}
		if( I('get.sq', '') ) { 
			$rcode = trim(I('get.sq', '')); $where['rcode'] = array('like', "%{$rcode}%");  
		}
		if( I('get.mc', '') ) { 
			$rname = trim(I('get.mc', '')); $where['rname'] = array('like', "%{$rname}%");  
		}
		$page = new \Demo\Org\Page(M('Router')->where($where)->count(), 10);
		$deviceList = M('Router')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('pages', $page->show());
		$this->assign('deviceList',$deviceList);
		$this->display();	
	}
	
	//查看设备WIFI状态
	public function wifistatus() {
		if(I('get.status', 1, 'intval') == 1) {
			\Common\Org\PInterface::setwifistatus( I('get.rid'), 0 );
		} else {
			\Common\Org\PInterface::setwifistatus( I('get.rid'), 1 );
		}
	}
	
	//设备连接
	public function devicelinks(){
		if(!I('get.rid'))exit();
		$where = array('rcode'=>I('get.rid'));
		$rwhere = array();
		$this->type == 1 ? $rwhere['rmerchant']=$this->jid : $rwhere['rshop']=$this->tsid;
		$merchant = M('Router')->where(array_merge($rwhere,$where))->find();
		$merchant or exit('无权查看');
		if(I('get.mac'))$where['rusermac'] = I('get.mac');
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['rlast'] = array(array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime')))), array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime')))), 'and');					
		} elseif( I('get.statime', '') ) {
			$where['rlast'] = array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['rlast'] = array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime'))));	
		}
		$page = new \Think\Page(M('routerUser')->where( $where )->count('distinct(rusermac)'), 20);
        $this->assign('userList', M('routerUser')->where( $where )->limit($page->firstRow.','.$page->listRows)->order('rlast DESC')->group('rusermac')->select());
        $this->assign('pages', $page->show());
		$this->display();
	}
	
	//修改设备
	public function deviceEdit() {
		if( IS_POST ) {
			$ymlist = I('post.ym', '');
			$ssido = I('post.ssido');
			$ssid = $ssido.'|gb2312;'.$ssido."|utf-8";
			
			$rcode = I('post.rcode');
			if( $rcode && $ssido ) {
				$ymbool = \Common\Org\PInterface::setymlist( $rcode, $ymlist );
				$ssbool = \Common\Org\PInterface::setssid( $rcode, iconv('UTF-8', 'gb2312', $ssid) );
				
				if( $ssbool && $ymbool && M('Router')->where("rid=".I('post.rid', 0, 'intval'))->setField("rwebtype", I('post.rwebtype', 1, 'intval')) !== false) {
					$this->success('添加成功', U('/Manage/device', '', true)); exit;
				}
			} 
			$this->error('添加失败' );	
		} else {
			$deviceInfo = M('Router')->where(array('rid'=>I('get.rid', 0, 'intval')))->find();
			if( !is_array($deviceInfo) || empty($deviceInfo) ) E('你无权查看当前页面');
			$this->assign('deviceInfo', $deviceInfo);

			$this->assign('ymlist', \Common\Org\PInterface::getymlist( $deviceInfo['rcode'] ));
			$ssid = \Common\Org\PInterface::getssid( $deviceInfo['rcode'] );
			if( $ssid ) {
				$ssid = iconv('gb2312', 'UTF-8', $ssid);
				$ssname = trim(substr($ssid, 0, strpos($ssid, "|")));
				$this->assign('ssid', $ssname);
			}
			$this->display();
		}
	}	
	
	//财务管理
	public function finance() {
		$page = new \Demo\Org\Page(M('bookkeeping')->alias('AS b')->join('__MEMBER__ AS m ON b.bmid=m.mid', 'left')->where(array('m.mid'=>$this->mid))->count(), 12);
		$this->assign('falist', M('bookkeeping')->alias('AS b')->join('__MEMBER__ AS m ON b.bmid=m.mid', 'left')->where(array('m.mid'=>$this->mid))->field('b.*,m.msurname')->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->assign('mbdzh', M('member')->where(array('mid'=>$this->mid))->getField('mbdzh'));				
		$this->display();	
	}
	
	//修改提现账户
	public function editifo() {
		if( IS_POST ) {
			$mbdzhy=I('post.ac', ''); $mbdzhq=I('post.bc', ''); $mbdzhx=I('post.cc', ''); $mbdzhs=I('post.dc', ''); $mbdzhc=I('post.ec', '');
			if( !$mbdzhy || !$mbdzhq || $mbdzhq != $mbdzhy ) exit("222");
			if( session('SendSms') != $mbdzhc ) { exit("2"); }
			exit( M('member')->where(array('mid'=>$this->mid))->save( array('mbdzh'=>$mbdzhy, 'msurname'=>$mbdzhx, 'mphone'=>$mbdzhs) ) !== false ? "1" : "0" );
		} else {
			$this->assign('member', M('member')->where(array('mid'=>$this->mid))->find());
			$this->display();	
		}
	}
	
	//发送验证码
	public function sendsms() {
		$tpl = I('get.val', ''); if( !$tpl ) exit("0");
		$content = \Org\Util\String::randString(4, 1);
		session('SendSms', $content);
		exit(sendmsg( $tpl, $content) ? "1" : "0");		
	}
}