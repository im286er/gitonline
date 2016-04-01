<?php
namespace Merchant\Controller;
class SalesController extends MerchantController {
	
	//我的订单
	public function myorder() {
		$where = $userids = array();
		!$_GET['dstatus'] && $_GET['dstatus']=0;
		
		//如果是品牌登录，则显示所有门店，如果是店长登录，只显示自己的门店订单
		$this->type == 1 ? $where['o_jid']=$this->jid : $where['o_sid']=$this->tsid;
		
		$where['o_gtype'] = 'Choose';//订单类型 
		
		//搜索
		if( I('get.o_id') != '' ) 
		{
			$where['o_id'] = array('like', '%'.I('get.o_id').'%');
		}
		if( I('get.o_dstatus') != '' ) 
		{
			$where['o_dstatus'] = I('get.o_dstatus');
		}
		if( I('get.o_pstatus') != '' ) 
		{
			$where['o_pstatus'] = I('get.o_pstatus');
		}

		if( I('get.statime', '') && I('get.statime', '') ) {
			$where['o_dstime'] = array(array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime')))), array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime')))), 'and');          					
		} elseif( I('get.statime', '') ) {
			$where['o_dstime'] = array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['o_dstime'] = array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime'))));    	
		}

		
		//订单状态选择
		if( I('get.dstatus', 0, 'intval') != 0 )
		{
			$where['o_dstatus'] = I('get.dstatus', 0, 'intval');
		}
		
	    $page = new \Common\Org\Page(M('Order')->where($where)->count(), 2);
		$datalist = M('Order')->where($where)->order('o_dstime desc')->limit($page->firstRow.','.$page->listRows)->select();
		if( is_array($datalist) && !empty($datalist) ) {
			foreach( $datalist as $_key=>$_value ) {
				if($_value['o_table']) 
					$datalist[$_key]['ogoods'] = M($_value['o_table'])->where(array('sp_oid'=>$_value['o_id'],'sp_status'=>1))->order('sp_id')->select();
				$datalist[$_key]['voucher'] = M('VoucherOrder')->where('o_id='.$_value['o_id'])->find();
				//$userids[] = $_value['o_uid'];
			}
		}
		$shops = M('shop')->where(array('jid'=>$this->jid))->getField('sid,sname');
		//if($userids) $users = M('user')->where(array('u_id'=>array('in',$userids)))->getField('u_id,u_name,u_ename,u_phone');
		
		
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
			3=>'<span style="color:red;"><b>待退款</b></span>',
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

		$this->assign('countOrder', $this->countOrder(1));
		//$this->assign('users', $users);
		$this->assign('shops', $shops);
        $this->assign('pages', $page->show());
		$this->assign('odstatus', $this->odstatus(1));
		$this->assign('oostatus', $this->oostatus());
		$this->assign('datalist', $datalist);
		$this->assign('type', $this->type);
    	$this->display();
    }


    //下单后哦退货的商品
	public function recedeOrderGoods(){
		$reasons = array('1'=>'经理同意','2'=>'库存不全','3'=>'客人要求','4'=>'质量问题','10'=>'其他原因');
		$this->assign('reasons',$reasons);
		$where =  array();
		$this->type == 1 ? $where['sp_jid']=$this->jid : $where['sp_sid']=$this->tsid;
		$shops = M('shop')->where(array('jid'=>$this->jid))->getField('sid,sname');
		$this->assign('shops', $shops);
		if( I('get.statime', '') && I('get.statime', '') ) {
			$where['sp_date'] = array(array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime')))), array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime')))), 'and');          					
		} elseif( I('get.statime', '') ) {
			$where['sp_date'] = array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['sp_date'] = array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime'))));    	
		}
		if( I('get.sp_otype') != '' ) {
			$where['sp_otype'] = I('get.sp_otype');
		}
		if( I('get.sp_reason') != '' ) 
		{
			$where['sp_reason'] = I('get.sp_reason');
		}

		if(I('get.type')=='tab1'){
			$condition = array();
			$reasondate = $datas = array();
			$otypeprice = M('goods_recede')->where($where)->group('sp_otype')->getField('sp_otype,sum(sp_price) AS price');
			$datetype = I('get.datetype',date('m'),'intval');
			if($datetype < 1 && $datetype > 12)$datetype = date('m');
			$this->assign('datetype', $datetype);
			$data = date('Y-'.$datetype.'-d');
			$stime = date('Y-m-01', strtotime($data));
			$etime = date('Y-m-d', strtotime(date('Y-m-01', strtotime($data)) . ' +1 month -1 day'));
			$recedeprice = M('goods_recede')->field(" FROM_UNIXTIME( unix_timestamp( sp_date ) , '%d' ) as spdate,sp_reason, count( sp_id ) AS spid")->where($condition)->group('spdate,sp_reason')->select();
			if($recedeprice)foreach($recedeprice as $key => $value){
				$reasondate[trim($value['spdate'],'0')][$value['sp_reason']] = $value['spid'];//按照日期统计
			}
			for($i=1;$i<=substr($etime,-2);$i++){
				foreach($reasons as $k => $v)$datas[$i][$k] = $reasondate[$i][$k]?$reasondate[$i][$k]:'0';
				$datas[$i]['100'] = array_sum($datas[$i]);
				$datas[$i]['id'] = $i;
			}
			$this->assign('otypeprice', $otypeprice);
			$this->assign('datas', $datas);
			$this->display('Sales_recedeStatistics');
			exit;
		}


		$page = new \Common\Org\Page(M('GoodsRecede')->where($where)->count(), 2);
		$datalist = M('GoodsRecede')->where($where)->order('sp_id desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('datalist', $datalist);
		$this->assign('otypes', array('0'=>'线下付款','1'=>'支付到商家','2'=>'支付到平台'));
		
	    $this->display();
	}

    //编辑订单
    public function editOrder(){
    	$oid = I('oid',0);
    	$order_info = M('order')->where(array('o_id'=>$oid))->find();
    	if(!D('Order')->cadEdit($oid)){
    		$this->error('订单状态不正确');
    	}
    	$goods_name = trim(I('goods_name'));
    	if(!empty($goods_name)){
    		$sql = "select * from azd_goods as g,azd_class as c where g.cid=c.cid and c.status=1 and g.gstatus=1 and g.gtype=0 and g.sid=".$order_info['o_sid']." and g.gname like '%".$goods_name."%'";
    		$add_goods = M()->query($sql);
    		$this->assign('add_goods',$add_goods);
    	}
    	$shop = M('shop')->where(array('sid'=>$order_info['o_sid']))->find();
    	$order_goods = M('goods_snapshot')->where(array('sp_oid'=>$oid))->order("sp_status desc")->select();
    	$this->assign('shop',$shop);
    	$this->assign('order_info',$order_info);
    	$this->assign('order_goods',$order_goods);
    	$this->assign('CurrentUrl','Salesmyorder');
    	$this->display();
    }
    //删除订单商品
    public function deleteOrderGoods(){
    	$sp_id = I('sp_id',0);
		$sp_reason = I('sp_reason');
		$sp_cause = I('sp_cause');
		$r = D('Order')->deleteOrderGoods($sp_id,$sp_reason,$sp_cause);
		exit($r ? '1' : '0');
    }
    //编辑订单商品数量
    public function editOrderGoods(){
    	$goods = I('goods');
    	$oid = I('oid');
    	$r = D('Order')->editOrderGoods($oid,$goods);
    	exit($r ? '1' : '0');
    }
	//
	public function editOseat(){
		$oid = I('oid');
		$o_seat = I('o_seat');
		$where = array();
		$this->type == 1 ? $where['o_jid']=$this->jid : $where['o_sid']=$this->tsid;
		$where['o_id']=$oid;
		$result = M('order')->where($where)->setField(array('o_seat'=>$o_seat));
		exit($result?'1':'0');
	
	}

    //添加商品进订单
    public function addOrderGoods(){
    	$gid = I('gid');
    	$oid = I('oid');
    	$number = intval(I('number'));
    	$r = D('Order')->addOrderGoods($oid,$gid,$number);
    	exit($r ? '1' : '0');
    }
    //打印订单
    public function printOrder(){
    	$oid = I('oid');
    	//订单打印
    	D('Print')->doPrint($oid,4);
    	exit('1');
    }
    //打印返利订单
    public function printFlOrder(){
    	$oid = I('oid');
    	//订单打印
    	D('Print')->doFlPrint($oid,4);
    	exit('1');
    }
    
	//我的预定
	public function myreserve(){
		$where =  $userids = array();
		$where['o_gtype'] = 'Seat';

		if( I('get.o_id') != '' ) {
			$where['o_id'] = array('like', '%'.I('get.o_id').'%');
		}
		if( I('get.o_dstatus') != '' ) {
			$where['o_dstatus'] = I('get.o_dstatus');
		}
		if( I('get.o_pstatus') != '' ) {
			$where['o_pstatus'] = I('get.o_pstatus');
		}

		if( I('get.statime', '') && I('get.statime', '') ) {
			$where['o_dstime'] = array(array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime')))), array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime')))), 'and');          					
		} elseif( I('get.statime', '') ) {
			$where['o_dstime'] = array('egt', date('Y-m-d 00:00:00', strtotime(I('get.statime'))));	
		} elseif( I('get.endtime', '') ) {
			$where['o_dstime'] = array('elt', date('Y-m-d 23:59:59', strtotime(I('get.endtime'))));    	
		}


		$this->type == 1 ? $where['o_jid']=$this->jid : $where['o_sid']=$this->tsid;
	    $page = new \Common\Org\Page(M('Order')->where($where)->count(), 6); 
		$datalist = M('Order')->where($where)->order('o_dstime desc')->limit($page->firstRow.','.$page->listRows)->select();
		if(is_array($datalist) && !empty($datalist) ) {
			foreach( $datalist as $_key=>$_value ) {
				if($_value['o_table'])$ogoods = $datalist[$_key]['ogoods'] = M($_value['o_table'])->where(array('sp_oid'=>$_value['o_id']))->order('sp_id')->select();
				$datalist[$_key]['sp_date'] = $ogoods['0']['sp_date'];
				$datalist[$_key]['sp_number'] = $ogoods['0']['sp_number'];
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
		$this->assign('type', $this->type);
    	$this->display();
    }

	public function oostatus(){
		$oostatus =  array(
				0 => '<span style="color:red;">未支付</span>',
				1 => '<span style="color:#339900;">已支付</span>',
				2 => '<span style="color:#ff9900;">已退款</span>',
				3 => '<span style="color:#ff9900;">待退款</span>',
			);
		return $oostatus;
	}

	public function odstatus($ordertype=1){
		if($ordertype==1){
			$odstatus =  array(
				1 => '<span style="color:red;">待处理订单</span>',
				3 => '<span style="color:#339900;">待完成订单</span>',
				4 => '<span style="color:#ff9900;">已完成订单</span>',
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
	   if($type=='o_pstatus' && $status == 1){
			$updatedata['o_pstime'] = date('Y-m-d H:i:s');
	   }
	   if($type=='o_dstatus' && $status==5)D('Mobile/Goods')->reduceRepertory($oid,'setInc',1);//如果关闭订单，库存恢复
	   $result = M('order')->where($where)->setField($updatedata);
	   
	   if($type=='o_dstatus' && $status==3){
	   		//订单打印
	   		D('Print')->doPrint($oid,2);
	   }
	   
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
		$classlist = M('class')->where(array('jid'=>$this->jid, 'status'=>1, 'sid'=>$sid, 'ctype'=>$ctype))->order('corder asc')->select();
	
		if($classlist)foreach($classlist as $k=>$v){
			$classlist[$k]['corder'] = ($k*2)+10;
			M('class')->where(array('cid'=>$v['cid']))->setField('corder',($k*2)+10);
		}
		if( is_array($classlist) && !empty($classlist) ) {
			
			$this->assign('classlist', $classlist);
			$cid = isset( $_GET['cid'] ) && intval( $_GET['cid'] ) ? intval($_GET['cid']) : $classlist[0]['cid'];
			B('Common\\Behavior\\CheckMerchantCid', '', $cid);

			$MinfoModel = $ctype==3 ? M("video") : M('goods');
			$count = $MinfoModel->where(array('cid'=>$cid, 'sid'=>$sid, 'gstatus'=>array('in',array('1','2')) ))->count();

			$page = new \Common\Org\Page( $count, 5);
			$infolist = $MinfoModel->where(array('cid'=>$cid, 'sid'=>$sid, 'gstatus'=>array('in',array('1','2')) ))->order("gstatus asc,gorder asc,gid desc")->limit($page->firstRow.','.$page->listRows)->select();
			$this->assign( 'infolist',  $infolist);

			$this->assign('page', $page->show());
			$this->assign('cid', $cid);
		}

		$shops = M('shop')->where(array('jid'=>$this->jid, "status"=>'1'))->getField('sid,sname');
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
			if(is_array(I('post.print_id'))){
				$print_id = join(',',I('post.print_id'));
			}else{
				$print_id = '';
			}
			exit( M("class")->add(array('print_id'=>$print_id,'jid'=>$this->jid, 'sid'=>$this->type==1 ? I('post.sid', 0, 'intval') : $this->tsid, 'cname'=>I('post.cname', ''), 'ctype'=>I('post.ctype', '', 'intval'))) ? '1' : '0' );
		} else {
			$print_list = array();
			$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
			if($sid > 0){
				$print_list = M('print')->where(array('print_sid'=>$sid,'print_status'=>1))->select();
			}
			
			$this->assign('print_list',$print_list);
			$this->assign('sid', $sid);
			$this->display();
		}
	}

	//删除分类信息
	public function delClass() {
		if( !IS_POST || !$_POST['cid'] ) exit('0');
		B('Common\\Behavior\\CheckMerchantCid', '', $_POST['cid']);
		$r = M('class')->where(array('cid'=>I('post.cid')))->save(array('status'=>0));
		M('goods')->where(array('cid'=>I('post.cid')))->save(array('gstatus'=>0));		
		exit( $r !== false ? '1' : '0' );
	}

	//修改分类信息
	public function editClass() {
		$cid=I('cid', 0, 'intval'); B('Common\\Behavior\\CheckMerchantCid', '', $cid);

		if( IS_POST ) {
			if( !$_POST['cname'] || !$_POST['cid'] ) exit('0');
			if(is_array(I('post.print_id'))){
				$print_id = join(',',I('post.print_id'));
			}else{
				$print_id = '';
			}
			exit( M("class")->save(array('cname'=>I('post.cname', ''),'print_id'=>$print_id, 'cid'=>I('post.cid', 0, 'intval'))) ? '1' : '0' );
		} else {
			$class_info = M('class')->where(array('cid'=>$cid))->find();
			$print_list = array();
			$sid = $class_info['sid'];
			if($sid > 0){
				$print_list = M('print')->where(array('print_sid'=>$sid,'print_status'=>1))->select();
			}
			$print_id = explode(',', $class_info['print_id']);
			$this->assign('print_id',$print_id);
			$this->assign('print_list',$print_list);
			$this->assign('class', $class_info);
			$this->display();
		}
	}

    //ajax重新加载
	public function ajaxClass() {
		$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
		$classList = M('class')->where(array('jid'=>$this->jid, 'sid'=>$sid, 'status'=>1, 'ctype'=>I('get.ctype', 0, 'intval')))->order('corder')->select();
		if($classlist)foreach($classlist as $k=>$v){
			$classlist[$k]['corder'] = ($k*2)+10;
			M('class')->where(array('cid'=>$v['cid']))->setField('corder',($k*2)+10);
		}
		$countclass = count($classList);
		$html = "";
		if(is_array($classList) && !empty($classList)) {
			foreach($classList as $key => $class) {
				$html .= '<li data-id="'.$class['cid'].'" data-order="'.$class['corder'].'" class="">';
				$html .= '	<a href="/Sales/goods/ctype/'.I('get.ctype', 0, 'intval').'/sid/'.$sid.'/cid/'.$class['cid'].'.html">'.$class['cname'].'</a>';
				$html .= '	<b class="pull-right" style="display:none;">';
				if($key!=0){
					$html .= "<i class='upicon' onClick=\"OrderMenu('{$class['cid']}','{$class[corder]}','up')\" ></i>";
				}
				if($key!=($countclass-1)){
					$html .= "<i class='downicon' onClick=\"OrderMenu('{$class['cid']}','{$class[corder]}','down')\" ></i>";
				}
                $html .= '		<i class="writeicon" onClick="DialogFrameFun(465, 500, \''.U('/Sales/editClass', array('cid'=>$class['cid'], 'sid'=>$sid), true).'\')"></i>';
                $html .= '		<i onClick="DeleMenu(\''.$class['cid'].'\')" class="deleteicon"></i>';
				$html .= '	</b>';
				$html .= '</li>';
			}	
		}
		exit( $html );
	}
	
    //移动分类$map['id']  = array('elt',100);
	public function corderClass() {
		$cid = I('post.cid');
		$corder = I('post.corder');
		$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
		$type = I('post.type');
		$ctype = I('post.ctype');
		if($cid){
			if($type=='up'){//向上排序减1
				$min = M('class')->where(array('jid'=>$this->jid,'sid'=>$sid, 'corder' => array('lt',$corder),'status'=>1, 'ctype'=>$ctype))->order('corder desc')->find();
				$result = M('class')-> where(array('cid'=>$cid))->setField('corder',($min['corder']?$min['corder']:$corder)-1);
				exit($result?'1':'0');
			}elseif($type=='down'){ //向下排序加1
				$max = M('class')->where(array('jid'=>$this->jid,'sid'=>$sid, 'corder' => array('gt',$corder),'status'=>1, 'ctype'=>$ctype))->order('corder asc')->find();
				$result = M('class')-> where(array('cid'=>$cid))->setField('corder',($max['corder']?$max['corder']:$corder)+1);
				exit($result?'1':'0');
			}
		}
	}

	//添加商品
	public function addGoods() {
		if( IS_POST ) { 
			if( !$_POST['cid'] ) exit( json_encode(array('msg'=>'添加失败')) );
			B('Common\\Behavior\\CheckMerchantCid', '', $_POST['cid']);
			$MinfoModel = D('Goods'); unset( $_POST['ctype'] );
			// if($_POST['showrebate']){
			// 	unset($_POST['showrebate']);
			// 	$_POST['gvrebate'] = $_POST['gdprice'] > 0 ? $_POST['gvrebate']/$_POST['gdprice']*100 : $_POST['gvrebate']/$_POST['goprice']*100;
			// }else{
			// 	$_POST['gvrebate'] = '';
			// }
			$_POST['gvrebate'] = $_POST['gdprice'] > 0 ? $_POST['gvrebate']/$_POST['gdprice']*100 : $_POST['gvrebate']/$_POST['goprice']*100;
			$_POST['isboutique'] = isset($_POST['isboutique']) && $_POST['isboutique']==1 ? '1' : '0';
			
			if(empty($_POST['gstock'])){
				$_POST['gstock'] = -1;
			}
			
			if(is_array(I('post.printid'))){
				$_POST['printid'] = join(',',I('post.printid'));
			}else{
				$_POST['printid'] = '';
			}
			//设置图片
			$_POST['gimg'] = $_POST['picture'][0] ? $_POST['picture'][0] : $_POST['gimg'];//只取第一张做缩略图
			$_POST['pictureset'] = serialize($_POST['picture']);
			unset( $_POST['picture'] );
			
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
			
			if( ($this->type==1 || $this->type==2) && $sid ) {//添加分店的时候，要有打印机
				$printList = M('print')->where( array("print_sid"=>$sid) )->select();
				$this->assign('printList', $printList);
				$this->assign('isprint', 1);
			}
			$this->assign('CurrentUrl', 'Salesgoods');
			$this->display( $ctype==3 ? "addVideo" : 'addGoods' );	
		}
	}

    //删除商品\视频
    public function delGoods() {
    	$MinfoModel = isset($_POST['type']) && (intval($_POST['type'])==1 || intval($_POST['type'])==2) ? M('goods') : M('video');
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
			// if($_POST['showrebate']){
			// 	unset($_POST['showrebate']);
			// 	$_POST['gvrebate'] = $_POST['gdprice'] > 0 ? $_POST['gvrebate']/$_POST['gdprice']*100 : $_POST['gvrebate']/$_POST['goprice']*100;
			// }else{
			// 	$_POST['gvrebate'] = '';
			// }
			
			$_POST['gvrebate'] = $_POST['gdprice'] > 0 ? $_POST['gvrebate']/$_POST['gdprice']*100 : $_POST['gvrebate']/$_POST['goprice']*100;
			$_POST['isboutique'] = isset($_POST['isboutique']) && $_POST['isboutique']==1 ? '1' : '0'; 
			
			if(empty($_POST['gstock'])){
				$_POST['gstock'] = -1;
			}
			if(is_array(I('post.printid'))){
				$_POST['printid'] = join(',',I('post.printid'));
			}else{
				$_POST['printid'] = '';
			}
			//设置图片
			if( $goods['gtype']==0 ) {
				$_POST['gimg'] = $_POST['picture'][0] ? $_POST['picture'][0] : '';//只取第一张做缩略图
				$_POST['pictureset'] = serialize($_POST['picture']);
				unset( $_POST['picture'] );
			}
			$msg = D('Goods')->update($_POST) !== false ? array('msg'=>'') : array('msg'=>D('Goods')->getError());

			if( $msg['msg'] == '' && $_POST['gid'] ) {
				$msg['info'] = D('Goods')->where(array('gid'=>$_POST['gid']))->find();
			}
			exit( json_encode($msg) ); 
		} else {
			$ginfo = M('goods')->alias('AS g')->join('__CLASS__ as c ON g.cid=c.cid', 'left')->where(array('c.jid'=>$this->jid, 'gid'=>I('get.gid')))->find();
			if( !is_array($ginfo) || empty($ginfo) ) E('你无权查看当前页面');
			$ginfo['gvrebate'] = $ginfo['gdprice'] > 0 ? $ginfo['gvrebate']*$ginfo['gdprice']/100 : $ginfo['gvrebate']*$ginfo['goprice']/100;
			$ginfo['gvrebate'] = round($ginfo['gvrebate'],2);
			$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
			$this->assign('clist', M('class')->where(array('jid'=>$this->jid, 'sid'=>$sid, 'ctype'=>$ginfo['ctype'], 'status'=>1))->select());
			$this->assign('ginfo', $ginfo);
			
			if( ($this->type==1 || $this->type==2) && $sid ) {//添加分店的时候，要有打印机
				$printList = M('print')->where( array("print_sid"=>$sid) )->select();
				$this->assign('printList', $printList);
				$this->assign('isprint', 1);
				$print_id = explode(',', $ginfo['printid']);
				$this->assign('print_id',$print_id);
			}
			$this->assign('CurrentUrl', 'Salesgoodsctype'.$ginfo['ctype']);
			$this->display();		
		}
    }

	public function statusGoods(){
		$MinfoModel = isset($_POST['type']) && (intval($_POST['type'])==1 || intval($_POST['type'])==2) ? M('goods') : M('video');
    	$goods = $MinfoModel->where(array('gid'=>I('post.id')))->find();
		if( !is_array($goods) || empty($goods) ) exit( json_encode(array('msg'=>'你无权删除当前页面')) );
		B('Common\\Behavior\\CheckMerchantCid', '', $goods['cid']);
		exit( $MinfoModel->where(array('gid'=>I('post.id')))->setField('gstatus', $_POST['gstatus']) ? '1' : '0' );
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
			$catidlist[] = $_cid['cid'];	
		}
		M('class')->where(array('sid'=>array('in', "$sid"), 'ctype'=>$ctype))->setField('status', 0);
		M('goods')->where(array('sid'=>array("in", "$sid"), 'cid'=>array("in", $catidlist)))->setField('gstatus', 0);

		//查看商家数据
		$cidlist = M('class')->where(array('jid'=>$this->jid, 'sid'=>0, 'ctype'=>$ctype, 'status'=>1))->select();
		foreach($cidlist as $cinfo) {
			$ocid=$cinfo['cid']; unset( $cinfo['cid'] );
			foreach( explode(',', $sid) as $nsid ) {
				$cinfo['sid'] = $nsid;
				if( $ncid=M('class')->add($cinfo) ) {
					M('class')->query("insert into azd_goods(`gname`,`gdescription`,`cid`,`sid`,`goprice`,`gdprice`,`gstock`,`gimg`,`gorder`,`gtype`,`gstatus`,`gvrebate`,`pictureset`, `gcontent`, `isboutique`) select `gname`,`gdescription`,$ncid as `cid`, $nsid as `sid`,`goprice`,`gdprice`,`gstock`,`gimg`,`gorder`,`gtype`,`gstatus`,`gvrebate`,`pictureset`, `gcontent`, `isboutique` from azd_goods where cid={$ocid} and sid=0 and gstatus in(1,2)");
				}
			}
		}
	}

	//代理券
    public function jqlist() {
		$where = $this->type==1 ? array('vu_jid'=>$this->jid) : array('vu_sid'=>array('like', '%,'.$this->tsid.',%'));
		$page = new \Common\Org\Page(M('voucher')->where($where)->count(), 8);
		$this->assign('vulist', M('voucher')->alias('v')->field('v.*,(SELECT count(*) FROM azd_voucher_user where vu_id=v.vu_id) as vu_sum,(SELECT count(*) FROM azd_voucher_order where vu_id=v.vu_id) as vu_used')->where($where)->order('v.vu_id DESC')->limit($page->firstRow.','.$page->listRows)->select());
		if( $this->type==1 ) {
			$shoplist = M('shop')->where(array('jid'=>$this->jid))->field('sid,sname')->select();
			foreach($shoplist as $shop) $shopname[$shop['sid']] = $shop['sname'];
			$this->assign('shopname', $shopname );
		}
		$this->assign('pages', $page->show());

		if( IS_POST ){
			$Module['Name'] = I('post.VoucherModuleName', '');
			$Module['Link'] = I('post.VoucherModuleLink', '');
			$Module['Icon'] = I('post.VoucherModuleIcon', '');
			if( $Module) {
				$this->writeFile($this->path.'VoucherModule.php',serialize($Module));
				exit("1");
			}
		}


		file_exists($this->path.'VoucherModule.php') && $VoucherModule=unserialize($this->readsFile($this->path.'VoucherModule.php'));
		$this->assign('VoucherModule', $VoucherModule);
		$this->assign('jid', $this->jid);
		$this->assign('CurrentUrl', "Messagehdlist");
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
			$data['vu_money'] = I('post.vu_money', '0.00', 'floatval');
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
				
				$page = new \Common\Org\Page(M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->count(), 15);
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
			$data['vu_money'] = I('post.vu_money', '0.00', 'floatval');
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
				$page = new \Common\Org\Page(M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->count(), 15);
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
	
	//全民返利我的订单
	public function rebateOrder(){
		$where = $userids = array();
		
		if( $this->type!=1 ) {//门店登录
			$where['flo_sid']=$this->tsid;
		} else {
			$where['flo_jid']=$this->jid;
		}
		
		$where['flo_gtype']=1;
		$where['flo_ptype']=1;//全民返利不应该有线下支付
		if(I('get.flo_id')!='')$where['flo_id'] = array('like','%'.I('get.flo_id').'%');
		if(I('get.flo_dstatus')!='')$where['flo_dstatus'] = I('get.flo_dstatus');
		if(I('get.flo_pstatus')!='')$where['flo_pstatus'] = I('get.flo_pstatus');
		$page = new \Common\Org\Page(M('fl_order')->where($where)->count(), 2);
		$datalist = M('fl_order')->where($where)->order('flo_dstime desc')->limit($page->firstRow.','.$page->listRows)->select();
		foreach( $datalist as $_key=>$_value ) {
			$datalist[$_key]['ogoods'] = M('fl_gsnapshot')->where(array('flg_oid'=>$_value['flo_id']))->order('flg_id')->select();
			$userids[] = $_value['flo_uid'];
		}
	
		$shops = M('shop')->where(array('jid'=>$this->jid))->getField('sid,sname');
		if($userids)$users = M('fl_user')->where(array('flu_userid'=>array('in',$userids)))->getField('flu_userid,flu_nickname,flu_username,flu_phone');
		$this->assign('users', $users);
		$this->assign('shops', $shops);
		$this->assign('pages', $page->show());
		$this->assign('datalist', $datalist);
		$this->assign('odstatus', $this->odstatus(1));
		$this->assign('oostatus', $this->oostatus());
		$this->assign('type', $this->type);
		$this->assign('CurrentUrl', 'Salesmyorder');
		$this->display();
	}
	
	public function rebateOperation(){
		$type = I('post.type');
		$status = I('post.status', '', 'intval');
		$oid = I('post.o_id');
		if(!$oid)exit('0');
		$where = array();
		//$this->type == 1 ? $where['o_jid']=$this->jid : $where['o_sid']=$this->tsid;
		$where['flo_jid']=$this->jid;
		$where['flo_id']=$oid;
		$ps = M('fl_order')->where($where)->getField('flo_pstatus');
		
		$updatedata = array();
		$updatedata['flo_dstatus'] = $status;
		if($ps==1 && $status==5){
			$updatedata['flo_pstatus'] = 3;
		}
		$result = M('fl_order')->where($where)->save($updatedata);
		
		if($status == 3){
			//订单打印
			D('Print')->doFlPrint($oid,2);
		}
		exit($result?'1':'0');
	}

	//返利APP， 确认此订单完成
	public function sendOrder()
	{
		$oid=I('post.o_id') or die("0");
		
		$orderinfo = M('flOrder')->where( array("flo_jid"=>$this->jid, "flo_id"=>$oid) )->find();
		if( !is_array($orderinfo) || empty($orderinfo) ) exit("0");
		
		//订单完成前，必须是支付过的 和 正常状态（状态5是订单无效）
		if( $orderinfo['flo_pstatus']!=1 || $orderinfo['flo_dstatus']==5 ) exit("0");
		
		//开启事务
		$orderModel = M('flOrder');
		$orderModel->startTrans();
		
		//把订单状态修改成 已完成
		$status_01 = $orderModel->where( array("flo_jid"=>$this->jid, "flo_id"=>$oid) )->setField( array("flo_dstatus"=>4, "flo_isback"=>1) );
		
		//如果是子账号登录，则获取它的品牌MID,因为这个钱，要打到总账号里
		if( $this->type!=1 ) {
			$mid = M("merchant")->where( "jid=".$this->jid )->getField("mid");
		} else {
			$mid = $this->mid;
		}
		$status_02 = M("member")->where( "mid=".$mid )->setInc("money", ($orderinfo['flo_price']-$orderinfo['flo_backprice']));
		
		//调用分钱的类，进行分钱
		$commission = \Common\Org\Commission::translation()->insertInfo( $oid );
		$commission = json_decode( $commission, true );
		$status_03 = $commission['erron']==0 ? true : false;
		
		if( $status_01 && $status_02 && $status_03 ) {
			$orderModel->commit();
			//订单打印
			D('Print')->doFlPrint($oid,3);
			exit("1");
		} else {
			$orderModel->rollback(); exit("0");
		}
	}
	
	//返利预定
	public function rebateReserve(){
		$where =  $userids = array();
		$where['flo_gtype'] = 2;
		if( $this->type!=1 ) {//门店登录
			$where['flo_sid']=$this->tsid;
		} else {
			$where['flo_jid']=$this->jid;
		}
		$page = new \Common\Org\Page(M('fl_order')->where($where)->count(), 2);
		$datalist = M('fl_order')->where($where)->order('flo_dstime desc')->limit($page->firstRow.','.$page->listRows)->select();
		
		foreach( $datalist as $_key=>$_value ) {
			$ogoods = $datalist[$_key]['ogoods'] = M('fl_gsnapshot')->where(array('flg_oid'=>$_value['flo_id']))->order('flg_id')->select();
			$datalist[$_key]['sp_date'] = $ogoods['0']['flg_date'];
			$goods = array_column($ogoods,'flg_name','flg_gid');
			$datalist[$_key]['goodsname'] =  implode("、",$goods);
			$userids[] = $_value['flo_uid'];
		}
		
		$shops = M('shop')->where(array('jid'=>$this->jid))->getField('sid,sname');
		if($userids)$users = M('fl_user')->where(array('flu_userid'=>array('in',$userids)))->getField('flu_userid,flu_nickname,flu_username,flu_phone');
		$this->assign('users', $users);
		$this->assign('shops', $shops);
		$this->assign('pages', $page->show());
		$this->assign('datalist', $datalist);
		$this->assign('odstatus', $this->odstatus(2));
		$this->assign('oostatus', $this->oostatus());
		$this->assign('type', $this->type);
		$this->assign('CurrentUrl', 'Salesmyreserve');
		$this->display();
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
	
    //批量导入商品
    public function importgoods()
    {
    	if( IS_POST ) {
    		//如果存存SID ，说明是往分店里导入数据，这时要先判断，此SID是不是属于当前商家的
    		$sid = I('post.sid', 0, 'intval');
    		if( $sid > 0 ) {
    			$jid = M('shop')->where("sid=".$sid)->getField("jid");
    			if( $jid != $this->jid ) { E('导入的数据为非法数据'); exit; }
    		}

    		$type = I('post.type', 0, 'intval');
    		if( $type!=1 && $type!=2 ) exit("0");
    		$status = $type==1 ? $this->_import_goods( $_POST ) : $this->_import_subscribe( $_POST );
    		exit( $status ? "1" : "0" );
    	} else {
    		$this->display();
    	}
    }

    //下载导入模板
    public function downsmarty()
    {
    	\Org\Net\Http::download(APP_DIR."/Public/Data/import_type".I('get.type', 1, 'intval').".xls");
    }

    private function _import_goods( array $post=array() )
    {
    	if( !file_exists(APP_DIR.$post['pinfo']) ) return false;
    	vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$PHPExcel = \PHPExcel_IOFactory::load( APP_DIR.$post['pinfo'] ); 
		$maxRow = $PHPExcel->getSheet(0)->getHighestRow(); 
		
		$cidlist = array();
		foreach( M("class")->where(array("jid"=>$this->jid, "sid"=>intval( $post['sid'] ), "ctype"=>1, "status"=>1))->select() as $class )
		{
			$cidlist[ $class['cid'] ] = $class['cname'];
		}
		$arrayData = array();
		if( $maxRow > 0 ) {
			for($i=2, $j=0; $i<=$maxRow; $i++, $j++) {
				$gname = (string)$PHPExcel->getSheet(0)->getCellByColumnAndRow(0, $i)->getValue();
				if( !$gname ) continue;

				$gdescription = $PHPExcel->getSheet(0)->getCellByColumnAndRow(1, $i)->getValue();

				$classname = $PHPExcel->getSheet(0)->getCellByColumnAndRow(2, $i)->getValue();
				$classname = trim( $classname );
				if( !$cid=array_search( $classname, $cidlist ) ) {
					$cid = M("class")->add( array("cname"=>$classname, "jid"=>$this->jid, "sid"=>intval( $post['sid'] ), "corder"=>99, "ctype"=>1, "status"=>1) );
					$cidlist[ $cid ] = $classname;
				}

				$goprice = (float)$PHPExcel->getSheet(0)->getCellByColumnAndRow(3, $i)->getValue();
				$gdprice = (float)$PHPExcel->getSheet(0)->getCellByColumnAndRow(4, $i)->getValue();
				if( $gdprice > $goprice ) $gdprice = $goprice;

				$gstock = (int)$PHPExcel->getSheet(0)->getCellByColumnAndRow(5, $i)->getValue();
				$gvrebate = (int)$PHPExcel->getSheet(0)->getCellByColumnAndRow(7, $i)->getValue();
				if( $gvrebate > 60 ) $gvrebate = 60;
				if( $gvrebate < 0 ) $gvrebate = 0;

				$arrayData[$j] = array(
					'gname'			=> $gname,
					'gdescription'	=> $gdescription,
					'cid'			=> $cid,
					'goprice'		=> $goprice,
					'gdprice'		=> $gdprice,
					'gstock'		=> $gstock,
					'gvrebate'		=> $gvrebate,
					'sid'			=> intval( $post['sid'] )
				);
			}
		}
		if( !is_array($arrayData) || empty($arrayData) ) return false;
		return M("goods")->addAll($arrayData) ? true : false;
    }

    private function _import_subscribe( array $post=array() ) 
    {
    	if( !file_exists(APP_DIR.$post['pinfo']) ) return false;
    	vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$PHPExcel = \PHPExcel_IOFactory::load( APP_DIR.$post['pinfo'] ); 
		$maxRow = $PHPExcel->getSheet(0)->getHighestRow(); 
		
		$cidlist = array();
		foreach( M("class")->where(array("jid"=>$this->jid, "sid"=>intval( $post['sid'] ), "ctype"=>2, "status"=>1))->select() as $class )
		{
			$cidlist[ $class['cid'] ] = $class['cname'];
		}

		$arrayData = array();
		if( $maxRow > 0 ) {
			for($i=2, $j=0; $i<=$maxRow; $i++, $j++) {
				$gname = (string)$PHPExcel->getSheet(0)->getCellByColumnAndRow(0, $i)->getValue();
				if( !$gname ) continue;

				$gdescription = (string)$PHPExcel->getSheet(0)->getCellByColumnAndRow(1, $i)->getValue();

				$classname = $PHPExcel->getSheet(0)->getCellByColumnAndRow(2, $i)->getValue();
				$classname = trim( $classname );
				if( !$cid=array_search( $classname, $cidlist ) ) {
					$cid = M("class")->add( array("cname"=>$classname, "jid"=>$this->jid, "sid"=>intval( $post['sid'] ), "corder"=>99, "ctype"=>2, "status"=>1) );
					$cidlist[ $cid ] = $classname;
				}

				$goprice = (float)$PHPExcel->getSheet(0)->getCellByColumnAndRow(3, $i)->getValue();
				$gdprice = (float)$PHPExcel->getSheet(0)->getCellByColumnAndRow(4, $i)->getValue();
				if( $gdprice > $goprice ) $gdprice = $goprice;

				$arrayData[$j] = array(
					'gname'			=> $gname,
					'gdescription'	=> $gdescription,
					'cid'			=> $cid,
					'goprice'		=> $goprice,
					'gdprice'		=> $gdprice,
					'gtype'			=> 1,
					'sid'			=> intval( $post['sid'] )
				);
			}
		}
		
		if( !is_array($arrayData) || empty($arrayData) ) return false;
		return M("goods")->addAll($arrayData) ? true : false;
    }

    public function ImportFile() {
    	if(stripos($_SERVER['HTTP_REFERER'], 'dishuos.com') === false ) E('你无权进行此操作');

		$uploadROOT = realpath(THINK_PATH.'../Public/'); //上传地址的根目录
		if(urldecode(I('get.custompath'))) {
			$uploadSubPath = str_replace('|','/',I('get.custompath')); //上传地址的子目录
			$subName = null;
		} else {
			$uploadSubPath = '/Upload/';//上传地址的子目录
			$subName = array('date','Y-m-d');
		}

		$uploadPath =$uploadROOT.$uploadSubPath;
        if(!file_exists($uploadPath)) mkdirs($uploadPath,  0777);
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'subName'	=> $subName,
			'exts'		=> 'xls',
			'maxSize'	=> 2560000
		);

		$attachment = new \Think\Upload( $uploadConfig );
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        if($attachmentInfo && is_array($attachmentInfo)) {
            echo json_encode(array('error'=>0, 'url'=>'/Public'.$uploadSubPath.($subName?date('Y-m-d').'/':'').$attachmentInfo['savename'], 'imglink'=>I('post.imglink'), 'imgtext'=>I('post.imgtext'), 'savename'=>basename($attachmentInfo['savename'])));
        } else {
            echo json_encode(array('error'=>1, 'message'=>$attachment->getError()));
        }
    }

	################################################################
	#                     以下是对新的订单流程操作                      #
	################################################################
	
	//同意取消订单
	public function AjaxagreeCancelOrder()
	{
		header("Content-type: text/html; charset=utf-8");  
		//先把要取消的订单查询出来
		$orderid = I('post.oid', '');
		$orderinfo = M("order")->where( array('o_jid'=>$this->jid, 'o_id'=>$orderid) )->find();
		if( !is_array($orderinfo) || empty($orderinfo) ) exit('0');
		//查找订单是不是选择线上支付但未付款
		if($orderinfo['o_type']!=0 && $orderinfo['o_pstatus']==0){
			$close_nopay = array();
			$close_nopay['o_dstatus'] = 5;
			$close_nopay['o_close'] = 2;
			$close_nopay_status = M('order')->where(array('o_id'=>$orderid))->setField( $close_nopay );
			exit($close_nopay_status?'1':'0');
		}

		//判断此订单是不是关闭状态
		if( $orderinfo['o_close'] != 1 ) exit('0');
		
		$message_content = '';
		$field_array = array();
		
		//判断此订单不是支付过，如果是 线上支付并且支付给商家成功，将把钱退给用户
		if( $orderinfo['o_type']==1 && $orderinfo['o_pstatus']==1 ) {
			//把此订单添加到退单表里
			$data = array();
			$data['jid'] = $orderinfo['o_jid'];
			$data['o_id'] = $orderid;
			$data['pay_type'] = $orderinfo['o_type'];
			$data['order_type'] = '1';
			$data['cause'] = $orderinfo['o_close_reason'];
			$data['batch_num'] = 1;
			$data['pay_trade_no'] = M('payLog')->where(array('jid'=>$this->jid, 'oid'=>$orderid))->getField('pay_trade_no');
			$batch_no = D('RefundOrder')->createRefund($data);
			
			//如果是把钱打给了商家，则商家退款流程
			$status = $this->_RefundOrderInfo($batch_no);
			if( $status ) D('RefundOrder')->where( array("batch_no"=>$batch_no) )->setField( array('refund_date'=>date('Y-m-d H:i:s'), 'status'=>1) );
			$message_content = $status ? "你的订单取消成功，退款已经打入您的账号内，请查询" : "你的订单取消成功，退款失败，商家会重新操作"; 
			$field_array['o_pstatus'] = $status ? 2 : 3;//2 退款成功 3 待退款
			
		//如果订单的钱是支付到系统平台，将有系统平台通一退款
		} elseif( $orderinfo['o_type']==2 && $orderinfo['o_pstatus']==1 )	{
			$message_content = '你的订单取消成功，商家将在48小时内进行退款操作！';
			$field_array['o_pstatus'] = 3;
			
			//先判断商家账号的金额够不够退款的，如果够减少商家的账号金额
			$sj_money = M("member")->where("mid=".$this->mid)->getField('money');
			if( $sj_money < $orderinfo['o_price'] ) exit('0');
			M("member")->where("mid=".$this->mid)->setDec("money", $orderinfo['o_price']);
			
		//线下支付或未支付成功
		} else {
			$message_content = '你的订单取消成功！';
		}

		//关闭订单
		$field_array['o_dstatus'] = 5;
		$field_array['o_close'] = 2;
		$_close_status = M('order')->where(array('o_id'=>$orderid))->setField( $field_array );
		$this->_SetGoodsNumber( $orderid );

		//给用户发送消息
		$status=$this->_SendMessageToUser($orderinfo['o_uid'], $orderid, $this->jid, '取消订单', $message_content);
		exit( $_close_status ? "1" : "0" );
	}
	
	//提醒用户进行支付
	public function setMessageForUser() 
	{
		header("Content-type: text/html; charset=utf-8");  
		$orderid = I('post.o', '');
		$userid = I('post.u', 0, 'intval');
		$contentmsg = '亲~ 欢迎光临！您的订单('.$orderid.')我们已经收到咯，还需要您选择付款或者线下支付，我们才能受理哦~';
		if( !$orderid || !$userid ) exit("0");
		
		//给用户发送通知
		$status = $this->_SendMessageToUser($userid, $orderid, $this->jid, "订单提醒", $contentmsg);
		exit( $status ? "1" : "0" );
	}
	
	//接受订单
	public function setOrderStatus() {
		$order_id = I('post.o', '');
		
		//更新订单的状态
		$status = M("order")->where(array('o_id'=>$order_id))->setField("o_dstatus", 3);
		if( !$status ) exit("0");
		
		//订单打印
		D('Print')->doPrint($order_id,2);
		exit("1");
	}
	
	//拒绝订单
	public function refuseOrderStatus()
	{
		header("Content-type: text/html; charset=utf-8");  
		$orderid = I('post.oid', '');
		$userid = I('post.uid', 0, 'intval');
		$content = I('post.reason', '');
		//if(!$orderid || !$userid || !$content ) exit('0');
		
		$orderInfo = M("order")->where(array('o_id'=>$orderid))->find();

		//判断订单的支付状态(如果支付成功，则退款)
		$message_content = '';
		$field_array = array();
		
		//判断此订单是不是支付过，如果是 线上支付并且支付给商家成功，将把钱退给用户
		if( $orderInfo['o_type']==1 && $orderInfo['o_pstatus']==1 && $orderInfo['o_price']>0 ) {
			//把此订单添加到退单表里
			$data = array();
			$data['jid'] = $orderInfo['o_jid'];
			$data['o_id'] = $orderid;
			$data['pay_type'] = $orderInfo['o_type'];
			$data['order_type'] = '1';
			$data['cause'] = $orderInfo['o_close_reason'];
			$data['batch_num'] = 1;
			$data['pay_trade_no'] = M('payLog')->where(array('jid'=>$this->jid, 'oid'=>$orderid))->getField('pay_trade_no');
			$batch_no = D('RefundOrder')->createRefund($data);
			
			//如果是把钱打给了商家，则商家退款流程
			$_status = $this->_RefundOrderInfo($batch_no);
			//if( $_status ) D('RefundOrder')->where( array("batch_no"=>$batch_no) )->setField( array('refund_date'=>date('Y-m-d H:i:s'), 'status'=>1) );
			$message_content = $_status ? "您的订单因{$content}已取消，退款已经打入您的账号内，请查询" : "您的订单因{$content}已取消，退款失败，商家会重新操作"; 
			//如果订单的钱是支付到系统平台，将有系统平台通一退款
		} elseif( $orderInfo['o_type']==2 && $orderInfo['o_pstatus']==1 && $orderInfo['o_price']>0 ) {
			$message_content = "您的订单因{$content}已取消，商家将在48小时内进行退款操作";
			$field_array['o_pstatus'] = 3;
			
			//先判断商家账号的金额够不够退款的，如果够减少商家的账号金额
			$sj_money = M("member")->where("mid=".$this->mid)->getField('money');
			if( $sj_money < $orderInfo['o_price'] ) exit('0');
			M("member")->where("mid=".$this->mid)->setDec("money", $orderInfo['o_price']);
			//线下支付或未支付成功
		} else {
			$message_content = "您的订单因{$content}已取消";
		}
		
		//关闭订单
		$field_array['o_dstatus'] = 5;
		$field_array['o_close'] = 2;
		$_update_status = M('order')->where(array('o_id'=>$orderid))->setField( $field_array );
		$this->_SetGoodsNumber( $orderid );

		//发送通知给用户
		$status = $this->_SendMessageToUser($userid, $orderid, $orderInfo['o_jid'], "拒绝订单", $message_content);
		exit( $_update_status !== false ? "1" : "0" );
	}
	
	//拒绝取消
	public function refuseCancleOrder()
	{
		$orderid = I('post.o', '');
		$userid = I('post.u', 0, 'intval');
		$sid = I('post.s', 0, 'intval');
		if( !$orderid || !$userid || !$sid ) exit('0');
		M('order')->where(array('o_id'=>$orderid))->save(array('o_close'=>3));
		
		$mservetel = M('shop')->where('sid='.$sid)->getField('mservetel');
		$contentmsg = "不好意思！您的订单已接受，欢迎享用~ 或者拨打电话：{$mservetel}，提醒商户取消";

		//给用户发送通知
		$status = $this->_SendMessageToUser($userid, $orderid, $this->jid, "订单提醒", $contentmsg);
		exit( $status ? "1" : "0" );	
	}
	
	//确认订单
	public function confirmOrderStatus() 
	{
		$orderid = I('post.o', '');
		if( !$orderid ) exit('0');	
		
		$OrderModel = M('order');
		$OrderModel->startTrans();
		
		$orderInfo = $OrderModel->where(array('o_id'=>$orderid))->find();
		
		$field_array['o_dstatus'] = 4;
		if( $orderInfo['o_type']==0 )//如果是线下支付，则把支付状态修改成 成功
		{
			$field_array['o_pstime'] = date('Y-m-d H:i:s');
			$field_array['o_pstatus'] = 1;
		}
		$status = $OrderModel->where(array('o_id'=>$orderid))->setField( $field_array );
		
		//如果是线上支付，并且把钱支付给了系统平台, 把钱打给商家设置的账号里
		/*
		if( is_array($orderInfo) && !empty($orderInfo) && $orderInfo['o_type']==2 && $orderInfo['o_pstatus']==1) 
		{
			//把钱放到商家账号里
			$_member_id = \Common\Org\Cookie::get('mid');
			M("member")->where("mid=".$_member_id)->setInc('money', $orderInfo['o_price']);
		}
		*/
		
		if( is_array($orderInfo) && !empty($orderInfo) && $status ) {
			$OrderModel->commit();
			//订单打印
			D('Print')->doPrint($orderid,3);
			exit('1');	
		} else {
			$OrderModel->rollback(); exit('0');	
		}
	}
	
	//给用户发送消息
	private function _SendMessageToUser($userid, $orderid, $jid, $title, $content)
	{
		
		$userInfo = M("fl_user")->where("flu_userid=".$userid)->find();
		$u_clientid = $userInfo['flu_clientid'];
		
		//if( !$u_clientid ) {//如果不存在，则用短信通知
			SENDMSG:;
			$user_phone = M("order")->where(array("o_id"=>$orderid))->getField("o_phone");
			if( !$user_phone ) $user_phone = $userInfo['flu_phone'];
			if( $user_phone ) {
				//获取商家的名称
				//$jia_name = \Common\Org\Cookie::get('mnickname');
				//$content = $content.'【'.($jia_name ? $jia_name : '阿宅订').'】';
				$content = $content.' 订单号:' . $orderid ;
				return sendmsg( $user_phone,  $content ) ? true : false;
			}
			return false;
		//}
		
		//获取商家的appid
		$appinfo = M('merchantApp')->where("jid=".$jid)->field("gt_appid,gt_appkey,gt_appsecret,gt_mastersecret")->find();
		if( !is_array($appinfo) || empty($appinfo) ) goto SENDMSG;
		
		$info = array();
		$info['title'] = $title;
		$info['time'] = date('Y-m-d H:i:s');
		$info['content'] = $content;
		$info['pid'] = 0;
		
		$args = array(
			'transmissionContent'	=> JSON($info),
		);
		$mesg = array(
			'offlineExpireTime'		=> 7200,
			'netWorkType'			=> 1
		);
		$status = \Common\Org\IGPushMsg::getIGPushMsg(true, $appinfo)->pushMessageToCid($u_clientid, 4, $args, $mesg);
		
		if(!$status) { goto SENDMSG; }
		return $status ? true : false;
	}
	
	//退款操作
	private function _RefundOrderInfo( $batchno )
	{
		header("Content-type: text/html; charset=utf-8");
		$RefundOrderModel = new \Common\Model\RefundOrderModel();

		$refundorder = $RefundOrderModel->where(array('batch_no'=>$batchno))->find();
		if( !is_array($refundorder) || empty($refundorder) ) return false;
		
		if( $refundorder['status'] != 1 && $refundorder['order_type']=='1') { //如果是普通订单，使用此流程
			$PayLog = new \Common\Model\PayLogModel();
			$refundData = array('o_id'=>$refundorder['o_id'],'money'=>$refundorder['money'],'cause'=>$refundorder['cause']);
			
			$parameter = $PayLog->promptlyRefund($refundData);
			$refund = new \Org\Util\pay\alipayrefund($PayLog->alipay_config);
			$s = $refund->buildRequestForm($parameter);
			return $parameter ? true : false;
		} 
		
		return false;
	}
	
	//取消订单，把库存给增加上
	private function _SetGoodsNumber( $orderid )
	{
		foreach( M("goodsSnapshot")->field("sp_gid,sp_number")->where( array("sp_oid"=>$orderid) )->select() as $g )
		{
			$goodsinfo = M("goods")->where("gid=".$g['sp_gid'])->find();
			if( is_array($goodsinfo) && !empty($goodsinfo) && $goodsinfo['gstock']!=-1 )
			{
				M("goods")->where("gid=".$g['sp_gid'])->setInc("gstock", $g['sp_number']);
			}
		}
	}

	//统计订单消息
	public function countOrder($type=0){
		$where = $this->type == 1 ? array('o_jid'=>$this->jid) : array('o_sid'=>$this->tsid); 
		$orderCount=M('order')->where(array_merge($where, array('o_dstatus'=>1,'o_gtype'=>'Choose') ))->count();
		if($type==1)return $orderCount;
		
		$corder = I('post.corder');
		$r = array(
			"new_order" => $orderCount-$corder,
			"close_order"     => M('order')->where(array_merge($where, array('o_close'=>1,'o_gtype'=>'Choose') ))->count()
		);
		die(json_encode($r));
	}
	//统计预定消息
	public function countReserve(){
		$where = $this->type == 1 ? array('o_jid'=>$this->jid) : array('o_sid'=>$this->tsid); 
		$reserveCount=M('order')->where(array_merge($where, array('o_dstatus'=>1,'o_gtype'=>'Seat') ))->count();
		die($reserveCount);
	}


	
}