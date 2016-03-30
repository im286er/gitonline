<?php
namespace Mobile\Controller;
//下单流程控制器

class CommentsController extends MobileController {

	public $action_name = 'Comments';

	/**
	* 显示评论列表
	*/
	public function index(){
		//查询留言
		$comments = M('comments');

		$opt = array(
				'jid' => $this->jid,
			);
		$comments_list = $comments->field('phone , content ,create_time')->where($opt)->select();

		$this->assign('comments_list' , $comments_list);
		$this->assign('jid',$jid);
		$this->mydisplay();
	}




	/**
	 * 添加评论
	 */
	public function edit(){
		//查询用户手机
		$phone = M('FlUser')->where(array('flu_userid'=>$this->mid))->getField('flu_phone');

		//添加评论
		$opt = array(
				'content'    =>I('get.content'),
				'jid'        =>$this->jid,
				'phone'      =>$phone,
				'create_time'=>date('Y-m-d'),
			);


		return M('Comments')->add($opt);
	}



}