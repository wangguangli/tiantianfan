<?php if (!defined('THINK_PATH')) exit();?>    <!DOCTYPE html>
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
                        本页经营概况
                    </div>

                    <div class="panel panel-flat">
                        <div class="panel-heading">

                            <form class="v-filter-container" action="<?php echo U('Account/management');?>" method="POST" onsubmit="return sub()">

                                <div class="filter-fields-wrap">
                                    <div class="filter-item clearfix">
                                        <div class="filter-item__field">

                                            <input type="hidden" name="ischeck" value="<?php echo ($_REQUEST['ischeck']); ?>">

                                            <div class="v__control-group">
                                                <label class="v__control-label">订单类别</label>
                                                <div class="v__controls">
                                                    <select name="cate" class="v__control_input" id="type" id="level">
                                                        <option value="0" selected="" >全部</option>
                                                        <option value="1"  >平台</option>
                                                        <option value="2" >商家</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="v__control-group">
                                                <label class="v__control-label">下单时间</label>
                                                <div class="v__controls">
                                                    <input type="text" id="startTime" name="startTime" value="<?php echo ($_REQUEST['startTime']); ?>" class="v__control_input" placeholder="请输入开始时间" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="v__control-group">
                                                <label class="v__control-label">商城订单金额</label>
                                                <div class="v__controls">
                                                    <input type="text"
                                                           value="<?php echo ($all_money); ?>" class="v__control_input"
                                                           autocomplete="off" readonly  style="color: red;">
                                                </div>
                                            </div>
                                            <div class="v__control-group">
                                                <label class="v__control-label">秒杀订单金额</label>
                                                <div class="v__controls">
                                                    <input type="text"
                                                           value="<?php echo ($all_flash); ?>" class="v__control_input"
                                                           autocomplete="off" readonly  style="color: red;">
                                                </div>
                                            </div>
                                            <div class="v__control-group">
                                                <label class="v__control-label">团购订单金额</label>
                                                <div class="v__controls">
                                                    <input type="text"
                                                           value="<?php echo ($all_group); ?>" class="v__control_input"
                                                           autocomplete="off" readonly  style="color: red;">
                                                </div>
                                            </div>






                                            <div class="filter-item clearfix">
                                                <div class="filter-item__field">
                                                    <div class="v__control-group">
                                                        <label class="v__control-label"></label>
                                                        <div class="v__controls">

                                                            <!--													<a class="btn btn-primary search">搜索</a>-->
                                                            <input type="submit" value="搜索" class="btn btn-primary search" style="width:100px;height: 35px;">
                                                            <a class="btn btn-success ml-15 dataExcel" style="display: none;">导出订单</a>
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
                        <div class="dataTables_wrapper no-footer mini-space" style="border:none;">
                            <div class="alert alert-tips alert-dismissible" role="alert" style="border:none;background-color:#D9EDF7 ">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                                <style>
                                    table{
                                        width: 100%;
                                        height: 100%;
                                    }
                                    th{
                                        text-align: center;
                                        width:25%;
                                    }
                                    td{
                                        text-align: center;
                                    }
                                </style>
                                <table>
                                   <tr>
                                       <th>交易额</th>
                                       <th>订单量</th>
                                       <th>付款订单</th>
                                       <th>售后订单</th>
                                   </tr>
                                   <tr>
                                       <td><?php echo ($sum_money); ?></td>
                                       <td><?php echo ($sum_order); ?></td>
                                       <td><?php echo ($sum_f_order); ?></td>
                                       <td><?php echo ($sum_s_order); ?></td>
                                   </tr>
                               </table>
                            </div>

                            <div id="chartmain" style="width:90%; height: 500px;"></div>

                            <div id="chartmain1" style="width:90%; height: 450px;margin-top: 2%"></div>
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

<script type="text/javascript">
    //指定图标的配置和数据
    var zorder = <?php echo ($z_order); ?>;

    var forder = <?php echo ($f_order); ?>;
    var sorder = <?php echo ($s_order); ?>;
    var option = {
        title:{
            text:'订单量'
        },
        tooltip:{



        },
        grid:{
            x:50,
            y:50,
            x2:5,
            y2:20,
            borderWidth:1
        },

        legend:{
            data:['订单量','付款订单','售后订单']

        },
        xAxis:{
            data:<?php echo ($data); ?>,

        },
        yAxis:{

        },
        series:[
        {
            symbolSize:8,
            name:'订单量',
            type:'line',
            data:zorder
        },
        {
            symbolSize: 8,
            name:'付款订单',
            type:'line',
            data:forder
        },
        {
            symbolSize: 8,
            name:'售后订单',
            type:'line',
            data:sorder
        }
        ],
        color: ['#00EE00', '#FF9F7F','#FFD700']
    };
    //初始化echarts实例
    var myChart = echarts.init(document.getElementById('chartmain'));

    //使用制定的配置项和数据显示图表
    myChart.setOption(option);


    var option1 = {
        title:{
            text:'交易额'
        },
        tooltip:{


        },
        grid:{

            x:50,
            y:50,
            x2:5,
            y2:20,
            borderWidth:1
        },

        legend:{
            data:['总交易额'],
        },
        xAxis:{
            data:<?php echo ($data); ?>
        },
        yAxis:{

        },
        series:[
            {
                symbolSize:8,
                name:'总交易额',
                type:'line',
                data:<?php echo ($z_order_money); ?>
            },

        ],
        color: ['#00EE00']
    };
    //初始化echarts实例
    var myChart1 = echarts.init(document.getElementById('chartmain1'));

    //使用制定的配置项和数据显示图表
    myChart1.setOption(option1);
</script>

<script>
    layui.use('laydate',function () {
            var laydate = layui.laydate;
            laydate.render({
                elem:'#startTime',
                type:'datetime',
                range: true
            })
        }
    );
    //判断数据
    function sub(){
        var sTime = $('#startTime').val();

        if(!sTime){
            alert('开始时间必须都有');
            return false;
        }

    }
</script>


</body>
</html>