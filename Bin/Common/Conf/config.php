<?php
return array(
    'DB_TYPE' => 'mysql',             // 数据库类型
    /*
    'DB_HOST' => 'rm-bp1tuzv5ev45f9rhcro.mysql.rds.aliyuncs.com',         // 服务器地址
    'DB_NAME' => 'tiantianfan.yuanzhihang.com20211021',    // 数据库名
    'DB_USER' => 'tiantianfan',              // 用户名
    'DB_PWD' => 'tiantianfan@1302',              // 密码
    'DB_PORT' => '3306',              // 端口
     */

    'DB_HOST' => 'mysql',         // 服务器地址
    'DB_NAME' => 'tiantianfan',    // 数据库名
    'DB_USER' => 'root',              // 用户名
    'DB_PWD' => 'root',              // 密码
    'DB_PORT' => '3306',              // 端口

    'DB_PREFIX' => 'sns_',              // 数据库表前缀
    'DB_CHARSET' => 'utf8',
    'DB_DEBUG' => '',
    'TMPL_ACTION_ERROR' => './Public/hint.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => './Public/hint.tpl', // 默认成功跳转对应的模板文件
    'URL_MODEL' => 2,
    'DEFAULT_MODULE' => 'M',
	'LOAD_EXT_FILE' =>'common',

    'C_HTTP_HOST' => 'http://tiantianfan.yuanzhihang.com/',
    'C_NIU_KEY' => '123456123456',                // 双牛支付账户的KEY

    'C_DF_SHOP_LOGO' => 'http://yzhpubqiniu01.yuanzhihang.com/shop_default.png',        // 商家默认LOGO
    'C_DF_GOODS' => 'http://yzhpubqiniu01.yuanzhihang.com/shop_default.png',        // 商品默认图片
    'C_DF_CATE' => 'http://yzhpubqiniu01.yuanzhihang.com/cate_default.png',        // 分类默认图片

);
