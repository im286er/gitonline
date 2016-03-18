<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class AppController extends ManagerController {

    //应用列表
    public function appList() {
		$where = array();
		$where['status'] = array('gt','0');
		if( I('get.keyword', '') ) {
			$keyword=I('get.keyword', ''); $where['name|source|intro']=array('like', "%{$keyword}%", 'or');
		}
		$appCategoryList = F('appCategoryList');
		$this->assign('appCategoryList', $appCategoryList);
		$page = new \Think\Page(M('app')->where($where)->count(), 10);
		$this->assign('applist', M('app')->where($where)->limit($page->firstRow.','.$page->listRows)->select());
		$this->assign('pages', $page->show());
        $this->display();
    }

    //添加应用
    public function appAdd() {
       	if( IS_POST ) {
			$info = I('post.info');
			if( $info['iosurl'] && substr(strtolower($info['iosurl']),-4)=='.ipa') {
				$info['iossize'] = sizecount(filesize(APP_DIR.$info['iosurl']));
				$basename = basename($info['iosurl'], '.ipa');
				$plist = file_get_contents(APP_DIR.'/Public/AppDown/ipa.plist');
			//	$plist = str_replace('#IPA_URL#', "https://www.dishuos.com".$info['iosurl'], $plist);

				$plist = str_replace('com.mayun.#JID#', $info['sign'], $plist);
				$plist = str_replace('#IPA_URL#', "http://www.dishuos.com".$info['iosurl'], $plist);
				
				$plist = str_replace('#IPA_NAME#',  $info['name'], $plist);
				$plist = str_replace('#VERSION#', ( $info['versions']? $info['versions']:'v1.0'), $plist);
				$s=file_put_contents(APP_DIR.dirname($info['iosurl'])."/".$basename.".plist", $plist);
				if($s) $info['iosurl'] = dirname($info['iosurl'])."/".$basename.".plist";
			}
			unset($info['sign']);
			if($info['androidurl'])$info['androidsize'] = sizecount(filesize(APP_DIR.$info['androidurl']));
			$info['addtime'] = time();
		 	if( M('app')->add($info) ) {
				$this->display('Jump:success');	   
			} else { $this->display('Jump:error'); }		   
		} else {
			$this->getappCategory();
			$this->display();
		}
    }
	
	public function getappCategory($categoryid=null){
			$appCategoryList = F('appCategoryList');
            if( !is_array($appCategoryList) || empty($appCategoryList) ) B('\Common\Behavior\CreateAppCategory', '', $appCategoryList);
            foreach($appCategoryList as $r) {
                $r['pid'] = $r['pid'];
                $r["selected"] = isset($categoryid) && intval($categoryid)==$r["id"] ? "selected=\"selected\"" : ""; 
                $rulesListArray[$r["id"]] = $r; 
            }
            $str = "<option value=\$id \$selected>\$spacer \$title</option>"; 
            $this->assign('appCategoryList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, $str));
	}


    //删除应用
    public function appDel() {
        $id = I('post.id', ''); if( !$id ) exit('0'); 
		I('post.status') != '' or exit('0');
		D('app_merchant')->where( array('appid'=>array('in', "$id")) )->setField('status',I('post.status'));
        exit(D('app')->where( array('id'=>array('in', "$id")) )->setField('status',I('post.status') ) ? "1" : "0");
    }
	
	public function appSync(){
		$id = I('post.id', ''); if( !$id ) exit('0'); 
		I('post.sync') != '' or exit('0');
		exit(D('App')->sync($id,1)?'1':'0');
		//D('App')->cancel_sync($value,1);->这里是取消同步
	}
	

	//修改应用
	public function appEdit() {
		if( IS_POST ) {
			$info = I('post.info');
			if( stristr($info['iosurl'], '.ipa') ) {
				$info['iossize'] = sizecount(filesize(APP_DIR.$info['iosurl']));
				$basename = basename($info['iosurl'], '.ipa');
				$plist = file_get_contents(APP_DIR.'/Public/AppDown/ipa.plist');
				$plist = str_replace('#IPA_URL#', "https://www.dishuos.com".$info['iosurl'], $plist);
				$plist = str_replace('com.mayun.#JID#', $info['sign'], $plist);
				$plist = str_replace('#IPA_NAME#',  $info['name'], $plist);
				$plist = str_replace('#VERSION#', ( $info['versions']? $info['versions']:'v1.0'), $plist);
				$s=file_put_contents(APP_DIR.dirname($info['iosurl'])."/".$basename.".plist", $plist);
				if($s) $info['iosurl'] = dirname($info['iosurl'])."/".$basename.".plist";
			}
			unset($info['sign']);
			if($info['androidurl'])$info['androidsize'] = sizecount(filesize(APP_DIR.$info['androidurl']));
		 	if( M('app')->save($info) !== false ) {
				$this->display('Jump:success');	   
			} else { $this->display('Jump:error'); }		   
		} else {
			$app = M('app')->where(array('id'=>I('get.id', 0, 'intval')))->find();
			if(!is_array($app) || empty($app)) { $this->assign('msg', '应用信息不存在'); $this->display('Jump:error'); }
			$this->getappCategory($app['categoryid']);
			$this->assign('app', $app);
			$this->display();
		}
	}


    //行业列表
    public function categoryList() {
        $appCategoryList = F('appCategoryList');
        if( !is_array($appCategoryList) || empty($appCategoryList) ) B('\Common\Behavior\CreateAppCategory', '', $appCategoryList);
        foreach($appCategoryList as $r) {
            $r['pid'] = $r['pid'];
            $r['manage'] = '<a href="javascript:addCategory('.$r['id'].');">添加子分类</a> | <a href="javascript:editCategory('.$r['id'].');">修改</a> | <a href="javascript:void(0);" onclick="delCategory('.$r['id'].')">删除</a>';
            $appCategoryListArray[$r['id']] = $r;
        }
        $str  = "<tr id='TP_RULE_\$id'><td>\$id</td><td style='text-align:left;'>\$spacer\$title</td><td>\$addtime</td><td>\$manage</td></tr>";
        $this->assign('appCategorylist', \Common\Org\Tree::ItreeInitialize()->initialize($appCategoryListArray)->treeView(0, $str));
        $this->display();
    }

    //添加列表
    public function categoryAdd() {
        if( IS_POST ){
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
            if( M('app_category')->add($_POST['info']) ) {
                B('\Common\Behavior\CreateAppCategory'); $this->display('Jump:success');
            } else { $this->display('Jump:error'); };
        } else {
            $appCategoryList = F('appCategoryList');
            if( !is_array($appCategoryList) || empty($appCategoryList) ) B('\Common\Behavior\CreateAppCategory', '', $appCategoryList);
            foreach($appCategoryList as $r) {
                $r['pid'] = $r['pid'];
                $r["selected"] = isset($_GET['pid']) && intval($_GET['pid'])==$r["id"] ? "selected=\"selected\"" : ""; 
                $rulesListArray[$r["id"]] = $r; 
            }
            $str = "<option value=\$id \$selected>\$spacer \$title</option>"; 
            $this->assign('appCategoryList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, $str));
            $this->display();
        }
    }

    //递归删除分类
    public function categoryDel() {
		$vid = I('get.vid', 0, 'intval'); if( !$vid ) exit('');
		$vid = D('AppCategory')->deleteAppCategory($vid); B('\Common\Behavior\CreateAppCategory');
		exit(implode(',', $vid));
    }
    
    //修改分类
    public function categoryEdit() {
        if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
            if( M('app_category')->save($_POST['info']) !== false ) {
               B('\Common\Behavior\CreateAppCategory'); $this->display('Jump:success');
            } else { $this->display('Jump:error'); };
        } else {
            $appCategoryList = F('appCategoryList');
            if( !is_array($appCategoryList) || empty($appCategoryList) ) B('\Common\Behavior\CreateAppCategory', '', $appCategoryList);
            $appCategoryInfo = $appCategoryList[I('get.vid', 0, 'intval')];
            if( !$appCategoryInfo || !is_array($appCategoryInfo) ) { $this->display('Jump:error'); }
            $this->_FilterVocation( $appCategoryList, $appCategoryInfo['id'] );

            foreach($appCategoryList as $vid=>$vocation) {
                if( $vid==$appCategoryInfo['id'] || $vocation['pid']==$vid ) continue;
                $vocation['selected'] = $appCategoryInfo['pid']==$vid ? "selected=\"selected\"" : "";
                $rulesListArray[$vid] = $vocation; 
            }
            $str = "<option value=\$id \$selected>\$spacer \$title</option>"; 
            $this->assign('appCategoryList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, $str));
            $this->assign('appCategoryInfo', $appCategoryInfo);
            $this->display();   
        }
    }

    private function _FilterVocation( &$appCategoryList, $_vid ) {
        foreach( $appCategoryList as $vid=>$vocation ) {
            if( $_vid==$vocation['pid'] ) {
				unset($appCategoryList[$vid]); $this->_FilterVocation($appCategoryList, $vid);
            }
        }
    }


}