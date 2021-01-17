<?php

namespace Admin\Controller;

use Clas\Agent;
use Clas\Address;
use Think\Page;

class AgentController extends CommonController
{

	//代理商列表
	public function index($data = null)
	{
		$data = I();
		if ($data['user_id'])
		{
			$where['user_id'] = $data['user_id'];
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
		if ($data['cre_time'])
		{
			$addTimeArr = explode(" - ", $data['cre_time']);
			$addTimeArr[0] = strtotime($addTimeArr[0]);
			$addTimeArr[1] = strtotime($addTimeArr[1]);
			$where['cre_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
		}
		if ($data['area_name'])
		{
			$map['name'] = array('like', '%' . $data['area_name'] . '%');
			$areaData = M('region')->where($map)->select();
			$sarea_data = array();
			foreach ($areaData as $k => $v)
			{
				array_push($sarea_data, $v['id']);
			}
			$where['area_id'] = array('in', $sarea_data);
		}
		$where['status'] = 1;
		$where['isdel'] = 0;
		$count = M('agent')->where($where)->count();
		$page = new Page($count, 10);
		$show = $page->show();

		$result = M('agent')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($result as $k => $v)
		{
			$area_name = "";
			$area_name .= M('region')->where("id=" . $v["province_id"])->getField('name');
			$area_name .= M('region')->where("id=" . $v["city_id"])->getField('name');
			$area_name .= M('region')->where("id=" . $v["district_id"])->getField('name');
			$result[$k]['area_name'] = $area_name;
		}
		$this->assign('show', $show);
		$this->assign('data', $result);
		$this->display();
	}

	// 代理审核
	public function check()
	{
		if ($_POST)
		{
			$data = I();
			if ($data['user_id'])
			{
				$where['user_id'] = $data['user_id'];
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
			if ($data['cre_time'])
			{
				$addTimeArr = explode(" - ", $data['cre_time']);
				$addTimeArr[0] = strtotime($addTimeArr[0]);
				$addTimeArr[1] = strtotime($addTimeArr[1]);
				$where['cre_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
			}
			if ($data['area_name'])
			{
				$map['name'] = array('like', '%' . $data['area_name'] . '%');
				$areaData = M('region')->where($map)->select();
				$sarea_data = array();
				foreach ($areaData as $k => $v)
				{
					array_push($sarea_data, $v['id']);
				}
				$where['area_id'] = array('in', $sarea_data);
			}
		}


		$where['status'] = 3;


		$count = M('agent')->where($where)->count();
		$page = new Page($count, 10);
		$show = $page->show();

		$result = M('agent')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($result as $k => $v)
		{
			$area_name = "";
			$area_name .= M('region')->where("id=" . $v["province_id"])->getField('name');
			$area_name .= M('region')->where("id=" . $v["city_id"])->getField('name');
			$area_name .= M('region')->where("id=" . $v["district_id"])->getField('name');


			$result[$k]['area_name'] = $area_name;
		}
		$this->assign('show', $show);
		$this->assign('data', $result);
		$this->display();
	}

	// 冻结列表
	public function frozen()
	{
		$data = I();
		if ($data['user_id'])
		{
			$where['user_id'] = $data['user_id'];
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
		if ($data['cre_time'])
		{
			$addTimeArr = explode(" - ", $data['cre_time']);
			$addTimeArr[0] = strtotime($addTimeArr[0]);
			$addTimeArr[1] = strtotime($addTimeArr[1]);
			$where['cre_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
		}
		if ($data['area_name'])
		{
			$map['name'] = array('like', '%' . $data['area_name'] . '%');
			$areaData = M('region')->where($map)->select();
			$sarea_data = array();
			foreach ($areaData as $k => $v)
			{
				array_push($sarea_data, $v['id']);
			}
			$where['area_id'] = array('in', $sarea_data);
		}
		$where['status'] = 2;
		$count = M('agent')->where($where)->count();
		$page = new Page($count, 10);
		$show = $page->show();

		$result = M('agent')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($result as $k => $v)
		{
			$area_name = "";
			$area_name .= M('region')->where("id=" . $v["province_id"])->getField('name');
			$area_name .= M('region')->where("id=" . $v["city_id"])->getField('name');
			$area_name .= M('region')->where("id=" . $v["district_id"])->getField('name');
			$result[$k]['area_name'] = $area_name;
		}
		$this->assign('show', $show);
		$this->assign('data', $result);
		$this->display();
	}

	// 代理商列表
	public function AgentList($data = null)
	{
		if (!$data)
		{
			$data = I();
		}
		$Agentout = new Agent($data['agent_id']);
		$data = $Agentout->agentList($data);
		jsonout('代理商列表', 0, $data);
	}

	// 代理审核处理
	public function checkAct()
	{
		$id = I('id');
		$st = I('st');    // 1恢复/通过，0删除/拒绝

		$agent = M('agent')->find($id);
		if (!$agent)
		{
			$this->error('此代理不存在');
		}

		if ($st == 1)
		{
			// 通过，成为代理
			M('agent')->where('id=' . $id)->setField('status', 1);
			M('user')->where('id=' . $agent['user_id'])->setField('is_agent', 1);
		}
		elseif ($st == 2)
		{
			// 冻结代理
			M('agent')->where('id=' . $id)->setField('status', 2);
			M('user')->where('id=' . $agent['user_id'])->setField('is_agent', 2);
		}
		elseif ($st == 0)
		{
			// 拒绝，删除代理
			M('agent')->where('id=' . $id)->setField('isdel', 1);
			// 此时不能直接删除用户的代理状态，而应查找一下是否还有其他省市的代理
			// 如果没有，则删除状态，如果有，则更改或不改状态
			$isHave = M("agent")->where("isdel=0 and user_id=" . $agent["user_id"])->order("status asc")->find();
			if ($isHave)
			{
				M('agent')->where('id=' . $id)->setField('status', $isHave["status"]);
			}
			else
			{
				M('user')->where('id=' . $agent['user_id'])->setField('is_agent', 0);
			}
		}

		$this->success('操作完成', U('check'));
	}

	// 添加代理
	public function add()
	{
		if (IS_POST)
		{
			$data = I();
			$obj = new Agent(0, $this->_role);
			$ok = $obj->add($data);
			if (!is_numeric($ok))
			{
				$this->error($ok);
			}
			$this->success('添加成功', U('Agent/index'));
			exit;
		}
		$list = M("user")->where("is_del=0 and status=1")->select();
		$p = M('region')->where(array('parent_id' => 0, 'level' => 1))->select();
		$feelist = M("config_shop_fee")->select();
		$this->assign('province', $p);
		$this->assign('user_id', $uid);
		$this->assign('list', $list);
		$this->assign('feelist', $feelist);
		$this->display();
	}

	//删除代理商
	public function delete()
	{
		$id = I('id');
		$data = M("agent")->where("id=" . $id)->find();
		if ($data)
		{
			$user_id = $data["user_id"];
		}
		else
		{
			$user_id = 0;
		}
		$model = M();
		$model->startTrans();

		$rs2 = M("agent")->where("id=" . $id)->setField('isdel', 1);
		$rs3 = M("agent")->where("id !=" . $id . " and user_id=" . $user_id)->count();
		if ($rs3 > 0)
		{
			$rs = true;
		}
		else
		{
			$rs = M('user')->where('id=' . $user_id)->setField("is_agent", 0);
		}
		if ($rs !== false && $rs2 !== false)
		{
			$model->commit();
			$this->success('操作成功！');
		}
		else
		{
			$model->rollback();
			$this->error('操作失败！');
		}

	}

	//修改代理
	public function agent_update()
	{
		$id = I("id");
		$data = M("agent")->where("id=" . $id)->find();

		if (!$data)
		{
			$this->error("信息异常");
		}

		if (IS_POST)
		{
			$data = I();

			$realname = I("realname");
			$phone = I("phone");
			$province_id = I("province_id", 0);
			$city_id = I("city_id", 0);
			$district_id = I("district_id", 0);
			$town_id = I("town_id", 0);
			$status = I("status", 0);
			if (!$id)
			{
				$this->error("缺少主要参数");
			}
			if (!$realname)
			{
				$this->error("姓名为必填");
			}
			if (!$phone)
			{
				$this->error("手机号为必填");
			}
			if (!$province_id)
			{
				$this->error("请选择省");
			}
			if (!$status)
			{
				// 代理状态，1正常，2冻结，3审核中
				$this->error("请选择状态");
			}

			$area_id = $town_id ? $town_id : ($district_id ? $district_id : ($city_id ? $city_id : $province_id));

			if ($province_id && $data["province_id"])
			{
				$province = M("region")->where("id=" . $province_id)->getField("name");
				$data["province"] = $province;
			}
			if ($city_id && $data["city_id"])
			{
				$city = M("region")->where("id=" . $city_id)->getField("name");
				$data["city"] = $city;
			}
			if ($district_id && $data["district_id"])
			{
				$district = M("region")->where("id=" . $district_id)->getField("name");
				$data["district"] = $district;
			}

			$model = M();
			$model->startTrans();

			$data["realname"] = $realname;
			$data["phone"] = $phone;
			$data["province_id"] = $province_id;
			$data["city_id"] = $city_id;
			$data["district_id"] = $district_id;
			$data["area_id"] = $area_id;
			$data["status"] = $status;

			$rs = M("agent")->where("id=" . $id)->save($data);

			// is_agent 是否代理，0否，1是，2冻结，3审核中
			$rs2 = M("user")->where("id=" . $data["user_id"])->setField("is_agent", $status);

			if ($rs !== false && $rs2 !== false)
			{
				$model->commit();
				$this->success("修改成功");
			}
			else
			{
				$model->rollback();
				$this->error("修改失败");
			}
			exit();
		}

		$province = M("region")->where("level=1")->order("id asc")->select();
		$city = M("region")->where("parent_id=" . $data["province_id"])->order("id asc")->select();
		$district = M("region")->where("parent_id=" . $data["city_id"])->order("id asc")->select();

		$feelist = M("config_shop_fee")->select();

		$this->assign("data", $data);
		$this->assign("province", $province);
		$this->assign("city", $city);
		$this->assign("district", $district);
		$this->assign("feelist", $feelist);
		$this->display();
	}

	/*
	 * @param   string   市
	 * @lz
	*/
	public function city()
	{
		$obj = new Address();
		$result = $obj->city();
		jsonout($result[0], $result[1], $result[2]);
	}

	/*
	 * @param   string    县
	 * @lz
	*/
	public function district()
	{
		$obj = new Address();
		$result = $obj->district();
		jsonout($result[0], $result[1], $result[2]);
	}

	// 删除列表
	public function deleteList()
	{
		$data = I();
		if ($data['user_id'])
		{
			$where['user_id'] = $data['user_id'];
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
		if ($data['cre_time'])
		{
			$addTimeArr = explode(" - ", $data['cre_time']);
			$addTimeArr[0] = strtotime($addTimeArr[0]);
			$addTimeArr[1] = strtotime($addTimeArr[1]);
			$where['cre_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
		}
		if ($data['area_name'])
		{
			$map['name'] = array('like', '%' . $data['area_name'] . '%');
			$areaData = M('region')->where($map)->select();
			$sarea_data = array();
			foreach ($areaData as $k => $v)
			{
				array_push($sarea_data, $v['id']);
			}
			$where['area_id'] = array('in', $sarea_data);
		}
		$where['isdel'] = 1;
		$count = M('agent')->where($where)->count();
		$page = new Page($count, 10);
		$show = $page->show();

		$result = M('agent')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($result as $k => $v)
		{
			$area_name = "";
			$area_name .= M('region')->where("id=" . $v["province_id"])->getField('name');
			$area_name .= M('region')->where("id=" . $v["city_id"])->getField('name');
			$area_name .= M('region')->where("id=" . $v["district_id"])->getField('name');
			$result[$k]['area_name'] = $area_name;
		}
		$this->assign('show', $show);
		$this->assign('data', $result);
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
		$content = I("content", '');
		$cre_time = I("cre_time", '');
		$area_name = I("area_name", '');
		if ($user_id)
		{
			$where['user_id'] = $user_id;
		}
		if ($content)
		{
			if (is_numeric($content) && strlen($content) < 10)
			{
				// 小于10位，并且是数字，按用户ID搜索
				$where_u['id'] = $content;
				$userArr = M('user')->where($where_u)->getField("id", true);
				if ($userArr)
				{
					$where['user_id'] = array("in", $userArr);
				}
			}
			else
			{
				// 非数字，或长度大于10位，按用户名或手机号搜索
				$where_u['phone|realname'] = array("like", "%" . $content . "%");
				$userArr = M('user')->where($where_u)->getField("id", true);
				if ($userArr)
				{
					$where['user_id'] = array("in", $userArr);
				}
			}
		}
		if ($cre_time)
		{
			$addTimeArr = explode(" - ", $cre_time);
			$addTimeArr[0] = strtotime($addTimeArr[0]);
			$addTimeArr[1] = strtotime($addTimeArr[1]);
			$where['cre_time'] = array(array("gt", $addTimeArr[0]), array("elt", $addTimeArr[1]));
		}
		if ($area_name)
		{
			$map['name'] = array('like', '%' . $area_name . '%');
			$areaData = M('region')->where($map)->select();
			$sarea_data = array();
			foreach ($areaData as $k => $v)
			{
				array_push($sarea_data, $v['id']);
			}
			$where['area_id'] = array('in', $sarea_data);
		}
		$where['status'] = 1;
		$where['isdel'] = 0;

		$tempID = 4;        // 导出模板ID
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