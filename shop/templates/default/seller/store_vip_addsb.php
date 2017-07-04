<div class="eject_con">
	<div id="warning" class="alert alert-error"></div>
	<form method="post" target="_parent" action="index.php?act=store_vip&op=seva_getsb"enctype="multipart/form-data" id="brand_apply_form">
		<input type="hidden" name="form_submit" value="ok" />
		<input type="hidden" name="vip_id" value="<?php echo $output['member_info']['member_id']; ?>" />
		
		<dl>
			<dt>目前用水币 :</dt>
			<dd id="privilege_time_1">
				<?php echo $output['member_info']['water_fee'] ?>
			</dd>
		</dl>
		<dl>
			<dt>赠予数额 :</dt>
			<dd id="privilege_time_2">
				<input type="text" name="getsb" placeholder="赠予水币数额" value="" id="brand_initial" />
			</dd>
		</dl>
		<dl>
			<dt>赠予说明 :</dt>
			<dd id="privilege_time_3">
				<textarea name="connit" id="" cols="30" rows="10" placeholder="瞎几把乱写点东西"></textarea>
			</dd>
		</dl>

		<div class="bottom">
			<label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"/></label>
		</div>
	</form>
</div>