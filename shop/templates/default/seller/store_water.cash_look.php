<div class="eject_con">
	<div id="warning" class="alert alert-error"></div>
	<dl>
		<dt>申请提现金额 :</dt>
		<dd id="privilege_time_1">
			<span><?php echo $output['info']['cash_fee'] ?></span>
		</dd>
	</dl>
	<dl>
		<dt>平台审核时间 :</dt>
		<dd id="privilege_time_1">
			<span> <?php if(empty($output['info']['adopt_time'])) {?>  <?php }else{ ?> <?php echo date('Y-m-d H:i:s',$output['info']['adopt_time']) ?> <?php } ?> </span>
		</dd>
	</dl>
	<dl>
		<dt>备注 :</dt>
		<dd id="privilege_time_1">
			<span><?php echo $output['info']['remark'] ?></span>
		</dd>
	</dl>
</div>