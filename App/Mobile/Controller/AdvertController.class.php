<?php
namespace Mobile\Controller;

class AdvertController extends MobileController {

	public function loadtemp($jid, $adtid, $adpos)
	{
		$adimglist_arr = M("advert")->where( array("ad_jid"=>$jid, "ad_tid"=>$adtid, "ad_pos"=>$adpos) )->select();
		foreach($adimglist_arr as $_ad) {
			if( !empty($_ad['ad_imglink']) && strpos($_ad['ad_imglink'], "http") !== 0 ) {
				$_ad['ad_imglink'] = "http://".$_ad['ad_imglink'];	
			}
			$adimglist[$_ad['ad_imgid']] = $_ad;
		}
		$this->assign('adpos', $adpos);
		$this->assign('advert', $adimglist);
	
		$type = $adimglist_arr[0]['ad_type'];
		if( $type ) {
			$adverFile = APP_PATH.'Merchant/'.C('DEFAULT_V_LAYER').'/Advert/'.$type.'.html';
			if( file_exists($adverFile )) :
				$adverContent = file_get_contents( $adverFile );
				$adverContent = preg_replace("/\<\!--START--\>(.*?)\<\!--END--\>/si", "", $adverContent);
				echo $this->fetch("", $adverContent );
			endif;
		}
	}
	
}