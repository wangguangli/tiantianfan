<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>

<head>
	<title><?php echo GC('web_title');?></title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="Keywords" content="<?php echo GC('keywords');?>">
	<meta name="description" content="<?php echo GC('description');?>">
	<!-- UC强制全屏 -->
	<meta name="full-screen" content="yes">
	<!-- QQ强制全屏 -->
	<meta name="x5-fullscreen" content="true">
	<link rel="stylesheet" href="/Public/mobile/style/jquery-weui.min.css">
	<!--<link rel="stylesheet" href="/Public/mobile/css_v3/weui.min.css?n=1">-->
	<link rel="stylesheet" href="/Public/mobile/style/main_style.css?n=1">
	<link rel="stylesheet" href="/Public/mobile/css_v3/index.css?n=5">
	<link href="/Public/css/footer.css" rel="stylesheet" type="text/css"/>
	<!--上拉加载-->
	<link rel="stylesheet" href="/Public/mobile/style/dropload.css">
	<script type="text/javascript" src="/Public/layer/mobile/layer.js"></script>
	<!--banner轮播图模块-->
	<style type="text/css">
		.inx-banner .swiper-slide a,
		.inx-banner .swiper-slide a img {
			height: 100%;
			width: 100%;
		}
		.pro-info-tootm1 {
			font-size: 2.22vw;
			color: #999;
			margin-top: 1.68vw;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
	</style>
</head>

<body ontouchstart style="background-color: #f7f8fa;height: auto;">
<div id="index">
	<section class="header_ec">
		<header>
			<div class="input_tents">
				<a href="<?php echo U('Goods/goodssearch');?>">
					<div class="tents_in">
						<img src="/Public/mobile/images_v3/f_dm1.png"/>
						<input type="text" name="contents" id="contents" value="" placeholder="输入商品名"
							   class="input_content" readonly="readonly"/>
					</div>
				</a>
			</div>
			<a href="<?php echo U('Cart/index');?>" class="scan">
				<img src="/Public/mobile/images_v3/f_dm2.png" style="width: 6vw;height: 6vw;"/>
			</a>
		</header>
		<div class="inx-banner">
			<div class="swiper-container" style="height: 35vw;position: relative;">
				<div class="swiper-wrapper">
					<?php if(is_array($banner)): $i = 0; $__LIST__ = $banner;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="swiper-slide swiper_img">
							<?php if($vo['module']=='goods'): ?><a href="<?php echo U('Goods/detail','id='.$vo['link']);?>"><img src="<?php echo ($vo["photo"]); ?>"/></a>
								<?php elseif($vo['module']=='shop'): ?>
								<a href="<?php echo U('Shop/index','shop_id='.$vo['link']);?>"><img src="<?php echo ($vo["photo"]); ?>"/></a>
								<?php elseif($vo['module']==category): ?>
								<a href="<?php echo U('Goods/index','cat_id='.$vo['cate_id']);?>">
									<img src="<?php echo ($vo["photo"]); ?>"/>
								</a>
								<?php elseif($vo['module']=='article'): ?>
								<a href="<?php echo U('Index/messageInfo','id='.$vo['link']);?>"><img src="<?php echo ($vo["photo"]); ?>"/></a>
								<?php elseif($vo['module']=='link'): ?>
								<a href="<?php echo ($vo['link']); ?>"><img src="<?php echo ($vo["photo"]); ?>"/></a>
								<?php elseif($vo['module']=='false'): ?>
								<a href="<?php echo ($vo['link']); ?>"><img src="<?php echo ($vo["photo"]); ?>"/></a>
								<?php elseif($vo['module']==''): ?>
								<img src="<?php echo ($vo["photo"]); ?>"/><?php endif; ?>
						</div><?php endforeach; endif; else: echo "" ;endif; ?>
				</div>
			</div>
		</div>
	</section>
	<!--商品分类模块-->
	<div class="swiper-container_nav">
		<div class="swiper-wrapper">
			<?php if(is_array($category)): $i = 0; $__LIST__ = $category;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="swiper-slide imgnav">
					<a href="<?php echo U('Goods/index','cat_id='.$vo['id']);?>">
						<div class="con_img_nav"><img src="<?php echo ($vo["img"]); ?>"/></div>
						<p><?php echo ($vo["name"]); ?></p>
					</a>
				</div><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>
		<div class="swiper-pagination-2 swiper-pagination_nav"></div>
	</div>
	<!--三张图-->
	<div class="three_img">

		<?php if(is_array($two_image)): $i = 0; $__LIST__ = $two_image;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['cate_id'] > 0): ?><a href="<?php echo U('Goods/index','cat_id='.$vo['cate_id']);?>">
							<img src="<?php echo ($vo["photo"]); ?>"/>
						</a>

					<?php elseif($vo['module']=='goods'): ?>
					<a href="<?php echo U('Goods/detail','id='.$vo['link']);?>">
						<img src="<?php echo ($vo["photo"]); ?>"/>
					</a>
					<?php elseif($vo['module']=='shop'): ?>
					<a href="<?php echo U('Shop/index','shop_id='.$vo['link']);?>">
						<img src="<?php echo ($vo["photo"]); ?>"/>
					</a>
					<?php elseif($vo['module']==category): ?>
						<a href="<?php echo U('Goods/index','cat_id='.$vo['cate_id']);?>">

						<img src="<?php echo ($vo["photo"]); ?>"/>
					</a>
					<?php elseif($vo['module']=='article'): ?>
					<a href="<?php echo U('Index/messageInfo','id='.$vo['link']);?>">
						<img src="<?php echo ($vo["photo"]); ?>"/>
					</a>
					<?php elseif($vo['module']=='link'): ?>
					<a href="<?php echo ($vo['link']); ?>">
						<img src="<?php echo ($vo["photo"]); ?>"/>
					</a>
					<?php elseif($vo['module']=='false'): ?>
					<a href="<?php echo ($vo['link']); ?>">
						<img src="<?php echo ($vo["photo"]); ?>"/>
					</a>
					<?php elseif($vo['module']==''): ?>
						<a href="">
							<img src="<?php echo ($vo["photo"]); ?>"/>
						</a><?php endif; endforeach; endif; else: echo "" ;endif; ?>




	</div>
	<!--限时秒杀-->
	<?php if($isHaveFlash > 0): ?><div class="Seconds_kill">
			<input type="hidden" id="time" value="<?php echo ($hour_end_time); ?>"/>
			<!--查看更多部分-->
			<div class="kill_title">
				<div class="kill_lf">
					<span class="kill_lf_txt"><?php echo ($flash["title"]); ?></span>
					<div class="kill_lf_time">
						<span id="hour">00</span> : <span id="minute">00</span> : <span id="second">00</span>
					</div>
				</div>
				<div class="kill_lr">
					<a href="<?php echo U('Goods/limited_time');?>">
						查看全部
						<img class="fan" src="/Public/mobile/images_v3/f_dm34.png"/>
					</a>
				</div>
			</div>
			<!--产品部分-->
			<div class="kill_product">
				<?php if(is_array($flash['goods_list'])): $i = 0; $__LIST__ = $flash['goods_list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="kill_list">
						<a href="<?php echo U('Goods/detail', array('goods_kind'=>2, 'id'=>$vo['id']));?>">
							<img src="<?php echo ($vo["thumb"]); ?>"/>
							<h2><?php echo ($vo["name"]); ?></h2>
							<span class="price">￥<em><?php echo ($vo["hour_price"]); ?></em></span>
							<div class="pro-info-tootm1">
								<div class="">
									<span style="font-size: 3.5vw;">已售:<?php echo ($vo["sell_count"]); ?></span>
									<p><span style="font-size: 3.5vw;">评论:<?php echo ($vo["common_count"]); ?></span></p>
								</div>
							</div>
						</a>
					</div><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>
		</div><?php endif; ?>

	<!--拼团-->
	<?php if($isHaveGroup > 0): ?><div class="Seconds_kill">
			<!--查看更多部分-->
			<div class="kill_title">
				<div class="kill_lf">
					<span class="kill_lf_txt"><?php echo ($group["title"]); ?></span>
				</div>
				<div class="kill_lr">
					<a href="<?php echo U('Goods/goodsgroup');?>">
						查看全部
						<img class="fan" src="/Public/mobile/images_v3/f_dm34.png"/>
					</a>
				</div>
			</div>
			<!--产品部分-->
			<div class="kill_product">
				<?php if(is_array($group['goods_list'])): $i = 0; $__LIST__ = $group['goods_list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="kill_list">
						<a href="<?php echo U('Goods/detail', array('goods_kind'=>1, 'id'=>$vo['id']));?>">
							<img src="<?php echo ($vo["thumb"]); ?>"/>
							<h2><?php echo ($vo["name"]); ?></h2>
							<span class="price"><span style="color: black;">原价:</span><span style="text-decoration: line-through">￥<?php echo ($vo["price"]); ?></span></span>
							<span class="price"><span style="color: black;">团购:</span><span style="font-size: 4.5vw;">￥<?php echo ($vo["spec_price"]); ?></span></span>
							<div class="pro-info-tootm1">
								<div class="">
									<span style="font-size: 3.5vw;">已售:<?php echo ($vo["sell_count"]); ?></span>
								<p><span style="font-size: 3.5vw;">评论:<?php echo ($vo["common_count"]); ?></span></p>
								</div>
							</div>
						</a>
					</div><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>
		</div><?php endif; ?>

	<div style="border-radius: 1.85vw;overflow: hidden;">
		<!--发现好货-->
		<div class="found_cont">
		<div class="found_cont_top">
			<div class="found_lf">
				<h4><?php echo ($today["title"]); ?></h4>
			</div>
<!--			<div class="found_lr">-->
<!--				<a href="<?php echo U('Index/todayGoods');?>">-->
<!--					查看更多 <img class="fan" src="/Public/mobile/images_v3/f_dm34.png"/>-->
<!--				</a>-->
<!--			</div>-->
		</div>
		<!--主要内容部分-->
		<div class="found_cont_mid">



					<?php if($index_goods_one['module']=='goods'): ?><a href="<?php echo U('Goods/detail','id='.$index_goods_one['link']);?>">
						<img src="<?php echo ($index_goods_one["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_one['module']=='shop'): ?>
					<a href="<?php echo U('Shop/index','shop_id='.$index_goods_one['link']);?>">
						<img src="<?php echo ($index_goods_one["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_one['module']== 'category'): ?>
					<a href="<?php echo U('Goods/index','cat_id='.$index_goods_one['cate_id']);?>">
						<img src="<?php echo ($index_goods_one["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_one['module']=='article'): ?>
					<a href="<?php echo U('Index/messageInfo','id='.$index_goods_one['link']);?>">
						<img src="<?php echo ($index_goods_one["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_one['module']=='link'): ?>
					<a href="<?php echo ($index_goods_one['link']); ?>">
						<img src="<?php echo ($index_goods_one["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_one['module']=='false'): ?>
					<a href="<?php echo ($index_goods_one['link']); ?>">
						<img src="<?php echo ($index_goods_one["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_one['module']==''): ?>
					<a href="">
						<img src="<?php echo ($index_goods_one["photo"]); ?>"/>
					</a><?php endif; ?>




<!--			<a class="FindGoods" href="">-->
<!--				<img src="/Public/mobile/images_v3/h_three_ic05.jpg" alt="">-->
<!--			</a>-->

			<div class="FindGoods_01">

				<?php if($index_goods_two['module']=='goods'): ?><a href="<?php echo U('Goods/detail','id='.$index_goods_two['link']);?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_two["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_two['module']=='shop'): ?>
					<a href="<?php echo U('Shop/index','shop_id='.$index_goods_two['link']);?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_two["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_two['module']==category): ?>
					<a href="<?php echo U('Goods/index','cat_id='.$index_goods_two['cate_id']);?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_two["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_two['module']=='article'): ?>
					<a href="<?php echo U('Index/messageInfo','id='.$index_goods_two['link']);?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_two["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_two['module']=='link'): ?>
					<a href="<?php echo ($index_goods_two['link']); ?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_two["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_two['module']=='false'): ?>
					<a href="<?php echo ($index_goods_two['link']); ?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_two["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_two['module']==''): ?>
					<a href="" class="FindGoods_02">
						<img src="<?php echo ($index_goods_two["photo"]); ?>"/>
					</a><?php endif; ?>



				<?php if($index_goods_three['module']=='goods'): ?><a href="<?php echo U('Goods/detail','id='.$index_goods_three['link']);?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_three["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_three['module']=='shop'): ?>
					<a href="<?php echo U('Shop/index','shop_id='.$index_goods_three['link']);?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_three["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_three['module']==category): ?>
					<a href="<?php echo U('Goods/index','cat_id='.$index_goods_three['cate_id']);?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_three["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_three['module']=='article'): ?>
					<a href="<?php echo U('Index/messageInfo','id='.$index_goods_three['link']);?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_three["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_three['module']=='link'): ?>
					<a href="<?php echo ($index_goods_three['link']); ?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_three["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_three['module']=='false'): ?>
					<a href="<?php echo ($index_goods_three['link']); ?>" class="FindGoods_02">
						<img src="<?php echo ($index_goods_three["photo"]); ?>"/>
					</a>
					<?php elseif($index_goods_three['module']==''): ?>
					<a href="" class="FindGoods_02">
						<img src="<?php echo ($index_goods_three["photo"]); ?>"/>
					</a><?php endif; ?>


<!--				<a href="">-->
<!--					<img src="/Public/mobile/images_v3/h_three_ic07.jpg" alt="">-->
<!--				</a>-->
			</div>

		</div>
	</div>
	<!--小站快报渲染模块-->
	<div class="inx-art-div">
		<div class="inx-article">
			<ul class="marquee">
				<?php if(is_array($news)): $i = 0; $__LIST__ = $news;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li class="notice-li" v-for="v in news">
						<a href="<?php echo U('index/messageInfo','id='.$vo['id']);?>"><?php echo ($vo["title"]); ?></a>
					</li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		</div>
		<!--更多-->
		<div class="inx-art-more">
			<a href="<?php echo U('index/saveAllMessage','type=1');?>">
				<span class="inx-art-line"></span> 更多
				<img src="/Public/mobile/images_v3/f_dm34.png"/>
			</a>
		</div>
	</div>
</div>
<!--背景图-->
<div class="ban_va">
	<?php if(is_array($Advertisement)): $i = 0; $__LIST__ = $Advertisement;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['module']=='goods'): ?><a href="<?php echo U('Goods/detail','id='.$vo['link']);?>"><img src="<?php echo ($vo["photo"]); ?>" width="100%" height="100%"/></a>
			<?php elseif($vo['module']=='shop'): ?>
			<a href="<?php echo U('Shop/index','shop_id='.$vo['link']);?>"><img src="<?php echo ($vo["photo"]); ?>" width="100%" height="100%"/></a>
			<?php elseif($vo['module']==category): ?>
			<a href="<?php echo U('Goods/index','cat_id='.$vo['cate_id']);?>">
	<img src="<?php echo ($vo["photo"]); ?>" width="100%" height="100%"/></a>
			<?php elseif($vo['module']=='article'): ?>
			<a href="<?php echo U('Index/messageInfo','id='.$vo['link']);?>"><img src="<?php echo ($vo["photo"]); ?>" width="100%"
																	   height="100%"/></a>
			<?php elseif($vo['module']=='link'): ?>
			<a href="<?php echo ($vo['link']); ?>"><img src="<?php echo ($vo["photo"]); ?>" width="100%" height="100%"/></a>
			<?php elseif($vo['module']=='false'): ?>
			<a href="<?php echo ($vo['link']); ?>"><img src="<?php echo ($vo["photo"]); ?>" width="100%" height="100%"/></a>
			<?php elseif($vo['module']==''): ?>
			<img src="<?php echo ($vo["photo"]); ?>" width="100%" height="100%"/><?php endif; endforeach; endif; else: echo "" ;endif; ?>
</div>

<!--猜你喜欢模块-->
<div class="guess_like">优质商品</div>
<div class="inx-like">
	<ul id="inx_id" class="in_pad">
		<!--这里加载商品数据-->
	</ul>
</div>
<!--end-->
<div style="clear:both;color: #73777a;font-size: 3vw;text-align: center;padding-top: 2vw;">ICP备案号：<a target="_blank"
																									 href="http://beian.miit.gov.cn"
																									 style="color: #73777a;font-size: 3vw;text-align: center;"><?php echo ($icp); ?></a>
</div>
<!--首页客服start-->
<div class="share_view">
	<div class="share_icon icon_four customer_service"></div>
	<div class="hiddshare">
		<a class="share_icon icon_three" href="<?php echo U('Cart/index');?>"></a>
		<div class="share_icon icon_tow" id="btn_share"></div>
	</div>
	<div class="share_icon">
		<div class="icon_one" data-type="1"></div>
	</div>
</div>
<!--首页客服end-->
<!--分享弹出窗-->
<div id="shareModal" class="sharemodal">
	<!-- 弹窗内容 -->
	<div class="share-content">
		<h6 class="share_h6">------- 分享到 --------</h6>
		<ul class="share_ul">
			<li class="wx">
				<img src="/Public/images/share/wechat.png" style="width: 12vw;height: 12vw;"/>
				<p>微信好友</p>
			</li>
			<li class="wx">
				<img src="/Public/images/share/circle.png" style="width: 12vw;height: 12vw;"/>
				<p>朋友圈</p>
			</li>

			<li class="icopy" data-clipboard-text="<?php echo ($share["url"]); ?>" type="button">
				<img src="/Public/images/share/copylink.png" style="width: 12vw;height: 12vw;"/>
				<p>复制链接</p>
			</li>

			<div class="clear"></div>
		</ul>
		<p class="share_pp" onclick="closeClick();">取消</p>

	</div>
</div>
<!--首页分享end-->

<!--客服弹出-->
<div id="customer_service" class="weui-popup__container popup-bottom">
	<div class="weui-popup__overlay"></div>
	<div class="weui-popup__modal">
		<div class="weui_pop_title">
			<div class="weui_pop_title_txt">联系方式</div>
			<button class="weui_pop_title_close close-popup"><img src="/Public/mobile/images_v3/cross.png"
																  class="weui_pop_title_close_img"></button>
		</div>
		<div class="weui_pop_content_wrap">
			<?php if($kefu['phone']): ?><div class="weui_kefu_block">
					<a href="tel:<?php echo ($kefu['phone']); ?>">电话：<?php echo ($kefu['phone']); ?></a><span>（拨打）</span>
				</div><?php endif; ?>
			<?php if($kefu['qq']): ?><div class="weui_kefu_block">
					<a type="button" class="icopy" data-clipboard-text="<?php echo ($kefu["qq"]); ?>"
					   href="http://wpa.qq.com/msgrd?v=3&site=qq&menu=yes&uin=<?php echo ($kefu["qq"]); ?>">QQ：<?php echo ($kefu['qq']); ?></a><span>（复制）</span>
				</div><?php endif; ?>
			<div class="weui_kefu_block yzh_kefu" style="display: none;">
				<a type="button" class="yzh_kefu">在线客服</a><span>（即时聊天）</span>
			</div>

			<div class="btn_center">
				<button class="btn_ok btn_long close-popup">知道了</button>
			</div>
		</div>
	</div>
</div>

<div style="width: 100%;height: 55px; display:inline-block"></div>
<footer class="bottom-nav">
    <div class="nav-div <?php echo ($menu_Shop); ?>">
	    <a href="<?php echo U('M/Index/index');?>">
			<i class="nav-icon3"></i>
			<p>首页</p >
		</a>
	</div>
	<div class="nav-div <?php echo ($menu_category); ?>">
	    <a href="<?php echo U('M/Goods/category');?>">
			<i class="nav-icon4"></i>
			<p>分类</p >
		</a>
	</div>
	<div class="nav-div <?php echo ($menu_near); ?>" style="display: none;">
	    <a href="<?php echo U('M/Near/index');?>">
			<i class="nav-icon5"></i>
			<p>附近</p >
		</a>
	</div>
	<div class="nav-div <?php echo ($menu_center); ?>">
	    <a href="<?php echo U('M/User/index');?>">
			<i class="nav-icon2"></i>
			<p>我的</p >
		</a>
	</div>
</footer>

<script src="/Public/mobile/js/vue.min.js"></script>
<script src="/Public/mobile/js/jquery-2.1.4.js"></script>
<script src="/Public/mobile/js/jquery-weui.min.js"></script>
<script src="/Public/mobile/js/swiper.min.js"></script>
<script src="/Public/mobile/js/dropload.min.js"></script>
<script type="text/javascript" src="//res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
<script type="text/javascript" src="/Public/mobile/js/clipboard.min.js"></script>


<!--腾讯云智服务在线客服-->
<script src="https://yzf.qq.com/xv/web/static/chat_sdk/yzf_chat.min.js"></script>
<script>
	//参数说明
	//sign：公司渠道唯一标识，复制即可，无需改动
	//uid：用户唯一标识，如果没有则不填写，默认为空
	//data：用于传递用户信息，最多支持5个，参数名分别为c1,c2,c3,c4,c5；默认为空
	//selector：css选择器(document.querySelector, 如#btnid .chat-btn等)，用于替换默认的常驻客服入口
	//callback(type, data): 回调函数,type表示事件类型， data表示事件相关数据
	//type支持的类型：newmsg有新消息，error云智服页面发生错误， close聊天窗口关闭
	window.yzf && window.yzf.init({
		sign: '37ef9b97d47501c370439ae84fe4b66a51d42db04c5f1d6d9b7d735b651adccc2d888745595690cb91edb453bae136192e242976',
		uid: '',
		data: {
			c1: '',
			c2: '',
			c3: '',
			c4: '',
			c5: ''
		},
		selector: '.yzh_kefu',
		callback: function (type, data) {
		}
	})
	//window.yzf.close() 关闭1已打开的回话窗口
</script>

<script type="text/javascript">

	$('#index').dropload({
		scrollArea: window,
		domDown : {
			domClass   : 'dropload-down',
			domRefresh : '<div class="dropload-refresh">↑上拉加载更多</div>',
			domLoad    : '<div class="dropload-load"><span class="loading"></span>加载中...</div>',
			domNoData  : '<div class="dropload-noData">已经到底了 </div>'
		},
		loadUpFn : function(me){
			location.reload()
		}
	})

		var swiper = new Swiper('.swiper-container_nav', {
		pagination: '.swiper-pagination-2', //轮播点点
		slidesPerView: 5, //一行显示5个
		slidesPerColumn: 2, //显示两行
		paginationClickable: true, // 此参数设置为true时，点击分页器的指示点分页器会控制Swiper切换。
		spaceBetween: 1 // 滑块之间的距离5px
	});
	$(".swiper-container").swiper({
		loop: true,
		autoplay: 3000
	});
	$("#searchShow").click(function () {
		$(".select-div").show();
	});
	$("#searcCancel").click(function () {
		$(".select-div").hide();
	});
	var num = $('.inx-article').find('li').length;
	if (num > 1) {
		var time = setInterval('time_play(".inx-article")', 3500);
		$('.gg_more a').mousemove(function () {
			clearInterval(time);
		}).mouseout(function () {
			time = setInterval('timer(".inx-article")', 3500);
		});
	}

	function time_play(opj) {
		$(opj).find(".marquee").animate({
			marginTop: "-8.5vw"
		}, 500, function () {
			$(this).css({
				marginTop: "0"
			}).find("li:first").appendTo(this);
		})
	}

	//上拉加载
	$(function () {

		// 点击客服弹出
		$(".customer_service").on("click", function () {
			$("#customer_service").popup();
		});

		// 页数
		var page = 0;
		var stop = 0;
		// dropload
		$('.inx-like').dropload({
			scrollArea: window,
			domDown : {
				domClass   : 'dropload-down',
				domRefresh : '<div class="dropload-refresh">↑上拉加载更多</div>',
				domLoad    : '<div class="dropload-load"><span class="loading"></span>加载中...</div>',
				domNoData  : '<div class="dropload-noData">我是有底线的 </div>'
			},
			loadDownFn: function (me) {
				if(stop == 0){
					page++; // 拼接HTML
					var html = '';
					$.ajax({
						type: 'GET',
						url: "<?php echo U('Index/goodsListIndex');?>?page=" + page,
						dataType: 'json',
						success: function (data) {
							stop = data.status;
							if (data.status == 1) {
								// $(".dropload-down").remove();
								$(".dropload-noData").text('我是有底线的');
								me.resetload();
								return;
							}
							var arrLen = data.result.length;
							if (arrLen > 0) {
								$.each(data.result, function (index, item) {
									if (item.tags) {
										var tags = '<span class="cakes">' + item.tags + '</span>';
									} else {
										var tags = '';
									}
									html += '<li>';
									html += '<a href="<?php echo U('Goods/detail');?>?id=' + item.id + '">';
									html += '<img class="pro_img" src="' + item.thumb + '"/>';
									html += '<div class="pro-info">';
									html += '<p class="elipe cakes_tit">' + tags + '<span class="goods_title">' + item.name + '</span></p>';
									// html += '<span class="cakes">热销商品</span>';
									html += '<div class="pro-info-tootm">';
									html += '<div class="price">';
									html += '￥<span>' + item.price + '</span>';
									html += '</div>';
									html += '<img src="/Public/mobile/images_v3/f_dm26.png"/>';
									html += '</div>';
									html += '<div class="pro-info-tootm1">';
									html += '<div class="price">';
									html += '<span style="font-size: 3.5vw;">已售:'+item.sell_count+'</span>';
									html += '<p><span style="font-size: 3.5vw;">评论:'+item.common_count+'</span></p>';
									html += '</div>';
									html += '</div>';
									html += '</div>';
									html += '</a>';
									html += '</li>';
								});
								// 如果没有数据
							} else {
								$(".dropload-noData").text('我是有底线的');
								me.lock(); // 锁定
								me.noData(); // 无数据
							}
							// 为了测试，延迟1秒加载
							setTimeout(function () {
								// console.log(html) // 插入数据到页面，放到最后面
								$('#inx_id').append(html); // 每次数据插入，必须重置
								me.resetload();
							}, 8);
						},
						error: function (xhr, type) {
							// alert('Ajax error!'); // 即使加载出错，也得重置
							me.resetload();
						}});
				}else{
					me.lock(); // 锁定
					me.noData(); // 无数据
					// 为了测试，延迟1秒加载
					setTimeout(function () {
						// console.log(html) // 插入数据到页面，放到最后面
						$('#inx_id').append(html); // 每次数据插入，必须重置
						me.resetload();
					}, 8);
				}
			}
		});
	});
	//秒杀时间倒计时
	var time = $('#time').val();
	var tmp = Date.parse(new Date()).toString();
	tmp = tmp.substr(0, 10);
	time = time - tmp;
	if (time == 0) {
		location.reload();
	}
	if (time < 0) {
		time = 3600;
	}
	//console.log(time);
	if (time) {
		var maxtime = time;
		timer = setInterval("CountDown()", 1000);
	}

	//一个小时，按秒计算，自己调整!
	function CountDown(a) {
		// console.log(maxtime)
		if (maxtime >= 0) {
			hour = Math.floor(maxtime / 3600);
			minutes = Math.floor((maxtime - hour * 3600) / 60);
			seconds = Math.floor(maxtime % 60);
			if (hour < 10) {
				hour = "0" + hour
			}
			if (minutes < 10) {
				minutes = "0" + minutes
			}
			if (seconds < 10) {
				seconds = "0" + seconds
			}
			$('#hour').html(hour);
			$('#minute').html(minutes);
			$('#second').html(seconds);
			--maxtime;
		} else {
			maxtime = 3600;
			timer = setInterval("CountDown()", 1000);
		}
	}

	//	客服展开关闭
	$(function () {
		$('.icon_one').click(function () {
			var openShare = $(this);
			var type = openShare.data("type");
			if (type == 1) {
				openShare.addClass('close');
				openShare.data("type", "0");
			} else {
				openShare.removeClass('close');
				openShare.data("type", "1");
			}
			$('.hiddshare').slideToggle()
		})
	})

	//分享弹窗
	$('#btn_share').click(function () {
		$('#shareModal').css('display', 'block');
	})

	function closeClick() {
		$('#shareModal').css('display', 'none');
	}

	$(".wx").click(function () {
		layer.open({
			content: "请点击右上角发送给朋友!"
			, skin: 'msg'
			, time: 3
		});
		$("#shareModal").css('display', 'none');
	})

	$(".pyq").click(function () {
		layer.open({
			content: "请点击右上角分享到朋友圈!"
			, skin: 'msg'
			, time: 3
		});
		$("#shareModal").css('display', 'none');
	})
	// 复制QQ、微信
	$(".icopy").click(function () {
		var clipboard = new ClipboardJS('.icopy');
		clipboard.on('success', function (e) {
			layer.open({
				content: "复制成功"
				, skin: 'msg'
				, time: 2
			});
		});
		clipboard.on('error', function (e) {
			layer.open({
				content: "复制失败,请手动复制"
				, skin: 'msg'
				, time: 2
			});
		});
	});

	wx.config({
		debug: false,
		appId: '<?php echo ($wxjssign["appId"]); ?>',
		timestamp: '<?php echo ($wxjssign["timestamp"]); ?>', // 必填，生成签名的时间戳
		nonceStr: '<?php echo ($wxjssign["nonceStr"]); ?>', // 必填，生成签名的随机串
		signature: '<?php echo ($wxjssign["signature"]); ?>', // 必填，签名，见附录1
		jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareQZone', 'openLocation', 'getLocation', 'wx-open-launch-weapp'], // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
		openTagList: ['wx-open-launch-weapp'] // 可选，需要使用的开放标签列表，例如['wx-open-launch-app']
	});
	wx.ready(function () {
		var shareObj = {
			title: "<?php echo ($share["title"]); ?>",
			desc: "<?php echo ($share["desc"]); ?>",
			link: "<?php echo ($share["url"]); ?>",
			imgUrl: "<?php echo ($share["thumb"]); ?>"
		};
		wx.onMenuShareTimeline({
			title: shareObj.title, // 分享标题
			desc: shareObj.desc, // 分享描述
			link: shareObj.link, // 分享链接
			imgUrl: shareObj.imgUrl,
			success: function () {
				share_inst()
			}
		});
		wx.onMenuShareAppMessage({
			title: shareObj.title, // 分享标题
			desc: shareObj.desc, // 分享描述
			link: shareObj.link, // 分享链接
			imgUrl: shareObj.imgUrl,
			success: function () {
				share_inst()
			}
		});
		wx.onMenuShareQQ({
			title: shareObj.title, // 分享标题
			desc: shareObj.desc, // 分享描述
			link: shareObj.link, // 分享链接
			imgUrl: shareObj.imgUrl,
			success: function () {
				share_inst()
			}
		});
		wx.onMenuShareQZone({
			title: shareObj.title, // 分享标题
			desc: shareObj.desc, // 分享描述
			link: shareObj.link, // 分享链接
			imgUrl: shareObj.imgUrl,
			success: function () {
				share_inst();
			}
		});
	});
</script>
</body>

</html>