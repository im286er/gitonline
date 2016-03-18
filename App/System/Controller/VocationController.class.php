<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class VocationController extends ManagerController {
    //行业列表
    public function vocationList() {
        $vocationList = F('VocationList');
        if( !is_array($vocationlist) || empty($vocationList) ) B('\Common\Behavior\CreateVocation', '', $vocationList);
        foreach($vocationList as $r) {
            $r['pid'] = $r['v_pid'];
            $r['v_manage'] = '<a href="javascript:addVocation('.$r['v_id'].');">添加子行业</a> | <a href="javascript:editVocation('.$r['v_id'].');">修改</a> | <a href="javascript:void(0);" onclick="delVocation('.$r['v_id'].')">删除</a>';
            $vocationListArray[$r['v_id']] = $r;
        }
        $str  = "<tr id='TP_RULE_\$v_id'><td>\$v_id</td><td style='text-align:left;'>\$spacer\$v_title</td><td>\$v_time</td><td>\$v_manage</td></tr>"; 
        $this->assign('vocationlist', \Common\Org\Tree::ItreeInitialize()->initialize($vocationListArray)->treeView(0, $str));
        $this->display();
    }

    //添加列表
    public function vocationAdd() {
        if( IS_POST ){
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
            if( M('vocation')->add($_POST['info']) ) {
                B('\Common\Behavior\CreateVocation'); $this->display('Jump:success');
            } else { $this->display('Jump:error'); };
        } else {
            $vocationList = F('VocationList');
            if( !is_array($vocationlist) || empty($vocationList) ) B('\Common\Behavior\CreateVocation', '', $vocationList);
            foreach($vocationList as $r) {
                $r['pid'] = $r['v_pid'];
                $r["selected"] = isset($_GET['pid']) && intval($_GET['pid'])==$r["v_id"] ? "selected=\"selected\"" : ""; 
                $rulesListArray[$r["v_id"]] = $r; 
            }
            $str = "<option value=\$v_id \$selected>\$spacer \$v_title</option>"; 
            $this->assign('vocationList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, $str));
            $this->display();
        }
    }

    //递归删除行业
    public function vocationDel() {
		$vid = I('get.vid', 0, 'intval'); if( !$vid ) exit('');
		$vid = D('vocation')->deleteVocation($vid); B('\Common\Behavior\CreateVocation');
		exit(implode(',', $vid));
    }
    
    //修改行业
    public function vocationEdit() {
        if( IS_POST ) {
			array_walk($_POST['info'], function(&$value, $key) { $value=htmlentities($value, ENT_NOQUOTES, "utf-8"); });
            if( M('vocation')->save($_POST['info']) !== false ) {
               B('\Common\Behavior\CreateVocation'); $this->display('Jump:success');
            } else { $this->display('Jump:error'); };
        } else {
            $vocationList = F('VocationList');
            if( !is_array($vocationlist) || empty($vocationList) ) B('\Common\Behavior\CreateVocation', '', $vocationList);
            $vocationInfo = $vocationList[I('get.vid', 0, 'intval')];
            if( !$vocationInfo || !is_array($vocationInfo) ) { $this->display('Jump:error'); }
            $this->_FilterVocation( $vocationList, $vocationInfo['v_id'] );

            foreach($vocationList as $vid=>$vocation) {
                if( $vid==$vocationInfo['v_id'] || $vocation['v_pid']==$vid ) continue;
                $vocation['selected'] = $vocationInfo['v_pid']==$vid ? "selected=\"selected\"" : "";
                $rulesListArray[$vid] = $vocation; 
            }
            $str = "<option value=\$v_id \$selected>\$spacer \$v_title</option>"; 
            $this->assign('vocationList', \Common\Org\Tree::ItreeInitialize()->initialize($rulesListArray)->treeRule(0, $str));
            $this->assign('vocationInfo', $vocationInfo);
            $this->display();   
        }
    }

    private function _FilterVocation( &$vocationList, $_vid ) {
        foreach( $vocationList as $vid=>$vocation ) {
            if( $_vid==$vocation['v_pid'] ) {
				unset($vocationList[$vid]); $this->_FilterVocation($vocationList, $vid);
            }
        }
    }


}