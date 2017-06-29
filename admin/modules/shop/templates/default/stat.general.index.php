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
			<h3><!-- <?php echo @date('Y-m-d',$output['stat_time']);?> --><?php if ($_GET['search_year'] != '' && $_GET['search_moon'] != '') { ?><?php echo $_GET['search_year']; ?>至<?php echo $_GET['search_moon']; ?>的情报 <?php }else{ ?> 至今新情报 <?php } ?>【下单量、下单金额、下单客户数、平均客单价】</h3>
			<!-- <select name="search_year" class="search_year">
				<?php for ($i=2015; $i < 2026 ; $i++) {  ?>
					<option value="<?php echo $i ?>" <?php if ($_GET['search_year'] == $i) { ?> selected = "selected" <?php } ?>  ><?php echo $i ?>年</option>
				<?php } ?>
			</select>
			<select name="search_moon" id="search" class="search_moon">
				<?php for ($i=1; $i < 13 ; $i++) {  ?>
					<option value="<?php echo $i ?>" <?php if ($_GET['search_moon'] == $i) { ?> selected = "selected" <?php } ?> ><?php echo $i ?>月</option>
				<?php } ?>
			</select> -->
			<input class="input-txt search_year" type="text" name="starttime" id="starttime" style="width: 120px !important;" value="<?php echo $_GET['search_year']; ?>">
			<input class="input-txt search_moon" type="text" name="endtime" id="endtime" style="width: 120px !important;" value="<?php echo $_GET['search_moon']; ?>">
			<input type="button" value="确定" class="search" id="search">
			<input type="submit" value="昨日" class="yesterday" id="yesterday">
			<input type="submit" value="前日" class="beforeyesterday" id="beforeyesterday">
			<input type="submit" value="最近7日" class="Sevenday" id="Sevenday">
			<input type="submit" value="最近30日" class="Thirtyday" id="Thirtyday">


		</div>
		<dl class="row">
			<dd class="opt">
				<ul class="nc-row">
					<li title="下单金额：<?php echo $output['statnew_arr']['orderamount'];?>元">
						<h4>下单金额</h4>
						<h6>有效订单的总金额(元)</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['orderamount'];?>" data-speed="1500"></h2>
					</li>
					<li title="下单店东数：<?php echo $output['statnew_arr']['ordermembernum'];?>">
						<h4>下单店东数</h4>
						<h6>有效订单的下单店东总数</h6>
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
					<li title="新增店东：<?php echo $output['statnew_arr']['newmember'];?>">
						<h4>新增店东</h4>
						<h6>期间内新注册店东总数</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['newmember'];?>" data-speed="1500"></h2>
					</li>
					<li title="店东数量：<?php echo $output['statnew_arr']['membernum'];?>">
						<h4>店东数量</h4>
						<h6>平台所有店东的数量</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['membernum'];?>" data-speed="1500"></h2>
					</li>
					<li title="新增供应商：<?php echo $output['statnew_arr']['newstore'];?>">
						<h4>新增供应商</h4>
						<h6>期间内新注册供应商总数</h6>
						<h2 class="timer" id="count-number"  data-to="<?php echo $output['statnew_arr']['newstore'];?>" data-speed="1500"></h2>
					</li>
					<li title="供应商数量：<?php echo $output['statnew_arr']['storenum'];?>">
						<h4>供应商数量</h4>
						<h6>平台所有供应商的数量</h6>
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
	<div class="ncap-stat-chart">
		<div class="title">
			<h3><?php echo @date('Y-m-d',$output['stat_time']);?>销售走势</h3>
		</div>
		<div id="container" class=" " style="height:400px"></div>
	</div>
	<div style="width:49%; margin-right:1%; float: left;">
		<table class="flex-table">
			<thead>
				<tr>
					<th width="24" align="center" class="sign"><i class="ico-check"></i></th>
					<th width="60" align="center" class="handle-s">操作</th>
					<th width="60" align="center">序号</th>
					<th width="120" align="left">供应商名称</th>
					<th width="60" align="center">下单金额</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach((array)$output['storetop30_arr'] as $k=>$v){ ?>
				<tr>
					<td class="sign"><i class="ico-check"></i></td>
					<td class="handle-s"><span>--</span></td>
					<td><?php echo $k+1;?></td>
					<td><?php echo $v['store_name'];?></td>
					<td><?php echo ncPriceFormat($v['orderamount']);?></td>
					<td></td>
				</tr>
				<?php } ?>
				<?php if(empty($output['storetop30_arr'])){ ?>
				<tr>
					<td class="no-data" colspan="100"><i class="fa fa-exclamation-triangle"></i><?php echo $lang['nc_no_record'];?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div style="width:50%; float: left;">
		<table class="flex-table2">
			<thead>
				<th width="24" align="center" class="sign"><i class="ico-check"></i></th>
				<th width="60" align="center" class="handle-s">操作</th>
				<th width="60" align="center">序号</th>
				<th width="250" align="left">商品名称</th>
				<th width="60" align="center">销量</th>
				<th></th>
					</thead>
			<tbody>
				<?php foreach((array)$output['goodstop30_arr'] as $k=>$v){ ?>
				<tr>
					<td class="sign"><i class="ico-check"></i></td>
					<td class="handle-s"><span>--</span></td>
					<td><?php echo $k+1;?></td>
					<td><a href='<?php echo urlShop('goods', 'index', array('goods_id' => $v['goods_id']));?>' target="_blank"><?php echo $v['goods_name'];?></a></td>
					<td><?php echo $v['ordergoodsnum'];?></td>
					<td></td>
				</tr>
				<?php } ?>
				<?php if(empty($output['goodstop30_arr'])){ ?>
				<tr>
					<td class="no-data" colspan="100"><i class="fa fa-exclamation-triangle"></i><?php echo $lang['nc_no_record'];?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/jquery.numberAnimation.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/highcharts.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/statistics.js"></script>
<script>
$(function () {
	//同步加载flexigrid表格
	$('.flex-table').flexigrid({
		height:'auto',// 高度自动
		usepager: false,// 不翻页
		striped:false,// 不使用斑马线
		resizable: false,// 不调节大小
		reload: false,// 不使用刷新
		columnControl: false,// 不使用列控制
		title:'7日内供应商销售TOP30'
		});
	$('.flex-table2').flexigrid({
		height:'auto',// 高度自动
		usepager: false,// 不翻页
		striped:false,// 不使用斑马线
		resizable: false,// 不调节大小
		reload: false,// 不使用刷新
		columnControl: false,// 不使用列控制
		title:'7日内商品销售TOP30'
		});

	$('#container').highcharts(<?php echo $output['stattoday_json'];?>);

	$('#search').click(function(){
		var search_year = $('.search_year').val();
		var search_moon = $('.search_moon').val();
		var url = "index.php?act=stat_general&op=index&search_year="+ search_year + "&search_moon=" + search_moon ;
		location.href = url;
	});
	$('#yesterday').click(function(){
		var day = new Date();
		var search_year = day.getFullYear() + "-" + (day.getMonth()+1) + "-" +  (day.getDate()-1) ;
		var url = "index.php?act=stat_general&op=index&search_year="+ search_year + "&search_moon=" + search_year ;
		location.href = url;
	});
	$('#beforeyesterday').click(function(){
		var day = new Date();
		var search_year = day.getFullYear() + "-" + (day.getMonth()+1) + "-" +  (day.getDate()-2) ;
		var url = "index.php?act=stat_general&op=index&search_year="+ search_year + "&search_moon=" + search_year ;
		location.href = url;
	});
	$('#Sevenday').click(function(){
		var day = new Date();
		var search_year = day.getFullYear() + "-" + (day.getMonth()+1) + "-" +  (day.getDate()-7) ;
		var search_moon = day.getFullYear() + "-" + (day.getMonth()+1) + "-" +  (day.getDate()) ;
		var url = "index.php?act=stat_general&op=index&search_year="+ search_year + "&search_moon=" + search_moon ;
		location.href = url;
	});
	$('#Thirtyday').click(function(){
		var day = new Date();
		var search_year = day.getFullYear() + "-" + (day.getMonth()) + "-" +  (day.getDate()) ;
		var search_moon = day.getFullYear() + "-" + (day.getMonth()+1) + "-" +  (day.getDate()) ;
		var url = "index.php?act=stat_general&op=index&search_year="+ search_year + "&search_moon=" + search_moon ;
		location.href = url;
	});
	$(document).ready(function(){
		$("#starttime").datepicker({dateFormat: 'yy-mm-dd'});
		$("#endtime").datepicker({dateFormat: 'yy-mm-dd'});
	})
});
</script>