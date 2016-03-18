<?php
namespace Common\Model;
use Think\Model;

class BookkeepingModel extends Model {

	public function calculate( $bmention='' ) {
		$data = array();


		/*
		$data['bjy'] = $bjy = $this->_GetRound($bmention*0.012); //计算手续费用
		$data['bjs'] = $bjs = $this->_GetRound( ($bmention-$bjy)*0.008 ); //计算技术服务费用
		$data['bsj'] = $bmention-$bjs-$bjy;//计算实际提现额度
		*/

		/**
		 * 如果小于350 元，收 2元，如果高于 350 按 0.6%，最高为 25元
		 */
		$data['bjs'] = 0;
		if( $bmention <= 350 ) {
			$data['bjy'] = $bjy = 2;
		} else {
			$bjy = $this->_GetRound( $bmention * 6 / 1000 );
			if( $bjy >= 25 ) $bjy = 25;
			$data['bjy'] = $bjy;
		}
		$data['bsj'] = $bmention - $bjy;
		return $data;	
	}

	public function btypes($type=null){
		$types = array(
			0 => '待审核',
			1 => '已打款',
			2 => '无效',
		);
		return $type!=''?$types[$type]:$types;
	}
	
	
	
	
	//获取比此值最大的一个小数
	private function _GetRound( $number ) {
		$_number = round( $number, 2);

		while( $_number < $number ) {
			$_number = $_number + 0.01;
		}
		
		return round($_number, 2);
	}
	
	
	
	
	
	
}

?>