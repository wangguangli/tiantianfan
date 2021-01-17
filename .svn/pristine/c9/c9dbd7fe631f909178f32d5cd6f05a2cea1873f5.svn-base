<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>跳转提示</title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <style>
        * {
            padding: 0;
            margin: 0;
            font-family: "microsoft yahei", Arial, Helvetica, sans-serif;
        }
        img {
            border: 0;
        }

        @media screen and (min-width: 1000px) {
            .user_cont{
                width: 1000px;
                margin: 0 auto;
            }
            .user_cont_img{
                width: 521px;
                height: 400px;
                margin: 0 auto;
                margin-top: 125px;
            }
            .user_cont_img img{
                width: 100%;
                height: 100%;
            }
            .user_cont_text{
                text-align: center;
                color: #606060;
                padding-bottom: 10px;
            }
            .user_cont_text p{
                font-size: 20px;
                margin-bottom: 12px;
            }
            .user_cont_text span{
                font-size: 15px;
            }

        }

        @media screen and (max-width: 1000px) {
            .user_cont{
                overflow: hidden;
                margin: 0 auto;
            }
            .user_cont_img{
                width: 58vw;
                height:44.6vw;
                margin: 0 auto;
                margin-top: 125px;
            }
            .user_cont_img img{
                width: 100%;
                height: 100%;
            }
            .user_cont_text{
                text-align: center;
                color: #606060;
            }
            .user_cont_text{
                font-size: 15px;
                padding-bottom: 6px;
            }
            .user_cont_text span{
                font-size: 14px;
            }
        }

        .user_cont_text a{
            text-decoration: none;
            color: #4e4e4e;
        }
        .txtb{
            color: #4e4e4e;
            font-weight: bold;
        }


    </style>
</head>
<body>
<div class="user_cont">
    <?php if(isset($message)) {?>
    <div class="user_cont_img">
        <img src="__PUBLIC__/images/success.png" />
    </div>
    <p class="user_cont_text txtb"><?php echo($message); ?></p>
    <?php }else{?>
    <div class="user_cont_img">
        <img src="__PUBLIC__/images/err.png" />
    </div>
    <p class="user_cont_text txtb"><?php echo($error); ?></p>
    <?php }?>
    <div class="user_cont_text jump">
        <span>页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间： <b style="font-weight: normal;" id="wait"><?php echo($waitSecond); ?></b></span>
    </div>


</div>
<script type="text/javascript">
	(function () {
		var wait = document.getElementById('wait'), href = document.getElementById('href').href;
		var interval = setInterval(function () {
			var time = --wait.innerHTML;
			if (time <= 0) {
				location.href = href;
				clearInterval(interval);
			}
			;
		}, 1000);
	})();
</script>
</body>
</html>