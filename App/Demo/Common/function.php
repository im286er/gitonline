<?php
function contains($str,$contain) {
	if(stripos($contain,"|") !== false) {
		$s = preg_split('/[|]+/i',$contain);
		$len = sizeof($s);
		for($i=0; $i < $len; $i++) {
			if(stripos($str,$s[$i]) !== false) {
				return(true);
			}
		}
	}
	if(stripos($str,$contain) !== false) {
		return(true);
	}
	return(false);
}

function upload_image_base64($imgData) {
	$file_path = '/Public/Upload/'.date('Y-m-d').'/';
	if( !file_exists(rtrim(APP_DIR, '/').$file_path) ) mkdir(rtrim(APP_DIR, '/').$file_path);
	
	$file_name = "";
	$flag = false;
	if(contains($imgData, "data:image/jpeg;")){
		$imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
		$file_name = time().'.jpg';
		$flag = true;
	} elseif(contains($imgData,"data:image/png")){
		$imgData = str_replace('data:image/png;base64,', '', $imgData);
		$file_name = time().'.png';
		$flag = true;
	}
	
	if($flag) {
		file_put_contents(rtrim(APP_DIR, '/').$file_path.$file_name, base64_decode(str_replace(" ", "+", $imgData)));
	}
	return $file_path.$file_name;
}

function cut_str($string, $sublen, $start = 0, $code = 'UTF-8') {
	if($code == 'UTF-8') {
		$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
		preg_match_all($pa, $string, $t_string);
		if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen));
		return join('', array_slice($t_string[0], $start, $sublen));
	} else {
		$start = $start*2;
		$sublen = $sublen*2;
		$strlen = strlen($string);
		$tmpstr = '';
		for($i=0; $i< $strlen; $i++) {
			if($i>=$start && $i< ($start+$sublen)) {
				if(ord(substr($string, $i, 1))>129) {
					$tmpstr.= substr($string, $i, 2);
				} else {
					$tmpstr.= substr($string, $i, 1);
				}
			}
			if(ord(substr($string, $i, 1))>129) $i++;
		}
		//if(strlen($tmpstr)< $strlen ) $tmpstr.= "...";
		return $tmpstr;
	}
}

function getstart($str,$offlen){
	$l = strlen($str)-$offlen;
	$temp = "";
	for($i=0;$i<$l;$i++){
		$temp.="*";
	}
	return $temp;
}