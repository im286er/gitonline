<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
	
	public function _initialize() {
		$MobileDetect = new \Common\Org\MobileDetect();
		if( $MobileDetect->isMobile() ) {
			header("location:http://sj.dishuos.com/v-U606099WAY7P");	
		}
	}
	
    public function index() {
    	//redirect('http://www.azding.cn/');
		$this->display();	
	}

	public function jm() {
		$_AddressList = F('AddressList');
        if( !is_array($_AddressList) || empty($_AddressList) ) B('Common\Behavior\CreateAddress', '', $_AddressList);
		$this->assign('addressList', $_AddressList);

		$this->display();
	}
	
	public function publicGetaddress( $pid=0 ) {
		$_AddressList = F('AddressList');
        if( !is_array($_AddressList) || empty($_AddressList) ) B('Common\Behavior\CreateAddress', '', $_AddressList);

        $str = '<option value="">请选择区域市级</option>';
        foreach($_AddressList as $address) {
            if($address['apid'] == $pid) $str .= '<option value="'.$address['aid'].'">'.$address['aname'].'</option>';
        }
        exit($str);   
    }
	
	//发送邮件
	public function sendmail() {
		$cookie = cookie('jm'); if($cookie) exit("2");
		
		$yx = I('post.yx');//意向
		$CompanyName = I('post.CompanyName');
		$Vocation = I('post.Vocation');
		$UserName = I('post.UserName');
		$UserPhone = I('post.UserPhone');
		$UserQq = I('post.UserQq');
		$UserEmail = I('post.UserEmail');
		$SelectQcity = I('post.SelectQcity');
		$SelectScity = I('post.SelectScity');
		$UserAddress = I('post.UserAddress');
		
		if( $UserName && preg_match("/1[0-9]{10}/", $UserPhone) ) {
			$string  = '<table width="90%" border="1" cellpadding="0" cellspacing="0">';
			$string .=  '<tr><th colspan="2" align="center" style="height:28px; padding-left:15px;">通过官网申请</th></tr>';
			$string .=  '<tr><th>意向</th><td style="height:28px; padding-left:15px;">'.$yx.'</td></tr>';
			$string .=  '<tr><th>公司名称</th><td style="height:28px; padding-left:15px;">'.$CompanyName.'</td></tr>';
			$string .=  '<tr><th>所处行业</th><td style="height:28px; padding-left:15px;">'.$Vocation.'</td></tr>';
			$string .=  '<tr><th>联系人</th><td style="height:28px; padding-left:15px;">'.$UserName.'</td></tr>';
			$string .=  '<tr><th>手机号码</th><td style="height:28px; padding-left:15px;">'.$UserPhone.'</td></tr>';
			$string .=  '<tr><th>QQ号</th><td style="height:28px; padding-left:15px;">'.$UserQq.'</td></tr>';
			$string .=  '<tr><th>邮箱</th><td style="height:28px; padding-left:15px;">'.$UserEmail.'</td></tr>';
			$string .=  '<tr><th>详细地址</th><td style="height:28px; padding-left:15px;">'.$SelectScity.$SelectQcity.$UserAddress.'</td></tr>';
			$string .= '</table>';
			
			if( sendemail('business@azding.com', '申请加盟', $string) ) {
				cookie('jm', '1', 3600); exit('1');	
			} 
		} exit("0");
	}
}