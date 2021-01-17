<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
    <title><?php echo GC('web_title');?>管理后台-分类列表</title>
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

        .so{
            font-size:18px;

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
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                        本页分类列表
                    </div>

                    <div class="panel panel-flat">
                        <div class="panel-heading">
                            <form class="v-filter-container" action="" method="POST">
                                <div class="filter-fields-wrap">
                            <div class="filter-item clearfix">
                                <div class="filter-item__field">
                                    <div class="v__control-group padd_left_11">

                                        <div class="v__controls">
                                            <!--													<a class="btn btn-primary search">搜索</a>-->
                                            <a href="<?php echo U('add_category');?>"><input type="button" value="添加" class="btn btn-primary search" style="width:100px;height: 35px;"></a>
                                            <a class="btn btn-success ml-15 dataExcel" style="display: none;">导出分类</a>
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
                                    <th class="">分类名称</th>
                                    <th class="">级别</th>

                                    <th class="text-center">操作</th>
                                </tr>
                                </thead>

                                <thead class="tr">

                                </thead>
<!--                                <?php if(is_array($list)): foreach($list as $key=>$vo): ?>-->

<!--                                    <tr class="tt_color" style="" id="n<?php echo ($vo["id"]); ?>">-->
<!--                                        <td style="width: 1px;">-->
<!--                                        </td>-->


<!--                                        <td class="">-->
<!--                                            <div class="ed_stand">-->

<!--                                                <p><span class="sp" onclick="getCategory(this.id)" id="<?php echo ($vo["id"]); ?>">+</span>&nbsp&nbsp<?php echo ($vo["name"]); ?></p>-->
<!--                                            </div>-->
<!--                                        </td>-->

<!--                                        <td class="">-->
<!--                                            <div class="ed_stand">-->
<!--                                                <p>一级</p>-->
<!--                                            </div>-->
<!--                                        </td>-->


<!--                                        <td class="text-center col-xs-2">-->
<!--                                            <div class="tt_stand">-->

<!--                                                <a title="查看详情" href="<?php echo U('Goods/up_category', array('id'=> $vo['id']));?>" class="layui-btn layui-btn-sm layui-btn-normal btn_detail">详情</a>-->
<!--                                                <a title="添加子类" href="<?php echo U('Goods/add_category', array('id'=> $vo['id']));?>" class="layui-btn layui-btn-sm layui-btn-normal btn_detail">添加子类</a>-->
<!--                                                <a title="删除分类" href="<?php echo U('Goods/delete_category', array('id'=>$vo['id']));?>" class="layui-btn layui-btn-sm layui-btn-danger btn_detail" onclick="return confirm('确定要删除吗?');">删除</a>-->
<!--                                            </div>-->
<!--                                        </td>-->
<!--                                    </tr>-->
<!--                                    <?php if(is_array($vo['gc'])): foreach($vo['gc'] as $key=>$v1): ?>-->
<!--                                    <tr class="tt_color" style="">-->
<!--                                        <td style="width: 1px;">-->
<!--                                        </td>-->


<!--                                        <td class="">-->
<!--                                            <div class="ed_stand">-->
<!--                                                <p style="padding-left:40px">|-&#45;&#45;&#45;&#45;<?php echo ($v1['name']); ?></p>-->
<!--                                            </div>-->
<!--                                        </td>-->

<!--                                        <td class="">-->
<!--                                            <div class="ed_stand">-->
<!--                                                <p>二级</p>-->
<!--                                            </div>-->
<!--                                        </td>-->


<!--                                        <td class="text-center col-xs-2">-->
<!--                                            <div class="tt_stand">-->

<!--                                                <a title="查看详情" href="<?php echo U('Goods/up_category', array('id'=> $v1['id']));?>" class="layui-btn layui-btn-sm layui-btn-normal btn_detail">详情</a>-->
<!--                                                <a title="添加子类" href="<?php echo U('Goods/add_category', array('id'=> $v1['id']));?>" class="layui-btn layui-btn-sm layui-btn-normal btn_detail">添加子类</a>-->
<!--                                                <a title="删除分类" href="<?php echo U('Goods/delete_category', array('id'=>$v1['id']));?>" class="layui-btn layui-btn-sm layui-btn-danger btn_detail" onclick="return confirm('确定要删除吗?');">删除</a>-->
<!--                                            </div>-->
<!--                                        </td>-->
<!--                                    </tr>-->
<!--                                        <?php if(is_array($v1['gc'])): foreach($v1['gc'] as $key=>$v2): ?>-->
<!--                                            <tr class="tt_color" style="">-->
<!--                                                <td style="width: 1px;">-->
<!--                                                </td>-->


<!--                                                <td class="">-->
<!--                                                    <div class="ed_stand">-->
<!--                                                        <p style="padding-left:80px">|-&#45;&#45;&#45;&#45;<?php echo ($v2['name']); ?></p>-->
<!--                                                    </div>-->
<!--                                                </td>-->

<!--                                                <td class="">-->
<!--                                                    <div class="ed_stand">-->
<!--                                                        <p>三级</p>-->
<!--                                                    </div>-->
<!--                                                </td>-->


<!--                                                <td class="text-center col-xs-2">-->
<!--                                                    <div class="tt_stand">-->

<!--                                                        <a title="查看详情" href="<?php echo U('Goods/up_category', array('id'=> $v2['id']));?>" class="layui-btn layui-btn-sm layui-btn-normal btn_detail">详情</a>-->

<!--                                                        <a title="删除分类" href="<?php echo U('Goods/delete_category', array('id'=>$v2['id']));?>" class="layui-btn layui-btn-sm layui-btn-danger btn_detail" onclick="return confirm('确定要删除吗?');">删除</a>-->
<!--                                                    </div>-->
<!--                                                </td>-->
<!--                                            </tr>-->
<!--<?php endforeach; endif; ?>-->

<!--<?php endforeach; endif; ?>-->
<!--<?php endforeach; endif; ?>-->
                            </table>

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

<script >
    var da = [] ;
    function resgat(){

        var cid
        var str ='';
        $.ajax({
            url:'/index.php/Api/Goodsout/category',
            data:'',
            type : "POST",
            dataType:"json",
            success:function(res){
                 da = res.result;
                 ress = da
                console.log(ress);
                for(var i=0;i<ress.length;i++) {
                        cid=ress[i]['id']


                    var upUrl="/Admin/Goods/up_category/id/"+cid;
                    var addUrl="/Admin/Goods/add_category/id/"+cid;
                    var deleteuRL="/Admin/Goods/deleteMuch/type/6_0/isajax/0/ids/"+cid;
                    var deleteuRL2="/Admin/Goods/deleteMuch/type/6_1/isajax/0/ids/"+cid;
                    // var addUrl="<?php echo U('Goods/add_category', array('id'=>'"+cid+"'));?>"
                    // var deleteuRL = "<?php echo U('Goods/delete_category',array('id'=>'"+cid+"'));?>"

                     str += "<tr class='tt_color' style='' id='n"+cid+"'>" +
                        "<td style='width: 1px;'>" +
                        "</td> " +
                        "<td class=''> " +
                        "<div class='ed_stand'> " +
                        "<p><span class='so' onclick='getCategory("+i+",0,"+cid+")' id='"+cid+"'>+</span>&nbsp&nbsp"+ress[i]['name']+"</p> " +
                        "</div> " +
                        "</td> " +
                        "<td class=''> " +
                        "<div class='ed_stand'> " +
                        "<p>一级</p> " +
                        "</div> " +
                        "</td> " +
                        "<td class='text-center col-xs-4'> " +
                        "<div class='tt_stand'> " +
                        "<a title='查看详情' href='"+upUrl+"' class='layui-btn layui-btn-sm layui-btn-normal btn_detail'>详情</a> " +
                        "<a title='添加子类' href='"+addUrl+"' class='layui-btn layui-btn-sm layui-btn-normal btn_detail'>添加子类</a> " +
                        "<a title='删除分类' href='"+deleteuRL+"' class='layui-btn layui-btn-sm layui-btn-danger btn_detail' onclick='return confirm('确定要删除吗?');'>删除</a> " +
                        "<a title='强制删除分类' href='"+deleteuRL2+"' class='layui-btn layui-btn-sm layui-btn-danger btn_detail' onclick='return confirm('确定要删除吗?删除后无法恢复！！！');'>强制删除</a> " +
                        "</div> " +
                        "</td> " +
                        "</tr>"


                }


            $('.tr').append(str);
        }

        })
    }
    window.onload = resgat();
    function getCategory(a,b,c,d){
        var cid = a;
        var level = b;
        var id = c;
        var d = d;
        var str =''

        if(level == 0){

            for(var i=0;i<da[cid]['gc'].length;i++){
                var de= da[cid]['gc'][i]

                var upUrl="/Admin/Goods/up_category/id/"+de['id'];
                var addUrl="/Admin/Goods/add_category/id/"+de['id'];
                var deleteuRL="/Admin/Goods/deleteMuch/type/6_0/isajax/0/ids/"+de['id'];
                var deleteuRL2="/Admin/Goods/deleteMuch/type/6_1/isajax/0/ids/"+de['id'];

                str+="<tr class='tt_color' style='' id='n"+de['id']+"'>"+
                    "<td style='width: 1px;'>" +
                    "</td><td class=''>" +
                    "<div class='ed_stand'>" +

                    "<p style='padding-left:40px;'><span class='so' onclick='getCategory("+cid+",1,"+de['id']+","+i+")' id='"+de['id']+"' >+</span>&nbsp&nbsp|-----"+de['name']+"</p>" +
                    "</div></td><td class=''>" +
                    "<div class='ed_stand'> " +
                    "<p>二级</p> " +
                    "</div> " +
                    "</td> " +
                    "<td class='text-center col-xs-4'> " +
                    "<div class='tt_stand'> " +
                    "<a title='查看详情' href='"+upUrl+"' class='layui-btn layui-btn-sm layui-btn-normal btn_detail' style='margin: 0 0 0 0'>详情</a>" +
                    "<a title='添加子类' href='"+addUrl+"' class='layui-btn layui-btn-sm layui-btn-normal btn_detail' style='margin: 0  0 0 14px'>添加子类</a>" +
                    "<a title='删除分类' href='"+deleteuRL+"' class='layui-btn layui-btn-sm layui-btn-danger btn_detail' style='margin:0 0 0 15px ' onclick='return confirm('确定要删除吗?');'>删除</a> " +
                    "<a title='强制删除分类' href='"+deleteuRL2+"' class='layui-btn layui-btn-sm layui-btn-danger btn_detail' style='margin:0 0 0 15px ' onclick='return confirm('确定要删除吗?删除后无法恢复！！！');'>强制删除</a> " +
                    "</div> " +
                    "</td> " +
                    "</tr>";
            };

        }
        else if(level == 1){
            for(var i=0;i<da[cid]['gc'][d]['gc'].length;i++){
                var de = da[cid]['gc'][d]['gc'][i]
                var upUrl="/Admin/Goods/up_category/id/"+de['id'];
                var deleteuRL="/Admin/Goods/deleteMuch/type/6_0/isajax/0/ids/"+de['id'];
                var deleteuRL2="/Admin/Goods/deleteMuch/type/6_1/isajax/0/ids/"+de['id'];

                str+="<tr class='tt_color' style='' id='n"+de['id']+"'> " +
                    "<td style='width: 1px;'> " +
                    "</td> " +
                    "<td class=''> " +
                    "<div class='ed_stand'>" +
                    "<p style='padding-left:80px'>|-----"+de['name']+"</p>" +
                    "</div>" +
                    "</td> " +
                    "<td class=''>" +
                    "<div class='ed_stand'> " +
                    "<p>三级</p> " +
                    "</div> " +
                    "</td> " +
                    "<td class='text-center col-xs-4'> " +
                    "<div class='tt_stand'> " +
                    "<a title='查看详情' href='"+upUrl+"' class='layui-btn layui-btn-sm layui-btn-normal btn_detail'>详情</a>" +
                    "<a title='删除分类' href='"+deleteuRL+"' class='layui-btn layui-btn-sm layui-btn-danger btn_detail' onclick='return confirm('确定要删除吗?');'>删除</a> " +
                    "<a title='强制删除分类' href='"+deleteuRL2+"' class='layui-btn layui-btn-sm layui-btn-danger btn_detail' onclick='return confirm('确定要删除吗?删除后无法恢复！！！');'>强制删除</a> " +
                    "</div> " +
                    "</td> " +
                    "</tr>";
            }

        }

        $('#n'+id+'').after(str);

        $('#'+id+'').text('-');
        $('#'+id+'').attr('onclick',"getCategory_bi("+cid+","+level+","+id+","+d+")");




    }

    function getCategory_bi(a,b,c,d){
        var cid = a;
        var level = b;
        var id = c;
        var d = d;




        var str='';
        if(level == 0){
            for(var i=0;i<da[cid]['gc'].length;i++){

                $('#n'+da[cid]['gc'][i]['id']+'').remove();
                for(var e=0;e<da[cid]['gc'][i]['gc'].length;e++){
                    $('#n'+da[cid]['gc'][i]['gc'][e]['id']+'').remove();
                }
            };
        }else if(level == 1){
            for(var i=0;i<da[cid]['gc'][d]['gc'].length;i++){
                var de = da[cid]['gc'][d]['gc'][i]['id']
                $('#n'+de+'').remove();
            }
        }



        $('#'+id+'').text('+');
        $('#'+id+'').attr('onclick',"getCategory("+cid+","+level+","+id+","+d+")");





    }

</script>


</body>
</html>