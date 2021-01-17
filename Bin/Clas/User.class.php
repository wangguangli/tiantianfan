<?php

namespace Clas;
class User
{

	private $_user_id;

	public function __construct($user_id = 0)
	{
		if (!$user_id)
		{
			$user_id = I("user_id", 0);
		}
		$this->user_id = $user_id;
	}

	// 这里一般从APP、手机端注册进入
	public function register_user()
	{
		$phone = I('phone');
		$phone_code = I('phone_code');
		if (!$phone)
		{
			return '手机号不正确';
		}
		$code_status = get_config("code_status");    // 注册时验证码是否开启并验证，1是，0否

		if (!$phone_code && $code_status)
		{
			return '验证码不正确';
		}
		if (!check_phone_code($phone, $phone_code) && $code_status)
		{
			return '验证码不正确';
		}
		$msg = $this->add();
		return $msg;
	}


	// 这里一般后台添加时可直接调用，并可用于上方APP
	public function add()
	{
		$phone = I('phone');
		$password = I('password', '', 'trim');
		$repassword = I('repassword', '', 'trim');
		$tuijian = I('tuijian', '');

		$code = I('phone_code');

		if (!$phone || !check_phone($phone))
		{
			return "手机号不正确";
		}
		if (!$password || $password != $repassword || strlen($password) < 3)
		{
			return "两次密码不正确";
		}

		$count = M('user')->where(array('phone' => $phone))->count();
		if ($count)
		{
			return "手机号已被使用";
		}

		$config = get_config('recommender');

		if ($config == 1)
		{
			if (!$tuijian)
			{
				return "推荐人不能为空";
			}
		}
		$data = array(
			'phone' => $phone,
			'password' => get_pwd($password),
			'realname' => '',
			'nickname' => $phone,
			'headimgurl' => '/Public/images/profile.png',
			'money' => 0,
			'cre_time' => time(),
			'ip' => get_client_ip()
		);

		if ($tuijian)
		{
			// 此处推荐信息是手机号
			$where["phone"] = $tuijian;
			$tInfo = get_user_info($where);
			if ($tInfo)
			{
				$data["first_leader"] = $tInfo["id"];
				$data["second_leader"] = $tInfo["first_leader"];
				$data["third_leader"] = $tInfo["second_leader"];
			}
			else
			{
				return "推荐人不存在";
			}
		}
		$rs = M("user")->add($data);
		if ($rs)
		{
			// 寻找此ID的所有上级，然后更新关系链
			update_recom_node($rs);
			user_sons_add($rs, 0);



//			$parent_user=get_parent($rs);
//			foreach ($parent_user as $key=>$value){
//                up_level_by_auto($value["id"]);
//            }

			return $rs;
		}
		else
		{
			return "注册失败，请重新注册";
		}
	}


	public function address()
	{
		$list = M('user_address')->where("user_id={$this->user_id}")->select();
		if (empty($list))
		{
			return null;
		}

		$region = get_region_list();
		$data = array();
		foreach ($list as $v)
		{
			$data[] = array(
				'address_id' => $v['id'],
				'consignee' => $v['consignee'],
				'phone' => $v['phone'],
				'province' => $region[$v['province']]['name'],
				'city' => $region[$v['city']]['name'],
				'district' => $region[$v['district']]['name'],
				'province_id' => $v['province'],
				'city_id' => $v['city'],
				'district_id' => $v['district'],
				'address' => $v['address'],
				'is_default' => $v['is_default']
			);
		}
		return $data;
	}


	public function editAddress()
	{
		$province = I('province', 0, 'intval');
		$city = I('city', 0, 'intval');
		if ($city < $province)
		{
			$tmp = $city;
			$city = $province;
			$province = $tmp;
		}
		$district = I('district', 0, 'intval');
		$consignee = I('consignee', '', 'trim');
		$address = I('address', '', 'trim');
		$phone = I('mobile', '', 'trim');
		$id = I('address_id', 0, 'intval');
		$is_default = I('is_default', 0, 'intval');


		if (empty($province) || empty($city) || empty($district))
		{
			return _dat('请选择省市区县');
		}
		if (empty($consignee))
		{
			return _dat('请输入收件人');
		}
		if (empty($address))
		{
			return _dat('请输入详细地址');
		}
		if (empty($phone) || !check_phone($phone))
		{
			return _dat('请输入手机号');
		}

		if (empty($id))
		{
			$c = M('user_address')->where('user_id=' . $this->user_id)->count();
			if ($c > 10)
			{
				return _dat('你添加的收货地址已达到上限');
			}
		}
		else
		{
			$_user_id = M('user_address')->where('id=' . $id)->getField('user_id');
			if ($this->user_id != $_user_id)
			{
				return _dat('非法操作');
			}
		}

		$data = array(
			'user_id' => $this->user_id,
			'phone' => $phone,
			'consignee' => $consignee,
			'province' => $province,
			'city' => $city,
			'district' => $district,
			'address' => $address,
			'is_default' => $is_default
		);

		if ($is_default)
		{
			M('user_address')->where('user_id=' . $this->user_id)->setField('is_default', 0);
		}

		if (empty($id))
		{
			$id = M('user_address')->add($data);
		}
		else
		{
			M('user_address')->where('id=' . $id)->save($data);
		}
		return _dat('ok', 0, array('address_id' => $id));
	}

	public function deleteAddress()
	{
		$id = I('id', 0, 'intval');
		M('user_address')->where("id={$id} and user_id={$this->user_id}")->delete();
		return _dat('删除成功', 0);
	}


	/**
	 * 修改支付密码
	 * @param int $user_id 用户ID
	 * @param string $oldpwd 原密码
	 * @param string $newpwd 新密码
	 * @param string $phone_code 手机验证码
	 * @param string $type 修改类型，默认0初始没有支付密码，1有密码 进行修改
	 * @return  string
	 * 说明：可分为未设置支付密码和已设置密码密码。当code=1111时，不验证 验证码有效性。
	 * @zd
	 */
	public function editPayPassword()
	{
		if (!$this->user_id)
		{
			return "缺少主要参数";
		}

		$code = I('phone_code', '', 'trim');
		$oldpwd = I('oldpwd', '', 'trim');
		$newpwd = I('newpwd', '', 'trim');

		if (strlen($newpwd) < 6)
		{
			return array(1, "支付密码不能少于6位");
		}


		$where["id"] = $this->user_id;
		$userInfo = get_user_info($where);
		if ($userInfo['pay_password'])
		{
			if (!$oldpwd)
			{
				return array(1, "请输入支付密码");
			}
			if ($userInfo['pay_password'] && $userInfo['pay_password'] != get_pwd($oldpwd))
			{
				return array(1, "原支付密码输入错误");
			}
		}

		$code_status = get_config("code_status");
		if (!$code && $code_status)
		{
			return array(1, "请输入验证码");
		}
		if (!check_phone_code($userInfo['phone'], $code) && $code_status)
		{
			return array(1, "验证码不正确");
		}
		if ($userInfo['status'] >= 2)
		{
			return array(1, "账户冻结或审核中，请联系客服修改");
		}

		$rs = M('user')->where('id=' . $this->user_id)->setField('pay_password', get_pwd($newpwd));
		if ($rs !== false)
		{
			return array(0, "设置成功");
		}
		else
		{
			return array(1, "设置失败，请重新操作");
		}

	}


	/**
	 * 修改登录密码
	 * @param int $user_id 用户ID
	 * @param string $oldpwd 原密码
	 * @param string $newpwd 新密码
	 * @param string $confirmpwd 确认密码
	 * @return  string
	 * @zd
	 */
	public function editPassword()
	{
		if (!$this->user_id)
		{
			return "缺少主要参数";
		}

		$oldpwd = I('oldpwd', '');
		$newpwd = I('newpwd', '');
		$confirmpwd = I('confirmpwd', '');
		//当是微信登陆时是没有旧密码的
		$userId = $this->user_id;
		$userInfo = M('user')->find($userId);
		$where['id'] = $this->user_id;
		if (!empty($userInfo['password']))
		{
			if (!$oldpwd)
			{
				return "请输入旧密码";
			}
			if ($oldpwd == $newpwd)
			{
				return "新旧密码不能相同";
			}
			$oldpwd = get_pwd($oldpwd);
			$userInfo = get_user_info($where, "password,level", 1);
			if ($userInfo['password'] !== $oldpwd)
			{
				return "旧密码输入不正确";
			}
		}
		if (!$newpwd)
		{
			return "新密码不能为空";
		}
		if (strlen($newpwd) < 6)
		{
			return "密码不能低于6位字符";
		}
		if ($newpwd != $confirmpwd)
		{
			return "新密码和确认密码不相同";
		}
		$pwd = get_pwd($newpwd);
		$data['password'] = $pwd;
		$row = M('user')->where($where)->save($data);
		if ($row !== false)
		{
			return 1;
		}
		return "修改失败，请重新操作";
	}


	/**
	 * 找回登录密码
	 * @param string $phone 用户ID
	 * @param string $newpwd 新密码
	 * @param string $confirmpwd 确认密码
	 * @param string $phone_code 手机验证码
	 * @return  string
	 * @zd
	 */
	public function findPassword($data)
	{
		$phone = $data['phone'];
		$newpwd = $data['newpwd'];
		$confirmpwd = $data['confirmpwd'];
		$phone_code = $data['phone_code'];
		if (empty($phone))
		{
			return '请输入手机号';
		}
		// $code_status = get_config("code_status");

		if (empty($phone_code))
		{
			return '请输入验证码';
		}
		if (!check_phone_code($phone, $phone_code))
		{
			return '验证码不正确';
		}

		if (empty($newpwd))
		{
			return '新密码不能为空';
		}
		if (strlen($newpwd) < 6)
		{
			return '密码不能低于6位字符';
		}
		if ($newpwd != $confirmpwd)
		{
			return '两次密码不相同';
		}

		$id = get_user_field($phone, "id", 1);
		if (!$id)
		{
			return '用户不存在';
		}
		$rs = M('user')->where('id=' . $id)->setField('password', get_pwd($newpwd));
		if ($rs !== false)
		{
			return 1;
		}
		else
		{
			return "修改失败，请重新操作";
		}
	}

	/**
	 * 用户信息修改
	 * @param int $user_id 手机号
	 * @param string                其他各种修改信息
	 * @return  string
	 * 说明：不包括手机号、密码、金额的修改，只是普通信息的修改，比如昵称、性别、头像、
	 * @zd
	 */
	public function editUser()
	{
		if (!$this->user_id)
		{
			return "缺少主要参数";
		}

		$data = I();
		unset($data["user_id"]);
		if (!$data)
		{
			return "没有信息传入";
		}
		unset($data["phone"]);
		unset($data["money"]);
		unset($data["realname"]);
		unset($data["id_card"]);
		// if ($data["id_card"]) {
		// 	$rs = is_idcard($data["id_card"]);
		// 	if (!$rs) {
		// 		return "身份证号不正确";
		// 	}
		// }
		if ($data['sex'] == "男")
		{
			$data['sex'] = 1;
		}
		if ($data['sex'] == "女")
		{
			$data['sex'] = 2;
		}
		$rs = M("user")->where("id=" . $this->user_id)->save($data);
		if ($rs !== false)
		{
			return 1;
		}
		else
		{
			return "修改失败";
		}
	}

	/**
	 * 更换手机号
	 * @param int $user_id 用户ID
	 * @param string $phone 原手机号
	 * @param string $newphone 新手机号
	 * @param string $phone_code 原手机验证码
	 * @return  string
	 * @zd
	 */
	public function changePhone()
	{
		if (!$this->user_id)
		{
			return "缺少主要参数";
		}
		$phone = I("phone");
		$newphone = I("newphone");
		$phone_code = I("phone_code");

		$where_c["phone"] = $newphone;
		$count = M("user")->where($where_c)->count();
		if ($count)
		{
			return "此手机号已存在，请更换";
		}

		$where["id"] = $this->user_id;
		$userInfo = get_user_info($where);
		if ($userInfo['status'] >= 2)
		{
			return '账户冻结或审核中，请联系客服修改';
		}
		if ($userInfo['is_del'])
		{
			return '账户状态异常，请联系客服';
		}
		if ($userInfo["phone"] !== $phone)
		{
			return '原手机号与账户初始手机不同';
		}
		if ($phone == $newphone)
		{
			return '新旧手机号相同';
		}

		//如果开启手机验证就进行验证
		$where['name'] = 'code_status';
		$code = M('config')->where($where)->find();
		if ($code['val'] == 1)
		{
			$code_status = get_config("code_status");
			if ($code_status == 0)
			{
				return '验证码不正确';
			}
			if (!check_phone_code($phone, $phone_code) && $code_status)
			{

				return '验证码不正确';
			}
		}


		if (!check_phone($newphone))
		{
			return '新手机号不正确';
		}

		$rs = M("user")->where("id=" . $this->user_id)->setField("phone", $newphone);
		if ($rs !== false)
		{
			return 1;
		}
		else
		{
			return '修改失败，请重新操作';
		}
	}

	/**
	 * 删除用户
	 * @param int $user_id 用户ID
	 * @return  string
	 * @zd
	 */
	public function deleteUser($data)
	{
		if (!$data['user_id'])
		{
			return "缺少主要参数";
		}
		$rs = M("user")->where("id=" . $data['user_id'])->setField("is_del", 1);
		if ($rs !== false)
		{
			return 1;
		}
		else
		{
			return "操作失败，请重新操作";
		}
	}

	/**
	 * 实名认证
	 * @param int $user_id 用户ID
	 * @param string $id_card 身份证号
	 * @param string $bank_name 持卡人姓名
	 * @param string $phone 手机号 -- 真实名时用
	 * @param string $bank_no 银行卡号 -- 真实名时用
	 * @return  string
	 * @zd
	 */
	public function nameVerify()
	{

		if (!$this->user_id)
		{
			return "缺少主要参数";
		}
		$phone = I('phone');
		$id_card = I('id_card');
		$bank_name = I('bank_name');
		$bank_no = I('bank_no');
		$phone_code = I('phone_code');    // 验证码
		$code_status = get_config("code_status");    // 注册时验证码是否开启并验证，1是，0否
		if (!$phone_code && $code_status)
		{
			return '请填写验证码';
		}
		if (!check_phone_code($phone, $phone_code) && $code_status)
		{
			return '验证码不正确';
		}
		$where["id"] = $this->user_id;
		$userInfo = get_user_info($where);
		if (!$userInfo || $userInfo["status"] >= 2 || $userInfo["is_del"])
		{
			return "账户状态异常，请联系客服";
		}
		if ($userInfo["realname"] && $userInfo["id_card"])
		{
			return "账户已实名";
		}

		$name_verify = get_config("name_verify");    // 1=真实实名，0=自主实名
		if (!$bank_name || !$id_card || !$phone || !$bank_no)
		{
			return "实名信息请填写完整";
		}

		// 自主实名
		if (!$name_verify)
		{
			$data["realname"] = $bank_name;
			$data["id_card"] = $id_card;
			$data["bank_no"] = $bank_no;
			$data["bank_phone"] = $phone;
			$rs = M("user")->where("id=" . $this->user_id)->save($data);
			if ($rs !== false)
			{
				return 1;
			}
			else
			{
				return "实名修改失败，请重新操作";
			}
		}

		$rs = $this->certification($phone, $id_card, $bank_name, $bank_no);
		$arr = json_decode($rs, true);
		if ($arr['status'] == 0)
		{
			if ($arr['data']['respCode'] == 90)
			{
				$data['realname'] = $bank_name;
				$data['id_card'] = $id_card;
				$data['bank_no'] = $bank_no;
				$data['bank_phone'] = $phone;
				$rs2 = M('user')->where("id = {$this->user_id}")->save($data);
				if ($rs2 !== false)
				{
					return 1;
				}
				else
				{
					return "实名认证失败";
				}
			}
			return $arr['data']['respMsg'];

		}
		return $arr['msg'];


	}


	/**
	 * 真实实名认证，扣费
	 * @param string $phone 手机号 -- 真实名时用
	 * @param string $id_card 身份证号
	 * @param string $bank_name 持卡人姓名
	 * @param string $bank_no 银行卡号 -- 真实名时用
	 * @return  string
	 * @zd
	 */
	public function certification($phone, $id_card, $bank_name, $bank_no)
	{

		$url = "http://pay.ishuangniu.com/index.php/Verifybank/index/index";

		// $key = C("C_NIU_KEY");
		// $merchantid = C("C_NIU_ID");
		$config = M('config_topay')->find();
		$merchantid = $config['account'];    // 下发商户号
		$key = $config['acc_key'];            // 安全密钥

		$data = array(
			'merchantid' => $merchantid,
			'transType' => 'WS156',
			'out_orderno' => date('YmdHis') . rand(1000, 9999),
			'certifTp' => '01',
			'certifId' => $id_card,
			'customerNm' => $bank_name,
			'accNo' => $bank_no,
			'phoneNo' => $phone,
		);

		$sign = strtoupper(md5("merchantid=$merchantid&accNo=$bank_no&key=$key"));

		$data['sign'] = $sign;

		$ok = httpRequest($url, "POST", $data);
		return $ok;

	}

	/**
	 * 用户列表 ，可搜索
	 * @param string $where 搜索条件
	 * @param string $order 搜索排序
	 * @param string $num 展示数量
	 * @param string $paging 分页展示，last_id 和 page 可同时存在，last_id优先，page为次，
	 * @return  json
	 * @zd
	 */
	public function userList($where, $order = "id desc", $num = 10, $paging = array())
	{

		// 搜索条件
		if (!isset($where["is_del"]))
		{
			$where["is_del"] = 0;
		}

		$num = $num <= 0 ? 10 : $num;
		if (!$paging)
		{
			$paging['page'] = 1;
			$paging['last_id'] = 0;
		}
		// 如果 last_id 和 page 都 <=0 即表示刚开始获取数据
		// 如果 last_id 和 page 都 >0，则以 last_id 为先
		if ($paging['last_id'] > 0)
		{
			$where['id'] = array("lt", $paging['last_id']);
			$limit = $num;
		}
		else
		{
			$paging['page'] = $paging['page'] <= 1 ? 1 : $paging['page'];
			$start = ($paging['page'] - 1) * $num;
			$limit = $start . "," . $num;
		}

		$list = M("user")->where($where)->limit($limit)->order($order)->field("password,pay_password", true)->select();

		if ($list)
		{
			foreach ($list as $key => &$value)
			{
				$value['level_name'] = user_level_name($value['level']);
				$value['status_name'] = user_status_name($value['status']);
			}
			return $list;
		}
		else
		{
			return array();
		}
	}


	/**
	 * 收藏列表
	 * @param int $user_id 用户ID
	 * @param int $type 收藏类型 1商品 2商家
	 * @return  string
	 * @zd
	 */
	public function favorites()
	{
		$num = 10;
		$type = I('type', 1);
		$page = I('page', 1);
		$start = ($page - 1) * $num;
		$limit = $start . "," . $num;
		$user_id = $this->user_id;
		if (!$user_id || !$type)
		{
			return _dat("缺少主要参数");
		}
		$list = array();
		if ($type == 2)
		{
			$ids = M('favorites as f')->join('left join sns_shop as s on f.fav_id=s.id')
				->where('f.user_id=' . $user_id . ' and f.type=' . $type)->field('s.id,s.user_id,s.shop_name,s.address,s.thumb,s.industry_name')->limit($limit)->order('f.id desc')->select();
			/*			$ids = M('favorites')->where('user_id=' . $user_id . ' and type = ' . $type)->order('id DESC')->select();*/
			if ($ids)
			{
				foreach ($ids as $k => &$v)
				{
					$v['thumb'] = full_pre_url($v['thumb']);
					$list[] = $v;
				}
			}
		}
		else
		{
			$ids = M('favorites as f')->join('left join sns_goods as g on f.fav_id = g.id')
				->where('f.user_id=' . $user_id . ' and f.type=' . $type . ' and g.is_on_sale=1')->field('g.id,g.name,g.price,g.thumb,g.subhead')
				->limit($limit)->order('f.id desc')->select();
			/*			$ids = M('favorites')->where('user_id=' . $user_id . ' and type = ' . $type)->order('id DESC')->select();*/
			$list = $ids;
		}
		foreach ($list as $key => &$value)
		{
			$value["thumb"] = full_pre_url($value["thumb"], "thumb");
		}
		return _dat("收藏列表", 0, $list);
	}

	/**
	 * 删除收藏列表
	 * @param int $user_id 用户ID
	 * @param int $type 收藏类型 1商品 2商家
	 * @return  string
	 * @zd
	 */
	public function delFavorites()
	{
		$user_id = $this->user_id;
		$type = I('type');
		$source = I('source');    // 来源，1商品界面，2商家界面，3收藏列表
		$id = I('id');            // 当source=1时，ID是商品ID，当source=2时，ID是商家id，当source=3时，ID是本记录的ID
		if ($source == 3)
		{
			$where["id"] = $id;
			$where["user_id"] = $user_id;
		}
		else
		{
			$where["fav_id"] = $id;
			$where["type"] = $type;
			$where["user_id"] = $user_id;
		}

		$ids = M('favorites')->where($where)->delete();
		if ($ids)
		{
			return array('删除成功', 0, $ids);
		}
		else
		{
			return array('删除失败', 0);
		}
	}

	/**
	 * 用户提现
	 * @param  $user_id int  用户id
	 * @param  $bank_id int  用户id
	 * @param  $tx_money int  用户id
	 * @param  $type int  1消费者，2商家，3代理
	 * @return array 结果集
	 * @mgl
	 */
	function Tixian($ds)
	{
		$user_id = $ds['user_id'];    // 统一为用户ID
		if (!$user_id)
		{
			return '请登录后操作';
		}
		$type = $ds['type']; // 1消费者，2商家，3代理
		$pay_password = $ds['pay_password'];
		if (!$pay_password)
		{
			return '请输入支付密码';
		}

		if ($type == 1)
		{
			$where_u["id"] = $user_id;
			$userInfo = get_user_info($where_u, "*", 0, $type);
		}
		elseif ($type == 2)
		{
			$where_u["id"] = $user_id;
			$userInfo = get_user_info($where_u, "*", 0, 1);
		}
		elseif ($type == 3)
		{
			$where_u["user_id"] = $user_id;
			$userInfo = get_user_info($where_u, "*", 0, $type);
		}
		else
		{
			return '请核实用户类型';
		}
		if (!$userInfo)
		{
			return "提现用户异常，请联系客服";
		}

		if (!$type || !in_array($type, array(1, 2, 3)))
		{
			return '用户类型异常';
		}
		$bank_id = $ds['bank_id'];

		if (!$bank_id)
		{
			return '请选择提现账户';
		}
		$bank = M("user_bank")->where("id=" . $bank_id)->find();
		if (!$bank)
		{
			return '提现账户信息不存在';
		}

		$tx_money = abs($ds['tx_money']);

		if (floatval($tx_money) <= 0)
		{
			return "提现金额必须大于0元";
		}

		if ($type == 1)
		{
            $money_type = 1;
			$userAmt = $userInfo["money"];
			$money_field = "money";
		}
		elseif ($type == 2)
		{
            $money_type = 18;
			$userAmt = $userInfo["price"];
			$money_field = "price";
		}
		elseif ($type == 3)
		{
			$userAmt = $userInfo["agent_money"];
			$money_field = "agent_money";
		}
		if ($userAmt < $tx_money)
		{
			return '账户余额不足';
		}
		$uPWD = get_user_field($user_id, "pay_password", 0, 1);
		if (!$uPWD)
		{
			return '请先设置支付密码';
		}
		if ($uPWD != get_pwd($pay_password))
		{
			return '支付密码错误，请重新输入';
		}

		// 查看提现通用配置
		$config = M("config_tx")->where("type=" . $type)->find();
		if (!$config)
		{
			return "提现配置异常，请联系管理";
		}
		// 最小额度
		if ($tx_money < $config["tx_min"])
		{
			return "提现金额必须大于" . $config["tx_min"];
		}

		if ($config["tx_min_type"] == 2)
		{
			// 最小倍数
			$ys = fmod($tx_money, $config["tx_min_type_val"]);
			if ($ys > 0)
			{
				return "提现金额必须是" . $config["tx_min_type_val"] . "的倍数";
			}
		}
		// 最大额度
		if ($tx_money > $config["tx_max"])
		{
			return "提现金额不能大于" . $config["tx_max"];
		}

		$db = M("tx_log");

		// 提现次数，大于0时才会对次数进行判断
		if ($config["tx_num"] > 0)
		{
			if ($config["tx_num_type"] == 2)
			{
				$startTime = strtotime(date("Y-m"));    // 当月月初
				$endTime = strtotime(date("Y-m", strtotime("+1 month")));    // 当月月末
				$tx_str = "当月";
			}
			else
			{
				$startTime = strtotime(date("Y-m-d"));    // 当天初
				$endTime = $startTime + 86400;    // 当天末
				$tx_str = "当天";
			}
			$where_c["user_id"] = $user_id;
			$where_c["cre_time"] = array('between', array($startTime, $endTime));
			$txs = $db->where($where_c)->count();
			if ($txs >= $config["tx_num"])
			{
				return $tx_str . "提现次数不能大于" . $config["tx_num"];
			}
		}

		// 提现手续费处理
		if ($config["tx_fee_type"] == 2)
		{
			$tx_sxf = round($config["tx_fee"] * $tx_money / 100, 2);
		}
		else
		{
			$tx_sxf = $config["tx_fee"];
		}

		$remark = '提现金额' . $tx_money . '元,未确认';

		$model = M();
		$model->startTrans();

		$data['user_id'] = $user_id;
		$data['user_money'] = $userAmt;
		$data['tx_money'] = $tx_money;
		$data['sxf_money'] = $tx_sxf; // 扣除手续费
		$data['sjzz_money'] = round($tx_money - $tx_sxf, 2);//实际到账
		$data['remark'] = $remark;
		$data['utype'] = 1;
		$data['amt_type'] = $money_type;    // 扣除账户，1余额账户，2商家收益，3代理收益，
		$data['ischeck'] = 0;//审核状态，0未审核，1提现通过，2提现拒绝

		$data['bank_type'] = $bank['type'];
		$data['bank_name'] = $bank['bank_name'];
		$data['cart_no'] = $bank['cart_no'];
		$data['name'] = $bank['name'];
		$data['bank_address'] = $bank['bank_address'];

		$data['cre_time'] = time();
		$data['cre_date'] = date("Y-m-d H:i:s");
		$data['ordernum'] = make_order_no();
		$data['money_field'] = $money_field;

		$rs2 = change_money_log($user_id, 1, $money_field, 0, $tx_money, $money_type, 5, $remark, 2, 1, 1);

		$data['log_id'] = $rs2;
		$res_tx = M('tx_log')->add($data);

		if ($res_tx && $rs2)
		{
			//将order_id绑定为提现id，修改收资记录使用
			M('account_log')->where('id=' . $rs2)->setField('order_id', $res_tx);
			$model->commit();
			return array('提现申请成功，请等待处理', 0, $res_tx);
		}
		else
		{
			$model->rollback();
			return '提现失败，请重新操作';
		}
	}

	/**
	 *
	 * 提现审核
	 * @param  $tx_id int  提现表id
	 * @param  $shtype int  审核类型1审核通过2拒绝
	 * @return array 结果集
	 * @mgl
	 */
	function tx_shenhe($data)
	{

		$tx_id = $data['tx_id'];//提现表id

		if (!$tx_id)
		{
			return '参数错误,请输入提现id';
		}
		$shtype = $data['shtype'];//审核类型

		if (!$shtype || !in_array($shtype, [1, 2]))
		{
			return '参数错误,审核类型必须是1审核通过2拒绝';
		}


		$tx = M('tx_log')->where('id=' . $tx_id)->find();
		if (!$tx)
		{
			return '提现记录不存在';
		}

		if ($tx['txtype'] == 1)
		{

			return '错误,该记录已打款,请勿重复操作';
		}

		M()->startTrans();

		$res = M('tx_log')->where('id=' . $tx_id)->setField('shtype', $shtype);
		if ($res)
		{
			M()->commit();
			return array('审核成功,请在手动打款后设置提现状态', 0);
		}
		else
		{
			M()->rollback();
			return '审核出错,请稍候重试';
		}

	}

	/**
	 *
	 * 提现打款状态更改
	 * @param  $tx_id int  提现表id
	 * @return json 结果集
	 * @mgl
	 */
	public function tx_status($data)
	{

		$tx_id = $data['tx_id'];//提现表id

		if (!$tx_id)
		{
			return '参数错误,请输入提现id';
		}

		$tx = M('tx_log')->where('id=' . $tx_id)->find();

		if (!$tx)
		{
			return '提现记录不存在';
		}
		if ($tx['shtype'] != 1)
		{

			return '错误,未审核或审核不通过,不能更改提现状态';
		}
		if ($tx['txtype'] == 1)
		{
			return '错误,该记录已打款,请勿重复操作';
		}

		M()->startTrans();

		$res = M('tx_log')->where('id=' . $tx_id)->setField('txtype', 1);
		if ($res)
		{
			M()->commit();
			return array('状态变更成功', 0);
		}
		else
		{
			M()->rollback();
			return '操作失败,请稍候重试';
		}
	}

	/**
	 * 获取用户数量 / 分页
	 * @param array $where 条件
	 * @param int $num 分页数量
	 * @return  array            总页数和总数
	 * @zd
	 */
	public function getNums($where, $num)
	{
		$num = $num >= 1 ? $num : 10;
		$total = M("user")->where($where)->count();
		$total = $total ? $total : 0;
		$max_page = ceil($total / $num);
		$data['total'] = $total;
		$data['max_page'] = $max_page;
		return $data;
	}

	/**
	 * 收支记录列表 ，可搜索
	 * @param string $where 搜索条件
	 * @param string $order 搜索排序
	 * @param string $num 展示数量
	 * @param string $paging 分页展示，last_id 和 page 可同时存在，last_id优先，page为次，
	 * @return  json
	 * @zd
	 */
	public function accountLog($where, $order = "id desc", $num = 10, $paging = array())
	{

		$num = $num <= 0 ? 10 : $num;
		if (!$paging)
		{
			$paging['page'] = 1;
			$paging['last_id'] = 0;
		}
		// 如果 last_id 和 page 都 <=0 即表示刚开始获取数据
		// 如果 last_id 和 page 都 >0，则以 last_id 为先
		if ($paging['last_id'] > 0)
		{
			$where['id'] = array("lt", $paging['last_id']);
			$limit = $num;
		}
		else
		{

			if ($paging["page"] <= 1)
			{
				$paging['page'] = 1;
			}
			/*$paging['page'] = $paging['page'] <=1 ? 1 : $paging['page'];*/

			$start = ($paging['page'] - 1) * $num;
			$limit = $start . "," . $num;
		}


		$list = M("account_log")->where($where)->limit($limit)->order($order)->select();

		if ($list)
		{
			foreach ($list as $key => &$value)
			{
				if ($value['op_type'] == 1)
				{
					$value["amount"] = "+" . $value["amount"];
				}
				elseif ($value['op_type'] == 2)
				{
					$value["amount"] = "-" . $value["amount"];
				}
				$value['user_phone'] = get_user_field($value['user_id'], "phone", 0);
				if ($value["utype"] == 2)
				{
					$value['shop_name'] = get_user_field($value['user_id'], "shop_name", 0, 2);
				}
				else
				{
					$value['shop_name'] = "";
				}
			}

			return $list;
		}
		else
		{
			return array();
		}
	}

	/**
	 * 获取用户收支明细的总记录数和分页数量
	 * @param array $where 条件
	 * @param int $num 分页数量
	 * @return  array            总页数和总数
	 * @zd
	 */
	public function getAccountLogNums($where, $num)
	{
		$num = $num >= 1 ? $num : 10;
		$total = M("account_log")->where($where)->count();
		$total = $total ? $total : 0;
		$max_page = ceil($total / $num);
		$data['total'] = $total;
		$data['max_page'] = $max_page;
		return $data;
	}

	/**
	 * @param $where
	 * @param $num
	 * @return mixed
	 * Notes:获取会员浏览记录总数和分页数量
	 * User: WangSong
	 * Date: 2020/7/11
	 * Time: 17:48
	 */
	public function getFootprintNums($where, $num)
	{
		$num = $num >= 1 ? $num : 10;
		$total = M("goods as g")->join('left join sns_goods_footprint as f on f.goods_id=g.id')->where($where)->group('g.id')->field('g.*')->select();
		$total = count($total) ? (count($total)) : 0;
		$max_page = ceil($total / $num);
		$data['total'] = $total;
		$data['max_page'] = $max_page;
		return $data;
	}

	/**
	 * 添加收藏（商品或商家）
	 * @param int $user_id 分页数量
	 * @param int $fav_id 商品ID或商家ID
	 * @param int $type 1商品 2商家
	 * @return  array                成功或失败
	 * @zd
	 */
	public function addFavorites($data)
	{
		if (!$data["user_id"])
		{
			return array("请登录后操作", 1);
		}

		if (!$data["fav_id"] || !$data["type"])
		{
			return array("缺少主要参数", 1);
		}
		$where["user_id"] = $data["user_id"];
		$where["fav_id"] = $data["fav_id"];
		$where["type"] = $data["type"];
		$rs = M("favorites")->where($where)->find();
		if ($rs)
		{
			return array("收藏成功", 0);
		}
		$where["add_time"] = time();
		$rs = M("favorites")->add($where);
		if ($rs)
		{
			return array("收藏成功", 0);
		}
		else
		{
			return array("收藏失败", 1);
		}


	}

	/**
	 * 商品浏览列表
	 * @param string $num 展示数量
	 * @param string $paging 分页展示，last_id 和 page 可同时存在，last_id优先，page为次，
	 * @return  json
	 */
	public function footprint($where, $order = "id desc", $num = 10, $paging = array())
	{
		$num = $num <= 0 ? 10 : $num;
		if (!$paging)
		{
			$paging['page'] = 1;
		}
		// 如果 last_id 和 page 都 <=0 即表示刚开始获取数据
		// 如果 last_id 和 page 都 >0，则以 last_id 为先

		$paging['page'] = $paging['page'] <= 1 ? 1 : $paging['page'];
		$start = ($paging['page'] - 1) * $num;
		$limit = $start . "," . $num;
		$list = M('goods_footprint as f')->join('left join sns_goods as g on f.goods_id=g.id')
			->where($where)->limit($limit)->field('f.*,g.name as goods_name,g.thumb,g.price')->order($order)->group('f.goods_id')->select();
		return $list;
	}

	/*
     * 判断手机验证码是否正确
     * */
	public function judge_phone_code($data)
	{
		$phone = $data['phone'];
		$phone_code = $data['phone_code'];
		if (!$phone)
		{
			return '手机号不正确';
		}
		$code_status = get_config("code_status");    // 注册时验证码是否开启并验证，1是，0否

		if (!$phone_code && $code_status)
		{
			return '验证码不正确';
		}
		if (!check_phone_code($phone, $phone_code) && $code_status)
		{
			return '验证码不正确';
		}
		return true;
	}

	/**
	 * @param $where
	 * @param $num
	 * @param $type
	 * @return mixed
	 * Notes:获取收藏数量
	 * User: WangSong
	 * Date: 2020/7/13
	 * Time: 9:25
	 */
	public function getFavorites($where, $num, $type)
	{
		$num = $num >= 1 ? $num : 10;
		if ($type == 1)
		{
			$total = M("favorites as f")->join('left join sns_goods as g on f.fav_id=g.id')->where($where)->count();
		}
		else
		{
			$total = M("favorites as f")->join('left join sns_shop as s on f.fav_id=s.id')->where($where)->count();
		}
		$total = $total ? $total : 0;
		$max_page = ceil($total / $num);
		$data['total'] = $total;
		$data['max_page'] = $max_page;
		return $data;
	}

    /**
     * @param $data
     * @return bool|int|string
     * Notes:会员签到
     * User: WangSong
     * Date: 2020/9/29
     * Time: 8:56
     */
    public function user_sign($data){
        $sign_config = M('config_signin')->find();

        if($sign_config['is_open'] == 0){
            return false;
        }
	    if(!$data){
            $data = I();
        }
	    if(!$data['user_id']){
	        return  '缺少user_id参数';
        }
        $user_id = $data['user_id'];
	    $where['user_id'] = $data['user_id'];
	    $where['sign_date'] = date('Y-m-d',time());
	    $sign_log = M('sign_log')->where($where)->find();
	    if($sign_log){
	        return '今日已签到，请明日再来';
        }
	    $last_sign_log = M('sign_log')->where('user_id='.$data['user_id'])->order('id desc')->find();
	    if($sign_config['is_cycle'] == 0){
            $count = 0;
        }else{
	        if($last_sign_log['count'] >= $sign_config['one_cycle_days']){
                $count = 1;
            }else{
	            $count = $last_sign_log['count'] + 1;
            }
        }
	    //开始计算当天的签到积分
        $score = $sign_config['sign_award'];
        if($sign_config['is_cycle'] == 0){
            $score = $sign_config['sign_award'];
            if($sign_config['first_sign_award'] != 0){
                $sign_log = M('sign_log')->where('user_id='.$user_id)->count();
                if($sign_log == 0){
                    $score = $sign_config['first_sign_award'];
                }else{
                    if((time() - $last_sign_log['cre_time'])/86400 > $sign_config['cycle_days']){

                        $score = $sign_config['first_sign_award'];

                    }
                }
            }
        }else{
            $special_award = $sign_config['special_award'];
            if($special_award){
                $arr = explode(',',$special_award);
                for ($i=0;$i<count($arr);$i++){
                    $arr1 = explode('=',$special_award);
                    if($count == $arr1[0]){
                        $score = $arr1[1];
                    }
                }
            }
        }
        //开始添加签到日志
	    $time = time();
        $da['is_cycle'] = $sign_config['is_cycle'];
	    $da['user_id'] = $data['user_id'];
	    /*if($last_sign_log){
            $da['sign_date'] = date('Y-m-d',strtotime('+1 day',strtotime($last_sign_log['sign_date'])));
        }else{
            $da['sign_date'] = date('Y-m-d',$time);
        }*/
        $da['sign_date'] = date('Y-m-d',$time);

	    $da['sign_time'] = $time;
	    $da['cre_time'] = $time;
	    $da['count'] = $count;
	    $da['score'] = $score;
        $re = M('sign_log')->add($da);
        if($re){
            if($score > 0){
                change_money_log($user_id,1,'score',0,$score,16,9,'会员签到',1,1,1);
            }
            return 1;
        }else{
            return '签到失败';
        }
    }

    /**
     * @param $data
     * @return array
     * Notes:会员敲到列表
     * User: WangSong
     * Date: 2020/9/29
     * Time: 8:56
     */
    /*public function get_user_sign_log($data){
	    $time = time();
        if(!$data){
            $data = I();
        }
        $sign_config = M('config_signin')->find();
        $special_award = $sign_config['special_award'];
        $arr = array();
        if($sign_config['is_cycle'] == 0){
            $user_sign_log = M('sign_log')->where('user_id='.$data['user_id'])->order('id desc')->limit(3)->select();
            $id = array();
            foreach ($user_sign_log as $k=>$v){
                $id[$k] = $v['id'];
            }
            array_multisort($user_sign_log, SORT_ASC, $id);
            $a = -1;
            foreach ($user_sign_log as $k=>$v){
                if($v['is_cycle'] == 1){
                    $a = $k;
                    break;
                }
            }
            if($a != -1){
                $user_sign_log = array_slice($user_sign_log,$a,(count($user_sign_log) - 1));
            }
            for ($i=0;$i<7;$i++){
                if($i<count($user_sign_log)){
                    $arr[$i]['is_sign'] = 1;
                    if(date('Y-m-01', strtotime($user_sign_log[$i]['sign_date'])) == date('Y-m-d',strtotime($user_sign_log[$i]['sign_date']))){
                        $arr[$i]['sign_date'] = date('m',strtotime($user_sign_log[$i]['sign_date'])).'.'.date('j',strtotime($user_sign_log[$i]['sign_date']));
                    }else{
                        $arr[$i]['sign_date'] = date('j',strtotime($user_sign_log[$i]['sign_date'])).'号';
                    }
                    $date1 = date('Y-m-d',strtotime($user_sign_log[$i]['sign_date']));
                    $arr[$i]['sign_award'] = '+'.$user_sign_log[$i]['score'];
                }else{
                    if(count($user_sign_log) == 0 ){
                        $count = count($user_sign_log);
                    }else{
                        $count = count($user_sign_log) - 1;
                    }
                    $arr[$i]['is_sign'] = 0;
                    if(date('Y-m-01', ($time+($i-$count)*86400)) == date('Y-m-d',($time+($i-$count)*86400))){
                        $arr[$i]['sign_date'] = date('m',($time+($i-$count)*86400)).'.'.date("j",($time+($i-$count)*86400));
                    }else{
                        $arr[$i]['sign_date'] = date("j",($time+($i-$count)*86400)).'号';
                    }
                    $date1 = date('Y-m-d',($time+($i-$count)*86400));
                    $arr[$i]['sign_award'] = '+'.$sign_config['sign_award'];
                    if($i==0){
                        $arr[$i]['sign_award'] = '+'.$sign_config['first_sign_award'];
                    }
                }
                if($date1 == date('Y-m-d',time())){
                    $arr[$i]['is_check'] = 1;
                }else{
                    $arr[$i]['is_check'] = 0;
                }
                $arr[$i]['is_special'] = 0;
            }

        }else{
            $last_user_sign_log = M('sign_log')->where('user_id='.$data['user_id'])->order('id desc')->find();
            if($last_user_sign_log['is_cycle'] == 0){
                $last_user_sign_log['count'] = 1;
            }
            $page = ceil($sign_config['one_cycle_days']/7);//周期天数
            $page1 = ceil($last_user_sign_log['count']/7);//签到的天数
            if($page1 > 1){
                if(is_int($last_user_sign_log['count']/7)){
                    $day = $last_user_sign_log['count']/7;
                    for ($i=0;$i < $sign_config['one_cycle_days'];$i++){
                        if($i==7){
                            break;
                        }
                        if($i==0){
                            $arr[$i]['is_check'] = 1;
                        }else{
                            $arr[$i]['is_check'] = 0;
                        }
                        $arr[$i]['is_sign'] = 0;
                        $arr[$i]['sign_date'] = ($day+$i+1).'天';
                        $arr[$i]['sign_award'] = '+'.$sign_config['sign_award'];
                        $arr[$i]['is_special'] = 0;
                        if($special_award){
                            $arr2 = explode(',',$special_award);
                            for ($i1=0;$i1<count($arr2);$i1++){
                                $arr1 = explode('=',$special_award);
                                if($day+$i+1 == $arr1[0]){
                                    $arr[$i]['sign_award'] =  $arr1[1];
                                    $arr[$i]['is_special'] = 1;
                                }
                            }
                        }
                    }
                }else{
                    $num = $last_user_sign_log['count'] - ($page1-1)*7;
                    $user_sign_log = M('sign_log')->where('user_id='.$data['user_id'])->order('id desc')->limit($num)->select();
                    for ($i=0;$i < $sign_config['one_cycle_days'];$i++){
                        if($i==7){
                            break;
                        }
                        if($i<count($user_sign_log)){
                            $arr[$i]['is_sign'] = 1;
                            $arr[$i]['sign_award'] = $user_sign_log[$i]['score'];
                        }else{
                            $arr[$i]['is_sign'] = 0;
                            $arr[$i]['sign_award'] = $sign_config['sign_award'];
                        }
                        if($i == count($user_sign_log)){
                            $arr[$i]['is_check'] = 1;
                        }else{
                            $arr[$i]['is_check'] = 0;
                        }
                        $arr[$i]['is_special'] = 0;
                        $arr[$i]['sign_date'] = ($last_user_sign_log['count']+$i+1).'天';
                        if($special_award){
                            $arr2 = explode(',',$special_award);
                            for ($i1=0;$i1<count($arr2);$i1++){
                                $arr1 = explode('=',$special_award);
                                if($last_user_sign_log['count']+$i+1 == $arr1[0]){
                                    $arr[$i]['sign_award'] =  '+'.$arr1[1];
                                    $arr[$i]['is_special'] = 1;
                                }
                            }
                        }
                    }
                }

            }else{
                $user_sign_log = M('sign_log')->where('user_id='.$data['user_id'])->order('id desc')->limit(7)->select();
                $id = array();
                foreach ($user_sign_log as $k=>$v){
                    $id[$k] = $v['id'];
                }
                array_multisort($user_sign_log, SORT_ASC, $id);
                $a = -1;
                foreach ($user_sign_log as $k=>$v){
                    if($v['is_cycle'] == 0){
                        $a = $k;
                        break;
                    }
                }
                if($a != -1){
                    $user_sign_log = array_slice($user_sign_log,$a,$a);
                }
                for ($i=0;$i<$sign_config['one_cycle_days'];$i++){
                    if($i == 7){
                        break;
                    }
                    if($i < count($user_sign_log)){
                        $arr[$i]['is_sign'] = 1;
                    }else{
                        $arr[$i]['is_sign'] = 0;
                    }
                    if($i == count($user_sign_log)){
                        $arr[$i]['is_check'] = 1;
                    }else{
                        $arr[$i]['is_check'] = 0;
                    }
                    $arr[$i]['is_special'] = 0;
                    $arr[$i]['sign_date'] = ($i+1).'天';
                    $arr[$i]['sign_award'] = '+'.$sign_config['sign_award'];
                    if($special_award){
                        $arr2 = explode(',',$special_award);
                        for ($i1=0;$i1<count($arr2);$i1++){
                            $arr1 = explode('=',$special_award);
                            if($i+1 == $arr1[0]){
                                $arr[$i]['sign_award'] =  '+'.$arr1[1];
                                $arr[$i]['is_special'] = 1;
                            }
                        }
                    }
                }
            }
        }
        $time1 = date('Y-m-d',time());
        $where['user_id'] = array('eq',$data['user_id']);
        $where['sign_date'] = array('eq',$time1);
        $user_sign_log1 = M('sign_log')->where($where)->find();
        if($user_sign_log1){
            $da['is_check'] = 1;
        }else{
            $da['is_check'] = 0;
        }
        $user = M('user')->where('id='.$data['user_id'])->find();
        $da['list'] = $arr;
        $da['user_score'] = $user['score'];
        $da['rules'] = htmlspecialchars_decode($sign_config['sign_rules']);
        return $da;
    }*/
    public function get_user_sign_log($data){
        $time = time();
        if(!$data){
            $data = I();
        }
        $sign_config = M('config_signin')->find();
        $special_award = $sign_config['special_award'];
        $arr = array();
        $last_user_sign_log = M('sign_log')->where('user_id='.$data['user_id'])->order('id desc')->find();
        if($sign_config['is_cycle'] == 0){
            $user_sign_log = M('sign_log')->where('user_id='.$data['user_id'])->order('id desc')->limit(3)->select();
            $id = array();
            foreach ($user_sign_log as $k=>$v){
                $id[$k] = $v['id'];
            }
            array_multisort($user_sign_log, SORT_ASC, $id);
            $a = -1;
            foreach ($user_sign_log as $k=>$v){
                if($v['is_cycle'] == 1){
                    $a = $k;
                    break;
                }
            }
            if($a != -1){
                $user_sign_log = array_slice($user_sign_log,$a,$a);
            }
            for ($i=0;$i<7;$i++){
                if($i<count($user_sign_log)){
                    $arr[$i]['is_sign'] = 1;
                    if(date('Y-m-01', strtotime($user_sign_log[$i]['sign_date'])) == date('Y-m-d',strtotime($user_sign_log[$i]['sign_date']))){
                        $arr[$i]['sign_date'] = date('m',strtotime($user_sign_log[$i]['sign_date'])).'.'.date('j',strtotime($user_sign_log[$i]['sign_date']));
                    }else{
                        $arr[$i]['sign_date'] = date('j',strtotime($user_sign_log[$i]['sign_date'])).'号';
                    }
                    $date1 = date('Y-m-d',strtotime($user_sign_log[$i]['sign_date']));
                    $arr[$i]['sign_award'] = '+'.$user_sign_log[$i]['score'];
                    if((time() - $last_user_sign_log['cre_time'])/86400 > $sign_config['cycle_days']){
                        if($i == 0){
                            $arr[$i]['sign_award'] = '+'.$sign_config['first_sign_award'];
                        }
                    }
                }else{
                    if(count($user_sign_log) == 0 ){
                        $count = count($user_sign_log);
                    }else{
                        $count = count($user_sign_log) - 1;
                    }
                    $arr[$i]['is_sign'] = 0;
                    if(date('Y-m-01', ($time+($i-$count)*86400)) == date('Y-m-d',($time+($i-$count)*86400))){
                        $arr[$i]['sign_date'] = date('m',($time+($i-$count)*86400)).'.'.date("j",($time+($i-$count)*86400));
                    }else{
                        $arr[$i]['sign_date'] = date("j",($time+($i-$count)*86400)).'号';
                    }
                    $date1 = date('Y-m-d',($time+($i-$count)*86400));
                    $arr[$i]['sign_award'] = '+'.$sign_config['sign_award'];
                    if($i==0){
                        $arr[$i]['sign_award'] = '+'.$sign_config['first_sign_award'];
                    }
                }
                if($date1 == date('Y-m-d',time())){
                    $arr[$i]['is_check'] = 1;
                }else{
                    $arr[$i]['is_check'] = 0;
                }
                $arr[$i]['is_special'] = 0;
            }

        }else{

            $num = $sign_config['one_cycle_days'] - $last_user_sign_log['count'];
            if($num > 0){
                if($num >10){
                    if($last_user_sign_log['count'] < 3){
                        $num = $last_user_sign_log['count'];
                    }else{
                        $num = 3;
                    }

                }else{
                    if($num < 3){
                        $num = 7- $num;
                    }else{
                        $num =3;
                    }
                }
            }

            $user_sign_log = M('sign_log')->where('user_id='.$data['user_id'])->order('id desc')->limit($num)->select();
            $id = array();
            foreach ($user_sign_log as $k=>$v){
                $id[$k] = $v['id'];
            }
            array_multisort($user_sign_log, SORT_ASC, $id);
            $a = -1;

            foreach ($user_sign_log as $k=>$v){
                if($v['is_cycle'] == 0){
                    $a = $k;
                    break;
                }
            }
            if($a != -1){
                $user_sign_log = array_slice($user_sign_log,$a,$a);
            }
            $a1 = 1;
            for ($i=0;$i<7;$i++){
                $arr[$i]['is_check'] = 0;
                if(count($user_sign_log) == 0){
                    $arr[$i]['is_check'] = 1;
                }else{
                    if($i == count($user_sign_log)+1){
                        $arr[$i]['is_check'] = 1;
                    }
                }
                if($special_award){
                    $arr2 = explode(',',$special_award);
                    for ($i1=0;$i1<count($arr2);$i1++){
                        $arr1 = explode('=',$special_award);
                        if($i+1 == $arr1[0]){
                            $arr[$i]['sign_award'] =  '+'.$arr1[1];
                            $arr[$i]['is_special'] = 1;
                        }
                    }
                }
                $arr[$i]['sign_award'] = '+'.$sign_config['sign_award'];
                if($i==0){
                    $arr[$i]['sign_award'] = '+'.$sign_config['first_sign_award'];
                }
                if($i<count($user_sign_log)){
                    $arr[$i]['is_sign'] = 1;
                    $arr[$i]['sign_date'] = $user_sign_log[$i]['count'].'天';
                    $arr[$i]['sign_award'] = '+'.$user_sign_log[$i]['score'];
                }else{
                    $arr[$i]['is_sign'] = 0;
                    if(count($user_sign_log) == 0 ){
                        $arr[$i]['sign_date'] = ($i+1).'天';
                    }else{

                        $arr[$i]['sign_date'] = ($last_user_sign_log['count']+$a1).'天';
                        $a1++;
                        if(($last_user_sign_log['count']+$a1) > $sign_config['one_cycle_days']){
                            break;
                        }
                    }

                }
                $arr[$i]['is_special'] = 0;
            }

        }
        $time1 = date('Y-m-d',time());
        $where['user_id'] = array('eq',$data['user_id']);
        $where['sign_date'] = array('eq',$time1);
        $user_sign_log1 = M('sign_log')->where($where)->find();
        if($user_sign_log1){
            $da['is_check'] = 1;
        }else{
            $da['is_check'] = 0;
        }
        $user = M('user')->where('id='.$data['user_id'])->find();
        $da['list'] = $arr;
        $da['user_score'] = $user['score'];
        $da['rules'] = htmlspecialchars_decode($sign_config['sign_rules']);
        return $da;
    }

}
