<?php

namespace Api\Controller;

use Clas\Shop;
use Clas\User;

class ShopinController extends CommonController
{


	/**
	 * 申请成为商家
	 * 一般传入参数
	 * @param string $shop_name 店铺名
	 * @param image $thumb 店铺形像图片
	 * @param string $cntact_person 联系人
	 * @param string $tel 联系电话
	 * @param int $province_id 省id
	 * @param int $city_id 市id
	 * @param int $district_id 县id
	 * @param int $town_id 乡镇街道id
	 * @param string $address 详细地址
	 * @param string $coordinate 格式:纬度,经度；如：116.123456,35.123456
	 * @param image $license1 身份证正面
	 * @param image $license2 身份证反面
	 * @param image $license3 营业执照
	 * @param int $industry 行业id
	 * @param string $description 店铺介绍（描述）
	 * @param string $images 店铺环境列表，格式:图片1,图片2,图片3,请看下面的备注
	 * @param int $type 店铺类型:1商家2个人。个人不用传营业执照
	 * @return  json
	 * @wz
	 */
	public function applyShop()
	{
		$obj = new Shop(0, 'user', $this->user_id);
		$ok = $obj->add();
		if (!is_numeric($ok))
		{
			jsonout($ok);
		}
		jsonout('提交成功请等待审核', 0);
	}

	/**
	 * 修改商家信息
	 * 传入参数类似新增时
	 * @return  json
	 * @zd
	 */
	public function editShop()
	{
		$obj = new Shop($this->user_id, 'user', $this->user_id);
		$ok = $obj->updateInfo();
		if ($ok != 'ok')
		{
			jsonout('修改失败:' . $ok, 1);
		}
		else
		{
			jsonout('修改完成', 0);
		}
	}

	/**
	 * 商家 账户余额 转换到 交易账户
	 * 按比例处理
	 * @param int $user_id 商家UID -- 不用了 20190620
	 * @param int $shop_id 商家UID
	 * @param int $money 要转换的额度
	 * @return  json
	 * @zd
	 */
	public function m2sm()
	{
		$money = I("money", 0);
		$shop_id = I("shop_id", 0);
		if ($money <= 0)
		{
			jsonout("请输入转换额度");
		}
		if (!$shop_id)
		{
			jsonout("缺少主要参数");
		}

		$where['id'] = $shop_id;
		$shopInfo = get_user_info($where, '', '', 2);
		if (!$shopInfo || $shopInfo['status'] != 1)
		{
			jsonout("商家信息异常，请重新登录");
		}
		if ($shopInfo['money'] <= 0)
		{
			jsonout("账户余额不足");
		}
		if ($shopInfo['money'] < $money)
		{
			jsonout("账户额度小于转换额度，请修改后转换");
		}
		$x_shop = get_config_project("x_shop");    //
		if ($x_shop <= 0)
		{
			jsonout("转换比例设置异常，请联系客服");
		}
		// 减少账户余额，增加交易账户额度（此额度要缩小相应的比例）
		$shop_money = round($money / $x_shop, 2);
		if ($shop_money <= 0)
		{
			jsonout("转换后额度<=0，请重新转换");
		}
		$model = M();
		$model->startTrans();

		$rs1 = change_money_log($shopInfo['user_id'], 1, "money", 0, $money, 7, 6, "账户余额转换交易账户", 2, 2, 1);
		$rs2 = change_money_log($shopInfo['user_id'], 1, "shop_money", 0, $shop_money, 2, 6, "账户余额转换到交易账户", 1, 2, 1);

		if ($rs1 && $rs2)
		{
			$model->commit();
			jsonout("转换完成", 0);
		}
		else
		{
			$model->rollback();
			jsonout("转换失败，请重新转换");
		}
	}

	/**
	 * Notes:添加线下门店
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 8:51
	 */
	public function applay_offile_shop()
	{
		$obj = new Shop(0, 'user', $this->user_id);
		$ok = $obj->applay_offile_shop();
		if (!is_numeric($ok))
		{
			jsonout($ok);
		}
		jsonout('操作成功', 0);
	}

	/**
	 * Notes:删除线下门店
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 9:31
	 */
	public function delete_shop_offile()
	{
		$obj = new Shop(0, 'user', $this->user_id);
		$ok = $obj->delete_shop_offile();
		if (!is_numeric($ok))
		{
			jsonout($ok);
		}
		jsonout('操作成功', 0);
	}

	/**
	 * Notes:开启或者关闭线下门店
	 * User: WangSong
	 * Date: 2020/7/8
	 * Time: 9:38
	 */
	public function is_offline_shop()
	{
		$obj = new Shop(0, 'user', $this->user_id);
		$ok = $obj->is_offline_shop();
		if (!is_numeric($ok))
		{
			jsonout($ok);
		}
		jsonout('操作成功', 0);
	}

}