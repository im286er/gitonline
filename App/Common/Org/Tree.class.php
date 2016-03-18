<?php
namespace Common\Org;

class Tree {
	public $data = array();	
	public $icon = array('&nbsp;&nbsp;&nbsp;&nbsp;│', '&nbsp;&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;&nbsp;└─ ');
	public $nbsp = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	public $rets = '';
	
	private static $ItreeInitialize;
	
	public static function ItreeInitialize() {
		if(!self::$ItreeInitialize || !self::$ItreeInitialize instanceof self) {
			self::$ItreeInitialize = new self();
		}
		return self::$ItreeInitialize;
	}
	
	public function initialize( array $array=array()) {
		$this->data=$array; $this->rets=''; return $this;
	}
	
	public function getChildList( $myid, $setMem=false) {
		$retarr = array();
		foreach($this->data as $key=>$temp) {
			if($temp['pid'] == $myid) {
				$retarr[$key] = $temp; if($setMem) unset($this->data[$key]);
			}	
		}
		return $retarr ? $retarr : false;
	}
	
	public function treeView($myid, $str, $adds='', $strgroup='') {
		$number = 1;
		$child = $this->getChildList( $myid, true);
		if(is_array($child) && !empty($child)) {
		    $total = count($child);
			foreach($child as $id=>$value) {
				$j = $k = '';
				if($number == $total) {
					$j .= $this->icon[2]; $k = $adds ? $this->nbsp : '';
				} else {
					$j .= $this->icon[1]; $k = $adds ? $this->icon[0] : '';
				}
				$spacer = $adds ? $adds.$j : '';
				@extract($value);
				$pid == 0 && $str_group ? eval("\$nstr = \"$strgroup\";") : eval("\$nstr = \"$str\";");
				$this->ret .= $nstr;
				$nbsp = $this->nbsp;
				$this->treeView($id, $str, $adds.$k.$nbsp, $strgroup);
				$number++;
			}
		}
		return $this->ret;
	}
	
	public function treeRule($myid, $str, $sid=0, $adds=''){
		$number=1;
		$child = $this->getChildList($myid);
		if( is_array($child) && !empty($child) ){
		    $total = count($child);
			foreach($child as $id=>$a){
				$j = $k = '';
				if($number == $total) {
					$j .= $this->icon[2]; $k = $adds ? $this->nbsp : ''; $this->data[$id]['rlst'] = 1;
				} else {
					$j .= $this->icon[1]; $k = $adds ? $this->icon[0] : '';
				}
				if($a['type']==3 && isset($this->data[$a['pid']]['rlst'])) { $j = '&nbsp;'.$j; }
				$spacer = $adds ? $adds.$j : '';
				$selected = $this->have($sid, $id) ? 'selected' : '';
				@extract($a); eval("\$nstr = \"$str\";");
				$this->rets .= $nstr;
				$this->treeRule($id, $str, $sid, $adds.$k.'&nbsp;');	
				$number++;
			}
		}
		return $this->rets;
	}
	
	public function getSortList($myid=0) {
		static $opinion_array = array();
		$child = $this->getChildList( $myid );
		if(is_array($child) && !empty($child)) {
			foreach($child as $opinion) {
				$opinion_array[$opinion['op_id']] = $opinion;
				$this->getSortList($opinion['op_id']);
			}
		}
		return $opinion_array;		
	}
	
	public function getLevel($id, $array=array(), $i=0) {
		foreach($array as $n=>$value) {
			if($value['id'] == $id) {
				if($value['pid']== 0) return $i;
				$i++;
				return $this->getLevel($value['pid'], $array, $i);
			}
		}
	}
	
	private function have($list, $item){
		return(strpos(',,'.$list.',', ','.$item.','));
	}
}
?>