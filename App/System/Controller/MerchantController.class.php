<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class MerchantController extends ManagerController {
    private $_AddressList = array();

    public function _initialize() {
        parent::_initialize();
        $this->_AddressList = F('AddressList');
        if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $this->_AddressList);
    }

    //商户列表
    public function merchantsList() {
		if( IS_POST ) {
			if(I('post.alipay_status') !='' && I('post.jid')){
				$alipay_status = I('post.alipay_status', 0, 'intval') == 1 ? 0 : 1; 
				$extend = M('merchant_extend')->find(I('post.jid'));
				if($extend){
					$result = M('merchant_extend')->where(array('jid'=>I('post.jid')))->save(array('alipay_status'=>$alipay_status));
				}else{
					$data['jid'] = I('post.jid');
					$data['alipay_status'] = $alipay_status;
					$result = M('merchant_extend')->add($data);
				}
			}
			exit($result?'1':'0');
		}
		$where = array('mtype'=>2, 'mstatus'=>array("neq", -1));
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['mregdate'] = array(array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime')))), array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime')))), 'and');          					
		} elseif( I('get.statime', '') ) {
			$where['mregdate'] = array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['mregdate'] = array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime'))));    	
		}
		if( isset($_GET['status']) && $_GET['status'] != '' ) {
			$where['mstatus'] = I('get.status', 0, 'intval');	
		}
		if( I('get.keyword', '') ) { 
			$keyword = trim(I('get.keyword', '')); $where['mnickname|mabbreviation|mlpname|mlptel|anickname|mname'] = array('like', "%{$keyword}%", 'or');  
		}
		
        $page = new \Think\Page(D('View')->view('merchant')->where($where)->count(), 10); 
		$merchantsList	= D('View')->where($where)->limit($page->firstRow.','.$page->listRows)->order('jid desc')->select();
		$this->assign('merchantsList', $merchantsList);
		$jids = array();
		if($merchantsList)foreach($merchantsList as $vo){$jids[]=$vo['jid'];}
		if(count($jids))$this->assign('alipays', M('merchant_extend')->where(array('jid'=>array('in',$jids)))->getField('jid,alipay_status'));
	    $this->assign('pages', $page->show());
		
		$this->assign('mname', \Common\Org\Cookie::get('mname'));
		
		$groupid = \Common\Org\Cookie::get('groupid');
		$this->assign('groupid', $groupid);
        $this->display();
    }

    //添加商户
    public function merchantAdd() {
        if( IS_POST ) {
			$_POST['info']['mcertificates'] = serialize($_POST['image']);
			$_POST['member']['mpwd'] = md5(md5($_POST['member']['mpwd']));
			
			//先判断这个代理商，是不是还可以再添加商户
			$agentid = intval( $_POST['info']['magent'] );
			if( ! \Common\Org\Maxmerchant::IsOpenMerchant($agentid) ) {
				$this->display('Jump:error'); exit; 	
			}
			
			$MemberMobel = D('Member');
			$MemberMobel->starttrans();
			
			$status_01 = $_POST['info']['mid'] = $muser['tmid'] = $MemberMobel->insert($_POST['member']);
			$status_02 = $_POST['app']['jid'] = $muser['tjid'] = D('Merchant')->insert($_POST['info']);
			$status_03 = M('merchantApp')->add($_POST['app']);
			$status_04 = M('merchantUser')->add($muser);
			$status_05 = \Common\Org\Maxmerchant::SetMerchantDec( $_POST['app']['jid'] );
			
			if( $status_01 && $status_02 && $status_03 && $status_04 && $status_05 ) {
				$MemberMobel->commit(); $this->display('Jump:tishi');
			} else {
				$MemberMobel->rollback(); $this->display('Jump:error');	
			}
        } else {
            foreach(M('agent')->alias('AS a')->field('a.id,a.anickname,a.pid')->where(array('m.mtype'=>1))->join('__MEMBER__ AS m ON a.mid=m.mid')->select() as $r) { 
                $agentListArray[$r["id"]] = $r; 
            }
            $this->assign('agentList', \Common\Org\Tree::ItreeInitialize()->initialize($agentListArray)->treeRule(0, "<option value=\$id>\$spacer \$anickname</option>"));
           
			$vocationList = F('VocationList');
        	if( !is_array($vocationList) || empty($vocationList) ) B('\Common\Behavior\CreateVocation', '', $vocationList);
			foreach($vocationList as $vid=>$vocation) $vocationList[$vid]['pid'] = $vocationList[$vid]['v_pid'];
			$this->assign('vocationList', \Common\Org\Tree::ItreeInitialize()->initialize($vocationList)->treeRule(0, "<option value=\$v_id>\$spacer \$v_title</option>"));
			
			foreach($this->_AddressList as $address) if($address['aid']!=0 && $address['apid']==0) $addressListArr[$address['aid']] = $address['aname'];
			$this->assign('address', $addressListArr);
			
			$this->assign('theme',array());
			$this->display();
        }
    }
	
	//模板
    public function theme() {
		$vid  = I('get.vid');
		$data = M('theme')->where(" (`t_vid` like '%,{$vid},%' or `t_vid`=',0,') && t_status=1 ")->select();	 
		$html = "<option value=''>请选择商家模板</option>";
		foreach( $data as $v ) $html .= '<option value="'.$v['t_sign'].'">'.$v['t_name'].'</option>';
		exit( $html ); 
    }  
	
    //删除商户
    public function merchantDel() {
        $jid = I('get.jid', ''); if( !$jid ) exit('0'); 
		$mstatus = I('get.status', 0, 'intval');
		
		$MerchantMobel = D('Merchant');
		$MerchantMobel->starttrans();
		
		if( $mstatus==1 ) {//关闭
			$status_01 = \Common\Org\Maxmerchant::SetMerchantInc( $jid );
		} else {//开启
			$status_01 = \Common\Org\Maxmerchant::SetMerchantDec( $jid );
		}		
		$status_02 = D('Merchant')->deleteMerchant( $jid, $mstatus);

		
		//if( $status_01 && $status_02 ) {
		if( $status_02 ) {
			$MerchantMobel->commit(); exit('1');
		} else {
			$MerchantMobel->rollback(); exit('0');
		}
    }

    //修改商户
    public function merchantEdit() {
        if( IS_POST ) {
            $_POST['info']['mcertificates'] = serialize($_POST['image']);

            $MerchantMobel = D('Merchant');
            $MerchantMobel->starttrans();

            $status_01 = D('Merchant')->update($_POST['info']);
            $status_02 = M('merchantApp')->save($_POST['app']);
            $status_03 = M('member')->save($_POST['member']);

            if( $status_01 !== false &&  $status_02 !== false &&  $status_03 !== false) {
            	$MerchantMobel->commit();
                $this->display('Jump:success'); 
            } else { 
            	$MerchantMobel->rollback();
				$this->assign('msg', D('Merchant')->getError()." ".M('member')->getError()." ".M("merchantApp")->getError());
				$this->display('Jump:error');
			}
        } else {
			$merchant = D('View')->view('emerchant')->where(array('jid'=>I('get.id', 0, 'intval')))->find();
            if(!is_array($merchant) || empty($merchant)) $this->display('Jump:error');
			$merchant['mcertificates'] = unserialize($merchant['mcertificates']);
			$this->assign('merchant', $merchant);
			$this->assign('addressArrs', explode(" ", get_address_byid($merchant['mcity'])));
			$vid =  $merchant['vid'];
			$this->assign('theme', M('theme')->where(" t_status=1 ")->select());     
			//所有的行业
			$this->assign('vocation', M('vocation')->select());
			
            $this->display();
        }
    }

	//店铺列表
	public function shopsList() {
		$where = array("status"=>array("neq", '-1'));
		if( I('get.jid', 0, 'intval') ) $where['jid'] = I('get.jid', 0, 'intval');
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['mregdate'] = array(array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime')))), array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime')))), 'and');          					
		} elseif( I('get.statime', '') ) {
			$where['mregdate'] = array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['mregdate'] = array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime'))));     	
		}   
		if( I('get.keyword', '') ) {
			$keyword = I('get.keyword', ''); $where['mnickname|sname|scontactsname|mservetel|msaletel'] = array('like', "%{$keyword}%", 'or');
		}
		$page = new \Think\Page(D('View')->view('shop')->where($where)->count(), 10);
		$this->assign('shopsList', D('View')->where($where)->limit($page->firstRow.','.$page->listRows)->order('jid desc')->select());
		$this->assign('pages', $page->show());
		$groupid = \Common\Org\Cookie::get('groupid');
		$this->assign('groupid', $groupid);
		$this->display();	
	}
	
	//添加店铺
	public function shopAdd() {
		if(IS_POST) {
			array_walk($_POST['shop'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			//$member['mname'] = I('post.mname', '');
			//$member['mpwd'] = md5(md5(I('post.mpwd')));
			//$member['mtype'] = 2;
			//$member['mregdate'] = $member['mlogindate'] = date('Y-m-d H:i:s');
			
			if( ($sid=D('Shop')->insert($_POST['shop']))  ) {
				//if(M('merchantUser')->add(array('tmid'=>$mid, 'tjid'=>intval($_POST['shop']['jid']), 'tsid'=>$sid, 'type'=>2))) $this->display('Jump:success');
				$this->display('Jump:success');
			} else { 
				//if( $sid ) D('Shop')->where('sid='.$sid)->delete();
				//if( $mid ) D('Member')->where('mid='.$mid)->delete();
				$this->assign('msg', D('Shop')->getError()); $this->display('Jump:error'); 
			}						
		} else {
			foreach($this->_AddressList as $address) if($address['aid']!=0 && $address['apid']==0) $addressListArr[$address['aid']] = $address['aname'];
			$this->assign('address', $addressListArr);
			
			$this->assign('merchant', M('merchant')->alias('AS merchant')->where(array('member.mstatus'=>1, "member.mtype"=>2))->join("__MEMBER__ AS member on member.mid=merchant.mid", 'left')->select());
		    $this->display();	
		}
	}
	
	//删除店铺
	public function shopDel() {
		$sid = I('get.sid', ''); if(!$sid) exit('0'); $status = I('get.status', 0, 'intval') == 1 ? '0' : '1';  
		//$tmid=M("merchant_user")->where("tsid=$sid")->find();
		//$mid=$tmid['tmid'];M('member')->where(array('mid'=>array('in', "$mid")))->save(array('mstatus'=>$status)); 
		exit(M('shop')->where(array('sid'=>array('in', "$sid")))->save(array('status'=>$status))!== false ? "1" : "0");  
	}

	//开启或者关闭店铺页面返利 
	public function shopQmstatus() {
		$sid = I('get.sid', ''); if(!$sid) exit('0'); $qmstatus = I('get.qmstatus', 0, 'intval') == 1 ? '0' : '1';  
		exit(M('shop')->where(array('sid'=>array('in', "$sid")))->save(array('qmstatus'=>$qmstatus))!== false ? "1" : "0");  
	}	
	//修改店铺
	public function shopEdit() {
		if( IS_POST ) {
			array_walk($_POST['shop'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			if( D('shop')->update($_POST['shop']) !== false ) {
				$this->display('Jump:success');
			} else { $this->assign('msg', D('Shop')->getError()); $this->display('Jump:error'); }		
		} else {
			$shop = M('shop')->alias('AS s')->where(array('sid'=>I('get.sid', 0, 'intval')))->join('__MERCHANT__ as m ON m.jid=s.jid')->field('s.*,m.mnickname')->find();
			if(!is_array($shop) || empty($shop)) { $this->display('Jump:error'); }
			
			foreach($this->_AddressList as $address) if($address['aid']!=0 && $address['apid']==0) $addressListArr[$address['aid']] = $address['aname'];
			$this->assign('address', $addressListArr);

			if( $shop['province'] )  {
				$addressListArr = array();
				foreach($this->_AddressList as $address) if($address['apid']==$shop['province']) $addressListArr[$address['aid']] = $address['aname'];
				$this->assign('addresst', $addressListArr);	
			}
			
			if( $shop['city'] )  {
				$addressListArr = array();
				foreach($this->_AddressList as $address) if($address['apid']==$shop['city']) $addressListArr[$address['aid']] = $address['aname'];
				$this->assign('addressq', $addressListArr);	
			}
			
			$this->assign('shop', $shop);
			$this->display();				
		}
	}
	
	//APP已经生成列表
	public function appsList() {
		$where = array('mtype'=>2, 'status'=>1, 'iosurl'=>array('NEQ', ''), 'iosurl'=>array('NEQ', ''));
		if( I('get.jid', '') ) { $jid=I('get.jid', ''); $where['mnickname'] = array('like', "%{$jid}%"); }
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['mregdate'] = array(array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime')))), array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime')))), 'and');          					
		} elseif( I('get.statime', '') ) {
			$where['mregdate'] = array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['mregdate'] = array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime'))));     	
		}
		$where['mstatus']= array("neq", -1);
        $page = new \Think\Page(D('View')->view('applist')->where($where)->count(), 15);
    	$this->assign('appsList', D('View')->where($where)->limit($page->firstRow.','.$page->listRows)->order('jid desc')->select());
        $this->assign('pages', $page->show());
        $this->display();
	}
	
	//APP未生成列表列表
	public function appnoList() {
		//$where = array('mtype'=>2,'appurl'=>'','iosurl'=>'');
		$where['_string'] = "MerchantApp.status=1 and Member.mtype=2 and (MerchantApp.appurl='' or MerchantApp.iosurl='')";
		if( I('get.jid', '') ) { $jid=I('get.jid', ''); $where['mnickname'] = array('like', "%{$jid}%", 'or'); }
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['mregdate'] = array(array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime')))), array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime')))), 'and');          					
		} elseif( I('get.statime', '') ) {
			$where['mregdate'] = array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['mregdate'] = array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime'))));     	
		}
		$where['mstatus']= array("neq", -1);
        $page = new \Think\Page(D('View')->view('applist')->where($where)->count(), 15);
		$this->assign('appsList', D('View')->where($where)->limit($page->firstRow.','.$page->listRows)->order('jid desc')->select());
        $this->assign('pages', $page->show());
        $this->display();
	}
	
	//上传客户端应用
	public function appUpload() {
		if( IS_POST ) {
			$info = array(
				'appurl'		=> I('post.appurl', ''),
				'appversions'	=> I('post.appversions', ''),
				'iosurl'		=> I('post.iosurl', ''),
				'iosversions'	=> I('post.iosversions', ''),
				'up_explain'	=> I('post.up_explain', ''),
				'endmakedate'	=> date('Y-m-d H:i:s'),
			);
			$jid = I('post.jid', 0, 'intval');
			if( $info['iosurl'] && substr(strtolower($info['iosurl']),-4)=='.ipa') {
				$appname = M('merchantApp')->where(array('jid'=>$jid))->getField('appname');
				$basename = basename($info['iosurl'], '.ipa');
				$plist = file_get_contents(APP_DIR.'/Public/Data/ipa.plist');
				$plist = str_replace('#IPA_URL#', "https://www.dishuos.com".$info['iosurl'], $plist);
				$plist = str_replace('#IPA_NAME#', $appname, $plist);
				$plist = str_replace('#JID#', $jid, $plist);
				$s=file_put_contents(APP_DIR.dirname($info['iosurl'])."/".$basename.".plist", $plist);
				if($s) $info['iosurl'] = dirname($info['iosurl'])."/".$basename.".plist";
			}
			if( M('merchantApp')->where(array('jid'=>$jid))->save( $info ) !== false ) {
				$this->display('Jump:success');
			} else { $this->display('Jump:error'); }

		} else {
        	$this->assign('appLoad',M('merchantApp')->where("jid=".I('get.jid'))->find());
			$this->display();
		}
	}
	
	//生成客户端应用 
	public function appDownload() {
		$appInfo = M('merchantApp')->where(array('jid'=>I('get.jid', 0, 'intval')))->getField('appurl');
		$filePath = rtrim(APP_DIR, '/').'/'.$appInfo;
		\Org\Net\Http::download($filePath);
	}
	
	//上传APP
	public function kindeditorUpload() {
		if( isset($_SERVER['HTTP_REFERER'] )) {
			if(stripos($_SERVER['HTTP_REFERER'], 'dishuos.com') === false ) E('你无权进行此操作');
		}
	    $uploadPath = realpath(THINK_PATH.'../Public').'/Media/'.I('get.jid', '').'/';
        if(!file_exists($uploadPath)) mkdir($uploadPath);
		
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'exts'		=> 'apk,ipa',
			'maxSize'	=> 31457280,
			'subName'	=> null,
		);
		$attachment = new \Think\Upload( $uploadConfig );
		
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        if($attachmentInfo && is_array($attachmentInfo)) {
            echo json_encode(array('error'=>0, 'url'=>'/Public/Media/'.I('get.jid').'/'.$attachmentInfo['savename'], 'savename'=>basename($attachmentInfo['savename'])));
        } else {
            echo json_encode(array('error'=>1, 'message'=>$attachment->getError()));
        }
    }
	
	//applogo上传
    public function kindeditorAppUpload() {
		//if(stripos($_SERVER['HTTP_REFERER'], 'dishuos.com') === false ) E('你无权进行此操作');
	    $uploadPath = realpath(THINK_PATH.'../Public').'/Upload/';
        if(!file_exists($uploadPath)) mkdir($uploadPath, true);
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'exts'		=> 'jpg,jpeg,png,gif,icon', 
			'maxSize'	=> 102400 
		);

		$attachment = new \Think\Upload( $uploadConfig );
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        if($attachmentInfo && is_array($attachmentInfo)) {
            echo json_encode(array('error'=>0, 'url'=>'/Public/Upload/'.date('Y-m-d').'/'.$attachmentInfo['savename'], 'savename'=>basename($attachmentInfo['savename'])));
        } else {
            echo json_encode(array('error'=>1, 'message'=>$attachment->getError()));
        }
    }

    //设置APPID
    public function setAppkey() {
    	if( IS_POST ) {
    		if( M('merchantApp')->save( $_POST['info'] ) !== false ) {
				$this->display('Jump:success');
			} else { $this->display('Jump:error'); }
    	} else {
			$info = M('merchantApp')->where('jid='.I('get.jid', 0, 'intval'))->find();
	    	if( !is_array($info) || empty($info) ) { E('你无权进行此操作'); }
	    	$this->assign('info', $info);
	    	$this->display();
    	}
    }
	
	//删除商家
	public function truncateAgent() {
		$jid = I('post.jid') or die('0');
		
		//获取所有的门店账号
		$member_id_list = array();
		$member_list = M("merchantUser")->where( array("tjid"=>$jid) )->select();
		foreach( $member_list as $m ) $member_id_list[] = $m['tmid'];
		
		$MemberModel = M('member');
		$MemberModel->startTrans();
		
		$status_01 = M("member")->where( array("mid"=>array("in", $member_id_list)) )->setField("mstatus", -1) !== false ? true : false;
		$status_02 = M("shop")->where("jid=".$jid)->setField("status", '-1') !== false ? true : false;
		$status_03 = M('merchantApp')->where("jid=".$jid)->setField("status", '0') !== false ? true : false;
		
		if( $status_01 && $status_02 && $status_03 ) {
			$MemberModel->commit();	exit('1');
		} else {
			$MemberModel->rollback(); exit('0');
		}
	}
	
	//彻底删除店
	public function truncateShop() {
		$sid = I('post.sid') or die('0');
		$ShopModel = M('shop');
		$status_01 = $ShopModel->where("sid=".$sid)->setField("status", '-1') !== false ? true : false;
		if( $status_01  ) {
				exit('1');
		} else {
			 exit('0');
		}
		/*
		//获取所有的门店账号
		$member_id_list = array();
		$member_list = M("merchantUser")->where( array("tsid"=>$sid, "type"=>2) )->select();
		foreach( $member_list as $m ) $member_id_list[] = $m['tmid'];
		
		$ShopModel = M('shop');
		$ShopModel->startTrans();
		
		$status_01 = $ShopModel->where("sid=".$sid)->setField("status", '-1') !== false ? true : false;
		$status_02 = M("member")->where( array("mid"=>array("in", $member_id_list)) )->setField("mstatus", 0) !== false ? true : false;
		
		if( $status_01 && $status_02 ) {
			$ShopModel->commit();	exit('1');
		} else {
			$ShopModel->rollback(); exit('0');
		}*/
	}
} 