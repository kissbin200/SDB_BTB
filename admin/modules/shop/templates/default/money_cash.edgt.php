<?php defined('In33hao') or exit('Access Invalid!');?>
<style>
.ncm-goods-gift {
	text-align: left;
}
.ncm-goods-gift ul {
		display: inline-block;
		font-size: 0;
		vertical-align: middle;
}
.ncm-goods-gift li {
		display: inline-block;
		letter-spacing: normal;
		margin-right: 4px;
		vertical-align: top;
		word-spacing: normal;
}
.ncm-goods-gift li a {
		background-color: #fff;
		display: table-cell;
		height: 30px;
		line-height: 0;
		overflow: hidden;
		text-align: center;
		vertical-align: middle;
		width: 30px;
}
.ncm-goods-gift li a img {
		max-height: 30px;
		max-width: 30px;
}
</style>
<div class="page">
	<div class="fixed-bar">
		<div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
			<div class="subject">
				<h3>水币提现管理</h3>
				<h5>水币提现管理</h5>
			</div>
		</div>
	</div>
	<div class="ncap-order-style">
		<div class="titile">
			<h3></h3>
		</div>
		<div class="ncap-order-flow"></div>

		<div class="ncap-order-details">
			<ul class="tabs-nav">
				<li class="current"><a href="javascript:void(0);">水币提现</a></li>
			</ul>
			<div class="tabs-panels">
				<div class="misc-info">
					<h4>下单</h4>
					<dl>
						<dt>订单号<?php echo $lang['nc_colon'];?></dt>
						<dd><?php echo $output['log_info']['ordns'];?></dd>
						<dt>提现金额<?php echo $lang['nc_colon'];?></dt>
						<dd><?php echo $output['log_info']['cash_fee'];?></dd>
						<dt>提交时间<?php echo $lang['nc_colon'];?></dt>
						<dd><?php echo date('Y-m-d H:i:s',$output['log_info']['add_time']);?></dd>
					</dl>
				</div>
				<div class="addr-note">
					<h4>水厂信息</h4>
					<dl>
						<dt>水厂名字<?php echo $lang['nc_colon'];?></dt>
						<dd><?php echo $output['store']['store_name'];?></dd>
						<dt>联系方式<?php echo $lang['nc_colon'];?></dt>
						<dd><?php echo $output['store']['mobile'];?></dd>
					</dl>
					<dl>
						<dt>现有水币<?php echo $lang['nc_colon'];?></dt>
						<dd><?php echo $output['store']['water_fee'];?></dd>
						<dt>现有余额<?php echo $lang['nc_colon'];?></dt>
						<dd><?php echo $output['store']['money_fee'];?></dd>
					</dl>
				</div>

				<div class="contact-info">
					<form action="" method="post">
						<input type="hidden" name="form_submit" value="ok" />
						<input type="hidden" name="orderid" value="<?php echo $output['log_info']['id'];?>">
						
						<?php if ($output['log_info']['status'] > 1) { ?>
							<dl>
								<dt>是否同意<?php echo $lang['nc_colon'];?></dt>
								<dd>
									<label for="item_state1"> <input type="radio" name="item_state" disabled="disabled" id="item_state1" <?php if ($output['log_info']['status'] == '2') { ?> checked="checked" <?php } ?> value="2">同意</label>
									<label for="item_state0"> <input type="radio" name="item_state" disabled="disabled" id="item_state0" <?php if ($output['log_info']['status'] == '3') { ?> checked="checked" <?php } ?> value="3">不同意</label>
								</dd>
							</dl>
							<dl>
								<dt>备注<?php echo $lang['nc_colon'];?></dt>
								<dd><textarea name="show_txt" rows="6" class="tarea" disabled="disabled" id="show_txt"><?php echo $output['log_info']['remark'];?></textarea></dd>
							</dl>
						<?}else{ ?>
							<h4>操作</h4>
							<dl>
								<dt>是否同意<?php echo $lang['nc_colon'];?></dt>
								<dd>
									<label for="item_state1"> <input type="radio" name="item_state" id="item_state1" value="2">同意</label>
									<label for="item_state0"> <input type="radio" name="item_state" id="item_state0" value="3">不同意</label>
								</dd>
							</dl>
							<dl>
								<dt>备注<?php echo $lang['nc_colon'];?></dt>
								<dd><textarea name="show_txt" rows="6" class="tarea" id="show_txt"></textarea></dd>
							</dl>
							<d>
								<dt> <input type="submit" value="确认" class="ncap-btn-big ncap-btn-green"></dt>
							</dl>
						<?php } ?>


					</form>
				</div>

			</div>
			
		
		</div>
	</div>
</div>
<script type="text/javascript">
		$(function() {
				$(".tabs-nav > li > a").mousemove(function(e) {
						if (e.target == this) {
								var tabs = $(this).parent().parent().children("li");
								var panels = $(this).parents('.ncap-order-details:first').children(".tabs-panels");
								var index = $.inArray(this, $(this).parents('ul').find("a"));
								if (panels.eq(index)[0]) {
										tabs.removeClass("current").eq(index).addClass("current");
									 panels.addClass("tabs-hide").eq(index).removeClass("tabs-hide");
								}
						}
				});
		});
</script>
