<?php 
/**
* 密码修改
*/
class store_password_correctControl extends BaseSellerControl{
	public function __construct() {
		parent::__construct();
	}

	public function indexOp(){

		Tpl::showpage('store_password_correct.index');
	}

	public function correctOp(){
		$member_id = $_SESSION['member_id'];
		
		$oid_pwd = $_POST['old_pwd']; //旧密码
		$new_pwd = $_POST['new_pwd'];  //新密码
		$age_pwd = $_POST['age_pwd'];   //再次输入的新密码

		if ($new_pwd != $age_pwd) {
			showDialog('新密码输入错！','','error');
		}

		$user_info = Model('member') -> getMemberInfoByID($member_id);

		if (md5($oid_pwd) != $user_info['member_passwd']) {
			showDialog('who are you ?','','error');
		}

		if (md5($new_pwd) == $user_info['member_passwd']) {
			showDialog('新密码居然和原来一样','','error');
		}

		//执行修改
		$upmember = Model('member') -> editMember(array('member_id'=>$member_id),array('member_passwd'=>md5($new_pwd)));
		if ($upmember) {
			showDialog('很好，修改成功！','reload','succ');
		}else{
			showDialog('修稿失败','','error');
		}
		
	}
}