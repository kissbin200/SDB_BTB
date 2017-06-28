<?php defined('In33hao') or exit('Access Invalid!');?>
<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<div class="subject">
				<h3><?php echo $lang['nc_statgeneral'];?></h3>
				<h5>商城统计最新情报及相关设置</h5>
			</div>
			<?php echo $output['top_link'];?> </div>
	</div>
	<div class="explanation" id="explanation">
		<div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
			<h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
			<span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
		<ul>
			<li><?php echo $lang['stat_validorder_explain'];?></li>
		</ul>
	</div>
	<div class="ncap-form-all ncap-stat-general">
		<div class="title">
			<h3><?php echo @date('Y-m-d',$output['stat_time']);?>最新情报</h3>
		</div>
		<dl class="row">
			<dd class="opt">
				<ul class="nc-row">
					<li title="下单金额：<?php echo $output['statnew_arr']['orderamount'];?>元">
						<h4>下单金额</h4>
						<h6>有效订单的总金额(元)</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['orderamount'];?>" data-speed="1500"></h2>
					</li>
					<li title="下单会员数：<?php echo $output['statnew_arr']['ordermembernum'];?>">
						<h4>下单会员数</h4>
						<h6>有效订单的下单会员总数</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['ordermembernum'];?>" data-speed="1500"></h2>
					</li>
					<li title="下单量：<?php echo $output['statnew_arr']['ordernum'];?>">
						<h4>下单量</h4>
						<h6>有效订单的总数量</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['ordernum'];?>" data-speed="1500"></h2>
					</li>
					<li title="下单商品数：<?php echo $output['statnew_arr']['ordergoodsnum'];?>">
						<h4>下单商品数</h4>
						<h6>有效订单包含的商品总数量</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['ordergoodsnum'];?>" data-speed="1500"></h2>
					</li>
					<li title="平均价格：<?php echo $output['statnew_arr']['priceavg'];?>元">
						<h4>平均价格</h4>
						<h6>有效订单包含商品的平均单价（元）</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['priceavg'];?>" data-speed="1500"></h2>
					</li>
					<li title="平均客单价：<?php echo $output['statnew_arr']['orderavg'];?>元">
						<h4>平均客单价</h4>
						<h6>有效订单的平均每单的金额（元）</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['orderavg'];?>" data-speed="1500"></h2>
					</li>
					<li title="新增会员：<?php echo $output['statnew_arr']['newmember'];?>">
						<h4>新增会员</h4>
						<h6>期间内新注册会员总数</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['newmember'];?>" data-speed="1500"></h2>
					</li>
					<li title="会员数量：<?php echo $output['statnew_arr']['membernum'];?>">
						<h4>会员数量</h4>
						<h6>平台所有会员的数量</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['membernum'];?>" data-speed="1500"></h2>
					</li>
					<li title="新增店铺：<?php echo $output['statnew_arr']['newstore'];?>">
						<h4>新增店铺</h4>
						<h6>期间内新注册店铺总数</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['newstore'];?>" data-speed="1500"></h2>
					</li>
					<li title="店铺数量：<?php echo $output['statnew_arr']['storenum'];?>">
						<h4>店铺数量</h4>
						<h6>平台所有店铺的数量</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['storenum'];?>" data-speed="1500"></h2>
					</li>
					<li title="新增商品：<?php echo $output['statnew_arr']['newgoods'];?>">
						<h4>新增商品</h4>
						<h6>期间内新增商品总数</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['newgoods'];?>" data-speed="1500"></h2>
					</li>
					<li title="商品数量：<?php echo $output['statnew_arr']['goodsnum'];?>">
						<h4>商品数量</h4>
						<h6>平台所有商品的数量</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['goodsnum'];?>" data-speed="1500" ></h2>
					</li>
				</ul>
		</dl>
	</div>
</div>