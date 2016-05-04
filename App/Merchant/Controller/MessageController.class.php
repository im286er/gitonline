<?php
namespace Merchant\Controller;

header("Content-type:text/html;charset=utf-8");

class MessageController extends MerchantController {
	//群发消息
	public function pushmsg() {
		if( IS_POST ) {
			
			$data['pcontent'] = $_POST['pcontent'];
			$data['putype'] = 1;
			$data['pmid'] = $this->mid;

			if( $pid=M('pushContent')->add($data) ) {
				$user = M('fl_user')->where(array('flu_sjid'=>$this->jid))->field('flu_phone')->select();
				$user_str = array();
				foreach($user as $k=>$v){
					if($v['flu_phone']){
						$user_str[] = $v['flu_phone'];
					}
				}
				$user_str2 = join(',',$user_str);
				if($user_str2){
					sendmsg($user_str2, $data['pcontent']);
				}
				$this->success('推送成功', U('/Message/listmsg', '', true));
			} else { $this->error('推送失败'); }			
		} else {
			$this->assign('CurrentUrl', 'Messagepushmsg2');
			$this->display();	
		}
	}



	//接受站内消息
	public function notice(){
	  $merchant = M('Merchant')->alias('AS mt')->join('__AGENT__ as agent ON mt.magent=agent.id', 'left')->where(array('mt.jid'=>$this->jid))->find();
	  if( IS_POST ) {
		if(I('post.action')=='view' && I('post.nid','','intval')){
			$viewwhere = array();
			$viewwhere['tmid']  = array('in',array('-1','0',$this->mid,0-$merchant['mid']));
			$viewwhere['nid'] = I('post.nid');
			$result = M('Notice')->where($viewwhere)->find();
			$checkview = M('NoticeData')->where(array('nid'=>I('post.nid'),'mid'=>$this->mid))->find();
			if(!$checkview){
				M('NoticeData')->add(array('nid'=>I('post.nid'),'mid'=>$this->mid));
			}
			exit($result['ncon']);
		}
		exit;
	  }
	  $where = $map = $nids = $mids = array();
	  $where['tmid']  = array('in',array('-1','0',$this->mid,0-$merchant['mid']));
	  $page = new \Common\Org\Page(M('Notice')->where($where)->count(), 9);
	  $notice = M('Notice')->order('nid desc')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
	  if($notice)foreach($notice as $value){
		  $nids[] =  $value['nid'];
		  $mids[] =  $value['fmid'];
	  }
	  if($nids){
		  $map['nid'] = array('in',$nids);
		  $map['mid'] = $this->mid;
		  $viewnids = M('NoticeData')->field('nid,mid')->where($map)->select();
		  $ids = array_column($viewnids,'mid','nid');
	  }

	  if($mids){
		  $membermap['mid'] = array('in',$mids);
		  $members = M('Member')->field('mid,mname')->where($membermap)->select();
		  $memberids = array_column($members,'mname','mid');
	  }
	  
	  $this->assign('ids', $ids);
	  $this->assign('memberids', $memberids);
	  $this->assign('notice', $notice);
	  $this->assign('pages', $page->show());
	  $this->display();	
	}
	
	//消息列表
	public function listmsg() {
		$where = array('tjid'=>$this->jid);
		foreach( M('merchantUser')->where($where)->field('tmid')->select() as $mid ) $midlist[] = $mid['tmid'];
		@array_unique($midlist); $midlist = @implode(",", $midlist);
		
		$page = new \Common\Org\Page(M('pushContent')->where(array('pmid'=>array('in', $midlist)))->count(), 6);
		$listmsg = M('pushContent')->where(array('pmid'=>array('in', $midlist)))->order('ptime desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('listmsg', $listmsg);
		$this->assign('pages', $page->show());
		$this->display();
	}


	
	//删除消息列表
	public function delsmsg() {
		$pid = I('get.pid', 0, 'intval');
		$msg = M('pushContent')->where(array('pid'=>$pid))->find();
		$jid = M('merchantUser')->where(array('tmid'=>$msg['pmid']))->getField('tjid');
		if( !$jid || $jid != $this->jid ) E('你无权查看当前页面');
		if( M('pushContent')->where(array('pid'=>$pid))->delete() ) {
			$this->success('操作成功', U('/Message/listmsg', '', true));	
		} else { $this->error('操作失败'); }
	}


	/**
	 * 留言列表
	 */
	public function commentmsg(){
		$where = array('jid'=>$this->jid);
		
		$page  = new \Common\Org\Page(M('Comments')->where($where)->count(), 6);

		$commentList = M('Comments')->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
		
		$this->assign('commentList' , $commentList);
		$this->assign('pages' , $page->show());
		$this->display();
	}



	/**
	 * 添加留言信息
	 */
	public function editComment(){
		if ( IS_POST ) {
			$opt = array(
					'jid'         => $this->jid,
					'phone'       => I('post.phone'),
					'content'     => I('post.pcontent'),
					'create_time' => date('Y-m-d'),
				);
			if ($re = M('Comments')->add($opt)) {
				$this->success('添加成功', U('/Message/commentmsg', '', true));
			}else{
				$this->error('操作失败');
			}
		}
		$this->display();
	}


	/**
	 * 删除留言信息
	 */
	public function delComment(){
		//获取ID
		$id  = I('get.id' , 0 , 'intval');

		//删除操作
		if (M('Comments')->where(array('id'=>$id))->delete()){
			$this->success('操作成功', U('/Message/commentmsg', '', true));
		}else{
			$this->error('操作失败'); 
		}
	}



	/**
	 * 特权价格
	 */
	public function parammsg(){
		//文件路径
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		
		if ( IS_POST ){
			$param = I('post.param');
			//保存参数到文件
			if (file_put_contents($path.'ParamMsg.php', $param)){
				$this->success('操作成功', U('/Message/parammsg', '', true));
			}else{
				$this->error('操作失败');
			}
		}
		//从文件查询参数
		file_exists($path.'ParamMsg.php') && $parammsg=file_get_contents($path.'ParamMsg.php');

		$this->assign('parammsg',$parammsg);
		$this->display();
	}

	
    //最新活动
    public function hdlist() {
		$where = $this->type==1 ? array('av_jid'=>$this->jid, 'av_status'=>1) : array('av_sid'=>array('like', '%,'.$this->tsid.',%'), 'av_status'=>1);
		$page = new \Common\Org\Page(M('active')->where($where)->count(), 5);
		$this->assign('avlist', M('active')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
		if( IS_POST ){
			$Module['Name'] = I('post.HdModuleName', '');
			$Module['Icon'] = I('post.HdModuleIcon', '');
			if( $Module) {
				$this->writeFile($this->path.'HdModule.php',serialize($Module));
				exit("1");
			}
		}
		file_exists($this->path.'HdModule.php') && $HdModule=unserialize($this->readsFile($this->path.'HdModule.php'));
		$this->assign('HdModule', $HdModule);
		$this->display();
    }
	
	//添加活动
	public function addhd() {
		if( IS_POST ) {
			$data = array();
			$data['av_title'] = I('post.t', '');
			$data['av_img'] = I('post.i', '');
			$data['av_con'] = preg_replace("/<[^><]*script[^><]*>/i", '', $_POST['c']);
			$data['av_stime'] = isset($_POST['s']) && !empty($_POST['s']) ? strip_tags($_POST['s']) : date('Y-m-d H:i:s');
			$data['av_etime'] = isset($_POST['e']) && !empty($_POST['e']) ? strip_tags($_POST['e']) : date('Y-m-d H:i:s', strtotime('+7 days'));
			$data['av_jid'] = $this->jid;
			$data['av_mid'] = $this->mid;
			if(!$data['av_title'] || !$data['av_img'] || !$data['av_jid'] || !$data['av_mid']) exit('0');
			if( $this->type != 1 ) {
				$data['av_sid'] = ','.$this->tsid.',';
			} else {
				$data['av_sid'] = ','.trim(implode(',', $_POST['d']), ',').',';
			}
			exit( M('active')->add($data) ? "1" : "0" );
		} else {
			//如果是 商家登录（品牌），先要判断此商家有没有分店
			if( $this->type == 1 ) {
				$splist = M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->select();
				if( !is_array($splist) || empty($splist) ) $this->error('您还没有一个分店，请先添加分店！');
				$this->assign('splist', $splist);
			}
			$this->display();
		}
	}
	
	//删除活动
	public function delhd() {
		$acinfo = M('active')->where(array('av_id'=>I('post.id', 0, 'intval')))->find();
		if( !is_array($acinfo) || empty($acinfo) || $acinfo['av_jid'] != $this->jid ) exit("0");
		exit( M('active')->where(array('av_id'=>I('post.id', 0, 'intval')))->setField('av_status', '2') !== false ? "1" : "0");
	}
	
	//修改活动
	public function editdh() {
		if( IS_POST ) {
			$data = array();
			$data['av_title'] = I('post.t', '');
			$data['av_img'] = I('post.i', '');
			$data['av_con'] = preg_replace("/<[^><]*script[^><]*>/i", '', $_POST['c']);
			$data['av_stime'] = isset($_POST['s']) && !empty($_POST['s']) ? strip_tags($_POST['s']) : date('Y-m-d H:i:s');
			$data['av_etime'] = isset($_POST['s']) && !empty($_POST['e']) ? strip_tags($_POST['e']) : date('Y-m-d H:i:s', strtotime('+7 days'));
			$data['av_jid'] = $this->jid;
			$data['av_mid'] = $this->mid;
			if(!$data['av_title'] || !$data['av_img'] || !$data['av_jid'] || !$data['av_mid']) exit('0');
			if( $this->type == 1 ) {
				$data['av_sid'] = ','.trim(implode(',', $_POST['d']), ',').',';
			}
			exit( M('active')->where(array('av_id'=>I('post.id', 0, 'intval')))->save($data) !== false ? "1" : "0" );
		} else {
			//如果是 商家登录（品牌），先要判断此商家有没有分店
			if( $this->type == 1 ) {
				$splist = M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->select();
				if( !is_array($splist) || empty($splist) )$this->error('您还没有一个分店，请先添加分店！');
				$this->assign('splist', $splist);
			}
			$acinfo = M('active')->where(array('av_id'=>I('get.id', 0, 'intval')))->find();
			if( !is_array($acinfo) || empty($acinfo) || $acinfo['av_jid'] != $this->jid ) E('你无权查看当前页面！');
			$this->assign('avinfo', $acinfo);
			$this->display();
		}
	}
	
	//活动详情
	public function infodh() {
		//如果是 商家登录（品牌），先要判断此商家有没有分店
		if( $this->type == 1 ) {
			$splist = M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->select();
			if( !is_array($splist) || empty($splist) ) $this->error('您还没有一个分店，请先添加分店！');
			$this->assign('splist', $splist);
		}
		$acinfo = M('active')->where(array('av_id'=>I('get.id', 0, 'intval')))->find();
		if( !is_array($acinfo) || empty($acinfo) || $acinfo['av_jid'] != $this->jid ) E('你无权查看当前页面！');
		$this->assign('avinfo', $acinfo);
		$this->assign('jid', $this->jid);
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

	
	#############################################
	#########          推送消息           #########
	#############################################
	//特定用户
	private function _IGtPushMessageToCidTrans( $data=array() ) {
		$args = array(
			'transmissionContent'	=> $data['pcontent'],
		);
		$mesg = array(
			'offlineExpireTime'		=> 252000000,
			'netWorkType'			=> 0
		);
		$app = M('merchantApp')->where('jid='.$this->jid)->find();


		$typesargs = array(
			'title'					=> $data['ptitle'],
			'text'					=> $data['pcontent'],
			'isRing'				=> true,
			'isVibrate'				=> true,
			'isClearable'			=> $data['pclea']==1 ? true : false,
			'transmissionType'		=> 1,
			'transmissionContent'	=> $data['pmsge']
		);


		$typemesg = array(
			'offlineExpireTime'		=> isset($data['pline']) && intval($data['pline'])>=0 && intval($data['pline'])<=72 ? intval($data['pline']) * 3600 : 7200,
			'netWorkType'			=> $data['pwebs']
		);

		\Common\Org\IGPushMsg::getIGPushMsg(true, $app)->pushMessageToApp(4, json_encode($args), json_encode($mesg), array(), array());
		\Common\Org\IGPushMsg::getIGPushMsg(true, $app)->pushMessageToApp(1, json_encode($typesargs), json_encode($typemesg), array(), array());
	}
	
}