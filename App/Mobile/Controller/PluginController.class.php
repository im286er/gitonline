<?php
namespace Mobile\Controller;
/*插件控制器
 *
*
* */

class PluginController extends MobileController {
	
	public function index(){
	
		$this->mydisplay();
	}
	
	/**特价详情**/
	public function bargain(){
		$this->mydisplay();
	}

	/**团购详情**/
	public function groupon(){
		$this->mydisplay();
	}

	/**转盘详情**/
	public function turnplate(){
		$this->mydisplay();
	}

	public function grouponinfo(){
		$this->mydisplay();
	
	}
}