<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
	<title><?php echo GC('web_title');?>管理后台</title>
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
						本页商品列表
					</div>

					<div class="panel panel-flat">
						<div class="panel-heading">

							<form class="v-filter-container" action="<?php echo U('index');?>" method="get" style="width: 100%" id="formHandle">

								<div class="filter-fields-wrap">
									<div class="filter-item clearfix">
										<div class="filter-item__field">

											<div class="v__control-group">
												<label class="v__control-label">商品标题</label>
												<div class="v__controls">
													<input type="text" name="name" value="" class="v__control_input"
													       placeholder="请输入搜索内容">
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">添加时间</label>
												<div class="v__controls v-date-input-control">
													<label for="cre_time">
														<input type="text" class="v__control_input pr-30" id="cre_time"
														       name="cre_time" value="<?php echo ($_REQUEST['cre_time']); ?>"
														       placeholder="请选择时间" autocomplete="off"
														       data-types="datetime" lay-key="4">
														<i class="icon icon-calendar"></i>
													</label>
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">分类标题</label>
												<div class="v__controls">
													<input type="text" name="cate_name" value=""
													       class="v__control_input" placeholder="请输入第三级分类标题">
												</div>
											</div>
											<div class="v__control-group">
												<label class="v__control-label">所属店铺</label>
												<div class="v__controls">
													<select class="v__control_input" name="shop_id">
														<option value="0">全部</option>
														<?php if(is_array($shop_list)): foreach($shop_list as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>"
															<?php if($_REQUEST['shop_id'] == $vo['id'] ): ?>selected<?php endif; ?>
															><?php echo ($vo["shop_name"]); ?></option><?php endforeach; endif; ?>
													</select>
												</div>
											</div>

											<div class="v__control-group">
												<label class="v__control-label">特殊分类</label>
												<div class="v__controls">
													<select class="v__control_input" name="hot_id">
														<option value="0">全部</option>
														<?php if(is_array($special)): foreach($special as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>"
															<?php if($_REQUEST['hot_id'] == $vo['id'] ): ?>selected<?php endif; ?>
															><?php echo ($vo["name"]); ?></option><?php endforeach; endif; ?>
													</select>
												</div>
											</div>

										</div>
									</div>


									<div class="filter-item clearfix">
										<div class="filter-item__field">
											<div class="v__control-group">
												<label class="v__control-label"></label>
												<div class="v__controls">
													<!--													<a class="btn btn-primary search">搜索</a>-->
													<input type="submit" value="搜索" class="btn btn-primary search"
													       style="width:100px;height: 35px;">
													<a class="btn btn-success ml-15" onclick="ExportExcel();">导出表格</a>
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
							<table class="table datatable-tools-basic basic_table">
								<thead>
								<tr>
									<th style="width: 1px;"></th>
									<th>ID</th>
									<th>图片</th>
									<th>标题</th>
									<th>价格</th>
									<th>店铺</th>
									<th>商品分类</th>
									<th>特殊分类</th>
									<th>营销属性</th>
									<th>添加时间</th>
									<th class="text-center">操作</th>
								</tr>
								</thead>

								<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr class="tt_color">
										<td style="width: 1px;">
											<input type="checkbox" name="much_id[]" value="<?php echo ($vo["id"]); ?>">
											<input type="hidden" name="shipping_code[]" value="<?php echo ($vo["id"]); ?>">
										</td>

										<td class=""><?php echo ($vo["id"]); ?></td>

										<td class="">
											<div class="ed_stand">
												<p><img src="<?php echo ($vo["thumb"]); ?>" width="50px"></p>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><?php echo ($vo["name"]); ?> </p>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><?php echo ($vo["price"]); ?></p>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><?php echo (getShopName($vo["shop_id"])); ?></p>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><?php echo (getCategoryName($vo["cat_id"],false)); ?></p>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><?php echo (getGoodsHot($vo["hot_id"])); ?></p>
											</div>
										</td>
										<td class="">
											<div class="zt_stand">
												<?php if($vo['is_group'] == 1): ?><p><span
														class="layui-bg-blue">团购</span></p><?php endif; ?>
												<?php if($vo['is_hour'] == 1): ?><p><span
														class="layui-bg-green">整点秒杀</span></p><?php endif; ?>
												<?php if($vo['is_time'] == 1): ?><p><span
														class="layui-bg-cyan">限时购</span></p><?php endif; ?>
											</div>
										</td>
										<td class="">
											<div class="ed_stand">
												<p><?php echo (get_date($vo["time"],'Y-m-d H:i:s')); ?></p>
											</div>
										</td>
										<td class="text-center">
											<div class="tt_stand">
												<a title="查看详情" href="<?php echo U('add');?>?id=<?php echo ($vo["id"]); ?>"
												   class="layui-btn layui-btn-sm layui-btn-normal btn_detail">详情</a>
												<!--<a title="分销" href="<?php echo U('goods_gauge_reward');?>?id=<?php echo ($vo["id"]); ?>"
												   class="layui-btn layui-btn-sm layui-btn-normal btn_detail">分销</a>-->
												<a title="删除商品" href="<?php echo U('delete');?>?id=<?php echo ($vo["id"]); ?>"
												   class="layui-btn layui-btn-sm layui-btn-danger btn_detail"
												   onclick="return confirm('确定要删除吗?');">删除</a>

											</div>
										</td>
									</tr><?php endforeach; endif; else: echo "" ;endif; ?>
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
									<a onclick="delete_much('5_0', 0)" class="num">批量删除</a>
								</div>

								<?php echo ($show); ?>
								<div class="clear"></div>
							</div>
						</div>
						<!-- /pagination types -->

					</div>
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
		//完成时间
		laydate.render({
			elem: '#cre_time'
			, type: 'datetime'
			, range: true
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