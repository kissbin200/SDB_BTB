<?php 
/**
* 水币充值设置
*/

defined('In33hao') or exit('Access Invalid!');
class rechargewaterControl extends SystemControl{
	
	public function __construct(){
		parent::__construct();
	}

	public function indexOp(){

		Tpl::setDirquna('shop');
		Tpl::showpage('rechargewater.index');
	}


	public function add_cardOp(){
		if (!chksubmit()) {	
			$param = array(
					'table'=>'admin',
					'field'=>'*',
					'where'=> ' 1=1'
				);
			$admin_list = Db::select($param);

			Tpl::output('adminlist',$admin_list);
			Tpl::setDirquna('shop');
			Tpl::showpage('rechargewater.add_card');
			return;
		}
		

		$arr = array_combine(array_filter($_POST['denomination']),array_filter($_POST['activity_denomination']));

		$data['activityTitle'] = $_POST['title'];
		$data['activityDenomination'] 	= serialize($arr);
		$data['activityStarttime'] = strtotime($_POST['starttime']);
		$data['activityEndtime'] = strtotime($_POST['endtime']);
		$data['activityAddid'] = $_POST['activityAddid'];
		$data['activityAddtime'] = time();

		$add = Model('rechargecard') -> addRechargewater($data);
		if ($add) {
			$msg = "操作成功";
			showMessage($msg, urlAdminShop('rechargewater', 'index'));
		}else{
			showMessage('参数错误', '', 'html', 'error');
		}
	}

	public function index_xmlOp(){
		$model = Model('rechargecard');

		//检测是否为子公司
		$back_company = $this->getAdminInfo();
		if ($back_company['id']>1) {
			$condition['activityAddid'] = $back_company['id'];
		}

		$list = (array) $model->getRechargeWaterList($condition);

		$data = array();
		$data['now_page'] = $model->shownowpage();
		$data['total_num'] = $model->gettotalnum();

		
		foreach ($list as $key => $water) {
			$i = array();
			$i['operation'] = <<<EOB
<a class="btn green confirm-del-on-click" href="javascript:;" data-href="index.php?act=rechargewater&op=del_card&id={$water['activityId']}"><i class="fa fa-trash"></i>删除</a>

EOB;
// <a class="btn green" href="index.php?act=rechargewater&op=edgt_card&id={$water['activityId']}"><i class="fa fa-edit"></i>编辑</a>
			$i['title'] = $water['activityTitle'];
			$I['batchflag'] = $water['activityTitle'];

			//拼接活动内容
			$Denomination = '';
			$den = unserialize($water['activityDenomination']);
			foreach ($den as $key => $adn) {
				$Denomination .= "充值".$key."送".$adn.'  ';
			}
			$i['batchflag'] = $key;
			$i['denomination'] = $Denomination;

			$i['playtime'] = date('Y-m-d',$water['activityStarttime']) . "至" . date('Y-m-d',$water['activityEndtime']);

			$admin = Model('admin') -> getOneAdmin($water['activityAddid']);
			$i['admin_name'] = $admin['admin_name'];
			$i['tscreated'] = date('Y-m-d',$water['activityAddtime']);

			$data['list'][$water['activityId']] = $i;
		}
		echo Tpl::flexigridXML($data);
	}


	public function edgt_cardOp(){
		if (!chksubmit()) {
			$pid = $_GET['id'];
			$model = Model('rechargecard');
			$info = $model -> getRechargeWaterByID($pid);

			$info['activityDenomination'] = unserialize($info['activityDenomination']);
			$info['activityStarttime'] = date('Y-m-d',$info['activityStarttime']);
			$info['activityEndtime'] = date('Y-m-d',$info['activityEndtime']);

			$param = array(
					'table'=>'admin',
					'field'=>'*',
					'where'=> ' 1=1'
				);
			$admin_list = Db::select($param);

			Tpl::output('adminlist',$admin_list);

			Tpl::output('info',$info);
			Tpl::setDirquna('shop');
			Tpl::showpage('rechargewater.edgt_card');
			return;
		}
		$activityId = $_POST['pid'];

		$arr = array_combine(array_filter($_POST['denomination']),array_filter($_POST['activity_denomination']));
		$data['activityTitle'] = $_POST['title'];
		$data['activityDenomination'] 	= serialize($arr);
		$data['activityStarttime'] = strtotime($_POST['starttime']);
		$data['activityEndtime'] = strtotime($_POST['endtime']);

		$add = Model('rechargecard') -> setRechargeWaterById($activityId,$data);
		if ($add) {
			$msg = "操作成功";
			showMessage($msg, urlAdminShop('rechargewater', 'index'));
		}else{
			showMessage('参数错误', '', 'html', 'error');
		}

	}


	public function del_cardOp(){
		$id = $_GET['id'];
		$find = Model('rechargecard') -> getRechargeWaterByID($id);
		$now = time();

		if ($find['activityStarttime']< $now && $find['activityEndtime']> $now ) {
			showMessage('该活动还在进行中，不能删除', '', 'html', 'error');
		}else{
			Model('rechargecard') -> delRechargeWaterById($id);
			showMessage('删除成功', urlAdminShop('rechargewater', 'index'));
		}
	}

}
