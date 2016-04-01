<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
// TypeEvent.class.php 2013-02-27
namespace Org\Util;
use OT\DataDictionary;
use ORG\ThinkSDK\ThinkOauth;
class AddShare{
	public $share_content = '';
	//登录成功，发表腾讯微博
	public function qq($token){
		import("ORG.ThinkSDK.ThinkOauth");
		$qq   = ThinkOauth::getInstance('qq', $token);
		$data = $qq->call('t/add_t',"content=".urlencode($this->share_content),'POST');

		if($data['ret'] == 0){
			return true;
		} else {
			return false;
		}
	}

	//登录成功，获取腾讯微博用户信息
	public function tencent($token){
		return true;
	}

	//登录成功，发表新浪微博
	public function sina($token){
		$sina = ThinkOauth::getInstance('sina', $token);
		$data = $sina->call('statuses/update', "status=".urlencode($this->share_content),'POST');

		if($data['error_code'] == 0){
			return true;
		} else {
			return false;
		}
	}

	//登录成功，获取网易微博用户信息
	public function t163($token){
		return true;
	}

	//登录成功，获取人人网用户信息
	public function renren($token){
		return true;
	}

	//登录成功，获取360用户信息
	public function x360($token){
		return true;
	}

	//登录成功，获取豆瓣用户信息
	public function douban($token){
		return true;
	}

	//登录成功，获取Github用户信息
	public function github($token){
		return true;
	}

	//登录成功，获取Google用户信息
	public function google($token){
		return true;
	}

	//登录成功，获取Google用户信息
	public function msn($token){
		return true;
	}

	//登录成功，获取点点用户信息
	public function diandian($token){
		return true;
	}

	//登录成功，获取淘宝网用户信息
	public function taobao($token){
		return true;
	}
	
	//登录成功，获取百度用户信息
	public function baidu($token){
		return true;
	}

	//登录成功，获取开心网用户信息
	public function kaixin($token){
		return true;
	}

	//登录成功，获取搜狐用户信息
	public function sohu($token){
		return true;
	}
	
	public function set_content($sharetext){
		$this->share_content = $sharetext;
	}
}