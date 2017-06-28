<div class="eject_con">
	<div id="warning" class="alert alert-error"></div>
	<form method="post" target="_parent" action="index.php?act=store_password_correct&op=correct"enctype="multipart/form-data" id="brand_apply_form">
		<input type="hidden" name="form_submit" value="ok" />
		<dl>
			<dt>原始密码 :</dt>
			<dd id="privilege_time_1">
				<input type="password" name="old_pwd" placeholder=" " value="" id="brand_initial" />
			</dd>
		</dl>
		<dl>
			<dt>新密码 :</dt>
			<dd id="privilege_time_2">
				<input type="password" name="new_pwd" placeholder=" " value="" id="brand_initial" />
			</dd>
		</dl>
		<dl>
			<dt>再次输入新密码 :</dt>
			<dd id="privilege_time_3">
				<input type="password" name="age_pwd" placeholder=" " value="" id="brand_initial" />
			</dd>
		</dl>
		<div class="bottom">
			<label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"/></label>
		</div>
	</form>
</div>