<div class="eject_con">
	<div id="warning" class="alert alert-error"></div>
	<form method="post" target="_parent" action="index.php?act=store_brokerage&op=pay"enctype="multipart/form-data" id="brand_apply_form">
		<input type="hidden" name="form_submit" value="ok" />
		<input type="hidden" name="secret" value="<?php echo $output['secret']; ?>" />
		
		<dl>
			<dt>登录密码 :</dt>
			<dd id="privilege_time_1">
				<input type="password" name="login_pwd" placeholder="请输入登录密码" value="" id="brand_initial" />
			</dd>

		</dl>
		

		<div class="bottom">
			<label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"/></label>
		</div>
	</form>
</div>