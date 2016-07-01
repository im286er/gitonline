<?php
/**
 * ����aid����ȡһ����ַ����ϸ��Ϣ
 * @param int $aid ��ַ��aid (address������)
 * @param string $type ����ֵ string|array ���ص���һ���ַ�������һ������
 * @return string|array
 */
function get_address_byid( $aid=0, $type='string') {
	$addressList = F('AddressList');
	if(!is_array($addressList) || empty($addressList)) {  B('Common\Behavior\CreateAddress', '', $addressList); }
	$apid = $addressList[$aid]['apid'];
	if($type == 'string') {
		if($addressList[$apid]['apid'] != 0) {
			$address = $addressList[$addressList[$apid]['apid']]['aname'].' '.$addressList[$apid]['aname'].' '.$addressList[$aid]['aname'];
		} else {
			$address = $addressList[$apid]['aname'].' '.$addressList[$aid]['aname'];
		}
	} else if($type == 'array') {
		if($addressList[$apid]['apid'] != 0) {
			$address = array($addressList[$addressList[$apid]['apid']]['aid'], $addressList[$apid]['aid'], $addressList[$aid]['aid']);
		} else {
			$address = array($addressList[$apid]['aid'], $addressList[$aid]['aid']);
		}
	}
	return $address;
}


//��֤ �ֻ����룬�����ַ��ʧ�ܷ���ȥ��β�ո��ַ���
function checkstr($str){
	if (preg_match('#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $str)) {
		return "email";
	}elseif(preg_match("/1[34568]{1}\d{9}$/", $str)){
		return "phone";
	}else{
		return trim($str);
	}

}

function setsmsip( $phone, $code='' )
{
	//����������������
	$nowtime = mktime(23, 59, 59, date('m'), date('d')-1, date('Y') );
	M("smsIp")->query("delete from azd_sms_ip where `time` <= ".$nowtime);

	//���ͬһ���ֻ��ţ�һ�췢�ͳ��� 20�� �� ͬһIPһ�췢�ͳ��� 100�� �� ͬһ�ֻ��ŷ��ͼ��С��50s�����δ��û�����
	$ip = get_client_ip();
	$phone_times = M("smsIp")->where( array('phone'=>$phone) )->count();
	$ip_times 	 = M("smsIp")->where( array('ip'=>$ip) )->count();
	$phone_last  = M("smsIp")->where( array('phone'=>$phone) )->order("time desc")->find();
	if($phone_times > 20 || $ip_times > 100 || $phone_last['time']+50 > time() ) return false;
	M('smsIp')->add( array('phone'=>$phone, 'ip'=>$ip, 'code'=>$code, 'time'=>time()) );
	return true;
}


//���ŷ���
function sendmsg($phone, $content, $foottxt = '����լ����',$verify=true) {
	if($verify){ if( ! setsmsip( $phone, $content ) ) return false; }
	$url = "http://58.83.147.92:8080/qxt/smssenderv2?user=CS_azd&password=".md5('66313768')."&tele=".$phone;
	if(  is_numeric($content) ) {
		$data = "msg=������֤��Ϊ��".$content."����լ����";
	} else {
		$data = "msg=".$content;
		$data = iconv('UTF-8', 'GB2312', $data);
		$data = $data."����լ����";
	}
	$ch = curl_init($url);
	//curl_setopt($ch, CURLOPT_ENCODING , 'gbk');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
	$res = curl_exec($ch);
	curl_close($ch);

	return substr($res, 0, 2)=='ok' ? true : false;
}

function flsendmsg($phone, $content , $qm) {
	if( setsmsip($phone, $content) == false) return false;
	$url = "http://58.83.147.92:8080/qxt/smssenderv2?user=CS_azd&password=".md5('66313768')."&tele=".$phone;
	if(  is_numeric($content) ) {
		$data = "msg=������֤��Ϊ��".$content."��ȫ������";
		if($qm == '438'){
			$data = "msg=������֤��Ϊ��".$content."�����ϴ�¡�";
		}
	} else {
		$data = "msg=".$content;
		$data = iconv('UTF-8', 'GB2312', $data);
		if($qm == '438'){
			$data = $data . "�����ϴ�¡�";
		}else{
			$data = $data . "��ȫ������";
		}
		
	}
	$ch = curl_init($url);
	//curl_setopt($ch, CURLOPT_ENCODING , 'gbk');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
	$res = curl_exec($ch);
	curl_close($ch);
	return substr($res, 0, 2)=='ok' ? true : false;
}


function sendemail($uName,$title,$content){
	$mail = new \Think\Mail(C("MAIL"));
	$re=$mail->send($uName,$title,$content);
	if( !$re) {
		echo $mail->showDebug();
	}
	if($re){
		return true;
	}else{
		return false;
	}
}

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
	if(function_exists("mb_substr")){
		if ($suffix && strlen($str)>$length)
			return mb_substr($str, $start, $length, $charset)."";
		else
			return mb_substr($str, $start, $length, $charset);
	}
	elseif(function_exists('iconv_substr')) {
		if ($suffix && strlen($str)>$length)
			return iconv_substr($str,$start,$length,$charset)."";
		else
			return iconv_substr($str,$start,$length,$charset);
	}
	$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all($re[$charset], $str, $match);
	$slice = join("",array_slice($match[0], $start, $length));
	if($suffix) return $slice."";
	return $slice;
}

function rad($d)
{
	return $d * M_PI / 180.0;
}
/**
 * ��ȡ���������֮��ľ��룬��λkm��С�����2λ
 */
function GetDistance($lat1, $lng1, $lat2, $lng2)
{
	$EARTH_RADIUS = 6378.137;
	$radLat1 = rad($lat1);
	$radLat2 = rad($lat2);
	$a = $radLat1 - $radLat2;
	$b = rad($lng1) - rad($lng2);
	$s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
	$s = $s * $EARTH_RADIUS;
	$s = round($s * 100) / 100;
	return $s;
}
//����
function encrypt($data, $key) {
	$prep_code = serialize($data);
	$block = mcrypt_get_block_size('des', 'ecb');
	if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
		$prep_code .= str_repeat(chr($pad), $pad);
	}
	$encrypt = mcrypt_encrypt(MCRYPT_DES, $key, $prep_code, MCRYPT_MODE_ECB);
	return base64_encode($encrypt);
}
//����
function decrypt($str, $key) {
	$str = base64_decode($str);
	$str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
	$block = mcrypt_get_block_size('des', 'ecb');
	$pad = ord($str[($len = strlen($str)) - 1]);
	if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) {
		$str = substr($str, 0, strlen($str) - $pad);
	}
	return unserialize($str);
}

function dselect($sarray, $name, $title = '', $selected = null, $extend = '', $key = 1, $ov = '', $abs = 0) {
	$select = '<select name="'.$name.'" '.$extend.'>';
	if($title) $select .= '<option value="'.$ov.'">'.$title.'</option>';
	foreach($sarray as $k=>$v) {
		if(!$v) continue;
		$_selected = ($abs ? ($key ? $k : $v) === $selected : ($key ? $k : $v) == $selected) && $selected != '' ? ' selected=selected' : '';
		$select .= '<option value="'.($key ? $k : $v).'"'.$_selected.'>'.$v.'</option>';
	}
	$select .= '</select>';
	return $select;
}

function dcheckbox($sarray, $name, $checked = '', $extend = '', $key = 1, $except = '', $abs = 0) {
	$checked = $checked ? explode(',', $checked) : array();
	$except = $except ? explode(',', $except) : array();
	$checkbox = $sp = '';
	foreach($sarray as $k=>$v) {
		if(in_array($key ? $k : $v, $except)) continue;
		$sp = in_array($key ? $k : $v, $checked) ? ' checked ' : '';
		$checkbox .= '<input type="checkbox" name="'.$name.'" value="'.($key ? $k : $v).'"'.$sp.$extend.'> '.$v.'&nbsp;';
	}
	return $checkbox;
}

if(!function_exists('array_column')){
	function array_column($input, $columnKey, $indexKey=null){
		$columnKeyIsNumber      = (is_numeric($columnKey)) ? true : false;
		$indexKeyIsNull         = (is_null($indexKey)) ? true : false;
		$indexKeyIsNumber       = (is_numeric($indexKey)) ? true : false;
		$result                 = array();
		foreach((array)$input as $key=>$row){
			if($columnKeyIsNumber){
				$tmp            = array_slice($row, $columnKey, 1);
				$tmp            = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
			}else{
				$tmp            = isset($row[$columnKey]) ? $row[$columnKey] : null;
			}
			if(!$indexKeyIsNull){
				if($indexKeyIsNumber){
					$key        = array_slice($row, $indexKey, 1);
					$key        = (is_array($key) && !empty($key)) ? current($key) : null;
					$key        = is_null($key) ? 0 : $key;
				}else{
					$key        = isset($row[$indexKey]) ? $row[$indexKey] : 0;
				}
			}
			$result[$key]       = $tmp;
		}
		return $result;
	}
}

if(!function_exists('array_sort')) {
	function array_sort($arr,$keys,$type='desc'){
		$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if($type == 'asc'){
			asort($keysvalue);
		}else{
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
			$new_array[$k] = $arr[$k];
		}
		return $new_array;
	}
}




/**************************************************************
 *
 * ʹ���ض�function������������Ԫ��������
 * @param string &$array  Ҫ������ַ���
 * @param string $function Ҫִ�еĺ���
 * @return boolean $apply_to_keys_also  �Ƿ�ҲӦ�õ�key��
 * @access public
 *
 *************************************************************/
function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
{
	static $recursive_counter = 0;
	if (++$recursive_counter > 10000) {
		die('possible deep recursion attack');
	}
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			arrayRecursive($array[$key], $function, $apply_to_keys_also);
		} else {
			$array[$key] = $function($value);
		}

		if ($apply_to_keys_also && is_string($key)) {
			$new_key = $function($key);
			if ($new_key != $key) {
				$array[$new_key] = $array[$key];
				unset($array[$key]);
			}
		}
	}
	$recursive_counter--;
}

/**************************************************************
 *
 * ������ת��ΪJSON�ַ������������ģ�
 * @param array $array  Ҫת��������
 * @return string  ת���õ���json�ַ���
 * @access public
 *
 *************************************************************/
function JSON($array) {
	arrayRecursive($array, 'urlencode', true);
	$json = json_encode($array);
	return urldecode($json);
}
/**
 * ��ȡ��ǰҳ������URL��ַ
 */
function get_url() {
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
	return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}
function qx_in($str1,$str2)
{
	$arr=explode(',',$str2);
	foreach ($arr as $val){
		if($val==$str1)return " checked ";
	}
	return "";
}

//�����ļ���
function mkdirs($dir, $mode = 0777){
	if (is_dir($dir) || mkdir($dir, $mode)) return TRUE;
	if (!mkdirs(dirname($dir), $mode)) return FALSE;
	return mkdir($dir, $mode);

}


/*********************************************************************
��������:encrypt
��������:���ܽ����ַ���
ʹ�÷���:
����     :encrypt('str','E','nowamagic');
����     :encrypt('�����ܹ����ַ���','D','fno2o');
����˵��:
$string   :��Ҫ���ܽ��ܵ��ַ���
$operation:�ж��Ǽ��ܻ��ǽ���:E:����   D:����
$key      :���ܵ�Կ��(�ܳ�);
 *********************************************************************/
function urlencrypt($string,$operation,$key='azd'){
	$key=md5($key);
	$key_length=strlen($key);
	$string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
	$string_length=strlen($string);
	$rndkey=$box=array();
	$result='';
	for($i=0;$i<=255;$i++)
	{
		$rndkey[$i]=ord($key[$i%$key_length]);
		$box[$i]=$i;
	}
	for($j=$i=0;$i<256;$i++)
	{
		$j=($j+$box[$i]+$rndkey[$i])%256;
		$tmp=$box[$i];
		$box[$i]=$box[$j];
		$box[$j]=$tmp;
	}
	for($a=$j=$i=0;$i<$string_length;$i++)
	{
		$a=($a+1)%256;
		$j=($j+$box[$a])%256;
		$tmp=$box[$a];
		$box[$a]=$box[$j];
		$box[$j]=$tmp;
		$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
	}
	if($operation=='D')
	{
		if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8))
		{
			return substr($result,8);
		}
		else
		{
			return'';
		}
	}
	else
	{
		return str_replace('=','',base64_encode($result));
	}
}


//�������л�������ajax����
function url_param_encrypt($str=null,$operation){
	if(!$str)return false;
	if($operation=='E'){
		$data = str_replace('/','_',urlencrypt(serialize($str),$operation));
		$data = str_replace('+','-',$data);
		$data = str_replace('%','^',$data);
		return $data;
	}elseif($operation=='D'){
		$data = str_replace('^','%',$data);
		$str = str_replace('-','+',$str);
		$data = unserialize(urlencrypt(str_replace('_','/',$str),$operation));

		return $data;
	}
	return false;

}


function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){
	if(is_array($multi_array)){
		foreach ($multi_array as $row_array){
			if(is_array($row_array)){
				$key_array[] = $row_array[$sort_key];
			}else{
				return false;
			}
		}
	}else{
		return false;
	}
	array_multisort($key_array,$sort,$multi_array);
	return $multi_array;
}

function dstrpos($string, $arr, $returnvalue = false) {
	if(empty($string)) return false;
	foreach((array)$arr as $v) {
		if(strpos($string, $v) !== false) {
			$return = $returnvalue ? $v : true;
			return $return;
		}
	}
	return false;
}

function checkrobot($useragent = '') {
	static $kw_spiders = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
	static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');

	$useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
	if(strpos($useragent, 'http://') === false && dstrpos($useragent, $kw_browsers)) return false;
	if(dstrpos($useragent, $kw_spiders)) return true;
	return false;
}


