<?php
namespace System\Controller;
use Common\Controller\ManagerController;

class StatisticsController extends ManagerController {
	
	public function distribution()
	{
		$_GET['type'] || $_GET['type'] = 1;

		$Statistics = new \Common\Org\Statistics();
		$this->assign('info', $Statistics->getDistributionInfo());
		$this->display();
	}
}