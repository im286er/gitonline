<?php
namespace Common\Model;
use Think\Model;

class TsbindModel extends Model {
	
	/* 绑定账号
	 * 
	 * */
	public function bind($jid,$sid,$type,$clientid){
		//删除clientid现有的绑定关系
		$this->unbindBycid($clientid);
		
		$data = array();
		$data['jid']      =  $jid;
		$data['sid']      =  $type == 1 ? 0 : $sid;
		$data['clientid'] =  $clientid;
		$data['addtime']  =  date("Y-m-d H:i:s");
		$data['exptime']  =  time() + 3600 * 24 * 7;
		$r = M('tsbind')->add($data);
		return $r ? true : false;
	}
	
	/* 解除当前账户绑定
	 * 
	 * */
	public function unbind(){
		$jid  = \Common\Org\Cookie::get(C('USER_COOKIE_JID'));
		$sid  = \Common\Org\Cookie::get(C('USER_COOKIE_SID'));
		$type = \Common\Org\Cookie::get(C('USER_COOKIE_TPE'));
		if($type == 1){
			M('tsbind')->where(array('jid'=>$jid,'sid'=>0))->delete();
		}else{
			M('tsbind')->where(array('jid'=>$jid,'sid'=>$sid))->delete();
		}
		return true;
	}
	
	/*	解除某个cid的绑定
	 * 
	 * */
	public function unbindBycid($clientid){
		M('tsbind')->where(array('clientid'=>$clientid))->delete();
		return true;
	}
	
	/* 下单之后推送消息到商家app
	 * $oid  订单号
	 * */
	public function send($oid){
		//查询订单信息
		$order_info = M('order')->where(array('o_id'=>$oid))->find();
		if(empty($order_info)){
			return false;
		}
		//查询需要推送的cid
		$sql      = "select clientid from azd_tsbind where jid='".$order_info['o_jid']."' and sid in(0,'".$order_info['o_sid']."') and exptime>='".time()."'";
		$cid_list = M('tsbind')->query($sql);
		
		$app = array(
				'gt_appid' => 'SUDKkVhSnR93hWOP8g75E8',
				'gt_appkey' => 'eyMPuwA8vt6Rb1pkjx3eu3',
				'gt_appsecret' => '5SSU4AMbjy8xNybJGcxYa',
				'gt_mastersecret' => 'bfNVtN2G3P8xfys628LX68',
		);
		$info['title'] = '来了来了~有一个新订单!';
		$info['time'] = date('Y-m-d H:i:s');
		$info['imageUrl'] = '';
		$info['content'] = '';
		$args = array( 'transmissionContent' => JSON($info) );
		$mesg = array( 'offlineExpireTime'=>7200, 'netWorkType'=>0 );
		
		$a_info = array(
			'title'					=> '来了来了~有一个新订单!',
			'text'					=> '来了来了~有一个新订单!',
			'isRing'				=> true,
			'isVibrate'				=> true,
			'isClearable'			=> true,
			'transmissionType'		=> 1,
			'transmissionContent'	=> '来了来了~有一个新订单!',
		);
		
		foreach($cid_list as $k=>$v) {
			$res1 = \Common\Org\IGPushMsg::getIGPushMsg(true, $app)->pushMessageToCid($v['clientid'], 4, json_encode($args), json_encode($mesg));
			$res2 = \Common\Org\IGPushMsg::getIGPushMsg(true, $app)->pushMessageToCid($v['clientid'], 1, json_encode($a_info), json_encode($mesg));
		}
		return true;
	}
	
	public function hebing($sid){
		$cart = $_COOKIE['ProductList'];
		//提交购物车数据
		if(!empty($cart) && in_array(CONTROLLER_NAME.ACTION_NAME,array('IndexshopCart','Flowconfirm'))){		
			M('temp_cart')->where(array('tableid'=>session('table')))->delete();
			$cart_arr2 = explode('|', $cart);
			foreach($cart_arr2 as $k1=>$v1){
				if(!empty($v1)){
					$temp = explode('_', $v1);
					$opt = array(
						'tableid' => session('table'),
						'gid' => $temp[1],
						'gnumber' => $temp[2],
						'sid' => $sid,
						'add_time' => date("Y-m-d H:i:s"),
					);
					M('temp_cart')->add($opt);
				}
			}
		}else{
			//读取购物车数据
			$r = M('temp_cart')->where(array('tableid'=>session('table')))->select();
			$cc = array();
			foreach($r as $k2=>$v2){
				$str = $sid.'_'.$v2['gid'].'_'.$v2['gnumber'];
				$cc[] = $str;
			}
			$cd = join('|',$cc);
			cookie('ProductList',$cd);
			//$_COOKIE['ProductList'] = $cd;
		}
		
	}
}

?>