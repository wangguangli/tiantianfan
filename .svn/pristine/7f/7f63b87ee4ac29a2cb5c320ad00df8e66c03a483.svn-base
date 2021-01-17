<?php

namespace Api\Controller;

use Clas\Orderin;
use Clas\Orderout;

/**
 * 订单类信息展示模块
 */
class OrderoutController extends CommonController
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取购买信息(商品/用户等)
	 * @param json      参照Orderout单元测试数据传来的数据格式 or
	 * @param array     参照Orderout单元测试数据传来的数据格式
	 * @return  array
	 * @author  史超
	 * 说明：生成订单需要的信息和数据
	 */
	public function getBuyInfo()
	{
		$address = $this->getAddressInfo();
		if (!$address)
		{
			$address = ['address_id' => 0];
		}
		$goods_kind = I("goods_kind", 0);
		$address_id = I("address_id", 0);    // 地址ID，运费用

		if ($address_id)
		{
			$where_add["id"] = $address_id;
			$address = get_address($where_add, 'user_address');
		}
		if ($address)
		{
			$city_id = $address["city"];
		}

		$Orderout = new Orderout($this->user_id);
		//dump(I());exit;
		$data = $Orderout->getBuyInfo("", $goods_kind, $city_id, $address['id']);

		if (!is_array($data))
		{
			jsonout($data, 1);
		}
		foreach ($data['goods_list'] as $k => $v)
		{
			if ($k)
			{
				$shop = $Orderout->getShop($k);
				$allGoodsList[$k]['shop_name'] = $shop['shop_name'];
				$allGoodsList[$k]['shop_thumb'] = full_pre_url($shop['shop_thumb']);
				$allGoodsList[$k]['shop_id'] = $shop['id'];
			}
			else
			{
				$allGoodsList[$k]['shop_name'] = '平台自营';
				$allGoodsList[$k]['shop_thumb'] = C("C_DF_SHOP_LOGO");
				$allGoodsList[$k]['shop_id'] = 0;
			}
			$allGoodsList[$k]['goods'] = $v;
		}
		$allGoodsList = array_values($allGoodsList);
        //判断是否有不一样类型的商品

        $goods_js = I('goods', '', 'htmlspecialchars_decode');
        $goods_js = json_decode($goods_js,true);

        $orderNumber = count($goods_js);

        if($orderNumber > 1){

            //先进行判断shipping_type是否一致  一致就直接结算
            //购物车购买
            //当前新增积分商品  目前普通商品无法和积分商品一起购买 判断商品类型
            $oneGoodsInfo = M('goods')->find($goods_js[0]['goods_id']);
            $shippngArray = array();
            $shippingTypeArray = array();
            $newshippngArray = array();
            $newsGoodsJs = array();
            foreach ($goods_js as $k => $v){
                if($v['shipping_type']){
                    //点击弹出的去结算会传参 shipping_type字段
                    $shippingTypeArray[$k] = $v['shipping_type'];
                }
                $goodsInfo = M('goods')->find($v['goods_id']);

                if($goodsInfo['product_type'] != $oneGoodsInfo['product_type']){

                    jsonout('不同类型的商品无法同时购买',1,null);
                }

                //会有一件商品两种都有配送方式
                if($goodsInfo['is_pick'] ==1 && $goodsInfo['is_shipping'] == 1){
                    //两种方式都支持  先给第一个默认为配送  在新增一个相同的默认为自提
                    $shippngArray[$k] = 3;//两种方式都支持 默认为配送订单
                    $goods_js[$k]['shipping_type'] = 1;
                    $newsGoodsJs[$k] = $v;
                    $newshippngArray[$k] = 2;//两种方式都支持 默认为配送订单
                    $goods_js[$k]['shipping_type'] = 1;
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
                $goods_js[$k]['thumb'] = $goodsInfo['thumb'];
                if(!$shopInfo){
                    $goods_js[$k]['shop_name'] = '平台自营';
                }else{
                    $goods_js[$k]['shop_name'] = $shopInfo['shop_name'];
                }
                //再求出规格的相关价格
                $specWhere['key'] = $v['goods_spec_id'];
                $specWhere['goods_id'] = $v['goods_id'];
                $specInfo = M('goods_spec_price')->where($specWhere)->find();
                $goods_js[$k]['price'] = $specInfo['price']* $v['goods_num'];
                $goods_js[$k]['goods_spec_id'] = $specInfo['key'] ;
            }

            if($newsGoodsJs){
                foreach ($newsGoodsJs as $k => $v){
                    $goodsInfo = M('goods')->find($v['goods_id']);
                    $newsGoodsJs[$k]['shipping_type'] = 2;
                    $newsGoodsJs[$k]['shop_id'] = $goodsInfo['shop_id'];
                    $shopInfo = M('shop')->find($goodsInfo['shop_id']);
                    if(!$shopInfo){
                        $newsGoodsJs[$k]['shop_name'] = '平台自营';
                    }else{
                        $newsGoodsJs[$k]['shop_name'] = $shopInfo['shop_name'];
                    }

                    $newsGoodsJs[$k]['thumb'] = $goodsInfo['thumb'];
                    //再求出规格的相关价格
                    $specWhere['key'] = $v['goods_spec_id'];
                    $specWhere['goods_id'] = $v['goods_id'];
                    $specInfo = M('goods_spec_price')->where($specWhere)->find();
                    $newsGoodsJs[$k]['price'] = $specInfo['price'] * $v['goods_num'];
                    $newsGoodsJs[$k]['goods_spec_id'] = $specInfo['key'];
                }
                $goods_js = array_merge($newsGoodsJs,$goods_js);
            }


            if($shippngArray){
                $shippngArray = array_merge($newshippngArray,$shippngArray);
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
                        $newArray['goods_shipping']['list'][$k]['shipping_type'] = 1;
                        $newArray['goods_shipping']['list'][$k]['goods_spec_id'] = $v['goods_spec_id'];

                    }else{
                        //相同店铺一起
                        //到店自提的  按照店铺id分开
                        $newArray['goods_pick']['shipping_type'] = 2;
                        $newArray['goods_pick']['shipping_name'] = '到店自提';
                        //按照商家id分组
                        $newArray['goods_pick']['shop'][$v['shop_id']]['list'][] = $v;
                        $newArray['goods_pick']['shop'][$v['shop_id']]['shop_id'] = $v['shop_id'];
                        $newArray['goods_pick']['shop'][$v['shop_id']]['shop_name'] = $v['shop_name'];
                    }
                }
                //在进行循环 相同商家在一起

                foreach ($newArray['goods_shipping']['list'] as $k => $v){
                    $priceArray[$k] = $v['price'];
                    $numberArray[$k] = $v['goods_num'];
                }
                $shippingPrice = array_sum($priceArray);
                $shippingNumber = array_sum($numberArray);
                $newArray['goods_shipping']['all_price'] = $shippingPrice;
                $newArray['goods_shipping']['all_number'] = $shippingNumber;

                foreach ($newArray['goods_pick']['shop'] as $k => $v){
                    $priceArray1 = array();
                    $numberArray1 = array();
                    foreach ($v['list'] as $kk => $vv){
                        $priceArray1[$kk] = $vv['price'];
                        $numberArray1[$kk] = $vv['goods_num'];
                    }
                    $pickPrice = array_sum($priceArray1);
                    $pickNumber = array_sum($numberArray1);
                    $newArray['goods_pick']['shop'][$k]['all_price'] = $pickPrice;
                    $newArray['goods_pick']['shop'][$k]['all_number'] = $pickNumber;
                }
                $newArray['type'] = 1;
                jsonout('不同配送方式的商品无法一起购买',0,$newArray);
            }elseif(in_array(2,$shippngArray)){
                //判断类型  自提的  如果商家不同 也要返回信息

                $newArray = array();
                foreach ($goods_js as $k => $v) {
                    //相同的在一个数组里面
                    if($v['shipping_type'] == 2){
                        //相同店铺一起
                        //到店自提的  按照店铺id分开
                        $newArray['goods_pick']['shipping_type'] = 2;
                        $newArray['goods_pick']['shipping_name'] = '到店自提';
                        //按照商家id分组
                        $newArray['goods_pick']['shop'][$v['shop_id']]['list'][] = $v;
                        $newArray['goods_pick']['shop'][$v['shop_id']]['shop_id'] = $v['shop_id'];
                        $newArray['goods_pick']['shop'][$v['shop_id']]['shop_name'] = $v['shop_name'];

                    }
                }
                //判断是否有不同商家
                $newArray['goods_shipping'] = '';
                //在进行循环 如果商家不同就返回错误数据
                $shop_number = count($newArray['goods_pick']['shop']);
                if($shop_number > 1){
                    foreach ($newArray['goods_pick']['shop'] as $k => $v){
                        $priceArray1 = array();
                        $numberArray1 = array();
                        foreach ($v['list'] as $kk => $vv){
                            $priceArray1[$kk] = $vv['price'];
                            $numberArray1[$kk] = $vv['goods_num'];
                        }
                        $pickPrice = array_sum($priceArray1);
                        $pickNumber = array_sum($numberArray1);
                        $newArray['goods_pick']['shop'][$k]['all_price'] = $pickPrice;
                        $newArray['goods_pick']['shop'][$k]['all_number'] = $pickNumber;
                    }
                    $newArray['type'] = 1;
                    jsonout('不同配送方式的商品无法一起购买',0,$newArray);
                }else{

                    $is_pick = 1;
                    $is_shipping = 0;
                }

            }elseif(in_array(1,$shippngArray)){
                $is_pick = 0;
                $is_shipping = 1;
            }elseif(in_array(3,$shippngArray)){
                $is_shipping = 1;
                $is_pick= 0;
            }
        }else{
            //单个商品购买

            $oneGoodsInfo = M('goods')->find($goods_js[0]['goods_id']);
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


        $pick_id = I('picksite_id',0);//自提点的id
        if($pick_id){
            //选择了自提点 查询时间
            $picksite = M('goods_picksite')->find($pick_id);

        }else{
            //自动查询上一次使用的自提点没有就去选择
            $pickUserWhere['user_id'] = $this->user_id;
            $orderPicksite = M('order_picksite')->where($pickUserWhere)->order('id DESC')->find();//最近的一个

            if($orderPicksite){
                $picksite = M('goods_picksite')->find($orderPicksite['picksite_id']);

            }
        }
        //app使用 picksute没有值就不在进行返回
        if(I('shipping_type') == 1){
            $is_shipping = 1;
            $is_pick = 0;
        }elseif (I('shipping_type') == 2){
            $is_shipping = 0;
            $is_pick = 1;
        }
        if(empty($picksite)){
            $result = array(
                'goods' => $allGoodsList,
                'total_price' => $data['total_price'],
                'total_goods_price' => $data['total_goods_price'],
                'address' => $address,
                'total_express_fee' => $data['total_express_fee'],
                'goods_kind' => $goods_kind,
                'goods_total_leader_money' => $data['goods_total_leader_money'],
                'group_order_id' => $data['group_order_id'],
                'coupon_list' => $data['coupon_list'],
                'ztd_data' => $data['ztd_data'],
                'picksite' =>$picksite,
                'is_shipping' => $is_shipping,//是否支持配送  1支持 0不支持
                'is_pick' => $is_pick,//是否支持自提  1支持 0不支持
                'type' => 0,//正常订单
            );
        }else{
            $result = array(
                'goods' => $allGoodsList,
                'total_price' => $data['total_price'],
	            'total_goods_price' => $data['total_goods_price'],
                'address' => $address,
                'total_express_fee' => $data['total_express_fee'],
                'goods_kind' => $goods_kind,
                'goods_total_leader_money' => $data['goods_total_leader_money'],
                'group_order_id' => $data['group_order_id'],
                'coupon_list' => $data['coupon_list'],
                'ztd_data' => $data['ztd_data'],
                'picksite' =>$picksite,
                'is_shipping' => $is_shipping,//是否支持配送  1支持 0不支持
                'is_pick' => $is_pick,//是否支持自提  1支持 0不支持
                'type' => 0,//正常订单
                'pick_info' => $picksite,
            );
        }

		if ($data['coupon_auto'])
		{
			$result['coupon_auto'] = $data['coupon_auto'];
		}
		jsonout('购买信息', 0, $result);
	}


    /**
     * app新增购物车返回数据
     * @author lty
     */
    public function cartGoodsList(){
        $goods_js = I('goods', '', 'htmlspecialchars_decode');
        $goods_js = json_decode($goods_js,true);
        //进行数据判断输出
        $oneGoodsInfo = M('goods')->find($goods_js[0]['goods_id']);//第一个商品的信息
        $shippingTypeArray = array();
        foreach ($goods_js as $k => $v){
            if($v['shipping_type']){
                $shippingTypeArray[$k] = $v['shipping_type'];
            }
            $goodsInfo = M('goods')->find($v['goods_id']);
            if($goodsInfo['product_type'] != $oneGoodsInfo['product_type']){
                //积分商品 虚拟商品 普通商品无法同时购买
                jsonout('不同类型的商品无法同时购买',1,null);
            }

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
            $goods_js[$k]['thumb'] = $goodsInfo['thumb'];
            if(!$shopInfo){
                $goods_js[$k]['shop_name'] = '平台自营';

            }else{
                $goods_js[$k]['shop_name'] = $shopInfo['shop_name'];
            }
            //再求出规格的相关价格
            $specWhere['key'] = $v['goods_spec_id'];
            $specWhere['goods_id'] = $v['goods_id'];
            $specInfo = M('goods_spec_price')->where($specWhere)->find();
            $goods_js[$k]['price'] = $specInfo['price']* $v['goods_num'];
            $goods_js[$k]['goods_spec_id'] = $specInfo['key'] ;
        }
        //将新增的配送数组  和  goods_js合并
        if($newsGoodsJs){
            foreach ($newsGoodsJs as $k => $v){
                $goodsInfo = M('goods')->find($v['goods_id']);
                $newsGoodsJs[$k]['shipping_type'] = 2;
                $newsGoodsJs[$k]['shop_id'] = $goodsInfo['shop_id'];
                $shopInfo = M('shop')->find($goodsInfo['shop_id']);
                if(!$shopInfo){
                    $newsGoodsJs[$k]['shop_name'] = '平台自营';
                }else{
                    $newsGoodsJs[$k]['shop_name'] = $shopInfo['shop_name'];
                }

                $newsGoodsJs[$k]['thumb'] = $goodsInfo['thumb'];

                //再求出规格的相关价格
                $specWhere['key'] = $v['goods_spec_id'];
                $specWhere['goods_id'] = $v['goods_id'];
                $specInfo = M('goods_spec_price')->where($specWhere)->find();
                //判断用户等级获取价格
                $newsGoodsJs[$k]['price'] = $specInfo['price'] * $v['goods_num'];
                $newsGoodsJs[$k]['goods_spec_id'] = $specInfo['key'];
            }
            $goods_js = array_merge($newsGoodsJs,$goods_js);
        }


        if($shippngArray){
            $shippngArray = array_merge($newshippngArray,$shippngArray);
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
            foreach ($goods_js as $k => $v) {
                //相同的在一个数组里面
                if($v['shipping_type'] == 1){
                    //快递（配送）
                    $newArray[0]['shipping_type'] = 1;
                    $newArray[0]['shipping_name'] = '送货上门';
                    $newArray[0]['list'][$k] = $v;

                }else{
                    //相同店铺一起
                    //到店自提的  按照店铺id分开
                    $newArray[$v['shop_id'] + 1]['shipping_type'] = 2;
                    $newArray[$v['shop_id'] + 1]['shipping_name'] = '到店自提';
                    $newArray[$v['shop_id'] + 1]['shop_id'] = $v['shop_id'];
                    $newArray[$v['shop_id'] + 1]['shop_name'] = $v['shop_name'];
                    //按照商家id分组
                    $newArray[$v['shop_id'] + 1]['list'][] = $v;

                }
            }

            //在进行循环 相同商家在一起
            $newArrayList = array_values($newArray[0]['list']);
            $newArray[0]['list'] = $newArrayList;

            foreach ($newArray as $k => $v){
                //计算数量和总价
                $priceArray = array();
                $numberArray = array();
                foreach ($v['list'] as $kk => $vv){
                    $priceArray[$kk] = $vv['price'];
                    $numberArray[$kk] = $vv['goods_num'];
                }
                $shippingPrice = array_sum($priceArray);
                $shippingNumber = array_sum($numberArray);
                $newArray[$k]['all_price'] = $shippingPrice;
                $newArray[$k]['all_number'] = $shippingNumber;
            }
            $newArray = array_values($newArray);
            jsonout('查询成功',0,$newArray);
        }elseif(in_array(2,$shippngArray)){
            //判断类型  自提的  如果商家不同 也要返回信息
            $newArray = array();
            foreach ($goods_js as $k => $v) {
                //相同的在一个数组里面
                if($v['shipping_type'] == 2){
                    //相同店铺一起
                    //到店自提的  按照店铺id分开
                    $newArray[$v['shop_id']]['shipping_type'] = 2;
                    $newArray[$v['shop_id']]['shipping_name'] = '到店自提';
                    //按照商家id分组
                    $newArray[$v['shop_id']]['shop_id'] = $v['shop_id'];
                    $newArray[$v['shop_id']]['shop_name'] = $v['shop_name'];
                    $newArray[$v['shop_id']]['list'][] = $v;
                }
            }

            //在进行循环 如果商家不同就返回错误数据
            foreach ($newArray as $k => $v){
                    $priceArray1 = array();
                    $numberArray1 = array();
                    foreach ($v['list'] as $kk => $vv){
                        $priceArray1[$kk] = $vv['price'];
                        $numberArray1[$kk] = $vv['goods_num'];
                    }
                    $pickPrice = array_sum($priceArray1);
                    $pickNumber = array_sum($numberArray1);
                    $newArray[$k]['all_price'] = $pickPrice;
                    $newArray[$k]['all_number'] = $pickNumber;
                }
            $newArray = array_values($newArray);

            jsonout('查询成功',0,$newArray);

        }
    }

	/**
	 * 订单列表详情
	 * @param order_status      订单ID
	 * @return  array
	 * @author  史超
	 * 说明：订单列表详情
	 */
	public function orderList()
	{
		$Orderout = new Orderout($this->user_id);
		$data = I();
        $data["order_type"] = array('not in','1,2,3,4');
		$list = $Orderout->userOrderList($data);
		jsonout('订单列表', 0, $list);
	}

	/**
	 * 订单详情
	 * @param order_id      订单ID
	 * @return  array
	 * @author  史超
	 * 说明：订单详情
	 */
	public function orderDetail()
	{
		$Orderout = new Orderout($this->user_id);
		$data = $Orderout->orderDetail(I('order_id',0),I('shop_id',0));
		jsonout('订单详情', 0, $data);
	}

	/**
	 * 快递公司接口
	 * @param express_id      物流公司ID
	 * @return  array
	 * @author  史超
	 * 说明：快递公司接口
	 */
	public function getExpress()
	{
		$express_id = I('express_id');
		$data = get_express($express_id);
		jsonout('快递公司信息', 0, $data);
	}

	/**
	 * 查看物流
	 * @param order_id      订单ID
	 * @return  array
	 * @author  史超
	 * 说明：查看物流
	 */
	public function viewLogistics()
	{
		$Orderout = new Orderout($this->user_id);
		$data = $Orderout->getViewLogistics();
		jsonout('物流信息', 0, $data);
	}

	/**
	 * 购物车列表
	 * @return  array
	 * @author  史超
	 * 说明：购物车列表
	 */
	public function cartList()
	{
		$id = I('user_id');

		$Orderout = new Orderout($this->user_id);
		$data = $Orderout->cartList();
		if ($data)
		{
			jsonout('加载完成', 0, $data);
		}
		else
		{
			jsonout('加载完成', 1);
		}
	}


	/**
	 * 获取地址信息
	 * @param where      查询条件
	 * @param table      查询表
	 * @return  array
	 * @author  史超
	 * 说明：查看物流
	 */
	public function getAddressInfo()
	{
		$where['user_id'] = $this->user_id;
		$where['is_default'] = 1;
		$data = get_address($where, 'user_address');
		return $data;
	}

	/**
	 * 商品可用自提点列表
	 * @param   $goods      商品数组
	 * @param   $address_id    地址ID
	 * @return  array
	 * @author  zd
	 * 说明：用户刚进入有默认地址时，或重新选择地址时，再调用此接口
	 */
	public function getPickSite()
	{
		$goodsArr = I("goods", '');
		$address_id = I("address_id", 0);
		$order = new Orderout();
		$order_arr = $order->getPickSite($goodsArr, $address_id);
		jsonout('自提点', 0, $order_arr);


	}

	/**
	 * 子订单详情
	 * @param string $order_goods_id 子订单ID
	 * @param int $user_id 用户ID
	 * @return  json
	 * @zd
	 */
	public function subOrderDetail()
	{
		$Orderout = new Orderout($this->user_id);
		$order_goods_id = I("order_goods_id", 0);
		$data = $Orderout->subOrderDetail($order_goods_id);
		if ($data)
		{
			jsonout('订单详情', 0, $data);
		}
		else
		{
			jsonout('订单异常，请核实或重新登录后尝试');
		}

	}

	/**
	 * 售后订单列表
	 * @return  json
	 * @zd
	 */
	public function refundList()
	{
		$Orderout = new Orderout($this->user_id);
		$data = I();
		$data = $Orderout->orderRefundList($data);
		if ($data)
		{
			jsonout('获取成功', 0, $data);
		}
		else
		{
			jsonout('获取失败', 1);
		}

	}

	/**
	 * 售后当前状态
	 * @param int $refund_id 售后订单ID
	 * @return  json
	 * @zd
	 * 说明：不论任何状态下，都会使用这个方法，只是状态不同，输出数据可能不同
	 */
	public function refundInfo()
	{
		$refund_id = I("refund_id", 0);
		$Orderin = new Orderin($this->user_id);
		$data = $Orderin->getRefundDetail($refund_id);
		if ($data['err'] == 0)
		{
			$process = get_refund_process_by_id($refund_id);
			$data['result']['process'] = $process;
			$refundInfo = $data['result'];
			jsonout("售后订单信息", 0, $refundInfo);
		}
		else
		{
			jsonout($data['msg'], 1);
		}
	}

	/**
	 * Created by PhpStorm.
	 * User: Administrator
	 * Date: 2020/5/23
	 * Time: 14:56
	 * 售后订单状态日志
	 */
	public function refund_order_log()
	{
		$refund_id = I('refund_id', 0);
		if (!$refund_id)
		{
			jsonout('请传入参数', 1);
		}
		$refund_log = M('order_refund_log')->where('refund_id=' . $refund_id)->select();
		foreach ($refund_log as $k => &$v)
		{
			$v['cre_date'] = date('Y-m-d H:i:s', $v['cre_time']);
		}
		if ($refund_log)
		{
			jsonout('获取成功', 0, $refund_log);
		}
		else
		{
			jsonout('暂无数据', 1);
		}
	}

	/**
	 * 获取订单分享信息
	 * @zd
	 */
	public function getShareInfo()
	{
		$order_id = I("order_id", 0);
		if (!$order_id)
		{
			jsonout('缺少主要信息');
		}

		$Orderout = new Orderout($this->user_id);
		$orderInfo = $Orderout->getOrderDetail($order_id);
		if ($orderInfo)
		{
			jsonout("订单信息", 0, $orderInfo);
		}
		else
		{
			jsonout('订单异常，请刷新后重试');
		}
	}

	/**
	 * Notes:获取成交记录
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 16:00
	 */
	public function get_transaction_record()
	{
		$orderOut = new Orderout();
		$list = $orderOut->get_transaction_record();
		if ($list)
		{
			jsonout('获取成功', 0, $list);
		}
		else
		{
			jsonout('暂无数据');
		}
	}


	/**
     * 获取商品的自提点列表
     * @author lty
     */

	public function picksite(){
	    //查询本市全部自提点
        $name = I('title');
        $name = trim($name);
        $city_id = I('city_id',0);
        $lat = I('lat');
        $lng = I('lng');
        $whereCity = '1=1';
        if(empty($lat)){
            //根据城市id查询
            if($city_id){
                $whereCity['city'] = $city_id;
            }
        }else{
            $where['name'] = array('like','%' . I('city_name') . '%');
            $cityInfo = M('region')->where($where)->find();
            $whereCity .= ' and city='.$cityInfo['id'];
        }
        if($name){
            $whereCity .= " and title like '%".$name."%'";
        }
        $whereCity .= " and is_del=0";
        if($lat){
            $sql = "select *,(2*6378.137* ASIN(SQRT(POW(SIN(PI()*({$lng}-lng)/360),2)
                +COS(PI()*{$lat}/180)* COS(lat * PI()/180)*POW(SIN(PI()*({$lat}-
                lat)/360),2)))) as distance from `sns_goods_picksite` where ".$whereCity."
                order by distance asc";
        }else{
            $sql = "select * from sns_goods_picksite where ".$whereCity." order by id desc";
        }
        $picksiteList = M()->query($sql);
        foreach ($picksiteList as $k=>$v){
            if($v['distance']){
                $picksiteList[$k]['distance'] = round($v['distance'],2);
            }
            $picksiteList[$k]['date_time'] = get_pick_date($v,time());
        }
        //$picksiteList = M('goods_picksite')->where($whereCity)->select();
        $ids = array();
        $where1['id_del'] = 0;
        $goods_picksite = M('goods_picksite')->where($where1)->group('city')->select();
        foreach ($goods_picksite as $k => $v){
            $ids[$k] = $v['city'];
        }
        $cityWhere['id'] = array('in',$ids);
        $region = M('region')->where($cityWhere)->select();
        $new_region = array();
        $index=0;
        if(!$goods_picksite){
            $new_region[0]['name'] = I('city_name');
            $i=1;
        }else{
            $i=0;
        }

        foreach ($region as $k=>$v){
            $new_region[$i] = $v;
            if($v['name'] == I('city_name')){
                $index = $k;
            }
            $i++;
        }
        $data['index'] = $index;
        $data['region'] = $new_region;
        $data['picksiteList'] = $picksiteList;
        jsonout('查询成功',0,$data);
    }

    /**
     * 拥有自提点的城市列表
     */
    public function cityPick(){
        $where['id_del'] = 0;
        $list = M('goods_picksite')->where($where)->select();
        $ids = array();
        foreach ($list as $k => $v){
            $ids[$k] = $v['city'];
        }
        $cityWhere['id'] = array('in',$ids);
        $region = M('region')->where($cityWhere)->select();
        jsonout('查询成功',0,$region);
    }

    /**
     * Notes:获取自提点营业时间
     * User: WangSong
     * Date: 2020/8/3
     * Time: 10:23
     */
    public function get_pick_date_time(){
        $data = I();
        if(!$data['picksite_id']){
            jsonout('缺少picksite_id参数');
        }
        $goods_picksite = M('goods_picksite')->where('id='.$data['picksite_id'])->find();
        $date_time = get_pick_date($goods_picksite,time());
        if($date_time){
            jsonout('获取成功',0,$date_time);
        }else{
            jsonout('暂无数据');
        }
    }

    /**
     * Notes:买单报单列表
     * User: WangSong
     * Date: 2020/9/10
     * Time: 10:14
     */
    public function declaration_order_list(){
        $orderOut = new Orderout($this->user_id);
        $data = $orderOut->declaration_order_list(I());
        if($data['list']){
            jsonout('获取成功',0,$data);
        }else{
            jsonout('暂无数据',1);
        }
    }

    /**
     * Notes:充值订单
     * User: WangSong
     * Date: 2020/9/11
     * Time: 15:20
     */
    public function recharge_order_list(){
        $orderOut = new Orderout($this->user_id);
        $data = $orderOut->recharge_order_list(I());
        if($data['list']){
            jsonout('获取成功',0,$data);
        }else{
            jsonout('暂无数据',1);
        }
    }

    /**
     * Notes:获取物流详情
     * User: WangSong
     * Date: 2020/9/18
     * Time: 9:53
     */
    public function get_logistics(){
        $orderout = new Orderout();
        $list = $orderout->get_logistics();
        jsonout('获取成功',0,$list);
    }

}