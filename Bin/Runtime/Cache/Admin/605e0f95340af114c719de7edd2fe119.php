<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
    <title><?php echo GC('web_title');?>管理后台-服务费列表</title>
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


    <style>

        table{
            border-bottom: 1px solid #ddd
        }


    </style>
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
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        本页面可以配置用户等级相关信息。</div>

                    <div class="panel panel-flat">
                        <div class="panel-heading">

                            <form class="v-filter-container" action="<?php echo U('User/index');?>" method="get">

                                <div class="filter-fields-wrap">
                                    <div class="filter-item clearfix">
                                        <div class="filter-item__field">
                                            <div class="v__control-group padd_left_11">

                                                <div class="v__controls">
                                                    <!--													<a class="btn btn-primary search">搜索</a>-->
                                                   <!-- <a href="<?php echo U('commission');?>"><input type="button" value="添加" class="btn btn-primary search" style="width:100px;height: 35px;"></a>-->
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

                                    <th class="text-center">等级ID</th>
                                    <th class="text-center">等级权重</th>
                                    <th class="text-center">等级名称</th>
                                    <th class="text-center">等级图标</th>
                                    <!--<th class="text-center">支付升级</th>-->
                                    <th class="text-center">自动升级</th>
                                    <!--<th class="text-center">自动降级</th>-->
                                    <th class="text-center">推荐奖励</th>
                                    <!--<th class="text-center">首次返佣</th>
                                    <th class="text-center">复购返佣</th>-->
                                    <th class="text-center">等级状态</th>
                                    <th class="text-center">操作</th>
                                </tr>
                                </thead>

                                <?php if(is_array($user_level)): $i = 0; $__LIST__ = $user_level;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr class="tt_color">

                                        <td class="text-center">
                                            <div class="ed_stand">
                                                <p><?php echo ($vo["id"]); ?></p>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="ed_stand">
                                                <p><?php echo ($vo["level_ranking"]); ?></p>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="ed_stand">
                                                <p><?php echo ($vo["level_name"]); ?></p>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <div class="">
                                                <img src="<?php echo ($vo["level_icon"]); ?>" style="width: 60px;height: 60px;" class="stand_left"/>
                                                <div class="clear"></div>
                                            </div>
                                        </td>


                                        <!--<td class="text-center">
                                            <div class="dd_stand zt_stand">
                                              <p>
                                                  <?php if($vo['up_level_by_money']): ?><span class="layui-bg-green">开启</span>
                                                    <?php else: ?>
                                                    <span class="layui-bg-cyan">关闭 </span><?php endif; ?>
                                              </p>
                                            </div>
                                        </td>-->

                                        <td class="text-center">
                                            <div class="dd_stand zt_stand">
                                              <p>  <?php if($vo['up_level_by_auto']): ?><span class="layui-bg-green">开启</span>
                                                <?php else: ?>
                                                    <span class="layui-bg-cyan">关闭</span><?php endif; ?>
                                              </p>
                                            </div>
                                        </td>
                                        <!--<td class="text-center">
                                            <div class="zt_stand">
                                                <p> <?php if($vo['down_level_by_auto']): ?><span class="layui-bg-green">开启</span>
                                                    <?php else: ?>
                                                    <span class="layui-bg-cyan">关闭</span><?php endif; ?>
                                                </p>
                                            </div>
                                        </td>-->
                                        <td class="text-center">
                                            <div class="zt_stand">
                                                <p > <?php if($vo['recommend_status']): ?><span class="layui-bg-green">开启</span>
                                                    <?php else: ?>
                                                    <span class="layui-bg-cyan">关闭</span><?php endif; ?>
                                                </p>
                                            </div>
                                        </td>
                                        <!--<td class="text-center">
                                            <div class="zt_stand">
                                               <p> <?php if($vo['buy_award_status']): ?><span class="layui-bg-green">开启</span>
                                                    <?php else: ?>
                                                    <span class="layui-bg-cyan">关闭</span><?php endif; ?>
                                               </p>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <div class="zt_stand">
                                                <p>
                                                    <?php if($vo['repeat_buy_award_status']): ?><span class="layui-bg-green">开启</span>
                                                    <?php else: ?>
                                                        <span class="layui-bg-cyan">关闭</span><?php endif; ?>
                                                </p>
                                            </div>
                                        </td>-->

                                        <td class="text-center">
                                            <div class="zt_stand">
                                                <p>
                                                    <?php if($vo['status']): ?><span class="layui-bg-green">开启</span>
                                                        <?php else: ?>
                                                        <span class="layui-bg-cyan">关闭</span><?php endif; ?>
                                                </p>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <div class="tt_stand">
                                                <a title="查看详情" href="<?php echo U('commission');?>?id=<?php echo ($vo["id"]); ?>" class="layui-btn layui-btn-sm layui-btn-normal btn_detail">详情</a>
                                                <a title="删除用户" href="<?php echo U('commission_del');?>?id=<?php echo ($vo["id"]); ?>" class="layui-btn layui-btn-sm layui-btn-danger btn_detail" onclick="return confirm('确定要删除吗?');" style="display: none;">删除</a>
                                            </div>
                                        </td>
                                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                            </table>
                            <!-- 分页 -->
                            <div class="page_1028" >
                                <?php echo ($show); ?>
                                <div class="clear"></div>
                            </div>
                            <!-- 分页 -->
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
            ,type: 'datetime'
            ,range: true
        });
    </script>
</body>
</html>