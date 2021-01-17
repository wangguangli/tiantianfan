<?php
namespace Api\Controller;

use Clas\User;

class CouponinController extends CommonController
{

	/**
	 * 领取优惠券
	 * @param   int $user_id 	用户ID
	 * @param   int $id 		优惠券ID
	 * @return  json
	 * @zd
	 */
	public function getCoupon()
	{
		$user_id = I("user_id", 0);
		$id = I("id", 0);
		if (!$user_id || !$id) {
			jsonout("缺少主要参数");
		}
		$where_ih['cid'] = $id;
		$where_ih['user_id'] = $user_id;
		$isHave = M("coupon_user")->where($where_ih)->count();
		if ($isHave > 0) {
			// 不论是否删除
			jsonout("已领取过此优惠券");
		}
		$where_c['id'] = $id;
		$couponInfo = M("coupon")->where($where_c)->find();
		if (!$couponInfo || $couponInfo['status'] == 0 || $couponInfo['is_del'] == 1)
		{
			jsonout("优惠券可能已下架，请领取其他券");
		}
		if ($couponInfo['start_type'] == 0 && $couponInfo['use_end_time'] < time())
		{
			jsonout("优惠券已过期，请领取其他券");
		}
		if ($couponInfo['send_num'] <= $couponInfo['get_num'] || $couponInfo['send_num'] <= $couponInfo['use_num'] )
		{
			jsonout("不好意思，你来晚了，优惠券已被领完");
		}

		// 只为新用户发放--没下单的用户
		if ($couponInfo['to_new_user'])
		{
			$where_ord['user_id'] = $user_id;
			$where_ord['order_status'] = array("gt", 0);
			$isHave = M("order")->where($where_ord)->count();
			if ($isHave > 0)
			{
				jsonout("此优惠券只能是未下单的新用户领取");
			}
		}

		// 可以领取了

		// 开始时间类型，0是默认按开始-结束的时间，1按领取后计算天数
		if ($couponInfo['start_type'] == 1)
		{
			$use_start_time = time();
			$use_end_time = time()+abs($couponInfo['use_days'])*86400;
		}
		else
		{
			$use_start_time = $couponInfo['use_start_time'];
			$use_end_time = $couponInfo['use_end_time'];
		}
		$model = M();
		$model->startTrans();

		$data['cid'] = $id;
		$data['user_id'] = $user_id;
		$data['use_start_time'] = $use_start_time;
		$data['use_end_time'] = $use_end_time;
		$data['cre_date'] = date("Y-m-d H:i:s");
		$data['cre_time'] = time();
		$data['ip'] = get_client_ip();
		$rs = M("coupon_user")->add($data);

		$rs2 = M("coupon")->where("id=".$id)->setInc("get_num", 1);

		if ($rs && $rs2)
		{
			$model->commit();
			jsonout("领取成功", 0);
		}
		else
		{
			$model->rollback();
			jsonout("领取失败，请重新领取");
		}


	}


	/**
	 * 删除优惠券
	 * @param   int $user_id 	用户ID
	 * @param   int $id 		已优惠券ID
	 * @return  json
	 * @zd
	 */
	public function delCoupon()
	{
		$user_id = I("user_id", 0);
		$id = I("id", 0);
		if (!$user_id || !$id)
		{
			jsonout("缺少主要参数");
		}
		$couponInfo = M("coupon_user")->where("id=".$id)->find();
		if (!$couponInfo)
		{
			jsonout("优惠券不存在");
		}
		if ($couponInfo['is_hide'] == 1 )
		{
			jsonout("优惠券已删除");
		}
		if ($couponInfo['order_id'] > 0)
		{
			jsonout("优惠券已被使用，不可删除");
		}
		$rs = M("coupon_user")->where("id=".$id)->setField("is_hide", 1);
		if ($rs !== false)
		{
			jsonout("删除成功", 0);
		}
		else
		{
			jsonout("删除失败，请重新操作");
		}


	}


}