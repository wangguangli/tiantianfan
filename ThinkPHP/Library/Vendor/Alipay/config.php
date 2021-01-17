<?php
$config = array (	
		// 在新模板中，这里不再使用，直接取数据库数据
		//应用ID,您的APPID。
		'app_id' => "",

		//商户私钥
		'merchant_private_key' => "",
		
		//异步通知地址
		'notify_url' => "http://zhaoxing.zhihuimoney.com/index.php/Api/Notifyin/alipay_notify",
		
		//同步跳转
		'return_url' => "http://zhaoxing.zhihuimoney.com/index.php/Api/Notifyin/alipay_notify",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "",
);