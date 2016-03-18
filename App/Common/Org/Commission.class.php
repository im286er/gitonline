<?php
namespace Common\Org;

class Commission {
	private $orderid = '';
	private $nowuser = '';
	private $insetbug = array();
	
	private static $ScommissionOjb;
	
	public static function translation() {
		if(!self::$ScommissionOjb || !self::$ScommissionOjb instanceof self) {
			self::$ScommissionOjb = new self();
		}
		return self::$ScommissionOjb;
	}
	
	private function __constuct() {}
	private function __clone() {}
	
	public function insertInfo( $oid, $debug=false ) {
		if( $oid ) $this->orderid = $oid; 
		if( !$this->orderid ) return JSON( array('errno'=>20001, 'error'=>'请输入正确的订单号') );
		
		$SorderInfo = M('flOrder')->where(array('flo_id'=>$this->orderid))->find();
		$this->nowuser = $SorderInfo['flo_uid'];
		
		if( !is_array($SorderInfo) || empty($SorderInfo) ) {
			return JSON( array('errno'=>20002, 'error'=>'订单号输入错误') );	
		}
		
		if( $SorderInfo['flo_gtype']==3 ) { //升级VIP，也就是会支付 9.9 元
			$this->upgradevip( $SorderInfo, $debug );	
		} else { //下订单返利收入
			$this->rebatedivided( $SorderInfo, $debug );
		}
		if( $debug ) return $this->insetbug;
		return JSON( array('erron'=>'0', 'error'=>'') );
	}

	//升级VIP分钱
	private function upgradevip( array $order, $debug=false ) {
		$register_vip_grade = C('REGISTER_VIP_GRADE');
		$AgentMoney = array_pop( $register_vip_grade );
		
		foreach($register_vip_grade as $k=>$money)
		{
			if( $userid=$this->getInviting($this->nowuser, $k) ) {//如果有邀请人，则向此邀请人中打入 收入比例
				$this->recordRevenue( $userid, $order['flo_id'], $money, 2, $this->nowuser, '', $debug);
			}
		}
		
		//此会员的代理收入
		$agentid = M('flUser')->where("flu_userid=".$this->nowuser)->getField('flu_sagentid');
		if( $agentid ) {
			//判断此代理是不是和个人会员绑定，如果绑定，提成进入此个人账户，没有绑定，则没有收入
			$agent_userid = M('flUser')->where("flu_gagentid=".$agentid)->getField('flu_userid');
			if( $agent_userid ) {
				$this->recordRevenue( $agent_userid, $order['flo_id'], $AgentMoney, 3, $this->nowuser, '', $debug);
			}
		}
	}
	
	//获取父级邀请人
	private function getInviting( $userid, $devel=0 ) {
		if( !$userid ) return false;

		do {
			$userid = M('flUser')->where("flu_userid=".$userid)->getField('flu_puserid');
			$devel --;
		} while( $devel>=0 );
		
		//如果返回 false，表示此会员上面没有邀请人了
		return $userid ? $userid : false;
	}
	
	//下订单返利收入
	private function rebatedivided( array $order, $debug=false ) {
		$order_return_grade = C('ORDER_RETURN_GRADE');
		$myself_money_grade = array_shift($order_return_grade);//用户自己的返现比例，其它都是邀请人
		$utype = M('fl_user')->where(array('flu_userid'=>$order['flo_uid']))->getField('flu_usertype');
		if($utype == 0){//普通会员系数
			$myself_money_grade = $myself_money_grade * C('USER_RATION_VIP');
		}
		//先计算自己的返现收入
		$myself_money = round($order['flo_backprice'] * $myself_money_grade / 100, 2);
		$this->recordRevenue($this->nowuser, $order['flo_id'], $myself_money, 0, $this->nowuser, '', $debug);

		//再计算两层邀请人收入
		$i=0;
		foreach($order_return_grade as $k=>$money) {
			$userid=$this->getInviting($this->nowuser, $k) ;

			if( $userid ) {//如果有邀请人，则向此邀请人中打入 收入比例
				$now_money_grade = array_shift( $order_return_grade );
				$now_money_grade = $utype != 0 ? $now_money_grade : $now_money_grade * C('USER_RATION_VIP');
				
				$now_money = round($order['flo_backprice'] * $now_money_grade / 100, 2);
				$this->recordRevenue($userid, $order['flo_id'], $now_money, 1, $this->nowuser, '', $debug);
			}
			
			if( ++$i >= 2 ) break;
		}
		
		//再次计算业务员的收入(暂时没有功能)
		$yw_money_grade = array_shift( $order_return_grade );
		
		//最后计算商家所在的代理商的收入
		$dl_money_grade = array_shift( $order_return_grade );
		$dl_money = round($order['flo_backprice'] * $dl_money_grade / 100, 2);

		$agent_id = M('merchant')->where("jid=".$order['flo_jid'])->getField('magent');
		$agent_id && $agent_userid=M('flUser')->where("flu_gagentid=".$agent_id)->getField('flu_userid');
		if( $agent_userid ) {
			$this->recordRevenue($agent_userid, $order['flo_id'], $dl_money, 4, $this->nowuser, '', $debug);
		}
	}
	
	
	//记录收入
	private function recordRevenue($flt_uid, $flt_oid='', $flt_balance=0, $flt_type=0, $flt_tuserid=0, $flt_notes='', $debug=false) {
		if( !$flt_uid ) return false;
		
		if( $debug ) {
			$this->updateDebug( $flt_uid, $flt_balance);
		} else {
			$data = array();
			$data['flt_uid'] = $flt_uid;
			$data['flt_oid'] = $flt_oid;
			$data['flt_balance'] = $flt_balance;
			$data['flt_type'] = $flt_type;
			$data['flt_tuserid'] = $flt_tuserid;
			$data['flt_addtime'] = date('Y-m-d H:i:s');
			$data['flt_notes'] = $flt_notes; //收入说明
			
			//增加账户余额
			$this->updateAmount($flt_uid, $flt_balance);
			return M('flTranslation')->add( $data ) ? true : false;
		}
	}
	
	//更新会员的账户金额 (只计算增加金额)
	private function updateAmount($userid, $money) {
		if($money > 0) M('flUser')->where("flu_userid=".$userid)->setInc("flu_balance", $money);
	}
	
	//如果是debug下，则把要分钱的记录列下来
	private function updateDebug( $userid, $money) {
		$this->insetbug[] = array( $userid, $money );
	}
	
}