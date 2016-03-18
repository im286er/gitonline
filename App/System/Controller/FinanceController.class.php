<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class FinanceController extends ManagerController
{
	private $agent_list = array();

	//代理商财务
	public function aFinanceList()
	{
		$this->agent_list = M('agent')->alias('AS a')->join('__MEMBER__ AS m ON a.mid=m.mid')->where("m.mstatus<>-1 AND m.mtype=1")->select();

		$page = new \Think\Page(count($this->agent_list), 16);
		$agentsList = M('agent')->field( 'a.id,a.mid,a.arank,a.anickname,a.pid' )->alias('AS a')->where( $where )->join('__MEMBER__ AS m ON a.mid=m.mid')->limit($page->firstRow.','.$page->listRows)->order('a.id DESC')->select();
		if( is_array($agentsList) && !empty($agentsList) )
		{
			foreach($agentsList as &$a)
			{
				//计算数据 cqg
				$a['c'] = count( $this->_GetAgentChildTotal( $a['id'], 'c', true ) );
				$a['q'] = count( $this->_GetAgentChildTotal( $a['id'], 'q', true ) );
				$a['g'] = count( $this->_GetAgentChildTotal( $a['id'], 'g', true ) );

				$a['s'] = count( $this->_GetMerchantTotal( $a['id'] ) );
				$a['u'] = $this->_GetMemberTotal( $a['id'] );
			}
		}

		$this->assign('agentsList', $agentsList);
		$this->assign('aranksList', array('p'=>'省', 'c'=>'市', 'q'=>'区', 'g'=>'个') );
        $this->assign('pages', $page->show());

		$this->display();
	}

	//获取子代理的数量
	private function _GetAgentChildTotal( $agentid=0, $type='p', $bool=false )
	{
		if( !$agentid || !is_array($this->agent_list) || empty($this->agent_list) ) return false;
		
		static $total_num = array();
		if( $bool ) $total_num = array();

		foreach($this->agent_list as $a)
		{
			if( $a['pid']==$agentid )
			{
				if($a['arank']==$type) $total_num[] = $a['id'];
				$this->_GetAgentChildTotal( $a['id'], $type, false );
			}
		}

		return array_unique($total_num);
	}

	//获取代理商下的商家总数
	private function _GetMerchantTotal( $agentid=0 )
	{
		if( !$agentid ) return 0;
		$agent_list = array_merge( array( $agentid ), $this->_GetAgentChildTotal( $agentid, 'c', true ), $this->_GetAgentChildTotal( $agentid, 'q', true ), $this->_GetAgentChildTotal( $agentid, 'g', true ) );

		$where = array('_string'=>'m.mstatus<>-1 AND m.mtype=2', 'magent'=>array('in', $agent_list));
		$merchant_list = M('merchant')->alias('AS j')->join('__MEMBER__ AS m ON j.mid=m.mid')->where( $where )->select();

		$return_merchant_id = array();
		foreach($merchant_list as $m) $return_merchant_id[] = $m['jid'];
		return $return_merchant_id;
	}

	//获取代理商下的会员总数
	private function _GetMemberTotal( $agentid=0 )
	{
		if( !$agentid ) return 0;
		$merchant_list = $this->_GetMerchantTotal( $agentid );

		return M('user')->where( array('u_jid'=>array('in', $merchant_list)) )->count();
	}

	//获取商家的收入信息
	private function _GetMerchantInfo( $merchantid=0 )
	{

	}





	//商家财务
	


	//会员财务
	
}