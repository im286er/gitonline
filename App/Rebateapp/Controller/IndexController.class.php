<?php
namespace Rebateapp\Controller;
use Think\Controller;

/*首页* * * */
class IndexController extends RebateappController {
//class IndexController extends Controller {
	public function index(){
		echo '全民返利';
	}

	/**APP设置**/
	public function setApp(){
		$config = array(
			'OAUTH_LOGIN' => 'OFF',//OFF  或者  ON
		);
		die(JSON($config));
	}

	public function icoList(){
		$coordinate = I('post.coordinate','120.127912,30.343272');
		$city = I('post.city');
		$address = M('address')->where(array('aname'=>array('like','%'.$city.'%')))->find();
			$icolist = array(
				array(
					'v_id'=>'1',
					'v_title'=>'美食',
					'v_url'=>U('Shop/shopList@flapp',array('v_id'=>1,'source'=>'home','coordinate'=>$coordinate,'city'=>$address['aid'])),
					'v_img'=>U('@yd').'/Public/Rebateapp/Images/ico/ico1.png',
					),
				array(
					'v_id'=>'2',
					'v_title'=>'旅游',
					'v_url'=>U('Shop/shopList@flapp',array('v_id'=>128,'source'=>'home','coordinate'=>$coordinate,'city'=>$address['aid'])),
					'v_img'=>U('@yd').'/Public/Rebateapp/Images/ico/ico7.png',
				),
				array(
					'v_id'=>'3',
					'v_title'=>'酒店',
					'v_url'=>U('Shop/shopList@flapp',array('v_id'=>44,'source'=>'home','coordinate'=>$coordinate,'city'=>$address['aid'])),
					'v_img'=>U('@yd').'/Public/Rebateapp/Images/ico/44.png',
				),
				array(
					'v_id'=>'4',
					'v_title'=>'充值',
					'v_url'=>U('Recharge/calls@flapp'),
					'v_img'=>U('@yd').'/Public/Rebateapp/Images/ico/ico6.png',
				),
				array(
					'v_id'=>'5',
					'v_title'=>'零食',
					'v_url'=>U('Shop/shopList@flapp',array('v_id'=>129,'source'=>'home','coordinate'=>$coordinate,'city'=>$address['aid'])),
					'v_img'=>U('@yd').'/Public/Rebateapp/Images/ico/129.png',
				),
				array(
					'v_id'=>'6',
					'v_title'=>'生活服务',
					'v_url'=>U('Shop/shopList@flapp',array('v_id'=>105,'source'=>'home','coordinate'=>$coordinate,'city'=>$address['aid'])),
					'v_img'=>U('@yd').'/Public/Rebateapp/Images/ico/105.png',
				),
				array(
					'v_id'=>'7',
					'v_title'=>'休闲',
					'v_url'=>U('Shop/shopList@flapp',array('v_id'=>23,'source'=>'home','coordinate'=>$coordinate,'city'=>$address['aid'])),
					'v_img'=>U('@yd').'/Public/Rebateapp/Images/ico/23.png',
				),
				array(
					'v_id'=>'8',
					'v_title'=>'其他',
					'v_url'=>U('Shop/shopList@flapp',array('v_id'=>124,'source'=>'home','coordinate'=>$coordinate,'city'=>$address['aid'])),
					'v_img'=>U('@yd').'/Public/Rebateapp/Images/ico/124.png',
				),
			);
	
		die(JSON($icolist));
	}


	//首页行业列表
	public function vocationList(){
		$this->icoList();
		/*
		$coordinate = I('post.coordinate','120.127912,30.343272');
		$city = I('post.city');
		$address = M('address')->where(array('aname'=>array('like','%'.$city.'%')))->find();
		$vorder = array(1=>0,21=>1,23=>2,44=>3,87=>4,69=>5,125=>6,124=>7,105=>8,54=>9,76=>10,11=>11);
		$opt = array(
			'v_pid' => 0
		);
		$vocationList = M('vocation')->where($opt)->select();
		$orders = array();
		
		foreach($vocationList as $k=>$v){
			$vocation[$vorder[$v['v_id']]]['v_id']    = $v['v_id'];
			$vocation[$vorder[$v['v_id']]]['v_title'] = $v['v_title'];
			$vocation[$vorder[$v['v_id']]]['v_img']   = U('@yd').'/Public/Rebateapp/Images/ico/'.$v['v_id'].'.png';
			$vocation[$vorder[$v['v_id']]]['v_url']   = U('Shop/shopList@flapp',array('v_id'=>$v['v_id'],'source'=>'home','coordinate'=>$coordinate,'city'=>$address['aid']));
		}
		$vocation[11]['v_title'] = '充值';
		$vocation[11]['v_img']   = U('@yd').'/Public/Rebateapp/Images/ico/0.png';
		$vocation[11]['v_url']   = U('Recharge/calls@flapp');
		ksort($vocation);
		//print_r($vocation);
		//$this->ajaxReturn($vocation);
		die(JSON($vocation));*/
	}
	
	//首页广告列表
	public function bannerList(){
		
		$banner = array();
		$opt = array(
			'btype' => '4'
		);
		$bannerList = M('banner')->where($opt)->order('bid desc')->find();
		$bannerLists[] = $bannerList;
		foreach($bannerLists as $k=>$v){
			$banner[$k]['bid']    = $v['bid'];
			$banner[$k]['btitle'] = $v['btitle'];
			$banner[$k]['burl']   = $v['burl'];
			$banner[$k]['bimg']   = U('@flapp').$v['bimg'];
		}
		
		//$this->ajaxReturn($banner);
		die(JSON($banner));
	}

	//首页商家猜你喜欢
	public function guesslike(){
		$shoplist = $appdata = $shopids = array();
		$num = 20;
		$coordinate = I('post.coordinate');
		$city = I('post.city','杭州市');
		if($coordinate){ //地理位置获取到的话
			$shopids = D('Shop')->locationStore($coordinate,0,$num);
			if(count($shopids>0))$ids = array_keys($shopids);
			//$shopdata = M('Goods')->alias('g')->join('__SHOP__ s ON g.sid= s.sid')->field('s.sid,s.sname,s.exterior,s.saddress,s.district,max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl')->group('g.sid')->where(array('s.sid'=>array('in',$ids),'g.gstatus'=>'1','s.status'=>'1','g.gvrebate'=>array('gt',0)))->order('field(s.sid,'.implode(',',$ids).')')->limit($num)->select();
			$shopdata = M('Goods')->alias('g')->join('__SHOP__ s ON g.sid= s.sid')->field('s.sid,s.sname,s.exterior,s.saddress,s.district,max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl')->group('g.sid')->where(array('s.sid'=>array('in',$ids),'g.gstatus'=>'1','s.is_show'=>'1','s.status'=>'1','s.qmstatus'=>'1'))->order('field(s.sid,'.implode(',',$ids).')')->limit($num)->select();
		}elseif($city){ //城市获取到的话
			$address = M('address')->where(array('aname'=>array('like','%'.$city.'%')))->find();
			if($address){
				$map = $where = array();
				$map['g.gstatus'] = '1';
				$map['s.status'] = '1';
				$map['s.qmstatus'] = '1';
				$where['s.province']  = $address['apid'];
				$where['s.city']  =  $address['aid'];
				$where['_logic'] = 'or';
				$map['_complex'] = $where;
				//$map['g.gvrebate'] =array('gt',0);
				$shopdata = M('Goods')->alias('g')->join('__SHOP__ s ON g.sid= s.sid')->group('g.sid')->field('s.sid,s.sname,s.exterior,s.saddress,s.district,max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl')->where($map)->order('s.city asc')->limit($num)->select();
			}
		}
		if(!$shopdata){
			//$shopdata = M('Goods')->alias('g')->join('__SHOP__ s ON g.sid= s.sid')->group('g.sid')->field('s.sid,s.sname,s.exterior,s.saddress,s.district,max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl')->where(array('s.status'=>'1','g.gstatus'=>'1','g.gvrebate'=>array('gt',0)))->order('s.sid desc')->limit($num)->select();

			$shopdata = M('Goods')->alias('g')->join('__SHOP__ s ON g.sid= s.sid')->group('g.sid')->field('s.sid,s.sname,s.exterior,s.saddress,s.district,max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl')->where(array('s.status'=>'1','g.gstatus'=>'1','s.qmstatus'=>'1','s.is_show'=>'1'))->order('s.sid desc')->limit($num)->select();
		}
		if($shopdata)foreach($shopdata as $key => $value){
			$value['sname'] = nl2br($value['sname']);
			$value['saddress'] = nl2br($value['saddress']);
			$value['distance'] = $shopids[$value['sid']]?($shopids[$value['sid']].'KM'):'';
			$value['exterior'] = $value['exterior']?U('@yd').$value['exterior']:U('@yd').'/Public/Rebateapp/Images/shop_pic.jpg';
			$value['linkurl'] = U('Shop/shopInfo@flapp',array('sid'=>$value['sid'],'superiorsource'=>'home','coordinate'=>$coordinate));
			$value['vipfl'] = '最高返现'.D('Shop')->calculateRebate($value['gfl']).'元';
			$value['generalfl'] = '最高返现'.D('Shop')->calculateRebate($value['gfl'],'general').'元';
			$shoplist[] =$value;
		}
		die(JSON($shoplist));
	}
	
	/***分享内容**/
	public function shareData() {
		$sharedata = array();
		$sharedata['errcode'] = '80011';
		$sharedata['errmsg'] = 'utoken已经失效，请重新登录获取！';
		if(I('post.utoken')){
			$result = D('FlUsertoken')->checkUtoken(I('post.utoken'));
			if(is_array($result)==false){
				$userid=$result;
				$sharedata['errcode'] = 'ok';
				$sharedata['errmsg'] = $userid;
			}
		}
		$type = I('post.type');
		if($userid)$nickname = D('FlUser')->where(array('flu_userid'=>$userid))->getField('flu_nickname');

		$sharedata['img'] = U('@www').'Public/Rebateapp/Images/120-1.png';
		$sharedata['linkurl'] = U('My/invite@flapp',array('inviter'=>$userid));
		if($type=='invite'){
			$sharedata['title'] = ($nickname?$nickname:'您的朋友').'邀你一起来托儿拿返现啦';
			$sharedata['introduce'] = '通过APP消费，不仅帮您省，还能帮您挣，就是这么奇妙这么任性，快来一起下载吧！';		
		}elseif($type=='shareshop'){
			//if(I('post.sid'))$sname = D('Shop')->where(array('sid'=>I('post.sid')))->getField('sname');
			//$sharedata['title'] = '这家店铺有返利哦';
			//$sharedata['introduce'] = ($nickname?$nickname:'您的朋友').'邀您一起来'.$sname.'拿返利，快来光顾吧~';
			if(I('post.sid'))$jid = M('Shop')->where(array('sid'=>I('post.sid')))->getField('jid');
			$path = APP_DIR.'/Public/Data/'.$jid.'/ShareData.php';
			$ShareData=json_decode(file_get_contents($path),true);
			if(!$ShareData[$type]){
				$mabbreviation = M("merchant")->where(array('jid'=>$jid))->getField("mabbreviation");
				$applogo = M("merchantApp")->where(array('jid'=>$jid))->getField("applogo");
				$active = M('active')->where(array('av_jid'=> $jid,'av_status' => 1))->order('av_id desc')->getField("av_title");
				$active = empty($active) ? '有趣的活动' : $active;
				$sharedata['title'] = "【好店大爆料】Duang ~我正在".$mabbreviation."，体验".$active."，现在登录/下载还有红包、抵价券哦！";
				//exit( JSON(array('errcode'=>'40303', 'errmsg'=>'未找到相应数据')) );
				$sharedata['img'] = U('@www').$applogo;
				//$sharedata['introduce'] = '通过APP消费，不仅帮您省，还能帮您挣，就是这么奇妙这么任性，快来一起下载吧！';
				$sharedata['title'] = $mabbreviation ? $mabbreviation : '社区店铺';
			}
			$sharedata['linkurl'] = U('Index/index@yd',array('jid'=>$jid,'v'=>time()));
		}elseif($type=='shareDZP'){
			$sharedata['title'] = ($nickname?$nickname:'您的朋友').'邀你一起来托儿玩大转盘啦';
			$sharedata['introduce'] = '通过APP消费，不仅帮您省，还能帮您挣，就是这么奇妙这么任性，快来一起下载吧！';
		}else{
			$sharedata['title'] = ($nickname?$nickname:'您的朋友').'邀你一起来托儿拿返现啦';
			$sharedata['introduce'] = '通过APP消费，不仅帮您省，还能帮您挣，就是这么奇妙这么任性，快来一起下载吧！';			
		}

		die(JSON($sharedata));
	}

	/****接受首次打开传递mac地址****/
	public function receiveMarking(){
		//D('FlInvite')->recordMarking(I('post.marking'));
		//if(!I('post.marking'))die(JSON(array('errcode'=>'4000','errmsg'=>'标识未提交')));
		die(JSON(array('errcode'=>'ok','errmsg'=>'提交成功')));
	}

	public function areaAll(){
		$data = array();
		$data = D('Address')->cacheArea();
		die(JSON($data));
	}
	
	/****获取地址信息列表****/
	public function areaList(){
		$data = array();
		//$data = D('Address')->cacheArea(true);
		$area = D('Address')->cacheData();
		$list = D('Shop')->field('city')->where(array('status'=>'1','city'=>array('gt',0)))->group('city')->select();
		//echo D('Shop')->getLastSql();
		if($list)foreach($list as $key => $value){
			$data[$key]['code'] = $value['city'];
			$data[$key]['name'] = $area[$value['city']];
		}
		die(JSON($data));

	}

	/****商户首页搜索****/
	public function shopSearch(){
		$keyword = I('post.keyword');
		$data = $map = $where = array();
		if(!$keyword)die(JSON($data));
		/*
		$map['shop.status'] = '1';
		$where['shop.sname']  = array('like', "%{$keyword}%");
		$where['merchant.mnickname']  = array('like',"%{$keyword}%");
		$where['_logic'] = 'or';
		$map['_complex'] = $where;
		$list = D('Shop')->alias('AS shop')->join("__MERCHANT__ AS merchant on shop.jid=merchant.jid", 'left')->field('shop.jid,shop.sid,shop.sname')->where($map)->select();
		*/
		//$sql = "SELECT s.sid,s.sname, max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gvrebate>0 and g.gstatus=1  and s.status='1' ";

		$sql = "SELECT s.sid,s.sname, max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gstatus=1  and s.status='1' and s.qmstatus='1' ";
		if($keyword)$sql .= " and (s.sname like '%{$keyword}%' or m.mnickname like '%{$keyword}%') ";
		$sql .= " GROUP BY s.sid ";
		$Model = new \Think\Model();
		$list = $Model->query($sql);
		if($list)foreach($list as $key => $value){
			$data[$key]['sname'] = nl2br($value['sname']);
			$data[$key]['linkurl'] = U('Shop/shopInfo@flapp',array('sid'=>$value['sid'],'superiorsource'=>'home'));
		}
		die(JSON($data));
	}

	/****商户地图列表****/
	public function shopMapLists() {
		$longitude=I('post.longitude');
		$latitude=I('post.latitude');
		$distance=I('post.distance');
		$city=I('post.city');
		if($city)$citycode = M('address')->where(array('aname'=>array('like','%'.$city.'%')))->getField('aid');

		$vocation = M('vocation')->where(array('v_pid'=>$v_id))->field('v_id')->select();
		$vocation_arr = array();
		foreach($vocation as $k=>$v){
			$vocation_arr[] = $v['v_id'];
		}
		if($v_id > 0){
			$vocation_arr[] = $v_id;
		}
		//$sql = "SELECT s.sid,s.lng,s.lat,s.sname,s.saddress,m.vid, max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gvrebate>0 and g.gstatus=1 ";
		$sql = "SELECT s.sid,s.lng,s.lat,s.sname,s.saddress,m.vid, max(case when g.gdprice>0 then g.gdprice*g.gvrebate else g.goprice*g.gvrebate end) as gfl FROM azd_merchant as m,azd_shop as s,azd_goods as g where m.jid=s.jid and s.sid=g.sid and g.gstatus=1 ";
		$sql .= " and s.status='1' and s.qmstatus = '1' and s.is_show = 1 ";
		if($citycode){
			$sql .= " and s.city='".$citycode."' ";
		}
		$sql .= " GROUP BY s.sid ";
		$Model = new \Think\Model();
		$datalist = $Model->query($sql);
	

		if($datalist)foreach($datalist as $key => $value){ 
				$apart = ($latitude && $longitude?GetDistance($latitude,$longitude,$value['lat'],$value['lng']):'');
				$datalist[$key]['distance'] = $apart;
				$datalist[$key]['vipfl'] = '最高返现'.D('Shop')->calculateRebate($value['gfl']).'元';
				$datalist[$key]['generalfl'] = '最高返现'.D('Shop')->calculateRebate($value['gfl'],'general').'元';
				$datalist[$key]['linkurl'] = U('Shop/shopInfo@flapp',array('sid'=>$value['sid'],'superiorsource'=>'map'));
				$datalist[$key]['vid'] = D("vocation")->topVid($value['vid']);
				$datalist[$key]['saddress'] = nl2br($value['saddress']);
				unset($datalist[$key]['sid']);
		}
		$data = array();
		$data['errcode'] = 0;
		$data['errmsg'] = 'ok';
		$data['data'] = $datalist;
		$datalist ?exit(JSON($data)) : exit(JSON(array('errcode'=>'40102','errmsg'=>'未找到相应数据')));
	}

	/****获取当前版本号****/
	public function checkUpdate(){
		$appid = 1;
		$appinfo = D('System/App')->field('ico,versions,status')->find($appid);
		$appinfo['ico'] = U('@www').$appinfo['ico'];
		$appinfo['downurl'] = U('Token/inviteDown@flapp');
		if($appinfo['status']<1)die(JSON(array()));
		die(JSON($appinfo));
	}

   //上传用户图像
   public function uploadUserImg() {
		$uploadROOT = realpath(THINK_PATH.'../Public/');//上传地址的根目录
		$uploadSubPath = '/Upload/flapp/'.date('Ym/');//上传地址的子目录
		$result = D('FlUsertoken')->checkUtoken(I('get.utoken'));
		if(is_array($result))die(JSON($result));
		$this->userid = $result;
		$subName = array('date','d');
		$uploadPath =$uploadROOT.$uploadSubPath;
        if(!file_exists($uploadPath)) mkdirs($uploadPath, 0775);
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'subName'	=> $subName,
			'exts'		=> 'jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF',
			'maxSize'	=> 2048000
		);
		$attachment = new \Think\Upload( $uploadConfig );
		if(!$_FILES['imgFile'])die(JSON(array('errcode'=>'90000', 'errmsg'=>'图片流未提交')));
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        if($attachmentInfo && is_array($attachmentInfo)) {
			$imgpath = U('@www').'/Public'.$uploadSubPath.($subName?date('d').'/':'').$attachmentInfo['savename'];
			D('FlUser')->where(array('flu_userid'=>$this->userid))->setField('flu_avatar',$imgpath);
            die(JSON(array('errcode'=>'ok', 'errmsg'=>$imgpath, 'savename'=>basename($attachmentInfo['savename']))));
        } else {
            die(JSON(array('errcode'=>'90000', 'errmsg'=>$attachment->getError())));
        }
    }
}