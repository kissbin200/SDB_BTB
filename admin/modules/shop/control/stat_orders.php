<?php
/**
 * 订单统计管理
 *
 */


defined('In33hao') or exit('Access Invalid!');

class stat_ordersControl extends SystemControl{
	public function __construct(){
		parent::__construct();
	}

	public function indexOp() {
		$this->ordersOp();
	}

	/**
	 * [ordersOp 订单统计]
	 * @return [type] [description]
	 */
	public function ordersOp(){
		//检测是否为子公司
		$back_company = $this->getAdminInfo();
		if ($back_company['id']>1) {
			$condition['back_company'] = $back_company['id'];
		}

		echo "string";
		Tpl::output('storelist',$store_list);
		Tpl::setDirquna('shop');
		Tpl::showpage('stat.orders');
	}
}