<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class NoticeController extends ManagerController {
	private $mid;
	
	public function _initialize() {
		parent::_initialize();
		$this->mid = (int)\Common\Org\Cookie::get('mid');
	}
	
	//消息列表
	public function noticesList() {
		$id=M("agent ad")->join("azd_member am on ad.mid=am.mid")->where("ad.mid=".$this->mid)->find();
        $agentMid=M("agent ad")->join("azd_member am on ad.mid=am.mid")->where("ad.id=".$id['pid'])->find();//查找上级代理商
        
        $mblist = M('member')->where(array('mtype'=>0))->field('mid')->select();
        $mblist2 = array();
        foreach($mblist as $k=>$v){
        	$mblist2[] = $v['mid'];
        }
        $mbstr  = join(',',$mblist2);
        
        if($id['pid'] == 0){
        	/*一级代理的通知显示范围
        	 * 1.系统发送给所有代理的
        	 * 2.自己发送的
        	 * 3.指定发送给自己的
        	 * */
        	$where = " ((n.fmid in (".$mbstr.") and n.tmid=-2) or n.fmid={$this->mid} or n.tmid={$this->mid}) ";
        }else{
        	/*子代理的通知显示范围
        	 * 1.系统发送给所有代理的
        	 * 2.上级代理发送给所有子代理的
        	 * 3.自己发送的
        	 * 4.指定发送给自己的
        	 * */
        	$where = " ((n.fmid in (".$mbstr.") and n.tmid=-2) or (n.fmid={$agentMid['mid']} and n.tmid=-2)  or n.fmid={$this->mid} or n.tmid={$this->mid}) ";
        }
        
		$count = M('notice')->alias('AS n')->where($where)->join("__NOTICE_DATA__ AS d ON n.nid=d.nid AND d.mid={$this->mid}", 'left')->count();
		
		$page = new \Think\Page($count,5);
		$show=$page->show();
		$tname = array(-1=>'所有商家', -2=>'所有代理商');
		$noticelist = M('notice')->alias('AS n')->where($where)->field('n.*,d.cid,m1.mname as fname,m2.mname AS tname')->join("__NOTICE_DATA__ AS d ON n.nid=d.nid AND d.mid={$this->mid}", 'left')->join('__MEMBER__ as m1 ON n.fmid=m1.mid', 'left')->join('__MEMBER__ as m2 ON n.tmid=m2.mid', 'left')->order('n.nid DESC')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($noticelist as $k=>$notice) {
			array_key_exists($notice['tmid'], $tname) && $noticelist[$k]['tname']=$tname[$notice['tmid']];  
		}
	
		$this->assign('noticelist', $noticelist);  
		$this->assign('mid', $this->mid);
		$this->assign('pages',$show);  
		$this->display();
	}


	//发送消息
	public function noticeAdd() {

		if( IS_POST ) {
	
			$data['fmid'] = $this->mid;
			
			if( I('post.p_type', 0, 'intval') !== 5 ) {

   				$data['tmid'] = I('post.p_type'); 
				
			} else {
				$data['tmid'] = M('member')->where(array('mname'=>I('post.muserid')))->getField('mid');
			}
			$data['ntis'] = I('post.ptitle');
			$data['ncon'] = I('post.pcontent'); 
			if( M('notice')->add($data) ) {
				$this->display('Jump:success');	
			} else { $this->display('Jump:error'); } 
		} else {
			$mid=$this->mid;
			//判断是否存在子代理
			$agentDa=M("agent")->where("mid=".$mid)->find(); 
			$pid=$agentDa['pid'];
			$this->assign('mid', $this->mid);
			$this->assign("pid",$pid);
			$this->display();
		}
	}

	//删除消息
	public function noticeDel() {
		$nid = I('get.nid', ''); if( !$nid ) exit('0');
		exit( M('notice')->where(array('nid'=>array('in', "$nid"), 'fmid'=>$this->mid))->delete() !== false && M('noticeData')->where(array('nid'=>array('in', "$nid"), 'mid'=>$this->mid))->delete() !== false ? "1" : "0");
	}
	
	//查看消息
	public function noticeSet() {
		$noticed=M('noticeData')->add(array('mid'=>$this->mid, 'nid'=>I('get.nid', 0, 'intval')));
	if($noticed){
		
	$data=1;	
	}
	echo $data;	 
	}
	
	//发送消息时，用于获取用户名
	public function publicGetName() {
		$keywords = I('get.q', ''); if( !$keywords ) exit('');
		foreach(M('Member')->field('mname,mtype')->where(array('mname'=>array('like', "{$keywords}%")))->select() as $m) {
			echo $m['mname']."|".$m['mtype']."\n";
		}
	}
	
	 //判断是否为代理商下的子代理或商户暂时不包括门店mid
	 public function keyMname(){
      $mids=array();
	 $mname=I('get.mname');
	 $mid=M("member")->where("mname='$mname'")->find(); 
	
	 $id =M("agent")->where("mid=".$this->mid)->find();$where['_string']="ad.pid='$id[id]' or ad.mid=".$_SESSION['member']['mid'];
     $agentMid=M("agent ad")->join("member am on ad.mid=am.mid")->where($where)->select(); 
	 foreach($agentMid as $k){$mids[]=$k['mid'];}
	$merchantMid= M("merchant_user ar")->join("azd_merchant at on  at.jid=ar.tjid")->join("azd_agent ad on  at.magent=ad.id")->join("azd_member ax on ax.mid=ad.mid")->where($where)->select();
      foreach($merchantMid as $ky){$mids[]=$ky['tmid'];}
	 
	if(in_array($mid['mid'],$mids)==true){$data=1;	}else{$data=0;}
	echo $data;  
	}
 }
