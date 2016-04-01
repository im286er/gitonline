<?php
namespace Org\Util\pay;
class alipayrefund {


    /**
     * HTTPS��ʽ��Ϣ��֤��ַ
     */
	public $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
     * HTTP��ʽ��Ϣ��֤��ַ
     */
	public $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';



	/**
	 *֧�������ص�ַ���£�
	 */
	public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';

	public $alipay_config;


	function __construct($alipay_config){
		$this->alipay_config = $alipay_config;
	}


    /**----------------------------------�첽֪ͨ����--------------------------------**/
    /**----------------------------------�첽֪ͨ����--------------------------------**/
    /**----------------------------------�첽֪ͨ����--------------------------------**/
    /**
     * ���notify_url��֤��Ϣ�Ƿ���֧���������ĺϷ���Ϣ
     * @return ��֤���
     */
	function verifyNotify(){
		if(empty($_POST)) {//�ж�POST���������Ƿ�Ϊ��
			return false;
		}
		else {
			//����ǩ�����
			$isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
			//��ȡ֧����Զ�̷�����ATN�������֤�Ƿ���֧������������Ϣ��
			$responseTxt = 'true';
			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
			
			//д��־��¼
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_POST);
			//logResult($log_text);
			
			//��֤
			//$responsetTxt�Ľ������true����������������⡢���������ID��notify_idһ����ʧЧ�й�
			//isSign�Ľ������true���밲ȫУ���롢����ʱ�Ĳ�����ʽ���磺���Զ�������ȣ��������ʽ�й�
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
    /**
     * ���return_url��֤��Ϣ�Ƿ���֧���������ĺϷ���Ϣ
     * @return ��֤���
     */
	public function verifyReturn(){
		if(empty($_GET)) {//�ж�POST���������Ƿ�Ϊ��
			return false;
		}
		else {
			//����ǩ�����
			$isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
			//��ȡ֧����Զ�̷�����ATN�������֤�Ƿ���֧������������Ϣ��
			$responseTxt = 'true';
			if (! empty($_GET["notify_id"])) {$responseTxt = $this->getResponse($_GET["notify_id"]);}	
			
			//��֤
			//$responsetTxt�Ľ������true����������������⡢���������ID��notify_idһ����ʧЧ�й�
			//isSign�Ľ������true���밲ȫУ���롢����ʱ�Ĳ�����ʽ���磺���Զ�������ȣ��������ʽ�й�
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
    /**
     * ��ȡ����ʱ��ǩ����֤���
     * @param $para_temp ֪ͨ�������Ĳ�������
     * @param $sign ���ص�ǩ�����
     * @return ǩ����֤���
     */
	public function getSignVeryfy($para_temp, $sign) {
		//��ȥ��ǩ�����������еĿ�ֵ��ǩ������
		$para_filter = $this->paraFilter($para_temp);
		
		//�Դ�ǩ��������������
		$para_sort =  $this->argSort($para_filter);
		
		//����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ���
		$prestr =  $this->createLinkstring($para_sort);
		
		$isSgin = false;
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$isSgin = $this->md5Verify($prestr, $sign, $this->alipay_config['key']);
				break;
			default :
				$isSgin = false;
		}
		
		return $isSgin;
	}

    /**
     * ��ȡԶ�̷�����ATN���,��֤����URL
     * @param $notify_id ֪ͨУ��ID
     * @return ������ATN���
     * ��֤�������
     * invalid����������� ��������������ⷵ�ش�����partner��key�Ƿ�Ϊ�� 
     * true ������ȷ��Ϣ
     * false �������ǽ�����Ƿ�������ֹ�˿������Լ���֤ʱ���Ƿ񳬹�һ����
     */
	public function getResponse($notify_id) {
		$transport = strtolower(trim($this->alipay_config['transport']));
		$partner = trim($this->alipay_config['partner']);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = $this->getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);
		
		return $responseTxt;
	}



    /**----------------------------------�첽֪ͨ���ֽ���--------------------------------**/
    /**----------------------------------�첽֪ͨ���ֽ���--------------------------------**/
    /**----------------------------------�첽֪ͨ���ֽ���--------------------------------**/



	/**
	 * ����ǩ�����
	 * @param $para_sort ������Ҫǩ��������
	 * return ǩ������ַ���
	 */
	public function buildRequestMysign($para_sort) {
		//����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ���
		$prestr = $this->createLinkstring($para_sort);
		
		$mysign = "";
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$mysign = $this->md5Sign($prestr, $this->alipay_config['key']);
				break;
			default :
				$mysign = "";
		}
		
		return $mysign;
	}

	/**
     * ����Ҫ�����֧�����Ĳ�������
     * @param $para_temp ����ǰ�Ĳ�������
     * @return Ҫ����Ĳ�������
     */
	public function buildRequestPara($para_temp) {
		//��ȥ��ǩ�����������еĿ�ֵ��ǩ������
		$para_filter = $this->paraFilter($para_temp);

		//�Դ�ǩ��������������
		$para_sort = $this->argSort($para_filter);

		//����ǩ�����
		$mysign = $this->buildRequestMysign($para_sort);
		
		//ǩ�������ǩ����ʽ���������ύ��������
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));
		
		return $para_sort;
	}

	/**
     * ����Ҫ�����֧�����Ĳ�������
     * @param $para_temp ����ǰ�Ĳ�������
     * @return Ҫ����Ĳ��������ַ���
     */
	public function buildRequestParaToString($para_temp) {
		//�������������
		$para = $this->buildRequestPara($para_temp);
		
		//�Ѳ�����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ����������ַ�����urlencode����
		$request_data = $this->createLinkstringUrlencode($para);
		
		return $request_data;
	}
	
    /**
     * ���������Ա�HTML��ʽ���죨Ĭ�ϣ�
     * @param $para_temp �����������
     * @param $method �ύ��ʽ������ֵ��ѡ��post��get
     * @param $button_name ȷ�ϰ�ť��ʾ����
     * @return �ύ��HTML�ı�
     */
	public function buildRequestForm($para_temp) {
		//�������������
		$para = $this->buildRequestPara($para_temp);
		$req_url = $this->alipay_gateway_new.http_build_query($para);
		redirect($req_url);
	}
	
	/**
     * ����������ģ��Զ��HTTP��POST����ʽ���첢��ȡ֧�����Ĵ�����
     * @param $para_temp �����������
     * @return ֧����������
     */
	public function buildRequestHttp($para_temp) {
		$sResult = '';
		
		//��������������ַ���
		$request_data = $this->buildRequestPara($para_temp);

		//Զ�̻�ȡ����
		$sResult = $this->getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$request_data,trim(strtolower($this->alipay_config['input_charset'])));

		return $sResult;
	}
	
	/**
     * ����������ģ��Զ��HTTP��POST����ʽ���첢��ȡ֧�����Ĵ����������ļ��ϴ�����
     * @param $para_temp �����������
     * @param $file_para_name �ļ����͵Ĳ�����
     * @param $file_name �ļ���������·��
     * @return ֧�������ش�����
     */
	public function buildRequestHttpInFile($para_temp, $file_para_name, $file_name) {
		
		//�������������
		$para = $this->buildRequestPara($para_temp);
		$para[$file_para_name] = "@".$file_name;
		
		//Զ�̻�ȡ����
		$sResult = $this->getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$para,trim(strtolower($this->alipay_config['input_charset'])));

		return $sResult;
	}
	
	/**
     * ���ڷ����㣬���ýӿ�query_timestamp����ȡʱ����Ĵ�����
	 * ע�⣺�ù���PHP5����������֧�֣���˱�������������ص�����װ��֧��DOMDocument��SSL��PHP���û��������鱾�ص���ʱʹ��PHP�������
     * return ʱ����ַ���
	 */
	public function query_timestamp() {
		$url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim(strtolower($this->alipay_config['partner']))."&_input_charset=".trim(strtolower($this->alipay_config['input_charset']));
		$encrypt_key = "";		

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
		
		return $encrypt_key;
	}

	/**
	 * ����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ���
	 * @param $para ��Ҫƴ�ӵ�����
	 * return ƴ������Ժ���ַ���
	 */
	public function createLinkstring($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".$val."&";
		}
		//ȥ�����һ��&�ַ�
		$arg = substr($arg,0,count($arg)-2);
		
		//�������ת���ַ�����ôȥ��ת��
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		
		return $arg;
	}
	/**
	 * ����������Ԫ�أ����ա�����=����ֵ����ģʽ�á�&���ַ�ƴ�ӳ��ַ����������ַ�����urlencode����
	 * @param $para ��Ҫƴ�ӵ�����
	 * return ƴ������Ժ���ַ���
	 */
	public function createLinkstringUrlencode($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".urlencode($val)."&";
		}
		//ȥ�����һ��&�ַ�
		$arg = substr($arg,0,count($arg)-2);
		
		//�������ת���ַ�����ôȥ��ת��
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		
		return $arg;
	}
	/**
	 * ��ȥ�����еĿ�ֵ��ǩ������
	 * @param $para ǩ��������
	 * return ȥ����ֵ��ǩ�����������ǩ��������
	 */
	public function paraFilter($para) {
		$para_filter = array();
		while (list ($key, $val) = each ($para)) {
			if($key == "sign" || $key == "sign_type" || $val == "")continue;
			else	$para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	/**
	 * ����������
	 * @param $para ����ǰ������
	 * return ����������
	 */
	public function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
	/**
	 * д��־��������ԣ�����վ����Ҳ���ԸĳɰѼ�¼�������ݿ⣩
	 * ע�⣺��������Ҫ��ͨfopen����
	 * @param $word Ҫд����־����ı����� Ĭ��ֵ����ֵ
	 */
	public function logResult($word='') {
		$fp = fopen("log.txt","a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"ִ�����ڣ�".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	/**
	 * Զ�̻�ȡ���ݣ�POSTģʽ
	 * ע�⣺
	 * 1.ʹ��Crul��Ҫ�޸ķ�������php.ini�ļ������ã��ҵ�php_curl.dllȥ��ǰ���";"������
	 * 2.�ļ�����cacert.pem��SSL֤���뱣֤��·����Ч��ĿǰĬ��·���ǣ�getcwd().'\\cacert.pem'
	 * @param $url ָ��URL����·����ַ
	 * @param $cacert_url ָ����ǰ����Ŀ¼����·��
	 * @param $para ���������
	 * @param $input_charset �����ʽ��Ĭ��ֵ����ֵ
	 * return Զ�����������
	 */
	public function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '') {

		if (trim($input_charset) != '') {
			$url = $url."_input_charset=".$input_charset;
		}
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL֤����֤
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//�ϸ���֤
		curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//֤���ַ
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // ����HTTPͷ
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// ��ʾ������
		curl_setopt($curl,CURLOPT_POST,true); // post��������
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post��������
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//���ִ��curl�����г����쳣���ɴ򿪴˿��أ��Ա�鿴�쳣����
		curl_close($curl);
		
		return $responseText;
	}

	/**
	 * Զ�̻�ȡ���ݣ�GETģʽ
	 * ע�⣺
	 * 1.ʹ��Crul��Ҫ�޸ķ�������php.ini�ļ������ã��ҵ�php_curl.dllȥ��ǰ���";"������
	 * 2.�ļ�����cacert.pem��SSL֤���뱣֤��·����Ч��ĿǰĬ��·���ǣ�getcwd().'\\cacert.pem'
	 * @param $url ָ��URL����·����ַ
	 * @param $cacert_url ָ����ǰ����Ŀ¼����·��
	 * return Զ�����������
	 */
	public function getHttpResponseGET($url,$cacert_url) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // ����HTTPͷ
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// ��ʾ������
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL֤����֤
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//�ϸ���֤
		curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//֤���ַ
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//���ִ��curl�����г����쳣���ɴ򿪴˿��أ��Ա�鿴�쳣����
		curl_close($curl);
		
		return $responseText;
	}

	/**
	 * ʵ�ֶ����ַ����뷽ʽ
	 * @param $input ��Ҫ������ַ���
	 * @param $_output_charset ����ı����ʽ
	 * @param $_input_charset ����ı����ʽ
	 * return �������ַ���
	 */
	public function charsetEncode($input,$_output_charset ,$_input_charset) {
		$output = "";
		if(!isset($_output_charset) )$_output_charset  = $_input_charset;
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset change.");
		return $output;
	}
	/**
	 * ʵ�ֶ����ַ����뷽ʽ
	 * @param $input ��Ҫ������ַ���
	 * @param $_output_charset ����Ľ����ʽ
	 * @param $_input_charset ����Ľ����ʽ
	 * return �������ַ���
	 */
	public function charsetDecode($input,$_input_charset ,$_output_charset) {
		$output = "";
		if(!isset($_input_charset) )$_input_charset  = $_input_charset ;
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset changes.");
		return $output;
	}

	/**
	 * ǩ���ַ���
	 * @param $prestr ��Ҫǩ�����ַ���
	 * @param $key ˽Կ
	 * return ǩ�����
	 */
	public function md5Sign($prestr, $key) {
		$prestr = $prestr . $key;
		return md5($prestr);
	}

	/**
	 * ��֤ǩ��
	 * @param $prestr ��Ҫǩ�����ַ���
	 * @param $sign ǩ�����
	 * @param $key ˽Կ
	 * return ǩ�����
	 */
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

}


?>