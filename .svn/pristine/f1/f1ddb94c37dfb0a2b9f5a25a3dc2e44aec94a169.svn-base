<?php

namespace Api\Controller;

use Think\Controller;
use Clas\Ad;
use Clas\Article;
use Clas\Goods;

class IndexoutController extends CommonController
{


	/**
	 * 分类列表
	 * @return  json
	 * @wz
	 */
	public function industryList()
	{
		$id = I('id');
		if ($id)
		{
			$list = M('industry')->where("id = {$id}")->field('id,name,icon')->find();
		}
		else
		{
			$list = M('industry')->field('id,name,icon')->select();
		}
		jsonout('分类列表', 0, $list);
	}

	/**
	 * 首页信息输出
	 * @param string $user_id 用户ID
	 * @return  json
	 * 说明：此接口会输出banner、分类、书报、广告、精品推荐
	 * @zd
	 */
	public function mall()
	{
		$ad = new Ad();

		// banner
		$data["position"] = "goods_top_banner";
		$data["row"] = 5;
		$ad1 = $ad->adList($data);
		$ad1 = $ad1[2];

		// 中间广告
		$data["position"] = "goods_middle_banner";
		$data["row"] = 1;
		$ad2 = $ad->adList($data);
		$ad2 = $ad2[2];

		// 精品推荐 左
		$data["position"] = "goods_f_banner";
		$data["row"] = 1;
		$ad31 = $ad->adList($data);

		// 精品推荐 右上边
		$data["position"] = "goods_r_t_banner";
		$data["row"] = 1;
		$ad32 = $ad->adList($data);

		// 精品推荐 右左下
		$data["position"] = "goods_r_fd_banner";
		$data["row"] = 1;
		$ad33 = $ad->adList($data);

		// 精品推荐 右右下
		$data["position"] = "goods_r_rd_banner";
		$data["row"] = 1;
		$ad34 = $ad->adList($data);

		$ad31 = full_pre_url($ad31[2][0], "photo");
		$ad32 = full_pre_url($ad32[2][0], "photo");
		$ad33 = full_pre_url($ad33[2][0], "photo");
		$ad34 = full_pre_url($ad34[2][0], "photo");
		$ad_ept = array(
			"ad_id" => 0,
			"photo" => "",
			"module" => "",
			"mod_id" => 0,
			"api_url" => "",
			"link" => "",
		);
		$ad31 = $ad31 ? $ad31 : $ad_ept;
		$ad32 = $ad32 ? $ad32 : $ad_ept;
		$ad33 = $ad33 ? $ad33 : $ad_ept;
		$ad34 = $ad34 ? $ad34 : $ad_ept;
		$ad3 = array($ad31, $ad32, $ad33, $ad34);

		$all = M('goods_category')->where("pid=0")->field('id,name,pid,img')->select();
		if ($all)
		{
			$all = full_pre_url($all, "img");
		}
		else
		{
			$all = array();
		}

		foreach ($all as $key => &$value)
		{
			if (!$value['img'])
			{
				$value['img'] = "";
			}
		}

		$news = array();    //快报列表
		$arc = new Article();
		$data_arc['type'] = 1;
		$news = $arc->articleList($data_arc);
		$news = $news[2];

		$code_status = get_config("code_status");
		$name_verify = get_config("name_verify");
		$code_status = $code_status ? $code_status : 0;
		$name_verify = $name_verify ? $name_verify : 0;
		$plat_qq = get_config("qq");
		$plat_kefu_mobile = get_config("kefu_mobile");
		$plat_weixin = "";
		$common_para = array(
			"code_status" => $code_status,
			"name_verify" => $name_verify,
			"plat_qq" => $plat_qq,
			"plat_kefu_mobile" => $plat_kefu_mobile,
			"plat_weixin" => $plat_weixin,
		);

		$goods = new Goods();
		// 拼团
		$where_gp["is_group"] = 1;
		$where_gp["isdel"] = 0;
		$where_gp["is_on_sale"] = 1;
		$where_gp["product_type"] = 0;
		$groupList = $goods->goodsList($where_gp, "sort asc, id desc", 3);
		if (!$groupList)
		{
			$groupList = array();
		}
		foreach ($groupList as $key => &$value)
		{
			if (!$value["name"])
			{
				$value["name"] = "今日团购";
			}
		}
		$goods_group["title"] = "购物拼团";
		$goods_group["desc"] = "好物超低购";
		$goods_group["goods_list"] = $groupList;

		// 秒杀
		$where_fla["is_hour"] = 1;
		$where_fla["isdel"] = 0;
		$where_fla["is_on_sale"] = 1;
		$where_fla["product_type"] = 0;
		$flashList = $goods->goodsList($where_fla, "sort asc, id desc", 3);
		//计算  当前时间段的可秒杀商品
		foreach ($flashList as $key2 => &$value2)
		{
			// 看看是否有符合当前时间的商品
			$flashStartTime = strtotime(date("Y-m-d H:00:00", time()));
			$flashEndTime = $flashStartTime + 3600;
			$whereFT = "goods_id=" . $value2['id'] . " and end_time>=" . $flashStartTime . " and start_time<" . $flashEndTime;
			$isHave = M("goods_hour")->where($whereFT)->find();
			if (!$isHave)
			{
				unset($flashList[$key2]);
				continue;
			}

		}


		if (!$flashList)
		{
			$flashList = array();
		}
		foreach ($flashList as $key2 => &$value2)
		{
			if ($value2["name"])
			{
				$value2["name"] = "今日秒杀";
			}
		}
		$flashList = array_values($flashList);

		$nextHour = strtotime(date("Y-m-d H:0:0"));
		$nextHour = $nextHour + 3600;
		$reset_time = $nextHour - time();
		$goods_flash["title"] = "秒杀时间";
		$goods_flash["reset_time"] = $reset_time;
		$goods_flash["goods_list"] = $flashList;

		// 今天好货
		$goods_today = array();
		$where_today["hot_id"] = 2;
		$where_today["isdel"] = 0;
		$where_today["is_on_sale"] = 1;
		$todayList = $goods->goodsList($where_today, "sort asc, id desc", 4);
		if (!$todayList)
		{
			$todayList = array();
		}
		$goods_today["title"] = "今日好货";
		$goods_today["desc"] = "品质新生活";
		$goods_today["goods_list"] = $todayList;

		// 第二版广告 4个分开处理
		$data4["position"] = "four_ads_1_v2";
		$data4["row"] = 2;
		$ad4s = $ad->adList($data4);
		$ad4_1["title"] = $ad4s[2][0]["title"] ? $ad4s[2][0]["title"] : "人气冠军";
		$ad4_1["content"] = $ad4s[2][0]["content"] ? $ad4s[2][0]["content"] : "好评如潮，质量超好";
		$ad4_1["goods_list"] = $ad4s[2];

		$data4["position"] = "four_ads_2_v2";
		$data4["row"] = 2;
		$ad4s = $ad->adList($data4);
		$ad4_2["title"] = $ad4s[2][0]["title"] ? $ad4s[2][0]["title"] : "畅销冠军";
		$ad4_2["content"] = $ad4s[2][0]["content"] ? $ad4s[2][0]["content"] : "销量过万，订单不断";
		$ad4_2["goods_list"] = $ad4s[2];

		$data4["position"] = "four_ads_3_v2";
		$data4["row"] = 2;
		$ad4s = $ad->adList($data4);
		$ad4_3["title"] = $ad4s[2][0]["title"] ? $ad4s[2][0]["title"] : "新品上市";
		$ad4_3["content"] = $ad4s[2][0]["content"] ? $ad4s[2][0]["content"] : "全新品牌，卓越品质";
		$ad4_3["goods_list"] = $ad4s[2];

		$data4["position"] = "four_ads_4_v2";
		$data4["row"] = 2;
		$ad4s = $ad->adList($data4);
		$ad4_4["title"] = $ad4s[2][0]["title"] ? $ad4s[2][0]["title"] : "明星单品";
		$ad4_4["content"] = $ad4s[2][0]["content"] ? $ad4s[2][0]["content"] : "甄选、优质、超值";
		$ad4_4["goods_list"] = $ad4s[2];

		$result["ad1"] = full_pre_url($ad1, "photo");
		$result["ad2"] = full_pre_url($ad2, "photo");
		$result["ad3"] = $ad3;
		$result["catelist"] = $all;
		$result["news"] = full_pre_url($news, "thumb");
		$result["common_para"] = $common_para;

		$result["goods_group"] = $goods_group;    // 拼团
		$result["goods_flash"] = $goods_flash;    // 秒杀
		$result["goods_today"] = $goods_today;    // 今日好货
		$result["ad4_1"] = $ad4_1;    // 第二版广告 4个分开处理
		$result["ad4_2"] = $ad4_2;    // 第二版广告 4个分开处理
		$result["ad4_3"] = $ad4_3;    // 第二版广告 4个分开处理
		$result["ad4_4"] = $ad4_4;    // 第二版广告 4个分开处理
		$result["banner_notice_1"] = "24小时急速发货";
		$result["banner_notice_2"] = "7天无忧退换";
		$result["banner_notice_3"] = "48小时快速退款";

		jsonout("首页上方信息", 0, $result);
	}


	function test()
	{
		$arr = array('订单ID', '订单号', '下单用户ID', '下单用户手机号', '订单总额', '支付方式');
		$arr = json_encode($arr);
		echo "<pre>";
		print_r($arr);
	}

	public function upload_image()
	{
		$case = I("case");
		$ok = common_upload_photo($case, $case);
		if (empty($ok['success']))
		{
			api_is_str($ok['msg']);
		}
		jsonout('ok', 0, array('img' => $ok['msg']));
	}

	//首页推广二维码
	public function get_index_code()
	{
		$token = get_wxtoken();
		if (!$token)
		{
			exit;
		}
		$url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $token;

		$user_id = I('user_id');

		$arr = array(
			'scene' => $user_id,
			'page' => 'pages/mall/index'
		);
		$resp = httpRequest($url, 'POST', json_encode($arr));
		header('content-type:image/jpeg');
		exit($resp);
	}

	// APP端测试
	public function app_index()
	{
		// 提交过来信息
		$third_path = THINK_PATH . "/Library/Vendor/Zhima/";
		require_once $third_path . 'AopClient.php';
		require_once $third_path . 'AopCertification.php';
		require_once $third_path . 'request/AlipayFundAuthOrderAppFreezeRequest.php';

		/*$app_id = '2021001190680009';

		$private_key = 'MIIEpAIBAAKCAQEA234Ce0MZ+LR15GS7vnreEB7jbcxWeJ1iM1CaKf4R7OScVSFlNyHvTQV+qgygMllifmoOjnyXwTq4lQhcMZLRQyrfhQbDVcSAFEvNw5HdtKxdEyTjLwuPMLEClb6eDMmlyrqNHpjkbTUocuNPwx/v7GYX5IGbFcUEUBYWk/Yv33ZvTh+FoP90wXRpZ0Hh+TcignTTm25R9LeD0LBm2eOQRNLNrUr5TE5PTN+0t9gAN4Tb+iVGVadTFw0/9+bVElTAKFGnPlpT9mmYADxQiRC6UXKUL7532MV/D7CsRQuPOAiE5zibsHKW27AJrEA9LcwtLI6Bca53qavSQUywxfNb8wIDAQABAoIBAQC5OivJ7aSbN19N8JxncLS5kfeHjyth+h8HAvw2d8yUx5AOX3JVBnQ14W80/haqprfxynqiUA6Y6H8gdb+nriiesC2Uy1JIrN0RQ69PXWdlsxeQsk5uQykBhD+UMxqZ3AnnfWrd6VTLvFJ4Mc/78JW+P6HmGmHQC0VKeHlxQA7CWlYL0GuJlL44bmkqcTnN7srrAylnxHdMqwWuD05qiStAOrAaCnnSR8sNScnLYDN3jJzyHY/+l401F6c86odimeJshEe7J4RGjtelzY3Pm4mWUYhR1ZbQyIycDQw8ujZFaFJ/t3cOlUgm0iWVwFpXeCh8h9Ny3WsAPTIZtLO5/k1hAoGBAPEPPfBOTlJOIMKr2WG2OHhTX4LRtOVf0m0+AkflsmQTnJuLJdnpZD6t7uK6v0M1BBhKj3KSTpJ2J085b6UfZ/083IsaPOF2GEz4GCuFA7vcagsm45KXpQ46RBYDghETGhKSl+iafzmORfIAc+KMLs73l7eTQF6/BQWypyucpPJrAoGBAOkYkjY16Kc6Apf/0eggXmAA+IqDlIcZbynXneifMNvd8KReXOjRlM4/i/HR8nHuki9UZ6saYRzsdGKljfNHW37y3plVgfpf3VKHB4M+T89H+xJiD8M4x4GFt8tST0uBzP/Xgvqn6dXC+aEjLzJjsDwzWgxowL2Lu42Qfo6/ve6ZAoGARrXJ3Wua3dm9El2GbnsdJW6PXbhV79KjzxeZY62lHmYCUj4G5Fzp4tjycd+FqxkqElYMrfbsxVmTPlICxdTolAf+SW9w3FHf3vQHYvypk7mcAZlut9lzuTCuOCbfSFLqGviVRs3K8/8f1VT8qT+lm2BVSKwQ/bnap0k4cDqKx/ECgYEAvAEIdNb4RmDAlM5OBFaPKAnAbSABnRHDmlEBWQ2Ev+F7ZWeCsnnP9qZEfo+iZ4hPugLu9o95QnXozWPQYSbtwi+roKraAhJuFVE5mv6YKoxZEhOruP7D01EB0+kYccNIkBXH29mw/aub4f7Z41+KVpPojlezFW5toJmpqgM28wkCgYBVJM/OjPlCdlrmd0PZf42WwjY+KqZZEUsK8WqSm/jJJkSmmmQb3ojYEifzxW9FuRthJfNlVRD3DHZ/tjm9+qGgYZ1Oj7B1o9JhdSaf+NbA40zKy5pbmq561ZicCzNbL2EEch7MkUwnof6sMJhCoK7BmJMtpqR2TNdmrFzEq9B3rg==';

		$alipay_key = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArEyA1HhD9JbvLfX0WispAzD6bjhoWAxNQLcT8q1cK/qnFT7w2TMzVKp8mebYedjGHkdCRb0yImaewB31MZebjGHAssIZ5247anFGGymljNWNZQj7Bd19lzk7cAgsIBbMFVjg5LLB8kIPPPhXcU+ejXXAGtOjoFqFL9i7cpct/6ODXJWrFBhE3gU+7t4iKENgfLs1w1HACh7ARmvfqBYennUdDSKOpcwKMnDORs645DHJW3BOuRdvz8KgQkYYB1sSfVhnL/Uze56qE6s6Fs5ho+lGKZaoMrS04XSJ/YcYMu4/RRPy2IFxueJt30JtGGDtN5HZRHqtSx/R1a11mgZJwwIDAQAB';*/

		$app_id = "2021001187688170";

		$private_key = "MIIEowIBAAKCAQEAv14Qwcqdhji5N3BGyZG178wDPKB/Ru4kI8OWlQnGp2LxflCfaJQ6dr9Om+BbyEKIWmhbQDhcDChcB2CUcV31ZTPj/dymrfB3OYGlmu74qJOWzjnd2y9+ApUV4O1AJ0LkXe1a1USBpxjEAT8lX7sDQudRIE9L3D+DC4Tsyv4DIB7djX2QpPH9z9OfRIq04x4MrsmM63RYUOoLqGhTxE50RuyBZVX84j4esatuPcr6X47v4EAVczlEHsXVPwUJYDeAb1Sv1CUd8HHbvX+BMMYHMSSnEJuuM1S8HtnCFTnM8VJaiCdOtAsVEirGyw4639Ug5BBAoP09Gw15yikb0nMI8wIDAQABAoIBAFdyqL2l1S4MpbbLu81IpJcb5y7BOOg00pb1Pc6FVR4QfMJ4HSAw/DZfXZClicuNe7m0jl5eYtG7b7J/U9YqVYUVnU3YkuJRTGoe8IpIQaDnMLa0gskyfqa3cfQC9pn6W1kAqc1jLMwq6QaY0H9aejcMdWoKUmQnMb8F0x8/zDXUmnmhDsAeaVOpS2WsmyD7AoIUXmcaknsaXCYuuUCvja/XEAXZiex1jx09KboGfrk7cKNdugxFe7KG4EIw1XJb/PcWqmj3MkA8kOWOxcQTQiW3/Ob5cliLFCoh3nEGL2A6fIb35llXcQ+acWUZozwEpvnRO7j2yiUAwQxRnaJGK8ECgYEA5t486mrxNkauOcaTGvpiyXLj8ZaXhnRNFV8osFetJnWyBULYFOsNSQLA8cbVcBiKWDCDu0TcabK6Z+zQgiPa6Udc+dcf+NmaktB9zxg+Ls8z7kbd2yv0SPcqqFI56wK5LtqgDtzLqwxvtXdwtT4QoP7AxNeEP+ujxXKPi22WWikCgYEA1DMI6YHvMSxTMDqS1CjkiZfQ80eE5aUcgcDLqXKBG8O3SgAmK3qY663sCKCJEycZBhIE7lrRLTyHdDgJ2+dP+qobma4XAAd/o7kjp5vJi19bjSIKeQ0TImG5w4mVtgrTU2Wvnpyx1hRETulRnI+cvgCpq6NKzWt/rDq38c9FZbsCgYARvKM7c0ni3J1IDQyCNxhd17jRd7tedhLyAGSU37eKy+IIa3FEciaMJG0EZj1BpnECg3+rZIf2iuetUlFWnkCUSYpIG5H2QWmRu/jeb0Nfv0WDGeizjPXwoSSi1+ZhOs1VXzCK08XF92ehrnJ3SjRm2gufU9tyOb1UTw/eK2YwuQKBgQDO3q4aj1yz0KWNCB1qji824HWJJrkt9EiVSnKCUCoD+kqu7vRHQO7iHJ1WT8Myk9Q2ccyy3oC8nBzltVgPNTNoiPv9V5X+plDOOUjENwFGSYGEVqJlHtT/mMw0D+aPIYCh9ik+9T3+GaX6VEYG3o8NQLIorTnYh6thKaF4MPxTzwKBgG7UM5JdVY2cDqC2KwIHYgZMgo0s+hZ2xOulH8Rb6l5jruy41qZK4SJtO1rjPbkBVqtzTMs3vXRQKWP52gijU1v5LErNFVhjlxrGFELG846Dgpgw+ga+onaiUXsFOB4oXcf5r6NcURf7srO6EZZOrg4KwBElXgWc5d8XADzby5cs";

		$alipay_key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArEyA1HhD9JbvLfX0WispAzD6bjhoWAxNQLcT8q1cK/qnFT7w2TMzVKp8mebYedjGHkdCRb0yImaewB31MZebjGHAssIZ5247anFGGymljNWNZQj7Bd19lzk7cAgsIBbMFVjg5LLB8kIPPPhXcU+ejXXAGtOjoFqFL9i7cpct/6ODXJWrFBhE3gU+7t4iKENgfLs1w1HACh7ARmvfqBYennUdDSKOpcwKMnDORs645DHJW3BOuRdvz8KgQkYYB1sSfVhnL/Uze56qE6s6Fs5ho+lGKZaoMrS04XSJ/YcYMu4/RRPy2IFxueJt30JtGGDtN5HZRHqtSx/R1a11mgZJwwIDAQAB";


		$invoke_return_url = "http://runfumianya.yuanzhihang.com/M/Index/to_back";
		$notify_url = "http://runfumianya.yuanzhihang.com/M/Index/zhima_notify";
		$out_order_no = make_order_no();
		$out_request_no = make_order_no();
		$order_title = "预授权冻结";
		$amount = 0.01;
		$payee_logon_id = "2113432351@qq.com";
		$payee_user_id = "";
		$pay_timeout = "2m";

		$aop = new \AopClient();
		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		$aop->appId = $app_id;
		$aop->rsaPrivateKey = $private_key;
		$aop->alipayrsaPublicKey = $alipay_key;
		$aop->apiVersion = '1.0';
		$aop->signType = 'RSA2';
		$aop->postCharset = 'utf-8';
		$aop->format = 'json';

		$out_order_no = make_order_no();
		$out_request_no = make_order_no();
		$order_title = "预授权冻结";
		$amount = 1;

		$request = new \AlipayFundAuthOrderAppFreezeRequest();

		$bizcontent = array(
			'out_order_no' => $out_order_no,
			'out_request_no' => $out_request_no,
			'order_title' => $order_title,
			'amount' => $amount,
			'product_code' => 'PRE_AUTH_ONLINE',
			'payee_logon_id' => '2113432351@qq.com'
		);
		$bizcontent = json_encode($bizcontent);

		$request->setBizContent($bizcontent);

		// $result = $aop->execute($request);
		$result = $aop->sdkExecute($request);

		_error_log("-----res---------");
		_error_log($result);

		if (I("t") == 2)
		{
			$this->assign("orderStr", $result);
			$this->display("Index/app_index");
		}
		else
		{
			jsonout("测试预授权", 0, $result);
		}
	}

}