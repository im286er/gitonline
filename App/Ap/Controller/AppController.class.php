<?php
namespace Ap\Controller;
use Think\Controller;
header("Content-type: text/html; charset=utf-8");
//header("Content-type:text/json");
class AppController extends Controller {
	protected $appid = 'azk';
	protected $appsecret = '57fed7593c362';
	
	/*
	protected function _initialize(){
		$key = I('get.key');
		$expiretime = intval(I('get.expiretime'));
		if(!$expiretime && !$key)exit(JSON(array('errcode'=>'40002','errmsg'=>'授权参数错误')));
		if($expiretime < time())exit(JSON(array('errcode'=>'40003','errmsg'=>'授权已过期')));
		$authkey = md5($this->appid.$this->appsecret.$expiretime);
		if($key!=$authkey)exit(JSON(array('errcode'=>'40004','errmsg'=>'密匙口令错误')));
    }
	*/
	
	//商户地图列表
	public function MerchantMapLists() {
		$longitude=I('get.longitude');
		$latitude=I('get.latitude');
		$distance=I('get.distance');
		$datalist = D('shop')->alias('AS shop')->field('shop.jid,merchant.mid,shop.sid,shop.sname,shop.mservetel,shop.saddress,shop.lng,shop.lat,merchant.mnickname,merchant.vid',false)->where('shop.lng!="" AND shop.lat!="" AND shop.status = "1"')->join("__MERCHANT__ AS merchant on shop.jid=merchant.jid", 'left')->select();
		$mlist = $mids = array();
		if($datalist){
			$mids = array_column($datalist,'mid');
			$mlist = D('member')->where(array('mstatus'=>1,'mid'=>array('in',$mids)))->getField('mid,mstatus');
		}



		$vocation_list = D("vocation")->select();
		if($longitude && $latitude && $datalist){
			foreach($datalist as $key => $value){ 
				$apart = GetDistance($latitude,$longitude,$value['lat'],$value['lng']);
				$datalist[$key]['distance'] = $apart;
				
			}
			$datalist = array_sort($datalist,'distance','asc');
		}
		if($datalist){
			$data_list  = array();
			foreach($datalist as $value) {
				if(!$mlist[$value['mid']])continue;
				$value['activity'] = M('Active')->where('FIND_IN_SET('.$value['sid'].',av_sid) AND av_stime =< "'.date('Y-m-d H:i:s').'" AND av_etime >= "'.date('Y-m-d H:i:s').'"')->count();
				$value['voucher'] = M('Voucher')->where('FIND_IN_SET('.$value['sid'].',vu_sid) AND vu_stime < "'.date('Y-m-d H:i:s').'" AND vu_etime >= "'.date('Y-m-d H:i:s').'"')->count();
				$value['linkurl'] = U('User/merchantInfo@yd',array('merchantid'=>$value['jid'],'shopid'=>$value['sid']));
				$value['v_img']    = get_main_vocation($vocation_list,$value["vid"]);
				$value['mnickname'] = $value["sname"];
				unset($value['sid']);
				$data_list[] = $value;
			}
			$data = array();
			$data['errcode'] = 0;
			$data['errmsg'] = 'ok';
			$data['data'] = $data_list;
		}
		$data ?exit(JSON($data)) : exit(JSON(array('errcode'=>'40102','errmsg'=>'未找到相应数据')));
	}
	
	//获取商户APP的首图
	public function IMerchantFigureImage() {
		$jid = I('get.jid', 0, 'intval');
		$path = APP_DIR.'/Public/Data/'.$jid.'/FigureImage.php';
		if( file_exists($path) && $jid && file_get_contents($path) ) {
			exit( JSON(array('errcode'=>'0', 'errmsg'=>'ok', 'data'=>file_get_contents($path))));		
		} else {
			exit( JSON(array('errcode'=>'40102', 'errmsg'=>'未找到相应数据')) );
		}
	}

	//API下载分享内容
	public function ShareData() {
		$jid = I('get.jid', 0, 'intval');
		$type = I('get.type','download', 'strval');
		if(!$jid)exit( JSON(array('errcode'=>'40302', 'errmsg'=>'jid不能为空')) );
		$path = APP_DIR.'/Public/Data/'.$jid.'/ShareData.php';
		$ShareData=json_decode(file_get_contents($path),true);
		if(!$ShareData[$type]){
			$mabbreviation = M("merchant")->where(array('jid'=>$jid))->getField("mabbreviation");
			$active = M('active')->where(array('av_jid'=> $jid,'av_status' => 1))->order('av_id desc')->getField("av_title");
			$active = empty($active) ? '有趣的活动' : $active;
			$ShareData[$type]['text'] = "【好店大爆料】Duang ~我正在".$mabbreviation."，体验".$active."，现在登录/下载还有红包、抵价券哦！";
			//exit( JSON(array('errcode'=>'40303', 'errmsg'=>'未找到相应数据')) );
		}
		$shareurl = U('Index/index@yd',array('jid'=>$jid,'v'=>time()));
		$data = $ShareData[$type]['text'].$shareurl;
		exit( JSON(array('errcode'=>'0', 'errmsg'=>'ok', 'data'=>$data)));		
	}

	//获取版本信息
	public function Versions(){
		$jid = I('get.jid', 0, 'intval');
		if(!$jid)exit( JSON(array('errcode'=>'40302', 'errmsg'=>'jid不能为空')) );
		$app = M('merchant_app')->where(array('jid'=>$jid))->field('appversions,iosversions')->find(); 
		$app['downurl'] = U('Index/appdown@yd',array('jid'=>$jid));
		exit( JSON(array('errcode'=>'0', 'errmsg'=>'ok', 'data'=>$app)));
	}

	//获取用户当前版本号
	public function getUserVersion(){
		$uid = I('get.uid', 0, 'intval');
		$version = I('get.version');
		if($uid && $version)
		$result = M('user')->where(array('u_id'=>$uid))->setField('u_versions',$version);
		if( $result ) {
			exit( JSON(array('errcode'=>'0', 'errmsg'=>'ok')));		
		} else {
			exit( JSON(array('errcode'=>'40102', 'errmsg'=>'未找到相应数据')) );
		}
	}


	//活动分享
	public function  activeShare(){
		$jid = I('get.jid', 0, 'intval');
		$av_id = I('get.av_id','', 'intval');
		if(!$jid)exit( JSON(array('errcode'=>'40302', 'errmsg'=>'jid不能为空')) );
		if(!$av_id)exit( JSON(array('errcode'=>'40502', 'errmsg'=>'活动Id不能为空')) );
		$active = M('active')->where(array('av_jid'=>$jid,'av_id'=>$av_id))->field('av_id,av_title,av_con,av_jid')->find(); 
		if($active){
			$data = '【'.$active['av_title'].'】 '.U('Active/info@yd',array('jid'=>$jid,'av_id'=>$av_id)).' '.msubstr($active['av_title'],0,100);
			exit( JSON(array('errcode'=>'0', 'errmsg'=>'ok', 'data'=>$data)));
		}else exit( JSON(array('errcode'=>'40503', 'errmsg'=>'暂无该活动')) );
	}



	//首次进入APP获取推送消息
	public function firestPushMsg() {
		$_jid=I('get.jid', 0, 'intval'); $_cid=I('get.cid'); $_vid=I('get.vcid'); $_tid=I('get.todid');
		if( !$_jid || !$_cid || !$_vid ) return false;
		
		$_AutoReply = file_get_contents( APP_DIR.'/Public/Data/'.$_jid.'/AutoReply1.php' );
		if( !$_AutoReply ) return false;
		
		//看看这个表是不是可以添加，如果可以，说明是第一次, 因为 jid,cid,vid 是唯一索引
		$title = \Org\Util\String::msubstr($_AutoReply, 0, 8);
		$pid = M('pushContent')->add( array('pmid'=>0, 'ptitle'=>$title, 'pcontent'=>$_AutoReply) );
		
		\Common\Org\Cookie::set("userclientid", $_cid, 604800, "", "dishuos.com");

		if( M('pushFirst')->add(array('jid'=>$_jid, 'cid'=>$_cid, 'vid'=>$_vid, 'tid'=>$_tid )) ) {
			$info['title'] = $title;
			$info['time'] = date('Y-m-d H:i:s');
			$info['pid'] = $pid;
			$info['content'] = strip_tags($_AutoReply);
			$args = array( 'transmissionContent' => JSON($info) );
			$mesg = array( 'offlineExpireTime'=>7200, 'netWorkType'=>0 );
			\Common\Org\IGPushMsg::getIGPushMsg(true, $appinfo)->pushMessageToCid($_cid, 4, json_encode($args), json_encode($mesg));
		}
	}
}