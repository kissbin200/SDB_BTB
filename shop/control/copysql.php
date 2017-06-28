<?php 
/**
 *  临时数据转移程序
 */

defined('In33hao') or exit('Access Invalid!');

class copysqlControl extends BaseGoodsControl {
	public function __construct() {
		parent::__construct ();
	}


	public function UserToMemberOp(){
		$old_sysuser_user = Model('old_sysuser_user');

		$n = 0;
		//查询未通过
		$userall = $old_sysuser_user -> where(array('status'=>successful,'user_id'=>$_GET['user_id'])) -> select();

		foreach ($userall as $key => $row) {

			/*查询用户属于哪个城市*/
			$area = explode('/', $row['area']);
			$param['admin_nickname'] =$area[1];
			$old_user_admin = Model('admin') -> infoAdmin($param,'admin_id');
			
			/*数据组合*/
			$data['member_name'] = $row['mobile'];
			$data['member_truename'] = $row['consignee'];
			$data['member_sex'] = $row['sex'];
			$data['member_passwd'] = '123456';//登录密码（默认：123456）
			$data['member_paypwd'] = '123456';//支付密码（默认：123456）
			$data['member_email'] = $row['email'];
			$data['member_mobile'] = $row['mobile'];
			$data['member_time'] = time();
			$data['from_company'] = $old_user_admin['admin_id'];

			//迁移
			if (!empty($old_user_admin['admin_id'])) {
				$user_empty =  Model('member') -> where(array('member_mobile'=>$row['mobile'])) ->find();
				
				if (empty($user_empty)) {
					$a = Model('member') -> addMember($data);

					if ($a) {
						$n ++;
					}
				}
			}
		}
		echo "成功迁移".$n."条数据。";
	}

	public function TradeToOrderOp(){
		$old_systrade_trade = Model('old_systrade_trade');//订单信息
		$old_sysshop_shop = Model('old_sysshop_shop');//水厂信息
		$old_sysuser_user = Model('old_sysuser_user');//用户信息
		$old_systrade_order = Model('old_systrade_order');//订单商品信息
		$model_store = Model('store');

		//查询单条数据
		$ord_one = $old_systrade_trade -> where(array('user_id'=>$_GET['user_id'])) -> select();
		// $ord_one = $old_systrade_trade -> where(array('user_id'=>16)) -> select();
		// var_dump($ord_one);exit;
		$n = 0;
		foreach ($ord_one as $key => $row) {
			//查询用户新ID
			$old_user = $old_sysuser_user -> where(array('user_id'=>$row['user_id'])) -> find();
			$new_user = Model('member') -> where(array('member_mobile'=>$old_user['mobile'])) -> find();
			//查询新店铺表ID
			$old_shop = $old_sysshop_shop -> where(array('shop_id'=>$row['shop_id'])) -> find();
			$new_shop = Model('store') ->field('store_id,store_name,store_client_gather') -> where( array('member_name'=>$old_shop['mobile'])  )->  find();

			//订单状态
			switch ($row['status']) {
				case 'WAIT_BUYER_PAY': $state='10';    break;
				case 'WAIT_SELLER_SEND_GOODS': $state='20';    break;
				case 'WAIT_BUYER_CONFIRM_GOODS': $state='30';    break;
				case 'TRADE_FINISHED': $state='40';    break;
				case 'TRADE_CLOSED': $state='0';    break;
				case 'TRADE_CLOSED_BY_SYSTEM': $state='0';    break;
			}

			//订单来源
			switch ($row['trade_from']) {
				case 'pc': $from='1';    break;
				case 'wap': $from='2';    break;
			}

			//重新生成订单号
			// $pay_sn = Logic('buy_1')->makePaySn($new_user['member_id']);
			$order_pay = array();
			$order_pay['pay_sn'] = $row['tid'];
			$order_pay['buyer_id'] = $new_user['member_id'];
			$order_pay_id = Model('order')->addOrderPay($order_pay);
			// var_dump($order_pay_id);

			//订单表数据
			$data['order_sn'] = Logic('buy_1')->makeOrderSn($order_pay_id);
			$data['pay_sn'] = $row['tid'];
			$data['store_id'] = $new_shop['store_id'];
			$data['store_name'] = $new_shop['store_name'];
			$data['buyer_id'] = $new_user['member_id'];
			$data['buyer_name'] = $new_user['member_truename'];
			$data['buyer_email'] = $new_user['member_email'];
			$data['buyer_phone'] = $new_user['member_mobile'];
			$data['add_time'] = $row['created_time'];
			$data['payment_code'] = $row['pay_type'];
			$data['payment_time'] = $row['pay_time'];
			$data['finnshed_time'] = $row['end_time'];
			$data['goods_amount'] = $row['payment'];
			$data['order_amount'] = $row['payment'];
			$data['shipping_fee'] = $row['post_fee'];
			$data['order_state'] = $state;
			$data['order_from'] = $from;
			


			//写入收货列表
			$empty_address = Model('address') -> getAddressInfo(array('member_id'=>$new_user['member_id'],'is_default'=>1));
			if (empty($empty_address)) {
				$area_id = Model('area') -> where(array('area_name'=>$row['receiver_district'])) -> find();
				$city_id = Model('area') -> where(array('area_name'=>$row['receiver_city'])) -> find();

				$address_data['member_id'] = $new_user['member_id'];
				$address_data['true_name'] = $empty_address['true_name'] = $new_user['member_truename'];
				$address_data['area_id'] = $area_id['area_id'];
				$address_data['city_id'] = $empty_address['city_id'] = $city_id['area_id'];
				$address_data['area_info'] = $empty_address['area_info'] = $row['receiver_state'].$row['receiver_city'].$row['receiver_district'];
				$address_data['address'] = $empty_address['address'] = $row['receiver_address'];
				$address_data['	tel_phone'] = '';
				$address_data['mob_phone'] = $empty_address['mob_phone']= $new_user['member_mobile'];
				$address_data['is_default'] = '1';
				$address_data['	dlyp_id'] = '0';

				Model('address') -> addAddress($address_data);
			}


				$serialize_area['phone'] = $empty_address['mob_phone'];
				$serialize_area['mob_phone'] = $empty_address['mob_phone'];
				$serialize_area['tel_phone'] = '0';
				$serialize_area['address'] = $empty_address['area_info'].$empty_address['address'];
				$serialize_area['area'] = $empty_address['area_info'];
				$serialize_area['street'] = $empty_address['address'];
				$serialize_area_ser = serialize($serialize_area);

				
			

			//迁移
			$ord_empty = Model('order') -> getOrderInfo(array('pay_sn'=>$row['tid']));
			if (empty($ord_empty)) {
				if (!empty($new_shop['store_id'])) {
					$a = Model('order') -> addOrder($data); //返回数据ID
					if ($a) {
						//订单商品信息
						$ord_goods_list = $old_systrade_order -> where(array('tid'=>$row['tid'])) -> select();
						foreach ($ord_goods_list as $ky => $ogl) {
							//查询商品新信息
							$news_goods = Model('goods') -> where(array('store_id'=>$new_shop['store_id'],'goods_name'=>$ogl['title'])) -> find();

							$data_ord['order_id'] = $a;
							$data_ord['goods_id'] = $news_goods['goods_id'];
							$data_ord['goods_name'] = $news_goods['goods_name'];
							$data_ord['goods_price'] = $news_goods['goods_price'];
							$data_ord['goods_num'] = $ogl['num'];
							$data_ord['goods_image'] = $news_goods['goods_image'];
							$data_ord['goods_pay_price'] = $news_goods['goods_price'] * $ogl['num'];
							$data_ord['store_id'] = $news_goods['store_id'];
							$data_ord['buyer_id'] = $new_user['member_id'];
							$data_ord['goods_type'] = '1';
							$data_ord['promotions_id'] = '0';
							$data_ord['commis_rate'] = '0';
							$data_ord['gc_id'] = $news_goods['gc_id'];
							$data_ord['goods_spec'] = '';
							$data_ord['goods_contractid'] = '';
							$data_ord['invite_rates'] = '0';

							Model('order') -> addOrderGood($data_ord);
							// var_dump($data_ord);
						}

						//写入订单信息扩展表
						$data_com['order_id'] = $a;
						$data_com['store_id'] = intval($new_shop['store_id']);
						$data_com['shipping_time'] = '';
						$data_com['shipping_express_id'] = '0';
						$data_com['evaluation_time'] = '';
						$data_com['evalseller_time'] = '';
						$data_com['order_message'] = '';
						$data_com['order_pointscount'] = '0';
						$data_com['voucher_price'] = '';
						$data_com['voucher_code'] = '';
						$data_com['deliver_explain'] = '';
						$data_com['daddress_id'] = '0';
						$data_com['reciver_name'] = $empty_address['true_name'];
						$data_com['reciver_info'] = $serialize_area_ser;
						$data_com['reciver_province_id'] = '0';
						$data_com['reciver_city_id'] = $empty_address['city_id'];
						$data_com['invoice_info'] = 'a:0:{}';
						$data_com['promotion_info'] = '';
						$data_com['dlyo_pickup_code'] = ''; 
						$data_com['promotion_total'] = '0.00';
						$data_com['discount'] = '0';
					
						$c = Model('order') -> addOrderCommon($data_com);


						if (empty($new_shop['store_client_gather'])) {
							/*写入第一个购买者ID*/
							$update = array('store_client_gather'=>$new_user['member_id']);
							$condition = array('store_id'=>$new_shop['store_id']);
							$EditStore = $model_store -> editStore($update,$condition);
						}else{
							$arr_gathe = explode(',',$new_shop['store_client_gather']);
							if (!in_array($new_user['member_id'],$arr_gathe)) {
								/*写入其它购买者ID*/
								$gathe = $new_shop['store_client_gather'].",".$new_user['member_id'];
								/*更新合集*/
								$update = array('store_client_gather'=>$gathe);
								$condition = array('store_id'=>$new_shop['store_id']);
								$EditStore = $model_store -> editStore($update,$condition);
							}
						}
					}
				}	
			}
			$n ++;	
		}
		echo "成功迁移".$n."条数据。";
	}
}