<?php 

defined('In33hao') or exit('Access Invalid!');
class store_sb_cash_logModel extends Model {
	public function __construct() {
		parent::__construct('store_sb_cash_log');
	}

	/**
	 * 生成充值编号
	 * @return string
	 */
	public function makeSn() {
		return mt_rand(10,99)
		. sprintf('%010d',time() - 946656000)
		. sprintf('%03d', (float) microtime() * 1000)
		. sprintf('%03d', (int) $_SESSION['store_id'] % 1000);
	}

	/**
	 * 水币提现列表
	 * @param array $condition
	 * @param string $field
	 * @param string $order
	 * @param number $page
	 * @param string $limit
	 * @return array
	 */
	public function getSbCashLogList($condition, $field = '*', $page = 0, $order = 'add_time desc', $limit = '') {
		return $this->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
	}

	/**
	 * 更新
	 */
	public function updateSbCashLog($condition,$update){
		$condition_str = $this->where($condition)->update($update);
		return $condition_str ;
	}

	/*
	 * 添加
	 */
	public function addSbCashLog($param){
		return $this->insert($param);
	}

	/*
	 * 添加
	 */
	public function getSbCashLog($param){
		return $this->where($param)->find();
	}
}