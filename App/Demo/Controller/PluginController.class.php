<?php
namespace Demo\Controller;
//公共小插件部分
class PluginController extends MerchantController {
	//支付宝支付设置
	public function setalipay(){
		if( $this->type != 1 ) E('你无权查看当前页面');
		$linkmobile = M('merchant')->where(array('jid'=>$this->jid))->getField('mlptel');
		$extend = M('merchant_extend')->find($this->jid);
		$this->assign('linkmobile', $linkmobile);
		$this->assign('extend', $extend);
		if( IS_POST ) {
			$data = array();
			$smscode = I('post.smscode');
			if(!$smscode ) $this->ajaxReturn(array('status'=>'0' ,'msg'=>'请输入手机收到的验证码'));
			if( session('SendSms') != $smscode )  $this->ajaxReturn(array('status'=>'0' ,'msg'=>'验证码输入错误，请重新修改'));
			if(!I('post.alipay_no')) $this->ajaxReturn(array('status'=>'0' ,'msg'=>'请输入支付宝商户号'));
			if(!I('post.alipay_email')) $this->ajaxReturn(array('status'=>'0' ,'msg'=>'请输入支付宝收款邮箱')); 
			if (!eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3}$",I('post.alipay_email'))) $this->ajaxReturn(array('status'=>'0' ,'msg'=>'请输入正确的支付宝收款邮箱'));
			if(!I('post.alipay_key')) $this->ajaxReturn(array('status'=>'0' ,'msg'=>'请输入支付宝密匙'));
			$data['alipay_no']=I('post.alipay_no');
			$data['alipay_email']=I('post.alipay_email');
			$data['alipay_key']=I('post.alipay_key');
			if($data){
				if($extend){
					$result = M('merchant_extend')->where(array('jid'=>$this->jid))->save($data);
				}else{
					$data['jid'] = $this->jid;
					$result = M('merchant_extend')->add($data);
				}
				if($result)
				$this->ajaxReturn(array('status'=>'1' ,'msg'=>'设置成功'));

			}
			$this->ajaxReturn(array('status'=>'0' ,'msg'=>'设置失败'));
			exit;
		}
		$this->display();	
	}

	//分享设置
	public function shareset(){
		$sharetype = array(
			'internet' => '上网分享',
			'login' => '登录分享',
			'download' => '活动分享',
		);
		$ShareData = array();
		file_exists($this->path.'ShareData.php') && $ShareData=json_decode(file_get_contents($this->path.'ShareData.php'),true);
		if( IS_POST ) {
			$type = I('post.type');
			if(array_key_exists($type,$sharetype)==false){
				$this->ajaxReturn(array('status'=>'0' ,'msg'=>'参数错误'));
			}
			$postdata = I('post.');
			$data = $postdata[$type];

			if(!$data['text']) $this->ajaxReturn(array('status'=>'0' ,'msg'=>'请输入分享内容'));
			if(mb_strlen($data['text'],'utf-8')>200)$this->ajaxReturn(array('status'=>'0' ,'msg'=>'微信分享内容限制200字，当前输入'.mb_strlen($data['text'],'utf-8').'个字'));
			
			$ShareData[$type] = $data;
			$result = file_put_contents($this->path.'ShareData.php', JSON($ShareData));
			if($result)$this->ajaxReturn(array('status'=>'1' ,'msg'=>'设置成功'));
			$this->ajaxReturn(array('status'=>'0' ,'msg'=>'设置失败'));
		}
		$type = I('get.type','internet');

		if($type=='download'){
			$mabbreviation = M("merchant")->where(array('jid'=>$this->jid))->getField("mabbreviation");
			$active = M('active')->where(array('av_jid'=> $this->jid,'av_status' => 1))->order('av_id desc')->getField("av_title");
			$active = empty($active) ? '有趣的活动' : $active;
			$ShareData[$type]['text'] = "【好店大爆料】Duang ~我正在".$mabbreviation."，体验".$active."，现在登录/下载还有红包、抵价券哦！";
		}else{
			if(!$ShareData[$type]['text'])$ShareData[$type]['text'] = '我正在使用帝鼠OS提供的物联网营销解决方案，硬件帮我拉客、软件帮我续客，微信跟支付宝都在帮我倒流。';
		}

		$this->assign('type', $type);
		$this->assign('data', $ShareData[$type]);
		$this->assign('sharetype', $sharetype);
		$this->display();	
	}


	//分享
	public function appdownset(){
		if( $this->type != 1 ) E('你无权查看当前页面');
		
		
		file_exists($this->path.'InfoMenuAppDownName.php') && $modulename=file_get_contents($this->path.'InfoMenuAppDownName.php');
		file_exists($this->path.'InfoMenuAppDownIcon.php') && $moduleicon=file_get_contents($this->path.'InfoMenuAppDownIcon.php');
		$this->assign('modulename', $modulename ? $modulename : '');
		$this->assign('moduleicon', $moduleicon ? $moduleicon : '');
		$this->assign('modulelink', 'http://yd.dishuos.com/Appdown/index/jid/'.$this->jid.'.html');
	
		
		$extend = M('merchant_extend')->find($this->jid);
		$app = D('App');
		if($extend['app_down']== 0 || !$extend)$app->merchant_sync($this->jid);//开通同步
		if(IS_POST){
			$result = $app->updateorder($this->jid,I('post.appid'),I('post.orders'));
			exit($result?'1':'0');
		}
		$appCategoryList = F('appCategoryList');
		$this->assign('appCategoryList', $appCategoryList);
		$this->assign('extend', $extend);
		$where = array();
		$where['am.status'] = '1';
		$where['am.jid'] = $this->jid;
		if( I('get.keywords', '') ) {
			$keyword=I('get.keywords', ''); 
			$where['app.name|app.source']=array('like', "%{$keyword}%", 'or');
		}

		$page = new \Demo\Org\Page(M('AppMerchant')->alias('AS am')->where($where)->join("__APP__ AS app on am.appid=app.id", 'left')->count(), 10);
		$datalist = M('AppMerchant')->alias('AS am')->where($where)->join("__APP__ AS app on am.appid=app.id", 'left')->order('am.orders asc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('pages', $page->show());
		$this->assign('datalist', $datalist);
		$this->assign('tfs', ($this->type==1 && $sid!=0) || $this->type!=1 ? '0' : '1');
		$this->display();
	}


	//设置APP下载的ICON和标题
	public function resetAppdown() {
		$ModuleName = I('post.ModuleName', '');
		$ModuleIcon = I('post.ModuleIcon', '');

		if( $ModuleName ) {
			$s=file_put_contents($this->path.'InfoMenuAppDownName.php', $ModuleName);
		}

		if( $ModuleIcon ) {
			$s=file_put_contents($this->path.'InfoMenuAppDownIcon.php', $ModuleIcon);
		}
		exit( $s ? '1' : '0' );
	}
}