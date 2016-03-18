<?php
namespace Mobile\Controller;
use Think\Controller;

class MobileController extends Controller {
	protected $jid, $sid, $mid,$localExamine,$isApp;
	
	public function _initialize() {
		
		//如果不存在 JID 参数，则跳转到官网
		if(I('get.jid') != cookie('jid') && I('get.jid') && intval(I('get.jid')))
		cookie('jid',I('get.jid'),1000000);
		cookie('jid') or redirect("http://www.dishuos.com/");
		
		$this->jid = cookie('jid');
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
		

		//print_r($j);
		if(!$j){
			redirect("http://www.dishuos.com/");
		}
		
		
		cookie('clientid',I('get.clientid'),1000000);
		
		$this->sid = isset($_GET['sid']) ? intval($_GET['sid']) : 0;
		
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
	
}