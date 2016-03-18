<?php
namespace Agent\Controller;
use Common\Controller\ManagerController;

class NodeController extends ManagerController {
 
    public function index(){
	
        $this->display();
    }
	public function add(){
		
		$this->display();
	}
    public function insert(){
    	$data=M("banner")->create();
   		$Agent=A("Agent/Agent");
   		$result=$Agent->upload('picture_IMG','',191,289, C('advert_img_url'),C('advert_img_link_dir'));
   		$data['bimg']=$result[0]['ipath'];
   		$dataresult=M("banner")->add($data);
   		if($dataresult){
   			$this->success('发布成功', U('Agent/Advert/index'));
   		}else{
   			$this->success('发布失败', U('Agent/Advert/index'));
   		}
    }
    
  
}