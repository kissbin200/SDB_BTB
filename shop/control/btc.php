<?php
/**
 * BTC访问接口
 */


defined('In33hao') or exit('Access Invalid!');
header("Access-Control-Allow-Origin:*");

class btcControl extends BaseGoodsControl {
	public function __construct() {
		parent::__construct ();
	}


	//店东创建
	public function BTCaddMemberOp(){
		$insert_array = array();
		$insert_array['member_name']    = trim($_POST['store_account']);
		$insert_array['member_mobile']   = trim($_POST['store_account']);
		$insert_array['member_passwd']  = '123456';
		$insert_array['member_email']   = trim($_POST['store_account'])."@qq.com";
		$insert_array['member_truename']= trim($_POST['store_user']);
		$insert_array['member_sex']     = '0';
		$insert_array['member_qq']      = trim($_POST['member_qq']);
		$insert_array['member_ww']      = trim($_POST['member_ww']);
		$insert_array['member_paypwd']      = '123456';
		$insert_array['user_host']      = "http://".trim($_POST['member_user_host']);
		$insert_array['btc_id'] = trim($_POST['btc_id']);

		/*增加字段*/
		$insert_array['member_store_address'] = trim($_POST['store_address']); //水店地址
		$insert_array['bank_name'] = trim($_POST['bank_name']); //开户行
		$insert_array['bank_account'] = trim($_POST['bank_account']); //开户人
		$insert_array['bank_card_number'] = trim($_POST['bank_card_number']); //卡号
		$insert_array['longitude'] = trim($_POST['longitude']); //经度坐标
		$insert_array['latitude'] = trim($_POST['latitude']); //纬度坐标
		$insert_array['member_store_name']= trim($_POST['store_name']); //水店名称

		//查询子公司
		$back_company = Model('admin') -> infoAdmin(array('admin_name'=>$_POST['member_btcstore_name']));
		$insert_array['from_company']    = $back_company['admin_id'];

		//默认允许举报商品
		$insert_array['inform_allow']   = '1';
		if (!empty($_POST['member_avatar'])){
			$insert_array['member_avatar'] = trim($_POST['member_avatar']);
		}
		
		$empty_info = Model('member') -> getMemberInfo(array('member_mobile'=>$_POST['store_account']));
		if (empty($empty_info)) {
			$result = Model('member')->addMember($insert_array);
			if ($result) {
				exit(json_encode(array( 'code'=>'1000','msg'=>'水店会员添加成功！' )));
			}else{
				exit(json_encode(array( 'code'=>'2000','msg'=>'水店会员添加失败！' )));
			}
		}else{
			exit(json_encode(array( 'code'=>'2000','msg'=>'水店会员已存在！' )));
		}
	}


	public function BTCaddWaterPlayerOp(){
		$insert_array = array();
		$insert_array['player_id'] = trim($_POST['employee_id']);
		$insert_array['employee_name']    = trim($_POST['employee_name']);
		$insert_array['phone']   = trim($_POST['phone']);
		$insert_array['employee_imageUrl']  = trim($_POST['employee_imageUrl']);
		$insert_array['employee_type']   = trim($_POST['employee_type']);
		$insert_array['employee_account']= trim($_POST['employee_account']);
		$insert_array['stated']= trim($_POST['stated']);
		$insert_array['btc_id'] = trim($_POST['btc_id']);
		$insert_array['add_time'] = TIMESTAMP;

		$empty_info = Model('member') -> getWaterPlayer(array('phone'=>$_POST['phone']));
		if (empty($empty_info)) {
			$result = Model('member')->addWaterPlayer($insert_array);
			if ($result) {
				exit(json_encode(array( 'code'=>'1000','msg'=>'水店送水工添加成功！' )));
			}else{
				exit(json_encode(array( 'code'=>'2000','msg'=>'水店送水工添加失败！' )));
			}
		}else{
			exit(json_encode(array( 'code'=>'2000','msg'=>'水店送水工已存在！' )));
		}
	}

	public function BTCdelWaterPlayerOp(){
		$del['player_id'] = trim($_POST['employee_id']);
		$result = Model('member')->delWaterPlayer($del);
		if ($result) {
			exit(json_encode(array( 'code'=>'1000','msg'=>'水店送水工删除成功！' )));
		}else{
			exit(json_encode(array( 'code'=>'2000','msg'=>'水店送水工删除失败！' )));
		}
	}

	public function BTCeditWaterPlayerOp(){
		$edit['player_id'] = trim($_POST['employee_id']); //条件
		//修改数据
		$updata['employee_name']    = trim($_POST['employee_name']);
		$updata['phone']   = trim($_POST['phone']);
		$updata['employee_imageUrl']  = trim($_POST['employee_imageUrl']);
		$updata['employee_type']   = trim($_POST['employee_type']);
		$updata['employee_account']= trim($_POST['employee_account']);
		$updata['stated']= trim($_POST['stated']);

		$result = Model('member')->editWaterPlayer($edit,$updata);
		if ($result) {
			exit(json_encode(array( 'code'=>'1000','msg'=>'水店送水工修改成功！' )));
		}else{
			exit(json_encode(array( 'code'=>'2000','msg'=>'水店送水工修改失败！' )));
		}
	}

}