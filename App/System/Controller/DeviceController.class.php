<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class DeviceController extends ManagerController{
	
	//设备列表之前先判断，设备是不是启动状态
	public function _before_deviceList() {
		if(session('setStatusOnly') == 0){
			$deviceList = M('Router')->field('rcode')->select();
			\Common\Org\PInterface::setStatusOnly( $deviceList );
			session('setStatusOnly',1);
		}
	}
	
    //设备列表
    public function deviceList() {
		!$_GET['type'] && $_GET['type']=1;
		
		if( isset($_GET['type']) && intval($_GET['type'])==2 ) {
			$where = array('ragent'=>0);
		} else if( isset($_GET['type']) && intval($_GET['type'])==3 ) {
			$where = array("_string"=>'ragent != 0');
		} else {
			$where = array();
		}
		
		if( isset($_GET['status']) && $_GET['status'] != '' ) {
			$where['rstatus'] = I('get.status', 0, 'intval');	
		}

		if( I('get.sq', '') ) { 
			$rcode = trim(I('get.sq', '')); 
			
			switch( I('get.search_type', 0, 'intval') )
			{
				case 1:
					$where['boxnum'] = I('get.sq', "", 'intval');  
				break;
				
				case 2:
					$where['rid'] = array('like', "%{$rcode}%");  
				break;
				
				case 3:
					$where['rcode'] = array('like', "%{$rcode}%");  
				break;
			}
			
			
		}

		if( I('get.mc', '') ) { 
			$rname = trim(I('get.mc', '')); $where['rname'] = array('like', "%{$rname}%");  
		}

        $page = new \Think\Page(M('router')->where($where)->count(), 10);
		$deviceList = M('router')->where( $where )->limit($page->firstRow.','.$page->listRows)->order('rid DESC')->select();
		$agentid = $merchantid = $shopid = array();
		if( is_array($deviceList) && !empty($deviceList) ) foreach( $deviceList as $d ) { 
			if( !empty($d['ragent']) ) $agentid[ $d['ragent'] ] = $d['ragent'];
			if( !empty($d['rmerchant']) ) $merchantid[ $d['rmerchant'] ] = $d['rmerchant'];
			if( !empty($d['rshop']) ) $shopid[ $d['rshop'] ] = $d['rshop'];
		}
		
		if( !empty($agentid) ) {//获取代理商
			$agentlist = M('agent')->where( array("id"=>array("in", $agentid)) )->field("id,anickname")->select();
			foreach( $agentlist as $a ) $agentlistarr[ $a['id'] ] = $a['anickname'];
		}
		
		if( !empty($merchantid) ) {//获取商家
			$merchantlist = M('merchant')->where( array("jid"=>array("in", $merchantid)) )->field("jid,mnickname")->select();
			foreach( $merchantlist as $a ) $merchantlistarr[ $a['jid'] ] = $a['mnickname'];
		}
		
		if( !empty($shopid) ) {//获取门店
			$shoplist = M('shop')->where( array("sid"=>array("in", $shopid)) )->field("sid,sname")->select();
			foreach( $shoplist as $a ) $shoplistarr[ $a['sid'] ] = $a['sname'];
		}
		
		$this->assign('agentlistarr', $agentlistarr ? $agentlistarr : array());
		$this->assign('merchantlistarr', $merchantlistarr ? $merchantlistarr : array());
		$this->assign('shoplistarr', $shoplistarr ? $shoplistarr : array());
        $this->assign('deviceList', $deviceList ? $deviceList : array());
        $this->assign('pages', $page->show());
		
        $this->display();
    }

	//添加设备
	public function addDevice() {
		if( IS_POST ) {
			$filePath = rtrim(APP_DIR, '/').$_POST['device'];
			if( !file_exists($filePath) ) $this->display('Jump:error');
			
			vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
			$PHPExcel = \PHPExcel_IOFactory::load( $filePath ); 
			$maxRow = $PHPExcel->getSheet(0)->getHighestRow(); 
	
			$arrayData = array();
			 if( $maxRow > 0 ) {
				for($i=1; $i<=$maxRow; $i++) {
					if(!$PHPExcel->getSheet(0)->getCellByColumnAndRow(2, $i)->getValue())continue;
					$arrayData[$i-1]['rid'] = $PHPExcel->getSheet(0)->getCellByColumnAndRow(0, $i)->getValue();
					$arrayData[$i-1]['boxnum'] = $rcode = $PHPExcel->getSheet(0)->getCellByColumnAndRow(1, $i)->getValue()?$PHPExcel->getSheet(0)->getCellByColumnAndRow(1, $i)->getValue():$rcode;
					$arrayData[$i-1]['rcode'] = $PHPExcel->getSheet(0)->getCellByColumnAndRow(2, $i)->getValue();
				}
			}

			if( !empty($arrayData) && M('router')->addAll( $arrayData ) ) {
				$this->display('Jump:success'); 
			} else {
				echo M()->getLastSql();exit;
				$this->display('Jump:error');
			}
		} else {
			vendor('PhpExcel.PhpExcel.IOFactory', '', '.php');
			$this->display();	
		}
	}
	
	//分配设备到商家
	public function setMerchant() {
		if( IS_POST ) {
			$rid = I('post.rid');
			
			//先把这些设备的绑定取消
			M('router')->where(array("rid"=>array("in", "{$rid}")))->save( array('ragent'=>0, 'rmerchant'=>0, 'rshop'=>0, "rstatus"=>0) );
			
			if( M('router')->where(array("rid"=>array("in", "{$rid}")))->save( $_POST['info'] ) !== false ) {
				$this->display('Jump:success'); 
			} else {
				$this->display('Jump:error');
			}
		} else {
			$agentlist = M('agent')->alias('AS a')->join('__MEMBER__ AS m ON a.mid=m.mid')->where(" m.mstatus=1")->field('a.id,a.anickname')->select();
			$this->assign('agentlist', $agentlist); 
			if( intval($_GET['type'])==1 ) {
				$regent=M('router')->where(array('rid'=>I('get.rid')))->find();
				if( !$regent ) goto noinfo;
				$this->assign('regent', $regent['ragent']);

				if( $regent['rmerchant'] ) {
					$merchant = M('merchant')->alias('AS j')->join('__MEMBER__ AS m ON j.mid=m.mid')->where("m.mstatus=1 and j.magent=".$regent['ragent'])->field('j.jid,j.mnickname')->select();	
					$this->assign('jid', $regent['rmerchant']);
					$this->assign('merchant', $merchant);
				}
				
				if( $regent['rmerchant'] ) {
					$shoplist = M('shop')->where("status='1' and jid=".$regent['rmerchant'])->field('sid,sname')->select();
					$this->assign('sid', $regent['rshop']);
					$this->assign('shoplist', $shoplist);
				}
			}
			noinfo: 
			$this->assign('rid', I('get.rid'));
			$this->display();		
		}
	}
	
	//连接情况
	public function linksInfos() {
		$where = array('rcode'=>I('get.rid'));
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['rlast'] = array(array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime')))), array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime')))), 'and');					
		} elseif( I('get.statime', '') ) {
			$where['rlast'] = array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['rlast'] = array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime'))));	
		}
		$page = new \Think\Page(M('routerUser')->where( $where )->count('distinct(rusermac)'), 15);

		$subQuery = M('routerUser')->where( $where )->order('rlast DESC')->select(false);
		$this->assign('userList',M('routerUser')->table($subQuery.' a')->where( $where )->limit($page->firstRow.','.$page->listRows)->order('rlast DESC')->group('rusermac')->select());

//		$this->assign('userList', M('routerUser')->where( $where )->limit($page->firstRow.','.$page->listRows)->order('rlast DESC')->group('rusermac')->select());
        $this->assign('pages', $page->show());
		$this->display();
	}

	//登录记录
	public function linksLists() {
		$where = array('rusermac'=>I('get.mac'));
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['rlast'] = array(array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime')))), array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime')))), 'and');					
		} elseif( I('get.statime', '') ) {
			$where['rlast'] = array('egt', date('Y-m-d H:i:s', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['rlast'] = array('elt', date('Y-m-d H:i:s', strtotime(I('get.endtime'))));	
		}
		$page = new \Think\Page(M('routerUser')->where( $where )->count(), 15);

        $this->assign('userList', M('routerUser')->where( $where )->limit($page->firstRow.','.$page->listRows)->order('rlast DESC')->select());
        $this->assign('pages', $page->show());
		$this->display();
	}
	
	//获取商家
	public function getMerchant() {
		$agentid = I('get.id', 0, 'intval');
		$merchant = M('merchant')->alias('AS j')->join('__MEMBER__ AS m ON j.mid=m.mid')->where("m.mstatus=1 and j.magent=".$agentid)->field('j.jid,j.mnickname')->select();	
		$html  = $merchant ? '<option value="0">选择所属商家</option>' : ''; 
		if( is_array($merchant) && !empty($merchant) ) {
			foreach($merchant as $m) {
				$html .= '<option value="'.$m['jid'].'">'.$m['mnickname'].'</option>';
			}	
		}
		echo $html;
	}
	
	//获取分店
	public function getShop() {
		$jid = I('get.id', 0, 'intval');
		$shoplist = M('shop')->where("status='1' and jid=".$jid)->field('sid,sname')->select();	
		$html  = $shoplist ? '<option value="0">选择所属分店</option>' : ''; 
		if( is_array($shoplist) && !empty($shoplist) ) {
			foreach($shoplist as $m) {
				$html .= '<option value="'.$m['sid'].'">'.$m['sname'].'</option>';
			}	
		}
		echo $html;
	}
	
	//导入设备
    public function kindeditorUpload() {
		if(stripos($_SERVER['HTTP_REFERER'], 'dishuos.com') === false ) E('你无权进行此操作');
       
	    $uploadPath = realpath(THINK_PATH.'../Public').'/Upload/';
        if(!file_exists($uploadPath)) mkdir($uploadPath, true);
        
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'exts'		=> 'xlsx,xls',
			'maxSize'	=> 10485760
		);
		$attachment = new \Think\Upload( $uploadConfig );
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        
        if($attachmentInfo && is_array($attachmentInfo)) {
            echo json_encode(array('error'=>0, 'url'=>'/Public/Upload/'.date('Y-m-d').'/'.$attachmentInfo['savename'], 'savename'=>basename($attachmentInfo['savename'])));
        } else {
            echo json_encode(array('error'=>1, 'message'=>$attachment->getError()));
        }
    }


}