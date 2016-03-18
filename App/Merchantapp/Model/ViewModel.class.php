<?php
namespace Merchantapp\Model;

class ViewModel extends \Think\Model\ViewModel {
	public $_viewFields = array(
		'userlogin' 	=> array(  
			'Merchant'	 	=> array('jid', 'mid', 'mnickname', 'mabbreviation', 'mlpname', 'mlptel', 'mcity', '_table'=>'__MERCHANT__', '_type'=>'INNER'),
			'Member' 		=> array('mstatus', 'mregdate', 'mname', 'mpwd', 'mtype', '_on'=>'Merchant.mid=Member.mid', '_type'=>'LEFT'),  
			'MerchantUser'  => array('tsid', 'type', '_on'=>'Member.mid=MerchantUser.tmid'), 
		)
	); 

	public function view($view) {
		if( isset($this->_viewFields[$view]) ) $this->viewFields = $this->_viewFields[$view];
		return $this;
	}
	
}
?>