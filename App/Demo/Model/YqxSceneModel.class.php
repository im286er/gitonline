<?php
namespace Demo\Model;
use Think\Model;

class YqxSceneModel extends Model {

    public function addscene() {
		$m_scene=M('YqxScene');
		$m_scene=M('YqxScene');
		$mapp = array();
		$mapp['userid_int'] = \Common\Org\Cookie::get('mid');
		$mapp['delete_int'] = 1;
		$delete_count = $m_scene->where($mapp)->count();
		$mapp2['userid_int'] = \Common\Org\Cookie::get('mid');
		$m_scenepage=M('yqxScenepage');
		$datas = $_POST;

		$datainfo['scenecode_varchar'] = 'U'.(date('y',time())-9).date('m',time()).\Org\Util\String::randString(6, 2, '3425678934567892345678934567892');
		$datainfo['scenename_varchar'] = $datas['name'];
		$datainfo['movietype_int'] = $datas['pageMode'];
		$datainfo['scenetype_int'] = intval($datas['type']);
		$datainfo['ip_varchar'] = get_client_ip();
		$datainfo['thumbnail_varchar'] = "default_thum.jpg";
		$datainfo['userid_int'] = \Common\Org\Cookie::get('mid');
		$datainfo['createtime_time'] = date('y-m-d H:i:s',time());
		
		$result1 = $m_scene->add($datainfo);
		
		if($result1){
			$datainfo2['scenecode_varchar'] = $datainfo['scenecode_varchar'];
			$datainfo2['sceneid_bigint'] = $result1;
			$datainfo2['content_text'] = "[]";
			$datainfo2['properties_text'] = 'null';
			$datainfo2['userid_int'] = \Common\Org\Cookie::get('mid');
			$result2 = $m_scenepage->add($datainfo2);
			echo json_encode(array("success" => true,
									"code"=> 200,
									"msg" => "success",
									"obj"=> $result1,
									"map"=> null,
									"list"=> null
								   )
							);
		}else{
			exit;
		}
    }


    public function addscenebysys() {
		$m_scene=M('yqxScene');
		$mapp = array();
		$mapp['userid_int'] = \Common\Org\Cookie::get('mid');
		$mapp['delete_int'] = 1;
		$delete_count = $m_scene->where($mapp)->count();
		$mapp2 = array();
		$mapp2['userid_int'] = \Common\Org\Cookie::get('mid');
		
		$m_scenepage=M('yqxScenepage');
		$datas = $_POST;
		

		$wheresysscene['userid_int']  = 0;
		$wheresysscene['sceneid_bigint']  = I('post.id',0);
		$_scene_sysinfo=$m_scene->where($wheresysscene)->select();


		$datainfo['scenecode_varchar'] = 'U'.(date('y',time())-9).date('m',time()).date('d',time()).\Org\Util\String::randString(6, 2, '3425678934567892345678934567892');
		$datainfo['scenename_varchar'] = $datas['name'];
		$datainfo['movietype_int'] = $_scene_sysinfo[0]['movietype_int'];
		$datainfo['scenetype_int'] = intval($datas['type']);
		$datainfo['ip_varchar'] = get_client_ip();
		$datainfo['thumbnail_varchar'] = $_scene_sysinfo[0]['thumbnail_varchar'];
		$datainfo['musicurl_varchar'] = $_scene_sysinfo[0]['musicurl_varchar'];
		$datainfo['musictype_int'] = $_scene_sysinfo[0]['musictype_int'];
		$datainfo['fromsceneid_bigint'] = $wheresysscene['sceneid_bigint'];
		$datainfo['userid_int'] = \Common\Org\Cookie::get('mid');
		$datainfo['createtime_time'] = date('y-m-d H:i:s',time());
		
		$result1 = $m_scene->add($datainfo);

		$m_scenedatasys=M('yqxScenedatasys');
		$m_scenedata=M('yqxScenedata');

		if($result1){
			$m_scene->where($wheresysscene)->setInc('usecount_int');
			
			$wheresyspage['userid_int']  = 0;
			$wheresyspage['sceneid_bigint']  = I('post.id',0);
			$_scene_syspageinfo=$m_scenepage->where($wheresyspage)->select();
			foreach($_scene_syspageinfo as $vo){
				$datainfo2['scenecode_varchar'] = $datainfo['scenecode_varchar'];
				$datainfo2['sceneid_bigint'] = $result1;
				$datainfo2['content_text'] = $vo['content_text'];
				$datainfo2['properties_text'] = 'null';
				$datainfo2['pagecurrentnum_int'] = $vo['pagecurrentnum_int'];
				$datainfo2['userid_int'] = \Common\Org\Cookie::get('mid');
				$datainfo2['createtime_time'] = date('y-m-d H:i:s',time());
				$result2 = $m_scenepage->add($datainfo2);
				
				$wheredata['userid_int'] = 0;
				$wheredata['sceneid_bigint'] = $vo['sceneid_bigint'];
				$wheredata['pageid_bigint'] = $vo['pageid_bigint'];
				$_scenedatasys_list = $m_scenedatasys->where($wheredata)->select();

				foreach($_scenedatasys_list as $vo2){
					$dataList[] = array('sceneid_bigint'=>$result1,
						'pageid_bigint'=>$result2,
						'elementid_int'=>$vo2['elementid_int'],
						'elementtitle_varchar'=>$vo2['elementtitle_varchar'],
						'elementtype_int'=>$vo2['elementtype_int'],
						'userid_int'=>\Common\Org\Cookie::get('mid')
					);
				}
			}
			if(count($dataList)>0){
				$m_scenedata->addAll($dataList);
			}
			echo json_encode(array("success" => true,
									"code"=> 200,
									"msg" => "success",
									"obj"=> $result1,
									"map"=> null,
									"list"=> null
								   )
							);
		}else{
			exit;
		}
    }


    public function addscenebycopy() {
		$m_scene=M('yqxScene');
		$mapp = array();
		$mapp['userid_int'] = \Common\Org\Cookie::get('mid');
		$mapp['delete_int'] = 1;
		$delete_count = $m_scene->where($mapp)->count();
		$mapp2 = array();
		$mapp2['userid_int'] = \Common\Org\Cookie::get('mid');
		
		$m_scenepage=M('yqxScenepage');
		$m_scenedata=M('yqxScenedata');
		

		$wheresysscene['userid_int']  = \Common\Org\Cookie::get('mid');
		$wheresysscene['sceneid_bigint']  = I('get.id',0);
		$_scene_sysinfo=$m_scene->where($wheresysscene)->select();


		$datainfo['scenecode_varchar'] = 'U'.(date('y',time())-9).date('m',time()).date('d',time()).\Org\Util\String::randString(6, 2, '3425678934567892345678934567892');
		$datainfo['scenename_varchar'] = '副本-'.$_scene_sysinfo[0]['scenename_varchar'];
		$datainfo['movietype_int'] = $_scene_sysinfo[0]['movietype_int'];
		$datainfo['scenetype_int'] = $_scene_sysinfo[0]['scenetype_int'];
		$datainfo['ip_varchar'] = get_client_ip();
		$datainfo['thumbnail_varchar'] = $_scene_sysinfo[0]['thumbnail_varchar'];
		$datainfo['musicurl_varchar'] = $_scene_sysinfo[0]['musicurl_varchar'];
		$datainfo['musictype_int'] = $_scene_sysinfo[0]['musictype_int'];
		$datainfo['fromsceneid_bigint'] = $wheresysscene['sceneid_bigint'];
		$datainfo['userid_int'] = \Common\Org\Cookie::get('mid');
		$datainfo['createtime_time'] = date('y-m-d H:i:s',time());
		
		$result1 = $m_scene->add($datainfo);
		if($result1){
			$m_scene->where($wheresysscene)->setInc('usecount_int');
			
			$wheresyspage['userid_int']  = \Common\Org\Cookie::get('mid');
			$wheresyspage['sceneid_bigint']  = I('get.id',0);
			$_scene_syspageinfo=$m_scenepage->where($wheresyspage)->select();
			foreach($_scene_syspageinfo as $vo){
				$datainfo2['scenecode_varchar'] = $datainfo['scenecode_varchar'];
				$datainfo2['sceneid_bigint'] = $result1;
				$datainfo2['content_text'] = $vo['content_text'];
				$datainfo2['properties_text'] = 'null';
				$datainfo2['pagecurrentnum_int'] = $vo['pagecurrentnum_int'];
				$datainfo2['userid_int'] = \Common\Org\Cookie::get('mid');
				$datainfo2['createtime_time'] = date('y-m-d H:i:s',time());
				$result2 = $m_scenepage->add($datainfo2);


				$wheredata['userid_int'] = \Common\Org\Cookie::get('mid');
				$wheredata['sceneid_bigint'] = $vo['sceneid_bigint'];
				$wheredata['pageid_bigint'] = $vo['pageid_bigint'];
				$_scenedatasys_list = $m_scenedata->where($wheredata)->select();

				foreach($_scenedatasys_list as $vo2){
					$dataList[] = array('sceneid_bigint'=>$result1,
						'pageid_bigint'=>$result2,
						'elementid_int'=>$vo2['elementid_int'],
						'elementtitle_varchar'=>$vo2['elementtitle_varchar'],
						'elementtype_int'=>$vo2['elementtype_int'],
						'userid_int'=>\Common\Org\Cookie::get('mid')
					);
				}

			}
			if(count($dataList)>0){
				$m_scenedata->addAll($dataList);
			}
			echo json_encode(array("success" => true,
									"code"=> 200,
									"msg" => "success",
									"obj"=> $result1,
									"map"=> null,
									"list"=> null
								   )
							);
		}else{
			exit;
		}
    }

    public function savepage() {
		$m_scene=M('yqxScene');
		$m_scenepage=M('yqxScenepage');
		$datas = json_decode(file_get_contents("php://input"),true);

		$where['pageid_bigint'] = $datas['id'];
		$where['sceneid_bigint'] = $datas['sceneId'];
		$datainfo['pagecurrentnum_int'] = intval($datas['num']);
		$datainfo['content_text'] = json_encode($datas['elements']);
		$datainfo['properties_text'] = json_encode($datas['properties']);
		$where['userid_int'] = \Common\Org\Cookie::get('mid');
		$m_scenepage->data($datainfo)->where($where)->save();

		$wheredata['userid_int'] = \Common\Org\Cookie::get('mid');
		$wheredata['pageid_bigint'] = $where['pageid_bigint'];
		$wheredata['sceneid_bigint'] = $where['sceneid_bigint'];
		$m_scenedata=M('yqxScenedata');
		
		$m_scenedata->where($wheredata)->delete();
		
		$dataList = array();
		foreach ($datas['elements'] as $key => $val ) 
		{	
			if($val['type']==5 || $val['type']==501 || $val['type']==502 || $val['type']==503 ){  // 501姓名、502手机 、503邮箱、5 文本
				$dataList[] = array('sceneid_bigint'=>$where['sceneid_bigint'],
									'pageid_bigint'=>$where['pageid_bigint'],
									'elementid_int'=>$val['id'],
									'elementtitle_varchar'=>$val['title'],
									'elementtype_int'=>$val['type'],
									'userid_int'=>\Common\Org\Cookie::get('mid')
								);
			}

		}
		if(count($dataList)>0){
			$aaaa = $m_scenedata->addAll($dataList);
		}
	
	
		if($datas['scene']['image']['bgAudio']['url']!="")
		{
			$bgdatainfo['musicurl_varchar'] = $datas['scene']['image']['bgAudio']['url'];
			$bgdatainfo['musictype_int'] = $datas['scene']['image']['bgAudio']['type'];
		}else{
			$bgdatainfo['musicurl_varchar'] = '';
		}
		$bgwhere['sceneid_bigint'] = $datas['sceneId'];
		$bgwhere['userid_int'] = \Common\Org\Cookie::get('mid');
		$m_scene->data($bgdatainfo)->where($bgwhere)->save();
		
		echo json_encode(array("success" => true,
								"code"=> 200,
								"msg" => "success",
								"obj"=> $result1,
								"map"=> null,
								"list"=> null
							   )
						);
						
    }

    public function openscene($status) {
		$m_scene=M('yqxScene');
		$datas = json_decode(file_get_contents("php://input"),true);

		$where['sceneid_bigint'] = I('get.id',0);
		$datainfo['showstatus_int'] = $status;
		$where['userid_int'] = \Common\Org\Cookie::get('mid');
		$m_scene->data($datainfo)->where($where)->save();
		
		echo json_encode(array("success" => true,
								"code"=> 200,
								"msg" => "success",
								"obj"=> $result1,
								"map"=> null,
								"list"=> null
							   )
						);
    }


    public function usepage() {
		$m_scene=M('yqxScenepagesys');
		$where['pageid_bigint'] = I('get.id',0);
		$m_scene->where($where)->setInc('usecount_int');
		
    }

    public function addpv() {
		$m_scene=M('yqxScene');
		$where['sceneid_bigint'] = I('get.id',0);
		$m_scene->where($where)->setInc('hitcount_int');
		
    }

    public function savesetting() {
		$m_scene=M('yqxScene');
		$datas = json_decode(file_get_contents("php://input"),true);

		$where['sceneid_bigint'] = $datas['id'];
		$datainfo['scenename_varchar'] = $datas['name'];
		$datainfo['scenetype_int'] = intval($datas['type']);
		$datainfo['movietype_int'] = intval($datas['pageMode']);
		$datainfo['thumbnail_varchar'] = $datas['image']['imgSrc'];
		$datainfo['desc_varchar'] = $datas['description'];
		$where['userid_int'] = \Common\Org\Cookie::get('mid');
		$m_scene->data($datainfo)->where($where)->save();
		
		//var_dump($result1);exit;
		echo json_encode(array("success" => true,
								"code"=> 200,
								"msg" => $m_scene."success".$datainfo['scenename_varchar'],
								"obj"=> $result1,
								"map"=> null,
								"list"=> null
							   )
						);
    }


}

?>
