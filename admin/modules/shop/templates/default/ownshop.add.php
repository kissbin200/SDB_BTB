<?php defined('In33hao') or exit('Access Invalid!');?>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title"><a class="back" href="index.php?act=ownshop&op=list" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
			<div class="subject">
				<h3>自营供应商 - <?php echo $lang['nc_new'];?>"</h3>
				<h5>自营供应商相关设置与管理</h5>
			</div>
		</div>
	</div>
	<!-- 操作说明 -->
	<div class="explanation" id="explanation">
		<div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
			<h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
			<span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
		<ul>
			<li>平台可以在此处添加供应商，新增的供应商默认为开启状态</li>
			<li>新增供应商默认绑定所有经营类目，可以手动设置绑定其经营类目</li>
			<li>新增供应商将自动创建供应商账号（用于供应商中心）</li>
		</ul>
	</div>
	<form id="store_form" method="post">
		<input type="hidden" name="form_submit" value="ok" />
		<input type="hidden" name="store_id" value="<?php echo $output['store_array']['store_id']; ?>" />
		<div class="ncap-form-default">
			<dl class="row">
				<dt class="tit">
					<label for="store_name"><em>*</em>供应商名称</label>
				</dt>
				<dd class="opt">
					<input type="text" value="" id="store_name" name="store_name" class="input-txt" />
					<span class="err"></span>
					<p class="notic"></p>
				</dd>
			</dl>
			<dl class="row">
				<dt class="tit">
					<label for="member_name"><em>*</em>商家账号</label>
				</dt>
				<dd class="opt">
					<input type="text" value="" id="member_name" name="member_name" class="input-txt" />
					<span class="err"></span>
					<p class="notic">请使用手机号码。</p>
				</dd>
			</dl>
			<dl class="row">
				<dt class="tit">
					<label for="seller_name"><em>*</em>手机号</label>
				</dt>
				<dd class="opt">
					<input type="text" value="" id="seller_name" name="seller_name" class="input-txt"  readonly="readonly" />
					<span class="err"></span>
					<p class="notic">无需输入，自动复制手机号码</p>
				</dd>
			</dl>
			
			<dl class="row">
				<dt class="tit">
					<label for="member_passwd"><em>*</em>登录密码</label>
				</dt>
				<dd class="opt">
					<input type="password" value="" id="member_passwd" name="member_passwd" class="input-txt" />
					<span class="err"></span>
					<p class="notic"></p>
				</dd>
			</dl>
			<div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
		</div>
	</form>
</div>
<script type="text/javascript">
$(function(){
		//按钮先执行验证再提交表单
		$("#submitBtn").click(function(){
				if($("#store_form").valid()){
						$("#store_form").submit();
				}
		});

		$('#store_form').validate({
				errorPlacement: function(error, element){
						var error_td = element.parent('dd').children('span.err');
						error_td.append(error);
				},
				rules : {
						store_name: {
								required : true,
								remote   : '<?php echo urlAdminShop('ownshop', 'ckeck_store_name')?>'
						},
						member_name: {
								required : true,
								minlength : 3,
								maxlength : 15,
								remote   : {
										url : 'index.php?act=ownshop&op=check_member_name',
										type: 'get',
										data:{
												member_name : function(){
														return $('#member_name').val();
												}
										}
								}
						},
						seller_name: {
								required : true,
								minlength : 3,
								maxlength : 15,
								remote   : {
										url : 'index.php?act=ownshop&op=check_seller_name',
										type: 'get',
										data:{
												seller_name : function(){
														return $('#seller_name').val();
												}
										}
								}
						},
						member_passwd : {
								required : true,
								minlength: 6
						}
				},
				messages : {
						store_name: {
								required : '<i class="fa fa-exclamation-circle"></i>请输入店铺名称',
								remote   : '<i class="fa fa-exclamation-circle"></i>店铺名称已存在'
						},
						member_name: {
								required : '<i class="fa fa-exclamation-circle"></i>请输入会员账号',
								minlength: '<i class="fa fa-exclamation-circle"></i>会员账号最短为3位',
								maxlength: '<i class="fa fa-exclamation-circle"></i>会员账号最长为15位',
								remote   : '<i class="fa fa-exclamation-circle"></i>此名称已被其它店铺会员占用，请重新输入'
						},
						seller_name: {
								required : '<i class="fa fa-exclamation-circle"></i>请输入商家账号',
								minlength: '<i class="fa fa-exclamation-circle"></i>商家账号最短为3位',
								maxlength: '<i class="fa fa-exclamation-circle"></i>商家账号最长为15位',
								remote  : '<i class="fa fa-exclamation-circle"></i>此名称已被其它店铺占用，请重新输入'
						},
						member_passwd : {
								required : '<i class="fa fa-exclamation-circle"></i>请输入登录密码',
								minlength: '<i class="fa fa-exclamation-circle"></i>登录密码长度不能小于6'
						}
				}
		});

	$("#member_name").blur(function(){
		console.log('123')
		$("#seller_name").val($(this).val())
	})
});
</script> 
