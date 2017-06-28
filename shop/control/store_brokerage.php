<?php 
/**
 *
 * 佣金结算设置——我是卖家
 *
 *
 *
 *@since      聖蓝玫瑰提供个人技术支持 
 * 
 */
defined('In33hao') or exit('Access Invalid!');

class store_brokerageControl extends BaseSellerControl {
	public function __construct() {
		parent::__construct();
		Language::read('member_brokerage_index');
	}

	public function indexOp(){
		$this -> index_oneOp();
	}

	//每单
	public function index_oneOp(){
		$model_brokerage = Model('rebate');
		$model_goods = Model('goods');
		$model_member_seller = Model('member_seller');
		$model_member = Model('member');
		$model_privilege = Model('privilege');
		$model_rebate_one = Model('rebate_one');
		$model_signed = Model('signed');
		$model_order = Model('order');

		$seller_id = $_SESSION['store_id'];
		
		if ($_GET['submit'] == 'ok') {
			$buyer_id = $_GET['pay_buyer_id'];
			// $goods_id = $_GET['pay_goods_id'];
			$privilege_type_id = $_GET['pay_privilege_id'];
			$moon = $_GET['pay_moon'];
			$search = $_GET['pay_t'];
			$privilege_id = $_GET['pay_id'];

			//搜索指定的水厂政策
			$BuyerSellerVip = $model_member_seller -> getMemberSellerId($buyer_id,$seller_id);
			$FindPrivilege['seller_id'] = $seller_id;
			// $FindPrivilege['goods_id'] = $goods_id;
			$FindPrivilege['privilege_type'] = $privilege_type_id;
			$FindPrivilege['privilege_vip_type'] = $BuyerSellerVip['vip_id'];
			$FindPrivilege['privilege_time_type'] = $search;
			// $FindPrivilege['privilege_status'] = '1';
			$FindPrivilege['id'] = $privilege_id;
			$SellerPrivilege = $model_privilege -> getPrivilegeInfo($FindPrivilege);
			$goods_id = $SellerPrivilege['goods_id'];

			//搜索指定用户签约政策
			$FindListSigned['seller_id'] = $seller_id;
			$FindListSigned['user_id'] = $buyer_id;
			$SignedList = $model_signed -> getSignedList($FindListSigned);
			foreach ($SignedList as $key => $slt) {
				$Plist[] = $slt['pid'];
			}

			//验证指定用户是否签约
			if (in_array($SellerPrivilege['id'], $Plist)) {
				$start_time = strtotime($_GET['search_start_time']." 00:00:00");  
				$end_time = strtotime($_GET['search_end_time']." 23:59:59");  

				//与签约时间比较
				$FindListSigned['pid'] = $SellerPrivilege['id'];
				$SignedInfo = $model_signed -> findSigned($FindListSigned);
				if ($start_time <= $SignedInfo['lottime'] && $SignedInfo['lottime'] < $end_time) {
					$start_time = $SignedInfo['lottime'];
				}

				if ($end_time >= $SellerPrivilege['privilege_valid_endtime']) {
					$end_time = $SellerPrivilege['privilege_valid_endtime'];
				}

				$condition = " `order_state` = '40' and `add_time` BETWEEN  '{$start_time}' and '{$end_time}' ";

				$SearchOrd = $model_order -> getOrderList($condition);

				if (empty($SearchOrd)) {
					showDialog('未能查询到用户订单信息','reload','error');
					exit;
				}

				foreach ($SearchOrd as $key => $row) {
					//搜索订单中每笔下单量
					$OrderGoodsInfo = $model_order -> getOrderGoodsInfo(array('order_id'=>$row['order_id'],'goods_id'=>$goods_id));

					/*返利条件过滤*/
					$privilege_val = unserialize($SellerPrivilege['privilege_val']);
					foreach ($privilege_val as $kty => $value) {
						$underlying[] = $kty ;
					}

					switch ($privilege_type_id) {
						case '1':
							if ($OrderGoodsInfo['goods_id'] == $goods_id && $OrderGoodsInfo['goods_num'] >= $underlying[0]) {
								$quarter_data['store_id'] = $seller_id;
								$quarter_data['buyer_id'] = $buyer_id;
								$quarter_data['goods_id'] = $goods_id;
								$quarter_data['buyer_vip'] = $BuyerSellerVip['vip_id'];
								$quarter_data['updataId'] = $row['order_id'];

								$FindMoonOne = $model_rebate_one -> getRebateOne($quarter_data);
								if (empty($FindMoonOne) ) {
									$quarter_data1['store_id'] = $seller_id;
									$quarter_data1['buyer_id'] = $buyer_id;
									$quarter_data1['goods_id'] = $goods_id;
									$quarter_data1['buyer_vip'] = $BuyerSellerVip['vip_id'];
									$quarter_data1['updataId'] = $row['order_id'];
									$quarter_data1['add_time'] = time();
									$quarter_data1['day'] = date('Y-m-d');
									$quarter_data1['pay_fee'] = $OrderGoodsInfo['goods_pay_price'];
									$quarter_data1['pay_num'] = $OrderGoodsInfo['goods_num'];
									$quarter_data1['privilege_val'] = $SellerPrivilege['privilege_val'];
									$model_rebate_one -> addRebateOne($quarter_data1);
								}
							}
							break;
						case '2':
							if ($OrderGoodsInfo['goods_id'] == $goods_id && $OrderGoodsInfo['goods_pay_price'] >= $underlying[0]) {
								$quarter_data['store_id'] = $seller_id;
								$quarter_data['buyer_id'] = $buyer_id;
								$quarter_data['goods_id'] = $goods_id;
								$quarter_data['buyer_vip'] = $BuyerSellerVip['vip_id'];
								$quarter_data['updataId'] = $row['order_id'];

								$FindMoonOne = $model_rebate_one -> getRebateOne($quarter_data);
							
								if (empty($FindMoonOne) ) {
									$quarter_data1['store_id'] = $seller_id;
									$quarter_data1['buyer_id'] = $buyer_id;
									$quarter_data1['goods_id'] = $goods_id;
									$quarter_data1['buyer_vip'] = $BuyerSellerVip['vip_id'];
									$quarter_data1['updataId'] = $row['order_id'];
									$quarter_data1['add_time'] = time();
									$quarter_data1['day'] = date('Y-m-d');
									$quarter_data1['pay_fee'] = $OrderGoodsInfo['goods_pay_price'];
									$quarter_data1['pay_num'] = $OrderGoodsInfo['goods_num'];
									$quarter_data1['privilege_val'] = $SellerPrivilege['privilege_val'];
									$model_rebate_one -> addRebateOne($quarter_data1);
								}
							}
							break;
						default:
							# code...
							break;
					}	

				}
			
				showDialog('操作完成','index.php?act=store_brokerage&op=index_one','succ');
			}else{
				showDialog('该用户未签约！','reload','error');
			}
		}


		$quarter_list_condition['store_id'] = $seller_id;
		$quarter_list_condition['status'] = 1;
		$quarter_list = $model_rebate_one -> getRebateOneList($quarter_list_condition,'*',20);

		foreach ($quarter_list as $key => $gou) {
			$quarter_list[$key]['goods'] = $model_rebate_one -> getRebateOneListGoodsById($gou['goods_id']);
			$quarter_list[$key]['user'] = $model_rebate_one -> getRebateOneListMemberById($gou['buyer_id']);
			$quarter_list[$key]['pol'] = $model_rebate_one -> getRebateOneListPrivilegeById($gou['goods_id'],$gou['store_id'],$gou['buyer_vip'],$gou['privilege_val']);
			$quarter_list[$key]['pol']['privilege_val'] = unserialize($gou['privilege_val']);
			$quarter_list[$key]['pol']['privilege_val'] = array_filter($quarter_list[$key]['pol']['privilege_val']);

			//数据组装
			foreach ($quarter_list[$key]['pol']['privilege_val'] as $ky => $value) {
				$underlying[] = $ky ;
			}
			switch ($quarter_list[$key]['pol']['privilege_type']) {
				case '1':
					//按销量计算
					for ($i=0; $i < count($underlying) ; $i++) { 
						if ( $gou['pay_num'] >= $underlying[$i]) {
							$string = $underlying[$i];
						}
					}	
					break;
				case '2':
					//按金额计算
					for ($i=0; $i < count($underlying) ; $i++) { 
						if ( $gou['pay_fee'] >= $underlying[$i]) {
							$string = $underlying[$i];
						}
					}
					break;
				default:
					# code...
					break;
			}
			$count_list['ordId'] = $gou['id'];
			$count_list['type'] = 'rebate_one';
			$count_list['buyNum'] = $gou['pay_num'];
			$count_list['buyFee'] = $gou['pay_fee'];
			$count_list['seller_id'] = $gou['buyer_id'];
			$count_list['store_id'] = $seller_id;
			$count_list['scale'] = $quarter_list[$key]['pol']['privilege_val'][$string]*0.01; //计算后得出比例
			$count_list['scale_fee'] = $gou['pay_fee'] * $count_list['scale'];

			$count_list['searchId'] = $gou['updataId'];
			$count_list['privilege_val'] = $gou['privilege_val'];
			$count_list['goods_id'] = $gou['goods_id'];
			$quarter_list[$key]['scale_fee'] = $count_list['scale_fee'];
			$quarter_list[$key]['secret'] = base64_encode(json_encode($count_list));
		}

		// $goods_list = $model_goods -> getGoodsList(array('store_id'=>$_SESSION['store_id']));
		$onilnePrivilege = $model_privilege -> getPrivilegeList(array('seller_id'=>$_SESSION['store_id'],'share'=>'1','privilege_time_type'=>'1'));

		$shore_seller_list = $model_member_seller -> getMemberSellerList(array('seller_id'=>$_SESSION['store_id']));
		foreach ($shore_seller_list as $key => $vip) {
			$shore_seller_list[$key]['info'] = $model_member -> getMemberInfoByID($vip['buyer_id']);
		}

		Tpl::output('onilnePrivilege',$onilnePrivilege);
		Tpl::output('list',$quarter_list);
		Tpl::output('vip',$shore_seller_list);
		Tpl::output('show_page',$model_rebate_one->showpage());
		self::profile_menu('order_one');
		Tpl::showPage('store_brokerage.index_one');
	}

	//月度
	public function index_moonOp(){
		$model_brokerage = Model('rebate');
		$model_goods = Model('goods');
		$model_member_seller = Model('member_seller');
		$model_member = Model('member');
		$model_privilege = Model('privilege');
		$model_rebate_moon = Model('rebate_moon');
		$model_signed = Model('signed');
		$model_order = Model('order');

		$seller_id = $_SESSION['store_id'];

		if ($_GET['submit'] == 'ok') {
			$buyer_id = $_GET['pay_buyer_id'];
			// $goods_id = $_GET['pay_goods_id'];
			$privilege_type_id = $_GET['pay_privilege_id'];
			$moon = $_GET['pay_moon'];
			$search = $_GET['pay_t'];
			$privilege_id = $_GET['pay_id'];

			//搜索指定的水厂政策
			$BuyerSellerVip = $model_member_seller -> getMemberSellerId($buyer_id,$seller_id);
			$FindPrivilege['seller_id'] = $seller_id;
			// $FindPrivilege['goods_id'] = $goods_id;
			$FindPrivilege['privilege_type'] = $privilege_type_id;
			$FindPrivilege['privilege_vip_type'] = $BuyerSellerVip['vip_id'];
			$FindPrivilege['privilege_time_type'] = $search;
			// $FindPrivilege['privilege_status'] = '1';
			
			$FindPrivilege['id'] = $privilege_id;
			$SellerPrivilege = $model_privilege -> getPrivilegeInfo($FindPrivilege);
			$goods_id = $SellerPrivilege['goods_id'];

			//搜索指定用户签约政策
			$FindListSigned['seller_id'] = $seller_id;
			$FindListSigned['user_id'] = $buyer_id;
			$SignedList = $model_signed -> getSignedList($FindListSigned);
			foreach ($SignedList as $key => $slt) {
				$Plist[] = $slt['pid'];
			}

			//验证指定用户是否签约
			if (in_array($SellerPrivilege['id'], $Plist)) {
				$start_time = strtotime(date('Y')."-".$moon."-01 00:00:00");  
				$end_time = $start_time + (60*60*24*date("t",$start_time))-1; 

				//与签约时间比较
				$FindListSigned['pid'] = $SellerPrivilege['id'];
				$SignedInfo = $model_signed -> findSigned($FindListSigned);
				if ($start_time <= $SignedInfo['lottime'] && $SignedInfo['lottime'] < $end_time) {
					$start_time = $SignedInfo['lottime'];
				}
				if ($end_time >= $SellerPrivilege['privilege_valid_endtime']) {
					$end_time = $SellerPrivilege['privilege_valid_endtime'];
				}
				$condition = " `order_state` = '40' and `add_time` BETWEEN  '{$start_time}' and '{$end_time}' ";

				$SearchOrd = $model_order -> getOrderList($condition);
				if (empty($SearchOrd)) {
					showDialog('未能查询到用户订单信息','reload','error');
					exit;
				}
			
				foreach ($SearchOrd as $key => $row) {
					//搜索订单中每笔下单量
					$OrderGoodsInfo = $model_order -> getOrderGoodsInfo(array('order_id'=>$row['order_id'],'goods_id'=>$goods_id));
					
					if ($OrderGoodsInfo['goods_id'] == $goods_id) {
						$buyNum[] = $OrderGoodsInfo['goods_num'];
						$searchId[] = $OrderGoodsInfo['order_id'];
						$buyFee[] = $OrderGoodsInfo['goods_pay_price'];
					}
				}

				if (empty($buyNum) || empty($buyFee) || empty($searchId) ) {
					showDialog('未能查询到用户订单信息','reload','error');
					exit;
				}
				
				//组装数据
				$quarter_data['store_id'] = $seller_id;
				$quarter_data['buyer_id'] = $buyer_id;
				$quarter_data['goods_id'] = $goods_id;
				$quarter_data['moon'] = $moon;
				$quarter_data['buyer_vip'] = $BuyerSellerVip['vip_id'];

				$FindMoonOne = $model_rebate_moon -> getRebateMoon($quarter_data);

				$quarter_data['add_time'] = time();
				$quarter_data['pay_fee'] = array_sum($buyFee);
				$quarter_data['pay_num'] = array_sum($buyNum);
				$quarter_data['updataId'] = implode(',',$searchId);
				$quarter_data['privilege_val'] = $SellerPrivilege['privilege_val'];

		
				if (empty($FindMoonOne)) {
					//插入一条记录
					$model_rebate_moon -> addRebateMoon($quarter_data);
					showDialog('操作完成','index.php?act=store_brokerage&op=index_moon','succ');
				}else{
					if ($FindMoonOne['status'] == '1') {
						//修改
						$cnn['id'] = $FindMoonOne['id'];
						$model_rebate_moon -> editRebateAll($quarter_data,$cnn);
						showDialog('操作完成','index.php?act=store_brokerage&op=index_moon','succ');
					}else{
						showDialog($moon.'月份已给该用户返利。','reload','error');
					}
				}
			}else{
				showDialog('该用户未签约！','reload','error');
			}
		}

		$quarter_list_condition['store_id'] = $seller_id;
		$quarter_list_condition['status'] = 1;
		$quarter_list = $model_rebate_moon -> getRebateMoonList($quarter_list_condition);
		foreach ($quarter_list as $key => $gou) {
			$quarter_list[$key]['goods'] = $model_rebate_moon -> getRebateMoonListGoodsById($gou['goods_id']);
			$quarter_list[$key]['user'] = $model_rebate_moon -> getRebateMoonListMemberById($gou['buyer_id']);
			$quarter_list[$key]['pol'] = $model_rebate_moon -> getRebateMoonListPrivilegeById($gou['goods_id'],$gou['store_id'],$gou['buyer_vip'],$gou['privilege_val']);
			$quarter_list[$key]['pol']['privilege_val'] = unserialize($gou['privilege_val']);
			$quarter_list[$key]['pol']['privilege_val'] = array_filter($quarter_list[$key]['pol']['privilege_val']);
			//数据组装
			foreach ($quarter_list[$key]['pol']['privilege_val'] as $ky => $value) {
				$underlying[] = $ky ;
			}
			switch ($quarter_list[$key]['pol']['privilege_type']) {
				case '1':
					//按销量计算
					for ($i=0; $i < count($underlying) ; $i++) { 
						if ( $gou['pay_num'] >= $underlying[$i]) {
							$string = $underlying[$i];
						}
					}	
					break;
				case '2':
					//按金额计算
					for ($i=0; $i < count($underlying) ; $i++) { 
						if ( $gou['pay_fee'] >= $underlying[$i]) {
							$string = $underlying[$i];
						}
					}
					break;
				default:
					# code...
					break;
			}
			$count_list['ordId'] = $gou['id'];
			$count_list['type'] = 'rebate_moon';
			$count_list['buyNum'] = $gou['pay_num'];
			$count_list['buyFee'] = $gou['pay_fee'];
			$count_list['seller_id'] = $gou['buyer_id'];
			$count_list['store_id'] = $seller_id;
			$count_list['scale'] = $quarter_list[$key]['pol']['privilege_val'][$string]*0.01; //计算后得出比例
			$count_list['scale_fee'] = $gou['pay_fee'] * $count_list['scale'];
			$count_list['searchId'] = $gou['updataId'];
			$count_list['privilege_val'] = $gou['privilege_val'];
			$count_list['goods_id'] = $gou['goods_id'];
			$quarter_list[$key]['scale_fee'] = $count_list['scale_fee'];
			$quarter_list[$key]['secret'] = base64_encode(json_encode($count_list));
		}

		$shore_seller_list = $model_member_seller -> getMemberSellerList(array('seller_id'=>$_SESSION['store_id']));
		foreach ($shore_seller_list as $key => $vip) {
			$shore_seller_list[$key]['info'] = $model_member -> getMemberInfoByID($vip['buyer_id']);
		}

		$onilnePrivilege = $model_privilege -> getPrivilegeList(array('seller_id'=>$_SESSION['store_id'],'share'=>'1','privilege_time_type'=>'2'));
		// $goods_list = $model_goods -> getGoodsList(array('store_id'=>$_SESSION['store_id']));

		Tpl::output('onilnePrivilege',$onilnePrivilege);
		Tpl::output('vip',$shore_seller_list);
		Tpl::output('list',$quarter_list);
		self::profile_menu('order_moon');
		Tpl::output('show_page',$model_rebate_moon->showpage());
		Tpl::showPage('store_brokerage.index_moon');
	}

	//季度
	public function index_quarterOp(){
		$model_brokerage = Model('rebate');
		$model_goods = Model('goods');
		$model_member_seller = Model('member_seller');
		$model_member = Model('member');
		$model_privilege = Model('privilege');
		$model_rebate_quarter = Model('rebate_quarter');
		$model_signed = Model('signed');
		$model_order = Model('order');

		$seller_id = $_SESSION['store_id'];

		if ($_GET['submit'] == 'ok') {
			$buyer_id = $_GET['pay_buyer_id'];
			// $goods_id = $_GET['pay_goods_id'];
			$privilege_type_id = $_GET['pay_privilege_id'];
			$quarter = $_GET['pay_quarter'];
			$search = $_GET['pay_t'];
			$privilege_id = $_GET['pay_id'];

			//搜索指定的水厂政策
			$BuyerSellerVip = $model_member_seller -> getMemberSellerId($buyer_id,$seller_id);
			$FindPrivilege['seller_id'] = $seller_id;
			// $FindPrivilege['goods_id'] = $goods_id;
			$FindPrivilege['privilege_type'] = $privilege_type_id;
			$FindPrivilege['privilege_vip_type'] = $BuyerSellerVip['vip_id'];
			$FindPrivilege['privilege_time_type'] = $search;

			$FindPrivilege['id'] = $privilege_id;
			$SellerPrivilege = $model_privilege -> getPrivilegeInfo($FindPrivilege);
			$goods_id = $SellerPrivilege['goods_id'];

			//搜索指定用户签约政策
			$FindListSigned['seller_id'] = $seller_id;
			$FindListSigned['user_id'] = $buyer_id;
			$SignedList = $model_signed -> getSignedList($FindListSigned);
			foreach ($SignedList as $key => $slt) {
				$Plist[] = $slt['pid'];
			}

			//验证指定用户是否签约
			if (in_array($SellerPrivilege['id'], $Plist)) {
				switch ($quarter) {
					case '1': $start_time = strtotime(date('Y')."-01-01 00:00:00");  $end_time = strtotime(date('Y')."-03-31 23:59:59"); break;
					case '2': $start_time = strtotime(date('Y')."-04-01 00:00:00");  $end_time = strtotime(date('Y')."-06-30 23:59:59"); break;
					case '3': $start_time = strtotime(date('Y')."-07-01 00:00:00");  $end_time = strtotime(date('Y')."-09-30 23:59:59"); break;
					case '4': $start_time = strtotime(date('Y')."-10-01 00:00:00");  $end_time = strtotime(date('Y')."-12-31 23:59:59"); break;
					default:break;
				}
				//与签约时间比较
				$FindListSigned['pid'] = $SellerPrivilege['id'];
				$SignedInfo = $model_signed -> findSigned($FindListSigned);
				if ($start_time <= $SignedInfo['lottime'] && $SignedInfo['lottime'] < $end_time) {
					$start_time = $SignedInfo['lottime'];
				}
				if ($end_time >= $SellerPrivilege['privilege_valid_endtime']) {
					$end_time = $SellerPrivilege['privilege_valid_endtime'];
				}

				$condition = " `order_state` = '40' and `add_time` BETWEEN  '{$start_time}' and '{$end_time}' ";
				$SearchOrd = $model_order -> getOrderList($condition);
				if (empty($SearchOrd)) {
					showDialog('未能查询到用户订单信息','reload','error');
					exit;
				}

				foreach ($SearchOrd as $key => $row) {
					//搜索订单中每笔下单量
					$OrderGoodsInfo = $model_order -> getOrderGoodsInfo(array('order_id'=>$row['order_id'],'goods_id'=>$goods_id));
					if ($OrderGoodsInfo['goods_id'] == $goods_id) {
						$buyNum[] = $OrderGoodsInfo['goods_num'];
						$searchId[] = $OrderGoodsInfo['order_id'];
						$buyFee[] = $OrderGoodsInfo['goods_pay_price'];
					}
				}

				if (empty($buyNum) || empty($buyFee) || empty($searchId) ) {
					showDialog('未能查询到用户订单信息','reload','error');
					exit;
				}

				//组装数据
				$quarter_data['store_id'] = $seller_id;
				$quarter_data['buyer_id'] = $buyer_id;
				$quarter_data['goods_id'] = $goods_id;
				$quarter_data['quarter'] = $quarter;
				$quarter_data['buyer_vip'] = $BuyerSellerVip['vip_id'];

				$FindMoonOne = $model_rebate_quarter -> getRebateQuarter($quarter_data);

				$quarter_data['add_time'] = time();
				$quarter_data['pay_fee'] = array_sum($buyFee);
				$quarter_data['pay_num'] = array_sum($buyNum);
				$quarter_data['updataId'] = implode(',',$searchId);
				$quarter_data['privilege_val'] = $SellerPrivilege['privilege_val'];
				// var_dump($quarter_data);exit;
				if (empty($FindMoonOne)) {
					//插入一条记录
					$model_rebate_quarter -> addRebateQuarter($quarter_data);
					showDialog('操作完成','index.php?act=store_brokerage&op=index_quarter','succ');
				}else{
					if ($FindMoonOne['status'] == '1') {
						//修改
						$cnn['id'] = $FindMoonOne['id'];
						$model_rebate_quarter -> editRebateAll($quarter_data,$cnn);
						showDialog('操作完成','index.php?act=store_brokerage&op=index_quarter','succ');
					}else{
						showDialog('第'.$quarter.'季度已给该用户返利。','reload','error');
					}
				}
			}else{
				showDialog('该用户未签约！','reload','error');
			}
		}

		$quarter_list_condition['store_id'] = $seller_id;
		$quarter_list_condition['status'] = 1;
		$quarter_list = $model_rebate_quarter -> getRebateQuarterList($quarter_list_condition);
		foreach ($quarter_list as $key => $gou) {
			$quarter_list[$key]['goods'] = $model_rebate_quarter -> getRebateQuarterListGoodsById($gou['goods_id']);
			$quarter_list[$key]['user'] = $model_rebate_quarter -> getRebateQuarterListMemberById($gou['buyer_id']);
			$quarter_list[$key]['pol'] = $model_rebate_quarter -> getRebateQuarterListPrivilegeById($gou['goods_id'],$gou['store_id'],$gou['buyer_vip'],$gou['privilege_val']);
			$quarter_list[$key]['pol']['privilege_val'] = unserialize($gou['privilege_val']);
			$quarter_list[$key]['pol']['privilege_val'] = array_filter($quarter_list[$key]['pol']['privilege_val']);
			//数据组装
			foreach ($quarter_list[$key]['pol']['privilege_val'] as $ky => $value) {
				$underlying[] = $ky ;
			}
			switch ($quarter_list[$key]['pol']['privilege_type']) {
				case '1':
					//按销量计算
					for ($i=0; $i < count($underlying) ; $i++) { 
						if ( $gou['pay_num'] >= $underlying[$i]) {
							$string = $underlying[$i];
						}
					}	
					break;
				case '2':
					//按金额计算
					for ($i=0; $i < count($underlying) ; $i++) { 
						if ( $gou['pay_fee'] >= $underlying[$i]) {
							$string = $underlying[$i];
						}
					}
					break;
				default:
					# code...
					break;
			}
			$count_list['ordId'] = $gou['id'];
			$count_list['type'] = 'rebate_quarter';
			$count_list['buyNum'] = $gou['pay_num'];
			$count_list['buyFee'] = $gou['pay_fee'];
			$count_list['seller_id'] = $gou['buyer_id'];
			$count_list['store_id'] = $seller_id;
			$count_list['scale'] = $quarter_list[$key]['pol']['privilege_val'][$string]*0.01; //计算后得出比例
			$count_list['scale_fee'] = $gou['pay_fee'] * $count_list['scale'];
			$count_list['searchId'] = $gou['updataId'];
			$count_list['privilege_val'] = $gou['privilege_val'];
			$count_list['goods_id'] = $gou['goods_id'];
			$quarter_list[$key]['scale_fee'] = $count_list['scale_fee'];
			$quarter_list[$key]['secret'] = base64_encode(json_encode($count_list));
		}



		$shore_seller_list = $model_member_seller -> getMemberSellerList(array('seller_id'=>$_SESSION['store_id']));
		foreach ($shore_seller_list as $key => $vip) {
			$shore_seller_list[$key]['info'] = $model_member -> getMemberInfoByID($vip['buyer_id']);
		}

		// $goods_list = $model_goods -> getGoodsList(array('store_id'=>$_SESSION['store_id']));
		$onilnePrivilege = $model_privilege -> getPrivilegeList(array('seller_id'=>$_SESSION['store_id'],'share'=>'1','privilege_time_type'=>'3'));

		Tpl::output('onilnePrivilege',$onilnePrivilege);
		Tpl::output('vip',$shore_seller_list);
		Tpl::output('list',$quarter_list);
		self::profile_menu('order_quarter');
		Tpl::output('show_page',$model_rebate_quarter->showpage());
		Tpl::showPage('store_brokerage.index_quarter');
	}

	//年度
	public function index_yearOp(){
		$model_brokerage = Model('rebate');
		$model_goods = Model('goods');
		$model_member_seller = Model('member_seller');
		$model_member = Model('member');
		$model_privilege = Model('privilege');
		$model_rebate_year = Model('rebate_year');
		$model_signed = Model('signed');
		$model_order = Model('order');

		$seller_id = $_SESSION['store_id'];

		if ($_GET['submit'] == 'ok') {
			$buyer_id = $_GET['pay_buyer_id'];
			// $goods_id = $_GET['pay_goods_id'];
			$privilege_type_id = $_GET['pay_privilege_id'];
			$year = $_GET['pay_year'];
			$search = $_GET['pay_t'];
			$privilege_id = $_GET['pay_id'];

			//搜索指定的水厂政策
			$BuyerSellerVip = $model_member_seller -> getMemberSellerId($buyer_id,$seller_id);
			$FindPrivilege['seller_id'] = $seller_id;
			// $FindPrivilege['goods_id'] = $goods_id;
			$FindPrivilege['privilege_type'] = $privilege_type_id;
			$FindPrivilege['privilege_vip_type'] = $BuyerSellerVip['vip_id'];
			$FindPrivilege['privilege_time_type'] = $search;
			// $FindPrivilege['privilege_status'] = '1';

			$FindPrivilege['id'] = $privilege_id;
			$SellerPrivilege = $model_privilege -> getPrivilegeInfo($FindPrivilege);
			$goods_id = $SellerPrivilege['goods_id'];

			//搜索指定用户签约政策
			$FindListSigned['seller_id'] = $seller_id;
			$FindListSigned['user_id'] = $buyer_id;
			$SignedList = $model_signed -> getSignedList($FindListSigned);
			foreach ($SignedList as $key => $slt) {
				$Plist[] = $slt['pid'];
			}

			//验证指定用户是否签约
			if (in_array($SellerPrivilege['id'], $Plist)) {
				$start_time = strtotime($year."-01-01 00:00:00");  
				$end_time = strtotime($year."-12-31 23:59:59");

				//与签约时间比较
				$FindListSigned['pid'] = $SellerPrivilege['id'];
				$SignedInfo = $model_signed -> findSigned($FindListSigned);
				if ($start_time <= $SignedInfo['lottime'] && $SignedInfo['lottime'] < $end_time) {
					$start_time = $SignedInfo['lottime'];
				}
				if ($end_time >= $SellerPrivilege['privilege_valid_endtime']) {
					$end_time = $SellerPrivilege['privilege_valid_endtime'];
				}

				$condition = " `order_state` = '40' and `add_time` BETWEEN  '{$start_time}' and '{$end_time}' ";
				$SearchOrd = $model_order -> getOrderList($condition);
				if (empty($SearchOrd)) {
					showDialog('未能查询到用户订单信息','reload','error');
					exit;
				}

				foreach ($SearchOrd as $key => $row) {
					//搜索订单中每笔下单量
					$OrderGoodsInfo = $model_order -> getOrderGoodsInfo(array('order_id'=>$row['order_id'],'goods_id'=>$goods_id));
					if ($OrderGoodsInfo['goods_id'] == $goods_id) {
						$buyNum[] = $OrderGoodsInfo['goods_num'];
						$searchId[] = $OrderGoodsInfo['order_id'];
						$buyFee[] = $OrderGoodsInfo['goods_pay_price'];
					}
				}

				if (empty($buyNum) || empty($buyFee) || empty($searchId) ) {
					showDialog('未能查询到用户订单信息','reload','error');
					exit;
				}

				//组装数据
				$quarter_data['store_id'] = $seller_id;
				$quarter_data['buyer_id'] = $buyer_id;
				$quarter_data['goods_id'] = $goods_id;
				$quarter_data['year'] = $year;
				$quarter_data['buyer_vip'] = $BuyerSellerVip['vip_id'];

				$FindMoonOne = $model_rebate_year -> getRebateYear($quarter_data);

				$quarter_data['add_time'] = time();
				$quarter_data['pay_fee'] = array_sum($buyFee);
				$quarter_data['pay_num'] = array_sum($buyNum);
				$quarter_data['updataId'] = implode(',',$searchId);
				$quarter_data['privilege_val'] = $SellerPrivilege['privilege_val'];

				if (empty($FindMoonOne)) {
					//插入一条记录
					$model_rebate_year -> addRebateYear($quarter_data);
					showDialog('操作完成','index.php?act=store_brokerage&op=index_year','succ');
				}else{
					if ($FindMoonOne['status'] == '1') {
						//修改
						$cnn['id'] = $FindMoonOne['id'];
						$model_rebate_year -> editRebateAll($quarter_data,$cnn);
						showDialog('操作完成','index.php?act=store_brokerage&op=index_year','succ');
					}else{
						showDialog($year.'年已给该用户返利。','reload','error');
					}
				}
			}else{
				showDialog('该用户未签约！','reload','error');
			}
		}

		$quarter_list_condition['store_id'] = $seller_id;
		$quarter_list_condition['status'] = 1;
		$quarter_list = $model_rebate_year -> getRebateYearList($quarter_list_condition);
		foreach ($quarter_list as $key => $gou) {
			$quarter_list[$key]['goods'] = $model_rebate_year -> getRebateYearListGoodsById($gou['goods_id']);
			$quarter_list[$key]['user'] = $model_rebate_year -> getRebateYearListMemberById($gou['buyer_id']);
			$quarter_list[$key]['pol'] = $model_rebate_year -> getRebateYearListPrivilegeById($gou['goods_id'],$gou['store_id'],$gou['buyer_vip'],$gou['privilege_val']);
			$quarter_list[$key]['pol']['privilege_val'] = unserialize($gou['privilege_val']);
			$quarter_list[$key]['pol']['privilege_val'] = array_filter($quarter_list[$key]['pol']['privilege_val']);
			//数据组装
			foreach ($quarter_list[$key]['pol']['privilege_val'] as $ky => $value) {
				$underlying[] = $ky ;
			}
			switch ($quarter_list[$key]['pol']['privilege_type']) {
				case '1':
					//按销量计算
					for ($i=0; $i < count($underlying) ; $i++) { 
						if ( $gou['pay_num'] >= $underlying[$i]) {
							$string = $underlying[$i];
						}
					}	
					break;
				case '2':
					//按金额计算
					for ($i=0; $i < count($underlying) ; $i++) { 
						if ( $gou['pay_fee'] >= $underlying[$i]) {
							$string = $underlying[$i];
						}
					}
					break;
				default:
					# code...
					break;
			}
			$count_list['ordId'] = $gou['id'];
			$count_list['type'] = 'rebate_year';
			$count_list['buyNum'] = $gou['pay_num'];
			$count_list['buyFee'] = $gou['pay_fee'];
			$count_list['seller_id'] = $gou['buyer_id'];
			$count_list['store_id'] = $seller_id;
			$count_list['scale'] = $quarter_list[$key]['pol']['privilege_val'][$string]*0.01; //计算后得出比例
			$count_list['scale_fee'] = $gou['pay_fee'] * $count_list['scale'];
			$count_list['searchId'] = $gou['updataId'];
			$count_list['privilege_val'] = $gou['privilege_val'];
			$count_list['goods_id'] = $gou['goods_id'];
			$quarter_list[$key]['scale_fee'] = $count_list['scale_fee'];
			$quarter_list[$key]['secret'] = base64_encode(json_encode($count_list));
		}




		//搜索生成信息
		$shore_seller_list = $model_member_seller -> getMemberSellerList(array('seller_id'=>$_SESSION['store_id']));
		foreach ($shore_seller_list as $key => $vip) {
			$shore_seller_list[$key]['info'] = $model_member -> getMemberInfoByID($vip['buyer_id']);
		}

		// $goods_list = $model_goods -> getGoodsList(array('store_id'=>$_SESSION['store_id']));
		$onilnePrivilege = $model_privilege -> getPrivilegeList(array('seller_id'=>$_SESSION['store_id'],'share'=>'1','privilege_time_type'=>'4'));

		Tpl::output('onilnePrivilege',$onilnePrivilege);
		Tpl::output('vip',$shore_seller_list);
		Tpl::output('list',$quarter_list);
		self::profile_menu('order_year');
		Tpl::output('show_page',$model_rebate_year->showpage());
		Tpl::showPage('store_brokerage.index_year');
	}



	public function zb_payOp(){
		$secret = $_GET['secret'];
		Tpl::output('secret',$secret);
		Tpl::showPage('store_brokerage.index_pay','null_layout');
	}




	public function payOp(){
		$secret = $_POST['secret'];
		$secret = @json_decode(base64_decode($secret), true);

		$model_rebate_log = Model('rebate_log');
		$model_rebate = Model('rebate');
		$model_member = Model('member');
		$model_rebate_all = Model($secret['type']);
		$model_predeposit = Model('predeposit');

		//验证
		$store_login_pwd = $model_member -> getMemberInfoByID($_SESSION['member_id']);
		$login_pwd = md5($_POST['login_pwd']);
		if ($store_login_pwd['member_passwd'] != $login_pwd) {
			showDialog('密码不正确','reload','error');
			exit;
		}

		$data['buyNum'] = $secret['buyNum'];
		$data['buyFee'] = $secret['buyFee'];
		$data['seller_name'] = $_SESSION['store_name'];
		$data['buyer_id'] = $secret['seller_id'];
		$data['store_id'] = $secret['store_id'];
		$data['scale'] = $secret['scale'];
		$data['scale_fee'] = $secret['scale_fee'];
		$data['searchId'] = $secret['searchId'];
		$data['searchTime'] = $secret['searchTime'];
		$data['privilege_val'] = $secret['privilege_val'];
		$data['add_time'] = time();
		$data['from_to'] = $secret['type'];

		$add = $model_rebate_log -> addRebateLog($data);

		if ($add) {

			// //修改记录【原始】
			// $secret['searchId'] = explode(',',$secret['searchId']);
			// foreach ($secret['searchId'] as $key => $val) {
			// 	$update['status'] = '2';
			// 	$comm['id'] = $val;
			// 	$comm['buyer_id'] = $secret['seller_id'];
			// 	$model_rebate -> editRebate($update,$comm);
			// }

	
			//修改记录【新建】
			$ud['status'] = 2;
			$cm['id'] = $secret['ordId'];
			$model_rebate_all -> editRebateAll($ud,$cm);
			

			//为该用户增加水币
			$findUser = $model_member -> getMemberInfoByID($secret['seller_id']);
			$updataUser['water_fee'] = $findUser['water_fee']*1 + $secret['scale_fee']*1;
			$commUser['member_id'] = $secret['seller_id'];
			$model_member -> editMember($commUser,$updataUser);

			//查询水厂
			$store_info = Model('store') -> getStoreInfoByID($secret['store_id']);

			//写入水币记录表(买家)
			$posit['pdr_sn'] = $model_predeposit -> makeSn();
			$posit['pdr_member_id'] = $secret['seller_id'];
			$posit['pdr_member_name'] = $findUser['member_name'];
			$posit['pdr_amount'] = $secret['scale_fee'];
			$posit['pdr_payment_code'] = $secret['type'];
			$posit['pdr_payment_name'] =  '来自'.$store_info['store_name'].'的返利';
			$posit['pdr_add_time'] = time();
			$posit['pdr_payment_state'] = '1';
			$posit['pdr_payment_time'] = time();

			//查询商品信息
			$goods = Model('goods') -> getGoodsInfo(array('goods_id'=>$secret['goods_id']));
			$posit['code_info'] = serialize(array('goods_name'=>$goods['goods_name'],'store_name'=>$goods['store_name'],'oid'=>$secret['ordId']));

			$model_predeposit -> addPdRechargeSb($posit);


			//写入水币记录表(卖家)
			$log_sb_add = array();
			$log_sb_add['log_store_name'] = $_SESSION['store_name'];
			$log_sb_add['log_store_id'] = $data['store_id'];
			$log_sb_add['log_type'] = 'sb_rebate';
			$log_sb_add['log_fee'] = '-'.$secret['scale_fee'];
			$log_sb_add['log_add_time'] = time();

			switch ($secret['type']) {
				case 'rebate_quarter':
					$op = "index_quarter";
					$log_sb_add['log_desc'] = "确认按季度返还水币，返还给： ".$findUser['member_name'];
					break;
				case 'rebate_moon':
					$op = "index_moon";
					$log_sb_add['log_desc'] = "确认按月返还水币，返还给： ".$findUser['member_name'];
					break;
				case 'rebate_year':
					$op = "index_year";
					$log_sb_add['log_desc'] = "确认按年返还水币，返还给： ".$findUser['member_name'];
					break;
				default:
					$log_sb_add['log_desc'] = "确认按每笔返还水币，返还给： ".$findUser['member_name'];
					$op = "index_one";
					break;
			}

			Model('store') -> addStoreSbLog($log_sb_add);

			//减少商家水币金额
			$data_sb['water_fee'] = array('exp','water_fee-'.$secret['scale_fee']);
			Model('store') -> editStore($data_sb,array('store_id'=>$data['store_id']));

			showDialog('操作完成',"index.php?act=store_brokerage&op=".$op,'succ');
		}else{
			showDialog('操作失败','reload','error');
		}
	}

	/**
	 * 用户中心右边，小导航
	 *
	 * @param string    $menu_type  导航类型
	 * @param string    $menu_key   当前导航的menu_key
	 * @return
	 */
	private function profile_menu($menu_key='') {
		Language::read('member_brokerage_index');
		$menu_array =array(
				// array('menu_key'=>'brokerage','menu_name'=>Language::get('nc_member_brokerage_config'), 'menu_url'=>'index.php?act=store_brokerage&op=index'),
				array('menu_key'=>'order_one','menu_name'=>'单笔返利', 'menu_url'=>'index.php?act=store_brokerage&op=index_one'),
				array('menu_key'=>'order_moon','menu_name'=>'每月返利', 'menu_url'=>'index.php?act=store_brokerage&op=index_moon'),
				array('menu_key'=>'order_quarter','menu_name'=>'季度返利', 'menu_url'=>'index.php?act=store_brokerage&op=index_quarter'),
				array('menu_key'=>'order_year','menu_name'=>'每年返利', 'menu_url'=>'index.php?act=store_brokerage&op=index_year')
				);
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
	}
}