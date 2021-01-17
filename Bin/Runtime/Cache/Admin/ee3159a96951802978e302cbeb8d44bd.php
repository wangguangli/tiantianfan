<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
	<title><?php echo GC('web_title');?>管理后台-广告管理</title>
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


	<!-- /theme JS files -->
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
						本页广告管理
					</div>

					<div class="panel panel-flat">
						<div class="panel-heading">
							<form class="v-filter-container" action="<?php echo U('Marketing/ad');?>" method="POST">
								<div class="filter-fields-wrap">
								<div class="filter-item clearfix">
									<div class="filter-item__field">
									<div class="v__control-group">
										<label class="v__control-label">名称</label>
										<div class="v__controls">
											<input type="text" class="v__control_input" name="title" value="<?php echo ($_REQUEST['title']); ?>" placeholder="请输入标题" autocomplete="off">
										</div>
									</div>
									<div class="v__control-group">
										<label class="v__control-label">选择位置</label>
										<div class="v__controls">
											<select class="v__control_input" name="id">

												<option value="0">请选择</option>
												<?php if(is_array($pos)): foreach($pos as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["description"]); ?></option><?php endforeach; endif; ?>
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
											<input type="submit" value="查询" class="btn btn-primary search" style="width:100px;height: 35px;">
											<a href="<?php echo U('ad_detail');?>"><input type="button" value="添加" class="btn btn-primary search" style="width:100px;height: 35px;"></a>
											<a class="btn btn-success ml-15 dataExcel" style="display: none;">导出订单</a>
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
									<th style="width: 1px;">
									</th>
									<th class="">ID</th>
									<th class="">标题</th>
									<th class="">位置描述</th>
									<th class="">标准尺寸</th>
									<th class="">图片</th>
									<th class="">时间</th>
									<th class="text-center">操作</th>
								</tr>
								</thead>


								<?php if(is_array($result)): foreach($result as $key=>$vo): ?><tr class="tt_color" style="">
										<td style="width: 1px;">
										</td>


										<td class=""><?php echo ($vo["id"]); ?></td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo ($vo["title"]); ?> </p>
											</div>
										</td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo ($vo["description"]); ?> </p>
											</div>
										</td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo ($vo["width"]); ?>×<?php echo ($vo["height"]); ?> </p>
											</div>

										</td>

										<td class="">
											<div class="ed_stand">
												<p><img src="<?php echo ($vo["photo"]); ?>" style="width:100px"> </p>
											</div>
										</td>

										<td class="">
											<div class="ed_stand">
												<p><?php echo (get_time($vo["time"])); ?> </p>
											</div>
										</td>

										<td class="text-center col-xs-2">
											<div class="tt_stand">
												<a title="查看详情" href="<?php echo U('ad_detail');?>?id=<?php echo ($vo["id"]); ?>" class="layui-btn layui-btn-sm layui-btn-normal btn_detail">详情</a>
												<a title="删除订单" href="<?php echo U('del_ad');?>?id=<?php echo ($vo["id"]); ?>" class="layui-btn layui-btn-sm layui-btn-danger btn_detail" onclick="return confirm('确定要删除吗?');">删除</a>
											</div>
										</td>
									</tr><?php endforeach; endif; ?>
							</table>
							<!-- 分页 -->
						<div class="page_1028" >
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

</script>
</body>
</html>