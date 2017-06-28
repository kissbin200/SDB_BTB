<div class="top">
	<ul >
		<li> <span>余额 <i><?php echo $output['StoreInfo']['money_fee']; ?></i>元 </span> <a href="index.php?act=store_bill&op=cash">提现</a> </li>
		<li> <span>水币 <i><?php echo $output['StoreInfo']['water_fee']; ?></i> 元</span> <a href="index.php?act=store_water&op=cash">提现</a> </li>
	</ul>
</div>

<div class="box">
	<div class="title">余额</div>
	<ul>
		<li>

			<span class="item_one">可提现余额 </span>
			<span class="item_one"><?php echo abs($output['count_money']['agree_fee']); ?>元</span>
		</li>
		<li>已提现金额 <i><?php echo abs($output['count_money']['cash']); ?></i>元</li>
		<li>待转帐金额 <i><?php echo abs($output['count_money']['cash_on']); ?></i>元</li>
	</ul>
</div>

<div class="box">
	<div  class="title">水币</div>
	<ul>
		<li>可提现水币 <i><?php if($output['count_sb']['agree_fee'] > 0){  ?> <?php echo abs($output['count_sb']['agree_fee']); ?> <?php }else{ ?> 0 <?php } ?></i>元 </li>
		<li>欠平台数量 <i><?php echo abs($output['count_sb']['top']); ?></i>元</li>
		<li>已提现水币总数 <i><?php echo abs($output['count_sb']['cash']); ?></i>元</li>
	</ul>
	<ul>
		<li>待转帐水币 <i><?php echo abs($output['count_sb']['cash_on']); ?></i>元</li>
		<li>政策反利扣水币总数 <i><?php echo abs($output['count_sb']['rebate']); ?></i>元</li>
		<li>充值赠送扣水币总数 <i><?php echo abs($output['count_sb']['activity']); ?></i>元</li>
	</ul>
</div>

<style>
	.top{
		border-top: 1px solid #f0f0f0;
		margin-bottom: 20px;
	}
	.top li{
		border-bottom: 1px solid #f0f0f0;
		border-left: 1px solid #f0f0f0;
		border-right: 1px solid #f0f0f0;
		height: 50px;
		line-height: 50px;
		padding: 0 10px;
	}
	.top li a{
		float: right;
		width: 80px;
		border:1px solid #999;
		height: 30px;
		line-height: 30px;
		margin-top: 10px;
		text-align: center;
		border-radius: 5px;
	}
	.box{
		margin-bottom: 20px;
	}
	.box .title{
		height: 30px;
		width: 100%;
		background:#36BC9B ;
		line-height: 30px;
		padding: 0 10px;
		color: white;
		box-sizing:border-box;
	}
	.box ul {
		height: 40px;
		line-height: 40px;
		border-bottom: 1px solid #f0f0f0;
		border-left: 1px solid #f0f0f0;
		border-right: 1px solid #f0f0f0;
		overflow: hidden;
	}
	 .boxul:nth-child(1){
		border-top: 1px solid #f0f0f0;
	}
	.box ul li{
		display: inline-block;
		width: 33%;
		float: left;
		padding: 0 10px;
		box-sizing:border-box;
	}
	.box ul li:nth-child(1), ul li:nth-child(2){
		border-right: 1px #f0f0f0 solid;
	}
</style>