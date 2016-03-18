<?php
namespace Common\Org;
class HttpClient{
        private $ch;

        function __construct(){
                $this->ch = curl_init();
                curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; QQDownload 685; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E)');//UA
                curl_setopt($this->ch, CURLOPT_TIMEOUT, 40);//��ʱ
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
                //curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);//HTTP����
                //curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);//Socks5����
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
                curl_setopt($this->ch, CURLOPT_HEADER, $header);//����Header
                curl_setopt($this->ch, CURLOPT_NOBODY, $nobody);//����Ҫ����
                return curl_exec($this->ch);
        }

        final public function Post($url, $data=array(), $header=false, $nobody=false){
                curl_setopt($this->ch, CURLOPT_URL, $url);
                curl_setopt($this->ch, CURLOPT_HEADER, $header);//����Header
                curl_setopt($this->ch, CURLOPT_NOBODY, $nobody);//����Ҫ����
                curl_setopt($this->ch, CURLOPT_POST, true);//POST
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
                return curl_exec($this->ch);
        }
		final public function Json($url, $data=null){
			if(!$data)return false;											// ����һ��CURL�Ự
    		curl_setopt($this->ch, CURLOPT_URL, $url); 								// Ҫ���ʵĵ�ַ
    		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE); 					// ����֤֤����Դ�ļ��
    		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE); 					// ��֤���м��SSL�����㷨�Ƿ����
    		curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
																				// ģ���û�ʹ�õ������
   			curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1); 						// ʹ���Զ���ת
    		curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1); 						// �Զ�����Referer
    		curl_setopt($this->ch, CURLOPT_POST, 1); 								// ����һ�������Post����
    		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data); 				// Post�ύ�����ݰ�
			curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json; charset=utf-8','Content-Length:'.strlen($data)));
    		curl_setopt($this->ch, CURLOPT_TIMEOUT, 30); 							// ���ó�ʱ���Ʒ�ֹ��ѭ��
    		curl_setopt($this->ch, CURLOPT_HEADER, 0); 								// ��ʾ���ص�Header��������
    		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);	 					// ��ȡ����Ϣ���ļ�������ʽ����
    		$tmpInfo = curl_exec($this->ch); 										// ִ�в���
    		if (curl_errno($this->ch)) {
      			echo 'Errno'.curl_error($this->ch);									// ��ץ�쳣
    		}
    		curl_close($this->ch); 													// �ر�CURL�Ự
   			return $tmpInfo; 													// ��������
        }

		final public function Xml($url, $data=null){
			if(!$data)return false;											// ����һ��CURL�Ự
    		curl_setopt($this->ch, CURLOPT_URL, $url); 								// Ҫ���ʵĵ�ַ
    		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE); 					// ����֤֤����Դ�ļ��
    		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE); 					// ��֤���м��SSL�����㷨�Ƿ����
    		curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
																				// ģ���û�ʹ�õ������
   			curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1); 						// ʹ���Զ���ת
    		curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1); 						// �Զ�����Referer
    		curl_setopt($this->ch, CURLOPT_POST, 1); 								// ����һ�������Post����
    		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data); 				// Post�ύ�����ݰ�
			curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('Content-Type:application/xml; charset=utf-8','Content-Length:'.strlen($data)));
    		curl_setopt($this->ch, CURLOPT_TIMEOUT, 30); 							// ���ó�ʱ���Ʒ�ֹ��ѭ��
    		curl_setopt($this->ch, CURLOPT_HEADER, 0); 								// ��ʾ���ص�Header��������
    		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);	 					// ��ȡ����Ϣ���ļ�������ʽ����
    		$tmpInfo = curl_exec($this->ch); 										// ִ�в���
    		if (curl_errno($this->ch)) {
      			echo 'Errno'.curl_error($this->ch);									// ��ץ�쳣
    		}
    		curl_close($this->ch); 													// �ر�CURL�Ự
   			return $tmpInfo; 													// ��������
        }

}