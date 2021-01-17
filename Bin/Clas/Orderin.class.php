<?php

namespace Clas;

use Think\Image;
use  Clas\ExpressApi;

/**
 * 所有订单类信息展示模块
 */
class Orderin
{

	private $user_id;

	public function __construct($user_id)
	{
		$this->user_id = $user_id;
	}

	/**
	 * 生成订单
	 * @return  array     订单详细信息数据
	 * @author  史超
	 * 说明：生成订单
	 */
	public function makeOrder()
	{
		$order_date_json = htmlspecialchars_decode(I('json_date'));
		$order_date = json_decode($order_date_json, true);

		$user_note = I('user_note');//买家留言
		if (!is_array($user_note))
		{
			$user_note1 = htmlspecialchars_decode($user_note);
			$user_note = json_decode($user_note1, true);
		}
		$goods_kind = I('goods_kind', 0);            // 0常规，1拼团，2整点秒杀，3限时购
        //进行判断 id相关就修改goods_kind
        if($order_date['goods'][0]['goods'][0]['goods_id'] == 746  ){
            $goods_kind = 10;
            $level_ranking = 1;
        }elseif($order_date['goods'][0]['goods'][0]['goods_id'] == 747 ){
            $level_ranking = 2;
            $goods_kind = 10;
        }elseif($order_date['goods'][0]['goods'][0]['goods_id'] == 749 ){
            $level_ranking = 3;
            $goods_kind = 10;
        }
		switch ($goods_kind)
		{
			case '1':
				$order_type = 5;
				break;
			case '2':
				$order_type = 6;
				break;
			case '3':
				$order_type = 7;
				break;
            case '10':
                $order_type = 12;
                break;
			default:
				$order_type = 0;
				break;
		}
		// $order_type // 5团购订单，6秒杀，7限时购
		$group_order_id = I('group_order_id', 0);    // 团购订单主ID
		$group_order_id = $group_order_id ? $group_order_id : 0;
		if ($group_order_id > 0)
		{
			//拼团时间检测
			$end_time = M('order')->where('id =' . $group_order_id)->getField('group_time_end');
			if ($end_time < time())
			{
				return "拼团时间已过";
			}
		}

		$address_id = I('address_id');
		if ($address_id)
		{
			$address_date = M('user_address')->where('id=' . $address_id)->find();
		}
		if (!$address_date)
		{
			return "请选择收货地址";
		}
		$goods_date = $order_date['goods'];
		if (!$goods_date)
		{
			return "请选择购买商品";
		}

		// 自提点和优惠券
		$picksite_id = I('picksite_id', 0);            // 自提点ID，如果有值的话，则无运费
		$user_coupon_id = I('user_coupon_id', 0);                // 手动选择的优惠券ID
		$user_coupon_id_auto = I('user_coupon_id_auto', 0);   // 平台自动为订单选择优惠券ID
		$UCInfo = get_coupon_info($user_coupon_id, 2);
		$UCAInfo = get_coupon_info($user_coupon_id_auto, 2);

		if ($user_coupon_id && $user_coupon_id_auto && ($UCInfo['same_time'] == 0 || $UCAInfo['same_time'] == 0))
		{
			return "优惠券和自动满减不可同时使用";
		}
		$couponGoodsArr = array();    // 商品数组 -- 为优惠券用

		$model = M();
		$model->startTrans();

		$order_no = make_order_no();
		$total_express_fee = 0;            // 总快递费用
		$total_commodity_price = 0;        // 总订单费用，包括快递
		$total_leader_fee = 0;             // 拼团发起人总优惠金额
		$actual_order_total = 0;           // 商品总价，只是商品

		$goods = array();
		$cartIdArr = array();
		$err = "";    // 下架商品提示
		$err2 = "";    // 库存不足提示
		$err3 = "";    // 运费规则不允许提示
		$err6 = "";    // 秒杀商品错误提示

		$n = 0;
		// 检测自提点的有效性
		if ($picksite_id)
		{
			$pickSiteInfo = M("goods_picksite")->where("is_del=0 and id=" . $picksite_id)->find();
			if (!$pickSiteInfo)
			{
				return "自提点不存在，请更换";
			}
		}

		$profit_space = get_config_project("profit_space");    // 提成收益得到的时间，1下单后，2收货后，3收货7天后

		foreach ($goods_date as $k => $v)
		{
			$goodsList = $v['goods'];
			foreach ($goodsList as $k1 => $v1)
			{
				$gField = "isdel,is_on_sale,id,store_count,name,price,shipping_fee,product_type";
				$goodsInfo = M("goods")->where("id=" . $v1["goods_id"])->field($gField)->find();

				$product_type = $goodsInfo['product_type'];//支付时数组中的商品类型应该是一致的
				// 对秒杀的商品判断
				if ($goods_kind == 2 || $goods_kind == 3)
				{
					$hourInfo = M("goods_hour")->where("goods_id=" . $v1["goods_id"])->find();
					if (!$hourInfo)
					{
						return "【" . $v1['goods_name'] . "】秒杀期限已过，请选购其他商品";
					}

					if ($hourInfo['start_time'] > time())
					{
						return "【" . $v1['goods_name'] . "】秒杀未开始，请选购其他商品";
					}
					if ($hourInfo['end_time'] < time())
					{
						return "【" . $v1['goods_name'] . "】秒杀已结束，请选购其他商品";
					}
				}

				// 没有自提点时，检测运费
				if (!$picksite_id)
				{
					// 运费规则检测
					$checkArr = check_express($v1["goods_id"], $address_date['city']);
					if ($checkArr[1] == 1)
					{
						$err3 .= $v1['goods_name'] . " ";
					}
				}

				$CGA['id'] = $v1["goods_id"];
				$CGA['spec_id'] = $v1["goods_spec_price_id"];
				$CGA['num'] = $v1["goods_num"];
				$couponGoodsArr[] = $CGA;

				if (!$goodsInfo || $goodsInfo["isdel"] == 1 || $goodsInfo["is_on_sale"] < 1)
				{
					$err .= $v1['goods_name'] . " ";
				}
				if ($v1['goods_spec_price_id'])
				{
					// 有规格
					$gspInfo = M("goods_spec_price")->where("id=" . $v1['goods_spec_price_id'])->find();
					if (!$gspInfo)
					{
						$err .= $v1['goods_name'] . " ";
					}
					if ($gspInfo['store_count'] < 1)
					{
						$err2 .= $v1['goods_name'] . " ";
					}
					switch ($goods_kind)
					{
						case '1':
							$gprice = $gspInfo["group_price"];
							break;
						case '2':
							$gprice = $gspInfo["hour_price"];
							break;
						case '3':
							$gprice = $gspInfo["time_price"];
							break;

						default:
							$gprice = $gspInfo["price"];
							break;
					}
					$purchasePrice = $gspInfo['purchase_price'];
				}
				else
				{
					// 无规格
					if ($goodsInfo['store_count'] < 1)
					{
						$err2 .= $v1['goods_name'] . " ";
					}
					$gprice = $goodsInfo['price'];
					$purchasePrice = $goodsInfo['purchase_price'];
				}
				// 转正数
				$gprice = abs($gprice);
				$v1['goods_num'] = abs($v1['goods_num']);

				$team_leader_free = 0;
				$team_leader_free_money = 0;
				if ($order_type == 5)
				{
					//拼团限制检测
					$group = M('goods_group')->where('is_open = 1 and goods_id =' . $v1["goods_id"])->find();
					if ($group['team_goods_num'] > 0)
					{//拼团数量检测
						if ($v1['goods_num'] > $group['team_goods_num'])
						{
							$err2 .= "数量超出限制";
							$model->rollback();
							return $err2;
						}
					}
					if ($group_order_id == 0)
					{
						//拼团发起人优惠 x元
						$team_leader_free = $group['team_leader_free'];
						if ($group['team_leader_free'] == 1)
						{
							$team_leader_free_money = $gprice;
						}
						else
						{
							if ($group['team_leader_free'] == 2)
							{
								$team_leader_free_money = $group['team_leader_free_money'];
							}
							else
							{
								$team_leader_free_money = 0;
							}
						}
					}
					else
					{
						$good_count = M('order_goods')->where(array('order_id' => $group_order_id, 'goods_id' => $v1["goods_id"]))->find();
						if (!$good_count)
						{
							$model->rollback();
							return '团购发起订单不存在，请重新选择发起订单';
						}
						$user_ids = M('order')->where(array('group_order_id' => $group_order_id))->getField('user_id', true);
						$user_id = I('user_id');
						if ($user_ids && in_array($user_id, $user_ids))
						{
							$model->rollback();
							return '此团购已参与';
						}
					}
				}

				// 如果有自提点ID，则无运费
				if ($picksite_id)
				{
					$one_express_fee = 0;
				}
				else
				{
					$one_express_fee = goods_express_fee($v1['goods_id'], abs($v1['goods_num']), $v1['goods_spec_price_id'], $address_date['city']);
					$total_express_fee = round($total_express_fee + $one_express_fee, 2);
				}

				$gprice_leader_fee = round($team_leader_free_money * $v1['goods_num'], 2);//拼团发起人优惠
				$gprice_total = round($gprice * $v1['goods_num'] - $gprice_leader_fee, 2);    // 商品总价= 价格-拼团发起人优惠
				$actual_order_total = $actual_order_total + $gprice_total;    // 主订单使用，只含商品的总价

				$total_commodity_price = round($total_commodity_price + $gprice_total , 2);
				$total_leader_fee = $total_leader_fee + $gprice_leader_fee;
				$total_commodity_price = abs($total_commodity_price);
				$total_leader_fee = abs($total_leader_fee);

				$goods[$n]['goods_id'] = $v1['goods_id'];
				$goods[$n]['goods_name'] = $v1['goods_name'];
				$goods[$n]['goods_thumb'] = $v1['goods_thumb'];
				$goods[$n]['goods_price'] = abs($gprice);
				$goods[$n]['goods_num'] = abs($v1['goods_num']);
				$goods[$n]['goods_total'] = abs($gprice_total);
				$goods[$n]['shop_id'] = $v1['shop_id'];
				$goods[$n]['leader_free'] = $team_leader_free;                  // 发起人（团长）是否免费，0否，1是，2优惠部分额度
				$goods[$n]['goods_leader_money'] = $team_leader_free_money;     // 单个商品优惠金额
				$goods[$n]['goods_total_leader_money'] = $gprice_leader_fee;    // 总优惠金额
				$goods[$n]['goods_spec_price_id'] = $v1['goods_spec_price_id'];
				$goods[$n]['goods_purchase_price'] = abs($purchasePrice);
				$goods[$n]['goods_actual_total'] = abs($gprice_total);
				$goods[$n]['profit_space'] = $profit_space;
				$goods[$n]['is_profit'] = 0;
				$goods[$n]['user_note'] = $user_note[$k];
				$cartIdArr[] = $v1["car_id"];
				$n++;
			}
		}
        //只加一次运费
        $one_express_fee =  M("express_rule")->where("id=1" )->find();
        $total_commodity_price = $total_commodity_price+$one_express_fee['first_price'];
		if ($picksite_id)
		{
			$picksite_code = substr(time(), 1, 8);    // 自提码
		}
		else
		{
			$picksite_code = "";
		}
		// 检测出来该用户的所有可用优惠券，然后对比传递过来的是否可用
		$userCoupon = check_goods_use_coupon($this->user_id, $couponGoodsArr);
		if (!in_array($user_coupon_id, $userCoupon['ucid']) && $user_coupon_id > 0)
		{
			$err5 = $UCInfo['name'] . " 已使用.";
			$model->rollback();
			return $err5;
		}
		if (!in_array($user_coupon_id_auto, $userCoupon['ucid']) && $user_coupon_id_auto > 0)
		{
			$err5 = $UCAInfo['name'] . " 已使用";
			$model->rollback();
			return $err5;
		}
		$is_use_coupon = 0;
		if ($user_coupon_id || $user_coupon_id_auto)
		{
			$is_use_coupon = 1;
		}
		$user_coupon_money = round($UCInfo['money'], 2) > 0 ? round($UCInfo['money'], 2) : 0;
		$user_coupon_auto_money = round($UCAInfo['money'], 2) > 0 ? round($UCAInfo['money'], 2) : 0;

		$total_commodity_price = round($total_commodity_price - $user_coupon_money - $user_coupon_auto_money, 2);

		// 判断最终金额
		if ($total_commodity_price <= 0 && ($user_coupon_id || $user_coupon_id_auto))
		{
			$err5 = "金额太低，不可使用优惠券";
			$model->rollback();
			return $err5;
		}

		if (abs($total_commodity_price) <= 0)
		{
			$err = "订单金额小于0，请确认是否正常";
			$model->rollback();
			return $err;
		}

		if ($picksite_id)
		{
			$shipping_type = 2;
		}
		else
		{
			$shipping_type = 1;
		}


		//进行判断订单状态  判断是普通商城的商品还是积分商城的商品  只需判断一个商品的类型即可

		if ($product_type == 1)
		{
			$order_type = 11;//积分订单
            $re = M('user')->where('id=' . $this->user_id)->setInc('wait_score', $total_commodity_price);
            if (!$re)
            {
                return array(1, "操作失败");
            }
		}


		

		$total_express_fee  = $one_express_fee['first_price'];
		if($total_commodity_price - $total_express_fee >= 99){
            $total_commodity_price = $total_commodity_price - $total_express_fee;
            $total_express_fee = 0;
        }
		$makeOrderData = array(
			'order_no' => $order_no,
			'user_id' => $this->user_id,
			'total_commodity_price' => $total_commodity_price,
			'total_express_fee' => $total_express_fee,
			'user_note' => '',
			'phone' => $address_date['phone'],
			'consignee' => $address_date['consignee'],
			'province' => $address_date['province'],
			'city' => $address_date['city'],
			'district' => $address_date['district'],
			'address' => $address_date['address'],
			'order_type' => $order_type,
			'add_time' => time(),
			'ip' => get_client_ip(),
			'level_ranking' => $level_ranking,
			'shipping_type' => $shipping_type,
			'picksite_id' => $picksite_id,
			'picksite_code' => $picksite_code,
			'is_use_coupon' => $is_use_coupon,                          // 是否使用优惠券
			'user_coupon_id' => $user_coupon_id,                        // 优惠券ID
			'user_coupon_money' => $user_coupon_money,                  // 优惠金额
			'user_coupon_id_auto' => $user_coupon_id_auto,              // 自动满减优惠券ID
			'user_coupon_auto_money' => $user_coupon_auto_money,        // 优惠金额
			'total_leader_money' => $total_leader_fee,                  // 拼团发起人优惠
			'actual_order_total' => $actual_order_total,                // 商品总价，无运费
			'profit_space' => $profit_space,                            // 商品总价，无运费
			'is_profit' => 0,                                           // 是否已经提成，1是，0否
		);
		$order_id = M('order')->add($makeOrderData);

		//如果是自提的订单 加入订单自提数据表

		if ($picksite_id)
		{
			$order_picksite = array(
				'user_id' => $this->user_id,
				'order_id' => $order_id,
				'picksite_id' => $picksite_id,
				'shop_id' => 0,
				'pick_date' => I('pick_date'),
				'pick_time' => I('pick_time'),
				'cre_time' => time(),
				'ip' => get_client_ip(),
				'status' => 0,
			);
			M('order_picksite')->add($order_picksite);
		}


		coupon_sign($order_id);    // 标注优惠券已使用

		// 一个商品，并且 是 拼团属性
		if ($order_type == 5)
		{
			$source = 1;
			$this->setGroupOrder($order_id, $goods[0]["goods_id"], $group_order_id, $source);
		}
		//插入订单日志
		$goods_name = '';
		for ($i = 0; $i < count($goods); $i++)
		{
			$goods_name .= $goods[$i]["goods_name"] . "，数量为：" . $goods[$i]["goods_num"] . "，";
		}
		add_order_log($order_id, "创建订单", '商品为' . $goods_name);
		$this->addOrderGoods($goods, $order_id, $order_no, $goods_kind);
		if ($err)
		{
			$err .= " 已下架，请选购其他商品";
			$model->rollback();
			return $err;
		}
		if ($err2)
		{
			$err2 .= " 库存不足，请选购其他商品";
			$model->rollback();
			return $err2;
		}
		if ($err3)
		{
			$err3 .= " 无可用运费规则，请联系客服";
			$model->rollback();
			return $err3;
		}

		foreach ($goods as $k => $v)
		{
			$goods_id = $v["goods_id"];
			$user_id = $this->user_id;
			M("cart")->where("user_id=" . $user_id . " and goods_id=" . $goods_id)->delete();
		}

		// 生成预分销日志
//		$this->make_distribution_log($order_id);

		$model->commit();
		$cartNum = implode("", $cartIdArr);
		$cartNum = intval($cartNum);

		if ($cartIdArr && $cartNum > 0)
		{
			// 数组有值，并且拆分后值大于0，即表示有真实的购物车ID，然后删除相应购物车的记录
			$where_cart["id"] = array("in", $cartIdArr);
			M("cart")->where($where_cart)->delete();
		}

		return array(
			'order_id' => $order_id,
			'order_no' => $order_no,
			'total_commodity_price' => $total_commodity_price,
			'order_type' => $order_type,
			'title' => '弹出支付方式'
		);
	}

	/**
	 * 订单商品，按商品发货，订单表为主表，该表为发货/物流等操作表
	 * @return  array     订单详细信息数据
	 * @author  史超
	 * 说明：生成订单
	 */
	public function addOrderGoods($goods, $order_id = 0, $order_no, $goods_kind = 0)
	{
		if ($order_id && $order_no)
		{

			foreach ($goods as $key => &$value)
			{
				if ($value["goods_spec_price_id"])
				{
					$specInfo = M("goods_spec_price")->where("id=" . $value["goods_spec_price_id"])->find();
					if ($specInfo)
					{
						$goods_spec_price_id = $value["goods_spec_price_id"];
						$goods_spec_price_name = $specInfo["key_name"];
						// $spec_price = $specInfo["price"];
						switch ($goods_kind)
						{
							case '1':
								$spec_price = $specInfo["group_price"];
								break;
							case '2':
								$spec_price = $specInfo["hour_price"];
								break;
							case '3':
								$spec_price = $specInfo["time_price"];
								break;

							default:
								$spec_price = $specInfo["price"];
								break;
						}
					}
					else
					{
						$goods_spec_price_id = 0;
						$goods_spec_price_name = "";
						$spec_price = M("goods")->where("id=" . $value["goods_id"])->getField("price");
					}
				}
				else
				{
					$goods_spec_price_id = 0;
					$goods_spec_price_name = "";
					$spec_price = M("goods")->where("id=" . $value["goods_id"])->getField("price");
				}
				$value["goods_spec_price_id"] = $goods_spec_price_id;
				$value["goods_spec_price_name"] = $goods_spec_price_name;

				$value["order_id"] = $order_id;
				$value["order_no"] = $order_no;
				$value["goods_total"] = round($spec_price * $value["goods_num"], 2);
				$value["add_time"] = time();
				$value["user_id"] = $this->user_id;
			}

			$goods = array_values($goods);
			M('order_goods')->addAll($goods);
		}
		else
		{
			M('order_goods')->add($goods);
		}
	}

	/**
	 * 支付操作
	 * @return  array     支付
	 * @author  史超
	 * 说明：支付订单
	 */
	public function payOrder($data = array())
	{
		_error_log('---payOrder---11--');
		_error_log($data);
		if ($data)
		{
			$order_id = $data['order_id'];
			$pay_type = $data['pay_type'];
		}
		else
		{
			$order_id = I('order_id');
			$pay_type = I('pay_type');
		}
		if (!$order_id)
		{
			return array(1, "缺少订单ID");
		}
		if (!$pay_type)
		{
			return array(1, "缺少支付方式");
		}
		$order = M('order')->where('id=' . $order_id)->find();
		$order_goods = M('order_goods')->where('order_id=' . $order_id)->select();
		$goods_name = '';
		foreach ($order_goods as $k => $v)
		{
			$goods_spec_price = M('goods_spec_price')->where('id=' . $v['goods_spec_price_id'])->find();
			if ($goods_spec_price['store_count'] < $v['goods_num'])
			{
				$goods_name .= '商品：' . $v['goods_name'] . ",";
			}
		}
		if ($goods_name)
		{
			return array(1, $goods_name . '库存不足，暂不能支付');
		}
		if (round($order['total_commodity_price'], 2) <= 0)
		{
			return array(1, "支付金额须大于0元");
		}
		if (!$order || $order["isdel"] == 1)
		{
			return array(1, "订单异常，无法支付，请联系客服");
		}
		if ($order['pay_status'])
		{
			return array(1, "订单已经支付");
		}
		if ($pay_type == 'yepay')
		{
			$res_pay = $this->ye_pay($order_id);
			if ($res_pay[0] < 1)
			{
				// 成功的结果
				$res = $this->pay_success($order_id, $pay_type);
				return array($res[0], $res[1]);
			}
			else
			{
				// 失败的结果
				return array($res_pay[0], $res_pay[1]);
			}
		}elseif($pay_type == 'price'){
            $res_pay = $this->price_pay($order_id);
            if ($res_pay[0] < 1)
            {
                // 成功的结果
                $res = $this->pay_success($order_id, $pay_type);
                return array($res[0], $res[1]);
            }
            else
            {
                // 失败的结果
                return array($res_pay[0], $res_pay[1]);
            }
        }elseif($pay_type == 'price_pay'){
            $res_pay = $this->price_pay1($order_id);
            if ($res_pay[0] < 1)
            {
                // 成功的结果
                $res = $this->pay_success($order_id, $pay_type);
                return array($res[0], $res[1]);
            }
            else
            {
                // 失败的结果
                return array($res_pay[0], $res_pay[1]);
            }
        }
		elseif ($pay_type == 'adminpay')
		{
			$res_pay = $this->pay_success($order_id, $pay_type);
			return $res_pay;
		}
		elseif ($pay_type == 'wxpay')
		{
			// 微信APP支付
			$data = $this->wx_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'xcxpay')
		{
			// 微信小程序支付
			$data = $this->wxxcx_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'wxmp')
		{
			// 微信wap支付
			$data = $this->wxmp_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'alipay')
		{
			// 支付宝APP支付
			$data = $this->ali_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'aliwap')
		{
			// 支付宝wap支付
			$data = $this->aliwap_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'alipc')
		{
			// 支付宝PC支付
			return array(1, "配置异常，请联络技术处理");
		}
		elseif ($pay_type == 'alimini')
		{
			// 支付宝小程序支付
			return array(1, "配置异常，请联络技术处理");
		}
		elseif ($pay_type == 'wxnative')
		{
			// 支付宝小程序支付
			return array(1, "配置异常，请联络技术处理");
		}
		elseif ($pay_type == 'wxqrcode')
		{
			// 支付宝小程序支付
			return array(1, "配置异常，请联络技术处理");
		}
		elseif ($pay_type == 'wait_pay')
		{
			//积分支付
			$res_pay = $this->wait_pay($order_id);
			if ($res_pay[0] < 1)
			{
				// 成功的结果
				$res = $this->pay_success($order_id, $pay_type);
				return array($res[0], $res[1]);
			}
			else
			{
				// 失败的结果
				return array($res_pay[0], $res_pay[1]);
			}
		}
		else
		{
			return array(1, "请选择支付方式");
		}

	}

	/**
	 * 支付宝APP支付
	 * @param order_id     订单ID
	 * @return  array     支付
	 * @author  zd
	 */
	public function ali_pay($order_id)
	{
		$href = THINK_PATH . "/Library/Vendor/Alipay";
		require_once $href . '/config.php';
		require_once $href . '/aop/AopClient.php';
		require_once $href . '/aop/request/AlipayTradeAppPayRequest.php';

		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if ($orderInfo['total_commodity_price'] <= 0)
		{
			return array(1, "支付金额不能<=0");
		}
		$config_pay3 = get_config_pay3(2);
		if (!$config_pay3)
		{
			return array(1, "支付宝配置异常，请联系客服");
		}

		$aop = new \AopClient();
		$aop->appId = $config_pay3['appid'];
		$aop->rsaPrivateKey = $config_pay3['key1'];
		$aop->alipayrsaPublicKey = $config_pay3['key2'];

		$aop->format = 'json';
		$aop->charset = $config['charset'];
		$aop->signType = $config['sign_type'];
		$aop->gatewayUrl = $config['gatewayUrl'];

		$body = '商城消费';
		$subject = '商城消费';
		$out_trade_no = make_order_no();
		$total_amount = sprintf("%.2f", $orderInfo['total_commodity_price']);

		// 写入第三方支付记录表
		$data_log3["serial_no"] = $out_trade_no;
		$data_log3["user_id"] = $orderInfo["user_id"];
		$data_log3["order_id"] = $orderInfo["id"];
		$data_log3["order_no"] = $orderInfo["order_no"];
		$data_log3["money"] = $total_amount;
		$data_log3["order_type"] = $orderInfo["order_type"];
		$data_log3["type"] = 2;
		$data_log3["status"] = 0;
		$data_log3["cre_time"] = time();
		$data_log3["cre_date"] = date("Y-m-d H:i:s");
		$data_log3["ip"] = get_client_ip();
		$log3 = M("pay3_log")->add($data_log3);
		if (!$log3)
		{
			return array(0, "订单支付异常，请联系客服");
		}

		$bizcontent = json_encode(
			[
				'body' => $body,
				'subject' => $subject,
				'out_trade_no' => $out_trade_no,//此订单号为商户唯一订单号
				'total_amount' => $total_amount,//保留两位小数
				'product_code' => 'QUICK_MSECURITY_PAY'
			]
		);
		$request = new \AlipayTradeAppPayRequest();
		$request->setReturnUrl(C("C_HTTP_HOST") . $config_pay3['notify_url']);
		$request->setNotifyUrl(C("C_HTTP_HOST") . $config_pay3['notify_url']);
		$request->setBizContent($bizcontent);
		$aliRes = $aop->sdkExecute($request);

		return array(0, $aliRes);
	}

	//微信小程序支付
	public function wxxcx_pay($order_id)
	{

		$href = THINK_PATH . "/Library/Vendor/Wxpay/lib";
		require_once $href . '/WxPayConfig.php';
		require_once $href . '/WxPayData.php';
		require_once $href . '/WxPayApi.php';
		// 微信小程序支付数据 $type=1 商家服务费，$type=2 推广码服务费
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if ($orderInfo['order_type'] == 11)
		{
			if ($orderInfo['total_express_fee'] <= 0)
			{
				return array(1, "支付运费金额不能<=0");
			}
		}
		else
		{
			if ($orderInfo['total_commodity_price'] <= 0)
			{
				return array(1, "支付金额不能<=0");
			}
		}

		$user_id = I('user_id');
		$openid = M('user')->where('id= ' . $user_id)->getField('openid_mini');
		if (!$openid)
		{
			return array(1, '缺少用户信息参数');
		}
		$config_pay3 = get_config_pay3(4);
		if (!$config_pay3)
		{
			return array(1, "微信支付配置异常，请联系客服");
		}
		if ($orderInfo['order_type'] == 11)
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_express_fee']);
		}
		else
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_commodity_price']);
		}

		$out_trade_no = make_order_no();
		$data_log3["serial_no"] = $out_trade_no;
		$data_log3["user_id"] = $orderInfo["user_id"];
		$data_log3["order_id"] = $orderInfo["id"];
		$data_log3["order_no"] = $orderInfo["order_no"];
		$data_log3["money"] = $total_amount;
		$data_log3["order_type"] = $orderInfo["order_type"];
		$data_log3["type"] = 5;
		$data_log3["status"] = 0;
		$data_log3["cre_time"] = time();
		$data_log3["cre_date"] = date("Y-m-d H:i:s");
		$data_log3["ip"] = get_client_ip();
		$log3 = M("pay3_log")->add($data_log3);
		if (!$log3)
		{
			return array(1, "订单支付异常，请联系客服");
		}
		$notify = C("C_HTTP_HOST") . $config_pay3['notify_url'];
		$datawx['body'] = 'xcxpay:' . $out_trade_no;
		$datawx['order_no'] = $out_trade_no;
		$datawx['fee'] = $total_amount;
		$datawx['openid'] = $openid;

		$timeout = 40;  // 实际意义是 除10之后的数字，1微信APP，3微信公众号，4微信小程序，5微信H5

		$input = new \WxPayUnifiedOrder($timeout);
		$input->SetBody($datawx['body']);
		$input->SetAttach("XCX"); // 这里用来区分是哪种支付方式，微信公众号支付=JSAPI，微信小程序=XCX，微信APP支付=APP，微信H5支付支付=MWEB
		$input->SetOut_trade_no($datawx['order_no']);
		$input->SetTotal_fee($datawx['fee'] * 100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetNotify_url($notify);
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($datawx['openid']);
		$order = \WxPayApi::unifiedOrder($input, $timeout);

		if ($order['return_code'] != "SUCCESS" || $order['result_code'] != "SUCCESS")
		{
			return array(1, $order['err_code_des']);
		}

		$jsapi = new \WxPayJsApiPay($timeout);
		$jsapi->SetAppid($order["appid"]);
		$timeStamp = time();
		$jsapi->SetTimeStamp("$timeStamp");
		$jsapi->SetNonceStr(\WxPayApi::getNonceStr());
		$jsapi->SetPackage("prepay_id=" . $order['prepay_id']);
		$jsapi->SetSignType("MD5");
		$jsapi->SetPaySign($jsapi->MakeSign());
		$parameters = json_encode($jsapi->GetValues());
		return array(0, $parameters);

	}

	/**
	 * 微信APP支付
	 * @param order_id     订单ID
	 * @return  array     支付
	 * @author  zd
	 */
	public function wx_pay($order_id)
	{
		$href = THINK_PATH . "/Library/Vendor/Wxpay/lib";
		require_once $href . '/WxPayConfig.php';
		require_once $href . '/WxPayData.php';
		require_once $href . '/WxPayApi.php';

		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if ($orderInfo['order_type'] == 11)
		{
			if ($orderInfo['total_express_fee'] <= 0)
			{
				return array(1, "支付运费金额不能<=0");
			}
		}
		else
		{
			if ($orderInfo['total_commodity_price'] <= 0)
			{
				return array(1, "支付金额不能<=0");
			}
		}

		$config_pay3 = get_config_pay3(1);
		if (!$config_pay3)
		{
			return array(1, "微信支付配置异常，请联系客服");
		}

		$body = "商城消费";
		$out_trade_no = make_order_no();
		if ($orderInfo['order_type'] == 11)
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_express_fee']);
		}
		else
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_commodity_price']);
		}


		// 写入第三方支付记录表
		$data_log3["serial_no"] = $out_trade_no;
		$data_log3["user_id"] = $orderInfo["user_id"];
		$data_log3["order_id"] = $orderInfo["id"];
		$data_log3["order_no"] = $orderInfo["order_no"];
		$data_log3["money"] = $total_amount;
		$data_log3["order_type"] = $orderInfo["order_type"];
		$data_log3["type"] = 1;
		$data_log3["status"] = 0;
		$data_log3["cre_time"] = time();
		$data_log3["cre_date"] = date("Y-m-d H:i:s");
		$data_log3["ip"] = get_client_ip();
		$log3 = M("pay3_log")->add($data_log3);
		if (!$log3)
		{
			return array(1, "订单支付异常，请联系客服");
		}

		$payData = new \WxPayUnifiedOrder();
		$payData->SetBody($body);
		$payData->SetOut_trade_no($out_trade_no);
		$payData->SetTotal_fee($total_amount * 100);
		$payData->SetTime_start(date("YmdHis"));
		$payData->SetTime_expire(date("YmdHis", time() + 600));
		$payData->SetNotify_url(C("C_HTTP_HOST") . $config_pay3['notify_url']);

		$payData->SetAppid($config_pay3['appid']);
		$payData->SetMch_id($config_pay3['mchid']);

		$payData->SetTrade_type("APP");
		$wxPay = \WxPayApi::unifiedOrder($payData);

		$appId = $wxPay['appid'];
		$prepayId = $wxPay['prepay_id'];
		$timeStamp = time();
		$partnerId = $wxPay['mch_id'];
		$nonceStr = $wxPay['nonce_str'];
		$sign = $wxPay['sign'];
		$package = 'Sign=WXPay';

		$wxRes = new \WxPayResults();
		$data = array(
			'appid' => $appId,
			'partnerid' => $partnerId,
			'prepayid' => $prepayId,
			'package' => $package,
			'noncestr' => $nonceStr,
			'timestamp' => $timeStamp,
			'sign' => $sign
		);
		$wxRes->FromArray($data);
		$sign = $wxRes->SetSign();
		$data['sign'] = $sign;

		return array(0, $data);
	}

	/**
	 * 微信公众号&H5支付
	 * @param order_id     订单ID
	 * @return  array     支付
	 * @author  王小溜
	 */
	public function wxmp_pay($order_id)
	{
		$config_pay3 = get_config_pay3(3);
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if (!$config_pay3['appid'])
		{
			return array(1, "微信支付配置异常，请使用其他支付方式");
		}

		$out_trade_no = make_order_no();
		if ($orderInfo['order_type'] == 11)
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_express_fee']);
		}
		else
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_commodity_price']);
		}


		if (stristr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'))
		{
			$type = 3;
		}
		else
		{
			$type = 4;
		}

		// 写入第三方支付记录表（增加）
		$data_log3["serial_no"] = $out_trade_no;
		$data_log3["user_id"] = $orderInfo["user_id"];
		$data_log3["order_id"] = $orderInfo["id"];
		$data_log3["order_no"] = $orderInfo["order_no"];
		$data_log3["money"] = $total_amount;
		$data_log3["order_type"] = $orderInfo["order_type"];
		$data_log3["type"] = $type;
		$data_log3["status"] = 0;
		$data_log3["cre_time"] = time();
		$data_log3["cre_date"] = date("Y-m-d H:i:s");
		$data_log3["ip"] = get_client_ip();
		$log3 = M("pay3_log")->add($data_log3);
		if (!$log3)
		{
			return array(1, "订单支付异常，请联系客服");
		}

		$href = THINK_PATH . "/Library/Vendor/Wxpay";
		require_once $href . '/lib/WxPayConfig.php';
		require_once $href . '/lib/WxPayData.php';
		require_once $href . '/lib/WxPayApi.php';
		require_once $href . '/example/WxPay.NativePay.php';
		require_once $href . '/example/WxPay.JsApiPay.php';
		if (stristr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'))
		{
			// 微信公众号支付
			$openid = $orderInfo['openid'];
			$input = new \WxPayUnifiedOrder();
			$input->SetBody($out_trade_no);
			$input->SetAttach("weixin");
			$input->SetOut_trade_no($out_trade_no);
			$input->SetTotal_fee($total_amount * 100);
			$input->SetTime_start(date("YmdHis"));
			$input->SetTime_expire(date("YmdHis", time() + 600));
			$input->SetNotify_url(C("C_HTTP_HOST") . $config_pay3['notify_url']);
			$input->SetTrade_type("JSAPI");
			$input->SetOpenid($openid);
			$order2 = \WxPayApi::unifiedOrder($input);
			if ($order2['return_code'] == "FAIL")
			{
				return array(1, $order2['return_msg']);
			}

			$tools = new \JsApiPay();
			$jsApiParameters = $tools->GetJsApiParameters($order2);

			$go_url = U('M/Index/pay_success', 'id=' . $order_id);
			$back_url = U('M/Index/pay_error', 'id=' . $order_id);
			$html = <<<EOF
				<script type="text/javascript">
				//调用微信JS api 支付
				function jsApiCall()
				{
					WeixinJSBridge.invoke(
						'getBrandWCPayRequest',$jsApiParameters,
						function(res){
							WeixinJSBridge.log(res.err_msg);
							 if(res.err_msg == "get_brand_wcpay_request:ok") {
								location.href='$go_url';
							 }else{
								//alert(JSON.stringify(res));
								alert('支付失败！');
								location.href='$back_url';
							 }
						}
					);
				}

				function callpay()
				{
					if (typeof WeixinJSBridge == "undefined"){
						if( document.addEventListener ){
							document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
						}else if (document.attachEvent){
							document.attachEvent('WeixinJSBridgeReady', jsApiCall);
							document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
						}
					}else{
						jsApiCall();
					}
				}
				callpay();
				</script>	
EOF;
			// exit(json_encode(array('status' => 0, 'msg' => '微信支付所需数据', 'result' => $html)));
			return array(0, $html);
		}
		else
		{
			//微信h5支付
			$input = new \WxPayUnifiedOrder();
			$input->SetBody($orderInfo['order_no']);
			$input->SetOut_trade_no($orderInfo['order_no']);
			$input->SetTotal_fee($total_amount * 100); //
			$input->SetTime_start(date("YmdHis"));
			$input->SetTime_expire(date("YmdHis", time() + 600));
			$input->SetNotify_url(C("C_HTTP_HOST") . $config_pay3['notify_url']);
			$input->SetTrade_type("MWEB");
			$wxData = \WxPayApi::unifiedOrder($input);
			if ($wxData['return_code'] == "SUCCESS" && $wxData['result_code'] == "SUCCESS")
			{
				$mweb_url = $wxData['mweb_url'];
				$redirect_url = U('pay_success', 'id=' . $order_id);
				$mweb_url = $mweb_url . '&redirect_url=' . $redirect_url;
				header('Location:' . $mweb_url);
			}
			else
			{
				return array(1, "微信支付请求失败，请重新发起支付");
			}
		}
	}

	/**
	 * 支付宝WAP支付
	 * @param order_id     订单ID
	 * @return  array     支付
	 * @author  王小溜
	 */
	public function aliwap_pay($order_id)
	{
		$href = THINK_PATH . "/Library/Vendor/Alipay";
		require_once $href . '/config.php';
		require_once $href . '/wappay/service/AlipayTradeService.php';
		require_once $href . '/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if ($orderInfo['order_type'] == 11)
		{
			if ($orderInfo['total_express_fee'] <= 0)
			{
				return array(1, "支付运费金额不能<=0");
			}
		}
		else
		{
			if ($orderInfo['total_commodity_price'] <= 0)
			{
				return array(1, "支付金额不能<=0");
			}
		}

		$config_pay3 = get_config_pay3(2);
		if (!$config_pay3)
		{
			return array(1, "支付宝配置异常，请使用其他支付方式");
		}

		$body = '商城消费';
		$subject = '商城消费';
		$out_trade_no = make_order_no();
		if ($orderInfo['order_type'] == 11)
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_express_fee']);
		}
		else
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_commodity_price']);
		}


		// 写入第三方支付记录表
		$data_log3["serial_no"] = $out_trade_no;
		$data_log3["user_id"] = $orderInfo["user_id"];
		$data_log3["order_id"] = $orderInfo["id"];
		$data_log3["order_no"] = $orderInfo["order_no"];
		$data_log3["money"] = $total_amount;
		$data_log3["order_type"] = $orderInfo["order_type"];
		$data_log3["type"] = 6;
		$data_log3["status"] = 0;
		$data_log3["cre_time"] = time();
		$data_log3["cre_date"] = date("Y-m-d H:i:s");
		$data_log3["ip"] = get_client_ip();
		$log3 = M("pay3_log")->add($data_log3);
		if (!$log3)
		{
			return array(0, "订单支付异常，请联系客服");
		}

		$alipay_config = array(
			"app_id" => $config_pay3['appid'],
			"merchant_private_key" => $config_pay3['key1'],
			"alipay_public_key" => $config_pay3['key2'],
			"charset" => "UTF-8",
			"sign_type" => "RSA2",
			"gatewayUrl" => "https://openapi.alipay.com/gateway.do",
		);

		$payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
		$payRequestBuilder->setBody('商城消费');
		$payRequestBuilder->setSubject('商城消费');
		$payRequestBuilder->setTotalAmount($total_amount);
		$payRequestBuilder->setOutTradeNo($out_trade_no);

		$aop = new \AlipayTradeService($alipay_config);
		$notify_url = C("C_HTTP_HOST") . $config_pay3['notify_url'];
		$return_url = C("C_HTTP_HOST") . $config_pay3['return_url'];
		$response = $aop->wapPay($payRequestBuilder, $return_url, $notify_url);
		return array(0, $response);

	}

	/**
	 * 余额支付
	 * @param user_id      支付金额
	 * @param order_id     订单ID
	 * @return  array     支付
	 * @author  史超
	 * 说明：余额支付
	 */
	public function ye_pay($order_id)
	{
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if ($orderInfo['order_type'] == 2 || $orderInfo['order_type'] == 4)
		{
			$user_id = M("shop")->where("id=" . $orderInfo["shop_id"])->getField("user_id");
		}
		else
		{
			$user_id = $orderInfo["user_id"];
		}
		if (!$user_id)
		{
			return array(1, "用户或商家信息异常，请重试或联系客服");
		}
		$user = M('user')->field('pay_password,money')->where('id=' . $user_id)->find();
		if (empty($user['pay_password']))
		{
			return array(1, "请先设置支付密码");
		}
		if (get_pwd(I('pay_password', '')) != $user['pay_password'])
		{
			return array(1, "支付密码不正确");
		}
		if ($orderInfo['order_type'] == 11)
		{
			if ($orderInfo['total_express_fee'] <= 0)
			{
				return array(1, "订单运费金额必须大于0");
			}
		}
		else
		{
			if ($orderInfo['total_commodity_price'] <= 0)
			{
				return array(1, "订单金额必须大于0");
			}
		}


		// 兆兴::这里要分各种情况：
		// 订单类型，0商城订单，1用户买单，2商家报单，3充值订单，4代理报单
		if ($orderInfo['order_type'] == 1)
		{
			// 1用户买单，放大订单金额
			$total_num = round($orderInfo['total_commodity_price'], 2);
			$user_money = $user["money"];    // 用户的账户余额
			$money_field = "money";
			$type = 1;
			$deal_type = 3;
			$utype = 1;
			$remark = "使用账户余额买单";
		}
		elseif ($orderInfo['order_type'] == 2)
		{
			// 2商家报单，使用交易账户报单，不用放大金额
			$where_t2['user_id'] = $user_id;
			$user = get_user_info($where_t2, '', '', 2);
			$user_money = $user["shop_money"];    // 交易账户
			$total_num = round($orderInfo['total_commodity_price'], 2);
			$money_field = "shop_money";
			$type = 2;
			$deal_type = 4;
			$utype = 2;
			$remark = "使用交易账户报单";
		}
		elseif ($orderInfo['order_type'] == 3)
		{
			return array(1, "充值订单不应操作此处，请联系客服");
		}
		elseif ($orderInfo['order_type'] == 4)
		{
			// 4代理报单，使用账户余额报单
			$where_t3['user_id'] = $user_id;
			$user = get_user_info($where_t3, '', '', 3);
			$user_money = $user["money"];    // 账户余额
			$total_num = round($orderInfo['total_commodity_price'], 2);
			$money_field = "money";
			$type = 10;
			$deal_type = 4;
			$utype = 3;
			$remark = "使用账户余额报单";
		}
		elseif ($orderInfo['order_type'] == 11)
		{
			// 1用户买单，放大订单金额
			$total_num = round($orderInfo['total_express_fee'], 2);
			$user_money = $user["money"];    // 用户的账户余额
			$money_field = "money";
			$type = 1;
			$deal_type = 3;
			$utype = 1;
			$remark = "使用账户余额支付运费";
		}
		else
		{
			// 0商城订单
			$total_num = round($orderInfo['total_commodity_price'], 2);
			$user_money = $user["money"];    // 用户的账户余额
			$money_field = "money";
			$type = 1;
			$deal_type = 2;
			$utype = 1;
			$remark = "在商城使用账户余额消费";
		}
		if ($total_num > $user_money)
		{
			return array(1, "账户额度不足，请更换支付方式");
		}

		$res = change_money_log($user_id, 1, $money_field, $user_money, $total_num, $type, $deal_type, $remark, 2, $utype, 1, $order_id);
		if ($res)
		{
			return array(0, "支付成功");
		}
		else
		{
			return array(1, "支付失败，请确认账户额度是否足够");
		}
	}


    public function price_pay1($order_id)
    {
        $orderInfo = M("order")->where("id=" . $order_id)->find();
        if ($orderInfo['order_type'] == 2 || $orderInfo['order_type'] == 4)
        {
            $user_id = M("shop")->where("id=" . $orderInfo["shop_id"])->getField("user_id");
        }
        else
        {
            $user_id = $orderInfo["user_id"];
        }
        if (!$user_id)
        {
            return array(1, "用户或商家信息异常，请重试或联系客服");
        }
        $user = M('user')->field('pay_password,money,price_pay')->where('id=' . $user_id)->find();
        if (empty($user['pay_password']))
        {
            return array(1, "请先设置支付密码");
        }
        if (get_pwd(I('pay_password', '')) != $user['pay_password'])
        {
            return array(1, "支付密码不正确");
        }
        if ($orderInfo['total_commodity_price'] <= 0)
        {
            return array(1, "订单金额必须大于0");
        }


        // 兆兴::这里要分各种情况：
        // 订单类型，0商城订单，1用户买单，2商家报单，3充值订单，4代理报单
        if ($orderInfo['order_type'] == 1)
        {
            // 1用户买单，放大订单金额
            $total_num = round($orderInfo['total_commodity_price'], 2);
            $user_money = $user["price_pay"];    // 用户的账户余额
            $money_field = "price_pay";
            $type = 1;
            $deal_type = 3;
            $utype = 1;
            $remark = "使用账户余额买单";
        }
        elseif ($orderInfo['order_type'] == 2)
        {
            // 2商家报单，使用交易账户报单，不用放大金额
            $where_t2['user_id'] = $user_id;
            $user = get_user_info($where_t2, '', '', 2);
            $user_money = $user["shop_money"];    // 交易账户
            $total_num = round($orderInfo['total_commodity_price'], 2);
            $money_field = "shop_money";
            $type = 2;
            $deal_type = 4;
            $utype = 2;
            $remark = "使用交易账户报单";
        }
        elseif ($orderInfo['order_type'] == 3)
        {
            return array(1, "充值订单不应操作此处，请联系客服");
        }
        elseif ($orderInfo['order_type'] == 4)
        {
            // 4代理报单，使用账户余额报单
            $where_t3['user_id'] = $user_id;
            $user = get_user_info($where_t3, '', '', 3);
            $user_money = $user["price_pay"];    // 账户余额
            $total_num = round($orderInfo['total_commodity_price'], 2);
            $money_field = "price_pay";
            $type = 10;
            $deal_type = 4;
            $utype = 3;
            $remark = "使用账户余额报单";
        }
        elseif ($orderInfo['order_type'] == 11)
        {
            // 1用户买单，放大订单金额
            $total_num = round($orderInfo['total_express_fee'], 2);
            $user_money = $user["price_pay"];    // 用户的账户余额
            $money_field = "price_pay";
            $type = 1;
            $deal_type = 3;
            $utype = 1;
            $remark = "使用账户余额支付运费";
        }
        else
        {
            // 0商城订单
            $total_num = round($orderInfo['total_commodity_price'], 2);
            $user_money = $user["price_pay"];    // 用户的账户余额
            $money_field = "price_pay";
            $type = 19;
            $deal_type = 2;
            $utype = 1;
            $remark = "在商城使用余额消费";
        }
        if ($total_num > $user_money)
        {
            return array(1, "账户额度不足，请更换支付方式");
        }

        $res = change_money_log($user_id, 1, $money_field, $user_money, $total_num, $type, $deal_type, $remark, 2, $utype, 1, $order_id);
        if ($res)
        {
            return array(0, "支付成功");
        }
        else
        {
            return array(1, "支付失败，请确认账户额度是否足够");
        }
    }
    public function price_pay($order_id)
    {
        $orderInfo = M("order")->where("id=" . $order_id)->find();
        if ($orderInfo['order_type'] == 2 || $orderInfo['order_type'] == 4)
        {
            $user_id = M("shop")->where("id=" . $orderInfo["shop_id"])->getField("user_id");
        }
        else
        {
            $user_id = $orderInfo["user_id"];
        }
        if (!$user_id)
        {
            return array(1, "用户或商家信息异常，请重试或联系客服");
        }
        $user = M('user')->field('pay_password,money,price')->where('id=' . $user_id)->find();
        if (empty($user['pay_password']))
        {
            return array(1, "请先设置支付密码");
        }
        if (get_pwd(I('pay_password', '')) != $user['pay_password'])
        {
            return array(1, "支付密码不正确");
        }
        if ($orderInfo['total_commodity_price'] <= 0)
        {
            return array(1, "订单金额必须大于0");
        }


        // 兆兴::这里要分各种情况：
        // 订单类型，0商城订单，1用户买单，2商家报单，3充值订单，4代理报单
        if ($orderInfo['order_type'] == 1)
        {
            // 1用户买单，放大订单金额
            $total_num = round($orderInfo['total_commodity_price'], 2);
            $user_money = $user["money"];    // 用户的账户余额
            $money_field = "money";
            $type = 1;
            $deal_type = 3;
            $utype = 1;
            $remark = "使用账户余额买单";
        }
        elseif ($orderInfo['order_type'] == 2)
        {
            // 2商家报单，使用交易账户报单，不用放大金额
            $where_t2['user_id'] = $user_id;
            $user = get_user_info($where_t2, '', '', 2);
            $user_money = $user["shop_money"];    // 交易账户
            $total_num = round($orderInfo['total_commodity_price'], 2);
            $money_field = "shop_money";
            $type = 2;
            $deal_type = 4;
            $utype = 2;
            $remark = "使用交易账户报单";
        }
        elseif ($orderInfo['order_type'] == 3)
        {
            return array(1, "充值订单不应操作此处，请联系客服");
        }
        elseif ($orderInfo['order_type'] == 4)
        {
            // 4代理报单，使用账户余额报单
            $where_t3['user_id'] = $user_id;
            $user = get_user_info($where_t3, '', '', 3);
            $user_money = $user["money"];    // 账户余额
            $total_num = round($orderInfo['total_commodity_price'], 2);
            $money_field = "money";
            $type = 10;
            $deal_type = 4;
            $utype = 3;
            $remark = "使用账户余额报单";
        }
        elseif ($orderInfo['order_type'] == 11)
        {
            // 1用户买单，放大订单金额
            $total_num = round($orderInfo['total_express_fee'], 2);
            $user_money = $user["money"];    // 用户的账户余额
            $money_field = "money";
            $type = 1;
            $deal_type = 3;
            $utype = 1;
            $remark = "使用账户余额支付运费";
        }
        else
        {
            // 0商城订单
            $total_num = round($orderInfo['total_commodity_price'], 2);
            $user_money = $user["price"];    // 用户的账户余额
            $money_field = "price";
            $type = 18;
            $deal_type = 2;
            $utype = 1;
            $remark = "在商城使用已返金额消费";
        }
        if ($total_num > $user_money)
        {
            return array(1, "账户额度不足，请更换支付方式");
        }

        $res = change_money_log($user_id, 1, $money_field, $user_money, $total_num, $type, $deal_type, $remark, 2, $utype, 1, $order_id);
        if ($res)
        {
            return array(0, "支付成功");
        }
        else
        {
            return array(1, "支付失败，请确认账户额度是否足够");
        }
    }
	/**
	 * 新增积分支付
	 * @author lty
	 */
	public function wait_pay($order_id)
	{
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		$user_id = $orderInfo["user_id"];
		if (!$user_id)
		{
			return array(1, "用户或商家信息异常，请重试或联系客服");
		}
		$user = M('user')->field('pay_password,score')->where('id=' . $user_id)->find();
		if (empty($user['pay_password']))
		{
			return array(1, "请先设置支付密码");
		}
		if (get_pwd(I('pay_password', '')) != $user['pay_password'])
		{
			return array(1, "支付密码不正确");
		}
		if ($orderInfo['total_commodity_price'] <= 0)
		{
			return array(1, "订单金额必须大于0");
		}

		// 订单类型 11积分订单
		if ($orderInfo['order_type'] == 11)
		{
			// 1用户买单，放大订单金额
			$total_num = round($orderInfo['total_commodity_price'], 2);
			$user_money = $user["score"];    // 用户的账户余额
			$money_field = "score";
			$type = 16;
			$deal_type = 2;
			$utype = 1;
			$remark = "使用账户积分消费";
		}
		elseif ($orderInfo['order_type'] == 3)
		{
			return array(1, "充值订单不应操作此处，请联系客服");

		}

		if ($total_num > $user_money)
		{
			return array(1, "账户额度不足，请更换支付方式");
		}

		$res = change_money_log($user_id, 1, $money_field, $user_money, $total_num, $type, $deal_type, $remark, 2, $utype, 1, $order_id);
		if ($res)
		{
			return array(0, "支付成功");
		}
		else
		{
			return array(1, "支付失败，请确认账户积分额度是否足够");
		}
	}

	/**
	 * 支付成功处理订单
	 * @param pay_type     支付方式
	 * @param order_id     订单ID
	 * @return  array     支付
	 * @author  史超
	 * 说明：余额支付
	 */
	public function pay_success($order_id, $pay_type)
	{
		$order = M('order')->where('id=' . $order_id)->find();
		$userInfo = M('user')->find($order['user_id']);
		if ($order['order_type'] == 11 && get_config('score_pay_mode') == 2 && $order["total_express_fee"] > 0)
		{
			$order_date['express_fee_payment_type'] = $pay_type;
			$order_date['payment_type'] = 'wait_pay';
		}
		else
		{
			$order_date['payment_type'] = $pay_type;
		}
		if ($order['order_type'] == 1)
		{
			$order_date['order_status'] = 3;
		}
		else
		{
			$order_date['order_status'] = 1;
		}

		//如果是普通商品，并且支付金额满足大于等于50元，并且该消费者没有获得该优惠券，则该优惠券赠送该消费者
		if ($order['order_type']==0){
		    $zf_money=get_config_project("zf_money");
		    $where_user['id']=$order['user_id'];
		    $user=get_user_info($where_user);

        }


		//如果购买了 特殊商品 就进行返利优惠券赠送
        if ($order['order_type'] == 12){

            //根据相关的等级赠送优惠券
            if($order['level_ranking'] == 1){

                $data_c['cid'] = 1638;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);

                $data_c['cid'] = 1639;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);

                $data_c['cid'] = 1640;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);

                $data_c['cid'] = 1641;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);


            }elseif($order['level_ranking'] == 2){

                $data_c['cid'] = 1638;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);

                $data_c['cid'] = 1639;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);

                $data_c['cid'] = 1640;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);

                $data_c['cid'] = 1641;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
            }elseif($order['level_ranking'] == 3){

                $data_c['cid'] = 1638;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                for ($i=0;$i<90;$i++){
                    M("coupon_user")->add($data_c);
                }


                $data_c['cid'] = 1639;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);

                $data_c['cid'] = 1640;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);

                $data_c['cid'] = 1641;
                $data_c['user_id'] = $order['user_id'];
                $data_c['use_start_time'] ='1607760977';
                $data_c['use_end_time'] = '1765527380';
                $data_c['cre_date'] = date("Y-m-d H:i:s");
                $data_c['cre_time'] = time();
                $data_c['ip'] = get_client_ip();
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
                M("coupon_user")->add($data_c);
            }
            M("user")->where("id=".$order['user_id'])->setField("is_coupon",1);

//            $where_c['is_del']=0;
//            $where_c['status']=1;
//            $where_c['to_new_user']=1;
//
//            $where_c['use_start_time']=array("elt",time());
//            $where_c['use_end_time']=array("egt",time());
//
//            $coupon=M("coupon")
//                ->where("is_del=0 and status=1 and to_new_user=1 and use_start_time<= ".time()." <= use_end_time and send_num>get_num")->select();
//            foreach ($coupon as $key=>$value){
//
//            }
//            M("user")->where("id=".$order['user_id'])->setField("is_coupon",1);

        }


		$order_date['pay_status'] = 1;
		$order_date['pay_time'] = time();
		$res = M('order')->where('id=' . $order_id)->save($order_date);

		$order_goods_date['order_status'] = 1;
		$order_goods_date['pay_status'] = 1;
		$order_goods_date['pay_time'] = time();
		$res2 = M('order_goods')->where('order_id=' . $order_id)->save($order_goods_date);
		//完善订单日志
		$goods_name = '';
		$order_goods = M("order_goods")->where("order_id=" . $order_id)->select();
		foreach ($order_goods as $k => $v)
		{
			$goods_name .= $v["goods_name"] . "，数量为：" . $v["goods_num"] . "，";
		}
		$order = M("order")->where("id=" . $order_id)->find();
		//插入订单日志
		if ($order['order_type'] == 11 && get_config('score_pay_mode') == 2 && $order["total_express_fee"] > 0)
		{
			add_order_log($order_id, "支付成功", '支付方式：' . payment_type_cn($pay_type) . '，支付运费金额：' . $order["total_express_fee"] . '商品为：' . $goods_name);
			add_order_log($order_id, "支付成功", '支付方式：' . payment_type_cn('wait_score') . '，支付积分金额：' . $order["actual_order_total"] . '商品为：' . $goods_name);

		}
		else
		{
			add_order_log($order_id, "支付成功", '支付方式：' . payment_type_cn($pay_type) . '，支付金额：' . $order["total_commodity_price"] . '商品为：' . $goods_name);
		}
		// 库存、销量处理
		$this->upStockAndSales($order_id);
		order_logic($order_id);            // 主要逻辑处理，虽在此时调用，但是提成相关并不一定在此时立即拿到，因为可能有收货后才提成的

		// 更新团购信息
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if ($orderInfo["order_type"] == 5)
		{
			$goods_id = M("order_goods")->where('order_id=' . $order_id)->getField("goods_id");
			$this->setGroupOrder($order_id, $goods_id, $orderInfo["group_order_id"], 2);
		}
		$re1 = true;
		$re2 = true;
		if ($order['order_type'] == 11 && get_config('score_pay_mode') == 2 && $order["total_express_fee"] > 0)
		{
		    //加入account_log数据表 用户的score做修改

            change_money_log($order['user_id'],1,'score',$userInfo['score'],$order['actual_order_total'],16,2,'使用账户积分消费',2,1,1,$order['id'],$order['user_id'],0);
//			$re1 = M('user')->where('id=' . $order['user_id'])->setDec('score', $order['actual_order_total']);
			$re2 = M('user')->where('id=' . $order['user_id'])->setDec('wait_score', $order['actual_order_total']);
		}

		if ($res !== false && $res2 !== false && $re1 && $re2)
		{
			$this->add_transaction_record($order_id);//记录交易明细
			return array(0, "操作完成");
		}
		else
		{
			return array(1, "订单支付异常请联系管理员");
		}
	}

	/**
	 * 订单发货   手动输入快递信息
	 * @return  array     发货
	 * @author  史超
	 * 说明：订单发货
	 */
	public function getDelivery_a($date)
	{

		if ($date)
		{
			$order_id = $date['goods'];
			$tracking_number = $date['tracking_number'];
			$express_id = $date['express_id'];
			$shop_note = $date['shop_note'];
		}
		else
		{
			$order_id = I('order_id', 0, 'intval');
			$tracking_number = I('tracking_number');
			$express_id = I('express_id');
			$goods_id = I('goods_id');
			$shop_note = I('shop_note', '');
		}
		if (empty($express_id) || empty($tracking_number))
		{
			return '请选择快递公司或输入快递号';
		}
		if (is_array($order_id))
		{
			$goods_name = '';
			foreach ($order_id as $k => $v)
			{

				$order = M('order_goods')->where('id=' . $v)->find();
				$goods_name .= $order["goods_name"] . "，数量为：" . $order["goods_num"] . "，";
				if ($order['shipping_status'])
				{
					//插入订单日志
					return '已经发过货不用重复操作';
				}
				$express_name = get_express($express_id);
				$data = array(
					'order_status' => 2,
					'shipping_status' => 1,
					'express_id' => $express_id,
					'express_id' => $express_id,
					'express_name' => $express_name['name'],
					'tracking_number' => $tracking_number,
					'shipping_time' => time()
				);
				M('order_goods')->where('id=' . $v)->save($data);


			}
			//完善订单日志
			add_order_log($order['order_id'], "订单发货", '订单号：' . $tracking_number . "商品为：" . $goods_name);
		}
		else
		{
			$order = M('order_goods')->where('order_id=' . $order_id . ' and goods_id=' . $goods_id)->find();
			if ($order['shipping_status'])
			{
				//插入订单日志
				return '已经发过货不用重复操作';
			}
			$express_name = get_express($express_id);
			$data = array(
				'order_status' => 2,
				'shipping_status' => 1,
				'express_id' => $express_id,
				'express_name' => $express_name['name'],
				'tracking_number' => $tracking_number,
				'shipping_time' => time()
			);
			M('order_goods')->where('order_id=' . $order_id . ' and goods_id=' . $goods_id)->save($data);
			//完善订单日志
			$order_goods = M("order_goods")->where('order_id=' . $order_id . ' and goods_id=' . $goods_id)->find();
			add_order_log($order['order_id'], "订单发货", '订单号：' . $tracking_number . ",商品名称为：" . $order_goods["goods_name"]);
		}

		$order_id = $order['order_id'];

		$order = M('order_goods')->where('order_status=1 and order_id=' . $order_id)->find();
		if (!$order)
		{
			$data_order = array(
				'order_status' => 2,
				'shipping_status' => 1,
				'shipping_time' => time(),
				'shop_note' => $shop_note,
			);
			M('order')->where('id=' . $order_id)->save($data_order);
			/*$goods_name = '';
			$order_goods = M("order_goods")->where("order_id=".$order_id)->select();
			foreach ($order_goods as $k=>$v){
				$goods_name .= $v["goods_name"].",数量为：".$v["goods_num"];
			}
			//完善订单日志
			add_order_log($order_id, '订单发货成功,发货商品为：'.$goods_name);*/
		} /*else {
            $data_order = array(
                'order_status' => -2,
                'shop_note' => $shop_note,
            );
            M('order')->where('id=' . $order_id)->save($data_order);
            //完善订单日志

            $goods_name = '';
            $order_goods = M("order_goods")->where("order_status=1 and order_id=" . $order_id)->select();
            foreach ($order_goods as $k=>$v){
                $goods_name .= $v["goods_name"].",";
            }
            add_order_log($order_id, '会员申请退货,商品为：'.$goods_name);
        }*/
		return 1;

	}

	/**
	 * 订单发货 自动发货 获取快递单号
	 * @return  array     发货
	 * @author  史超
	 * 说明：订单发货
	 */
	public function getDelivery_b($date)
	{

		if ($date)
		{
			$order_id = $date['goods'];

			$shop_note = $date['shop_note'];
		}
		else
		{
			$order_id = I('order_id', 0, 'intval');
			$user_id = I('user_id', 0, 'intval');

			$goods_id = I('goods_id');
			$force_type = I('force_type', 1);
			$shop_note = I('shop_note', '');
		}

		if (is_array($order_id))
		{

			foreach ($order_id as $k => $v)
			{

				$order = M('order_goods')->where('id=' . $v)->find();

				if ($order['shipping_status'])
				{
					//完善订单日志
					/* add_order_log($order['order_id'], '商品：'.$order['goods_name'].'已经发过货不用重复操作');*/
					return '已经发过货不用重复操作';
				}
				if ($order['order_status'] == 8)
				{
					//完善订单日志
					/* add_order_log($order['order_id'], '商品：'.$order['goods_name'].'已经提交快递打印不用重复操作');*/
					return '已经提交快递打印不用重复操作';
				}

			}
		}
		else
		{
			$order = M('order_goods')->where('order_id=' . $order_id . ' and goods_id=' . $goods_id)->find();
			if ($order['shipping_status'])
			{
				//完善订单日志
				/*add_order_log($order_id, '商品：'.$order['goods_name'].'已经发过货不用重复操作');*/
				return '已经发过货不用重复操作';
			}
			if ($order['order_status'] == 8)
			{
				//完善订单日志
				/*add_order_log($order_id, '商品：'.$order['goods_name'].'已经提交快递打印不用重复操作');*/
				return '已经提交快递打印不用重复操作';
			}

		}
		$data_order = array(
			'shop_note' => $shop_note,
		);

		M('order')->where('id=' . $order['order_id'])->save($data_order);
		$where_og['id'] = array('in', $order_id);
		$order_arr = M('order_goods')->field('id,order_id,goods_num,goods_name,goods_spec_price_name')->where($where_og)->select();

		$order = M('order')->field('id,order_no,province,city,district,address,phone,consignee,user_note,shop_note')->where('id=' . $order_arr[0]['order_id'])->find();
		$order['order_goods'] = $order_arr;
		$arr[0] = $order;

		$express = new  ExpressApi();
		$res = $express->express($arr);
		$res = json_decode($res, true);
		if ($res['success'])
		{
			M('order')->where('id=' . $order['id'])->save(array('order_status' => 8));
			$order_arr = M('order_goods')->where('order_id=' . $order['id'])->save(array('order_status' => 8));
			return 1;
		}
		else
		{
			return '快递接口请求失败';
		}

	}

	/**
	 * 订单收货
	 * @return  array     收货
	 * @author  史超
	 * 说明：订单收货
	 */
	public function takeDelivery()
	{
		$order_id = I('order_id', 0, 'intval');
		$user_id = I('user_id', 0, 'intval');
		$goods_id = I('goods_id');
		$shop_id = I('shop_id');
		$order_goods_id = I('order_goods_id', 0);
		if ($goods_id)
		{
			if (!$order_goods_id)
			{
				return '该商品订单不存在';
			}
			$order = M('order_goods')->where('order_id=' . $order_id . ' and goods_id=' . $goods_id . ' and id=' . $order_goods_id)->find();
			if ($order['order_status'] > 2)
			{
				return '已收货不用重复操作';
			}
			$data = array(
				'order_status' => 3,
				'shipping_status' => 2,
				'confirm_time' => time()
			);
			M('order_goods')->where('order_id=' . $order_id . ' and goods_id=' . $goods_id . ' and id=' . $order_goods_id)->save($data);
			add_order_log($order_id, "确认收货", '商品' . $order["goods_name"] . '，数量为：' . $order["goods_num"]);
			$order = M('order_goods')->where('order_status=2 and order_id=' . $order_id)->find();
			if (!$order)
			{
				$data_order = array(
					'order_status' => 3,
					'shipping_status' => 2,
					'confirm_time' => time()
				);
				M('order')->where('id=' . $order_id)->save($data_order);
			}

		}
		else
		{
			$data = array(
				'order_status' => 3,
				'shipping_status' => 2,
				'confirm_time' => time()
			);
			$order_godos = M("order_goods")->where('order_id=' . $order_id . ' and (order_status=2 or order_status=1) and shop_id=' . $shop_id)->select();
			$goods_name = '';
			foreach ($order_godos as $k => $v)
			{
				$goods_name .= $v["goods_name"] . ",数量为：" . $v["goods_num"] . "，";
			}
			M('order_goods')->where('order_id=' . $order_id . ' and (order_status=2 or order_status=1) and shop_id=' . $shop_id)->save($data);
			$data_order = array(
				'order_status' => 3,
				'shipping_status' => 2,
				'confirm_time' => time()
			);
			$order = M('order_goods')->where('order_id=' . $order_id . ' and order_status=1')->find();
			if (!$order)
			{
				M('order')->where('id=' . $order_id)->save($data_order);
				//完善订单日志
			}
			add_order_log($order_id, "确认收货", '商品名称：' . $goods_name);
		}
		//如果是自提的订单 就修改订单自提表的信息
		M('order_picksite')->where('order_id=' . $order_id)->setField('status', 1);//订单完成
		// 分销逻辑处理
		order_logic($order_id);
		return true;
	}

	/**
	 * 评价订单商品
	 * @return  array     评价订单商品
	 * @author  史超
	 * 说明：评价订单商品
	 */
	public function getEvaluate($images = null)
	{
		$order_id = I('order_id', 0, 'intval');
		$user_id = I('user_id', 0, 'intval');
		$goods_id = I('goods_id');
		$order = M('order_goods')->where('order_id=' . $order_id . ' and goods_id=' . $goods_id . ' and user_id=' . $user_id)->find();
		if ($order['order_status'] > 3)
		{
			return '订单商品已评价不用重复操作';
		}
		if (empty($images))
		{
			$images = '';
		}
		else
		{
			$arr = explode(',', I('images'));//评论图片字符串
			$images = json_encode($arr);//图片json
		}
		// $images = json_decode($images, true);
		// $images = I('images', $images);
		if (empty($images))
		{
			$images = '';
		}
		$shop_id = $order['shop_id'] ? $order['shop_id'] : 0;
		$date = array(
			'user_id' => $user_id,
			'order_id' => $order_id,
			'goods_id' => $goods_id,
			'shop_id' => $shop_id,
			'content' => I('evaluation_content'),
			'service_attitude' => I('service_attitude'),
			'commodity_quality' => I('commodity_quality'),
			'time' => time(),
			'images' => $images
		);
		M('goods_comment')->add($date);
		$data = array(
			'order_status' => 5,
			'evaluate_time' => time()
		);
		M('order_goods')->where('order_id=' . $order_id . ' and goods_id=' . $goods_id)->save($data);
		$order = M('order_goods')->where('order_status=3 and order_id=' . $order_id)->find();
		if (!$order)
		{
			$data_order = array(
				'order_status' => 5
			);
			M('order')->where('id=' . $order_id)->save($data_order);
			//完善订单日志
			add_order_log($order_id, "订单评价", '订单评价成功');
		}
		else
		{
			$data_order = array(
				'order_status' => -5
			);
			M('order')->where('id=' . $order_id)->save($data_order);
			//完善订单日志
			add_order_log($order_id, "售后完成", '商品名称：' . $order["goods_name"] . "，数量为：" . $order["goods_num"]);
		}
		return true;
	}

	/**
	 * 删除订单
	 * @return  array     删除订单
	 * @author  史超
	 * 说明：删除订单
	 */
	public function deleteOrder($order_id, $shop_id)
	{
		if (!$order_id)
		{
			$order_id = I('order_id', 0, 'intval');
		}
		$shop_id = I('shop_id');
		if (is_numeric($shop_id))
		{
			$order_goods = M("order_goods")->where("order_id=" . $order_id . ' and shop_id=' . $shop_id)->select();
		}
		else
		{
			$order_goods = M("order_goods")->where("order_id=" . $order_id)->select();
		}
		$order_goods_all = M("order_goods")->where("id=" . $order_id . ' and isdel !=1')->find();


		$goods_name = '';
		foreach ($order_goods as $k => $v)
		{
			$goods_name .= "商品名称：" . $v["goods_name"] . "，数量：" . $v["goods_num"] . "，";
		}
		if (is_numeric($shop_id))
		{
			M('order_goods')->where('order_id=' . $order_id . ' and shop_id=' . $shop_id)->setField('isdel', 1);
		}
		else
		{
			M('order_goods')->where('order_id=' . $order_id)->setField('isdel', 1);
		}

	
			M('order')->where('id=' . $order_id)->setField('isdel', 1);

		//完善订单日志
		add_order_log($order_id, "删除订单", $goods_name);
		return true;
	}

	/**
	 * 取消订单
	 * @return  array     取消订单
	 * @author  史超
	 * 说明：取消订单
	 */
	public function cancelOrder($order_id)
	{
		if (!$order_id)
		{
			$order_id = I('order_id', 0, 'intval');
		}
        $order = M('order')->where('id='.$order_id)->find();
		M('order')->where('id=' . $order_id)->setField('order_status', 4);
		M('order_goods')->where('order_id=' . $order_id)->setField('order_status', 4);
		//完善订单日志
		$order_goods = M("order_goods")->where("order_id=" . $order_id)->select();
		$goods_name = '';
		foreach ($order_goods as $k => $v)
		{
			$goods_name .= "商品名称：" . $v["goods_name"] . "，数量：" . $v["goods_num"] . "，";
		}
		add_order_log($order_id, "取消订单", $goods_name);
        M( 'user' )->where( 'id=' . $order['user_id'] )->setDec( 'wait_score',$order[ 'actual_order_total' ] );

        //判断该订单是否用了优惠券  用了就在返回回去
        if($order['is_use_coupon'] > 0){
            M('order')->where('id='.$order_id)->setField('is_use_coupon',0);
            M('order')->where('id='.$order_id)->setField('user_coupon_money',0);
            M('order')->where('id='.$order_id)->setField('user_coupon_id',0);
            M('coupon_user')->where('id='.$order['user_coupon_id'])->setField('order_id',0);
            M('coupon_user')->where('id='.$order['user_coupon_id'])->setField('use_time',0);
            M('coupon_user')->where('id='.$order['user_coupon_id'])->setField('use_date',0);
        }
		return true;
	}

	/**
	 * 获取商品信息
	 * @param json      参照下方单元测试数据传来的数据格式
	 * @param array     参照下方单元测试数据传来的数据格式
	 * @return  array     商品详细信息数据
	 * @author  史超
	 * 说明：商品详细信息数据
	 */
	private function getGoodsInfo()
	{
		if (is_array(I('goods')))
		{
			$goods = I('goods');
		}
		else
		{
			$goods_j = htmlspecialchars_decode(I('goods'));
			$goods = json_decode($goods_j, true);
		}
		return $goods;
	}

	/**
	 * 加入购物车
	 * @return  array     加入购物车
	 * @author  史超
	 * 说明：加入购物车
	 */
	public function makeCart($goods)
	{
		if (empty($goods))
		{
			$goods = $this->getGoodsInfo();
			if (!$goods)
			{
				return '没有选择商品';
			}
			$type = I('type');    // 操作方式 加入、删除、加、减
		}
		else
		{
			$type = $goods[0]['type'];
		}
		if (count($goods) == count($goods, 1))
		{
			$arr[0] = $goods;
			$goods = $arr;
		}
		if ($type == 'add')
		{

			foreach ($goods as $k => $v)
			{
				if ($v['goods_num'] < 1)
				{
					return '请选择商品数量';
				}
				$c = M('cart')->where('user_id=' . $this->user_id)->count();
				if ($c > 200)
				{
					return '购物车中商品数量过多请清除一部分再添加';
				}
				$cart = M('cart')->field('id,goods_num')->where(['user_id' => $this->user_id, 'goods_id' => $v['goods_id'], 'goods_spec_id' => $v['goods_spec_id']])->find();
				if (empty($cart))
				{

					$where_spec["key"] = $v["goods_spec_id"];
					$where_spec["goods_id"] = $v["goods_id"];
					$specInfo = M("goods_spec_price")->where($where_spec)->find();

					$data = [
						'user_id' => $this->user_id,
						'goods_id' => $v['goods_id'],
						'goods_num' => $v['goods_num'],
						'goods_spec_id' => $v['goods_spec_id'],
						'goods_spec_price_id' => $specInfo['id'],
						'goods_spec_price_name' => $specInfo['key_name'],
						'time' => time(),
						'ip' => get_client_ip()
					];
					M('cart')->add($data);
					return true;
				}
				else
				{
					M('cart')->where(['user_id' => $this->user_id, 'goods_id' => $v['goods_id'], 'goods_spec_id' => $v['goods_spec_id']])->setInc('goods_num', $v['goods_num']);
					return true;
				}
			}
		}
		elseif ($type == 'up')
		{
			foreach ($goods as $k => $v)
			{

				$cart = M('cart')->field('id,goods_num')->where(['id' => $v['cart_id']])->find();
				if (empty($cart))
				{
					return '商品不存在请重新添加';
				}
				else
				{
					M('cart')->where(['id' => $v['cart_id']])->setInc('goods_num', $v['goods_num']);
					return true;
				}
			}
		}
		elseif ($type == 'down')
		{
			foreach ($goods as $k => $v)
			{
				$cart = M('cart')->field('id,goods_num')->where(['id' => $v['cart_id']])->find();
				if (empty($cart))
				{
					return '商品不存在请重新添加';
				}
				else
				{
					if ($cart['goods_num'] > $v['goods_num'])
					{
						M('cart')->where('id=' . $v['cart_id'])->setDec('goods_num', $v['goods_num']);
					}
					else
					{
						M('cart')->where('id=' . $v['cart_id'])->delete();
					}
					return true;
				}
			}
		}
		elseif ($type == 'del')
		{
			foreach ($goods as $k => $v)
			{
				M('cart')->where('id=' . $v['cart_id'])->delete();
			}
			return true;
		}
	}

	/**
	 * 申请售后
	 * @return  array     申请售后
	 * @author  zd
	 * 说明：申请售后
	 */
	public function refundOrder($images = null)
	{
		$order_id = I('order_id', 0, 'intval');
		$user_id = I('user_id', 0, 'intval');
		$goods_id = I('goods_id', 0);
		$refund_reason = I('refund_reason', '');
		$refund_type = I('refund_type', 0);
		if (empty($refund_type))
		{
			jsonout('请选择售后方式', 1);
		}
		if (empty($refund_reason))
		{
			jsonout('请选择申请原因', 1);
		}
		$newOrder = M('order')->find($order_id);
		$order = M('order_goods')->where('order_id=' . $order_id . ' and goods_id=' . $goods_id . ' and user_id=' . $user_id)->find();
		$refund_num = I('refund_num', 0);
		if ($order['refund_num'] > 0 && $order['goods_num'] == 0)
		{
			jsonout('此商品已申请售后', 1);
		}
		if ($refund_num <= 0 || $refund_num > $order['goods_num'])
		{
			jsonout('申请数量不对', 1);
		}

		if (!$order)
		{
			jsonout("没有此订单商品", 1);
		}
		if ($order["order_status"] == 4)
		{
			jsonout("此订单商品已取消，不可申请售后");
		}
		if ($order["order_status"] == 5)
		{
			jsonout("此订单商品已评价，不可申请售后");
		}
		if ($order["order_status"] < 0)
		{
			jsonout("此订单商品为售后，不可申请售后");
		}
		if ($order['order_status'] == 1 && $refund_type != 3)
		{
			jsonout('商家还没有发货 只能申请退款', 1);
		}
		if (($order['order_status'] == 2 || $order['order_status'] == 3) && $refund_type == 3)
		{
			jsonout('商家已发货不能申请退款', 1);
		}

		// if ($order["order_status"] < 0)
		// {
		// 	jsonout("当前订单商品状态不允许退货，请联系客服");
		// }
		if (empty($images))
		{
			$images = htmlspecialchars_decode(I('images'));
		}
		else
		{
			$arr = explode(',', $images);//评论图片字符串
			$images = json_encode($arr);//图片json
		}

		//如果使用了优惠券 就把优惠券的金额减去
//        $user_coupon_money = 0;
//		$user_coupon_money = -  $user_coupon_money  ;
		$date = array(
			'user_id' => $user_id,
			'order_id' => $order_id,
			'refund_no' => make_order_no(),
			'goods_id' => $goods_id,
			'order_goods_id' => $order["id"],
			'goods_name' => $order["goods_name"],
			'goods_num' => $order["goods_num"],
			'goods_thumb' => $order["goods_thumb"],
			'goods_price' => $order["goods_price"],
			'refund_num' => $refund_num,
			'refund_total' => $order["goods_price"] * $refund_num - $newOrder['user_coupon_money'],
			'shop_id' => $order["shop_id"],
			'goods_spec_price_id' => $order["goods_spec_price_id"],
			'goods_spec_price_name' => $order["goods_spec_price_name"],
			'refund_status' => 1,
			'refund_type' => $refund_type,
			'goods_type' => I('goods_type', 1),
			'refund_reason' => $refund_reason,
			'refund_description' => I('refund_description', ''),
			'images' => $images,
			'add_time' => time(),
		);
		$model = M();
		$model->startTrans();

		$rs = M('order_refund')->add($date);
		M('order')->where('id='.$order_id)->setField('user_coupon_money',0);
		$data_up['order_status'] = -1;
		$data_up['return_time'] = time();
		$data_up['refund_num'] = $refund_num;
		$data_up['goods_num'] = $order['goods_num'] - $refund_num;
		$rs2 = M("order_goods")->where("id=" . $order['id'])->save($data_up);
		$order_goods_count = M('order_goods')->where('order_id=' . $order_id)->count();
		$order_refund_count = M('order_refund')->where('order_id=' . $order_id)->count();
		if ($order_goods_count == $order_refund_count)
		{
			$rs3 = M("order")->where("id=" . $order_id)->save(array('order_status' => 6));
		}
		$data = M('order_refund')->find($rs);
		$rs4 = add_order_return_log($data, 1);
		if ($rs && $rs2 !== false)
		{
			switch ($refund_type)
			{
				case 1:
					$new_refund_type = "商品退货";
					break;
				case 2:
					$new_refund_type = "商品换货";
					break;
				case 3:
					$new_refund_type = "商品退款";
					break;
			}
			add_order_log($order_id, $new_refund_type, "商品名称：" . $order["goods_name"] . "，数量：" . $order["goods_num"]);
			$model->commit();
			return $rs;
		}
		else
		{
			$model->rollback();
			return false;
		}

	}

	/**
	 * 买家售后回寄
	 */
	public function userRefundExpress()
	{
		$refund_id = I('refund_id', 0, 'intval');

		$refund = M('order_refund')->find($refund_id);
		if (!$refund)
		{
			return '没有找到订单售后';

		}
		//用于用户换货的收货地址
		$user_address_id = I('user_address_id', 0);
		if ($refund['refund_type'] == 2 && empty($user_address_id))
		{
			return '换货请选择收货地址';
		}
		$user_express = I('user_express', '');
		$user_tracking_number = I('user_tracking_number', 0);
		if (empty($user_express))
		{
			return '请填写快递公司名称';
		}
		if (empty($user_tracking_number))
		{
			return '请填写快递编号';
		}
		$data = array(
			'user_address_id' => $user_address_id,
			'refund_status' => 4,
			'user_express' => $user_express,
			'user_tracking_number' => $user_tracking_number,
		);
		$rs = M('order_refund')->where('id=' . $refund_id)->save($data);
		$rs2 = M('order_goods')->where('id=' . $refund['order_goods_id'])->save(array('order_status' => -4));
		$refund = M('order_refund')->find($refund_id);
		add_order_return_log($refund, 4);
		if ($rs && $rs2 != false)
		{
			return true;
		}
		else
		{
			return '提交失败';
		}
	}

	/**
	 * 买家售后确认收货
	 */
	public function userRefundReceipt()
	{
		$refund_id = I('refund_id', 0, 'intval');

		$refund = M('order_refund')->find($refund_id);
		if (!$refund)
		{
			return '没有找到订单售后';

		}
		if ($refund['refund_status'] == 7)
		{
			return '此售后订单已经确认收货,不能重复提交';
		}
		$data = array(
			'refund_status' => 7,
		);
		$rs = M('order_refund')->where('id=' . $refund_id)->save($data);
		$rs2 = M('order_goods')->where('id=' . $refund['order_goods_id'])->save(array('order_status' => -7));
		add_order_return_log($refund, 7);
		if ($rs && $rs2 != false)
		{
			return true;
		}
		else
		{
			return '提交失败';
		}
	}

	/**
	 * 买家售后确认收货
	 */
	public function userRefundComplete()
	{
		$refund_id = I('refund_id', 0, 'intval');

		$refund = M('order_refund')->find($refund_id);
		if (!$refund)
		{
			return '没有找到订单售后';

		}
		if ($refund['refund_status'] == 9)
		{
			return '此售后订单已经确认收货,不能重复提交';
		}
		$data = array(
			'refund_status' => 9,
		);
		$rs = M('order_refund')->where('id=' . $refund_id)->save($data);
		$rs2 = M('order_goods')->where('id=' . $refund['order_goods_id'])->save(array('order_status' => -9));
		add_order_return_log($refund, 9);
		if ($rs && $rs2 != false)
		{
			return true;
		}
		else
		{
			return '提交失败';
		}
	}

	/**
	 * 订单支付后处理销量和库存
	 * @return  bool
	 * @author  zd
	 * 说明：库存要处理规格或非规格的
	 */
	public function upStockAndSales($order_id)
	{
		$where["order_id"] = $order_id;
		$list = M("order_goods")->where($where)->select();
		foreach ($list as $key => $value)
		{
			// 销量
			if ($value['goods_id'])
			{
				M("goods")->where("id=" . $value["goods_id"])->setInc("sell_count", $value["goods_num"]);
			}

			// 库存 -- 减少对应规格的
			if ($value['goods_spec_price_id'])
			{
				M("goods_spec_price")->where("id=" . $value['goods_spec_price_id'])->setDec("store_count", $value["goods_num"]);
			}
			else
			{
				if ($value['goods_id'])
				{
					M("goods")->where("id=" . $value["goods_id"])->setDec("store_count", $value["goods_num"]);
				}
			}
			$all = M('goods_spec_price')->where('goods_id='.$value['goods_id'])->sum('store_count');
			M('goods')->where('id='.$value['goods_id'])->setField('store_count',$all);
		}


	}

	/**
	 * 团购订单
	 * order_id        主订单ID
	 * goods            商品信息
	 * group_order_id    团购发起订单ID，若是发起订单，则为0或与 order_id 相同
	 * source            来源，1刚创建订单，2订单支付后
	 *
	 * 若本就是发起订单，则把本订单的团购信息更新
	 * 若不是发起订单，则更新所有与主订单相关的团购信息
	 *
	 */
	public function setGroupOrder($order_id, $goods_id, $group_order_id, $source = 1)
	{
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if ($order_id == $group_order_id || $group_order_id == 0)
		{
			$group_leader = 1;    // 是否团购发起人，1是，0否
			$group_order_id = $order_id;
		}
		else
		{
			$group_leader = 0;
		}
		$groupInfo = M("goods_group")->where("goods_id=" . $goods_id)->find();
		if (!$groupInfo)
		{
			$group_people_num = 2;
			$group_time_end = time() + 86400;
		}
		else
		{
			$group_people_num = $groupInfo["team_people_num"];
			if ($orderInfo["pay_time"])
			{
				$add_time = $orderInfo["pay_time"];
			}
			elseif ($orderInfo["add_time"])
			{
				$add_time = $orderInfo["add_time"];
			}
			else
			{
				$add_time = time();
			}
			$group_time_end = $groupInfo["team_time_limit"] * 3600 + $add_time;
		}

		// 先更新本订单的独有信息
		$where_ord["id"] = $order_id;
		$data_ord["group_leader"] = $group_leader;
		$data_ord["group_order_id"] = $group_order_id;
		$data_ord["group_time_end"] = $group_time_end;
		$data_ord["group_people_num"] = $group_people_num;
		$data_ord["goods_id"] = $goods_id;
		if ($source == 1)
		{
			$data_ord["total_commodity_price"] = $orderInfo["total_commodity_price"];
		}
		M("order")->where($where_ord)->save($data_ord);

		// 再更新其他相关订单的相同信息

		$where["pay_status"] = 1;
		$where["group_order_id"] = $group_order_id;
		$num_have = M("order")->where($where)->count();

		$data_num["group_people_num_have"] = $num_have;
		M("order")->where($where)->save($data_num);
	}


	/**
	 * 获取售后订单信息
	 * @param int $refund_id 售后订单ID
	 * @return  json
	 * @zd
	 */
	public function getRefundDetail($refund_id = 0)
	{
		if (!$refund_id)
		{
			return _dat("参数异常，请核实", 1);
		}
		$refundInfo = M("order_refund")->where("id=" . $refund_id)->find();
		if (!$refundInfo)
		{
			return _dat("没有此售后订单，请核实", 1);
		}
		if ($refundInfo['shop_id'])
		{
			// 商家
			$shopInfo = M("shop")->where("id=" . $refundInfo['shop_id'])->field("id,shop_name,thumb")->find();
			if ($shopInfo)
			{
				$refundInfo['shop_name'] = $shopInfo['shop_name'];
				$refundInfo['shop_thumb'] = $shopInfo['thumb'];
			}
			else
			{
				$refundInfo['shop_name'] = "平台商家";
				$refundInfo['shop_thumb'] = C("C_DF_SHOP_LOGO");
			}
		}
		else
		{
			$refundInfo['shop_name'] = "平台自营";
			$refundInfo['shop_thumb'] = C("C_DF_SHOP_LOGO");
		}

		// 需要轮换输出某些信息：
		// （退换货）商品收货地址   refund_address_id
		// （换货时）用户收货地址   user_address_id
		// （换货时）商家发货的快递公司名  shop_express_id
		if ($refundInfo['refund_address_id'])
		{
			$shopAddInfo = M("shop_refund_address")->where("id=" . $refundInfo['refund_address_id'])->find();
			if ($shopAddInfo)
			{
				$refundInfo['shop_refund_address']['consignee'] = $shopAddInfo['receiver'];
				$refundInfo['shop_refund_address']['phone'] = $shopAddInfo['phone'];
				$refundInfo['shop_refund_address']['address'] = $shopAddInfo['address'];
				$refundInfo['shop_refund_address']['zip_code'] = $shopAddInfo['zip_code'];
			}
			else
			{
				$refundInfo['shop_refund_address']['consignee'] = "";
				$refundInfo['shop_refund_address']['phone'] = "";
				$refundInfo['shop_refund_address']['address'] = "";
				$refundInfo['shop_refund_address']['zip_code'] = "";
			}
		}
		else
		{
			$refundInfo['shop_refund_address']['consignee'] = "";
			$refundInfo['shop_refund_address']['phone'] = "";
			$refundInfo['shop_refund_address']['address'] = "";
			$refundInfo['shop_refund_address']['zip_code'] = "";
		}
		if ($refundInfo['user_address_id'])
		{
			$userAddInfo = M("user_address")->where("id=" . $refundInfo['user_address_id'])->find();
			if ($userAddInfo)
			{
				$address = get_region_name($userAddInfo['province']) . get_region_name($userAddInfo['city']) . get_region_name($userAddInfo['district']) . get_region_name($userAddInfo['twon']) . $userAddInfo['address'];
				$refundInfo['user_address']['consignee'] = $userAddInfo['consignee'];
				$refundInfo['user_address']['phone'] = $userAddInfo['phone'];
				$refundInfo['user_address']['address'] = $address;
			}
			else
			{
				$refundInfo['user_address']['consignee'] = "";
				$refundInfo['user_address']['phone'] = "";
				$refundInfo['user_address']['address'] = "";
			}
		}
		if ($refundInfo['shop_express_id'])
		{
			$expressInfo = M("express")->where("id=" . $refundInfo['shop_express_id'])->find();
			if ($expressInfo)
			{
				$refundInfo['shop_express'] = $expressInfo['name'];
			}
			else
			{
				$refundInfo['shop_express'] = "";
			}
		}
		$refundInfo['refund_type_cn'] = get_refund_type($refundInfo['refund_type']);
		if ($refundInfo['images'])
		{
			$imagesArr = json_decode($refundInfo['images'], true);
			// $imagesArr = json_decode($imagesArr);
			$refundInfo['images_arr'] = full_pre_url($imagesArr);
		}
		else
		{
			$refundInfo['images_arr'] = array();
		}
		return _dat($refundInfo, 0);
	}

	/**
	 * 支付宝 PC支付
	 * @param order_id int 订单ID
	 * @return  输出支付页面
	 * @author  sunhaiyang
	 */
	public function alipc_pay($order_id)
	{
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if ($orderInfo['order_type'] == 11)
		{
			if ($orderInfo['total_express_fee'] <= 0)
			{
				return array(1, "支付运费金额不能<=0");
			}
		}
		else
		{
			if ($orderInfo['total_commodity_price'] <= 0)
			{
				return array(1, "支付金额不能<=0");
			}
		}

		$config_pay3 = get_config_pay3(2);
		if (!$config_pay3)
		{
			return array(1, "支付宝配置异常，请使用其他支付方式");
		}
		if (!$orderInfo || $orderInfo["isdel"] == 1)
		{
			return array(1, "订单异常，无法支付，请联系客服");
		}
		if ($orderInfo['pay_status'])
		{
			return array(1, "订单已经支付");
		}
		// 判断有积分支付是否含商城商品
		$integral = I('integral', 0);
		$order_goods = M('order_goods')->where("order_id=" . $orderInfo['id'])->select();
		foreach ($order_goods as $key => $value)
		{
			$goods = M('goods')->where('isdel=0 and is_on_sale=1 and id=' . $value['goods_id'])->find();
			if ($goods)
			{
				if ($integral > 0 && $goods['goods_kind'] != 4)
				{
					return array(1, "订单含商城商品，不可使用积分");
				}
			}
			else
			{
				return array(1, "商品不存在或被删除，请重新下单");
			}
		}
		//判断有积分，并且是否有商城商品，给予提示，订单含商城商品，不可使用积分，没有积分正常走
		//总额减去积分
		//扣除用户积分
		//积分存入订单
		if ($integral > 0)
		{
			$user = get_user_info(array('id' => $orderInfo['user_id']));
			if ($user['integral'] < $integral)
			{
				return array(1, "积分不足，请重新支付");
			}
			if ($integral > $orderInfo['total_commodity_price'])
			{ //如果积分大于总金额，扣除订单总金额用户积分，总金额置0
				$integral = $orderInfo['total_commodity_price'];
				$orderInfo['total_commodity_price'] = 0.01; //基本不会走
			}
			else
			{
				$orderInfo['total_commodity_price'] = $orderInfo['total_commodity_price'] - $integral;
			}
			M('order')->where("id=" . $orderInfo['id'])->setField('integral', $integral);
		}
		//商户订单号，商户网站订单系统中唯一订单号，必填
		$out_trade_no = make_order_no();
		//订单名称，必填
		$proName = trim('商城订单');
		//付款金额，必填
		if ($orderInfo['order_type'] == 11)
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_express_fee']);//trim($_POST['WIDtotal_amount']);
		}
		else
		{
			$total_amount = sprintf("%.2f", $orderInfo['total_commodity_price']);//trim($_POST['WIDtotal_amount']);
		}
		//商品描述，可空
		$body = '商城消费';//trim($_POST['WIDbody']);
		Vendor('Alipay.aop.AopClient');
		Vendor('Alipay.aop.request.AlipayTradePagePayRequest');
		//请求
		$c = new \AopClient();
		$config = get_config_pay3(2);
		$c->gatewayUrl = "https://openapi.alipay.com/gateway.do";
		$c->appId = $config['appid']; //appid
		$c->rsaPrivateKey = $config['key1']; //商户私钥
		$c->format = "json";
		$c->charset = "UTF-8";
		$c->signType = "RSA2";
		$c->alipayrsaPublicKey = $config['key2']; //支付宝公钥
		$request = new \AlipayTradePagePayRequest();
		$request->setReturnUrl(C('C_HTTP_HOST') . $config['return_url']); //同步回调
		$request->setNotifyUrl(C('C_HTTP_HOST') . $config['notify_url']); //异步回调
		$request->setBizContent(
			"{" .
			"    \"product_code\":\"FAST_INSTANT_TRADE_PAY\"," .
			"    \"subject\":\"$proName\"," .
			"    \"out_trade_no\":\"$out_trade_no\"," .
			"    \"total_amount\":$total_amount," .
			"    \"body\":\"$body\"" .
			"  }"
		);
		$result = $c->pageExecute($request);
		// 写入第三方支付记录表
		$data_log3["serial_no"] = $out_trade_no;
		$data_log3["user_id"] = $orderInfo["user_id"];
		$data_log3["order_id"] = $orderInfo["id"];
		$data_log3["order_no"] = $orderInfo["order_no"];
		$data_log3["money"] = $total_amount;
		$data_log3["order_type"] = $orderInfo["order_type"];
		$data_log3["type"] = 7;
		$data_log3["status"] = 0;
		$data_log3["cre_time"] = time();
		$data_log3["cre_date"] = date("Y-m-d H:i:s");
		$data_log3["ip"] = get_client_ip();
		$log3 = M("pay3_log")->add($data_log3);
		if (!$log3)
		{
			return array(0, "订单支付异常，请联系客服");
		}
		//输出
		echo $result;

	}

	/**
	 * 购物车删除单个商品
	 * @param user_id goods_id
	 * return true/false
	 * sunhaiyang
	 */
	public function one_cart_delete()
	{
		$user_id = I('user_id');
		$goods_id = I('goods_id');
		if (!$user_id || !$goods_id)
		{
			jsonout('参数异常');
		}
		$res = M('cart')->where('goods_id=' . $goods_id . ' and user_id=' . $user_id)->delete();
		return $res;

	}

	/**
	 * @param $order_id
	 * Notes:添加成交记录
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 16:00
	 */
	public function add_transaction_record($order_id)
	{
		$order_goods = M('order_goods')->where('order_id=' . $order_id)->select();
		$where['id'] = $order_goods[0]['user_id'];
		$user = get_user_info($where, '*', 0, 1);
		if ($user['nickname'])
		{
			$buyer_name = $user['nickname'];
		}
		elseif ($user['realname'])
		{
			$buyer_name = $user['realname'];
		}
		else
		{
			$buyer_name = $user['phone'];
		}
		if (!$user['headimgurl'])
		{
			$user['headimgurl'] = '/Public/images/profile.png';
		}
		$new_data = array();
		foreach ($order_goods as $k => $v)
		{
			$new_data[$k]['order_goods_id'] = $v['id'];
			$new_data[$k]['buyer_name'] = $buyer_name;
			$new_data[$k]['goods_id'] = $v['goods_id'];
			$new_data[$k]['goods_name'] = $v['goods_name'];
			$new_data[$k]['goods_thumb'] = $v['goods_thumb'];
			$new_data[$k]['goods_spec_price_id'] = $v['goods_spec_price_id'];
			$new_data[$k]['goods_spec_price_name'] = $v['goods_spec_price_name'];
			$new_data[$k]['num'] = $v['goods_num'];
			$new_data[$k]['buy_time'] = $v['pay_time'];
			$new_data[$k]['headimgurl'] = $user['headimgurl'];
		}
		M('transaction_record')->addAll($new_data);
	}

	/**
	 * Title：生成预分销日志
	 * Note：
	 * @param int $order_id 主订单ID
	 * User： zd
	 * Date： 2020/7/10 20:54
	 */
	public function make_distribution_log($order_id = 0)
	{
		if (!$order_id)
		{
			return true;
		}
		$res = M("distribution_log")->where("order_id=" . $order_id)->find();
		if ($res)
		{
			return true;
		}
		$orderInfo = M("order")->where("id=" . $order_id)->find();
		if (!$orderInfo || $orderInfo['isdel'] == 1 || $orderInfo['order_status'] == 4 || $orderInfo['order_status'] < 0)
		{
			return true;
		}
		/*
		 * 先分用户的：
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

		/*
		 * 先分用户的：
		 * 再分商家和代理的：
		 * */

		$spArr = array();    // 商家和平台组合的数组

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
						$levelInfo = get_config_user_level($firstInfo['level']);
						if ($firstInfo && $firstInfo['status'] == 1 && $firstInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
						{
							$award_mode_1 = $levelInfo['buy_award_mode'];
							$first_money = abs($levelInfo['buy_award_first_money']);
							$first_point = abs($levelInfo['buy_award_first_point']);
						}
						$levelInfo = get_config_user_level($secondInfo['level']);
						if ($secondInfo && $secondInfo['status'] == 1 && $secondInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
						{
							$award_mode_2 = $levelInfo['buy_award_mode'];
							$second_money = abs($levelInfo['buy_award_second_money']);
							$second_point = abs($levelInfo['buy_award_second_point']);
						}
						$levelInfo = get_config_user_level($thirdInfo['level']);
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
					$levelInfo = get_config_user_level($firstInfo['level']);
					if ($firstInfo && $firstInfo['status'] == 1 && $firstInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
					{
						$award_mode_1 = $levelInfo['buy_award_mode'];
						$first_money = abs($levelInfo['buy_award_first_money']);
						$first_point = abs($levelInfo['buy_award_first_point']);
					}
					$levelInfo = get_config_user_level($secondInfo['level']);
					if ($secondInfo && $secondInfo['status'] == 1 && $secondInfo['is_del'] == 0 && $levelInfo['buy_award_status'])
					{
						$award_mode_2 = $levelInfo['buy_award_mode'];
						$second_money = abs($levelInfo['buy_award_second_money']);
						$second_point = abs($levelInfo['buy_award_second_point']);
					}
					$levelInfo = get_config_user_level($thirdInfo['level']);
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

			// 预分销日志数组公共部分
			$add_dist_log['order_profit_time'] = 0;
			$add_dist_log['cre_date'] = date("Y-m-d H:i:s");
			$add_dist_log['cre_time'] = time();
			$add_dist_log['ip'] = get_client_ip();
			$add_dist_log['order_id'] = $order_id;
			$add_dist_log['order_goods_id'] = $value['id'];
			$add_dist_log['son_order_no'] = $value['order_no'];
			$add_dist_log['goods_id'] = $value['goods_id'];
			$add_dist_log['goods_name'] = $value['goods_name'];
			$add_dist_log['goods_thumb'] = $value['goods_thumb'];
			$add_dist_log['goods_purchase_price'] = $value['goods_purchase_price'];
			$add_dist_log['goods_price'] = $value['goods_price'];
			$add_dist_log['goods_num'] = $value['goods_num'];
			$add_dist_log['goods_total'] = $value['goods_total'];
			$add_dist_log['goods_spec_price_id'] = $value['goods_spec_price_id'];
			$add_dist_log['goods_spec_price_name'] = $value['goods_spec_price_name'];
			$add_dist_log['shop_id'] = $value['shop_id'];
			$add_dist_log['order_type'] = $value['order_type'];
			$add_dist_log['profit_space'] = $value['profit_space'];
			$add_dist_log['is_profit'] = 0;
			$add_dist_log['order_cre_time'] = $value['add_time'];

			if ($self_money > 0)
			{
				$add_dist_log['user_id'] = $userInfo['id'];
				$add_dist_log['utype'] = 1;
				$add_dist_log['son_user_id'] = $userInfo['id'];
				$add_dist_log['son_utype'] = 1;
				$add_dist_log['money_field'] = "money";
				$add_dist_log['money'] = $self_money;
				$add_dist_log['type'] = 1;
				$add_dist_log['deal_type'] = 16;
				$add_dist_log['remark'] = "商品自购提成-余额";
				$add_dist_log['op_type'] = 1;
				$add_dist_log['level'] = $userInfo['level'];
				$add_dist_log['son_level'] = $userInfo['level'];
				add_distribution_log($add_dist_log);
			}
			if ($self_point > 0)
			{
				$add_dist_log['user_id'] = $userInfo['id'];
				$add_dist_log['utype'] = 1;
				$add_dist_log['son_user_id'] = $userInfo['id'];
				$add_dist_log['son_utype'] = 1;
				$add_dist_log['money_field'] = "score";
				$add_dist_log['money'] = $self_point;
				$add_dist_log['type'] = 16;
				$add_dist_log['deal_type'] = 16;
				$add_dist_log['remark'] = "商品自购提成-积分";
				$add_dist_log['op_type'] = 1;
				$add_dist_log['level'] = $userInfo['level'];
				$add_dist_log['son_level'] = $userInfo['level'];
				add_distribution_log($add_dist_log);
			}

			if ($first_money > 0)
			{
				$add_dist_log['user_id'] = $firstInfo['id'];
				$add_dist_log['utype'] = 1;
				$add_dist_log['son_user_id'] = $userInfo['id'];
				$add_dist_log['son_utype'] = 1;
				$add_dist_log['money_field'] = "money";
				$add_dist_log['money'] = $first_money;
				$add_dist_log['type'] = 1;
				$add_dist_log['deal_type'] = 17;
				$add_dist_log['remark'] = "下一级{$userInfo['phone']}消费提成-余额";
				$add_dist_log['op_type'] = 1;
				$add_dist_log['level'] = $firstInfo['level'];
				$add_dist_log['son_level'] = $userInfo['level'];
				add_distribution_log($add_dist_log);
			}
			if ($first_point > 0)
			{
				$add_dist_log['user_id'] = $firstInfo['id'];
				$add_dist_log['utype'] = 1;
				$add_dist_log['son_user_id'] = $userInfo['id'];
				$add_dist_log['son_utype'] = 1;
				$add_dist_log['money_field'] = "score";
				$add_dist_log['money'] = $first_point;
				$add_dist_log['type'] = 16;
				$add_dist_log['deal_type'] = 17;
				$add_dist_log['remark'] = "下一级{$userInfo['phone']}消费提成-积分";
				$add_dist_log['op_type'] = 1;
				$add_dist_log['level'] = $firstInfo['level'];
				$add_dist_log['son_level'] = $userInfo['level'];
				add_distribution_log($add_dist_log);
			}
			if ($second_money > 0)
			{
				$add_dist_log['user_id'] = $secondInfo['id'];
				$add_dist_log['utype'] = 1;
				$add_dist_log['son_user_id'] = $userInfo['id'];
				$add_dist_log['son_utype'] = 1;
				$add_dist_log['money_field'] = "money";
				$add_dist_log['money'] = $second_money;
				$add_dist_log['type'] = 1;
				$add_dist_log['deal_type'] = 18;
				$add_dist_log['remark'] = "下二级{$userInfo['phone']}消费提成-余额";
				$add_dist_log['op_type'] = 1;
				$add_dist_log['level'] = $secondInfo['level'];
				$add_dist_log['son_level'] = $userInfo['level'];
				add_distribution_log($add_dist_log);
			}
			if ($second_point > 0)
			{
				$add_dist_log['user_id'] = $secondInfo['id'];
				$add_dist_log['utype'] = 1;
				$add_dist_log['son_user_id'] = $userInfo['id'];
				$add_dist_log['son_utype'] = 1;
				$add_dist_log['money_field'] = "score";
				$add_dist_log['money'] = $second_point;
				$add_dist_log['type'] = 16;
				$add_dist_log['deal_type'] = 18;
				$add_dist_log['remark'] = "下二级{$userInfo['phone']}消费提成-积分";
				$add_dist_log['op_type'] = 1;
				$add_dist_log['level'] = $secondInfo['level'];
				$add_dist_log['son_level'] = $userInfo['level'];
				add_distribution_log($add_dist_log);
			}
			if ($third_money > 0)
			{
				$add_dist_log['user_id'] = $thirdInfo['id'];
				$add_dist_log['utype'] = 1;
				$add_dist_log['son_user_id'] = $userInfo['id'];
				$add_dist_log['son_utype'] = 1;
				$add_dist_log['money_field'] = "money";
				$add_dist_log['money'] = $third_money;
				$add_dist_log['type'] = 1;
				$add_dist_log['deal_type'] = 19;
				$add_dist_log['remark'] = "下三级{$userInfo['phone']}消费提成-余额";
				$add_dist_log['op_type'] = 1;
				$add_dist_log['level'] = $thirdInfo['level'];
				$add_dist_log['son_level'] = $userInfo['level'];
				add_distribution_log($add_dist_log);
			}
			if ($third_point > 0)
			{
				$add_dist_log['user_id'] = $thirdInfo['id'];
				$add_dist_log['utype'] = 1;
				$add_dist_log['son_user_id'] = $userInfo['id'];
				$add_dist_log['son_utype'] = 1;
				$add_dist_log['money_field'] = "score";
				$add_dist_log['money'] = $third_point;
				$add_dist_log['type'] = 16;
				$add_dist_log['deal_type'] = 19;
				$add_dist_log['remark'] = "下三级{$userInfo['phone']}消费提成-积分";
				$add_dist_log['op_type'] = 1;
				$add_dist_log['level'] = $thirdInfo['level'];
				$add_dist_log['son_level'] = $userInfo['level'];
				add_distribution_log($add_dist_log);
			}


			// 分商家和代理的
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
				if ($shop_amount > 0)
				{
					$add_dist_log['user_id'] = $shopInfo['user_id'];
					$add_dist_log['utype'] = 2;
					$add_dist_log['son_user_id'] = $userInfo['id'];
					$add_dist_log['son_utype'] = 1;
					$add_dist_log['money_field'] = "shop_money";
					$add_dist_log['money'] = $shop_amount;
					$add_dist_log['type'] = 2;
					$add_dist_log['deal_type'] = 2;
					$add_dist_log['remark'] = "用户{$userInfo['phone']}消费店铺增加营业额";
					$add_dist_log['op_type'] = 1;
					$add_dist_log['level'] = 0;
					$add_dist_log['son_level'] = $userInfo['level'];
					add_distribution_log($add_dist_log);
				}

				// 县级代理提成
				if ($shopInfo["district_id"] > 0 && $orderInfo['is_profit_3'] == 0)
				{
					$where_dist["isdel"] = 0;
					$where_dist["area_id"] = $shopInfo["district_id"];
					$distInfo = get_user_info($where_dist, '', '', 3);
					if ($distInfo)
					{
						// 正常代理
						$district_comm = get_config_fx("district_comm");
						$district_comm = round($district_comm / 100 * $value['goods_total'], 2);
						if ($district_comm > 0)
						{
							$add_dist_log['user_id'] = $distInfo['user_id'];
							$add_dist_log['utype'] = 3;
							$add_dist_log['son_user_id'] = $userInfo['id'];
							$add_dist_log['son_utype'] = 1;
							$add_dist_log['money_field'] = "agent_money";
							$add_dist_log['money'] = $district_comm;
							$add_dist_log['type'] = 3;
							$add_dist_log['deal_type'] = 10;
							$add_dist_log['remark'] = "用户{$userInfo['phone']}消费增加区域服务奖励";
							$add_dist_log['op_type'] = 1;
							$add_dist_log['level'] = 0;
							$add_dist_log['son_level'] = $userInfo['level'];
							add_distribution_log($add_dist_log);
						}
					}
				}
			}
		}
	}

	/**
	 * @return int|string
	 * Notes:积分+运费支付判断积分余额是否足够并且存放冻结字段中
	 * User: WangSong
	 * Date: 2020/9/7
	 * Time: 17:50
	 */
	public function check_pwd_score()
	{
		$data = I();
		$user = M('user')->where('id=' . $data['user_id'])->find();
		if (get_pwd($data['pay_password']) != $user['pay_password'])
		{
			return '支付密码不正确';
		}
		$order = M('order')->where('id=' . $data['order_id'])->find();
		if ($order['order_status'] > 0)
		{
			return '该订单已支付';
		}
		if (($user['score'] - $user['wait_score']) < $order['actual_order_total'])
		{
			return '您有' . $user['wait_score'] . '积分待支付,积分余额不足，暂不能支付';
		}
		return 1;

	}

	/**
	 * @param $data
	 * @return array
	 * Notes:运费支付
	 * User: WangSong
	 * Date: 2020/9/8
	 * Time: 9:03
	 */
	public function payOrder1($data)
	{
		if ($data)
		{
			$order_id = $data['order_id'];
			$pay_type = $data['pay_type'];
		}
		else
		{
			$order_id = I('order_id');
			$pay_type = I('pay_type');
		}

		$order = M('order')->where('id=' . $order_id)->find();
		$re = M('user')->where('id=' . $order['user_id'])->setInc('wait_score', $order['actual_order_total']);
		if (!$re)
		{
			return array(1, "操作失败");
		}
		if (round($order['total_express_fee'], 2) <= 0)
		{
			return array(1, "支付金额须大于0元");
		}
		if (!$order || $order["isdel"] == 1)
		{
			return array(1, "订单异常，无法支付，请联系客服");
		}
		if ($order['pay_status'])
		{
			return array(1, "订单已经支付");
		}
		if ($pay_type == 'yepay')
		{
			$res_pay = $this->ye_pay($order_id);
			if ($res_pay[0] < 1)
			{
				// 成功的结果
				$res = $this->pay_success($order_id, $pay_type);
				return array($res[0], $res[1]);
			}
			else
			{
				// 失败的结果
				return array($res_pay[0], $res_pay[1]);
			}
		}
		elseif ($pay_type == 'adminpay')
		{
			$res_pay = $this->pay_success($order_id, $pay_type);
			return $res_pay;
		}
		elseif ($pay_type == 'wxpay')
		{
			// 微信APP支付
			$data = $this->wx_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'xcxpay')
		{
			// 微信小程序支付
			$data = $this->wxxcx_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'wxmp')
		{
			// 微信wap支付
			$data = $this->wxmp_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'alipay')
		{
			// 支付宝APP支付
			$data = $this->ali_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'aliwap')
		{
			// 支付宝wap支付
			$data = $this->aliwap_pay($order_id);
			return array($data[0], $data[1]);
		}
		elseif ($pay_type == 'alipc')
		{
			// 支付宝PC支付
			return array(1, "配置异常，请联络技术处理");
		}
		elseif ($pay_type == 'alimini')
		{
			// 支付宝小程序支付
			return array(1, "配置异常，请联络技术处理");
		}
		elseif ($pay_type == 'wxnative')
		{
			// 支付宝小程序支付
			return array(1, "配置异常，请联络技术处理");
		}
		elseif ($pay_type == 'wxqrcode')
		{
			// 支付宝小程序支付
			return array(1, "配置异常，请联络技术处理");
		}
		elseif ($pay_type == 'wait_pay')
		{
			//积分支付
			$res_pay = $this->wait_pay($order_id);
			if ($res_pay[0] < 1)
			{
				// 成功的结果
				$res = $this->pay_success($order_id, $pay_type);
				return array($res[0], $res[1]);
			}
			else
			{
				// 失败的结果
				return array($res_pay[0], $res_pay[1]);
			}
		}
		else
		{
			return array(1, "请选择支付方式");
		}

	}

	/**
	 * @param $date
	 * @return int|string
	 * Notes:商家后台发货（移动端）
	 * User: WangSong
	 * Date: 2020/9/18
	 * Time: 16:56
	 */
	public function shop_getDelivery($date)
	{

		if (!$date)
		{
			$date = I();
		}
		$order = array();
		if ($date['type'] == 1)
		{

			if (is_array($date['arr']))
			{
				$arr = $date['arr'];
			}
			else
			{
				$arr1 = htmlspecialchars_decode($date['arr']);
				$arr = json_decode($arr1, true);
			}
			$goods_name = '';
			$is_express = 0;
			foreach ($arr as $k => $v)
			{

				if ($v['express_num'] && $v['express_id'] > 0)
				{
					$is_express = 1;
				}
				$order = M('order_goods')->where('id=' . $v['id'])->find();
				$goods_name .= $order["goods_name"] . "，数量为：" . $order["goods_num"] . "，";
				if (!$order['shipping_status'])
				{
					if ($v['express_num'] && $v['express_id'] > 0)
					{
						$name = get_express($v['express_id']);
						$data = array(
							'order_status' => 2,
							'shipping_status' => 1,
							'express_id' => $v['express_id'],
							'express_name' => $name['name'],
							'tracking_number' => $v['express_num'],
							'shipping_time' => time()
						);
						M('order_goods')->where('id=' . $v['id'])->save($data);

						add_order_log($order['order_id'], "订单发货", '订单号：' . $v['express_num'] . "商品为：" . $goods_name);
					}

				}
			}
			if ($is_express == 0)
			{
				return "请选择物流公司以及输入物流单号";
			}
			//完善订单日志
		}
		else
		{
			if (!$date['express_id'] && !$date['express_num'])
			{
				return "请选择物流公司以及输入物流单号";
			}
			$orders = M('order_goods')->where('order_id=' . $date['order_id'] . ' and shop_id=' . $date['shop_id'])->select();
			$goods_name = '';
			foreach ($orders as $k => $v)
			{
				$order = $v;
				$goods_name .= $v["goods_name"] . "，数量为：" . $v["goods_num"] . "，";
				if (!$v['shipping_status'])
				{
					$name = get_express($date['express_id']);
					$data = array(
						'order_status' => 2,
						'shipping_status' => 1,
						'express_id' => $date['express_id'],
						'express_name' => $name['name'],
						'tracking_number' => $date['tracking_number'],
						'shipping_time' => time()
					);
					M('order_goods')->where('id=' . $v['id'])->save($data);
				}

			}
			if ($goods_name)
			{
				add_order_log($date['order_id'], "订单发货", '订单号：' . $date['tracking_number'] . "商品为：" . $goods_name);

			}

		}

		$order_id = $order['order_id'];

		$order = M('order_goods')->where('order_status=1 and order_id=' . $order_id)->find();
		if (!$order)
		{
			$data_order = array(
				'order_status' => 2,
				'shipping_status' => 1,
				'shipping_time' => time(),
				'shop_note' => '',
			);
			M('order')->where('id=' . $order_id)->save($data_order);
			/*$goods_name = '';
			$order_goods = M("order_goods")->where("order_id=".$order_id)->select();
			foreach ($order_goods as $k=>$v){
				$goods_name .= $v["goods_name"].",数量为：".$v["goods_num"];
			}
			//完善订单日志
			add_order_log($order_id, '订单发货成功,发货商品为：'.$goods_name);*/
		} /*else {
            $data_order = array(
                'order_status' => -2,
                'shop_note' => $shop_note,
            );
            M('order')->where('id=' . $order_id)->save($data_order);
            //完善订单日志

            $goods_name = '';
            $order_goods = M("order_goods")->where("order_status=1 and order_id=" . $order_id)->select();
            foreach ($order_goods as $k=>$v){
                $goods_name .= $v["goods_name"].",";
            }
            add_order_log($order_id, '会员申请退货,商品为：'.$goods_name);
        }*/
		return 1;

	}

	/**
	 * Notes:根据物流单号进行确认收货
	 * User: WangSong
	 * Date: 2020/9/24
	 * Time: 8:52
	 */
	public function takeNumDelivery()
	{

		$order_id = I('order_id', 0, 'intval');
		$user_id = I('user_id', 0, 'intval');
		$shop_id = I('shop_id');
		$tracking_number = I('tracking_number');
		$where['order_id'] = array('eq', $order_id);
		$where['shop_id'] = array('eq', $shop_id);
		$where['tracking_number'] = array('eq', $tracking_number);
		$order_godos = M("order_goods")->where('order_id=' . $order_id . ' and (order_status=2 or order_status=1) and shop_id=' . $shop_id)->select();

		$goods_name = '';
		foreach ($order_godos as $k => $v)
		{
			$goods_name .= $v["goods_name"] . ",数量为：" . $v["goods_num"] . "，";
		}

		$data = array(
			'order_status' => 3,
			'shipping_status' => 2,
			'confirm_time' => time()
		);
		M('order_goods')->where($where)->save($data);
		add_order_log($order_id, "确认收货", '商品' . $goods_name);
		$new_order_goods = M('order_goods')->where('order_id=' . $order_id . ' and order_status=2')->find();
		$data_order = array(
			'order_status' => 3,
			'shipping_status' => 2,
			'confirm_time' => time()
		);
		if (!$new_order_goods)
		{
			M('order')->where('id=' . $order_id)->save($data_order);
			//完善订单日志
			//如果是自提的订单 就修改订单自提表的信息
			M('order_picksite')->where('order_id=' . $order_id)->setField('status', 1);//订单完成
			// 分销逻辑处理
			order_logic($order_id);

		}
		add_order_log($order_id, "确认收货", '商品名称：' . $goods_name);
		return true;
	}

}