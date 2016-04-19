<?php
namespace Mobile\Controller;
/*首页控制器
 *
*
* */
class IndexController extends MobileController {
	public function index(){
		//广告图片 start
		$banner = M('banner');
		$opt = array(
			'jid' => $this->jid,
		);
		$banner_list = $banner->where($opt)->order('bid asc')->select();
		foreach($banner_list as $k=>$v){
			$banner_list[$k]['burl'] = 'http://'.ltrim($v['burl'],'http://');
			$banner_list[$k]['burl']  = $banner_list[$k]['burl'] == 'http://' ? '' : $banner_list[$k]['burl'];
		}
		//广告图片 end
		
		$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');
		
		//首页显示的活动 start
		$active = M('active');
		$opt = array(
				'av_jid'    => $this->jid,
				'av_status' => 1
		);
		if($tpl_name == 'yshs'  || $tpl_name == 'clothes' || $tpl_name == 'fruit' || $tpl_name == 'tonghui' || $tpl_name == 'netbar'){
			$avnum = 3;
		}elseif($tpl_name=='coffee' || $tpl_name == 'market') {
			$avnum = 6;
		}elseif( $tpl_name == 'zyg' || $tpl_name=='njl' || $tpl_name=='jshs'){
			$avnum = 4;
		}elseif($tpl_name=='qiye'){
			$avnum = 8;
		}else{
			$avnum = 2;
		}
		$active_list = $active->where($opt)->order('av_id desc')->limit($avnum)->select();
		//首页显示的活动 end
		
		
		//首页显示的资讯 start
		$news = M('new');
		$opt = array(
				'new_jid'    => $this->jid,
				'new_status' => 1
		);
		if($tpl_name == 'yshs' || $tpl_name == 'clothes' || $tpl_name == 'market' || $tpl_name == 'netbar'){
			$newsnum = 3;
		}elseif($tpl_name == 'fruit' || $tpl_name=='coffee' || $tpl_name=='njl'){
			$newsnum = 6;
		}elseif($tpl_name=='shouji' || $tpl_name='hunsha' || $tpl_name=='jshs'){
			$newsnum = 9;
		}elseif($tpl_name=='qiye'){
			$newsnum = 4;
		}else{
			$newsnum = 2;
		}
		$news_list = $news->where($opt)->order('new_id desc')->limit( $newsnum )->select();
		$this->assign('news_list',$news_list);
		//首页显示的资讯 end
		
		//首页显示的优惠券 start
		$coupon = M('voucher');
		$opt = array(
				'v.vu_jid'    => $this->jid,
				'v.vu_status' => 1,
				'v.vu_etime' => array('egt',date("Y-m-d H:i:s")),
		);
		
		$NEW_COUPON_NUMBER = C('NEW_COUPON_NUMBER');
		if( $tpl_name=='shouji' ) $NEW_COUPON_NUMBER = 3;
		$coupon_list = $coupon->alias('v')->field('v.*, (v.vu_cum - (SELECT count(*) FROM azd_voucher_user where vu_id=v.vu_id)) as vu_sum')->where($opt)->having('vu_sum>0')->order('v.vu_id desc')->limit( $NEW_COUPON_NUMBER )->select();
		//首页显示的优惠券 end
		
		//显示视频数量  start
		$category = M('class');
		$opt = array(
				'jid' => $this->jid,
				//'sid' => $this->sid,
				'ctype' => 3,
				'status' => 1
		);
		$category_list = $category->where($opt)->order('corder')->select();
		
		$re = array();
		foreach($category_list as $k=>$v){
			$re[] = $v['cid'];
		}
		$video = M('video');
		$opt = array(
			'gstatus' => 1
		);
		if($re){
			$opt['cid'] = array('in',join(',',$re));
			$video_count = $video->where($opt)->count();
		}else{
			$video_count =0;
		}
		//显示视频数量  end
		
		//取得商家第一个分店的相关信息 start
		$shop_info = M('shop')->where(array('jid'=>$this->jid))->field('saddress,mservetel')->order('sid asc')->find();
		//取得商家第一个分店的相关信息 end
		
		$merchant = M('Merchant')->where("jid='{$this->jid}'")->find();
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';


		/***在线点餐**/
		file_exists($path.'InfoMenu1Name.php') && $module1name=file_get_contents($path.'InfoMenu1Name.php');
		file_exists($path.'InfoMenu1Icon.php') && $module1icon=file_get_contents($path.'InfoMenu1Icon.php');
		$this->assign('module1name', $module1name ? $module1name : '在线点菜');
		$this->assign('module1icon', $module1icon ? $module1icon : '/Public/Images/mobile/nav-img1.png');
		/***在线点餐结束**/
		/***在线订座**/
		file_exists($path.'InfoMenu2Name.php') && $module2name=file_get_contents($path.'InfoMenu2Name.php');
		file_exists($path.'InfoMenu2Icon.php') && $module2icon=file_get_contents($path.'InfoMenu2Icon.php');
		$this->assign('module2name', $module2name ? $module2name : '在线订座');
		$this->assign('module2icon', $module2icon ? $module2icon : '/Public/Images/mobile/nav-img2.png');
		/**在线订座结束**/
		/**微视频开始**/
		file_exists($path.'InfoMenu3Name.php') && $InfoMenu3Name=file_get_contents($path.'InfoMenu3Name.php');
		file_exists($path.'InfoMenu3Icon.php') && $InfoMenu3Icon=file_get_contents($path.'InfoMenu3Icon.php');
		$this->assign('InfoMenu3Name', $InfoMenu3Name ? $InfoMenu3Name : '微视频');
		$this->assign('InfoMenu3Icon', $InfoMenu3Icon ? $InfoMenu3Icon : '/Public/Images/mobile/002.png');
		/**微视频结束**/
		/***微商城开始**/
		file_exists($path.'WshopModuleLink.php') && $WshopModuleLink=file_get_contents($path.'WshopModuleLink.php');
		file_exists($path.'WshopModuleName.php') && $WshopModuleName=file_get_contents($path.'WshopModuleName.php');
		file_exists($path.'WshopModuleIcon.php') && $WshopModuleIcon=file_get_contents($path.'WshopModuleIcon.php');
		$this->assign('WshopModuleLink', $WshopModuleLink ? $WshopModuleLink : '#');
		$this->assign('WshopModuleName', $WshopModuleName ? $WshopModuleName : '微商城');
		$this->assign('WshopModuleIcon', $WshopModuleIcon); 
		/***微商城结束**/
		/**品牌分店开始**/
		file_exists($path.'ShopName.php') && $ShopMenuName=file_get_contents($path.'ShopName.php');
		file_exists($path.'ShopIcon.php') && $ShopMenuIcon=file_get_contents($path.'ShopIcon.php');
		$this->assign('ShopMenuName', $ShopMenuName ? $ShopMenuName : '品牌分店');
		$this->assign('ShopMenuIcon', $ShopMenuIcon ? $ShopMenuIcon : '/Public/Images/mobile/nav-img3.png');
		/***品牌分店结束**/
		/**精品下载开始**/
		file_exists($path.'InfoMenuAppDownName.php') && $InfoMenuAppDownName=file_get_contents($path.'InfoMenuAppDownName.php');
		file_exists($path.'InfoMenuAppDownIcon.php') && $InfoMenuAppDownIcon=file_get_contents($path.'InfoMenuAppDownIcon.php');
		$this->assign('InfoMenuAppDownName', $InfoMenuAppDownName ? $InfoMenuAppDownName : '精品下载');
		$this->assign('InfoMenuAppDownIcon', $InfoMenuAppDownIcon ? $InfoMenuAppDownIcon : '/Public/Mobile/default/img/jpyy.png');
		/**精品下载结束**/

		/**营销专题开始**/
		file_exists($path.'BoutiqueModuleName.php') && $BoutiqueModuleName=file_get_contents($path.'BoutiqueModuleName.php');
		file_exists($path.'BoutiqueModuleIcon.php') && $BoutiqueModuleIcon=file_get_contents($path.'BoutiqueModuleIcon.php');
		$this->assign('BoutiqueModuleName', $BoutiqueModuleName ? $BoutiqueModuleName : '营销专题');
		$this->assign('BoutiqueModuleIcon', $BoutiqueModuleIcon ? $BoutiqueModuleIcon : '/Public/Mobile/default/img/jpyy.png');
		/**营销专题结束**/

		/**最新活动开始**/
		file_exists($path.'HdModule.php') && $HdModule=unserialize(file_get_contents($path.'HdModule.php'));
		$this->assign('HdModuleName', $HdModule ? $HdModule['Name'] : '最新活动');
		$this->assign('HdModuleIcon', $HdModule ? $HdModule['Icon'] : '');
		/**最新活动结束**/
		/**优惠券开始**/
		file_exists($path.'VoucherModule.php') && $VoucherModule=unserialize(file_get_contents($path.'VoucherModule.php'));
		$this->assign('VoucherModuleName', $VoucherModule ? $VoucherModule['Name'] : '优惠券');
		$this->assign('VoucherModuleLink', $VoucherModule ? $VoucherModule['Link'] : '');
		$this->assign('VoucherModuleIcon', $VoucherModule ? $VoucherModule['Icon'] : '');
		/**优惠券结束**/
		/**最新资讯开始**/
		file_exists($path.'NewModule.php') && $NewModule=file_get_contents($path.'NewModule.php');
		$NewModuleC = unserialize($NewModule);
		$this->assign('NewModulename', $NewModuleC['Name'] ? $NewModuleC['Name'] : '最新资讯');
		$this->assign('NewModuleicon', $NewModuleC['Icon'] ? $NewModuleC['Icon'] : '/Public/Mobile/default/img/ico_mart.png');
		/**最新资讯结束**/
		/**配置菜单文件读取开始**/
		$setting_array = array();
		$setting_array = unserialize( file_get_contents( $path.'setting.conf' ) );
		$this->assign('setting_array',$setting_array['modules']);
		/**配置菜单读取结束**/
		if(in_array($this->jid,array(297,288,287,296))==false){
		$appinfo = M('merchantApp')->where(array('jid'=>$this->jid,'iosurl'=>array('neq',''),'appurl'=>array('neq','')))->find();
		$this->assign('appinfo', $appinfo);
		}

		$this->assign('merchant', $merchant);
		$this->assign('banner_list', $banner_list);
		$this->assign('active_list', $active_list);
		$this->assign('coupon_list', $coupon_list);
		$this->assign('video_count', $video_count);
		$this->assign('shop_info', $shop_info);
		
		//查询非系统模块
		$module_list = array();
		$sign = M("merchant")->where(array('jid'=>$this->jid))->getField("modules");
		if($sign){
			$opt = array(
				'module_pid' => array('gt',0),
				'module_status' => '1',
				'module_system' => 0,
				'module_sign'  => array('in',$sign)
			);
			$module_list = M("merchant_module")->where($opt)->order("module_order")->select();
		}
		$this->assign('module_list', $module_list);
		$this->mydisplay();
	}
	
	//app下载
	public function appdown(){
		$jid = I('get.jid') ? I('get.jid') : $this->jid;
		$merchant = M("merchant")->where(array('jid'=>$jid))->find();
		$mtapp = M('merchantApp')->where(array('jid'=>$jid))->find();
		$user_agent = I('server.HTTP_USER_AGENT');
		if(strpos($user_agent, "MicroMessenger")) {
			$this->assign('merchant', $merchant);
			$this->assign('app', $mtapp);
			$this->assign('apptype', 2);
			$this->assign('page_name', $mtapp['appname'].'APP下载');
			//广告图片 start
			$banner = M('banner');
			$opt = array('jid' => $jid);
			$banner_list = $banner->where($opt)->order('bid desc')->select();
			foreach($banner_list as $k=>$v){
				$banner_list[$k]['burl'] = stristr('http://', $v['burl']) ? $v['burl'] : 'http://'.$v['burl'];
				$banner_list[$k]['burl']  = $banner_list[$k]['burl'] == 'http://' ? '' : $banner_list[$k]['burl'];
			}
			$vocation = M('vocation')->find($merchant['vid']);
			$this->assign('vocation',$vocation);
			$this->assign('banner_list',$banner_list);
			$this->display();
		}elseif(stristr($user_agent,'iPhone') || stristr($user_agent, 'ipa')){
			if(file_exists( APP_DIR.$mtapp['iosurl']) && $mtapp['iosurl']) {
				M('MerchantApp')->where(array('jid'=>$this->jid))->setInc('ios_downloads');
				$this->assign('merchant', $merchant);
				$this->assign('app', $mtapp);
				$this->assign('apptype', 2);
				$this->assign('page_name', $mtapp['appname'].'APP下载');
				//广告图片 start
				$banner = M('banner');
				$opt = array(
					'jid' => $jid,
				);
				$banner_list = $banner->where($opt)->order('bid desc')->select();
				foreach($banner_list as $k=>$v){
					$banner_list[$k]['burl'] = stristr('http://', $v['burl']) ? $v['burl'] : 'http://'.$v['burl'];
					$banner_list[$k]['burl']  = $banner_list[$k]['burl'] == 'http://' ? '' : $banner_list[$k]['burl'];
				}
				$vocation = M('vocation')->find($merchant['vid']);
				$this->assign('vocation',$vocation);
				$this->assign('banner_list',$banner_list);
				$this->display('download_ios');
				exit;
			}
		}elseif(stristr($user_agent,'Android') || I('get.type')=='must') {
			if(file_exists( APP_DIR.$mtapp['appurl']) && $mtapp['appurl']) {
				M('MerchantApp')->where(array('jid'=>$this->jid))->setInc('android_downloads');
				//header('Location: '.$mtapp['appurl']); exit;


				$this->assign('merchant', $merchant);
				$this->assign('app', $mtapp);
				$this->assign('apptype', 1);
				$this->assign('page_name', $mtapp['appname'].'APP下载');
				//广告图片 start
				$banner = M('banner');
				$opt = array(
					'jid' => $jid,
				);
				$banner_list = $banner->where($opt)->order('bid desc')->select();
				foreach($banner_list as $k=>$v){
					$banner_list[$k]['burl'] = stristr('http://', $v['burl']) ? $v['burl'] : 'http://'.$v['burl'];
					$banner_list[$k]['burl']  = $banner_list[$k]['burl'] == 'http://' ? '' : $banner_list[$k]['burl'];
				}
				$vocation = M('vocation')->find($merchant['vid']);
				$this->assign('vocation',$vocation);
				$this->assign('banner_list',$banner_list);
				$this->display('download_ios');
				exit;
			}
			redirect( U('/Index/index/', array('jid' => $jid) ) );
		} else {
			redirect( U('/Index/index/', array('jid' => $jid) ) );
		}

	}


	/**
	 * 获取轮播图
	 * @return mixed
	 */
	public function banner(){
		//广告图片 start
		$banner = M('banner');
		$opt = array(
			'jid' => $this->jid,
		);
		$banner_list = $banner->where($opt)->order('bid asc')->select();
		foreach($banner_list as $k=>$v){
			$banner_list[$k]['burl'] = 'http://'.ltrim($v['burl'],'http://');
			$banner_list[$k]['burl']  = $banner_list[$k]['burl'] == 'http://' ? '' : $banner_list[$k]['burl'];
		}
		//广告图片 end
		$this->assign('banner_list',$banner_list);
	}


	/**
	 * 获取活动列表
	 * @return mixed
	 */
	public function active(){
		$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');

		//首页显示的活动 start
		$active = M('active');
		$opt = array(
			'av_jid'    => $this->jid,
			'av_status' => 1
		);
		if($tpl_name == 'yshs'  || $tpl_name == 'clothes' || $tpl_name == 'fruit' || $tpl_name == 'tonghui' || $tpl_name == 'netbar'){
			$avnum = 3;
		}elseif($tpl_name=='coffee' || $tpl_name == 'market') {
			$avnum = 6;
		}elseif( $tpl_name == 'zyg' || $tpl_name=='njl' || $tpl_name=='jshs'){
			$avnum = 4;
		}elseif($tpl_name=='qiye'){
			$avnum = 8;
		}else{
			$avnum = 2;
		}
		$active_list = $active->where($opt)->order('av_id desc')->limit($avnum)->select();
		$this->assign('active_list',$active_list);
		//首页显示的活动 end
	}


	/**
	 * 显示资讯
	 * @return mixed
	 */
	public function news(){
		$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');
		//首页显示的资讯 start
		$news = M('new');
		$opt = array(
			'new_jid'    => $this->jid,
			'new_status' => 1
		);
		if($tpl_name == 'yshs' || $tpl_name == 'clothes' || $tpl_name == 'market' || $tpl_name == 'netbar'){
			$newsnum = 3;
		}elseif($tpl_name == 'fruit' || $tpl_name=='coffee' || $tpl_name=='njl'){
			$newsnum = 6;
		}elseif($tpl_name=='shouji' || $tpl_name='hunsha' || $tpl_name=='jshs'){
			$newsnum = 9;
		}elseif($tpl_name=='qiye'){
			$newsnum = 4;
		}else{
			$newsnum = 2;
		}
		$news_list = $news->where($opt)->order('new_id desc')->limit( $newsnum )->select();
		$this->assign('news_list',$news_list);
		//首页显示的资讯 end
	}


	/**
	 * 获取优惠券列表
	 * @return mixed
	 */
	public function coupon(){
		$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');
		//首页显示的优惠券 start
		$coupon = M('voucher');
		$opt = array(
			'v.vu_jid'    => $this->jid,
			'v.vu_status' => 1,
			'v.vu_etime' => array('egt',date("Y-m-d H:i:s")),
		);

		$NEW_COUPON_NUMBER = C('NEW_COUPON_NUMBER');
		if( $tpl_name=='shouji' ) $NEW_COUPON_NUMBER = 3;
		$coupon_list = $coupon->alias('v')->field('v.*, (v.vu_cum - (SELECT count(*) FROM azd_voucher_user where vu_id=v.vu_id)) as vu_sum')->where($opt)->having('vu_sum>0')->order('v.vu_id desc')->limit( $NEW_COUPON_NUMBER )->select();
		$this->assign('coupon_list',$coupon_list);
		//首页显示的优惠券 end
	}


	/**
	 * 显示视频数量
	 * @return int
	 */
	public function video(){
		//显示视频数量  start
		$category = M('class');
		$opt = array(
			'jid' => $this->jid,
			//'sid' => $this->sid,
			'ctype' => 3,
			'status' => 1
		);
		$category_list = $category->where($opt)->order('corder')->select();

		$re = array();
		foreach($category_list as $k=>$v){
			$re[] = $v['cid'];
		}
		$video = M('video');
		$opt = array(
			'gstatus' => 1
		);
		if($re){
			$opt['cid'] = array('in',join(',',$re));
			$video_count = $video->where($opt)->count();
		}else{
			$video_count =0;
		}
		$this->assign('video_count',video_count);
		//显示视频数量  end
	}


	public function new1(){
		//获取活动
		$this->active();
		//获取轮播图
		$this->banner();
		//获取优惠券
		$this->coupon();

		$this->mydisplay();
	}



}