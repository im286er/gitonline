<?php
namespace Merchant\Controller;

class InfoController extends MerchantController {
	//在线消费、远程预订、本地视频
	public function index() {
		$ctype=3;
		if( $this->type == 1 ) {
			file_exists($this->path.'InfoMenu'.$ctype.'Name.php') && $modulename=file_get_contents($this->path.'InfoMenu'.$ctype.'Name.php');
			file_exists($this->path.'InfoMenu'.$ctype.'Icon.php') && $moduleicon=file_get_contents($this->path.'InfoMenu'.$ctype.'Icon.php');
			$this->assign('modulename', $modulename ? $modulename : '');
			$this->assign('moduleicon', $moduleicon ? $moduleicon : '');
			$this->assign('modulelink','http://yd.dishuos.com/Video/index/jid/'.$this->jid.'.html' );
		}
		$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
		$classlist = M('class')->where(array('jid'=>$this->jid, 'status'=>1, 'sid'=>$sid, 'ctype'=>$ctype))->order('corder desc')->select();
		
		if( is_array($classlist) && !empty($classlist) ) {
			$this->assign('classlist', $classlist);
			$cid = isset( $_GET['cid'] ) && intval( $_GET['cid'] ) ? intval($_GET['cid']) : $classlist[0]['cid'];
			B('Common\\Behavior\\CheckMerchantCid', '', $cid);

			$MinfoModel = $ctype==3 ? M("video") : M('goods');
			$page = new \Common\Org\Page( $MinfoModel->where(array('cid'=>$cid, 'gstatus'=>1, ))->count(), 5);
			$this->assign( 'infolist', $MinfoModel->where(array('cid'=>$cid, 'gstatus'=>1))->order("gorder asc")->limit($page->firstRow.','.$page->listRows)->select() );
			
			//echo $MinfoModel->getlastsql();
			$this->assign('page', $page->show());
			$this->assign('cid', $cid);
		}
		$this->assign('sid', $sid);
		$this->assign('tfs', ($this->type==1 && $sid!=0) || $this->type!=1 ? '0' : '1');
		$this->assign('ptitle','视频上架');
		$this->assign('CurrentUrl', 'Salesgoods');
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
		$classList = M('class')->where(array('jid'=>$this->jid, 'sid'=>$sid, 'status'=>1, 'ctype'=>3))->order('corder')->select();
		$html = "";
		if(is_array($classList) && !empty($classList)) {
			foreach($classList as $class) {
				$html .= '<li data-id="'.$class['cid'].'" data-order="'.$class['corder'].'" class="">';
				$html .= '	<a href="/Info/index/ctype/3/sid/'.$sid.'/cid/'.$class['cid'].'.html">'.$class['cname'].'</a>';
				$html .= '	<b class="pull-right" style="display:none;">';
                $html .= '		<i class="writeicon" onClick="DialogFrameFun(465, 234, \''.U('/Info/editClass', array('cid'=>$class['cid'], 'sid'=>$sid), true).'\')"></i>';
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
			$MinfoModel =  D('Video'); unset( $_POST['ctype'] );
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
			$this->display("addVideo");	
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
	

    //修改视频
    public function editVideo() {
    	if( IS_POST ) {
			$video = M('video')->where(array('gid'=>I('post.gid')))->find();
			if( !is_array($video) || empty($video) ) exit( json_encode(array('msg'=>'你无权修改当前页面')) );
			B('Common\\Behavior\\CheckMerchantCid', '', $video['cid']);
			
			$msg = D('Video')->update($_POST) !== false ? array('msg'=>'') : array('msg'=>D('Video')->getError());
			if( $msg['msg'] == '' && $_POST['gid'] ) {
				$msg['info'] = D('Video')->where(array('gid'=>$_POST['gid']))->find();
			}
			exit( json_encode($msg) );
		} else {
			$vinfo = M('video')->alias('AS g')->join('__CLASS__ as c ON g.cid=c.cid', 'left')->where(array('c.jid'=>$this->jid, 'gid'=>I('get.gid')))->find();
			if( !is_array($vinfo) || empty($vinfo) ) E('你无权查看当前页面');
			$sid = $this->type==1 ? I('get.sid', 0, 'intval') : $this->tsid;
			$this->assign('clist', M('class')->where(array('jid'=>$this->jid, 'sid'=>$sid, 'ctype'=>$vinfo['ctype'], 'status'=>1))->select());
			$this->assign('vinfo', $vinfo);
			$this->display();		
		}
    }
	
	//同步数据到分店
	public function synchro() {
		if( IS_POST ) {
			$ctype=intval( $_POST['ctype'] ); $sid=substr($_POST['sid'], 0, -1);
			if(!$ctype || !$sid) exit("0");
			
			if( $ctype==3 ) {
				$this->_synchroVideo($sid);
			}
			exit("1");
		} else {
			$this->assign('splist', M('shop')->where(array('jid'=>$this->jid, 'status'=>"1"))->select());
			$this->display();	
		}
	}


	private function _synchroVideo($sid) {
		foreach( M('class')->where(array('sid'=>array('in', "$sid"), 'ctype'=>$ctype))->field('cid')->select() as $_cid ) {
			$catidlist[] = $_cid;	
		}
		$catidlist = implode(',', array_unique($catidlist));
		M('class')->where(array('sid'=>array('in', "$sid"), 'ctype'=>$ctype))->setField('status', 0);
		M('video')->where(array('sid'=>array("in", "$sid"), 'cid'=>array("in", "$catidlist")))->setField('gstatus', 0);
		//查看商家数据
		$cidlist = M('class')->where(array('jid'=>$this->jid, 'sid'=>0, 'ctype'=>3, 'status'=>1))->select();
		foreach($cidlist as $cinfo) {
			$ocid=$cinfo['cid']; unset( $cinfo['cid'] );
			foreach( explode(',', $sid) as $nsid ) {
				$cinfo['sid'] = $nsid;
				if( $ncid=M('class')->add($cinfo) ) {
					M('class')->query("insert into azd_video(`gname`,`gdescription`,`cid`,`sid`,`gfile`,`gimg`,`gorder`,`gstatus`) select `gname`,`gdescription`,$ncid as `vcid`,$nsid as `sid`,`gfile`,`gimg`,`gorder`,`gstatus` from azd_video where cid={$ocid} and sid=0 and gstatus=1");
				}
			}
		}
	}

}