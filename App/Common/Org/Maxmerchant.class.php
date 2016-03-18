<?php
namespace Common\Org;

class Maxmerchant {
	
	//在审核或解禁通过的时候，用于减少父代理商的可开数
	public static function SetMaxDec(int $agentid ) 
	{
		if( !$agentid || $agentid<= 0 ) return false;
		
		$agentinfo = M("agent")->where("id=".$agentid)->find();
		if( !is_array($agentinfo) || empty($agentinfo) ) return false;
		
		if( $agentinfo['pid']==0 ) return true;//如果父ID为0，说明是系统来添加的

		//查出父代理
		$pagentid = self::GetPagentid( $agentinfo['pid'] );
		$pagentinfo = M("agent")->where("id=".$pagentid)->find();
		
		$p_maxnum = $pagentinfo['maxnum'] >=0 ? intval( $pagentinfo['maxnum'] ) : 0;
		$p_overnum = $pagentinfo['overnum'] >=0 ? intval( $pagentinfo['overnum'] ) : 0;
		$_maxnum = $agentinfo['maxnum'] >= 0 ? intval( $agentinfo['maxnum'] ) : 0;
		
		//此代理的最大商户数太多，父代理已经不够分配
		if( ($_maxnum+$p_overnum) > $p_maxnum ) return false;

		//减少父代理的商户数
		$status = M("agent")->where("id=".$pagentid)->setInc("overnum", $_maxnum);
		return $status !== false ? true : false;
	}
	
	//在审核解禁商家的时候，用于减少代理商的可开数
	public static function SetMerchantDec( int $jid )
	{
		if( !$jid || $jid<= 0 ) return false;
		$agentid = M("merchant")->where("jid=".$jid)->getField("magent");
	
		$agentinfo = M("agent")->where("id=".$agentid)->find();
		if( !is_array($agentinfo) || empty($agentinfo) ) return false;
		
		//查出父代理
		$pagentid = self::GetPagentid( $agentinfo['id'] );
		$pagentinfo = M("agent")->where("id=".$pagentid)->find();
		
		$p_maxnum = $pagentinfo['maxnum'] >=0 ? intval( $pagentinfo['maxnum'] ) : 0;
		$p_overnum = $pagentinfo['overnum'] >=0 ? intval( $pagentinfo['overnum'] ) : 0;
		
		//如果父代理已经不够分配
		if( (1+$p_overnum) > $p_maxnum ) return false;
		
		//减少父代理的商户数
		$status = M("agent")->where("id=".$pagentid)->setInc("overnum", 1);
		return $status !== false ? true : false;
	}
	
	//禁用子代理的时候，会把子代理的可开商户数添加到父代理里
	public static function SetMaxInc( int $agentid )
	{
		if( !$agentid || $agentid<= 0 ) return false;
		
		$agentinfo = M("agent")->where("id=".$agentid)->find();
		if( !is_array($agentinfo) || empty($agentinfo) ) return false;
		
		//查出父代理
		$pagentid = self::GetPagentid( $agentinfo['pid'] );
		$pagentinfo = M("agent")->where("id=".$pagentid)->find();
		
		$p_maxnum = $pagentinfo['maxnum'] >=0 ? intval( $pagentinfo['maxnum'] ) : 0;
		$p_overnum = $pagentinfo['overnum'] >= 0 ? intval( $pagentinfo['overnum'] ) : 0;
		$n_maxnum = $agentinfo['maxnum'] >= 0 ? intval( $agentinfo['maxnum'] ) : 0;
		$n_overnum = $agentinfo['overnum'] >= 0 ? intval( $agentinfo['overnum'] ) : 0;
		
		if( ($n_maxnum-$n_overnum) >= $p_overnum ) {
			$_maxnum = $p_overnum;
		} else {
			$_maxnum = $n_maxnum-$n_overnum;
		}
		
		//增加父代理的商户数
		$status = M("agent")->where("id=".$pagentid)->setDec("overnum", $_maxnum);
		return $status !== false ? true : false;
	}
	
	//禁用商家的时候，会增加父代理的可开商户数
	public static function SetMerchantInc( int $jid )
	{
		if( !$jid || $jid<= 0 ) return false;
		$agentid = M("merchant")->where("jid=".$jid)->getField("magent");
	
		$agentinfo = M("agent")->where("id=".$agentid)->find();
		if( !is_array($agentinfo) || empty($agentinfo) ) return false;
		
		$pagentid = self::GetPagentid( $agentid );
		$pagentinfo = M("agent")->where("id=".$pagentid)->find();
		$p_maxnum = $pagentinfo['maxnum'] >=0 ? intval( $pagentinfo['maxnum'] ) : 0;
		$p_overnum = $pagentinfo['overnum'] >=0 ? intval( $pagentinfo['overnum'] ) : 0;
		
		//如果父代理已经不够分配
		if( (1+$p_overnum) > $p_maxnum ) return false;
		
		//减少父代理的商户数
		$status = M("agent")->where("id=".$pagentid)->setDec("overnum", 1);
		return $status !== false ? true : false;	
	}
	
	//判断代理商是不是还可以再添加商户
	public static function IsOpenMerchant( int $agentid )
	{
		if( !$agentid || $agentid<=0 ) return false;
		
		$agentinfo = M("agent")->where("id=".$agentid)->find();
		if( !is_array($agentinfo) || empty($agentinfo) ) return false;
		
		return $agentinfo['maxnum'] <= $agentinfo['overnum'] ? false : true;
	}	
	
	//根据代理商ID，查出最近的父代理商ID（正常状态）
	public static function GetPagentid( int $agentid )
	{
		if( !$agentid || $agentid<=0 ) return $agentid;
		
		$pagentinfo = M("agent a")->join("__MEMBER__ AS m ON a.mid=m.mid")->where("a.id=".$agentid)->field("a.id,a.pid,m.mstatus")->find();
		if( !is_array($pagentinfo) || empty($pagentinfo) || $pagentinfo['pid']==0 ) return $agentid;
		
		return $pagentinfo['mstatus']==1 ? $pagentinfo['id'] : self::GetPagentid( $pagentinfo['pid'] );
	}
	
}