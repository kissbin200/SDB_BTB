<?php

defined('In33hao') or exit('Access Invalid!');
class versionModel extends Model {
	public function __construct(){
		parent::__construct('versionandroid');
	}


	public function getversionInfoA($condition,$order = 'addtime DESC') {
		return $this->where($condition)->order($order)->select();
	}

	public function addversionA($insert) {
		return $this->insert($insert);
	}

	public function editversionA($update, $condition) {
		return $this->where($condition)->update($update);
	}
}