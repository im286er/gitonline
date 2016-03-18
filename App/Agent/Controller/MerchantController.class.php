<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;
class MerchantController extends ManagerController { 

	private $_AddressList = array();

	public function _initialize() {
		parent::_initialize();
		$this->_AddressList = F('AddressList');
		if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $this->_AddressList);
	}
	
	public function index() {
		//获取所有商家的ID
		$merchant_id_list = $this->_getMerchantId();
		$where['jid'] = array('in', $merchant_id_list);
		if( I('get.keyword', '') ) { 
			$keyword = trim(I('get.keyword', '')); 
			$where['mnickname|mabbreviation|mname'] = array('like', "%{$keyword}%", 'or');  
		}
		$Page= new \Think\Page(D('View')->view('merchant')->where($where)->count(),10);
		$result = D('View')->where($where)->order('jid DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('page', $Page->show());
	    foreach($result as $key=>$val) { 
	    	$result[$key]['mendian'] = M("shop")->where("jid=".$val['jid']." and (status='1' or status='0') ")->count();
	    	$result[$key]['shebei'] = M("router")->where("rmerchant=".$val['jid'])->count();
	   	}

	    $this->assign('result', $result); 
	   
	   	//判断当前代理商是不是还有权再开商家
	   	$agentid = \Common\Org\Cookie::get('agentid');
		$maxnum = M("agent")->where("id=".$agentid)->getField("maxnum");
		$overnum = M("agent")->where("id=".$agentid)->getField("overnum"); 
		if( $maxnum-$overnum <= 0 ) $this->assign('overnum', 1);
		$this->display();
	}
	

	public function  add() {
		$agentid = \Common\Org\Cookie::get('agentid');
		$maxnum = M("agent")->where("id=".$agentid)->getField("maxnum");
		$overnum = M("agent")->where("id=".$agentid)->getField("overnum"); 
		if( $maxnum-$overnum <= 0 ) E('你无权再进行添加商家');
		
		$agentid=M('agent')->where("mid=".$_SESSION['member']['mid'])->find();
		if( IS_POST ) {
			$_POST['info']['mcertificates'] = serialize($_POST['image']);
			$_POST['info']['magent'] =$agentid['id'];
			$_POST['member']['mpwd'] = md5(md5($_POST['member']['mpwd']));
			if( ($_POST['info']['mid']=$muser['tmid']=D('Member')->insert($_POST['member'])) && ($_POST['app']['jid']=$muser['tjid']=D('Merchant')->insert($_POST['info'])) && M('merchantApp')->add($_POST['app']) && M('merchantUser')->add(array_merge($muser, array('tsid'=>0, 'type'=>1))) ) { 
				$this->display('Jump:tishi'); exit;
		}
		if( $_POST['shop']['jid'] ) D('merchant')->where(array('jid'=>$_POST['shop']['jid']))->delete(); 
		if( $_POST['info']['mid'] ) D('member')->where(array('mid'=>$_POST['info']['mid']))->delete(); 
		
		$this->assign('msg', D('Member')->getError()." ".D('Merchant')->getError()." ".M("merchantApp")->getError());
		$this->display('Jump:error');
		} else {
			foreach(M('agent')->alias('AS a')->field('a.id,a.anickname,a.pid')->where(array('a.state'=>'1', 'm.mtype'=>1, 'm.mstatus'=>1))->join('__MEMBER__ AS m ON a.mid=m.mid')->select() as $r) { 
				$agentListArray[$r["id"]] = $r; 
		}
		$this->assign('agentList', \Common\Org\Tree::ItreeInitialize()->initialize($agentListArray)->treeRule(0, "<option value=\$id>\$spacer \$anickname</option>"));
		
		foreach(M('vocation')->select() as $r) { $r['pid'] = $r['v_pid']; $vocationListArray[$r["v_id"]] = $r; }
		$this->assign('vocationList', \Common\Org\Tree::ItreeInitialize()->initialize($vocationListArray)->treeRule(0, "<option value=\$v_id>\$spacer \$v_title</option>"));
		
		foreach($this->_AddressList as $address) if($address['aid']!=0 && $address['apid']==0) $addressListArr[$address['aid']] = $address['aname'];
		$this->assign('address', $addressListArr);
		
		$this->assign('theme', array()); 
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
		$mstatus = I('get.status', 0, 'intval') or exit('0');
		
		$MerchantMobel = D('Merchant');
		$MerchantMobel->starttrans();
		
		$status_01 = \Common\Org\Maxmerchant::SetMerchantInc( $jid );
		$status_02 = D('Merchant')->deleteMerchant( $jid, $mstatus);
		
		if( $status_01 && $status_02 ) {
			$MerchantMobel->commit(); exit('1');
		} else {
			$MerchantMobel->rollback(); exit('0');
		}
	} 

	//查看商户详细资料
	public  function preview(){
		$jid = I('jid', 0, 'intval');
		$previewData=M("merchant ag")->join("azd_vocation am on ag.vid = am.vid") ->join('azd_address ad on ad.aid=ag.mcity') ->join('azd_member ac on ac.mid=ag.mid') ->field('ag.*, am.title,ad.aname,ac.mid,ac.mstatus,ac.mregdate,ac.mname,ac.mpwd,ac.msurname,ac.idcard')->where("ag.jid=$jid")->find();
		$imgData=unserialize($previewData['mcertificates']);
		$previewData['yimg']=$imgData['yyzz'];
		$previewData['simg']=$imgData['swdj'];
		$previewData['fimg']=$imgData['frzj'];
		$this->assign('previewData',$previewData);
		$this->display();	
	}


	//修改商户
	public function edit() {
		if( IS_POST ) {
			$_POST['info']['mcertificates'] = serialize($_POST['image']);
			if(D('Merchant')->update($_POST['info']) !== false || M('merchantApp')->save($_POST['app']) !== false) {
				$this->display('Jump:success');  
			} else { 
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
			$this->assign('theme', M('theme')->where(" (`t_vid` like '%,{$vid},%' or `t_vid`=',0,') && t_status=1 ")->select());  
			$this->display();
		}
	}

	//门店列表	  
	public  function shopList(){
		$jid = I('jid', 0, 'intval');
		$merchantData=M("merchant")->where("jid=".$jid)->find();
		
		if(I('get.mname')){
			$str=str_replace(" ","",I('get.mname')); $str=trim($str); $where['sname|scontactsname|scontactstel|mservetel|msaletel'] = array('like', "%{$str}%", 'or');
		}
		$where['jid']=$jid; 
		$where['_string'] = "(status='1' or status='0')";
		$Page= new \Think\Page(D('View')->view('shop')->where($where)->count(),10);	  
		$shopListData=D('View')->view('shop')->where($where)->order('sid DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('page',$Page->show());   	
		$this->assign("shopListData",$shopListData);    
		$this->assign('merchantData',$merchantData);
		$this->display();
	}	


	//添加shop门店页面
	public function shopAdd(){
		if(IS_POST) {
			array_walk($_POST['shop'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$member['mname'] = I('post.mname', '');
			$member['mpwd'] = md5(md5(I('post.mpwd')));
			$member['mtype'] = 2;
			$member['mregdate'] = $member['mlogindate'] = date('Y-m-d H:i:s');
		
			if( ($sid=D('Shop')->insert($_POST['shop'])) && ($mid=D('Member')->insert($member)) ) {
				if(M('merchantUser')->add(array('tmid'=>$mid, 'tjid'=>intval($_POST['shop']['jid']), 'tsid'=>$sid, 'type'=>2))) $this->display('Jump:success');
			} else { 
				if( $sid ) D('Shop')->where('sid='.$sid)->delete();
				if( $mid ) D('Member')->where('mid='.$mid)->delete();
				$this->assign('msg', D('Shop')->getError()." ".D('Member')->getError()); $this->display('Jump:error'); 
			}						
		} else {
			$jid=I('get.jid', 0, 'intval');
			
			foreach($this->_AddressList as $address) if($address['aid']!=0 && $address['apid']==0) $addressListArr[$address['aid']] = $address['aname'];
			$this->assign('address', $addressListArr);
		
			$this->assign('merchant', M('merchant')->where("jid=$jid")->find());
			$this->display();	
		}
	}

	//修改店铺
	public function updateShop() {
		if( IS_POST ) {
			array_walk($_POST['shop'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			if( D('shop')->update($_POST['shop']) !== false ) {
				$this->display('Jump:success');
			} else { $this->assign('msg', D('Shop')->getError()); $this->display('Jump:error'); }		
		} else {
			$shop = M('shop')->alias('AS s')->where(array('sid'=>I('get.sid', 0, 'intval')))->join('__MERCHANT__ as m ON m.jid=s.jid')->field('s.*,m.mnickname')->find();
			if(!is_array($shop) || empty($shop)) { $this->display('Jump:error'); }
			$this->assign('shop', $shop);
			
			
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
			$this->display(); 				
		}
	}
	
	//删除店铺fdf 
	public function shopDel() {
		$sid = I('get.sid', ''); if(!$sid) exit('0'); $status = I('get.status', 0, 'intval') == 1 ? '0' : '1';  
		$tmid=M("merchant_user")->where("tsid=$sid")->find();
		$mid=$tmid['tmid'];M('member')->where(array('mid'=>array('in', "$mid")))->save(array('mstatus'=>$status)); 
		exit(M('shop')->where(array('sid'=>array('in', "$sid")))->save(array('status'=>$status))!== false ? "1" : "0");  
	}
	
	//APP已生成列表
	public function appList() {
		$merchant_id_list = $this->_getMerchantId();
		if( I('get.jid', '') ) { $jianame = I('get.jid', ''); $where['mnickname'] = array('like', "%{$jianame}%", 'or'); }
        $where['iosurl'] = array('NEQ', '');
		$where['appurl'] = array('NEQ', '');
		$where['jid'] = array('in', $merchant_id_list);
		$where['mstatus']= array("neq", -1);
		if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['mregdate'] = array(array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime')))), array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime')))), 'and');          					
		} elseif( I('get.statime', '') ) {
			$where['mregdate'] = array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['mregdate'] = array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime'))));     	
		} 
        $page = new \Think\Page(D('View')->view('emerchant')->where($where)->count(), 10); 
    	$this->assign('appsList', D('View')->view('emerchant')->where($where)->order("jid desc")->limit($page->firstRow.','.$page->listRows)->select()); 
        $this->assign('pages', $page->show()); 
        $this->display(); 
	}
	
	//APP未生成列表
	public function appnoList() {
		$merchant_id_list = $this->_getMerchantId();
		if( I('get.jid', '') ) { $jianame=I('get.jid', ''); $where['mnickname'] = array('like', "%{$jianame}%", 'or'); }
       	if( I('get.statime', '') && I('get.endtime', '') ) {
			$where['mregdate'] = array(array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime')))), array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime')))), 'and');          					
		} elseif( I('get.statime', '') ) {
			$where['mregdate'] = array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['mregdate'] = array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime'))));     	
		} 
        $where['jid'] 	 = array('in', $merchant_id_list);
		$where['mstatus']= array("neq", -1);
        $where['_string'] = "( appurl='' or iosurl='' )";

        $page = new \Think\Page(D('View')->view('emerchant')->where($where)->count(), 10); 
    	$this->assign('appsList', D('View')->view('emerchant')->where($where)->order("jid desc")->limit($page->firstRow.','.$page->listRows)->select()); 
        $this->assign('pages', $page->show()); 
        $this->display(); 
	}
	
	//applogo上传
    public function kindeditorAppUpload() {
		if(stripos($_SERVER['HTTP_REFERER'], 'dishuos.com') === false ) E('你无权进行此操作');
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
	
	//下载所有的商家
	public function downloadMerchant()
	{
		$agentid = \Common\Org\Cookie::get('agentid');
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
		
		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		foreach($char_column_list as $char)
			$objPHPExcel->getActiveSheet()->getColumnDimension($char)->setWidth('30');
		
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '商家统计表');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
 		
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '商家ID')
					->setCellValue('B2', '商家全称')
					->setCellValue('C2', '商家简称')
					->setCellValue('D2', '所属代理')
					->setCellValue('E2', '法人名称')
					->setCellValue('F2', '法人电话')
					->setCellValue('G2', '入驻时间');
		
		
		//查询所有的子代理
		$child_agent_list_array = array(
			$agentid => '自身代理'
		);
		$child_agent_list = M('agent')->alias('AS a')->join("__MEMBER__ AS m ON a.mid=m.mid")->where("a.pid={$agentid} and m.mstatus=1")->field("a.id,a.anickname")->select();
		foreach($child_agent_list as $c) $child_agent_list_array[$c['id']] = $c['anickname'];
		
		//获取所有的商家
		$agentid_list = array_keys($child_agent_list_array);
		$merchant_list = M('merchant')->alias('AS j')->join("__MEMBER__ AS m ON j.mid=m.mid")->where(array("j.magent"=>array("in", $agentid_list), "m.mstatus"=>1))->field("j.*,m.mregdate")->select();
		
		if( is_array($merchant_list) && !empty($merchant_list) )
		{
			$i = 3;
			foreach($merchant_list as $c):
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:G{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}:G{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$i, $c['jid'])->setCellValue('B'.$i, $c['mnickname'])->setCellValue('C'.$i, $c['mabbreviation'])->setCellValue('D'.$i, $child_agent_list_array[$c['magent']])->setCellValue('E'.$i, $c['mlpname'])->setCellValue('F'.$i, $c['mlptel'])->setCellValue('G'.$i, $c['mregdate']);
				$i ++;
			endforeach;
		}
		
		$objPHPExcel->getActiveSheet()->setTitle('商家统计表');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="商家统计表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;	
	}
	
	//获取此代理商下的所有商家ID
	public function _getMerchantId() {
		//先获取商家中的所有子代理
		$agentid = \Common\Org\Cookie::get('agentid');
		if( !$agentid ) return false;
		
		$agent_id_list = $this->_getAgentId( $agentid );
		$agent_id_list[] = $agentid;
		
		$cagent_list = M("agent")->where("pid=".$agentid)->field("id")->select();
		foreach($cagent_list as $c) $agent_id_list[] = $c['id'];
		
		//获取所有的商家ID
		$merchant_list = M("merchant j")->join("__MEMBER__ AS m ON j.mid=m.mid")->where(array("m.mstatus"=>array("neq", "-1"), "j.magent"=>array("in", $agent_id_list)))->select();
		$merchant_list_id = array();
		foreach( $merchant_list as $m ) $merchant_list_id[] = $m['jid'];
		
		return $merchant_list_id;		
	}
	
	//获取此代理商下的所有子代理
	public function _getAgentId( $agentid ) {
		static $cagent_list_id = array();

		$cagent_list = M("agent a")->join("__MEMBER__ AS m ON a.mid=m.mid")->where("a.pid=".$agentid." AND m.mstatus <> -1")->field("id")->select();
		if( is_array($cagent_list) && !empty($cagent_list) ) {
			foreach($cagent_list as $c) {
				$cagent_list_id[] = $c['id']; self::_getAgentId($c['id']);
			}
		}
		return $cagent_list_id;
	}
	
	
}