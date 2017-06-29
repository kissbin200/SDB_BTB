<?php
/**
 * APP访问接口集合
 */


defined('In33hao') or exit('Access Invalid!');
header("Access-Control-Allow-Origin:*");

class mobileApiControl extends BaseGoodsControl {
	public function __construct() {
		parent::__construct ();
	}

	/**
	 * [postLoginUser 用户登录]
	 * @return [json]         [数据]
	 */
	public function postLoginUserOp(){	
		$model_member   = Model('member');
		$login_info = array();
		$login_info['user_name'] = $_POST['user_mobile'];
		$login_code = $_POST['cms_code'];  //来源提交
		$now = time();

		$Fcode['smc_mobile'] = $login_info['user_name'];
		$Fcode['smc_type'] = '1';
		$code = Model('smc') -> getSmcInfo($Fcode);

		/*测试用*/
		if ($login_code != '4321') {
			if ($code['smc_munber'] != $login_code) {
				echo json_encode(array('code'=>'2000','msg'=>'验证码错误'));
				exit;
			}
			
			if ($now - $code['smc_addtime'] > 300) {
				echo json_encode(array('code'=>'2000','msg'=>'验证码过期'));
				exit;
			}
		}
		

		$member_info = $model_member->login($login_info);
		
		if(isset($member_info['error'])) {
			echo json_encode(array('code'=>'2000','msg'=>$member_info['error']));
			exit;
		}

		$model_member->createSession($member_info, true);
		if (empty($member_info)) {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
		}else{
			//写入token
			$addData['member_id'] = $FindData['member_id'] = $member_info['member_id'];
			$FindToken = Model('mb_user_token') -> getMbUserTokenInfo($FindData);
			if (empty($FindToken)) {
				$addData['token'] = $token = $this -> getAskToken($login_info['user_name'],$login_code);
				$addData['member_name'] = $member_info['member_name'];
				$addData['login_time'] = time();
				$addData['client_type'] = 'APP';
				Model('mb_user_token') -> addMbUserToken($addData);
			}else{
				$upData['token'] = $token = $this -> getAskToken($login_info['user_name'],$login_code);
				$upData['login_time'] = time();
				Model('mb_user_token') -> updateMemberToken($upData,$member_info['member_id']);
			}
			$member_info['token'] = $token;
			$member_info['token_addtime'] = time();
			$member_info['avatar_url'] = getMemberAvatar($member_info['member_avatar']);

			echo json_encode(array('code'=>'1000','msg'=>'有效用户','data'=>$member_info));
		}
	}


	/**
	 * [getUserSeeGoodsList 用户可以看到的产品]
	 * @return [type] [description]
	 */
	public function getUserSeeGoodsListOp(){

		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$from_company = $Verify['info']['from_company']; //所属的子公司
		$StoreOnlineList = array();
		$StoreOnlineList['back_company'] = $from_company;

		$storeList = Model('store') -> getStoreOnlineList($StoreOnlineList);

		$Goods_Find_Info = array();

		foreach ($storeList as $key => $slt) {
			$find_id[] = $slt['store_id'];
		}
		$find_id = implode(',',$find_id);

		//分页
		$pindex = max(1, intval($_GET['page']));
		$page = 7;
		$limit = ($pindex - 1)*$page .",".$page;

		// $goods = Model('goods') -> getGoodsList(" `store_id` IN ({$find_id}) and `goods_state` = '1' ",'*','',' goods_salenum DESC ');   " `store_id` IN ({$find_id}) and `goods_state` = '1' "
		
		$goods_see['store_id'] = array('in',$find_id);
		$goods_see['goods_state'] = 1;
		$goods = Model('goods') -> getGoodsList2($goods_see,'*','',' goods_salenum DESC ',$limit);

		foreach ($goods as $ky => $gd) {
			$Goods_Find_Info[$ky]['goodsId']= $gd['goods_id'];
			$Goods_Find_Info[$ky]['goodsCommonid']= $gd['goods_commonid'];
			$Goods_Find_Info[$ky]['goodsName']= $gd['goods_name'];
			$Goods_Find_Info[$ky]['storeName']= $gd['store_name'];
			$Goods_Find_Info[$ky]['goodsJingle']= $gd['goods_jingle'];
			$Goods_Find_Info[$ky]['goodsPromotionPrice']= $gd['goods_promotion_price'];
			$Goods_Find_Info[$ky]['goodsMarketprice']= $gd['goods_marketprice'];
			$Goods_Find_Info[$ky]['goodsStorage']= $gd['goods_storage'];
			$Goods_Find_Info[$ky]['goodsDiStorage']= $gd['goods_distorage'];
			$Goods_Find_Info[$ky]['goodsImg']= cthumb($gd['goods_image'],360,$gd['store_id']);
			$Goods_Find_Info[$ky]['goods_salenum'] = $gd['goods_salenum'];

			//会员价格
			$member_vip = Model('member_seller') -> getMemberSeller(array('buyer_id'=>$Verify['info']['member_id'],'seller_id'=>$gd['store_id']));
			if ($member_vip['vip_id'] > 0) {
				$vip_info = Model('member_vip') -> getMemberVip(array('vip_level_seller_id'=>$gd['store_id'],'id'=>$member_vip['vip_id']));
				$goods_price_vip = unserialize($gd['goods_price_vip']);
				if ($goods_price_vip) {
					$Goods_Find_Info[$ky]['goodsPrice']= $goods_price_vip[$vip_info['vip_level_name']];
				}else{
					$Goods_Find_Info[$ky]['goodsPrice']= $gd['goods_price'];
				}
			}else{
				$Goods_Find_Info[$ky]['goodsPrice']= $gd['goods_price'];
			}
			



			//查询商品是否有返利政策、用户是否参与   
			$in_store_vip = Model('member_seller') -> getMemberSeller(array('seller_id'=>$gd['store_id'],'buyer_id'=>$Verify['info']['member_id']));
			$goodspri = Model('privilege') -> getPrivilegeInfo(array('goods_id'=>$gd['goods_id'],'privilege_status'=>1,'privilege_vip_type'=>$in_store_vip['vip_id']));
			// $goodspri = Model('privilege') -> getPrivilegeInfo(array('goods_id'=>$gd['goods_id']));
			if (!empty($goodspri)) {
				//查询用户是否为该水厂的会员
				if (!empty($in_store_vip)) {
					$Goods_Find_Info[$ky]['goodsPri'] = '1';
					$userpri = Model('signed') -> findSigned(array('pid'=>$goodspri['id'],'user_id'=>$Verify['info']['member_id']));
					if (!empty($userpri)) {
						$Goods_Find_Info[$ky]['userPri'] = '1';
					}
				}
			}
		}

		echo json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$Goods_Find_Info));
	}


	/**
	 * [getUserSeeGoodsInfoOp 用户可以看到的商品详情]
	 * @return [type] [description]
	 */
	public function getUserSeeGoodsInfoOp(){
		$goods_id =  intval($_GET['goods_id']);
		$user_id = $_GET['user_id']; //用户ID

		$Verify = $this -> VerifyUser($user_id);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}	
	}


	/**
	 * [getGoodsCart 加入购物车]
	 * @return [type] [description]
	 */
	public function getGoodsCartOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$goodsid = $_GET['goodsId']; //商品ID
		$pay_num = $_GET['payNum']; //购买数量

		$select['goods_id'] = $goodsid;
		$select['buyer_id'] = $Verify['info']['member_id'];
		$buygoods = Model('cart') -> getCartInfo($select);
		if (!empty($buygoods)) {
			$udata['goods_num'] = array('exp','goods_num+'.$pay_num);;
			$upnum = Model('cart') -> editCart($udata,$select);
			if ($upnum) {
				exit(json_encode(array( 'code'=>'1000','msg'=>'购买数量已累加' )));
			}else{
				exit(json_encode(array( 'code'=>'2000','msg'=>'购买数量累加失败' )));
			}
		}

		$goods = Model('goods') -> getGoodsDetail($goodsid);

		//数据组装
		$add_cart = array();
		$add_cart['goods_id'] = $goodsid;
		$add_cart['buyer_id'] = $Verify['info']['member_id'];
		$add_cart['store_id'] = $goods['goods_info']['store_id'];
		$add_cart['store_name'] = $goods['goods_info']['store_name'];
		$add_cart['goods_name'] = $goods['goods_info']['goods_name'];
		$add_cart['goods_image'] = $goods['goods_info']['goods_image'];
		$add_cart['bl_id'] = 0;

		//会员等级价格
		$member_vip = Model('member_seller') -> getMemberSeller(array('buyer_id'=>$Verify['info']['member_id'],'seller_id'=>$goods['goods_info']['store_id']));
		if ($member_vip['vip_id'] > 0) {
			$vip_info = Model('member_vip') -> getMemberVip(array('vip_level_seller_id'=>$goods['goods_info']['store_id'],'id'=>$member_vip['vip_id']));
			$goods_price_vip = unserialize($goods['goods_info']['goods_price_vip']);
			if ($goods_price_vip) {
				$add_cart['goods_price']= $goods_price_vip[$vip_info['vip_level_name']];
			}else{
				$add_cart['goods_price'] = $goods['goods_info']['goods_price'];
			}
		}else{
			$add_cart['goods_price'] = $goods['goods_info']['goods_price'];
		}

		$add = Model('cart') -> addCart($add_cart,'db',$pay_num);
		if ($add) {
			echo json_encode(array('code'=>'1000','msg'=>'操作成功'));
		}else{
			echo json_encode(array('code'=>'2000','msg'=>'操作失败'));
		}
	}

	/**
	 * [getGoodsCartListOp 购物车列表]
	 * @return [type] [description]
	 */
	public function getGoodsCartListOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$new_list_info = array();
		$list_info_cart = array();
		$list_info_cart_goods = array();

		$find['buyer_id'] = $Verify['info']['member_id'];
		$list = Model('cart') -> listCart('db',$find);

		foreach ($list as $key => $value) {
			$list[$key]['goods_image'] = cthumb($value['goods_image'],60,$value['store_id']);
			$new_list_info[] = $value['store_id'] ;
			$list_info_cart_goods[] = $value['goods_id'] ;
		}
		$new_list_info = array_unique($new_list_info);
		
		foreach ($new_list_info as $key => $nid) {
			$finddata['store_id'] = $nid;
			$finddata['buyer_id'] = $Verify['info']['member_id'];
			$list_info_cart[] = Model('cart') -> listCart('db',$finddata);
		}

		foreach ($list_info_cart as $key => $lic) {
			foreach ($lic as $ky => $gg) {
				$goods = Model('goods') -> getGoodsInfo(array('goods_id'=>$gg['goods_id']));
				$list_info_cart[$key][$ky]['goods_storage'] = $goods['goods_storage'];
				$list_info_cart[$key][$ky]['goods_distorage'] = $goods['goods_distorage'];
			}		
		}
		echo json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$list_info_cart));
	}

	/**
	 * [getGoodsCartCountOp 查询购物车数量]
	 * @return [type] [description]
	 */
	public function getGoodsCartCountOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$mun = Model('cart') -> countCartByMemberId($Verify['info']['member_id']);
		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$mun)));
	}


	/**
	 * [getGoodsCartPlusOp 购物车列表修改商品数量]
	 * @return [type] [description]
	 */
	public function getGoodsCartPlusOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$cart_id    = intval(abs($_GET['cart_id']));
		$quantity   = intval(abs($_GET['quantity']));

		if(empty($cart_id) || empty($quantity)) {
			exit(json_encode(array( 'code'=>'2000','msg'=>'无参数' )));
		}

		$model_cart = Model('cart');
		$model_goods= Model('goods');
		$logic_buy_1 = logic('buy_1');

		//存放返回信息
		$return = array();

		$cart_info = $model_cart->getCartInfo(array('cart_id'=>$cart_id,'buyer_id'=>$Verify['info']['member_id']));

		//普通商品
		$goods_id = intval($cart_info['goods_id']);
		$goods_info = $logic_buy_1->getGoodsOnlineInfo($goods_id,$quantity);
		if(empty($goods_info)) {
			$return['state'] = 'invalid';
			$return['msg'] = '商品已被下架';
			$return['subtotal'] = 0;
			QueueClient::push('delCart', array('buyer_id'=>$Verify['info']['member_id'],'cart_ids'=>array($cart_id)));
			exit(json_encode(array('code'=>'2000','msg'=>$return['msg'])));
		}

		//抢购
		$logic_buy_1->getGroupbuyInfo($goods_info);

		//限时折扣
		$logic_buy_1->getXianshiInfo($goods_info,$quantity);

		$quantity = $goods_info['goods_num'];

		if(intval($goods_info['goods_storage']) < $quantity) {
			$return['state'] = 'shortage';
			$return['msg'] = '库存不足';
			$return['goods_num'] = $goods_info['goods_storage'];
			$return['goods_price'] = $goods_info['goods_price'];
			$return['subtotal'] = $goods_info['goods_price'] * intval($goods_info['goods_storage']);
			$model_cart->editCart(array('goods_num'=>$goods_info['goods_storage']),array('cart_id'=>$cart_id,'buyer_id'=>$Verify['info']['member_id']));
			exit(json_encode(array('code'=>'2000','msg'=>$return['msg'])));
		}

		$data = array();
		$data['goods_num'] = $quantity;
		$data['goods_price'] = $goods_info['goods_price'];
		$update = $model_cart->editCart($data,array('cart_id'=>$cart_id,'buyer_id'=>$Verify['info']['member_id']));
		if ($update) {
			$return = array();
			$return['state'] = 'true';
			$return['subtotal'] = $goods_info['goods_price'] * $quantity;
			$return['goods_price'] = $goods_info['goods_price'];
			$return['goods_num'] = $quantity;
		} else {
			$return = array('msg'=>Language::get('cart_update_buy_fail','UTF-8'));
		}
		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$return)));
	}


	/**
	 * [getGoodsCartDelOp 删除购物车商品]
	 * @return [type] [description]
	 */
	public function getGoodsCartDelOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$cart_id    = intval(abs($_GET['cart_id']));
		$model_cart = Model('cart');

		$del = $model_cart -> delCart('db',array('cart_id'=>$cart_id));
		if ($del) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'操作失败')));
		}
	}




	/**
	 * [getGoodsCartPayStep1Op 购物车结算] 第一步
	 * @return [type] [description]
	 */
	public function getGoodsCartPayStep1Op(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		//数据组装
		$data = array();
		if ($_GET['from'] == 'cart') {
			$data['ifcart'] = '1';
			$data['ifchain'] = '';
			//购物车信息组装
			$cart_ids = $_GET['PayData'];
			$cart_ids = explode(',',$cart_ids);
			foreach ($cart_ids as $key => $value) {
				$o = Model('cart') -> getCartInfo(array('cart_id'=>$value));
				$cart_id[] = $o['cart_id']."|".$o['goods_num'];

				//重新组合购买商品信息和数量
				$goods = Model('goods') -> getGoodsInfo(array('goods_id'=>$o['goods_id']));
				$goods['goods_image'] = cthumb($goods['goods_image'],60,$goods['store_id']);
				$goods['goods_paynum'] = $o['goods_num'];

				//会员等级价格
				$member_vip = Model('member_seller') -> getMemberSeller(array('buyer_id'=>$Verify['info']['member_id'],'seller_id'=>$goods['store_id']));
				if ($member_vip['vip_id'] > 0) {
					$vip_info = Model('member_vip') -> getMemberVip(array('vip_level_seller_id'=>$goods['store_id'],'id'=>$member_vip['vip_id']));
					$goods_price_vip = unserialize($goods['goods_price_vip']);
					if ($goods_price_vip) {
						$goods['goods_price']= $goods_price_vip[$vip_info['vip_level_name']];
					}
				}
			
				$goods_info[] = $goods;
			}
			$data['cart_id'] = $cart_id;

		}else{
			$data['cart_id'] = array($_GET['goods_id']."|".$_GET['quantity']);
			//重新组合购买商品信息和数量
			$goods = Model('goods') -> getGoodsInfo(array('goods_id'=>$_GET['goods_id']));
			$goods['goods_image'] = cthumb($goods['goods_image'],60,$goods['store_id']);
			$goods['goods_paynum'] = $_GET['quantity'];

			//会员等级价格
			$member_vip = Model('member_seller') -> getMemberSeller(array('buyer_id'=>$Verify['info']['member_id'],'seller_id'=>$goods['store_id']));
			if ($member_vip['vip_id'] > 0) {
				$vip_info = Model('member_vip') -> getMemberVip(array('vip_level_seller_id'=>$goods['store_id'],'id'=>$member_vip['vip_id']));
				$goods_price_vip = unserialize($goods['goods_price_vip']);
				if ($goods_price_vip) {
					$goods['goods_price']= $goods_price_vip[$vip_info['vip_level_name']];
				}
			}


			$goods_info[] = $goods;
		}

		$logic_buy = Logic('buy');
		$result = $logic_buy->buyStep1($data['cart_id'], $data['ifcart'], $Verify['info']['member_id'], '');

		$result['data']['cart_list'] = $goods_info;
		if (!$result['state']) {
			exit(json_encode(array('code'=>'2000','msg'=>'结算失败','data'=>$result)));
		} else {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$result['data'],'from'=>$_GET['from'])));
		}
	}

	/**
	 * [postGoodsCartPayStep2Op 购物车结算] 第二步
	 * @return [type] [description]
	 */
	public function postGoodsCartPayStep2Op(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$logic_buy = logic('buy');
		$from = $_POST['from'];
		$pay_name = $_POST['payName']; //支付方式
		$buy_city_id = $_POST['cityId'];
		$address_id = $_POST['addressId'];
		// $payType = $_POST['payType'];	

		$data = array();
		if ($from == 'cart') {
			$data['ifcart'] = '1';
			//购物车信息组装
			$cart_ids = $_POST['PayData'];
			
			// $cart_ids = explode(',',$cart_ids);
			foreach ($cart_ids as $key => $value) {
				$o = Model('cart') -> getCartInfo(array('cart_id'=>$value));
				$cart_id[] = $o['cart_id']."|".$o['goods_num'];
				$goods_id[] = $o['goods_id']."|".$o['goods_num'];
			}
			$data['cart_id'] = $cart_id;
			$data['goods_id'] = $goods_id;
		}else {
			$data['cart_id'] = array($_POST['goods_id']."|".$_POST['quantity']);
			$data['goods_id'] = $_POST['goods_id']."|".$_POST['quantity'];
		}
		$data['pay_name'] = $pay_name;
		$data['address_id'] = $address_id;
		$data['buy_city_id'] = $buy_city_id;
		$data['vat_hash'] = $logic_buy -> buyEncrypt('deny_vat',$Verify['info']['member_id']);
		$array = array('1'=>'','3'=>'','4'=>'','5'=>'');
		$data['offpay_hash_batch'] = $logic_buy -> buyEncrypt($array,$Verify['info']['member_id']);;
		$data['offpay_hash'] = $logic_buy -> buyEncrypt('deny_offpay',$Verify['info']['member_id']);

		$result = $logic_buy->buyStep2($data, $Verify['info']['member_id'], $Verify['info']['member_name'], $Verify['info']['member_email']);
	
		if (!$result['state']) {
			exit(json_encode(array('code'=>'2000','msg'=>'结算失败')));
		} else {
			if ($pay_name == 'offline') {
				//货到付款结算
				$model_order = Model('order');
				$update['payment_code'] = $pay_name ;
				$update['order_state'] = 20;
				$UpEidt = $model_order -> editOrder($update,array('pay_sn'=>$result['data']['pay_sn']));
				if ($UpEidt) {
					exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$pay_name)));
				}else{
					exit(json_encode(array('code'=>'2000','msg'=>'操作失败')));
				}
			}else{
				$result['data']['userwater'] = $Verify['info']['water_fee'];
				$result['data']['useravailable'] = $Verify['info']['available_predeposit'];
				exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$result['data'])));
			}
		}
	}


	/**
	 * [getGoodsCartPayStep3Op 购物车结算] 第三步
	 * @return [type] [description]
	 */
	public function postGoodsCartPayStep3Op(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$paySn = $_POST['paySn'];    //订单编号
		$payType = $_POST['payType'];	//支付方式
		$paypwd = $_POST['paypwd'];  //用户支付密码

		$logic_buy_1 = Logic('buy_1');
		$model_order = Model('order');
		$listOrd = $model_order -> getOrderList(array('pay_sn'=>$paySn));

		foreach ($listOrd as $key => $ord) {
			if ($ord['order_state'] == '20') {
				exit(json_encode(array('code'=>'2000','msg'=>'该订单已支付过')));
			}
		}
		
		if ($Verify['info']['member_paypwd'] == '' || $Verify['info']['member_paypwd'] != md5($paypwd)) {
			exit(json_encode(array('code'=>'2000','msg'=>'无效的支付密码')));
		}


		if ($payType == 'sbpay') {
			$pay = $logic_buy_1 -> sbPay($listOrd, $post, $Verify['info']);
		}else{
			//余额付款(pd)
			$pay = $logic_buy_1 -> pdPay($listOrd, $post, $Verify['info']);
		}
		
		if ($pay[0]['order_state'] == '20') {
			exit(json_encode(array('code'=>'1000','msg'=>'支付成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'支付失败')));
		}

	}




	/********************************个人中心*******************************************/

	/**
	 * [getUserGoodsOrderListOp 用户已下订单]
	 * @return [type] [description]
	 */
	public function getUserGoodsOrderListOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$find_ord = array();
		$find_ord['buyer_id'] = $Verify['info']['member_id'];
		$find_ord['order_state'] = array('notlike',0);
		$find_ord['store_id'] = array('neq','');
		if ($_GET['state'] > 0) {
			$find_ord['order_state'] = $_GET['state'];
		}

		//分页
		$pindex = max(1, intval($_GET['page']));
		$psize = 5;
		$limit = ($pindex - 1)*$psize .",".$psize;

		$list = Model('order') -> getOrderList($find_ord,'','',' order_id desc ',$limit,array('order_goods','order_common'));

		$list = array_values($list);

		foreach ($list as $key => $value) {
			$img = $value['extend_order_goods'];
			$list[$key]['extend_order_goods'][0]['goods_image'] = cthumb($img[0]['goods_image'],60,$g['store_id']);
		
		}

		if (!empty($list)) {
			echo json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$list));
		}else{
			$empty = array();
			echo json_encode(array('code'=>'1000','msg'=>'无结果','data'=>$empty));
		}
	}


	/**
	 * [getUserGoodsOrderInfoOp 用户已下订单信息]
	 * @return [type] [description]
	 */
	public function getUserGoodsOrderInfoOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$order_id = $_GET['oid'];
		$logic_order = logic('order');
		$result = $logic_order->getMemberOrderInfo($order_id,$Verify['info']['member_id']);

		$result['data']['order_info']['payment_time']= date('Y-m-d H:i:s',$result['data']['order_info']['payment_time']);
		$result['data']['order_info']['finnshed_time']= date('Y-m-d H:i:s',$result['data']['order_info']['finnshed_time']);
		$result['data']['order_info']['add_time']= date('Y-m-d H:i:s',$result['data']['order_info']['add_time']);
		$result['data']['order_info']['extend_order_common']['shipping_time']= date('Y-m-d H:i:s',$result['data']['order_info']['extend_order_common']['shipping_time']);
		$result['data']['order_info']['daddress_info'] = $result['data']['daddress_info'];

		echo json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$result['data']));
	}

	/**
	 * [getUserPolicyListOp 商家给用户的政策列表]
	 * @return [type] [description]
	 */
	public function getUserPolicyListOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$model_member_seller = Model('member_seller');
		$model_privilege = Model('privilege');
		$model_signed = Model('signed');

		$find_list['buyer_id'] = $Verify['info']['member_id'];

		$list = $model_member_seller -> getMemberSellerList($find_list,'*',20);
		foreach ($list as $key => $val) {
			$find_privilege['seller_id'] = $val['seller_id'];
			$find_privilege['privilege_vip_type'] = $val['vip_id'];
			$find_privilege['privilege_status'] = 1;
			$find_privilege['share'] = 1;
			$list[$key]['privilegelist'] = $model_privilege -> getPrivilegeList($find_privilege);

			
			foreach ($list[$key]['privilegelist'] as $ky => $g) {
				$find = $model_signed -> findSigned(array('user_id'=>$Verify['info']['member_id'],'seller_id'=>$g['seller_id'],'pid'=>$g['id']));
				$list[$key]['privilegelist'][$ky]['privilege_val'] = unserialize($list[$key]['privilegelist'][$ky]['privilege_val']);
				if (!empty($find)) {
					$list[$key]['privilegelist'][$ky]['play'] = '1';
				}
			}


		}
		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$list[0]['privilegelist'])));
	}


	/**
	 * [getUserPolicyInfoOp 政策详情]
	 * @return [type] [description]
	 */
	public function getUserPolicyInfoOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$policyID = $_GET['pid'] ;
		$policyInfo = Model('privilege') -> getPrivilegeInfo(array('id'=>$policyID)) ;
		$play = Model('signed') -> findSigned(array('user_id'=>$Verify['info']['member_id'],'pid'=>$policyID,'seller_id'=>$policyInfo['seller_id']));

		//数据格式化
		$policyInfo['privilege_valid_starttime'] = date('Y-m-d H:i:s' , $policyInfo['privilege_valid_starttime']);
		$policyInfo['privilege_valid_endtime'] = date('Y-m-d H:i:s' , $policyInfo['privilege_valid_endtime']);
		$policyInfo['privilege_cerat_time'] = date('Y-m-d H:i:s' , $policyInfo['privilege_cerat_time']);	
		$policyval = unserialize($policyInfo['privilege_val']);

		$datat = array();
		foreach ($policyval as $key => $pal) {
			$datats =array();
			$datats['num'] = $key;
			$datats['per'] = $pal;
			$datat[] = $datats;
		}
		$policyInfo['privilege_arr'] = $datat;

		if (!empty($play)) {
			$policyInfo['play'] = '1';
			$policyInfo['playtime'] = date('Y-m-d H:i:s' , $play['lottime']);
		}
		if (!empty($policyInfo)) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$policyInfo)));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'无效政策')));
		}
	}


	/**
	 * [getUserPolicyGoOp 政策签约]
	 * @return [type] [description]
	 */
	public function getUserPolicyGoOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$seller_id = $_GET['seller_id'];
		$pid = $_GET['pid'];
		$pwd = $_GET['pwd'];
		$now = time();

		$Fcode['smc_mobile'] = $Verify['info']['member_mobile'];
		$Fcode['smc_type'] = '2';
		$code = Model('smc') -> getSmcInfo($Fcode);

		if ($code['smc_munber'] != $pwd) {
			echo json_encode(array('code'=>'2000','msg'=>'验证码错误'));
			exit;
		}

		if ($now - $code['smc_addtime'] > 300) {
			echo json_encode(array('code'=>'2000','msg'=>'验证码过期'));
			exit;
		}

		$model_signed = Model('signed');
		$data['user_id'] = $Verify['info']['member_id'];
		$data['seller_id'] = $seller_id;
		$data['pid'] = $pid;
		$data['lottime'] = time();
		$add = $model_signed -> addSigned($data);

		if ($add) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'操作失败')));
		}
	}


	/**
	 * [getUserSchoolListOp 水来学院]
	 * @return [type] [description]
	 */
	public function getUserSchoolListOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$list = Model('cms_notify') -> listNotify(array('cms_type'=>'1'));
		foreach ($list as $key => $gh) {
			$look =  Model('cms_notify') ->findNotifyLog(array('log_type'=>'1','log_pid'=>$gh['id'],'log_member_id'=>$Verify['info']['member_id']));
			if (!empty($look)) {
				$list[$key]['see'] = '1';
			}
		}



		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$list)));
	}


	/**
	 * [getUserMessageListOp 消息列表]
	 * @return [type] [description]
	 */
	public function getUserMessageListOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$list = Model('cms_notify') -> listNotify(array('cms_type'=>'2'));
		foreach ($list as $key => $gh) {
			$look =  Model('cms_notify') ->findNotifyLog(array('log_type'=>'2','log_pid'=>$gh['id'],'log_member_id'=>$Verify['info']['member_id']));
			if (!empty($look)) {
				$list[$key]['see'] = '1';
			}
		}

		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$list)));
	}	


	/**
	 * [getUserSchoolInfoOp 水来学院信息详情]
	 * @return [type] [description]
	 */
	public function getUserSchoolInfoOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$policyID = $_GET['pid'] ;
		$info = Model('cms_notify') -> findNotify(array('id'=>$policyID));


		$data['log_pid'] = $policyID;
		$data['log_member_id'] = $Verify['info']['member_id'];
		$data['log_type'] = $info['cms_type'];
		$look = Model('cms_notify') -> findNotifyLog($data);

		if (empty($look)) {
			$data['log_add_time'] = time();
			$add = Model('cms_notify') -> addNotifyLog($data);
		}

		if (!empty($info)) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$info)));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'无效政策')));
		}
	}


	/**
	 * [getUserAddressList 我的收货地址列表]
	 * @return [type] [description]
	 */
	public function getUserAddressListOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$find_address = array();
		$find_address['member_id'] = $Verify['info']['member_id'];
		$list = Model('address') -> getAddressList($find_address);
		foreach ($list as $key => $address) {
			$arr1 = array();
			$arr2 = array();
			$addre = explode(' ', $address['area_info']);

			$arr1['id'] = $address['city_id'];
			$arr1['name'] = $addre[1];
			$arr2['id'] = $address['area_id'];
			$arr2['name'] = $addre[2];

			$list[$key]['province'] = $addre[0];
			$list[$key]['cityinfo'] = $arr1;
			$list[$key]['areainfo'] = $arr2;
			
		}
		if (!empty($list)) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$list)));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'无地址')));
		}
	}


	/**
	 * [getUserAddressDefault 设置默认地址]
	 * @return [type] [description]
	 */
	public function getUserAddressDefaultOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$addressId = $_GET['addressId'];
		$common['member_id'] = $Verify['info']['member_id'];
		$updata1['is_default'] = '0';
		$dateUp = Model('address') -> editAddress($updata1,$common);

		$common['address_id'] = $addressId;
		$updata['is_default'] = '1';
		$dateUp = Model('address') -> editAddress($updata,$common);
		if (!empty($dateUp)) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'操作失败')));
		}
	}


	/**
	 * [getUserAddressAdditionOp 新增收货地址]
	 * @return [type] [description]
	 */
	public function postUserAddressAdditionOp(){ 
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$true_name = $_POST['name'];
		$area_id = $_POST['areaId'];
		$city_id = $_POST['cityId'];
		$area_info = $_POST['province']." ".$_POST['city']." ".$_POST['area'];
		$address = $_POST['address'];
		$mob_phone = $_POST['phone'];

		$addDate = array();
		$addDate['member_id'] = $Verify['info']['member_id'];
		$addDate['true_name'] = $true_name;
		$addDate['area_id'] = $area_id;
		$addDate['city_id'] = $city_id;
		$addDate['area_info'] = $area_info;
		$addDate['address'] = $address;
		$addDate['mob_phone'] = $mob_phone;

		$add = Model('address') -> addAddress($addDate);
		if ($add) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'操作失败')));
		}
	}


	/**
	 * [postUserAddressEditOp 编辑收货地址]
	 * @return [type] [description]
	 */
	public function postUserAddressEditOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$true_name = $_POST['name'];
		$area_id = $_POST['areaId'];
		$city_id = $_POST['cityId'];
		$area_info = $_POST['province']." ".$_POST['city']." ".$_POST['area'];
		$address = $_POST['address'];
		$mob_phone = $_POST['phone'];

		$addDate = array();
		$addDate['member_id'] = $Verify['info']['member_id'];
		$addDate['true_name'] = $true_name;
		$addDate['area_id'] = $area_id;
		$addDate['city_id'] = $city_id;
		$addDate['area_info'] = $area_info;
		$addDate['address'] = $address;
		$addDate['mob_phone'] = $mob_phone;

		$common['address_id'] = $_POST['aid'];

		$edit = Model('address') -> editAddress($addDate,$common);
		if ($edit) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'操作失败')));
		}
	}


	/**
	 * [getUserAddressDelOp 删除收货地址]
	 * @return [type] [description]
	 */
	public function getUserAddressDelOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$common['address_id'] = $_GET['aid'];

		$del = Model('address') -> delAddress($common);
		if ($del) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'操作失败')));
		}
	}

	/**
	 * [getUserAddressInfoOp 查找收货地址信息]
	 * @return [type] [description]
	 */
	public function getUserAddressInfoOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$addressId = $_GET['addressId'];
		$addinfo = Model('address') -> getAddressInfo(array('address_id'=>$addressId));

		$address = explode(' ', $addinfo['area_info']);
		$arr1['id'] = $addinfo['area_id'];
		$arr1['name'] = $address[2];
		$arr2['id'] = $addinfo['city_id'];
		$arr2['name'] = $address[1];

		$addinfo['areainfo'] = $arr1;
		$addinfo['cityinfo'] = $arr2;
		$addinfo['province'] = $address[0];

		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$addinfo)));

	}


	/**
	 * [getUserWatercoinActivityListOp 用户水币充值活动]
	 * @return [type] [description]
	 */
	public function getUserWatercoinActivityListOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		if ($Verify['info']['from_company'] > 0) {
			$now = time();
			$condition = " `activityStarttime` < '{$now}'  and  `activityEndtime` > '{$now}'  and  `activityAddid` = '{$Verify['info']['from_company']}' or  `activityAddid` = '1' ";
			$list = Model('rechargecard') -> getRechargeWaterList($condition);

			$info = array();
			foreach ($list as $key => $reg) {
				$activityDenomination = unserialize($reg['activityDenomination']);
				foreach ($activityDenomination as $ky => $ve) {
					$arr = array();
					$arr['criterion'] = $ky;
					$arr['append'] = $ve;
					$arr['character'] = "充".$ky."送".$ve;
					$info[] = $arr;
				}
			}

			exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$info)));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'该用户没有获得活动信息')));
		}
		
	}








	/**
	 * [getUserWatercoinLogListOp 用户水币交易记录]
	 * @return [type] [description]
	 */
	public function getUserWatercoinLogListOp(){

		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$model_predeposit = Model('predeposit');
		$data = array();

		$raglist = array();
		//查询充值记录
		$recharge_log = $model_predeposit -> getPdRechargeSbList(array('pdr_member_id'=>$Verify['info']['member_id'],'pdr_payment_state'=>1),'','*',' pdr_add_time DESC');
		foreach ($recharge_log as $key => $rag) {

			if ($rag['pdr_amount_fj'] > 0) {
				$raglist[$key]['fee'] = '+' .$rag['pdr_amount']. '送' .$rag['pdr_amount_fj'];
			}else{
				$raglist[$key]['fee'] = '+' .$rag['pdr_amount'];
			}
			$raglist[$key]['dece'] = $rag['pdr_payment_name'];
			$raglist[$key]['addtime'] = date('Y-m-d H:i:s',$rag['pdr_add_time']);
			$raglist[$key]['time'] = $rag['pdr_add_time'];
			
		}

		$epglist = array();
		//查询消费记录
		$expend_log = $model_predeposit -> getPdLogList(" `lg_member_id` = '{$Verify['info']['member_id']}' and `lg_type` like '%sb%' ",'','*',' lg_add_time DESC ');
		foreach ($expend_log as $key => $epg) {
			$decr = explode('，',$epg['lg_desc']);

			$epglist[$key]['dece2'] = $decr[1];
			$epglist[$key]['order_sn'] = $decr[2];
			$epglist[$key]['addtime'] = date('Y-m-d H:i:s',$epg['lg_add_time']);
			$epglist[$key]['time'] = $epg['lg_add_time'];
			$fee = $epg['lg_av_amount'];
			if ($fee > 0) {
				$epglist[$key]['fee'] = '+' .$fee;
				$epglist[$key]['dece'] = '退款';
			}else{
				$epglist[$key]['fee'] = $fee;
				$epglist[$key]['dece'] = '购买商品';
			}
			
		}
		$data = array_values(array_merge($raglist,$epglist));


		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$data)));
	}

	/**
	 * [getUserBalancecoinLogListOp 用户余额交易记录]
	 * @return [type] [description]
	 */
	public function getUserBalancecoinLogListOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$epglist = array();
		$model_predeposit = Model('predeposit');

		$expend_log = $model_predeposit -> getPdLogList(" `lg_member_id` = '{$Verify['info']['member_id']}' and `lg_type` NOT LIKE '%sb%' ",'','*',' lg_add_time DESC ');
		foreach ($expend_log as $key => $epg) {
			$decr = explode('，',$epg['lg_desc']);

			
			$epglist[$key]['dece2'] = $decr[1];
			$epglist[$key]['order_sn'] = $decr[2];
			$epglist[$key]['addtime'] = date('Y-m-d H:i:s',$epg['lg_add_time']);
			$epglist[$key]['time'] = $epg['lg_add_time'];
			$fee = $epg['lg_av_amount'];
			if ($fee > 0) {
				$epglist[$key]['fee'] = '+' .$fee;
				$epglist[$key]['dece'] = '退款';
			}else{
				$epglist[$key]['fee'] = $fee;
				$epglist[$key]['dece'] = '购买商品';
			}
		}
		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>array_values($epglist))));
	}

	/**
	 * [getUserWatercoinAddOp 用户充值水币]
	 * @return [type] [description]
	 */
	public function getUserWatercoinAddOp(){ 
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$pdr_amount = abs(floatval($_GET['amount']));
		if ($pdr_amount <= 0) {
			exit(json_encode(array('code'=>'2000','msg'=>'无效金额')));
		}

		if (!empty($_GET['append'])) {
			$now = time();
			$condition = " `activityStarttime` < '{$now}'  and  `activityEndtime` > '{$now}'  and  `activityAddid` = '{$Verify['info']['from_company']}' or  `activityAddid` = '1' ";
			$list = Model('rechargecard') -> getRechargeWaterList($condition);
			foreach ($list as $key => $reg) {
				$activityDenomination = unserialize($reg['activityDenomination']);
				foreach ($activityDenomination as $ky => $ve) {
					$criterion[] = $ky;
					$append[] = $ve;
				}
			}
			$appends = str_replace($criterion,$append,$pdr_amount);

			if (in_array($appends,$append)) {
				$pdr_amount_fj = abs(floatval($appends));
			}else{
				$pdr_amount_fj = 0;
			}
		}else{
			$pdr_amount_fj = 0;
		}

		$model_pdr = Model('predeposit');
		$data = array();
		$data['pdr_sn'] = $pay_sn = $model_pdr->makeSn();
		$data['pdr_member_id'] = $Verify['info']['member_id'];
		$data['pdr_member_name'] = $Verify['info']['member_name'];
		$data['pdr_amount'] = $pdr_amount;
		$data['pdr_amount_fj'] = $pdr_amount_fj;
		$data['pdr_add_time'] = TIMESTAMP;

		$insert = $model_pdr->addPdRechargeSb($data);

		if ($insert) {
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$data)));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'操作失败')));
		}
	}


	/**
	 * [getUserOrderAbrogateOp 用户取消订单]
	 * @return [type] [description]
	 */
	public function getUserOrderAbrogateOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$FindOrd['order_sn'] = $_GET['osn'];
		$FindOrd['buyer_id'] = $Verify['info']['member_id'];

		$OrderInfo = Model('order') -> getOrderInfo($FindOrd);
		$model_order = Model('order');

		$msg = '用户取消';
		if ($OrderInfo['order_type'] != 2 && $OrderInfo['payment_code'] != 'offline') {
			//更新订单信息
			$update_order = array('order_state'=>0);
			$cancel_condition['order_id'] = $OrderInfo['order_id'];

			$update = $model_order->editOrder($update_order,$cancel_condition);
			if (!$update) {
				throw new Exception('保存失败');
			}

			//添加订单日志
			$data = array();
			$data['order_id'] = $OrderInfo['order_id'];
			$data['log_role'] = 'buyer';
			$data['log_msg'] = '取消了订单';
			$data['log_user'] = $Verify['info']['member_name'];
			if ($msg) {
				$data['log_msg'] .= ' ( '.$msg.' )';
			}
			$data['log_orderstate'] = 0;
			$model_order->addOrderLog($data);
			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'操作失败')));
		}
		
	}
	
	/**
	 * [getLoginSnsOp 短信验证]
	 * @return [type] [description]
	 */
	public function getLoginSnsOp(){
		$mobile = $_GET['mobile'];

		$memberinfo = Model('member') -> getMemberInfo(array('member_mobile'=>$mobile));

		if (empty($memberinfo)) {
			exit(json_encode(array('code'=>'3000','msg'=>'无效用户')));
		}

		$code = rand('1000','9999');
		// $sms = "您的验证码是：".$code.", 在5分钟内有效。【水来了】";
		$get = $this -> sendMessage($mobile,$code,'login');
		if ($get) {
			$smc['smc_mobile'] = $mobile;
			$smc['smc_type'] = '1';
			$Find = Model('smc') -> getSmcInfo($smc);
			if (empty($Find)) {
				$smc['smc_munber'] = $code;
				$smc['smc_addtime'] = time();
				Model('smc') -> addSmc($smc);
			}else{
				$smc['smc_munber'] = $code;
				$smc['smc_addtime'] = time();
				Model('smc') -> editSmc($smc,array('smc_mobile'=>$mobile));
			}

			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'发送失败')));
		}
	}

	/**
	 * [getPolicySnsOp 政策短信验证]
	 * @return [type] [description]
	 */
	public function getPolicySnsOp(){
		$mobile = $_GET['mobile']; //用户ID

		$code = rand('1000','9999');
		// $sms = "您的政策签约验证码是：".$code.", 在5分钟内有效。【水来了】";
		$get = $this -> sendMessage($mobile,$code,'policy');
		if ($get) {
			$smc['smc_mobile'] = $mobile;
			$smc['smc_type'] = '2';
			$Find = Model('smc') -> getSmcInfo($smc);
			if (empty($Find)) {
				$smc['smc_munber'] = $code;
				$smc['smc_addtime'] = time();
				Model('smc') -> addSmc($smc);
			}else{
				$smc['smc_munber'] = $code;
				$smc['smc_addtime'] = time();
				Model('smc') -> editSmc($smc,array('smc_mobile'=>$mobile));
			}

			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'发送失败')));
		}
	}



	/**
	 * [VerifyUser 用户验证]
	 * @param [type] $mobile [用户手机号码]
	 * @param [type] $token  [description]
	 */
	public function VerifyUser($mobile,$token){
		$find_user['member_mobile'] = $mobile;
		$userInfo = Model('member') -> getMemberInfo($find_user);
		$now = time();

		$result =array();
		if (empty($userInfo)) {
			$result['state'] = '1';
			$result['gg'] = '无用户信息';
		}else{	
			$FindToken['token'] = $token;
			$FindToken['member_id'] = $userInfo['member_id'];
			$token_info = Model('mb_user_token') -> getMbUserTokenInfo($FindToken);

			if (!empty($token_info)) {
				if ($now - $userInfo['member_login_time'] > 259200) {
					$result['state'] = '1';
					$result['gg'] = 'token过期';
				}else{
					if ($now - $token_info['login_time'] > 259200) {
						$result['state'] = '1';
						$result['gg'] = '登录时间过期';
					}else{
						$result['state'] = '2';
						$result['info'] = $userInfo;
					}
				}
			}else{
				$result['state'] = '1';
				$result['gg'] = '无token信息';
			}
		}
		return $result;
	}


	// public static  function  sendMessage($phone,$code){
	// 	$curlPost ='userid=3112&account=欣佳&password=XINJIAGOU123&mobile='.$phone.'&content='.$code.'&sendTime=&extno=&action=send';
	// 	$ch = curl_init();
	// 	curl_setopt($ch, CURLOPT_URL, 'http://211.147.242.161:8888/sms.aspx');
	// 	curl_setopt($ch, CURLOPT_HEADER, 1);
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 	curl_setopt($ch, CURLOPT_POST, 1);
	// 	curl_setopt($ch, CURLOPT_POSTFIELDS,$curlPost);
	// 	$data =trim(curl_exec($ch));

	// 	curl_close($ch);
	// 	if(preg_match('~(.*?)<message>ok<\/message>(.*?)~',$data)){
	// 		return true;
	// 	}else{
	// 		return false ;
	// 	}
	// }


	/**
	 * [sendMessage description]
	 * @param  [type] $phone [手机号]
	 * @param  [type] $code  [验证码]
	 * @param  [type] $tem   [类型]
	 * @return [type]        [description]
	 */
	public static function sendMessage($phone,$code,$tem){
		$apiAccount = 'ACC6f296be18c084c7d99b9caeb5901c0f5'; //智语平台分配的开发者帐号
		$appId = 'APPf220211a6a6b45548e5d0cc33357a5f3'; //应用Id
		$timeStamp = TIMESTAMP;
		$sign = md5($apiAccount.'APIb6eaebb65ef54fe2a5e9641af35893e0'.$timeStamp);
		
		switch ($tem) {
			case 'login':$templateId = 'mtlN919ppBNB15Q6';break;
			case 'policy':$templateId = 'mtl25Wc9628u5709';break;
			case 'pay':$templateId = 'mtl2d5y61ytmLW85';break;
		}


		$singerId = 'msnUt4Y7102J11V1';
		
		$curlPost = array(
			'apiAccount' => $apiAccount,
			'appId' => $appId,
			'timeStamp' => $timeStamp,
			'sign' => $sign,
			'templateId' => $templateId,
			'singerId' => $singerId,
			'mobile' => $phone,
			'param' => $code,
		);
		$curlPost = json_encode($curlPost);
		$url ="http://www.zypaas.com:9988/V1/Account/".$apiAccount."/sms/sureTempalteSend";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$curlPost);
		$data =trim(curl_exec($ch));
		curl_close($ch);

		return true;
	}


	/**
	 * [getAskToken Token生成]
	 * @param  [type] $mobile [手机号]
	 * @param  [type] $code   [短息号码]
	 * @return [type]         [description]
	 */
	public function getAskToken($mobile,$code){
		$now = time();
		$token = $mobile.$code.$now.'zyb';
		$token = hash('md5',$token);
		
		return $token;
	}

	/**
	 * [getVersionOp 移动端版本查询]
	 * @return [type] [description]
	 */
	public function getVersionAndroidOp(){
		$msg = Model('version') -> getversionInfoA(' 1=1 ');

		exit(json_encode(array('code'=>'1000','msg'=>'信息已获取','data'=>$msg[0])));
	}

	/**
	 * [getVersionOp 移动端版本查询]
	 * @return [type] [description]
	 */
	public function getVersionIosOp(){
		$data = json_decode(file_get_contents("VersionIOS.json"));
		$ios = $data->IOS;
		$shu = strnatcmp($ios,$_GET['IOS']);
		if ( $shu > 0 ) {
			$msg = $data->IOSUrl;
			exit(json_encode(array('code'=>'1000','msg'=>'发现新版本','data'=>$msg)));
		} else {
			exit(json_encode(array('code'=>'2000','msg'=>'您已是最新版本')));
		}
	}


	public function getUserInfoOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}else{
			$pay_sn = $_GET['pay_sn'];
			if (!empty($pay_sn)) {
				$order_info = Model('order') -> getOrderList(array('pay_sn'=>$pay_sn));
				$pay_fee = 0;
				$order_sn = array();
				foreach ($order_info as $key => $ord) {
					$pay_fee += $ord['order_amount'];
					$order_sn[] = $ord['order_sn'];
				}
				$Verify['info']['pay_fee'] = $pay_fee;
				$Verify['info']['order_sn'] = $order_sn;
			}

			$Verify['info']['member_passwd'] = '';
			$Verify['info']['member_paypwd'] = '';
			exit(json_encode(array('code'=>'1000','msg'=>'有效用户','data'=>$Verify['info'])));
		}
	}


	/**
	 * [getUserForbackWaterfeeOp 查询用户已返水币金额]
	 * @return [type] [description]
	 */
	public function getUserForbackWaterfeeOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);

		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}else{
			//查询已返利金额
			$getforblack = " `pdr_member_id` = '{$Verify['info']['member_id']}' and `pdr_payment_code` like '%rebate_%' and `pdr_payment_state` = '1' ";
			$sb_recharge = Model('predeposit') -> getPdRechargeSbList($getforblack);
			$forback = 0;
			foreach ($sb_recharge as $key => $sre) {
				$forback += $sre['pdr_amount'];
			}
			$forbackWaterfee = (string)$forback;
			exit(json_encode(array('code'=>'1000','msg'=>'有效用户','data'=>$forbackWaterfee)));
		}
	}

	/**
	 * [getUserForbackWaterfeeListOp 查询用户已返水币金额明细]
	 * @return [type] [description]
	 */
	public function getUserForbackWaterfeeListOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$ste = !empty($_GET['ste']) ? $_GET['ste'] : '0';
		$ste =  str_replace(array('0','1','2','3','4'),array('rebate_','rebate_one','rebate_moon','rebate_quarter','rebate_year'),$ste);
		
		$getforblack = " `pdr_member_id` = '{$Verify['info']['member_id']}' and `pdr_payment_code` like '%{$ste}%' and `pdr_payment_state` = '1' ";
		$sb_recharge = Model('predeposit') -> getPdRechargeSbList($getforblack,'','*',' pdr_add_time DESC ');
		foreach ($sb_recharge as $key => $sre) {
			$sb_recharge[$key]['pdr_payment_code'] = str_replace(array('rebate_one','rebate_moon','rebate_quarter','rebate_year'),array('每笔','每月','每季度','每年'),$sre['pdr_payment_code']);

			$sb_recharge[$key]['code_info'] = unserialize($sre['code_info']);
			$sb_recharge[$key]['pdr_add_time'] = date('Y-m-d',$sre['pdr_add_time']);
		}
		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$sb_recharge)));
	}


	/**
	 * [getUserMessgCountOp 一些消息提示]
	 * @return [type] [description]
	 */
	public function getUserMessgCountOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}
		$model_cms = Model('cms_notify');

		//水来了学院
		$snum = 0;
		$liste = $model_cms -> listNotify(array('cms_type'=>'1'));
		foreach ($liste as $key => $gh) {
			$looke =  Model('cms_notify') ->findNotifyLog(array('log_type'=>'1','log_pid'=>$gh['id'],'log_member_id'=>$Verify['info']['member_id']));
			if (empty($looke)) {
				$snum ++;
			}
		}
		unset($liste);
		$mnum = 0;
		$lists = $this -> findselect1();

		foreach ($lists as $key => $ghs) {
			$looks =  Model('cms_notify') ->findNotifyLog(array('log_type'=>'2','log_pid'=>$ghs['id'],'log_member_id'=>$Verify['info']['member_id']));
			if (empty($looks)) {
				$mnum ++;
			}
		}

		$model_member_seller = Model('member_seller');
		$model_privilege = Model('privilege');
		$model_signed = Model('signed');

		$pnum = 0;
		$find_list['buyer_id'] = $Verify['info']['member_id'];
		$list = $model_member_seller -> getMemberSellerList($find_list,'*',20);
		foreach ($list as $key => $val) {
			$find_privilege['seller_id'] = $val['seller_id'];
			$find_privilege['privilege_vip_type'] = $val['vip_id'];
			$find_privilege['privilege_status'] = 1;
			$find_privilege['share'] = 1;
			$list[$key]['privilegelist'] = $model_privilege -> getPrivilegeList($find_privilege);
			foreach ($list[$key]['privilegelist'] as $ky => $g) {
				$find = $model_signed -> findSigned(array('user_id'=>$Verify['info']['member_id'],'seller_id'=>$g['seller_id'],'pid'=>$g['id']));
				$list[$key]['privilegelist'][$ky]['privilege_val'] = unserialize($list[$key]['privilegelist'][$ky]['privilege_val']);
				if (empty($find)) {
					$pnum ++;
				}
			}
		}


		$count['School'] = $snum;
		$count['message'] = $mnum;
		$count['privilege'] = $pnum;

		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$count)));
	}


	public function findselect1(){
		$model_cms = Model('cms_notifys');
		$liste = $model_cms -> listNotify(array('cms_type'=>'2'));
		return $liste;
	}


	/**
	 * [getAreaList 地区列表]
	 * @return [type] [description]
	 */
	public function getAreaSListOp(){
		$pro = !empty($_GET['pro']) ? $_GET['pro'] : '0';		//省
		//查询省
		$provincelist = Model('area') -> getAreaList(array('area_parent_id'=>$pro));
		foreach ($provincelist as $key => $pvt) {
			$province = array();
			$province['id'] = $pvt['area_id'];
			$province['name'] = $pvt['area_name'];
			$provinceinfo[] = $province;
		}
		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$provinceinfo)));
	}

	/**
	 * [getAreaList 地区列表]
	 * @return [type] [description]
	 */
	public function getAreaCListOp(){
		$pro = $_GET['pro'];		
		//查询市
		$provincelist = Model('area') -> getAreaList(array('area_parent_id'=>$pro));
		foreach ($provincelist as $key => $pvt) {
			$province = array();
			$province['id'] = $pvt['area_id'];
			$province['name'] = $pvt['area_name'];
			$provinceinfo[] = $province;
		}
		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$provinceinfo)));
	}

	/**
	 * [getAreaList 地区列表]
	 * @return [type] [description]
	 */
	public function getAreaAListOp(){
		$city = $_GET['city'];		
		//查询区
		$provincelist = Model('area') -> getAreaList(array('area_parent_id'=>$city));
		foreach ($provincelist as $key => $pvt) {
			$province = array();
			$province['id'] = $pvt['area_id'];
			$province['name'] = $pvt['area_name'];
			$provinceinfo[] = $province;
		}
		exit(json_encode(array('code'=>'1000','msg'=>'操作成功','data'=>$provinceinfo)));
	}


	/**
	 * [getUserOrderReceiveOp 用户确定收货]
	 * @return [type] [description]
	 */
	public function getUserOrderReceiveOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$model_order = Model('order');
		$osn = $_GET['osn']; //订单编号
		$order_info = $model_order -> getOrderInfo(array('order_sn'=>$osn));

		if ($Verify['info']['member_id'] != $order_info['buyer_id']) {
			exit(json_encode(array('code'=>'2000','msg'=>'无权操作')));
		}

		/*用户确认订单后划水币给商家*/
		if ($order_info['payment_code'] == 'sbpay') {
			$order_amount = floatval($order_info['order_amount']);
			// $order_amount = floatval(ncPriceFormat($order_amount));
			//给店铺加水币
			$data_sb['water_fee'] = array('exp','water_fee+'.$order_amount);
			Model('store')->editStore($data_sb,array('store_id'=>$order_info['store_id']));
			//记录订单日志(商户)
			$log_sb_add = array();
			$log_sb_add['log_store_name'] = $order_info['store_name'];
			$log_sb_add['log_store_id'] = $order_info['store_id'];
			$log_sb_add['log_type'] = 'sb_pay';
			$log_sb_add['log_fee'] = $order_info['order_amount'];
			$log_sb_add['log_add_time'] = time();
			$log_sb_add['log_desc'] = "确认水币购买，订单号: ".$order_info['order_sn'];
			Model('store') -> addStoreSbLog($log_sb_add);
		}

		/*在线支付记录*/
		if ($order_info['payment_code'] == 'appwxpay') {
			$order_amount = floatval($order_info['order_amount']);
			$data_money['money_fee'] = array('exp','money_fee+'.$order_amount);
			Model('store')->editStore($data_money,array('store_id'=>$order_info['store_id']));
			//记录订单日志(商户)
			$log_money_add = array();
			$log_money_add['log_store_name'] = $order_info['store_name'];
			$log_money_add['log_store_id'] = $order_info['store_id'];
			$log_money_add['log_type'] = 'online_'.$order_info['payment_code'];
			$log_money_add['log_fee'] = $order_info['order_amount'];
			$log_money_add['log_add_time'] = time();
			$log_money_add['log_desc'] = "确认在线支付，订单号: ".$order_info['order_sn'];
			Model('store') -> addStoreMoneyLog($log_money_add);
		}


		
		$model_store = Model('store');
		$model_member_seller = Model('member_seller');
		/*
		** 执行购买者与买卖者的从属关系操作
		** By 聖蓝玫瑰 2017年4月14日 09:56:54
		 */
		$buyer_id = $order_info['buyer_id'];
		$store_id = $order_info['store_id'];

		//查询购买者是否已成为店铺的消费者
		$StoreInfo = $model_store -> getStoreInfoByID($store_id);

		if (empty($StoreInfo['store_client_gather'])) {
			/*写入第一个购买者ID*/
			$update = array('store_client_gather'=>$buyer_id);
			$condition = array('store_id'=>$store_id);
			$EditStore = $model_store -> editStore($update,$condition);
		}else{
			$arr_gathe = explode(',',$StoreInfo['store_client_gather']);
			if (!in_array($buyer_id,$arr_gathe)) {
				/*写入其它购买者ID*/
				$gathe = $StoreInfo['store_client_gather'].",".$buyer_id;
				/*更新合集*/
				$update = array('store_client_gather'=>$gathe);
				$condition = array('store_id'=>$store_id);
				$EditStore = $model_store -> editStore($update,$condition);
			}
		}

		//插入店铺会员表
		$data_seller['buyer_id'] = $buyer_id;
		$data_seller['seller_id'] = $store_id;

		$find_seller = $model_member_seller -> getMemberSeller($data_seller);
		if (empty($find_seller)) {
			$data_seller['vip_id'] = 0;
			$model_member_seller -> addMemberSeller($data_seller);
		}
		/**
		 *  结束
		 */
		


		/**
		 **返利信息写入开始
		 */
		$model_rebate = Model('rebate');
		$model_privilege = Model('privilege');
		$model_signed = Model('signed');

		//查询是否同意政策
		$putOn = $model_signed -> getSignedList(array('user_id'=>$buyer_id,'seller_id'=>$store_id));
		foreach ($putOn as $key => $p) {
			$pid[] = $p['pid'];
		}

		$order_goods_info = $model_order -> getOrderGoodsInfo(array('order_id'=>$order_info['order_id']));
		$order_seller_buyer_info = $model_order -> getOrderBuyerInfo($buyer_id,$store_id);
		
		//查询分红分类
		$Privilege_find = $model_privilege -> getPrivilegeInfo(array('seller_id'=>$order_info['store_id'],'goods_id'=>$order_goods_info['goods_id']));

		if (in_array($Privilege_find['id'],$pid)) {
			$rebate_arr['pay_num'] = $order_goods_info['goods_num'];
			$rebate_arr['pay_goods_name'] = $order_goods_info['goods_name'];
			$rebate_arr['pay_goods_id'] = $order_goods_info['goods_id'];
			$rebate_arr['order_sn'] = $order_info['order_sn'];
			$rebate_arr['store_id'] = $order_info['store_id'];
			$rebate_arr['store_name'] = $order_info['store_name'];
			$rebate_arr['buyer_id'] = $order_info['buyer_id'];
			$rebate_arr['buyer_name'] = $order_info['buyer_name'];
			$rebate_arr['add_time'] = $order_info['add_time'];
			$rebate_arr['pay_name'] = $order_info['payment_name'];
			$rebate_arr['pay_fee'] = $order_info['store_name'];
			$rebate_arr['pay_fee'] = $order_info['order_amount'];
			$rebate_arr['status'] = 1;
			$rebate_arr['creattime'] = time();

			$rebate_arr['buyer_vip'] = $order_seller_buyer_info['id'];
			$rebate_arr['buyer_vip_name'] = $order_seller_buyer_info['vip_level_name'];

			if ($Privilege_find['privilege_valid_starttime'] < $order_info['add_time'] && $order_info['add_time']< $Privilege_find['privilege_valid_endtime'] ) {
				$rebate_arr['privilege_status'] = 20;
				$rebate_arr['privilege_type'] = $Privilege_find['privilege_type'];
				$rebate_arr['privilege_name'] = $Privilege_find['privilege_name'];
				$rebate_arr['privilege_time_type'] = $Privilege_find['privilege_time_type'];
				$rebate_arr['privilege_time_name'] = $Privilege_find['privilege_time_name'];
				$rebate_arr['privilege_val'] = $Privilege_find['privilege_val'];
			}else{
				$rebate_arr['privilege_status'] = 10;
			}

			$addRebate = $model_rebate -> addRebate($rebate_arr);
		}
		/**
		 * 返利信息写入结束
		 */

		$logic_order = Logic('order');
		$if_allow = $model_order->getOrderOperateState('receive',$order_info);
		if (!$if_allow) {
			exit(json_encode(array('code'=>'2000','msg'=>'无权操作')));
		}else{
			$run = $logic_order->changeOrderStateReceive($order_info,'buyer',$_SESSION['member_name'],'签收了货物');
			if ($run['state']) {
				exit(json_encode(array('code'=>'1000','msg'=>'货物签收完成')));
			}else{
				exit(json_encode(array('code'=>'2000','msg'=>'签收失败')));
			}
		}
	}


	/**
	 * [getPayPwdSnsOp 支付密码短信]
	 * @return [type] [description]
	 */
	public function getPayPwdSnsOp(){
		$mobile = $_GET['mobile']; //用户ID

		$code = rand('1000','9999');
		// $sms = "您现在操作支付密码设置，验证码是：".$code.", 在5分钟内有效。【水来了】";
		$get = $this -> sendMessage($mobile,$code,'pay');
		if ($get) {
			$smc['smc_mobile'] = $mobile;
			$smc['smc_type'] = '3';
			$Find = Model('smc') -> getSmcInfo($smc);
			if (empty($Find)) {
				$smc['smc_munber'] = $code;
				$smc['smc_addtime'] = time();
				Model('smc') -> addSmc($smc);
			}else{
				$smc['smc_munber'] = $code;
				$smc['smc_addtime'] = time();
				Model('smc') -> editSmc($smc,array('smc_mobile'=>$mobile));
			}

			exit(json_encode(array('code'=>'1000','msg'=>'操作成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'发送失败')));
		}
	}

	/**
	 * [getPayPwdSnsProof 支付密码短信验证]
	 * @return [type] [description]
	 */
	public function getPayPwdSnsProofOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$getcode = $_GET['code'];
		$Fcode['smc_mobile'] = $mobile;
		$Fcode['smc_type'] = '3';
		$code = Model('smc') -> getSmcInfo($Fcode);

		if ($code['smc_munber'] != $getcode) {
			exit(json_encode(array('code'=>'2000','msg'=>'验证码错误')));
		}

		if ($now - $code['smc_addtime'] > 300) {
			exit(json_encode(array('code'=>'2000','msg'=>'验证码过期')));
		}

		exit(json_encode(array('code'=>'1000','msg'=>'验证码有效')));
	}


	/**
	 * [postUserPayPwd 设置用户支付密码]
	 * @return [type] [description]
	 */
	public function postUserPayPwdOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		$newpass = md5($_POST['pwd']);

		$cfind['member_id'] = $Verify['info']['member_id'];
		$cfind['member_mobile'] = $mobile;

		$udata['member_paypwd'] = $newpass;

		$up = Model('member') -> editMember($cfind,$udata);
		if ($up) {
			exit(json_encode(array('code'=>'1000','msg'=>'修改成功')));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'修改失败')));
		}
	}


	/**
	 * [getUserBTCloginOp BTC登录验证]
	 * @return [type] [description]
	 */
	public function getUserBTCloginOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}
		exit(json_encode(array('code'=>'1000','msg'=>'通过','data'=>$Verify['info'])));
	}


	public function postUserPaypwdyzOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		$paypwd = $_POST['paypwd'];

		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'3000','msg'=>'无效用户'));
			exit;
		}

		if ($Verify['info']['member_paypwd'] == '' || $Verify['info']['member_paypwd'] != md5($paypwd)) {
			exit(json_encode(array('code'=>'2000','msg'=>'无效的支付密码')));
		}

		exit(json_encode(array('code'=>'1000','msg'=>'有效的支付密码')));
	}

}
