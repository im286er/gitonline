<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class IndexController extends ManagerController {
    //顶部菜单（一级菜单）
    public function index() {
		$groupid = \Common\Org\Cookie::get('groupid');

		$where = array('pid'=>0, 'type'=>1, 'status'=>1);
		if( $groupid != 0 ) {
			$rules = M('authGroup')->where("id=".$groupid)->getField('rules');
			$where['id'] = array("in", "$rules");	
		}
		$topNavList = M('authRule')->where( $where )->order('id')->select();
		$this->assign('topNavList', $topNavList);
		
		if( isset($topNavList[0]['id']) ) {
			$_whereo = $groupid ? array('pid'=>$topNavList[0]['id'], 'id'=>array("in", "$rules"), 'type'=>2, 'status'=>1) : array('pid'=>$topNavList[0]['id'], 'type'=>2, 'status'=>1);
			foreach(M('authRule')->where( $_whereo )->order('id')->select() as $rule) {
				$_wheret = $groupid ? array('pid'=>$rule['id'], 'id'=>array("in", "$rules"), 'type'=>3, 'status'=>1) : array('pid'=>$rule['id'], 'type'=>3, 'status'=>1);
				$rule['list'] = M('authRule')->where($_wheret)->order('id')->select();
				$authRuleList[] = $rule; 
			}	
		}
		$this->assign("authRuleList",$authRuleList); 
		$this->assign('msg', M('notice')->alias('AS n')->where("( n.fmid={$mid} OR n.tmid IN (-4, -3, {$mid}) ) AND d.cid is null")->join("__NOTICE_DATA__ AS d ON n.nid=d.nid AND d.mid={$mid}", 'left')->count());
        $this->display();
    }

    //左侧菜单（二级菜单|三级菜单）
    public function left() {
		$groupid = \Common\Org\Cookie::get('groupid');
		if( $groupid != 0 ) {
			$rules = M('authGroup')->where("id=".$groupid)->getField('rules');
		}
		
		$_IKN = 1;
		$html = '';
		$_whereo = $groupid ? array('pid'=>I('get.pid', 1, 'intval'), 'id'=>array("in", "$rules"), 'type'=>2, 'status'=>1) : array('pid'=>I('get.pid', 1, 'intval'), 'type'=>2, 'status'=>1);
		
        foreach(M('authRule')->where( $_whereo )->order('id')->select() as $key=>$rule) {
            $html .= '<div class="sideBar_nav">';
			$_wheret = $groupid ? array('pid'=>$rule['id'], 'id'=>array("in", "$rules"), 'type'=>3, 'status'=>1) : array('pid'=>$rule['id'], 'type'=>3, 'status'=>1);
		    $list = M('authRule')->where( $_wheret )->order('id')->select();
			$html .= '<h3 class="funicon_'.$rule['id'].'">'.$rule['title'].'</h3>';
			$html .= '<ul class="menuson">';
			if( is_array($list) && !empty($list) ) {
				foreach( $list as $_key=>$_value) {
					if( $_IKN== 1 ) { 
						$html .= '<li onClick="ChangeClassName(this);" class="selected"><cite></cite><a href="'.U($_value['name']."@xt", '', true, true).'" target="rFrame">'.$_value['title'].'</a><i></i></li>';					
						$RightUrl = U($_value['name']."@xt", '', true, true);
					} else {
						$html .= '<li onClick="ChangeClassName(this);"><cite></cite><a href="'.U($_value['name']."@xt", '', true, true).'" target="rFrame">'.$_value['title'].'</a><i></i></li>';					
					}
					$_IKN ++;
				}
			}
			$html .= '</ul></div>';
		}
		$html .= '<script type="text/javascript">LoadRightUrl("'.$RightUrl.'");</script>';
    	echo $html;
    }

    //系统主体部分
    public function main() {
		$groupid = \Common\Org\Cookie::get('groupid');
		if( $groupid==10 || $groupid==0 ) : //只有高级管理或超级管理员才能查看信息
			$count = array(
				'countIncomes'	=> '0.00', //实际总收入
				'countIncomey'  => '0.00', //预计总收入
				'countMentions' => '0.00', //实际总支出
				'countMentiony' => '0.00', //预计总支出
			);
			
			//计算最近30天的信息
			$startMapDate=$orderMapDate=array();
			//日期-预计收入-预计支付-实际收入-实际支出
			for($i=-29; $i<=0; $i++)  {
				$startMapDate[date('Ymd', mktime(0, 0, 0, date('m'), date('d')+$i, date('Y')))] = array(date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+$i, date('Y'))), "0.00", "0.00", "0.00", "0.00");
				$orderMapDate[date('Ymd', mktime(0, 0, 0, date('m'), date('d')+$i, date('Y')))] = array(date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+$i, date('Y'))), "0", "0", "0");
			}
			
			//计算最近30天的总收入, 以下单的时间为准, 有可能会出现 实际收入 > 预计收入
			$stime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-29, date('Y')));
			//把已关闭、待退款、已退款的订单去除
			foreach( M('order')->where("o_dstatus <> 5 and o_dstime >= '{$stime}' and o_pstatus!=2 and o_pstatus!=3")->select() as $b ) {
				$count['countIncomey'] += $b['o_price'];
				if( $b['o_pstatus'] == 1 )  $count['countIncomes'] += $b['o_price'];
			
				$betime = date('Ymd', strtotime($b['o_dstime']));
				if( isset($startMapDate[$betime]) ) {
					if( $b['o_pstatus']==1 ) {
						$startMapDate[$betime][3]  += $b['o_price']; 
					} else {
						$startMapDate[$betime][1]  += $b['o_price']; 
					}
				}
			}
			
			//计算最近30天的总支付
			foreach( M('bookkeeping')->where("btype <> 2 and bstime >= '{$stime}'")->select() as $b ) {
				$count['countMentiony'] += $b['bmention'];
				if( $b['btype'] == 1 ) $count['countMentiony'] += $b['bmention'];
			
				$betime = date('Ymd', strtotime($b['o_dstime']));
				if( isset($startMapDate[$betime]) ) {
					if( $b['btype']==1 ) {
						$startMapDate[$betime][4]  += $b['bmention']; 
					} else {
						$startMapDate[$betime][2]  += $b['bmention']; 
					}
				}
			}
			
			foreach($startMapDate as $key=>&$value) {
				$startMapDate[$key] = implode('|', $value);
			}
			
			
			$this->assign('data', implode(',', $startMapDate));
			$this->assign('count', $count);
		
		
			//订单统计
			$order = array(
				'countOrdero'  	=> '0', //订单总数
				'countOrders'  	=> '0', //已支付订单总数
				'countOrdery'	=> '0', //未支付订单总数
			);
			foreach( M('order')->where("o_dstatus <> 5 and o_dstime >= '{$stime}'")->select() as $b ) {
				$order['countOrdero'] ++;
				if( (int)$b['o_pstatus']==1 ) $order['countOrders'] ++; else $order['countOrdery'] ++;
	
				$betime = date('Ymd', strtotime($b['o_pstime']));
				if( isset($orderMapDate[$betime]) ) {
					$orderMapDate[$betime][1] ++;
					if($b['o_pstatus']==1) $orderMapDate[$betime][2] ++;
					if($b['o_pstatus']==0) $orderMapDate[$betime][3] ++;
				}
			}
			foreach($orderMapDate as &$o) { $o = implode('|', $o); }
			$this->assign('order_data', implode(',', $orderMapDate));
			$this->assign('order_cont', $order);
		endif;
		$this->assign('groupid', $groupid);
		
		$this->assign('mid', $mid);
		
		//会员总数
        $this->assign('user_cont', M('user')->count());
		
		$stairAgent = M('agent')->alias("a")->join("__MEMBER__ AS m ON a.mid=m.mid")->where("m.mstatus <> -1 AND a.pid = 0 AND m.mtype=1")->count();
		$secondAgent = M('agent')->alias("a")->join("__MEMBER__ AS m ON a.mid=m.mid")->where("m.mstatus <> -1 AND a.pid > 0 AND m.mtype=1")->count();
		$this->assign('stairAgent', $stairAgent);
		$this->assign('secondAgent', $secondAgent);
        //代理商总数
        $this->assign('agent_cont', $stairAgent+$secondAgent);

	

        //商家总数
        $this->assign('merchant_cont', M("merchant")->alias("j")->join("__MEMBER__ AS m ON j.mid=m.mid")->where("m.mstatus <> -1 AND m.mtype=2")->count());
		
    	$this->display();
    }

}
