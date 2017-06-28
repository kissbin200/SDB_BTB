<?php 
/*
 *水币提现
 */

defined('In33hao') or exit('Access Invalid!');
class money_cashControl extends SystemControl{

	public function __construct(){
		parent::__construct();
	}

	public function indexOp(){
	
		Tpl::setDirquna('shop');
		Tpl::showpage('money_cash.index');
	}

	public function get_sb_cash_xmlOp(){
		$list = Model('store_money_cash_log')->getMoneyCashLogList(' 1=1 ');

		$data = array();
		$data['now_page'] = Model('store_money_cash_log')->shownowpage();
		$data['total_num'] = Model('store_money_cash_log')->gettotalnum();

		foreach ($list as $key => $row) {
			$i = array();
			$i['operation'] = <<<EOB
<a class="btn green" href="index.php?act=money_cash&op=edgt_cash&id={$row['id']}"><i class="fa fa-edit"></i>编辑</a>

EOB;
			$store = Model('store') -> getStoreInfoByID($row['store_id']);
			$i['title'] = $store['store_name'];
			$i['batchflag'] = $row['cash_fee'];
			$i['denomination'] = $this -> orderStatusName($row['status']);//状态
			$i['playtime'] = date('Y-m-d',$row['add_time']);
			$i['admin_name'] = $row['approval_name'];
			$i['tscreated'] = date('Y-m-d',$row['adopt_time']);
			$i['gettalk'] = $row['remark'];

			$data['list'][$row['id']] = $i;
		}
		echo Tpl::flexigridXML($data);
	}


	public function edgt_cashOp(){
		$log_id = $_GET['id'];

		$log_info = Model('store_money_cash_log') -> getMoneyCashLog(array('id'=>$log_id));
		$store = Model('store') -> getStoreInfoByID($log_info['store_id']);

		if ($_POST['form_submit'] == 'ok') {
			$back_company = $this->getAdminInfo();
			
			$updata = array();
			$updata['status'] = $_POST['item_state'];
			$updata['remark'] = $_POST['show_txt'];
			$updata['approval_name'] = $back_company['name']."-".$back_company['id'];
			$updata['adopt_time'] = time();
			Model('store_money_cash_log') -> updateMoneyCashLog(array('id'=>$_POST['orderid']),$updata);

			//扣除水厂水币并写入记录表
			if ($_POST['item_state'] == '2') {
				$log_info = Model('store_money_cash_log') -> getMoneyCashLog(array('id'=>$_POST['orderid']));
				$store = Model('store') -> getStoreInfoByID($log_info['store_id']);

				$data_sb['money_fee'] = array('exp','money_fee-'.$log_info['cash_fee']);	
				Model('store') -> editStore($data_sb,array('store_id'=>$log_info['store_id']));

				$data_log = array();
				$data_log['log_store_name'] = $store['store_name'];
				$data_log['log_store_id'] = $log_info['store_id'];
				$data_log['log_type'] = 'money_cash';
				$data_log['log_fee'] = '-'.$log_info['cash_fee'];
				$data_log['log_add_time'] = time();
				$data_log['log_desc'] = "确认余额提现，订单号：".$log_info['ordns'];
				Model('store') -> addStoreMoneyLog($data_log);
			}

			showMessage('操作成功','index.php?act=money_cash');
		}

		Tpl::output('store',$store);
		Tpl::output('log_info',$log_info);
		Tpl::setDirquna('shop');
		Tpl::showpage('money_cash.edgt');
	}

	function orderStatusName($payment_code) {
		return str_replace(array('1','2','3'),array('申请中','已通过','未通过'),$payment_code);
	}
}