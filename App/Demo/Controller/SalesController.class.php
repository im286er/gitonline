<?php
namespace Demo\Controller;
//ALTER TABLE `azd_voucher` ADD `vu_rule` SMALLINT NOT NULL COMMENT '消费规则，满70使用，请输入70' AFTER `vu_price` 
class SalesController extends MerchantController {
	
	//我的订单
	public function myorder(){
		$where = $userids = array();
		$this->type == 1 ? $where['o_jid']=$this->jid : $where['o_sid']=$this->tsid;
		$where['o_gtype'] = 'Choose';
	    $page = new \Demo\Org\Page(M('Order')->where($where)->count(), 2);
		$datalist = M('Order')->where($where)->order('o_dstime desc')->limit($page->firstRow.','.$page->listRows)->select();
		if( is_array($datalist) && !empty($datalist) ) {
			foreach( $datalist as $_key=>$_value ) {
				if($_value['o_table'])$datalist[$_key]['ogoods'] = M($_value['o_table'])->where(array('sp_oid'=>$_value['o_id']))->order('sp_id')->select();
				$datalist[$_key]['voucher'] = M('VoucherOrder')->where('o_id='.$_value['oid'])->find();
				$userids[] = $_value['o_uid'];

			}	
		}
		$shops = M('shop')->where(array('jid'=>$this->jid))->getField('sid,sname');
		if($userids)$users = M('user')->where(array('u_id'=>array('in',$userids)))->getField('u_id,u_name,u_ename,u_phone');
		$this->assign('users', $users);
		$this->assign('shops', $shops);
        $this->assign('pages', $page->show());
		$this->assign('datalist', $datalist);
		$this->assign('odstatus', $this->odstatus(1));
		$this->assign('oostatus', $this->oostatus());
    	$this->display();
    }
    
	//我的预定
	public function myreserve(){
		$where =  $userids = array();
		$where['o_gtype'] = 'Seat';
		$this->type == 1 ? $where['o_jid']=$this->jid : $where['o_sid']=$this->tsid;
	    $page = new \Demo\Org\Page(M('Order')->where($where)->count(), 2); 
		$datalist = M('Order')->where($where)->order('o_dstime desc')->limit($page->firstRow.','.$page->listRows)->select();
		if(is_array($datalist) && !empty($datalist) ) {
			foreach( $datalist as $_key=>$_value ) {
				if($_value['o_table'])$ogoods = $datalist[$_key]['ogoods'] = M($_value['o_table'])->where(array('sp_oid'=>$_value['o_id']))->order('sp_id')->select();
				$datalist[$_key]['sp_date'] = $ogoods['0']['sp_date'];
				$datalist[$_key]['voucher'] = M('VoucherOrder')->where('o_id='.$_value['o_id'])->find();
				$goods = array_column($ogoods,'sp_name','sp_gid');
				$datalist[$_key]['goodsname'] =  implode("、",$goods);
				$userids[] = $_value['o_uid'];
			}	
		}
		$shops = M('shop')->where(array('jid'=>$this->jid))->getField('sid,sname');
		if($userids)$users = M('user')->where(array('u_id'=>array('in',$userids)))->getField('u_id,u_name,u_ename,u_phone');
		$this->assign('users', $users);
		$this->assign('shops', $shops);
        $this->assign('pages', $page->show());
		$this->assign('datalist', $datalist);
		$this->assign('odstatus', $this->odstatus(2));
		$this->assign('oostatus', $this->oostatus());
    	$this->display();
    }

	public function oostatus(){
		$oostatus =  array(
				0 => '<span style="color:red;">未支付</span>',
				1 => '<span style="color:#339900;">已支付</span>',
				2 => '<span style="color:#ff9900;">已退款</span>',
			);
		return $oostatus;
	}

	public function odstatus($ordertype=1){
		if($ordertype==1){
			$odstatus =  array(
				1 => '<span style="color:red;">订单待处理</span>',
				3 => '<span style="color:#339900;">已确认订单</span>',
				4 => '<span style="color:#ff9900;">已拒绝订单</span>',
				5 => '<span style="color:#000000;">已关闭订单</span>',
			);
		}elseif($ordertype==2){
			$odstatus =  array(
				1 => '<span style="color:red;">预定待处理</span>',
				3 => '<span style="color:#339900;">同意预定</span>',
				4 => '<span style="color:#ff9900;">拒绝预定</span>',
				5 => '<span style="color:#000000;">预定关闭</span>',
			);	
		
		}
		return $odstatus;
	}

	public function operation(){
	   $type = I('post.type');
	   if(!$type && ($type!='o_dstatus' || $type!='o_pstatus' ))exit('0');
	   $status = I('post.status', '', 'intval');
	   $oid = I('post.o_id');
	   if(!$oid)exit('0');
	   $where = array();
	   $this->type == 1 ? $where['o_jid']=$this->jid : $where['o_sid']=$this->tsid;
	   $where['o_id']=$oid;
	   $updatedata = array();
	   $updatedata[$type] = $status;
	   if($type=='oostatus' && $status == 1)
	   $updatedata['o_pstime'] = date('Y-m-d H:i:s');
	   $result = M('order')->where($where)->setField($updatedata);
	   exit($result?'1':'0');
	}

	//在线消费、远程预订、本地视频
	public function goods() {
		$ctype=I('get.ctype', 0, 'intval'); if( !$ctype || !in_array($ctype, array('1', '2', '3')) ) E('你无权查看当前页面');
		if( $this->type == 1 ) {
			file_exists($this->path.'InfoMenu'.$ctype.'Name.php') && $modulename=file_get_contents($this->path.'InfoMenu'.$ctype.'Name.php');
			file_exists($this->path.'InfoMenu'.$ctype.'Icon.php') && $moduleicon=file_get_contents($this->path.'InfoMenu'.$ctype.'Icon.php');
			$this->assign('modulename', $modulename ? $modulename : '');
			$this->assign('moduleicon', $moduleicon ? $moduleicon : '');
			$this->assign('modulelink', $ctype==1 ? 'http://yd.dishuos.com/Shop/index/mod/Choose/jid/'.$this->jid.'.html' : 'http://yd.dishuos.com/Shop/index/mod/Seat/jid/'.$this->jid.'.html');
		}

		$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
		$classlist = M('class')->where(array('jid'=>$this->jid, 'status'=>1, 'sid'=>$sid, 'ctype'=>$ctype))->order('corder desc')->select();
		
		if( is_array($classlist) && !empty($classlist) ) {
			$this->assign('classlist', $classlist);
			$cid = isset( $_GET['cid'] ) && intval( $_GET['cid'] ) ? intval($_GET['cid']) : $classlist[0]['cid'];
			B('Common\\Behavior\\CheckMerchantCid', '', $cid);

			$MinfoModel = $ctype==3 ? M("video") : M('goods');
			$page = new \Demo\Org\Page( $MinfoModel->where(array('cid'=>$cid, 'gstatus'=>1, ))->count(), 5);
			$this->assign( 'infolist', $MinfoModel->where(array('cid'=>$cid, 'gstatus'=>1))->order("gorder asc")->limit($page->firstRow.','.$page->listRows)->select() );
			
			//echo $MinfoModel->getlastsql();
			$this->assign('page', $page->show());
			$this->assign('cid', $cid);
		}

		$shops = M('shop')->where(array('jid'=>$this->jid))->getField('sid,sname');
		$this->assign('shops', $shops);
		$this->assign('sid', $sid);
		$this->assign('tfs', ($this->type==1 && $sid!=0) || $this->type!=1 ? '0' : '1');
		$this->assign('ptitle', $ctype==1 ? '商品上架' : ($ctype==2 ? '预约上架' : '视频上架'));
		$this->assign('guide',I('guide'));
		$this->display();
	}

	//在线消费和远程预订中的 设置模块名称和ICON图标
	public function resetModuleInfo() {
		$ModuleType = I('post.ModuleType', '', 'intval');
		$ModuleName = I('post.ModuleName', '');
		$ModuleIcon = I('post.ModuleIcon', '');

		if( $ModuleType && $ModuleName ) {
			$s=file_put_contents($this->path.'InfoMenu'.$ModuleType.'Name.php', $ModuleName);
		}

		if( $ModuleType && $ModuleIcon ) {
			$s=file_put_contents($this->path.'InfoMenu'.$ModuleType.'Icon.php', $ModuleIcon);
		}
		exit( $s ? '1' : '0' );
	}


	//添加分类信息
	public function addClass() {
		if( IS_POST ) {
			if( !$_POST['cname'] || !intval($_POST['ctype']) ) exit('0');
			exit( M("class")->add(array('jid'=>$this->jid, 'sid'=>$this->type==1 ? I('post.sid', 0, 'intval') : $this->tsid, 'cname'=>I('post.cname', ''), 'ctype'=>I('post.ctype', '', 'intval'))) ? '1' : '0' );
		} else {
			$this->assign('sid', $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid);
			$this->display();
		}
	}

	//删除分类信息
	public function delClass() {
		if( !IS_POST || !$_POST['cid'] ) exit('0');
		B('Common\\Behavior\\CheckMerchantCid', '', $_POST['cid']);
		exit( M('class')->where(array('cid'=>I('post.cid')))->save(array('status'=>0)) !== false ? '1' : '0' );
	}

	//修改分类信息
	public function editClass() {
		$cid=I('cid', 0, 'intval'); B('Common\\Behavior\\CheckMerchantCid', '', $cid);

		if( IS_POST ) {
			if( !$_POST['cname'] || !$_POST['cid'] ) exit('0');
			exit( M("class")->save(array('cname'=>I('post.cname', ''), 'cid'=>I('post.cid', 0, 'intval'))) ? '1' : '0' );
		} else {
			$this->assign('class', M('class')->where(array('cid'=>$cid))->find());
			$this->display();
		}
	}

    //ajax重新加载
	public function ajaxClass() {
		$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
		$classList = M('class')->where(array('jid'=>$this->jid, 'sid'=>$sid, 'status'=>1, 'ctype'=>I('get.ctype', 0, 'intval')))->order('corder')->select();
		$html = "";
		if(is_array($classList) && !empty($classList)) {
			foreach($classList as $class) {
				$html .= '<li data-id="'.$class['cid'].'" data-order="'.$class['corder'].'" class="">';
				$html .= '	<a href="/Sales/goods/ctype/'.I('get.ctype', 0, 'intval').'/sid/'.$sid.'/cid/'.$class['cid'].'.html">'.$class['cname'].'</a>';
				$html .= '	<b class="pull-right" style="display:none;">';
                $html .= '		<i class="writeicon" onClick="DialogFrameFun(465, 234, \''.U('/Sales/editClass', array('cid'=>$class['cid'], 'sid'=>$sid), true).'\')"></i>';
                $html .= '		<i onClick="DeleMenu(\''.$class['cid'].'\')" class="deleteicon"></i>';
				$html .= '	</b>';
				$html .= '</li>';
			}	
		}
		exit( $html );
	}
	
	//添加商品
	public function addGoods() {
		if( IS_POST ) { 
			if( !$_POST['cid'] ) exit( json_encode(array('msg'=>'添加失败')) );
			B('Common\\Behavior\\CheckMerchantCid', '', $_POST['cid']);
			$MinfoModel = $_POST['ctype']==3 ? D('Video') : D('Goods'); unset( $_POST['ctype'] );
			$msg = ($gid=$MinfoModel->insert($_POST)) ? array('msg'=>'') : array('msg'=>$MinfoModel->getError());
			if( $msg['msg'] == '' && $gid ) {
				$msg['info'] = $MinfoModel->where(array('gid'=>$gid))->find();
			}
			exit( json_encode($msg) );
		} else {
			$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
			$ctype=I('get.ctype', 0, 'intval') or die('你无权查看当前页面');
			$this->assign('clist', M('class')->where(array('jid'=>$this->jid, 'sid'=>$sid, 'ctype'=>$ctype, 'status'=>1))->select());
			$this->assign('sid', $sid);
			$this->display( $ctype==3 ? "addVideo" : 'addGoods' );	
		}
	}

    //删除商品\视频
    public function delGoods() {
    	$MinfoModel = isset($_POST['type']) && intval($_POST['type'])==1 ? M('goods') : M('video');
    	$goods = $MinfoModel->where(array('gid'=>I('post.id')))->find();
		if( !is_array($goods) || empty($goods) ) exit( json_encode(array('msg'=>'你无权删除当前页面')) );
		B('Common\\Behavior\\CheckMerchantCid', '', $goods['cid']);

		exit( $MinfoModel->where(array('gid'=>I('post.id')))->setField('gstatus', 0) ? '1' : '0' );
    }
	
	//修改商品
	public function editGoods() {
		if( IS_POST ) {
			$goods = M('goods')->where(array('gid'=>I('post.gid')))->find();
			if( !is_array($goods) || empty($goods) ) exit( json_encode(array('msg'=>'你无权修改当前页面')) );
			B('Common\\Behavior\\CheckMerchantCid', '', $goods['cid']);
			
			$msg = D('Goods')->update($_POST) !== false ? array('msg'=>'') : array('msg'=>D('Goods')->getError());
			if( $msg['msg'] == '' && $_POST['gid'] ) {
				$msg['info'] = D('Goods')->where(array('gid'=>$_POST['gid']))->find();
			}
			exit( json_encode($msg) ); 
		} else {
			$ginfo = M('goods')->alias('AS g')->join('__CLASS__ as c ON g.cid=c.cid', 'left')->where(array('c.jid'=>$this->jid, 'gid'=>I('get.gid')))->find();
			if( !is_array($ginfo) || empty($ginfo) ) E('你无权查看当前页面');
			$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
			$this->assign('clist', M('class')->where(array('jid'=>$this->jid, 'sid'=>$sid, 'ctype'=>$ginfo['ctype'], 'status'=>1))->select());
			$this->assign('ginfo', $ginfo);
			$this->display();		
		}
    }

	//同步数据到分店
	public function synchro() {
		if( IS_POST ) {
			$ctype=intval( $_POST['ctype'] ); $sid=substr($_POST['sid'], 0, -1);
			if(!$ctype || !$sid) exit("0");
			$this->_synchroGoods($ctype, $sid);
			exit("1");
		} else {
			$this->assign('splist', M('shop')->where(array('jid'=>$this->jid, 'status'=>"1"))->select());
			$this->display();	
		}
	}
	
	private function _synchroGoods($ctype, $sid) {
		foreach( M('class')->where(array('sid'=>array('in', "$sid"), 'ctype'=>$ctype))->field('cid')->select() as $_cid ) {
			$catidlist[] = $_cid;	
		}
		$catidlist = implode(',', array_unique($catidlist));
		
		M('class')->where(array('sid'=>array('in', "$sid"), 'ctype'=>$ctype))->setField('status', 0);
		M('goods')->where(array('sid'=>array("in", "$sid"), 'cid'=>array("in", "$catidlist")))->setField('gstatus', 0);
		
		//查看商家数据
		$cidlist = M('class')->where(array('jid'=>$this->jid, 'sid'=>0, 'ctype'=>$ctype, 'status'=>1))->select();
		foreach($cidlist as $cinfo) {
			$ocid=$cinfo['cid']; unset( $cinfo['cid'] );
			foreach( explode(',', $sid) as $nsid ) {
				$cinfo['sid'] = $nsid;
				if( $ncid=M('class')->add($cinfo) ) {
					M('class')->query("insert into azd_goods(`gname`,`gdescription`,`cid`,`sid`,`goprice`,`gdprice`,`gstock`,`gimg`,`gorder`,`gtype`,`gstatus`) select `gname`,`gdescription`,$ncid as `cid`, $nsid as `sid`,`goprice`,`gdprice`,`gstock`,`gimg`,`gorder`,`gtype`,`gstatus` from azd_goods where cid={$ocid} and sid=0 and gstatus=1");
				}
			}
		}
	}


	//代理券
    public function jqlist() {
		$where = $this->type==1 ? array('vu_jid'=>$this->jid) : array('vu_sid'=>array('like', '%,'.$this->tsid.',%'));
		$page = new \Demo\Org\Page(M('voucher')->where($where)->count(), 5);
		$this->assign('vulist', M('voucher')->alias('v')->field('v.*,(SELECT count(*) FROM azd_voucher_user where vu_id=v.vu_id) as vu_sum')->where($where)->order('v.vu_id DESC')->limit($page->firstRow.','.$page->listRows)->select());
		if( $this->type==1 ) {
			$shoplist = M('shop')->where(array('jid'=>$this->jid))->field('sid,sname')->select();
			foreach($shoplist as $shop) $shopname[$shop['sid']] = $shop['sname'];
			$this->assign('shopname', $shopname );
		}
		$this->assign('pages', $page->show());

		if( IS_POST ){
			$Module['Name'] = I('post.VoucherModuleName', '');
			$Module['Link'] = I('post.VoucherModuleLink', '');
			if( $Module) {
				$this->writeFile($this->path.'VoucherModule.php',serialize($Module));
				exit("1");
			}
		}
		file_exists($this->path.'VoucherModule.php') && $VoucherModule=unserialize($this->readsFile($this->path.'VoucherModule.php'));
		$this->assign('VoucherModule', $VoucherModule);
		$this->assign('jid', $this->jid);
		$this->display();
    }
	
	//添加代理券
	public function addjq() {

		
		if( IS_POST ) {
         $data = array();
			$data['vu_name'] = I('post.n', '');
			$data['vu_price'] = I('post.p', '0.00', 'floatval');
			$data['vu_num'] = I('post.u', '1', 'intval');
			$data['vu_stime'] = isset($_POST['s']) && !empty($_POST['s']) ? strip_tags($_POST['s']) : date('Y-m-d H:i:s');
			$data['vu_etime'] = isset($_POST['e']) && !empty($_POST['e']) ? strip_tags($_POST['e']) : date('Y-m-d H:i:s', strtotime('+7 days'));
			$data['vu_jid'] = $this->jid;
			$data['vu_cum'] = I('post.t', 0, 'intval');
			$data['vu_img'] = I('post.img', '');
			$data['vu_description'] = I('post.des', '');
			if( !$data['vu_name'] ) exit('0');
			if( $this->type != 1 ) {
				$data['vu_sid'] =','.$this->tsid.',';
			} else {
			 $data['vu_sid'] = ','.trim(implode(',', $_POST['d']), ',').',';
			}
			exit( M('voucher')->add($data) ? "1" : "0" );
		} else {
			//如果是 商家登录（品牌），先要判断此商家有没有分店
			if( $this->type == 1 ) {
				
				$page = new \Demo\Org\Page(M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->count(), 15);
				$splist = M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->limit($page->firstRow.','.$page->listRows)->select();
				if( !is_array($splist) || empty($splist) ) $this->error('您还没有一个分店，请先添加分店！');
				$this->assign('splist', $splist);
				$this->assign('pagef', $page->show());
			}
			$this->display();	
		}
	}
	
	//实效｜核销 代理券
	public function deljq() {
		$vuinfo = M('voucher')->where(array('vu_id'=>I('post.id', 0, 'intval')))->find();
		if( !is_array($vuinfo) || empty($vuinfo) || $vuinfo['vu_jid'] != $this->jid ) exit("0");
		$status = I('post.status', 0, 'intval');
		exit( M('voucher')->where(array('vu_id'=>I('post.id', 0, 'intval')))->setField('vu_status', $status) !== false ? "1" : "0");
	}
	
	//修改代理券
	public function editjq() {
		if( IS_POST ) {
			$data = array();
			$data['vu_name'] = I('post.n', '');
			$data['vu_price'] = I('post.p', '0.00', 'floatval');
			$data['vu_num'] = I('post.u', '1', 'intval');
			$data['vu_stime'] = isset($_POST['s']) && !empty($_POST['s']) ? strip_tags($_POST['s']) : date('Y-m-d H:i:s');
			$data['vu_etime'] = isset($_POST['e']) && !empty($_POST['e']) ? strip_tags($_POST['e']) : date('Y-m-d H:i:s', strtotime('+7 days'));
			$data['vu_jid'] = $this->jid;
			$data['vu_cum'] = I('post.t', 0, 'intval');
			$data['vu_img'] = I('post.img', '');
			$data['vu_description'] = I('post.des', '');
			if( !$data['vu_name'] ) exit('0');
			if( $this->type != 1 ) {
				$data['vu_sid'] = ','.$this->tsid.',';
			} else {
				$data['vu_sid'] = ','.trim(implode(',', $_POST['d']), ',').',';
			}
			exit( M('voucher')->where('vu_id='.I('post.id', 0, 'intval'))->save($data) ? "1" : "0" );
		} else {
			//如果是 商家登录（品牌），先要判断此商家有没有分店
			if( $this->type == 1 ) {
				$page = new \Demo\Org\Page(M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->count(), 15);
				$splist = M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->limit($page->firstRow.','.$page->listRows)->select();
				if( !is_array($splist) || empty($splist) ) $this->error('您还没有一个分店，请先添加分店！');
				$this->assign('splist', $splist);
				$this->assign('pagef', $page->show());
			}
			$voucher  = M('voucher')->where(array('vu_id'=>I('get.id', 0, 'intval')))->find();
			if( !is_array($voucher) || empty($voucher) || $voucher['vu_jid'] != $this->jid ) $this->error('你无权查看当前页面');
			$this->assign('voucher', $voucher);
			$this->display();	
		}
	}
	


    public function writeFile($filename, $str) {  
        if (function_exists(file_put_contents)) {  
            file_put_contents($filename, $str);  
        } else {  
            $fp = fopen($filename, "wb");  
            fwrite($fp, $str);  
            fclose($fp);  
        }  
    }
    public function readsFile($filename) {  
        if (function_exists(file_get_contents)) {  
            return file_get_contents($filename);  
        } else {  
            $fp = fopen($filename, "rb");  
            $str = fread($fp, filesize($filename));  
            fclose($fp);  
            return $str;  
        }  
    }

}