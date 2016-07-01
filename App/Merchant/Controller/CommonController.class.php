<?php
namespace Merchant\Controller;
use Think\Controller;

class CommonController extends Controller {
	public function creatMyQrcode(){
		$mid = I('mid',0);
		$jid = I('jid',0);
		$sid = I('sid',0);
		$size = 4;
		$suid = \Think\Crypt\Driver\Base64::encrypt($mid, C('CODEKEY'));
		$qcUrl = U('Index/index@yd',array('jid'=>$jid,'sid'=>$sid,'suid'=>$suid));
		vendor("phpqrcode.phpqrcode");
		$QRcode = new \QRcode();
		echo $QRcode::png($qcUrl, false, 'H', $size);
	}
}