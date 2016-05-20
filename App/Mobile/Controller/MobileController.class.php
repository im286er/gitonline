<?php
namespace Mobile\Controller;
use Think\Controller;

class MobileController extends Controller {
	protected $jid, $sid, $mid,$localExamine,$isApp,$isnewTheme;
	
	public function _initialize() {
		$jid = intval(I('get.jid',0)) > 0 ? intval(I('get.jid',0)) : cookie('jid');
		$jj  = M("merchant")->where(array('jid'=>$jid))->getField('jid');
		if($jj){
			$this->jid = $jid;
			if($jid != cookie('jid')){
				cookie('jid',$jid,1000000);
				cookie('sid',null);
			}
		}else{
			$this->display('Error:404');
			exit;
		}
		$sid = intval(I('get.sid',0)) > 0 ? intval(I('get.sid',0)) : cookie('sid');
		$ss = M('shop')->where(array('jid'=>$this->jid,'sid'=>$sid))->getField('sid');
		if(empty($ss) && empty($sid)){
			$shops = M('shop')->where(array('jid'=>$this->jid, "status"=>'1'))->getField('sid,sname');
			$sid = $ss = key($shops);
		}
		if($ss){
			$this->sid = $sid;
			if($sid != cookie('sid')){
				cookie('sid',$sid,1000000);
				cookie('ProductList',null);
				session('table',null);
			}
		}else{
			$this->display('Error:404');
			exit;
		}
		
		$table = I('table',0);//桌号
		if($table){
			session('table',$table);
		}
		
		//cookie('sid') or redirect("http://www.dishuos.com/");
		
		$j = M("merchant")->where(array('jid'=>$this->jid))->find();
		$this->assign('head_style', $this->head_style_control($j['theme']));

		$color_value = M("theme")->where( array("t_sign"=>$j['theme']) )->field('t_color,t_opacity')->find();
		$this->assign('color_value', $color_value['t_color']);
		$this->assign('color_opacity', $color_value['t_opacity']);


		//这里做一个默认配置，如果商家没有设置配置，则调用此文件
		$settingpath = APP_DIR.'/Public/Data/'.$this->jid.'/setting.conf';
		if( !file_exists($settingpath) ) {
			$setting['consume_type'] = 3;
			$setting['pay_type'] = 3;
			$setting['consume_title_1'] = '店内消费';
			$setting['consume_title_2'] = '外送上门';
			file_put_contents($settingpath, serialize( $setting ));
		}
		
		cookie('clientid',I('get.clientid'),1000000);
		
		if($this->sid > 0){
			$s = M('shop')->where(array('sid'=>$this->sid,'status'=>'1'))->find();
			if(!$s){
				$this->redirect('Index/index', array('jid' => $this->jid));
			}
		}
		
		$this->assign('jid', $this->jid);
		$this->assign('sid', $this->sid);

		$this->mid = 0;
		$uid = cookie('mid')?cookie('mid'):0;
		if($uid){
			$m = M('FlUser')->where(array('flu_userid'=>$uid))->find();
			if($m){
				$this->mid = $uid;
			}
		}

		if($s['theme']=='new1' || $s['theme']=='new2'){
			$this->isnewTheme = 1;
		}else{
			$this->isnewTheme = 0;
		}

		//判断是通过全民返利过来的	
		if(I('get.opentype')=='flapp'){
			cookie('opentype',I('get.opentype'));
			cookie('flsid',I('get.flsid'));
			$superiorsource = I('get.superiorsource');
			if($superiorsource){
				cookie('superiorsource',$superiorsource);
				I('get.v_id')?cookie('source_vid',I('get.v_id')):cookie('source_vid',null);
				I('get.city')?cookie('source_city',I('get.city')):cookie('source_city',null);
			}
		}
		//$this->mid = 1;//测试
		
		//cookie('location',null);
		//加载app控件的判断
		if(I('get.opentype')=='app')
		cookie('opentype',I('get.opentype'));

		if(stristr(I('server.HTTP_USER_AGENT'),'iPhone') && cookie('opentype')=='app'){
			$this->assign('runmode', 'macapp');
		}


		if(cookie('location')){
			$this->localExamine = true;
		}else{
			$this->localExamine = false;
		}
		$this->assign('localExamine', $this->localExamine);
		
		$user_agent = I('server.HTTP_USER_AGENT');
		if(stristr($user_agent,'iPhone') || stristr($user_agent, 'ipa')){
			$msystem = 'ios';
			$this->assign('msystem','ios');
		}else{
			$msystem = 'android';
			$this->assign('msystem','android');
		}
		
		if(cookie('opentype')=='app'){
			$appinfo = M('merchantApp')->where(array('jid'=>$this->jid))->find();
			if(($appinfo['appversions'] >= '1.08' && $msystem == 'android') || ($appinfo['iosversions'] >= '1.08' && $msystem == 'ios')){
				$this->isApp = 1;
			}else{
				$this->isApp = 0;
			}
		}else{
			$this->isApp = 0;
		}
		
		$this->assign('action_name',ACTION_NAME);
		$this->assign('controller_name',CONTROLLER_NAME);
		$this->assign('isApp',$this->isApp);
		$this->assign('mid',$this->mid);
	}
	
	public function mydisplay($action_name=''){
		$tpl_name = '';
		if($action_name){
			//模块界面
			$moduletemp = M('merchant')->where(array('jid'=>$this->jid))->getField('moduletemp');
			$modulearr = unserialize($moduletemp);
			foreach($modulearr as $k=>$v){
				if($k == $action_name){
					$tpl_name = $v;
					break;
				}
			}
		}else{
			$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');
			//非模块界面
		}
		$this->assign('tpl_name', $tpl_name);
		
		$this->theme($tpl_name)->display();
	}

	public function head_style_control($theme=null){
		$list = array(
				'tonghui' 	=> 'background: #5681f7 none repeat scroll 0 0;',
				'yshs'    	=> 'background: #468dd3 none repeat scroll 0 0;',
				'skin4s'  	=> 'background: #e5e5e5 none repeat scroll 0 0;',
				'skincy'  	=> 'background: #e5e5e5 none repeat scroll 0 0;',
				'ktv'	  	=> 'background: #000000 none repeat scroll 0 0;',
				'clothes' 	=> 'background: rgba(0, 0, 0, 0) linear-gradient(to bottom, rgba(63, 59, 58, 0.8) 0%, rgba(208, 207, 207, 0.5) 100%) repeat scroll 0 0;',
				'fruit'   	=> 'background: rgba(0, 0, 0, 0) linear-gradient(to bottom, rgba(179, 164, 155, 1) 0%, rgba(0, 0, 0, 0) 100%) repeat scroll 0 0;',
				'jiazheng'	=> 'background: #a8aaec none repeat scroll 0 0;',
				'jiudian' 	=> 'background: #4b94d0 none repeat scroll 0 0;',
				'qiye'		=> 'background: rgba(0, 0, 0, 0) linear-gradient(to bottom, rgba(35, 92, 182, 1) 0%, rgba(35, 92, 182, 1) 20%, rgba(0, 0, 0, 0) 100%) repeat scroll 0 0;',
				'shouji'	=> 'background: rgba(0, 0, 0, 0) linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 100%) repeat scroll 0 0',
				'jiaju'		=> 'background: #fff none repeat scroll 0 0',
				'hunsha'	=> 'background: rgba(252, 246, 248, 0.5)',
				'meifa'		=> 'background: #e27574',
				'coffee'	=> "background: rgba(0, 0, 0, 0) url('/Public/Mobile/coffee/img/coffee_top_bg.png') no-repeat scroll 0 0 / 100% 100%",
				'market'	=> 'background: #a2be39 none repeat scroll 0 0',
				'netbar'	=> 'background: #191615 none repeat scroll 0 0',
				'cyh' 		=> 'background: linear-gradient(to bottom, rgba(10, 6, 5, 0.62) 0%, rgba(255, 255, 255, 0) 100%);',
				'zyg' 		=> 'background: #100805 none repeat scroll 0 0;',
				'njl' 		=> "background: rgba(0, 0, 0, 0) url('/Public/Mobile/njl/img/njl_head_bg.png') no-repeat scroll 0 0 / 100% 100%;",
				'catering' 	=> "background: #b54d52 none repeat scroll 0 0;",
				'jshs' 		=> "background: #000000 none repeat scroll 0 0;",
				'clothes_blue' 		=> "background: #323d4f none repeat scroll 0 0;",
				'mancloth' 			=> "background: #15100c none repeat scroll 0 0;",
		);
		if($list[$theme])return $list[$theme];
		return false;
	}


	//功能按钮
	public function funcMenu(){
		$sid      = I('sid',$this->sid);
		$theme    = M('shop')->where(array('sid'=>$sid))->getField('theme');
		$category = M('category')->alias('c')->join('azd_module m on c.model=m.module_sign')->where(array('c.sid'=>$sid, 'c.status'=>1, 'c.jid'=>$this->jid))->field('c.*,m.module_link')->order('c.corder')->select();
		foreach($category as $k=>$v){
			$category[$k]['url'] = $v['module_link'].'cid/'.$v['id'].'/sid/'.$sid.'.html';
		}
		//背景图
		// $backImg = M('BackImg')->where(array('b_sid'=>$sid))->find();
		$backImg = M('shop')->field('img_url,img_height')->where(array('sid'=>$sid))->find();
		//分类名称
		$cname   = M('category')->where(array('id'=>I('cid',0), 'status'=>1, 'jid'=>$this->jid))->getField('cname');
		//客服电话
		$info = M('shop')->field('qq,mservetel')->where(array('sid'=>$sid))->find();
		if ($info['qq']) {
			$link = "http://wpa.qq.com/msgrd?v=3&uin=".$info['qq']."&site=qq&menu=yes";
		}else{
			$link = "tel:".$info['mservetel']."";
		}
		//商铺列表
		$shop_list = M('shop')->field('sid,sname')->where(array('jid'=>$this->jid))->select(); 

		$this->assign('shop_list', $shop_list);
		$this->assign('cname', $cname);
		$this->assign('link', $link);
		$this->assign('category', $category);
		$this->assign('backImg', $backImg);
	}



	//新模板渲染
	public function newdisplay(){
		$sid     = $this->sid == '0' ? I('sid','95') : $this->sid;

		$tpl_name = M('shop')->where(array('sid'=>$sid))->getField('theme');

		$this->assign('tpl_name', $tpl_name);
		
		$this->theme($tpl_name)->display();
	}


	
}