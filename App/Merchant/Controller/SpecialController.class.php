<?php
namespace Merchant\Controller;

class SpecialController extends MerchantController {
	
	//列表
	public function splist() {
		$this->display();
	}
	
	//创建修改场景
	public function spcreate() {
		$this->display();	
	}	
	
	//获取系统模板列表
	public function syslist() {
		$_scene = M('yqxScene');
		$where['userid_int']  = 0;
		$where['delete_int']  = 0;
		
		$pageshowsize = I('get.pageSize', 6);
		if($pageshowsize>30)
		{
			$pageshowsize = 30;
		}
		
		$_scene_list = $_scene->where($where)->order('rank desc,  sceneid_bigint desc')->page(I('get.pageNo',1), $pageshowsize)->select();
		$_scene_count = $_scene->where($where) ->count();
		
		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map": {"count": '.$_scene_count.',"pageNo": '.I('get.pageNo',0).',"pageSize": '.$pageshowsize.'},"list": [';
		$jsonstrtemp = '';
		foreach($_scene_list as $vo){
			$jsonstrtemp = $jsonstrtemp .'{
            "id": '.$vo["sceneid_bigint"].',
            "name": '.json_encode($vo["scenename_varchar"]).',
            "createUser": "'.$vo['userid_int'].'",
            "createTime": 1423645519000,
            "type": '.$vo["scenetype_int"].',
            "pageMode": '.$vo["movietype_int"].',
            "image": {
                "bgAudio": {
                    "url": "'.$vo["musicurl_varchar"].'",
                    "type": "'.$vo["musictype_int"].'"
                },
                "imgSrc": "'.$vo["thumbnail_varchar"].'",
                "hideEqAd": false,
                "isAdvancedUser": false
            },
            "isTpl": 0,
            "isPromotion": 0,
            "status": '.$vo['showstatus_int'].',
            "openLimit": 0,
            "submitLimit": 0,
            "startDate": null,
            "endDate": null,
            "accessCode": null,
            "thirdCode": null,
            "updatetime": 1423645519000,
            "publishtime": 1423645519000,
            "applyTemplate": 0,
            "applyPromotion": 0,
            "sourceId": 1225273,
            "code": "'.$vo["scenecode_varchar"].'",
            "description": '.json_encode($vo["desc_varchar"]).',
            "sort": 0,
            "pageCount": 0,
            "dataCount": 0,
            "showCount": '.$vo["hitcount_int"].',
            "userLoginName": null,
            "userName": null
        },';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').']}';
		echo $jsonstr;		
	}

	//获取自人模板列表
	public function mylist() {
		$_scene = M('yqxScene');
		$scenetype = intval(I('get.type',0));
		if($scenetype > 0) {
			$where['scenetype_int']  = $scenetype;
		}
		$where['userid_int']  = intval( $this->mid );

		$where['delete_int']  = 0;
		$pageshowsize = I('get.pageSize',12);
		if($pageshowsize>30){
			$pageshowsize = 30;
		}
		$_scene_list=$_scene->where($where)->order('sceneid_bigint desc')->page(I('get.pageNo',1),$pageshowsize)->select();
		$_scene_count = $_scene->where($where) ->count();
		
		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map": {"count": '.$_scene_count.',"pageNo": '.I('get.pageNo',0).',"pageSize": '.$pageshowsize.'},"list": [';
		$jsonstrtemp = '';
		foreach($_scene_list as $vo){
			$publishtime=$vo['publishtime']>0 ? $vo['publishtime']:'null';
			$updatetime=$vo['updatetime']>0 ? $vo['updatetime']:'null';
			
			$jsonstrtemp = $jsonstrtemp .'{
            "id": '.$vo["sceneid_bigint"].',
            "name": '.json_encode($vo["scenename_varchar"]).',
            "createUser": "'.$vo['userid_int'].'",
            "createTime": 1423645519000,
            "type": '.$vo["scenetype_int"].',
            "pageMode": '.$vo["movietype_int"].',
            "image": {
                "bgAudio": {
                    "url": "'.$vo["musicurl_varchar"].'",
                    "type": "'.$vo["musictype_int"].'"
                },
                "imgSrc": "'.$vo["thumbnail_varchar"].'",
                "hideEqAd": false,
                "isAdvancedUser": false
            },
            "isTpl": 0,
            "isPromotion": 0,
            "status": '.$vo['showstatus_int'].',
            "openLimit": 0,
            "submitLimit": 0,
            "startDate": null,
            "endDate": null,
            "accessCode": null,
            "thirdCode": null,
            "updatetime": '.$updatetime.',
            "publishtime": '.$publishtime.',
            "applyTemplate": 0,
            "applyPromotion": 0,
            "sourceId": 1225273,
            "code": "'.$vo["scenecode_varchar"].'",
            "description": '.json_encode($vo["desc_varchar"]).',
            "sort": 0,
            "pageCount": 0,
            "dataCount": '.$vo["datacount_int"].',
            "showCount": '.$vo["hitcount_int"].',
            "userLoginName": null,
            "userName": null
        },';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').']}';
		echo $jsonstr;
	}

	
	public function create(){
        if (IS_POST) D("YqxScene")->addscene();
    }
	
	
    public function createBySys(){
        if (IS_POST) D("YqxScene")->addscenebysys();
    }


	public function pageList(){
		$_scenepage = M('yqxScenepage');
		$where['sceneid_bigint']  = I('get.id',0);
		$where['mytypl_id'] =0;
		if(intval( $this->mid ) !=1 )
		{
			$where['userid_int']  = intval( $this->mid );
		}
		$_scene_list=$_scenepage->where($where)->order('pagecurrentnum_int asc')->select();
		
		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map":null,"list":[';
		$jsonstrtemp = '';
		foreach($_scene_list as $vo){
			$jsonstrtemp = $jsonstrtemp .'{"id":'.$vo["pageid_bigint"].',"sceneId":'.$vo["sceneid_bigint"].',"num":'.$vo["pagecurrentnum_int"].',"name":"'.$vo["pagename_varchar"].'","properties":null,"elements":null,"scene":null},';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').']}';
		echo $jsonstr;
    }
	
	public function savePageName(){
		if(I('post.id',0)&& I('post.name')){
			$where['pageid_bigint'] = I('post.id',0);
			$where['sceneid_bigint'] = I('post.sceneId',0);
			$where['userid_int'] = $this->mid;
			
			$datainfo['pagename_varchar']= I('post.name');
			M('yqxScenepage')->data($datainfo)->where($where)->save();;
			$jsonstr = '{"success":true,"code":200,"msg":"操作成功","obj":null,"map":null,"list":null}';
		}else{
			$jsonstr = '{"success":false,"code":100,"msg":"操作失败","obj":null,"map":null,"list":null}';
			}
		echo $jsonstr;
	}
	
	public function pageSort(){
 		if(I('get.id',0)){
			$where['pageid_bigint'] = I('get.id',0);
			
  			$where['userid_int'] = $this->mid;
			
			$order=$datainfo['pagecurrentnum_int']= I('get.pos');
			$re_bool= M('yqxScenepage')->data($datainfo)->where($where)->save();
			 
 			$work_id=M('yqxScenepage')->where($where)->getField('sceneid_bigint');
			 
 			 
			 $photoList=	M('yqxScenepage')->field('pagecurrentnum_int,pageid_bigint')->where("pageid_bigint<>{$where['pageid_bigint']} AND sceneid_bigint='$work_id' AND userid_int={$where['userid_int']} AND pagecurrentnum_int>='$order'")->order('pagecurrentnum_int asc')->select();
				
				foreach($photoList as $k=> $row){
					$sort=$row['pagecurrentnum_int']+$k+1;
				    M('yqxScenepage')->where("pageid_bigint={$row[pageid_bigint]}")->save(array('pagecurrentnum_int'=>$sort)); 
 				}					
			 
		}
		$jsonstr = '{"success":true,"code":200,"msg":"操作成功","obj":null,"map":null,"list":null}';
		echo $jsonstr;

	}
	
	
	//复制为系统场景
	public function copyOsScene(){
		$m_scene=M('yqxScene');
		$m_scenepage=M('yqxScenepage');
		$m_scenedata=M('yqxScenedata');
		

		$wheresysscene['sceneid_bigint']  = I('get.id',0);
		$_scene_sysinfo=$m_scene->where($wheresysscene)->select();


		$datainfo['scenecode_varchar'] = 'U'.(date('y',time())-9).date('m',time()).date('d',time()).\Org\Util\String::randString(10, 2, '3425678934567892345678934567892');
		$datainfo['scenename_varchar'] = '副本-'.$_scene_sysinfo[0]['scenename_varchar'];
		$datainfo['movietype_int'] = $_scene_sysinfo[0]['movietype_int'];
		$datainfo['scenetype_int'] = $_scene_sysinfo[0]['scenetype_int'];
		$datainfo['ip_varchar'] = get_client_ip();
		$datainfo['thumbnail_varchar'] = $_scene_sysinfo[0]['thumbnail_varchar'];
		$datainfo['musicurl_varchar'] = $_scene_sysinfo[0]['musicurl_varchar'];
		$datainfo['musictype_int'] = $_scene_sysinfo[0]['musictype_int'];
		$datainfo['fromsceneid_bigint'] = $wheresysscene['sceneid_bigint'];
		$datainfo['userid_int'] = 0;
		$datainfo['createtime_time'] = date('y-m-d H:i:s',time());
		
		$result1 = $m_scene->add($datainfo);
		if($result1){
			$m_scene->where($wheresysscene)->setInc('usecount_int');
			
			 
			$wheresyspage['sceneid_bigint']  = I('get.id',0);
			$_scene_syspageinfo=$m_scenepage->where($wheresyspage)->select();
			foreach($_scene_syspageinfo as $vo){
				$datainfo2['scenecode_varchar'] = $datainfo['scenecode_varchar'];
				$datainfo2['sceneid_bigint'] = $result1;
				$datainfo2['content_text'] = $vo['content_text'];
				$datainfo2['properties_text'] = 'null';
				$datainfo2['pagecurrentnum_int'] = $vo['pagecurrentnum_int'];
				$datainfo2['userid_int'] =0;
				$datainfo2['createtime_time'] = date('y-m-d H:i:s',time());
				$result2 = $m_scenepage->add($datainfo2);
				$wheredata['sceneid_bigint'] = $vo['sceneid_bigint'];
				$wheredata['pageid_bigint'] = $vo['pageid_bigint'];
				$_scenedatasys_list = $m_scenedata->where($wheredata)->select();

				foreach($_scenedatasys_list as $vo2){
					$dataList[] = array('sceneid_bigint'=>$result1,
						'pageid_bigint'=>$result2,
						'elementid_int'=>$vo2['elementid_int'],
						'elementtitle_varchar'=>$vo2['elementtitle_varchar'],
						'elementtype_int'=>$vo2['elementtype_int'],
						'userid_int'=>0
						);
				}

			}
			if(count($dataList)>0){
				$m_scenedata->addAll($dataList);
			}
			echo json_encode(array("success" => true,
								"code"=> 200,
								"msg" => "success",
								"obj"=> null,
								"map"=> null,
								"list"=> null
							   )
						);
		}else{
			echo json_encode(array("success" => true,
								"code"=> 300,
								"msg" => "error",
								"obj"=> null,
								"map"=> null,
								"list"=> null
							   )
						);
		}
	}
	
	public function systag(){
		$m_upfile = M('yqxTag');
		$where['userid_int']  = 0;
		if(I('get.type',0)==1){
			$where['type_int']=88;
		}
		if(I('get.type',0)==2){
			$where['type_int']=2;
		}
		if(I('get.type',0)==11){
			$where['type_int']=array('NEQ',88);;
			$where['type_int']=array('NEQ',2);;
		}
		$where['biztype_int']  = I('get.bizType',0);
		$pageshowsize = 30;
		$m_upfilelist=$m_upfile->where($where)->order('tagid_int asc')->select();
		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map":null,"list":[';
		$jsonstrtemp = '';
		foreach($m_upfilelist as $vo)
        {
			$jsonstrtemp = $jsonstrtemp .'{"id":'.$vo["tagid_int"].',"name":'.json_encode($vo["name_varchar"]).',"createUser":"0","createTime":1423122412000,"bizType":'.$vo["biztype_int"].'},';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').'';
		$jsonstr = $jsonstr.']}';
		
		echo $jsonstr; 
	}
	
	public function syspagetpl(){
		$_scene = M('yqxScenepagesys');
		$scenetype = intval(I('get.tagId',0));
		$where['tagid_int']  = $scenetype;

		$_scene_list=$_scene->where($where)->order('pageid_bigint desc')->select();

		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map": null,"list": [';
		$jsonstrtemp = '';
		foreach($_scene_list as $vo){
			$jsonstrtemp = $jsonstrtemp .'{"id":'.$vo["pageid_bigint"].',"sceneId":1,"num":1,"name":"name","properties":{"thumbSrc":"'.$vo["thumbsrc_varchar"].'"},"elements":null,"scene":null},';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').']}';
		echo $jsonstr;
    }
	
	public function design(){
		$_scenepage = M('yqxScenepage');
		$where['pageid_bigint']  = I('get.id',0);
		if(intval( $this->mid )!=1)
		{
			$where['userid_int']  = intval( $this->mid );
		}
		$_scene_list=$_scenepage->where($where)->select();
		$_scene = M('yqxScene');
		if(intval( $this->mid )!=1)
		{
			$where2['userid_int']  = intval( $this->mid );
		}
		$where2['delete_int']  = 0;
		$where2['sceneid_bigint']  = $_scene_list[0]['sceneid_bigint'];
		$_scene_list2=$_scene->where($where2)->select();     
		
		
		
		$replaceArray = json_decode($_scene_list[0]['content_text'],true);
		foreach($replaceArray as $key => $value){
			$replaceArray[$key]['sceneId'] = $_scene_list[0]['sceneid_bigint'];
			$replaceArray[$key]['pageId'] = $where['pageid_bigint'];
		}
		$replaceArray = json_encode($replaceArray);
			
		
		$jsonstr = '{"success": true,"code": 200,"msg": "success","obj": {"id": '.$_scene_list[0]['pageid_bigint'].',"sceneId": '.$_scene_list[0]['sceneid_bigint'].',"num": '.$_scene_list[0]['pagecurrentnum_int'].',"name": null,"properties": '.$_scene_list[0]["properties_text"].',"elements": '.$replaceArray.',"scene": {"id": '.$_scene_list2[0]['sceneid_bigint'].',"name": '.json_encode($_scene_list2[0]['scenename_varchar']).',"createUser": "'.$_scene_list2[0]['userid_int'].'","createTime": 1425998747000,"type": '.$_scene_list2[0]['scenetype_int'].',"pageMode": '.$_scene_list2[0]['movietype_int'].',"image": {"imgSrc": "'.$_scene_list2[0]['movietype_int'].'","isAdvancedUser": false';
		if($_scene_list2[0]['musicurl_varchar']!=''){
			$jsonstr = $jsonstr.',"bgAudio": {"url": "'.$_scene_list2[0]["musicurl_varchar"].'","type": "'.$_scene_list2[0]["musictype_int"].'"}';
		}
		$jsonstr = $jsonstr.'},"isTpl": 0,"isPromotion": 0,"status": 1,"openLimit": 0,	"submitLimit": 0,	"startDate": null,	"endDate": null,	"accessCode": null,	"thirdCode": null,	"updatetime": 1426038857000,	"publishtime": 1426038857000,	"applyTemplate": 0,	"applyPromotion": 0,	"sourceId": null,	"code": "'.$_scene_list2[0]['scenecode_varchar'].'",	"description": "'.($_scene_list2[0]['desc_varchar']).'",	"sort": 0,"pageCount": 0,	"dataCount": 0,	"showCount": '.$_scene_list2[0]['hitcount_int'].',	"userLoginName": null,"userName": null}},	"map": null,"list": null}';
		echo $jsonstr;
    }
	
	public function createpage(){
		$_scenepage = M('yqxScenepage');
		$_scene = M('yqxScene');
		$where['pageid_bigint']  = I('get.id',0);
		$iscopy  = I('get.copy',"false");
		if(intval( $this->mid )!=1)
		{
			$where['userid_int']  = intval( $this->mid );
		}
		$_scene_list=$_scenepage->where($where)->select();
		if(!$_scene_list)
		{
			header('HTTP/1.1 403 Unauthorized');
			echo json_encode(array("success" => false,"code"=> 403,"msg" => "false","obj"=> null,"map"=> null,"list"=> null));
			exit;
		}
		$datainfo['scenecode_varchar'] = $_scene_list[0]['scenecode_varchar'];
		$datainfo['sceneid_bigint'] = $_scene_list[0]['sceneid_bigint'];
		$datainfo['pagecurrentnum_int'] = $_scene_list[0]['pagecurrentnum_int']+1;
		$datainfo['createtime_time'] = date('y-m-d H:i:s',time());
		if($iscopy=="true")
		{
			$datainfo['content_text'] = $_scene_list[0]['content_text'];
		}
		else
		{
			$datainfo['content_text'] = "[]";
		}
		$datainfo['properties_text'] = 'null';
		$datainfo['userid_int'] = $this->mid;
		$result = $_scenepage->add($datainfo);
		
		$where2['sceneid_bigint']  = $_scene_list[0]['sceneid_bigint'];
		if(intval( $this->mid )!=1)
		{
			$where2['userid_int']  = intval( $this->mid );
		}
		$_scene_list2=$_scene->where($where2)->select();     

		$jsonstr = '{
					"success": true,
					"code": 200,
					"msg": "success",
					"obj": {
						"id": '.$result.',
						"sceneId": '.$_scene_list[0]['sceneid_bigint'].',
						"num": '.($_scene_list[0]['pagecurrentnum_int']+1).',
						"name": null,
						"properties": null,
						"elements": null,
						"scene": {
							"id": '.$_scene_list2[0]['sceneid_bigint'].',
							"name": '.json_encode($_scene_list2[0]['scenename_varchar']).',
							"createUser": "'.$_scene_list2[0]['userid_int'].'",
							"createTime": 1425998747000,
							"type": '.$_scene_list2[0]['scenetype_int'].',
							"pageMode": '.$_scene_list2[0]['movietype_int'].',
							"image": {
								"imgSrc": "'.$_scene_list2[0]['thumbnail_varchar'].'",
								"isAdvancedUser": false
							},
							"isTpl": 0,
							"isPromotion": 0,
							"status": '.$_scene_list2[0]['showstatus_int'].',
							"openLimit": 0,
							"submitLimit": 0,
							"startDate": null,
							"endDate": null,
							"accessCode": null,
							"thirdCode": null,
							"updatetime": 1426039827000,
							"publishtime": 1426039827000,
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
						}
					},
					"map": null,
					"list": null
				}';
				echo $jsonstr;

    }
	
	public function syspageinfo(){
		$_scene = M('yqxScenepagesys');
		$scenetype = intval(I('get.id',0));
		$where['pageid_bigint']  = $scenetype;
		$_scene_list=$_scene->where($where)->select();
		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":{"id":'.$_scene_list[0]['pageid_bigint'].',"sceneId":1,"num":1,"name":"sys","properties":{"thumbSrc":"'.$_scene_list[0]['thumbsrc_varchar'].'"},"elements":'.$_scene_list[0]['content_text'].',"scene":null},"map":null,"list":null}';
		echo $jsonstr;
    }

	public function savepage(){
        if (IS_POST) D("YqxScene")->savepage();
    }
	
	public function usepage(){
        D("YqxScene")->usepage();
    }
	
	public function my(){
		header('Content-type: text/json');
		$m_upfile = M('yqxTag');
		$where['userid_int']  = intval(session("userid"));
		$where['type_int']=1;
		$where['biztype_int']  = 0;
		$pageshowsize = 30;
		$m_upfilelist=$m_upfile->where($where)->order('tagid_int desc')->select();
		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map":null,"list":[';
		$jsonstrtemp = '';
		foreach($m_upfilelist as $vo)
        {
			$jsonstrtemp = $jsonstrtemp .'{"id":'.$vo["tagid_int"].',"name":'.json_encode($vo["name_varchar"]).',"createUser":"0","createTime":1423122412000,"bizType":'.$vo["biztype_int"].'},';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').'';
		$jsonstr = $jsonstr.']}';
		
		echo $jsonstr; 
	}
	
	public function bguserlist(){
		header('Content-type: text/json');
		$m_upfile = M('yqxUpfile');
		$where['userid_int']  = $this->mid;
		$where['biztype_int']  = I('get.bizType',0);
		$where['filetype_int']  = I('get.fileType',0);
		if(I('get.tagId',0)>0)
		{
			$where['tagid_int']  = I('get.tagId',0);
		}
		$where['delete_int']  = 0;
		$pageshowsize = I('get.pageSize',17);
		if($pageshowsize>30){
			$pageshowsize = 30;
		}
		$m_upfilelist=$m_upfile->where($where)->order('fileid_bigint desc')->page(I('get.pageNo',1),$pageshowsize)->select();
		$m_upfile_count = $m_upfile->where($where) ->count();
		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map":{"count":'.$m_upfile_count.',"pageNo":'.I('get.pageNo',1).',"pageSize":'.$pageshowsize.'},"list":[';
		$jsonstrtemp = '';
		foreach($m_upfilelist as $vo)
        {
			$jsonstrtemp = $jsonstrtemp .'{"id":'.$vo["fileid_bigint"].',"name":'.json_encode($vo["filename_varchar"]).',"extName":"'.$vo["ext_varchar"].'","fileType":'.$vo["filetype_int"].',"bizType":'.$vo["biztype_int"].',"path":"'.$vo["filesrc_varchar"].'","tmbPath":"'.$vo["filethumbsrc_varchar"].'","createTime":1426209633000,"createUser":"'.$vo["userid_int"].'","sort":0,"size":'.$vo["sizekb_int"].',"status":1},';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').'';
		$jsonstr = $jsonstr.']}';
		echo $jsonstr; 
	}
	
	public function bgsyslist(){
		$m_upfile = M('yqxUpfilesys');
		$where['filetype_int']  = I('get.fileType',0);
		if(I('get.tagId',0)>0){
			$where['tagid_int']  = I('get.tagId',0);
		}
		if(I('get.bizType',0)>0){
			$where['biztype_int']  = I('get.bizType',0);
		}
		$pageshowsize = I('get.pageSize',17);
		if($pageshowsize>40){
			$pageshowsize = 40;
		}
		$m_upfilelist=$m_upfile->where($where)->order('fileid_bigint asc')->page(I('get.pageNo',1),$pageshowsize)->select();
		$m_upfile_count = $m_upfile->where($where) ->count();
		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map":{"count":'.$m_upfile_count.',"pageNo":'.I('get.pageNo',1).',"pageSize":'.$pageshowsize.'},"list":[';
		$jsonstrtemp = '';
		foreach($m_upfilelist as $vo)
        {
			$jsonstrtemp = $jsonstrtemp .'{"id":'.$vo["fileid_bigint"].',"name":'.json_encode($vo["filename_varchar"]).',"extName":"'.$vo["ext_varchar"].'","fileType":'.$vo["filetype_int"].',"bizType":'.$vo["biztype_int"].',"path":"'.$vo["filesrc_varchar"].'","tmbPath":"'.$vo["filethumbsrc_varchar"].'","createTime":1426209633000,"createUser":"'.$vo["userid_int"].'","sort":0,"size":'.$vo["sizekb_int"].',"status":1},';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').'';
		$jsonstr = $jsonstr.']}';
		
		echo $jsonstr; 
	}
	
	public function delpage(){
		$map['pageid_bigint']= I('get.id',0);
		if(intval( $this->mid )!=1)
		{
			$map['userid_int']  = intval( $this->mid );
		}
        M("yqxScenepage")->where($map)->delete();
		echo json_encode(array(
				"success" => true,
				"code"=> 200,
				"msg" => "success",
				"obj"=> null,
				"map"=> null,
				"list"=> null
			   )
		);
    }
	
	public function createtag(){
		$m_scenedata=M('yqxTag');
		$datainput['name_varchar'] = I("post.tagName",'');
		$datainput['type_int'] = 1;
		$datainput['biztype_int'] = 0;
		$datainput['userid_int'] = intval( $this->mid );
		$datainput['create_time'] = date('y-m-d H:i:s',time());
		$result = $m_scenedata->data($datainput)->add();
		$jsonstr='{"success":true,"code":200,"msg":"操作成功","obj":'.$result.',"map":null,"list":null}';
		echo $jsonstr;
    }
	
	public function publish(){
		$m_scene=M('yqxScene');	 
		$where['sceneid_bigint'] = I('get.id',0);
		$datainfo['publishtime'] = time();
		$where['userid_int'] = $this->mid;
		if($m_scene->data($datainfo)->where($where)->save()){
			$jsonstr='{"success":true,"code":200,"msg":"操作成功","obj":null,"map":null,"list":null}';	
		}else{
			$jsonstr='{"success":false,"code":101,"msg":"操作失败","obj":null,"map":null,"list":null}';	
		}
 		echo $jsonstr;
	}
	
	
	public function detail(){
		$_scene = M('yqxScene');
		if(intval( $this->mid )!=1)
		{
			$where['userid_int']  = intval( $this->mid );
		}
		$where['sceneid_bigint']  = I('get.id',0);
		$where['delete_int']  = 0;
		$_scene_list=$_scene->where($where)->select();     

		$jsonstr = '{
			"success": true,
			"code": 200,
			"msg": "success",
			"obj": {
				"id": '.$_scene_list[0]['sceneid_bigint'].',
				"name": '.json_encode($_scene_list[0]['scenename_varchar']).',
				"createUser": "'.$_scene_list[0]['userid_int'].'",
				"createTime": 1425998747000,
				"type": '.$_scene_list[0]['scenetype_int'].',
				"pageMode": '.$_scene_list[0]['movietype_int'].',
				"image": {
					"imgSrc": "'.$_scene_list[0]['thumbnail_varchar'].'",
					"isAdvancedUser": false';
				
				if($_scene_list[0]["musicurl_varchar"]!='')
				{
					$jsonstr = $jsonstr.',"bgAudio": {"url": "'.$_scene_list[0]["musicurl_varchar"].'","type": "'.$_scene_list[0]["musictype_int"].'"}';
				}
				$jsonstr = $jsonstr.'},
				"isTpl": 0,
				"isPromotion": 0,
				"status": '.$_scene_list[0]['showstatus_int'].',
				"openLimit": 0,
				"submitLimit": 0,
				"startDate": null,
				"endDate": null,
				"accessCode": null,
				"thirdCode": null,
				"updatetime": 1426041829000,
				"publishtime": 1426041829000,
				"applyTemplate": 0,
				"applyPromotion": 0,
				"sourceId": null,
				"code": "'.$_scene_list[0]['scenecode_varchar'].'",
				"description": '.json_encode($_scene_list[0]['desc_varchar']).',
				"sort": 0,
				"pageCount": 0,
				"dataCount": '.$_scene_list[0]["datacount_int"].',
				"showCount": '.$_scene_list[0]['hitcount_int'].',
				"userLoginName": null,
				"userName": null
			},
			"map": null,
			"list": null
		}';
		echo $jsonstr;
    }

	
	public function getdata(){
		$_scenedata = M('yqxScenedata');
		$_scenedatadetail = M('yqxScenedatadetail');
		$where['sceneid_bigint']  = I('get.id',0);
		$where['userid_int']  = intval( $this->mid );
		$_scene_list=$_scenedata->where($where)->order('dataid_bigint asc')->select();


		$pageshowsize = I('get.pageSize',10);
		if($pageshowsize>10){
			$pageshowsize = 10;
		}

		$wheredetail['sceneid_bigint']  = I('get.id',0);
		$_scenedatadetail_list=$_scenedatadetail->where($wheredetail)->order('detailid_bigint desc')->page(I('get.pageNo',1),$pageshowsize)->select();
		$_scenedatadetail_count=$_scenedatadetail->where($wheredetail)->count();
		if(count($_scene_list)>0)
		{
			$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map":{"count":'.$_scenedatadetail_count.',"pageNo":'.I('get.pageNo',0).',"pageSize": '.$pageshowsize.'},"list":[[';
			$jsonstrtemp = '';
			$listkey='';
			foreach($_scene_list as $vo){
				$jsonstrtemp = $jsonstrtemp .''.json_encode($vo["elementtitle_varchar"]).',';
				$listkey=$listkey .$vo["elementid_int"].',';
			}
			$listkey = explode(',',rtrim($listkey,','));
			$jsonstr = $jsonstr.$jsonstrtemp.'"时间"],';
			$jsonstrtemp = '';			
			foreach($_scenedatadetail_list as $vo2){
				$tempjson = json_decode($vo2["content_varchar"],true);
				$jsonstrtemp = $jsonstrtemp.'[';			
				foreach($listkey as $vo3){
					$jsonstrtemp = $jsonstrtemp .json_encode($tempjson['eq']['f_'.$vo3]).',';
				}
				$jsonstrtemp = $jsonstrtemp.'"'.$vo2['createtime_time'].'"],';			
			}
			if($jsonstrtemp == '')
			{
				$jsonstrtemp = '[]';
			}
			$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').']}';
		}
		else
		{
			$jsonstr='{"success":true,"code":200,"msg":"操作成功","obj":null,"map":{"count":0,"pageNo":1,"pageSize":10},"list":[]}';
		}
		echo $jsonstr;

    }
	
	public function on(){
        $returnInfo = D("YqxScene")->openscene(1);
    }
	
    public function off(){
        $returnInfo = D("YqxScene")->openscene(2);
    }
	
	public function saveSettings(){
        if (IS_POST) {
            $returnInfo = D("YqxScene")->savesetting();
		}
    }
	
	public function createByCopy(){
        $returnInfo = D("YqxScene")->addscenebycopy();
    }
	
	public function delscene(){
		$map['sceneid_bigint']= I('get.id',0);
		if(intval( $this->mid )!=1) {
			$map['userid_int']  = $this->mid;
		}
		$datainfo['delete_int'] = 1;
		M("yqxScene")->data($datainfo)->where($map)->save();

		echo json_encode(array("success" => true,
								"code"=> 200,
								"msg" => "success",
								"obj"=> null,
								"map"=> null,
								"list"=> null
							   )
						);
    }
	
	public function opencount(){
		$_scene = M('yqxScene');
		$where['userid_int']  = intval( $this->mid );
		$where['delete_int']  = 0;
		$where['showstatus_int']  = 1;
		$_scene_list=$_scene->where($where)->count();
		echo '{"success":true,"code":200,"msg":"success","obj":'.$_scene_list.',"map":null,"list":null}';
    }
	
	public function pvcount(){
		$_scene = M('yqxScene');
		$where['userid_int']  = intval( $this->mid );
		$where['delete_int']  = 0;
		$_scene_list=$_scene->where($where)->sum('hitcount_int');
		echo '{"success":true,"code":200,"msg":"success","obj":'.$_scene_list.',"map":null,"list":null}';
    }
	
	public function getcount(){
		echo json_encode(array("success" => true,
								"code"=> 200,
								"msg" => "success",
								"obj"=> null,
								"map"=> null,
								"list"=> null
							   )
						);
    }
	
	public function newDataScene(){
		$jsonstr='{"success":true,"code":200,"msg":"操作成功","obj":null,"map":null,"list":[';  //{"ID":3458342,"TITLE":"my雅思逢考必过公益讲座"}]}
		$_scene = M('yqxScene');
		$where['userid_int']  = $this->mid;
		$where['datacount_int']  = array('gt',0);
		$jsonstrtemp = '';
		$_scene_list=$_scene->where($where)->field('sceneid_bigint,scenename_varchar')->order('updatetime desc')->select();
		foreach($_scene_list as $k=>$vo){
			$detailid=M('yqxScenedatadetail')->where("sceneid_bigint='".$vo['sceneid_bigint']."' AND is_import=0")->getField('detailid_bigint');
			if($detailid){
				$jsonstrtemp = $jsonstrtemp .'{
					"ID": '.$vo["sceneid_bigint"].',
					"TITLE":"'.$vo["scenename_varchar"].'"		 
				},';
			}
		}
		
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').']}';
		echo $jsonstr;
	} 
	
	public function prospectCount(){
		$model = M('yqxScenedatadetail');
		$where['userid']  = $this->mid;
		$where['is_import']  = 0;
		$count=$model->where($where)->count();
		echo '{"success":true,"code":200,"msg":"success","obj":'.$count.',"map":null,"list":null}';
	}
	
	public function count(){
		$_scene = M('yqxCustomer');
		$where['userid']  = $this->mid;
		$where['status']  = 1;
		$_scene_list=$_scene->where($where)->count();
		echo '{"success":true,"code":200,"msg":"success","obj":'.$_scene_list.',"map":null,"list":null}';
	}
	
	public function getAllData(){
		$pageshowsize = I('get.pageSize',6);
		$where['userid']  = intval( $this->mid );	 
		$_scene_list=M('yqxCustomer')->where($where)->order('createTime desc')->page(I('get.pageNo',1),$pageshowsize)->select();
		$_scene_count = M('yqxCustomer')->where($where) ->count();
 		$jsonstr = '{"success":true,"code":200,"msg":"success","obj":null,"map": {"count": '.$_scene_count.',"pageNo": '.I('get.pageNo',0).',"pageSize": '.$pageshowsize.'},"list": [';
		
		$jsonstrtemp = '';
		foreach($_scene_list as $vo){
			$vo["groupId"]=$vo["groupId"]?intval($vo["groupId"]) : 'null';
			$vo["groupId"]=0;
			$jsonstrtemp = $jsonstrtemp .'{
            "id": '.$vo["id"].',
            "name":"'.$vo["name"].'",
			  "sex": "'.$vo['sex'].'",
			  "mobile": "'.$vo['mobile'].'",
            "tel": "'.$vo['tel'].'",                  
            "email": "'.$vo["email"].'",  
			   "company": "'.$vo["company"].'",  
			   "job": "'.$vo["job"].'",  
			   "address": "'.$vo["address"].'",  
			   "website": "'.$vo["website"].'",  
			   "qq": "'.$vo["qq"].'",  
			   "weixin": "'.$vo["weixin"].'",  
			   "yixin": "'.$vo["yixin"].'",  
			   "weibo": "'.$vo["weibo"].'",  
			  "laiwang": "'.$vo["laiwang"].'",  
			  "remark": "'.$vo["remark"].'",  
			  "origin": '.$vo["origin"].',  
			  "originName": "'.$vo["originname"].'",  
			  "status": '.$vo['status'].', 
            "createUser": "'.$vo['userid_int'].'",
            "createTime":'.$vo['createtime'].', 
            "groupId":'.$vo["groupId"].',
            "groupName": "'.$vo["groupname"].'",            
            "group": null
        },';
		}
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').']}';
		echo $jsonstr;
	}
	
	public function upload(){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = 3145728 ;// 设置附件上传大小
		if(I('get.fileType',0)==2) {
			$upload->exts = array('mp3');// 设置附件上传类型
			$upload->savePath = 'mp3/'.$this->mid.'/'; // 设置附件上传（子）目录
		} else {
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->savePath = 'pic/'.$this->mid.'/'; // 设置附件上传（子）目录
		}
		
		//$upload->rootPath = realpath(THINK_PATH.'../Public/Yqx/');; // 设置附件上传根目录
		$upload->rootPath = './Public/Yqx/'; 
		
		$upload->subName  = array('date','Ym');
		$upload->saveName = 'uniqid';
		$info = $upload->upload();
		if(!$info) {// 上传错误提示错误信息
			header('Content-type: text/json');
			header('HTTP/1.1 401 error');
			echo json_encode(array("success" => false,"code"=> 1001,"msg" => "文件上传错误!","obj"=> null,"map"=> null,"list"=> null));
			echo $this->error($upload->getError()); exit;
		}else{
			header('Content-type: text/json');
			header('HTTP/1.1 200 ok');
			foreach($info as $file){
				$thubimagenew = $file['savepath'].$file['savename'];
				if(I('get.fileType',0)!=2) {
					$image = new \Think\Image(); 
					$thubimage = $file['savepath'].$file['savename'];
					$image->open($upload->rootPath.$thubimage);
					$thubimagenew = str_replace(".".$file['ext'],"_thumb.".$file['ext'],$file['savename']);
					$thubimagenewftp =$thubimagenew;
					$thubimagenew =  $file['savepath'].$thubimagenew;
					if(I('get.fileType',0)==0) {
						$s = $image->thumb(80, 126)->save($upload->rootPath.$thubimagenew);
					} else {
						$s = $image->thumb(80, 80)->save($upload->rootPath.$thubimagenew);
					}
				}
				$sizeint = intval($file['size']/1024);
				$jsonstr = '{"success":true,"code":200,"msg":"success","obj":{"id":9386090,"name":"'.$file['savename'].'","extName":"'.strtoupper($file['ext']).'","fileType":0,"bizType":0,"path":"'.$file['savepath'].$file['savename'].'","tmbPath":"'.$thubimagenew.'","createTime":1426209412922,"createUser":"'.session("userid").'","sort":0,"size":'.$sizeint.',"status":1},"map":null,"list":null}';
				$model = M('yqxUpfile');
				$data['ext_varchar'] = strtoupper($file['ext']);
				$data['filename_varchar'] = $file['name'];
				$data['filetype_int'] = I('get.fileType',0);
				$data['biztype_int'] = I('get.bizType',0);
				$data['userid_int'] = $this->mid;
				$data['filesrc_varchar'] = $file['savepath'].$file['savename'];
				$data['sizekb_int'] = $sizeint;
				$data['filethumbsrc_varchar'] = $thubimagenew;
				$data['create_time'] = date('y-m-d H:i:s',time());
				$model->add($data);
				echo $jsonstr;
			}
		}
    }

	public function deletepic(){
		$m_file = M("yqxUpfile");
		$map['fileid_bigint']= I('post.id',0);
		if(intval( $this->mid )!=1)
		{
			$map['userid_int']  = $this->mid;
		}
		$fileinfo=$m_file->where($map)->select();
		if($fileinfo)
		{
			try {
				$fullpath="./Public/Yqx/".$fileinfo[0]["filethumbsrc_varchar"];
				unlink($fullpath);
			} catch (Exception $e) {}
			try {
				$fullpath="./userfiles/".$fileinfo[0]["filesrc_varchar"];
				unlink($fullpath);
			} catch (Exception $e) {   

				$datainfo['delete_int'] = 1;
				$m_file->data($datainfo)->where($map)->save();

				echo json_encode(array("success" => false,
						"code"=> 404,
						"msg" => "delerror",
						"obj"=> null,
						"map"=> null,
						"list"=> null
					   )
				);
				exit();   
			}   
			$m_file->where($map)->delete();
			echo json_encode(array("success" => true,
									"code"=> 200,
									"msg" => "success",
									"obj"=> null,
									"map"=> null,
									"list"=> null
								   )
							);
		}
    }	
	
	public function spcrop(){
		$src=APP_DIR.'/Public/Yqx/'. I('post.src');
		$ImageCut = new \Think\ImageCut($src, I('post.x'), I('post.y'), I('post.x2'), I('post.y2'));// 实例化上传类
		$returnImg=$ImageCut->generate_shot();
		$returnImg=str_replace(APP_DIR.'/Public/Yqx/','',$returnImg);
		$jsonstr = '{"success":true,"code":200,"msg":"操作成功","obj":"'.$returnImg.'","map":null,"list":null}';	
		echo $jsonstr;
	}

	public function checkuser() {
		if($this->mid > 0) {
			$property='null';
			$mytplid=M('yqxMytpl')->where('userid_int='.$this->mid)->getField('id');
 			if($mytplid){
				$property='{\"myTplId\":'.$mytplid.'}';
 			}
			
			$mname = M('member')->where("mid=".$this->mid)->getField('mname');
			
			$userInfoStr='"id":"'.$this->mid.'","loginName":"'.$mname.'","xd":0,"sex":1,"phone":null,"tel":null,"qq":null,"headImg":"","idNum":null,"idPhoto":null,"regTime":1425093623000,"extType":0,"property":"'.$property.'","companyId":null,"deptName":null,"deptId":0,"name":"'.$mname.'","email":null,"type":1,"status":0,"relType":null,"companyTplId":null,"roleIdList":[]';
			$jsonStr='{"success":true,"code":200,"msg":"操作成功","obj":{'.$userInfoStr.'},"map":null,"list":null}';
			echo $jsonStr;
		} else {
			echo json_encode(array("success" => false,"code"=> 1001,"msg" => "未登录","obj"=> null,"map"=> null,"list"=> null));
		}	
		
	}


	public function saveMyTpl(){
		$m_scenepage=M('yqxScenepage');
		$datas = json_decode(file_get_contents("php://input"),true);

	 
		$myTplId = intval($datas['sceneId']);
		if(!$myTplId){
			$myTplId=M('yqxMytpl')->add(array('userid_int'=>intval($this->mid))); 
		}
		if($myTplId){
			
			$datainfo['pagecurrentnum_int'] = intval($datas['num']);
			$datainfo['content_text'] = json_encode($datas['elements']);
			
			$datainfo['properties_text'] =  'null';
			$datainfo['scenecode_varchar'] =  'U6040278S2';
			$datainfo['pagename_varchar'] =  $datas['name'] ;
			$datainfo['userid_int'] = intval( $this->mid );
			$datainfo['createtime_time'] = date('y-m-d H:i:s',time());
			$datainfo['sceneid_bigint'] = $myTplId;
			$datainfo['mytypl_id'] = $myTplId;		
			$m_scenepage->add($datainfo);
			$jsonstr='{"success":true,"code":200,"msg":"操作成功","obj":'.$myTplId.',"map":null,"list":null}';
 			
			
		}else{
 			$jsonStr='{"success":false,"code":100,"msg":"操作失败","obj":'.$myTplId.',"map":null,"list":null}';
			 
		}
		echo $jsonstr;
			
	}
	
	
	public function getMyTpl(){
		$jsonstr='{"success":true,"code":200,"msg":"操作成功","obj":null,"map":null,"list":[';
		
		$where['mytypl_id']= I('get.id',0);
		$where['userid_int']  = intval( $this->mid );
		$_scene_list= M('yqxScenepage')->where($where)->order('pagecurrentnum_int asc')->select();
		$jsonstrtemp = '';
		foreach($_scene_list as $vo){
			
			$replaceArray = json_decode($vo['content_text'],true);
			foreach($replaceArray as $key => $value){
				$replaceArray[$key]['sceneId'] = $where['mytypl_id'];
				$replaceArray[$key]['pageId'] = $vo['pageid_bigint'];
			}
			$replaceArray = json_encode($replaceArray);
			
			$jsonstrtemp = $jsonstrtemp .'{
			 "id": '.$vo["pageid_bigint"].',
            "sceneId": '.$where['mytypl_id'].',
            "name": '.json_encode($vo["scenename_varchar"]).', 
            "num": '.$vo["pagecurrentnum_int"].', 
            "properties": null, 
            "elements": '.$replaceArray.', 
            "scene": null
        },';
		}
		
		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').']}';
		echo $jsonstr;
	}


	public function formField(){
		$jsonstr = '{"success":true,"code":200,"msg":"操作成功","obj":null,"map":null,"list":[';
 		$_scenedata = M('yqxScenedata');	 
		$where['sceneid_bigint']  = I('get.id',0);
		$where['userid_int']  = intval( $this->mid );
		$_scene_list=$_scenedata->where($where)->order('dataid_bigint asc')->select();
		foreach($_scene_list as $k=>$vo) {
			$jsonstrtemp = $jsonstrtemp .'{
			"id": '.$vo["elementid_int"].',  
			"title":"'.$vo["elementtitle_varchar"].'"		 
				},';
		}

		$jsonstr = $jsonstr.rtrim($jsonstrtemp,',').']}';
		echo $jsonstr;
    }
}