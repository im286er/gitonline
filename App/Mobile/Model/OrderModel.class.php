<?php
namespace Mobile\Model;
use Think\Model;

class OrderModel extends Model {

	protected $_validate = array(
    );

	//强制更新
	public function runGtype($force=false) {
		$gtype = array(
			'Choose'=>'在线订餐',
			'Seat'=>'远程预定',
		);
		return $gtype;
	}
	

	/**
	 * 推送订单 （to 顺丰）
	 * orderid              订单号
	 * j_company            寄件方公司名称
	 * j_contact            寄件方联系人
	 * j_telphone           寄件方联系电话
	 * j_address            寄件地址
	 * d_company            到件方公司名称
	 * d_contact            到件方联系人
	 * d_telphone           到件方联系电话
	 * d_address            到件方地址
	 * d_province           到件方省份
	 * d_city               到件方城市
	 * j_province           寄件方省份
	 * j_city               寄件方城市
	 * name                 商品名称
	 * mailno               运单号
	 * */
	public function xmlservice($orderid,$j_contact,$j_telphone,$j_address,$d_company,$d_contact,$d_telphone,$d_address) 
	{
		$checkhead = C('EXPRESS_CHECKHEADER');
		$body = '<Request service="OrderReverseService" lang="zh-CN"><Head>'.$checkhead['SF'].'</Head><Body><Order orderid="'.$orderid.'" express_type="1"  j_contact="'.$j_contact.'" j_mobile="'.$j_telphone.'" j_address="'.$j_address.'"  d_company="'.$d_company.'" d_contact="'.$d_contact.'" d_mobile="'.$d_telphone.'" d_address="'.$d_address.'" parcel_quantity="1" pay_method="3" custid="'.C('MONTHLY_NUM').'" ><Cargo name="衣服" unit="件" /><AddedService name="INSURE" value="500" /></Order></Body></Request>';
	    $checkword = C('EXPRESS_CHECKWORD');
	    $newbody = $body.$checkword['SF'];
		$md5 =  md5($newbody,true);  
		$verifyCode = base64_encode($md5);
		$url1 = C('EXPRESS_URL');
		$url  = $url1['SF']; 
		$fields = array('xml'=>$body,'verifyCode'=>$verifyCode);
		$parambody =  http_build_query($fields, '', '&'); 
		$res = $this->post($url,$parambody); 
		return $res;
	}



	function xmlserviceback($orderid) 
	{
	     $checkhead = C('EXPRESS_CHECKHEADER');
	     $body = '<Request service="OrderRvsCancelService" lang="zh-CN"><Head>'.$checkhead['SF'].'</Head><Body><Order orderid="'.$orderid.'"  ></Order></Body></Request>';
	     $checkword = C('EXPRESS_CHECKWORD');
	     $newbody = $body.$checkword['SF'];
	     $md5 =  md5($newbody,true);  
	     $verifyCode = base64_encode($md5);
	     $url1 = C('EXPRESS_URL');
	     $url  = $url1['SF']; 
	     $fields = array('xml'=>$body,'verifyCode'=>$verifyCode);
	     $parambody =  http_build_query($fields, '', '&'); 
	     $res = $this->post($url,$parambody); 
	     return $res;
	}


	/**
	 * xml传送
	 */
	function post($url,$body) 
	{ 
	     $curlObj = curl_init();
	     curl_setopt($curlObj, CURLOPT_URL, $url); // 设置访问的url
	     curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1); //curl_exec将结果返回,而不是执行
	     curl_setopt($curlObj, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8"));
	     curl_setopt($curlObj, CURLOPT_URL, $url);
	     curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, FALSE);
	     curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, FALSE);
	     curl_setopt($curlObj, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		
	     curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, 'POST');      

	     curl_setopt($curlObj, CURLOPT_POST, true);
	     curl_setopt($curlObj, CURLOPT_POSTFIELDS, $body);       
	     curl_setopt($curlObj, CURLOPT_ENCODING, 'gzip');

	     $res = @curl_exec($curlObj);

	     curl_close($curlObj);

	     // if ($res === false) {
	     //      $errno = curl_errno($curlObj);
	     //      if ($errno == CURLE_OPERATION_TIMEOUTED) {
	     //           $msg = "Request Timeout:   seconds exceeded";
	     //      } else {
	     //           $msg = curl_error($curlObj);
	     //      }
	     //      echo $msg;
	     //      $e = new XN_TimeoutException($msg);           
	     //      throw $e;
	     // } 
		return $res;
	}

}

?>