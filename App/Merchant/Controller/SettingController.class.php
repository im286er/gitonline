<?php
namespace Merchant\Controller;

class SettingController extends MerchantController {
	private $setting_file_path = '';

	public function _initialize()
	{
		parent::_initialize();
		$this->setting_file_path = $this->path.'setting.conf';
	}

	public function app()
	{
		if( IS_POST )
		{
			$consume_type = array_sum( $_POST['consume_type'] );
			$pay_type = array_sum( $_POST['pay_type'] );
			if( $consume_type==0 || $pay_type==0 ) exit('0');

			$setting_array = array();
			if( file_exists( $this->setting_file_path ) )
			{
				$setting_array = unserialize( file_get_contents( $this->setting_file_path ) );
			}
			$setting_array['consume_type'] = $consume_type;
			$setting_array['pay_type'] = $pay_type;

			if( count($_POST['modules']) != 6 ) exit('0');
			$setting_array['modules'] = $_POST['modules'];

			$s = file_put_contents($this->setting_file_path, serialize($setting_array));
			exit( $s!==false ? '1' : '0');
		}
		else
		{
			$setting_array = array();
			if( file_exists( $this->setting_file_path ) )
			{
				$setting_array = unserialize( file_get_contents( $this->setting_file_path ) );
			}
			$this->assign('setting', $setting_array);

			$array_icon = array();
			//首页的7个ICON，选择6个
			file_exists($this->path.'InfoMenu1Name.php') && $module1=file_get_contents($this->path.'InfoMenu1Name.php');
			if( !$module1 ) $module1 = '在线消费';
			$array_icon[] = array('name'=>$module1, 'value'=>'InfoMenu1Name');

			file_exists($this->path.'InfoMenu2Name.php') && $module2=file_get_contents($this->path.'InfoMenu2Name.php');
			if( !$module2 ) $module2 = '在线预订';
			$array_icon[] = array('name'=>$module2, 'value'=>'InfoMenu2Name');

			file_exists($this->path.'InfoMenuAppDownName.php') && $module3=file_get_contents($this->path.'InfoMenuAppDownName.php');
			if( !$module3 ) $module3 = '精品下载';
			$array_icon[] = array('name'=>$module3, 'value'=>'InfoMenuAppDownName');

			file_exists($this->path.'InfoMenu3Name.php') && $module4=file_get_contents($this->path.'InfoMenu3Name.php');
			if( !$module4 ) $module4 = '精彩视频';
			$array_icon[] = array('name'=>$module4, 'value'=>'InfoMenu3Name');

			file_exists($this->path.'WshopModuleName.php') && $module5=file_get_contents($this->path.'WshopModuleName.php');
			if( !$module5 ) $module5 = '自定模块';
			$array_icon[] = array('name'=>$module5, 'value'=>'WshopModuleName');

			//file_exists($this->path.'BoutiqueModuleName.php') && $module6=file_get_contents($this->path.'BoutiqueModuleName.php');
			//if( !$module6 ) $module6 = '秒杀活动';
			//$array_icon[] = array('name'=>$module6, 'value'=>'BoutiqueModuleName');

			file_exists($this->path.'ShopName.php') && $module7=file_get_contents($this->path.'ShopName.php');
			if( !$module7 ) $module7 = '品牌介绍';
			$array_icon[] = array('name'=>$module7, 'value'=>'ShopName');

			$this->assign('array_icon', $array_icon);
			$this->display();
		}
	}


	//设置消费类型的名称
	public function settitle()
	{
		$setting_array = array();
		if( file_exists( $this->setting_file_path ) )
		{
			$setting_array = unserialize( file_get_contents( $this->setting_file_path ) );
		}

		$consume_title_1 = I('post.title1', '店内消费');
		$consume_title_2 = I('post.title2', '外送上门');

		$setting_array['consume_title_1'] = $consume_title_1;
		$setting_array['consume_title_2'] = $consume_title_2;

		$s = file_put_contents($this->setting_file_path, serialize($setting_array));
		exit( $s!==false ? '1' : '0');
	}
}