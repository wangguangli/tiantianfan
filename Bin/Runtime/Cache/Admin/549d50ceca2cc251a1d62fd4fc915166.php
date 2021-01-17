<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
	<title><?php echo GC('web_title');?>管理后台-首页</title>
	<!-- css+js -->
		<link href="/Public/admin_new/css/icons/icomoon/styles.css?v=1.2.0" rel="stylesheet" type="text/css">
	<link href="/Public/admin_new/css/style/bootstrap.min.css?v=1.2.0" rel="stylesheet" type="text/css">
	<link href="/Public/admin_new/css/style/commons.css?v=1.2.0" rel="stylesheet" type="text/css">
	<link href="/Public/admin_new/css/style/platform.css?v=1.2.0" rel="stylesheet" type="text/css">
	<link href="/Public/admin_new/css/minified/core.min.css?v=1.2.0" rel="stylesheet" type="text/css">
	<link href="/Public/admin_new/css/minified/components.min.css?v=1.2.0" rel="stylesheet" type="text/css">
	<link href="/Public/admin_new/css/minified/colors.min.css?v=1.2.0" rel="stylesheet" type="text/css">

	<!-- Common JS files -->
	<script type="text/javascript" src="/Public/admin_new/js/core/libraries/jquery.min.js"></script>
	<script type="text/javascript" src="/Public/admin_new/js/core/libraries/bootstrap.min.js"></script>
	<script type="text/javascript" src="/Public/admin_new/js/core/app.js"></script>
	<script type="text/javascript" src="/Public/extends/laydate/laydate.js"></script>
	<script type="text/javascript" src="/Public/extends/layui/layui.js"></script>
	<script type="text/javascript" src="/Public/admin_new/js/common_admin.js"></script> <!-- 分页必用 -->
	<script type="text/javascript" src="/Public/extends/echarts/4.7.0/echarts.min.js"></script>
	<!-- /Common JS files -->
	<style>
		.H-DIV{
			padding-right: 20px;
			float: left;
			width: calc(100% - 240px);
		}
		.flex-index-right{
			float: right;
		}
		@media (max-width: 1280px){
			.H-DIV{
				width: 100%;
			}
			.process .procedure{
				width: 46%;

			}
			.process .procedure:nth-child(n+3){
				margin-top: 20px;
			}
			.v-card-body .engage .payment{
				width: 98%;
			}
			.flex-index-right{
				width: 100%;
			}
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
	<div class="page-content" style="width: 100%;">
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
			<!-- Page Body -->
			<div class="v-container">
				<!-- page -->
				<!--				<div class="flex">-->
				<!--					-->
				<!--				</div>-->
				<div class="H-DIV">
					<div class="v-card-body" id="mallGuide">
						<div class="newbie mb-20 flex flex-pack-justify">
							<div class="bold">商城助手</div>
						</div>
						<div class="process">
							<div class="procedure">
								<div class="step-number">
									<img src="/Public/admin_new/images/5.png" alt="">
									<div class="select">
										<div class="flex">
											<div class="choose">商城基本配置</div>
										</div>
										<div>
											<a href="<?php echo U('Index/web');?>" class="v-btn-set">前往设置</a>
										</div>
									</div>
								</div>
							</div>
							<div class="procedure">
								<div class="step-number">
									<img src="/Public/admin_new/images/2.png" alt="">
									<div class="select">
										<div class="flex">
											<div class="choose">运费模板</div>
										</div>
										<div>
											<a href="<?php echo U('Goods/express');?>" class="v-btn-set">前往配置</a>
										</div>
									</div>
								</div>
							</div>
							<div class="procedure">
								<div class="step-number">
									<img src="/Public/admin_new/images/4.png" alt="">
									<div class="select">
										<div class="flex">
											<div class="choose">发布商品</div>
										</div>
										<div>
											<a href="<?php echo U('Goods/add');?>" class="v-btn-set">立即发布</a>
										</div>
									</div>
								</div>
							</div>
							<div class="procedure">
								<div class="step-number">
									<img src="/Public/admin_new/images/1.png" alt="">
									<div class="select">
										<div class="flex">
											<div class="choose">订单列表</div>
										</div>
										<div>
											<a href="<?php echo U('Order/index');?>" class="v-btn-set">立即查看</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--商城助手-->
					<!--运营状况-->
					<div class="v-card-body">
						<div class="newbie mb-20 flex flex-pack-justify">
							<div class="bold">运营概况</div>
						</div>
						<div class="engage">
							<div class="payment">
								<div class="payment-one">
									<div class="pay-img"><img src="/Public/admin_new/images/6.png"
															  alt=""></div>
									<div class="string"></div>
									<div class="figures">
										<div class="figure">
											<div class="pay-mange">待发货订单</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-red"><?php echo ((isset($order_1) && ($order_1 !== ""))?($order_1):0); ?></a>
											</div>
										</div>
										<div class="figure">
											<div class="pay-mange">售后订单</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-red"><?php echo ((isset($order_6) && ($order_6 !== ""))?($order_6):0); ?></a>
											</div>
										</div>
										<div class="figure">
											<div class="pay-mange">本月成交订单</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-red"><?php echo ((isset($order_m) && ($order_m !== ""))?($order_m):0); ?></a>
											</div>
											<div class="pay-day">上月：<?php echo ((isset($order_pm) && ($order_pm !== ""))?($order_pm):0); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="payment">
								<div class="payment-one">
									<div class="pay-img"><img src="/Public/admin_new/images/7.png"
															  alt=""></div>
									<div class="string"></div>
									<div class="figures">
										<div class="figure">
											<div class="pay-mange">今日营业额</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-red"><?php echo ((isset($order_money_today) && ($order_money_today !== ""))?($order_money_today):0); ?></a>
											</div>
											<div class="pay-day">昨日：<?php echo ((isset($order_money_yestoday) && ($order_money_yestoday !== ""))?($order_money_yestoday):0); ?></div>
										</div>
										<div class="figure">
											<div class="pay-mange">今日支付订单</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-red"><?php echo ((isset($order_count_today) && ($order_count_today !== ""))?($order_count_today):0); ?></a>
											</div>
											<div class="pay-day">昨日：<?php echo ((isset($order_count_yestoday) && ($order_count_yestoday !== ""))?($order_count_yestoday):0); ?></div>
										</div>
										<div class="figure">
											<div class="pay-mange">今日支付人数</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-red"><?php echo ((isset($order_user_today) && ($order_user_today !== ""))?($order_user_today):0); ?></a>
											</div>
											<div class="pay-day">昨日：<?php echo ((isset($order_user_yestoday) && ($order_user_yestoday !== ""))?($order_user_yestoday):0); ?></div>
										</div>

									</div>
								</div>
							</div>
							<!--<div class="payment">
								<div class="payment-one">
									<div class="pay-img"><img src="/Public/admin_new/images/8.png"
															  alt=""></div>
									<div class="string"></div>
									<div class="figures">
										<div class="figure">
											<div class="pay-mange">今日新增商家</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-blue"><?php echo ((isset($shop_today) && ($shop_today !== ""))?($shop_today):0); ?></a>
											</div>
											<div class="pay-day">昨日：<?php echo ((isset($shop_yestoday) && ($shop_yestoday !== ""))?($shop_yestoday):0); ?></div>
										</div>
										<div class="figure">
											<div class="pay-mange">待审核商家</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-blue"><?php echo ((isset($shop_checking) && ($shop_checking !== ""))?($shop_checking):0); ?></a>
											</div>
										</div>
										<div class="figure">
											<div class="pay-mange">总商家数</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-blue"><?php echo ((isset($shop_all) && ($shop_all !== ""))?($shop_all):0); ?></a>
											</div>
										</div>
									</div>
								</div>
							</div>-->
							<div class="payment">
								<div class="payment-one">
									<div class="pay-img"><img src="/Public/admin_new/images/9.png"
															  alt=""></div>
									<div class="string"></div>
									<div class="figures">
										<div class="figure">
											<div class="pay-mange">待审核提现(笔)</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-blue"><?php echo ((isset($tx_checking) && ($tx_checking !== ""))?($tx_checking):0); ?></a>
											</div>
										</div>
										<div class="figure">
											<div class="pay-mange">今日已提现(元)</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-blue"><?php echo ((isset($tx_money_today) && ($tx_money_today !== ""))?($tx_money_today):0); ?></a>
											</div>
										</div>
										<div class="figure">
											<div class="pay-mange">本月已提现(元)</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-blue"><?php echo ((isset($tx_money_month) && ($tx_money_month !== ""))?($tx_money_month):0); ?></a>
											</div>
											<div class="pay-day">上月：<?php echo ((isset($tx_money_month_pre) && ($tx_money_month_pre !== ""))?($tx_money_month_pre):0); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="payment">
								<div class="payment-one">
									<div class="pay-img"><img src="/Public/admin_new/images/10.png"
															  alt=""></div>
									<div class="string"></div>
									<div class="figures">
										<div class="figure">
											<div class="pay-mange">今日新增会员</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-green"><?php echo ((isset($user_today) && ($user_today !== ""))?($user_today):0); ?></a>
											</div>
											<div class="pay-day">昨日:<?php echo ((isset($user_yestoday) && ($user_yestoday !== ""))?($user_yestoday):0); ?></div>
										</div>
										<div class="figure">
											<div class="pay-mange">本周新增</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-green"><?php echo ((isset($user_week) && ($user_week !== ""))?($user_week):0); ?></a>
											</div>
											<div class="pay-day">本月:<?php echo ((isset($user_month) && ($user_month !== ""))?($user_month):0); ?></div>
										</div>
										<div class="figure">
											<div class="pay-mange">总会员数</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-green"><?php echo ((isset($user_all) && ($user_all !== ""))?($user_all):0); ?></a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="payment">
								<div class="payment-one">
									<div class="pay-img"><img src="/Public/admin_new/images/11.png"
															  alt=""></div>
									<div class="string"></div>
									<div class="figures">
										<div class="figure">
											<div class="pay-mange">出售中商品</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-green"><?php echo ((isset($goods_sale) && ($goods_sale !== ""))?($goods_sale):0); ?></a>
											</div>
										</div>
										<div class="figure">
											<div class="pay-mange">仓库中商品</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-green"><?php echo ((isset($goods_unsale) && ($goods_unsale !== ""))?($goods_unsale):0); ?></a>
											</div>
										</div>
										<div class="figure">
											<div class="pay-mange">已删除</div>
											<div class="pay-num"><a href="javascript:;" class="index-order-green"><?php echo ((isset($goods_del) && ($goods_del !== ""))?($goods_del):0); ?></a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--运营状况-->
					<!--快捷入口-->
					<div class="v-card-body" style="display: none;">
						<div class="newbie mb-20 flex flex-pack-justify">
							<div class="bold">快捷入口</div>
						</div>
						<div class="succession">
							<div class="balance">
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/12.png" alt="">
									<div class="ml-10">发布商品</div>
								</a>
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/13.png" alt="">
									<div class="ml-10">商城装修</div>
								</a>
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/14.png" alt="">
									<div class="ml-10" style="margin-left: 9px;">公众号管理</div>
								</a>
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/15.png" alt="">
									<div class="ml-10">订单管理</div>
								</a>
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/16.png" alt="">
									<div class="ml-10">订单分析</div>
								</a>
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/17.png" alt="">
									<div class="ml-10">分销商</div>
								</a>
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/18.png" alt="">
									<div class="ml-10">发货助手</div>
								</a>
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/19.png" alt="">
									<div class="ml-10">商品助手</div>
								</a>
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/20.png" alt="">
									<div class="ml-10">优惠券</div>
								</a>
								<a href="" class="balanceOperation">
									<img src="/Public/admin_new/images/21.png" alt="">
									<div class="ml-10">满减送</div>
								</a>
							</div>
						</div>
					</div>
					<!--快捷入口-->
					<!--订单概况-->
				</div>
				<div class="flex-index-right">
					<div class="mp-guide" style="display: none;">
						<div class="v-card-body">
							<a href="" target="_blank" class="mp-guide-box">
								<div class="mp-guide-item flex">
									<div class="mpgi-pic-icon">
										<i class="icon icon-shop"></i>
									</div>
									<div>
										<p class="mt-04 mb-04">如何快速搭建商城</p>
										<p>常见问题</p>
									</div>
								</div>
							</a>
							<a href="" target="_blank" class="mp-guide-box">
								<div class="mp-guide-item flex">
									<div class="mpgi-pic-icon">
										<i class="icon icon-community-mp"></i>
									</div>
									<div>
										<p class="mt-04 mb-04">更多干货</p>
										<p>前往商家社区</p>
									</div>
								</div>
							</a>
						</div>
					</div>
					<div class="contact">
						<div class="v-card-body">
							<a class="flex contactQQ" href="javascript:;">
								<div class="flex contacts contacts-bottom">
									<div class="contacts-icon"><i class="icon icon-index-service"
																  style="color: #1e83ff"></i></div>
									<div class="contacts-word">
										<p class="title">邮箱反馈</p>
										<p class="title fs-12">1014905292@qq.com</p>
									</div>
								</div>
							</a>
							<div class="flex contacts">
								<div class="contacts-icon"><i class="icon icon-index-moblie"
															  style="color: #3db479"></i></div>
								<div class="contacts-word">
									<p class="title">技术支持</p>
									<p>0537-3429561</p>
								</div>
							</div>
						</div>
					</div>
					<div class="merchantsAnnouncement">
						<div class="v-card-body">
							<div class="newbie mb-20 flex flex-pack-justify">
								<div>
									<span class="bold">商家公告</span>
									<a href="" target="_blank" class="text-primary ml-10"
									   style="display: none;">更多</a>
								</div>
							</div>
							<ul class="ma_ul">
								<li class="no_news">暂无消息内容</li>
							</ul>
						</div>
					</div>
					<div class="merchantsMail">
						<div class="v-card-body">
							<div class="newbie mb-20 flex flex-pack-justify">
								<div><span class="bold">站内信</span> <a href="" target="_blank"
																	  class="text-primary ml-10"
																	  style="display: none;">更多</a></div>
							</div>
							<ul class="ma_ul">

								<li class="no_news">暂无消息内容</li>

								<li class="line-1-ellipsis" style="display: none;">
									<a href="" target="_blank">05-09</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="proRecommend" id="product_list"></div>
				</div>
			</div>


			<!-- Page Body --><!-- Page Body -->

			<!-- /Page Body -->
		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</div>
<!-- /page container -->



</body>
</html>