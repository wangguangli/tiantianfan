<?php

namespace Clas;

use Think\Page;

/**
 * 所有订单类信息展示模块
 */
class Orderout
{

	private $user_id;

	public function __construct($user_id)
	{
		$this->user_id = $user_id;
	}

	/**
	 * 获取商品信息
	 * @para  goods_kind     商品打开来源，0常规，1拼团，2整点秒杀，3限时购
	 * @para  city_id         城市ID，用为计算运费
	 * @return  array     商品详细信息数据
	 * @author  史超
	 * 说明：商品详细信息数据
	 */
	public function getBuyInfo($goods, $goods_kind = 0, $city_id = 0, $address_id = 0)
	{

		//获取生成订单的所有商品
		if (!empty($goods))
		{
			$goodsList = $this->getGoodsList($goods, $goods_kind, $city_id);
		}
		else
		{
			$goodsList = $this->getGoodsList("", $goods_kind, $city_id);
		}
		if (!is_array($goodsList))
		{
			return $goodsList;
		}

		$group_order_id = I('group_order_id', 0, 'intval');

		// 检测可用优惠券
		$couponList = $goodsList['coupon_list'];
		$couponAuto = $goodsList['coupon_auto'];

		if (!$couponList)
		{
			$couponList = array();
		}
		if (!$couponAuto)
		{
			$couponAuto = array();
		}
		if (empty($goods))
		{
			$goods = $this->getGoodsInfo();
		}
		$ztd_data = $this->getPickSite(json_encode($goods), $address_id);
		return array(
			'goods_list' => $goodsList['goods_list'],
			'total_price' => $goodsList['total_price'],
			'total_goods_price' => $goodsList['total_goods_price'],
			'total_express_fee' => $goodsList['total_express_fee'],
			'goods_total_leader_money' => $goodsList['goods_total_leader_money'],//拼团发起人优惠
			'group_order_id' => $group_order_id,
			'coupon_list' => $couponList,
			'coupon_auto' => $couponAuto,
			'ztd_data' => $ztd_data['picksite'],
		);
	}

	/**
	 * 获取商品信息
	 * @para  goods_kind     商品打开来源，0常规，1拼团，2整点秒杀，3限时购
	 * @return  array     商品详细信息数据
	 * @author  史超
	 * 说明：商品详细信息数据
	 */
	private function getGoodsList($goods, $goods_kind = 0, $city_id = 0)
	{
		//获取商品信息

		if (empty($goods))
		{
			$goods = $this->getGoodsInfo();
		}
		if (!$goods)
		{
			return '没有选择商品';
		}
		$total_price = 0;
		$total_express_fee = 0;
		$couponGoodsArr = array();    // 优惠券使用的商品数组
		$couponList = array();
		$couponAuto = array();
		$goods_total_leader_money = 0;

		if (count($goods) == count($goods, 1))
		{
			$couponGoodsArr[0]['id'] = $goods['goods_id'];
			$couponGoodsArr[0]['spec_id'] = $goods['goods_spec_id'];
			$couponGoodsArr[0]['num'] = $goods['goods_num'];

			$goodsDetail = $this->getGoodsDetail($goods['goods_id'], $goods['goods_num'], $goods['goods_spec_id'], $goods['id'] ? $goods['id'] : 0, $goods_kind, $city_id);
			if (!is_array($goodsDetail))
			{
				return $goodsDetail;
			}
			$total_price += $goodsDetail['goods_total'];
			$goods_total_leader_money += $goodsDetail['goods_total_leader_money'];
			$total_express_fee += $goodsDetail['shipping_fee'];
			$goods_list[$goodsDetail['shop_id']][] = $goodsDetail;
		}
		else
		{
			foreach ($goods as $k => $v)
			{
				//获取商品信息
				$CGA['id'] = $v['goods_id'];
				$CGA['spec_key'] = $v['goods_spec_id'];
				$CGA['num'] = $v['goods_num'];
				$couponGoodsArr[] = $CGA;

				$goodsDetail = $this->getGoodsDetail($v['goods_id'], $v['goods_num'], $v['goods_spec_id'], $v['cart_id'], $goods_kind, $city_id);
				if (!is_array($goodsDetail))
				{
					return $goodsDetail;
				}
				$total_price += $goodsDetail['goods_total'];
				$total_express_fee += $goodsDetail['shipping_fee'];
				$goods_total_leader_money += $goodsDetail['goods_total_leader_money'];
				$goods_list[$goodsDetail['shop_id']][] = $goodsDetail;
				if ($goodsDetail['is_coupon']==1){
                    $is_coupon=1;
                }
			}
		}

		//运费订单都算一件

        $total_express_fee = $total_express_fee /count($goods) ;


		$total_goods_price = round($total_price,2);
		$total_price = round($total_price + $total_express_fee, 2);

		// 检测优惠券使用列表
		$couponAllList = check_goods_use_coupon($this->user_id, $couponGoodsArr);
        $couponList = $couponAllList['list'];

//		if ($is_coupon==1){
//            $couponList = $couponAllList['list'];
//        }else{
//            $couponList=array();
//        }

		if ($couponAllList['auto'])
		{
			$couponAuto[0] = $couponAllList['auto'][0];
		}



		return array(
			'total_price' => (string)$total_price,
			'total_goods_price' => (string)$total_goods_price,
			'goods_list' => $goods_list,
			'total_express_fee' => (string)$total_express_fee,
			'coupon_list' => $couponList,    // 需要选择的可使用优惠券
			'coupon_auto' => $couponAuto,    // 不需要选择的优惠券，自动使用
			'goods_total_leader_money' => $goods_total_leader_money//拼团发起人优惠
		);
	}

	/**
	 * 获取商品信息
	 * @return  array     商品详细信息数据
	 * @author  史超
	 * 说明：商品详细信息数据
	 */
	private function getGoodsDetail($goods_id, $goods_num, $spec_key, $cart_id = 0, $goods_kind = 0, $city_id = 0)
	{
		$goods = $this->getGoods($goods_id);

		if (empty($goods) || empty($goods['is_on_sale']))
		{
			return '商品不存在或已下架';
		}
		if (!is_numeric($goods_num) || $goods_num < 1)
		{
			return '请输入正确的购买数量';
		}
		if ($goods_num > $goods['store_count'])
		{
			return '商品库存不足';
		}

		if (empty($spec_key))
		{
			$price = $goods['price'];
			$market_price = $goods['market_price'];
			$thumb = $goods['thumb'];
			$spec_name = '';
			$goods_spec_price_id = 0;
			$group_price = 0;
			$hour_price = 0;
			$time_price = 0;
		}
		else
		{
			$goods_spec = $this->getGoodsSpec($goods_id, $spec_key);
			$price = $goods_spec['price'];
			$thumb = $goods_spec['thumb'] ? $goods_spec['thumb'] : $goods['thumb'];
			$spec_name = $goods_spec['key_name'];
			$goods_spec_price_id = $goods_spec['id'];

			$group_price = $goods_spec['group_price'];
			$hour_price = $goods_spec['hour_price'];
			$time_price = $goods_spec['time_price'];

		}
		$subhead = $goods['subhead'];
		$team_leader_free = 0;
		$team_leader_free_money = 0;
		if ($goods_kind == 1)
		{//拼团发起人优惠
			$price2 = $price;
			$price = $group_price;
			$group = M('goods_group')->where('is_open = 1 and goods_id =' . $goods_id)->find();
			if ($group['team_goods_num'] > 0)
			{//拼团发起人数量限制
				if ($goods_num > $group['team_goods_num'])
				{
					return '数量超出限制';
				}
			}
			$group_order_id = I('group_order_id', 0, 'intval');
			if ($group_order_id == 0)
			{//拼团发起人优惠
				$team_leader_free = $group['team_leader_free'];
				if ($group['team_leader_free'] == 1)
				{
					$team_leader_free_money = $price;
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
				$good_count = M('order_goods')->where(array('order_id' => $group_order_id, 'goods_id' => $goods_id))->find();
				if (!$good_count)
				{
					return '团购商品不一致';
				}
				$user_ids = M('order')->where(array('group_order_id' => $group_order_id))->getField('user_id', true);
				$user_id = I('user_id');
				if (in_array($user_id, $user_ids))
				{
					return '团购已参与';
				}
			}

		}
		elseif ($goods_kind == 2)
		{
			$price2 = $price;
			$price = $hour_price;
		}
		elseif ($goods_kind == 3)
		{
			$price2 = $price;
			$price = $time_price;
		}
		else
		{
			$price2 = $price;
		}
		$shipping_fee = goods_express_fee($goods_id, $goods_num, $goods_spec_price_id, $city_id);
		$account_payable = $goods_total = $price * $goods_num - $team_leader_free_money * $goods_num;
		$thumb = full_pre_url($thumb);

		$newGoods = array(
			'goods_id' => $goods_id,
			'goods_name' => $goods['name'],
			'goods_thumb' => $thumb,
			'goods_subhead' => $subhead,
			'goods_price' => (string)sprintf("%.2f", $price ? $price : 0),
			'goods_price_2' => (string)sprintf("%.2f", $price2 ? $price2 : 0),
			'goods_market_price' => (string)sprintf("%.2f", $market_price ? $market_price : 0),
			'goods_num' => $goods_num,
			'spec_name' => $spec_name,
			'goods_spec_id' => $spec_key,
			'goods_spec_price_id' => $goods_spec_price_id,
			'goods_total' => (string)sprintf("%.2f", $goods_total ? $goods_total : 0),
			'cart_id' => (string)($cart_id ? $cart_id : 0),
			'shop_id' => $goods['shop_id'],
			'group_price' => $group_price,
			'hour_price' => $hour_price,
			'time_price' => $time_price,
			'shipping_fee' => $shipping_fee,
			'team_leader_free' => $team_leader_free,
			'team_leader_free_money' => $team_leader_free_money,
			'shipping_type' => $goods['shipping_type'],
			'product_type' => $goods['product_type'],
			'goods_total_leader_money' => (string)sprintf("%.2f", $team_leader_free_money * $goods_num),//拼团发起人总优惠
            'is_shipping' => $goods['is_shipping'],
            'is_pick' => $goods['is_pick'],
            'is_coupon' => $goods['is_coupon'],
        );
		return $newGoods;
	}

	/**
	 * 获取商品信息
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
	 * 获取商品信息
	 * @return  array     商品详细信息数据
	 * @author  史超
	 * 说明：商品详细信息数据
	 */
	public function orderDetail($order_id,$shop_id)
	{
		//获取生成订单的所有商品(就是为了多跳几下)
		$orderDetail = $this->getOrderDetail($order_id,$shop_id);

		if ($orderDetail['err'])
		{
			return $orderDetail;
		}
        $order_end_time = get_config('order_end_time');//获取倒计时时间
        $order = $orderDetail["order"];
        if(is_numeric($shop_id)){

            if($order['order_status'] != 0){
                $order_goods = M('order_goods')->where('order_id='.$order_id.' and shop_id='.$shop_id.' and order_status=2')->find();

                if($order_goods){
                    $order['status_name'] = get_order_status(2);
                    $order['order_status'] = 2;
                }else{
                    $order_goods = M('order_goods')->where('order_id='.$order_id.' and shop_id='.$shop_id.' and order_status=1')->find();

                    if($order_goods){
                        $order[ 'status_name' ] = get_order_status( 1 );
                        $order[ 'order_status' ] = 1;
                    }else{
                        $order_goods = M('order_goods')->where('order_id='.$order_id.' and shop_id='.$shop_id.' and order_status=3')->find();

                        if($order_goods){
                            $order[ 'status_name' ] = get_order_status( 3 );
                            $order[ 'order_status' ] = 3;
                        }else{
                            $order_goods = M('order_goods')->where('order_id='.$order_id.' and shop_id='.$shop_id)->find();
                            $order[ 'status_name' ] = get_order_status( $order_goods['order_status'] );
                            $order[ 'order_status' ] = $order_goods['order_status'];
                        }
                    }
                }
            }else{
                $order[ 'status_name' ] = get_order_status( 0 );
            }

        }

        $time = $order['add_time'] + $order_end_time*60;
        $new_time = $time - time();
        if($new_time < 0){
            $new_time = 0;
        }

		return array(
			'goods_list' => $orderDetail['goods_list'],
			'address' => $orderDetail['address'],
			'phone' => $orderDetail['phone'],
			'order' => $order,
			'order_end_time' => $new_time,
			'express_url' => "http://m.kuaidi100.com/",
		);
	}

	/**
	 * 获取订单信息
	 * @param json      参照下方单元测试数据传来的数据格式
	 * @param $shop_id      商家id
	 * @return  array     订单信息数据
	 * @author  史超
	 * 说明：订单信息数据
	 */
	public function getOrderDetail($order_id,$shop_id)
	{
		if (empty($order_id))
		{
			$order_id = I('order_id');
            $shop_id = I('shop_id');
		}
		$order = M('order')->where('id=' . $order_id)->find();
		$order['status_name'] = get_order_status($order['order_status']);
		$region = get_region_list();
		$address = $region[$order['province']]['name'];
		$city = $region[$order['city']]['name'];
		//特区无省份
		if ($city == '市辖区' || $city == '县')
		{
			$city = '';
		}
		$address = $address . $city . $region[$order['district']]['name'] . $order['address'];
		$order['address'] = $address;
		if( is_numeric($shop_id) && $order['order_status'] != 0 && $order['order_status'] != 4){
            $goodslist = M('order_goods')->where('order_id=' . $order_id.' and shop_id='.$shop_id)->select();
        }else{
            $goodslist = M('order_goods')->where('order_id=' . $order_id)->select();

        }
		foreach ($goodslist as $k => $v)
		{
			if ($v['shop_id'])
			{
				$shopInfo = M("shop")->where("id=" . $v['shop_id'])->field('id,user_id,shop_name,thumb,tel')->find();
				if ($shopInfo)
				{
					$goods_list[$v['shop_id']]['shop_name'] = $shopInfo["shop_name"];
					$goods_list[$v['shop_id']]['shop_id'] = $shopInfo["id"];
					$goods_list[$v['shop_id']]['thumb'] = $shopInfo["thumb"];
					$goods_list[$v['shop_id']]['phone'] = $shopInfo["tel"];
					$goods_list[$v['shop_id']]['user_note'] = $v["user_note"];
				}
				else
				{
					$goods_list[$v['shop_id']]['shop_name'] = "平台商家";
					$goods_list[$v['shop_id']]['shop_id'] = 0;
					$goods_list[$v['shop_id']]['thumb'] = C("C_DF_SHOP_LOGO");
					$goods_list[$v['shop_id']]['phone'] = get_config("kefu_mobile");
					$goods_list[$v['shop_id']]['user_note'] = $v["user_note"];
				}
			}
			else
			{
				$goods_list[$v['shop_id']]['shop_name'] = '平台自营';
				$goods_list[$v['shop_id']]['shop_id'] = 0;
				$goods_list[$v['shop_id']]['thumb'] = C("C_DF_SHOP_LOGO");
				$goods_list[$v['shop_id']]['phone'] = get_config("kefu_mobile");
                $goods_list[$v['shop_id']]['user_note'] = $v["user_note"];

            }
			$goods_list[$v['shop_id']]['goods_list'][] = $v;
		}

		if ($order['shipping_type'] == 2 && $order['picksite_id'])
		{
			// 自提订单的自提点信息
			$pickInfo = M("goods_picksite")->where("id=" . $order['picksite_id'])->find();
			if ($pickInfo)
			{
				$order['picksite_title'] = $pickInfo['title'];
				$order['picksite_phone'] = $pickInfo['phone'];
				$order['picksite_address'] = get_region_name($pickInfo['province']) . get_region_name($pickInfo['city']) . get_region_name($pickInfo['district']) . $pickInfo['address'];
				$order['picksite_worktime'] = get_week_day($pickInfo['work_day']) . " " . $pickInfo['work_time_start'] . "-" . $pickInfo['work_time_end'];
			}
			else
			{
				$order['picksite_title'] = "";
				$order['picksite_phone'] = "";
				$order['picksite_address'] = "";
				$order['picksite_worktime'] = "";
			}
		}
		$allGoodsList = array_values($goods_list);
		$share['url'] = C("C_HTTP_HOST") . 'M/Goods/detail?goods_kind=1&id=' . $allGoodsList[0]['goods_list'][0]['goods_id'];
		$share['title'] = $allGoodsList[0]['goods_list'][0]['goods_name'];
		$share['thumb'] = $allGoodsList[0]['goods_list'][0]['goods_thumb'];
		$share['desc'] = '我发现了一个便宜的商品,快来参加购买吧';
		if ($order['order_type'] == 5)
		{
			$num = $order['group_people_num'] - $order['group_people_num_have'];
			$share_view['num'] = $num;
			$last_time = $order['group_time_end'] - time();
			$share_view['last_time'] = $last_time;
			if ($last_time > 0 && $num > 0)
			{
				$user_id_arr = M('order')->where('order_status=1 and pay_status=1 and group_order_id=' . $order['group_order_id'])->order('id ASC')->getField('user_id', true);
				if (!empty($user_id_arr))
				{
					$where_header['id'] = array('in', $user_id_arr);
					$share_view['header'] = M('user')->field('headimgurl')->where($where_header)->select();
					$order['share_view'] = $share_view;
				}
//                print_r($share_view['header']);
//                exit();


				$order['is_share'] = 1;
				$order['share'] = $share;
			}
			else
			{
				$order['is_share'] = 0;
			}
		}
		else
		{
			$order['is_share'] = 0;
		}
		$order['payment_type_cn'] = payment_type_cn($order['payment_type']);
		$orderDetail['order'] = $order;
		$orderDetail['address'] = get_order_address($order);
		$orderDetail['goods_list'] = $allGoodsList;
		$orderDetail['phone'] = get_config("kefu_mobile");
		return $orderDetail;
	}

	/**
	 * 获取售后商品信息
	 * @return  array     商品详细信息数据
	 * @author  史超
	 * 说明：商品详细信息数据
	 */
	public function orderRefundDetail($refund_id)
	{
		//获取生成订单的所有商品(就是为了多跳几下)
		$orderDetail = $this->getRefundOrderDetail($refund_id);
		if ($orderDetail['err'])
		{
			return $orderDetail;
		}
		return array(
			'goods_list' => $orderDetail['goods_list'],
			'address' => $orderDetail['address'],
			'phone' => $orderDetail['phone'],
			'order' => $orderDetail['order'],
		);
	}

	/**
	 * 获取订单信息
	 * @param json      参照下方单元测试数据传来的数据格式
	 * @return  array     订单信息数据
	 * @author  史超
	 * 说明：订单信息数据
	 */
	private function getRefundOrderDetail($refund_id)
	{
		if (empty($refund_id))
		{
			$refund_id = I('refund_id');
		}
		$order = M('order_refund')->where('id=' . $refund_id)->find();

		$goods = M('order_goods')->where('id=' . $order['order_goods_id'])->find();
		if ($goods['shop_id'])
		{
			$shopInfo = M("shop")->where("id=" . $goods['shop_id'])->field('id,user_id,shop_name,thumb,tel')->find();
			if ($shopInfo)
			{
				$goods_list['shop_name'] = $shopInfo["shop_name"];
				$goods_list['shop_id'] = $shopInfo["id"];
				$goods_list['thumb'] = $shopInfo["thumb"];
				$goods_list['phone'] = $shopInfo["tel"];
			}
			else
			{
				$goods_list['shop_name'] = "平台商家";
				$goods_list['shop_id'] = 0;
				$goods_list['thumb'] = C("C_DF_SHOP_LOGO");
				$goods_list['phone'] = get_config("kefu_mobile");
			}
		}
		else
		{
			$goods_list['shop_name'] = '平台自营';
			$goods_list['shop_id'] = 0;
			$goods_list['thumb'] = C("C_DF_SHOP_LOGO");
			$goods_list['phone'] = get_config("kefu_mobile");
		}

		$orderDetail['order'] = $order;
		$orderDetail['goods_list'] = $goods_list;
		$orderDetail['phone'] = get_config("kefu_mobile");
		return $orderDetail;
	}

	/**
	 * 获取商品信息
	 * @return  array     商品详细信息数据
	 * @author  史超
	 * 说明：商品详细信息数据
	 */
	public function orderList($data, $order = "")
	{
		$orderList = $this->getOrderList($data, $order);
		return $orderList;
	}

	public function userOrderList($data, $order = ""){
	    $where['isdel'] = 0;
        if ($data['order_status'] >= 0 && is_numeric($data['order_status']))
        {
            $where['order_status'] = $data['order_status'];
        }
        if (intval($data['page']) < 1)
        {
            $page = 1;
        }
        else
        {
            $page = $data['page'];
        }
        $num = 10;
        if($data['user_id']){
            $where['user_id'] = $data['user_id'];
        }
        $where["order_type"] = array('not in','1,2,3,4,12');

        if($data['order_status'] != -1){
            if($data['order_status'] == 0){
                $count = M('order')->where($where)->count();
                $limit = ($page-1)*$num.','.$num;
                $list = M('order')->where($where)->limit($limit)->order('id desc')->select();
                foreach ($list as $key => &$val)
                {
                    $val['order_status_cn'] = get_order_status($val['order_status']);
                    $goodsTotal = M('order_goods')->where('order_id=' . $val['id'])->sum("goods_total");
                    $val['goods_total'] = $goodsTotal > 0 ? $goodsTotal : 0;
                    $order_goods = M('order_goods')->where('order_id=' . $val['id'])->count();
                    if ($order_goods > 1)
                    {
                        $new_order_goods = M('order_goods')->where('order_id=' . $val['id'])->group('shop_id')->field('shop_id')->select();
                        $goods = M('order_goods')->where('order_id=' . $val['id'])->select();
                        $val['goods'] = $goods ? $goods : array();
                        if(count($new_order_goods) > 1){
                            $val['shop_name'] = '平台商城';
                            $val['shop_id'] = 0;
                            $val['thumb'] = C('C_DF_SHOP_LOGO');
                        }else{
                            $shop = M('shop')->where('id=' . $new_order_goods[0]['shop_id'])->find();
                            if($shop){
                                $val['shop_name'] = $shop['shop_name'];
                                $val['shop_id'] = $new_order_goods[0]['shop_id'];
                                $val['thumb'] = full_pre_url($shop['thumb']);
                            }else{
                                $val['shop_name'] = '平台商城';
                                $val['shop_id'] = 0;
                                $val['thumb'] = C('C_DF_SHOP_LOGO');
                            }

                        }
                    }
                    else
                    {
                        $goods = M('order_goods')->where('order_id=' . $val['id'].' and isdel = 0')->find();
                        if ($goods['shop_id'])
                        {
                            $shop = M('shop')->where('id=' . $goods['shop_id'])->find();
                            $val['shop_name'] = $shop['shop_name'];
                            $val['shop_id'] = $goods['shop_id'];
                            $val['thumb'] = full_pre_url($shop['thumb']);
                        }
                        else{
                            $val['shop_name'] = '平台自营';
                            $val['shop_id'] = 0;
                            $val['thumb'] = C('C_DF_SHOP_LOGO');
                        }


                        $val['shop_id'] = $goods['shop_id'] ? $goods['shop_id'] : 0;
                        $goods_one = M('order_goods')->where('order_id=' . $val['id'].' and isdel = 0')->select();
                        $val['goods'] = $goods_one ? $goods_one : array();
                    }
                    $val['order_status_cn'] = get_order_status($val['order_status']);
                    //如果是报单订单，订单金额应改为商家输入金额
                    if ($val['order_type'] == 2)
                    {
                        $val['total_commodity_price'] = $val['shop_note'];
                    }
                    $val['goods_count'] = count($val['goods']);
                    if(!$val['goods']){
                        array_splice($list, $key, 1);
                    }

                }
            }else{

                $new_list = M('order_goods')->where($where)->group('order_id,shop_id')->order('order_id desc')->field('shop_id,order_id as id,order_status')->select();
                $count = count($new_list);
                $list = array_slice($new_list,($page-1)*$num,$num);
                foreach ($list as $k=>&$v){
                    $is_del = 0;
                    if($data['order_status'] == 1){
                        $new_order = M('order_goods')->where('order_id='.$v['id'].' and shop_id='.$v['shop_id'].' and order_status=2')->find();
                        if($new_order){
                            $is_del = 1;
                        }
                    }
                    if($data['order_status'] == 3){
                        $new_order = M('order_goods')->where('order_id='.$v['id'].' and shop_id='.$v['shop_id'].' and (order_status=2 or order_status=1)')->find();
                        if($new_order){
                            $is_del = 1;
                        }
                    }

                    $shop = M('shop')->where('id=' . $v['shop_id'])->find();
                    $v['order_status_cn'] = get_order_status($v['order_status']);
                    $goods = M('order_goods')->where('order_id=' . $v['id'].' and shop_id='.$v['shop_id'])->select();
                    $v['total_commodity_price'] = M('order_goods')->where('order_id=' . $v['id'].' and shop_id='.$v['shop_id'])->sum('goods_total');
                    $v['goods'] = $goods ? $goods : array();
                    if($shop){
                        $v['shop_name'] = $shop['shop_name'];
                        $v['thumb'] = full_pre_url($shop['thumb']);
                        $v['goods_count'] = count($goods);
                    }else{
                        $v['shop_name'] = '平台自营';
                        $v['shop_id'] = 0;
                        $v['thumb'] = C('C_DF_SHOP_LOGO');
                        $v['goods_count'] = count($goods);
                    }
                    if($is_del == 1){
                        array_splice($list, $k, 1);
                    }
                    if(!$v['goods']){
                        array_splice($list, $k, 1);
                    }
                }

            }
        }else{
           $where1['user_id'] = array('eq',$data['user_id']);
           $where1['order_status'] = array('eq',0);
           $where1['order_type'] = array('not in','1,2,3,4');
           $count1 = M('order')->where($where1)->count();
           $where2['user_id'] = array('eq',$data['user_id']);
           $where2['order_status'] = array('gt',0);
           $where2['order_type'] = array('not in','1,2,3,4');
           $where2['isdel'] = array('not in','1,2,3,4');
           $count2 = M('order_goods')->where($where2)->count();
           $count = $count1 + $count2;
            $limit = ($page-1)*$num.','.$num;
            $order_list = M('order')->where($where)->limit($limit)->order('id desc')->select();
            $list = array();

            foreach ($order_list as $key => &$val)
            {
                $goodsTotal = M('order_goods')->where('order_id=' . $val['id'].' and isdel = 0')->sum("goods_total");
                $val['goods_total'] = $goodsTotal > 0 ? $goodsTotal : 0;
                $order_goods = M('order_goods')->where('order_id=' . $val['id'])->count();
                if ($order_goods > 1 )
                {
                    $order_goods_k = 0;
                    $order_goods_1 = M('order_goods')->where('order_id='.$val['id'].' and isdel = 0')->group('shop_id')->field('shop_id,order_status')->select();
                    if(count($order_goods_1) > 1 && $val['order_status'] == 0){
                       $order_goods3 =M('order_goods')->where('order_id='.$val['id'].' and isdel = 0')->select();
                        $val['order_status_cn'] = get_order_status($val['order_status']);
                       $val['goods'] = $order_goods3 ? $order_goods3 : array();
                       $val['shop_name'] = '平台自营';
                       $val['shop_id'] = 0;
                       $val['thumb'] = C('C_DF_SHOP_LOGO');
                        if($val['goods']){
                            $val['goods_count'] = count($order_goods3);
                            array_push($list,$val);
                        }
                    }else{
                        foreach ($order_goods_1 as $k=>$v){
                            $order_goods_2 = M('order_goods')->where('order_id='.$val['id'].' and shop_id='.$v['shop_id'].' and isdel = 0')->select();
                            $val['goods'] = $order_goods_2 ? $order_goods_2 : array();
                            $shop = M('shop')->where('id=' . $v['shop_id'])->find();
                            $val['order_status_cn'] = get_order_status($val['order_status']);
                            if($shop){
                                $val['shop_name'] = $shop['shop_name'];
                                $val['thumb'] = full_pre_url($shop['thumb']);
                                $val['shop_id'] = $v['shop_id'];
                            }else{
                                $val['shop_name'] = '平台自营';
                                $val['shop_id'] = 0;
                                $val['thumb'] = C('C_DF_SHOP_LOGO');
                            }

                            $val['goods_count'] = count($order_goods_2);
                            $val['total_commodity_price'] = M('order_goods')->where('order_id=' . $val['id'].' and shop_id='.$v['shop_id'])->sum('goods_total');
                            if($val['goods']){
                                array_push($list,$val);
                                $order_goods_k++;
                            }

                        }
                    }

                }
                else
                {
                    $goods = M('order_goods')->where('order_id=' . $val['id'].' and isdel = 0')->find();
                    if ($goods['shop_id'])
                    {
                        $shop = M('shop')->where('id=' . $goods['shop_id'])->find();
                        $val['shop_name'] = $shop['shop_name'];
                        $val['shop_id'] = $goods['shop_id'];
                        $val['thumb'] = full_pre_url($shop['thumb']);
                    }
                    else{
                        $val['shop_name'] = '平台自营';
                        $val['shop_id'] = 0;
                        $val['thumb'] = C('C_DF_SHOP_LOGO');
                    }


                    $val['shop_id'] = $goods['shop_id'] ? $goods['shop_id'] : 0;
                    $goods_one = M('order_goods')->where('order_id=' . $val['id'].' and isdel = 0')->select();
                    $val['goods'] = $goods_one ? $goods_one : array();
                    $val['order_status_cn'] = get_order_status($val['order_status']);
                    //如果是报单订单，订单金额应改为商家输入金额
                    if ($val['order_type'] == 2)
                    {
                        $val['total_commodity_price'] = $val['shop_note'];
                    }
                    $val['goods_count'] = count($val['goods']);
                    if($val['goods']){
                        array_push($list,$val);
                    }
                }
            }


        }
        return array(
            'list' => $list,
            'page' => $page,
            'current_page' => $data['page'],
            'max_page' => ceil($count/$num)
        );
    }

	/**
	 * 获取订单信息
	 * @param   $data           传递各种搜索条件
	 * @param   $order          排序方式
	 * @return  array           订单信息数据
	 * @author  zd
	 * 说明：订单信息数据
	 * order_status        -1全部订单，0未支付，1待发货，2待收货，3已收货，4取消(未支付)，5评价完成，6售后订单
	 *
	 */
	private function getOrderList($data, $order = "id desc")
	{
		if ($data['order_no'])
		{
			$where['order_no'] = array('like', '%' . $data['order_no'] . '%');
		}
		if ($data['payment_type'])
		{
			$where['payment_type'] = $data['payment_type'];
		}

		if ($data['order_status'] >= 0 && is_numeric($data['order_status']))
		{
			$where['order_status'] = $data['order_status'];
		}
		if (isset($data["order_type"]))
		{
			$where['order_type'] = $data["order_type"];
		}
		if ($data['user_id'])
		{
			$where['user_id'] = $data['user_id'];
		}
		if ($data['isdel'])
		{
			$where['isdel'] = 1;
		}
		else
		{
			$where['isdel'] = 0;
		}

		if ($data['add_time'])
		{
			$addTimeArr = explode(" - ", $data['add_time']);
			$addTimeArr[0] = strtotime($addTimeArr[0]);
			$addTimeArr[1] = strtotime($addTimeArr[1]);
			$where['add_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
		}
		if ($data['pay_time'])
		{
			$addTimeArr = explode(" - ", $data['pay_time']);
			$addTimeArr[0] = strtotime($addTimeArr[0]);
			$addTimeArr[1] = strtotime($addTimeArr[1]);
			$where['pay_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
		}
		if ($data['shipping_time'])
		{
			$addTimeArr = explode(" - ", $data['shipping_time']);
			$addTimeArr[0] = strtotime($addTimeArr[0]);
			$addTimeArr[1] = strtotime($addTimeArr[1]);
			$where['shipping_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
		}
		if ($data['confirm_time'])
		{
			$addTimeArr = explode(" - ", $data['confirm_time']);
			$addTimeArr[0] = strtotime($addTimeArr[0]);
			$addTimeArr[1] = strtotime($addTimeArr[1]);
			$where['confirm_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
		}
		if ($data['content'])
		{
			if (is_numeric($data['content']) && strlen($data['content']) < 10)
			{
				// 小于10位，并且是数字，按用户ID搜索
				$where_u['id'] = $data['content'];
				$userArr = M('user')->where($where_u)->getField("id", true);
				if ($userArr)
				{
					$where['user_id'] = array("in", $userArr);
				}
			}
			else
			{
				// 非数字，或长度大于10位，按用户名或手机号搜索
				$where_u['phone|realname'] = array("like", "%" . $data['content'] . "%");
				$userArr = M('user')->where($where_u)->getField("id", true);
				if ($userArr)
				{
					$where['user_id'] = array("in", $userArr);
				}
			}
		}
		if ($data['goods_name'])
		{
			$where_og['goods_name'] = array("like", "%" . $data['goods_name'] . "%");
			$ogArr = M('order_goods')->where($where_og)->getField("order_id", true);
			if ($ogArr)
			{
				$where['id'] = array("in", $ogArr);
			}
		}
		if ($data['tracking_number'])
		{
			$where_og['tracking_number'] = array("like", "%" . $data['tracking_number'] . "%");
			$ogArr = M('order_goods')->where($where_og)->getField("order_id", true);
			if ($ogArr)
			{
				$where['id'] = array("in", $ogArr);
			}
		}
		if ($data['trade_no'])
		{
			$where_og['trade_no|serial_no'] = array("like", "%" . $data['trade_no'] . "%");
			$where_og['status'] = 1;
			$ogArr = M('pay3_log')->where($where_og)->getField("order_id", true);
			if ($ogArr)
			{
				$where['id'] = array("in", $ogArr);
			}
		}
		if (intval($data['page']) < 1)
		{
			$where['page'] = 1;
		}
		else
		{
			$where['page'] = $data['page'];
		}
		if ($data['shop_id'])
		{
			$where['shop_id'] = $data['shop_id'];
		}
        if ($data['pay_status'])
        {
            $where['pay_status'] = $data['pay_status'];
        }
		if (!$order)
		{
			$order = "id desc";
		}

		$row = $data['page_size'] ? $data['page_size'] : 10;
		$count = M("order")->where($where)->count();
		$page = new Page($count, $row);
		$firstRow = ($where['page'] - 1) * $row;
		$list = M("order")->where($where)->order($order)->limit($firstRow . ',' . $row)->select();
		$page = $page->show();
		$max_page = ceil($count / $row);
		foreach ($list as $key => &$val)
		{
			$goodsTotal = M('order_goods')->where('order_id=' . $val['id'])->sum("goods_total");
			$val['goods_total'] = $goodsTotal > 0 ? $goodsTotal : 0;
			$order_goods = M('order_goods')->where('order_id=' . $val['id'])->count();
			if ($order_goods > 1)
			{
				$goods = M('order_goods')->where('order_id=' . $val['id'])->select();
				//判断快递是否开启
				$express_service = M("express_service")->where("is_start=1")->find();
				foreach ($goods as $goods_k => $goods_v)
				{
					if ($express_service)
					{
						$goods[$goods_k]["express_url"] = C("C_HTTP_HOST") . "/M/Order/express/user_id/" . $val["user_id"] . "/order_id/" . $val["id"] . "/goods_id/" . $goods_v["goods_id"];
					}
					else
					{
						$goods[$goods_k]["express_url"] = "https://m.kuaidi100.com/";
					}
				}
				$val['goods'] = $goods ? $goods : array();
				$val['shop_name'] = '平台商城';
				$val['thumb'] = C('C_DF_SHOP_LOGO');
			}
			else
			{
			    if($val['order_type'] == 1|| $val['order_type'] == 2 || $val['order_type'] == 3|| $val['order_type'] == 4){
                    if($val['shop_id']){
                        $shop = M('shop')->where('id=' . $val['shop_id'])->find();
                        $val['shop_name'] = $shop['shop_name'];
                        $val['thumb'] = full_pre_url($shop['thumb']);
                    }else{
                        $val['shop_name'] = '平台自营';
                        $val['thumb'] = C('C_DF_SHOP_LOGO');
                    }

                }else{
                    $goods = M('order_goods')->where('order_id=' . $val['id'])->find();
                    if ($goods['shop_id'])
                    {
                        $shop = M('shop')->where('id=' . $goods['shop_id'])->find();
                        $val['shop_name'] = $shop['shop_name'];
                        $val['thumb'] = full_pre_url($shop['thumb']);
                    }
                    else
                    {
                        $val['shop_name'] = '平台自营';
                        $val['thumb'] = C('C_DF_SHOP_LOGO');
                    }
                }

				$val['shop_id'] = $goods['shop_id'] ? $goods['shop_id'] : 0;
				$goods_one = M('order_goods')->where('order_id=' . $val['id'])->select();
				//判断快递是否开启
				$express_service = M("express_service")->where("is_start=1")->find();
				foreach ($goods_one as $goods_k => $goods_v)
				{
					if ($express_service)
					{
						$goods_one[$goods_k]["express_url"] = C("C_HTTP_HOST") . "/M/Order/express/user_id/" . $val["user_id"] . "/order_id/" . $val["id"] . "/goods_id/" . $goods_v["goods_id"];
					}
					else
					{
						$goods_one[$goods_k]["express_url"] = "https://m.kuaidi100.com/";
					}
				}
				$val['goods'] = $goods_one ? $goods_one : array();
			}
			$val['shipping_address'] = get_order_address($val);
			$val['goods_count'] = count($val['goods']);
			$val['province_cn'] = get_region_name($val['province']);
			$val['city_cn'] = get_region_name($val['city']);
			$val['district_cn'] = get_region_name($val['district']);

			$val['order_status_cn'] = get_order_status($val['order_status']);
			$val['order_type_cn'] = get_order_type_cn($val['order_type']);
			$val['pay_status_cn'] = get_pay_status_cn($val['pay_status']);
			$val['shipping_status_cn'] = get_shipping_status_cn($val['shipping_status']);
			$val['shipping_type_cn'] = get_shipping_type_cn($val['shipping_type']);
			$val['payment_type_cn'] = payment_type_cn($val['payment_type']);

			$val['serial_no'] = get_order_trade_no($val['id'], '', "serial_no");
			$val['trade_no'] = get_order_trade_no($val['id'], '', "trade_no");
			$val['add_time_format'] = get_time($val['add_time']);
			$val['pay_time_format'] = get_time($val['pay_time']);
			//如果是报单订单，订单金额应改为商家输入金额
			if ($val['order_type'] == 2)
			{
				$val['total_commodity_price'] = $val['shop_note'];
			}
		}

		return array(
			'list' => $list,
			'page' => $page,
			'current_page' => $data['page'],
			'max_page' => $max_page
		);

	}

	/**
	 * 获取售后商品信息
	 * @return  array     商品详细信息数据
	 * @author  史超
	 * 说明：商品详细信息数据
	 */
	public function orderRefundList($data)
	{
		//获取生成订单的所有商品(就是为了多跳几下)
		$orderList = $this->getRefundOrderList($data);
		return $orderList;
	}

	/**
	 * 获取订单信息
	 * @param order_status      -1全部订单 0未支付订单 1已支付未发货订单 2已发货未收货待收获订单 3已收货未评价订单
	 * @return  array     订单信息数据
	 * @author  史超
	 * 说明：订单信息数据
	 */
	private function getRefundOrderList($date)
	{

		if ($date['user_id'])
		{
			$where['user_id'] = $date['user_id'];
		}
		$where['isdel'] = 0;
		$type = I('type');
		$content = I('content');
		if (!empty($content))
		{
			if ($type == 0)
			{
				$phone['phone'] = $content;
				$user = M('user')->where($phone)->find();
				if ($user)
				{
					$where['user_id'] = $user['id'];
				}
			}
			else
			{
				if ($type == 1)
				{
					$where['order_no'] = array('like', '%' . $content . '%');
				}
			}
		}
		$page_where = $where;
		$row = 10;
		if ($date['page'])
		{
			$where = get_page_last_id('order', $page_where, 'desc', $row, 'id', $date['page']);
		}
		else
		{
			$where = request_last_id($where);
		}
		if ($date['admin'] == 1)
		{
			$count = M("order_refund")->where($where)->count();
			$param = I('post.');
			$Page = new Page($count, 10, $param);
			$order = M("order")->where($where)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
			$page = $Page->show();
		}
		else
		{
			$order = flow_page('order_refund', $where, '', false, $row);
		}

		foreach ($order as $k => $v)
		{

			$goods = M('order_goods')->where('id=' . $v['order_goods_id'])->find();
			if ($goods['shop_id'])
			{
				$shop = M('shop')->where('id=' . $goods['shop_id'])->find();
				$order[$k]['shop_name'] = $shop['shop_name'];
				$order[$k]['thumb'] = full_pre_url($shop['thumb']);
			}
			else
			{
				$order[$k]['shop_name'] = '自营';
				$order[$k]['thumb'] = C('C_DF_SHOP_LOGO');
			}
			$order[$k]['shop_id'] = $goods['shop_id'] ? $goods['shop_id'] : 0;
			$goods_one = M('order_goods')->where('id=' . $v['order_goods_id'])->getField('goods_id,goods_name,goods_thumb,refund_num as goods_num', true);
			$goods_one = array_values($goods_one);
			$order[$k]['goods'] = $goods_one ? $goods_one : array();

			$order[$k]['shipping_address'] = shop_return_address($v['refund_address_id']);
			$order[$k]['goods_count'] = count($order[$k]['goods']);
			$order[$k]['order_status_cn'] = get_refund_status($v['refund_status']);
			$order[$k]['province_cn'] = get_region_name($v['province']);
			$order[$k]['city_cn'] = get_region_name($v['city']);
			$order[$k]['district_cn'] = get_region_name($v['district']);
			$order[$k]['order_status'] = '-' . $v['refund_status'];
			$order[$k]['total_commodity_price'] = $v['refund_total'];
		}
		$last_id = get_last_id($order, 'id', $row);
		if ($date['admin'] != 1)
		{
			$page = get_page('order', $page_where, 'desc', $row, 'id', $last_id);
		}
		return array(
			'list' => $order,
			'last_id' => $last_id,
			'page' => $page
		);

	}

	/**
	 * 查看物流
	 * @return  array     物流
	 * @author  孙海洋
	 * 说明：查看物流
	 */
	public function getViewLogistics()
	{
		$list = array();
		$rtnData = array();
		$user_id = I('user_id');
		$goods_id = I('goods_id');
		$order_id = I('order_id');
		$log = M('express_log')->where("goods_id=$goods_id and order_id=$order_id")->find();
		if (empty($log) || $log['edittime'] + 3600 < time())
		{
			$service = M('express_service')->where("is_start=1")->find(); //获取开启的三方快递服务
			$order = M('order_goods')->where("goods_id=$goods_id and order_id=$order_id")->find(); //获取订单信息
			$express = M('express')->where("id=" . $order['express_id'])->find(); //获取快递公司
			// $order['tracking_number'] = "75142235436935";
			// $express['ali_com'] = "zhongtong";
			// $express['express_number'] = "zhongtong";
			// $express['juhe_com'] = "zto"; //测试数据
			switch ($service['code'])
			{
				case 'ali':
					$host = "http://ali-deliver.showapi.com";
					$path = "/showapi_expInfo";
					$method = "GET";
					$appcode = $service['sign'];
					$headers = array();
					array_push($headers, "Authorization:APPCODE " . $appcode);
					$querys = "com=" . $express['ali_com'] . "&nu=" . $order['tracking_number'];
					$url = $host . $path . "?" . $querys;
					$data = $this->express_curl($service['code'], $url, $method, $headers, '');
					$list = $data['showapi_res_body']['data'];
					foreach ($list as $key => $value)
					{
						$rtnData[$key]['time'] = $value['time'];
						$rtnData[$key]['content'] = $value['context'];
					}
					break;

				case 'juhe':
					$host = "http://v.juhe.cn";
					$path = "/exp/index";
					$method = "GET";
					$key = $service['sign'];
					$headers = array();
					$querys = "com=" . $express['juhe_com'] . "&no=" . $order['tracking_number'] . "&key=" . $key;
					$url = $host . $path . "?" . $querys;
					$data = $this->express_curl($service['code'], $url, $method, '', '');
					$list = $data['result']['list'];
					krsort($list);
					$list = array_values($list);
					foreach ($list as $key => $value)
					{
						$rtnData[$key]['time'] = $value['datetime'];
						$rtnData[$key]['content'] = $value['remark'];
					}
					break;

				case 'hundred':
					$post_data = array();
					$post_data["customer"] = $service['customer'];
					$key = $service['sign'];
					$param = array(
						'com' => $express['express_number'],
						'num' => $order['tracking_number']
					);
					$post_data['param'] = json_encode($param);
					$url = 'http://poll.kuaidi100.com/poll/query.do';
					$post_data["sign"] = md5($post_data["param"] . $key . $post_data["customer"]);
					$post_data["sign"] = strtoupper($post_data["sign"]);
					$o = "";
					foreach ($post_data as $k => $v)
					{
						$o .= "$k=" . urlencode($v) . "&";        //默认UTF-8编码格式
					}
					$post_data = substr($o, 0, -1);
					$data = $this->express_curl($service['code'], $url, '', '', $post_data);
					$list = $data['data'];
					foreach ($list as $key => $value)
					{
						$rtnData[$key]['time'] = $value['time'];
						$rtnData[$key]['content'] = $value['context'];
					}
					break;

				case 'bird':
					$appkey = $service['sign'];
					$param = array(
						'ShipperCode' => $express['bird_com'],
						'LogisticCode' => $order['tracking_number']
					);

					$requestData = json_encode($param);
					$datas = array(
						'EBusinessID' => $service['customer'],
						'RequestType' => '1002',
						'RequestData' => urlencode($requestData),
						'DataType' => '2',
					);
					$datas['DataSign'] = urlencode(base64_encode(md5($requestData . $appkey)));
					$temps = array();
					foreach ($datas as $key => $value)
					{
						$temps[] = sprintf('%s=%s', $key, $value);
					}
					$post_data = implode('&', $temps);
					$url = "http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx";
					$url_info = parse_url($url);
					if (empty($url_info['port']))
					{
						$url_info['port'] = 80;
					}
					$httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
					$httpheader .= "Host:" . $url_info['host'] . "\r\n";
					$httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
					$httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
					$httpheader .= "Connection:close\r\n\r\n";
					$httpheader .= $post_data;
					$fd = fsockopen($url_info['host'], $url_info['port']);
					fwrite($fd, $httpheader);
					$gets = "";
					$headerFlag = true;
					while (!feof($fd))
					{
						if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n"))
						{
							break;
						}
					}
					while (!feof($fd))
					{
						$gets .= fread($fd, 128);
					}
					$list = json_decode($gets, true)['Traces'];
					foreach ($list as $key => $value)
					{
						$rtnData[$key]['time'] = $value['AcceptTime'];
						$rtnData[$key]['content'] = $value['AcceptStation'];
					}
					fclose($fd);
					break;
				default:
					$url = $service['sign'] ? $service['sign'] : 'https://m.kuaidi100.com/';
					$url = $url . '/result.jsp?nu=' . $order['tracking_number'];
					header('Location:' . $url);
					break;
			}
			if (!empty($rtnData))
			{
				$params = array(
					'user_id' => $user_id,
					'order_id' => $order_id,
					'goods_id' => $goods_id,
					'tracking_number' => $order['tracking_number'],
					'newset_message' => json_encode($rtnData),
					'express_service' => $service['code'],
					'express_name' => $express['name'],
					'edittime' => time()
				);
				M('express_log')->add($params);
			}
		}
		else
		{
			$params = $log;
		}
		return $params;
	}

	public function express_curl($type, $url, $method, $headers, $post_data)
	{
		$curl = curl_init();
		if ($type == 'ali')
		{
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_FAILONERROR, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		}
		else
		{
			if ($type == 'juhe')
			{
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
				curl_setopt($curl, CURLOPT_FAILONERROR, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			}
			else
			{
				if ($type == 'hundred')
				{
					curl_setopt($curl, CURLOPT_POST, 1);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				}
			}
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		$data = curl_exec($curl);
		if ($type == 'hundred')
		{
			$data = str_replace("\"", '"', $data);
		}
		$data = json_decode($data, true);
		return $data;
	}

	/**
	 * 购物车列表
	 * @return  array     购物车列表
	 * @author  史超
	 * 说明：购物车列表
	 */
	public function cartList()
	{
		if (!$this->user_id)
		{
			return array();
		}
		$cart = M('cart')->where('user_id=' . $this->user_id)->select();
		foreach ($cart as $k => $v)
		{

			$goodsDetail = $this->getGoodsDetail($v['goods_id'], $v['goods_num'], $v['goods_spec_id'], $v['id']);
			if (!is_array($goodsDetail))
			{
				unset($cart[$k]);
				continue;
			}
			$goodsDetail['shop_id'] = intval($goodsDetail['shop_id']);
			if ($goodsDetail['shop_id'] > 0)
			{
				$shop = M('shop')->where('id=' . $goodsDetail['shop_id'])->find();
				$goods_list[$goodsDetail['shop_id']]['shop_id'] = $shop['user_id'];
				$goods_list[$goodsDetail['shop_id']]['shopid'] = $shop['id'];
				$goods_list[$goodsDetail['shop_id']]['shop_name'] = $shop['shop_name'];
				$goods_list[$goodsDetail['shop_id']]['thumb'] = $shop['thumb'];
			}
			else
			{
				$goods_list[$goodsDetail['shop_id']]['shopid'] = 0;
				$goods_list[$goodsDetail['shop_id']]['shop_id'] = 0;
				$goods_list[$goodsDetail['shop_id']]['shop_name'] = '平台自营';
				$goods_list[$goodsDetail['shop_id']]['thumb'] = C('C_DF_SHOP_LOGO');
			}
			$goods_list[$goodsDetail['shop_id']]['goods_list'][] = $goodsDetail;
		}
		$goods_list = array_values($goods_list);
		return $goods_list;
	}

	/**
	 * 商品信息
	 */
	public function getGoods($goods_id)
	{
	    //重新整合规格的总数量
        $all = M('goods_spec_price')->where('goods_id='.$goods_id)->sum('store_count');
        M('goods')->where('id='.$goods_id)->setField('store_count',$all);
		$goods = M('goods')->where('id=' . $goods_id)->find();
		return $goods;
	}

	/**
	 * 商家信息
	 */
	public function getShop($shop_id = 0)
	{
		if ($shop_id)
		{
			$shopInfo = M("shop")->where("id=" . $shop_id)->field("id,user_id,shop_name,thumb as shop_thumb,status")->find();
			if ($shopInfo)
			{
				$shop_sales = M("order_goods")->where("order_status>0 and shop_id=" . $shop_id)->sum("goods_num");
				$shopInfo["shop_sales"] = $shop_sales;
			}
		}
		return $shopInfo;
	}

	/**
	 * 商品规格信息
	 */
	public function getGoodsSpec($goods_id, $spec_key)
	{
		$where["goods_id"] = $goods_id;
		$where["key"] = $spec_key;
		$goods_spec = M('goods_spec_price')->where($where)->find();
		return $goods_spec;
	}

	/**
	 * 子订单信息
	 * @zd
	 */
	public function subOrderDetail($order_goods_id)
	{
		$orderGoodsInfo = array();
		if (!$order_goods_id)
		{
			$order_goods_id = I("order_goods_id", 0);
		}
		if (!$order_goods_id)
		{
			return $orderGoodsInfo;
		}
		$orderGoodsInfo = M("order_goods")->where("id=" . $order_goods_id)->find();
		if (!$orderGoodsInfo)
		{
			return array();
		}
		if ($orderGoodsInfo['shop_id'])
		{
			// 商家
			$shopInfo = M("shop")->where("id=" . $orderGoodsInfo['shop_id'])->field("id,shop_name,thumb")->find();
			if ($shopInfo)
			{
				$orderGoodsInfo['shop_name'] = $shopInfo['shop_name'];
				$orderGoodsInfo['shop_thumb'] = $shopInfo['thumb'];
			}
			else
			{
				$orderGoodsInfo['shop_name'] = "平台商家";
				$orderGoodsInfo['shop_thumb'] = C("C_DF_SHOP_LOGO");
			}
		}
		else
		{
			$orderGoodsInfo['shop_name'] = "平台自营";
			$orderGoodsInfo['shop_thumb'] = C("C_DF_SHOP_LOGO");
		}
		// 把退款方式给出来
		if ($orderGoodsInfo['order_status'] == 1)
		{
			$orderGoodsInfo['refund_type'][0] = array(
				'id' => 3,
				'title' => '仅退款',
			);
		}
		elseif ($orderGoodsInfo['order_status'] == 2 || $orderGoodsInfo['order_status'] == 3)
		{
			$orderGoodsInfo['refund_type'][0] = array(
				'id' => 1,
				'title' => '退货',
			);
			$orderGoodsInfo['refund_type'][1] = array(
				'id' => 2,
				'title' => '换货',
			);
		}
		else
		{
			$orderGoodsInfo['refund_type'][0] = array(
				'id' => 0,
				'title' => '不可售后',
			);
		}

		// 退款原因
		$refund_reason = refund_reason();
		$orderGoodsInfo['reason'] = $refund_reason;
		return $orderGoodsInfo;
	}


	public function getPickSite($goodsArr, $address_id)
	{
		$is_pick = 1;            // 是否可自提，1可提，0不可自提
		$picksite = array();    // 自提点列表
		$total_express_fee = 0;    // 总运费

		$res['is_pick'] = $is_pick;
		$res['picksite'] = $picksite;
		$res['total_express_fee'] = $total_express_fee;

		//$goodsArr = I("goods", '');
		//$address_id = I("address_id", 0);
		if (!$goodsArr || !$address_id)
		{
			$res['is_pick'] = 0;
			return $res;
		}
		$city = M("user_address")->where("id=" . $address_id)->getField("city");
		if (!$city)
		{
			$res['is_pick'] = 0;
			return $res;
		}
		$goodsArr = json_decode(htmlspecialchars_decode($goodsArr), true);
		foreach ($goodsArr as $key => $value)
		{
			// 获取运费
			if ($value['goods_spec_id'])
			{
				$where_sp['key'] = $value['goods_spec_id'];
				$goods_spec_price = M('goods_spec_price')->where($where_sp)->find();
                $goods_spec_price_id = $goods_spec_price['id'];
				//print_r($goods_spec_price_id);exit();
			}
			else
			{
				$goods_spec_price_id = 0;
			}
			$shipping_fee = goods_express_fee($value['goods_id'], $value['goods_num'], $goods_spec_price_id, $city);
			$total_express_fee = round($total_express_fee + $shipping_fee, 2);

			$goods = M("goods")->where("id=" . $value['goods_id'])->find();
			if (!$goods || $goods['isdel'] == 1 || $goods['is_on_sale'] == 0 || $goods['is_pick'] == 0)
			{
				$is_pick = 0;
				break;
			}
			if ($goods['is_pick'] && $goods['picksite'])
			{
				$where_ps['id'] = array("in", $goods['picksite']);
				$where_ps['city'] = $city;
				$picksite = M("goods_picksite")->where($where_ps)->select();
				foreach ($picksite as $key_ps => &$value_ps)
				{
					$value_ps['province'] = get_region_name($value_ps['province']);
					$value_ps['city'] = get_region_name($value_ps['city']);
					$value_ps['district'] = get_region_name($value_ps['district']);
					$value_ps['work_day'] = get_week_day($value_ps['work_day']);
				}
			}
			else
			{
				$is_pick = 0;
			}
		}

		if (!$is_pick)
		{
			$picksite = array();
		}
		else
		{
			if (count($picksite) < 1)
			{
				$is_pick = 0;
			}
		}
		$res['is_pick'] = $is_pick;
		$res['picksite'] = $picksite;
		$res['total_express_fee'] = $total_express_fee;
		return $res;
	}

	/**
	 * @param $goods_id
	 * Notes:获取成交记录
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 16:01
	 */
	public function get_transaction_record($goods_id)
	{
		$data = I();
		if (!$data['page'])
		{
			$data['page'] = 1;
		}
		if (!$goods_id)
		{
			$goods_id = $data['goods_id'];
		}
		$num = 20;
		$firstRows = ($data['page'] - 1) * 20;
		$limit = $firstRows . ',' . $num;
		$list = M('transaction_record')->where('goods_id=' . $goods_id)->order('id desc')->limit($limit)->select();
		foreach ($list as $k => $v)
		{
			if (mb_strlen($v['buyer_name'], 'utf-8') < 3)
			{
				$name1 = mb_substr($v['buyer_name'], 0, 1, 'utf-8');
				$name = $name1 . '*';
			}
			else
			{
				$search = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
				if (preg_match($search, $v['buyer_name']))
				{
					$name = substr_replace($v['buyer_name'], '****', 3, 4);
				}
				else
				{
					$length = mb_strlen($v['buyer_name'], 'utf-8');
					$new_name = '';
					for ($i = 0; $i < $length - 2; $i++)
					{
						$new_name .= '*';
					}
					$name1 = mb_substr($v['buyer_name'], 0, 1, 'utf-8');
					$name2 = mb_substr($v['buyer_name'], -1, 1, 'utf-8');
					$name = $name1 . $new_name . $name2;
				}
			}
			$list[$k]['buyer_name'] = $name;
			$list[$k]['headimgurl'] = full_pre_url($v['headimgurl']);
			$list[$k]['buy_time'] = format_date($v['buy_time'], 'm-d H:i');
		}
		return $list;
	}


	/**
	 * 分销订单
	 * @author lty
	 */
	public function distributionOrder()
	{
		$user_id = $this->user_id;

		$where['user_id'] = $user_id;
		$num = 10;
		$pages = I('page', 1);
		$page = ($pages - 1) * $num;
		$type = I('type', 0);
		if ($type >= 0)
		{
			$where['order_status'] = $type;
		}
		$list = M('distribution_log')->where($where)->limit($page, $num)->order('id DESC')->select();
		//print_r(M()->getLastSql());exit();
		if ($list)
		{
			foreach ($list as $k => $v)
			{
				$orderInfo = M('order_goods')->find($v['order_goods_id']);
				if ($orderInfo['pay_time'] > 0)
				{
					//支付时间
					$save['order_pay_time'] = $orderInfo['pay_time'];
				}
				if ($orderInfo['shipping_time'] > 0)
				{
					//发货时间
					$save['order_shipping_time'] = $orderInfo['pay_time'];
				}
				if ($orderInfo['confirm_time'] > 0)
				{
					//收货时间
					$save['order_confirm_time'] = $orderInfo['confirm_time'];
				}
				//订单状态
				$save['order_status'] = $orderInfo['order_status'];
				M('distribution_log')->where('id=' . $v['id'])->save($save);
			}
		}

		//重新查询
		$list = M('distribution_log')->where($where)->limit($page, $num)->order('id DESC')->select();


		foreach ($list as $k => $v)
		{
			$userInfo = M('user')->find($v['user_id']);
			$list[$k]['nickname'] = $userInfo['nickname'];//用户名
			$list[$k]['headimgurl'] = full_pre_url($userInfo['headimgurl']);//用户名
			//订单状态
			$list[$k]['order_status_cn'] = get_order_status($v['order_status']);
			if ($v['order_status'] == 0)
			{
				$list[$k]['order_status_cn'] = '待付款';
			}
			elseif ($v['order_status'] == 1)
			{
				$list[$k]['order_status_cn'] = '已付款';
			}
			elseif ($v['order_status'] >= 2)
			{
				$list[$k]['order_status_cn'] = '已完成';
			}
			else
			{
				$list[$k]['order_status_cn'] = '售后';
			}
			//返利等级
			if ($v['son_floor'] > 0)
			{
				//有返利等级 没有的时候就行商家收益等
				if ($v['son_floor'] == 1)
				{
					$list[$k]['son_floor_cn'] = '一级';
				}
				elseif ($v['son_floor'] == 2)
				{
					$list[$k]['son_floor_cn'] = '二级';
				}
				elseif ($v['son_floor'] == 3)
				{
					$list[$k]['son_floor_cn'] = '三级';
				}
				else
				{
					$list[$k]['son_floor_cn'] = '团体';
				}

			}
			else
			{
				$list[$k]['son_floor_cn'] = '';
			}
		}

		$count = M('distribution_log')->where($where)->count();
		$array = array(
			'list' => $list,
			'max_page' => ceil($count / $num),
			'page' => $pages,
			'type' => $type,
		);
		return $array;


	}

    /**
     * @param $data
     * @return array|mixed
     * Notes:获取买单报单列表
     * User: WangSong
     * Date: 2020/9/11
     * Time: 14:34
     */
    public function declaration_order_list($data){
        if(!$data){
            $data = I();
        }
        $page = $data['page'];
        $page = $page < 1 ? 1 : $page;
        $user_id = $this->user_id;
        $where['user_id'] = array('eq',$user_id);
        $type = $data['type'];
        if($type == 1){
            $where['order_type'] = array('eq',1);
        }else{
            $where['order_type'] = array('in','2,4');
        }
        $where['order_status'] = array('gt',0);
        $where['pay_status'] = array('eq',1);
        $where['page_size'] = 10; // wap,N条，不再分页
        $where['page'] = $page;   // 页码
        $data = $this->orderList($where);
        return $data;
    }

    /**
     * @param $data
     * @return array|mixed
     * Notes:充值订单
     * User: WangSong
     * Date: 2020/9/11
     * Time: 15:21
     */
    public function recharge_order_list($data){
        if(!$data){
            $data = I();
        }
        $page = $data['page'];
        $page = $page < 1 ? 1 : $page;
        $user_id = $this->user_id;
        $where['user_id'] = array('eq',$user_id);
        $where['order_type'] = array('eq',3);
        $where['order_status'] = array('gt',0);
        $where['pay_status'] = array('eq',1);
        $where['page_size'] = 10; // wap,N条，不再分页
        $where['page'] = $page;   // 页码
        $data = $this->orderList($where);
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     * Notes:获取物流详情
     * User: WangSong
     * Date: 2020/9/18
     * Time: 10:02
     */
    public function get_logistics($data){
        if(!$data){
            $data = I();
        }
        $order_goods = M('order_goods')->where('order_id='.$data['order_id'].' and shop_id='.$data['shop_id'])->group('tracking_number')->field('tracking_number,express_id,express_name,order_status,order_id,shop_id')->select();
        $num = 0;
        $goods = array();
        foreach ($order_goods as $k=>&$v){
            if($v['tracking_number'] != ''){
                $num ++;
            }
            $v['order_status_sn'] = get_order_status($v['order_status']);
            $where['tracking_number'] = array('eq',$v['tracking_number']);
            $where['order_id'] = array('eq',$data['order_id']);
            $where['shop_id'] = array('eq',$data['shop_id']);
            $new_goods = M('order_goods')->where($where)->select();
            $v['goods'] = $new_goods?$new_goods:array();
            $v['goods_num'] = count($new_goods)?count($new_goods):0;
            $v['express_url'] = "http://m.kuaidi100.com/";
        }
        $goods['num'] = $num;
        $goods['goods'] = $order_goods;
        return $goods;
    }
}