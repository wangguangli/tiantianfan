<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
	<title><?php echo GC('web_title');?>管理后台-售后订单</title>
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
						本页面是售后订单，对订单进行售后操作。
					</div>

					<div class="panel panel-flat">
						<div class="panel-heading">

							<form class="v-filter-container" action="" method="get" style="width: 100%;">

								<div class="filter-fields-wrap">
									<div class="filter-item clearfix">
										<div class="filter-item__field">
											<div class="v__control-group">
												<label class="v__control-label">退款编号</label>
												<div class="v__controls">
													<input type="text" name="refund_no" value="<?php echo ($_REQUEST['refund_no']); ?>"
													       class="v__control_input" placeholder="请输入退款编号"
													       autocomplete="off">
												</div>
											</div>

<!--											<div class="v__control-group">-->
<!--												<label class="v__control-label">会员信息</label>-->
<!--												<div class="v__controls">-->
<!--													<input type="text" class="v__control_input" name="content"-->
<!--													       value="<?php echo ($_REQUEST['content']); ?>" autocomplete="off"-->
<!--													       placeholder="手机号码/会员ID/用户名">-->
<!--												</div>-->
<!--											</div>-->

											<div class="v__control-group">
												<label class="v__control-label">售后状态</label>
												<div class="v__controls">
													<select class="v__control_input" name="refund_status">
														<option value="0"
														<?php if($_REQUEST['refund_status'] == '0' ): ?>selected<?php endif; ?>
														>全部</option>
														<option value="1"
														<?php if($_REQUEST['refund_status'] == 1 ): ?>selected<?php endif; ?>
														>申请售后</option>
														<option value="2"
														<?php if($_REQUEST['refund_status'] == 2 ): ?>selected<?php endif; ?>
														>同意售后</option>
														<option value="3"
														<?php if($_REQUEST['refund_status'] == 3 ): ?>selected<?php endif; ?>
														>拒绝售后</option>
														<option value="4"
														<?php if($_REQUEST['refund_status'] == 4 ): ?>selected<?php endif; ?>
														>买家邮寄</option>
														<option value="5"
														<?php if($_REQUEST['refund_status'] == 5 ): ?>selected<?php endif; ?>
														>商家收货</option>
														<option value="6"
														<?php if($_REQUEST['refund_status'] == 6 ): ?>selected<?php endif; ?>
														>商家回寄</option>
														<option value="7"
														<?php if($_REQUEST['refund_status'] == 6 ): ?>selected<?php endif; ?>
														>买家收货</option>
														<option value="8"
														<?php if($_REQUEST['refund_status'] == 6 ): ?>selected<?php endif; ?>
														>退款</option>
														<option value="9"
														<?php if($_REQUEST['refund_status'] == 6 ): ?>selected<?php endif; ?>
														>售后完成</option>
													</select>
												</div>
											</div>


											<div class="v__control-group">
												<label class="v__control-label">创建时间</label>
												<div class="v__controls v-date-input-control">
													<label for="add_time">
														<input type="text" class="v__control_input pr-30" id="add_time"
														       name="add_time" value="<?php echo ($_REQUEST['add_time']); ?>"
														       placeholder="请选择时间" autocomplete="off"
														       data-types="datetime" lay-key="3">
														<i class="icon icon-calendar"></i>
													</label>
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
						<div class="dataTables_wrapper no-footer">
							<table class="table datatable-tools-basic basic_table">
								<thead>
								<tr>
									<th style="width: 1px;"><input type="checkbox"
									                               onclick="$('input[name*=\'selected\']').prop('checked', this.checked);">
									</th>
									<th>ID</th>
									<th>退款编号</th>
									<th>用户信息</th>
									<th>商品详情</th>
									<th>商家信息</th>
									<th class="text-center">原订单信息</th>
									<th class="text-center">退换类型</th>
									<th class="text-center">售后状态</th>
									<th class="text-center">创建时间</th>
									<th class="text-center">操作</th>
								</tr>
								</thead>


								<?php if(is_array($list)): foreach($list as $key=>$vo): ?><tr class="tt_color" style="">
										<td style="width: 1px;">
											<input type="checkbox" name="selected[]" value="<?php echo ($vo["id"]); ?>">
											<input type="hidden" name="shipping_code[]" value="<?php echo ($vo["id"]); ?>">
										</td>


										<td class=""><?php echo ($vo["id"]); ?></td>
										<td class=""><?php echo ($vo["refund_no"]); ?></td>

										<td class="">
											<div class="ed_stand">
												<p>ID:<?php echo ($vo["user_id"]); ?></p>
												<p>姓名：<?php echo (get_user_field($vo["user_id"],phone)); ?></p>
												<p>手机号：<?php echo (get_user_field($vo["user_id"],phone)); ?></p>
											</div>
										</td>
										<td class="col-xs-3">
											<div class="id_stand">
												<img src="<?php echo ($vo["goods_thumb"]); ?>" style="width: 80px;height: 80px;"
												     class="stand_left"/>
												<div class="stand_right">
													<h6 title="<?php echo ($sub["goods_name"]); ?>"><?php echo ($vo["goods_name"]); ?></h6>
													<p>规格：<?php echo ($vo["goods_spec_price_name"]); ?></p>
													<p>数量：<?php echo ($vo["goods_num"]); ?>件</p>
													<p>小计：<span>￥<?php echo ($vo["refund_total"]); ?>元</span></p>
												</div>
												<div class="clear"></div>
											</div>
										</td>


										<td class="">
											<div class="ed_stand">
												<div class="ed_stand">
													<p>UID:<?php echo ($vo["shop_id"]); ?></p>
													<p><?php echo (get_user_field($vo["shop_id"],shop_name,0,2)); ?></p>
												</div>

											</div>
										</td>
										<td class="text-center">
											<div class="ed_stand">
												<p>ID：<?php echo ($vo["order_id"]); ?></p>
												<p><?php echo (get_order_info($vo["order_id"],'','order_no')); ?></p>
											</div>
										</td>
										<td class="text-center">
											<div class="ed_stand">
												<p><?php echo (get_refund_type($vo["refund_type"])); ?></p>
											</div>
										</td>
										<td class="text-center">
											<div class="ed_stand">
												<p><?php echo (get_refund_status($vo["refund_status"])); ?></p>
											</div>
										</td>
										<td class="text-center">
											<div class="ed_stand">
												<p><?php echo (get_time($vo["add_time"],'Y-m-d H:i:s')); ?></p>
											</div>
										</td>
										<td class="text-center col-xs-1">
											<div class="tt_stand">
												<a title="操作" href="<?php echo U('refund_detail', array('id'=>$vo['id']));?>"  class="layui-btn layui-btn-sm layui-btn-normal btn_detail" >操作</a>
											</div>
										</td>

									</tr><?php endforeach; endif; ?>
							</table>
							<!-- 分页 -->

							<div class="page_1028" >
<!--								<?php if($express_type == 1 ): ?>-->
<!--									<a onclick="pi_ship()" class="btn btn-primary pull-left">批量发货</a><?php endif; ?>-->
								<?php echo ($show); ?>
<!--								<a onclick="pi_ship()" class="btn btn-primary pull-left"-->
<!--								  style="display: none">批量删除</a></if>-->
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

    laydate.render({
        elem: '#pay_time'
        , type: 'datetime'
        , range: true
    });

    //根据搜索值导出订单
    function expOrder() {
        var content = $("#content").val();//搜索内容
        var cate = $("#cate").val();//搜索条件
        var startTime = $("#startTime").val();//开始时间
        var endTime = $("#endTime").val();//结束时间
        var refund_status = $("#refund_status").val();//结束时间

        $downUrl = "/index.php/Admin/Order/expOrder?content=" + content + "&cate=" + cate + "&order_type=0&starttime=" + startTime + "&endtime=" + endTime + "&refund_status=" + refund_status;
        location.href = $downUrl;
        // console.log($downUrl);
        return;
    }

    //批量发货
    function pi_ship() {
        var arr = [];
        $('input:checkbox:checked').each(function () {
            var checkValue = $(this).val();
            arr.push(checkValue);
            // 选中框中的值
        });
        if (arr === undefined || arr.length == 0) {
            alert('未选择商品');
            return false;
        }
        if (arr[0] == 'on') {
            arr.splice(0, 1);
        }
        $.ajax({
            type: "POST",
            url: "/index.php/Admin/order/pi_ship",
            data: {pi: arr},
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if (data.status == 0) {
                    alert('操作成功');
                } else {
                    alert('操作失败');
                }
            }
        });
    }
</script>


</body>
</html>