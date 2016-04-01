<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Think;

class Page {
    // 分页栏每页显示的页数
    public $rollPage = 5;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页URL地址
    public $url     =   '';
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow    ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =    array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage%  %linkPage% %downPage% %first%  %prePage%   %nextPage% %end%');
    // 默认分页变量名
    protected $varPage;
	//URL规则
	protected $urlrules;
	
    /**
     * 架构函数
     * @access public
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows, $listRows='', $parameter='', $url='', $nowPages='') {
        $this->totalRows    =   $totalRows;
        $this->parameter    =   $parameter;
        $this->varPage      =   C('VAR_PAGE') ? C('VAR_PAGE') : 'page' ;
        if(!empty($listRows)) {
            $this->listRows =   intval($listRows);
        }
        $this->totalPages   =   ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages    =   ceil($this->totalPages/$this->rollPage);
        $this->nowPage      =   $nowPages ? $nowPages : !empty($_GET[$this->varPage]) ? intval($_GET[$this->varPage]) : 1;
	    if($this->nowPage<1) {
            $this->nowPage  =   1;
        } elseif(!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage  =   $this->totalPages;
        }
        $this->firstRow     =   $this->listRows*($this->nowPage-1);
        if(!empty($url))    $this->url  =   $url; 
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

	//分页显示输出
    public function show() {
        if(0 == intval($this->totalRows) || 1 == intval($this->totalPages)) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);
        // 分析分页参数
        if($this->url) {
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url, '', false), $depr).$depr.'__PAGE__';
        } else {
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter, $parameter);
            } elseif(is_array($this->parameter)) {
                $parameter      =   $this->parameter;
            } elseif(empty($this->parameter)) {
                unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  !empty($_POST) ? $_POST : $_GET;
                if(empty($var)) {
                    $parameter  =   array();
                } else {
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__PAGE__';
            $url            =   U('', $parameter);
        }
		
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0) {
            $upPage     =   "<a href='".str_replace('__PAGE__', $upRow, $url)."' class='a1'>".$this->config['prev']."</a>";
        } else {
            $upPage     =   "<a href='javascript:void(0);' class='a1'>".$this->config['prev']."</a>";
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<a href='".str_replace('__PAGE__', $downRow, $url)."' class='a1'>".$this->config['next']."</a>";
        } else {
            $downPage   =   "<a href='javascript:void(0);' class='a1'>".$this->config['next']."</a>";
        }
		
        if($nowCoolPage == 1) {
            $theFirst   =   '';
            $prePage    =   '';
        } else {
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<a href='".str_replace('__PAGE__', $preRow, $url)."' >上".$this->rollPage."页</a>";
            $theFirst   =   "<a href='".str_replace('__PAGE__', 1, $url)."' >".$this->config['first']."</a>";
        }
		
		// 1 2 3 4 5
        $linkPage = "";
        for($i=1; $i<=$this->rollPage; $i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a> ";
                } else {
                    break;
                }
            } else {
                if($this->totalPages != 1){
                    $linkPage .= "<span>".$page."</span> ";
                }
            }
        }
		
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   "";
            $theEnd     =   '';
        } else {
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<a href='".str_replace('__PAGE__', $nextRow, $url)."'  class='a1'>下".$this->rollPage."页</a>";
            $theEnd     =   "<a href='".str_replace('__PAGE__', $theEndRow, $url)."'  class='a1'>".$this->config['last']."</a>";
        }
		
		$this->totalRows = "<a class='a1'>".$this->totalRows."条</a>";
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }
	
	//文章列表页分页
	public function getPageLists($catid = '') {
		import("@.ORG.Url"); $url = new Url();
		$catid = $catid ? $catid : I('get.catid', 0, 'intval');
		
		if(!$catid || 0 == $this->totalRows || $this->totalPages == 1) return '';
        $p              = $this->varPage;
        $nowCoolPage    = ceil($this->nowPage / $this->rollPage);
		
		$upRow 			= $this->nowPage - 1;
        if ($upRow>0) {
            $upPage     = "<a href='".$url->categoryUrl($catid, $upRow)."' class='a1'>".$this->config['prev']."</a>";
        } else {
            $upPage     = "<a href='javascript:void(0);' class='a1' >".$this->config['prev']."</a>";
        }
		
		$downRow		= $this->nowPage + 1;
        if ($downRow <= $this->totalPages){
            $downPage   = "<a href='".$url->categoryUrl($catid, $downRow)."' class='a1'>".$this->config['next']."</a>";
        } else {
            $downPage   = "<a href='javascript:void(0);' class='a1'>".$this->config['next']."</a>";
        }
		
		
		if($nowCoolPage == 1) {
            $theFirst   = '';
        } else {
            $preRow     = $this->nowPage - $this->rollPage;
            $prePage    = "<a href='".$url->categoryUrl($catid, $preRow)."' >上".$this->rollPage."页</a>";
            $theFirst   = "<a href='".$url->categoryUrl($catid, 1)."' >".$this->config['first']."</a>";
        }
		
		// 1 2 3 4 5
        $linkPage = "";
        for($i=1; $i<=$this->rollPage; $i++){
            $page       =   ($nowCoolPage-1) * $this->rollPage + $i;
            if($page != $this->nowPage){
                if($page <= $this->totalPages){
                    $linkPage .= "<a href='".$url->categoryUrl($catid, $page)."'>".$page."</a> ";
                } else {
                    break;
                }
            } else {
                if($this->totalPages != 1 ){
                    $linkPage .= "<span>".$page."</span> ";
                }
            }
        }
		
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   "";
            $theEnd     =   '';
        } else {
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<a href='".$url->categoryUrl($catid, $nextRow)."'  class='a1'>下".$this->rollPage."页</a>";
            $theEnd     =   "<a href='".$url->categoryUrl($catid, $theEndRow)."'  class='a1'>".$this->config['last']."</a>";
        }
		
		$this->totalRows = "<a class='a1'>".$this->totalRows."条</a>";
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
	}
	
	public function getTotalPages() {
		return $this->totalPages;	
	}
	
	//获取URL规则
	private function getUrlrule() {
		$urlrules = $urlrulesArray = array();
		$urlrules = M('urlrule')->select();
		foreach($urlrules as $urlrule) {
			$urlrulesArray[$urlrule['urlruleid']] = $urlrule['urlrule'];
		}
		return $urlrulesArray;
	}
}
