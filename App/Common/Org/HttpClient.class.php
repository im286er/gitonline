<?php
namespace Common\Org;
class HttpClient{
        private $ch;

        function __construct(){
                $this->ch = curl_init();
                curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; QQDownload 685; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E)');//UA
                curl_setopt($this->ch, CURLOPT_TIMEOUT, 40);//超时
                //curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
                curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($this->ch, CURLOPT_ENCODING, 'UTF-8');
        }

        function __destruct(){
                curl_close($this->ch);
        }

        final public function setProxy($proxy='http://127.0.0.1'){
                //curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
                //curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);//HTTP代理
                //curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);//Socks5代理
                curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
        }

        final public function setReferer($ref=''){
                if($ref != ''){
                        curl_setopt($this->ch, CURLOPT_REFERER, $ref);//Referrer
                }
        }

        final public function setCookie($ck=''){
                if($ck != ''){
                        curl_setopt($this->ch, CURLOPT_COOKIE, $ck);//Cookie
                }
        }

        final public function Get($url, $header=false, $nobody=false){
                curl_setopt($this->ch, CURLOPT_URL, $url);
                curl_setopt($this->ch, CURLOPT_POST, false);//POST
                curl_setopt($this->ch, CURLOPT_HEADER, $header);//返回Header
                curl_setopt($this->ch, CURLOPT_NOBODY, $nobody);//不需要内容
                return curl_exec($this->ch);
        }

        final public function Post($url, $data=array(), $header=false, $nobody=false){
                curl_setopt($this->ch, CURLOPT_URL, $url);
                curl_setopt($this->ch, CURLOPT_HEADER, $header);//返回Header
                curl_setopt($this->ch, CURLOPT_NOBODY, $nobody);//不需要内容
                curl_setopt($this->ch, CURLOPT_POST, true);//POST
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
                return curl_exec($this->ch);
        }
		final public function Json($url, $data=null){
			if(!$data)return false;											// 启动一个CURL会话
    		curl_setopt($this->ch, CURLOPT_URL, $url); 								// 要访问的地址
    		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE); 					// 对认证证书来源的检查
    		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE); 					// 从证书中检查SSL加密算法是否存在
    		curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
																				// 模拟用户使用的浏览器
   			curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1); 						// 使用自动跳转
    		curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1); 						// 自动设置Referer
    		curl_setopt($this->ch, CURLOPT_POST, 1); 								// 发送一个常规的Post请求
    		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data); 				// Post提交的数据包
			curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json; charset=utf-8','Content-Length:'.strlen($data)));
    		curl_setopt($this->ch, CURLOPT_TIMEOUT, 30); 							// 设置超时限制防止死循环
    		curl_setopt($this->ch, CURLOPT_HEADER, 0); 								// 显示返回的Header区域内容
    		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);	 					// 获取的信息以文件流的形式返回
    		$tmpInfo = curl_exec($this->ch); 										// 执行操作
    		if (curl_errno($this->ch)) {
      			echo 'Errno'.curl_error($this->ch);									// 捕抓异常
    		}
    		curl_close($this->ch); 													// 关闭CURL会话
   			return $tmpInfo; 													// 返回数据
        }

		final public function Xml($url, $data=null){
			if(!$data)return false;											// 启动一个CURL会话
    		curl_setopt($this->ch, CURLOPT_URL, $url); 								// 要访问的地址
    		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE); 					// 对认证证书来源的检查
    		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE); 					// 从证书中检查SSL加密算法是否存在
    		curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
																				// 模拟用户使用的浏览器
   			curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1); 						// 使用自动跳转
    		curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1); 						// 自动设置Referer
    		curl_setopt($this->ch, CURLOPT_POST, 1); 								// 发送一个常规的Post请求
    		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data); 				// Post提交的数据包
			curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('Content-Type:application/xml; charset=utf-8','Content-Length:'.strlen($data)));
    		curl_setopt($this->ch, CURLOPT_TIMEOUT, 30); 							// 设置超时限制防止死循环
    		curl_setopt($this->ch, CURLOPT_HEADER, 0); 								// 显示返回的Header区域内容
    		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);	 					// 获取的信息以文件流的形式返回
    		$tmpInfo = curl_exec($this->ch); 										// 执行操作
    		if (curl_errno($this->ch)) {
      			echo 'Errno'.curl_error($this->ch);									// 捕抓异常
    		}
    		curl_close($this->ch); 													// 关闭CURL会话
   			return $tmpInfo; 													// 返回数据
        }

}