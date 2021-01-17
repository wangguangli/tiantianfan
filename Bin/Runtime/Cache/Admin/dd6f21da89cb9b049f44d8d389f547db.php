<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
	<title><?php echo GC('web_title');?>管理后台-提现记录</title>
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
						本页提现记录 总提现：<?php echo ((isset($all_money) && ($all_money !== ""))?($all_money):0.00); ?>，今日提现：<?php echo ((isset($today_money) && ($today_money !== ""))?($today_money):0.00); ?>

						<div style="float: right;padding-right: 20px;">代发账户余额：<span class="dfzhye">0</span>元
							<button id="queryBalance" class="layui-btn layui-btn-sm"
							        style="height: 23px;line-height: 23px;">查询
							</button>
						</div>

					</div>

					<div class="panel panel-flat">
						<div class="panel-heading">

							<form class="v-filter-container" action="<?php echo U('Account/tixian');?>" method="POST"
							      onsubmit="return sub()" id="formHandle">

								<div class="filter-fields-wrap">
									<div class="filter-item clearfix">
										<div class="filter-item__field">

											<input type="hidden" name="ischeck" value="<?php echo ($_REQUEST['ischeck']); ?>">

											<div class="v__control-group">
												<label class="v__control-label">类别</label>
												<div class="v__controls">
													<select name="cate" class="v__control_input" id="type" id="level">
														<option value="0" selected="">收款人</option>
														<option value="1">账户</option>
														<option value="2">用户类型</option>
													</select>
												</div>
											</div>

											<div class="v__control-group">
												<label class="v__control-label">关键字</label>
												<div class="v__controls">
													<input type="text" id="shop_id" name="data"
													       value="<?php echo ($_REQUEST['data']); ?>" class="v__control_input"
													       placeholder="请输入搜索内容" autocomplete="off">
												</div>
											</div>


											<div class="v__control-group">
												<label class="v__control-label">开始时间</label>
												<div class="v__controls">
													<input type="text" id="startTime" name="startTime"
													       value="<?php echo ($_REQUEST['startTime']); ?>" class="v__control_input"
													       placeholder="请输入开始时间" autocomplete="off">
												</div>
											</div>

											<div class="v__control-group">
												<label class="v__control-label">结束时间</label>
												<div class="v__controls">
													<input type="text" id="endTime" name="endTime"
													       value="<?php echo ($_REQUEST['endTime']); ?>" class="v__control_input"
													       placeholder="请输入结束时间" autocomplete="off">
												</div>
											</div>


											<div class="filter-item clearfix">
												<div class="filter-item__field">
													<div class="v__control-group">
														<label class="v__control-label"></label>
														<div class="v__controls">
															<!--													<a class="btn btn-primary search">搜索</a>-->
															<input type="submit" value="搜索"
															       class="btn btn-primary search"
															       style="width:100px;height: 35px;">
															<a class="btn btn-success ml-15" onclick="ExportExcel();">导出表格</a>
														</div>
													</div>
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
						<div class="dataTables_wrapper no-footer mini-space">
							<table class="table datatable-tools-basic basic_table">
								<thead>
								<tr>
									<th style="width:1px;"></th>
									<th class="">ID</th>
									<th class="">用户信息</th>
									<th class="">用户类型</th>
									<th class="">账户</th>
									<th class="">收款人</th>
									<th class="">账户</th>
									<th class="">账号</th>
									<th class="">提现数额</th>
									<th class="">手续费</th>
									<th class="">实际转账</th>
									<th class="">状态</th>
									<th class="">申请时间</th>
									<th class="">操作时间</th>

									<th class="text-center">操作</th>
								</tr>
								</thead>


								<?php if(is_array($list)): foreach($list as $key=>$i): ?><tr class="tt_color" style="">
										<td style="width: 1px;">
										</td>


										<td class=""><?php echo ($i["id"]); ?></td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo ($i["phone"]); ?>（<?php echo ($i["realname"]); ?>）</p>
											</div>
										</td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo ($i["user_role"]); ?></p>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<?php if($i['amt_type'] == 1): ?><p>佣金</p>
													<?php else: ?>
													<p>待返金额</p><?php endif; ?>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><?php echo ($i["name"]); ?></p>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><span style="color: #6161ef;">[<?php echo (tx_bank_type($i["bank_name"])); ?>]</span>
													<?php echo ($i["bank_name"]); ?></p>
											</div>
										</td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo ($i["cart_no"]); ?></p>
											</div>
										</td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo ($i["tx_money"]); ?></p>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><?php echo ($i["sxf_money"]); ?></p>
											</div>
										</td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo ($i["sjzz_money"]); ?></p>
											</div>
										</td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo (tx_status($i["ischeck"])); ?></p>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><?php echo ($i["cre_date"]); ?></p>
											</div>
										</td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo ($i["up_date"]); ?></p>
											</div>
										</td>


										<td class="text-center col-xs-2">
											<div class="tt_stand">
												<?php if(empty($i["ischeck"])): ?><a title="同意" href="<?php echo U('confirm_tixian', 'id=' . $i['id']);?>"
													   class="layui-btn layui-btn-sm layui-btn-normal btn_detail"
													   onClick="return confirm('该申请提现确认同意要汇款吗？')">同意</a>
													<a title="拒绝" href="<?php echo U('delete', 'id=' . $i['id']);?>"
													   class="layui-btn layui-btn-sm layui-btn-danger btn_detail"
													   onClick="return confirm('该申请提现确认拒绝汇款吗？')">拒绝</a><?php endif; ?>

											</div>
										</td>
									</tr><?php endforeach; endif; ?>
							</table>

							<div class="page_1028">
								<?php echo ($show); ?>
								<div class="clear"></div>
							</div>
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
	layui.use('laydate', function () {
			var laydate = layui.laydate;
			laydate.render({
				elem: '#startTime'
			})
			laydate.render({
				elem: '#endTime'
			})
		}
	);

	//判断数据
	function sub() {
		var sTime = $('#startTime').val();
		var eTime = $('#endTime').val();
		if (sTime && !eTime) {

			alert('开始/结束时间必须都有');
			return false;

		}
		if (!sTime && eTime) {
			alert('开始/结束时间必须都有');
			return false;
		}
		if (sTime && eTime) {
			return true;
		} else {
			return true;
		}
	}

	$("#queryBalance").on("click", function () {
		var url = "/Api/Userout/getMerchantMoney";
		$.ajax({
			url: url,
			dataType: "json",
			success: function (res) {
				if (res.status == 0) {
					var pay_money = res.result.pay_money;
					$(".dfzhye").text(pay_money);
				} else {
					layui.use('layer', function () {
						var layer = layui.layer;
						layer.msg(res.msg);
					});
				}
			}
		})
	});

	//根据搜索值导出表格
	function ExportExcel() {
		var url = "<?php echo U('export_excel');?>";
		var data = $("#formHandle").serialize();    // 获取整个搜索的条件数据,  formHandle 这个是搜索form的ID
		$.ajax({
			url: url,
			type: 'post',
			async: false,
			data: data,
			timeout: 120000,
			dataType: 'json',
			success: function (data) {
				if (data.status == 0) {
					var excelUrl = '/' + data.result;
					window.open(excelUrl, '_blank').location;   // 在新标签页打开
				} else {
					alert(data.msg);
					return;
				}
			}
		});
	}
</script>


</body>
</html>