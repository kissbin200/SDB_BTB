<?php defined('In33hao') or exit('Access Invalid!');?>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<div class="subject">
				<h3>余额提现管理</h3>
				<h5>余额提现管理</h5>
			</div>
			<ul class="tab-base nc-row">
				<li><a class="current" href="JavaScript:void(0);">余额提现管理</a></li>
			</ul>
		</div>
	</div>
	<!-- 操作说明 -->
	<div class="explanation" id="explanation">
		<div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
			<h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
			<span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
		<ul>
			<li>不晓得</li>
		</ul>
	</div>
	<div id="flexigrid"></div>
</div>

<script>

$(function() {
		var flexUrl = 'index.php?act=money_cash&op=get_sb_cash_xml';

		$("#flexigrid").flexigrid({
				url: flexUrl,
				colModel: [
						{display: '操作', name: 'operation', width: 210, sortable: false, align: 'center', className: 'handle-s'},
						{display: '申请水厂', name: 'title', width: 200, sortable: false, align: 'left'},
						{display: '申请金额', name: 'batchflag', width: 80, sortable: false, align: 'left'},
						{display: '状态', name: 'denomination', width: 80, sortable: 1, align: 'left'},
						{display: '申请时间', name: 'playtime', width: 200, sortable: 1, align: 'left'},
						{display: '操作管理员', name: 'admin_name', width: 80, sortable: false, align: 'left'},
						{display: '操作时间', name: 'tscreated', width: 128, sortable: 1, align: 'left'},
						{display: '备注', name: 'gettalk', width: 200, sortable: 1, align: 'left'},
				],
				sortname: "id",
				sortorder: "desc",
				title: '余额提现申请列表'
		});
})
</script>
