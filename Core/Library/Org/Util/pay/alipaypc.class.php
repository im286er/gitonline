<?php
namespace Org\Util\pay;

class alipaypc{
	
	public $partner		= '2088911647217055';
	
	//收款支付宝账号
	public $seller_email	= 'dishuos@azding.com';
	
	//安全检验码，以数字和字母组成的32位字符
	public $key			= '3xc76qzgfa8v87afldkox5ffzv5qvtek';
	
	//签名方式 不需修改
	public $sign_type    =  'MD5';
	
	//字符编码格式 目前支持 gbk 或 utf-8
	public $input_charset = 'utf-8';
	
	//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
	public $transport    = 'http';
	
	//支付类型
	public $payment_type = "1";
	//必填，不能修改
	//服务器异步通知页面路径
	public $notify_url = "http://yd.dishuos.com/Pay/notify/type/alipaypc";
	//需http://格式的完整路径，不能加?id=123这类自定义参数
	
	//页面跳转同步通知页面路径
	public $return_url = "http://yd.dishuos.com/Pay/call_back/type/alipaypc";
	//需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
	
	//防钓鱼时间戳
	public $anti_phishing_key = "";
	//若要使用请调用类文件submit中的query_timestamp函数
	
	//客户端的IP地址
	public $exter_invoke_ip = "";
	//非局域网的外网IP地址，如：221.0.0.1
	
	public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
	
	/**
	 * HTTPS形式消息验证地址
	 */
	public $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
	 * HTTP形式消息验证地址
	 */
	public $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
	
	//支付结果数组
	public $pay_info = array();
	
	public function buildRequest($order_info){
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "create_direct_pay_by_user",
				"partner" => trim($this->partner),
				"seller_email" => trim($this->seller_email),
				"payment_type"	=> $this->payment_type,
				"notify_url"	=> $this->notify_url,
				"return_url"	=> $this->return_url,
				"out_trade_no"	=> $order_info['o_id'],
				"subject"	=> $order_info['o_title'],
				"total_fee"	=> $order_info['o_price'],
				"body"	=> $order_info['o_title'],
				"show_url"	=> $show_url,
				"exter_invoke_ip"	=> get_client_ip(),
				"_input_charset"	=> trim(strtolower($this->input_charset))
		);
		
		//待请求参数数组
		$para = $this->buildRequestPara($parameter);
		
		/*
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower($this->input_charset))."' method='".$this->method."'>";
		while (list ($key, $val) = each ($para)) {
			$sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
		}
		
		//submit按钮控件请不要含有name属性
		$sHtml = $sHtml."<input type='submit' value='".$this->button_name."'></form>";
		
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		
		echo $sHtml;*/
		$req_url = $this->alipay_gateway_new.http_build_query($para);
		
		redirect($req_url);
	}
	
	/**
	 * 针对notify_url验证消息是否是支付宝发出的合法消息
	 * @return 验证结果
	 */
	public function verifyNotify(){
		if(empty($_POST)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
	
			//写日志记录
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_POST);
			//logResult($log_text);
	
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * 针对return_url验证消息是否是支付宝发出的合法消息
	 * @return 验证结果
	 */
	public function verifyReturn($get){
		if(empty($get)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($get, $get["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($get["notify_id"])) {$responseTxt = $this->getResponse($get["notify_id"]);}
	
			//写日志记录
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n return_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($get);
			//logResult($log_text);
	
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 * 生成要请求给支付宝的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组
	 */
	protected function buildRequestPara($para_temp){
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);
		
		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);
		
		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
		
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->sign_type));
		
		return $para_sort;
	}
	
	/**
	 * 除去数组中的空值和签名参数
	 * @param $para 签名参数组
	 * return 去掉空值与签名参数后的新签名参数组
	 */
	protected function paraFilter($para) {
		$para_filter = array();
		while (list ($key, $val) = each ($para)) {
			if($key == "sign" || $key == "sign_type" || $val == "")continue;
			else	$para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	
	/**
	 * 对数组排序
	 * @param $para 排序前的数组
	 * return 排序后的数组
	 */
	protected function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
	
	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	protected function buildRequestMysign($para_sort) {
		 
		print_r($para_sort);
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkstring($para_sort);
		print_r($prestr);
		$mysign = "";
		switch (strtoupper(trim($this->sign_type))) {
			case "MD5" :
				$mysign = $this->md5Sign($prestr, $this->key);
				print_r($mysign);
				break;
			default :
				$mysign = "";
		}

		return $mysign;
	}
	
	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	protected function createLinkstring($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".$val."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,count($arg)-2);
	
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
		return $arg;
	}
	
	/**
	 * 签名字符串
	 * @param $prestr 需要签名的字符串
	 * @param $key 私钥
	 * return 签名结果
	 */
	protected function md5Sign($prestr, $key) {
		$prestr = $prestr . $key;
		return md5($prestr);
	}
	
	/**
	 * 获取返回时的签名验证结果
	 * @param $para_temp 通知返回来的参数数组
	 * @param $sign 返回的签名结果
	 * @return 签名验证结果
	 */
	protected function getSignVeryfy($para_temp, $sign) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);
	
		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);
	
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkstring($para_sort);
	
		$isSgin = false;
		switch (strtoupper(trim($this->sign_type))) {
			case "MD5" :
				$isSgin = $this->md5Verify($prestr, $sign, $this->key);
				break;
			default :
				$isSgin = false;
		}
	
		return $isSgin;
	}
	
	/**
	 * 获取远程服务器ATN结果,验证返回URL
	 * @param $notify_id 通知校验ID
	 * @return 服务器ATN结果
	 * 验证结果集：
	 * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
	 * true 返回正确信息
	 * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
	 */
	protected function getResponse($notify_id) {
		$transport = strtolower(trim($this->transport));
		$partner = trim($this->partner);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = $this->getHttpResponseGET($veryfy_url, $this->cacert);
	
		return $responseTxt;
	}
	
	/**
	 * 远程获取数据，GET模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param $url 指定URL完整路径地址
	 * @param $cacert_url 指定当前工作目录绝对路径
	 * return 远程输出的数据
	 */
	protected function getHttpResponseGET($url,$cacert_url) {
		$curl = curl_init($url);
	
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADER, 0);
	
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
	
		return $responseText;
	}
	// 验证签名
    public function md5Verify($prestr, $sign, $key) {
        $prestr = $prestr . $key;
        $mysgin = md5($prestr);
        if($mysgin == $sign) {
            return true;
        }
        else {
            return false;
        }
    }
	public function get_pay_info(){
		$this->pay_info = array(
			'out_trade_no'  => $_POST["out_trade_no"],
			'trade_no'      => $_POST["trade_no"],
			'trade_status'  => $_POST["trade_status"],
		);
		return $this->pay_info;
	}
	public function is_success(){
		if($this->pay_info['trade_status'] == 'TRADE_FINISHED' || $this->pay_info['trade_status'] == 'TRADE_SUCCESS'){
			return true;
		}else{
			return false;
		}
	}
	public function success(){
		echo "success";	
	}
	public function fail(){
		echo "fail";
	}
}