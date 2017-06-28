<?php defined('In33hao') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
	<?php include template('layout/submenu');?>
</div>
<form method="get" action="index.php" target="_self">
	<table class="search-form">
		<input type="hidden" name="act" value="store_brokerage" />
		<input type="hidden" name="op" value="index_one" />
		<input type="hidden" name="submit" value="ok"> 
		<input type="hidden" name="pay_t" value="1"> 
		<tr>
			<td class="tr">
				<div class="fr">
					<label class="submit-border"><input type="submit" class="submit" value="生成" /></label>
				</div>
				<div class="fr">
					<div class="fl" style="margin-right:3px;">
						<select name="pay_buyer_id" class="querySelect">
							<option value="" <?php echo $output['search_arr']['pay_buyer_id']==''?'selected':''; ?>>选择用户</option>
							<?php foreach ($output['vip'] as $val){?>
							<option value="<?php echo $val['buyer_id'] ?>" <?php echo $output['search_arr']['pay_buyer_id']==$val['buyer_id']?'selected':''; ?>> <?php echo $val['info']['member_name'] ?> </option>
							<?php } ?>
						</select>
					</div>
					<div class="fl" style="margin-right:3px;">
						<select name="pay_id" class="querySelect">
							<option value="">选择政策</option>
							<?php foreach ($output['onilnePrivilege'] as $val){?>
							<option value="<?php echo $val['id'] ?>" <?php echo $output['search_arr']['pay_id']==$val['id']?'selected':''; ?>> <?php echo $val['title'] ?> </option>
							<?php } ?>
						</select>
					</div>
					<div class="fl" style="margin-right:3px;">
						<select name="pay_privilege_id" class="querySelect">
							<option value="1">销量</option>
							<option value="2">金额</option>
						</select>
					</div>
					<input type="text" class="text w70" name="search_start_time" id="search_start_time" value=" <?php if (empty($output['search_arr']['start_time'])) { ?> <?php echo date('Y-m-d');?> <?php }else{ ?> <?php echo date('Y-m-d',$output['search_arr']['start_time']);?><?php } ?>" /> ~
					<input type="text" class="text w70" name="search_end_time" id="search_end_time" value=" <?php if (empty($output['search_arr']['end_time'])) { ?> <?php echo date('Y-m-d');?> <?php }else{ ?> <?php echo date('Y-m-d',$output['search_arr']['end_time']);?><?php } ?>" />
					<label class="add-on"><i class="icon-calendar"></i></label>
				</div>
			</td>
		</tr>
	</table>
</form>

<table class="ncsc-default-table">
	<thead>
		<tr>
			<th class="w10"></th>
			<th>客户名称</th>
			<th>产品名称</th>
			<th>数量与金额</th>
			<th>政策</th>
			<th>返利状态</th>
			<th>记录日期</th>
			<th>金额</th>
			<th>操作</th>
		</tr>
	</thead>
	<tbody>
		<?php if (!empty($output['list'])) { ?>
		<?php foreach($output['list'] as $val) { ?>
		<?php if ( $val['scale_fee']  > '1') { ?>
		<tr class="bd-line">
			<td></td>
			<td><?php echo $val['user']['member_name']; ?></td>
			<td><?php echo $val['goods']['goods_name']; ?></td>
			<td><?php echo $val['pay_num']; ?>件 / <?php echo $val['pay_fee']; ?> 元 </td>
			<td>
				用户等级： <?php echo $val['pol']['privilege_vip_name'] ?> <br />
				<?php foreach($val['pol']['privilege_val'] as $key => $vall) { ?>
					<?php echo $key ?> 以上，按  <?php echo $vall ?> %算 <br>
				<?php } ?>
			</td>
			<td><?php if($val['status'] == '1') { ?>未返利<?php } else { ?>已返利<?php } ?></td>
			<td><?php echo date('m',$val['add_time']); ?>月</td>
			<td><?php echo $val['scale_fee'] ?></td>
			<td><?php if($val['status'] == '1') { ?><!-- <a href="index.php?act=store_brokerage&op=pay&secret=<?php echo $val['secret'] ?>" class="ncbtn ncbtn-mint mt10">同意</a> -->
				<a href="javascript:void(0)" class="ncbtn ncbtn-mint" nc_type="dialog" dialog_title="请输入登录密码" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=store_brokerage&op=zb_pay&secret=<?php echo $val['secret'] ?>">同意</a>
			<?php } else { ?><?php } ?>
			</td> <!-- <?php echo $val['secret'] ?> -->
		</tr>
		<?php } ?>
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



<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/highcharts/highcharts.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script>
<script type="text/javascript">
	$('#search_end_time , #search_start_time').datepicker({dateFormat: 'yy-mm-dd'});
</script>