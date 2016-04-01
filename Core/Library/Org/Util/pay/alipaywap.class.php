<?php
namespace Org\Util\pay;

class alipaywap{
	
	public $partner		= '2088911647217055';
	
	//收款支付宝账号
	public $seller_email	= 'dishuos@azding.com';
	
	//安全检验码，以数字和字母组成的32位字符
	public $key			= '3xc76qzgfa8v87afldkox5ffzv5qvtek';
	
	//返回格式
	public $format = "xml";
	//必填，不需要修改
	
	//签名方式 不需修改
	public $sign_type    = 'MD5';
	
	//返回格式
	public $v = "2.0";
	//必填，不需要修改
	
	//服务器异步通知页面路径
	public $notify_url = "http://yd.dishuos.com/Pay/notify/type/alipaywap";
	//需http://格式的完整路径，不允许加?id=123这类自定义参数
	
	//页面跳转同步通知页面路径
	public $call_back_url = "http://yd.dishuos.com/Pay/call_back/type/alipaywap";
	//需http://格式的完整路径，不允许加?id=123这类自定义参数
	
	//操作中断返回地址
	public $merchant_url = "http://yd.dishuos.com/Pay/merchant/type/alipaywap";
	//用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数
	
	//字符编码格式 目前支持 gbk 或 utf-8
	public $input_charset = 'utf-8';
	
	public $alipay_gateway_new = 'http://wappaygw.alipay.com/service/rest.htm?';
	
	//ca证书路径地址，用于curl中ssl校验
	//请保证cacert.pem文件在当前文件夹目录中
	public $cacert    = 'cacert.pem';
	
	//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
	public $transport    = 'http';
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
		$req_id = time().rand(1000,9999);
		//请求业务参数详细
		$req_data = '<direct_trade_create_req><notify_url>' . $this->notify_url . '</notify_url><call_back_url>' . $this->call_back_url . '</call_back_url><seller_account_name>' . trim($this->seller_email) . '</seller_account_name><out_trade_no>' . $order_info['o_id'] . '</out_trade_no><subject>' . $order_info['o_title'] . '</subject><total_fee>' . $order_info['o_price'] . '</total_fee><merchant_url>' . $this->merchant_url . '</merchant_url></direct_trade_create_req>';

		//构造要请求的参数数组，无需改动
		$para_token = array(
				"service" => "alipay.wap.trade.create.direct",
				"partner" => trim($this->partner),
				"sec_id" => trim($this->sign_type),
				"format"	=> $this->format,
				"v"	=> $this->v,
				"req_id"	=> $req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> trim(strtolower($this->input_charset))
		);
		
		//建立请求
		$html_text = $this->buildRequestHttp($para_token);
		
		//URLDECODE返回的信息
		$html_text = urldecode($html_text);
		
		//解析远程模拟提交后返回的信息
		$para_html_text = $this->parseResponse($html_text);
		
		//获取request_token
		$request_token = $para_html_text['request_token'];
		
		//业务详细
		$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
		//必填
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "alipay.wap.auth.authAndExecute",
				"partner" => trim($this->partner),
				"sec_id" => trim($this->sign_type),
				"format"	=> $this->format,
				"v"	=> $this->v,
				"req_id"	=> $req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> trim(strtolower($this->input_charset))
		);
		
		//待请求参数数组
		$para = $this->buildRequestPara($parameter);
		
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
				
			//对notify_data解密
			$decrypt_post_para = $_POST;
			
			//notify_id从decrypt_post_para中解析出来（也就是说decrypt_post_para中已经包含notify_id的内容）
			$doc = new \DOMDocument();
			$doc->loadXML($decrypt_post_para['notify_data']);
			$notify_id = $doc->getElementsByTagName( "notify_id" )->item(0)->nodeValue;
				
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($notify_id)) {$responseTxt = $this->getResponse($notify_id);}
				
			//生成签名结果
			$isSign = $this->getSignVeryfy($decrypt_post_para, $_POST["sign"],false);
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
		if(empty($get)) {//判断GET来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($get, $get["sign"],true);
				
			//写日志记录
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "return_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_GET);
			//logResult($log_text);
				
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if ($isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果
	 * @param $para_temp 请求参数数组
	 * @return 支付宝处理结果
	 */
	protected function buildRequestHttp($para_temp) {
		$sResult = '';
	
		//待请求参数数组字符串
		$request_data = $this->buildRequestPara($para_temp);
		
		//远程获取数据
		$sResult = $this->getHttpResponsePOST($this->alipay_gateway_new, $this->cacert,$request_data,trim(strtolower($this->input_charset)));
	
		return $sResult;
	}
	
	/**
	 * 生成要请求给支付宝的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组
	 */
	protected function buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);
	
		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);
	
		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
	
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		if($para_sort['service'] != 'alipay.wap.trade.create.direct' && $para_sort['service'] != 'alipay.wap.auth.authAndExecute') {
			$para_sort['sign_type'] = strtoupper(trim($this->sign_type));
		}
	
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
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkstring($para_sort);
	
		$mysign = "";
		switch (strtoupper(trim($this->sign_type))) {
			case "MD5" :
				$mysign = $this->md5Sign($prestr, $this->key);
				break;
			case "RSA" :
				$mysign = "";
				//$mysign = $this->rsaSign($prestr, $this->alipay_config['private_key_path']);
				break;
			case "0001" :
				$mysign = "";
				//$mysign = $this->rsaSign($prestr, $this->alipay_config['private_key_path']);
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
	 * 远程获取数据，POST模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param $url 指定URL完整路径地址
	 * @param $cacert_url 指定当前工作目录绝对路径
	 * @param $para 请求的数据
	 * @param $input_charset 编码格式。默认值：空值
	 * return 远程输出的数据
	 */
	protected function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '') {
		
		if (trim($input_charset) != '') {
			$url = $url."_input_charset=".$input_charset;
		}
		$curl = curl_init($url);
		
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);

		return $responseText;
	}
	
	/**
	 * 解析远程模拟提交后返回的信息
	 * @param $str_text 要解析的字符串
	 * @return 解析结果
	 */
	protected function parseResponse($str_text) {
		//以“&”字符切割字符串
		$para_split = explode('&',$str_text);
		//把切割后的字符串数组变成变量与数值组合的数组
		foreach ($para_split as $item) {
			//获得第一个=字符的位置
			$nPos = strpos($item,'=');
			//获得字符串长度
			$nLen = strlen($item);
			//获得变量名
			$key = substr($item,0,$nPos);
			//获得数值
			$value = substr($item,$nPos+1,$nLen-$nPos-1);
			//放入数组中
			$para_text[$key] = $value;
		}
	
		if( ! empty ($para_text['res_data'])) {
			
			//token从res_data中解析出来（也就是说res_data中已经包含token的内容）
			$doc = new \DOMDocument();
			$doc->loadXML($para_text['res_data']);
			$para_text['request_token'] = $doc->getElementsByTagName( "request_token" )->item(0)->nodeValue;
		}
	
		return $para_text;
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
	
	/**
     * 异步通知时，对参数做固定排序
     * @param $para 排序前的参数组
     * @return 排序后的参数组
     */
	protected function sortNotifyPara($para) {
		$para_sort['service'] = $para['service'];
		$para_sort['v'] = $para['v'];
		$para_sort['sec_id'] = $para['sec_id'];
		$para_sort['notify_data'] = $para['notify_data'];
		return $para_sort;
	}


	/**
	 * 获取返回时的签名验证结果
	 * @param $para_temp 通知返回来的参数数组
	 * @param $sign 返回的签名结果
	 * @return 签名验证结果
	 */
	protected function getSignVeryfy($para_temp, $sign, $isSort) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);
		//对待签名参数数组排序
		
		if($isSort) {
			$para_sort = $this->argSort($para_filter);
		} else {
			$para_sort = $this->sortNotifyPara($para_filter);
		}

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
		$doc = new \DOMDocument();
		$doc->loadXML($_POST['notify_data']);
		if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
			$this->pay_info = array(
					'out_trade_no'  => $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue,
					'trade_no'      => $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue,
					'trade_status'  => $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue,
			);
		}
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