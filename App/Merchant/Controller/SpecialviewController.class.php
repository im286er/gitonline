<?php
namespace Merchant\Controller;
use Think\Controller;

class SpecialviewController extends Controller {
    public function index(){
		$_scene = M('yqxScene');
		$where['scenecode_varchar']  = I('get.id',0);
		
		$where['delete_int']  = 0;
		$_scene_list=$_scene->where($where)->select();   
		
		$argu2 = array();
		$argu2['title'] = $_scene_list[0]["scenename_varchar"];
		$argu2['url'] = 'v-'.$_scene_list[0]["scenecode_varchar"];
		$argu2['desc'] = $_scene_list[0]["desc_varchar"];
		$argu2['imgsrc'] = $_scene_list[0]["thumbnail_varchar"];
		$this->assign("confinfo2", $argu2);
		$this->display();
    }


    public function test(){
		$confinfo = $this->get_js_sdk("wx6d73b33aa7167bd4","db3f717101a567a8c6c7ae1851feaa19");
		$this->assign("confinfo",$confinfo);
		$this->display();
    }

	public function curlSend($url,$post_data=""){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		if($post_data != ""){
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	public function get_accesstoken($APP_ID,$APP_SECRET){
		$ACCESS_TOKEN = S($APP_ID);
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$APP_ID."&secret=".$APP_SECRET;
		$json = $this->curlSend($url);
		
		$data=json_decode($json,true);
			
		S($APP_ID,$data['access_token'],7000);
		$ACCESS_TOKEN = S($APP_ID);
		return $ACCESS_TOKEN;
	}

	public function get_jsapi_ticket($ACCESS_TOKEN){
		$jsapi_ticket = S($ACCESS_TOKEN);
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$ACCESS_TOKEN."&type=jsapi";
		$json = $this->curlSend($url);
		$data = json_decode($json,true);
		
		$aaa = S($ACCESS_TOKEN,$data['ticket'],7000);
		$jsapi_ticket = S($ACCESS_TOKEN);
		return $jsapi_ticket;
	}

	public function get_js_sdk($APP_ID,$APP_SECRET){
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== off || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		$argu = array();
		$argu['appId'] = $APP_ID;
		$argu['url'] = $url;
		$argu['nonceStr'] = $this->createNonceStr();
		$argu['timestamp'] = time();
		
		$ACCESS_TOKEN = $this->get_accesstoken($APP_ID, $APP_SECRET);
		$argu['jsapi_ticket'] = $this->get_jsapi_ticket($ACCESS_TOKEN);
	
		$string = "jsapi_ticket=".$argu['jsapi_ticket']."&noncestr=".$argu['nonceStr']."&timestamp=".$argu['timestamp']."&url=".$argu['url'];
		$argu['signature'] = sha1(trim($string));
		return $argu;
	}

	public function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
		
	
	
	public function view(){
		$_scene = M('yqxScene');
 		if(is_numeric(I('get.id',0))){
			$where2['sceneid_bigint']  = I('get.id',0);
		} else {
			$where2['scenecode_varchar']  = I('get.id',0);
		}
		$where2['delete_int']  = 0;
		$_scene_list2=$_scene->where($where2)->select();
		
		if($_scene_list2[0]['showstatus_int']!=1) {
			if($_scene_list2[0]['userid_int']!= intval( \Common\Org\Cookie::get('mid') )) {
				$where3['sceneid_bigint']  = 267070;
				$_scene_list2=$_scene->where($where3)->select();
			}  
		}  

		$_scenepage = M('yqxScenepage');
		$where['sceneid_bigint']  = $_scene_list2[0]['sceneid_bigint'];
		$_scene_list=$_scenepage->where($where)->order('pagecurrentnum_int asc')->select();

		$jsonstr = '{"success": true,"code": 200,"msg": "操作成功","obj": {"id": '.$_scene_list2[0]['sceneid_bigint'].',"name": '.json_encode($_scene_list2[0]['scenename_varchar']).',"createUser": "'.$_scene_list2[0]['userid_int'].'","type": '.$_scene_list2[0]['scenetype_int'].',"pageMode": '.$_scene_list2[0]['movietype_int'].',"image": {"imgSrc": "'.$_scene_list2[0]['thumbnail_varchar'].'",';
		
		$jsonstr = $jsonstr.'"isAdvancedUser": false';
		
		if($_scene_list2[0]["musicurl_varchar"]!='') {
			$jsonstr = $jsonstr.',"bgAudio": {"url": "'.$_scene_list2[0]["musicurl_varchar"].'","type": "'.$_scene_list2[0]["musictype_int"].'"}';
		}
		$jsonstr = $jsonstr.'},
        "isTpl": 0,
        "isPromotion": 0,
        "status": 1,
        "openLimit": 0,
        "startDate": null,
        "endDate": null,
        "updatetime": 1426045746000,
		"createTime": 1426572693000,
		"publishtime":1426572693000,
        "applyTemplate": 0,
        "applyPromotion": 0,
        "sourceId": null,
        "code": "'.$_scene_list2[0]['scenecode_varchar'].'",
        "description": '.json_encode($_scene_list2[0]['desc_varchar']).',
        "sort": 0,
        "pageCount": 0,
        "dataCount": 0,
        "showCount": 0,
        "userLoginName": null,
        "userName": null
		},
		"map": null,
		"list": [';
		
		$jsonstrtemp = '';
		foreach($_scene_list as $vo) {
			if(strpos($vo["content_text"],'eqs\/link?id')!==false){
				$vo["content_text"]=str_replace('eqs\/link?id','?c=scene&a=link&id',$vo["content_text"]);
			}
			$jsonstrtemp = $jsonstrtemp .'{"id": '.$vo["pageid_bigint"].',"sceneId": '.$vo["sceneid_bigint"].',"num": '.$vo["pagecurrentnum_int"].',"name": null,"properties":'.$vo["properties_text"].',"elements": '.$vo["content_text"].',"scene": null},';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').'';
		$jsonstr = $jsonstr.']}';
		echo $jsonstr;
    }
	
	public function addpv(){
         $returnInfo = D("YqxScene")->addpv();
    }	
	
	public function adduser(){
		$m_scenedata=M('yqxScenedatadetail');
		$datainput['sceneid_bigint'] = I("get.id",0);
		$datainput['ip_varchar'] = get_client_ip();
		$datainput['createtime_time'] = date('y-m-d H:i:s',time());
		$datainput['userid'] = intval( $this->mid );
		
		$datainput['content_varchar'] = json_encode($_POST);
		$result = $m_scenedata->data($datainput)->add();
		if($result)
		{
			$m_scene=M('yqxScene');
			$where['sceneid_bigint'] = I('get.id',0);
			$m_scene->where($where)->setInc('datacount_int');
		}
		
		$jsonstr='{"success":true,"code":200,"msg":"操作成功","obj":null,"map":{"count":0,"pageNo":1,"pageSize":10},"list":[]}';
		echo $jsonstr;
    }
	
	
}