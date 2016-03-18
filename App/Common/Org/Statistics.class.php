<?php
/**
 * 温馨提示：此统计尽量不要使用，太浪费资源！ 各个表之间设计的不太合理，查询统计太慢！
 * 固此统计只做为一个简单测试版，以后要好好优化
 *
 * 2015-09-20
 * @Author Aylan
 */
namespace Common\org;

class Statistics {
	private $_EchartsProvinceList = array('110000'=>array('name'=>'北京','value'=>0), '120000'=>array('name'=>'天津','value'=>0), '310000'=>array('name'=>'上海','value'=>0), '500000'=>array('name'=>'重庆','value'=>0), '130000'=>array('name'=>'河北','value'=>0), '410000'=>array('name'=>'河南','value'=>0), '530000'=>array('name'=>'云南','value'=>0), '210000'=>array('name'=>'辽宁','value'=>0), '230000'=>array('name'=>'黑龙江','value'=>0), '430000'=>array('name'=>'湖南','value'=>0), '340000'=>array('name'=>'安徽','value'=>0), '370000'=>array('name'=>'山东','value'=>0), '650000'=>array('name'=>'新疆','value'=>0), '320000'=>array('name'=>'江苏','value'=>0), '330000'=>array('name'=>'浙江','value'=>0), '360000'=>array('name'=>'江西','value'=>0), '420000'=>array('name'=>'湖北','value'=>0), '450000'=>array('name'=>'广西','value'=>0), '620000'=>array('name'=>'甘肃','value'=>0), '140000'=>array('name'=>'山西','value'=>0), '150000'=>array('name'=>'内蒙古','value'=>0), '610000'=>array('name'=>'陕西','value'=>0), '220000'=>array('name'=>'吉林','value'=>0), '350000'=>array('name'=>'福建','value'=>0), '520000'=>array('name'=>'贵州','value'=>0), '440000'=>array('name'=>'广东','value'=>0), '630000'=>array('name'=>'青海','value'=>0), '540000'=>array('name'=>'西藏','value'=>0), '510000'=>array('name'=>'四川','value'=>0), '640000'=>array('name'=>'宁夏','value'=>0), '460000'=>array('name'=>'海南','value'=>0), '999999'=>array('name'=>'台湾','value'=>0), '999998'=>array('name'=>'香港','value'=>0), '999997'=>array('name'=>'澳门','value'=>0));
	
	//用户分布图(代理商、商家、会员全省分布，注：会员以所属商家地域，即会员所属的商家在哪个省，会员就属于哪个省)
	/**
	 * $config['agentid'] = 0 | 指定代理下的统计
	 * $config['merchantid'] = 0 | 指定商家下的统计，注：此时不返回代理商数量
	 * @param  array
	 * @return array
	 */
	public function getDistributionInfo( $config=array('agentid'=>0, 'merchantid'=>0	) )
	{
		$Rdistribution = array();
		if( !$config['merchantid'] ) $Rdistribution['agent'] = $this->getAgentByProince( $config['agentid'] );

		$Rdistribution['merchant'] = $this->getMerchantByProince( $config['agentid'], $config['merchantid'] );
		$Rdistribution['member'] = $this->getMemberByProince( $config['merchantid'] );
		return $Rdistribution;
	}

	//获取代理商的全省数量
	private function getAgentByProince( $agentid=0 )
	{
		$where['_string'] = "m.mstatus!='-1'";
		if( $agentid ) $where['a.id'] = array("in", $this->_getAgentId($agentid));

		$_RagentList = $this->_EchartsProvinceList;
		$_AgentList = M("agent")->alias("AS a")->join("__MEMBER__ AS m ON a.mid=m.mid")->where( $where )->field("a.id,a.aid")->select();

		foreach($this->_getProvinceByAid( $_AgentList ) as $a)
		{
			$_RagentList[ $a['id'] ]['value'] += 1;
		}

		return $_RagentList;
	}

	//获取商家的全省数量
	private function getMerchantByProince( $agentid=0, $merchantid=0 )
	{
		$where['_string'] = "m.mstatus!='-1'";
		if( $agentid ) $where['j.magent'] = array("in", $this->_getAgentId($agentid));

		$_RmerchantList = $this->_EchartsProvinceList;
		$_MerchantList = M("merchant")->alias("AS j")->join("__MEMBER__ AS m ON j.mid=m.mid")->where( $where )->field("j.jid,j.mcity as aid")->select();

		foreach($this->_getProvinceByAid( $_MerchantList ) as $a)
		{
			$_RmerchantList[ $a['id'] ]['value'] += 1;
		}

		return $_RmerchantList;
	}

	//获取会员的各省数量
	private function getMemberByProince( $merchantid=0 )
	{
		$where['_string'] = "j.mcity <> ''";
		if( $merchantid ) $where['u.u_jid'] = $merchantid;

		$_RuserList = $this->_EchartsProvinceList;
		$_UserList = M("user")->alias("AS u")->join("__MERCHANT__ AS j ON u.u_jid=j.jid", "left")->where( $where )->field("j.jid,j.mcity as aid")->select();

		foreach($this->_getProvinceByAid( $_UserList ) as $a)
		{
			$_RuserList[ $a['id'] ]['value'] += 1;
		}

		return $_RuserList;
	}

	//获取指定代理下的所有的子代理
	private function _getAgentId( $agentid, $bool=false ) {
		static $cagent_list_id = array();

		$where['a.pid'] = $agentid;
		if( !$bool ) $where['_string'] = "m.mstatus!='-1'";

		$cagent_list = M("agent")->alias("AS a")->join("__MEMBER__ AS m ON a.mid=m.mid")->where( $where )->field("id")->select();
		if( is_array($cagent_list) && !empty($cagent_list) ) {
			foreach($cagent_list as $c) {
				$cagent_list_id[] = $c['id']; $this->_getAgentId($c['id']);
			}
		}

		return $cagent_list_id;
	}

	//根据一个 aid( 即  azd_address 表主键 )求，所在省份，统计所在区域以省为单位,并转化为 ECharts 所识别省
	private function _getProvinceByAid( $aid=array() )
	{
		header("Content-type:text/html; charset=utf-8");
		$AddressList = F('AddressList');
		if( !is_array($this->_AddressList) || empty($this->_AddressList) ) B('Common\Behavior\CreateAddress', '', $_AddressList);
		
		$_RaddressList = array();
		foreach( (array)$aid as $_aid ):
			$_tmp_aid = $_aid['aid'];
			do {
				if( isset($AddressList[$_tmp_aid]) && $AddressList[$_tmp_aid]['apid'] != '0' ) {
					$_tmp_aid = $AddressList[ $_tmp_aid ]['apid'];
				} else break;
			} while( true );
			$_RaddressList[] = array('id'=> $_tmp_aid, 'name'=>$this->_EchartsProvinceList[ $_tmp_aid ]['name']);
		endforeach;

		return $_RaddressList;
	}


}
