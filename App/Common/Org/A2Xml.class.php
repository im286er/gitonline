<?php
/**
 +------------------------------------------------------------------------------
 * A2Xml管理类-数组转化为XML
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Cookie.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
namespace Common\Org;
class A2Xml {

  private $version  = '1.0';
  private $encoding  = 'UTF-8';
  private $root    = 'root';
  private $xml    = null;

  function __construct() {
    $this->xml = new \XmlWriter();
  }

  /****数组转化为XML****/
  function toXml($data, $eIsArray=FALSE) {
    if(!$eIsArray) {
      $this->xml->openMemory();
      $this->xml->startDocument($this->version, $this->encoding);
      $this->xml->startElement($this->root);
    }
    foreach($data as $key => $value){
  
      if(is_array($value)){
        $this->xml->startElement($key);
        $this->toXml($value, TRUE);
        $this->xml->endElement();
        continue;
      }
      $this->xml->writeElement($key, $value);
    }
    if(!$eIsArray) {
      $this->xml->endElement();
      return $this->xml->outputMemory(true);
    }
  }

	// Xml 转 数组, 包括根键，忽略空元素和属性，尚有重大错误
	public function xml_to_array( $xml ){
		$reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
				$count = count($matches[0]);
				$arr = array();
				for($i = 0; $i < $count; $i++)
				{
					$key = $matches[1][$i];
					$val = $this->xml_to_array( $matches[2][$i] );  // 递归
					if(array_key_exists($key, $arr))
					{
						if(is_array($arr[$key]))
						{
							if(!array_key_exists(0,$arr[$key])) 
							{
								$arr[$key] = array($arr[$key]);
							}
						}else{
							$arr[$key] = array($arr[$key]);
						}
						$arr[$key][] = $val;
					}else{
						$arr[$key] = $val;
					}
				}
				return $arr;
			}else{
				return $xml;
			}
	}

	// Xml 转 数组, 不包括根键
	public function xmltoarray( $xml ){
		$arr = $this->xml_to_array($xml);
		$key = array_keys($arr);
		return $arr[$key[0]];
	}

}