<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html><head>
	<meta charset="utf-8">
    <title><?php echo GC('web_title');?>管理后台-登录</title>
    <meta name="description" content="Dashboard">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="/Public/admin_new/css/bootstrap.css?v=1.2.0" rel="stylesheet">
	<link href="/Public/admin_new/css/index_beyond.css?v=1.2.0" rel="stylesheet" type="text/css" id="beyond-link">
	<link href="/Public/admin_new/css/index_demo.css?v=1.2.0" rel="stylesheet">
	<link href="/Public/admin_new/css/index_animate.css?v=1.2.0" rel="stylesheet">
	<style>
		.login-container.animated.fadeInDown{
			z-index:666;
			width: 96%;
			max-width: 410px;
			margin: 15% auto;
		}
	</style>
</head>
<body>
    <div class="loginbg">
		<div class="login-container animated fadeInDown">
		    <!--<div class="title-tio"><?php echo GC('web_title');?></div>-->
			<form action="" method="post">
				<div class="dlbg" >
					<div class="dlbg_login">账户登录</div>
					<div class="loginbox-textbox">
						<input class="input_name" placeholder="用户名" name="username" type="text">
						<img src="/Public/admin_new/images/name.png" width="20px"height="20px"/>
					</div>
					<div class="loginbox-textbox">
						<input class="input_name" placeholder="密码" name="password" type="password">
						<img src="/Public/admin_new/images/passsword.png" width="23px"height="23px"/>
					</div>
					<div class="loginbox-submit">
						<input class="btn btn-primary btn-block" value="登录" type="submit">
					</div>
					<!--<div class="account">
						<p>没有账户?</p>
						<p>忘记密码</p>
					</div>-->
				</div>
					<!--<div class="logobox">
						<p class="text-center">版权所有 &copy; 2017-<?php echo date('Y');?> <?php echo GC('web_title');?> <br/> 技术支持：山东远之航信息技术有限公司
						</p>
					</div>-->
			</form>
		</div>
		<div class="login-bg-img"><img src="/Public/admin_new/images/icon_earth.040b199d.png"></div>
    </div>
</body>
</html>