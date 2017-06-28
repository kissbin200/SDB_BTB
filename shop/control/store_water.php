<?php 

defined('In33hao') or exit('Access Invalid!');

class store_waterControl extends BaseSellerControl {

	public function __construct() {
		parent::__construct();
	}


	public function indexOp(){
		if (!empty($_GET['type'])) {
			$where['log_type'] = $_GET['type'];
		}

		$where['log_store_id'] = $_SESSION['store_id'];
		$list = Model('store_sb_log') -> getSbLogList($where,'*',20);
		foreach ($list as $key => $g) {
			$list[$key]['log_type'] = $this -> orderPaymentName($g['log_type']);
		}

		//一些统计
		$count_list = $this -> show_water_count();


		Tpl::output('count_list',$count_list);
		Tpl::output('return_list',$list);
        		Tpl::output('show_page',Model('store_sb_log')->showpage());
		self::profile_menu('water','index');
		Tpl::showpage('store_water.index');
	}


	/**
	 * [show_water_countOp ]
	 * @return [type] [description]
	 */
	public function show_water_count(){
		$list = Model('store_sb_log') -> getSbLogList(array('log_store_id'=>$_SESSION['store_id']));

		$count_list = array();
		foreach ($list as $key => $row) {
			$count_list['sb_amount'] += $row['log_fee']; //总水币数量

			//充值赠送扣除水币
			if ($row['log_type'] == 'sb_activity') {
				$count_list['activity'] += $row['log_fee'];
			}

			//政策返利扣除水币
			if ($row['log_type'] == 'sb_rebate') {
				$count_list['rebate'] += $row['log_fee'];
			}

			//欠平台数量
			if ($row['log_type'] == 'sb_top') {
				$count_list['top'] += $row['log_fee'];
			}

			//本月收入
			$first_day_unixtime = strtotime(date('Y-m-01 00:00:00', time()));
			$last_day_unixtime = strtotime(date('Y-m-01 23:59:59', time())." +1 month -1 day");
			if ($first_day_unixtime < $row['log_add_time']  &&  $last_day_unixtime > $row['log_add_time']) {
				$count_list['moon'] += $row['log_fee'];
			}

			//今日收入
			$firsts_day_unixtime = strtotime(date('Y-m-d 00:00:00', time()));
			$lasts_day_unixtime = strtotime(date('Y-m-d 23:59:59', time()));
			if ($firsts_day_unixtime < $row['log_add_time']  &&  $lasts_day_unixtime > $row['log_add_time']) {
				$count_list['day'] += $row['log_fee'];
			}

			//已提现
			if ($row['log_type'] == 'sb_cash') {
				$count_list['cash'] += $row['log_fee'];
			}
		}

		return $count_list;
	}


	/**
	 * [cashOp 提现申请]
	 * @return [type] [description]
	 */
	public function cashOp(){
		$list = Model('store_sb_cash_log') -> getSbCashLogList(array('store_id'=>$_SESSION['store_id']));
		foreach ($list as $key => $g) {
			$list[$key]['status'] = $this -> orderStatusName($g['status']);
		}

		$cash_pay = Model('store_sb_cash_log') -> getSbCashLog(array('store_id'=>$_SESSION['store_id'],'status'=>1));
		if (empty($cash_pay)) {
			$go_pay = '1';
		}else{
			$go_pay = '2';
		}

		Tpl::output('go_pay',$go_pay);
		Tpl::output('list',$list);
		Tpl::output('show_page',Model('store_sb_cash_log')->showpage());
		self::profile_menu('water','index_broken');
		Tpl::showpage('store_water.cash');
	}


	public function cash_payOp(){
		$count_list = $this -> show_water_count();
		$agree_fee = $count_list['sb_amount'] - $count_list['top'];

		if ($_POST['form_submit'] == 'ok') {
			//验证
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
				array("input"=>$_POST["cash_fee"],"require"=>"true","validator"=>"Number","message"=>"请输入正确金额"),
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				showValidateError($error);
			}

			if ($_POST['agree_fee'] <  $_POST['cash_fee']) {
				showDialog('古人云：做人要厚道！不可太贪。','','error');
			}

			$data = array();
			$data['ordns'] = $pay_sn = Model('store_sb_cash_log')->makeSn();
			$data['store_id'] = $_SESSION['store_id'];
			$data['cash_fee'] = $_POST['cash_fee'];
			$data['add_time'] = TIMESTAMP;

			$insert = Model('store_sb_cash_log')->addSbCashLog($data);
			if (!$insert){
				showDialog('数据有误','','error');
			}
			showDialog('申请成功，请耐心等待！','reload','succ');
		}


		Tpl::output('agree_fee',$agree_fee);
		Tpl::showpage('store_water.cash_pay','null_layout');
	}

	public function cash_lookOp(){
		$ordns = $_GET['ns'];

		$info = Model('store_sb_cash_log')->getSbCashLog(array('ordns'=>$ordns));

		Tpl::output('info',$info);
		Tpl::showpage('store_water.cash_look','null_layout');
	}

	/**
	 * 取得订单支付类型文字输出形式
	 *
	 * @param array $payment_code
	 * @return string
	 */
	function orderPaymentName($payment_code) {
		return str_replace(array('sb_pay','sb_back','sb_rebate','sb_top','sb_activity','sb_cash'),array('水币支付(客户)','水币退款(客户)','水币返利(商家)','水币充值(平台)','水币充值活动(客户)','水币提现(平台)'),$payment_code);
	}


	function orderStatusName($payment_code) {
		return str_replace(array('1','2','3'),array('申请中','已通过','未通过'),$payment_code);
	}

	/**
	 * 小导航
	 *
	 * @param string    $menu_type  导航类型
	 * @param string    $menu_key   当前导航的menu_key
	 * @return
	 */
	private function profile_menu($menu_type,$menu_key='') {
		$menu_array = array();
		switch ($menu_type) {
			case 'water':
				$menu_array = array(
					array('menu_key'=>'index_broken','menu_name'=>'水币提现',  'menu_url'=>'index.php?act=store_water&op=cash'),
					array('menu_key'=>'index','menu_name'=>'水币流水记录',  'menu_url'=>'index.php?act=store_water&op=index'),
				);
				break;
		}
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
	}
}