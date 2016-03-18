<?php
namespace Ap\Controller;
use Think\Controller;

class PortalController extends Controller {
	private $address = "120.26.89.187";
	private $port = 10019;
	
	public function check(){
		$deviceid = I('get.deviceid', '') or die('参数出错');
		$mac	  = I('get.mac') or die('参数出错');
	
		//如果服务器上，有这个用户，并且在这个范围内，直接通过
		$info = M("routerLogin")->where( array("rid"=>$deviceid, "mac"=>$mac) )->find();
		$rinf = M("router")->where( array("rcode"=>$deviceid) )->getField('norsysver');
		if( ( is_array($info) && $info['time']-time() > 0 ) || ( !empty($rinf) && stripos($rinf, $mac)!==false )  ) {
			die('1');
		}else{
			die('0');
		}
	}
	//打开认证页面
	public function openportal() {
		$deviceid = I('get.deviceid', '') or die('参数出错');
		$mac	  = I('get.mac') or die('参数出错');
		$rip	  = I('get.ipaddr') or die('参数出错');
		
		//如果服务器上，有这个用户，并且在这个范围内，直接通过
		$info = M("routerLogin")->where( array("rid"=>$deviceid, "mac"=>$mac) )->find();
		$rinf = M("router")->where( array("rcode"=>$deviceid) )->getField('norsysver');

/*
		if( ( is_array($info) && $info['time']-time() > 0 ) || ( !empty($rinf) && stripos($rinf, $mac)!==false )  ) {
			$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
			$ret = $client->connect($this->address, $this->port, 0.3, 0);
			$time= ceil( ($info['time']-time()) / 60 );
			$status = $client->send( "sz,".$deviceid.",".$rip.",".$mac.",1,".$time );
			if( $status ) {
				$ld = I('get.ld');
				if( !$ld ) {
					exit('<script type="text/javascript">window.close();</script>');
				} else {
					if(stripos($ld, 'generate_204') === false){
						header("Location:".$ld);exit;
					}else{
						$jid = M("router")->where( array("rcode"=>$deviceid) )->getField('rmerchant');
						if($jid==0){$jid=70;}
						header("Location:".U('Index/index@yd',array('jid'=>$jid)));exit;
					}
				}
			}
		}*/
		
		$router = M('router')->where(array('rcode'=>$deviceid))->field("ragent,rmerchant,rshop,rstatus,rwebtype")->find();
		if($router['rstatus'] != 1) M('router')->where(array('rcode'=>$deviceid))->setField('rstatus', '1');
		//if( !is_array($router) || empty($router) ) $this->redirect("/Index/index@yd");	
		
		//如果设备还没有分配商家，则默认是 帝鼠OS
		if( $router['ragent']==0 ) $router['ragent'] = 115;
		if( $router['rmerchant']==0 ) $router['rmerchant'] = 70;
		if( $router['rshop']==0 ) $router['rshop'] = 90;
			
		//如果存在商家ID，则把认证页面中的图片修改一下
		$jid = $router['rmerchant'] ? $router['rmerchant'] : '';
		$sid = $router['rshop'] ? $router['rshop'] : '';
		
		if( $jid && $sid ) {
			$banner = M('banner')->where('jid='.$jid)->order("bid DESC")->getField('bimg');	
			$this->assign('banner', $banner ? $banner : '');

			$appicon = M('merchantApp')->where('jid='.$jid)->getField('applogo');
			$this->assign('appicon', $appicon);
			
			$jianame = M('merchant')->where('jid='.$jid)->getField('mnickname');
			$this->assign('jianame', $jianame);
			
			$shopname = M('shop')->where(array("status"=>"1", "sid"=>$sid))->getField('sname');
			$this->assign('sname', $shopname);
			
			$active_list = M('active')->where( array('av_jid'=>$jid, 'av_status'=>1) )->order('av_id asc')->find();
			$this->assign('activelist', $active_list);
			
			$coupon_list = M('voucher')->where(array('vu_jid'=>$jid, 'vu_status'=>1))->order('vu_id desc')->find();
			$this->assign('couponinfo', $coupon_list);
			$this->assign('jid', $jid);	
		}
		
		$template = $router['rwebtype'] ? "portal_".$router['rwebtype'] : "portal_1";
		$this->display($template);
	}
	
	//设置用户认证通过
	public function setportal() {
		$rid 	= I('get.rid') or die('0');
		$rip 	= I('get.rip') or die('0');
		$mac 	= I('get.mac') or die('0');
		
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
		$ret = $client->connect($this->address, $this->port, 0.3, 0);
		$status = $client->send( "sz,".$rid.",".$rip.",".$mac.",1,600" );

		//如果谁成功，则把 mac 记下来
		if( $status ) {
			M("routerLogin")->add( array("rid"=>$rid, "mac"=>$mac, "time"=>time()+36000), "", true ); 
		}

		exit($status ? "ok" : "0" );
	}
	
	//重启
	public function obstart() {
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
		$ret = $client->connect($this->address, $this->port, 0.3, 0);
		
		$string  = "sz,".$_GET['rid'].",,,2";
		return $client->send( $string ) ? true : false;
	}
	
	//保存用户的信息
	public function setinfo() {
		array_walk($_POST, function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
		M('routerUser')->add($_POST);
	}
	
	public function ftpServer(){
		/*
		//创建websocket服务器对象，监听0.0.0.0:9502端口
		$ws = new \swoole_websocket_server("0.0.0.0", 9502);

		//监听WebSocket连接打开事件
		$ws->on('open', function ($ws, $request) {
    		var_dump($request->fd, $request->get, $request->server);
    		$ws->push($request->fd, "hello, welcome\n");
		});

		//监听WebSocket消息事件
		$ws->on('message', function ($ws, $frame) {
    		echo "Message: {$frame->data}\n";
    		$ws->push($frame->fd, "server: {$frame->data}");
		});

		//监听WebSocket连接关闭事件
		$ws->on('close', function ($ws, $fd) {
    		echo "client-{$fd} is closed\n";
		});

		$ws->start();*/
		phpinfo();
	}
}