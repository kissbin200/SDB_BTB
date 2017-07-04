<?php defined('In33hao') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
	<?php include template('layout/submenu');?>
</div>
<table class="search-form">
	<form method="get">
		<input type="hidden" name="act" value="store_vip">
		<input type="hidden" name="op" value="sblist">
		<tr>
			<td>&nbsp;</td>
			<th>时间:</th>
			<td class="w160"> 
				<input type="text" class="w80 text" data-dp="1" name="moon" value=" <?php echo $_GET['moon'] ?> ">
			 </td>
			<td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="查询" /></label></td>
		</tr>
	</form>
</table>

<table class="ncsc-default-table">
	<thead>
		<tr>
			<th class="w150">会员名</th>
			<th>赠送数量</th>
			<th>赠送时间</th>
			<th>领取时间</th>
			<th class="w100">状态</th>
		</tr>
	</thead>
	<tbody>
		<?php if (!empty($output['list'])) { ?>
		<?php foreach($output['list'] as $val) { ?>
		<tr class="bd-line">
			<td><?php echo $val['member_name'] ?></td>
			<td><?php echo $val['ago'] ?></td>
			<td> <?php echo date('Y-m-d',$val['add_time']) ?></td>
			<td><?php if ($val['stutas'] == '1') {  ?> - <?php }else{ ?> <?php echo date('Y-m-d',$val['give_time']) ?> <?php } ?> </td>
			<td> <?php if ($val['stutas'] == '1') {  ?> 未领取 <?php }else{ ?> 已领取 <?php } ?> </td>
		</tr>
		<?php } ?>
		<?php } else { ?>
		<tr>
			<td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<?php if (!empty($output['list'])) { ?>
		<tr>
			<td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
		</tr>
		<?php } ?>
	</tfoot>
</table>
<script type="text/javascript">
	$(function() {
		$("input[data-dp='1']").datepicker({dateFormat: 'yy-mm'});
	})
</script>