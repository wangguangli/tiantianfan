<?php

namespace M\Controller;
header("Content-type: text/html; charset=utf-8");

use Clas\Jssdk;
use Think\Controller;
use Clas\Orderin;
use Clas\Orderout;
use M\Controller\UserController as User;

class CartController extends CommonController
{


	/**
	 * 加入购物车
	 */
	public function addCart()
	{
		$array = I();
		$pid = $array['pid'];//规格键值
		$goods_spec_id = implode('_', $pid);
		$goods['goods_spec_id'] = $goods_spec_id;
		$goods['goods_num'] = $array['num'];
		$goods['goods_id'] = $array['goods_id'];
		$goods['type'] = 'add';
		$goodsall[0] = $goods;
		$user_id = session('user_id');
		$Orderin = new Orderin($user_id);
		$data = $Orderin->makeCart($goodsall);
		if ($data == 1)
		{
			jsonout('操作购物车', 0, '操作成功');
		}
		else
		{
			jsonout('操作购物车', 1, $data);
		}
	}


	public function confirm()
	{
		$yhq_id = I('yhq_id');
		$yhq_money = I('yhq_money');
        $picksite = '';
		$pick_id = I('picksite_id',0);//自提点的id
        if($pick_id){
            //选择了自提点 查询时间
            $picksite = M('goods_picksite')->find($pick_id);
            if($picksite){
                $data_time = get_pick_date($picksite,time());
            }
        }else{
            $data_time = array();
        }
		$goods_js = I('goods', '', 'htmlspecialchars_decode');
		$goods_kind = I("goods_kind");

		$group_order_id = I("group_order_id");

		if ($goods_js)
		{
			session('goods', $goods_js);
		}
		else
		{
			$goods_js = session('goods');
		}
		if (isset($_GET["goods_kind"]) || isset($_POST["goods_kind"]))
		{
			session('goods_kind', $goods_kind);
		}
		else
		{
			$goods_kind = session('goods_kind');
		}


		if (isset($_GET["group_order_id"]) || isset($_POST["group_order_id"]))
		{
			session('group_order_id', $group_order_id);
		}
		else
		{
			$group_order_id = session('group_order_id');
		}
		$goods_js = json_decode($goods_js, true);


		//当前新增积分商品  目前普通商品无法和积分商品一起购买 判断商品类型

        $orderNumber = count($goods_js);
        $product_type = 0;
        if($orderNumber > 1){
            //购物车购买
            //当前新增积分商品  目前普通商品无法和积分商品一起购买 判断商品类型
            $oneGoodsInfo = M('goods')->find($goods_js[0]['goods_id']);
            $product_type = $oneGoodsInfo['product_type'];
            $shippngArray = array();
            $shippingTypeArray = array();
            foreach ($goods_js as $k => $v){
                if($v['shipping_type']){
                    $shippingTypeArray[$k] = $v['shipping_type'];
                }
                $goodsInfo = M('goods')->find($v['goods_id']);
                if($goodsInfo['product_type'] != $oneGoodsInfo['product_type']){
                    $this->error('不同类型的商品无法同时购买');
                }
                //会有一件商品两种都有配送方式
                if($goodsInfo['is_pick'] ==1 && $goodsInfo['is_shipping'] == 1){

                    //两种方式都支持  先给第一个默认为配送  在新增一个相同的默认为自提
                    $shippngArray[$k] = 3;//两种方式都支持 默认为配送订单
                    $goods_js[$k]['shipping_type'] = 1;
                    $newsGoodsJs[$k] = $v;
                    $newshippngArray[$k] = 2;//两种方式都支持 默认为配送订单
                }elseif($goodsInfo['is_pick'] == 1){
                    //支持自提点
                    $shippngArray[$k] = 2;
                    $goods_js[$k]['shipping_type'] = 2;
                }elseif($goodsInfo['is_shipping'] == 1){
                    //支持发货
                    $shippngArray[$k] = 3;
                    $goods_js[$k]['shipping_type'] = 1;
                }

                $goods_js[$k]['shop_id'] = $goodsInfo['shop_id'];
                $shopInfo = M('shop')->find($goodsInfo['shop_id']);
                if(!$shopInfo){
                    $goods_js[$k]['shop_name'] = '平台自营';
                }else{
                    $goods_js[$k]['shop_name'] = $shopInfo['shop_name'];
                }
                $goods_js[$k]['thumb'] = $goodsInfo['thumb'];

                //再求出规格的相关价格
                $specWhere['key'] = $v['goods_spec_id'];
                $specWhere['goods_id'] = $v['goods_id'];
                $specInfo = M('goods_spec_price')->where($specWhere)->find();
                $goods_js[$k]['price'] = $specInfo['price'] * $v['goods_num'];
            }
            if($shippingTypeArray){
                //直接判断
                if($shippingTypeArray[0] == 1){
                    //配送的
                    $shippngArray = array(3);
                }else{
                    //自提的
                    $shippngArray = array(2);
                }
            }


            //不同配送方式也无法同时购买 进行判断$shippingArray数组
            if(in_array(3,$shippngArray) && in_array(2,$shippngArray)){
                //两种类型都在 不能同时购买  返回相关数据 将获取的数组进行返回
                $newArray = array();
                $price = 0;
                foreach ($goods_js as $k => $v) {
                    //相同的在一个数组里面
                    if($v['shipping_type'] == 1){
                        //快递（配送）
                        $newArray['goods_shipping']['shipping_type'] = 1;
                        $newArray['goods_shipping']['shipping_name'] = '送货上门';
                        $newArray['goods_shipping']['list'][$k]['cart_id'] = $v['cart_id'];
                        $newArray['goods_shipping']['list'][$k]['goods_id'] = $v['goods_id'];
                        $newArray['goods_shipping']['list'][$k]['goods_num'] = $v['goods_num'];
                        $newArray['goods_shipping']['list'][$k]['thumb'] = $v['thumb'];
                        $newArray['goods_shipping']['list'][$k]['price'] = $v['price'];

                    }else{
                        //相同店铺一起
                        //到店自提的  按照店铺id分开
                        $newArray['goods_pick']['shipping_type'] = 2;
                        $newArray['goods_pick']['shipping_name'] = '到店自提';
                        //按照商家id分组
                        $newArray['goods_pick']['shop'][$v['shop_id']]['list'][] = $v;
                        $newArray['goods_pick']['shop'][$v['shop_id']]['shop_id'] = $v['shop_id'];
                        $newArray['goods_pick']['shop'][$v['shop_id']]['all_price'] = $v['price'] + $price;
                        $newArray['goods_pick']['shop'][$v['shop_id']]['shop_name'] = $v['shop_name'];
                    }
                }
                //在进行循环 相同商家在一起

                foreach ($newArray['goods_shipping']['list'] as $k => $v){
                    $priceArray[$k] = $v['price'];
                }
                $shippingPrice = array_sum($priceArray);
                $newArray['goods_shipping']['all_price'] = $shippingPrice;

                foreach ($newArray['goods_pick']['shop'] as $k => $v){
                    $priceArray1 = array();
                    foreach ($v['list'] as $kk => $vv){
                        $priceArray1[$kk] = $vv['price'];
                    }
                    $pickPrice = array_sum($priceArray1);
                    $newArray['goods_pick']['shop'][$k]['all_price'] = $pickPrice;
                }
                $this->error('不同配送方式的商品无法同时购买');
            }elseif(in_array(2,$shippngArray)){
                $is_pick = 1;
                $is_shipping = 0;
            }elseif(in_array(1,$shippngArray)){
                $is_pick = 0;
                $is_shipping = 1;
            }elseif(in_array(3,$shippngArray)){
                $is_shipping = 1;
                $is_pick= 0;
            }
        }else{
            $oneGoodsInfo = M('goods')->find($goods_js[0]['goods_id']);
            $product_type = $oneGoodsInfo['product_type'];
            if($oneGoodsInfo['is_pick'] == 1 && $oneGoodsInfo['is_shipping'] == 1){
                $is_shipping = 1;
                $is_pick= 1;
            }elseif ($oneGoodsInfo['is_pick'] == 1){
                $is_shipping = 0;
                $is_pick= 1;
            }elseif($oneGoodsInfo['is_shipping'] == 1){
                $is_shipping = 1;
                $is_pick= 0;
            }
        }

		$user_id = session('user_id');
		$address_id = I("address_id", 0);

		$address = $this->getAddressInfo($user_id, $address_id);

		if (!$address)
		{
			$address = array('address_id' => 0);
			$city_id = 0;
		}
		else
		{
			$city_id = $address['city'];
		}


		$Orderout = new Orderout($user_id);


		$data = $Orderout->getBuyInfo($goods_js, $goods_kind, $city_id, $address['id']);
		if (!is_array($data))
		{
			$this->error($data);
		}


		foreach ($data['goods_list'] as $k => $v)
		{
			if ($k)
			{

				$shop = $Orderout->getShop($k);
				$allGoodsList[$k]['shop_name'] = $shop['shop_name'];
				$allGoodsList[$k]['shop_thumb'] = full_pre_url($shop['shop_thumb']);
				$allGoodsList[$k]['shop_id'] = $shop['user_id'];
			}
			else
			{
				$allGoodsList[$k]['shop_name'] = '平台自营';
				$allGoodsList[$k]['shop_thumb'] = C("C_DF_SHOP_LOGO");
				$allGoodsList[$k]['shop_id'] = 0;

			}

			if ($data['coupon_auto'])
			{
				$v[0]['goods_price'] = $v[0]['goods_price'] - $data['coupon_auto'][0]['money'];
				$data['total_price'] = $data['total_price'] - $data['coupon_auto'][0]['money'];

			}
			// 优惠券的逻辑处理
			if ($yhq_money)
			{
				$v[0]['goods_price'] = $v[0]['goods_price'] - $yhq_money;
				$data['total_price'] = $data['total_price'] - $yhq_money;
				$this->assign('yhq_money', $yhq_money);
				$this->assign('yhq_id', $yhq_id);
			}

			$allGoodsList[$k]['goods'] = $v;

		}
		$allGoodsList = array_values($allGoodsList);
        $pick_id = I('picksite_id',0);//自提点的id
        if($pick_id){
            //选择了自提点 查询时间
            $picksite = M('goods_picksite')->find($pick_id);
            if($picksite){
                $data_time = get_pick_date($picksite,time());
            }
        }else{
            //自动查询上一次使用的自提点没有就去选择
            $pickUserWhere['user_id'] = $this->user_id;
            $orderPicksite = M('order_picksite')->where($pickUserWhere)->order('id DESC')->find();//最近的一个
            if($orderPicksite){
                $picksite = M('goods_picksite')->find($orderPicksite['id']);
                if($picksite){
                    $data_time = get_pick_date($picksite,time());
                }else{
                    $data_time = array();
                }
            }else{
                $data_time = array();
            }
        }
        $data_time = array_values($data_time);



        //最后计算 如果总价值超过99元就免费邮寄
        if($data['total_goods_price'] >= 99){
            $data['total_price'] = $data['total_price'] - $data['total_express_fee'];
            $data['total_express_fee'] = 0;

            $result = array(
                'goods' => $allGoodsList,
                'total_price' => $data['total_price'],
                'total_money' => round($data['total_price']-$data['total_express_fee'],2),
                'address' => $address,
                'total_express_fee' => $data['total_express_fee'],
                'picksite' =>$picksite,
                'goods_total_leader_money' => $data['goods_total_leader_money'],
            );
        }else{
            $result = array(
                'goods' => $allGoodsList,
                'total_price' => $data['total_price'],
                'total_money' => round($data['total_price']-$data['total_express_fee'],2),
                'address' => $address,
                'total_express_fee' => $data['total_express_fee'],
                'picksite' =>$picksite,
                'goods_total_leader_money' => $data['goods_total_leader_money'],
            );
        }
//		$result = array(
//			'goods' => $allGoodsList,
//			'total_price' => $data['total_price'],
//			'total_money' => round($data['total_price']-$data['total_express_fee'],2),
//			'address' => $address,
//			'total_express_fee' => $data['total_express_fee'],
//            'picksite' =>$picksite,
//			'goods_total_leader_money' => $data['goods_total_leader_money'],
//		);
		$goods = json_encode($result);
        $shiptype = I('shiptype',1);
        if($is_shipping != 1){
            $shiptype = 2;
        }
        $is_score_pay = 0;
        if($product_type && get_config('score_pay_mode') == 1){
            $is_score_pay = 1;//判断积分商品运费支付是不是积分支付还是现金支付
        }
        if($goods_kind != 0){
            $data['coupon_list'] = array();
        }

		$this->assign('result', $result);
		$this->assign('goods', $goods);
		$this->assign('date_time', $data_time);
		$this->assign('time_list', $data_time[0]['date_time']);
		$this->assign('date_time_list', json_encode($data_time));
		$this->assign('data', $data);
		$this->assign('goods_js', $goods_js);
		$this->assign('goods_kind', $goods_kind);
		$this->assign('group_order_id', $group_order_id);
		$this->assign('yhq_arr', $data['coupon_list']);
		$this->assign('auto_arr', $data['coupon_auto']);
		$this->assign('ztd_arr', $data['ztd_data']);
		$this->assign('is_shipping', $is_shipping);
		$this->assign('is_pick', $is_pick);
		$this->assign('picksite', $picksite);
		$this->assign('shiptype', $shiptype);
		$this->assign('product_type', $product_type);
		$this->assign('is_score_pay', $is_score_pay);
		$this->display();
	}

	public function getAddressInfo($user_id, $address_id = 0)
	{
		if ($address_id)
		{
			$where['id'] = $address_id;
		}
		else
		{
			$where['is_default'] = 1;
		}
		$where['user_id'] = $user_id;
		$data = get_address($where, 'user_address');
		return $data;
	}

	// 确认支付，选择支付方式
	public function sure_pay()
	{
		$order_id = I("order_id", 0);    // 订单ID
		$user_id = session('user_id');
		$goods_kind = I("goods_kind", 0);    // 来源分类，0常规商品，1拼团，2整点秒杀，3限时购
		$group_order_id = I("group_order_id", 0);    // 拼团时用到的主订单ID
		if (!$order_id)
		{
			$Orderin = new Orderin($user_id);
			$data = $Orderin->makeOrder();
			if (is_array($data))
			{
				if ($data['status'] == 1)
				{
					$this->error($data['msg']);
				}
			}
			else
			{
				$this->error($data);
			}
			$order_id = $data['order_id'];
		}
		else
		{
			$data = M("order")->where("id=" . $order_id)->find();
			$data["order_id"] = $data["id"];
		}
		if (!is_array($data))
		{
			$this->error($data);
		}
		$user = M('user')->find($user_id);
		if (!$user['openid_temp'] && stristr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'))
		{
			$refer = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/M/Cart/sure_pay?order_id={$order_id}";
			session("refer", $refer);
			$this->getCode();
			$user = M('user')->find($user_id);
		}
		$data_ord['openid'] = $user['openid_temp'];
		M("order")->where("id=" . $order_id)->save($data_ord);

		if (empty($user['pay_password']))
		{
			$is_psw = 0;
		}
		else
		{
			$is_psw = 1;
		}
		$iswx = 0;  // 是否微信，1是，0否
		if (stristr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'))
		{
			$iswx = 1;
		}
        $score_pay_mode = get_config('score_pay_mode');
		$order = M('order')->where('id='.$data['order_id'])->find();
		$this->assign('data', $data);
		$this->assign('order', $order);
		$this->assign('is_psw', $is_psw);
		$this->assign('goods_kind', $goods_kind);
		$this->assign('iswx', $iswx);
		$this->assign('is_alipay', get_config('is_alipay'));
		$this->assign('is_wx_pay', get_config('is_wx_pay'));
		$this->assign('is_ye_pay', get_config('is_ye_pay'));
		$this->assign('score_pay_mode', $score_pay_mode);
		//查询运费

		$this->display();
	}

	// 拼团分享提示
	public function share()
	{
		$order_id = I('id');
		$order = M('order')->where('id=' . $order_id)->find();
		if ($order['order_type'] == 5)
		{
			$isShare = 1;   // 可以分享
			$groupNum = M("order")->where("group_order_id=" . $order_id . " and isdel=0 and order_status=1")->count();
			if ($groupNum >= $order['group_people_num'])
			{
				$isShare = 2;    // 拼团人数满足了
				$num = 0;
			}
			else
			{
				$num = $order['group_people_num'] - $order['group_people_num_have'];
			}
			if($order['group_order_id'] != $order['id']){
			    $newOrder = M('order')->find($order['group_order_id']);
                if ($newOrder['group_time_end'] > time())
                {
                    $rem_time = $newOrder['group_time_end'] - time();
                }
                else
                {
                    $isShare = 3;    // 已结束
                    $rem_time = 0;
                }
            }else{
                if ($order['group_time_end'] > time())
                {
                    $rem_time = $order['group_time_end'] - time();
                }
                else
                {
                    $isShare = 3;    // 已结束
                    $rem_time = 0;
                }
            }

		}
		else
		{
			$this->error("非团购订单，不需要分享");
		}
        //结束时间应该是一开始的时间

		$pt_map['order_type'] = 5;
		$pt_map['group_order_id'] = $order['group_order_id'];
		$pt_map['order_status'] = 1;
		$pt_map['pay_stauts'] = 1;
		$pt_data = M('order')->where($pt_map)->select();

		$order_goods = M('order_goods')->field('id,goods_name,goods_thumb,goods_id')->where('order_id=' . $order_id)->find();
		$Orderout = new Orderout();
		$data = $Orderout->orderDetail($order_id);
		$goodsId = $order_goods['goods_id'];
		$order['number'] = $num;
		$order['rem_time'] = $rem_time;
//		$share['url'] = C('C_HTTP_HOST') . '/M/Goods/detail?goods_kind=1&order_id=' . $order_id.'&share_uid='.$order['user_id'];
		$share['url'] = C('C_HTTP_HOST') . '/M/Goods/detail?goods_kind=1&id=' . $goodsId.'&share_uid='.$order['user_id'];
		$share['title'] = '超低价的商品，你确定不来看一下吗？';
		$share['desc'] = $order_goods['goods_name'];
		$share['thumb'] = $order_goods['goods_thumb'];
		
		$js = new Jssdk();
		$this->assign('wxjssign', $js->getSign(1));
		$this->assign('order', $order);
		$this->assign('share', $share);
		$this->assign('pt_data', $pt_data);
		$this->assign('pt_share', $data['order']['share']);
		$this->assign('goods', $data['goods_list'][0]['goods_list']);
		$this->assign('isShare', $isShare);
		$this->assign('goodsId', $goodsId);
		$this->display();
	}

	// 微信code
	public function getCode()
	{
		$wx = M('config_pay3')->where("type=3")->find();
		$appid = $wx['appid'];
		if (!$appid)
		{
			$this->error("请联系客服配置微信信息");
			exit;
		}
		$url = urlencode('http://' . $_SERVER['HTTP_HOST'] . "/M/Weixin/GetOpenid");
		$codeUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$url}&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
		redirect($codeUrl);
	}

	// 支付宝支付及提示
	public function alipay()
	{
		$order_id = I("order_id", 0);
		if (!$order_id)
		{
			$this->error("订单异常，请重新下单");
		}
		if (stristr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'))
		{
			$this->assign("notice", 1);
			$this->display();
		}
		else
		{
			$orderInfo = M("order")->where("id=" . $order_id)->field("payment_type,order_status")->find();
			if ($orderInfo['order_status'] != 0)
			{
				$this->error("订单已支付，请核实");
			}
			$data['order_id'] = $order_id;
			$data['pay_type'] = "aliwap";
			$Orderin = new Orderin(0);
			$res = $Orderin->payOrder($data);
			if ($res[0] == 1)
			{
				$this->error($res[1]);
			}
			$this->assign("payData", $res[1]);
			$this->assign("notice", 0);
			$this->display();
		}


	}

	/**
	 * Title：购物车数据
	 * Note：直接输出即可
	 * User： zd
	 * Date： 2020/7/9 20:58
	 */
	public function index()
	{
        session('goods_kind', 0);
		$Orderout = new Orderout($this->user_id);
		$list = $Orderout->cartList();
		$this->assign("list", $list);
        $this->assign("cars", json_encode($list));
		$this->assign('menu_near', 'cut');
		$this->display();
	}


	/**
     * 自提点列表 包括距离查询
     * @author lty
     */
	public function picksiteList(){

	    $name = I('title');
	    $name = trim($name);
        $lat = I('lat');
        $lng = I('lng');
        $url = "http://api.map.baidu.com/geocoder?location={$lat},{$lng}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        //返回xml格式  转化为数组
        $output = xmlToArr($output);
        $city = $output['result']['addressComponent']['city'];
        $where['name'] = array('like','%' . $city . '%');
        $cityInfo = M('region')->where($where)->find();
        $whereCity['city'] = array('eq',$cityInfo['id']);
        if($name){
            $whereCity['title'] = array('like','%'.$name.'%');
        }
	    $picksiteList = M('goods_picksite')->where($whereCity)->select();
	    if(!$picksiteList){
	        $this->error('该商品没有自提点，请联系商家');
        }else{
	        foreach ($picksiteList as $k => $v){
                $kil = get_distance($lat,$lng,$v['latitude'],$v['longitude']);
                $picksiteList[$k]['kil'] = $kil;
            }
        }
	    $this->assign('picksite',$picksiteList);
	    $this->display();
    }

}