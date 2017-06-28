<?php
/**
 * 我的钱包
 */


defined('In33hao') or exit('Access Invalid!');
class store_walletControl extends BaseSellerControl {
	public function __construct() {
		parent::__construct();
	}


	public function indexOp(){
		$StoreInfo = Model('store') -> where(array('store_id'=>$_SESSION['store_id'])) -> find();

		/* 统计 */
		$count_sb = $this -> show_water_count(); //水币
		$count_sb['agree_fee'] = $count_sb['sb_amount'] - $count_sb['top'];
		$cash_sb = Model('store_sb_cash_log') -> where(array('status'=>1,'store_id'=>$_SESSION['store_id'])) -> select();
		foreach ($cash_sb as $ky => $cmy) {
			$count_sb['cash_on'] += $cmy['cash_fee'];
		}


		$count_money = $this -> show_money_count(); //余额

		$count_money['agree_fee'] = $count_money['sb_amount'];

		$cash_money = Model('store_money_cash_log') -> where(array('status'=>1,'store_id'=>$_SESSION['store_id'])) -> select();
		foreach ($cash_money as $ky => $cmy) {
			$count_money['cash_on'] += $cmy['cash_fee'];
		}

		Tpl::output('count_money',$count_money);
		Tpl::output('count_sb',$count_sb);
		Tpl::output('StoreInfo',$StoreInfo);
		Tpl::showPage('store_wallet.store_index');
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
	 * [show_money_count 统计]
	 * @return [type] [description]
	 */
	public function show_money_count(){
		$list = Model('store_money_log') -> getMoneyLogList(array('log_store_id'=>$_SESSION['store_id']));
		foreach ($list as $key => $row) {
			$count_list['sb_amount'] += $row['log_fee']; //总余额数量

			//来自微信支付
			if ($row['log_type'] == 'online_appwxpay') {
				$count_list['appwxpay'] += $row['log_fee']; 
			}

			//本月收入
			$first_day_unixtime = strtotime(date('Y-m-01 00:00:00', time()));
			$last_day_unixtime = strtotime(date('Y-m-01 23:59:59', time())." +1 month -1 day");
			if ($first_day_unixtime < $row['log_add_time']  &&  $last_day_unixtime > $row['log_add_time']) {
				$count_list['moon'] += $row['log_fee'];
			}

			//上月收入
			$firstd_day_unixtime = strtotime(date('Y-m-01 00:00:00', time())." - 1 month");
			$lastd_day_unixtime = strtotime(date('Y-m-01 00:00:00', time())) - 1;
			if ($firstd_day_unixtime < $row['log_add_time']  &&  $lastd_day_unixtime > $row['log_add_time']) {
				$count_list['smoon'] += $row['log_fee'];
			}

			//今日收入
			$firsts_day_unixtime = strtotime(date('Y-m-d 00:00:00', time()));
			$lasts_day_unixtime = strtotime(date('Y-m-d 23:59:59', time()));
			if ($firsts_day_unixtime < $row['log_add_time']  &&  $lasts_day_unixtime > $row['log_add_time']) {
				$count_list['day'] += $row['log_fee'];
			}

			//已提现
			if ($row['log_type'] == 'money_cash') {
				$count_list['cash'] += $row['log_fee'];
			}

		}

		return $count_list;
	}
}