<?php
namespace Rebateapp\Controller;

//充值
class RechargeController extends RebateviewController {
	private $usertype = 0;
	
	public function _initialize() {
		parent::_initialize();
		if( $this->userid ) {
			$usertype = M("flUser")->where("flu_userid=".$this->userid)->getField("flu_usertype");	
		}
		$this->usertype = $usertype && $usertype==1 ? "1" : "0";
		$this->assign('usertype', $this->usertype);
	}

	//话费充值
	public function calls() {
		if( IS_POST ) {
			$postnum = I('post.post_num', 0, 'intval');
			$pnumber = I('post.post_pnumber');
			
			if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[01256789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $pnumber)) E('提交失败');
			
			//获取当前手机号的返利信息
			$datalist = $this->_getRechargeByphone( $pnumber, $postnum );
			$this->assign('pnumber', $pnumber);
			$this->assign('data', $datalist);
			
			$linkurl = url_param_encrypt(U('Recharge/calls@flapp'), 'E');
			$this->assign('linkurl',$linkurl);
			
			//保存COOKIE
			$recharge_info['pnumber'] = $pnumber;
			$recharge_info['postnum'] = $postnum;
			setcookie('recharge_info', serialize($recharge_info));

			$this->display('Recharge_order_calls');
		} else {
			if( isset($_COOKIE['recharge_info']) && !empty($_COOKIE['recharge_info']) ) {
				$cookies = unserialize( $_COOKIE['recharge_info'] );
				$phone = $cookies['pnumber'];
			} else {
				$phone = M("flUser")->where("flu_userid=".$this->userid)->getField("flu_phone");
			}
						
			if( $phone ) { //如果存在手机号，则显示当前手机号所在城市的比例
				$return = $this->_getPhoneAddress( $phone ); $this->assign('phone', $phone);
			} else { //默认显示浙江移动的比例
				$return = array(1=>'移动', '2'=>'浙江');
			}
			$datainfo = M("czData")->where(array("province"=>$return[2], "type"=>$return[1]))->find();
			$pricelist = array(10, 20, 30, 50, 100, 200, 300, 500);
			$return_grade = array_shift( C('ORDER_RETURN_GRADE') );

			$datalist = array();
			foreach($pricelist as $i=>$price) {
				$datalist[$i][0] = $price = $pricelist[$i]; //实际支付金额
				if( $datainfo['pricetype'.$price] >= 100 ) {
					$datalist[$i][1] = 0; //返利金额
					$datalist[$i][2] = 0; //VIP获取返利
					$datalist[$i][3] = 0; //普通获取返利
				} else {
					$datalist[$i][1] = round( (100-$datainfo['pricetype'.$price])	* $price / 100 * 0.80, 2 );//返利金额
					$datalist[$i][2] = round( $datalist[$i][1] * $return_grade / 100, 2 );// VIP返利
					$datalist[$i][3] = round( $datalist[$i][2] * C('USER_RATION_VIP'), 2 );// 普通返利
				}					
			}
			$this->assign('datalist', $datalist);
			
			$Recharge = new \Common\Org\Recharge();
			$this->assign('balance', $Recharge->Pbackprice());
			$this->display();
		}
	}
	
	//话费提交订单
	public function callsubmit() {
		if(!$this->userid) $this->ajaxReturn(array("msg"=>"false", "content"=>"操作失败，请先登录"));
		
		if( !isset($_COOKIE['recharge_info']) || empty($_COOKIE['recharge_info']) )
		{
			$this->ajaxReturn(array('msg'=>'false', 'content'=>'您输入的信息不正确'));
		}
		$recharge_info = unserialize( $_COOKIE['recharge_info'] );
		
		$pnumber = $recharge_info['pnumber'];//手机号
		$postnum = $recharge_info['postnum'];//充值金额
		
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[01256789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $pnumber))
		{
			$this->ajaxReturn(array('msg'=>'false', 'content'=>'您要充值的信息不存在'));
		}
		
		$datalist = $this->_getRechargeByphone( $pnumber, $postnum );

		$oid = orderNumber();
		$order = M('fl_order');
		$opt = array(
				'flo_id' 			=> $oid,
				'flo_sid' 			=> 0,
				'flo_jid' 			=> 0,
				'flo_uid' 			=> $this->userid,
				'flo_receivingid' 	=> 0,
				'flo_dstime' 		=> date("Y-m-d H:i:s"),
				'flo_dstatus' 		=> 1,
				'flo_price'   		=> $postnum,
				'flo_pstatus' 		=> 0,
				'flo_gtype' 		=> 4,
				'flo_ptype' 		=> 0,
				'flo_pway' 			=> 'scancode',
				'flo_remarks' 		=> '',
				'flo_backprice' 	=> $datalist[1]
		);
		if( !$order->add($opt) ) {
			$this->ajaxReturn(array('msg'=>'false', 'content'=>'订单创建失败'));
		}
		
		$flginfo = array();
		$flginfo['flg_gid'] 	= 0;
		$flginfo['flg_oid']		= $oid;
		$flginfo['flg_name']	= "话费充值";
		$flginfo['flg_gdescription'] = $pnumber;//这个用于在充值接口
		$flginfo['flg_goprice']	= $postnum;
		$flginfo['flg_gdprice'] = $postnum;
		$flginfo['flg_number']	= 0;//如果是流量，保存流量信息，如果是充值，则为0
		$flginfo['flg_grebate']	= $datalist[1];
		if( !M("flGsnapshot")->add( $flginfo ) ) {
			$order->where( array("flo_id"=>$oid) )->delete();
			$this->ajaxReturn(array('msg'=>'false', 'content'=>'订单创建失败'));
		}
		setcookie('recharge_info', NULL, time()-3600);
		$this->ajaxReturn( array('msg'=>"true", "oid"=>$oid) );
	}
	
	
	//流量充值
	public function flow() {
		if( IS_POST ) {
			$pnumber = I('post.post_pnumber');//手机号
			$postnum = I('post.post_num');
			
			if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[01256789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $pnumber)) E('提交失败');
			$return = $this->_getPhoneAddress( $pnumber );
			if( $return[1] == '移动' ) {
				$datalist = C('flow.data1');
				setcookie('datatype', 'flow.data1');
			} elseif( $return[1] == '联通' ) {
				$datalist = C('flow.data2');
				setcookie('datatype', 'flow.data2');
			} elseif( $return[1] == '电信' ) {
				$datalist = C('flow.data3');
				setcookie('datatype', 'flow.data3');
			} else E('提交失败');
			
			$data = array();
			$datainfo = $datalist[ $postnum ];
			$return_grade = array_shift( C('ORDER_RETURN_GRADE') );

			$data[0] = $datainfo[0];//购买流量
			$data[1] = $datainfo[1];//实际支付金额
			$data[2] = round($datainfo[1] * (1-$datainfo[2]) * 0.8, 2);//返现金额
			$data[3] = round($data[2] * $return_grade / 100 , 2); //VIP返现金额
			$data[4] = round($data[3] * C('USER_RATION_VIP') , 2);//普通返现金额
			
			$this->assign('pnumber', $pnumber);
			$this->assign('data', $data);
			
			$linkurl = url_param_encrypt(U('Recharge/flow@flapp'), 'E');
			$this->assign('linkurl',$linkurl);
			
			//保存COOKIE
			setcookie('new_phone', $pnumber);
			setcookie('postnum', $postnum);
			
			$this->display('Recharge_order_flow');
		} else {
			if( isset($_COOKIE['new_phone']) && !empty($_COOKIE['new_phone']) ) {
				$phone = trim( $_COOKIE['new_phone'] );
			} else {
				$phone = M("flUser")->where("flu_userid=".$this->userid)->getField("flu_phone");
			}
			
			if( $phone ) { //如果存在手机号，则显示当前手机号所在城市的比例
				$return = $this->_getPhoneAddress( $phone ); $this->assign('phone', $phone);
			} else { //默认显示浙江移动的比例
				$return = array(1=>'移动', '2'=>'浙江');
			}
			
			if( $return[1] == '移动' ) {
				$datalist = C('flow.data1');
			} elseif( $return[1] == '联通' ) {
				$datalist = C('flow.data2');
			} elseif( $return[1] == '电信' ) {
				$datalist = C('flow.data3');	
			}
			
			$data = array();
			if( is_array($datalist) && !empty($datalist) ) {
				$return_grade = array_shift( C('ORDER_RETURN_GRADE') );
				foreach( $datalist as $k=>$v ) {
					$data[$k][0] = $v[0];//购买流量
					$data[$k][1] = $v[1];//实际支付金额
					$data[$k][2] = round($v[1] * (1-$v[2]) * 0.8, 2);//返现金额
					$data[$k][3] = round($data[$k][2] * $return_grade / 100 , 2);//VIP返现金额
					$data[$k][4] = round($data[$k][3] * C('USER_RATION_VIP') , 2);//普通返现金额
				}
			}
			$this->assign('datalist', $data);
			$this->assign('defaultv', array_shift($data));
			$this->display();	
		}		
	}
	
	//流量提交订单
	public function submit(){
		if( !$this->userid ) $this->ajaxReturn(array("msg"=>"false", "content"=>"操作失败，请先登录"));		
		
		if( !isset($_COOKIE['datatype']) || !isset($_COOKIE['new_phone']) || empty($_COOKIE['new_phone']) || !isset($_COOKIE['postnum']) || empty($_COOKIE['postnum']) )
		{
			$this->ajaxReturn(array('msg'=>'false', 'content'=>'您输入的信息不正确'));
		}

		$datalist = C( $_COOKIE['datatype'] );
		$pnumber = $_COOKIE['new_phone'];
		$postnum = $_COOKIE['postnum'];
		
		$datainfo = $datalist[ $postnum ];
		$return_grade = array_shift( C('ORDER_RETURN_GRADE') );

		$data[0] = $datainfo[0];//购买流量
		$data[1] = $datainfo[1];//实际支付金额
		$data[2] = round($datainfo[1] * (1-$datainfo[2]) * 0.8, 2);//返现金额
		
		if( !$data || !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[01256789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $pnumber)) {
			$this->ajaxReturn(array('msg'=>'false', 'content'=>'您要充值的信息不存在'));
		}
		
		$oprice = $data[1];	//实际支付金额
		$backprice = $data[2]; //返利金额
		
		if($this->usertype == '0') { //普通会员
			$backprice = round($backprice * C('USER_RATION_VIP'), 2);
		}
		
		$oid = orderNumber();
		$order = M('fl_order');
		$opt = array(
				'flo_id' 			=> $oid,
				'flo_sid' 			=> 0,
				'flo_jid' 			=> 0,
				'flo_uid' 			=> $this->userid,
				'flo_receivingid' 	=> 0,
				'flo_dstime' 		=> date("Y-m-d H:i:s"),
				'flo_dstatus' 		=> 1,
				'flo_price'   		=> $oprice,
				'flo_pstatus' 		=> 0,
				'flo_gtype' 		=> 5,
				'flo_ptype' 		=> 0,
				'flo_pway' 			=> 'scancode',
				'flo_remarks' 		=> '',
				'flo_backprice' 	=> $backprice
		);
		if( !$order->add($opt) ) {
			$this->ajaxReturn(array('msg'=>'false', 'content'=>'订单创建失败'));
		}
		
		$flginfo = array();
		$flginfo['flg_gid'] 	= 0;
		$flginfo['flg_oid']		= $oid;
		$flginfo['flg_name']	= "流量充值";
		$flginfo['flg_gdescription'] = $pnumber;//这个用于在充值接口
		$flginfo['flg_goprice']	= $data[1];
		$flginfo['flg_gdprice'] = $oprice;
		$flginfo['flg_number']	= $data[0];//如果是流量，保存流量信息，如果是充值，则为0
		$flginfo['flg_grebate']	= $data[2];
		if( !M("flGsnapshot")->add( $flginfo ) ) {
			$order->where( array("flo_id"=>$oid) )->delete();
			$this->ajaxReturn(array('msg'=>'false', 'content'=>'订单创建失败'));
		}
		setcookie('datatype', NULL, time()-3600);
		setcookie('new_phone', NULL, time()-3600);
		setcookie('postnum', NULL, time()-3600);
		$this->ajaxReturn( array('msg'=>"true", "oid"=>$oid) );
	}
		
	//ajax获取手机所在地
	public function ajaxPhoneAddress( $phone ) {
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[01256789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $phone) ) {
			exit( JSON( array('errno'=>1, 'error'=>'手机号格式不正确') ) );
		}
		$info = json_decode( getPhoneAddress( $phone ), true );
		if( $inofo['errno'] ) {
			exit( JSON( array('errno'=>2, 'error'=>$info['error']) ) );
		}
		exit( JSON( array('errno'=>0, 'data'=>$info['data']) ) ); 
	}
	
	//ajax获取当前手机的返利
	public function ajaxPhoneRecharge( $phone, $price ) {
		if( !$phone || !$price ) exit( "0" );
		$datainfo = $this->_getRechargeByphone($phone, $price);
		if( !$datainfo ) exit("0");
		
		exit( JSON($datainfo) );		
	}
	
	//ajax获取话费的返利比例（针对当前用户）
	private function _getRechargeByphone( $phone, $price=10 ) {
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[01256789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $phone) ) {
			return false;
		}
		$info = $this->_getPhoneAddress( $phone );
		if( !is_array($info) || empty($info) || !$info ) return false;
		
		$datainfo = M("czData")->where(array("province"=>$info[2], "type"=>$info[1]))->find();
		if( !$datainfo ) return false;
		
		$data = array();
		$data[0] = $price; //实际支付金额
		if( $datainfo['pricetype'.$price] >= 100 ) {//没有收入的情况下，返利为 0 
			$data[1] = $data[2] = $data[3] = 0;
		} else {
			$return_grade = array_shift( C('ORDER_RETURN_GRADE') );
			$backprice = round( (100-$datainfo['pricetype'.$price]) * $price / 100 * 0.8, 2);
			$data[1] = $backprice;//返现金额
			$data[2] = round($backprice*$return_grade / 100, 2); //个人返现
			$data[3] = round($data[2]*C('USER_RATION_VIP'), 2);
		}
		return $data;		
	}
	
	//获取当前手机的省份和运营商
	private function _getPhoneAddress( $phone ) {
		if( !preg_match("/^13[0-9]{9}$|^15[012356789][0-9]{8}$|^18[01256789][0-9]{8}$|^17[0-9]{9}$|^147[0-9]{8}$/", $phone) ) {
			return false;
		}
		$info = json_decode( getPhoneAddress( $phone ), true );
		if( $inofo['errno'] ) return false;
		
		$string = $info['data'];
		$return = array();
		if( stripos($string, '移动') !== false ) {
			$return[1] = '移动';
		} else if( stripos($string, '电信') !== false ) {
			$return[1] = '电信';
		} else if( stripos($string, '联通') !== false ) {
			$return[1] = '联通';
		} else return false;
		
		
		if( stripos($string, '河南') !== false ) {
			$return[2] = '河南';
		} else if( stripos($string, '广西') !== false ) {
			$return[2] = '广西';
		} else if( stripos($string, '山西') !== false ) {
			$return[2] = '山西';
		} else if( stripos($string, '安徽') !== false ) {
			$return[2] = '安徽';
		} else if( stripos($string, '北京') !== false  ) {
			$return[2] = '北京';
		} else if( stripos($string, '福建') !== false  ) {
			$return[2] = '福建';
		} else if( stripos($string, '浙江') !== false  ) {
			$return[2] = '浙江';
		} else if( stripos($string, '广东') !== false  ) {
			$return[2] = '广东';
		} else if( stripos($string, '甘肃') !== false  ) {
			$return[2] = '甘肃';
		} else if( stripos($string, '贵州') !== false  ) {
			$return[2] = '贵州';
		} else if( stripos($string, '海南') !== false  ) {
			$return[2] = '海南';
		} else if( stripos($string, '河北') !== false  ) {
			$return[2] = '河北';
		} else if( stripos($string, '黑龙江') !== false  ) {
			$return[2] = '黑龙江';
		} else if( stripos($string, '湖北') !== false  ) {
			$return[2] = '湖北';
		} else if( stripos($string, '湖南') !== false  ) {
			$return[2] = '湖南';
		} else if( stripos($string, '吉林') !== false  ) {
			$return[2] = '吉林';
		} else if( stripos($string, '江苏') !== false  ) {
			$return[2] = '江苏';
		} else if( stripos($string, '江西') !== false  ) {
			$return[2] = '江西';
		} else if( stripos($string, '辽宁') !== false  ) {
			$return[2] = '辽宁';
		} else if( stripos($string, '内蒙古') !== false  ) {
			$return[2] = '内蒙古';
		} else if( stripos($string, '宁夏') !== false  ) {
			$return[2] = '宁夏';
		} else if( stripos($string, '青海') !== false  ) {
			$return[2] = '青海';
		} else if( stripos($string, '山东') !== false  ) {
			$return[2] = '山东';
		} else if( stripos($string, '陕西') !== false  ) {
			$return[2] = '陕西';
		} else if( stripos($string, '上海') !== false  ) {
			$return[2] = '上海';
		} else if( stripos($string, '四川') !== false  ) {
			$return[2] = '四川';
		} else if( stripos($string, '天津') !== false  ) {
			$return[2] = '天津';
		} else if( stripos($string, '西藏') !== false  ) {
			$return[2] = '西藏';
		} else if( stripos($string, '新疆') !== false  ) {
			$return[2] = '新疆';
		} else if( stripos($string, '云南') !== false  ) {
			$return[2] = '云南';
		} else if( stripos($string, '重庆') !== false  ) {
			$return[2] = '重庆';
		} else return false;
		
		return $return;
	}
}
