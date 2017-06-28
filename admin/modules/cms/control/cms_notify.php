<?php 
/**
 * 
 */

defined('In33hao') or exit('Access Invalid!');
class cms_notifyControl extends SystemControl{
	public function __construct(){
		parent::__construct();
		Language::read('cms');
	}

	public function indexOp(){
		$this -> notifyListOp();
	}

	public function notifyListOp(){




		Tpl::setDirquna('cms');
		Tpl::showpage("cms_notify.list");       
	}

	/**
	 * [cms_notify_addOp ]
	 * @return [type] [description]
	 */
	public function cms_notify_addOp(){
		
		Tpl::setDirquna('cms');
		Tpl::showpage("cms_notify.add");
	}

	public function cms_notify_editOp(){
		$find = Model('cms_notify') -> findNotify(array('id'=>$_GET['id']));



		Tpl::output('info',$find);
		Tpl::setDirquna('cms');
		Tpl::showpage("cms_notify.add");
	}


	public function cms_notify_sevaOp(){
		$title = $_POST['notify_title'];
		$body = $_POST['g_body'];
		$newid = $_POST['notify_id'];
		$cms_type = $_POST['cms_type'];

		$model_notify = Model('cms_notify');

		$add_data = array();
		$add_data['title'] = $title;
		$add_data['cms_type'] = $cms_type;
		$add_data['content'] = htmlspecialchars_decode($_POST['g_body'], ENT_QUOTES);
		// $add_data['content'] = $_POST['g_body'];
		if (empty($newid)) {
			//新增
			$add_data['addtime'] = time();
			$add = $model_notify -> addNotify($add_data);

			if ($add) {
				showMessage('OK','');
			}else{
				showMessage('NO','','error');
			}

		}else{
			//编辑
			$updata = $model_notify -> editNotify($add_data,array('id'=>$newid));
			if ($updata) {
				showMessage('OK','');
			}else{
				showMessage('NO','','error');
			}
		}
	}



	public function cms_notify_xmlOp(){
		$model_notify = Model('cms_notify');
		$page = intval($_POST['rp']);
		if ($page < 1) {
			$page = 15;
		}
		
		$condition = ' 1=1 ';
		$list = $model_notify->listNotify($condition, $page);
		$data = array();
		$data['now_page'] = $model_notify->shownowpage();
		$data['total_num'] = $model_notify->gettotalnum();
		foreach ($list as $k => $v){
			$i = array();
$i['operation'] = <<<EOB
<a class="btn green" href="index.php?act=cms_notify&op=cms_notify_edit&id={$v['id']}"><i class="fa fa-edit"></i>编辑</a>
EOB;
// <a class="btn green confirm-del-on-click" href="javascript:;" data-href="index.php?act=rechargewater&op=del_card&id={$water['activityId']}"><i class="fa fa-trash"></i>删除</a>
			$i['special_title'] = $v['title'];
			$i['special_type_text'] = str_replace(array('1','2'),array('学院','消息'),$v['cms_type']);

			$data['list'][$v['id']] = $i;

		}
		

		echo Tpl::flexigridXML($data);

	}
}