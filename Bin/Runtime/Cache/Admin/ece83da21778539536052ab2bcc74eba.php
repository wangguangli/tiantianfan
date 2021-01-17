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


	<script src="/Public/admin_new/js/semantic.js" type="text/javascript"></script>
	<link href="/Public/admin_new/css/semantic.css" rel="stylesheet" type="text/css">
	<style type="text/css">
		.placeholder {
			margin-bottom: 10px;
		}

		.placeholder img {
			width: 113px;
			height: 113px;
			border: 1px dashed #ccc;
		}

		.spam_check {
			background: #833e4d !important;
		}

		.type_0411 {
			display: flex;
			flex-direction: row;
		}

		.type_0411_1 {
			border: 1px dashed #ccc;
			align-items: center;
			margin-right: 20px;
			padding-right: 23px;
			padding-left: 9px;
			cursor: pointer;
			height: 70px;
			display: flex;
		}

		.type_0411_1:hover {
			background: #fcf5de;
		}

		.type_0411_active {
			background: #fcf5de;
		}

		.type_0411_txt {
			padding-left: 5px;
			font-size: 15px;
		}

		.type_0412 {
			display: flex;
			flex-direction: row;
			border-bottom: 2px solid #e6e9f0;
			position: absolute;
			top: 0;
			left: -130px;
			padding-left: 140px;
		}

		.type_0412_1 {
			height: 35px;
			line-height: 35px;
			padding-left: 15px;
			padding-right: 15px;
		}

		.type_0412_active {
			color: #1989fa;
			font-weight: bold;
			border-bottom: 2px solid #1989fa;
		}

		.type_0413 {
			height: 37px;
			border-bottom: 2px solid #e6e9f0;
		}

		.t_two, .t_three, .t_four {
			display: none;
		}


		.ui.fluid.dropdown {
			min-width: 100px;
		}

		.dropdown {
			margin-right: 10px;
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
						在本页添加商品，主要信息必须填写.
					</div>
					<!-- page -->
					<form class="form-horizontal pt-15" method="post" onsubmit="return check();">
						<input type="hidden" class="form-control" name="user_id" value="<?php echo ($data['id']); ?>">

						<div class="form-group">
							<label class="col-xs-2"></label>
							<div class="col-xs-6 type_0413">
								<div class="type_0412">
									<div class="type_0412_1 type_0412_active" data-num="t_one">
										基本信息
									</div>
									<div class="type_0412_1" data-num="t_two">
										图片信息
									</div>
									<div class="type_0412_1" data-num="t_three">
										价格设置
									</div>
									<div class="type_0412_1" data-num="t_four">
										详情设置
									</div>
								</div>
							</div>
						</div>

						<!--基本信息-->
						<div class="sub_div t_one">
							<div class="form-group">
								<label class="col-xs-2 control-label text-right">商品名称</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="请输入商品名称" id="name" name="name"
									       value="<?php echo ($goods["name"]); ?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">副标题</label>
								<div class="col-xs-6  control-label">
									<input type="text" class="form-control" placeholder="请输入副标题" id="subhead"
									       name="subhead" value="<?php echo ($goods["subhead"]); ?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">商品编码</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="请输入商品编码" name="product_code"
									       value="<?php echo ($goods["product_code"]); ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="col-xs-2 control-label text-right">本店价格</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="请输入本店价格" id="price"
									       name="price"
									       value="<?php echo ($goods["price"]); ?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">市场价格</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" id="market_price" placeholder="请输入市场价格"
									       name="market_price" value="<?php echo ($goods["market_price"]); ?>">
								</div>
							</div>


							<!--商城类型 普通商城还是积分商城							-->
							<div class="form-group">
								<label class="col-xs-2 control-label text-right">商品类型</label>
								<div class="col-xs-6 ">
									<select name="product_type" class="form-control select-form-control inline-block">
										<option value="0"
										<?php if($goods['product_type'] == 0): ?>selected<?php endif; ?>
										>普通商品</option>
										<option value="1"
										<?php if($goods['product_type'] == 1): ?>selected<?php endif; ?>
										>积分商品</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-xs-2 control-label text-right">商品标记</label>
								<div class="col-xs-6 ">
									<select name="tags_type" class="form-control select-form-control inline-block">
										<option value="0"
										<?php if($goods['tags_type'] == 0): ?>selected<?php endif; ?>
										>新品</option>
										<option value="1"
										<?php if($goods['tags_type'] == 1): ?>selected<?php endif; ?>
										>热销</option>
										<option value="2"
										<?php if($goods['tags_type'] == 2): ?>selected<?php endif; ?>
										>优质</option>

									</select>
								</div>
							</div>


							<div class="form-group">
								<label class="col-xs-2 control-label text-right">特殊分类</label>
								<div class="col-xs-6 ">
									<select name="hot_id" class="form-control select-form-control inline-block"
									        id="hot_id">
										<option value="0">非特殊分类</option>
										<?php if(is_array($show_list)): foreach($show_list as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>"
											<?php if($goods['hot_id'] == $vo['id']): ?>selected<?php endif; ?>
											><?php echo ($vo["name"]); ?></option><?php endforeach; endif; ?>
									</select>
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">商品分类</label>
								<div class="col-xs-6 ">
									<div class="col-xs-4">
										<select name="cat_id_1" class="form-control select-form-control inline-block"
										        onchange="get_category(this.value,'cat_id_2','0');" id="cat_id_1">
											<option value="0">请选择商品分类</option>
											<?php if(is_array($cat_list)): foreach($cat_list as $k=>$v): ?><option value="<?php echo ($v['id']); ?>"
												<?php if($v['id'] == $catinfo['c1']): ?>selected="selected"<?php endif; ?>
												>
												<?php echo ($v['name']); ?></option><?php endforeach; endif; ?>
										</select>
									</div>

									<div class="col-xs-4">
										<select name="cat_id_2" id="cat_id_2" class="form-control"
										        onchange="get_category(this.value,'cat_id_3','0');">
											<option value="0">请选择商品分类</option>
											<?php if(is_array($catinfo['c2list'])): foreach($catinfo['c2list'] as $k2=>$v2): ?><option value="<?php echo ($v2['id']); ?>"
												<?php if($v2['id'] == $catinfo['c2']): ?>selected="selected"<?php endif; ?>
												>
												<?php echo ($v2['name']); ?></option><?php endforeach; endif; ?>
										</select>
									</div>

									<div class="col-xs-4">
										<select name="cat_id_3" id="cat_id_3" class="form-control">
											<option value="0">请选择商品分类</option>
											<?php if(is_array($catinfo['c3list'])): foreach($catinfo['c3list'] as $k3=>$v3): ?><option value="<?php echo ($v3['id']); ?>"
												<?php if($v3['id'] == $catinfo['c3']): ?>selected="selected"<?php endif; ?>
												>
												<?php echo ($v3['name']); ?></option><?php endforeach; endif; ?>
										</select>
									</div>


								</div>
							</div>
							<style>
								.cate_div {
									min-width: 30px;
									height: 30px;
									border: none;
									background: none;
									border: 1px dashed #ccc;
									display: block;
									float: left;
									padding: 3px 5px;
									margin-right: 8px;
									margin-bottom: 8px;
								}
							</style>
							<div class="form-group">
								<label class="col-xs-2 control-label text-right">附属分类(三级分类)</label>
								<div class="col-xs-6 ">
									<div class="cate_list">
										<span class="cate_div ext_cate" cid="c0" id="cate_div">附属分类</span>
										<input type="hidden" value="0" name="extra_cate_list[]" class="cate_ipt"
										       id="cate_ipt">
										<?php if(is_array($extra_cate_list)): foreach($extra_cate_list as $key=>$vo): ?><span class="cate_div ext_cate" cid="c<?php echo ($vo["cate_id"]); ?>"><?php echo ($vo["name"]); ?></span>
											<input type="hidden" value="<?php echo ($vo["cate_id"]); ?>" name="extra_cate_list[]"
											       class="cate_ipt c<?php echo ($vo["cate_id"]); ?>"><?php endforeach; endif; ?>
									</div>
									<div style="clear: both;">
										<select name="extra_cate_id" class="form-control"
										        onchange="changeCate(this.options[this.options.selectedIndex].value, this.options[this.options.selectedIndex].text);">
											<option value="0">请选择附属分类</option>
											<?php if(is_array($cate3list)): foreach($cate3list as $key=>$vo): ?><option value="<?php echo ($vo['id']); ?>">
													<?php echo ($vo['name']); ?>
												</option><?php endforeach; endif; ?>
										</select>
									</div>
								</div>
							</div>

							<!--<div class="form-group">
								<label class="col-xs-2 control-label text-right">商家</label>
								<div class="col-xs-6 ">
									<select name="shop_id" class="form-control select-form-control inline-block"
									        id="shop_id">
										<option value="0">平台自营</option>
										<?php if(is_array($shop)): $i = 0; $__LIST__ = $shop;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><option value="<?php echo ($list["id"]); ?>"
											<?php if($goods['shop_id'] == $list['id']): ?>selected<?php endif; ?>
											><?php echo ($list["shop_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
									</select>
								</div>
							</div>-->

							<!--<div class="form-group">
								<label class="col-xs-2 control-label text-right">服务费比例</label>
								<div class="col-xs-6 ">
									<select name="shop_fee_id" class="form-control select-form-control inline-block"
									        id="shop_fee_id">
										<option value="0">请选择服务费比例</option>
										<?php if(is_array($feelist)): $i = 0; $__LIST__ = $feelist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"
											<?php if($goods['shop_fee_id'] == $vo['id']): ?>selected<?php endif; ?>
											>平台服务费：<?php echo ($vo["percent1"]); ?>%，用户：<?php echo ($vo["percent2"]); ?>%，商家：<?php echo ($vo["percent3"]); ?>%</option><?php endforeach; endif; else: echo "" ;endif; ?>
									</select>
								</div>
							</div>-->

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">商品销量</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="请输入商品销量" name="sell_count"
									       value="<?php echo ($goods["sell_count"]); ?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">商品库存（自动计算）</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="请输入商品库存" name="store_count"
									       value="<?php echo ($goods["store_count"]); ?>" readonly>
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">排序（值越小越靠前）</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="请输入排序值" name="sort"
									       value="<?php echo ($goods["sort"]); ?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">服务费比例（%）</label>
								<div class="col-xs-6">
									<input type="number" class="form-control" placeholder="请输入服务费比例" name="service_fee_bfb"
										   value="<?php echo ($goods["service_fee_bfb"]); ?>">
								</div>
							</div>



							<div class="form-group">
								<label class="col-xs-2 control-label text-right">返还比例（%）</label>
								<div class="col-xs-6">
									<input type="number" class="form-control" placeholder="请输入返还比例" name="still_fee_bfb"
										   value="<?php echo ($goods["still_fee_bfb"]); ?>">
								</div>
							</div>



							<div class="form-group">
								<label class="col-xs-2 control-label text-right">是否上架</label>
								<div class="col-xs-6 ">
									<select name="is_on_sale" class="form-control select-form-control inline-block">
										<option value="0"
										<?php if($goods['is_on_sale'] == 0): ?>selected<?php endif; ?>
										>下架</option>
										<option value="1"
										<?php if($goods['is_on_sale'] == 1): ?>selected<?php endif; ?>
										>上架</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-xs-2 control-label text-right">是否使用代金券</label>
								<div class="col-xs-6 ">
									<select name="is_coupon" class="form-control select-form-control inline-block">
										<option value="0"
										<?php if($goods['is_coupon'] == 0): ?>selected<?php endif; ?>
										>否</option>
										<option value="1"
										<?php if($goods['is_coupon'] == 1): ?>selected<?php endif; ?>
										>是</option>
									</select>
								</div>
							</div>



							<div class="form-group">
								<label class="col-xs-2 control-label text-right">商品运费模板</label>
								<div class="col-xs-6 ">
									<select name="shipping_rule_id"
									        class="form-control select-form-control inline-block"
									        id="shipping_rule_id">
										<option value="0">请选择运费模板</option>
										<?php if(is_array($rule_list)): foreach($rule_list as $k=>$v): ?><option value="<?php echo ($v['id']); ?>"
											<?php if($v['id'] == $goods['shipping_rule_id']): ?>selected="selected"<?php endif; ?>
											>
											<?php echo ($v['name']); ?></option><?php endforeach; endif; ?>
									</select>
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">服务列表</label>
								<div class="col-xs-6 ">
									<label class="radio-inline">
										<input type="checkbox" name="service_show1" value="1"
										<?php if($goods['service_show'][0] == 1): ?>checked<?php endif; ?>
										>
										7天无理由退货
									</label>
									<label class="radio-inline">
										<input type="checkbox" name="service_show2" value="1"
										<?php if($goods['service_show'][1] == 1): ?>checked<?php endif; ?>
										>
										收益获得方式
									</label>
									<label class="radio-inline">
										<input type="checkbox" name="service_show3" value="1"
										<?php if($goods['service_show'][2] == 1): ?>checked<?php endif; ?>
										>
										配送方式
									</label>
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">新增属性</label>
								<div class="col-xs-6">
									<label class="radio-inline">
										<input type="checkbox" name="is_group" value="1" class="styled is_group"
										<?php if($goods['is_group'] == 1): ?>checked<?php endif; ?>
										>拼团
									</label>
									<label class="radio-inline">
										<input type="checkbox" name="is_hour" value="1" class="styled is_hour"
										<?php if($goods['is_hour'] == 1): ?>checked<?php endif; ?>
										>整点秒杀
									</label>
									<label class="radio-inline" style="display: none;">
										<input type="checkbox" name="is_time" value="1" class="styled is_time"
										<?php if($goods['is_time'] == 1): ?>checked<?php endif; ?>
										>限时购
									</label>
								</div>
							</div>
							<script type="text/javascript">
								$(".is_group").on("click", function () {
									var kv = $(this).val();
									if ($(this).is(':checked')) {
										// 原来没选，现在选择了
										if (kv == 1) {
											// 是否团购
											$(".kind_group").show("normal");
										}
									} else {
										// 原来选择，现在不再选择
										if (kv == 1) {
											$(".kind_group").hide("normal");
										}
									}
								});

								$(".is_hour").on("click", function () {
									var kv = $(this).val();
									if ($(this).is(':checked')) {
										// 原来没选，现在选择了
										if (kv == 1) {
											// 是否整点秒杀
											$(".kind_hour").show("normal");
										}
									} else {
										// 原来选择，现在不再选择
										if (kv == 1) {
											$(".kind_hour").hide("normal");
										}
									}
								});

								$(".is_time").on("click", function () {
									var kv = $(this).val();
									if ($(this).is(':checked')) {
										// 原来没选，现在选择了
										if (kv == 1) {
											// 是否限时购
											$(".kind_time").show("normal");
										}
									} else {
										// 原来选择，现在不再选择
										if (kv == 1) {
											$(".kind_time").hide("normal");
										}
									}
								});

							</script>


							<!--团购设置-->
							<div class="kind_group"
							<?php if($goods['is_group'] == 1): ?>style="display: block;"
								<?php else: ?>
								style="display: none;"<?php endif; ?>
							>
							<div class="form-group">
								<label class="col-xs-2 control-label text-right"><b>（拼团）</b>是否开启</label>
								<div class="col-xs-6 ">
									<label class="radio-inline">
										<input type="radio" class="styled" name="group[is_open]" value="1"
										<?php if($group['is_open'] == 1): ?>checked<?php endif; ?>
										>
										是
									</label>
									<label class="radio-inline">
										<input type="radio" class="styled" name="group[is_open]" value="0"
										<?php if($group['is_open'] == 0): ?>checked<?php endif; ?>
										>
										否
									</label>

								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right"><b>（拼团）</b>发起人是否免费</label>
								<div class="col-xs-6 ">
									<label class="radio-inline">
										<input type="radio" class="styled" name="group[team_leader_free]" value="1"
										<?php if($group['team_leader_free'] == 1): ?>checked<?php endif; ?>
										>
										免费
									</label>
									<label class="radio-inline">
										<input type="radio" class="styled" name="group[team_leader_free]" value="0"
										<?php if($group['team_leader_free'] == 0): ?>checked<?php endif; ?>
										>
										不免费
									</label>
									<label class="radio-inline">
										<input type="radio" class="styled" name="group[team_leader_free]" value="2"
										<?php if($group['team_leader_free'] == 2): ?>checked<?php endif; ?>
										>
										优惠
									</label>

								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right"><b>（拼团）</b>发起人优惠（优惠时）</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="只有上一项选择优惠时，此值才有效"
									       name="group[team_leader_free_money]"
									       value="<?php echo ($group["team_leader_free_money"]); ?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right"><b>（拼团）</b>是否单独购买</label>
								<div class="col-xs-6 ">
									<label class="radio-inline">
										<input type="radio" class="styled" name="group[buy_alone]" value="1"
										<?php if($group['buy_alone'] == 1): ?>checked<?php endif; ?>
										>
										是
									</label>
									<label class="radio-inline">
										<input type="radio" class="styled" name="group[buy_alone]" value="0"
										<?php if($group['buy_alone'] == 0): ?>checked<?php endif; ?>
										>
										否
									</label>

								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right">（拼团）</b>成团人数</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="一个团需要x人，才算拼团成功，正常发货"
									       name="group[team_people_num]"
									       value="<?php echo ($group["team_people_num"]); ?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right"><b>（拼团）</b>有效成团时间(小时)</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="0 表示无限制"
									       name="group[team_time_limit]"
									       value="<?php echo ($group["team_time_limit"]); ?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right"><b>（拼团）</b>单次购买数量限制</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="0 表示无限制"
									       name="group[team_goods_num]"
									       value="<?php echo ($group["team_goods_num"]); ?>">
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 control-label text-right"><b>（拼团）</b>团购商品排序</label>
								<div class="col-xs-6">
									<input type="text" class="form-control" placeholder="0 表示无限制" name="group[sort]"
									       value="<?php echo ($group["sort"]); ?>">
								</div>
							</div>
						</div>


						<!-- 整点秒杀设置 -->
						<div class="kind_hour"
						<?php if($goods['is_hour'] == 1): ?>style="display: block;"
							<?php else: ?>
							style="display: none;"<?php endif; ?>
						>

						<div class="form-group">
							<label class="col-xs-2 control-label text-right"><b>（秒杀）</b>开始时间</label>
							<div class="col-xs-6">
								<input type="text" class="form-control" id="start_time_hour" name="hour[start_time]"
								       value="<?php echo ($hour["start_time"]); ?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-2 control-label text-right"><b>（秒杀）</b>结束时间</label>
							<div class="col-xs-6">
								<input type="text" class="form-control" id="end_time_hour" name="hour[end_time]"
								       value="<?php echo ($hour["end_time"]); ?>">
							</div>
						</div>
				</div>
				<!-- 秒杀设置 -->

				<!-- 限时购设置 -->
				<div class="kind_time"
				<?php if($goods['is_time'] == 1): ?>style="display: block;"
					<?php else: ?>
					style="display: none;"<?php endif; ?>
				>
				<div class="form-group">
					<label class="col-xs-2 control-label text-right"><b>（限时购）</b>开始时间</label>
					<div class="col-xs-6">
						<input type="text" class="form-control" id="start_time_time" name="time[start_time]"
						       value="<?php echo ($time["start_time"]); ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-2 control-label text-right"><b>（限时购）</b>结束时间</label>
					<div class="col-xs-6">
						<input type="text" class="form-control" id="end_time_time" name="time[end_time]"
						       value="<?php echo ($time["end_time"]); ?>">
					</div>
				</div>
			</div>
			<!-- 限时购设置 -->

			<div class="form-group" style="display: none;">
				<label class="col-xs-2 control-label text-right">是否支持发货</label>
				<div class="col-xs-6 ">
					<label class="radio-inline">
						<input type="checkbox" name="is_shipping" id="is_shipping" value="1"
						<?php if($goods['is_shipping'] == 1): ?>checked<?php endif; ?>
						>
						商品发货（勾选则开启）
					</label>
				</div>
			</div>
			<div class="form-group" style="display: none;">
				<label class="col-xs-2 control-label text-right">是否支持自提</label>
				<div class="col-xs-6 ">
					<label class="radio-inline">
						<input type="checkbox" name="is_pick" id="is_pick" value="1"
						<?php if($goods['is_pick'] == 1): ?>checked<?php endif; ?>
						>
						商品自提（勾选则开启）
					</label>
				</div>
			</div>
			<script>
				$("#is_pick").on("click", function () {
					var kv = $(this).val();
					if ($(this).is(':checked')) {
						// 原来没选，现在选择了
						if (kv == 1) {
							// 是否限时购
							$(".mention").show("normal");
						}
					} else {
						// 原来选择，现在不再选择
						if (kv == 1) {
							$(".mention").hide("normal");
						}
					}
				});
			</script>


			<!--目前暂时不显示自提点列表  商品全部加入-->
			<div class="form-group" style="display: none;">
				<label class="col-xs-2 control-label text-right"></label>
				<div class="col-xs-6  mention"
				<?php if($goods['is_pick'] == 1): ?>style="display: block;"
					<?php else: ?>
					style="display: none;"<?php endif; ?>
				>
				<?php if(is_array($picks)): $i = 0; $__LIST__ = $picks;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="form-group" style="margin-left:2.2%">
						<input type="checkbox" name="picksite[]" value="<?php echo ($vo['id']); ?>"
						<?php if(in_array($vo['id'], $pickHave)): ?>checked<?php endif; ?>
						> <?php echo ($vo["title"]); ?>，<?php echo ($vo["name"]); ?> <?php echo ($vo["phone"]); ?> ，<?php echo (get_region_name($vo["province"])); ?> <?php echo (get_region_name($vo["city"])); ?>
						<?php echo (get_region_name($vo["district"])); ?> <?php echo ($vo["address"]); ?>，<?php echo (get_week_day($vo["work_day"])); ?>
						<?php echo ($vo["work_time_start"]); ?>-<?php echo ($vo["work_time_end"]); ?>
					</div><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>


		</div>

	</div>

	<!--图片信息-->
	<div class="sub_div t_two">
		<div class="form-group">
			<label class="col-xs-2 control-label text-right">商品封面图片</label>
			<div class="col-xs-6 control-label">
				<div class="placeholder">
					<img src="<?php echo ($goods_img); ?>" class="img_headimgurl">
				</div>
				<button type="button" class="layui-btn layui-btn-sm" id="upimg">
					<i class="layui-icon">&#xe67c;</i>上传图片
				</button>

				<input type="hidden" name="thumb" id="thumb" class="headimgurl"
				       value="<?php echo ($goods_img); ?>">
				<span style="color: red">（请上传大于450px的正方形, 非正方形会导致变形,图片大小不能超过3M）</span>

			</div>
		</div>
		<div class="form-group"  style="display: none">
			<label class="control-label col-lg-2">商品视频</label>
			<div class="col-lg-6">
				<div class="placeholder">
					<video src="<?php echo ($goods_video); ?>" class="headvideo" controls="controls"
					       style="width: 50%"></video>
					<!--<img src="" class="img_headimgurl">-->
				</div>

				<button type="button" class="layui-btn " id="upvideo">
					<i class="layui-icon">&#xe67c;</i>上传视频
				</button>
				<span style="color: red">（视频大小不能超过<?php echo ($size); ?>M）</span>
				<input type="hidden" name="video" class="headvideourl" value="<?php echo ($goods_video); ?>">

			</div>

		</div>
		<div class="form-group">
			<label class="col-xs-2 control-label text-right">上传商品轮播图片</label>
			<div class="col-xs-6 control-label">
				<div class="placeholder imgapd">
					<img src="<?php echo ($goods_img2); ?>" class="img_images ">

					<?php if(is_array($goods["images"])): foreach($goods["images"] as $key=>$vo2): ?><img src="<?php echo ($vo2); ?>" class="img_images hover_ext " h="IMG<?php echo ($key); ?>"><?php endforeach; endif; ?>

				</div>
				<button type="button" class="layui-btn layui-btn-sm" id="upimgs">
					<i class="layui-icon">&#xe67c;</i>上传图片（点击图片可删除）
				</button>
				<span style="color: red">（请上传大于450px的正方形, 非正方形会导致变形,图片大小不能超过3M）</span>

				<div class="for_clone">
					<?php if(is_array($goods["images"])): foreach($goods["images"] as $key=>$vo2): ?><input type="hidden" name="images[]" class="images img_ipt IMG<?php echo ($key); ?> "
						       value="<?php echo ($vo2); ?>"><?php endforeach; endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!--价格设置-->
	<div class="sub_div t_three">


		<div class="form-group" style="display: none;">
			<label class="col-xs-2 control-label text-right">规格类型</label>
			<div class="col-xs-3 ">
				<select name="type_id" id="type_id"
				        class="form-control select-form-control inline-block">
					<option value="0">请选择规格类型</option>
					<?php if(is_array($type_list)): foreach($type_list as $k=>$v): ?><option value="<?php echo ($v['id']); ?>"
						<?php if($v['id'] == $goods['type_id']): ?>selected="selected"<?php endif; ?>
						>
						<?php echo ($v['name']); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
			<div class="col-xs-3">
				<div class="col-lg-8">
					<input type="text" name="type_search" class="form-control type_search"
					       placeholder="请输入规格类型名称">
				</div>
				<div class="col-lg-4">
					<a href="javascript:;" class="layui-btn layui-btn-sm layui-btn-normal search_type">查询</a>
					<a href="javascript:;" class="layui-btn layui-btn-sm layui-btn-normal add_type">添加</a>
				</div>
			</div>
		</div>

		<!--规格重新处理 2020.07.28-->
		<div class="form-group">
			<label class="col-xs-2 control-label text-right">商品规格</label>
			<div class="col-xs-6">
				<div class="sku_div">
					<div class="">
						<div class="sku_wrap">
							<!--这里放置规格列表信息-->
							<div class="sku_3_1"
							<?php if($sku_spec_1 == 0): ?>style="display: none;"<?php endif; ?>
							>
							<div class="sku_title" dtn="1">
								<div class="sku_title_left">规格名：</div>
								<div class="sku_title_right">
									<select name="sku_1" id="sku_sel_1" class="ui fluid search dropdown dropdown_1 "
									        dty="one" onchange="change_sku_1()">
										<option value="0">请选择</option>
										<?php if($sku_spec_1 > 0): if(is_array($spec_list_have)): $i = 0; $__LIST__ = $spec_list_have;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo1["id"]); ?>"
												<?php if($sku_spec_1 == $vo1['id']): ?>selected<?php endif; ?>
												<?php if($sku_spec_2 == $vo1['id']): ?>disabled<?php endif; ?>
												<?php if($sku_spec_3 == $vo1['id']): ?>disabled<?php endif; ?>
												><?php echo ($vo1["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>
									</select>
								</div>
							</div>
							<div class="sku_list">
								<div class="sku_list_left">规格值：</div>
								<div class="sku_list_right sku_list_right_1 dropdown_1_1_1">
									<?php if($sku_spec_1 > 0): if(is_array($sku_spec_item_a_1)): $i = 0; $__LIST__ = $sku_spec_item_a_1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1_1): $mod = ($i % 2 );++$i;?><select name="sku_list_1_<?php echo ($vo1_1); ?>" id="sku_list_1_<?php echo ($vo1_1); ?>"
											        class="ui fluid search dropdown dropdown_1_1"
											        onchange="change_sku_item_1(this)" dty="two">
												<option value="0">请选择</option>
												<?php if(is_array($sku_spec_list_1)): $i = 0; $__LIST__ = $sku_spec_list_1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo1["id"]); ?>"
													<?php if($vo1['id'] == $vo1_1): ?>selected<?php endif; ?>
													<?php if (in_array($vo1['id'], $sku_spec_item_a_1) && $vo1['id'] != $vo1_1 ) { echo " disabled "; } ?>
													><?php echo ($vo1["item"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
											</select><?php endforeach; endif; else: echo "" ;endif; endif; ?>
								</div>
								<div>
									<a class="add_sub_spec_1" skunum="1">添加规格值</a>
								</div>
							</div>
						</div>

						<div class="sku_3_2"
						<?php if($sku_spec_2 == 0): ?>style="display: none;"<?php endif; ?>
						>
						<div class="sku_title" dtn="2">
							<div class="sku_title_left">规格名：</div>
							<div class="sku_title_right">
								<select name="sku_2" id="sku_sel_2" class="ui fluid search dropdown dropdown_2 "
								        dty="one"
								        onchange="change_sku_2()">
									<option value="0">请选择</option>
									<?php if($sku_spec_2 > 0): if(is_array($spec_list_have)): $i = 0; $__LIST__ = $spec_list_have;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo2): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo2["id"]); ?>"
											<?php if($sku_spec_2 == $vo2['id']): ?>selected<?php endif; ?>
											<?php if($sku_spec_1 == $vo2['id']): ?>disabled<?php endif; ?>
											<?php if($sku_spec_3 == $vo2['id']): ?>disabled<?php endif; ?>
											><?php echo ($vo2["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>
								</select>
							</div>
						</div>
						<div class="sku_list">
							<div class="sku_list_left">规格值：</div>
							<div class="sku_list_right sku_list_right_2 dropdown_1_1_2">
								<?php if($sku_spec_2 > 0): if(is_array($sku_spec_item_a_2)): $i = 0; $__LIST__ = $sku_spec_item_a_2;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1_1): $mod = ($i % 2 );++$i;?><select name="sku_list_2_<?php echo ($vo1_1); ?>" id="sku_list_2_<?php echo ($vo1_1); ?>"
										        class="ui fluid search dropdown dropdown_1_2"
										        onchange="change_sku_item_2(this)" dty="two">
											<option value="0">请选择</option>
											<?php if(is_array($sku_spec_list_2)): $i = 0; $__LIST__ = $sku_spec_list_2;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo1["id"]); ?>"
												<?php if($vo1['id'] == $vo1_1): ?>selected<?php endif; ?>
												<?php if (in_array($vo1['id'], $sku_spec_item_a_2) && $vo1['id'] != $vo1_1 ) { echo " disabled "; } ?>
												><?php echo ($vo1["item"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
										</select><?php endforeach; endif; else: echo "" ;endif; endif; ?>
							</div>
							<div>
								<a class="add_sub_spec_2" skunum="1">添加规格值</a>
							</div>
						</div>
					</div>

					<div class="sku_3_3"
					<?php if($sku_spec_3 == 0): ?>style="display: none;"<?php endif; ?>
					>
					<div class="sku_title" dtn="3">
						<div class="sku_title_left">规格名：</div>
						<div class="sku_title_right">
							<select name="sku_3" id="sku_sel_3" class="ui fluid search dropdown dropdown_3 " dty="one"
							        onchange="change_sku_3()">
								<option value="0">请选择</option>
								<?php if($sku_spec_3 > 0): if(is_array($spec_list_have)): $i = 0; $__LIST__ = $spec_list_have;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo3): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo3["id"]); ?>"
										<?php if($sku_spec_3 == $vo3['id']): ?>selected<?php endif; ?>
										<?php if($sku_spec_1 == $vo3['id']): ?>disabled<?php endif; ?>
										<?php if($sku_spec_2 == $vo3['id']): ?>disabled<?php endif; ?>
										><?php echo ($vo3["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>
							</select>
						</div>
					</div>
					<div class="sku_list">
						<div class="sku_list_left">规格值：</div>
						<div class="sku_list_right sku_list_right_3 dropdown_1_1_3">
							<?php if($sku_spec_3 > 0): if(is_array($sku_spec_item_a_3)): $i = 0; $__LIST__ = $sku_spec_item_a_3;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1_1): $mod = ($i % 2 );++$i;?><select name="sku_list_3_<?php echo ($vo1_1); ?>" id="sku_list_3_<?php echo ($vo1_1); ?>"
									        class="ui fluid search dropdown dropdown_1_3"
									        onchange="change_sku_item_3(this)" dty="two">
										<option value="0">请选择</option>
										<?php if(is_array($sku_spec_list_3)): $i = 0; $__LIST__ = $sku_spec_list_3;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo1["id"]); ?>"
											<?php if($vo1['id'] == $vo1_1): ?>selected<?php endif; ?>
											<?php if (in_array($vo1['id'], $sku_spec_item_a_3) && $vo1['id'] != $vo1_1 ) { echo " disabled "; } ?>
											><?php echo ($vo1["item"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
									</select><?php endforeach; endif; else: echo "" ;endif; endif; ?>
						</div>
						<div>
							<a class="add_sub_spec_3" skunum="1">添加规格值</a>
						</div>
					</div>
				</div>

			</div>
			<div class="add_btn">
				<span class="layui-btn layui-btn-sm layui-btn-primary new_add_spec">添加规格项目</span>
				<input type="hidden" name="spec_id_1" class="spec_id_1" value="<?php echo ($sku_spec_1); ?>">
				<input type="hidden" name="spec_item_id_1" class="spec_item_id_1" value="<?php echo ($sku_spec_item_1); ?>">

				<input type="hidden" name="spec_id_2" class="spec_id_2" value="<?php echo ($sku_spec_2); ?>">
				<input type="hidden" name="spec_item_id_2" class="spec_item_id_2" value="<?php echo ($sku_spec_item_2); ?>">

				<input type="hidden" name="spec_id_3" class="spec_id_3" value="<?php echo ($sku_spec_3); ?>">
				<input type="hidden" name="spec_item_id_3" class="spec_item_id_3" value="<?php echo ($sku_spec_item_3); ?>">

			</div>
		</div>
	</div>
</div>

</div>

<!--规格重新处理 --end  2020.07.28-->

<div class="form-group">
	<label class="col-xs-2 control-label text-right">规格价格<br>
		<span style="color: #ff3b30;display: none;">（若此处<br>规格数量较多<br>建议新增“规格类型”<br>然后在新类型下<br>新建规格）</span>
	</label>
	<div class="col-xs-6">
		<div id="ajax_spec_data"><!-- ajax 返回规格--></div>
		<div id="pesc_price"></div>
	</div>
</div>
</div>

<!--详情设置-->
<div class="sub_div t_four">
	<div class="form-group">
		<label class="col-xs-2 control-label text-right">商品详情</label>
		<div class="col-xs-6">
			<script id="editor" type="text/plain" style="width:100%;height:400px;" name="content"><?php echo ($goods["content"]); ?>

			</script>
		</div>
	</div>
</div>

<input type="hidden" name="goods_id" value="<?php echo ($goods["id"]); ?>">

<div class="form-group">
	<label class="col-xs-2 control-label"></label>
	<div class="col-xs-8">
		<button class="layui-btn layui-btn-sm layui-btn-normal" type="submit">提交</button>
		<a href="javascript:history.go(-1);" class="layui-btn layui-btn-sm layui-btn-primary">返回</a>
	</div>
</div>
</form>

<!-- page end -->

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

<!-- 上传图片 -->
<script>

	layui.use('form', function () {
		var form = layui.form;
	});

	/* 秒杀时间插件 */
	laydate.render({
		elem: '#start_time_hour',
		type: 'datetime'
	});
	laydate.render({
		elem: '#end_time_hour',
		type: 'datetime'
	});
	/* 限时购时间插件 */
	laydate.render({
		elem: '#start_time_time',
		type: 'datetime'
	});
	laydate.render({
		elem: '#end_time_time',
		type: 'datetime'
	});

	var ue = UE.getEditor('editor');
	var goods_id = '<?php echo ($goods_id); ?>'

	var type_id = '<?php echo ($type_id); ?>';

	function check() {
		var name = $("#name").val();
		var subhead = $("#subhead").val();
		var thumb = $("#thumb").val();
		var images = $("#images").val();

		var price = $("#price").val();
		var market_price = $("#market_price").val();
		var shop_fee_id = $("#shop_fee_id").val();
		var cat_id_1 = $("#cat_id_1").val();
		var shipping_rule_id = $("#shipping_rule_id").val();
		var type_id = $("#type_id").val();
		if (!name) {
			alert("请填写商品名称");
			return false;
		}
		if (!subhead) {
			alert("请填写商品副标题/简介");
			return false;
		}
		if (thumb == '/Public/images/img_place.png') {
			alert("请上传商品封面图片");
			return false;
		}
		// if (!images) {
		// 	alert("请上传商品轮播图片");
		// 	return false;
		// }
		if (!price) {
			alert("请填写本店价格");
			return false;
		}
		if (!market_price) {
			alert("请填写市场价格");
			return false;
		}
		if (shop_fee_id == 0) {
			// alert("请选择服务费比例");
			// return false;
		}
		if (cat_id_1 == 0) {
			alert("请选择商品分类");
			return false;
		}
		if (shipping_rule_id == 0) {
			alert("请选择商品运费模板");
			return false;
		}
		if (type_id == 0) {
			// alert("请选择规格类型");
			// return false;
		}
		return true;
	}


	/** 以下 商品规格相关 js*/

	// 商品类型切换时 ajax 调用  返回不同的属性输入框
	$("#type_id").change(function () {
		if ($(this).val() != 0) {
			type_id = $(this).val();
		} else {
			type_id = '<?php echo ($type_id); ?>';
		}

		// spec(type_id, goods_id);
		$.ajax({
			type: 'GET',
			data: {goods_id: goods_id, type_id: type_id},
			url: "<?php echo U('Goods/ajax_spec_select');?>",
			async: false,
			success: function (data) {
				$("#ajax_spec_data").html('')
				$("#ajax_spec_data").append(data);
				ajaxGetSpecInput();	// 触发完  马上处罚 规格输入框
				$('.add_spec').click(function () {
					index = layer.open({
						type: 2,
						area: ['700px', '450px'],
						fixed: false, //不固定
						maxmin: true,
						content: "<?php echo U('add_spec');?>?type_id=" + $(this).attr('data-id')
					});
				});
				$('.add_spec_child').click(function () {
					index = layer.open({
						type: 2,
						area: ['700px', '450px'],
						fixed: false, //不固定
						maxmin: true,
						content: "<?php echo U('add_spec');?>?spec_id=" + $(this).attr('data-id')
					});
				});

				$('img.del').click(function () {
					var id = $(this).attr('data-id');
					layer.confirm('确认要删除该规格项？', {
						btn: ['是的', '取消'] //按钮
					}, function () {

						$.post("<?php echo U('ajax_delete_spec');?>", {id: id}, function (res) {

							if (res.status == 1) {
								layer.msg('操作成功!');
								$("#type_id").trigger('change', type_id);
								return;
							}
							layer.msg(res.info);

						}, 'json');
					});
				});
			}
		});
	});
	// 触发商品规格
	var index;
	// $("#type_id").trigger('change');

	// 按钮切换 class
	$("#ajax_spec_data").on("click", "span", function () {

		if ($(this).hasClass('spam_check')) {
			$(this).removeClass('spam_check');
		} else {
			$(this).addClass('spam_check');
		}
		ajaxGetSpecInput();
	});

	function ajaxGetSpecInput() {
		//	  var spec_arr = {1:[1,2]};// 用户选择的规格数组
		//	  spec_arr[2] = [3,4]; \
		var spec_arr = {};// 用户选择的规格数组
		var spec_obj = {};
		// 选中了哪些属性
		$("#ajax_spec_data  span.child_span").each(function () {
			if ($(this).hasClass('spam_check')) {
				var tmp = spec_obj[$(this).attr('data-title')];
				if (tmp) {
					tmp.push($(this).attr('data-id'));
					spec_obj[$(this).attr('data-title')] = tmp;
				} else {
					var tmp = new Array();
					tmp.push($(this).attr('data-id'));
					spec_obj[$(this).attr('data-title')] = tmp;
				}
				$(this).removeClass('spam_check');
				$(this).addClass('spam_check')
			}
			for (var k in spec_obj) {
				if (spec_obj[k].length <= 0) {
					delete spec_obj[k];
				}
			}

		});
		ajaxGetSpecInput2(spec_obj); // 显示下面的输入框
	}


	/**
	 * 根据用户选择的不同规格选项
	 * 返回 不同的输入框选项
	 */
	function ajaxGetSpecInput2(spec_arr) {
		$.ajax({
			type: 'POST',
			// data:{'spec_arr':spec_arr},
			// url:"/index.php/admin/Goods/ajaxGetSpecInput/goods_id/"+goods_id,
			data: {'spec_arr': JSON.stringify(spec_arr), 'goods_id': goods_id},
			url: "/index.php/admin/Goods/ajaxGetSpecInput",
			success: function (data) {

				$("#pesc_price").html('')
				$("#pesc_price").append(data);
				hbdyg(Object.keys(spec_arr).length);  // 合并单元格
			}
		});
	}

	function get_category(id, next, select_id) {

		var url = '/index.php?m=Admin&c=Goods&a=getCategory&parent_id=' + id;
		$.ajax({
			type: "GET",
			url: url,
			error: function (request) {
				alert("服务器繁忙, 请联系管理员!");
				return;
			},
			success: function (v) {
				v = "<option value='0'>请选择商品分类</option>" + v;
				$('#' + next).empty().html(v);
				(select_id > 0) && $('#' + next).val(select_id);//默认选中
			}
		});
	}

	layui.use('upload', function () {
		var upload = layui.upload;

		//执行实例
		var uploadInst = upload.render({

			elem: '#upimg' 							//绑定元素
			, url: '/index.php/Admin/Common/uploads' 	//上传接口
			, data: {handlename: "headimg"}			// 接口接收值
			, field: "headimg"						// input name，与接口接收值 一致
			, done: function (res) {
				//上传完毕回调-不一定成功，需要判断
				if (res.status > 0) {
					// 异常提示
					layer.alert(res.result);
				} else {
					$(".headimgurl").val(res.result);
					$(".img_headimgurl").attr("src", res.result);
				}
			}
			, error: function (e) {
				//请求异常回调
			}
		});
	});
	layui.use('upload', function () {
		var upload = layui.upload;

		//执行实例
		var uploadInst = upload.render({

			elem: '#upvideo' 							//绑定元素
			, url: '/index.php/Admin/Common/uploads' 	//上传接口
			, data: {handlename: "headimg", video: "1"}			// 接口接收值
			, field: "headimg"						// input name，与接口接收值 一致
			, done: function (res) {

				//上传完毕回调-不一定成功，需要判断
				if (res.status > 0) {
					// 异常提示
					layer.alert(res.result);
				} else {
					$(".headvideourl").val(res.result);
					$(".headvideo").attr("src", res.result);
				}
			}
			, error: function (e) {
				//请求异常回调
			}
		});
	});

	layui.use('upload', function () {
		var upload = layui.upload;

		//执行实例
		var uploadInst = upload.render({

			elem: '#upimgs' 							//绑定元素
			, url: '/index.php/Admin/Common/uploads' 	//上传接口
			, data: {handlename: "headimg"}			// 接口接收值
			, field: "headimg"						// input name，与接口接收值 一致
			, done: function (res) {
				//上传完毕回调-不一定成功，需要判断
				if (res.status > 0) {
					// 异常提示
					layer.alert(res.result);
				} else {
					var tsH = Date.parse(new Date());
					tsH = "IMG" + tsH;

					var iptStr = '<input type="hidden" name="images[]" class="images img_ipt ' + tsH + ' " value="' + res.result + '">';
					$(".for_clone").append(iptStr);
					$(".imgapd").append("<img src=" + res.result + " class='img_images hover_ext' h='" + tsH + "' >");
				}
			}
			, error: function (e) {
				//请求异常回调
			}
		});
	});

	// 合并单元格
	function hbdyg(maxCol) {
		var tab = document.getElementById("spec_input_tab"); //要合并的tableID
		var val, count, start;  //maxCol：合并单元格作用到多少列
		if (tab != null) {
			for (var col = maxCol - 1; col >= 0; col--) {
				count = 1;
				val = "";
				for (var i = 0; i < tab.rows.length; i++) {
					if (val == tab.rows[i].cells[col].innerHTML) {
						count++;
					} else {
						if (count > 1) { //合并
							start = i - count;
							tab.rows[start].cells[col].rowSpan = count;
							for (var j = start + 1; j < i; j++) {
								tab.rows[j].cells[col].style.display = "none";
							}
							count = 1;
						}
						val = tab.rows[i].cells[col].innerHTML;
					}
				}
				if (count > 1) { //合并，最后几行相同的情况下
					start = i - count;
					tab.rows[start].cells[col].rowSpan = count;
					for (var j = start + 1; j < i; j++) {
						tab.rows[j].cells[col].style.display = "none";
					}
				}
			}
		}
	}


	$(document).ready(function () {

		$(".imgapd").on("click", ".hover_ext", function () {
			var that = $(this);
			layer.confirm('是否删除此图片?', {icon: 3, title: '提示'}, function (index) {

				var h = that.attr("h");
				if (h) {
					$(".for_clone").find("." + h).remove();
					that.remove();
				} else {
					layer.msg('删除失败，请尝试刷新');
				}

				layer.close(index);
			});
		});


	});
	$(function () {

		$('form').on("change", ".spec_img_file", function (e) {
			var id = $(this).data("number");
			var formData = new FormData();
			var imgUrl = $('.spec_img' + id).value;
			formData.append("headimg", document.getElementById('spec_img' + id).files[0]);
			formData.append("handlename", 'headimg');
			$.ajax({
				url: '/index.php/Admin/Common/uploads',
				type: 'post',
				data: formData,
				contentType: false,
				dataType: "json",
				processData: false,
				success: function (res, index) {
					if (res.status > 0) {
						layer.msg(res.result);
					} else {
						$('.spec_img' + id).val(res.result);
						$('.spec_' + id).attr("src", res.result)
					}
				},
				error: function () {
					layer.msg('发送失败');
				}
			});
		});

		$(".cate_list").on("click", ".ext_cate", function () {
			var that = $(this);
			var cid = that.attr("cid");
			if (cid == "c0") {
				return true;
			}
			layer.confirm('是否删除此附属分类?', {icon: 3, title: '提示'}, function (index) {
				if (cid) {
					$(".cate_list").find("." + cid).remove();
					that.remove();
				} else {
					layer.msg('删除失败，请尝试刷新');
				}

				layer.close(index);
			});
		});

	})

	function changeCate(id, txt) {
		var cate_div = $("#cate_div").clone(true).text(txt).removeAttr("id").attr("cid", "c" + id);
		var cate_ipt = $("#cate_ipt").clone(true).val(id).removeAttr("id").addClass("c" + id);
		$(".cate_list").append(cate_div);
		$(".cate_list").append(cate_ipt);
	}

	var is_edit = 0;      // 是否编辑商品规格
	var show_sku_1 = 0;   // 规格1 是否显示，1显示，0不显示
	var show_sku_2 = 0;   // 规格2 是否显示，1显示，0不显示
	var show_sku_3 = 0;   // 规格3 是否显示，1显示，0不显示
	var add_num = "<?php echo ($add_num); ?>";
	var shop_id = "<?php echo ($shop_id); ?>";    // 商家ID

	$(function () {

		// 上方基本信息、图片、规格、详情的切换
		$(".type_0412_1").on("click", function () {
			$(this).addClass("type_0412_active").siblings().removeClass("type_0412_active");
			var dvc = $(this).attr("data-num");
			$("." + dvc).show().siblings(".sub_div").hide();
		});

		// 上方商品类型的选择
		$(".type_0411_1").on("click", function () {
			var hot_id = $(this).attr("data-tn");
			$(this).addClass("type_0411_active").siblings().removeClass("type_0411_active");
			$("input[name=hot_id]").val(hot_id);

			if (hot_id == 1) {
				$('#range').show();
			} else {
				$('#range').hide();
			}
			if (hot_id == 2) {
				$('#express').show();
			} else {
				$('#express').hide();
			}
		});


		get_final_spec();   // 打开页面，请求规格组合

		// 新添加规格  @zd.220.07.29
		if (add_num == 2) {
			show_sku_1 = 1;
		}
		if (add_num == 3) {
			show_sku_1 = 1;
			show_sku_2 = 1;
		}
		if (add_num == 4) {
			show_sku_1 = 1;
			show_sku_2 = 1;
			show_sku_3 = 1;
		}
		if (goods_id > 0) {
			is_edit = 1;
		}
		$(".new_add_spec").on("click", function () {
			if (add_num >= 4) {
				layer.msg("最多只能添加三组规格");
				return false;
			}
			$.ajax({
				type: 'GET',
				data: {spec_id: 0, spec_name: '', shop_id: shop_id, goods_id: goods_id, add_num: add_num},
				url: "<?php echo U('new_spec_list');?>",
				async: false,
				dataType: 'json',
				success: function (data) {

					if (data.status == 1) {
						layer.msg(data.msg);
						return false;
					}
					var option_str_spec = "";
					var list = data.result.list;
					var spec_id = data.result.spec_id;
					var itemlist = data.result.itemlist;
					var spec_name = data.result.spec_name;

					console.log('---list----');
					console.log(list);

					add_spec_repair(list);

					console.log('add_num:' + add_num);
					console.log('is_edit:' + is_edit);

					if (is_edit == 1) {
						if (show_sku_1 == 0) {
							show_sku_1 = 1;
							$(".sku_3_1").show();
							add_num = parseInt(add_num) + 1;
							console.log('add_num : ' + add_num);
						} else if (show_sku_2 == 0) {
							show_sku_2 = 2;
							$(".sku_3_2").show();
							add_num = parseInt(add_num) + 1;
							console.log('add_num : ' + add_num);
						} else if (show_sku_3 == 0) {
							show_sku_3 = 3;
							$(".sku_3_3").show();
							add_num = parseInt(add_num) + 1;
							console.log('add_num : ' + add_num);
						}
					} else {

						if (show_sku_1 == 0) {
							show_sku_1 = 1;
							$(".sku_3_1").show();
						} else if (show_sku_2 == 0) {
							show_sku_2 = 1;
							$(".sku_3_2").show();
						} else if (show_sku_3 == 0) {
							show_sku_3 = 1;
							$(".sku_3_3").show();
						}

						add_num = parseInt(add_num)+1;
					}


				}
			});
		});


		// 鼠标放在规格名和规格值上面时
		$("body").on("mouseover", ".dropdown", function () {
			$(this).find(".z_del_icon").show();
		});
		$("body").on("mouseout", ".dropdown", function () {
			$(this).find(".z_del_icon").hide();
		});

		$("body").on("click", ".z_del_icon", function () {
			var that = $(this);
			console.log(that);

			layer.confirm('是否删除此规格?', {icon: 3, title: '提示'}, function (index) {
				var dty = that.siblings("select").attr("dty");
				if (dty == "two") {
					// 删除单个规格值
					that.parent().remove();
					reset_spec();
					get_final_spec();
				} else if (dty == "one") {
					// 删除单个规格名
					that.parents(".sku_title").siblings(".sku_list").find(".sku_list_right").html('');
					that.parents(".sku_title").find("select").empty();
					that.parents(".sku_title").parent().hide();
					var dtn = that.parents(".sku_title").attr("dtn");
					console.log('dtn:' + dtn);
					if (dtn == 1) {
						show_sku_1 = 0;
					} else if (dtn == 2) {
						show_sku_2 = 0;
					} else if (dtn == 3) {
						show_sku_3 = 0;
					}
					add_num = parseInt(add_num) - 1;
					console.log('add_num:' + add_num);
					console.log('show_sku_1:' + show_sku_1);
					console.log('show_sku_2:' + show_sku_2);
					console.log('show_sku_3:' + show_sku_3);
					reset_spec();
					get_final_spec();
				}


				layer.close(index);
			});
		});

	})


	/*
	 * 添加规格：
	 * 1，规格名，如果只选择，则拿到规格对应的ID，用此ID去查询包含的规格值，并赋值给规格值（后面的添加按钮，也要有作用）
	 * 2，如果规格名不再选择，而是输入的，则先通过接口，把新规格名写入，然后再去选择（或填写）规格值。
	 * 备注：如果规格名 不是选择的，而是输入的新的，则要插入数据库。
	 *      如果规格值 不是选择的，而是输入的新的，则同样要插入数据库。
	 *
	 * */
	var sku_spec_list = '';      // 规格名列表
	var sku_spec_list_k = '';      // 规格名列表
	var sku_item_num_1 = 0;                 // 第1个规格值的数量（排序0开始）
	var sku_itemlist_1 = '';                // 第1个规格值列表
	var sku_item_num_2 = 0;                 // 第2个规格值的数量（排序0开始）
	var sku_itemlist_2 = '';                // 第2个规格值列表
	var sku_item_num_3 = 0;                 // 第3个规格值的数量（排序0开始）
	var sku_itemlist_3 = '';                // 第3个规格值列表
	var sku_item_arr_1 = new Array();       // 规格值 切换
	var sku_item_arr_2 = new Array();
	var sku_item_arr_3 = new Array();

	if (<?php echo ($sku_spec_list_j_1); ?>) {
		sku_itemlist_1 = <?php echo ($sku_spec_list_j_1); ?>;
	}
	if (<?php echo ($sku_spec_list_j_2); ?>) {
		sku_itemlist_2 = <?php echo ($sku_spec_list_j_2); ?>;
	}
	if (<?php echo ($sku_spec_list_j_3); ?>) {
		sku_itemlist_3 = <?php echo ($sku_spec_list_j_3); ?>;
	}

	// 规格名切换
	function change_sku_1() {
		console.log('--e--');

		var spec_id = $("#sku_sel_1 option:selected").val();
		var spec_name = $("#sku_sel_1 option:selected").text();
		var option_class = $("#sku_sel_1 option:selected").attr("class");

		if (option_class == "addition") {
			spec_id = 0;
		}
		// 把信息传递过去，请来过来最新的规格名和规格值
		$.ajax({
			type: 'GET',
			data: {spec_id: spec_id, spec_name: spec_name, shop_id: shop_id, goods_id: goods_id, add_num: 1},
			url: "<?php echo U('new_spec_list');?>",
			async: false,
			dataType: 'json',
			success: function (data) {
				if (data.status == 1) {
					layer.msg(data.msg);
					return false;
				}

				$(".spec_id_1").val(spec_id);
				var list = data.result.list;
				sku_itemlist_1 = data.result.itemlist;
				var spec_name = data.result.spec_name;
				spec_id = data.result.spec_id;

				// 1::选择之后，把其他下拉框中的有的，先删除
				del_repeat_spec(1);

				// 2::把其他输入框补全以前删除的
				add_spec_repair(list);

				// 3::新增规格名时，不要重复。

				var sku_list_html = add_sub_item(0, 1);
				$(".sku_list_right_1").html('');
				$(".sku_list_right_1").append(sku_list_html);
				$('.dropdown_1_1').dropdown({
					allowAdditions: true,
				});
				$('.dropdown_1').dropdown('set selected', spec_id);

			}
		});

		$(".spec_item_id_1").val(0);
		$("input[name=sku_list_1]").val('');
		get_final_spec();
	}

	function change_sku_2() {
		console.log('--e--');

		var spec_id = $("#sku_sel_2 option:selected").val();
		var spec_name = $("#sku_sel_2 option:selected").text();
		var option_class = $("#sku_sel_2 option:selected").attr("class");

		if (option_class == "addition") {
			spec_id = 0;
		}
		// 把信息传递过去，请来过来最新的规格名和规格值
		$.ajax({
			type: 'GET',
			data: {spec_id: spec_id, spec_name: spec_name, shop_id: shop_id, goods_id: goods_id, add_num: 2},
			url: "<?php echo U('new_spec_list');?>",
			async: false,
			dataType: 'json',
			success: function (data) {
				if (data.status == 1) {
					layer.msg(data.msg);
					return false;
				}

				$(".spec_id_2").val(spec_id);
				var list = data.result.list;
				sku_itemlist_2 = data.result.itemlist;
				var spec_name = data.result.spec_name;
				spec_id = data.result.spec_id;

				// 1::选择之后，把其他下拉框中的有的，先删除
				del_repeat_spec(2);

				// 2::把其他输入框补全以前删除的
				add_spec_repair(list);

				// 3::新增规格名时，不要重复。

				var sku_list_html = add_sub_item(0, 2);
				$(".sku_list_right_2").html('');
				$(".sku_list_right_2").append(sku_list_html);
				$('.dropdown_1_2').dropdown({
					allowAdditions: true,
				});
				$('.dropdown_2').dropdown('set selected', spec_id);
			}
		});
		$(".spec_item_id_2").val(0);
		$("input[name=sku_list_2]").val('');
		get_final_spec();
	}

	function change_sku_3() {
		console.log('--e--');

		var spec_id = $("#sku_sel_3 option:selected").val();
		var spec_name = $("#sku_sel_3 option:selected").text();
		var option_class = $("#sku_sel_3 option:selected").attr("class");

		if (option_class == "addition") {
			spec_id = 0;
		}
		// 把信息传递过去，请来过来最新的规格名和规格值
		$.ajax({
			type: 'GET',
			data: {spec_id: spec_id, spec_name: spec_name, shop_id: shop_id, goods_id: goods_id, add_num: 3},
			url: "<?php echo U('new_spec_list');?>",
			async: false,
			dataType: 'json',
			success: function (data) {
				if (data.status == 1) {
					layer.msg(data.msg);
					return false;
				}
				$(".spec_id_3").val(spec_id);
				var list = data.result.list;
				sku_itemlist_3 = data.result.itemlist;
				var spec_name = data.result.spec_name;
				spec_id = data.result.spec_id;

				// 1::选择之后，把其他下拉框中的有的，先删除
				del_repeat_spec(3);

				// 2::把其他输入框补全以前删除的
				add_spec_repair(list);

				// 3::新增规格名时，不要重复

				var sku_list_html = add_sub_item(0, 3);
				$(".sku_list_right_3").html('');
				$(".sku_list_right_3").append(sku_list_html);
				$('.dropdown_1_3').dropdown({
					allowAdditions: true,
				});
				$('.dropdown_3').dropdown('set selected', spec_id);
			}
		});
		$(".spec_item_id_3").val(0);
		$("input[name=sku_list_3]").val('');
		get_final_spec();
	}

	function change_sku_item_1(that) {
		console.log('--change_sku_item_1--');

		var sku_item_select_id = $(that).attr("id");

		var spec_item_id = 0;       // 规格值ID
		var sku_list_1 = '';        // 规格值输入框中的内容
		var spec_id = 0;            // 规格名ID
		var go_to_req = 0;          // 是否去请求，1去请求，0不请求
		var sku_item_class = '';

		spec_id = $("#sku_sel_1 option:selected").val();

		var handle = "#" + sku_item_select_id;
		var handle_str = "#" + sku_item_select_id + " option";
		var handle_str_selected = "#" + sku_item_select_id + " option:selected";
		spec_item_id = $(handle_str_selected).val();

		// 在下拉列表中，找到了对应的值
		$(handle_str).each(function (index, ele) {
			var option_value = $(this).val();
			if (spec_item_id == option_value && spec_item_id.length > 0) {
				// spec_id = $(this).attr("spec_id");
				sku_list_1 = $(this).text();
				sku_item_class = $(this).attr("class");
			}
		});

		if (spec_id < 1) {
			layer.msg("请选择规格名");
			return false;
		}
		if (sku_item_class == "addition") {
			go_to_req = 1;
			spec_item_id = 0;   // 重新请求，一般代表是新填写的规格值
		} else {
			go_to_req = 0;
		}
		var filter_id = $(".spec_item_id_1").val(); // 已选择的ID

		if (go_to_req == 1) {
			$.ajax({
				type: 'GET',
				data: {
					spec_id: spec_id,
					spec_item_name: sku_list_1,
					shop_id: shop_id,
					goods_id: goods_id,
					add_num: 1,
					filter_id: filter_id
				},
				url: "<?php echo U('new_spec_item_list');?>",
				async: false,
				dataType: 'json',
				success: function (data) {
					if (data.status == 1) {
						layer.msg(data.msg);
						return false;
					}
					sku_itemlist_1 = data.result.list;
					var itemlist = data.result.itemlist;
					var spec_item_id = data.result.spec_item_id;
					var spec_item_name = data.result.spec_item_name;

					var option_str_item = "";
					for (y in itemlist) {
						var option_res = itemlist[y];
						option_str_item += "<option value='" + option_res.id + "' spec_id='" + option_res.spec_id + "'>" + option_res.item + "</option>";
					}
					console.log('--option_str_item----');
					console.log(option_str_item);
					$(handle).html(option_str_item);
					$('.dropdown_1_1_1').dropdown({
						allowAdditions: true,
					});
					$(handle).dropdown(
						'set selected', spec_item_id
					);
				}
			});
		}
		// 查一下同级所有选择的规格值，并更新隐藏域和数组
		get_spec_item_1();
	}

	function change_sku_item_2(that) {
		console.log('--change_sku_item_2--');

		var sku_item_select_id = $(that).attr("id");

		var spec_item_id = 0;          // 规格值ID
		var sku_list_2 = '';        // 规格值输入框中的内容
		var spec_id = 0;  // 规格名ID
		var go_to_req = 0;  // 是否去请求，1去请求，0不请求
		var sku_item_class = '';

		spec_id = $("#sku_sel_2 option:selected").val();

		var handle = "#" + sku_item_select_id;
		var handle_str = "#" + sku_item_select_id + " option";
		var handle_str_selected = "#" + sku_item_select_id + " option:selected";
		spec_item_id = $(handle_str_selected).val();

		// 在下拉列表中，找到了对应的值
		$(handle_str).each(function (index, ele) {
			var option_value = $(this).val();
			if (spec_item_id == option_value && spec_item_id.length > 0) {
				// spec_id = $(this).attr("spec_id");
				sku_list_2 = $(this).text();
				sku_item_class = $(this).attr("class");
			}
		});

		if (spec_id < 1) {
			layer.msg("请选择规格名");
			return false;
		}
		if (sku_item_class == "addition") {
			go_to_req = 1;
			spec_item_id = 0;   // 重新请求，一般代表是新填写的规格值
		} else {
			go_to_req = 0;
		}
		var filter_id = $(".spec_item_id_1").val(); // 已选择的ID

		if (go_to_req == 1) {
			$.ajax({
				type: 'GET',
				data: {
					spec_id: spec_id,
					spec_item_name: sku_list_2,
					shop_id: shop_id,
					goods_id: goods_id,
					add_num: 2,
					filter_id: filter_id
				},
				url: "<?php echo U('new_spec_item_list');?>",
				async: false,
				dataType: 'json',
				success: function (data) {
					if (data.status == 1) {
						layer.msg(data.msg);
						return false;
					}
					sku_itemlist_2 = data.result.list;
					var itemlist = data.result.itemlist;
					var spec_item_id = data.result.spec_item_id;
					var spec_item_name = data.result.spec_item_name;

					var option_str_item = "";
					for (y in itemlist) {
						var option_res = itemlist[y];
						option_str_item += "<option value='" + option_res.id + "' spec_id='" + option_res.spec_id + "'>" + option_res.item + "</option>";
					}
					$(handle).html(option_str_item);
					$('.dropdown_1_1_2').dropdown({
						allowAdditions: true,
					});
					$(handle).dropdown(
						'set selected', spec_item_id
					);
				}
			});
		}
		// 查一下同级所有选择的规格值，并更新隐藏域和数组
		get_spec_item_2();
	}

	function change_sku_item_3(that) {
		console.log('--change_sku_item_3--');

		var sku_item_select_id = $(that).attr("id");

		var spec_item_id = 0;          // 规格值ID
		var sku_list_3 = '';        // 规格值输入框中的内容
		var spec_id = 0;  // 规格名ID
		var go_to_req = 0;  // 是否去请求，1去请求，0不请求
		var sku_item_class = '';

		spec_id = $("#sku_sel_3 option:selected").val();

		var handle = "#" + sku_item_select_id;
		var handle_str = "#" + sku_item_select_id + " option";
		var handle_str_selected = "#" + sku_item_select_id + " option:selected";
		spec_item_id = $(handle_str_selected).val();

		// 在下拉列表中，找到了对应的值
		$(handle_str).each(function (index, ele) {
			var option_value = $(this).val();
			if (spec_item_id == option_value && spec_item_id.length > 0) {
				// spec_id = $(this).attr("spec_id");
				sku_list_3 = $(this).text();
				sku_item_class = $(this).attr("class");
			}
		});

		if (spec_id < 1) {
			layer.msg("请选择规格名");
			return false;
		}
		if (sku_item_class == "addition") {
			go_to_req = 1;
			spec_item_id = 0;   // 重新请求，一般代表是新填写的规格值
		} else {
			go_to_req = 0;
		}
		var filter_id = $(".spec_item_id_1").val(); // 已选择的ID

		if (go_to_req == 1) {
			$.ajax({
				type: 'GET',
				data: {
					spec_id: spec_id,
					spec_item_name: sku_list_3,
					shop_id: shop_id,
					goods_id: goods_id,
					add_num: 3,
					filter_id: filter_id
				},
				url: "<?php echo U('new_spec_item_list');?>",
				async: false,
				dataType: 'json',
				success: function (data) {
					if (data.status == 1) {
						layer.msg(data.msg);
						return false;
					}
					sku_itemlist_3 = data.result.list;
					var itemlist = data.result.itemlist;
					var spec_item_id = data.result.spec_item_id;
					var spec_item_name = data.result.spec_item_name;

					var option_str_item = "";
					for (y in itemlist) {
						var option_res = itemlist[y];
						option_str_item += "<option value='" + option_res.id + "' spec_id='" + option_res.spec_id + "'>" + option_res.item + "</option>";
					}
					$(handle).html(option_str_item);
					$('.dropdown_1_1_3').dropdown({
						allowAdditions: true,
					});
					$(handle).dropdown(
						'set selected', spec_item_id
					);
				}
			});
		}
		// 查一下同级所有选择的规格值，并更新隐藏域和数组
		get_spec_item_3();
	}

	// 生成规格值的数据内容
	function add_sub_item(skunum, num) {
		// 规格值 重新处理
		var sku_list_html = '';
		if (num == 2) {
			if (sku_item_arr_2.length < 1) {
				$(".dropdown_1_1_2 option:selected").each(function () {
					var select_val = $(this).val();
					sku_item_arr_2.push(select_val);
				});
			}
			sku_item_num_2 = sku_item_num_2 + 1;  // 先加一下，表示本次的次序
			var sku_item_num_2_class = "sku_item_num_2_" + sku_item_num_2;
			sku_itemlist_x = sku_itemlist_2;
			sku_item_arr_x = sku_item_arr_2;
			sku_list_html += '<select dty="two" name="sku_list_2_' + sku_item_num_2 + '" id="sku_list_2_' + sku_item_num_2 + '" class="ui fluid search dropdown dropdown_1_2" onchange="change_sku_item_2(this)">';
		} else if (num == 3) {
			if (sku_item_arr_3.length < 1) {
				$(".dropdown_1_1_3 option:selected").each(function () {
					var select_val = $(this).val();
					sku_item_arr_3.push(select_val);
				});
			}
			sku_item_num_3 = sku_item_num_3 + 1;  // 先加一下，表示本次的次序
			var sku_item_num_3_class = "sku_item_num_3_" + sku_item_num_3;
			sku_itemlist_x = sku_itemlist_3;
			sku_item_arr_x = sku_item_arr_3;
			sku_list_html += '<select dty="two" name="sku_list_3_' + sku_item_num_3 + '" id="sku_list_3_' + sku_item_num_3 + '" class="ui fluid search dropdown dropdown_1_3" onchange="change_sku_item_3(this)">';
		} else {
			if (sku_item_arr_1.length < 1) {
				$(".dropdown_1_1_1 option:selected").each(function () {
					var select_val = $(this).val();
					sku_item_arr_1.push(select_val);
				});
			}
			sku_item_num_1 = sku_item_num_1 + 1;  // 先加一下，表示本次的次序
			var sku_item_num_1_class = "sku_item_num_1_" + sku_item_num_1;
			sku_itemlist_x = sku_itemlist_1;
			sku_item_arr_x = sku_item_arr_1;
			sku_list_html += '<select dty="two" name="sku_list_1_' + sku_item_num_1 + '" id="sku_list_1_' + sku_item_num_1 + '" class="ui fluid search dropdown dropdown_1_1" onchange="change_sku_item_1(this)">';
		}

		sku_list_html += '<option value="0">请选择</option>';

		console.log('-----sku_itemlist_x------');
		console.log(sku_itemlist_x);

		var option_str_item = "";
		for (y in sku_itemlist_x) {
			var option_res = sku_itemlist_x[y];
			var item_index = sku_item_arr_x.indexOf(option_res.id);
			if (item_index >= 0) {
				// 已经被选择过了
			} else {
				option_str_item += "<option value='" + option_res.id + "' spec_id='" + option_res.spec_id + "'>" + option_res.item + "</option>";
			}
		}
		sku_list_html += option_str_item;
		sku_list_html += '</select>';
		return sku_list_html;
	}

	// 添加规格值
	$(".add_sub_spec_1").on("click", function () {
		var spec_id = $(".spec_id_1").val();                    // 规格名ID
		if (spec_id < 1) {
			layer.msg("请选择规格名");
			return false;
		}
		var skunum = $(this).attr("skunum");
		var sku_list_html = add_sub_item(skunum, 1);
		$(".sku_list_right_1").append(sku_list_html);
		$('.dropdown_1_1').dropdown({
			allowAdditions: true,
		});
	});
	$(".add_sub_spec_2").on("click", function () {
		var spec_id = $(".spec_id_2").val();                    // 规格名ID
		if (spec_id < 1) {
			layer.msg("请选择规格名");
			return false;
		}
		var skunum = $(this).attr("skunum");
		var sku_list_html = add_sub_item(skunum, 2);
		$(".sku_list_right_2").append(sku_list_html);
		$('.dropdown_1_2').dropdown({
			allowAdditions: true,
		});
	});
	$(".add_sub_spec_3").on("click", function () {
		var spec_id = $(".spec_id_3").val();                    // 规格名ID
		if (spec_id < 1) {
			layer.msg("请选择规格名");
			return false;
		}
		var skunum = $(this).attr("skunum");
		var sku_list_html = add_sub_item(skunum, 3);
		$(".sku_list_right_3").append(sku_list_html);
		$('.dropdown_1_3').dropdown({
			allowAdditions: true,
		});
	});

	// select下拉框美化
	$('.dropdown_1').dropdown({
		allowAdditions: true,
	});
	$('.dropdown_2').dropdown({
		allowAdditions: true,
	});
	$('.dropdown_3').dropdown({
		allowAdditions: true,
	});

	$('.dropdown_1_1').dropdown({
		allowAdditions: true,
	});
	$('.dropdown_1_2').dropdown({
		allowAdditions: true,
	});
	$('.dropdown_1_3').dropdown({
		allowAdditions: true,
	});

	// 获取所有选择中的规格值（规格名1下面的）
	function get_spec_item_1() {
		console.log('--get_spec_item_1---');
		sku_item_arr_1 = [];

		$(".dropdown_1_1_1 option:selected").each(function (index, ele) {
			var select_val = $(this).val();
			sku_item_arr_1.push(select_val);
			var sku_item_arr_1_string = sku_item_arr_1.toString();
			$(".spec_item_id_1").val(sku_item_arr_1_string);
		});

		$(".dropdown_1_1_1 select").each(function (index, ele) {
			var e_v = $(ele).val();
			var e_id = $(ele).attr("id");
			var e_handle_opt = "#" + e_id + " option";
			var e_handle = "#" + e_id;
			var opt_index = -1;
			var sku_diff = get_diff_item(sku_itemlist_1, sku_item_arr_1);

			$(e_handle_opt).each(function (index2, opt) {
				if (opt.value > 0) {
					var opt_index = sku_item_arr_1.indexOf(opt.value);
				} else {
					var opt_index = -1;
				}
				if (opt_index >= 0) {
					if (e_v == opt.value) {
					} else {
						$(this).remove();
					}
				}
				// 把原来已经删除的，但是要出现在本下拉框的，展示出来
				for (x1 in sku_diff) {
					var option_d1 = sku_diff[x1];
					if (option_d1['id'] == opt.value && opt.value > 0) {
						sku_diff.splice(x1, 1);
					}
				}
			});

			var sku_diff_html = '';
			for (x2 in sku_diff) {
				var option_res = sku_diff[x2];
				sku_diff_html += '<option value="' + option_res.id + '" spec_id="' + option_res.spec_id + '">' + option_res.item + '</option>';
			}
			if (sku_diff_html.length > 11) {
				$(e_handle).append(sku_diff_html);
			}
			// 组合已选择的数据，然后向上请求
			get_final_spec();
		})
	}

	function get_spec_item_2() {
		console.log('--get_spec_item_2---');
		sku_item_arr_2 = [];

		$(".dropdown_1_1_2 option:selected").each(function (index, ele) {
			var select_val = $(this).val();
			sku_item_arr_2.push(select_val);
			var sku_item_arr_2_string = sku_item_arr_2.toString();
			$(".spec_item_id_2").val(sku_item_arr_2_string);
		});

		$(".dropdown_1_1_2 select").each(function (index, ele) {
			var e_v = $(ele).val();
			var e_id = $(ele).attr("id");
			var e_handle_opt = "#" + e_id + " option";
			var e_handle = "#" + e_id;
			var opt_index = -1;
			var sku_diff = get_diff_item(sku_itemlist_2, sku_item_arr_2);

			$(e_handle_opt).each(function (index2, opt) {
				if (opt.value > 0) {
					var opt_index = sku_item_arr_2.indexOf(opt.value);
				} else {
					var opt_index = -1;
				}
				if (opt_index >= 0) {
					if (e_v == opt.value) {
					} else {
						$(this).remove();
					}
				}
				// 把原来已经删除的，但是要出现在本下拉框的，展示出来
				for (x1 in sku_diff) {
					var option_d1 = sku_diff[x1];
					if (option_d1['id'] == opt.value && opt.value > 0) {
						sku_diff.splice(x1, 1);
					}
				}
			});

			var sku_diff_html = '';
			for (x2 in sku_diff) {
				var option_res = sku_diff[x2];
				sku_diff_html += '<option value="' + option_res.id + '" spec_id="' + option_res.spec_id + '">' + option_res.item + '</option>';
			}
			if (sku_diff_html.length > 11) {
				$(e_handle).append(sku_diff_html);
			}
			// 组合已选择的数据，然后向上请求
			get_final_spec();
		})
	}

	function get_spec_item_3() {
		console.log('--get_spec_item_3---');
		sku_item_arr_3 = [];

		$(".dropdown_1_1_3 option:selected").each(function (index, ele) {
			var select_val = $(this).val();
			sku_item_arr_3.push(select_val);
			var sku_item_arr_3_string = sku_item_arr_3.toString();
			$(".spec_item_id_3").val(sku_item_arr_3_string);
		});

		$(".dropdown_1_1_3 select").each(function (index, ele) {
			var e_v = $(ele).val();
			var e_id = $(ele).attr("id");
			var e_handle_opt = "#" + e_id + " option";
			var e_handle = "#" + e_id;
			var opt_index = -1;
			var sku_diff = get_diff_item(sku_itemlist_3, sku_item_arr_3);

			$(e_handle_opt).each(function (index3, opt) {
				if (opt.value > 0) {
					var opt_index = sku_item_arr_3.indexOf(opt.value);
				} else {
					var opt_index = -1;
				}
				if (opt_index >= 0) {
					if (e_v == opt.value) {
					} else {
						$(this).remove();
					}
				}
				// 把原来已经删除的，但是要出现在本下拉框的，展示出来
				for (x1 in sku_diff) {
					var option_d1 = sku_diff[x1];
					if (option_d1['id'] == opt.value && opt.value > 0) {
						sku_diff.splice(x1, 1);
					}
				}
			});

			var sku_diff_html = '';
			for (x3 in sku_diff) {
				var option_res = sku_diff[x3];
				sku_diff_html += '<option value="' + option_res.id + '" spec_id="' + option_res.spec_id + '">' + option_res.item + '</option>';
			}
			if (sku_diff_html.length > 11) {
				$(e_handle).append(sku_diff_html);
			}
			// 组合已选择的数据，然后向上请求
			get_final_spec();
		})
	}

	// 获取规格值数组差集
	// arr1 是原始规格值列表（二维），arr2 是已选择的规格值列表（一维）
	function get_diff_item(arr1, arr2) {
		var len = arr1.length;
		var arr = [];
		while (len--) {
			if (arr2.indexOf(arr1[len]['id']) < 0) {
				arr.push(arr1[len]);
			}
		}
		return arr;
	}

	// 获取规格名差集
	// num 1：规格名1的差值。2：规格名2的差值。3：规格名3的差值。
	function get_diff_spec(num, new_list) {
		console.log('---get_diff_spec---');
		var spec_id_1_val = $(".spec_id_1").val();
		var spec_id_2_val = $(".spec_id_2").val();
		var spec_id_3_val = $(".spec_id_3").val();

		for (sp in new_list) {
			if (num == 1) {
				if (new_list[sp]['id'] == spec_id_2_val || new_list[sp]['id'] == spec_id_3_val) {
					new_list.splice(sp, 1);
				}

			}
			if (num == 2) {
				if (new_list[sp]['id'] == spec_id_1_val || new_list[sp]['id'] == spec_id_3_val) {
					new_list.splice(sp, 1);
				}
			}
			if (num == 3) {
				if (new_list[sp]['id'] == spec_id_1_val || new_list[sp]['id'] == spec_id_2_val) {
					new_list.splice(sp, 1);
				}
			}
		}
		return new_list;
	}

	// 获取最终的规格名+值，然后组合，为请求接口做好准备
	function get_final_spec() {
		console.log('-----get_f_sp-----')
		var spec_arr = {};
		var spec_id_1 = $(".spec_id_1").val();
		var spec_item_id_1 = $(".spec_item_id_1").val();

		console.log(spec_item_id_1);

		if (spec_id_1 > 0) {
			spec_item_id_1_1 = spec_item_id_1.split(',');
			console.log(spec_item_id_1_1);

			for (x1 in spec_item_id_1_1) {
				if (spec_item_id_1_1[x1] <= 0) {
					spec_item_id_1_1.splice(x1, 1);
				}
			}
			if (spec_item_id_1_1.length > 0) {
				spec_arr[spec_id_1] = spec_item_id_1_1;
			}
		}

		var spec_id_2 = $(".spec_id_2").val();
		var spec_item_id_2 = $(".spec_item_id_2").val();
		if (spec_id_2 > 0) {
			spec_item_id_2_2 = spec_item_id_2.split(',');
			for (x2 in spec_item_id_2_2) {
				if (spec_item_id_2_2[x2] <= 0) {
					spec_item_id_2_2.splice(x2, 1);
				}
			}
			if (spec_item_id_2_2.length > 0) {
				spec_arr[spec_id_2] = spec_item_id_2_2;
			}
		}

		var spec_id_3 = $(".spec_id_3").val();
		var spec_item_id_3 = $(".spec_item_id_3").val();
		if (spec_id_3 > 0) {
			spec_item_id_3_3 = spec_item_id_3.split(',');
			for (x3 in spec_item_id_3_3) {
				if (spec_item_id_3_3[x3] <= 0) {
					spec_item_id_3_3.splice(x3, 1);
				}
			}
			if (spec_item_id_3_3.length > 0) {
				spec_arr[spec_id_3] = spec_item_id_3_3;
			}
		}

		console.log('-----spec_arr------');
		console.log(spec_arr);
		ajaxGetSpecInput2(spec_arr); // 显示下面的输入框
	}


	// 删除规格名中重复的
	function del_repeat_spec(num) {
		var spec_id_1_val = $(".spec_id_1").val();
		var spec_id_2_val = $(".spec_id_2").val();
		var spec_id_3_val = $(".spec_id_3").val();
		if (num == 1) {
			$("#sku_sel_2 option").each(function (index, ele) {
				if (ele.value == spec_id_1_val && spec_id_1_val > 0) {
					ele.remove();
				}
				if (ele.value == spec_id_3_val && spec_id_3_val > 0) {
					ele.remove();
				}
			});
			$("#sku_sel_3 option").each(function (index, ele) {
				if (ele.value == spec_id_1_val && spec_id_1_val > 0) {
					ele.remove();
				}
				if (ele.value == spec_id_2_val && spec_id_2_val > 0) {
					ele.remove();
				}
			});
		} else if (num == 2) {
			$("#sku_sel_1 option").each(function (index, ele) {
				if (ele.value == spec_id_2_val && spec_id_2_val > 0) {
					ele.remove();
				}
				if (ele.value == spec_id_3_val && spec_id_3_val > 0) {
					ele.remove();
				}
			});
			$("#sku_sel_3 option").each(function (index, ele) {
				if (ele.value == spec_id_1_val && spec_id_1_val > 0) {
					ele.remove();
				}
				if (ele.value == spec_id_2_val && spec_id_2_val > 0) {
					ele.remove();
				}
			});
		} else if (num == 3) {
			$("#sku_sel_1 option").each(function (index, ele) {
				if (ele.value == spec_id_2_val && spec_id_2_val > 0) {
					ele.remove();
				}
				if (ele.value == spec_id_3_val && spec_id_3_val > 0) {
					ele.remove();
				}
			});
			$("#sku_sel_2 option").each(function (index, ele) {
				if (ele.value == spec_id_1_val && spec_id_1_val > 0) {
					ele.remove();
				}
				if (ele.value == spec_id_3_val && spec_id_3_val > 0) {
					ele.remove();
				}
			});
		}
	}

	// 补充规格名下拉框中以前被删除，现在应该出现的
	function add_spec_repair(list) {
		// 列出所有规格名列表
		// 然后去掉第1(或2，或3)选择的，就是当前规格名所有下拉框值
		// 然后循环 option，如果相同 则不管。如果不在规格列表中，则把本次option删除。
		// 上面的循环中，每循环完一次就删除相应的规格名列表，最后剩余一个新数组
		// 循环新数组，则只需要增加就行了，此数组表示原来列表中没有的
		var list_id = new Array();      // 只存ID
		var list_id_one = new Array();
		var list_id_two = new Array();
		var list_id_three = new Array();

		var list_one = new Array();       // 存整条数据
		var list_two = new Array();
		var list_three = new Array();

		for (xx = 0; xx < list.length; xx++) {
			var items = list[xx];
			list_one[xx] = items;
			list_two[xx] = items;
			list_three[xx] = items;

			list_id[xx] = items.id;
			list_id_one[xx] = items.id;
			list_id_two[xx] = items.id;
			list_id_three[xx] = items.id;
		}

		var spec_id_1_val = $(".spec_id_1").val();
		var spec_id_2_val = $(".spec_id_2").val();
		var spec_id_3_val = $(".spec_id_3").val();

		// 添加第一个
		var num_x = list_id_one.indexOf(spec_id_2_val);
		if (num_x >= 0) {
			list_id_one.splice(num_x, 1);
			list_one.splice(num_x, 1);
		}
		var num_x = list_id_one.indexOf(spec_id_3_val);
		if (num_x >= 0) {
			list_id_one.splice(num_x, 1);
			list_one.splice(num_x, 1);
		}

		$("#sku_sel_1 option").each(function (index_1, ele_1) {
			if (ele_1.className == 'addition') {
				$(this).remove();
			}
			var num_x = list_id_one.indexOf(ele_1.value);
			if (num_x >= 0) {
				list_id_one.splice(num_x, 1);
				list_one.splice(num_x, 1);
			}
		});
		var html_add = '';
		for (xx_1 in list_one) {
			var option_xx = list_one[xx_1];
			html_add += '<option value="' + option_xx.id + '">' + option_xx.name + '</option>';
		}
		if (html_add.length > 11) {
			$("#sku_sel_1").append(html_add);
		}

		// 添加第二个
		var num_x = list_id_two.indexOf(spec_id_1_val);
		if (num_x >= 0) {
			list_id_two.splice(num_x, 1);
			list_two.splice(num_x, 1);
		}
		var num_x = list_id_two.indexOf(spec_id_3_val);
		if (num_x >= 0) {
			list_id_two.splice(num_x, 1);
			list_two.splice(num_x, 1);
		}
		$("#sku_sel_2 option").each(function (index_2, ele_2) {
			if (ele_2.className == 'addition') {
				$(this).remove();
			}
			var num_x = list_id_two.indexOf(ele_2.value);
			if (num_x >= 0) {
				list_id_two.splice(num_x, 1);
				list_two.splice(num_x, 1);
			}
		});
		var html_add = '';
		for (xx_2 in list_two) {
			var option_xx = list_two[xx_2];
			html_add += '<option value="' + option_xx.id + '">' + option_xx.name + '</option>';
		}
		if (html_add.length > 11) {
			$("#sku_sel_2").append(html_add);
		}

		// 添加第三个
		var num_x = list_id_three.indexOf(spec_id_1_val);
		if (num_x >= 0) {
			list_id_three.splice(num_x, 1);
			list_three.splice(num_x, 1);
		}
		var num_x = list_id_three.indexOf(spec_id_2_val);
		if (num_x >= 0) {
			list_id_three.splice(num_x, 1);
			list_three.splice(num_x, 1);
		}
		$("#sku_sel_3 option").each(function (index_3, ele_3) {
			if (ele_3.className == 'addition') {
				$(this).remove();
			}
			var num_x = list_id_three.indexOf(ele_3.value);
			if (num_x >= 0) {
				list_id_three.splice(num_x, 1);
				list_three.splice(num_x, 1);
			}
		});
		var html_add = '';
		for (xx_3 in list_three) {
			var option_xx = list_three[xx_3];
			html_add += '<option value="' + option_xx.id + '">' + option_xx.name + '</option>';
		}
		if (html_add.length > 11) {
			$("#sku_sel_3").append(html_add);
		}
	}

	// 重置规格信息
	function reset_spec() {
		var spec_id_1 = $("#sku_sel_1 option:selected").val();
		if (spec_id_1 > 0) {
			$(".spec_id_1").val(spec_id_1)
		} else {
			$(".spec_id_1").val(0)
		}
		var spec_id_2 = $("#sku_sel_2 option:selected").val();
		if (spec_id_2 > 0) {
			$(".spec_id_2").val(spec_id_2)
		} else {
			$(".spec_id_2").val(0)
		}
		var spec_id_3 = $("#sku_sel_3 option:selected").val();
		if (spec_id_3 > 0) {
			$(".spec_id_3").val(spec_id_3)
		} else {
			$(".spec_id_3").val(0)
		}

		sku_item_arr_1 = [];
		$(".dropdown_1_1_1 option:selected").each(function (index, ele) {
			var select_val = $(this).val();
			sku_item_arr_1.push(select_val);
		});
		var sku_item_arr_1_string = sku_item_arr_1.toString();
		if (sku_item_arr_1_string.length > 0) {
			$(".spec_item_id_1").val(sku_item_arr_1_string);
		} else {
			$(".spec_item_id_1").val('0');
		}


		sku_item_arr_2 = [];
		$(".dropdown_1_1_2 option:selected").each(function (index, ele) {
			var select_val = $(this).val();
			sku_item_arr_2.push(select_val);
		});
		var sku_item_arr_2_string = sku_item_arr_2.toString();
		if (sku_item_arr_2_string.length > 0) {
			$(".spec_item_id_2").val(sku_item_arr_2_string);
		} else {
			$(".spec_item_id_2").val('0');
		}

		sku_item_arr_3 = [];
		$(".dropdown_1_1_3 option:selected").each(function (index, ele) {
			var select_val = $(this).val();
			sku_item_arr_3.push(select_val);
		});
		var sku_item_arr_3_string = sku_item_arr_3.toString();
		$(".spec_item_id_3").val(sku_item_arr_3_string);
		if (sku_item_arr_3_string.length > 0) {
			$(".spec_item_id_3").val(sku_item_arr_3_string);
		} else {
			$(".spec_item_id_3").val('0');
		}

	}


</script>
<!-- 上传图片 -->

</body>
</html>