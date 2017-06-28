<?php
/**
 * 实物订单结算
 * * @好商城 (c) 2015-2018 33HAO Inc. (http://www.33hao.com)
 * @license    http://www.33 hao.c om
 * @link       交流群号：138182377
 * @since      好商城提供技术支持 授权请购买shopnc授权
 */



defined('In33hao') or exit('Access Invalid!');
class store_billControl extends BaseSellerControl {
	/**
	 * 每次导出多少条记录
	 * @var unknown
	 */
	const EXPORT_SIZE = 1000;
	private $_bill_info;

	public function __construct() {
		parent::__construct() ;
		Language::read('member_layout');
	}

	/**
	 * 结算列表
	 *
	 */
	public function indexOp() {

		$this -> index_othOp();
		exit;

		$model_bill = Model('bill');
		$condition = array();
		$condition['ob_store_id'] = $_SESSION['store_id'];
		if (preg_match('/^\d+$/',$_GET['ob_id'])) {
			$condition['ob_id'] = intval($_GET['ob_id']);
		}
		if (is_numeric($_GET['bill_state'])) {
			$condition['ob_state'] = intval($_GET['bill_state']);
		}

		//一些统计
		$count_ord = $this -> show_bill_count();
		Tpl::output('count_ord',$count_ord);
		// var_dump($count_ord);

		$bill_list = $model_bill->getOrderBillList($condition,'*',12,'ob_state asc,ob_id desc');
		Tpl::output('bill_list',$bill_list);
		Tpl::output('show_page',$model_bill->showpage());

		$model_store_ext = Model('store_extend');
		$ext_info = $model_store_ext->getStoreExtendInfo(array('store_id'=>$_SESSION['store_id']));

		Tpl::output('bill_cycle',$ext_info['bill_cycle'] ? $ext_info['bill_cycle'].'天' : '1个月');

		$this->profile_menu('list','list');
		Tpl::showpage('store_bill.index');

	}

	/**
	 * [show_bill_count 本月的数据统计]
	 * @return [type] [description]
	 */
	public function show_bill_count(){
		//该月第一天0时unix时间戳
		$first_day_unixtime = strtotime(date('Y-m-01 00:00:00', time()));
		//该月最后一天最后一秒时unix时间戳
		$last_day_unixtime = strtotime(date('Y-m-01 23:59:59', time())." +1 month -1 day");

		$find = " '{$first_day_unixtime}' < `add_time` and `add_time` <  '{$last_day_unixtime}' ";
		$order_list = Model('order') -> getOrderList($find);

		$ord = array();
		foreach ($order_list as $key => $olt) {
			$ord['order_amount'] += $olt['order_amount'];
			$ord['sb_amount'] += $olt['sb_amount'];
		}
		$ord['xj_amount'] = $ord['order_amount'] - $ord['sb_amount'];
		$ord['ord_count'] = count($order_list);

		return $ord;
	}






	/**
	 * 查看结算单详细
	 *
	 */
	public function show_billOp(){
		if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
			showMessage('参数错误','','html','error');
		}
		$model_bill = Model('bill');
		$condition = array();
		$condition['ob_id'] = intval($_GET['ob_id']);
		$condition['ob_store_id'] = $_SESSION['store_id'];
		$bill_info = $model_bill->getOrderBillInfo($condition);
		if (!$bill_info){
			showMessage('参数错误','','html','error');
		}
		$this->_bill_info = $bill_info;

		$order_condition = array();
		$order_condition['order_state'] = ORDER_STATE_SUCCESS;
		$order_condition['store_id'] = $bill_info['ob_store_id'];
		$if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
		$if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
		$start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
		$end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
		if ($if_start_date || $if_end_date) {
			$order_condition['finnshed_time'] = array('time',array($start_unixtime,$end_unixtime));
		} else {
			$order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
		}
		if ($_GET['type'] =='refund'){
			if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
				$order_condition['refund_sn'] = $_GET['query_order_no'];
			}
			//退款订单列表
			$model_refund = Model('refund_return');
			$refund_condition = array();
			$refund_condition['seller_state'] = 2;
			$refund_condition['store_id'] = $bill_info['ob_store_id'];
			$refund_condition['goods_id'] = array('gt',0);
			$refund_condition['admin_time'] = $order_condition['finnshed_time'];
			if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
				$refund_condition['refund_sn'] = $_GET['query_order_no'];
			}
			$refund_list = $model_refund->getRefundReturnList($refund_condition,20,'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount');
			if (is_array($refund_list) && count($refund_list) == 1 && $refund_list[0]['refund_id'] == '') {
				$refund_list = array();
			}
			//取返还佣金
			Tpl::output('refund_list',$refund_list);
			Tpl::output('show_page',$model_refund->showpage());
			$sub_tpl_name = 'store_bill.show.refund_list';
			$this->profile_menu('show','refund_list');
		} elseif ($_GET['type'] == 'cost') {
			//店铺费用
			$model_store_cost = Model('store_cost');
			$cost_condition = array();
			$cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
			$cost_condition['cost_time'] = $order_condition['finnshed_time'];
			$store_cost_list = $model_store_cost->getStoreCostList($cost_condition,20);

			//取得店铺名字
			$store_info = Model('store')->getStoreInfoByID($bill_info['ob_store_id']);
			Tpl::output('cost_list',$store_cost_list);
			Tpl::output('store_info',$store_info);
			Tpl::output('show_page',$model_store_cost->showpage());
			$sub_tpl_name = 'store_bill.show.cost_list';
			$this->profile_menu('show','cost_list');
		
		}elseif ($_GET['type'] == 'book') {
			$condition = array();
			//被取消的预定订单列表
			$model_order = Model('order');
			if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
				$order_info = $model_order->getOrderInfo(array('order_sn'=> $_GET['query_order_no']));
				if ($order_info) {
					$condition['book_order_id'] = $order_info['order_id'];
				} else {
					$condition['book_order_id'] = 0;
				}                
			}

			$model_order_book = Model('order_book');

			$condition['book_store_id'] = $bill_info['ob_store_id'];
			$condition['book_cancel_time'] = $order_condition['finnshed_time'];
			$order_book_list = $model_order_book->getOrderBookList($condition,$_POST['rp'],'book_id desc','*');
			
			//然后取订单信息
			$tmp_book = array();
			$order_id_array = array();
			if (is_array($order_book_list)) {
				foreach ($order_book_list as $order_book_info) {
					$order_id_array[] = $order_book_info['book_order_id'];
					$tmp_book[$order_book_info['book_order_id']]['book_cancel_time'] = $order_book_info['book_cancel_time'];
					$tmp_book[$order_book_info['book_order_id']]['book_real_pay'] = $order_book_info['book_real_pay'];
				}
			}
			$order_list = $model_order->getOrderList(array('order_id'=>array('in',$order_id_array)));
			Tpl::output('deposit_list',$tmp_book);
			Tpl::output('order_list',$order_list);
			Tpl::output('show_page',$model_order->showpage());
			$sub_tpl_name = 'store_bill.show.order_book_list';
			$this->profile_menu('show','book_list');
		} else {

			if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
				$order_condition['order_sn'] = $_GET['query_order_no'];
			}
			//订单列表
			$model_order = Model('order');
			$order_list = $model_order->getOrderList($order_condition,20);

			//然后取订单商品佣金
			$order_id_array = array();
			if (is_array($order_list)) {
				foreach ($order_list as $order_info) {
					$order_id_array[] = $order_info['order_id'];
				}
			}
			$order_goods_condition = array();
			$order_goods_condition['order_id'] = array('in',$order_id_array);
			$field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
			$commis_list = $model_order->getOrderGoodsList($order_goods_condition,$field,null,null,'','order_id','order_id');
			Tpl::output('commis_list',$commis_list);
			Tpl::output('order_list',$order_list);
			Tpl::output('show_page',$model_order->showpage());
			$sub_tpl_name = 'store_bill.show.order_list';
			$this->profile_menu('show','order_list');
		}

		Tpl::output('sub_tpl_name',$sub_tpl_name);
		Tpl::output('bill_info',$bill_info);
		Tpl::showpage('store_bill.show');
	}

	/**
	 * 打印结算单
	 *
	 */
	public function bill_printOp(){
		if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
			showMessage('参数错误','','html','error');
		}
		$model_bill = Model('bill');
		$condition = array();
		$condition['ob_id'] = intval($_GET['ob_id']);
		$condition['ob_state'] = BILL_STATE_SUCCESS;
		$condition['ob_store_id'] = intval($_SESSION['store_id']);
		$bill_info = $model_bill->getOrderBillInfo($condition);
		if (!$bill_info){
			showMessage('参数错误','','html','error');
		}

		Tpl::output('bill_info',$bill_info);
		Tpl::showpage('store_bill.print','null_layout');
	}

	/**
	 * 店铺确认出账单
	 *
	 */
	public function confirm_billOp(){
		if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
			showDialog('参数错误','','error');
		}
		$model_bill = Model('bill');
		$condition = array();
		$condition['ob_id'] = intval($_GET['ob_id']);
		$condition['ob_store_id'] = $_SESSION['store_id'];
		$condition['ob_state'] = BILL_STATE_CREATE;
		$update = $model_bill->editOrderBill(array('ob_state'=>BILL_STATE_STORE_COFIRM),$condition);
		if ($update){
			showDialog('确认成功','reload','succ');
		}else{
			showDialog(L('nc_common_op_fail'),'reload','error');
		}
	}

	/**
	 * 导出结算订单明细CSV
	 *
	 */
	public function export_orderOp(){
		if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
			showMessage('参数错误','','html','error');
		}

		$model_bill = Model('bill');
		$bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>intval($_GET['ob_id']),'ob_store_id'=>$_SESSION['store_id']));
		if (!$bill_info){
			showMessage('参数错误','','html','error');
		}

		$model_order = Model('order');
		$condition = array();
		if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
			$condition['order_sn'] = $_GET['query_order_no'];
		}
		$condition['order_state'] = ORDER_STATE_SUCCESS;
		$condition['store_id'] = $_SESSION['store_id'];
		$if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
		$if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
		$start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
		$end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
		if ($if_start_date || $if_end_date) {
			$condition['finnshed_time'] = array('time',array($start_unixtime,$end_unixtime));
		} else {
			$condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
		}
		if (!is_numeric($_GET['curpage'])){
			$count = $model_order->getOrderCount($condition);
			$array = array();
			if ($count > self::EXPORT_SIZE ){
				//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=store_bill&op=show_bill&ob_id='.$_GET['ob_id']);
				Tpl::showpage('store_export.excel');
				exit();
			}else{
				//如果数量小，直接下载
				$data = $model_order->getOrderList($condition,'','*','order_id desc',self::EXPORT_SIZE,array('order_goods'));
			}
		}else{
			//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_order->getOrderList($condition,'','*','order_id desc',"{$limit1},{$limit2}",array('order_goods'));
		}

		//订单商品表查询条件
		$order_id_array = array();
		if (is_array($data)) {
			foreach ($data as $order_info) {
				$order_id_array[] = $order_info['order_id'];
			}
		}
		$order_goods_condition = array();
		$order_goods_condition['order_id'] = array('in',$order_id_array);

		$export_data = array();
		$export_data[0] = array('订单编号','下单时间','成交时间','订单金额','运费','佣金','平台红包','买家','买家编号','商品');
		$order_totals = 0;
		$shipping_fee_totals = 0;
		$commis_totals = 0;
		$rpt_totals = 0;
		$k = 0;
		foreach ($data as $v) {
			//该订单算佣金
			$field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
			$commis_list = $model_order->getOrderGoodsList($order_goods_condition,$field,null,null,'','order_id','order_id');
			$export_data[$k+1][] = $v['order_sn'];
			$export_data[$k+1][] = date('Y-m-d',$v['add_time']);
			$export_data[$k+1][] = date('Y-m-d',$v['finnshed_time']);
			$order_totals += $export_data[$k+1][] = $v['order_amount'];
			$shipping_fee_totals += $export_data[$k+1][] = $v['shipping_fee'];
			$commis_totals += $export_data[$k+1][] = $commis_list[$v['order_id']]['commis_amount'];
			$rpt_totals += $export_data[$k+1][] = $v['rpt_amount'];
			$export_data[$k+1][] = $v['buyer_name'];
			$export_data[$k+1][] = $v['buyer_id'];
			$goods_string = '';
			if (is_array($v['extend_order_goods'])) {
				foreach ($v['extend_order_goods'] as $v) {
					$goods_string .= $v['goods_name'].'|单价:'.$v['goods_price'].'|数量:'.$v['goods_num'].'|实际支付:'.$v['goods_pay_price'].'|佣金比例:'.$v['commis_rate'].'%';
				}
			}
			$export_data[$k+1][] = $goods_string;
			$k++;
		}
		$count = count($export_data);
		$export_data[$count][] = '合计';
		$export_data[$count][] = '';
		$export_data[$count][] = '';
		$export_data[$count][] = $order_totals;
		$export_data[$count][] = $shipping_fee_totals;
		$export_data[$count][] = $commis_totals;
		$export_data[$count][] = $rpt_totals;
		$csv = new Csv();
		$export_data = $csv->charset($export_data,CHARSET,'gbk');
		$csv->filename = 'order-detail';
		$csv->export($export_data);
	}

	/**
	 * 导出结算未退定金预定订单明细CSV
	 *
	 */
	public function export_bookOp(){
		if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
			showMessage('参数错误','','html','error');
		}

		$model_bill = Model('bill');
		$bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>intval($_GET['ob_id']),'ob_store_id'=>$_SESSION['store_id']));
		if (!$bill_info){
			showMessage('参数错误','','html','error');
		}
	
		$model_order = Model('order');
		$model_order_book = Model('order_book');
		$condition = array();
		if (preg_match('/^\d{8,20}$/',$_GET['query_order_no'])) {
			$order_info = $model_order->getOrderInfo(array('order_sn'=>$_GET['query_order_no']));
			if ($order_info) {
				$condition['book_order_id'] = $order_info['order_id'];
			} else {
				$condition['book_order_id'] = 0;
			}
		}
		$condition['book_store_id'] = $_SESSION['store_id'];
		$condition['book_cancel_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");

		if (!is_numeric($_GET['curpage'])){
			$count = $model_order_book->getOrderBookCount($condition);
			$array = array();
			if ($count > self::EXPORT_SIZE ){
				//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=store_bill&op=show_bill&ob_id='.$_GET['ob_id']);
				Tpl::showpage('store_export.excel');
				exit();
			}
			$limit = false;
		}else{
			//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$limit = "{$limit1},{$limit2}";
		}

		$order_book_list = $model_order_book->getOrderBookList($condition,'','book_id desc','*',$limit);

		//然后取订单信息
		$tmp_book = array();
		$order_id_array = array();
		if (is_array($order_book_list)) {
			foreach ($order_book_list as $order_book_info) {
				$order_id_array[] = $order_book_info['book_order_id'];
				$tmp_book[$order_book_info['book_order_id']]['book_cancel_time'] = $order_book_info['book_cancel_time'];
				$tmp_book[$order_book_info['book_order_id']]['book_real_pay'] = $order_book_info['book_real_pay'];
			}
		}
		$data = $model_order->getOrderList(array('order_id'=>array('in',$order_id_array)),'','*','order_id desc');
		
		$export_data = array();
		$export_data[0] = array('订单编号','下单时间','取消时间','订单金额','运费','未退定金','商家','商家编号','买家','买家编号');
		$order_amount = 0;
		$deposit_amount = 0;
		$k = 0;
		foreach ($data as $v) {
			//该订单算佣金
			$export_data[$k+1][] = $v['order_sn'];
			$export_data[$k+1][] = date('Y-m-d',$v['add_time']);
			$export_data[$k+1][] = date('Y-m-d',$tmp_book[$v['order_id']]['book_cancel_time']);
			$order_amount += $export_data[$k+1][] = $v['order_amount'];
			$export_data[$k+1][] = $v['shipping_fee'];
			$deposit_amount += $export_data[$k+1][] = ncPriceFormat($tmp_book[$v['order_id']]['book_real_pay']);
			$export_data[$k+1][] = $v['store_name'];
			$export_data[$k+1][] = $v['store_id'];
			$export_data[$k+1][] = $v['buyer_name'];
			$export_data[$k+1][] = $v['buyer_id'];
			$k++;
		}
		$count = count($export_data);
		$export_data[$count][] = '合计';
		$export_data[$count][] = '';
		$export_data[$count][] = $order_amount;
		$export_data[$count][] = '';
		$export_data[$count][] = '';
		$export_data[$count][] = $deposit_amount;
		$csv = new Csv();
		$export_data = $csv->charset($export_data,CHARSET,'gbk');
		$csv->filename = 'order-book-list';
		$csv->export($export_data);
	}

	/**
	 * 导出结算退单明细CSV
	 *
	 */
	public function export_refund_orderOp(){
		if (!preg_match('/^\d+$/',$_GET['ob_id'])) {
			showMessage('参数错误','','html','error');
		}
		$model_bill = Model('bill');
		$bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>intval($_GET['ob_id']),'ob_store_id'=>$_SESSION['store_id']));
		if (!$bill_info){
			showMessage('参数错误','','html','error');
		}

		$model_refund = Model('refund_return');
		$condition = array();
		$condition['seller_state'] = 2;
		$condition['store_id'] = $_SESSION['store_id'];
		$condition['goods_id'] = array('gt',0);
		$if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
		$if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
		$start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
		$end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
		if ($if_start_date || $if_end_date) {
			$condition['admin_time'] = array('time',array($start_unixtime,$end_unixtime));
		} else {
			$condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
		}

		if (!is_numeric($_GET['curpage'])){
			$count = $model_refund->getRefundReturn($condition);
			$array = array();
			if ($count > self::EXPORT_SIZE ){   //显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=store_bill&op=show_bill&query_type=refund&ob_id='.$_GET['ob_id']);
				Tpl::showpage('store_export.excel');
				exit();
			}else{
				//如果数量小，直接下载
				$data = $model_refund->getRefundReturnList($condition,'','refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount',self::EXPORT_SIZE);
			}
		}else{
			//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_refund->getRefundReturnList(condition,'','refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount',"{$limit1},{$limit2}");
		}
		if (is_array($data) && count($data) == 1 && $data[0]['refund_id'] == '') {
			$refund_list = array();
		}
		$export_data = array();
		$export_data[0] = array('退单编号','订单编号','退单金额','退单佣金','退还红包','类型','退款时间','买家','买家编号');
		$refund_amount = 0;
		$commis_totals = 0;
		$rpt_amount = 0;
		$k = 0;
		foreach ($data as $v) {
			$export_data[$k+1][] = $v['refund_sn'];
			$export_data[$k+1][] = $v['order_sn'];
			$refund_amount += $export_data[$k+1][] = $v['refund_amount'];
			$commis_totals += $export_data[$k+1][] = ncPriceFormat($v['commis_amount']);
			$rpt_amount += $export_data[$k+1][] = ncPriceFormat($v['rpt_amount']);
			$export_data[$k+1][] = str_replace(array(1,2),array('退款','退货'),$v['refund_type']);
			$export_data[$k+1][] = date('Y-m-d',$v['admin_time']);
			$export_data[$k+1][] = $v['buyer_name'];
			$export_data[$k+1][] = $v['buyer_id'];
			$k++;
		}
		$count = count($export_data);
		$export_data[$count][] = '';
		$export_data[$count][] = '合计';
		$export_data[$count][] = $refund_amount;
		$export_data[$count][] = $commis_totals;
		$export_data[$count][] = $rpt_amount;
		$csv = new Csv();
		$export_data = $csv->charset($export_data,CHARSET,'gbk');
		$csv->filename = 'order-refund-detail';
		$csv->export($export_data);
	}



	public function index_othOp(){
		$where['log_store_id'] = $_SESSION['store_id'];
		$list = Model('store_money_log') -> getMoneyLogList($where,'*',20);
		foreach ($list as $key => $g) {
			$list[$key]['log_type'] = $this -> orderPaymentName($g['log_type']);
		}

		//一些统计
		$count_list = $this -> show_money_count();

		Tpl::output('count_list',$count_list);
		Tpl::output('return_list',$list);
        		Tpl::output('show_page',Model('store_money_log')->showpage());
		$this->profile_menu('list','list');
		Tpl::showpage('store_bill.index_oth');
	}


	/**
	 * 取得订单支付类型文字输出形式
	 *
	 * @param array $payment_code
	 * @return string
	 */
	function orderPaymentName($payment_code) {
		return str_replace(array('online_appwxpay','money_cash'),array('微信支付(客户)','余额提现(商户)'),$payment_code);
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



	/**
	 * [cashOp 提现申请]
	 * @return [type] [description]
	 */
	public function cashOp(){
		$list = Model('store_money_cash_log') -> getMoneyCashLogList(array('store_id'=>$_SESSION['store_id']));
		foreach ($list as $key => $g) {
			$list[$key]['status'] = $this -> orderStatusName($g['status']);
		}

		Tpl::output('list',$list);
		Tpl::output('show_page',Model('store_money_cash_log')->showpage());
		self::profile_menu('list','index_broken');
		Tpl::showpage('store_bill.cash');
	}


	function orderStatusName($payment_code) {
		return str_replace(array('1','2','3'),array('申请中','已通过','未通过'),$payment_code);
	}

	public function cash_payOp(){
		$count_list = $this -> show_money_count();
		$agree_fee = $count_list['sb_amount'];
		
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
			$data['ordns'] = $pay_sn = Model('store_money_cash_log')->makeSn();
			$data['store_id'] = $_SESSION['store_id'];
			$data['cash_fee'] = $_POST['cash_fee'];
			$data['add_time'] = TIMESTAMP;

			$insert = Model('store_money_cash_log')->addMoneyCashLog($data);
			if (!$insert){
				showDialog('数据有误','','error');
			}
			showDialog('申请成功，请耐心等待！','reload','succ');
		}

		Tpl::output('agree_fee',$agree_fee);
		Tpl::showpage('store_bill.cash_pay','null_layout');
	}

	public function cash_lookOp(){
		$ordns = $_GET['ns'];

		$info = Model('store_money_cash_log')->getMoneyCashLog(array('ordns'=>$ordns));

		Tpl::output('info',$info);
		Tpl::showpage('store_bill.cash_look','null_layout');
	}













	/**
	 * 用户中心右边，小导航
	 *
	 * @param string    $menu_type  导航类型
	 * @param string    $menu_key   当前导航的menu_key
	 * @return
	 */
	private function profile_menu($menu_type,$menu_key='') {
		$menu_array = array();
		switch ($menu_type) {
			case 'list':
				$menu_array = array(
					1=>array('menu_key'=>'list','menu_name'=>'余额结算记录', 'menu_url'=>'index.php?act=store_bill&op=index_oth'),
					2=>array('menu_key'=>'index_broken','menu_name'=>'余额提现', 'menu_url'=>'index.php?act=store_bill&op=cash'),
				);
				break;
			case 'show':
				$menu_array = array(
				array('menu_key'=>'order_list','menu_name'=>'订单列表', 'menu_url'=>'index.php?act=store_bill&op=show_bill&ob_id='.$_GET['ob_id']),
				array('menu_key'=>'refund_list','menu_name'=>'退款订单','menu_url'=>'index.php?act=store_bill&op=show_bill&type=refund&ob_id='.$_GET['ob_id']),
				array('menu_key'=>'cost_list','menu_name'=>'促销费用','menu_url'=>'index.php?act=store_bill&op=show_bill&type=cost&ob_id='.$_GET['ob_id'])
				);
				if (floatval($this->_bill_info['ob_order_book_totals']) > 0) {
					array_push($menu_array,array('menu_key'=>'book_list','menu_name'=>'未退定金', 'menu_url'=>'index.php?act=store_bill&op=show_bill&type=book&ob_id='.$_GET['ob_id']));
				}
				break;
		}
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
	}
}
