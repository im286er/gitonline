<?php
namespace Merchantapp\Controller;

class MonitorController extends MerchantappController {
	
	//订单首页
	public function index() {
		//如果品牌登录并且品牌下的分店总数超过1个，则显示列表
		if( $this->type==1 ) { 
			$shoplist = M('shop')->where("status='1' and jid=".$this->jid)->select();
			foreach($shoplist as $k=>$s)
			{
				$shoplist[$k]['countnum'] = M('router')->where("rshop=".$s['sid'])->count();	
			}			
			$this->assign("shoplist", $shoplist);			
		} else {
			$sid = $this->sid ? $this->sid : $this->sidlist[0];
			redirect('/Monitor/monitorinfo/sid/'.$sid);
		}
		$this->display();
	}
	
	//数据统计，以后要换成其它方式，现在的效率太低
	public function monitorinfo() {
		$sid = I('get.sid', 0, 'intval');
		
		$sname = M('shop')->where(array('sid'=>$sid))->getField('sname');
		//先获取当前门店的所有设备ID
		$rcode = M('router')->where("rshop=".$sid)->field('rcode')->select();	
		$rcodeList = array(); foreach($rcode as $r) $rcodeList[] = $r['rcode'];
		$rcodestr = implode("','", $rcodeList);
		
		//今日客流总量
		$time_to = date('Y-m-d H:i:s', mktime(date('H')-4, 0, 0, date('m'), date('d'), date('y')));
		$time_tt = date('Y-m-d H:i:s', mktime(date('H')-3, 0, 0, date('m'), date('d'), date('y'))); 
		$time_ts = date('Y-m-d H:i:s', mktime(date('H')-2, 0, 0, date('m'), date('d'), date('y'))); 
		$time_tf = date('Y-m-d H:i:s', mktime(date('H')-1, 0, 0, date('m'), date('d'), date('y'))); 
		$time_ti = date('Y-m-d H:i:s', mktime(date('H')-0, 0, 0, date('m'), date('d'), date('y'))); 
		$time_tocon = M('routerUser')->where("`rlast`>='{$time_to}' and `rlast`<='{$time_tt}' and `rcode` in ('{$rcodestr}')")->count();
		$time_ttcon = M('routerUser')->where("`rlast`>='{$time_tt}' and `rlast`<='{$time_ts}' and `rcode` in ('{$rcodestr}')")->count();
		$time_tscon = M('routerUser')->where("`rlast`>='{$time_ts}' and `rlast`<='{$time_tf}' and `rcode` in ('{$rcodestr}')")->count();
		$time_tfcon = M('routerUser')->where("`rlast`>='{$time_tf}' and `rlast`<='{$time_ti}' and `rcode` in ('{$rcodestr}')")->count();
		$time_ticon = M('routerUser')->where("`rlast`>='{$time_ti}' and `rcode` in ('{$rcodestr}')")->count();
		$this->assign('nowcon', array(date('H')-4, date('H')-3, date('H')-2, date('H')-1, date('H'), $time_tocon, $time_ttcon, $time_tscon, $time_tfcon, $time_ticon));
		$this->assign('nowmax', array_sum( array($time_tocon, $time_ttcon, $time_tscon, $time_tfcon, $time_ticon) ));
		
		//昨日客流量
		$time_yo = date('Y-m-d H:i:s', mktime(date('H')-4, 0, 0, date('m'), date('d')-1, date('y')));
		$time_yt = date('Y-m-d H:i:s', mktime(date('H')-3, 0, 0, date('m'), date('d')-1, date('y'))); 
		$time_ys = date('Y-m-d H:i:s', mktime(date('H')-2, 0, 0, date('m'), date('d')-1, date('y'))); 
		$time_yf = date('Y-m-d H:i:s', mktime(date('H')-1, 0, 0, date('m'), date('d')-1, date('y'))); 
		$time_yi = date('Y-m-d H:i:s', mktime(date('H')-0, 0, 0, date('m'), date('d')-1, date('y'))); 
		$yess_tocon = M('routerUser')->where("`rlast`>='{$time_yo}' and `rlast`<='{$time_yt}' and `rcode` in ('{$rcodestr}')")->count();
		$yess_ttcon = M('routerUser')->where("`rlast`>='{$time_yt}' and `rlast`<='{$time_ys}' and `rcode` in ('{$rcodestr}')")->count();
		$yess_tscon = M('routerUser')->where("`rlast`>='{$time_ys}' and `rlast`<='{$time_yf}' and `rcode` in ('{$rcodestr}')")->count();
		$yess_tfcon = M('routerUser')->where("`rlast`>='{$time_yf}' and `rlast`<='{$time_yi}' and `rcode` in ('{$rcodestr}')")->count();
		$yess_ticon = M('routerUser')->where("`rlast`>='{$time_yi}' and `rcode` in ('{$rcodestr}')")->count();
		$this->assign('yescon', array(date('H')-4, date('H')-3, date('H')-2, date('H')-1, date('H'), $yess_tocon, $yess_ttcon, $yess_tscon, $yess_tfcon, $yess_ticon));
		$this->assign('yesnum', $yess_tocon+$yess_ttcon+$yess_tscon+$yess_tfcon+$yess_ticon);
				
		$yeso_tocon = M('routerUser')->where("`rlast`>='{$time_yo}' and `rlast`<='{$time_yt}' and `rcode` in ('{$rcodestr}')")->group('ruserip')->count();
		$yeso_ttcon = M('routerUser')->where("`rlast`>='{$time_yt}' and `rlast`<='{$time_ys}' and `rcode` in ('{$rcodestr}')")->group('ruserip')->count();
		$yeso_tscon = M('routerUser')->where("`rlast`>='{$time_ys}' and `rlast`<='{$time_yf}' and `rcode` in ('{$rcodestr}')")->group('ruserip')->count();
		$yeso_tfcon = M('routerUser')->where("`rlast`>='{$time_yf}' and `rlast`<='{$time_yi}' and `rcode` in ('{$rcodestr}')")->group('ruserip')->count();
		$yeso_ticon = M('routerUser')->where("`rlast`>='{$time_yi}' and `rcode` in ('{$rcodestr}')")->group('ruserip')->count();
		$this->assign('yeocon', array(date('H')-4, date('H')-3, date('H')-2, date('H')-1, date('H'), $yeso_tocon, $yeso_ttcon, $yeso_tscon, $yeso_tfcon, $yeso_ticon));
		$this->assign('yeonum', $yeso_tocon+$yeso_ttcon+$yeso_tscon+$yeso_tfcon+$yeso_ticon);
	
		$first_day = M('routerUser')->where("`rcode` in ('{$rcodestr}')")->order('rlast')->getField('rlast');
		$daynum = intval( ceil(abs(strtotime($first_day) - time())/86400) / 5 ) ;

		$time = strtotime($first_day." +".($daynum * 1). " day");
		$time_yo = date('Y-m-d H:i:s', mktime(date('H', $time), 0, 0, date('m', $time), date('d', $time), date('y', $time)));
		$time = strtotime($first_day." +".($daynum * 2). " day");
		$time_yt = date('Y-m-d H:i:s', mktime(date('H', $time), 0, 0, date('m', $time), date('d', $time), date('y', $time)));
		$time = strtotime($first_day." +".($daynum * 3). " day");
		$time_ys = date('Y-m-d H:i:s', mktime(date('H', $time), 0, 0, date('m', $time), date('d', $time), date('y', $time)));
		$time = strtotime($first_day." +".($daynum * 4). " day");
		$time_yf = date('Y-m-d H:i:s', mktime(date('H', $time), 0, 0, date('m', $time), date('d', $time), date('y', $time)));
		$time = strtotime($first_day." +".($daynum * 5). " day");
		$time_yi = date('Y-m-d H:i:s', mktime(date('H', $time), 0, 0, date('m', $time), date('d', $time), date('y', $time)));
		$yeso_tocon = M('routerUser')->where("`rlast`>='{$time_yo}' and `rlast`<='{$time_yt}' and `rcode` in ('{$rcodestr}')")->count();
		$yeso_ttcon = M('routerUser')->where("`rlast`>='{$time_yt}' and `rlast`<='{$time_ys}' and `rcode` in ('{$rcodestr}')")->count();
		$yeso_tscon = M('routerUser')->where("`rlast`>='{$time_ys}' and `rlast`<='{$time_yf}' and `rcode` in ('{$rcodestr}')")->count();
		$yeso_tfcon = M('routerUser')->where("`rlast`>='{$time_yf}' and `rlast`<='{$time_yi}' and `rcode` in ('{$rcodestr}')")->count();
		$yeso_ticon = M('routerUser')->where("`rlast`>='{$time_yi}' and `rcode` in ('{$rcodestr}')")->group('ruserip')->count();
		$this->assign('countnum', array(date('m/d', strtotime($time_yo)), date('m/d', strtotime($time_yt)), date('m/d', strtotime($time_ys)), date('m/d', strtotime($time_yf)), date('m/d', strtotime($time_yi)), $yeso_tocon, $yeso_ttcon, $yeso_tscon, $yeso_tfcon, $yeso_ticon));
		$this->assign('counnum', $yeso_tocon+$yeso_ttcon+$yeso_tscon+$yeso_tfcon+$yeso_ticon);
		
		$this->assign('sname',$sname);
		$this->display();	
	}

}