<?php
namespace Merchant\Controller;

class AdvertController extends MerchantController {

	public function setAdvert() {
		//查询当前模板的广告位数
		$adnumber = M('theme')->where(array("t_sign"=>I('get.tid')))->getField("t_adnum");
		if( $adnumber <= 0 ) E('当前模板不需要设置广告');
		!$_GET['pos'] && $_GET['pos']=1;
		$this->assign("adnumber", $adnumber);
		
		//判断当前广告位所使用的广告类型
		$adtype = M("advert")->where( array("ad_jid"=>$this->jid, "ad_tid"=>I('get.tid',''), "ad_pos"=>intval($_GET['pos'])) )->field("ad_type")->find();
		$adtype = isset($adtype['ad_type']) && !empty($adtype['ad_type']) ? $adtype['ad_type'] : '';
		$this->assign("adtype", $adtype);
		
		$advertFileList = glob( APP_PATH.MODULE_NAME.'/'.C('DEFAULT_V_LAYER').'/Advert/*.html');
		$advertList = array();
		foreach($advertFileList as $ad) {
			$adtype = basename($ad, '.html');
			$adimglist_arr = $adimglist = array();
			$adimglist_arr = M("advert")->where( array("ad_jid"=>$this->jid, "ad_pos"=>I("get.pos"), "ad_type"=>$adtype) )->select();
			foreach($adimglist_arr as $_ad) {
				if( !empty($_ad['ad_imglink']) && strpos($_ad['ad_imglink'], "http") !== 0 ) {
					$_ad['ad_imglink'] = "http://".$_ad['ad_imglink'];	
				}
				$adimglist[$_ad['ad_imgid']] = $_ad;
			}
			$this->assign('houtai',1);
			$this->assign('advert', $adimglist);
			$advertList[] = $this->fetch( $ad );
		}
		$this->assign('advertList', $advertList);
		$this->display();	
	}
	
	
	public function ajaxSetImage() {
		$where['ad_jid'] = $this->jid;
		$where['ad_tid'] = I('post.t', 0, 'intval');
		$where['ad_pos'] = I('post.p', 0, 'intval');
		$where['ad_imgid'] = I('post.i', 0, 'intval');
		$where['ad_type'] = I('post.s', '');
		
		$imagelink = I('post.l');
		if( strpos($imagelink, "http") !== 0 ) {
			$imagelink = "http://".$imagelink;	
		}
		M("advert")->where($where)->setField( array('ad_imgsrc'=>I('post.v'), 'ad_imglink'=> $imagelink, 'ad_title'=>I('post.b')) );
		echo M()->getLastSql();
	}

	public function ajaxSetStyle()
	{
		$where['ad_jid'] = $this->jid;
		$imagnum = I('post.n', 0, 'intval');
		$tid = I('post.t', '');
		$pos = I('post.p', 0, 'intval');
		$style = I('post.s', '');
		$data = array();
		for($i=1; $i<=$imagnum; $i++)
		{
			$info = array();
			$info['ad_jid'] = $this->jid;
			$info['ad_tid'] = $tid;
			$info['ad_pos'] = $pos;
			$info['ad_type'] = $style;
			$info['ad_imgid'] = $i;
			$data[] = $info;
		}
		
		//删除以前的样式和图片
		M('advert')->where( array('ad_jid'=>$this->jid, 'ad_tid'=>$tid, 'ad_pos'=>$pos) )->delete();
		//添加样式
		$status = M('advert')->addAll( $data );	
		exit( $status ? "1" : "0" );
	}






}