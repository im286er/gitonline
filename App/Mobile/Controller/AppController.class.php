<?php
namespace Mobile\Controller;
/*控制器
 *
*
* */

class AppController extends MobileController {
	
	public function index(){
		$where = array();
		$where['am.status'] = '1';
		$where['am.jid'] = $this->jid;
		$user_agent = I('server.HTTP_USER_AGENT');
		if(stristr($user_agent,'iPhone') || stristr($user_agent, 'ipa')){
			$msystem = 'ios';
			$map['iosurl']  = array('neq','');
		}else{
			$msystem = 'android';
			$map['androidurl']  = array('neq','');
		}
		if( I('get.keywords', '') ) {
			$keyword=I('get.keywords', ''); 
			$where['app.name|app.source']=array('like', "%{$keyword}%", 'or');
		}
		$page = new \Common\Org\Page(M('AppMerchant')->alias('AS am')->where($where)->join("__APP__ AS app on am.appid=app.id", 'left')->count(), 10);
		$datalist = M('AppMerchant')->alias('AS am')->where($where)->join("__APP__ AS app on am.appid=app.id", 'left')->order('am.orders asc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('pages', $page->show());
		$this->assign('datalist', $datalist);
		$path = APP_DIR.'/Public/Data/'.$this->jid.'/';
		file_exists($path.'InfoMenuAppDownName.php') && $InfoMenuAppDownName=file_get_contents($path.'InfoMenuAppDownName.php');
		file_exists($path.'InfoMenuAppDownIcon.php') && $InfoMenuAppDownIcon=file_get_contents($path.'InfoMenuAppDownIcon.php');
		$this->assign('page_name', $InfoMenuAppDownName ? $InfoMenuAppDownName : '精品应用');
		$this->assign('page_url',U('Index/index',array('jid'=>$this->jid)));
		$this->assign('msystem',$msystem);
		$this->mydisplay();
	}


	//app下载
	public function down(){
		$appid = I('get.appid');
		$app = D('System/App');
		$appinfo = $app->find($appid);
		
		$this->assign('appinfo', $appinfo);
		$user_agent = I('server.HTTP_USER_AGENT');
		if(strpos($user_agent, "MicroMessenger")) {
			$this->display();
			exit;
		}elseif(stristr($user_agent,'iPhone') || stristr($user_agent, 'ipa')){
			if(file_exists( APP_DIR.$appinfo['iosurl']) && $appinfo['iosurl']) {
				if($appid==1)redirect('https://itunes.apple.com/cn/app/quan-min-fan-li/id1020272189?mt=8');
				$app->where(array('id'=>$appid))->setInc('iosdownloads');
				M('AppMerchant')->where(array('jid'=>$this->jid,'appid'=>$appid))->setInc('ioshit');
				$this->assign('app', $appinfo);
				$this->assign('apptype', 2);
				$this->display('App_iosdown');
				exit;
			}

		}elseif(stristr($user_agent,'Android')) {
			if(file_exists( APP_DIR.$appinfo['androidurl']) && $appinfo['androidurl']) {
				$app->where(array('id'=>$appid))->setInc('androiddownloads');
				M('AppMerchant')->where(array('jid'=>$this->jid,'appid'=>$appid))->setInc('androidhit');
				header('Location: '.$appinfo['androidurl']); exit;
			}
		}else{
			if(file_exists( APP_DIR.$appinfo['androidurl']) && $appinfo['androidurl']) {
				$app->where(array('id'=>$appid))->setInc('androiddownloads');
				M('AppMerchant')->where(array('jid'=>$this->jid,'appid'=>$appid))->setInc('androidhit');
				header('Location: '.$appinfo['androidurl']); exit;
			}
		}
		redirect('/Index/index/');
	}

}