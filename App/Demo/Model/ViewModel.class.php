<?php
namespace Demo\Model;

class ViewModel extends \Think\Model\ViewModel {
	public $_viewFields = array(
		'user'	  	=> array(
			'User' 			=> array('*', '_type'=>'INNER'),
			'Member'		=> array('mtype', 'mname', 'mregdate', 'mlogindate', 'mstatus', 'mphone', '_on'=>'User.mid=Member.mid', '_type'=>'LEFT'),
			'Shop' 			=> array('sname', 'jid', '_on'=>'User.usid=Shop.sid', '_type'=>'LEFT'),
		),
		'member'	  	=> array(
			'Member' 			=> array('*', '_type'=>'INNER'),
			'LoginSdk'		=> array('jid','nickname', 'avatar', 'sdkname', '_on'=>'LoginSdk.mid=Member.mid', '_type'=>'LEFT'),
		),		
		
	);

	public function view($view) {
		if( isset($this->_viewFields[$view]) ) $this->viewFields = $this->_viewFields[$view];
		return $this;
	}
	
}
?>