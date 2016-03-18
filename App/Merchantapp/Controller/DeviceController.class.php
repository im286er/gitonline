<?php
namespace Merchantapp\Controller;

class DeviceController extends MerchantappController {
	
	//设备管理首页
	public function index() {
		//如果品牌登录并且品牌下的分店总数超过1个，则显示列表
		if( $this->type==1) {
			$shoplist = M('shop')->where("status='1' and jid=".$this->jid)->select();
			foreach($shoplist as $k=>$s) {
				$shoplist[$k]['count'] = M('router')->where('rshop='.$s['sid'])->count();	
			}
			$this->assign("shoplist", $shoplist);			
		} else {
			$sid = $this->sid ? $this->sid : $this->sidlist[0]['sid'];
			redirect('/Device/devicelist/sid/'.$sid);
		}
		$this->display();
	}
	
	//查个某个分店的设备列表
	public function devicelist() {
		//如果是品牌登录并且分店总数超过1个
		if( $this->type==1 ) {
			$this->assign('shop_count', true);
		}
		
		$sid = I('get.sid', '', 'intval');	
		if( !$sid ) E('你无权进行此操作！');
		$type = I('get.type', 1, 'intval');
		
		//查看这个分店的所有的订单
		switch($type) {
			case 1:
				$deviceList = M("router")->alias('AS r')->join('__SHOP__ AS s ON r.rshop=s.sid')->field('r.*,s.sname')->where("s.status='1' and r.rshop=".$sid)->order('r.rid desc')->limit(5)->select();
			break;
			case 2:
				$deviceList = M("router")->alias('AS r')->join('__SHOP__ AS s ON r.rshop=s.sid')->field('r.*,s.sname')->where("s.status='1' and r.rstatus=1 and r.rshop=".$sid)->order('r.rid desc')->limit(5)->select();
			break;	
			case 3:
				$deviceList = M("router")->alias('AS r')->join('__SHOP__ AS s ON r.rshop=s.sid')->field('r.*,s.sname')->where("s.status='1' and r.rstatus=0 and r.rshop=".$sid)->order('r.rid desc')->limit(5)->select();
			break;
		}

		//三个统计
		$this->assign("count_a", M('router')->where("rshop=".$sid)->count());//总设备
		$this->assign("count_w", M('router')->where("rstatus=1 and rshop=".$sid)->count());//在线总数
		$this->assign("count_x", M('router')->where("rstatus=0 and rshop=".$sid)->count());//离线总数
		
		$this->assign('sid', $sid);
		$this->assign('type', $type);
		$this->assign('deviceList', $deviceList);		
		$this->display();
	}

	//重启设备
	public function restart() {
		$rid = I('get.rid', '');
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if( $socket === false ) { exit('0'); }
		
		$result = socket_connect($socket, '120.26.89.187', 10019);
		if( $result === false )  { exit('0'); }
		
		$in  = "sz,".$rid.",,,2";
		$len = socket_write($socket, $in, strlen($in));
		socket_close($socket);
		
		exit($len==false ? $len : '0');
	}
}