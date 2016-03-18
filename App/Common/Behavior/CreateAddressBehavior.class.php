<?php
namespace Common\Behavior;

class CreateAddressBehavior {
	
	public function run( &$addressList ) {
		$AddressList = array(); 
		foreach(M('address')->order('aid')->select() as $a) { 
			$AddressList[$a['aid']] = $a; 
		}
		F('AddressList', $AddressList);
		$addressList = $AddressList;
	}
		
}