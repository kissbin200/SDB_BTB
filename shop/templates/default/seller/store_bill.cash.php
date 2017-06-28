<?php defined('In33hao') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
	<?php include template('layout/submenu');?>
	<a href="javascript:void(0)" class="ncbtn ncbtn-mint" nc_type="dialog" dialog_title="提现申请" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=store_bill&op=cash_pay">提现申请</a>
</div>



<table class="ncsc-default-table">
	<thead>
		<tr>
			<th class="w10">单号</th>
			<th class="w10">金额</th>
			<th class="w10">状态</th>
			<th class="w10">申请时间</th>
			<th class="w10">操作</th>
		</tr>
	</thead>
	<?php if (is_array($output['list']) && !empty($output['list'])) { ?>
	<tbody>
		<?php foreach ($output['list'] as $key => $val) { ?>
		<tr class="bd-line" >
			<td><?php echo $val['ordns'] ?></td>
			<td><?php echo $val['cash_fee'] ?></td>
			<td><?php echo $val['status'] ?></td>
			<td><?php echo date('Y-m-d H:i:s',$val['add_time']) ?></td>
			<td><a href="javascript:void(0)" class="ncbtn ncbtn-mint" nc_type="dialog" dialog_title="提现查看" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=store_bill&op=cash_look&ns=<?php echo $val['ordns'] ?>">查看</a></td>
		</tr>
		<?php } ?>
		<?php } else { ?>
		<tr>
			<td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign">&nbsp;</i><span><?php echo $lang['no_record'];?></span></div></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<?php if (is_array($output['list']) && !empty($output['list'])) { ?>
		<tr>
			<td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
		</tr>
		<?php } ?>
	</tfoot>
</table>