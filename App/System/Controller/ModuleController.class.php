<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class ModuleController extends ManagerController {
	
	//模块管理
    public function index(){
		$page = new \Think\Page(M('merchantModule')->where('module_pid=0')->count(), 10);
		$moduleLists = M('merchantModule')->where('module_pid=0')->limit($page->firstRow.','.$page->listRows)->select();
		//这里用了一个循环查询，效率很低，不过，模块表的数据不会太多
		foreach($moduleLists as $key=>$module) {
			$moduleLists[$key]['module_list'] = M('merchantModule')->where('module_pid='.$module['module_id'])->select();
		}
		$this->assign('pages', $page->show());
		$this->assign('moduleList', $moduleLists);
		$this->display();
    }		
    
	
	//添加模块
	public function moduleAdd() {
		if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			if( intval($_POST['info']['module_pid'])==0 ) {
				$module_id = M('merchantModule')->add( array('module_pid'=>0, 'module_status'=>'1', 'module_name'=>strip_tags($_POST['info']['module_name']) ) );				
			} else {
				$_POST['info']['module_sign'] = str_replace('/', '_', $_POST['info']['module_link']);
				$_POST['info']['module_industry'] = in_array(0, $_POST['module_industry']) ? ",0," : ','.implode(',', $_POST['module_industry']).',';	
				$module_id = M('merchantModule')->add( $_POST['info'] );
			}
			
			if( $module_id ) { $this->display('Jump:success'); } else { $this->display('Jump:error'); };
		} else {
			$vocationList = F('VocationList');
            if( !is_array($vocationlist) || empty($vocationList) ) B('\Common\Behavior\CreateVocation', '', $vocationList);
            foreach($vocationList as $r) {
                $r['pid'] = $r['v_pid']; $rulesListArray[$r["v_id"]] = $r; 
            }
            $str = "<option value=\$v_id \$selected>\$spacer \$v_title</option>"; 
            $this->assign('vocationList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, $str));
			
			$this->assign('moduleList', M('merchantModule')->where(array('module_pid'=>0, 'module_status'=>'1'))->field('module_id,module_name')->select());
			$this->display();
		}
	}
	
	//为模块添加模板
	public function moduleAddTpl() {
		if( IS_POST ) {	
			$tplname = array();
			foreach($_POST['TplImg'] as $k => $img) {
				if( file_exists(APP_DIR.$img) ) {
					$tplname[] = array('img'=>$img, 'txt'=>isset( $_POST['TplName'][$k] ) ? $_POST['TplName'][$k] : '' );	
				}	
			}
			if( M('merchantModule')->where('module_id='.I('post.mid', 0, 'intval'))->setField('module_template', serialize($tplname)) ) { $this->display('Jump:success'); } else { $this->display('Jump:error'); };
		} else {
			$moduleTpl = M('merchantModule')->where('module_id='.I('get.id', 0, 'intval'))->getField('module_template');
			if( $moduleTpl ) $this->assign('moduleTpl', unserialize($moduleTpl));	
			$this->display();	
		}
	}
	
	//功能模块删除
    public function moduleDelte(){ 
		$mdid = I('get.mdid', ''); if(!mdid) exit('0'); $status = I('get.status', 0, 'intval') == 1 ? '0' : '1';  
        exit(M('merchantModule')->where(array('module_id'=>array('in', "$mdid")))->save(array('module_status'=>$status))!== false ? "1" : "0");   	  
    }
	
	//功能模块编辑
    public function moduleEdit(){ 
		if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
			if( intval($_POST['info']['module_pid'])==0 ) {
				$module_id = M('merchantModule')->where(array('module_id'=>I('post.moduleid')))->save( array('module_pid'=>0, 'module_status'=>'1', 'module_name'=>strip_tags($_POST['info']['module_name']) ) );				
			} else {
				$_POST['info']['module_industry'] = in_array(0, $_POST['module_industry']) ? ",0," : ','.implode(',', $_POST['module_industry']).',';	
				$module_id = M('merchantModule')->where(array('module_id'=>I('post.moduleid')))->save( $_POST['info'] );
			}
			if( $module_id !== false ) { $this->display('Jump:success'); } else { $this->display('Jump:error'); };
    	}else{
			$moduleInfo = M('merchantModule')->where(array('module_id'=>I('get.rid', 0, 'intval')))->find();
			if( $moduleInfo['module_pid'] != 0 ) {
				$vocationList = F('VocationList');
				if( !is_array($vocationlist) || empty($vocationList) ) B('\Common\Behavior\CreateVocation', '', $vocationList);
				foreach($vocationList as $r) {
					$r['pid'] = $r['v_pid']; 
					$r['selected'] = in_array($r['v_id'], explode(',', $moduleInfo['module_industry'])) ? "selected='selected'" : '';
					$rulesListArray[$r["v_id"]] = $r; 
				}
				$str = "<option value=\$v_id \$selected>\$spacer \$v_title</option>"; 
				$this->assign('vocationList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, $str));
				
				$this->assign('moduleList', M('merchantModule')->where(array('module_pid'=>0, 'module_status'=>'1'))->field('module_id,module_name')->select());
			}
			$this->assign('moduleInfo', $moduleInfo);		
			$this->display();
		}	 
    }
 
	//上传图片
    public function kindeditorUpload() {
		if(stripos($_SERVER['HTTP_REFERER'], 'dishuos.com') === false ) E('你无权进行此操作');
	    $uploadPath = realpath(THINK_PATH.'../Public').'/Upload/';
        if(!file_exists($uploadPath)) mkdir($uploadPath, true);
		$uploadConfig = array(
			'rootPath'	=> $uploadPath,
			'exts'		=> 'jpg,jpeg,png',
			'maxSize'	=> 10480
		);
		$attachment = new \Think\Upload( $uploadConfig );
        $attachmentInfo = $attachment->uploadOne($_FILES['imgFile']);
        if($attachmentInfo && is_array($attachmentInfo)) {
            echo json_encode(array('error'=>0, 'url'=>'/Public/Upload/'.date('Y-m-d').'/'.$attachmentInfo['savename'], 'savename'=>basename($attachmentInfo['savename'])));
        } else {
            echo json_encode(array('error'=>1, 'message'=>$attachment->getError()));
        }
    }	
}