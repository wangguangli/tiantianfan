<?php
Vendor('Qiniu.autoload');

use \Qiniu\Auth;
use \Qiniu\Storage\UploadManager;


/**
 * 所有的函数写清楚标题，传入参数、返回参数、作者。若函数稍复杂 请做详细注释 !!!
 * 每写新函数，放在此页面上方，不要写在最下方，或随意找个位置。
 */

/**
 * 记录帐户变动  （注释示例）
 * @param int $user_id 用户id
 * @param string $desc 变动说明
 * @return  bool
 * @author  zd
 * 说明：（如果该函数功能、逻辑比较复杂，那么在此处做一下整个处理流程说明，让别人或自己以后可以看懂写的是什么）
 */


// ------------------以上说明---在下一行开始写函数---!!!!!!!!!!!!!!!!!!!!---------------------------------



/**
 * 获取自提点最近三天的营业时间
 * @param $data  自提点的信息
 * @param $time 当前时间
 * @param $array 需要返回的数据
 * @author lty
 */
function get_pick_date($data,$time,$array = array()){
    $pickWeek = explode(',',$data['work_day']);//可以营业的星期
    $nowWeek = date('w',$time);//今天是星期几
    $now = date('w',time());//今天是星期几
    $number = count($pickWeek);
    if(in_array($nowWeek,$pickWeek)){
        //查询时间范围 按照每小时进行分段
        $star_time = strtotime($data['work_time_start']);//开始时间
        $end_time = strtotime($data['work_time_end']);//开始时间
        //递归获取数据
        $date_time = date_time($star_time,$end_time,array());
        //判断今天是周几
        if($nowWeek == $now){
            $week = '(今天)';
        }else{
            if($nowWeek ==0){
                $week = '(周日)';
            }elseif($nowWeek == 1){
                $week = '(周一)';
            }elseif($nowWeek == 2){
                $week = '(周二)';
            }elseif($nowWeek == 3){
                $week = '(周三)';
            }elseif($nowWeek == 4){
                $week = '(周四)';
            }elseif($nowWeek == 5){
                $week = '(周五)';
            }elseif($nowWeek == 6){
                $week = '(周六)';
            }
        }

        $array[$nowWeek] = array(
            'picksite_id' => $data['id'],
            'data' => date('m-d',$time).$week,
            'date_time' => $date_time,//时间列表
        );
        $array = array_values($array);
    }

    
    if(count($array) <3){
        //在判断是否营业时间就小于3
        if(count($array) == $number){

            return $array;
        }
        //继续执行
        $time = $time + 86400;
        $array = get_pick_date($data,$time,$array);
        return $array;
    }else{
        return $array;
    }

}
/**
 * 获取自提点最近三天的营业时间
 * @param $star_time  开始营业时间
 * @param $end_time 结束营业时间
 * @param $array 需要返回的数据
 * @author lty
 */
function date_time($star_time,$end_time,$array = array()){
    $now_time = $star_time + 3600;
    if($now_time >= $end_time){
        //返回数据
        $array[$star_time] = date('H:i',$star_time).'~'.date('H:i',$end_time);
        $array = array_values($array);
        return $array;
    }else{
        //不大于  继续执行
        $array[$star_time] = date('H:i',$star_time).'~'.date('H:i',$now_time);
        $array =  date_time($now_time,$end_time,$array);
        $array = array_values($array);
        return $array;
    }
}




// 获取微信token
function get_access_token($weixin = false)
{
	if (!$weixin)
	{
		$weixin = M('config_pay3')->where("type=3")->find();
	}
	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$weixin['appid']}&secret={$weixin['key2']}";
	$req = httpRequest($url, 'GET');
	$arr = json_decode($req, 1);
	$web_expires = time() + 7000; // 提前200秒过期

	if ($arr['errcode'])
	{
		_error_log($arr['errcode'] . ':' . $arr['errmsg'] . '__________!!!_________');
	}
	$data = array(
		'web_access_token' => $arr['access_token'],
		'web_expires' => $web_expires
	);
	return $arr['access_token'];
}

/**
 * 获取用户头像
 * @param $user_id
 * @return mixed
 */
function get_user_headimg($user_id)
{
	$headImg = M('User')->where('id=' . $user_id)->getField('headimgurl');
	return $headImg;
}

// XML转换为数组
function xmlToArr($xml)
{
	libxml_disable_entity_loader(true);
	$arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	return $arr;
}

/*
 * 标记优惠券使用
 * $order_id 			订单ID2
 * @return  			array
 * @zd
 */
function coupon_sign($order_id)
{
	if (!$order_id)
	{
		return true;
	}
	$orderInfo = M("order")->where("id=" . $order_id)->find();
	if ($orderInfo['user_coupon_id'])
	{
		$where['id'] = $orderInfo['user_coupon_id'];
		$data['order_id'] = $order_id;
		$data['use_time'] = time();
		$data['use_date'] = date("Y-m-d H:i:s");
		M("coupon_user")->where($where)->save($data);
	}
	if ($orderInfo['user_coupon_id_auto'])
	{
		$where['id'] = $orderInfo['user_coupon_id_auto'];
		$data['order_id'] = $order_id;
		$data['use_time'] = time();
		$data['use_date'] = date("Y-m-d H:i:s");
		M("coupon_user")->where($where)->save($data);
	}
	return true;
}

/*
 * 获取优惠券信息
 * $id 					优惠券ID/用户领取表的ID
 * $type 				默认1 ID值是优惠表的ID，2用户领取表的ID
 * @return  			array
 * @zd
 */
function get_coupon_info($id, $type)
{
	if (!$id)
	{
		return array();
	}
	if ($type == 2)
	{
		$where = " cu.is_hide=0 and cu.id=" . $id;
		$where .= " and c.status=1 and c.is_del=0 ";

		$join = " left join __COUPON__ as c on cu.cid=c.id";
		$field = "cu.*,c.name,c.money,c.conditions,c.goods_id,c.shop_id,c.to_new_user,c.same_time,c.type";
		$data = M("coupon_user")->alias("cu")->join($join)->field($field)->where($where)->find();
	}
	else
	{
		$data = M("coupon")->where("id=" . $id)->find();
	}
	if (!$data)
	{
		$data = array();
	}
	if ($data['conditions'])
	{
		// 满减提示
		$data['conditions_cn'] = "满" . $data['conditions'] . "元可用";
	}
	if ($data['conditions'])
	{
		// 满减提示
		$data['conditions_cn'] = "满" . $data['conditions'] . "元可用";
	}
	$shopName = "全平台通用优惠券";
	$couponType = "平台券";
	$newUser = "";            // 是否仅新用户可用 -- 即以前未使用过优惠券的
	$effectiveTime = "";    // 有效时间
	if ($data['shop_id'])
	{
		$shopName = M("shop")->where("id=" . $data['shop_id'])->getField("shop_name");
		$shopName = $shopName ? $shopName : "平台商家";
		$shopName = "仅限" . $shopName . "使用";
		$couponType = "商家券";
	}
	if ($data['goods_id'])
	{
		$shopName = M("goods")->where("id=" . $data['goods_id'])->getField("name");
		$shopName = $shopName ? $shopName : "指定商品";
		$shopName = "仅限" . $shopName . "使用";
		$couponType = "商品券";
	}
	$data['shop_name'] = $shopName;
	$data['coupon_type'] = $couponType;
	if ($data['to_new_user'])
	{
		$newUser = "[新用户]";
	}
	$data['to_new_user_cn'] = $newUser;

	if ($data['start_type'] == 1)
	{
		$effectiveTime = "领取后{$data['use_days']}天有效期";
	}
	else
	{
		$effectiveTime = date("Y.m.d", $data['use_start_time']) . " - " . date("Y.m.d", $data['use_end_time']);
	}
	if ($data['use_start_time'])
	{
		$data['use_start_date'] = date("Y-m-d H:i:s", $data['use_start_time']);
	}
	if ($data['use_end_time'])
	{
		$data['use_end_date'] = date("Y-m-d H:i:s", $data['use_end_time']);
	}
	$data['effective_time'] = $effectiveTime;

	return $data;
}

/*
 * 检测商品（1或多个）的可用优惠券，有则输出优惠券列表  -- 优惠券与自动满减可同时使用
 * $user_id 			用户ID
 * $goodsArr 			商品信息，必须是数组 array( 'id'=>1, 'spec_id'=>'98', 'spec_key'=>'1155_1156', 'num'=>5)  这是一个商品，多个商品则是多个数组组合
 * @return  			array
 * @zd
 * 说明：
 *		1，计算每个商品购买的总价，以及所有商品 总额。
 * 		2，开始筛选优惠券 -- 用户领取过的：
 * 			A：时间范围、状态符合的
 * 			B：总额 <= 平台优惠券满减条件，
 * 				或 某商家的商品总额 <= 商家优惠券满减条件，
 * 				或 某商品总额 <= 对应商品优惠券满减条件
 * 		3：以及符合条件的、没有领取的，平台自动满减的优惠券
 *
 * 注：这个列表也可在订单入库前使用
 */
function check_goods_use_coupon($user_id, $goodsArr)
{
	if (!$goodsArr)
	{
		return array();
	}
	// 如果传入的不是数组，则转为数组再处理
	if (!is_array($goodsArr))
	{
		return array();
	}
	$total = 0;    // 所有商品总额
	$totalShop = array();    // 所有商家的商品总额 array( '8'=> array('shop_id'=>8, 'total'=> 1100) );
	$totalGoods = array();    // 所有商品 每个商品的总额  array( '8'=> array('goods_id'=>8, 'total'=> 1100) );

	$hot_id = 0;    // 默认0，如果是2，则是积分商品不可使用优惠券

	foreach ($goodsArr as $key => $value)
	{
		$shopMoney = 0;        // 单个商家的额度
		$goodsMoney = 0;    // 单个商品的额度
		if (!$value['id'])
		{
			continue;
		}
		$goodsInfo = M("goods")->where("id=" . $value['id'])->find();

		if (!$goodsInfo)
		{
			continue;
		}
		if ($goodsInfo['hot_id'] == 2)
		{
			// $hot_id = 2;
			// break;	// 积分商品直接跳出
		}
		if ($value['spec_key'] || $value['spec_id'])
		{
			// 有规格ID
			if ($value['spec_key'])
			{
				$where_key['goods_id'] = $value['id'];
				$where_key['key'] = $value['spec_key'];
			}
			if ($value['spec_id'])
			{
				$where_key['id'] = $value['spec_id'];
			}

			$specInfo = M("goods_spec_price")->where($where_key)->find();

			if (!$specInfo)
			{
				continue;
			}

			// 所有商品总金额处理
			$total = round($total + $specInfo['price'] * $value['num'], 2);

			// 商家金额数据处理
			$shopID = $goodsInfo['shop_id'];
			$goodsMoney = round($specInfo['price'] * $value['num'], 2);
			if ($totalShop[$shopID])
			{
				$shopMoney = round($totalShop[$shopID]['total'] + $goodsMoney, 2);
				$shopMoney = $shopMoney > 0 ? $shopMoney : 0;
				$totalShop[$shopID]['shop_id'] = $shopID;
				$totalShop[$shopID]['total'] = $shopMoney;
			}
			else
			{
				$totalShop[$shopID]['shop_id'] = $shopID;
				$totalShop[$shopID]['total'] = $shopMoney;
			}

			// 商品金额数据处理
			$goodsID = $goodsInfo['id'];
			if ($totalGoods[$goodsID])
			{
				$goodsMoney = round($totalGoods[$goodsID]['total'] + $goodsMoney, 2);
				$goodsMoney = $goodsMoney > 0 ? $goodsMoney : 0;
				$totalGoods[$goodsID]['goods_id'] = $goodsID;
				$totalGoods[$goodsID]['total'] = $goodsMoney;
			}
			else
			{
				$totalGoods[$goodsID]['goods_id'] = $goodsID;
				$totalGoods[$goodsID]['total'] = $goodsMoney;
			}

		}
		else
		{
			// 没有规格ID

			// 所有商品总金额处理
			$total = round($total + $goodsInfo['price'] * $value['num'], 2);

			// 商家金额数据处理
			$shopID = $goodsInfo['shop_id'];
			$goodsMoney = round($goodsInfo['price'] * $value['num'], 2);
			if ($totalShop[$shopID])
			{
				$shopMoney = round($totalShop[$shopID]['total'] + $goodsMoney, 2);
				$shopMoney = $shopMoney > 0 ? $shopMoney : 0;
				$totalShop[$shopID]['shop_id'] = $shopID;
				$totalShop[$shopID]['total'] = $shopMoney;
			}
			else
			{
				$totalShop[$shopID]['shop_id'] = $shopID;
				$totalShop[$shopID]['total'] = $shopMoney;
			}

			// 商品金额数据处理
			$goodsID = $goodsInfo['id'];
			if ($totalGoods[$goodsID])
			{
				$goodsMoney = round($totalGoods[$goodsID]['total'] + $goodsMoney, 2);
				$goodsMoney = $goodsMoney > 0 ? $goodsMoney : 0;
				$totalGoods[$goodsID]['goods_id'] = $goodsID;
				$totalGoods[$goodsID]['total'] = $goodsMoney;
			}
			else
			{
				$totalGoods[$goodsID]['goods_id'] = $goodsID;
				$totalGoods[$goodsID]['total'] = $goodsMoney;
			}
		}
	}

	// 优惠券筛选处理
	$couponArr = array();    // 可用优惠券列表
	$couponAuto = array();
	if ($hot_id == 2)
	{
		// $coupon['list'] = $couponArr;
		// $coupon['auto'] = $couponAuto;
		// return $coupon;
	}


	// 根据商家循环
	$list = array();

	foreach ($totalShop as $key2 => $value2)
	{
		$where = " cu.is_hide=0 and cu.order_id = 0 and cu.user_id=" . $user_id;
		$where .= " and cu.use_start_time<=" . time() . " and cu.use_end_time>" . time();
		$where .= " and c.status=1 and c.is_del=0 ";
		$where .= " and ( c.conditions<={$total} or  ( c.conditions <= {$value2['total']} and c.shop_id={$value2['shop_id']} )  )";

		$join = " left join __COUPON__ as c on cu.cid=c.id";
		$field = "cu.*,c.name,c.money,c.conditions,c.goods_id,c.shop_id,c.to_new_user,c.same_time,c.type";
		$list = M("coupon_user")->alias("cu")->join($join)->field($field)->where($where)->order("cu.id desc")->select();
	}

	// 返回当前可用的优惠券
	$couponArr = array_merge($couponArr, $list);

	// 根据商品循环
	$list = array();
	foreach ($totalGoods as $key3 => $value3)
	{
		$where = " cu.is_hide=0 and cu.order_id = 0 and cu.user_id=" . $user_id;
		$where .= " and cu.use_start_time<=" . time() . " and cu.use_end_time>" . time();
		$where .= " and c.status=1 and c	.is_del=0 ";
		$where .= " and ( c.conditions<={$total} or  ( c.conditions <= {$value3['total']} and c.goods_id={$value3['goods_id']} )  )";
		$join = " left join __COUPON__ as c on cu.cid=c.id";
		$field = "cu.*,c.name,c.money,c.conditions,c.goods_id,c.shop_id,c.to_new_user,c.same_time,c.type";
		$list = M("coupon_user")->alias("cu")->join($join)->field($field)->where($where)->order("cu.id desc")->select();
	}

	$couponArr = array_merge($couponArr, $list);

	/**
	 * code401 经过array_unique处理之后只输出一个优惠券 应该是有两个才对
	 */
	$couponArr = array_unique($couponArr, SORT_REGULAR);

	foreach ($couponArr as $key4 => $value4)
	{
		// 自动满减，放在新数组，并且到最后取一个最大值 -- 自动为用户使用最大值
		if ($value4['type'] == 2 || $value4['type'] == 4)
		{
			$money = intval($value4['money']);
			$couponAuto[$money] = $value4;
			unset($couponArr[$key4]);
		}
	}
	if ($couponAuto)
	{
		krsort($couponAuto);    // 排序，最大额度在最前
	}
	$where_ord['user_id'] = $user_id;
	$where_ord['isdel'] = 0;
	$where_ord['order_type'] = array("in", array(1, 6, 7));
	$where_ord['order_status'] = array("in", array(1, 2, 3, 5));
	$isHaveOrder = M("order")->where($where_ord)->count();

	foreach ($couponArr as $key5 => $value5)
	{
		if ($isHaveOrder && $value5['to_new_user'])
		{
			unset($couponArr[$key5]);
			continue;
		}
		$info = get_coupon_info($value5['id'], 2);
		$couponArr[$key5] = $info;
	}

	foreach ($couponAuto as $key6 => $value6)
	{
		if ($isHaveOrder && $value6['to_new_user'])
		{
			unset($couponAuto[$key6]);
			continue;
		}
		$info = get_coupon_info($value6['id'], 2);
		$couponAuto[$key6] = $info;
	}
	// 先把自动满减处理
	$couponAuto = array_values($couponAuto);

	if ($couponAuto[0] && $couponAuto[0]['money'] > 0)
	{
		// 如果第一个自动满减有值，则把领取的优惠券，重新过滤一遍
		foreach ($couponArr as $key6 => $value6)
		{
			// $total
			if (round($value6['money'] + $couponAuto[0]['money'], 2) > $total)
			{
				unset($couponArr[$key6]);
				continue;
			}
		}
	}
	// 处理后的优惠券
	$couponArr = array_values($couponArr);

	$ucid = array();

	foreach ($couponArr as $key7 => $value7)
	{
		$ucid[] = $value7['id'];
	}
	foreach ($couponAuto as $key8 => $value8)
	{
		$ucid[] = $value8['id'];
	}

	$coupon['list'] = $couponArr;
	$coupon['auto'] = $couponAuto;
	$coupon['ucid'] = $ucid;
	return $coupon;
}

/*
 * 获取周几
 * 如果只传入一个值，那么返回相应的周几
 * 如果传入一个数组或逗号分隔的字符串，则输出逗号分隔的具体的周几
 */
function get_week_day($day)
{
	$str = "";
	$weekArr = array('周天', '周一', '周二', '周三', '周四', '周五', '周六');
	$day = explode(",", $day);
	foreach ($day as $val)
	{
		$str = $str . $weekArr[$val] . ",";
	}
	$str = trim($str, ",");
	return $str;
}

// 获取所有上级
function get_parent($user_id)
{
	$parents = array();
	$field = "id,phone,level,first_leader,second_leader,third_leader,status,is_del";
	$uinfo = M("user")->where("id=" . $user_id)->field($field)->find();
	if (!$uinfo)
	{
		return true;
	}
	$list = M("user")->where("id=" . $uinfo['first_leader'])->field($field)->select();
	if ($list)
	{
		foreach ($list as $item)
		{
			$parents[] = $item;
			$parents = array_merge($parents, get_parent($item['id']));
		}
	}
	return $parents;

}

/**
 * 判断城市是否在某个运费规则内
 * 如果在，输出属于哪个条目
 * 如果不在，则输出提示
 * goods_id        商品ID
 * city_id            城市ID
 * @return        array
 * @zd
 */
function check_express($goods_id, $city_id)
{
	if (!$city_id || !$goods_id)
	{
		return array("符合结果", 0, 0);
	}
	$goodsInfo = M("goods")->where("id=" . $goods_id)->field("shipping_type,shipping_rule_id")->find();
	if (!$goodsInfo)
	{
		return array("商品无可用运费规则，请联系客服", 1, 0);
	}
	if ($goodsInfo["shipping_type"] == 2)
	{
		return array("自提商品，不计运费", 0, 0);
	}
	$ruleInfo = M("express_rule")->where("id=" . $goodsInfo["shipping_rule_id"])->find();
	if (!$ruleInfo || !$goodsInfo["shipping_rule_id"])
	{
		return array("商品无可用运费规则，请联系客服", 1, 0);
	}

	if (empty($city_id))
	{
		$sql = "select id,find_in_set(0, area) as num  FROM " . C('DB_PREFIX') . "express_rule where parent_id=" . $ruleInfo["parent_id"];
	}
	else
	{
		$sql = "select id,find_in_set(" . $city_id . ", area) as num  FROM " . C('DB_PREFIX') . "express_rule where parent_id=" . $ruleInfo["parent_id"];
	}

	$rs = M()->query($sql);
	$rule = 0;
	foreach ($rs as $key => $value)
	{
		if ($value["num"] > 0)
		{
			$rule = $value["id"];
			continue;
		}
	}
	if ($rule)
	{
		return array("符合结果", 0, $rule);
	}
	else
	{
		return array("商品无可用运费规则，请联系客服.", 1, 0);
	}

}


/**
 * 根据商品信息获取相应的快递费用
 * goods_id        商品ID
 * goods_num        商品数量
 * goods_spec_price_id    商品规格表ID
 * city_id            城市ID
 * @return    返回信息，下标0：提示，下标1：0正常，1异常，下标2：快递费用
 * @zd
 */
function goods_express_fee($goods_id = 0, $goods_num = 1, $goods_spec_price_id = 0, $city_id = 0)
{
	$fee = 0;
	if (!$goods_id)
	{
		return $fee;
	}
	$chkArr = check_express($goods_id, $city_id);
	if ($chkArr[1] == 1)
	{
		return $fee;    // 如果检测异常，则直接输出金额为0
	}

	$ruleInfo = M("express_rule")->where("id=" . $chkArr[2])->find();

	// 目前只计算规格商品的运费
	// 计算：
	// 1，先判断是按件计费，还是按重量计算
	// 2，按件计的话，商品数组中已经有数量，并且此前已得到运费规则，根据规则计算
	// 3，按重量的话，先查一下规格商品的重量，然后乘数量，就是总的重量，然后根据规则计算
	if ($ruleInfo["type"] == 2)
	{
		// 按件
		$fee = round($ruleInfo["first_price"], 2);        // 首先符合首件规则
		$diff = $goods_num - $ruleInfo["the_first"];        // 购买数量-规则数量，如果正数，表示还有没参与计算的，如果小于就不用再计算了
		if ($diff > 0)
		{
			// 继续计算
			$rate = ceil($diff / $ruleInfo["the_second"]);        // 整数倍，进一
			$fee = round($fee + $rate * $ruleInfo["second_price"], 2);
		}
		return $fee;
	}
	elseif ($ruleInfo["type"] == 1)
	{
		// 按重量
		$fee = round($ruleInfo["first_price"], 2);        // 首先符合首重规则
		if ($goods_spec_price_id)
		{
			$specInfo = M("goods_spec_price")->where("id=" . $goods_spec_price_id)->find();
			if ($specInfo)
			{
				// 商品重量(数量*单重)-规则首重，如果正数，表示还有没参与计算的，如果小于就不用再计算了
				$diff = $specInfo["weight"] * $goods_num - $ruleInfo["the_first"];
				if ($diff > 0)
				{
					// 继续计算
					$rate = ceil($diff / $ruleInfo["the_second"]);        // 整数倍，进一
					$fee = round($fee + $rate * $ruleInfo["second_price"], 2);
				}
			}
		}
		return $fee;
	}
	else
	{
		return $fee;    // 不属于任何
	}

}


/**
 * 支付方式转中文
 * @zd
 */
function payment_type_cn($payment_type)
{
	switch ($payment_type)
	{
		case 'yepay':
			$str = "佣金支付";
			break;
		case 'wxpay':
			$str = "微信APP";
			break;
		case 'wxmp':
			$str = "微信公众号";
			break;
		case 'wxmini':
			$str = "微信小程序";
			break;
		case 'wxh5':
			$str = "微信H5";
			break;
		case 'native':
			$str = "微信扫一扫";
			break;
		case 'qrcode':
			$str = "微信付款码";
			break;
		case 'alipay':
			$str = "支付宝APP";
			break;
		case 'aliwap':
			$str = "支付宝WAP";
			break;
		case 'alipc':
			$str = "支付宝PC";
			break;
		case 'alimini':
			$str = "支付宝小程序";
			break;
		case 'adminpay':
			$str = "后台支付";
			break;
		case 'wait_pay':
			$str = "积分支付";
			break;
        case 'price':
            $str = "已返支付";
            break;
        case 'price_pay':
            $str = "金额支付";
            break;
		default:
			$str = "";
			break;
	}
	return $str;
}


/**
 * 多维改为二维
 * @param array $arrTmp 数组
 * @param array $ret 返回数组
 * @return  array
 * @zd
 */
function reformat($arrTmp, &$ret = null)
{
	foreach ($arrTmp as $k => $v)
	{
		$ret[] = $v;

		if ($v['childs'])
		{
			reformat($v['childs'], $ret);
		}
	}
	return $ret;
}

/**
 * 获取商品分类ID下的所有子分类，不包括本级分类
 * @param int $cat_id 订单ID
 * @return  array
 * @zd
 */
function get_childs($cat_id)
{
	$childs = array();
	$list = M("goods_category")->where("pid=" . $cat_id)->select();
	foreach ($list as $item)
	{
		$item['childs'] = get_childs($item['id']);
		$childs[] = $item;
	}
	return $childs;
}


/**
 * 微信、支付宝直接扫二维码买单，通过注册或登录，用户才能提成
 * @param int $order_id 订单ID
 * @return  bool
 * @zd
 */
function order_qr_user($logid, $order_id, $user_id)
{
	$where_log['id'] = $logid;
	$logInfo = M("pay3_log")->where($where_log)->find();
	if (!$logInfo || $logInfo['is_get'] == 1)
	{
		return false;
	}
	$value = M("order")->where("id=" . $order_id)->find();
	if (intval($value["order_status"]) < 1)
	{
		return false;    // 非支付订单 不能处理
	}

	/**
	 * 开始处理主要逻辑，一般这里的逻辑根据项目的不同来处理
	 * 1，每个商家有不同的服务费比例，根据服务费 处理商家应得的营业额和账户积分
	 * 2，平台得相应比例的服务费
	 *
	 * 3，消费者根据商家所得的账户积分 获取一定比例的账户积分
	 *
	 * 4，消费者的上一级得消费者此次账户积分的 x   --- 创业会员
	 * 5，消费者的上二级得消费者此次账户积分的 x   --- 创业会员
	 *
	 * 6，商家的上一级得商家此次账户积分的 x       --- 创业会员
	 * 7，商家的上二级得商家此次账户积分的 x       --- 创业会员
	 *
	 * 8，商家所属区域的代理，得商家营业额一定比例 到代理账户积分
	 *
	 */

	$shop_amount = 0;    // 商家营业额度
	$shop_money_wait = 0;    // 商家账户积分
	$shop_comm_1 = 0;    // 商家一级提成
	$shop_comm_2 = 0;    // 商家二级提成

	$user_money_wait = 0;    // 用户账户积分
	$user_comm_1 = 0;    // 用户一级提成
	$user_comm_2 = 0;    // 用户二级提成
	$district_comm = 0;    // 县级代理提成
	$city_comm = 0;    // 市级代理提成

	if ($value['shop_id'] > 0 && $value['order_type'] == 1 && $value["total_commodity_price"] > 0)
	{
		// 商家信息，商家可能会没有，没有的话，营业额度肯定不再计入，但是 其他用户提成要计算
		$where_shop["user_id"] = $value['shop_id'];
		$shopInfo = get_user_info($where_shop, '', '', 2);

		$where_shop_user["id"] = $value['shop_id'];
		$shopUserInfo = get_user_info($where_shop_user, '', '', 1);

		if (!$shopInfo)
		{
			$shopFee = M("config_shop_fee")->order("id desc")->find();
		}
		else
		{
			$shopFee = get_config_shop_fee($shopInfo["shop_fee_id"]);
		}

		// 账户积分
		$shop_money_wait = round($value['total_commodity_price'] * 100, 2);    // 总额*现金积分比值100

		// 用户得账户积分
		$value['user_id'] = $user_id;
		$where_user["id"] = $value["user_id"];
		$userInfo = get_user_info($where_user);
		if ($shop_money_wait > 0 && $userInfo['status'] == 1)
		{
			// 正常状态、用户
			$user_money_wait = round($shop_money_wait * $shopFee['percent2'] / 100, 2);
			if ($user_money_wait > 0)
			{
				change_money_log($value['user_id'], 1, "score", 0, $user_money_wait, 16, 2, "在商家买单增加账户积分", 1, 1, 1, $value['id'], $value['shop_id']);
			}
		}

		// 用户的上一级得账户积分
		if ($user_money_wait > 0 && $userInfo["first_leader"] > 0)
		{
			$where_user_first["id"] = $userInfo["first_leader"];
			$userFirstInfo = get_user_info($where_user_first);
			if ($userFirstInfo && $userFirstInfo['status'] == 1 && $userFirstInfo['level'] == 1)
			{
				// 正常状态、创业会员
				$user_comm_1 = get_config_fx("user_comm_1");
				$user_comm_1 = round($user_comm_1 / 100 * $user_money_wait, 2);    // 比例*用户的账户积分
				if ($user_comm_1 > 0)
				{
					change_money_log($userInfo['first_leader'], 1, "score", 0, $user_comm_1, 16, 10, "提成下级用户的账户积分", 1, 1, 1, $value['id'], $userInfo['id']);
				}
			}
		}
		// 用户的上二级得账户积分
		if ($user_money_wait > 0 && $userInfo["second_leader"] > 0)
		{
			$where_user_second["id"] = $userInfo["second_leader"];
			$userSecondInfo = get_user_info($where_user_second);
			if ($userSecondInfo && $userSecondInfo['status'] == 1 && $userSecondInfo['level'] == 1)
			{
				// 正常状态、创业会员
				$user_comm_2 = get_config_fx("user_comm_2");
				$user_comm_2 = round($user_comm_2 / 100 * $user_money_wait, 2);    // 比例*用户的账户积分
				if ($user_comm_2 > 0)
				{
					change_money_log($userInfo['second_leader'], 1, "score", 0, $user_comm_2, 16, 10, "提成下下级用户的账户积分", 1, 1, 1, $value['id'], $userInfo['id']);
				}
			}
		}

		$data_log['is_get'] = 1;
		$data_log['user_id'] = $user_id;
		M("pay3_log")->where("id=" . $logid)->save($data_log);

		$data_ord['user_id'] = $user_id;
		M("order")->where("id=" . $logInfo['order_id'])->save($data_ord);

	}

}

/**
 * 关于订单支付(收货)后一些主要逻辑
 * @param int $order_id 订单ID
 * @return  bool
 * @zd
 */
function order_logic($order_id = 0)
{
	if (!$order_id)
	{
		return false;
	}
	$orderInfo = M("order")->where("id=" . $order_id)->field("id,order_type,user_id,order_status,profit_space,is_profit_1,is_profit_2,is_profit_3")->find();
	if (!$orderInfo)
	{
		return false;
	}
	// 订单分类处理，并且还要看提成等收益是否符合后台的设置
	// 订单类型，0商城订单，1用户买单，2商家报单，3充值订单，4代理报单，5团购订单，6整点秒杀，7限时购，8砍价订单，9预售订单，10升级订单
	if ($orderInfo["order_type"] == 0)
	{

	    //判断什么时候可以进行返利

        $profit_space = get_config_project('profit_space');


        if($orderInfo['is_profit'] == 0){
            //与平常项目不同 使用新的返利模式

            if ($profit_space == 1 && $orderInfo["order_status"] != 1)
            {
                return true;
            }
            if ($profit_space == 2 && $orderInfo["order_status"] < 3)
            {
                return true;
            }
            // 收货7天后收益
            if ($profit_space == 3 && (($orderInfo["order_status"] != 3 && $orderInfo["order_status"] != 5) || $orderInfo["confirm_time"] + 86400 * 7 > time()))
            {
                return true;
            }

            if ($profit_space == 4 && ($orderInfo["order_status"] != 2 || $orderInfo["shipping_time"] + 86400 * 7 > time()))
            {
                return false;
            }
            //下拉一单 判断上级是不是可以升级
            order_type0($order_id);
            first_level($orderInfo['user_id']);
            up_level_by_auto($orderInfo['user_id']);
        }


        //用户升级方法
	    
		// 2020.07 新增了预分销功能，所以此处的用户、商家、代理提成 不再直接计算了
		// 而是从预分销表读取。
		// 等级的提升，还是按原来的走
		// order_goods_award($order_id);   // 后台设置的消费返佣逻辑，自身+上级分成
		// order_profit_t0($order_id);     // 商家和代理等用户得到的分成
//		order_goods_award_by_distribution_log($orderInfo);

	}
	elseif ($orderInfo["order_type"] == 1)
	{
//		order_profit_t1($order_id);
	}
	elseif ($orderInfo["order_type"] == 2)
	{
//		order_profit_t2($order_id);
	}
	elseif ($orderInfo["order_type"] == 3)
	{
		 order_profit_t3($order_id);//充值
	}
	elseif ($orderInfo["order_type"] == 4)
	{
		// order_profit_t4($order_id);      // 不确定，暂停注释，不再使用
	}
	elseif ($orderInfo["order_type"] == 5)
	{
		// order_profit_t5($order_id);      // 暂时没有逻辑
	}
	elseif ($orderInfo["order_type"] == 6)
	{
		// order_profit_t6($order_id);      // 暂时没有逻辑
	}
	elseif ($orderInfo["order_type"] == 7)
	{
		// order_profit_t7($order_id);      // 暂时没有逻辑
	}
	elseif ($orderInfo["order_type"] == 8)
	{
		// order_profit_t8($order_id);      // 暂时没有逻辑
	}
	elseif ($orderInfo["order_type"] == 9)
	{
		// order_profit_t9($order_id);      // 暂时没有逻辑
	}
	elseif ($orderInfo["order_type"] == 10)
	{
		// 升级订单
        up_level_by_money($orderInfo['user_id'], $orderInfo['id']);
	}elseif ($orderInfo["order_type"] == 12)
    {
        // 升级订单
        up_level_by_money($orderInfo['user_id'], $orderInfo['id']);
    }


}

function order_profit_t3($order_id){

    $orderInfo = M('order')->find($order_id);
    if($orderInfo['is_profit']== 1){
        return true;
    }
    $userInfo = M('user')->find($orderInfo['user_id']);
    change_money_log($orderInfo['user_id'],1,'price_pay',$userInfo['price_pay'],$orderInfo['total_commodity_price'],19,1,'余额充值',1,1,1,$order_id,$orderInfo['id'],0);

    M('order')->where('id='.$order_id)->setField('is_profit',1);
}

/**
 * 商城订单收益处理（商家和代理等用户得到的分成）
 * @param int $order_id 订单ID
 * @return  bool
 * @zd
 */
function order_profit_t0($order_id)
{
	$orderInfo = M("order")->where("id=" . $order_id)->find();
	if (!$orderInfo || $orderInfo['isdel'] == 1 || $orderInfo['order_status'] == 0 || $orderInfo['order_status'] == 4 || $orderInfo['order_status'] < 1)
	{
		return true;
	}

	$profit_space = $orderInfo['profit_space'];

	// 因为把 $profit_space 写入订单表了，所以此值不再需要获取了，而是取订单中的值
	// $profit_space = get_config_project("profit_space");    // 提成等收益处理（1支付后立即收益，2表示收货后，3表示收货7天后，4表示发货7天后）

	if (!in_array($profit_space, array(1, 2, 3, 4)))
	{
		$profit_space = 1;
	}
	if (intval($orderInfo["order_status"]) < 1)
	{
		return false;    // 非支付订单 不能处理
	}
	if ($orderInfo['is_profit_2'] == 1 && $orderInfo['is_profit_3'] == 1)
	{
		return false;    // 已提成，不再提成
	}
	if ($profit_space == 1 && $orderInfo["order_status"] != 1)
	{
		return false;
	}
	if ($profit_space == 2 && $orderInfo["order_status"] < 3)
	{
		return false;
	}
	// 收货7天后收益
	if ($profit_space == 3 && (($orderInfo["order_status"] != 3 && $orderInfo["order_status"] != 5) || $orderInfo["confirm_time"] + 86400 * 7 > time()))
	{
		return false;
	}
	// 发货7天后收益
	if ($profit_space == 4 && ($orderInfo["order_status"] != 2 || $orderInfo["shipping_time"] + 86400 * 7 > time()))
	{
		return false;
	}
	/**
	 * ---2019.12.17-单独处理商家和代理的逻辑---
	 * 1，商家得相应的营业额
	 * 2，代理根据比例拿提成
	 *
	 */
	$orderGoods = M("order_goods")->where("order_id=" . $order_id)->select();
	$spArr = array();    // 商家和平台组合的数组
	foreach ($orderGoods as $key => $value)
	{
		$shop_amount = 0;    // 商家营业额度
		$district_comm = 0;    // 县级代理提成
		if ($value['shop_id'] > 0)
		{
			// 商家信息，商家可能会没有，没有的话，营业额度肯定不再计入，但是 其他用户提成要计算
			$where_shop["id"] = $value['shop_id'];
			$shopInfo = get_user_info($where_shop, '', '', 2);

			$where_shop_user["user_id"] = $shopInfo['user_id'];
			$shopUserInfo = get_user_info($where_shop_user, '', '', 1);

			if (!$shopInfo)
			{
				$shopFee = M("config_shop_fee")->order("id desc")->find();
			}
			else
			{
				$shopFee = get_config_shop_fee($shopInfo["shop_fee_id"]);
			}
		}
		else
		{
			$shopFee = M("config_shop_fee")->order("id desc")->find();
		}

		if ($value['shop_id'] > 0)
		{
			// 商家增加营业额
			$shop_amount = round($value['goods_total'] * (100 - $shopFee['percent1']) / 100, 2);
			if ($shop_amount > 0 && $shopInfo["status"] == 1 && $orderInfo['is_profit_2'] == 0)
			{
				$res_sam = change_money_log($shopInfo['user_id'], 1, "shop_money", 0, $shop_amount, 2, 2, "用户消费店铺增加营业额", 1, 2, 1, $value['id'], $value['user_id']);
				if ($res_sam)
				{
					updateSonPorfit($order_id, $value['id'], 2);
				}
			}

			// 县级代理提成
			if ($shopInfo["district_id"] > 0 && $orderInfo['is_profit_3'] == 0)
			{
				$where_dist["isdel"] = 0;
				$where_dist["area_id"] = $shopInfo["district_id"];
				$distInfo = get_user_info($where_dist, '', '', 3);
				if ($distInfo && $distInfo['status'] == 1)
				{
					// 正常状态、代理
					$district_comm = get_config_fx("district_comm");
					$district_comm = round($district_comm / 100 * $value['goods_total'], 2);    // 比例*商家营业额度*放大100倍
					if ($district_comm > 0)
					{
						$res_dam = change_money_log($distInfo['user_id'], 1, "agent_money", 0, $district_comm, 3, 10, "区域服务奖励", 1, 3, 1, $value['id'], $shopInfo['user_id']);
					}
				}
			}

		}
		else
		{
			_error_log("-----noshop----");
			_error_log("-----user_id----" . $value["user_id"]);
		}
		updateSonPorfit($order_id, $value['id'], 2);
		updateSonPorfit($order_id, $value['id'], 3);
	}

}

/**
 * 用户买单订单收益处理
 * @param int $order_id 订单ID
 * @return  bool
 * @zd
 */
function order_profit_t1($order_id)
{
	$value = M("order")->where("id=" . $order_id)->find();
	if (!in_array($value["order_status"], array(1, 2, 3, 5)))
	{
		return false;    // 非支付订单 不能处理
	}

	/**
	 * 开始处理主要逻辑，一般这里的逻辑根据项目的不同来处理
	 * 1，每个商家有不同的服务费比例，根据服务费 处理商家应得的营业额
	 * 2，商家对应的代理得提成
	 * 3，平台得相应比例的服务费
	 *
	 */
	if ($value['order_type'] != 1)
	{
		return false;
	}

	if ($value['shop_id'] > 0)
	{
		// 商家信息，商家可能会没有，没有的话，营业额度肯定不再计入，但是 其他用户提成要计算
		$where_shop["id"] = $value['shop_id'];
		$shopInfo = get_user_info($where_shop, '', '', 2);
		if (!$shopInfo)
		{
			$shopFee = M("config_shop_fee")->order("id desc")->find();
		}
		else
		{
			$shopFee = get_config_shop_fee($shopInfo["shop_fee_id"]);
		}

		// 商家增加营业额
		$shop_amount = round($value['total_commodity_price'] * (100 - $shopFee['percent1']) / 100, 2);
		if ($shop_amount > 0 && $shopInfo["status"] == 1 && $value['is_profit_2'] == 0)
		{
			change_money_log($shopInfo['user_id'], 1, "shop_money", 0, $shop_amount, 2, 2, "用户买单店铺增加营业额", 1, 2, 1, $value['id'], $value['user_id']);
		}

		// 县级代理提成
		if ($shop_amount > 0 && $shopInfo["district_id"] > 0)
		{
			$where_dist["isdel"] = 0;
			$where_dist["area_id"] = $shopInfo["district_id"];
			$distInfo = get_user_info($where_dist, '', '', 3);
			if ($distInfo && $distInfo['status'] == 1 && $value['is_profit_3'] == 0)
			{
				// 正常状态、代理
				$district_comm = get_config_fx("district_comm");
				$district_comm = round($district_comm / 100 * $shop_amount, 2);    // 比例*商家营业额度*放大100倍
				if ($district_comm > 0)
				{
					change_money_log($distInfo['user_id'], 1, "agent_money", 0, $district_comm, 9, 10, "获得区域商家收入的提成(县级)", 1, 3, 1, $value['id'], $shopInfo['user_id']);
				}
			}
		}

		// 平台手续费
		$plat_amount = round($value['total_commodity_price'] * $shopFee['percent1'] / 100, 2);
		if ($plat_amount > 0)
		{
			change_money_log(0, 0, "money", 0, $plat_amount, 11, 3, "用户买单平台增加手续费", 1, 4, 0, $value['id'], $value['user_id']);
		}

	}

}


/**
 * 商家报单订单收益处理
 * @param int $order_id 订单ID
 * @return  bool
 * @zd
 */
function order_profit_t2($order_id)
{
	$value = M("order")->where("id=" . $order_id)->find();
	if (!in_array($value["order_status"], array(1, 2, 3, 5)))
	{
		return false;    // 非支付订单 不能处理
	}

	/**
	 * 开始处理主要逻辑，一般这里的逻辑根据项目的不同来处理
	 * 1，每个商家有不同的服务费比例
	 * 2，平台得相应比例的服务费
	 * 3，商家所属区域的代理，得商家营业额一定比例 到代理账户
	 *
	 */

	if ($value['shop_id'] > 0 && $value['order_type'] == 2)
	{
		// 商家信息，商家可能会没有，没有的话，营业额度肯定不再计入，但是 其他用户提成要计算
		$where_shop["id"] = $value['shop_id'];
		$shopInfo = get_user_info($where_shop, '', '', 2);

		if (!$shopInfo)
		{
			$shopFee = M("config_shop_fee")->order("id desc")->find();
		}
		else
		{
			$shopFee = get_config_shop_fee($shopInfo["shop_fee_id"]);
		}

		// 平台手续费
		$plat_amount = round($value['total_commodity_price'], 2);
		if ($plat_amount > 0)
		{
			change_money_log(0, 0, "money", 0, $plat_amount, 11, 3, "商家报单平台增加手续费", 1, 4, 0, $value['id'], $value['shop_id']);
		}

		$shop_amount = floatval($value['shop_note']);     // 商家报单总额度

		// 县级代理提成
		if ($shop_amount > 0 && $shopInfo["district_id"] > 0 && $value['is_profit_3'] == 0)
		{
			$where_dist["isdel"] = 0;
			$where_dist["area_id"] = $shopInfo["district_id"];
			$distInfo = get_user_info($where_dist, '', '', 3);
			if ($distInfo && $distInfo['status'] == 1)
			{
				// 正常状态、代理
				$district_comm = get_config_fx("district_comm");
				$district_comm = round($district_comm / 100 * $shop_amount, 2);    // 比例*商家营业额度
				if ($district_comm > 0)
				{
					change_money_log($distInfo['user_id'], 1, "agent_money", 0, $district_comm, 9, 10, "获得区域商家收入的提成(县级)", 1, 3, 1, $value['id'], $shopInfo['user_id']);
				}
			}
		}

	}

}


/**
 * 代理报单订单收益处理
 * @param int $order_id 订单ID
 * @return  bool
 * @zd
 */
function order_profit_t4($order_id)
{
	$value = M("order")->where("id=" . $order_id)->find();
	if (!in_array($value["order_status"], array(1, 2, 3, 5)))
	{
		return false;    // 非支付订单 不能处理
	}

	/**
	 * 开始处理主要逻辑，一般这里的逻辑根据项目的不同来处理
	 * 1，每个代理有不同的服务费比例，根据服务费 处理代理应得的账户积分。
	 * 2，平台得相应比例的服务费
	 *
	 * 3，消费者根据代理所得的账户积分 获取一定比例的账户积分
	 *
	 * 4，消费者的上一级得消费者此次账户积分的 x   --- 创业会员
	 * 5，消费者的上二级得消费者此次账户积分的 x   --- 创业会员
	 *
	 *
	 */

	$shop_amount = 0;    // 商家营业额度
	$shop_money_wait = 0;    // 商家账户积分
	$shop_comm_1 = 0;    // 商家一级提成
	$shop_comm_2 = 0;    // 商家二级提成

	$user_money_wait = 0;    // 用户账户积分
	$user_comm_1 = 0;    // 用户一级提成
	$user_comm_2 = 0;    // 用户二级提成
	$district_comm = 0;    // 县级代理提成
	$city_comm = 0;    // 市级代理提成

	if ($value['shop_id'] > 0 && $value['order_type'] == 4)
	{
		// 代理信息，代理可能会没有，没有的话，营业额度肯定不再计入，但是 其他用户提成要计算
		$where_shop["id"] = $value['shop_id'];
		$shopInfo = get_user_info($where_shop, '', '', 3);

		$where_shop_user["id"] = $value['shop_id'];
		$shopUserInfo = get_user_info($where_shop_user, '', '', 1);

		if (!$shopInfo)
		{
			$shopFee = M("config_shop_fee")->order("id desc")->find();
		}
		else
		{
			$shopFee = get_config_shop_fee($shopInfo["shop_fee_id"]);
		}

		$total = floatval($value['shop_note']);        // 用户消费总额
		$shop_amount = round($total - $value['total_commodity_price'], 2);    // 营业额，不加入代理账户


		// 代理账户积分
		$shop_money_wait = round($total * $shopFee['percent3'] / 100 * 100, 2);    // 总额*代理返还比例*现金积分比值100
		if ($shop_money_wait > 0 && $shopInfo["status"] == 1)
		{
			// change_money_log($value['shop_id'], 1, "money_wait", 0, $shop_money_wait, 8, 2, "代理报单增加账户积分", 1, 2, 1, $value['id'], $value['user_id']);
		}

		// 订单金额对应总积分
		$user_total = round($total * 100, 2);

		// 用户得账户积分
		$where_user["id"] = $value["user_id"];
		$userInfo = get_user_info($where_user);
		if ($user_total > 0 && $userInfo['status'] == 1)
		{
			// 正常状态、用户
			$user_money_wait = round($user_total * $shopFee['percent2'] / 100, 2);
			if ($user_money_wait > 0)
			{
				// change_money_log($value['user_id'], 1, "money_wait", 0, $user_money_wait, 6, 2, "代理报单增加账户积分", 1, 1, 1, $value['id'], $value['shop_id']);
			}
		}

		// 用户的上一级得账户积分
		if ($user_money_wait > 0 && $userInfo["first_leader"] > 0)
		{
			$where_user_first["id"] = $userInfo["first_leader"];
			$userFirstInfo = get_user_info($where_user_first);
			if ($userFirstInfo && $userFirstInfo['status'] == 1 && $userFirstInfo['level'] == 1)
			{
				// 正常状态、创业会员
				$user_comm_1 = get_config_fx("user_comm_1");
				$user_comm_1 = round($user_comm_1 / 100 * $user_money_wait, 2);    // 比例*用户的账户积分
				if ($user_comm_1 > 0)
				{
					// change_money_log($userInfo['first_leader'], 1, "money_wait", 0, $user_comm_1, 6, 10, "提成下级用户的账户积分", 1, 1, 1, $value['id'], $userInfo['id']);
				}
			}
		}
		// 用户的上二级得账户积分
		if ($user_money_wait > 0 && $userInfo["second_leader"] > 0)
		{
			$where_user_second["id"] = $userInfo["second_leader"];
			$userSecondInfo = get_user_info($where_user_second);
			if ($userSecondInfo && $userSecondInfo['status'] == 1 && $userSecondInfo['level'] == 1)
			{
				// 正常状态、创业会员
				$user_comm_2 = get_config_fx("user_comm_2");
				$user_comm_2 = round($user_comm_2 / 100 * $user_money_wait, 2);    // 比例*用户的账户积分
				if ($user_comm_2 > 0)
				{
					// change_money_log($userInfo['second_leader'], 1, "money_wait", 0, $user_comm_2, 6, 10, "提成下下级用户的账户积分", 1, 1, 1, $value['id'], $userInfo['id']);
				}
			}
		}

	}

}

/**
 * 获取服务费信息
 * @param int        id    ID
 * @return  string                对应值
 * @zd
 */
function get_config_shop_fee($id)
{
	$where["id"] = $id;
	$data = M("config_shop_fee")->where($where)->find();
	return $data;
}

/**
 * 获取第三方支付配置
 * @param type        string    类别，1微信APP，2支付宝APP，3微信公众号，4，微信小程序，5，微信H5
 * @return  array                    对应值
 * @zd
 */
function get_config_pay3($type)
{
	$where["type"] = $type;
	$data = M("config_pay3")->where($where)->order("id desc")->find();
	if (!$data)
	{
		return array();
	}
	return $data;
}


/**
 * 获取项目其他基本配置表中的值
 * @param name        string    字段名称
 * @return  string                    对应值
 * @zd
 */
function get_config_project($name)
{
	$where["name"] = $name;
	$data = M("config_project")->where($where)->getField("val");
	if (empty($data))
	{
		return 0;
	}
	return $data;
}


/**
 * 获取各提成值
 * @param name        string    字段名称
 * @return  string                    对应值
 * @zd
 */
function get_config_fx($name)
{
	$where["name"] = $name;
	$data = M("config_fx")->where($where)->getField("val");
	if (empty($data))
	{
		return 0;
	}
	return $data;
}

/**
 * 获取二维码值
 * @param name        string    字段名称
 * @return  string                    对应值
 * @zd
 */
function get_config_poster($name)
{
	$where["name"] = $name;
	$data = M("config_poster")->where($where)->getField("val");
	if (empty($data))
	{
		return 0;
	}
	return $data;
}


/**
 * 获取提现配置
 * @param type        string    字段名称
 * @return  string                    对应值
 * @zd
 */
function get_config_tx($type)
{
	$where["type"] = $type;
	$data = M("config_tx")->where($where)->order("id desc")->find();
	return $data;
}


/**
 * 双牛下发
 * @param money            num        支付金额
 * @param bank_user        string    银行卡户主
 * @param bank_no        string    银行卡号
 * @param remark        string    备注说明
 * @param ordernum        string    商户订单号
 * @param user_id        num        数字
 * @return  string                    等级对应的名称
 * @zd
 */
function SNXF($money, $bank_user, $bank_no, $remark, $ordernum, $user_id)
{

	$user = M('user')->where("id = {$user_id}")->find();
	$config = M('config_topay')->find();
	$url = "http://pay.ishuangniu.com/index.php/Payapi/Fourpay/index";
	$merchantid = $config['account'];    // 下发商户号
	$key = $config['acc_key'];            // 安全密钥
	$money = sprintf("%.2f", $money);
	if (empty($remark))
	{
		$remark = '提现';
	}
	$data = array(
		'merchant_id' => $merchantid,
		'money' => $money,
		'bank_no' => $bank_no,
		'bank_user' => $bank_user,
		'idcard' => $user['id_card'],
		'mobile' => $user['phone'],
		'ordernum' => $ordernum,
		'remark' => $remark,
		'notify_url' => C("C_HTTP_HOST") . '/index.php/Api/Notifyin/snxf',
	);
	$a = "merchant_id=$merchantid&money=$money&bank_no=$bank_no&ordernum=$ordernum&key=$key";
	$sign = strtoupper(md5("merchant_id=$merchantid&money=$money&bank_no=$bank_no&ordernum=$ordernum&key=$key"));

	$data['sign'] = $sign;
	$ok = http_post($url, $data);
	return json_decode($ok, true);
}


/**
 * 投放区域/展示分类 转文字，非三级分类的分类
 * @param int        num        数字
 * @return  string                    等级对应的名称
 * @zd
 */
function show_status($num = 0)
{
	$str = M('sns_goods_special_category')->where('id=' . $num)->getField('name');
	if (!$str)
	{
		$str = '无';
	}
	return $str;
}


/**
 * 投放区域分类
 * @return  array
 * @zd
 */
function show_list()
{
	$list = M('goods_special_category')->select();
	return $list;
}


/**
 * 提现状态 转文字
 * @param int        num        数字
 * @return  string                    等级对应的名称
 * @zd
 */
function tx_status($num = 0)
{
	switch ($num)
	{
		case 1:
			$str = "提现通过";
			break;
		case 2:
			$str = "提现拒绝";
			break;

		default:
			$str = "待审核";
			break;
	}
	return $str;
}

/**
 * 提现账户类型转文字
 * @param int        num        数字
 * @return  string                    等级对应的名称
 * @zd
 */
function tx_bank_type($num = 0)
{
	switch ($num)
	{
		case 1:
			$str = "支付宝";
			break;
		case 2:
			$str = "微信";
			break;

		default:
			$str = "银行";
			break;
	}
	return $str;
}

/**
 * 订单类型
 * @return  array                       订单类型对应的名称
 * @zd
 */
function order_type()
{
	$data = array(
		array(
			'type' => '0',
			'name' => '商城订单'
		),
		array(
			'type' => '1',
			'name' => '用户买单'
		),
		array(
			'type' => '2',
			'name' => '商家报单'
		),
		array(
			'type' => '3',
			'name' => '充值订单'
		),
		array(
			'type' => '4',
			'name' => '代理报单'
		),
		array(
			'type' => '5',
			'name' => '团购订单'
		),
		array(
			'type' => '6',
			'name' => '整点秒杀'
		),
		array(
			'type' => '7',
			'name' => '限时购'
		),
		array(
			'type' => '8',
			'name' => '砍价订单'
		),
		array(
			'type' => '9',
			'name' => '预售订单'
		),
		array(
			'type' => '10',
			'name' => '升级订单'
		),
	);
	return $data;
}

/**
 * 用户等级名称
 * @param int        level        等级，等级权重
 * @return  string                等级对应的名称
 * @zd
 */
function user_level_name($level = 0)
{
	$name = M("config_user_level")->where("level_ranking=" . $level)->getField('level_name');
	return $name;
}


/**
 * 用户状态
 * @param int        status        状态数字
 * @return  string                        状态对应的名称
 * @zd
 */
function user_status_name($status = 0)
{
	switch ($status)
	{
		case 1:
			$str = "正常";
			break;

		case 2:
			$str = "冻结";
			break;

		case 3:
			$str = "审核中";
			break;

		default:
			$str = "";
			break;
	}
	return $str;
}


/**
 * 图片/文件 上传
 * @param string      handlename    控件名称
 * @param string      path            存放路径，上传在本地服务时使用
 * @param int        size            大小，字节
 * @param array       exts            允许的文件后缀，数组
 * @return  json
 * 说明：正确=返回图片路径，错误-提示错误   **通过后台修改，可选择上传到七牛或后台**
 * @zd
 */
function uploads($handlename, $path = "asset", $size = 3145728, $exts = array(), $exit = 1, $is_video = 0, $real_url = 1)
{
	if (!$path)
	{
		$path = "asset";
	}
	if (!$size)
	{
		$size = 3145728;
	}
	if (!$is_video)
	{
		$msg = '图片上传';
	}
	else
	{
		$msg = '视频上传';
	}
	if (!$handlename)
	{
		jsonout($msg, 1, "请传入控件名称");
	}
	$upload_type = get_config("upload_type", true);

	if ($upload_type == 1 && $real_url == 1)
	{

		// 七牛上传
		$ret = qiniu_upload($handlename, $size);
		if ($exit < 1)
		{
			return array($msg, $ret[1], $ret[2]);
		}
		else
		{
			jsonout($msg, $ret[1], $ret[2], $exit);
		}
	}
	else
	{
		$ret = client_upload($handlename, $path, $size, '', $real_url);
		if ($exit < 1)
		{
			return array($msg, $ret[1], $ret[2]);
		}
		else
		{
			jsonout($msg, $ret[1], $ret[2]);
		}
	}

}


/**
 * 上传到七牛云
 * @param handlename      name    控件名称
 * @param size            name    文件大小，单位字节，换算成k 要 /1024
 * @return  array                    下标0：结果 0成功，1失败，下标1：成功时路径，失败时提示
 * 说明:新项目在使用七牛云上传时，先在七牛云把默认域名、空间处理好，其次在本项目的config中，把相关的key、域名、空间填写完整，ok~
 * @zd
 */
function qiniu_upload($handlename, $size = 3145728, $exts = array())
{

	// 用于签名的公钥和私钥
	$accessKey = get_config_qiniu("access_key");
	$secretKey = get_config_qiniu("secret_key");
	$bucket = get_config_qiniu("bucket");

	if (!$exts)
	{
		$exts = array('jpg', 'png', 'gif', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'mp3', 'mp4');
	}
	if (!is_array($exts))
	{
		$exts = array('jpg', 'png', 'gif', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'mp3', 'mp4');
	}
	$file = $_FILES[$handlename];

	if (!$file['tmp_name'] && $file['error'] > 0 && $file['size'] <= 0)
	{
		$ret = array('图片上传', 1, "文件异常，请重新上传");
		return $ret;
	}
	$size = intval($size);
	if ($size < 1)
	{
		$size = 3145728; // =3M
	}
	if ($file['size'] > $size)
	{

		$ret = array('图片上传', 1, "文件太大，请重新上传");
		return $ret;
	}

	$pos = strripos($file['name'], ".");
	$fileExt = substr($file['name'], $pos + 1);
	$fileExt = strtolower($fileExt);

	if (!in_array($fileExt, $exts))
	{
		$ret = array('图片上传', 1, "文件格式不对，请重新上传");
		return $ret;
	}
	$fileExt = "." . $fileExt;
	$auth = new Auth($accessKey, $secretKey);
	$token = $auth->uploadToken($bucket);
	$uploadMgr = new UploadManager();

	$key = time() . rand(1000, 9999) . $fileExt;
	$filePath = $file['tmp_name'];
	list($res, $err) = $uploadMgr->putFile($token, $key, $filePath);

	if ($err !== null)
	{
		$ret = array('图片上传', 1, "上传失败，请重新上传");
	}
	$ret = array('图片上传', 0, get_config_qiniu("img_domain") . $res['key']);
	return $ret;
}


/**
 * 服务器本地存储
 * @param handlename      name    控件名称
 * @param path            name    存储路径，默认 asset
 * @param size            name    文件大小，单位字节，换算成k 要 /1024
 * @return  array                    下标0：结果 0成功，1失败，下标1：成功时路径，失败时提示
 * @zd
 */
function client_upload($handlename, $path, $size = 3145728, $exts = array(), $real_url = 1)
{
	// 本地服务器存储
	if (!$exts)
	{
		$exts = array('jpg', 'png', 'gif', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'mp3', 'mp4', 'apk');
	}
	if (!is_array($exts))
	{
		$exts = array('jpg', 'png', 'gif', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'mp3', 'mp4');
	}
	$upload = new \Think\Upload();// 实例化上传类
	$newPath = "/upload/$path/";
	$upload->maxSize = $size;// 设置附件上传大小，字节，换成K 要 / 1024
	$upload->exts = $exts;// 设置附件上传类型
	$upload->rootPath = "." . $newPath; // 设置附件上传根目录
	$upload->savePath = ''; // 设置附件上传（子）目录
	// 没有文件夹，自动创建
	if (!is_dir($upload->rootPath))
	{
		mkdir($upload->rootPath);
	}
	$info = $upload->uploadOne($_FILES[$handlename]);

	if ($info)
	{
		if ($real_url == 0)
		{
			$ret = array('图片上传', 0, $newPath . $info['savepath'] . $info['savename']);
		}
		else
		{
			$ret = array('图片上传', 0, full_pre_url($newPath . $info['savepath'] . $info['savename']));
		}
	}
	else
	{
		$err = $upload->getError();
		$ret = array('图片上传', 1, $err);
	}
	return $ret;
}


/**
 * 为某个字段信息补充url
 * @param data                传入的整体数据
 * @param field                要操作的字段
 * @return  data                返回操作后的传入data数据
 * @zd
 * 说明：如果不是数组，直接补充后返回，如果是数组，则循环数组，然后返回
 */
function full_pre_url($data, $field = '')
{
	if (!$data)
	{
		return $data ? $data : "";
	}
	if (!is_array($data))
	{
		$res = strpos($data, "http");
		if ($res !== false)
		{
			return $data;
		}
		else
		{
			return C("C_HTTP_HOST") . $data;
		}
	}

	if (!$field)
	{
		return $data;
	}

	foreach ($data as $key => &$value)
	{
		if (is_array($value))
		{
			if (!isset($value[$field]))
			{

			}
			elseif (is_null($value[$field]))
			{
				$value[$field] = "";
			}
			elseif (!$value[$field] || empty($value[$field]))
			{
				// $value[$field] = "";
			}
			else
			{
				$res = strpos($value[$field], "http");
				if ($res === false)
				{
					$value[$field] = C("C_HTTP_HOST") . $value[$field];
				}

			}

		}
	}
	return $data;
}


/**
 * 写稿系统/各种日志
 * @param $type 用户类比1普通用户2商家用户3代理商用户4管理员用户  必须  需要指定
 * @param $opt_id  操作用户id  必须 操作人员id
 * @param $uid 备操作用户id 没有传入 0
 * @param string $content 简述操作内容,不传默认为空
 * @param $costArr  金额集合,金额操作时传入,其他可不传,包含cost和old_cost键值对,cost代表修改金额,小于0传入负数,old_cost原金额正负同cost
 * @param $order_id 订单id 订单操作时传入,其他可不传
 * @mgl
 */
function system_log($type, $opt_id, $uid, $content = '', $costArr, $order_id)
{


	switch ($type)
	{

		case 1:
			$memo = 'ID=' . $opt_id . '的会员用户,在' . date('Y-m-d H:i:s') . '进行了' . $content . '操作';
			if ($costArr)
			{
				$memo .= ',操作金额=￥' . $costArr['cost'] . ',原金额=￥' . $costArr['old_cost'];
			}
			if ($order_id)
			{
				$memo .= ',订单ID=' . $order_id;
			}
			break;
		case 2:
			$memo = 'ID=' . $opt_id . '的商家用户,在' . date('Y-m-d H:i:s') . '进行了' . $content . '操作';
			if ($costArr)
			{
				$memo .= ',操作金额=￥' . $costArr['cost'] . ',原金额=￥' . $costArr['old_cost'];
			}
			if ($order_id)
			{
				$memo .= ',订单ID=' . $order_id;
			}
			if ($uid)
			{
				$memo .= ',被操作用户ID=' . $uid;
			}
			break;
		case 3:
			$memo = 'ID=' . $opt_id . '的代理商用户,在' . date('Y-m-d H:i:s') . '进行了' . $content . '操作';
			if ($costArr)
			{
				$memo .= ',操作金额=￥' . $costArr['cost'] . ',原金额=￥' . $costArr['old_cost'];
			}
			if ($order_id)
			{
				$memo .= ',订单ID=' . $order_id;
			}
			if ($uid)
			{
				$memo .= ',被操作用户ID=' . $uid;
			}
			break;
		case 4:
			$memo = 'ID=' . $opt_id . '的管理员用户,在' . date('Y-m-d H:i:s') . '进行了' . $content . '操作';
			if ($costArr)
			{
				$memo .= ',操作金额=￥' . $costArr['cost'] . ',原金额=￥' . $costArr['old_cost'];
			}
			if ($order_id)
			{
				$memo .= ',订单ID=' . $order_id;
			}
			if ($uid)
			{
				$memo .= ',被操作用户ID=' . $uid;
			}
			break;

	}

	$data['opt_id'] = $opt_id;
	$data['uid'] = $uid;
	$data['user_amount'] = $costArr['old_cost'] ? $costArr['old_cost'] : 0;
	$data['amount'] = $costArr['cost'] ? $costArr['cost'] : 0;
	if ($costArr['cost'])
	{
		$data['type'] = $costArr['cost'] > 0 ? 2 : 1;
	}
	$data['utype'] = $type;
	$data['cre_time'] = time();
	$data['order_id'] = $order_id ? $order_id : 0;
	$data['memo'] = $memo;
	$data['ip'] = get_client_ip();
	M('system_log')->add($data);

}


/*
 * 得到日期
 * @suzl
 */
function get_date($timestamp, $format = 'Y-m-d')
{
	if (empty($timestamp))
	{
		return '';
	}
	return date($format, $timestamp);
}


/**
 * Note： 用户账户类型
 * User： zd
 * Date： 2020/5/2 15:16
 */
function user_money_type_cn($num = 0)
{
	$typeArr = array(
		0 => "无账户",
		1 => "用户余额",
		2 => "商家收益",
		3 => "代理收益",
		4 => "微信APP",
		5 => "支付宝APP",
		6 => "待返余额",
		7 => "商家已返",
		8 => "商家待返",
		9 => "代理已返",
		10 => "代理待返",
		11 => "平台默认账户",
		12 => "微信公众号",
		13 => "微信H5",
		14 => "微信小程序",
		15 => "支付宝WAP",
		16 => "用户积分",
	);
	return $typeArr[$num];
}


/**
 * 金额操作，含日志
 * @param user_id               用户ID，不论账户类型，都用 user_id
 * @param user_amount_auto      用户原金额是否自动获取，1是，0否
 * @param money_field           金额字段
 * @param user_amount           用户原金额
 * @param amount                变动金额
 * @param type                  账户类型，1用户余额，2商家收益，3代理收益，4微信账户(app)，5支付宝账户，6待返余额，
 *                                          7商家已返，8商家待返，9代理已返10代理待返，11平台默认账户，
 *                                          12微信公众号，13微信H5，14微信小程序，15支付宝WAP，16用户积分，17商家积分，18代理积分，19支付宝PC，20支付宝小程序
 *
 *    *** ***  *** *** ***  *** *** *** 账户类型的修改，一定要与上方的函数user_money_type_cn统一起来 *** *** *** *** *** *** *** *** *** *** ***
 *
 * @param deal_type             交易类型，1充值，2商城消费，3买单，4报单，5提现，6互转，7系统操作，8提现返还9签到，10提成，11定时返还，12(订单)退款，
 *                                          13会员升级提成（一级），14会员升级提成（二级），15会员升级提成（三级），
 *                                          16消费提成（自身），17消费提成（一级），18消费提成（二级），19消费提成（三级），
 * @param remark                描述
 * @param op_type               操作类型，1增加，2减少
 * @param utype                 用户类型，1用户，2商家，3代理
 * @param is_query              是否对账户执行金额变动操作，1是，0否
 * @param order_id              订单ID
 * @param other_user_id         对方user_id
 * @param $distribution_log_id  预分销表ID
 * @return  json                json数据
 * @zd
 */
function change_money_log($user_id = 0, $user_amount_auto = 1, $money_field = "money", $user_amount = 0, $amount = 0, $type = 1, $deal_type = 2, $remark = "", $op_type, $utype = 1, $is_query = 0, $order_id = 0, $other_user_id = 0, $distribution_log_id = 0)
{


	if (!$user_id)
	{
		return false;
	}
	if ($utype == 2)
	{
		$udb = M("shop");
		$where_u["user_id"] = $user_id;
	}
	elseif ($utype == 3)
	{
		$udb = M("agent");
		$where_u["user_id"] = $user_id;
	}
	else
	{
		$udb = M("user");
		$where_u["id"] = $user_id;
	}
	if ($user_amount_auto)
	{
		$memInfo = $udb->where($where_u)->find();
		if (!$memInfo)
		{
			return false;
		}
		$user_amount = $memInfo[$money_field];
	}


	$amount = abs($amount);

	if ($op_type == 1)
	{
		$now_money = round($user_amount + $amount, 3);
	}
	elseif ($op_type == 2)
	{
		/*if ($user_amount < $amount && $is_query == 1)
		{
			return false;
		}*/
		$now_money = round($user_amount - $amount, 3);
	}
	else
	{
		return false;
	}

	if ($is_query)
	{
		// 执行金额变动操作
		$rs = $udb->where($where_u)->setField($money_field, $now_money);
	}
	else
	{
		$rs = true;
	}
	if (!$rs)
	{
		return false;
	}

	// 插入日志表
	$data["user_id"] = $user_id;
	$data["user_amount"] = $user_amount;
	$data["amount"] = $amount;
	$data["type"] = $type;
	$data["deal_type"] = $deal_type;
	$data["remark"] = $remark;
	$data["order_id"] = $order_id;
	$data["other_user_id"] = $other_user_id;
	$data["utype"] = $utype;
	$data["op_type"] = $op_type;
	$data["cre_date"] = date("Y-m-d H:i:s");
	$data["cre_time"] = time();
	$data["ip"] = get_client_ip();


	$rs2 = M("account_log")->add($data);
	if ($rs2)
	{
		if ($distribution_log_id)
		{
			// 更新对应预分销记录
			$logData['is_profit'] = 1;
			$logData['order_profit_time'] = time();
			M("distribution_log")->where("id=" . $distribution_log_id)->save($logData);
		}
		return $rs2;
	}
	else
	{
		return false;
	}
}


/**
 * 检测验证码是否有效
 * @param string $phone 手机号
 * @param string $code 验证码
 * @return  string                    1有效
 * @author  zd
 */
function check_phone_code($phone, $code)
{
	if (!$code)
	{
		return false;
	}
	$where["code"] = $code;
	$where["phone"] = $phone;
	$where["expire_time"] = array("egt", time());
	$data = M("sms_log")->where($where)->find();
	if (!$data)
	{
		return false;
	}
	return true;
}


/**
 * json输出信息
 * @param string $msg 信息提示
 * @param int $status 0=正常，默认1=错误，2自动退出
 * @param array $result 要输出的数据
 * @param int $exit 0=继续执行，默认1=输入后停止
 * @return  array
 * @author  zd
 */
function jsonout($msg = '', $status = 1, $result = null, $exit = 1)
{
	$array['msg'] = $msg;
	$array['status'] = $status;
	if ($result)
	{
		$array['result'] = $result;
	}
	if ($exit)
	{
		exit(json_encode($array));
	}
	else
	{
		return $array;
	}
}


/**
 * 获取单个用户信息
 * @param string $where 查找条件
 * @param string $field 输出字段信息，默认所有，此时不包括密码
 * @param int $pwd 是否返回密码字段，默认0不返，1返回
 * @param int $utype 用户类型，默认1用户，2商家，3代理
 * @return  array
 * @author  zd
 */
function get_user_info($where, $field = "*", $pwd = 0, $utype = 1)
{
	if ($utype == 2)
	{
		$db = M("shop");
	}
	elseif ($utype == 3)
	{
		$db = M("agent");
	}
	else
	{
		$db = M("user");
	}
	if (!$field || empty($field))
	{
		$field = "*";
	}

	$userInfo = $db->where($where)->field($field)->find();
	if (!$userInfo)
	{
		return array();
	}
	//是否设置支付密码
	if ($userInfo['pay_password'])
	{
		$userInfo["is_paypwd"] = "1";
	}
	else
	{
		$userInfo["is_paypwd"] = "0";
	}
	if (!$pwd && $utype == 1)
	{
		unset($userInfo['password']);
		unset($userInfo['pay_password']);
	}
	if ($utype == 1)
	{
		$userInfo["level_name"] = user_level_name($userInfo['level']);
	}
	return $userInfo;
}

/**
 * 获取用户/商家/代理单个字段
 * @param string $input 查找时 传入的值
 * @param string $field 输出字段信息，默认手机号phone
 * @param int $type 传入值的类型，默认0=id，1=phone,2=openid,3=user_id
 * @param int $utype 用户类型，默认1用户，2商家，3代理
 * @return  string                 直接输入字段的值
 * @author  zd
 */


function get_user_field($input, $field = "phone", $type = 0, $utype = 1)
{
	if ($type == 1)
	{
		$where['phone'] = $input;
	}
	elseif ($type == 2)
	{
		$where['openid'] = $input;
	}
	elseif ($type == 3)
	{
		$where['user_id'] = $input;
	}
	else
	{
		$where['id'] = $input;
	}
	if (!$field)
	{
		$field = "phone";
	}
	if ($utype == 2)
	{
		$db = M("shop");
	}
	elseif ($utype == 3)
	{
		$db = M("agent");
	}
	else
	{
		$db = M("user");
	}
	$data = $db->where($where)->field($field)->getField($field);
	if (!$data)
	{
		return "";
	}
	return $data;
}

/**
 * 电脑端获取数据列表
 * @param string $table 查询表名
 * @param string/array      $where   查询条件
 * @param string $column 查询字段
 * @param string/array      $other_query   其他条件
 * @return                    list              数据
 * @return                    show              分页
 * @return                    sum              数据条数
 * @author  zd
 */
function pages($table, $where, $column = '', $other_query = false)
{
	$m = M($table);
	$p = I('p', 0, 'intval');
	$row = I('row', 10, 'intval');
	$isgroup = false;
	$group = '';

	if ($p == -1)
	{
		return null;
	}

	if (is_array($other_query))
	{
		if ($other_query['order'])
		{
			$order = $other_query['order'];
		}
		if ($other_query['group'])
		{
			$group = $other_query['group'];
		}
	}
	else
	{
		if (stripos($other_query, ' ASC') !== false || stripos($other_query, ' DESC') !== false)
		{
			$order = $other_query;
		}
	}

	if (empty($order))
	{

		if (stripos($column, 'id,') === 0)
		{
			$order = 'id DESC';
		}
		else
		{
			$order = $m->getPk() . ' DESC';
		}
	}

	if ($column)
	{
		$mm = $m->field($column)->where($w);
	}
	else
	{
		$mm = $m->where($w);
	}

	if ($group)
	{
		$mm = $mm->group($group);
		$isgroup = true;
	}

	$a = $mm->order($order)->page("$p,$row")->select();
	if ($isgroup)
	{
		$count = $m->where($w)->count('distinct ' . $group);
	}
	else
	{
		$count = $m->where($w)->count();
	}
	$Page = new \Think\Page($count, $row);
	$show = $Page->show();
	$data = array('list' => $a, 'show' => $show, 'sum' => $count);
	return $data;
}


// -------- 2018.11.21 之前部分存留 -------------------------------------

//新的分页控件
function page($table, $w, $column = '', $other_query = false)
{
	$m = M($table);
	$p = I('p', 0, 'intval');
	$row = I('row', 10, 'intval');
	$isgroup = false;
	$group = '';

	if ($p == -1)
	{
		return null;
	}

	if (is_array($other_query))
	{
		if ($other_query['order'])
		{
			$order = $other_query['order'];
		}
		if ($other_query['group'])
		{
			$group = $other_query['group'];
		}
	}
	else
	{
		if (stripos($other_query, ' ASC') !== false || stripos($other_query, ' DESC') !== false)
		{
			$order = $other_query;
		}
	}

	if (empty($order))
	{

		if (stripos($column, 'id,') === 0)
		{
			$order = 'id DESC';
		}
		else
		{
			$order = $m->getPk() . ' DESC';
		}
	}

	if ($column)
	{
		$mm = $m->field($column)->where($w);
	}
	else
	{
		$mm = $m->where($w);
	}

	if ($group)
	{
		$mm = $mm->group($group);
		$isgroup = true;
	}

	$a = $mm->order($order)->page("$p,$row")->select();
	if ($isgroup)
	{
		$count = $m->where($w)->count('distinct ' . $group);
	}
	else
	{
		$count = $m->where($w)->count();
	}
	$Page = new \Think\Page($count, $row);
	$show = $Page->show();
	$data = array('list' => $a, 'show' => $show, 'sum' => $count);
	return $data;
}

//手机端分页
function flow_page($table, $where, $column = '', $other_query = false, $row = 0)
{

	if (empty($row))
	{
		$row = I('row', 20, 'intval');
		if ($row > 100)
		{
			$row = 20;
		}
	}

	if ($other_query['order'] || $other_query['ORDER'])
	{
		$order = $other_query['order'];
	}
	else
	{
		$order = 'id desc';
	}
	if ($column)
	{
		$data = M($table)->field($column)->where($where)->limit($row)->order($order)->select();
	}
	else
	{
		$data = M($table)->where($where)->limit($row)->order($order)->select();
	}
	return $data;
}


/**
 * @param $input_name form表单中的name
 * @param $path 保存目录名,结尾带'/'
 * @param array $exts 格式数组
 * @param $saveName 保存具体的文件名，可为空
 * @param $size 文件大小
 * @return array
 * 如果success为0，msg中保存的是错误信息，success为1则保存的是具体路径
 */
function common_upload_file($input_name, $path, $exts, $saveName = '', $size = 3145728)
{
	if (empty($saveName))
	{
		$r = rand(0, 9);
		$saveName = uniqid() . $r;
	}
	$config = array(
		'maxSize' => $size,
		'rootPath' => './upload/',
		'savePath' => $path . '/',
		'saveName' => $saveName,
		'exts' => $exts
	);

	$upload = new \Think\Upload($config);
	$info = $upload->uploadOne($_FILES[$input_name]);
	$arr = array();
	if (!$info)
	{
		$arr = array(
			'success' => 0,
			'msg' => $upload->getError(),
			'path' => ''
		);
	}
	else
	{
		$arr = array(
			'success' => 1,
			'msg' => '/upload/' . $info['savepath'] . $info['savename'],
			'path' => '/upload/' . $info['savepath'] . $info['savename'],
			'ext' => $info['ext'],
			'type' => $info['type']
		);
	}
	return $arr;
}

//上传图片
function common_upload_photo($input_name, $path, $saveName = '', $size = 31457280)
{
	$config = array(
		'maxSize' => $size,
		'rootPath' => './upload/',
		'savePath' => $path . '/',
		'exts' => array('jpg', 'png', 'gif', 'jpeg')
	);
	if ($saveName)
	{
		$config['saveName'] = $saveName;
	}

	$upload = new \Think\Upload($config);
	$info = $upload->uploadOne($_FILES[$input_name]);
	$arr = array();
	if (!$info)
	{
		$arr = array(
			'success' => false,
			'msg' => $upload->getError(),
			'path' => ''
		);
	}
	else
	{
		$arr = array(
			'success' => true,
			'msg' => '/upload/' . $info['savepath'] . $info['savename'],
			'path' => '/upload/' . $info['savepath'] . $info['savename'],
			'ext' => $info['ext'],
			'type' => $info['type']
		);
	}
	return $arr;
}


//取得二维数组中的一列值
function get_array_column($arr, $element, $funname = '')
{

	if (empty($arr))
	{
		return '';
	}

	$column_array = array();
	if (function_exists('array_column'))
	{
		$column_array = array_column($arr, $element);
	}
	else
	{
		foreach ($arr as $v)
		{
			if ($v[$element])
			{
				$column_array[] = $v[$element];
			}
		}
		if ($column_array)
		{
			$column_array = array_unique($column_array);
		}
	}

	if (empty($column_array))
	{
		return '';
	}

	if ($funname && function_exists($funname))
	{
		$_data = call_user_func($funname, $column_array);
		return $_data;
	}
	else
	{
		return $column_array;
	}
}


/*
* 格式化时间
*/
function format_date($timestamp, $format = 'Y-m-d')
{
	if (empty($timestamp))
	{
		return '';
	}
	return date($format, $timestamp);
}

/**
 * 得到星期
 */
function get_week($timestamp)
{
	if ($timestamp <= 6)
	{
		$w = $timestamp;
	}
	else
	{
		$w = date('w', $timestamp);
	}

	$a = array(
		0 => '星期日',
		1 => '星期一',
		2 => '星期二',
		3 => '星期三',
		4 => '星期四',
		5 => '星期五',
		6 => '星期六',
	);
	return $a[$w];
}

/**
 * 一周起止时间戳
 */
function get_week_time()
{
	$beginThisweek = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('y'));
	$now = time();
	return array($beginThisweek, $now);
}

/**
 * 一月起止时间戳
 */
function get_month_time()
{
	$beginThismonth = mktime(0, 0, 0, date('m'), 1, date('y'));
	$now = time();
	return array($beginThismonth, $now);
}

/**
 * 一季度时间戳
 */
function get_quarter_time()
{
	$season = ceil(date('n') / 3);//当月是第几季度
	$begin = mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y'));
	//end = mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y'));
	$now = time();
	return array($begin, $now);
}

/**
 * 一年起止时间戳
 */
function get_year_time()
{
	$begin = mktime(0, 0, 0, 1, 1, date('y'));
	$now = time();
	return array($begin, $now);
}

//加密
function get_pwd($mingwen, $is_md5 = true, $salt = false)
{

	if ($is_md5)
	{
		$mingwen = md5($mingwen);
	}

	$newgnim = strrev($mingwen);

	if ($salt)
	{
		return sha1($mingwen . $salt . $newgnim);
	}
	else
	{
		return sha1($mingwen . '3.1415' . $newgnim);
	}
}

//对称加密
function encrypt2($data, $key = '2.718', $expire = 0)
{
	return \Think\Crypt::encrypt($data, $key, $expire);
}

//对称解密
function decrypt2($data, $key = '2.718')
{
	return \Think\Crypt::decrypt($data, $key);
}


//检查密码
function check_pwd($user_pwd, $pwd, $jiami = true, $salt = '3.1415')
{
	if (get_pwd($user_pwd, $jiami, $salt) == $pwd)
	{
		return true;
	}
	else
	{
		return false;
	}
}


//根据经常变化的值得到token
function get_change_str_token($user)
{
	$unique = $user['password'];
	$id = $user['id'];
	$str = $user['status'] . $user['isshop'] . $user['isagent'] . $user['openid'];
	$token = get_token($unique, $id, $str);
	return $token;
}

//检查token
function check_token($token, $user)
{
	if (empty($token))
	{
		api_json('session过期..', 3);
	}
	if (!is_array($user))
	{
		api_json('session过期..', 3);
	}


	if ($token != get_change_str_token($user))
	{
		session('app_sess_user', null);
		api_json('session过期..', 3);
	}
}

//获取token
function get_token($unique, $id, $phone = '')
{

	$str = $unique . '+' . $id . '+' . $phone;
	$date = date('Y-m-d h');    //1小时有效期
	$ip = get_client_ip();
	return sha1(md5($str) . $date . $ip);
}

//删除数组中的相同元素
function delete_array_element(&$arr, $val)
{
	foreach ($arr as $key => $value)
	{
		if ($value == $val)
		{
			unset($arr[$key]);
		}
	}
}


function get_http_url($url)
{
	$url = trim($url);
	if (empty($url) || $url == '#')
	{
		return '#';
	}
	else
	{
		if (strpos($url, 'http') !== 0)
		{
			return 'http://' . $url;
		}
		else
		{
			return $url;
		}
	}
}


function get_json_field($json, $field)
{
	if (empty($json))
	{
		return '';
	}
	$arr = json_decode($json, true);
	if (is_array($field))
	{
		$data = array();
		foreach ($field as $i)
		{
			$data[$i] = $arr[$i];
		}
		return $data;
	}
	return $arr[$field];
}


// err 0:OK,>=1:有错误,-1空
function _dat($msg = '', $code = 1, $result = null)
{
	if (func_num_args() == 0)
	{
		$arr = ['err' => -1, 'msg' => '暂无数据', 'result' => null];
	}
	else
	{
		if (func_num_args() == 1)
		{
			if (is_array($msg))
			{
				$arr = $msg;
			}
			else
			{
				if ($msg === true || strcasecmp($msg, 'OK') == 0)
				{
					$arr = ['err' => 0, 'msg' => 'OK', 'result' => null];
				}
				else
				{
					$arr = ['err' => 1, 'msg' => $msg];
				}
			}
		}
		else
		{
			if (func_num_args() == 2)
			{
				if (is_array($msg))
				{
					$arr = array_merge(['err' => $code, 'result' => $msg]);
				}
				else
				{
					$arr = ['err' => $code, 'msg' => $msg];
				}
			}
			else
			{
				$arr = ['err' => $code, 'msg' => $msg, 'result' => $result];
			}
		}
	}
	return $arr;
}


function set_config_cache()
{
	$c = M('config')->getField('name,val');
	S('config_cache', $c);
	return $c;
}

function get_config($name, $is_new = false)
{
	if (is_array($name))
	{
		$where['name'] = array('in', $name);
		$data = M('config')->where($where)->getField('name,val');
		return $data;
	}

	if ($is_new)
	{
		$data = M('config')->where(array('name' => $name))->getField('val');
	}
	else
	{
		$cc = S('config_cache');
		if ($cc && isset($cc[$name]))
		{
			$data = $cc[$name];
		}
		else
		{
			$c = set_config_cache();
			$data = $c[$name];
		}
	}
	return $data;
}


/**
 * 获取当前的url 地址
 * @return type
 */
function get_url()
{
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$url = $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
	$url = $url . "/";
	return $url;
}


// 根据传入的值，输出米/千米
function get_kilometre($x)
{
	if (empty($x))
	{
		return '';
	}
	if ($x < 1000)
	{
		return $x . 'm';
	}
	$km = round($x / 1000, 2);
	return $km . 'km';
}

//两点经纬度距离计算(米)
function get_distance($lat1, $lng1, $lat2, $lng2)
{

	if ($lat1 <= 0 || $lng1 <= 0 || $lat2 <= 0 || $lng2 <= 0)
	{
		return 0;
	}

	$radLat1 = $lat1 * M_PI / 180;
	$radLat2 = $lat2 * M_PI / 180;
	$a = $radLat1 - $radLat2;
	$b = $lng1 * M_PI / 180 - $lng2 * M_PI / 180;
	$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
	$s1 = $s * 6378.137; //地球半径
	$s2 = round($s1 * 1000);
	/*
	$s2 = round($s2 * 10000) / 10000;
	$s2 = $s2*1000;
	return ceil($s2);
	*/
	return $s2;
}

//计算两点经纬度 距离
function GetDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
{
	$radLat1 = $lat1 * PI() / 180.0;   //PI()圆周率
	$radLat2 = $lat2 * PI() / 180.0;
	$a = $radLat1 - $radLat2;
	$b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
	$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
	$s = $s * 6378.137;
	$s = round($s * 1000);
	if ($len_type = 1)
	{
		$s /= 1000;
	}
	return $s;
}

/**
 * 验证手机号是否合法
 * @param strint $phone
 * @return int $r 1:合法 0：不合法
 */
function check_phone($phone)
{
	$x = preg_match('/^1[123456789][0-9]{9}$/', $phone);
	if ($x)
	{
		return true;
	}
	return false;
}


/**
 * 错误日志
 * @param $text 数据
 * @param $code 错误代码或描述
 * @param $userid 操作人
 */
function _error_log($text, $code = 0, $userid = 0)
{
	$date = date('Y-m-d H:i:s');
	$str = "[{$date}]错误描述or等级:{$code}；操作用户:{$userid}；内容【";
	if (is_array($text))
	{
		$str .= var_export($text, true);
	}
	elseif (is_object($text))
	{
		$str .= '对象不能打印';
	}
	else
	{
		$str .= $text;
	}
	$str .= '】';
	$str .= "\r\n";

	error_log($str, 3, 'Public/log/log_' . date('Ymd') . '.txt');
	/*
	$fff=fopen("Public/z_jn_##_yzh__log.txt","a+");
	fwrite($fff, $str);
	fclose($fff);
	*/
}

//生成20位订单号 20181121113512
function make_order_no()
{
	$mt = microtime(true);
	list(, $m) = explode(".", $mt);
	$uid1 = str_pad($m, 4, '0');

	return date('YmdHis') . $uid1 . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
}


//验证身份证号是否正确
function is_idcard($id)
{
	$id = strtoupper($id);
	$regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
	$arr_split = array();
	if (!preg_match($regx, $id))
	{
		return false;
	}

	//检查15位
	if (15 == strlen($id))
	{
		$regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
		@preg_match($regx, $id, $arr_split);
		//检查生日日期是否正确
		$dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
		if (!strtotime($dtm_birth))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	$regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
	@preg_match($regx, $id, $arr_split);
	$dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
	if (!strtotime($dtm_birth)) //检查生日日期是否正确
	{
		return FALSE;
	}

	//检验18位身份证的校验码是否正确。
	//校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
	$arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
	$arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
	$sign = 0;
	for ($i = 0; $i < 17; $i++)
	{
		$b = (int)$id{$i};
		$w = $arr_int[$i];
		$sign += $b * $w;
	}

	$n = $sign % 11;
	$val_num = $arr_ch[$n];
	if ($val_num != substr($id, 17, 1))
	{
		return false;
	}
	return true;
}

// 发送短信验证码
function get_phone_code($phone = '', $type = 0)
{
	if (!$phone)
	{
		$phone = I('phone');
	}
	if (!$phone)
	{
		return '请输入正确的手机号';
	}
	if (!check_phone($phone))
	{
		return '请输入正确的手机号';
	}

	// 判断是否在允许发送的时间内
	$sms_space_time = get_config("sms_space_time");    // 间隔时间，秒
	$sms_space_time = intval($sms_space_time);
	$where["phone"] = $phone;
	$info = M("sms_log")->where($where)->order("id desc")->find();
	if (time() < $info["cre_time"] + $sms_space_time)
	{
		return '请求过于频繁，请稍候再试';
	}

	$code = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);


	$ss = send_sms($phone, $code, $type);

	if (!$ss || $ss["code"] != '20000')
	{
		if ($ss['code'] == '40019')
		{
			return '短信条数不足，请告知客服';
		}
		else
		{

			return '短信发送失败请重试或联系客服';
		}


	}
	// 加入短信日志
	$expire_time = get_config("sms_expire_time");
	$expire_time = $expire_time ? $expire_time : 60;    // 默认60秒
	$expire_time = intval($expire_time + time());
	$data_log["code"] = $code;
	$data_log["phone"] = $phone;
	$data_log["expire_time"] = $expire_time;
	$data_log["cre_time"] = time();
	M("sms_log")->add($data_log);
	session('phone_code_time', $phone . '_' . $code . '_' . time());
	return true;
}

// 新的发送短信
function send_sms($mobile, $code, $type = 0)
{


	$ext = '';
	// $account = '皓辉母婴';
	// $key ='ef4d571a7c1afae59ba22be8f682a9a4';
	$timestamp = time();
	$where['status'] = 1;
	$where['type'] = $type;
	$config_sms = M('config_sms')->where($where)->find();

	$account = $config_sms['account'];
	$key = $config_sms['acc_key'];
	$name = $config_sms['name'];
	if ($name == "新短信")
	{
		$content = $config_sms['acc_tmp'];
		$content = str_replace('{}', $code, $content);
		$moblie_suatus = moblie_status($content, $mobile, $code, $ext, $account, $key, $timestamp);
	}
	elseif ($name == "老短信")
	{
		$content = $config_sms['acc_tmp'];
		$content = str_replace('@', $code, $content);
		$moblie_suatus = sms_old($content, $mobile, $code, $account, $key);
	}
	elseif ($name == '聚合')
	{
		$mb_id = $config_sms['mb_id'];

		$moblie_suatus = sms_juhe($mb_id, $mobile, $code, $key);
	}

	return $moblie_suatus;
}


//新短信接口
function moblie_status($content, $mobile, $code, $ext, $account, $key, $timestamp)
{
	$jsonobj = array();
	$jsonobj['content'] = $content;
	$jsonobj['mobile'] = $mobile;
	$jsonobj['code'] = $code;
	$jsonobj['ext'] = $ext;
	$jsonobj['account'] = $account;
	$jsonobj['key'] = md5(($key) . md5($timestamp));
	$jsonobj['timestamp'] = $timestamp;
	$jsonobjString = json_encode($jsonobj, true);

	$url = "http://api.ljioe.cn/api/v1/sms";
	$data = testliantong($url, $jsonobjString);
	$result = json_decode($data, true);
	return $result;
}

//老版短信接口
function sms_old($content, $mobile, $code, $account, $key)
{
	$_sms = array(
		'user' => $account,
		'pwd' => $key,
	);
	if (!$content)
	{
		$content = "您的验证码是" . $code . "请勿向任何人提供您收到的短信验证码【中和通联】";
	}
	$flag = 0;
	$params = '';//要post的数据
	$argv = array(
		'name' => $_sms['user'],     //必填参数。用户账号
		'pwd' => $_sms['pwd'],     //必填参数。（web平台：基本资料中的接口密码）
		'content' => $content,   //必填参数。发送内容（1-500 个汉字）UTF-8编码
		'mobile' => $mobile,   //必填参数。手机号码。多个以英文逗号隔开
		//'stime'=>'',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
		'sign' => $_sms['sign'],    //必填参数。用户签名。
		'type' => 'pt',  //必填参数。固定值 pt
		//'extno'=>''    //可选参数，扩展码，用户定义扩展码，只能为数字
	);
	foreach ($argv as $key => $value)
	{
		if ($flag != 0)
		{
			$params .= "&";
			$flag = 1;
		}
		$params .= $key . "=";
		$params .= urlencode($value);// urlencode($value);
		$flag = 1;
	}
	$url = "http://web.cr6868.com/asmx/smsservice.aspx?" . $params;
	//提交的url地址

	$con = substr(file_get_contents($url), 0, 1);  //获取信息发送后的状态
	if ($con == '0')
	{
		$ss["code"] = '20000';
	}
	else
	{
		$ss["code"] = '10000';
	}
	return $ss;

}

//聚合短信接口
function sms_juhe($mb_id, $mobile, $code, $key)
{
	$val = urlencode('#code#=' . $code);
	$params = array(
		'key' => '1bafdcd9ace5283101e885777468613c', //您申请的APPKEY
		'mobile' => $mobile, //接受短信的用户手机号码
		'tpl_id' => $mb_id, //您申请的短信模板ID，根据实际情况修改
		'tpl_value' => $val//您设置的模板变量，根据实际情况修改
	);

	$paramstring = http_build_query($params);

	$url = "http://v.juhe.cn/sms/send";
	$data = juheCurl($url, $paramstring, 1);
	$result = json_decode($data, true);;
	if ($result['error_code'] == 0)
	{
		$ss["code"] = '20000';
	}
	else
	{
		$ss["code"] = '10000';
	}
	return $ss;
}

function juheCurl($url, $params = false, $ispost = 0)
{
	$httpInfo = array();
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if ($ispost)
	{
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_URL, $url);
	}
	else
	{
		if ($params)
		{
			curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
		}
		else
		{
			curl_setopt($ch, CURLOPT_URL, $url);
		}
	}
	$response = curl_exec($ch);
	if ($response === FALSE)
	{
		//echo "cURL Error: " . curl_error($ch);
		return false;
	}
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$httpInfo = array_merge($httpInfo, curl_getinfo($ch));
	curl_close($ch);
	return $response;
}

/**
 * POST调用api接口
 * @author lucifer
 * @date 2018年7月31日上午9:32:50
 */

function testliantong($url, $jsonobjString)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonobjString);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	curl_setopt(
		$ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8',
			'Content-Length: ' . strlen($jsonobjString)
		)
	);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}


//防form重复提交
function form_token($url = '')
{
	//页面:<input type="hidden" value="{$_token_}" name="_token_"/>
	if ($url)
	{
		if (session('_token_') == I('_token_'))
		{
			session('_token_', null);
		}
		else
		{
			redirect($url, 1, '重复提交内容……');
			exit;
		}
		return false;
	}

	$token = uniqid();
	session('_token_', $token);
	echo "<input type=\"hidden\" value=\"{$token}\" name=\"_token_\" />";
}


/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug 调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false)
{
	$method = strtoupper($method);
	$ci = curl_init();
	/* Curl settings */
	curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
	curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
	curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
	curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
	switch ($method)
	{
		case "POST":
			curl_setopt($ci, CURLOPT_POST, true);
			if (!empty($postfields))
			{
				$tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
				curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
			}
			break;
		default:
			curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
			break;
	}
	$ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
	curl_setopt($ci, CURLOPT_URL, $url);
	if ($ssl)
	{
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
	}
	//curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
	curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
	curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ci, CURLINFO_HEADER_OUT, true);
	/*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
	$response = curl_exec($ci);
	$requestinfo = curl_getinfo($ci);
	$http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
	if ($debug)
	{
		_error_log($postfields);
		_error_log($requestinfo);
		_error_log($response);
	}
	curl_close($ci);
	return $response;
}

function http_post($url, $data)
{

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	$re = curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。

	$res = curl_exec($ch);

	if ($res === FALSE)
	{
		echo 'cURL Error:' . curl_error($ch);
	}
	$r = curl_close($ch);
	if ($r === FALSE)
	{
		echo 'cURL Error:' . curl_error($ch);
	}
	return $res;

}

//get_config快捷方式
function GC($name, $is_new = false)
{
	$v = get_config($name, $is_new);
	if (empty($v) && $v != 0)
	{
		return '';
	}
	return $v;
}


function get_industry($id = 0)
{
	if ($id)
	{
		$name = M('industry')->where('id=' . $id)->getField('name');
		return $name;
	}
	$list = M('industry')->getField('id,name');
	return $list;
}

// 获取区域名称
function get_region_name($id)
{
	if (!isset($id) || empty($id) || !is_numeric($id))
	{
		return '';
	}
	$name = M('region')->where('id=' . $id)->getField('name');
	if (empty($name))
	{
		return '';
	}
	return $name;
}

/**
 * 获取物流公司接口
 * @param $express_id 公司id,或为空
 * @return 物流公司信息
 * @author  史超
 */
function get_express($express_id = 0)
{
	if ($express_id)
	{
		$express_list = M('express')->where('id=' . $express_id)->find();
	}
	else
	{
		if (!F('express_list'))
		{
			$express_list = M('express')->where('1=1')->select();
			F('express_list', $express_list);
		}
	}
	return $express_list ? $express_list : F('express_list');
}

/**
 * 获取地址信息
 * @param $where 查询条件
 * @param $table 查询表名
 * @return 地址信息
 * @author  史超
 */
function get_address($where, $table)
{
	$region = get_region_list();
	$where["isdel"] = 0;
	$date = M($table)->where($where)->find();
	$addr['id'] = $date['id'];
	$addr['province'] = $date['province'];
	$addr['city'] = $date['city'];
	$addr['district'] = $date['district'];
	$addr['consignee'] = $date['consignee'];
	$addr['phone'] = $date['phone'];
	$address = $region[$date['province']]['name'];
	$city = $region[$date['city']]['name'];
	//特区无省份
	if ($city == '市辖区' || $city == '县')
	{
		$city = '';
	}
	$address = $address . $city . $region[$date['district']]['name'] . $date['address'];
	$addr['address'] = $address;
	$region = null;
	return $addr;
}

function get_order_address($order)
{
	$region = get_region_list();
	$addr = "收货人：{$order['consignee']}，";
	$addr .= "电话：{$order['phone']}，";
	$addr .= "地址：{$region[$order['province']]['name']}，";
	$city = $region[$order['city']]['name'];
	if ($city == '市辖区' || $city == '县')
	{
		$city = '';
	}
	if ($city)
	{
		$addr .= $city . '，' . $region[$order['district']]['name'] . '，' . $order['address'];
	}
	else
	{
		$addr .= $region[$order['district']]['name'] . '，' . $order['address'];
	}

	$region = null;
	return $addr;
}

// 订单状态
function get_order_status($order_status)
{
	if ($order_status == '0')
	{
		return "待付款";
	}
	elseif ($order_status == 1)
	{
		return "待发货";
	}
	elseif ($order_status == '2')
	{
		return "待收货";
	}
	elseif ($order_status == '3')
	{
		return "已完成";
	}
	elseif ($order_status == '4')
	{
		return "已取消";
	}
	elseif ($order_status == '5')
	{
		return "已评价";
	}
	elseif ($order_status == '6')
	{
		return "已售后";
	}
	elseif ($order_status == '8')
	{
		return "快递打印中";
	}
	elseif ($order_status == -1)
	{
		return "申请售后";
	}
	elseif ($order_status == -2)
	{
		return "同意售后";
	}
	elseif ($order_status == -3)
	{
		return "商家拒绝";
	}
	elseif ($order_status == -4)
	{
		return "买家邮寄";
	}
	elseif ($order_status == -5)
	{
		return "商家收货";
	}
	elseif ($order_status == -6)
	{
		return "商家回寄";
	}
	elseif ($order_status == -7)
	{
		return "买家收货";
	}
	elseif ($order_status == -8)
	{
		return "退款";
	}
	elseif ($order_status == -9)
	{
		return "售后完成";
	}
	else
	{
		return "未知状态";
	}
}

/**
 * 角色权限过滤菜单
 * @param $act_list 权限信息
 * @return 菜单信息
 * @author  史超
 */
function getMenuList($act_list)
{
	//根据角色权限过滤菜单
	$menu_list = getAllMenu();
	if ($act_list != 'all' && $act_list != null)
	{
		$menu_list = M('jscd')->where('level=1 and is_del=0')->order("id asc")->select();
		foreach ($menu_list as $k => $v)
		{
			$menu_ej_list = M('jscd')->where('level=2 and is_del=0 and parent_id=' . $v['id'] . " and id in ($act_list)")->order("id asc")->select();
			$menu_ej = array();
			foreach ($menu_ej_list as $k1 => $v1)
			{
				$menu_ej[$k1]['name'] = $v1['name'];
				$menu_ej[$k1]['act'] = $v1['group1'];
				$menu_ej[$k1]['control'] = $v['group1'];
				$menu_ej[$k1]['args'] = $v1['args'];
			}
			$menu[$v['group1']]['name'] = $v['name'];
			$menu[$v['group1']]['control'] = $v['group1'];
			$menu[$v['group1']]['Icon'] = $v['icon'];
			$menu[$v['group1']]['sub_menu'] = $menu_ej;
		}
		return $menu;
	}
	elseif ($act_list == null)
	{
		redirect(U('Admin/Login/index'), 1, '您还没有权限！');
		exit;
	}
	else
	{
		return $menu_list;
	}
}

/**
 * 获取菜单列表
 * @return 菜单列表信息
 * @author  史超
 */
function getAllMenu()
{
	$menu_list = M('jscd')->where('level=1 and is_del=0')->order("id asc")->select();
	foreach ($menu_list as $k => $v)
	{
		$menu_ej_list = M('jscd')->where('level=2 and is_del=0 and parent_id=' . $v['id'])->order("id asc")->select();
		$menu_ej = array();
		foreach ($menu_ej_list as $k1 => $v1)
		{
			$menu_ej[$k1]['name'] = $v1['name'];
			$menu_ej[$k1]['act'] = $v1['group1'];
			$menu_ej[$k1]['control'] = $v['group1'];
			$menu_ej[$k1]['args'] = $v1['args'];
		}
		$menu[$v['group1']]['name'] = $v['name'];            // 获得一级名称
		$menu[$v['group1']]['Icon'] = $v['icon'];     //
		$menu[$v['group1']]['sub_menu'] = $menu_ej;      //把二级 的名称 和  地址 ：control/act   放到一个数组
	}
	return $menu;
}

/**
 * 获取最后一条数据
 * @return 获取最后一条数据
 * @author  史超
 */
function request_last_id($where, $order = 'desc')
{
	$last_id = I('last_id', 0, 'intval');
	if ($last_id > 0)
	{
		if ($order == 'asc')
		{
			$where['id'] = array('gt', $last_id);
		}
		else
		{
			$where['id'] = array('lt', $last_id);
		}
	}
	elseif ($last_id == -1)
	{
		jsonout('', 1, '已经是最后一条了');
	}
	return $where;
}

/**
 * 获取最后一个id
 * @return 获取最后一个id
 * @author  史超
 */
function get_last_id($arr, $field = 'id', $row = 0)
{
	if (empty($row))
	{
		$row = I('row', 20, 'intval');
	}
	if ($row > 100)
	{ //最大不超过100行
		$row = 20;
	}
	if (is_array($arr))
	{
		if (count($arr) < 1)
		{
			return -1;
		}
		$a = end($arr);
		if ($a[$field])
		{
			return $a[$field];
		}
	}
	return 0;
}

/**
 * 根据last_id判断页数
 * @return 根据last_id判断页数
 * @author  史超
 */
function get_page($table, $where, $order = 'desc', $row, $field = 'id', $last_id)
{
	$list = M($table)->where($where)->order($field . ' ' . $order)->getField($field, true);
	$max_page = ceil(count($list) / $row);
	if ($last_id != -1)
	{
		$key = array_search($last_id, $list);
		$page = ceil($key / $row);
	}
	else
	{
		$page = $max_page;
	}
	return array('page' => $page, 'max_page' => $max_page);
}

/**
 * 根据page判断lastid
 * @return 根据page判断lastid
 * @author  史超
 */
function get_page_last_id($table, $where, $order = 'desc', $row, $field = 'id', $page)
{
	if ($page > 1)
	{
		$list = M($table)->where($where)->order($field . ' ' . $order)->getField($field, true);
		$max_page = ceil(count($list) / $row);
		if ($page > $max_page)
		{
			jsonout('', 1, '已经是最后一页了');
		}
		else
		{
			$num = $row * ($page - 1);
			$list = M($table)->where($where)->order($field . ' ' . $order)->limit(0, $num)->select();
			$a = end($list);
			$last_id = $a[$field];
			if ($order == 'asc')
			{
				$where['id'] = array('gt', $last_id);
			}
			else
			{
				$where['id'] = array('lt', $last_id);
			}
		}
	}
	return $where;
}

/*
* 得到日期时间
*/
function get_time($timestamp, $format = 'Y-m-d H:i:s')
{
	if (empty($timestamp))
	{
		return '';
	}
	return date($format, $timestamp);
}

/********获取省市县接口********/
function get_region_list()
{
	if (!F('region_list'))
	{
		$region_list = M('region')->where('level<4')->getField('id,name,parent_id');
		F('region_list', $region_list);
	}
	return $region_list ? $region_list : F('region_list');
}

function get_ad($position, $row = 0)
{
	$newAd = array();
	if ($row)
	{
		$ad = M('ad')->where(array('position' => $position))->order('sort desc,id desc')->limit($row)->select();
		foreach ($ad as $v)
		{
			$newAd[] = array(
				'ad_id' => $v['id'],
				'photo' => full_pre_url($v['photo']),
				'module' => $v['module'],
				'mod_id' => $v['mod_id'],
				'api_url' => $v['api_url'],
				'link' => $v['link'],
				'title' => $v['title'],
				'content' => $v['content'],
                'cate_id' => $v['cate_id']
			);
		}
	}
	else
	{
		if (I('page'))
		{
			$page = I('page', 1, 'intval');
			$start = ($page - 1) * 10;
			$count = M('ad')->count();
			$max_page = ceil($count / 10);
			$newAd['ad'] = M('ad')->order('id desc')->limit($start, 10)->select();//此处未对页数处理
			$newAd['page'] = $page;
			$newAd['max_page'] = $max_page;
		}
		else
		{
			$ad = M('ad')->where(array('position' => $position))->find();
			$newAd = array(
				'ad_id' => $ad['id'],
				'photo' => full_pre_url($ad['photo']),
				'module' => $ad['module'],
				'mod_id' => $ad['mod_id'],
				'api_url' => $ad['api_url'],
				'link' => $ad['link']
			);
		}
	}
	return $newAd;
}


/*
 * 订单操作记录
 */
function add_order_log($order_id, $log_type, $action)
{
	$order = M('order')->where(array('id' => $order_id))->find();
	$data['order_id'] = $order_id;
	$data['user_id'] = $order['user_id'];
	$data['time'] = time();
	$data['desc'] = $action;
	$data['log_type'] = $log_type;
	$data['ip'] = get_client_ip();
	return M('order_log')->add($data);
}

/**
 * 添加退货日志
 */
function add_order_return_log($data, $refund_status)
{

	switch ($refund_status)
	{
		case 1:
			$title = '提交申请';
			$remark = '您的服务单已申请成功,请等待售后审核';
			break;
		case 2:
			if ($data['refund_type'] == 1 || $data['refund_type'] == 2)
			{
				$title1 = '审核通过';
				$remark1 = '审核通过';
				$title2 = '请将商品回寄商家';
				if (empty($data['shop_reason']))
				{
					$remark2 = '请把商品及相关包装证件一起发回商家';
				}
				else
				{
					$remark2 = $data['shop_reason'];
				}

			}
			else
			{
				$title1 = '审核通过';
				$remark1 = '审核通过';
				$title2 = '即将退款';
				if (empty($data['shop_reason']))
				{
					$remark2 = '售后正在准备退款,请耐心等待';
				}
				else
				{
					$remark2 = $data['shop_reason'];
				}
			}

			break;
		case 3:
			$title = '审核拒绝';
			$remark = $data['shop_reason'];
			break;
		case 4:
			$title = '用户回寄';
			$remark = '已将商品回寄,快递公司:' . $data['user_express'] . ',快递单号:' . $data['user_tracking_number'];
			break;
		case 5:
			$title = '卖家已收货';
			$remark = '您的回寄商品卖家已收到,请等待后续处理';
			break;
		case 6:
			$title = '商家发货';
			$remark = '新的商品已发出,快递公司:' . $data['shop_express_name'] . ',快递单号:' . $data['shop_tracking_number'];
			break;
		case 7:
			$title = '买家已收货';
			break;
		case 8:
			$title = '退款成功';
			$remark = '您的售后已按原支付方式退款,请注意查收';
			break;
		case 9:
			$title = '售后完成';
			break;
		default:
			$title = '';
			$remark = '';
	}
	//退货日志数组
	if ($refund_status == 2)
	{
		$log_arr = array(
			'title' => $title1,
			'remark' => $remark1,
			'refund_status' => $refund_status,
			'refund_type' => $data['refund_type'],
			'order_id' => $data['order_id'],
			'order_goods_id' => $data['order_goods_id'],
			'refund_id' => $data['id'],
			'cre_time' => time(),
			'ip' => get_client_ip()
		);
		//添加到退货日志中
		$rs = M('order_refund_log')->add($log_arr);
		$log_arr = array(
			'title' => $title2,
			'remark' => $remark2,
			'refund_status' => $refund_status,
			'refund_type' => $data['refund_type'],
			'order_id' => $data['order_id'],
			'order_goods_id' => $data['order_goods_id'],
			'refund_id' => $data['id'],
			'cre_time' => time(),
			'ip' => get_client_ip()
		);
		//添加到退货日志中
		$rs = M('order_refund_log')->add($log_arr);
	}
	else
	{
		$log_arr = array(
			'title' => $title,
			'remark' => $remark,
			'refund_status' => $refund_status,
			'refund_type' => $data['refund_type'],
			'order_id' => $data['order_id'],
			'order_goods_id' => $data['order_goods_id'],
			'refund_id' => $data['id'],
			'cre_time' => time(),
			'ip' => get_client_ip()
		);
		//添加到退货日志中
		$rs = M('order_refund_log')->add($log_arr);
	}

	if (empty($rs))
	{
		return false;
	}
	else
	{
		return true;
	}
}

/**
 * 退换货原因
 */
function refund_reason()
{
	$arr = array(
		'尺码拍错/不喜欢/效果不好',
		'质量问题',
		'材质成分含量、面料、皮质不符',
		'尺寸不符',
		'做工粗糙/有瑕疵',
		'颜色/款式/图案与描述不符',
		'卖家发错货',
		'假冒品牌',
		'其他',
	);
	return $arr;
}

/**
 * 商家退货地址
 */
function shop_return_address($id)
{
	$address = M('shop_refund_address')->where('id=' . $id)->getField('address');
	return $address;
}

// 售后订单状态
function get_refund_status($refund_status)
{

	if ($refund_status == 1)
	{
		return "申请售后";
	}
	elseif ($refund_status == 2)
	{
		return "同意售后";
	}
	elseif ($refund_status == 3)
	{
		return "商家拒绝";
	}
	elseif ($refund_status == 4)
	{
		return "买家邮寄";
	}
	elseif ($refund_status == 5)
	{
		return "商家收货";
	}
	elseif ($refund_status == 6)
	{
		return "商家回寄";
	}
	elseif ($refund_status == 7)
	{
		return "买家收货";
	}
	elseif ($refund_status == 8)
	{
		return "退款";
	}
	elseif ($refund_status == 9)
	{
		return "售后完成";
	}
	else
	{
		return "未知状态";
	}
}

//售后类型
function get_refund_type($refund_type)
{
	if ($refund_type == 1)
	{
		return "退货";
	}
	elseif ($refund_type == 2)
	{
		return "换货";
	}
	elseif ($refund_type == 3)
	{
		return "退款";
	}
}

/**
 * 用于售后页面上现在的售后流程
 * @param $array  需要的状态的数组  例如 $array 为 [1,2] 返回的数组为array('提交申请','客服审核')
 * @return array
 */
function get_refund_process($array)
{
	$arr[1] = '提交申请';
	$arr[2] = '审核通过';
	$arr[3] = '审核拒绝';
	$arr[4] = '买家回寄';
	$arr[5] = '商家收货';
	$arr[6] = '商家发货';
	$arr[7] = '买家收货';
	$arr[8] = '退款';
	$arr[9] = '售后完成';
	$arr[10] = '审核中';
	$data = array();
	foreach ($array as $k => $value)
	{
		$data[$k] = $arr[$value];
	}
	return $data;
}


/**
 * 根据售后订单的状态，来展示不同步的流程
 * @param int $refund_id 售后订单ID
 * @return array
 * @zd
 */
function get_refund_process_by_id($refund_id = 0)
{
	if (!$refund_id)
	{
		return array();
	}
	$data = M("order_refund")->where("id=" . $refund_id)->field("id,refund_type,refund_status")->find();
	if (!$data)
	{
		return array();
	}
	// refund_type： 1 退货 2换货 3仅退款
	// 状态 1 申请 2同意  3拒绝  4 买家邮寄 5商家收货  6 商家回寄 7买家收货  8退款 9 售后完成
	if ($data['refund_status'] == 1)
	{
		$arr = array(1, 10);
	}
	if ($data['refund_status'] == 2)
	{
		$arr = array(1, 2);
	}
	if ($data['refund_status'] == 3)
	{
		$arr = array(1, 3);
	}
	if ($data['refund_status'] == 4)
	{
		if ($data['refund_type'] == 3)
		{
			$arr = array(1, 2);
		}
		else
		{
			$arr = array(1, 2, 4);
		}
	}
	if ($data['refund_status'] == 5)
	{
		// 商家收货
		// 1 退货 2换货 3仅退款
		if ($data['refund_type'] == 1)
		{
			$arr = array(1, 2, 4, 5);
		}
		elseif ($data['refund_type'] == 2)
		{
			$arr = array(1, 2, 4, 5);
		}
		elseif ($data['refund_type'] == 3)
		{
			$arr = array(1, 2);
		}

	}
	if ($data['refund_status'] == 6)
	{
		// 商家回寄
		// 1 退货 2换货 3仅退款
		if ($data['refund_type'] == 1)
		{
			$arr = array(1, 2, 4, 5);
		}
		elseif ($data['refund_type'] == 2)
		{
			$arr = array(1, 2, 4, 5, 6);
		}
		elseif ($data['refund_type'] == 3)
		{
			$arr = array(1, 2);
		}
	}
	if ($data['refund_status'] == 7)
	{
		// 1 退货 2换货 3仅退款
		if ($data['refund_type'] == 1)
		{
			$arr = array(1, 2, 4, 5);
		}
		elseif ($data['refund_type'] == 2)
		{
			$arr = array(1, 2, 4, 6, 7);
		}
		elseif ($data['refund_type'] == 3)
		{
			$arr = array(1, 2);
		}
	}
	if ($data['refund_status'] == 8)
	{
		// 1 退货 2换货 3仅退款
		if ($data['refund_type'] == 1)
		{
			$arr = array(1, 2, 4, 5, 8);
		}
		elseif ($data['refund_type'] == 2)
		{
			$arr = array(1, 2, 4, 6, 7);
		}
		elseif ($data['refund_type'] == 3)
		{
			$arr = array(1, 2, 8);
		}
	}
	if ($data['refund_status'] == 9)
	{
		// 1 退货 2换货 3仅退款
		if ($data['refund_type'] == 1)
		{
			$arr = array(1, 2, 4, 5, 8);
		}
		elseif ($data['refund_type'] == 2)
		{
			$arr = array(1, 2, 4, 6, 7);
		}
		elseif ($data['refund_type'] == 3)
		{
			$arr = array(1, 2, 8, 9);
		}
	}

	$array = get_refund_process($arr);
	$last_process = end($array);
	$log = M("order_refund_log")->where("refund_id=" . $refund_id)->order("id desc")->field("title,remark")->find();

	$res['process_title'] = $last_process;
	$res['process_line'] = $array;
	$res['process_tips'] = $log;
	return $res;
}


/**
 * 获取七牛云的配置信息
 * @param name        string    字段名称
 * @param $id         int       对应的ID
 * @return  string                对应值
 * @zd
 */
function get_config_qiniu($name, $id = 0)
{
	if (!$id)
	{
		$where = "1=1";
	}
	else
	{
		$where["id"] = $id;
	}
	$data = M("config_qiniu")->where($where)->order('id desc')->getField($name);
	if (!$data)
	{
		return "";
	}
	return $data;
}

/**
 * 把七牛云信息和上传文件方式，写入到本地
 * @param name        string    字段名称
 * @return  string                对应值
 * @zd
 */
function put_qiniu_file()
{
	$qnInfo = M("config_qiniu")->order("id desc")->field("access_key,secret_key,bucket,img_domain")->find();
	$upload_type = M("config")->where("name='upload_type'")->getField("val");
	$qnInfo['upload_type'] = $upload_type;
	$json = json_encode($qnInfo);
	file_put_contents("./Public/extends/ueditor/1.4.3/php/must.json", $json);
}


/**
 * 添加管理员登录记录
 * @param   $is_success     int      是否登录成功，1成功，0失败
 * @param   $username       string   用户名
 * @param   $password       string   密码，登录失败时有
 * @param   $aid            int      登录成功时用户ID
 * @zd
 */
function add_admin_login_log($is_success = 0, $username = '', $password = '', $aid = 0)
{
	$data['aid'] = $aid;
	$data['username'] = $username;
	$data['password'] = $password;
	$data['cre_date'] = date("Y-m-d H:i:s");
	$data['cre_time'] = time();
	$data['ip'] = get_client_ip();
	$data['is_success'] = $is_success;
	M("admin_log")->add($data);
}


/**
 * 查询本项目支付流水号或第三方支付订单号
 * @param   $order_id       string      订单ID
 * @param   $order_no       string      订单号
 * @return  string                      订单号
 * @zd
 */
function get_order_trade_no($order_id = 0, $order_no = '', $field = "")
{
	if (!$order_id && !$order_no)
	{
		return "";
	}
	if ($order_id)
	{
		$where['order_id'] = $order_id;
	}
	if ($order_no)
	{
		$where['order_no'] = $order_no;
	}
	if ($field)
	{
		$data = M('pay3_log')->where($where)->getField($field);
	}
	else
	{
		$data = M('pay3_log')->where($where)->find();
	}
	$data = $data ? $data : "";
	return $data;
}


/**
 * 获取订单类型
 * @param   $order_type     int      订单类型
 * @return  string                  订单类型
 * @zd
 */
function get_order_type_cn($order_type = '')
{
	switch ($order_type)
	{
		case 0:
			$str = "商城订单";
			break;
		case 1:
			$str = "用户买单";
			break;
		case 2:
			$str = "商家报单";
			break;
		case 3:
			$str = "充值订单";
			break;
		case 4:
			$str = "代理报单";
			break;
		case 5:
			$str = "团购订单";
			break;
		case 6:
			$str = "整点秒杀";
			break;
		case 7:
			$str = "限时购";
			break;
		case 8:
			$str = "砍价订单";
			break;
		case 9:
			$str = "预售订单";
			break;
		case 10:
			$str = "升级订单";
			break;
		case 11:
			$str = "积分订单";
			break;
		default:
			$str = "商城订单";
			break;
	}
	return $str;
}


/**
 * 获取支付状态
 * @param   $pay_status     int      状态值
 * @return  string                  订单类型
 * @zd
 */
function get_pay_status_cn($pay_status = '')
{
	if ($pay_status == 1)
	{
		$str = "已支付";
	}
	else
	{
		$str = "未支付";
	}
	return $str;
}

/**
 * 获取发货状态
 * @param   $shipping_status     int      状态值
 * @return  string                  订单类型
 * @zd
 */
function get_shipping_status_cn($shipping_status = '')
{
	if ($shipping_status == 1)
	{
		$str = "已发货";
	}
	elseif ($shipping_status == 2)
	{
		$str = "已收货";
	}
	elseif ($shipping_status == 3)
	{
		$str = "自提";
	}
	else
	{
		$str = "未发货";
	}
	return $str;
}


/**
 * 获取发货方式
 * @param   $shipping_type     int      状态值
 * @return  string                  订单类型
 * @zd
 */
function get_shipping_type_cn($shipping_type = '')
{
	if ($shipping_type == 1)
	{
		$str = "快递发货";
	}
	elseif ($shipping_type == 0)
	{
		$str = "自提/虚拟";
	}
	else
	{
		$str = "";
	}
	return $str;
}

/**
 * 格式化开始结束时间查询条件
 * @param $time_res array 未被格式化的时间
 * @return array 格式化完成后的时间
 */
function get_time_style($time_res)
{
	$timeResArr = explode(" - ", $time_res);
	$timeResArr[0] = strtotime($timeResArr[0]);
	$timeResArr[1] = strtotime($timeResArr[1]);
	return $timeResArr;
}

/**
 * 获取订单的某个字段/全部信息
 * @param   $order_id       string      订单ID
 * @param   $order_no       string      订单号
 * @return  string                      订单号
 * @zd
 */
function get_order_info($order_id = 0, $order_no = '', $field = "")
{
	if (!$order_id && !$order_no)
	{
		return "";
	}
	if ($order_id)
	{
		$where['id'] = $order_id;
	}
	if ($order_no)
	{
		$where['order_no'] = $order_no;
	}
	if ($field)
	{
		$data = M('order')->where($where)->getField($field);
	}
	else
	{
		$data = M('order')->where($where)->find();
	}
	$data = $data ? $data : "";
	return $data;
}

// 获取用户姓名
function get_user_name($user_id)
{
	$resData = M('user')->where('id=' . $user_id)->getField('realname');
	return $resData;
}

// 获取用户手机号
function get_user_phone($user_id)
{
	$resData = M('user')->where('id=' . $user_id)->getField('phone');
	return $resData;
}


function getUrlMethod($data)
{


	$data = mb_substr($data, 1, -1);

	$data = str_replace('index.php/', '', $data);

	$dd = strrchr($data, '.');

	$rr = str_replace($dd, '', $data);

	$de = explode('/', $rr);

	$count = count($de);
	$date = array();

	for ($i = 0; $i < $count; $i++)
	{
		if ($i == 1)
		{
			$date['controller'] = $de[$i];
		}
		else
		{
			if ($i == 2)
			{
				$date['method'] = $de[$i];
			}
		}
	}


//    $date['method'] = mb_substr($date['method'],0,strrpos($date['method'],'/'));

	$where['group1'] = array('eq', $date['controller']);
	$where['level'] = array('eq', 1);
	$da = M('jscd')->where($where)->find();
	$where1['parent_id'] = array('eq', $da['id']);
	$where1['group1'] = array('eq', $date['method']);

	$de = M('jscd')->where($where1)->find();
	if (!$de)
	{

		$de['name'] = $da['name'];
	}

	return $de['name'];
}


/**
 * 手机号中间4位 * 代替
 * @param $phone          手机
 * @param $type           类型，1默认遮挡中间4位，2只保留前后各一位
 * @return string
 * @zd
 */
function get_phone_mask($phone, $type = 1)
{
	if (!$phone)
	{
		return '';
	}
	if ($type == 2)
	{
		$str_1 = substr($phone, 0, 1);
		$str_2 = substr($phone, -1);
		return $str_1 . '****' . $str_2;
	}
	else
	{
		return substr_replace($phone, '****', 3, 4);
	}
}


// 二维关联数组根据其中一个字段排序
// @zd
function sort_array_multisort($array, $orderby, $order = SORT_ASC, $sort_flags = SORT_NUMERIC)
{
	$refer = array();
	foreach ($array as $key => $value)
	{
		$refer[$key] = $value[$orderby];
	}
	if ($orderby == "star" || $orderby == "xl")
	{
		$order = SORT_DESC;
	}
	else
	{
		$order = SORT_ASC;
	}
	array_multisort($refer, $order, $sort_flags, $array);
	return $array;
}

// 获取特殊分类信息 商城分类
function getGoodsHot($i = false)
{
	if (!$i)
	{
		return "无";
	}
	$one = M('goods_special_category')->where("id=$i")->find();
	if ($one)
	{
		return $one['name'];
	}
	else
	{
		return "无";
	}
}

// 获取普通的商品分类信息
function getCategoryName($id, $ispid = true)
{
	$cat = M('goods_category')->find($id);
	$name = $cat['name'];
	if ($ispid && $cat['pid'])
	{
		$pname = M('goods_category')->where('id=' . $cat['pid'])->getField('name');
		if ($pname)
		{
			$name = $pname . ' - ' . $name;
		}
	}
	if (!$name)
	{
		return "无";
	}
	else
	{
		return $name;
	}
}

// 获取商家名称
function getShopName($id)
{
	if (empty($id))
	{
		return '自营';
	}
	if (is_array($id))
	{
		$list = M('shop')->where('id in(' . join(',', $id) . ')')->getField('id,shop_name');
		$list[0] = '自营';
		return $list;
	}
	$shop_name = M('shop')->where('id=' . $id)->getField('shop_name');
	$shop_name = $shop_name ? $shop_name : "下架商家";
	return $shop_name;
}

// 把一般的、常用的值 转换为文字
function numToString($num)
{
	if ($num == 1)
	{
		return "是";
	}
	else
	{
		return "否";
	}
}

/*
 * 更改用户等级，并写入日志
 * $newLevel        新等级
 * @zd
 * */
function change_level_log($user_id, $newLevel = 0, $order_id = 0, $remark = '')
{
	if (!$user_id)
	{
		return true;
	}
	$where_u['id'] = $user_id;
	$userInfo = get_user_info($where_u);
	if (!$userInfo)
	{
		return true;
	}
	$newLevel = intval($newLevel);
	$rs = M("user")->where("id=" . $user_id)->setField("level", $newLevel);
	if ($rs !== false)
	{
		if (!$remark)
		{
			$remark = "等级变更";
		}
		$data_log['user_id'] = $user_id;
		$data_log['old_level'] = $userInfo['level'];
		$data_log['new_level'] = $newLevel;
		$data_log['order_id'] = $order_id;
		$data_log['remark'] = $remark;
		$data_log['cre_time'] = time();
		$data_log['cre_date'] = date("Y-m-d H:i:s");
		$data_log['ip'] = get_client_ip();
		M("user_level_log")->add($data_log);
		//等级更新成功  查询上级是否满足条件升级
//        first_level($user_id);
		return true;
	}
	else
	{
		return false;
	}
}

function first_level($user_id){
    $userInfo = M('user')->find($user_id);
    $profit_space = get_config_project('profit_space');
    if($userInfo['first_leader']){
        $top = M('user')->find($userInfo['first_leader']);

        //查询所有的下级
        $allUser = M('user')->where('first_leader='.$top['id'])->select();
        foreach ($allUser as $k => $v){
            $ids[$k] = $v['id'];
        }

        $orderWhere['user_id'] = array('in',$ids);
        $orderWhere['pay_status'] = 1;
        $orderWhere['profit_space'] = array('egt',$profit_space);
        $count = M('order')->where($orderWhere)->count();//下级多少单
        if($top['level'] == 0){
            $info = get_config_user_level(1);
        }elseif($top['level'] == 1){
            $info = get_config_user_level(2);
        }elseif($top['level'] == 2){
            $info = get_config_user_level(3);
        }
        //判断满足了哪一个条件
        $where['first_leader'] = $userInfo['first_leader'];
        if($info['up_level_by_auto'] == 1){
            //查询下级邀请多少
            $number = $info['number'];
            if($info['up_auto_first_leader_level'] == 0){
                if($count >= $number){
                    change_level_log($top['id'],$info['level_ranking']);
                }
            }elseif($info['up_auto_first_leader_level'] == 1){

                if($count >= $number){
                    change_level_log($top['id'],$info['level_ranking']);
                }
            }elseif($info['up_auto_first_leader_level'] == 2){

                if($count >= $number){
                    change_level_log($top['id'],$info['level_ranking']);
                }
            }elseif($info['up_auto_first_leader_level'] == 3){

                if($count >= $number){
                    change_level_log($top['id'],$info['level_ranking']);
                }
            }
        }
    }
}
/*
 * 支付升级
 * 根据等级配置，决定用户是否升级
 * 如果传入订单ID，则根据订单信息判断是否可以升级。
 * 如果没有传入订单ID，则寻找用户支付成功、等级最大的订单
 *  同时还要大于当前等级，如果小于或等于的话，就没有意义了
 * 如果有 且 符合条件，则升级，记录升级日志
 * @zd
 * */
function up_level_by_money($user_id = 0, $order_id = 0)
{
	if (!$user_id)
	{
		return true;
	}
	$up_level_by_money = get_config_project("up_level_by_money");   // 支付升级
	if (!$up_level_by_money)
	{
		return true;
	}
	$where_u['id'] = $user_id;
	$userInfo = get_user_info($where_u);
	if (!$userInfo || $userInfo['status'] != 1 || $userInfo['is_del'] == 1)
	{
		return true;
	}
	if (!$order_id)
	{
		$where['user_id'] = $user_id;
		$where['order_status'] = array("in", "1,2,3,5");
		$where['order_type'] = 10;
		$where['isdel'] = 0;
		$where['level_ranking'] = array("gt", $userInfo['level']);
		$remark = "支付订单升级";
	}
	else
	{
		$where['id'] = $order_id;
		$remark = "用户订单升级";
	}
	$orderInfo = M("order")->where($where)->order("level_ranking desc,id desc")->find();
	if (!$orderInfo)
	{
		return true;
	}
	if ($userInfo['level'] >= $orderInfo['level_ranking'])
	{
		return true;
	}
	if($orderInfo['is_profit'] == 1){
	    return true;
    }

//	change_money_log(($user_id),1,'price_pay',0,$orderInfo['total_commodity_price'],19,10,'购买身份赠送金额',1,1,1,0,0,0);
	$rs = change_level_log($user_id, $orderInfo['level_ranking'], $orderInfo['id'], $remark);
	M('order')->where('id='.$orderInfo['id'])->setField('is_profit',1);
//	recommend_level_up($user_id, $orderInfo['level_ranking'], $orderInfo['id']);
	return $rs;
}

/*
 * 自动升级--根据设置的下级人员、订单额度等
 * @zd
 * */
function up_level_by_auto($user_id = 0)
{
	if (!$user_id)
	{
		return true;
	}
	$up_level_by_auto_project = get_config_project("up_level_by_auto");   // 自动升级
	$down_level_by_auto_project = get_config_project("down_level_by_auto");   // 自动降级
	if (!$up_level_by_auto_project)
	{
		return true;
	}
	$where_u['id'] = $user_id;
	$userInfo = get_user_info($where_u);
	if (!$userInfo || $userInfo['status'] != 1 || $userInfo['is_del'] == 1)
	{
		return true;
	}
	// 取出所有大于本等级的级别，如果没有，则不再计算升级相关逻辑。但是（可能）要计算降级的。
	// 先确定升级条件满足方式，是全部，还是其中几个
	if ($down_level_by_auto_project)
	{
		$where_lv['is_del'] = 0;
		$where_lv['status'] = 1;
	}
	else
	{
		$where_lv['is_del'] = 0;
		$where_lv['status'] = 1;
		$where_lv['level_ranking'] = array("gt", $userInfo['level']);
	}
	$upList = M("config_user_level")->where($where_lv)->order("level_ranking desc")->select();
	if ($upList)
	{
		// 此时开始计算所有数据
		$sonList = get_son_num_data($user_id, 0);
		update_son_level($user_id, 0);  // 更新用户等级

		foreach ($upList as $key => $value)
		{
			if ($value['up_level_by_auto'] == 1)
			{
				$sonOrder = get_son_order_data($user_id, 0, 0, $value['up_auto_goods'], $value['up_auto_order_type']);
				$up_nost = 0;
				// 人数+等级 升级各条件：1成立，0不成立，2默认-无意义
				$upc_0 = 2; // 团队  人数+等级
				$upc_1 = 2; // 下一级人数+等级
				$upc_2 = 2; // 下二级人数+等级
				$upc_3 = 2; // 下三级人数+等级

				if ($value['up_auto_first_leader_num'] > 0)
				{
					// 需要判断下一级人数，先看等级要求
					$up_auto_first_leader_num = 0;
					foreach ($sonList['level_1'] as $key1 => $value1)
					{
						if ($key1 >= $value['up_auto_first_leader_level'])
						{
							$up_auto_first_leader_num += $value1;
						}
					}
					if ($up_auto_first_leader_num < $value['up_auto_first_leader_num'])
					{
						$upc_1 = 0;
					}
					else
					{
						$upc_1 = 1;
					}
				}
				else
				{
					$up_nost++;
				}
				if ($value['up_auto_second_leader_num'] > 0)
				{
					// 需要判断下一级人数，先看等级要求
					$up_auto_second_leader_num = 0;
					foreach ($sonList['level_2'] as $key2 => $value2)
					{
						if ($key2 >= $value['up_auto_second_leader_level'])
						{
							$up_auto_second_leader_num += $value2;
						}
					}
					if ($up_auto_second_leader_num < $value['up_auto_second_leader_num'])
					{
						$upc_2 = 0;
					}
					else
					{
						$upc_2 = 1;
					}
				}
				else
				{
					$up_nost++;
				}
				if ($value['up_auto_third_leader_num'] > 0)
				{
					// 需要判断下一级人数，先看等级要求
					$up_auto_third_leader_num = 0;
					foreach ($sonList['level_3'] as $key3 => $value3)
					{
						if ($key3 >= $value['up_auto_third_leader_level'])
						{
							$up_auto_third_leader_num += $value3;
						}
					}
					if ($up_auto_third_leader_num < $value['up_auto_third_leader_num'])
					{
						$upc_3 = 0;
					}
					else
					{
						$upc_3 = 1;
					}
				}
				else
				{
					$up_nost++;
				}
				if ($value['up_auto_team_num'] > 0)
				{
					// 需要判断下一级人数，先看等级要求
					$up_auto_team_num = 0;
					foreach ($sonList['level_0'] as $key0 => $value0)
					{
						if ($key0 >= $value['up_auto_team_level'])
						{
							$up_auto_team_num += $value0;
						}
					}
					if ($up_auto_team_num < $value['up_auto_team_num'])
					{
						$upc_0 = 0;
					}
					else
					{
						$upc_0 = 1;
					}
				}
				else
				{
					$up_nost++;
				}

				// 金额+等级 升级各条件：1成立，0不成立，2默认-无意义
				$upm_0 = 2; // 团队  金额+等级
				$upm_1 = 2; // 下一级金额+等级
				$upm_2 = 2; // 下二级金额+等级
				$upm_3 = 2; // 下三级金额+等级
				if ($value['up_auto_first_money'] > 0)
				{
					// 需要判断下一级金额，先看等级要求
					$up_auto_first_money = 0;
					foreach ($sonOrder['money_1'] as $keyd1 => $valued1)
					{
						if ($keyd1 >= $value['up_auto_first_money_level'])
						{
							$up_auto_first_money += $valued1;
						}
					}
					if ($up_auto_first_money < $value['up_auto_first_money'])
					{
						$upm_1 = 0;
					}
					else
					{
						$upm_1 = 1;
					}
				}
				else
				{
					$up_nost++;
				}
				if ($value['up_auto_second_money'] > 0)
				{
					// 需要判断下一级金额，先看等级要求
					$up_auto_second_money = 0;
					foreach ($sonOrder['money_2'] as $keyd2 => $valued2)
					{
						if ($keyd2 >= $value['up_auto_second_money_level'])
						{
							$up_auto_second_money += $valued2;
						}
					}
					if ($up_auto_second_money < $value['up_auto_second_money'])
					{
						$upm_2 = 0;
					}
					else
					{
						$upm_2 = 1;
					}
				}
				else
				{
					$up_nost++;
				}
				if ($value['up_auto_third_money'] > 0)
				{
					// 需要判断下一级金额，先看等级要求
					$up_auto_third_money = 0;
					foreach ($sonOrder['money_3'] as $keyd3 => $valued3)
					{
						if ($keyd3 >= $value['up_auto_third_money_level'])
						{
							$up_auto_third_money += $valued3;
						}
					}
					if ($up_auto_third_money < $value['up_auto_third_money'])
					{
						$upm_3 = 0;
					}
					else
					{
						$upm_3 = 1;
					}
				}
				else
				{
					$up_nost++;
				}
				if ($value['up_auto_team_money'] > 0)
				{
					// 需要判断下一级金额，先看等级要求
					$up_auto_team_money = 0;
					foreach ($sonOrder['money_0'] as $keyd0 => $valued0)
					{
						if ($keyd0 >= $value['up_auto_team_money_level'])
						{
							$up_auto_team_money += $valued0;
						}
					}
					if ($up_auto_team_money < $value['up_auto_team_money'])
					{
						$upm_0 = 0;
					}
					else
					{
						$upm_0 = 1;
					}
				}
				else
				{
					$up_nost++;
				}

				// 订单数量+等级 升级各条件：1成立，0不成立，2默认-无意义
				$upn_0 = 2; // 团队  金额+等级
				$upn_1 = 2; // 下一级金额+等级
				$upn_2 = 2; // 下二级金额+等级
				$upn_3 = 2; // 下三级金额+等级
				if ($value['up_auto_first_order'] > 0)
				{
					// 需要判断下一级金额，先看等级要求
					$up_auto_first_order = 0;
					foreach ($sonOrder['nums_1'] as $keyn1 => $valuen1)
					{
						if ($keyn1 >= $value['up_auto_first_order_level'])
						{
							$up_auto_first_order += $valuen1;
						}
					}
					if ($up_auto_first_order < $value['up_auto_first_order'])
					{
						$upn_1 = 0;
					}
					else
					{
						$upn_1 = 1;
					}
				}
				else
				{
					$up_nost++;
				}
				if ($value['up_auto_second_order'] > 0)
				{
					// 需要判断下一级金额，先看等级要求
					$up_auto_second_order = 0;
					foreach ($sonOrder['nums_2'] as $keyn2 => $valuen2)
					{
						if ($keyn2 >= $value['up_auto_second_order_level'])
						{
							$up_auto_second_order += $valuen2;
						}
					}
					if ($up_auto_second_order < $value['up_auto_second_order'])
					{
						$upn_2 = 0;
					}
					else
					{
						$upn_2 = 1;
					}
				}
				else
				{
					$up_nost++;
				}
				if ($value['up_auto_third_order'] > 0)
				{
					// 需要判断下一级金额，先看等级要求
					$up_auto_third_order = 0;
					foreach ($sonOrder['nums_3'] as $keyn3 => $valuen3)
					{
						if ($keyn3 >= $value['up_auto_third_order_level'])
						{
							$up_auto_third_order += $valuen3;
						}
					}
					if ($up_auto_third_order < $value['up_auto_third_order'])
					{
						$upn_3 = 0;
					}
					else
					{
						$upn_3 = 1;
					}
				}
				else
				{
					$up_nost++;
				}
				if ($value['up_auto_team_order'] > 0)
				{
					// 需要判断下一级金额，先看等级要求
					$up_auto_team_order = 0;
					foreach ($sonOrder['nums_0'] as $keyn0 => $valuen0)
					{
						if ($keyn0 >= $value['up_auto_team_order_level'])
						{
							$up_auto_team_order += $valuen0;
						}
					}
					if ($up_auto_team_order < $value['up_auto_team_order'])
					{
						$upn_0 = 0;
					}
					else
					{
						$upn_0 = 1;
					}
				}
				else
				{
					$up_nost++;
				}

				// 本人的订单总额，2默认无意义，0不成立，1成立
				$ups = 2;
				$orderSelf = get_user_total_money($user_id, '', $value['up_auto_goods'], $value['up_auto_order_type']);
				$up_auto_self_money = $orderSelf[0];
				if ($value['up_auto_self_money'] > 0)
				{
					if ($up_auto_self_money < $value['up_auto_self_money'])
					{
						$ups = 0;
					}
					else
					{
						$ups = 1;
					}
				}
				else
				{
					$up_nost++;
				}

				$autoUp = 0;    // 是否自动升级，1是，0否
				$up_auto_mode = $value['up_auto_mode']; // 升级方式，1满足其中一个即可，2全部满足
				if ($up_auto_mode == 1 && $value['level_ranking'] > $userInfo['level'])
				{
					if (
						$upc_0 == 1 || $upc_1 == 1 || $upc_2 == 1 || $upc_3 == 1 ||
						$upm_0 == 1 || $upm_1 == 1 || $upm_2 == 1 || $upm_3 == 1 ||
						$upn_0 == 1 || $upn_1 == 1 || $upn_2 == 1 || $upn_3 == 1 || $ups == 1
					)
					{
						if ($up_nost < 13)
						{
							$autoUp = 1;
						}
					}
				}

				if ($up_auto_mode == 2 && $value['level_ranking'] > $userInfo['level'])
				{
					if (
						$upc_0 >= 1 && $upc_1 >= 1 && $upc_2 >= 1 && $upc_3 >= 1 &&
						$upm_0 >= 1 && $upm_1 >= 1 && $upm_2 >= 1 && $upm_3 >= 1 &&
						$upn_0 >= 1 && $upn_1 >= 1 && $upn_2 >= 1 && $upn_3 >= 1 && $ups >= 1
					)
					{
						if ($up_nost < 13)
						{
							$autoUp = 1;
						}
					}
				}
				if ($autoUp == 1 && $value['level_ranking'] > $userInfo['level'])
				{
					change_level_log($user_id, $value['level_ranking'], '', "自动升级");
					recommend_level_up($user_id, $value['level_ranking']);
					break;
				}
			}

			// 用户可以降级，并且全部配置允许降级，并且当前等级要小于用户等级，才可以降级
			if ($value['down_level_by_auto'] == 1 && $down_level_by_auto_project && !$userInfo['only_up_level'] && $value['level_ranking'] < $userInfo['level'])
			{
				$nost = 0;    // 没有勾选数量
				$yest = 0;    // 勾选并成功的
				$dlvd_1 = 0;  // 自动降级，1是，0否，天-个人-订单量
				$dlvd_2 = 0;  // 自动降级，1是，0否，天-个人-订单总额
				$orderMoneyNumDays = array();
				if ($value['down_auto_self_order_num_1'] > 0 && $value['down_auto_self_order_days'] > 0)
				{
					// 对比x天内的订单数量
					$orderMoneyNumDays = get_user_order_money_num($user_id, 0, 0, $value['down_auto_self_order_days'], $value['down_auto_goods'], $value['down_auto_order_type']);
					if ($orderMoneyNumDays['num'] < $value['down_auto_self_order_num_1'])
					{
						$dlvd_1 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_1 = 1;
					$nost++;
				}

				if ($value['down_auto_self_money_num_1'] > 0 && $value['down_auto_self_money_days'] > 0)
				{
					// 对比x天内的订单总额
					if ($value['down_auto_self_money_days'] == $value['down_auto_self_order_days'] && $orderMoneyNumDays)
					{
						// 用原来的值 ，不再处理
					}
					else
					{
						$orderMoneyNumDays = get_user_order_money_num($user_id, 0, 0, $value['down_auto_self_money_days'], $value['down_auto_goods'], $value['down_auto_order_type']);
					}
					if ($orderMoneyNumDays['money'] < $value['down_auto_self_money_num_1'])
					{
						$dlvd_2 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_2 = 1;
					$nost++;
				}

				$dlvd_3 = 0;  // 自动降级，1是，0否，天-团队-订单量
				$dlvd_4 = 0;  // 自动降级，1是，0否，天-团队-订单总额
				$orderMoneyNumDays = array();
				if ($value['down_auto_team_order_num_1'] > 0 && $value['down_auto_team_order_days'] > 0)
				{
					// 对比x天内的订单数量-团队
					$orderMoneyNumDays = get_user_order_money_num($user_id, 1, 0, $value['down_auto_team_order_days'], $value['down_auto_goods'], $value['down_auto_order_type']);
					if ($orderMoneyNumDays['num'] < $value['down_auto_team_order_num_1'])
					{
						$dlvd_3 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_3 = 1;
					$nost++;
				}

				if ($value['down_auto_team_money_num_1'] > 0 && $value['down_auto_team_money_days'] > 0)
				{
					// 对比x天内的订单总额
					if ($value['down_auto_team_order_days'] == $value['down_auto_team_money_days'] && $orderMoneyNumDays)
					{
						// 用原来的值 ，不再处理
					}
					else
					{
						$orderMoneyNumDays = get_user_order_money_num($user_id, 1, 0, $value['down_auto_team_money_days'], $value['down_auto_goods'], $value['down_auto_order_type']);
					}
					if ($orderMoneyNumDays['money'] < $value['down_auto_team_money_num_1'])
					{
						$dlvd_4 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_4 = 1;
					$nost++;
				}

				$dlvd_5 = 0;  // 自动降级，1是，0否，周-个人-订单量
				$dlvd_6 = 0;  // 自动降级，1是，0否，周-个人-订单总额
				$orderMoneyNumDays = array();
				if ($value['down_auto_self_order_num_2'] > 0 && $value['down_auto_self_order_weeks'] > 0)
				{
					// 对比x天内的订单数量
					$orderMoneyNumDays = get_user_order_money_num($user_id, 0, 1, $value['down_auto_self_order_weeks'], $value['down_auto_goods'], $value['down_auto_order_type']);
					if ($orderMoneyNumDays['num'] < $value['down_auto_self_order_num_2'])
					{
						$dlvd_5 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_5 = 1;
					$nost++;
				}

				if ($value['down_auto_self_money_num_2'] > 0 && $value['down_auto_self_money_weeks'] > 0)
				{
					// 对比x天内的订单总额
					if ($value['down_auto_self_money_weeks'] == $value['down_auto_self_order_days'] && $orderMoneyNumDays)
					{
						// 用原来的值 ，不再处理
					}
					else
					{
						$orderMoneyNumDays = get_user_order_money_num($user_id, 0, 1, $value['down_auto_self_money_weeks'], $value['down_auto_goods'], $value['down_auto_order_type']);
					}
					if ($orderMoneyNumDays['money'] < $value['down_auto_self_money_num_2'])
					{
						$dlvd_6 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_6 = 1;
					$nost++;
				}

				$dlvd_7 = 0;  // 自动降级，1是，0否，周-团队-订单量
				$dlvd_8 = 0;  // 自动降级，1是，0否，周-团队-订单总额
				$orderMoneyNumDays = array();
				if ($value['down_auto_team_order_num_2'] > 0 && $value['down_auto_team_order_weeks'] > 0)
				{
					// 对比x天内的订单数量-团队
					$orderMoneyNumDays = get_user_order_money_num($user_id, 1, 1, $value['down_auto_team_order_weeks'], $value['down_auto_goods'], $value['down_auto_order_type']);
					if ($orderMoneyNumDays['num'] < $value['down_auto_team_order_num_2'])
					{
						$dlvd_7 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_7 = 1;
					$nost++;
				}

				if ($value['down_auto_team_money_num_2'] > 0 && $value['down_auto_team_money_weeks'] > 0)
				{
					// 对比x天内的订单总额
					if ($value['down_auto_team_order_weeks'] == $value['down_auto_team_money_weeks'] && $orderMoneyNumDays)
					{
						// 用原来的值 ，不再处理
					}
					else
					{
						$orderMoneyNumDays = get_user_order_money_num($user_id, 1, 1, $value['down_auto_team_money_weeks'], $value['down_auto_goods'], $value['down_auto_order_type']);
					}
					if ($orderMoneyNumDays['money'] < $value['down_auto_team_money_num_2'])
					{
						$dlvd_8 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_8 = 1;
					$nost++;
				}

				$dlvd_9 = 0;  // 自动降级，1是，0否，月-个人-订单量
				$dlvd_10 = 0;  // 自动降级，1是，0否，月-个人-订单总额
				$orderMoneyNumDays = array();
				if ($value['down_auto_self_order_num_3'] > 0 && $value['down_auto_self_order_months'] > 0)
				{
					// 对比x天内的订单数量
					$orderMoneyNumDays = get_user_order_money_num($user_id, 0, 2, $value['down_auto_self_order_months'], $value['down_auto_goods'], $value['down_auto_order_type']);

					if ($orderMoneyNumDays['num'] < $value['down_auto_self_order_num_3'])
					{
						$dlvd_9 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_9 = 1;
					$nost++;
				}

				if ($value['down_auto_self_money_num_3'] > 0 && $value['down_auto_self_money_months'] > 0)
				{
					// 对比x天内的订单总额
					if ($value['down_auto_self_money_months'] == $value['down_auto_self_order_days'] && $orderMoneyNumDays)
					{
						// 用原来的值 ，不再处理
					}
					else
					{
						$orderMoneyNumDays = get_user_order_money_num($user_id, 0, 2, $value['down_auto_self_money_months'], $value['down_auto_goods'], $value['down_auto_order_type']);
					}
					if ($orderMoneyNumDays['money'] < $value['down_auto_self_money_num_3'])
					{
						$dlvd_10 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_10 = 1;
					$nost++;
				}

				$dlvd_11 = 0;  // 自动降级，1是，0否，月-团队-订单量
				$dlvd_12 = 0;  // 自动降级，1是，0否，月-团队-订单总额
				$orderMoneyNumDays = array();
				if ($value['down_auto_team_order_num_3'] > 0 && $value['down_auto_team_order_months'] > 0)
				{
					// 对比x天内的订单数量-团队
					$orderMoneyNumDays = get_user_order_money_num($user_id, 1, 2, $value['down_auto_team_order_months'], $value['down_auto_goods'], $value['down_auto_order_type']);
					if ($orderMoneyNumDays['num'] < $value['down_auto_team_order_num_3'])
					{
						$dlvd_11 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_11 = 1;
					$nost++;
				}

				if ($value['down_auto_team_money_num_3'] > 0 && $value['down_auto_team_money_months'] > 0)
				{
					// 对比x天内的订单总额
					if ($value['down_auto_team_order_months'] == $value['down_auto_team_money_months'] && $orderMoneyNumDays)
					{
						// 用原来的值 ，不再处理
					}
					else
					{
						$orderMoneyNumDays = get_user_order_money_num($user_id, 1, 2, $value['down_auto_team_money_months'], $value['down_auto_goods'], $value['down_auto_order_type']);
					}
					if ($orderMoneyNumDays['money'] < $value['down_auto_team_money_num_3'])
					{
						$dlvd_12 = 1;
						$yest++;
					}
				}
				else
				{
					$dlvd_12 = 1;
					$nost++;
				}

				$autoDown = 0;    // 是否自动降级，1是，0否
				$down_auto_mode = $value['down_auto_mode']; // 降级方式，1满足其中一个即可，2全部满足
				if ($down_auto_mode == 1)
				{
					if (
						$dlvd_1 == 1 || $dlvd_2 == 1 || $dlvd_3 == 1 || $dlvd_4 == 1 ||
						$dlvd_5 == 1 || $dlvd_6 == 1 || $dlvd_7 == 1 || $dlvd_8 == 1 ||
						$dlvd_9 == 1 || $dlvd_10 == 1 || $dlvd_11 == 1 || $dlvd_12 == 1
					)
					{
						if ($yest < $nost)
						{
							// yest 表示勾选选项成功的数量，nost表示未勾选(成功)数量
							// 因为未勾选也表示成功，所以一定要未勾选的小于勾选成功的，才表示其中一个勾选是成功的。
							$autoDown = 1;
						}
					}
				}

				if ($down_auto_mode == 2)
				{
					if (
						$dlvd_1 == 1 && $dlvd_2 == 1 && $dlvd_3 == 1 && $dlvd_4 == 1 &&
						$dlvd_5 == 1 && $dlvd_6 == 1 && $dlvd_7 == 1 && $dlvd_8 == 1 &&
						$dlvd_9 == 1 && $dlvd_10 == 1 && $dlvd_11 == 1 && $dlvd_12 == 1
					)
					{
						if ($yest >= 1)
						{
							// yest 表示勾选选项成功的数量，nost表示未勾选(成功)数量
							// 至少要有一个勾选成功的
							$autoDown = 1;
						}
					}
				}
				if ($autoDown == 1)
				{
					change_level_log($user_id, $value['level_ranking'], '', "不符合当前等级，自动降级");
					break;
				}
			}

		}
	}
	return true;

}


/*
 * 更新某用户所有下级的等级
 * $user_id     本人ID
 * @zd
 * */
function update_son_level($user_id = 0, $type = 0)
{
	if (!$user_id)
	{
		return true;
	}
	if ($type > 0)
	{
		$where['floor'] = array("elt", $type);
	}
	$where['user_id'] = $user_id;
	$list = M("user_sons")->where($where)->field("id,son_user_id")->select();
	foreach ($list as $key => $value)
	{
		$newLevel = M("user")->where("id=" . $value['son_user_id'])->getField("level");
		$where_us['son_user_id'] = $value['son_user_id'];
		M("user_sons")->where($where_us)->setField("son_user_level", $newLevel);
	}
}

/*
 * 获取用户团队的等级和数量
 * $user_id     本人ID
 * $type        获取第x层，0全部下级，1前下一级，2前下二级，3前下三级。。。。
 * $upLevel     是否更新用户等级，1是，0否
 * @zd
 * */
function get_son_num_data($user_id = 0, $type = 1, $upLevel = 0)
{
	if (!$user_id)
	{
		return "";
	}
	if ($upLevel)
	{
		update_son_level($user_id, $type);
	}
	if ($type > 0)
	{
		$where['floor'] = array("elt", $type);
	}
	$uids = array();            // 所有用户
	$level_0 = array();         // 只有等级和数量，团队下等级对应的数量
	$level_1 = array();         // 下一级等级对应的数量
	$level_2 = array();         // 下二级等级对应的数量
	$level_3 = array();         // 下三级等级对应的数量

	$level_uids_0 = array();    // 只有等级和用户ID，整个团队
	$level_uids_1 = array();    // 下一级等级和UID
	$level_uids_2 = array();    // 下二级等级和UID
	$level_uids_3 = array();    // 下三级等级和UID

	$where['user_id'] = $user_id;
	$list = M("user_sons")->where($where)->select();
	foreach ($list as $key => $value)
	{
		$uids[] = $value['son_user_id'];
		$level_uids_0[$value['son_user_level']][] = $value['son_user_id'];
		$level_0[$value['son_user_level']] = $level_0[$value['son_user_level']] + 1;

		if ($value['floor'] == 1)
		{
			$level_uids_1[$value['son_user_level']][] = $value['son_user_id'];
			$level_1[$value['son_user_level']] = $level_1[$value['son_user_level']] + 1;
		}
		if ($value['floor'] == 2)
		{
			$level_uids_2[$value['son_user_level']][] = $value['son_user_id'];
			$level_2[$value['son_user_level']] = $level_2[$value['son_user_level']] + 1;
		}
		if ($value['floor'] == 3)
		{
			$level_uids_3[$value['son_user_level']][] = $value['son_user_id'];
			$level_3[$value['son_user_level']] = $level_3[$value['son_user_level']] + 1;
		}
	}
	$sonArr['uids'] = $uids;
	$sonArr['level_0'] = $level_0;
	$sonArr['level_1'] = $level_1;
	$sonArr['level_2'] = $level_2;
	$sonArr['level_3'] = $level_3;
	$sonArr['level_uids_0'] = $level_uids_0;
	$sonArr['level_uids_1'] = $level_uids_1;
	$sonArr['level_uids_2'] = $level_uids_2;
	$sonArr['level_uids_3'] = $level_uids_3;
	return $sonArr;
}

/*
 * 获取本人下级订单总量和总额
 * $user_id     用户ID
 * $type        0只获取三级内成员订单数组，1获取三级内和团队的订单数据  ????
 * $upLevel     是否更新用户等级，1是，0否
 * 可通过用户（获取下级用户）为条件获取订单数据
 * 如果有下级
 * 返回：
 *      uids    本人下x级的所有用户
 *      level   本人下x级的等级对应的用户数量
 *      level_uid   本人下x级的等级对应的用户id
 * @zd
 * */
function get_son_order_data($user_id = 0, $type = 0, $upLevel = 0, $goodsArr = array(), $cateArr = array())
{
	if (!$user_id)
	{
		return "";
	}
	if ($upLevel)
	{
		update_son_level($user_id, $type);
	}
	/*
	 * $orderData 数组说明：
	 * money 以元为依据的数组
	 * money_all 团队(元)数组，其中的key表示等级，其中的value表示金额
	 * money_first 下一级(元)数组，其中的key表示等级，其中的value表示金额
	 * money_second 下二级(元)数组，其中的key表示等级，其中的value表示金额
	 * money_third 下三级(元)数组，其中的key表示等级，其中的value表示金额
	 *
	 * nums 以订单数量为依据的数组
	 * nums_all 团队(订单量)数组，其中的key表示等级，其中的value表示金额
	 * nums_first 下一级(订单量)数组，其中的key表示等级，其中的value表示金额
	 * nums_second 下二级(订单量)数组，其中的key表示等级，其中的value表示金额
	 * nums_third 下三级(订单量)数组，其中的key表示等级，其中的value表示金额
	 *
	 * */
	/*
	 $orderData = array(
		'money_all' => array(
			'1'=>10,
			'0'=>23,
			'2'=>15,
		),
		'money_first' => array(
			'1'=>10,
			'0'=>23,
			'2'=>15,
		),
		'money_second' => array(
			'1'=>10,
			'0'=>23,
			'2'=>15,
		),
		'money_third' => array(
			'1'=>10,
			'0'=>23,
			'2'=>15,
		),

		'nums_all' => array(
			'1'=>10,
			'0'=>23,
			'2'=>15,
		),
		'nums_first' => array(
			'1'=>10,
			'0'=>23,
			'2'=>15,
		),
		'nums_second' => array(
			'1'=>10,
			'0'=>23,
			'2'=>15,
		),
		'nums_third' => array(
			'1'=>10,
			'0'=>23,
			'2'=>15,
		),
	);
	*/
	$money_all = array();
	$money_first = array();
	$money_second = array();
	$money_third = array();
	$nums_all = array();
	$nums_first = array();
	$nums_second = array();
	$nums_third = array();

	if ($type > 0)
	{
		$where['floor'] = array("elt", $type);
	}
	$where['user_id'] = $user_id;
	$list = M("user_sons")->where($where)->select();

	foreach ($list as $key => $value)
	{
		$orderCalc = get_user_total_money($value['son_user_id'], '', $goodsArr, $cateArr);
		$orderTotal = $orderCalc[0];
		$orderNums = $orderCalc[1];
		$money_all[$value['son_user_level']] = $money_all[$value['son_user_level']] + $orderTotal;
		$nums_all[$value['son_user_level']] = $nums_all[$value['son_user_level']] + $orderNums;

		if ($value['floor'] == 1)
		{
			$money_first[$value['son_user_level']] = $money_first[$value['son_user_level']] + $orderTotal;
			$nums_first[$value['son_user_level']] = $nums_first[$value['son_user_level']] + $orderNums;
		}
		if ($value['floor'] == 2)
		{
			$money_second[$value['son_user_level']] = $money_second[$value['son_user_level']] + $orderTotal;
			$nums_second[$value['son_user_level']] = $nums_second[$value['son_user_level']] + $orderNums;
		}
		if ($value['floor'] == 3)
		{
			$money_third[$value['son_user_level']] = $money_third[$value['son_user_level']] + $orderTotal;
			$nums_third[$value['son_user_level']] = $nums_third[$value['son_user_level']] + $orderNums;
		}
	}
	$orderData = array(
		'money_0' => $money_all,
		'money_1' => $money_first,
		'money_2' => $money_second,
		'money_3' => $money_third,

		'nums_0' => $nums_all,
		'nums_1' => $nums_first,
		'nums_2' => $nums_second,
		'nums_3' => $nums_third,
	);
	return $orderData;
}

/*
 * 获取某用户订单总额 和 订单总数量
 * 下标 0金额，1数量
 * @zd
 * */
function get_user_total_money($user_id = 0, $uidArr = array(), $goodsArr = '', $cateArr = '')
{
	$orderData = array(0, 0);    // 下标 0金额，1数量
	if (!$user_id && !$uidArr)
	{
		return 0;
	}
	if ($uidArr)
	{
		$where_ord['user_id'] = array("in", $uidArr);
	}
	else
	{
		$where_ord['user_id'] = $user_id;
	}
	$where_ord['isdel'] = 0;
	$where_ord['order_status'] = array("in", "1,2,3,5");
	if ($goodsArr)
	{
		$where_ord['goods_id'] = array("in", $goodsArr);
	}
	if ($cateArr)
	{
		$where_ord['order_type'] = array("in", $cateArr);
	}
	$orderTotal = M("order_goods")->where($where_ord)->sum("goods_actual_total");
	$orderNums = M("order_goods")->where($where_ord)->group("order_id")->count();

	$orderTotal = $orderTotal > 0 ? $orderTotal : 0;
	$orderNums = $orderNums > 0 ? $orderNums : 0;
	$orderData[0] = $orderTotal;
	$orderData[1] = $orderNums;
	return $orderData;
}

/*
 * 获取用户购买商品 及特殊分类ID
 * $type 0同时获取商品和分类，1获取商品ID，2获取分类ID
 * @zd
 * */
function get_user_buy_goods($user_id = 0, $type = 0)
{
	if (!$user_id)
	{
		return array();
	}
	$where['order_status'] = array("in", "1,2,3,5,8");
	$where['user_id'] = $user_id;
	$goodsID = M("order_goods")->where($where)->getField("goods_id", true);
	return $goodsID;
}

// 获取推荐关系链
// @zd
function get_recom_node($user_id = 0)
{
	$node = array();    // 下标 0 从顶级开始的推荐关系，下标 1 从本人开始的推荐关系
	if (!$user_id)
	{
		return $node;
	}
	$where['id'] = $user_id;
	$userInfo = get_user_info($where);
	if (!$userInfo || !$userInfo['first_leader'])
	{
		return $node;
	}
	$list = get_parent($user_id);
	$ids = array();
	foreach ($list as $key => $value)
	{
		$ids[] = $value['id'];
	}
	$max = implode("-", $ids);
	if ($max)
	{
		$max = $user_id . "-" . $max;
	}
	$node[1] = $max;

	sort($ids, SORT_NUMERIC);
	$min = implode("-", $ids);
	if ($min)
	{
		$min = $min . "-" . $user_id;
	}
	$node[0] = $min;
	return $node;
}

// 更新推荐关系链
// @zd
function update_recom_node($user_id = 0)
{
	if (!$user_id)
	{
		return true;
	}
	$recom_node = get_recom_node($user_id);
	if ($recom_node[0])
	{
		$data_u['recom_node_min'] = $recom_node[0];
	}
	if ($recom_node[1])
	{
		$data_u['recom_node_max'] = $recom_node[1];
	}
	if ($data_u)
	{
		M("user")->where("id=" . $user_id)->save($data_u);
	}
}

// 循环插入当前用户与所有上级关系及层级
// @wangshenbin
function user_sons_add($user_id, $level)
{
	$parent = get_parent($user_id);
	$up_ji = 0;
	foreach ($parent as $k => $v)
	{
		++$up_ji;
		$da['user_id'] = $v['id'];
		$da['son_user_id'] = $user_id;
		$da['son_user_level'] = $level;
		$da['floor'] = $up_ji;
		$date[] = $da;
	}
	M('user_sons')->addAll($date);
	return;
}

/*
 * 获取前x天、x周、x月内的订单总额或订单总量---自购+团队的
 * $user_id     用户ID，也可能是用户数组
 * $getTeam     是否获取团队的额度，1是，0否，当获取团队额度时，不再显示个人的
 * $type        类型，0天，1周，2月
 * $num         数值，前1.2.3天（不含当天），前1.2.3周（不含本周），前1.2月（不含本月）
 * @zd
 * */
function get_user_order_money_num($user_id = '', $getTeam = 0, $type = 0, $num = 0, $goodsArr = '', $cateArr = '')
{
	if (!$user_id || !$num)
	{
		return "";
	}
	if ($getTeam)
	{
		$uidArr = M("user_sons")->where("user_id=" . $user_id)->getField("son_user_id", true);
	}
	else
	{
		$uidArr = array($user_id);
	}
	if ($type == 1)
	{
		// 获取当前周
		$endTime = strtotime(date("Y-m-d", strtotime("this week")));
		$startTime = $endTime - $num * 7 * 86400;
	}
	elseif ($type == 2)
	{
		// 获取当前月
		$startTime = strtotime(date('Y-m-01 00:00:00', strtotime("-{$num} month")));
		$endTime = strtotime(date("Y-m-1"));
	}
	else
	{
		$endTime = strtotime(date("Y-m-d"));
		$startTime = $endTime - $num * 86400;
	}
	$where_ord['user_id'] = array("in", $uidArr);
	$where_ord['isdel'] = 0;
	$where_ord['order_status'] = array("in", "1,2,3,5");
	$where_ord['pay_time'] = array("between", array($startTime, $endTime));
	if ($goodsArr)
	{
		$where_ord['goods_id'] = array("in", $goodsArr);
	}
	if ($cateArr)
	{
		$where_ord['order_type'] = array("in", $cateArr);
	}
	$orderTotal = M("order_goods")->where($where_ord)->sum("goods_actual_total");
	$orderNums = M("order_goods")->where($where_ord)->group("order_id")->count();
	$orderTotal = $orderTotal > 0 ? $orderTotal : 0;
	$orderNums = $orderNums > 0 ? $orderNums : 0;
	return array('num' => $orderNums, 'money' => $orderTotal);

}


// M("config_user_level")->where($where_lv)->order("level_ranking desc")->select();


/*
 * 获取等级配置
 * $level       等级权重
 * $type        类型，0默认 第一个参数传等级权重，1 第1个参数传等级ID
 * @zd
 * */
function get_config_user_level($level = 0, $type = 0)
{
	$level = intval($level);
	if ($type == 1)
	{
		$data = M("config_user_level")->where("is_del=0 and status=1 and id=" . $level)->find();
	}
	else
	{
		$data = M("config_user_level")->where("is_del=0 and status=1 and level_ranking=" . $level)->find();
	}
	return $data;
}

/*
 * 推荐奖励提成
 * 升级后，上三级是否得提成
 * $user_id         用户ID
 * $newLevel        新升级等级
 * @zd
 * */
function recommend_level_up($user_id = 0, $newLevel = 0, $order_id = 0)
{
	if (!$user_id || !$newLevel)
	{
		return true;
	}
	$where['id'] = $user_id;
	$userInfo = get_user_info($where);
	if ($userInfo['level'] != $newLevel)
	{
		return true;
	}
	$userLevel = get_config_user_level($newLevel);
	if (!$userLevel['recommend_status'])
	{
		return true;
	}
	if ($userLevel['recommend_first_money'] > 0 && $userInfo['first_leader'] > 0)
	{
		change_money_log($userInfo['first_leader'], 1, "money", 0, $userLevel['recommend_first_money'], 1, 13, "会员" . get_phone_mask($userInfo['phone']) . "升级，提成一级奖励-余额", 1, 1, 1, $order_id, $user_id);
	}
	if ($userLevel['recommend_first_point'] > 0 && $userInfo['first_leader'] > 0)
	{
		change_money_log($userInfo['first_leader'], 1, "score", 0, $userLevel['recommend_first_point'], 16, 13, "会员" . get_phone_mask($userInfo['phone']) . "升级，提成一级奖励-积分", 1, 1, 1, $order_id, $user_id);
	}
	if ($userLevel['recommend_second_money'] > 0 && $userInfo['second_leader'] > 0)
	{
		if ($userLevel['recommend_level_diff'])
		{
			$userLevel['recommend_second_money'] = round($userLevel['recommend_second_money'] - $userLevel['recommend_first_money'], 2);
		}
		if ($userLevel['recommend_second_money'] > 0)
		{
			change_money_log($userInfo['second_leader'], 1, "money", 0, $userLevel['recommend_second_money'], 1, 14, "会员" . get_phone_mask($userInfo['phone']) . "升级，提成二级奖励-余额", 1, 1, 1, $order_id, $user_id);
		}
		else
		{
			$userLevel['recommend_second_money'] = 0;
		}
	}
	if ($userLevel['recommend_second_point'] > 0 && $userInfo['second_leader'] > 0)
	{
		if ($userLevel['recommend_level_diff'])
		{
			$userLevel['recommend_second_point'] = round($userLevel['recommend_second_point'] - $userLevel['recommend_first_point'], 2);
		}
		if ($userLevel['recommend_second_point'] > 0)
		{
			change_money_log($userInfo['second_leader'], 1, "score", 0, $userLevel['recommend_second_point'], 16, 14, "会员" . get_phone_mask($userInfo['phone']) . "升级，提成二级奖励-积分", 1, 1, 1, $order_id, $user_id);
		}
		else
		{
			$userLevel['recommend_second_point'] = 0;
		}
	}
	if ($userLevel['recommend_third_money'] > 0 && $userInfo['second_leader'] > 0)
	{
		if ($userLevel['recommend_level_diff'])
		{
			$userLevel['recommend_third_money'] = round($userLevel['recommend_third_money'] - $userLevel['recommend_first_money'] - $userLevel['recommend_second_money'], 2);
		}
		if ($userLevel['recommend_third_money'] > 0)
		{
			change_money_log($userInfo['third_leader'], 1, "money", 0, $userLevel['recommend_third_money'], 1, 15, "会员" . get_phone_mask($userInfo['phone']) . "升级，提成三级奖励-余额", 1, 1, 1, $order_id, $user_id);
		}
	}
	if ($userLevel['recommend_third_point'] > 0 && $userInfo['second_leader'] > 0)
	{
		if ($userLevel['recommend_level_diff'])
		{
			$userLevel['recommend_third_point'] = round($userLevel['recommend_third_point'] - $userLevel['recommend_first_point'] - $userLevel['recommend_second_point'], 2);
		}
		if ($userLevel['recommend_third_point'] > 0)
		{
			change_money_log($userInfo['third_leader'], 1, "score", 0, $userLevel['recommend_third_point'], 16, 15, "会员" . get_phone_mask($userInfo['phone']) . "升级，提成三级奖励-积分", 1, 1, 1, $order_id, $user_id);
		}
	}
	return true;
}


/*
 * 商城订单奖励，首购和复购
 * $order_id         订单ID
 * @zd
 *1
 * 这个方法目前没有再使用了，因为使用了预分销功能。
 *
 * */
function order_goods_award($order_id = 0)
{
	if (!$order_id)
	{
		return true;
	}
	$orderInfo = M("order")->where("id=" . $order_id)->find();
	if (!$orderInfo || $orderInfo['isdel'] == 1 || $orderInfo['order_status'] == 0 || $orderInfo['order_status'] == 4 || $orderInfo['order_status'] < 1)
	{
		return true;
	}

	$profit_space = $orderInfo['profit_space'];

	// 因为把 $profit_space 写入订单表了，所以此值不再需要获取了，而是取订单中的值
	// $profit_space = get_config_project("profit_space");    // 提成等收益处理（1支付后立即收益，2表示收货后，3表示收货7天后，4表示发货7天后）

	if (!in_array($profit_space, array(1, 2, 3, 4)))
	{
		$profit_space = 1;
	}
	if (intval($orderInfo["order_status"]) < 1)
	{
		return false;    // 非支付订单 不能处理
	}
	if ($orderInfo['is_profit_1'] == 1)
	{
		return false;    // 已提成，不再提成
	}
	if ($profit_space == 1 && $orderInfo["order_status"] != 1)
	{
		return false;
	}
	if ($profit_space == 2 && $orderInfo["order_status"] < 3)
	{
		return false;
	}
	// 收货7天后收益
	if ($profit_space == 3 && (($orderInfo["order_status"] != 3 && $orderInfo["order_status"] != 5) || $orderInfo["confirm_time"] + 86400 * 7 > time()))
	{
		return false;
	}
	// 发货7天后收益
	if ($profit_space == 4 && ($orderInfo["order_status"] != 2 || $orderInfo["shipping_time"] + 86400 * 7 > time()))
	{
		return false;
	}

	/*
	 * 判断是否开启 全局-购买奖励，单品-购买奖励
	 * 如果开启单品购买，那么奖励就按单个商品的奖励处理。
	 * 如果没有开启单品的奖励，那么 看看全局的奖励是否开启。
	 * 如果全局的开启，按全局的走。如果全局的没开。则不再提成.
	 * */
	$goods_award_buy = get_config_project("goods_award_buy");   // 购买奖励-单品
	$award_buy = get_config_project("award_buy");               // 购买奖励-全局

	if (!$goods_award_buy && !$award_buy)
	{
		return true;
	}
	$userInfo = array();
	$firstInfo = array();
	$secondInfo = array();
	$thirdInfo = array();

	$user_id = $orderInfo['user_id'];
	$where_u['id'] = $user_id;
	$userInfo = get_user_info($where_u);
	if (!$userInfo)
	{
		return true;
	}
	if ($userInfo['first_leader'] > 0)
	{
		$where_f['id'] = $userInfo['first_leader'];
		$firstInfo = get_user_info($where_f);
	}
	if ($userInfo['second_leader'] > 0)
	{
		$where_s['id'] = $userInfo['second_leader'];
		$secondInfo = get_user_info($where_s);
	}
	if ($userInfo['third_leader'] > 0)
	{
		$where_t['id'] = $userInfo['third_leader'];
		$thirdInfo = get_user_info($where_t);
	}

	$orderGoods = M("order_goods")->where("order_id=" . $order_id)->select();

	foreach ($orderGoods as $key => $value)
	{
		// 判断是单品奖励
		$self_money = 0;
		$self_point = 0;
		$first_money = 0;
		$first_point = 0;
		$second_money = 0;
		$second_point = 0;
		$third_money = 0;
		$third_point = 0;

		$isFirst = is_first_or_seond($user_id, $value['goods_id']); // 是否首次购买，1首购，2复购
		if ($goods_award_buy)
		{
			$where_ga['goods_id'] = $value['goods_id'];
			$where_ga['goods_spec_price_id'] = $value['goods_spec_price_id'];
			$awardInfo = M("goods_award")->where($where_ga)->find();

			if (!$awardInfo || $awardInfo['award_status'] == 0)
			{
				if ($isFirst == 2)
				{
					// 复购返佣开启
					$levelInfo = get_config_user_level($userInfo['level']);
					if ($levelInfo['repeat_buy_award_status'])
					{
						$award_mode = $levelInfo['repeat_buy_award_mode'];
						$self_money = abs($levelInfo['repeat_buy_award_self_money']);
						$self_point = abs($levelInfo['repeat_buy_award_self_point']);
					}
					$levelInfo = get_config_user_level($firstInfo['level']);
					if ($firstInfo && $firstInfo['status'] == 1 && $firstInfo['is_del'] == 0 && $levelInfo['repeat_buy_award_status'])
					{
						$award_mode_1 = $levelInfo['repeat_buy_award_mode'];
						$first_money = abs($levelInfo['repeat_buy_award_first_money']);
						$first_point = abs($levelInfo['repeat_buy_award_first_point']);
					}
					$levelInfo = get_config_user_level($secondInfo['level']);
					if ($secondInfo && $secondInfo['status'] == 1 && $secondInfo['is_del'] == 0 && $levelInfo['repeat_buy_award_status'])
					{
						$award_mode_2 = $levelInfo['repeat_buy_award_mode'];
						$second_money = abs($levelInfo['repeat_buy_award_second_money']);
						$second_point = abs($levelInfo['repeat_buy_award_second_point']);
					}
					$levelInfo = get_config_user_level($thirdInfo['level']);
					if ($thirdInfo && $thirdInfo['status'] == 1 && $thirdInfo['is_del'] == 0 && $levelInfo['repeat_buy_award_status'])
					{
						$award_mode_3 = $levelInfo['repeat_buy_award_mode'];
						$third_money = abs($levelInfo['repeat_buy_award_third_money']);
						$third_point = abs($levelInfo['repeat_buy_award_third_point']);
					}

				}
				else
				{
					// 首购返佣开启
					$levelInfo = get_config_user_level($userInfo['level']);
					if ($levelInfo['buy_award_status'])
					{
						$award_mode = $levelInfo['buy_award_mode'];
						$self_money = abs($levelInfo['buy_award_self_money']);
						$self_point = abs($levelInfo['buy_award_self_point']);
					}
					if ($firstInfo && $firstInfo['status'] == 1 && $firstInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
					{
						$award_mode_1 = $levelInfo['buy_award_mode'];
						$first_money = abs($levelInfo['buy_award_first_money']);
						$first_point = abs($levelInfo['buy_award_first_point']);
					}
					if ($secondInfo && $secondInfo['status'] == 1 && $secondInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
					{
						$award_mode_2 = $levelInfo['buy_award_mode'];
						$second_money = abs($levelInfo['buy_award_second_money']);
						$second_point = abs($levelInfo['buy_award_second_point']);
					}
					if ($thirdInfo && $thirdInfo['status'] == 1 && $thirdInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
					{
						$award_mode_3 = $levelInfo['buy_award_mode'];
						$third_money = abs($levelInfo['buy_award_third_money']);
						$third_point = abs($levelInfo['buy_award_third_point']);
					}
				}
			}
			else
			{
				$award_mode = $awardInfo['award_mode'];
				if ($isFirst == 2)
				{
					$self_money = abs($awardInfo['self_money_repeat']);
					$self_point = abs($awardInfo['self_point_repeat']);
				}
				else
				{
					$self_money = abs($awardInfo['self_money']);
					$self_point = abs($awardInfo['self_point']);
				}
				$self_money = round($self_money * $value['goods_num'], 2);
				$self_point = round($self_point * $value['goods_num'], 2);

				if ($firstInfo && $firstInfo['status'] == 1 && $firstInfo['is_del'] == 0)
				{
					if ($isFirst == 2)
					{
						$first_money = abs($awardInfo['first_money_repeat']);
						$first_point = abs($awardInfo['first_point_repeat']);
					}
					else
					{
						$first_money = abs($awardInfo['first_money']);
						$first_point = abs($awardInfo['first_point']);
					}
					$first_money = round($first_money * $value['goods_num'], 2);
					$first_point = round($first_point * $value['goods_num'], 2);
				}
				if ($secondInfo && $secondInfo['status'] == 1 && $secondInfo['is_del'] == 0)
				{
					if ($isFirst == 2)
					{
						$second_money = abs($awardInfo['second_money_repeat']);
						$second_point = abs($awardInfo['second_point_repeat']);
					}
					else
					{
						$second_money = abs($awardInfo['second_money']);
						$second_point = abs($awardInfo['second_point']);
					}
					$second_money = round($second_money * $value['goods_num'], 2);
					$second_point = round($second_point * $value['goods_num'], 2);
				}
				if ($thirdInfo && $thirdInfo['status'] == 1 && $thirdInfo['is_del'] == 0)
				{
					if ($isFirst == 2)
					{
						$third_money = abs($awardInfo['third_money_repeat']);
						$third_point = abs($awardInfo['third_point_repeat']);
					}
					else
					{
						$third_money = abs($awardInfo['third_money']);
						$third_point = abs($awardInfo['third_point']);
					}
					$third_money = round($third_money * $value['goods_num'], 2);
					$third_point = round($third_point * $value['goods_num'], 2);
				}
			}

		}
		elseif ($award_buy)
		{
			if ($isFirst == 2)
			{
				// 复购返佣开启
				$levelInfo = get_config_user_level($userInfo['level']);
				if ($levelInfo['repeat_buy_award_status'])
				{
					$award_mode = $levelInfo['repeat_buy_award_mode'];
					$self_money = abs($levelInfo['repeat_buy_award_self_money']);
					$self_point = abs($levelInfo['repeat_buy_award_self_point']);
				}
				$levelInfo = get_config_user_level($firstInfo['level']);
				if ($firstInfo && $firstInfo['status'] == 1 && $firstInfo['is_del'] == 0 && $levelInfo['repeat_buy_award_status'])
				{
					$award_mode_1 = $levelInfo['repeat_buy_award_mode'];
					$first_money = abs($levelInfo['repeat_buy_award_first_money']);
					$first_point = abs($levelInfo['repeat_buy_award_first_point']);
				}
				$levelInfo = get_config_user_level($secondInfo['level']);
				if ($secondInfo && $secondInfo['status'] == 1 && $secondInfo['is_del'] == 0 && $levelInfo['repeat_buy_award_status'])
				{
					$award_mode_2 = $levelInfo['repeat_buy_award_mode'];
					$second_money = abs($levelInfo['repeat_buy_award_second_money']);
					$second_point = abs($levelInfo['repeat_buy_award_second_point']);
				}
				$levelInfo = get_config_user_level($thirdInfo['level']);
				if ($thirdInfo && $thirdInfo['status'] == 1 && $thirdInfo['is_del'] == 0 && $levelInfo['repeat_buy_award_status'])
				{
					$award_mode_3 = $levelInfo['repeat_buy_award_mode'];
					$third_money = abs($levelInfo['repeat_buy_award_third_money']);
					$third_point = abs($levelInfo['repeat_buy_award_third_point']);
				}

			}
			else
			{
				// 首购返佣开启
				$levelInfo = get_config_user_level($userInfo['level']);
				if ($levelInfo['buy_award_status'])
				{
					$award_mode = $levelInfo['buy_award_mode'];
					$self_money = abs($levelInfo['buy_award_self_money']);
					$self_point = abs($levelInfo['buy_award_self_point']);
				}
				if ($firstInfo && $firstInfo['status'] == 1 && $firstInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
				{
					$award_mode_1 = $levelInfo['buy_award_mode'];
					$first_money = abs($levelInfo['buy_award_first_money']);
					$first_point = abs($levelInfo['buy_award_first_point']);
				}
				if ($secondInfo && $secondInfo['status'] == 1 && $secondInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
				{
					$award_mode_2 = $levelInfo['buy_award_mode'];
					$second_money = abs($levelInfo['buy_award_second_money']);
					$second_point = abs($levelInfo['buy_award_second_point']);
				}
				if ($thirdInfo && $thirdInfo['status'] == 1 && $thirdInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
				{
					$award_mode_3 = $levelInfo['buy_award_mode'];
					$third_money = abs($levelInfo['buy_award_third_money']);
					$third_point = abs($levelInfo['buy_award_third_point']);
				}
			}
		}

		if ($award_mode == 1)
		{
			$self_money = round($self_money / 100 * $value['goods_actual_total'], 2);
			$self_point = round($self_point / 100 * $value['goods_actual_total'], 2);
		}
		if ($award_mode_1 == 1)
		{
			$first_money = round($first_money / 100 * $value['goods_actual_total'], 2);
			$first_point = round($first_point / 100 * $value['goods_actual_total'], 2);
		}
		if ($award_mode_2 == 1)
		{
			$second_money = round($second_money / 100 * $value['goods_actual_total'], 2);
			$second_point = round($second_point / 100 * $value['goods_actual_total'], 2);
		}
		if ($award_mode_3 == 1)
		{
			$third_money = round($third_money / 100 * $value['goods_actual_total'], 2);
			$third_point = round($third_point / 100 * $value['goods_actual_total'], 2);
		}

		if ($self_money > 0)
		{
			$res_1 = change_money_log($userInfo['id'], 1, "money", 0, $self_money, 1, 16, "商品自购提成-余额", 1, 1, 1, $order_id, $userInfo['id']);
		}
		else
		{
			$res_1 = true;
		}
		if ($self_point > 0)
		{
			$res_2 = change_money_log($userInfo['id'], 1, "score", 0, $self_point, 6, 16, "商品自购提成-积分", 1, 1, 1, $order_id, $userInfo['id']);
		}
		else
		{
			$res_2 = true;
		}
		if ($res_1 && $res_2)
		{
			updateSonPorfit($order_id, $value['id'], 1);
		}

		if ($first_money > 0)
		{
			change_money_log($firstInfo['id'], 1, "money", 0, $first_money, 1, 17, "下一级{$userInfo['phone']}消费提成-余额", 1, 1, 1, $order_id, $userInfo['id']);
		}
		if ($first_point > 0)
		{
			change_money_log($firstInfo['id'], 1, "score", 0, $first_point, 16, 17, "下一级{$userInfo['phone']}消费提成-积分", 1, 1, 1, $order_id, $userInfo['id']);
		}
		if ($second_money > 0)
		{
			change_money_log($secondInfo['id'], 1, "money", 0, $second_money, 1, 18, "下二级{$userInfo['phone']}消费提成-余额", 1, 1, 1, $order_id, $userInfo['id']);
		}
		if ($second_point > 0)
		{
			change_money_log($secondInfo['id'], 1, "score", 0, $second_point, 16, 18, "下二级{$userInfo['phone']}消费提成-积分", 1, 1, 1, $order_id, $userInfo['id']);
		}
		if ($third_money > 0)
		{
			change_money_log($thirdInfo['id'], 1, "money", 0, $third_money, 1, 19, "下三级{$userInfo['phone']}消费提成-余额", 1, 1, 1, $order_id, $userInfo['id']);
		}
		if ($third_point > 0)
		{
			change_money_log($thirdInfo['id'], 1, "score", 0, $third_point, 16, 19, "下三级{$userInfo['phone']}消费提成-积分", 1, 1, 1, $order_id, $userInfo['id']);
		}
	}

}


/*
 * 判断用户是首购，还是复购（分单品复购，全站复购）
 * $user_id         用户ID
 * $goods_id        商品ID
 * 返回：
 *      1 首购
 *      2 复购
 * @zd
 * */
function is_first_or_seond($user_id = 0, $goods_id = 0)
{
	if (!$user_id)
	{
		return 1;
	}
	$type = get_config_project("repeat_buy_type");
	$type = $type ? $type : 1;
	if ($type == 1)
	{
		if ($goods_id)
		{
			$where['goods_id'] = $goods_id;
		}
		else
		{
			return 1;
		}
	}
	else
	{
		// 商品大于0，表示有商品订单
		$where['goods_id'] = array("gt", 0);
	}
	$where['user_id'] = $user_id;
	$where['pay_status'] = 1;
	$where['order_status'] = array("in", "1,2,3,5,7,8");
	$isHave = M("order_goods")->where($where)->count();
	if ($isHave <= 1)
	{
		return 1;
	}
	else
	{
		return 2;
	}

}

/**
 * Notes:字符串截取 防止中文乱码
 * User: WangSong
 * Date: 2019/11/19
 * Time: 16:51
 * @param $str
 * @param int $start
 * @param $length
 * @param string $charset
 * @param bool $suffix
 * @return false|string
 */
function zh_substr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
	switch ($charset)
	{
		case 'utf-8':
			$char_len = 3;
			break;
		case 'UTF8':
			$char_len = 3;
			break;
		default:
			$char_len = 2;
	}
	//小于指定长度，直接返回
	if (strlen($str) <= ($length * $char_len))
	{
		return $str;
	}
	if (function_exists("mb_substr"))
	{
		$slice = mb_substr($str, $start, $length, $charset);
	}
	else
	{
		if (function_exists('iconv_substr'))
		{
			$slice = iconv_substr($str, $start, $length, $charset);
		}
		else
		{
			$re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re[$charset], $str, $match);
			$slice = join("", array_slice($match[0], $start, $length));
		}
	}
	if ($suffix)
	{
		return $slice;
	}
	return $slice;
}

/*
* 授权获取手机号，解密相关的数据
* @zd
* */
function wxDataDecrypt($encryptedData, $iv, $sessionKey)
{
	$aesKey = base64_decode($sessionKey);
	$aesIV = base64_decode($iv);
	$aesCipher = base64_decode($encryptedData);
	$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
	$data = json_decode($result, true);
	return $data;
}

/*
* 授权获取手机号，解密相关的数据
* @zd
* */
function getLiveStatus($status = 0)
{
	// 直播状态 101: 直播中, 102: 未开始, 103: 已结束, 104: 禁播, 105: 暂停中, 106: 异常, 107: 已过期
	switch ($status)
	{
		case 101:
			$statusCN = "直播中";
			break;
		case 102:
			$statusCN = "未开始";
			break;
		case 103:
			$statusCN = "已结束";
			break;
		case 104:
			$statusCN = "禁播";
			break;
		case 105:
			$statusCN = "暂停中";
			break;
		case 106:
			$statusCN = "异常";
			break;
		case 107:
			$statusCN = "已过期";
			break;
		default:
			$statusCN = "已结束";
	}
	return $statusCN;
}


// 获取所有下级
// @zd
function get_sons($user_id = 0)
{
	$subs = array();
	$field = "id,phone,level,first_leader,second_leader,third_leader,status,is_del,money,money_wait,score";
	$list = M("user")->where("first_leader=" . $user_id)->field($field)->select();

	foreach ($list as $item)
	{
		$item['child'] = get_sons($item['id']);
		$subs[] = $item;
	}
	return $subs;
}

// 获取所有下级:推荐关系  --- tree 树形插件使用
function get_sons_tree($user_id = 0)
{
	$subs = array();
	$field = "id,level,phone,realname";
	$list = M("user")->where("first_leader=" . $user_id)->field($field)->select();

	foreach ($list as $item)
	{
		$item['children'] = get_sons_tree($item['id']);
		$item['title'] = "ID:" . $item['id'] . "（手机:{$item['phone']}）（姓名:{$item['realname']}）（等级:" . user_level_name($item['level']) . "） ";
		$subs[] = $item;
	}
	return $subs;
}


/**
 * Title：表格导出
 * Note：按模板中的参数导出
 * @param array/string          $queryParam         查询条件
 * @param string $order 排序方式
 * @param string $limit 输出数量限制
 * @param int $templateId 模板ID
 * @param string $fileName 文件名，不含文件格式后缀
 * @param bool $isDown 是否下载，1是，0否
 * User： zd
 * Date： 2020/6/13 14:32
 */
function exportExcel($queryParam = '', $order = '', $limit = '', $templateId = 0, $fileName = '', $isDown = true)
{
	vendor("PHPExcel.PHPExcel");
	$savePath = './upload/export/';
	$obj = new \PHPExcel();

	if (!$templateId)
	{
		return _dat('导出错误，请联系技术');
	}
	// 给定导出模板ID，然后拿到导出的列名字，以及列对应的数据
	$templateInfo = M("export_template")->where("id=" . $templateId)->find();
	if (!$templateInfo)
	{
		return _dat('没有对应的导出模板，请先设置');
	}
	$cellTitle = json_decode($templateInfo['field_cn'], true);   // 表格头
	$_cnt = count($cellTitle);
	if ($_cnt > 50)
	{
		return _dat('导出列名超过50个，导出失败');
	}

	// 处理相应的导出数据 $cellData
	$cellData = get_export_data($templateInfo, $queryParam, $order, $limit);

	if ($cellData['err'] > 0)
	{
		return _dat($cellData['msg']);
	}

	//横向单元格标识，共52个
	$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
	$_row = 1;   //设置纵向单元格标识
	$obj->getActiveSheet(0)->setTitle('Sheet1-' . date('Y-md-Hi'));   //设置sheet名称

	// 循环列名
	if ($cellTitle)
	{
		$i = 0;
		foreach ($cellTitle AS $v)
		{
			//设置列标题
			$obj->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $_row, $v);
			$i++;
		}
		$_row++;
	}
	//填写数据
	if ($cellData)
	{
		$i = 0;
		foreach ($cellData AS $_v)
		{
			$j = 0;
			foreach ($_v AS $_cell)
			{
				if (is_numeric($_cell) && strlen($_cell) > 10)
				{
					// 长度大于10的数字，按字符串输出，不以科学计数处理
					$obj->getActiveSheet(0)->setCellValueExplicit($cellName[$j] . ($i + $_row), $_cell, PHPExcel_Cell_DataType::TYPE_STRING);
				}
				else
				{
					$obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + $_row), $_cell);
				}
				$j++;
			}
			$i++;
		}
	}
	//文件名处理
	if (!$fileName)
	{
		$fileName = date("Y-m-d") . "_" . time();
	}
	$fileName = $fileName . '.xlsx';
	$objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
	$_savePath = $savePath . $fileName;
	$objWrite->save($_savePath);
	if ($isDown)
	{
		//网页下载
		header('pragma:public');
		header("Content-Disposition:attachment;filename=$fileName");
		$objWrite->save('php://output');
		exit;
	}
	return _dat('导出表格', 0, $_savePath);
}

/**
 * Title：获取导出数据
 * Note：
 * @param array $queryParam 查询条件
 * @param array $order 排序方式
 * @param array $templateInfo 模板数据信息
 * User： zd
 * Date： 2020/6/15 10:51
 */
function get_export_data($templateInfo = array(), $queryParam = '', $order = '', $limit = '')
{
	if (!$templateInfo)
	{
		return _dat('模板信息异常，请联系技术或重新设置导出模板');
	}
	if (!$templateInfo['field_en'])
	{
		return _dat('模板设置错误，请联系技术或重新设置导出模板');
	}

	$fieldArr = json_decode($templateInfo['field_en'], true);
	$where['is_del'] = 0;
	$where['type'] = $templateInfo['type'];
	$where['id'] = array("in", $fieldArr);
	$fieldList = M('export_fields')->order('sorting asc')->where($where)->select();

	// 找到相应使用的字段后，把需要导出的字段组合一个数组，再把需要关联的字段组合一个数组
	$fieldArr = array();        // 字段数组
	$fieldRelation = array();   // 关联数组
	$fieldTable = "";           // 表名
	foreach ($fieldList as $key => $value)
	{
		if (!$fieldTable)
		{
			$fieldTable = $value['table_name'];
		}
		$value['field_en_alias'] = trim($value['field_en_alias']);
		if ($value['field_en_alias'] && strlen($value['field_en_alias']) >= 1)
		{
			$fieldArr[] = $value['field_en'] . " as " . $value['field_en_alias'];
		}
		else
		{
			$fieldArr[] = $value['field_en'];
		}

		if ($value['is_relation'])
		{
			$relArr = array();
			$relArr['is_relation'] = $value['is_relation'];
			if ($value['field_en_alias'])
			{
				$field_en = $value['field_en_alias'];
			}
			else
			{
				$field_en = $value['field_en'];
			}
			$relArr['field_en'] = $field_en;
			$relArr['relation_table_name'] = $value['relation_table_name'];
			$relArr['relation_field_en'] = $value['relation_field_en'];
			$relArr['relation_condition_field_en'] = $value['relation_condition_field_en'];
			$fieldRelation[$field_en] = $relArr;
		}
	}

	$fieldStr = implode(",", $fieldArr);
	if (!$limit || $limit <= 0)
	{
		$list = M($fieldTable)->where($queryParam)->field($fieldStr)->order($order)->select();
	}
	else
	{
		$list = M($fieldTable)->where($queryParam)->field($fieldStr)->order($order)->limit($limit)->select();
	}

	foreach ($list as $key2 => &$value2)
	{
		foreach ($fieldRelation as $key3 => $value3)
		{
			$relationData = relation_change_to($value3['is_relation'], $value3['relation_table_name'], $value3['relation_condition_field_en'], $value3['relation_field_en'], $value2[$key3]);
			$value2[$key3] = $relationData;
		}
	}
	return $list;
}


/**
 * Title：把关联的字段，改为实际想要的信息
 * Note： 如果type=2表示调用了方法，那么为了简便，这个方法只能是接受一个参数。并且 $condition_field和$field是用不到的。
 * @param int $type 类型，是否关联，0否，1关联表，2关联方法，3时间戳转换成格式化日期，4格式化日期转为日间戳
 * @param string $table 表名/方法名
 * @param string $condition_field 关联表的条件字段
 * @param string $field 关联表的信息字段
 * @param string $data 原始信息
 * User： zd
 * Date： 2020/6/15 15:24
 */
function relation_change_to($type = 0, $table = '', $condition_field = '', $field = '', $data = '')
{
	if ($type == 4)
	{
		if ($data)
		{
			return strtotime($data);
		}
		else
		{
			return "";
		}
	}
	elseif ($type == 3)
	{
		if ($data)
		{
			return date("Y-m-d H:i:s", $data);
		}
		else
		{
			return "";
		}
	}
	elseif ($type == 2)
	{
		// 这里涉及到具体的某个字段的中文转换
		return $table($data);

	}
	elseif ($type == 1)
	{
		$where[$condition_field] = $data;
		$data = M($table)->where($where)->getField($field);
		return $data ? $data : '';
	}
	else
	{
		return $data;
	}
}


/**
 * Title：导出模板的类型
 * Note： 如果传入值，则只返回对应中文，如果不传入值，则返回全部的值
 * @param $type
 * User： zd
 * Date： 2020/6/16 10:32
 */
function get_export_template_type($type)
{
	$typeArr = array(
		1 => "用户",
		2 => "商家",
		3 => "代理",
		4 => "订单",
		5 => "商品",
		6 => "金额变动日志",
		7 => "提现日志"
	);
	if ($type > 0)
	{
		return $typeArr[$type];
	}
	else
	{
		return $typeArr;
	}
}

/**
 * Title：导出字段的关联类型
 * Note： 传入值，则只返回对应中文
 * @param $type
 * User： zd
 * Date： 2020/6/16 10:32
 */
function get_export_field_relation($num = 0)
{
	$relationArr = array(
		0 => "无",
		1 => "关联表",
		2 => "关联方法",
		3 => "格式化时间",
		4 => "转时间戳",
	);
	return $relationArr[$num];
}


/**
 * Title： 获取性别的
 * Note：
 * @param $num
 * User： zd
 * Date： 2020/6/20 14:42
 */
function get_sex_cn($num)
{
	if ($num == 1)
	{
		$str = "男";
	}
	elseif ($num == 2)
	{
		$str = "女";
	}
	else
	{
		$str = "女";
	}
	return $str;
}

/**
 * Title： 把头像的链接补全
 * Note：
 * User： zd
 * Date： 2020/6/20 15:59
 */
function full_header_img_url($img)
{
	return full_pre_url($img);
}


/**
 * Title：获取商家状态
 * Note：
 * @param $status
 * @return data
 * User： zd
 * Date： 2020/6/22 15:25
 */
function get_shop_status_cn($status)
{
	if ($status == 1)
	{
		$str = "正常";
	}
	elseif ($status == 2)
	{
		$str = "冻结";
	}
	elseif ($status == 3)
	{
		$str = "审核中";
	}
	elseif ($status == 0)
	{
		$str = "审核中";
	}
	else
	{
		$str = "无";
	}
	return $str;
}


/**
 * Title：金额变动类型
 * Note：
 * @param $num
 * User： zd
 * Date： 2020/6/22 17:39
 */
function get_op_type_cn($num)
{
	if ($num == 1)
	{
		$str = "增加";
	}
	elseif ($num == 2)
	{
		$str = "减少";
	}
	else
	{
		$str = "";
	}
	return $str;
}


/**
 * Note： 获取交易类型
 * User： zd
 * Date： 2020/5/2 15:16
 */
function get_deal_type_cn($num = 0)
{
	$typeArr = array(
		0 => "",
		1 => "充值",
		2 => "商城消费",
		3 => "买单",
		4 => "报单",
		5 => "提现",
		6 => "互转",
		7 => "系统操作",
		8 => "提现返还",
		9 => "签到",
		10 => "提成",
		11 => "定时返还",
		12 => "退款",
		13 => "一级升级提成",
		14 => "二级升级提成",
		15 => "三级升级提成",
		16 => "自身消费提成",
		17 => "一级升级提成",
		18 => "二级升级提成",
		19 => "三级升级提成",
	);
	return $typeArr[$num];
}


/**
 * Title：获取用户类型
 * Note：
 * @param $num
 * User： zd
 * Date： 2020/6/22 18:34
 */
function get_utype_cn($num)
{
	if ($num == 1)
	{
		$str = "用户";
	}
	elseif ($num == 2)
	{
		$str = "商家";
	}
	elseif ($num == 3)
	{
		$str = "代理";
	}
	elseif ($num == 4)
	{
		$str = "平台";
	}
	else
	{
		$str = "";
	}
	return $str;
}


/**
 * Title： 获取提现状态
 * Note：
 * @param $num
 * User： zd
 * Date： 2020/6/23 8:52
 */
function get_tx_check_cn($num)
{
	if ($num == 1)
	{
		$str = "提现通过";
	}
	elseif ($num == 2)
	{
		$str = "提现拒绝";
	}
	elseif ($num == 3)
	{
		$str = "处理中";
	}
	else
	{
		$str = "未审核";
	}
	return $str;
}


/**
 * Title： 获取提现账户类型
 * Note：
 * @param $num
 * User： zd
 * Date： 2020/6/23 8:52
 */
function get_bank_type_cn($num)
{
	if ($num == 1)
	{
		$str = "支付宝";
	}
	elseif ($num == 2)
	{
		$str = "微信";
	}
	else
	{
		$str = "银行";
	}
	return $str;
}

//商家订单状态
function get_shop_order_status_cn($order_status)
{
	if ($order_status == '0')
	{
		return "待支付";
	}
	elseif ($order_status == 1)
	{
		return "待发货";
	}
	elseif ($order_status == '2')
	{
		return "已发货";
	}
	elseif ($order_status == '3')
	{
		return "已收货";
	}
	elseif ($order_status == '4')
	{
		return "已取消";
	}
	elseif ($order_status == '5')
	{
		return "已评价";
	}
	elseif ($order_status == '6')
	{
		return "已售后";
	}
	elseif ($order_status == '8')
	{
		return "快递打印中";
	}
	elseif ($order_status == -1)
	{
		return "申请售后";
	}
	elseif ($order_status == -2)
	{
		return "同意售后";
	}
	elseif ($order_status == -3)
	{
		return "商家拒绝";
	}
	elseif ($order_status == -4)
	{
		return "买家邮寄";
	}
	elseif ($order_status == -5)
	{
		return "商家收货";
	}
	elseif ($order_status == -6)
	{
		return "商家回寄";
	}
	elseif ($order_status == -7)
	{
		return "买家收货";
	}
	elseif ($order_status == -8)
	{
		return "退款";
	}
	elseif ($order_status == -9)
	{
		return "售后完成";
	}
	else
	{
		return "未知状态";
	}
}


/**
 * Title：更新是否收益的状态
 * Note：先更新子订单，再检测更新主订单
 * @param int $order_id 主订单ID
 * @param int $order_goods_id 子订单ID
 * @param int $type 更新类型，1用户，2商家，3代理
 * User： zd
 * Date： 2020/7/2 12:11
 */
function updateSonPorfit($order_id = 0, $order_goods_id = 0, $type = 1)
{
	if (!$order_id)
	{
		return false;
	}
	if ($type == 2)
	{
		$field = "is_profit_2";
	}
	elseif ($type == 3)
	{
		$field = "is_profit_3";
	}
	else
	{
		$field = "is_profit_1";
	}
	if ($order_goods_id)
	{
		$res = M("order_goods")->where("id=" . $order_goods_id)->setField($field, 1);
		if ($res !== false)
		{
			// 看看主订单是否需要更新
			$where['order_id'] = $order_id;
			$where[$field] = 0;
			$res_1 = M("order_goods")->where($where)->count();
			if ($res_1 < 1)
			{
				M("order")->where("id=" . $order_id)->setField($field, 1);
			}
		}
	}
	else
	{
		M("order")->where("id=" . $order_id)->setField($field, 1);
		M("order_goods")->where("order_id=" . $order_id)->setField($field, 1);
	}


}


/**
 * Title： 为你推荐
 * Note：根据商品ID，找到同商家、同类目下的商品，默认6条，按销量排序
 * @param int $goods_id
 * @param int $num
 * User： zd
 * Date： 2020/7/8 9:25
 */
function get_recommend_goods($goods_id = 0, $num = 6)
{
	if (!$goods_id || $goods_id <= 0)
	{
		return array();
	}
	$where['id'] = $goods_id;
	$where['is_group'] = 0;
	$where['is_hour'] = 0;
	$goodsInfo = M("goods")->where($where)->field("cat_id,sell_count,shop_id")->find();
	if (!$goodsInfo)
	{
		return array();
	}
	$where = "is_on_sale=1 and isdel=0 and is_group=0 and is_hour=0 and id !=" . $goods_id . " and cat_id=" . $goodsInfo['cat_id'];
	$field = "id,name,subhead,price,thumb";
	$list = M("goods")->where($where)->limit(0, $num)->field($field)->order("sell_count desc")->select();
	return $list;
}


/**
 * Title：用户单个信息美化输出
 * Note：主要在商品评价 输出用户的手机号、名称时，遮挡部分敏感信息
 * @param string $data 传入的信息（用户数组）
 * @param int $data_type 要获取信息类型，1手机，2用户名称（姓名、昵称、等），3头像图片补全链接
 * @param int $type 美化类型，0不美化，1宽松美化（手机遮挡中间4拉，名称只显示前后一个），2严格美化（手机只保留前后一位，名称只显示前后一个）
 * User： zd
 * Date： 2020/7/9 14:47
 *
 */
function get_user_info_beauty($data = '', $data_type = 1, $type = 0)
{
	if (!$data)
	{
		return "";
	}
	if ($data_type == 1)
	{
		if ($data['phone'])
		{
			if ($type == 1)
			{
				return get_phone_mask($data['phone'], 1);
			}
			elseif ($type == 2)
			{
				return get_phone_mask($data['phone'], 2);
			}
			else
			{
				return $data['phone'];
			}
		}
		else
		{
			return "****";
		}
	}
	elseif ($data_type == 2)
	{
		// $name 可能是微信昵称，也可能是手机号，也可能是姓名
		$name = $data['nickname'] ? $data['nickname'] : $data['username'];
		$name = $name ? $name : $data['realname'];
		if (is_numeric($name) && strlen($name) > 10)
		{
			if ($type == 1)
			{
				return get_phone_mask($name, 1);
			}
			elseif ($type == 2)
			{
				return get_phone_mask($name, 2);
			}
			else
			{
				return $name;
			}
		}
		else
		{
			if ($type >= 1)
			{
				$str_1 = mb_substr($name, 0, 1, 'utf-8');
				$str_2 = mb_substr($name, -1, 1, 'utf-8');
				return $str_1 . '***' . $str_2;
			}
			else
			{
				return $name;
			}
		}
	}
	elseif ($data_type == 3)
	{
		if (!$data['headimgurl'])
		{
			$data['headimgurl'] = "/Public/images/profile.png";
		}
		$img = full_pre_url($data['headimgurl']);
		return $img;
	}

}


/**
 * Title：添加预分销日志
 * Note：
 * @param array $userInfo 用户信息数组
 * @param array $orderInfo 订单信息数组
 * User： zd
 * Date： 2020/7/10 21:10
 *
 *  要计算 son_floor
 *  另外 updateSonPorfit 发如何处理？
 *
 */
function add_distribution_log($logInfo = array())
{
	if (!$logInfo || !$logInfo['user_id'])
	{
		return false;
	}
	if (!$logInfo['type'] || !$logInfo['deal_type'] || !$logInfo['op_type'])
	{
		return false;
	}
	if (!$logInfo['order_id'] || $logInfo['goods_price'] <= 0 || $logInfo['goods_num'] <= 0 || $logInfo['goods_total'] <= 0)
	{
		return false;
	}
	if (!$logInfo['profit_space'] || !$logInfo['order_cre_time'])
	{
		return false;
	}
	if ($logInfo['user_id'] && $logInfo['son_user_id'])
	{
		$where_son['user_id'] = $logInfo['user_id'];
		$where_son['son_user_id'] = $logInfo['son_user_id'];
		$floor = M("user_sons")->where($where_son)->getField("floor");
		$floor = $floor ? $floor : 0;
	}
	else
	{
		$floor = 0;
	}
	$logInfo['son_floor'] = $floor;
	$res = M("distribution_log")->add($logInfo);
	if ($res)
	{
		return true;
	}
	else
	{
		return false;
	}

}


/**
 * Title：从预分销表中读取、写入用户奖励
 * Note：
 * @param array $orderInfo
 * User： zd
 * Date： 2020/7/13 9:19
 */
function order_goods_award_by_distribution_log($orderInfo = array())
{
	if (!$orderInfo)
	{
		return true;
	}
	$order_id = $orderInfo['id'];
	if (!$order_id || $order_id <= 0)
	{
		return true;
	}
	if (!$orderInfo || $orderInfo['isdel'] == 1 || $orderInfo['order_status'] == 0 || $orderInfo['order_status'] == 4 || $orderInfo['order_status'] < 1)
	{
		return true;
	}

	$profit_space = $orderInfo['profit_space'];

	if (!in_array($profit_space, array(1, 2, 3, 4)))
	{
		$profit_space = 1;
	}
	if (!is_numeric($orderInfo["order_status"]) || $orderInfo["order_status"] < 1 || $orderInfo["order_status"] == 4 || $orderInfo["order_status"] == 6)
	{
		return true;    // 非支付订单 不能处理
	}
	if ($profit_space == 1 && $orderInfo["order_status"] != 1)
	{
		return true;
	}
	if ($profit_space == 2 && $orderInfo["order_status"] < 3)
	{
		return true;
	}
	// 收货7天后收益
	if ($profit_space == 3 && (($orderInfo["order_status"] != 3 && $orderInfo["order_status"] != 5) || $orderInfo["confirm_time"] + 86400 * 7 > time()))
	{
		return true;
	}
	// 发货7天后收益
	if ($profit_space == 4 && ($orderInfo["order_status"] != 2 || $orderInfo["shipping_time"] + 86400 * 7 > time()))
	{
		return false;
	}

	$where['order_id'] = $order_id;
	$where['is_profit'] = 0;
	$list = M("distribution_log")->where($where)->select();
	foreach ($list as $key => $value)
	{
		change_money_log($value['user_id'], 1, $value['money_field'], 0, $value['money'], $value['type'], $value['deal_type'], $value['remark'], $value['op_type'], $value['utype'], 1, $value['order_id'], $value['son_user_id'], $value['id']);
	}
	// 查询一下各角色是否有未处理完的收益，如果都处理完成，则更新订单表的收益状态
	$where_u['utype'] = 1;
	$where_u['is_profit'] = 0;
	$have_u1 = M("distribution_log")->where($where_u)->count();
	if ($have_u1 == 0)
	{
		updateSonPorfit($order_id,0,1);
	}
	$where_u['utype'] = 2;
	$where_u['is_profit'] = 0;
	$have_u2 = M("distribution_log")->where($where_u)->count();
	if ($have_u2 == 0)
	{
		updateSonPorfit($order_id,0,2);
	}
	$where_u['utype'] = 3;
	$where_u['is_profit'] = 0;
	$have_u3 = M("distribution_log")->where($where_u)->count();
	if ($have_u3 == 0)
	{
		updateSonPorfit($order_id,0,3);
	}

}
    // 获取微信token
    function get_wxtoken()
    {
        $wxdata = M('config_pay3')->where('type=4')->find();
        $u = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$wxdata['appid']."&secret=".$wxdata['key2'];
        $rs = file_get_contents($u);
        if ($rs)
        {
            $rs_arr = json_decode($rs, true);
            if ($rs_arr['access_token'])
            {
                return $rs_arr['access_token'];
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

?>