<?php 

defined('In33hao') or exit('Access Invalid!');
class store_money_logModel extends Model {
	public function __construct() {
		parent::__construct('store_money_log');
	}


	/**
	 * 余额列表
	 * @param array $condition
	 * @param string $field
	 * @param string $order
	 * @param number $page
	 * @param string $limit
	 * @return array
	 */
	public function getMoneyLogList($condition, $field = '*', $page = 0, $order = 'log_add_time desc', $limit = '') {
		return $this->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
	}

}