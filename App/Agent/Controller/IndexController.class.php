<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class IndexController extends ManagerController {
	
	//代理商首页
	public function index() {
		$menulist = C('ADMIN_MENU_LIST');
		
		//此代理商的 MID 和 代理ID
		$mid = \Common\Org\Cookie::get('mid');
		$agentid = \Common\Org\Cookie::get('agentid');
		
		//代理商信息
		$agentInfo = M("agent")->where("id=".$agentid)->find();
		if( $agentInfo['arank']=='g' ) unset( $menulist[1] );//如果是个人代理，则不能再添加子代理了
		
		//消息通知
		$notice = M('notice AS n')->where("( n.fmid={$mid} OR n.tmid IN (-4, -2, {$mid}) ) AND d.cid is null")->join("__NOTICE_DATA__ AS d ON n.nid=d.nid AND d.mid={$mid}", 'left')->count();
		$this->assign('msg', $notice);
		
		$pid = $agentInfo['arank']=='g' ? 2 : 1;
		$this->assign('menulist', $menulist);
		$this->assign('authRuleList', $menulist[$pid]['list']);
		$this->display();
	}
	
	//左侧菜单（二级菜单|三级菜单）
    public function left() {
		$menulist = C('ADMIN_MENU_LIST');
		$pid  = I('get.pid');
		$_IKN = 1;
		$html = '';
        foreach($menulist[$pid]['list'] as $key=>$rule) {
            $html .= '<div class="sideBar_nav">';
			$html .= '<h3 class="funicon_'.$rule['icon'].'">'.$rule['name'].'</h3>';
			$html .= '<ul class="menuson">';
			if( is_array($rule['list']) && !empty($rule['list']) ) {
				foreach( $rule['list'] as $_value) {
					$_v = explode('|', $_value);
					if( $_IKN== 1 ) { 
						$html .= '<li onClick="ChangeClassName(this);" class="selected"><cite></cite><a href="'.U($_v[1]."@dl", '', true, true).'" target="rFrame">'.$_v[0].'</a><i></i></li>';					
						$RightUrl = U($_v[1]."@dl", '', true, true);
					} else {
						$html .= '<li onClick="ChangeClassName(this);"><cite></cite><a href="'.U($_v[1]."@dl", '', true, true).'" target="rFrame">'.$_v[0].'</a><i></i></li>';					
					}
					$_IKN ++;
				}
			}
			$html .= '</ul></div>';
		}
		$html .= '<script type="text/javascript">LoadRightUrl("'.$RightUrl.'");</script>';
    	echo $html;
    }
	
    public function main() {
    	$MerchantController = A("Merchant");
		$AgentIdLists = $MerchantController->_getAgentId( \Common\Org\Cookie::get('agentid') );
		$MerchantIdLists = $MerchantController->_getMerchantId();
    	
    	//代理商总数
    	$this->assign('agent_cont', count($AgentIdLists)); 
    	//商家总数
		$this->assign('merchant_cont', count($MerchantIdLists));

        //收支统计
       /* $count = array(
            'countIncomes'	=> '0.00', //实际总收入
            'countIncomey'  => '0.00', //预计总收入
            'countMentions' => '0.00', //实际总支出
            'countMentiony' => '0.00', //预计总支出
        );

		//计算最近30天的信息
		$startMapDate=$orderMapDate=array();
		for($i=-29; $i<=0; $i++)  {
            $startMapDate[date('Ymd', mktime(0, 0, 0, date('m'), date('d')+$i, date('Y')))] = array(date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+$i, date('Y'))), "0.00", "0.00", "0.00", "0.00");
            $orderMapDate[date('Ymd', mktime(0, 0, 0, date('m'), date('d')+$i, date('Y')))] = array(date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+$i, date('Y'))), "0", "0", "0");
        }
		
		$agentinfo = M("agent")->where("mid=".$_SESSION['member']['mid'])->find();  
		$agentinfoArr = M("agent")->alias('AS agent')->where("(agent.id={$agentinfo['id']} OR agent.pid={$agentinfo['id']}) AND member.mstatus >= 0")->join("__MEMBER__ AS member on agent.mid=member.mid", 'left')->field('agent.id')->select();
		foreach($agentinfoArr as $a) $agentIdArr[] = $a['id'];  
    	$bwhere['_string']="(ad.pid='$agentinfo[id]' or ad.mid=".$_SESSION['member']['mid'].") AND ax.mstatus != '-1'";
        $bwhere['type']='1';  	   
		$merchant= M("merchant_user ar")->join("azd_merchant at on  at.jid=ar.tjid")->join("azd_agent ad on  at.magent=ad.id")->join("azd_member ax on ax.mid=ad.mid")->where($bwhere)->select();
		$tmids=array();
		$jids=array();
		foreach($merchant as $km){$tmids[]=$km['tmid'];$jids[]=$km['tjid'];}
		//print_r(implode(',',$jids));
 
		
		//计算最近30天的总收入, 以下单的时间为准, 有可能会出现 实际收入 > 预计收入
		$stime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-29, date('Y')));
		$orderWhere['o_dstatus']=array('NEQ',5);
		$orderWhere['o_dstime']=array('egt',$stime); 
		$orderWhere['o_jid']=array('in',$jids); 
		$orderWhere['_string']="o_pstatus!=2 and o_pstatus!=3"; 
		foreach( M('order')->where($orderWhere)->select() as $b ) { 
		
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
		$bookWhere['btype']=array('NEQ',2);
		$bookWhere['bstime']=array('egt',$stime); 
		$bookWhere['bmid']=array('in',$tmids);   
		foreach( M('bookkeeping')->where($bookWhere)->select() as $b ) {
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
        foreach( M('order')->where($orderWhere)->select() as $b ) {
            $order['countOrdero'] ++;
            if($b['o_pstatus']==1) $order['countOrders'] ++; else $order['countOrdery'] ++;

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
        */
		//会员总数
		$countWhere['u_jid'] = array('in', $MerchantIdLists); 
        $this->assign('user_cont', M('user')->where($countWhere)->count()); 

		$this->assign('pid', $agentinfo['pid']);
    	$this->display();
    }
}