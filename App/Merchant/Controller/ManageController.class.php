<?php
namespace Merchant\Controller;

class ManageController extends MerchantController {
	//广告管理
	public function advert() {
		$sid  = I('sid', 0, 'intval');
		$where['jid'] = $this->jid;
		$where['sid'] = $sid;
		if( isset($_POST['keywords']) && !empty($_POST['keywords']) ) $where['btitle'] = array('like', "%{$_POST['keywords']}%");
		$page = new \Common\Org\Page(M('banner')->where($where)->count(), 12);
		$this->assign('bdlist', M('banner')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		$this->assign('sid', $sid);
		if (I('dtype', 1) == 2) {
			$this->assign('sid', $sid);
			$this->display('advert2');exit;
		}
		$this->display();	
	}
	
	//添加广告
	public function adinse() {
		$sid   = I('sid', 0, 'intval');
		$dtype = I('dtype', 1, 'intval');
		if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			$_POST['info']['jid'] = $this->jid;
			if( M('banner')->add( $_POST['info'] ) ) {
				$this->success('添加成功', U('/Manage/advert', array('dtype'=>$dtype, 'sid'=>$_POST['info']['sid']), true));	
			} else { $this->error('添加失败'); }		
		} else {
			if ($dtype == 2) {
				$this->assign('sid', $sid);
				$this->assign('dtype', $dtype);
				$this->display('adinse2');exit;
			}
			$this->assign('sid', $sid);
			$this->display();	
		}
	}
	
	//删除广告
	public function adupde() {
		$sid   = I('sid', 0, 'intval');
		$banner = M('banner')->where(array('bid'=>I('get.bid'), 'btype'=>'3'))->find();
		if( !$banner || $banner['jid'] != $this->jid) E('你无权查看当前页面');

		if( M('banner')->where(array('bid'=>I('get.bid')))->delete() ) {
			$this->success('删除成功', U('/Manage/advert', array('sid'=>$banner['sid']), true));		
		} else { $this->error('操作失败'); }
	}
	
	//修改广告
	public function adedit() {
		$sid   = I('sid', 0, 'intval');
		$dtype = I('dtype', 1, 'intval');
		if( IS_POST ) {
			$banner = M('banner')->where(array('bid'=>intval($_POST['info']['bid']), 'btype'=>'3'))->find();
			if( !$banner || $banner['jid'] != $this->jid) E('你无权查看当前页面');

			if( M('banner')->save( $_POST['info'] ) ) {
				$this->success('修改成功', U('/Manage/advert', array('dtype'=>$dtype, 'sid'=>$sid), true));	
			} else { $this->error('修改失败'); }		

		} else {
			$banner = M('banner')->where(array('bid'=>I('get.bid'), 'btype'=>'3'))->find();
			if( !$banner || $banner['jid'] != $this->jid) E('你无权查看当前页面');
			$this->assign('banner', $banner);
			if ($dtype == 2) {
				$this->assign('sid', $sid);
				$this->assign('dtype', $dtype);
				$this->display('adedit2');exit;
			}
			$this->assign('sid', $banner['sid']);
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
		//if( $this->type != 1 ) E('你无权查看当前页面');
		$merchant=M('merchant')->where(array('jid'=>$this->jid))->find();
		$this->assign('merchant',$merchant);
		//行业列表
		//$vocation = M('vocation')->where(array('v_pid'=>0))->select();
		//$this->assign('vocation',$vocation);
		if( IS_POST ) {
			$t_sign = I('post.t_sign', '');
			$result = M('merchant')->where(array('jid'=>$this->jid))->setField('theme',$t_sign);
			if($result)
				$this->ajaxReturn(array('status'=>1,'msg'=>'修改成功'));
			else
				$this->ajaxReturn(array('status'=>0,'msg'=>'修改失败'));
		}
		//$v_id = I('v_id',0);
		$t_price = I('t_price',0);
		if($t_price == 1){
			$where = " t_status=1 AND t_price = 0 ";
		}elseif($t_price == 2){
			$where = " t_status=1 AND t_price > 0 ";
		}else{
			$where = " t_status=1 ";
		}
		$this->assign('t_price',$t_price);

		//查询店铺
		$shops = D('auth')->getAuthShops($this->mid);
		$this->assign('shop', $shops);
		
		$page = new \Common\Org\Page(M('Theme')->where($where)->count(), 6);
		$themes = M('Theme')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('pages', $page->show());
		$this->assign('themes',$themes);
		$this->display();
	}
	
	//设备列表之前先判断，设备是不是启动状态
	public function _before_device() {
		$this->type == 1 ? $where['rmerchant']=$this->jid : $where['rshop']=$this->tsid;
		$deviceList = $deviceList_array = array();
		$deviceList = M('Router')->where($where)->select();
		foreach($deviceList as $d) if($d['rshop']) $deviceList_array[] = $d;
		\Common\Org\PInterface::setstatus( $deviceList_array );
	}
	
	//设备管理
	public function device(){
		$where = array();
		$this->type == 1 ? $where['r.rmerchant']=$this->jid : $where['r.rshop']=$this->tsid;
		if( isset($_GET['status']) && $_GET['status'] != '' ) {
			$where['r.rstatus'] = I('get.status', 0, 'intval');	
		}
		if( I('get.sq', '') ) { 
			$rcode = trim(I('get.sq', '')); $where['r.rcode'] = array('like', "%{$rcode}%");  
		}
		if( I('get.mc', '') ) { 
			$rname = trim(I('get.mc', '')); $where['r.rname'] = array('like', "%{$rname}%");  
		}
		$page = new \Common\Org\Page(M('Router')->alias('AS r')->where($where)->count(), 10);
		$deviceList = M('Router')->alias('AS r')->join('__SHOP__ AS s ON r.rshop=s.sid', 'left')->field('r.*,s.sname')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('pages', $page->show());
		$this->assign('deviceList',$deviceList);
		
		//查询所有的子门店
		$shop_list = '';
		foreach( M('shop')->where("jid=".$this->jid." and status='1'")->field('sid,sname')->select() as $s ) {
			$shop_list .= '<option value="'.$s['sid'].'">'.$s['sname'].'</option>';
		}
		
		$this->assign('shoplist', $shop_list);
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
			$default_ymlist = array('qq.com', 'weibo.com', 'login.sina.com.cn', 'dishuos.com', 'qzone.qq.com');
			$ymlist_array = explode(";", I('post.ym', ''));
			$new_ymlist = array_filter( array_unique( array_merge($default_ymlist, $ymlist_array) ) );
			$ymlist_string = implode(";", $new_ymlist);
			
			$ssido = I('post.ssido');
			$ssid = $ssido.'|gb2312;'.$ssido."|utf-8";
			$rcode = I('post.rcode');
			
			if( $rcode && $ssido ) {
				$ymbool = \Common\Org\PInterface::setymlist( $rcode, $ymlist_string );
				$ssbool = \Common\Org\PInterface::setssid( $rcode, iconv('UTF-8', 'gb2312', $ssid) );
				
				$field_array = array(
						'rwebtype'			=> I('post.rwebtype', 1, 'intval'),
						'norsysver'			=> I('post.norsysver', '')
				);
				if( $ssbool && $ymbool && M('Router')->where("rid=".I('post.rid'))->setField( $field_array ) !== false) {
					$this->success('添加成功', U('/Manage/device', '', true)); exit;
				}
			} 
			$this->error('添加失败' );	
		} else {
			$deviceInfo = M('Router')->where(array('rid'=>I('get.rid')))->find();
			if( !is_array($deviceInfo) || empty($deviceInfo) ) E('你无权查看当前页面');
			$this->assign('deviceInfo', $deviceInfo);

			$ymlist = \Common\Org\PInterface::getymlist( $deviceInfo['rcode'] );
			if( $ymlist ) {
				$ymlist_array = explode(',', $ymlist);
				$ymlist_array = array_filter( explode(";", $ymlist_array[2]), function($v) {
					$default_ymlist = array('qq.com', 'weibo.com', 'login.sina.com.cn', 'dishuos.com', 'qzone.qq.com');
					return in_array($v, $default_ymlist) || !$v ? false : true;
				} );
				
				$this->assign('ymlist', implode(";", $ymlist_array));
			}

			$ssid = \Common\Org\PInterface::getssid( $deviceInfo['rcode'] );
			if( $ssid ) {
				$ssid = iconv('gb2312', 'UTF-8', $ssid);
				$ssid = trim(substr($ssid, 0, strpos($ssid, "|")));
				$ssid = explode(',', $ssid);
				$this->assign('ssid', $ssid[2] ? $ssid[2] : '');
			}
			$this->display();
		}
	}	
	
	//把设备分到分店里
	public function setShop()
	{
		$rid = I('post.rid');
		$sid = I('post.sid');
		if( !$sid || !$rid ) exit('0');
		
		//先把这些设备的绑定取消
		M('router')->where(array("rid"=>$rid))->save( array('rshop'=>0, 'rstatus'=>0) );
		
		if( M('router')->where(array("rid"=>$rid))->setField('rshop', $sid) !== false ) {
			exit('1');
		} else {
			exit('0');
		}
	}
	
	public function getSS(){
		$where = '';
		$sstime = I('sstime',0);
		$statime = I('statime',0);
		$endtime = I('endtime',0);
		$sid = I('sid',0);
		if($sstime > 0){
			if($sstime == 1){
				$where .= " and o_dstime between '" . date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d"),date("Y"))) . "' and '" . date("Y-m-d H:i:s",mktime(23, 59, 59,date("m"),date("d"),date("Y")))."' ";
			}elseif($sstime == 2){
				$where .= " and o_dstime between '" . date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y"))) . "' and '" . date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y"))) ."' ";
			}elseif($sstime == 3){
				$season = ceil((date('n'))/3);
				$where .= " and o_dstime between '" . date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y'))) . "' and '" . date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y')))."' ";
			}elseif($sstime == 4){
				$where .= " and o_dstime between '" . date("Y-m-d H:i:s",mktime(0, 0 , 0,1,1,date("Y"))) . "' and '" . date("Y-m-d H:i:s",mktime(23, 59, 59,12,31,date("Y")))."' ";
			}
		}else{
			if( $statime ) {
				$where .= " and o_dstime >= '" . date('Y-m-d H:i:s', strtotime($statime))."' ";
			}
			if( $endtime ) {
				$where .= " and o_dstime <= '" . date('Y-m-d 23:59:59', strtotime($endtime))."' ";
			}
		}
		
		if($this->role != 1 && $sid > 0){
			$sinfo = M('shop')->where(array('sid'=>$sid))->find();
			if($this->shift == 2){
				if($sinfo['bb_stime'] != '' && $sinfo['bb_etime'] != ''){
					$where .= " and DATE_FORMAT(o_dstime,'%H.%i') between ".$sinfo['bb_stime'].' and '.$sinfo['bb_etime'];
				}
			}elseif($this->shift == 3){
				if($sinfo['wb_stime'] != '' && $sinfo['wb_etime'] != ''){
					$where .= " and DATE_FORMAT(o_dstime,'%H.%i') between ".$sinfo['wb_stime'].' and '.$sinfo['wb_etime'];
				}
			}
		}
		return $where;
	}
	
	//财务管理
	public function _before_finance()
	{
		
		isset($_GET['type']) || $_GET['type']=0;
		if( intval($_GET['type']) != 4 )
		{
			$member = M('member')->where(array('mid'=>$this->mid))->find();
			$this->assign('member', $member);
			
			$ww = $this->getSS();
			$wheresid =  I('get.sid')?' AND o_sid = '.I('get.sid'):null;
			$whereflsid =  I('get.sid')?' AND flo_sid = '.I('get.sid'):null;

			//线下总收入
			$this->assign('count_a', M('order')->where("o_type=0 AND o_dstatus=4 AND o_pstatus in(1,3) AND o_jid={$this->jid}".$wheresid.$ww)->sum('o_price'));
			$this->assign('count_b', M('order')->where("o_type=1 AND o_dstatus=4 AND o_pstatus in(1,3) AND o_jid={$this->jid}".$wheresid.$ww)->sum('o_price'));
			$this->assign('count_c', M('order')->where("o_type=2 AND o_dstatus=4 AND o_pstatus in(1,3) AND o_jid={$this->jid}".$wheresid.$ww)->sum('o_price'));
			$count_d = M('flOrder')->where("flo_ptype=1 AND flo_dstatus=4 AND flo_pstatus in(1,3) AND flo_jid={$this->jid}".$whereflsid)->sum('flo_price')-M('flOrder')->where("flo_ptype=1 AND flo_dstatus=4 AND flo_pstatus in(1,3) AND flo_jid={$this->jid}".$whereflsid)->sum('flo_backprice');//支付金额减去返利金额
			$this->assign('count_d',$count_d);
		}
	}

	public function finance() 
	{
		$sid = I('sid',0);
		$this->assign('sid',$sid);
		
		$shops = D('auth')->getAuthShops($this->mid);
		$this->assign('shops', $shops);
		$sp_oid = $resource_gdprice = $resource_gdprice_array = array();
		$ww = $this->getSS();
		
		switch( intval($_GET['type']) )
		{
			case 0:
			case 1:
			case 2://线上订单明细
				$wheresid =  I('get.sid')?' AND o_sid = '.I('get.sid'):null;
				$where = "o_type=".intval($_GET['type'])." AND o_dstatus=4 AND o_pstatus in(1,3) AND o_jid={$this->jid}".$wheresid.$ww;
				$page = new \Common\Org\Page(M('order')->where( $where )->count(), 12);
				$resource = M('order')->where( $where )->limit($page->firstRow.','.$page->listRows)->order('o_dstime desc')->select();
				
				if( is_array($resource) && !empty($resource) )
				{
					foreach( $resource as $r ) $sp_oid[] = $r['o_id'];
					$resource_gdprice = M('goodsSnapshot')->where( array("sp_oid"=>array("in", $sp_oid),'sp_status'=>1) )->group("sp_oid")->field('sp_oid,sum(sp_goprice*sp_number) as goprice')->select();
					
					foreach( $resource_gdprice as $gd ) $resource_gdprice_array[$gd['sp_oid']] = $gd;
				}
			break;

			case 3://全民返利明细
				$whereflsid =  I('get.sid')?' AND flo_sid = '.I('get.sid'):null;
				$where = "flo_ptype=1 AND flo_dstatus=4 AND flo_pstatus in(1,3) AND flo_jid={$this->jid}".$whereflsid;
				$page = new \Common\Org\Page(M('flOrder')->where( $where )->count(), 12);
				$resource = M('flOrder')->alias('AS o')->join('__FL_USER__ AS u ON o.flo_uid=u.flu_userid', 'left')->where( $where )->field("flo_id as o_id,flo_sid,flo_dstime as o_dstime,flo_price as o_price,flu_nickname as o_name,flu_phone as o_phone,flo_backprice")->limit($page->firstRow.','.$page->listRows)->order('flo_dstime desc')->select();
				if( is_array($resource) && !empty($resource) )
				{
					foreach( $resource as $r ) $sp_oid[] = $r['o_id'];
					$resource_gdprice = M('flGsnapshot')->where( array("flg_oid"=>array("in", $sp_oid)) )->group("flg_oid")->field('flg_oid,sum(flg_goprice*sp_number) as goprice')->select();
					
					foreach( $resource_gdprice as $gd ) $resource_gdprice_array[$gd['flg_oid']] = $gd;
				}
			break;

			case 4://支出明细
				$page = new \Common\Org\Page(M('bookkeeping')->alias('AS b')->join('__MEMBER__ AS m ON b.bmid=m.mid', 'left')->where(array('m.mid'=>$this->mid))->count(), 12);
				$resource = M('bookkeeping')->alias('AS b')->join('__MEMBER__ AS m ON b.bmid=m.mid', 'left')->where(array('m.mid'=>$this->mid))->field('b.*,m.msurname')->limit($page->firstRow.','.$page->listRows)->order('bstime desc')->select();
			break;
		}

		$this->assign('resource_gdprice', $resource_gdprice_array);
		$this->assign('pages', $page->show());
		$this->assign('resource', $resource);
	}

	public function _after_finance()
	{
		if(intval($_GET['type'])==4){
			$this->assign('CurrentUrl', 'Managefinance4');
		}
		$this->display( intval($_GET['type'])==4 ? 'Manage_expend' : 'Manage_finance' );
	}

	//下载订单
	public function download()
	{	$shopwhere = $this->jid?array('jid'=>$this->jid, "status"=>'1'):array('sid'=>$this->sid, "status"=>'1');
		$shops = D('Shop')->where($shopwhere)->cache(true)->getField('sid,sname');
		$this->assign('shops', $shops);

		if( IS_GET )
		{
			$this->display(); exit;
		}
		$type = I('post.type', 0, 'intval');

		//收入明细
		$resource = array();
		switch( $type )
		{
			case 0://线上订单明细
				$where = "o_type=".intval($type)." AND o_dstatus=4 AND o_pstatus in(1,3) AND o_close=0 AND o_jid={$this->jid}";
				if( isset($_POST['stime']) && !empty($_POST['stime']) )
				{
					$where .= " AND o_dstime >= '".$_POST['stime']."'";
				}
				if( isset($_POST['etime']) && !empty($_POST['etime']) )
				{
					$where .= " AND o_dstime <= '".$_POST['etime']."'";
				}
				if( $_POST['sid'])
				{
					$where .= " AND o_sid = '".$_POST['sid']."'";
				}
				$resource = M('order')->where( $where )->select();
			break;
			case 1:
				$where = "o_type=".intval($type)." AND o_dstatus=4 AND o_pstatus in(1,3) AND o_close=0 AND o_jid={$this->jid}";
				if( isset($_POST['stime']) && !empty($_POST['stime']) )
				{
					$where .= " AND o_dstime >= '".$_POST['stime']."'";
				}
				if( isset($_POST['etime']) && !empty($_POST['etime']) )
				{
					$where .= " AND o_dstime <= '".$_POST['etime']."'";
				}
				if( $_POST['sid'])
				{
					$where .= " AND o_sid = '".$_POST['sid']."'";
				}
				$resource = M('order')->where( $where )->select();
			case 2:
				$where = "o_type=".intval($type)." AND o_dstatus=4 AND o_pstatus in(1,3) AND o_close=0 AND o_jid={$this->jid}";
				if( isset($_POST['stime']) && !empty($_POST['stime']) )
				{
					$where .= " AND o_dstime >= '".$_POST['stime']."'";
				}
				if( isset($_POST['etime']) && !empty($_POST['etime']) )
				{
					$where .= " AND o_dstime <= '".$_POST['etime']."'";
				}
				if( $_POST['sid'])
				{
					$where .= " AND o_sid = '".$_POST['sid']."'";
				}
				$resource = M('order')->where( $where )->select();
			break;

			case 3://全民返利明细
				$where = "flo_ptype=1 AND flo_dstatus=4 AND flo_pstatus in(1,3) AND flo_jid={$this->jid}";
				if( isset($_POST['stime']) && !empty($_POST['stime']) )
				{
					$where .= " AND flo_dstime >= '".$_POST['stime']."'";
				}
				if( isset($_POST['etime']) && !empty($_POST['etime']) )
				{
					$where .= " AND flo_dstime <= '".$_POST['etime']."'";
				}
				if( $_POST['sid'])
				{
					$where .= " AND flo_sid = '".$_POST['sid']."'";
				}
				$resource = M('flOrder')->alias('AS o')->join('__FL_USER__ AS u ON o.flo_uid=u.flu_userid', 'left')->where( $where )->field("flo_id as o_id,flo_dstime as o_dstime,flo_price as o_price,flu_nickname as o_name,flu_phone as o_phone")->select();
			break;
		}
		//导入 PhpExcel
		vendor('PHPExcel.IOFactory', VENDOR_PATH.'PhpExcel');
		$objPHPExcel = new \PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document");
		
		$char_column_list = array('A', 'B', 'C', 'D', 'E', 'F');

		//设计高宽
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(22);
		foreach($char_column_list as $char)
			$objPHPExcel->getActiveSheet()->getColumnDimension($char)->setWidth('30');
			
		//设置标题
		$objPHPExcel->getActiveSheet()->mergeCells( $type==3 ? 'A1:F1' : 'A1:E1');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '订单收入明细');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			
		$objPHPExcel->getActiveSheet()->getStyle($type==3 ? 'A2:F2' : 'A2:E2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle($type==3 ? 'A2:F2' : 'A2:E2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A2', '下单时间')
					->setCellValue('B2', '下单人')
					->setCellValue('C2', '手机号')
					->setCellValue('D2', '订单价格');
		if( $type==3 ) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2', '返利金额')->setCellValue('F2', '订单号');
		} else {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2', '订单号');
		}
		
		if( is_array($resource) && !empty($resource) )
		{
			$i = 3;
			foreach($resource as $c):
				$objPHPExcel->getActiveSheet()->getStyle($type==3 ? "A{$i}:F{$i}" : "A{$i}:F{$i}")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle($type==3 ? "A{$i}:F{$i}" : "A{$i}:F{$i}")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$i, $c['o_dstime'])
							->setCellValue('B'.$i, $c['o_name'])
							->setCellValue('C'.$i, $c['o_phone'])
							->setCellValue('D'.$i, $c['o_price']);
				if( $type==3 ) {
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$i, $c['flo_backprice'])->setCellValue('F'.$i, " ".$c['o_id']);
				} else {
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$i, " ".$c['o_id']);
				}
				$i ++;
			endforeach;
		}
		$objPHPExcel->getActiveSheet()->setTitle('订单收入明细');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$shops[$_POST['sid']].'订单收入明细.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	//提现申请
	public function carryapply(){
		$Bookkeeping = D('Common/Bookkeeping');
		$mid = M('merchant')->where(array('jid'=>$this->jid))->getField('mid');
		$member = M('member')->where(array('mid'=>$mid))->find();
		

		if(I('get.action')=='calculate'){
			$bmention = I('get.bmention');
			$data = $Bookkeeping->calculate($bmention);
			$this->ajaxReturn(array('status'=>1,'data'=>$data));
		}
		if( IS_POST ) {
			if($member['money'] < 100)$this->ajaxReturn(array('status'=>0,'msg'=>'您的账号里可提现金额小于100元'));
			$bmention = I('post.bmention');
			if($member['money'] < $bmention)$this->ajaxReturn(array('status'=>0,'msg'=>'提现申请金额不能超过账户金额！'));
			$data = array();
			$data = $Bookkeeping->calculate($bmention);
			$data['bmention'] = $bmention;
			$data['bls'] = $bls = date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
			$data['bmid'] = $this->mid;
			$data['bstime'] = date('Y-m-d H:i:s');
			$data['bip'] = get_client_ip();
			$data['bdzh'] = $member['mbdzh'];
			$data['bname'] = $member['msurname'];
			$data['bresidue'] = $member['money']-$bmention;
			$data['butype'] = 0;
			$result = $Bookkeeping->data($data)->add();
			if($result){
				M('member')->where(array('mid'=>$this->mid))->setDec('money',$bmention);
				$this->ajaxReturn(array('status'=>1,'msg'=>'提现申请成功！'));
			}else $this->ajaxReturn(array('status'=>0,'msg'=>'提现申请失败！'));
		} else {
			$this->assign('CurrentUrl', 'Managecarryapply2');
			$this->assign('member', $member);
			$this->display();	
		}
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



	// 模板管理
	public function mobileTheme(){
		$shops = D('auth')->getAuthShops($this->mid);
		$sid = I('get.sid', key($shops), 'intval');
		cookie('shop'.$this->jid, $sid);
		
		//查询配置
		$setting_file_path = $this->path.'setting.conf';
		$setting_array = array();
		if( file_exists( $setting_file_path ) )
		{
			$setting_array = unserialize( file_get_contents( $setting_file_path ) );
		}
		$this->assign('setting', $setting_array);
		//二维码
		//$mtapp = M('merchantApp')->where(array('jid'=>$this->jid))->find();
		//$this->assign('mtapp', $mtapp);
		//$this->assign('qrcodefile',$this->createQrCode($mtapp));
		//if( $this->type != 1 ) E('你无权查看当前页面');
		$merchant=M('merchant')->where(array('jid'=>$this->jid))->find();
		$this->assign('merchant',$merchant);
		//行业列表
		$vocation = M('vocation')->where(array('v_pid'=>0))->select();
		$this->assign('vocation',$vocation);
		if( IS_POST ) {
			$t_sign = I('post.t_sign', '');
			$result = M('merchant')->where(array('jid'=>$this->jid))->setField('theme',$t_sign);
			if($result)
				$this->ajaxReturn(array('status'=>1,'msg'=>'修改成功'));
			else
				$this->ajaxReturn(array('status'=>0,'msg'=>'修改失败'));
		}
		//拼接查询条件
		$t_price = I('t_price',0);
		if($t_price == 1){
			$where = " t_status=1 AND t_price = 0 ";
		}elseif($t_price == 2){
			$where = " t_status=1 AND t_price > 0 ";
		}else{
			$where = " t_status=1 ";
		}

		$v_id  = I('v_id',0);
		if ($v_id != 0) {
			$where .= " AND t_vid like '%$v_id%' ";
		}
		$page   = new \Common\Org\Page(M('Theme')->where($where)->count(), 6);
		$themes = M('Theme')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		//功能列表		
		$module = M('module');
		$func   = $module->where(array('module_status'=>1))->select();
		$mfunc  = M('merchant_module')->where(array('jid'=>$this->jid))->field('module_sign')->select();
		$tmfunc = array();
		foreach($mfunc as $k=>$v){
			$tmfunc[] = $v['module_sign'];
		}
		//栏目列表
		$category = M('category')->where(array('sid'=>$sid, 'jid'=>$this->jid))->order('corder')->select();
		for ($i=0; $i <count($category) ; $i++) { 
			for ($j=0; $j <count($func) ; $j++) { 
				if ($category[$i]['model'] == $func[$j]['module_sign']){
					// if ($func[$j]['module_sign'] == 'goods') {
					// 	$category[$i]['url'] = '/Design/goods/sid/'.$sid.'/cid/'.$category[$i]['id'].'.html';
					// }else{
						$category[$i]['url'] = $func[$j]['default_url'].'cid/'.$category[$i]['id'].'/sid/'.$sid.'.html';
					// }
				}
			}
		}
		$this->assign('CurrentUrl','Managetemplate');
		$this->assign('shopInfo', M('shop')->where(array('sid'=>$sid))->find());
		$this->assign('category', $category);
		$this->assign('t_price',$t_price);
		$this->assign('v_id',$v_id);
		$this->assign('pages', $page->show());
		$this->assign('themes',$themes);
		$this->assign('func', $func);
		$this->assign('tmfunc', $tmfunc);
		$this->assign('jid',$this->jid);
		$this->assign('sid',$sid);
		$this->display();	
	}


	//应用模板
	public function appTheme(){
		$sid    = I('sid', 0 ,'intval');
		$t_sign = I('t_sign');

		$re = M('shop')->where(array('sid'=>$sid))->setField('theme', $t_sign);
		$this->redirect('mobileTheme', array('sid'=>$sid));
		
	}	


	//系统模板
	public function sysTheme(){
		$shop = D('auth')->getAuthShops($this->mid);		
		$sid = I('sid', key($shop) );
		//查询新旧模板
		$shop_theme = M('shop')->where(array('sid'=>$sid))->getField('theme');
		$this->assign('shop_theme',$shop_theme);
		
		//条件
		$t_price = I('t_price',0);
		if($t_price == 1){
			$where = " t_status=1 AND t_price = 0 ";
		}elseif($t_price == 2){
			$where = " t_status=1 AND t_price > 0 ";
		}else{
			$where = " t_status=1 ";
		}

		$v_id  = I('v_id',0);
		if ($v_id != 0) {
			$where .= " AND t_vid like '%,$v_id,%' ";
		}
		//分页
		$page   = new \Common\Org\Page(M('Theme')->where($where)->count(), 4);
		$themes = M('Theme')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		//查询店铺
		
		//行业列表
		$vocation = M('vocation')->where(array('v_pid'=>0))->select();

		$this->assign('vocation',$vocation);
		$this->assign('shop', $shop);
		$this->assign('pages', $page->show());
		$this->assign('t_price', $t_price);
		$this->assign('v_id', $v_id);
		$this->assign('sid', $sid);
		$this->assign('themes',$themes);
		$this->display();
	}


	//背景音乐
	public function backMusic(){
		$sid = cookie('shop'.$this->jid);
		if ( IS_POST ) {
			$post_data = I('post.');
			$m_name    = $post_data['m_name'];
			$m_url     = $post_data['m_url'];
			if ($m_url == '' || $m_name == '') exit('2');
			//添加条件
			$opt = array(
				'music_name' => $m_name,
				'music_url'  => $m_url,
			);
			exit(M('shop')->where(array('sid'=>$sid))->save($opt) !== false ? '1' : '3');
		}else{
			$this->assign('info',M('shop')->where(array('sid'=>$sid))->find());
			$this->assign('pagename',"背景音乐上传");
			$this->display();
		}
		
	}


	//上传商铺logo
	public function logo(){
		$sid = cookie('shop'.$this->jid);
		if ( IS_POST ) {
			$upload = new \Think\Upload();// 实例化上传类
		    $upload->maxSize   =     3145728 ;// 设置附件上传大小
		    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		    $upload->rootPath  =     './Public/Data/'.$this->jid.'/'; // 设置附件上传根目录
		    // 上传文件 
		    $info   =   $upload->upload();
		    if(!$info) {// 上传错误提示错误信息
		        $this->error($upload->getError());
		    }else{// 上传成功
		    	$re = M('shop')->where(array('sid'=>$sid))->setField('logo','/Public/Data/'.$this->jid.'/'.$info['file']['savepath'].$info['file']['savename']);
		    }
		    $this->redirect('mobileTheme');
		}else{
			$this->display();
		}
	}



	//底部文字
	public function addFooterText(){
		$dtype = I('dtype', 1);
		if ( IS_POST ) {
			$post_data = I('post.');
			$t_content = $post_data['con'];
			if ($t_content == '') exit('2');
			//添加条件
			$opt = array(
				'footer_content'   => $t_content,
			);
			exit(M('merchant')->where(array('jid'=>$this->jid))->save($opt) !== false ? '1' : '3');
		}else{
			$this->assign('info',M('merchant')->where(array('jid'=>$this->jid))->getField('footer_content'));
			$this->assign('pagename',"底部文字");
			if ($dtype == 2) {
				$this->display('addFooterText2');
			}else{
				$this->display();
			}
			
		}
	}


	//创建二维码
	public function createQrCode($mtapp,$size=10,$domain=true){
		$qrcodefile = 'qrcode'.$size.'.png';
		if(!file_exists($this->path.$qrcodefile)) {
			vendor("phpqrcode.phpqrcode");
			$QRcode = new \QRcode();
			$qrcodefile = $qrcodefile;
			$codetxt = U('Index/appdown@yd',array('jid'=>$this->jid));
			$QRcode::png($codetxt, $this->path.$qrcodefile, 'H', $size);
			$applogo = (APP_DIR.$mtapp['applogo']);
			$QR = $this->path.$qrcodefile;
			if(file_exists($applogo)) {
				$QR = imagecreatefromstring(file_get_contents($QR)); 
				$applogo = imagecreatefromstring(file_get_contents($applogo)); 
				$QR_width = imagesx($QR); 
				$QR_height = imagesy($QR); 
				$logo_width = imagesx($applogo); 
				$logo_height = imagesy($applogo); 
				$logo_qr_width = $QR_width / 5; 
				$scale = $logo_width / $logo_qr_width; 
				$logo_qr_height = $logo_height / $scale; 
				$from_width = ($QR_width - $logo_qr_width) / 2; 
				imagecopyresampled($QR,$applogo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height); 
			} 
			imagepng($QR,$this->path.$qrcodefile); 
		}
		$file = '/Public/Data/'.$this->jid.'/'.$qrcodefile;
		if($domain)$file ='http://'.I('server.HTTP_HOST').$file;
		return $file;
	}

}