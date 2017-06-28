<?php

defined('In33hao') or exit('Access Invalid!');
class cms_notifyModel extends Model {
	public function __construct() {
		parent::__construct('cms_notify');
	}

	/**
	 * 单条查询
	 *
	 * @param
	 */
	public function findNotify($condition = array()) {
		return $this->where($condition)->find();
	}

	/*
	 *列表查询
	 * 
	 */
	public function listNotify($condition = array(), $limit = ''){
		return $this->where($condition)->limit($limit)->select();
	}

	/*
	 *列表查询统计
	 * 
	 */
	public function listNotifyCount($condition = array()){
		return $this->where($condition)->count();
	}

	/*
	 * 编辑
	 */
	public function editNotify($update, $condition){
		return $this->where($condition)->update($update);
	}

	/*
	 * 新增
	 */
	public function addNotify($param){
		return $this->insert($param);
	}



	/*
	 * 新增访问记录
	 */
	public function addNotifyLog($param){
		return $this->table('cms_notify_log')->insert($param);
	}

	/**
	 * 单条查询
	 *
	 * @param
	 */
	public function findNotifyLog($condition = array()) {
		return $this->table('cms_notify_log')->where($condition)->find();
	}

	/*
	 *访问记录统计
	 * 
	 */
	public function listNotifyLogCount($condition = array()){
		return $this->table('cms_notify_log')->where($condition)->count();
	}

}