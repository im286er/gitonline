<?php
namespace Merchant\Controller;

class DesignController extends MerchantController {
	protected $sid;
	public function _initialize() {
		parent::_initialize();
		C('TMPL_FILE_DEPR','');
		$this->sid = cookie('shop'.$this->jid);
	}


	/**
	 * 后台编辑模板
	 */
	public function ctheme(){
		$sid     = I('sid',$this->sid,'intval');
		//背景图
		$backImg = M('shop')->field('img_url,img_height,theme')->where(array('sid'=>$sid))->find();
		$opt = array(
			'jid' => $this->jid,
			'sid' => $sid,
		);
		$banner_list = M('banner')->where($opt)->order('bid desc')->find();
		//判断新旧模板
		if ( !in_array($backImg['theme'], C('NEW_THEMES')) ) {
			exit('旧模板不支持装修');
		}

		$ghome	 = M('Ghome')->where(array('g_sid'=>$sid))->find();

		$category= M('category')->where(array('sid'=>$sid, 'status'=>1, 'jid'=>$this->jid))->order('corder')->select();

		$order   = $ghome['g_sort'] == 1 ? 'gdate desc' : 'gsales desc'; 

		$goods   = M('Goods')->where(array('sid'=>$ghome['g_sid'], 'cid'=>$ghome['g_cid'], 'gstatus'=>1))->order($order)->limit($ghome['g_num'])->select();

		$cname   = M('category')->where(array('id'=>$ghome['g_cid'], 'status'=>1))->getField('cname');

		$this->assign('goods', $goods);
		$this->assign('cname', $cname);
		$this->assign('category', $category);
		$this->assign('banner_list',$banner_list);
		$this->display('/ct/index');
	}



	//商品列表
	public function goods(){
		$sid     = I('sid',$this->sid,'intval');

		$cid     = I('cid',0 , 'intval');

		$goods   = M('Goods')->where(array('sid'=>$sid, 'cid'=>$cid, 'gstatus'=>1))->select();

		$cname   = M('category')->where(array('id'=>$cid, 'jid'=>$this->jid))->getField('cname');

		$this->assign('goods', $goods);
		$this->assign('cname', $cname);
		$this->assign('category', $category);
		$this->assign('backImg', $backImg);
		$this->assign('sid', $sid);
		$this->assign('cid', $cid);
		$this->display('/ct/goods');
	}



	//评价列表
	public function comment(){
		$this->display('/ct/comment');
	}


	//活动列表
	public function activity(){
		$g_data = I('get.');
		$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');

		//首页显示的活动 start
		$active = M('active');
		$opt = array(
			'av_jid'    => $this->jid,
			'av_status' => 1,
			'av_cid'	=> $g_data['cid'],
			'av_sid'	=> $g_data['sid'],
		);

		$active_list = $active->where($opt)->order('av_id desc')->limit(3)->select();
		$this->assign('active_list',$active_list);
		//首页显示的活动 end
		//分类名称
		$cname   = M('category')->where(array('id'=>$g_data['cid'], 'status'=>1))->getField('cname');

		$this->assign('cname', $cname);
		$this->assign('cid', $g_data['cid']);
		$this->assign('sid', $g_data['sid']);
		$this->display('/ct/activity');
	}


	//活动列表
	public function video(){
		$g_data = I('get.');
		$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');

		//首页显示的活动 start
		$active = M('video');
		$opt = array(
			'jid'     => $this->jid,
			'gstatus' => 1,
			'sid'	  => $g_data['sid'],
		);

		$video = $active->where($opt)->order('gid desc')->limit(3)->select();
		$this->assign('video',$video);
		//首页显示的活动 end
		//分类名称
		$cname   = M('category')->where(array('id'=>$g_data['cid'], 'status'=>1))->getField('cname');

		$this->assign('cname', $cname);
		$this->assign('cid', $g_data['cid']);
		$this->assign('sid', $g_data['sid']);
		$this->display('/ct/video');
	}

	


	//优惠券列表
	public function coupon(){
		$g_data = I('get.');
		$tpl_name = M('merchant')->where(array('jid'=>$this->jid))->getField('theme');
		//首页显示的优惠券 start
		$coupon = M('voucher');
		$opt = array(
			'v.vu_jid'    => $this->jid,
			'v.vu_status' => 1,
			'v.vu_etime'  => array('egt',date("Y-m-d H:i:s")),
			'v.vu_cid'	  => $g_data['cid'],
			'v.vu_sid'	  => $g_data['sid'],
		);

		$NEW_COUPON_NUMBER = C('NEW_COUPON_NUMBER');
		if( $tpl_name=='shouji' ) $NEW_COUPON_NUMBER = 3;
		$coupon_list = $coupon->alias('v')->field('v.*, (v.vu_cum - (SELECT count(*) FROM azd_voucher_user where vu_id=v.vu_id)) as vu_sum')->where($opt)->having('vu_sum>0')->order('v.vu_id desc')->limit( $NEW_COUPON_NUMBER )->select();
		$this->assign('coupon_list',$coupon_list);
		//首页显示的优惠券 end
		//分类名称
		$cname   = M('category')->where(array('id'=>$g_data['cid'], 'status'=>1))->getField('cname');

		$this->assign('cname', $cname);
		$this->assign('cid', $g_data['cid']);
		$this->assign('sid', $g_data['sid']);
		$this->display('/ct/coupon');
	}


	//编辑优惠券
	public function inc_coupon(){
		$this->display('/inc_coupon');
	}


	//大转盘列表
	public function dzp(){
		$this->display('/ct/dzp');
	}



	//关于商家
	public function shop(){
		$app = M("merchant_app")->where(array('jid'=>$this->jid))->find();
		$app['appjs'] = str_replace(chr(32), "&nbsp;",$app['appjs']);
		$this->assign('m_app',$app);
		$page_name = '关于我们';
		if(I('get.jump')==1){
			$this->assign('page_url',U('Index/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		}else{
			$this->assign('page_url',U('User/index',array('opentype'=>cookie('opentype'),'jid'=>$this->jid)));
		}
		$this->assign('page_name',$page_name);
		
		//把所有的分店的电话获取出来
		$shop_tel  = M('shop')->where(array('jid'=>$this->jid, 'status'=>'1'))->field('sname,mservetel')->select();
		//店铺信息
		$sid       = $this->sid == '0' ? I('sid','95') : $this->sid;
		$shop_info = M('shop')->where(array('sid'=>$sid, 'status'=>'1'))->find();

		// $this->funcMenu();
		$this->assign('shop_tel', $shop_tel);
		$this->assign('shop_info', $shop_info);
		$this->display('/ct/shop');
	}


	//编辑关于我们栏目
	public function inc_shop(){
		$sid     = I('sid',$this->sid,'intval');
		if (IS_POST) {
			$p_data = I('post.');
			$opt    = array(
				'sname'    => $p_data['sname'],
				'msaletel' => $p_data['msaletel'],
				'qsj'	   => $p_data['qsj'],
				'psj'	   => $p_data['psj'],
				'ps_time'  => $p_data['ps_time'],
				'mexplain' => $p_data['mexplain'],
				'yy_stime' => $p_data['yy_stime'],
				'yy_etime' => $p_data['yy_etime'],
				'saddress' => $p_data['saddress'],
				'license'  => $p_data['license'],
				'permit'   => $p_data['permit'],
			);
			M('shop')->where(array('sid'=>$p_data['sid']))->save($opt);
		}
		$info = M('shop')->where(array('sid'=>$sid))->find();
		$this->assign('info', $info);
		$this->display('/inc_shop');
	}


	//背景图编辑
	public function inc_banner(){
		$sid     = I('sid',$this->sid,'intval');
		if ( IS_POST ){
			if ( $re = $this->upload() ){
				$opt = array(
					'img_url' 	  => '/Public/Data/'.$this->jid.'/'.$re['file']['savepath'].$re['file']['savename'],
					'img_height'    => I('b_height','356'),
				);

				M('shop')->where(array('sid'=>$sid))->save($opt);
			}
		}
		
		$this->display('/inc_banner');
	}


	//首页推荐列表
	public function inc_indexprolist(){
		$sid     = I('sid',$this->sid,'intval');
		if (IS_POST) {
			$p_data = I('post.');
			$opt    = array(
				'g_num'			=> $p_data['g_num'],
				'g_cid'			=> $p_data['g_cid'],
				'g_sort'		=> $p_data['g_sort'],
				'g_jid'			=> $this->jid,
				'g_sid'			=> $sid,
				'g_date'		=> date('Y-m-d H:i:s')
			);

			M('Ghome')->where(array('g_sid'=>$sid))->save($opt);
		}
		$category = M('category')->where(array('sid'=>$sid, 'jid'=>$this->jid))->select();
		$info	  = M('Ghome')->where(array('g_sid'=>$sid))->find();

		$this->assign('info',$info);
		$this->assign('category',$category);	
		$this->display('/inc_indexprolist');
	}


	//功能列表
	public function inc_menu(){
		$sid     = I('sid',$this->sid,'intval');
		if (IS_POST) {
			$p_data = I('post.');
			$img = $this->upload($p_data['type']);
			if ($p_data['type'] == 2) {
				$opt = array(
					'cname'  => $p_data['cname'],
					'model'	 => $p_data['model'],
					'corder' => $p_data['corder'],
					'sid'	 => $sid,
					'jid'	 => $this->jid,
					'cimg'	 => '/Public/Data/'.$this->jid.'/'.$img['file']['savepath'].$img['file']['savename'],
				);
				M('category')->add($opt);
			}else if($p_data['type'] == 3){
				if ($p_data['cid'] == '') {
					exit('2');
				}
				exit(M('category')->where(array('id'=>$p_data['cid']))->delete() ? '1' :'3' );
			}else{
				foreach ($p_data['id'] as $key => $value) {
					$arr[$key] = array(
						'cname' => $p_data['cname'][$key],
						//'model' => $p_data['model'][$key],
						'corder'=> $p_data['corder'][$key],
						//'sid'	=> $sid,
						//'jid'	=> $this->jid,
					);
					if ($_FILES['file']['name'][$key] =='') {
						$arr[$key]['cimg'] = $p_data['cimg'][$key];
					}else{
						$arr[$key]['cimg'] = '/Public/Data/'.$this->jid.'/'.$img[$key]['savepath'].$img[$key]['savename'];
					}
					M('category')->where(array('id'=>$p_data['id'][$key]))->save($arr[$key]);
				}
				// $map['id'] = array('in',$p_data['id']);
				// $delInfo   = M('category')->where($map)->delete();
				// $result    = M('category')->addAll($arr);
			}
			
		}
		$info = M('category')->where(array('sid'=>$sid, 'status'=>1, 'jid'=>$this->jid))->order('corder')->select();
		//功能列表		
		$module = M('module');
		$func   = $module->table('azd_module O')->join('azd_merchant_module M on O.module_sign=M.module_sign')->field('O.*, M.*, M.id as mid')->where(array('jid'=>$this->jid))->select();
		
		$this->assign('info', $info);
		$this->assign('type', I('type',0));
		$this->assign('func_list', $func);
		$this->display('/inc_menu');
	}


	//编辑分类
	public function inc_class(){
		$g_data = I('get.');
		if (IS_POST) {
			$g_data = I('post.');

			M('category')->where(array('id'=>$g_data['cid']))->setField('cname', $g_data['cname']);
		}
		//分类查询
		$opt = array(
			'status' => 1,
			'id'	 => $g_data['cid'],
		);
		$info = M('category')->where($opt)->find();

		$this->assign('info', $info);
		$this->assign('sid', $g_data['sid']);
		$this->assign('cid', $g_data['cid']);
		$this->display('/inc_activity');
	}



	// 文件上传
    public function upload($type) {
            $upload = new \Think\Upload();// 实例化上传类
		    $upload->maxSize   =     3145728 ;// 设置附件上传大小
		    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		    $upload->rootPath  =     './Public/Data/'.$this->jid.'/'; // 设置附件上传根目录
		    // 上传文件 
		    $info   =   $upload->upload();
		    if(!$info) {// 上传错误提示错误信息
		    	if ($type == 2) {
		    		$this->error($upload->getError());
		    	}else{
		    		return true;
		    	}
		    }else{// 上传成功
	      //       foreach($info as $file){
    			// 	return $file['savepath'].$file['savename'];
    			// }
    			return $info;
		    }
    }




}