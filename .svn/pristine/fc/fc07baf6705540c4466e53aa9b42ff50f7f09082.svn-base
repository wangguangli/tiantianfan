<?php
namespace Api\Controller;

use Clas\User;

class CouponoutController extends CommonController
{

	/**
	 * 领券中心/商品优惠券/店铺优惠券列表
	 * @param   int $user_id 	用户ID
	 * @param   int $goods_id 	商品ID
	 * @param   int $shop_id 	商家ID
	 * @return  json
	 * @zd
	 * 如果传入UID，则表示是否已被领取
	 */
	public function couponList()
	{
		$user_id = I("user_id", 0);
		$goods_id = I("goods_id", 0);
		$shop_id = I("shop_id", 0);
		$where = "status= 1 and is_del=0 and type in (1,2,3,4) and money>0";
		if ($goods_id)
		{
			$where.= " and goods_id=".$goods_id;
		}
		if ($shop_id)
		{
			$where.= " and shop_id=".$shop_id;
		}
		$list = M("coupon")->where($where)->field("cre_date,cre_time,ip,status,is_del", true)->order("id desc")->select();
		foreach ($list as $key=>&$value)
		{
			// 该用户是否领取过此优惠券
			if ($user_id)
			{
				$where_uc['cid'] = $value['id'];
				$where_uc['user_id'] = $user_id;
				$userCoupon = M("coupon_user")->where($where_uc)->find();
				if ($userCoupon)
				{
					$value['is_have'] = 1;	// 已领取过
				}
				else
				{
					$value['is_have'] = 0;	// 未领取过
				}
				//该券是否过期
				if(time()>$value['use_end_time']){
					$value['is_over'] = 1; //已过期
				}else{
					$value['is_over'] = 0; //未过期
				}
			}
			else
			{
				$value['is_have'] = 0;	// 未领取过
			}

			// 满减提示
			$value['conditions_cn'] = "满".$value['conditions']."元可用";
			$shopName = "全平台通用优惠券";
			$couponType = "平台券";
			$newUser = "";			// 是否仅新用户可用 -- 即以前未使用过优惠券的
			$effectiveTime = "";	// 有效时间
			if ($value['shop_id'])
			{
				$shopName = M("shop")->where("id=".$value['shop_id'])->getField("shop_name");
				$shopName = $shopName ? $shopName : "平台商家";
				$shopName = "仅限".$shopName."使用";
				$couponType = "商家券";
			}
			if ($value['goods_id'])
			{
				$shopName = M("goods")->where("id=".$value['goods_id'])->getField("name");
				$shopName = $shopName ? $shopName : "指定商品";
				$shopName = "仅限".$shopName."使用";
				$couponType = "商品券";
			}
			$value['shop_name'] = $shopName;
			$value['coupon_type'] = $couponType;
			if ($value['to_new_user'])
			{
				$newUser = "[新用户]";
			}
			$value['to_new_user_cn'] = $newUser;

			if ($value['start_type'] == 1)
			{
				$effectiveTime = "领取后{$value['use_days']}天有效期";
			}
			else
			{
				$effectiveTime = date("Y.m.d", $value['use_start_time'])." - ".date("Y.m.d", $value['use_end_time']);
			}
			$value['effective_time'] = $effectiveTime;

			// 是否被全部领取完了
			if ($value['send_num'] > $value['get_num'])
			{
				$value['sell_out'] = 0;	// 还没有被抢完
			}
			else
			{
				$value['sell_out'] = 1;	// 已被抢完
			}

		}
		if (!$list)
		{
			jsonout('暂无可领取优惠券', 1);
		}
		else
		{
			jsonout('优惠券列表', 0, $list);
		}

	}

	/**
	 * 我的优惠券列表
	 * @param   int $user_id 	用户ID
	 * @param   int $type 		类型，0全部，1未使用，2已使用，3已过期
	 * @return  json
	 * @zd
	 */
	public function myCoupon()
	{
		$user_id = I("user_id", 0);
		$type = I("type", 0);
		if (!$user_id)
		{
			jsonout("缺少主要参数");
		}
		$join = " left join __COUPON__ as c on cu.cid=c.id";
		if ($type == 1)
		{
			$where = "cu.is_hide=0 and cu.order_id=0 and cu.use_end_time>=".time();
		}
		elseif ($type == 2)
		{
			$where = "cu.is_hide=0 and cu.order_id>0 ";
		}
		elseif ($type == 3)
		{
			$where = "cu.is_hide=0 and cu.order_id=0 and cu.use_end_time<".time();
		}
		else
		{
			$where = "cu.is_hide=0";
		}
		$where.= " and user_id=".$user_id;
		$field = "cu.*,c.name,c.money,c.conditions,c.goods_id,c.shop_id,c.to_new_user,c.same_time,c.status,c.is_del";
		$list = M("coupon_user")->alias("cu")->join($join)->field($field)->where($where)->order("cu.id desc")->select();
		foreach ($list as $key=>&$value)
		{
			// 不可用优惠券，去除
			if (!$value['status'])
			{
				unset($list[$key]);
				continue;
			}
			if ($value['is_del'] == 1)
			{
				unset($list[$key]);
				continue;
			}
			if ($value['money'])
			{
			    if($value['money']>1000){
                    $value['money'] =intval($value['money']);
                }

			}
			// 满减提示
			$value['conditions_cn'] = "满".$value['conditions']."元可用";
			$shopName = "全平台通用优惠券";
			$couponType = "平台券";
			$newUser = "";			// 是否仅新用户可用 -- 即以前未使用过优惠券的
			$effectiveTime = "";	// 有效时间
			if ($value['shop_id'])
			{
				$shopName = M("shop")->where("id=".$value['shop_id'])->getField("shop_name");
				$shopName = $shopName ? $shopName : "平台商家";
				$shopName = "仅限".$shopName."使用";
				$couponType = "商家券";
			}
			if ($value['goods_id'])
			{
				$shopName = M("goods")->where("id=".$value['goods_id'])->getField("name");
				$shopName = $shopName ? $shopName : "指定商品";
				$shopName = "仅限".$shopName."使用";
				$couponType = "商品券";
			}

			$value['shop_name'] = $shopName;
			$value['coupon_type'] = $couponType;
			if ($value['to_new_user'])
			{
				$newUser = "[新用户]";
			}
			$value['to_new_user_cn'] = $newUser;

			$effectiveTime = date("Y.m.d", $value['use_start_time'])." - ".date("Y.m.d", $value['use_end_time']);
			$value['effective_time'] = $effectiveTime;

			// 赋值优惠券真实状态
			// $type 		类型，0全部，1未使用，2已使用，3已过期
			if ($type == 2)
			{
				$value['use_status'] = 0;
			}
			elseif ($type == 3 )
			{
				$value['use_status'] = 0;
			}
			elseif ($type == 1 )
			{
				$value['use_status'] = 1;
			}
			else
			{
				if ($value['order_id'] > 0 || $value['use_end_time'] < time() )
				{
					$value['use_status'] = 0;
				}
				else
				{
					$value['use_status'] = 1;
				}
			}
		}
		$list = array_values($list);
		if (!$list)
		{
			jsonout("暂无优惠券，可去领券中心领取", 1);
		}
		else
		{
			jsonout("我的优惠券", 0, $list);
		}
	}

    
}