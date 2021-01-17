<?php

namespace Api\Controller;

use Think\Controller;
use Clas\Ad;
use Clas\Article;

class NearoutController extends CommonController
{


	/**
	 * 附近首页上半部分信息输出
	 * @param string $user_id 用户ID
	 * @return  json
	 * 说明：此接口会输出banner、分类、快报
	 * @zd
	 */
	public function index()
	{
		$ad = new Ad();

		// banner
		$data["position"] = "near_top_banner";
		$data["row"] = 3;
		$ad1 = $ad->adList($data);
		$ad1 = $ad1[2];

		$catelist = M('industry')->field('id,name,icon')->select();
		if (!$catelist)
		{
			$catelist = array();
		}

		$ad2 = array();    //广告列表

		$ad = new Ad();
		// 广告列表
		$data["position"] = "near_center";
		$data["row"] = 6;
		$ad3 = $ad->adList($data);
		$ad3 = $ad3[2];

		$result["ad1"] = full_pre_url($ad1, "photo");
		$catelist = full_pre_url($catelist, "icon");
		foreach ($catelist as $key => &$value)
		{
			$value["img"] = $value["icon"];
		}
		$result["catelist"] = $catelist;
		$result["ad2"] = $ad3;
		jsonout("首页上方信息", 0, $result);
	}

}