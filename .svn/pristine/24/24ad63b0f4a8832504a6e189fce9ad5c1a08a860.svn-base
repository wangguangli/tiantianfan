<?php

namespace Api\Controller;

use Clas\Orderout;
use Clas\User;

class UseroutController extends CommonController
{

	/**
	 * 个人信息输出
	 * @param string $user_id 用户ID
	 * @return  json
	 * 可输出用户基本信息，若有需要 可输出商家信息、代理信息
	 * @zd
	 */
	public function index()
	{
		set_time_limit(0);
		if (!$this->user_id)
		{
			jsonout('缺少主要参数');
		}
		if ($this->user["is_del"] || $this->user["status"] > 1)
		{
			jsonout('账户异常，请联系客服', 0, $this->user);
		}

		// 获取所有未结算收益的订单，然后循环处理收益
		$where_og['user_id'] = $this->user_id;
		$where_og['isdel'] = 0;
		$where_og['is_profit_1|is_profit_2|is_profit_3'] = 0;
		$where_og['order_status'] = array("in", "1,2,3,5");
		$orderList = M("order")->where($where_og)->field("id")->select();
		foreach ($orderList as $key => $value)
		{
			order_logic($value['id']);
		}

		$where_s["user_id"] = $this->user_id;
		$this->user['collage'] = M('favorites')->where($where_s)->count();
		if ($this->user["is_shop"])
		{
			$shopinfo = get_user_info($where_s, "*", 0, 2);    // 这里通过接口取值
			$this->user["shopinfo"] = $shopinfo;
		}
		if ($this->user["is_agent"])
		{
			$agentinfo = get_user_info($where_s, "*", 0, 3);    // 这里通过接口取值
			$this->user["agentinfo"] = $agentinfo;
		}
		if ($this->user["first_leader"])
		{
			$where_f["id"] = $this->user["first_leader"];
			$firstInfo = get_user_info($where_f, "id,level,phone,realname");
		}
		if ($firstInfo)
		{
			$this->user["first_leader_phone"] = $firstInfo["phone"];
			$this->user["first_leader_name"] = $firstInfo["realname"];
		}
		else
		{
			$this->user["first_leader_phone"] = "";
			$this->user["first_leader_name"] = "";
		}


		//计算当前的订单数量
		//查询订单的相关数量
		$orderWhere["user_id"] = $this->user_id;
		$orderWhere["order_status"] = 0;
		$orderWhere["order_type"] = array('not in', '1,2,3,4');;
		$orderWhere["isdel"] = 0;
		$payOrder = M('order')->where($orderWhere)->count();//待付款的订单数量
		if ($payOrder > 9)
		{
			$payOrder = 9;
		}
		$orderWhere['order_status'] = 1;
		$ship = M('order')->where($orderWhere)->count();//待发货的订单数量
		if ($ship > 9)
		{
			$ship = 9;
		}
		$orderWhere['order_status'] = 2;
		$receipt = M('order')->where($orderWhere)->count();//待收货的订单数量
		if ($receipt > 9)
		{
			$receipt = 9;
		}
		$orderWhere['order_status'] = 3;
		$comment = M('order')->where($orderWhere)->count();//待评论的订单数量
		if ($comment > 9)
		{
			$comment = 9;
		}

		//退款订单  目前返回0  app暂不显示
		$refundsWhere['user_id'] = $this->user_id;
		$refundsWhere['isdel'] = 0;
		$refunds_num = M('order_refund')->where($refundsWhere)->count();
		if (!$this->user['qrcode'])
		{
			$this->user['qrcode'] = $this->make_qrcode();
		}
		$this->user['pay_order_number'] = $payOrder;
		$this->user['ship_order_number'] = $ship;
		$this->user['receipt_order_number'] = $receipt;
		$this->user['comment_order_number'] = $comment;
		$this->user['refunds_num'] = 0;//目前暂时不显示
		jsonout('个人中心', 0, $this->user);
	}

	/**
	 * 用户列表 ，可搜索
	 * @param string $keyword 搜索....
	 * @return  json
	 * @zd
	 */
	public function userList()
	{
		// last_id 和 page 可同时存在，last_id优先，page为次，
		$last_id = I("last_id", 0);
		$page = I("page", 1);
		$type = I('type');
		$data = I('data');
		if ($type == 0)
		{
			if (!empty($data))
			{
				$where['phone'] = array('eq', $data);
			}
		}
		else
		{
			if ($type == 1)
			{
				if (!empty($data))
				{
					$where['nickname'] = array('like', '%' . $data . '%');
				}

			}
		}

		$paging['page'] = $page;
		$paging['last_id'] = $last_id;
		$num = 10;

		$obj = new User();
		$where['is_del'] = 0;
		$ok = $obj->userList($where, "id desc", $num, $paging);

		if (!$ok)
		{
			jsonout("暂无数据");
		}
		$numArr = $obj->getNums($where, $num);
		$res['list'] = $ok;
		$res['page'] = $page;
		$res['last_id'] = get_last_id($ok, 'id', $num);
		$res['max_page'] = $numArr['max_page'];
		jsonout("用户列表", 0, $res);
	}

	/**
	 * 这个是测试用
	 * @return  json
	 * @zd
	 */
	public function getUserList()
	{
		$page = I('page', 1, 'intval');
		$start = ($page - 1) * 10;
		$count = M('user')->count();
		$max_page = ceil($count / 10);
		$data['users'] = M('user')->limit($start, 10)->select();
		$data['page'] = $page;
		$data['max_page'] = $max_page;
		jsonout("用户列表", 0, $data);
	}

	/**
	 * 用户账户收支记录
	 * @param string $last_id 最后一个ID，分页使用
	 * @param string $page 页码，分布使用
	 * @param string $user_id
	 * @param string $type 账户类型
	 * @param string $deal_type 交易类型
	 * @param string $utype 用户类型，1用户，2商家，3代理
	 * @param string $op_type 操作类型，1增加，2减少
	 * @return  json
	 * @zd
	 */
	public function accountLog()
	{
		$page = I("page", 1);
		$user_id = I("user_id", 0);
		$type = I("type", 0);
		$deal_type = I("deal_type", 0);
		$utype = I("utype", 1);
		$op_type = I("op_type", 0);
		$paging['page'] = $page;

		$num = 10;

		if (!$user_id)
		{
			jsonout("没有更多数据");
		}

		if ($user_id)
		{
			$where['user_id'] = $user_id;
		}
		if ($deal_type)
		{
			$where['deal_type'] = $deal_type;
		}
		if ($type)
		{
			$where['type'] = $type;
		}
		if ($type == -1)
		{
			$where['type'] = 1;
			$where['deal_type'] = array('in', array(16, 17, 18, 19, 20));
		}
		if ($utype)
		{
			$where['utype'] = $utype;
		}
		if ($op_type)
		{
			$where['op_type'] = $op_type;
		}


		$obj = new User();
		$ok = $obj->accountLog($where, "id desc", $num, $paging);
		if (!$ok)
		{
			jsonout("暂无数据");
		}
		$numArr = $obj->getAccountLogNums($where, $num);
		$res['list'] = $ok;
		$res['page'] = $page;
		$res['last_id'] = get_last_id($ok, 'id', $num);
		$res['max_page'] = $numArr['max_page'];
		jsonout("收支记录", 0, $res);
	}

	/**
	 * 根据手机号输出用户基本信息
	 * @param string $phone 手机号
	 * @return  json            用户基本信息
	 * @zd
	 */
	public function baseInfoByPhone()
	{
		$phone = I("phone");
		$where["phone"] = $phone;
		$data = M("user")->where($where)->field("id,phone,realname,nickname,level")->find();
		if (!$data)
		{
			jsonout("此用户不存在", 1);
		}
		jsonout("用户基本信息", 0, $data);
	}

	/**
	 * 提现说明
	 * @param int $type 1消费者，2商家，3代理
	 * @return  json            用户基本信息
	 * @zd
	 */
	public function moneyOutNotice()
	{
		$type = I("type", 1);
		$type = $type ? $type : 1;
		$info = M("config_tx")->where("type=" . $type)->order("id desc")->find();
		$res = array('notice' => '', 'money' => '0.00');
		if (!$info)
		{
			jsonout("没有提现说明，不用操作", 0, $res);
		}

		$notice = "最小提现金额为" . $info['tx_min'];
		$notice .= "，最大提现金额为" . $info['tx_max'];
		if ($info['tx_min_type'] == 2)
		{
			$notice .= "，提现金额必须为" . $info['tx_min_type_val'] . "倍数。";
		}
		if ($info['tx_num'] > 0)
		{
			if ($info['tx_num_type'] == 2)
			{
				$notice .= "每月提现" . $info['tx_num'] . "次。";
			}
			else
			{
				$notice .= "每日提现" . $info['tx_num'] . "次。";
			}
		}
		if ($info['tx_fee_type'] == 2)
		{
			$notice .= "每笔手续费" . $info['tx_fee'] . "%。";
		}
		else
		{
			$notice .= "每笔手续费" . $info['tx_fee'] . "元。";
		}
		if ($info['notice'])
		{
			$notice .= $info['notice'];
		}
		jsonout("提现说明", 0, $notice);
	}

	/**
	 * 商品浏览记录
	 * @param string $last_id 最后一个ID，分页使用
	 * @param string $page 页码，分布使用
	 * @param string $user_id
	 * @return  json
	 */
	public function footprint()
	{
		// last_id 和 page 可同时存在，last_id优先，page为次，
		$last_id = I("last_id", 0);
		$page = I("page", 1);
		$user_id = I("user_id", 0);
		$paging['page'] = $page;
		$num = 10;

		if (!$user_id)
		{
			jsonout("没有更多数据");
		}

		if ($user_id)
		{
			$where['user_id'] = $user_id;
		}
		$obj = new User();
		$ok = $obj->footprint($where, "id desc", $num, $paging);
		if (!$ok)
		{
			jsonout("暂无数据");
		}
		$numArr = $obj->getFootprintNums($where, $num);
		$res['list'] = $ok;
		$res['page'] = $page;
		$res['max_page'] = $numArr['max_page'];
		jsonout("收支记录", 0, $res);
	}

	/*
	 * 查询双牛商家账户余额，只有余额，不含有商户信息
	 * @zd
	 */
	public function getMerchantMoney()
	{
		$payInfo = M("config_topay")->order("id desc")->find();
		if (!$payInfo)
		{
			jsonout("没有双牛商户");
		}
		$url = "http://pay.ishuangniu.com/index.php/Payapi/Fourpay/queryBalance";
		$sign = md5($payInfo['account'] . date("YmdH") . $payInfo['acc_key']);
		$postData['merchant_id'] = $payInfo['account'];
		$postData['sign'] = $sign;
		$res = httpRequest($url, "post", $postData);
		$res = json_decode($res, true);
		if ($res['status'] == '0000')
		{
			jsonout("商户信息", 0, $res['data']);
		}
		else
		{
			jsonout($res['msg']);
		}
	}


	/**
	 * 我的分销页面数据
	 * @author lty
	 */
	public function distribution()
	{
		$user_id = $this->user_id;
		$userInfo = M('user')->find($user_id);
		//上级的信息
		if ($userInfo['first_leader'])
		{
			$top = M('user')->find($userInfo['first_leader']);
			$top_name = $top['nickname'];
		}
		else
		{
			$top_name = '';
		}

		//已提金额
		$txWhere['user_id'] = $user_id;
		$txWhere['utype'] = 1;
		$txWhere['ischeck'] = 1;
		$price = M('tx_log')->where($txWhere)->sum('tx_money');
		if (!$price)
		{
			$price = 0;
		}

		//预得佣金
		$rebateWhere['user_id'] = $user_id;
		$rebateWhere['is_profit'] = 0;
		$rebateMoney = M('distribution_log')->where($rebateWhere)->sum('money');
		if (!$rebateMoney)
		{
			$rebateMoney = 0;
		}
		$array = array(
			'user_id' => $user_id,
			'nickname' => $userInfo['nickname'],
			'headimgurl' => $userInfo['headimgurl'],
			'first_leader' => $userInfo['first_leader'],
			'first_leader_name' => $top_name,
			'tx_money' => $price,//已提现金额
			'money' => $userInfo['money'],//可提金额
			'expected_money' => $rebateMoney,//预得佣金
		);
		jsonout('查询成功', 0, $array);
	}


	/**
	 * 分销订单
	 * @author lty
	 */
	public function distributionOrder()
	{
		//查询新表  查询时根据订单相关状态修改数据表的状态
		$user_id = $this->user_id;
		$orderOut = new Orderout($user_id);
		$res = $orderOut->distributionOrder();
		if ($res['list'])
		{
			jsonout('查询成功', 0, $res);
		}
		else
		{
			jsonout('暂无数据');
		}

	}

	/**
	 * 推广二维码合成
	 */
	public function make_qrcode()
	{
		header("Content-Type: text/html; charset=UTF-8");
		$user_id = $this->user['id'];
		$where['id'] = $user_id;
		$data = get_user_info($where);
		Vendor('phpqrcode.phpqrcode');
		$url = C("C_HTTP_HOST") . ('M/Index/reg') . '?tuijian=' . $data['phone'];
		$url = urldecode($url);
		$time = time();
		$filename = 'upload/qrcode/' . $data['id'] . '_' . $time . '.png';
		$object = new \QRcode();
		// // ob_clean();//这个一定要加上，清除缓冲区
		$object->png($url, $filename, 'Q', '6', '2');
		$qrcode = '/' . $filename;
		$code = 'upload/qrcode/' . $data['id'] . '_' . $time . '.png';
		$this->createSharePng($qrcode, $code);
		$date['qrcode'] = '/' . $code;
		M('user')->where(array('id =' . $user_id))->save($date);
		return $date['qrcode'];
	}

	/**
	 * 分享图片生成
	 * @param $codeName 二维码图片
	 * @param $fileName string 保存文件名,默认空则直接输入图片
	 */
	public function createSharePng($codeName, $fileName = '')
	{
		header("Content-Type: text/html; charset=UTF-8");
		//原始图像
		$dst = get_config_poster('tuiguang_haibao');
		$preg = "/^http(s)?:\\/\\/.+/";
		if (preg_match($preg, $dst))
		{
			$dst = get_config_poster('tuiguang_haibao');
		}
		else
		{
			$dst = $_SERVER['DOCUMENT_ROOT'] . get_config_poster('tuiguang_haibao');
		}
		$data = (pathinfo($dst));
		if ($data['extension'] == 'png')
		{
			$dst_im = imagecreatefrompng($dst);
		}
		elseif ($data['extension'] == 'jpg' || $data['extension'] == 'jpeg')
		{
			$dst_im = imagecreatefromjpeg($dst);
		}
		elseif ($data['extension'] == 'gif')
		{
			$dst_im = imagecreatefromgif($dst);
		}
		$dst_info = getimagesize($dst);
		//二维码图像
		$src = $_SERVER['DOCUMENT_ROOT'] . $codeName;
		$src_im = imagecreatefrompng($src);
		$src_info = getimagesize($src);
		$w = get_config_poster('ewm_width');//二维码宽度
		$h = get_config_poster('ewm_height');//二维码高度
		$ewm_top = get_config_poster('ewm_top');//二维码上边距
		$ewm_left = get_config_poster('ewm_left');//二维码左边距
		$image = imagecreatetruecolor($w, $h);
		imagecopyresampled($image, $src_im, 0, 0, 0, 0, $w, $h, $src_info['0'], $src_info['1']);
		imagecopymerge($dst_im, $image, $ewm_left, $ewm_top, 0, 0, $w, $h, 100);//合并二维码
		//合并水印图片
		//输出图片
		if ($fileName)
		{
			imagepng($dst_im, $fileName);   // 生成图片之后，保存
		}
		else
		{
			Header("Content-Type: image/png");  // 生成图片之后，展示出来
			imagepng($dst_im);
		}
		imagejpeg($dst_im); //  这里控制输出jpg图片
		imagedestroy($dst_im);
		imagedestroy($src_im);
	}


	/**
	 * 二维码图片
	 */
	public function qrcode($url = "", $level = 3, $size = 4)
	{
		$user_id = session('user_id');
		if (empty($url))
		{
			$url = C("C_HTTP_HOST") . U('Index/reg') . '?user_id=' . $user_id;
		}
		Vendor('phpqrcode.phpqrcode');
		$errorCorrectionLevel = intval($level);//容错级别
		$matrixPointSize = intval($size);//生成图片大小
		//生成二维码图片
		$object = new \QRcode();
		$object->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
	}

	//二维码保存
	public function saveEwm()
	{
		header("Content-Type: text/html; charset=UTF-8");
		$user_id = I('user_id');
		$user = M('user')->where('id=' . $user_id)->find();
		if ($user['code_image'])
		{
			jsonout('获取成功', 0, C("C_HTTP_HOST") . $user['code_image']);
		}
		else
		{
			$qr_path = "./upload/";
			if (!file_exists($qr_path . 'user/'))
			{
				mkdir($qr_path . 'user/', 0700, true);//判断保存目录是否存在，不存在自动生成文件目录
			}
			$time = time();
			$filename = 'upload/user/' . $time . '.png';
			$token = get_wxtoken();
			$url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $token;

			$arr = array(
				'scene' => $user_id,
				'page' => 'pages/mall/index'
			);
			$resp = httpRequest($url, 'POST', json_encode($arr));
			$qrcode = '/' . $filename;
			$code = 'upload/user/' . I('user_id') . '_' . $time . '.png';
			$date['code_image'] = '/' . $code;

			$res = file_put_contents($filename, $resp);//将微信返回的图片数据流写入文件
			$this->createSharePng1($qrcode, $code);
			$re = M('user')->where('id=' . $user_id)->save($date);
			if ($re)
			{
				jsonout('获取成功', 0, C("C_HTTP_HOST") . $date['code_image']);
			}
		}
	}

	/**
	 * 分享图片生成
	 * @param $codeName 二维码图片
	 * @param $fileName string 保存文件名,默认空则直接输入图片
	 */
	public function createSharePng1($codeName, $fileName = '')
	{
		header("Content-Type: text/html; charset=UTF-8");
		//原始图像
		$dst = C("C_HTTP_HOST") . '/Public/mini/images/code.png';
		$preg = "/^http(s)?:\\/\\/.+/";
		if (preg_match($preg, $dst))
		{
			$dst = C("C_HTTP_HOST") . '/Public/mini/images/code.png';
		}
		else
		{
			$dst = C("C_HTTP_HOST") . '/Public/mini/images/code.png';
		}
		$data = (pathinfo($dst));
		if ($data['extension'] == 'png')
		{
			$dst_im = imagecreatefrompng($dst);
		}
		elseif ($data['extension'] == 'jpg' || $data['extension'] == 'jpeg')
		{
			$dst_im = imagecreatefromjpeg($dst);
		}
		elseif ($data['extension'] == 'gif')
		{
			$dst_im = imagecreatefromgif($dst);
		}
		$dst_info = getimagesize($dst);
		//二维码图像
		$src = $_SERVER['DOCUMENT_ROOT'] . $codeName;
		// $src_im = imagecreatefrompng($src);
		$src_im = imagecreatefromstring(file_get_contents($src));
		$src_info = getimagesize($src);
		$w = get_config_poster('ewm_width');//二维码宽度
		$h = get_config_poster('ewm_height');//二维码高度
		$ewm_top = get_config_poster('ewm_top');//二维码上边距
		$ewm_left = get_config_poster('ewm_left');//二维码左边距
		$image = imagecreatetruecolor($w, $h);
		imagecopyresampled($image, $src_im, 0, 0, 0, 0, $w, $h, $src_info['0'], $src_info['1']);
		imagecopymerge($dst_im, $image, $ewm_left, $ewm_top, 0, 0, $w, $h, 100);//合并二维码
		//合并水印图片
		//输出图片
		ob_clean();
		if ($fileName)
		{
			imagepng($dst_im, $fileName);   // 生成图片之后，保存

		}
		else
		{
			Header("Content-Type: image/png");  // 生成图片之后，展示出来
			imagepng($dst_im);
		}
		/* imagejpeg($dst_im); //  这里控制输出jpg图片
		 imagedestroy($dst_im);
		 imagedestroy($src_im);*/
	}


	/**
	 * Title：VIP 等级列表
	 * Note：
	 * User： zd
	 * Date： 2020-10-23
	 */
	public function upgrade()
	{
		$user_id = I("user_id", 0);
		_error_log($this->user);
		if ($user_id)
		{
			$level = $this->user['level'];
		}
		else
		{
			$level = 0;
		}
		$list = M("config_user_level")->where("up_level_by_money=1 and is_del=0 and status=1 and level_ranking>" . $level)->order("level_ranking asc")->field("id,level_name,up_level_money")->select();
		foreach ($list as $key => &$value)
		{
			$value['remark'] = "会员专属服务\n福利、保障";
		}
		jsonout("等级列表", 0, $list);
	}


}