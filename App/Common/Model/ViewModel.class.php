<?php
namespace Common\Model;

class ViewModel extends \Think\Model\ViewModel {
	public $_viewFields = array(
		'merchant' 	=> array(  
			'Merchant'	 	=> array('jid', 'mid', 'mnickname', 'mabbreviation', 'mlpname', 'mlptel', 'mcity', '_type'=>'INNER'),
			'Member' 		=> array('mstatus', 'mregdate','mname','mpwd','mbdzh', '_on'=>'Merchant.mid=Member.mid', '_type'=>'LEFT'),  
			'Agent' 		=> array('anickname', '_on'=>'Merchant.magent=Agent.id', '_type'=>'LEFT'), 
			'Vocation' 		=> array('v_title'=>'vname', '_on'=>'Merchant.vid=Vocation.v_id')     
		),
		
		'emerchant'	=> array(
			'Merchant'		=> array('*', '_type'=>'INNER'),
			'Member' 		=> array('mbdzh', 'idcard','mregdate', '_on'=>'Merchant.mid=Member.mid', '_type'=>'LEFT'),
			'MerchantApp' 	=> array('*', '_on'=>'Merchant.jid=MerchantApp.jid'),
			'Agent'			=> array('anickname', '_on'=>'Merchant.magent=Agent.id', '_type'=>'LEFT'),
			'Vocation' 		=> array('v_title'=>'vname', '_on'=>'Merchant.vid=Vocation.v_id')
		),
		
		'shop'	  	=> array(
			'Shop' 			=> array('*', '_type'=>'INNER'),
			'Merchant' 		=> array('mnickname', 'mcity', '_on'=>'Shop.jid=Merchant.jid', '_type'=>'INNER'),
	        //'Merchant_user'	=> array('tmid', '_on'=>'Shop.sid=Merchant_user.tsid','_type'=>'INNER'),
			//'Member'		=> array('mstatus', 'mregdate','mname', '_on'=>'Merchant_user.tmid=Member.mid','_type'=>'LEFT')   
		),
		
		'staffer' 	=> array(
			'Member' 		=> array('mid','mname', 'gid', 'mregdate', 'mstatus', 'mtype', 'msurname', 'mphone', '_type'=>'LEFT'),
			'AuthGroup' 	=> array('title', '_on'=>'Member.gid=AuthGroup.id')
		),
		
		'user'	  	=> array( 
			'User' 			=> array('*', '_type'=>'INNER'),
			'Merchant' 		=> array('mnickname', '_on'=>'User.u_jid=Merchant.jid'), 
			'Agent'			=> array('anickname', '_on'=>'Merchant.magent=Agent.id', '_type'=>'LEFT'),
		),
		
		//V会员
		'vuser'		=> array(
			'User'			=> array('flu_userid', 'flu_avatar', 'flu_nickname', 'flu_username', 'flu_phone', 'flu_usertype', 'flu_gjid', 'flu_source', 'flu_regtime', 'flu_lasttime', 'flu_lastip', 'flu_balance', '_table'=>'__FL_USER__', '_type'=>'LEFT'),
			'Merchant' 		=> array('mnickname', '_on'=>'User.flu_sjid=Merchant.jid', '_type'=>'LEFT'), 
			'Agent'			=> array('anickname', '_on'=>'User.flu_sagentid=Agent.id'),	
		),
		
		'applist'	=> array(
			'MerchantApp'	=> array('jid', 'android_downloads', 'ios_downloads', 'status', 'endmakedate', 'appurl', '_type'=>'INNER'),
			'Merchant'		=> array('mnickname', 'mcity', '_on'=>'MerchantApp.jid=Merchant.jid', '_type'=>'LEFT'),
			'Member'		=> array('mregdate', 'mtype', '_on'=>'Merchant.mid=Member.mid')
		),
		
		'order'		=> array(
			'Orders'		=> array('o_id', 'o_name', 'o_phone', 'o_dstime','o_pway', 'o_price', 'o_dstatus', 'o_pstatus', 'o_close', 'o_type', '_table'=> "__ORDER__", '_type'=>'INNER'),
			'Merchant'		=> array('mnickname'=>'j_name', '_on'=>'Orders.o_jid=Merchant.jid', '_type'=>'LEFT'),
			'Shop'			=> array('sname'=>'s_name', '_on'=>'Orders.o_sid=Shop.sid', '_type'=>'INNER')
		),
		
		'vorder'		=> array(
			'Orders'		=> array('flo_id', 'flo_gtype', 'flo_dstime', 'flo_price', 'flo_backprice', 'flo_dstatus', 'flo_pstatus', '_table'=> "__FL_ORDER__", '_type'=>'LEFT'),
			'Merchant'		=> array('mnickname', '_on'=>'Orders.flo_jid=Merchant.jid', '_type'=>'LEFT'),
			'User'			=> array('flu_nickname', 'flu_phone', '_on'=>'Orders.flo_uid=User.flu_userid', '_table'=>'__FL_USER__', '_type'=>'LEFT'),
			'Receiving'		=> array('flr_name', 'flr_phone', '_on'=>'Orders.flo_receivingid=Receiving.flr_receivingid', '_table'=>'__FL_RECEIVING__'),
		),
		
		'push'		=> array(
			'PushContent'	=> array('*', '_type'=>'INNER'),
			'Member'		=> array('mtype','mname', '_on'=>'PushContent.pmid=Member.mid', '_type'=>'LEFT'),
			'Merchant'		=> array('mnickname', '_on'=>'Member.mid=Merchant.mid', '_type'=>'LEFT'), 
			'Agent'			=> array('anickname', '_on'=>'Member.mid=Agent.mid')
		),

		'opinion'	=> array(
			'Opinion'		=> array('op_type', 'op_id', 'op_replytxt', 'op_content', 'op_addtime', '_type'=>'LEFT'),
			'User'          => array('*', '_on'=>'Opinion.op_uid=User.u_id', '_type'=>'LEFT'),
			'Merchant'		=> array('mnickname', '_on'=>'Opinion.op_jid=Merchant.jid', '_type'=>'LEFT'),
             'Shop'			=> array('sname', '_on'=>'Opinion.op_sid=Shop.sid')
		),
		
		'opinfo'	=> array(
			'Opinion'		=> array('op_type', 'op_id', 'op_replytxt', 'op_content', 'op_addtime', '_type'=>'LEFT'),
			'User'          => array('*', '_on'=>'Opinion.op_uid=User.u_id', '_type'=>'LEFT'),
			'Merchant'		=> array('mnickname', '_on'=>'Opinion.op_jid=Merchant.jid', '_type'=>'LEFT'),
             'Shop'			=> array('sname', '_on'=>'Opinion.op_sid=Shop.sid')
			
		)
		

		
	); 

	public function view($view) {
		if( isset($this->_viewFields[$view]) ) $this->viewFields = $this->_viewFields[$view];
		return $this;
	}
	
}
?>