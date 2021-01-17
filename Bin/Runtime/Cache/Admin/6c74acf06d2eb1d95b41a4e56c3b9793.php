<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
	<title><?php echo GC('web_title');?>管理后台-用户订单</title>
		<link rel="stylesheet" type="text/css" href="/Public/admin_new/css/styles.css?v=1.2.0">
	<link rel="stylesheet" type="text/css" href="/Public/admin_new/css/bootstrap.min.css?v=1.2.0">
	<link rel="stylesheet" type="text/css" href="/Public/admin_new/css/core.min.css?v=1.2.0">
	<link rel="stylesheet" type="text/css" href="/Public/admin_new/css/components.min.css?v=1.2.0">
	<link rel="stylesheet" type="text/css" href="/Public/admin_new/css/xd_list.css?v=1.2.0">
	<link rel="stylesheet" type="text/css" href="/Public/admin_new/css/qrcode.css?v=1.2.0">
	<link rel="stylesheet" type="text/css" href="/Public/extends/layui/css/layui.css">
	<link rel="stylesheet" type="text/css" href="/Public/admin_new/css/css_common.css?v=1.2.0"> <!-- 分页必用，这个一定放在样式文件的最后 -->

	<script type="text/javascript" src="/Public/js/jquery/jquery.2.1.4.min.js"></script>
	<script type="text/javascript" src="/Public/admin_new/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/Public/admin_new/js/app.js"></script>
	<script type="text/javascript" src="/Public/extends/laydate/laydate.js"></script>
	<script type="text/javascript" src="/Public/extends/layer/layer.js"></script>
	<script type="text/javascript" src="/Public/extends/layui/layui.js"></script>
	<script type="text/javascript" src="/Public/extends/echarts/4.7.0/echarts.min.js"></script> <!-- 图表必用 -->
	<!-- UE编辑器 -->
	<script type="text/javascript" src="/Public/extends/ueditor/1.4.3/ueditor.config.js"></script>
	<script type="text/javascript" src="/Public/extends/ueditor/1.4.3/ueditor.all.min.js"> </script>

	<script type="text/javascript" src="/Public/admin_new/js/common_admin.js"></script> <!-- 通用JS放这里，分页必用 -->
	<script type="text/javascript" src="/Public/admin_new/js/global.js"></script> <!-- 通用JS放这里 -->


</head>
<body>
<!-- header -->
<!-- Main navbar -->
<div class="navbar navbar-default header-highlight">
	<div class="navbar-header" style="text-align: center;padding: 20px 0;">
		<a style="width: 200px;display: block;" href="<?php echo U('Index/index');?>">
			<img src="<?php echo GC('logo');?>" style="width: 60px;height: 60px;">
			<div style="height: 40px;text-align: center; padding:10px;color: #fff;font-size: 18px;"><?php echo GC('web_title');?>
			</div>
		</a>
	</div>

	<div class="navbar-collapse collapse" id="navbar-mobile">
		<ul class="nav navbar-nav">
			<li>
				<ul class="breadcrumb">
					<li><i class="icon-home2 position-left"></i><?php echo GC('web_title');?></li>
					<li><?php echo ($cccname); ?></li>
				</ul>
			</li>
		</ul>

		<ul class="nav navbar-nav navbar-right">

			<li class="dropdown dropdown-user">
				<a class="dropdown-toggle" data-toggle="dropdown">
					<span><?php echo ($admin_info["username"]); ?></span>
					<i class="caret"></i>
				</a>

				<ul class="dropdown-menu dropdown-menu-right">

					<li><a href="<?php echo U('Admin/Index/changePwd');?>"><i class="icon-cog5"></i>修改密码</a></li>
					<li><a href="<?php echo U('Common/logout');?>"><i class="icon-switch2"></i>退出登录</a></li>
				</ul>
			</li>
		</ul>
	</div>
</div>
<!-- /main navbar -->

<!-- /header -->
<!-- Page container -->
<div class="page-container">
	<!-- Page content -->
	<div class="page-content">
		<!-- main sidebar -->
		<div class="sidebar sidebar-main">
	<div class="sidebar-content">

		<!-- Main navigation -->
		<div class="sidebar-category sidebar-category-visible">
			<div class="category-content no-padding">
				<ul class="navigation navigation-main navigation-accordion">

					<li><a href="<?php echo U('Index/index');?>"><i class="icon-home4"></i> <span>首页</span></a></li>
					<?php if(is_array($menu_list)): foreach($menu_list as $k=>$vo): if(!empty($vo["sub_menu"])): ?><li
							<?php if(strtolower($controllerName) == 'Index'): elseif(strtolower($controllerName) == strtolower($k)): ?>
								class="active"<?php endif; ?>
							>
							<a href="javascript:;"><i class="<?php echo ($vo["Icon"]); ?>"></i> <span><?php echo ($vo["name"]); ?></span></a>
							<ul>
								<?php if(is_array($vo["sub_menu"])): foreach($vo["sub_menu"] as $kk=>$vv): ?><li>
										<a href="/index.php/Admin/<?php echo ($vv["control"]); ?>/<?php echo ($vv["act"]); ?>/<?php echo ($vv["user_type"]); ?>"><?php echo ($vv["name"]); ?></a>
									</li><?php endforeach; endif; ?>
							</ul>
							</li><?php endif; endforeach; endif; ?>
				</ul>
			</div>
		</div>
		<!-- /main navigation -->

	</div>
</div>
<!-- /main sidebar -->
		<!-- /main sidebar -->

		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Content area -->
			<div class="content" style="padding-top: 10px;">
				<!-- Pagination types -->
				<div class="v-container">
					<div class="alert alert-tips alert-dismissible" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
								aria-hidden="true">×</span></button>
						本页面订单列表，目前是集合所有订单种类，可查询以区分。
					</div>

					<div class="panel panel-flat">
						<div class="panel-heading">

							<form class="v-filter-container" action="<?php echo U('index');?>" method="get" id="formHandle">

								<div class="filter-fields-wrap">
									<div class="filter-item clearfix">
										<div class="filter-item__field">
											<div class="v__control-group">
												<label class="v__control-label">订单编号</label>
												<div class="v__controls">
													<input type="text" name="order_no" value="<?php echo ($_REQUEST['order_no']); ?>"
													       class="v__control_input" placeholder="请输入订单编号"
													       autocomplete="off">
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">商品名称</label>
												<div class="v__controls">
													<input type="text" class="v__control_input" name="goods_name"
													       value="<?php echo ($_REQUEST['goods_name']); ?>" placeholder="请输入商品名称"
													       autocomplete="off">
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">快递单号</label>
												<div class="v__controls">
													<input type="text" class="v__control_input" name="tracking_number"
													       value="<?php echo ($_REQUEST['tracking_number']); ?>" placeholder="请输入快递单号"
													       autocomplete="off">
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">会员信息</label>
												<div class="v__controls">
													<input type="text" class="v__control_input" name="content"
													       value="<?php echo ($_REQUEST['content']); ?>" autocomplete="off"
													       placeholder="手机号码/会员ID/用户名">
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">支付方式</label>
												<div class="v__controls">
													<select class="v__control_input" name="payment_type">
														<option value="">全部</option>

														<option value="yepay"
														<?php if($_REQUEST['payment_type'] == 'yepay' ): ?>selected<?php endif; ?>
														>佣金支付</option>

														<!--<option value="wxpay"
														<?php if($_REQUEST['payment_type'] == 'wxpay' ): ?>selected<?php endif; ?>
														>微信APP</option>-->

														<option value="wxpay"
														<?php if($_REQUEST['payment_type'] == 'wxpay' ): ?>selected<?php endif; ?>
														>微信支付</option>

<!--														<option value="price_pay"-->
<!--														<?php if($_REQUEST['payment_type'] == 'price_pay' ): ?>-->
<!--															selected-->
<!--<?php endif; ?>-->
<!--														>金额支付</option>-->

														<option value="wait_pay"
														<?php if($_REQUEST['payment_type'] == 'wait_pay' ): ?>selected<?php endif; ?>
														>积分支付</option>

														<!--<option value="wxh5"
														<?php if($_REQUEST['payment_type'] == 'wxh5' ): ?>selected<?php endif; ?>
														>微信WAP</option>

														<option value="wxmini"
														<?php if($_REQUEST['payment_type'] == 'wxmini' ): ?>selected<?php endif; ?>
														>微信小程序</option>

														<option value="wxnative"
														<?php if($_REQUEST['payment_type'] == 'wxnative' ): ?>selected<?php endif; ?>
														>微信扫一扫</option>

														<option value="wxqrcode"
														<?php if($_REQUEST['payment_type'] == 'wxqrcode' ): ?>selected<?php endif; ?>
														>微信付款码</option>


														<option value="alipay"
														<?php if($_REQUEST['payment_type'] == 'alipay' ): ?>selected<?php endif; ?>
														>支付宝APP</option>-->

<!--														<option value="aliwap"-->
<!--														<?php if($_REQUEST['payment_type'] == 'aliwap' ): ?>-->
<!--															selected-->
<!--<?php endif; ?>-->
<!--														>支付宝WAP</option>-->

														<!--<option value="alimini"
														<?php if($_REQUEST['payment_type'] == 'alimini' ): ?>selected<?php endif; ?>
														>支付宝小程序</option>

														<option value="alipc"
														<?php if($_REQUEST['payment_type'] == 'alipc' ): ?>selected<?php endif; ?>
														>支付宝PC</option>-->

														<option value="adminpay"
														<?php if($_REQUEST['payment_type'] == 'adminpay' ): ?>selected<?php endif; ?>
														>后台支付</option>

													</select>
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">订单状态</label>
												<div class="v__controls">
													<select class="v__control_input" name="order_status">
														<option value="-1"
														<?php if($_REQUEST['order_status'] == -1 ): ?>selected<?php endif; ?>
														>全部</option>
														<option value="-2"
														<?php if($_REQUEST['order_status'] == '-2' ): ?>selected<?php endif; ?>
														>待支付</option>
														<option value="1"
														<?php if($_REQUEST['order_status'] == 1 ): ?>selected<?php endif; ?>
														>待发货</option>
														<option value="2"
														<?php if($_REQUEST['order_status'] == 2 ): ?>selected<?php endif; ?>
														>待收货</option>
														<option value="3"
														<?php if($_REQUEST['order_status'] == 3 ): ?>selected<?php endif; ?>
														>已收货</option>
														<option value="4"
														<?php if($_REQUEST['order_status'] == 4 ): ?>selected<?php endif; ?>
														>取消(未支付)</option>
														<option value="5"
														<?php if($_REQUEST['order_status'] == 5 ): ?>selected<?php endif; ?>
														>评价完成</option>
														<option value="6"
														<?php if($_REQUEST['order_status'] == 6 ): ?>selected<?php endif; ?>
														>售后</option>
													</select>
												</div>
											</div>
											<!--<div class="v__control-group">
												<label class="v__control-label">订单类型</label>
												<div class="v__controls">
													<select class="v__control_input" name="order_type">
														<option value="0">全部</option>
														<option value="0">商城订单</option>
														<option value="1">用户买单</option>
														<option value="2">商家报单</option>
														<option value="3">充值订单</option>
														<option value="4">代理报单</option>
														<option value="5">团购订单</option>
														<option value="6">秒杀订单</option>
														<option value="7">限时购</option>
														<option value="8">砍价订单</option>
														<option value="9">预售订单</option>
													</select>
												</div>
											</div>-->
											<div class="v__control-group">
												<label class="v__control-label">下单时间</label>
												<div class="v__controls v-date-input-control">
													<label for="add_time">
														<input type="text" class="v__control_input pr-30" id="add_time"
														       name="add_time" value="<?php echo ($_REQUEST['add_time']); ?>"
														       placeholder="请选择时间" autocomplete="off"
														       data-types="datetime" lay-key="1">
														<i class="icon icon-calendar"></i>
													</label>
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">付款时间</label>
												<div class="v__controls v-date-input-control">
													<label for="pay_time">
														<input type="text" class="v__control_input pr-30" id="pay_time"
														       name="pay_time" value="<?php echo ($_REQUEST['pay_time']); ?>"
														       placeholder="请选择时间" autocomplete="off"
														       data-types="datetime" lay-key="2">
														<i class="icon icon-calendar"></i>
													</label>
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">发货时间</label>
												<div class="v__controls v-date-input-control">
													<label for="shipping_time">
														<input type="text" class="v__control_input pr-30"
														       id="shipping_time" name="shipping_time"
														       value="<?php echo ($_REQUEST['shipping_time']); ?>" placeholder="请选择时间"
														       autocomplete="off" data-types="datetime" lay-key="3">
														<i class="icon icon-calendar"></i>
													</label>
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">收货时间</label>
												<div class="v__controls v-date-input-control">
													<label for="confirm_time">
														<input type="text" class="v__control_input pr-30"
														       id="confirm_time" name="confirm_time"
														       value="<?php echo ($_REQUEST['confirm_time']); ?>" placeholder="请选择时间"
														       autocomplete="off" data-types="datetime" lay-key="4">
														<i class="icon icon-calendar"></i>
													</label>
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">支付单号</label>
												<div class="v__controls">
													<input type="text" class="v__control_input" name="trade_no"
													       value="<?php echo ($_REQUEST['trade_no']); ?>" autocomplete="off"
													       placeholder="支付流水号/第三方微信、支付宝订单号">
												</div>
											</div>
										</div>
									</div>
									<div class="filter-item clearfix">
										<div class="filter-item__field">
											<div class="v__control-group">
												<label class="v__control-label"></label>
												<div class="v__controls">
													<input type="submit" value="搜索" class="btn btn-primary search"
													       style="min-width:100px;">
													<a class="btn btn-success ml-15" onclick="ExportExcel()">导出订单</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</form>
							<!--搜索框-->
							<div class="heading-elements">

							</div>
						</div>
						<div class="dataTables_wrapper no-footer">
							<table class="table datatable-tools-basic basic_table border_btm_ddd">
								<thead>
								<tr>
									<th style="width: 1px;"></th>
									<th class="col-xs-1">订单ID</th>
									<th class="col-xs-1">下单用户</th>
									<th class="text-center col-xs-3">商品</th>
									<th class="col-xs-3 text-center">订单金额</th>
									<th class="col-xs-2">状态</th>
									<th>操作</th>
								</tr>
								</thead>
								<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><table class="table v-table table-auto-center mb-10"
									       style="border: 1px solid #ddd;margin-top: 10px;">
										<tbody class="tbody_list" style="margin-bottom: 10px;">
										<tr style="background: #f9f9f9;border-top: 1px solid #ddd;">
											<td colspan="7" class="text-left bg-f9">
												<span class="mr-15-oList">创建时间：<?php echo ($vo["add_time_format"]); ?></span>&nbsp;&nbsp;
												<span class="mr-15-oList">订单号：<?php echo ($vo["order_no"]); ?></span>&nbsp;&nbsp;
												<span class="mr-15-oList">订单类型：<span class="label layui-bg-cyan"><?php echo ($vo["order_type_cn"]); ?> </span></span>
												<span class="mr-15-oList">支付时间：<?php echo ($vo['pay_time_format']?$vo['pay_time_format']:'N'); ?></span>&nbsp;&nbsp;
												<span class="mr-15-oList">支付流水号：<?php echo ($vo['serial_no']?$vo['serial_no']:'N'); ?></span>&nbsp;&nbsp;
												<span class="mr-15-oList">第三方订单号：<?php echo ($vo['trade_no']?$vo['trade_no']:'N'); ?></span>&nbsp;&nbsp;
											</td>
										</tr>
										<tr class="tt_color">
											<td style="width: 1px;">
												<input type="checkbox" name="much_id[]" value="<?php echo ($vo["id"]); ?>">
												<input type="hidden" name="shipping_code[]" value="<?php echo ($vo["id"]); ?>">
											</td>
											<td class="col-xs-1"><?php echo ($vo["id"]); ?></td>
											<td class="col-xs-1">
												<div class="ed_stand">
													<p>ID:<?php echo ($vo["user_id"]); ?></p>
													<p>姓名：<?php echo (get_user_field($vo["user_id"],realname)); ?></p>
													<p>手机号：<?php echo (get_user_field($vo["user_id"],phone)); ?></p>
												</div>
											</td>
											<td class="col-xs-3">
												<?php if(is_array($vo['goods'])): $i = 0; $__LIST__ = $vo['goods'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?><div class="id_stand">
														<img src="<?php echo ($sub["goods_thumb"]); ?>" style="width: 80px;height: 80px;"
														     class="stand_left"/>
														<div class="stand_right">
															<h6 title="<?php echo ($sub["goods_name"]); ?>"><?php echo ($sub["goods_name"]); ?></h6>
															<p>规格：<span><?php echo ($sub["goods_spec_price_name"]); ?></span></p>
															<p>数量：<span><?php echo ($sub["goods_num"]); ?>件</span></p>
															<p>小计：<span><?php echo ($sub["goods_total"]); ?>元</span></p>
														</div>
														<div class="clear"></div>
													</div><?php endforeach; endif; else: echo "" ;endif; ?>
											</td>
											<td class="text-center col-xs-3">
												<div class="dd_stand">
<!--													<p>满减：￥<?php echo ($vo["user_coupon_auto_money"]); ?></p>-->
													<p>优惠券：￥<?php echo ($vo["user_coupon_money"]); ?></p>
													<p>商品运费：￥<?php echo ($vo["total_express_fee"]); ?></p>
													<!--<p>后台改价：￥0.00</p>-->
													<p>商品总价：￥<?php echo ($vo["goods_total"]); ?></p>
													<p>实际付款：<span style="color: #DB5D5A;">￥<?php echo ($vo["total_commodity_price"]); ?></span>
													</p>
												</div>
											</td>
											<td class="col-xs-2">
												<div class="zt_stand">
													<p>订单状态：<span class="layui-bg-blue"><?php echo ($vo["order_status_cn"]); ?></span></p>
													<p>支付状态：<span class="layui-bg-green"><?php echo ($vo['pay_status_cn']); ?></span>
													</p>
													<p>支付方式：<span class="layui-bg-green"><?php echo ($vo['payment_type_cn']?$vo['payment_type_cn']:'无'); ?></span>
													</p>
													<p>发货状态：<span
															class="layui-bg-cyan"><?php echo ($vo['shipping_status_cn']); ?> </span>
													</p>
													<p>发货方式：<span
															class="layui-bg-cyan"><?php echo ($vo['shipping_type_cn']); ?> </span></p>
												</div>
											</td>
											<td class="text-center col-xs-2">
												<div class="tt_stand">
													<a title="查看详情" href="<?php echo U('Order/detail', array('id'=>$vo['id']));?>"
													   class="layui-btn layui-btn-sm layui-btn-normal btn_detail">详情</a>
													<a title="删除订单" href="<?php echo U('Order/delete', array('id'=>$vo['id']));?>"
													   class="layui-btn layui-btn-sm layui-btn-danger btn_detail"
													   onclick="return confirm('确定要删除吗?');">删除</a>
												</div>
											</td>
										</tr>
										<tr class="title-tr">
											<td colspan="7" class="text-left">
												<span class="mr-15-oList">收货人：<?php echo ($vo["consignee"]); ?></span>&nbsp;&nbsp;
												<span class="mr-15-oList">电话：<?php echo ($vo["phone"]); ?></span>&nbsp;&nbsp;
												<span class="mr-15-oList">地址：<?php echo ($vo["province_cn"]); echo ($vo["city_cn"]); echo ($vo["district_cn"]); echo ($vo["address"]); ?> </span>
											</td>
										</tr>
										</tbody>
									</table><?php endforeach; endif; else: echo "" ;endif; ?>
							</table>
							<!-- 分页 -->

							<div class="page_1028">

								<div class="pull-left">
									<!-- 这里一定要注意 整个页面只有，且只能在这里有 全选框，并且按钮的值有特殊要求 -->
									<!-- 如：5_1   横线前面数字 代表删除的哪些内容，1=用户，2商家，3代理，4订单，5商品，6商品三级分类，7日志 -->
									<!-- 如：5_1   横线后面数字 代理删除类型，0逻辑删除，1彻底删除 -->
									<a class="num"><label><input type="checkbox"
									                             onclick="$('input[name*=\'much_id\']').prop('checked', this.checked);">
										全选</label></a>
									<a onclick="delete_much('4_0', 0)" class="num">批量删除</a>
									<a onclick="delete_much('4_1', 1)" class="num">彻底删除</a>
								</div>

								<?php echo ($show); ?>
								<div class="clear"></div>
							</div>
							<style type="text/css">
								.page div span {
									float: left;
									padding: 0 5px;
									height: 25px;
									display: table;
									border: 1px solid #eee;
									line-height: 25px;
									text-align: center;
									margin: 5px;
								}
							</style>
							<!-- 分页 -->
						</div>
						<!-- /pagination types -->
					</div>
				</div>
				<!--end-->
				<!-- Footer -->
				<div class="footer text-muted">
	&copy; <?php echo GC('web_title');?>
</div>

				<!-- /footer -->
			</div>
			<!-- /content area -->
		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</div>
<!-- /page container -->

<script>
	//执行一个laydate实例
	//  laydate.render({
	//      elem: '#startTime' //指定元素
	//  });
	//  laydate.render({
	//      elem: '#endTime' //指定元素
	//  });
	//执行一个laydate实例
	//下单时间
	laydate.render({
		elem: '#add_time'
		, type: 'datetime'
		, range: true
	});
	//付款时间
	laydate.render({
		elem: '#pay_time'
		, type: 'datetime'
		, range: true
	});
	//发货时间
	laydate.render({
		elem: '#shipping_time'
		, type: 'datetime'
		, range: true
	});
	//完成时间
	laydate.render({
		elem: '#confirm_time'
		, type: 'datetime'
		, range: true
	});

	//根据搜索值导出表格
	function ExportExcel() {
		var url = "<?php echo U('orderDc');?>";
		var data = $("#formHandle").serialize();    // 获取整个搜索的条件数据
		var downUrl = "/index.php/Admin/Order/orderDc?"+data;
		location.href = downUrl;
		// $.ajax({
		// 	url: url,
		// 	type: 'post',
		// 	async: false,
		// 	data: data,
		// 	timeout: 120000,
		// 	dataType: 'json',
		// 	success: function (data) {
		// 		if (data.status == 0) {
		// 			var excelUrl = '/' + data.result;
		// 			window.open(excelUrl, '_blank').location;   // 在新标签页打开
		// 		} else {
		// 			alert(data.msg);
		// 			return;
		// 		}
		// 	}
		// });
	}

</script>


</body>
</html>