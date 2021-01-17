<?php

namespace Admin\Controller;

use Clas\User;
use Think\Image;
use Think\Page;

class UserController extends CommonController
{

	/**
	 * 会员列表
	 * 说明：直接引用
	 * @zd
	 */
	public function index()
	{
		$getData = I();
		if ($getData['user_id'])
		{
			$where['id'] = $getData['user_id'];
		}
		if ($getData['user_name'])
		{
			$where['username|nickname'] = array('like', '%' . $getData['user_name'] . '%');
		}
		if ($getData['phone'])
		{
			$where['phone'] = array('like', '%' . $getData['phone'] . '%');
		}
        if ($getData['level']!='' && $getData['level']>=0)
		{
			$where['level'] = $getData['level'];
		}
		if ($getData['status'])
		{
			$where['status'] = $getData['status'];
		}
		if ($getData['cre_time'])
		{
			$getCreTime = get_time_style($getData['cre_time']);
			$where['cre_time'] = array(array("gt", $getCreTime[0]), array("elt", $getCreTime[1]));
		}
		$num = 10;
		$where['is_del'] = 0;
		$count = M("user")->where($where)->count();
		$page = new Page($count, $num);
		$show = $page->show();
		$list = M("user")->where($where)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
		$user_level = M('config_user_level')->where('is_del=0')->field('id,level_name,level_ranking')->select();
		$this->assign('level', $user_level);
		$this->assign("list", $list);
		$this->assign("show", $show);
		$this->display();
	}


	/**
	 * 会员删除
	 * @param int $id 用户ID
	 * @return  string            结果
	 * @zd
	 */
	public function delete()
	{
		$user_id = I("id", 0);
		if (!$user_id)
		{
			$this->error("参数异常，请核实");
		}
		$user = new User();
		$data['user_id'] = $user_id;
		$rs = $user->deleteUser($data);
		if (is_numeric($rs))
		{
			$this->success("删除成功");
		}
		else
		{
			$this->error("删除失败，请重新删除");
		}
		exit();
	}


	/**
	 * 会员详情
	 * @param int $id 用户ID
	 * @return  string            结果
	 * @zd
	 */
	public function detail()
	{
		$user_id = I("id", 0);
		if (!$user_id)
		{
			$this->error("参数异常，请核实");
		}
		$where['id'] = $user_id;
		$data = get_user_info($where);
		$level = M('config_user_level')->where('is_del=0')->field('level_ranking,level_name')->select();
		$this->assign('level', $level);
		$this->assign("data", $data);
		$this->display();


	}

	/**
	 * 会员修改
	 * @param int $id 用户ID
	 * @param string    xxx    各种信息
	 * @return  string            结果
	 * @zd
	 */
	public function edit()
	{
		$data = I();

		$user_id = $data["user_id"];
		unset($data["user_id"]);
		if ($data["password"])
		{
			$data["password"] = get_pwd($data["password"]);
		}
		else
		{
			unset($data['password']);
		}
		if ($data["pay_password"])
		{
			$data["pay_password"] = get_pwd($data["pay_password"]);
		}
		else
		{
			unset($data['pay_password']);
		}
		if (!$data["headimgurl"])
		{
			unset($data["headimgurl"]);
		}
		$rs = M("user")->where("id=" . $user_id)->save($data);
		if ($rs !== false)
		{
			$this->success("修改成功", U("index"));
		}
		else
		{
			$this->error("修改失败");
		}

	}


	/**
	 * 金额操作
	 * @param int $id 用户ID
	 * @param string    xxx    各种信息
	 * @return  string            结果
	 * @zd
	 */
	public function amount()
	{
		$data = I();
		if (!$data["id"])
		{
			$this->error("账户异常");
		}

		if (!$data["utype"])
		{
			$data["utype"] = 1;    // 1用户，2商家，3代理
		}
		if ($data['remark'])
		{
			$remark = $data['remark'];
		}
		else
		{
			$remark = '系统设置';
		}
		if (IS_POST)
		{
			if (!$data["account"])
			{
				$this->error("请选择账户");
			}
			switch ($data["account"])
			{
				case 1:
					$field = "money";
					break;
				case 6:
					$field = "money_wait";
					break;
				case 16:
					$field = "score";
					break;

				case 7:
					$field = "money";
					break;
				case 8:
					$field = "money_wait";
					break;
				case 2:
					$field = "shop_money";
					break;

				case 10:
					$field = "money";
					break;
				case 11:
					$field = "money_wait";
					break;
				case 3:
					$field = "agent_money";
					break;
                case 18:
                    $field = "price";
                    break;
				default:
					# code...
					break;
			}
			if ($data["type"] != 1 && $data["type"] != 2)
			{
				$this->error("请选择操作方式");
			}
			$num = round($data["num"], 6);
			if ($num <= 0)
			{
				$this->error("额度必须大于0");
			}

			$where["id"] = $data["id"];
			$userInfo = get_user_info($where, "*", 0, $data["utype"]);
			if (!$userInfo)
			{
				$this->error("没有此账户");
			}
			if ($data['utype'] == 1)
			{
				$user_id = $userInfo['id'];
			}
			else
			{
				$user_id = $userInfo['user_id'];
			}
			$rs = change_money_log($user_id, 1, $field, 0, $num, $data["account"], 7, $remark, $data["type"], $data["utype"], 1);
			if ($rs)
			{
				$this->success("操作成功");
			}
			else
			{
				$this->error("操作失败，请重新操作");
			}
			exit();
		}
		$this->assign("id", $data["id"]);
		$this->assign("utype", $data["utype"]);
		$this->display();
	}

	/**
	 * 金额记录
	 * @param int $id 用户ID
	 * @return  string            结果
	 * @zd
	 */
	public function account()
	{
		$id = I("id", 0);
		$utype = I("utype", 1);    // 1用户，2商家，3代理
		$type = I("type", 0);
		$deal_type = I("deal_type", 0);
		$op_type = I("op_type", 0);
		$startTime = I("startTime", '');
		$endTime = I("endTime", '');

		if ($startTime && $endTime)
		{
			$where['cre_time'] = array("between", array(strtotime($startTime), strtotime($endTime)));
		}
		if ($startTime && !$endTime)
		{
			$where['cre_time'] = array("egt", strtotime($startTime));
		}
		if (!$startTime && $endTime)
		{
			$where['cre_time'] = array("elt", strtotime($endTime));
		}

		$where_u['id'] = $id;
		$info = get_user_info($where_u, '', '', $utype);
		if ($info)
		{
			if ($utype == 1)
			{
				$user_id = $info['id'];
			}
			else
			{
				$user_id = $info['user_id'];
			}
		}
		else
		{
			$user_id = 0;
		}

		if ($user_id)
		{
			$where['user_id'] = $user_id;
		}
		if ($type)
		{
			$where['type'] = $type;
		}
		if ($deal_type)
		{
			$where['deal_type'] = $deal_type;
		}
		if ($utype)
		{
			$where['utype'] = $utype;
		}
		if ($op_type)
		{
			$where['op_type'] = $op_type;
		}

		$num = 10;
		$count = M("account_log")->where($where)->count();
		$page = new Page($count, $num);
		$show = $page->show();
		$list = M("account_log")->where($where)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->assign("list", $list);
		$this->assign("show", $show);
		$this->assign("utype", $utype);
		$this->assign("id", $id);
		$this->display();
	}

	//等级设置
	public function level_list()
	{
		$count = $user_level = M('config_user_level')->where('is_del!=1')->count();
		$page = new  Page($count, 10);
		$show = $page->show();
		$user_level = M('config_user_level')->where('is_del!=1')->
		field('id,level_name,level_icon,level_ranking,up_level_by_money,up_level_by_auto,down_level_by_auto,recommend_status,buy_award_status,repeat_buy_award_status,status')
			->limit($page->firstRow . ',' . $page->listRows)->order('level_ranking')->select();

		$this->assign('user_level', $user_level);
		$this->assign('show', $show);
		$this->display();
	}

	//等级配置修改
	public function commission()
	{
		if ($_POST)
		{
			// 获取订单分类、商品
			$up_auto_order_type = I("up_auto_order_type");
			$up_auto_goods = I("up_auto_goods");
			$down_auto_order_type = I("down_auto_order_type");
			$down_auto_goods = I("down_auto_goods");

			foreach ($up_auto_order_type as $key => &$value)
			{
				if ($value == 999999999)
				{
					unset($up_auto_order_type[$key]);
				}
			}
			if ($up_auto_order_type)
			{
				$up_auto_order_type = array_unique($up_auto_order_type);
				$up_auto_order_type = implode(",", $up_auto_order_type);
			}
			else
			{
				$up_auto_order_type = "";
			}

			foreach ($up_auto_goods as $key2 => &$value2)
			{
				if ($value2 == 0)
				{
					unset($up_auto_goods[$key2]);
				}
			}
			if ($up_auto_goods)
			{
				$up_auto_goods = array_unique($up_auto_goods);
				$up_auto_goods = implode(",", $up_auto_goods);
			}
			else
			{
				$up_auto_goods = "";
			}

			foreach ($down_auto_order_type as $key3 => &$value3)
			{
				if ($value3 == 999999999)
				{
					unset($down_auto_order_type[$key3]);
				}
			}
			if ($down_auto_order_type)
			{
				$down_auto_order_type = array_unique($down_auto_order_type);
				$down_auto_order_type = implode(",", $down_auto_order_type);
			}
			else
			{
				$down_auto_order_type = "";
			}

			foreach ($down_auto_goods as $key4 => &$value4)
			{
				if ($value4 == 0)
				{
					unset($down_auto_goods[$key4]);
				}
			}
			if ($down_auto_goods)
			{
				$down_auto_goods = array_unique($down_auto_goods);
				$down_auto_goods = implode(",", $down_auto_goods);
			}
			else
			{
				$down_auto_goods = "";
			}

			//获取等级名称
			$level_name = I('level_name');
			$data['level_name'] = $level_name;
			$level_icon = I('level_icon');
			$data['level_icon'] = $level_icon;
			//获取等级权重
			$level_ranking = I('level_ranking');
			if ($level_ranking)
			{
				$res = M('config_user_level')->where('level_ranking=' . $level_ranking . ' and is_del=0')->find();
				if (!$res)
				{
					$data['level_ranking'] = $level_ranking;
				}
				else
				{
					$this->error('权重重复，请重新输入', U('commission'));
				}

			}


			//等级状态
			$status = I('status');
			$data['status'] = $status;
			$data['number'] = I('number',0);
			//是否是否金额购买升级
			$up_level_by_money = I('up_level_by_money');

			if ($up_level_by_money == 'on')
			{
				//如果是就等于1
				$data['up_level_by_money'] = 1;
				$up_level_money = I('up_level_money');
				$data['up_level_money'] = $up_level_money;
			}
			else
			{
				$data['up_level_by_money'] = 0;

			}

			//自动升级
			$up_level_by_auto = I('up_level_by_auto'); //自动升级是否开启
			if ($up_level_by_auto == 'on')
			{
				$data['up_level_by_auto'] = 1;
				$data['up_auto_mode'] = I('up_auto_mode'); //升级满足条件
				$data['up_auto_first_leader_num'] = I('up_auto_first_leader_num'); //下一级人数
				$data['up_auto_first_leader_level'] = I('up_auto_first_leader_level'); //下一级等级
				$data['up_auto_second_leader_num'] = I('up_auto_second_leader_num'); //下二级人数
				$data['up_auto_second_leader_level'] = I('up_auto_second_leader_level'); //下二级等级
				$data['up_auto_third_leader_num'] = I('up_auto_third_leader_num'); //下三级人数
				$data['up_auto_third_leader_level'] = I('up_auto_third_leader_level'); //下三级等级
				$data['up_auto_team_num'] = I('up_auto_team_num');
				$data['up_auto_team_level'] = I('up_auto_team_level');
				$data['up_auto_self_money'] = I('up_auto_self_money');
				$data['up_auto_first_money'] = I('up_auto_first_money');
				$data['up_auto_first_money_level'] = I('up_auto_first_money_level');
				$data['up_auto_second_money'] = I('up_auto_second_money');
				$data['up_auto_second_money_level'] = I('up_auto_second_money_level');
				$data['up_auto_third_money'] = I('up_auto_third_money');
				$data['up_auto_third_money_level'] = I('up_auto_third_money_level');
				$data['up_auto_team_money'] = I('up_auto_team_money');
				$data['up_auto_team_money_level'] = I('up_auto_team_money_level');
				$data['up_auto_first_order'] = I('up_auto_first_order');
				$data['up_auto_first_order_level'] = I('up_auto_first_order_level');
				$data['up_auto_second_order'] = I('up_auto_second_order');
				$data['up_auto_second_order_level'] = I('up_auto_second_order_level');
				$data['up_auto_third_order'] = I('up_auto_third_order');
				$data['up_auto_third_order_level'] = I('up_auto_third_order_level');
				$data['up_auto_team_order'] = I('up_auto_team_order');
				$data['up_auto_team_order_level'] = I('up_auto_team_order_level');
				$data['up_auto_goods'] = I('up_auto_goods');
				$data['up_auto_special_category'] = I('up_auto_special_category');
			}
			else
			{
				$data['up_level_by_auto'] = 0;
			}

			$down_level_by_auto = I('down_level_by_auto');
			if ($down_level_by_auto == 'on')
			{
				$data['down_level_by_auto'] = 1;
				$data['down_auto_mode'] = I('down_auto_mode');
				$data['down_auto_self_order_num_1'] = I('down_auto_self_order_num_1');
				$data['down_auto_self_order_days'] = I('down_auto_self_order_days');
				$data['down_auto_self_money_num_1'] = I('down_auto_self_money_num_1');
				$data['down_auto_self_money_days'] = I('down_auto_self_money_days');
				$data['down_auto_team_order_num_1'] = I('down_auto_team_order_num_1');
				$data['down_auto_team_order_days'] = I('down_auto_team_order_days');
				$data['down_auto_team_money_num_1'] = I('down_auto_team_money_num_1');
				$data['down_auto_team_money_days'] = I('down_auto_team_money_days');
				$data['down_auto_self_order_num_2'] = I('down_auto_self_order_num_2');
				$data['down_auto_self_order_weeks'] = I('down_auto_self_order_weeks');
				$data['down_auto_self_money_num_2'] = I('down_auto_self_money_num_2');
				$data['down_auto_self_money_weeks'] = I('down_auto_self_money_weeks');
				$data['down_auto_team_order_num_2'] = I('down_auto_team_order_num_2');
				$data['down_auto_team_order_weeks'] = I('down_auto_team_order_weeks');
				$data['down_auto_team_money_num_2'] = I('down_auto_team_money_num_2');
				$data['down_auto_team_money_weeks'] = I('down_auto_team_money_weeks');
				$data['down_auto_self_order_num_3'] = I('down_auto_self_order_num_3');
				$data['down_auto_self_order_months'] = I('down_auto_self_order_months');
				$data['down_auto_self_money_num_3'] = I('down_auto_self_money_num_3');
				$data['down_auto_self_money_months'] = I('down_auto_self_money_months');
				$data['down_auto_team_order_num_3'] = I('down_auto_team_order_num_3');
				$data['down_auto_team_order_months'] = I('down_auto_team_order_months');
				$data['down_auto_team_money_num_3'] = I('down_auto_team_money_num_3');
				$data['down_auto_team_money_months'] = I('down_auto_team_money_months');

			}
			else
			{
				$data['down_level_by_auto'] = 0;
			}

			//推荐奖励是否开启
			$recommend_status = I('recommend_status');
			if ($recommend_status == 'on')
			{
				$data['recommend_status'] = 1;
				$data['recommend_mode'] = I('recommend_mode', 2);
				$data['recommend_first_money'] = I('recommend_first_money');
				$data['recommend_first_point'] = I('recommend_first_point');
				$data['recommend_second_money'] = I('recommend_second_money');
				$data['recommend_second_point'] = I('recommend_second_point');
				$data['recommend_third_money'] = I('recommend_third_money');
				$data['recommend_third_point'] = I('recommend_third_point');
				$data['recommend_level_diff'] = I('recommend_level_diff');
			}
			else
			{
				$data['recommend_status'] = 0;
			}

			//首次消费返佣是否开启
			$buy_award_status = I('buy_award_status');
			if ($buy_award_status == 'on')
			{
				$data['buy_award_status'] = 1;
				$data['buy_award_mode'] = I('buy_award_mode');
				$data['buy_award_self_money'] = I('buy_award_self_money');
				$data['buy_award_self_point'] = I('buy_award_self_point');
				$data['buy_award_first_money'] = I('buy_award_first_money');
				$data['buy_award_first_point'] = I('buy_award_first_point');
				$data['buy_award_second_money'] = I('buy_award_second_money');
				$data['buy_award_second_point'] = I('buy_award_second_point');
				$data['buy_award_third_money'] = I('buy_award_third_money');
				$data['buy_award_third_point'] = I('buy_award_third_point');

			}
			else
			{
				$data['buy_award_status'] = 0;
			}

			$repeat_buy_award_status = I('repeat_buy_award_status');
			if ($repeat_buy_award_status == 'on')
			{
				$data['repeat_buy_award_status'] = 1;
				$data['repeat_buy_award_mode'] = I('repeat_buy_award_mode');
				$data['repeat_buy_award_self_money'] = I('repeat_buy_award_self_money');
				$data['repeat_buy_award_self_point'] = I('repeat_buy_award_self_point');
				$data['repeat_buy_award_first_money'] = I('repeat_buy_award_first_money');
				$data['repeat_buy_award_first_point'] = I('repeat_buy_award_first_point');
				$data['repeat_buy_award_second_money'] = I('repeat_buy_award_second_money');
				$data['repeat_buy_award_second_point'] = I('repeat_buy_award_second_point');
				$data['repeat_buy_award_third_money'] = I('repeat_buy_award_third_money');
				$data['repeat_buy_award_third_point'] = I('repeat_buy_award_third_point');
			}
			else
			{
				$data['repeat_buy_award_status'] = 0;
			}
			$data['up_auto_goods'] = $up_auto_goods;
			$data['up_auto_order_type'] = $up_auto_order_type;
			$data['down_auto_goods'] = $down_auto_goods;
			$data['down_auto_order_type'] = $down_auto_order_type;
			$data['up_time'] = time();
			$data['up_date'] = date('Y-m-d H:i:s', time());
			$id = I('id');

			if ($id)
			{
				$res = M('config_user_level')->where('id=' . $id)->save($data);
			}
			else
			{
				$res = M('config_user_level')->add($data);
			}
			if ($res)
			{
				$this->success('操作成功', U('level_list'));
			}
			else
			{
				$this - error('操作失败');
			}
		}
		else
		{
			$level = M('config_user_level')->where('is_del!=1')->select();

			$id = I('id');
			if ($id)
			{
				$res = M('config_user_level')->where('id=' . $id)->find();
				$up_auto_goods = $res['up_auto_goods'];
				if ($up_auto_goods)
				{
					$where_uag['id'] = array("in", $up_auto_goods);
					$up_auto_goods = M("goods")->where($where_uag)->field("id,name")->select();
				}
				$up_auto_order_type = $res['up_auto_order_type'];
				if ($up_auto_order_type)
				{
					$up_auto_order_type = explode(",", $up_auto_order_type);
				}

				$down_auto_goods = $res['down_auto_goods'];
				if ($down_auto_goods)
				{
					$where_dag['id'] = array("in", $down_auto_goods);
					$down_auto_goods = M("goods")->where($where_dag)->field("id,name")->select();
				}
				$down_auto_order_type = $res['down_auto_order_type'];
				if ($down_auto_order_type)
				{
					$down_auto_order_type = explode(",", $down_auto_order_type);
				}
				$this->assign('up_auto_goods', $up_auto_goods);
				$this->assign('up_auto_order_type', $up_auto_order_type);
				$this->assign('down_auto_goods', $down_auto_goods);
				$this->assign('down_auto_order_type', $down_auto_order_type);
				$this->assign('data', $res);
			}
			$goods = M('goods')->where('isdel=0')->field('id,name')->select();
			$type = order_type();


			$this->assign('goods', $goods);
			$this->assign('type', $type);
			$this->assign('level', $level);
			$this->display();
		}

	}


	//等级配置删除
	public function commission_del()
	{
		$id = I('id');
		$data['is_del'] = 1;

		$res = M('config_user_level')->where('id=' . $id)->save($data);
		if ($res)
		{
			$this->success('删除成功');
		}
		else
		{
			$this->error('删除失败');
		}
	}


	//用户添加
	public function user_add()
	{
		if ($_POST)
		{
			$data = I('');
			if ($data['password'] != $data['password1'])
			{
				$this->error('两次密码不一致');
			}
			$user_one = M('user')->where('phone=' . $data['phone'])->find();
			if ($user_one)
			{
				$this->error('已有该账号');
			}
			if ($data['phone1'])
			{
				$tui = M('user')->where('phone=' . $data['phone1'])->find();
				if ($tui)
				{
					$date['first_leader'] = $tui['id'];
					if ($tui['first_leader'])
					{
						$date['second_leader'] = $tui['first_leader'];
					}
					if ($tui['second_leader'])
					{
						$date['third_leader'] = $tui['second_leader'];
					}
				}
				else
				{
					$this->error('推荐人不存在');
				}
			}
			$date['realname'] = $data['realname'];
			$date['phone'] = $data['phone'];
			$date['password'] = get_pwd($data['password']);
			$date['headimgurl'] = $data['headimgurl'];
			$date['level'] = $data['level'];
			$date['cre_time'] = time();

			$user = M('user')->add($date);

			if ($user)
			{
				if ($data['phone1'])
				{
					user_sons_add($user, $data['level']);
				}

				update_recom_node($user);
				$this->success('添加成功', U('index'));
			}
			else
			{

				$this->error('添加失败');
			}


		}
		else
		{
			$level = M('config_user_level')->where('is_del=0')->field('id,level_ranking,level_name')->select();
			$this->assign('level', $level);
			$this->display();
		}
	}


	/**
	 * 已删除用户列表
	 * 说明：直接引用
	 * @zd
	 */
	public function deleteList()
	{
		$getData = I();
		if ($getData['user_id'])
		{
			$where['id'] = $getData['user_id'];
		}
		if ($getData['user_name'])
		{
			$where['username|nickname'] = array('like', '%' . $getData['user_name'] . '%');
		}
		if ($getData['phone'])
		{
			$where['phone'] = array('like', '%' . $getData['phone'] . '%');
		}
		if ($getData['level'])
		{
			$where['level'] = $getData['level'];
		}
		if ($getData['status'])
		{
			$where['status'] = $getData['status'];
		}
		if ($getData['cre_time'])
		{
			$getCreTime = get_time_style($getData['cre_time']);
			$where['cre_time'] = array(array("gt", $getCreTime[0]), array("elt", $getCreTime[1]));
		}
		$num = 10;
		$where['is_del'] = 1;
		$count = M("user")->where($where)->count();
		$page = new Page($count, $num);
		$show = $page->show();
		$list = M("user")->where($where)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
		$user_level = M('config_user_level')->where('is_del=0')->field('id,level_name')->select();
		$level_id = M('config_user_level')->where('is_del=0')->field('id')->select();
		$level = array();
		foreach ($level_id as $key => $v)
		{
			$level[$key] = $v['id'];
		}
		$this->assign('level', $user_level);
		$this->assign('level_id', $level);
		$this->assign("list", $list);
		$this->assign("show", $show);
		$this->display();
	}

	// 会员推荐结构
	public function userMap()
	{
		$list = get_sons_tree(0);
		$this->assign("list", json_encode($list));
		$this->display();
	}


	/**
	 * Title： 导出表格
	 * Note：
	 * User： zd
	 * Date： 2020/6/20 11:20
	 */
	public function export_excel()
	{
		$user_id = I("user_id", '');
		$user_name = I("user_name", '');
		$phone = I("phone", '');
		$level = I("level", '');
		$status = I("status", 0);
		$cre_time = I("cre_time", '');

		if ($user_id)
		{
			$where['id'] = $user_id;
		}
		if ($user_name)
		{
			$where['username|nickname'] = array('like', '%' . $user_name . '%');
		}
		if ($phone)
		{
			$where['phone'] = array('like', '%' . $phone . '%');
		}
		if ($level)
		{
			$where['level'] = $level;
		}
		if ($status)
		{
			$where['status'] = $status;
		}
		if ($cre_time)
		{
			$getCreTime = get_time_style($cre_time);
			$where['cre_time'] = array(array("gt", $getCreTime[0]), array("elt", $getCreTime[1]));
		}
		$where['is_del'] = 0;

		$tempID = 3;        // 导出模板ID
		$res = exportExcel($where, 'id desc', 0, $tempID, '', 0);
		if ($res['err'] == 0)
		{
			jsonout('数据导出', 0, $res['result']);
		}
		else
		{
			jsonout($res['msg'], 1);
		}
	}

	/**
	 * Title： 导出表格
	 * Note：
	 * User： zd
	 * Date： 2020/6/20 11:20
	 */
	public function export_excel_account()
	{
		$id = I("id", 0);
		$utype = I("utype", 1);    // 1用户，2商家，3代理
		$type = I("type", 0);
		$deal_type = I("deal_type", 0);
		$op_type = I("op_type", 0);
		$startTime = I("startTime", '');
		$endTime = I("endTime", '');

		if ($startTime && $endTime)
		{
			$where['cre_time'] = array("between", array(strtotime($startTime), strtotime($endTime)));
		}
		if ($startTime && !$endTime)
		{
			$where['cre_time'] = array("egt", strtotime($startTime));
		}
		if (!$startTime && $endTime)
		{
			$where['cre_time'] = array("elt", strtotime($endTime));
		}

		$where_u['id'] = $id;
		$info = get_user_info($where_u, '', '', $utype);
		if ($info)
		{
			if ($utype == 1)
			{
				$user_id = $info['id'];
			}
			else
			{
				$user_id = $info['user_id'];
			}
		}
		else
		{
			$user_id = 0;
		}

		if ($user_id)
		{
			$where['user_id'] = $user_id;
		}
		if ($type)
		{
			$where['type'] = $type;
		}
		if ($deal_type)
		{
			$where['deal_type'] = $deal_type;
		}
		if ($utype)
		{
			$where['utype'] = $utype;
		}
		if ($op_type)
		{
			$where['op_type'] = $op_type;
		}

		$tempID = 6;        // 导出模板ID
		$res = exportExcel($where, 'id desc', 0, $tempID, '', 0);
		if ($res['err'] == 0)
		{
			jsonout('数据导出', 0, $res['result']);
		}
		else
		{
			jsonout($res['msg'], 1);
		}
	}
}