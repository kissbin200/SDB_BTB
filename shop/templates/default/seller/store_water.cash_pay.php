<div class="eject_con">
	<div id="warning" class="alert alert-error"></div>
	<form method="post" target="_parent" action="index.php?act=store_water&op=cash_pay"enctype="multipart/form-data" id="brand_apply_form">
		<input type="hidden" name="form_submit" value="ok" />
		<input type="hidden" name="agree_fee" value="<?php echo $output['agree_fee'] ?>">
		<dl>
			<dt>提现金额 :</dt>
			<dd id="privilege_time_1">
				<input type="text" name="cash_fee" placeholder=" " value="" id="brand_initial" />
				<p>目前可以提现<?php if($output['agree_fee'] > 0){  ?> <?php echo abs($output['agree_fee']); ?> <?php }else{ ?> 0 <?php } ?></p>
			</dd>
		</dl>
		<div class="bottom">
			<label class="submit-border"><input type="submit" class="submit" <?php if($output['agree_fee'] <= 0) { ?> disabled="disabled" <?php } ?> value="<?php echo $lang['nc_submit'];?>"/></label>
		</div>
	</form>
</div>