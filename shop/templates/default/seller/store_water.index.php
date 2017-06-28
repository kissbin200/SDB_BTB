<?php defined('In33hao') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />

<div class="tabmenu">
	<?php include template('layout/submenu');?>
</div>

<table class="search-form">
	<form method="get">
		<input type="hidden" name="act" value="store_water">
		<input type="hidden" name="op" value="index">
		<tr>
			<td>&nbsp;</td>
			<th>类型:</th>
			<td class="w160"> 
				<select name="type" class="text" id="">
					<option value="sb_pay" <?php if ($_GET['type'] == 'sb_pay') { ?>  selected  <?php }?> >水币支付(客户)</option>
					<option value="sb_back" <?php if ($_GET['type'] == 'sb_back') { ?>  selected  <?php }?>>水币退款(客户)</option>
					<option value="sb_rebate" <?php if ($_GET['type'] == 'sb_rebate') { ?>  selected  <?php }?>>水币返利(商家)</option>
					<option value="sb_top" <?php if ($_GET['type'] == 'sb_top') { ?>  selected  <?php }?>>水币充值(平台)</option>
					<option value="sb_activity" <?php if ($_GET['type'] == 'sb_activity') { ?>  selected  <?php }?>>水币充值活动(客户)</option>
					<option value="sb_cash" <?php if ($_GET['type'] == 'sb_cash') { ?>  selected  <?php }?>>水币提现(平台)</option>
				</select>
			 </td>
			<td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label></td>
		</tr>
	</form>
</table>

<table class="ncsc-default-table">
	<thead>
		<tr>
			<th class="w10">类型</th>
			<th class="w10">金额</th>
			<th class="w10">去向说明</th>
			<th class="w10">结算时间</th>
		</tr>
	</thead>
	<?php if (is_array($output['return_list']) && !empty($output['return_list'])) { ?>
	<tbody>
		<?php foreach ($output['return_list'] as $key => $val) { ?>
		<tr class="bd-line" >
			<td><?php echo $val['log_type'] ?></td>
			<td><?php if ($val['log_fee'] > 0) { ?> <font color="#f30">+<?php echo $val['log_fee'] ?></font> <?php }else{ ?> <font color="#28b779"><?php echo $val['log_fee'] ?></font> <?php }  ?></td>
			<td><?php echo $val['log_desc'] ?></td>
			<td><?php echo date('Y-m-d H:i:s',$val['log_add_time']) ?></td>
		</tr>
		<?php } ?>
		<?php } else { ?>
		<tr>
			<td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign">&nbsp;</i><span><?php echo $lang['no_record'];?></span></div></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<?php if (is_array($output['return_list']) && !empty($output['return_list'])) { ?>
		<tr>
			<td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
		</tr>
		<?php } ?>
	</tfoot>
</table>