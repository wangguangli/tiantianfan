<?php

namespace Api\Controller;

use Think\Controller;
use Clas\Orderin;
use Think\Model;

class NotifyinController extends Controller
{


	/**
	 * *** 此控制器主要应用各种回调处理 ***
	 */


	/**
	 * 双牛下发的回调
	 * @zd
	 */
	public function snxf()
	{
		$data = I();
		if ($data['result_code'] == '0000')
		{
			$where['ordernum'] = $data['ordernum'];
			$log = M('tx_log')->where($where)->find();

			$data_up["ischeck"] = 1;
			$data_up["up_time"] = time();
			$data_up["up_date"] = date("Y-m-d H:i:s");
			$data_up["remark"] = "提现" . $log["tx_money"] . "元，已完成";
			M('tx_log')->where("id = {$log['id']}")->save($data_up);
		}
		else
		{
		}
	}

	/**
	 * 支付宝APP支付的回调
	 * @zd
	 */
	public function alipay_notify()
	{
		$href = THINK_PATH . "/Library/Vendor/Alipay";
		require_once $href . '/config.php';
		require_once $href . '/pagepay/service/AlipayTradeService.php';


		// 如果用这个验证，就要把config信息补全
		// $alipaySevice = new \AlipayTradeService($config);
		// $result = $alipaySevice->check($_POST);
		// if ($result)
		// {
		// 	# code...
		// }

		$out_trade_no = $_POST['out_trade_no'];    // 商户订单号
		$trade_status = $_POST['trade_status'];    // 交易状态


		if ($_POST['trade_status'] == 'TRADE_SUCCESS')
		{
			$config_pay3 = get_config_pay3(2);
			if ($config_pay3["appid"] !== $_POST['app_id'])
			{
				exit("fail");
			}

			// 先查第三方表
			// 如果未操作过，那么更新相应记录的信息
			// 并根据记录查找相应的订单
			// 找到订单的后，处理订单的状态
			// 处理商品库存和销量
			//  *** 有些项目还需要处理提成相关的信息 ***

			$where_log["serial_no"] = $out_trade_no;
			$pay3Info = M("pay3_log")->where($where_log)->find();
			if (!$pay3Info)
			{
				exit("fail");
			}
			if (round($pay3Info['money'], 2) != round($_POST['total_amount'], 2))
			{
				exit("fail");
			}
			if ($pay3Info["status"] > 0)
			{
				exit("success");
			}

			$where_ord["id"] = $pay3Info["order_id"];
			$orderInfo = M("order")->where($where_ord)->find();
			if (!$orderInfo)
			{
				exit("fail");
			}
			if ($orderInfo['order_status'] > 0 || $orderInfo['order_status'] < 0)
			{
				exit("fail");
			}
			if ($orderInfo['pay_status'] == 1)
			{
				exit("fail");
			}
			if($orderInfo['order_type'] == 11){
                if ($orderInfo['total_express_fee'] != $_POST['total_amount'])
                {
                    exit("fail");
                }
                $remark = "支付宝支付运费";
                $money = $orderInfo['total_express_fee'];
            }else{
                if ($orderInfo['total_commodity_price'] != $_POST['total_amount'])
                {
                    exit("fail");
                }
                $money = $orderInfo['total_commodity_price'];
                $remark = "支付宝支付消费";
            }


			$where_up['id'] = $pay3Info['id'];
			$data_up["status"] = 1;
			$data_up["trade_no"] = $_POST['trade_no'];
			$data_up["buyer_open_id"] = $_POST['buyer_id'];
			$data_up["up_time"] = time();
			$data_up["up_date"] = date("Y-m-d H:i:s");
			M("pay3_log")->where($where_up)->save($data_up);

			// 记录消费
			change_money_log($orderInfo["user_id"], 0, "money", 0, $money, 5, 2, $remark, 2, 1, 0, $orderInfo['id']);

			// 更新订单状态（主订单、子订单）
			$Orderin = new Orderin(0);
			$rs = $Orderin->pay_success($orderInfo["id"], "alipay");

		}
		else
		{
			// 失败
			echo "fail";
		}
	}


	/**
	 * 微信APP支付的回调
	 * @zd
	 */
	public function wxpay_notify()
	{
		$href = THINK_PATH . "/Library/Vendor/Wxpay/lib";
		require_once $href . '/WxPayData.php';

		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		if (!$xml)
		{
			$xml = file_get_contents('php://input');
		}
		if ($xml)
		{
			$pdb = new \WxPayDataBase();
			$arr = $pdb->FromXml($xml);
			$xml_no = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[NO]]></return_msg></xml>";
			$xml_ok = "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";

			// 要取数据库的值
			$config_pay3 = get_config_pay3(1);
			if ($config_pay3["appid"] !== $arr['appid'])
			{
				exit($xml_no);
			}

			if ($arr['result_code'] == 'SUCCESS')
			{
				$out_trade_no = $arr['out_trade_no'];

				$where_log["serial_no"] = $out_trade_no;
				$pay3Info = M("pay3_log")->where($where_log)->find();
				if (!$pay3Info)
				{
					exit($xml_no);
				}
				$total_amount = round($arr["total_fee"] / 100, 2);
				if ($pay3Info['money'] != $total_amount)
				{
					exit($xml_no);
				}
				if ($pay3Info["status"] > 0)
				{
					exit("success");
				}

				$where_ord["id"] = $pay3Info["order_id"];
				$orderInfo = M("order")->where($where_ord)->find();
				if (!$orderInfo)
				{
					exit($xml_no);
				}
				if ($orderInfo['order_status'] > 0 || $orderInfo['order_status'] < 0)
				{
					exit($xml_no);
				}
				if ($orderInfo['pay_status'] == 1)
				{
					exit($xml_no);
				}
				if($orderInfo['order_type'] == 11){
                    if ($orderInfo['total_express_fee'] != $total_amount)
                    {
                        exit($xml_no);
                    }
                    $remark = "微信支付运费";
                    $money = $orderInfo['total_express_fee'];
                }else{
                    if ($orderInfo['total_commodity_price'] != $total_amount)
                    {
                        exit($xml_no);
                    }
                    $money = $orderInfo['total_commodity_price'];
                    $remark = "微信消费";
                }


				$where_up['id'] = $pay3Info['id'];
				$data_up["status"] = 1;
				$data_up["trade_no"] = $arr['transaction_id'];
				$data_up["buyer_open_id"] = $arr['openid'];
				$data_up["up_time"] = time();
				$data_up["up_date"] = date("Y-m-d H:i:s");
				M("pay3_log")->where($where_up)->save($data_up);

				// 记录消费
				change_money_log($orderInfo["user_id"], 0, "money", 0, $money, 4, 2, $remark, 2, 1, 0, $orderInfo['id']);

				// 更新订单状态（主订单、子订单）
				$Orderin = new Orderin(0);
				$rs = $Orderin->pay_success($orderInfo["id"], "wxpay");

				if ($rs)
				{
					exit($xml_ok);
				}
			}
			else
			{
				exit($xml_no);
			}


		}

	}

	// 微信公众号支付
	public function wxmp_notify()
	{
		$href = "./ThinkPHP/Library/Vendor/Wxpay/lib";
		require_once $href . '/WxPayData.php';

		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		if (!$xml)
		{
			$xml = file_get_contents('php://input');
		}
		if ($xml)
		{
			$pdb = new \WxPayDataBase();
			$arr = $pdb->FromXml($xml);
			$xml_no = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[NO]]></return_msg></xml>";
			$xml_ok = "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";

			// 要取数据库的值
			$config_pay3 = get_config_pay3(3);
			if ($config_pay3["appid"] !== $arr['appid'])
			{
				exit($xml_no);
			}

			if ($arr['result_code'] == 'SUCCESS')
			{
				$out_trade_no = $arr['out_trade_no'];

				$where_log["serial_no"] = $out_trade_no;
				$pay3Info = M("pay3_log")->where($where_log)->find();
				if (!$pay3Info)
				{
					exit($xml_no);
				}
				$total_amount = round($arr["total_fee"] / 100, 2);
				if ($pay3Info['money'] != $total_amount)
				{
					exit($xml_no);
				}
				if ($pay3Info["status"] > 0)
				{
					exit("success");
				}

				$where_ord["id"] = $pay3Info["order_id"];
				$orderInfo = M("order")->where($where_ord)->find();
				if (!$orderInfo)
				{
					exit($xml_no);
				}
				if ($orderInfo['order_status'] > 0 || $orderInfo['order_status'] < 0)
				{
					exit($xml_no);
				}
				if ($orderInfo['pay_status'] == 1)
				{
					exit($xml_no);
				}
				if($orderInfo['order_type'] == 11){
                    if ($orderInfo['total_express_fee'] != $total_amount)
                    {
                        exit($xml_no);
                    }
                    $money = $orderInfo['total_express_fee'];
                    $remark = "微信支付运费";
                }else{
                    if ($orderInfo['total_commodity_price'] != $total_amount)
                    {
                        exit($xml_no);
                    }
                    $money = $orderInfo['total_commodity_price'];
                    $remark = "微信消费";
                }


				$where_up['id'] = $pay3Info['id'];
				$data_up["status"] = 1;
				$data_up["trade_no"] = $arr['transaction_id'];
				$data_up["buyer_open_id"] = $arr['openid'];
				$data_up["up_time"] = time();
				$data_up["up_date"] = date("Y-m-d H:i:s");
				M("pay3_log")->where($where_up)->save($data_up);

				// 记录消费
				change_money_log($orderInfo["user_id"], 0, "money", 0, $money, 4, 2, $remark, 2, 1, 0, $orderInfo['id']);

				// 无openid，则记录入库 为新用户
				if ($arr['openid'])
				{
					if ($orderInfo['unionid'])
					{
						$where_u["unionid"] = $orderInfo["unionid"];
						$rso = M("user")->where($where_u)->find();
						if (!$rso)
						{
							$data_o["unionid"] = $orderInfo["unionid"];
							$data_o["cre_time"] = time();
							$data_o["ip"] = get_client_ip();
							$uid = M("user")->add($data_o);
						}
						else
						{
							$uid = $rso["id"];
						}
					}
					else
					{
						$where_u["openid"] = $arr["openid"];
						$rso = M("user")->where($where_u)->find();

						if (!$rso)
						{
							$data_o["openid"] = $arr["openid"];
							$data_o["cre_time"] = time();
							$data_o["ip"] = get_client_ip();
							$uid = M("user")->add($data_o);
						}
						else
						{
							$uid = $rso["id"];
						}
					}

					if ($uid && !$orderInfo["user_id"])
					{
						M("order")->where("id=" . $orderInfo["id"])->setField("user_id", $uid);
						M("pay3_log")->where("id=" . $pay3Info["id"])->setField("user_id", $uid);
					}
				}

				// 更新订单状态（主订单、子订单）
				$Orderin = new Orderin(0);
				$rs = $Orderin->pay_success($orderInfo["id"], "wxpay");

				if ($rs)
				{
					exit($xml_ok);
				}
			}
			else
			{
				exit($xml_no);
			}
		}
	}


	// 快递回调接口
	public function express_notify()
	{

		// "success":true,"rspBeans":[{
		// "account":"18053966531","tid":"A100010","name":"许文超","expressCompanyName":"申通快递","expressNo":"70121212121","printTime":"2019-08-21 16:18:00"}]

		$express_json = file_get_contents("php://input");
		$express_arr = json_decode($express_json, true);
		//回调成功 进入   接口请求失败是不用进入回调的

		/*$data = array(
			'order_status' => 2,
			'shipping_status' => 1,
			'express_id' => $express_id,
			'tracking_number' => $tracking_number,
			'shipping_time' => time()
		);
		M('order_goods')->where('order_id=' . $order_id . ' and goods_id=' . $goods_id)->save($data);*/

		if ($express_arr['success'])
		{

			foreach ($express_arr['rspBeans'] as $k => $v)
			{
				$data_og['express_name'] = $v['expressCompanyName'];
				$data_og['tracking_number'] = $v['expressNos'][0];
				$data_og['order_status'] = 2;
				$data_og['shipping_status'] = 1;
				$data_og['shipping_time'] = time();
				$data_or = array(
					'order_status' => 2,
					'shipping_status' => 1,
					'shipping_time' => time(),
				);
				M('order')->where('order_no=' . $v['tid'])->save($data_or);
				$order_id = M('order')->where('order_no=' . $v['tid'])->getField('id');
				$goods_name = '';
				foreach ($v['orderBeans'] as $k1 => $v1)
				{
					M('order_goods')->where('id=' . $v1['oid'])->save($data_og);
					$order_goods = M("order_goods")->where("id=" . $v1["id"])->find();
					$goods_name .= $order_goods["goods_name"] . ",";
				}
				add_order_log($order_id, "发货成功", '商品' . $goods_name);
			}
		}
		echo 'SUCCESS';
	}

}