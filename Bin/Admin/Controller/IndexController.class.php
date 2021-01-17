<?php

namespace Admin\Controller;

use Clas\Fanli;

class IndexController extends CommonController
{
	public function index()
	{
		// 首页统计重新处理
		// @zd 20191004

        //判断是否有权限


        if( session('role_id') != 1){
            $this->error('您没有该权限');
        }
		$todayStart = strtotime(date("Y-m-d"));                                // 今天开始
		$todayEnd = $todayStart + 86400;                                              // 今天结束
		$todayStartPre = $todayStart - 86400;                                              // 昨天开始
		$monthStart = strtotime(date("Y-m-01"));                              // 本月开始
		$monthEnd = strtotime(date('Y-m-01', strtotime('+1 month')));  // 本月结束
		$monthStartPre = strtotime(date('Y-m-01', strtotime('-1 month')));  // 上月开始
		$weekStart = strtotime('this week Monday', time());                    // 本周开始
		$weekEnd = $weekStart + 86400 * 7;                                           // 本周结束

		// 订单数量统计
		$where_ord_1['isdel'] = 0;  // 下面几个共用
        $where_ord_1['order_type'] = array('in',array(0,6,5));   // 待发货
		$where_ord_1['order_status'] = 1;   // 待发货
		$order_1 = M("order")->where($where_ord_1)->count();

		$where_ord_1['order_status'] = 6;   // 售后
		$order_6 = M("order")->where($where_ord_1)->count();

		$where_ord_1['order_status'] = array("in", "1,2,3,5,6");   // 成交
		$where_ord_1['pay_time'] = array("between", array($monthStart, $monthEnd)); // 本月
		$order_m = M("order")->where($where_ord_1)->count();

		$where_ord_1['pay_time'] = array("between", array($monthStartPre, $monthStart)); // 上月
		$order_pm = M("order")->where($where_ord_1)->count();

		// 今天+昨天订单金额
		$where_ord_2['isdel'] = 0;
		$where_ord_2['order_type'] = array('neq',12);
		$where_ord_2['confirm_time'] = array('gt',0);
		$where_ord_2['pay_time'] = array("between", array($todayStart, $todayEnd)); // 今天

		$order_money_today = M("order")->where($where_ord_2)->sum("total_commodity_price");
        $order_money_today = $order_money_today ? $order_money_today : 0;
		$allCoupon = M("order")->where($where_ord_2)->sum("user_coupon_money");
		$allCoupon = $allCoupon ? $allCoupon :0;
        $order_money_today = $order_money_today + $allCoupon;


		$where_ord_2['pay_time'] = array("between", array($todayStartPre, $todayStart)); // 昨天
		$order_money_yestoday = M("order")->where($where_ord_2)->sum("total_commodity_price");
        $order_money_yestoday = $order_money_yestoday ? $order_money_yestoday : 0;
        $allCoupon1 = M("order")->where($where_ord_2)->sum("user_coupon_money");
        $allCoupon1 = $allCoupon1 ? $allCoupon1 :0;
        $order_money_yestoday = $order_money_yestoday + $allCoupon1;

		// 今天+昨天订单数量
		$where_ord_2['pay_time'] = array("between", array($todayStart, $todayEnd)); // 今天
		$order_count_today = M("order")->where($where_ord_2)->count();

		$where_ord_2['pay_time'] = array("between", array($todayStartPre, $todayStart)); // 昨天
		$order_count_yestoday = M("order")->where($where_ord_2)->count();

		// 今天+昨天支付人数
		$where_ord_2['pay_time'] = array("between", array($todayStart, $todayEnd)); // 今天
		$order_user_today_array = M("order")->where($where_ord_2)->group("user_id")->select();
		$order_user_today = count($order_user_today_array);

		$where_ord_2['pay_time'] = array("between", array($todayStartPre, $todayStart)); // 昨天
		$order_user_yestoday_array = M("order")->where($where_ord_2)->group("user_id")->select();
		$order_user_yestoday = count($order_user_yestoday_array);

		// 商家统计
		$where_shop['cre_time'] = array("between", array($todayStart, $todayEnd)); // 今天
		$shop_today = M("shop")->where($where_shop)->count();

		$where_shop['cre_time'] = array("between", array($todayStartPre, $todayStart)); // 昨天
		$shop_yestoday = M("shop")->where($where_shop)->count();

		$where_shop_2['status'] = array("in", "0,3"); // 待审
		$shop_checking = M("shop")->where($where_shop_2)->count();

		$where_shop_2['status'] = array("in", "0,1,2,3"); // 待审
		$shop_all = M("shop")->where($where_shop_2)->count();

		// 提现统计
		$where_tx['ischeck'] = 0;   // 待审核
		$tx_checking = M("tx_log")->where($where_tx)->count();

		$where_tx['ischeck'] = array("in", "1,3");   // 今日已提现
		$where_tx['up_time'] = array("between", array($todayStart, $todayEnd)); // 今天
		$tx_money_today = M("tx_log")->where($where_tx)->sum("tx_money");

		$where_tx['ischeck'] = array("in", "1,3");   // 本月已提现
		$where_tx['up_time'] = array("between", array($monthStart, $monthEnd)); // 本月
		$tx_money_month = M("tx_log")->where($where_tx)->sum("tx_money");

		$where_tx['ischeck'] = array("in", "1,3");   // 本月已提现
		$where_tx['up_time'] = array("between", array($monthStartPre, $monthStart)); // 本月
		$tx_money_month_pre = M("tx_log")->where($where_tx)->sum("tx_money");

		// 会员统计
		$where_user['is_del'] = 0;
		$where_user['cre_time'] = array("between", array($todayStart, $todayEnd)); // 今天
		$user_today = M("user")->where($where_user)->count();

		$where_user['cre_time'] = array("between", array($todayStartPre, $todayStart)); // 昨天
		$user_yestoday = M("user")->where($where_user)->count();

		$where_user['cre_time'] = array("between", array($weekStart, $weekEnd)); // 本周
		$user_week = M("user")->where($where_user)->count();

		$where_user['cre_time'] = array("between", array($monthStart, $monthEnd)); // 本月
		$user_month = M("user")->where($where_user)->count();

		$where_user['cre_time'] = array("gt", 0); // 所有
		$user_all = M("user")->where($where_user)->count();

		// 商品统计
		$where_goods['isdel'] = 0;          // 未删除
		$where_goods['is_on_sale'] = 1;     // 上架
		$goods_sale = M("goods")->where($where_goods)->count();

		$where_goods['is_on_sale'] = 0;     // 下架
		$goods_unsale = M("goods")->where($where_goods)->count();

		$where_goods['isdel'] = 1;     // 已删除
		$where_goods['is_on_sale'] = array("in", "0,1");     // 已删除
		$goods_del = M("goods")->where($where_goods)->count();

		$this->assign("order_1", $order_1);
		$this->assign("order_6", $order_6);
		$this->assign("order_m", $order_m);
		$this->assign("order_pm", $order_pm);
		$this->assign("order_money_today", $order_money_today);
		$this->assign("order_money_yestoday", $order_money_yestoday);
		$this->assign("order_count_today", $order_count_today);
		$this->assign("order_count_yestoday", $order_count_yestoday);
		$this->assign("order_user_today", $order_user_today);
		$this->assign("order_user_yestoday", $order_user_yestoday);
		$this->assign("shop_today", $shop_today);
		$this->assign("shop_yestoday", $shop_yestoday);
		$this->assign("shop_checking", $shop_checking);
		$this->assign("shop_all", $shop_all);
		$this->assign("tx_money_today", $tx_money_today);
		$this->assign("tx_money_month", $tx_money_month);
		$this->assign("tx_money_month_pre", $tx_money_month_pre);
		$this->assign("user_today", $user_today);
		$this->assign("user_yestoday", $user_yestoday);
		$this->assign("user_week", $user_week);
		$this->assign("user_month", $user_month);
		$this->assign("user_all", $user_all);
		$this->assign("goods_sale", $goods_sale);
		$this->assign("goods_unsale", $goods_unsale);
		$this->assign("goods_del", $goods_del);
		$this->display();
	}


	public function clear_cache()
	{
		M()->execute('truncate table __PREFIX__session');
		set_config_cache();

		// TODO F('xxx', null); 清除缓存
		// TODO S('xxx', null); 清除缓存


		//删除缓存文件
		$ss = session_save_path();
		$file = scandir($ss);
		foreach ($file as $i)
		{
			if ($i != '..' && $i != '.')
			{
				unlink($ss . DIRECTORY_SEPARATOR . $i);
			}
		}

		$this->success('OK');
	}


	public function close_notice()
	{
		$id = I('id', 0, 'intval');
		M('notice')->where("id={$id}")->setField('isopen', 0);
		$this->success("已关闭");
	}

	public function delete_notice()
	{
		$id = I('id', 0, 'intval');
		M('notice')->where("id={$id}")->delete();
		$this->success("已删除");
	}

	public function notice()
	{
		if (IS_POST)
		{
			$title = I('title', '', 'trim');
			foreach ($title as $k => $v)
			{
				M('notice')->where('id=' . $k)->setField('title', $v);
			}
			$this->success("ok");
		}
		else
		{
			$list = M('notice')->select();
			$this->assign('list', $list);
			$this->display();
		}
	}


	public function changePwd()
	{
		if (IS_POST)
		{
			$oldpwd = I('oldpwd', '');
			$newpwd = I('newpwd', '');
			$confirmpwd = I('confirmpwd', '');

			if (empty($oldpwd) || empty($newpwd))
			{
				$this->error('密码不能为空');
			}
			if ($oldpwd == $newpwd)
			{
				$this->error('新旧密码不能相同');
			}
			if ($newpwd != $confirmpwd)
			{
				$this->error('新密码和确认密码不相同');
			}

			$code = M('admin')->where("id={$this->_admin_id}")->getField('random_code');

			$a['id'] = $this->_admin_id;
			$a['password'] = get_pwd($oldpwd, 1, $code);

			$b['password'] = get_pwd($newpwd, 1, $code);

			$row = M('admin')->where($a)->save($b);

			if ($row)
			{
				$this->success('修改成功', U('Index/index'));
			}
			else
			{
				$this->error('修改失败，旧密码不正确');
			}
		}
		else
		{
			$this->display();
		}
	}

	public function version()
	{

		if (IS_POST)
		{

			$id = I('id');
			$os = I('os');
			$appname = I('appname');
			$versionstr = I('versionstr');
			$versionnumber = I('versionnumber');
			$data = array();


			foreach ($id as $i)
			{
				$file_url = '';
				if ($_FILES['upload' . $i]['error'] == UPLOAD_ERR_OK)
				{
					$upload = new \Think\Upload(
						array(
							'rootPath' => './upload/',
							'subName' => 'app',
							'saveName' => 'quancheng',
							'saveExt' => 'apk',
							'replace' => true
						)
					);
					$info = $upload->uploadOne($_FILES['upload' . $i]);
					if (!$info)
					{
						$this->error($upload->getError());
					}

					$file_url = get_url() . '/upload/' . $info['savepath'] . $info['savename'];
				}


				$data[] = array(
					'os' => $os[$i],
					'appname' => $appname[$i],
					'versionstr' => $versionstr[$i],
					'versionnumber' => $versionnumber[$i],
					'loadurl' => $file_url,
				);
			}

			$this->_save_config_file($data);

			M('version')->where('id<>0')->setField('is_current', 0);

			M('version')->addAll($data);

			$this->success('操作成功');
			exit;
		}

		$version = M('version')->where('is_current=1')->select();
		$this->assign('version', $version);
		$this->display();
	}

	public function web()
	{
		$where['is_display'] = 1;
		$config = M('config')->where($where)->select();
		if (IS_POST)
		{
			$ratio = I('ratio');
			$type = I('type');

			foreach ($ratio as $k => $v)
			{

				if ($type[$k] == 'uint')
				{
					$v = abs($v);
				}
				elseif ($type[$k] == 'file')
				{
					$ok = common_upload_photo('file' . $k, 'web');

					if ($ok['success'] == 1)
					{
						$v = $ok['path'];
					}
				}
				M('config')->where("id=$k")->setField('val', $v);
			}
			//判断是否开启上传到7牛云
			$where['name'] = 'upload_type';
			$config = M('config')->where($where)->find();
			$data['upload_type'] = $config['val'];
			$json_string = json_encode($data);
			file_put_contents('Public/data.txt', $json_string);
			set_config_cache(); //重新设置缓存，每次修改都要执行一次，要不然不会实时更新
			put_qiniu_file();
			$this->success('设置完成');
		}
		else
		{
			$this->assign('config', $config);
			$this->display();
		}
	}

	private function _save_config_file($data)
	{

		$str = '<?xml version="1.0" encoding="utf-8"?><note>';
		foreach ($data as $i)
		{
			$str .= '<phone>';

			$str .= '<os>' . $i['os'] . '</os>';
			$str .= '<appname>' . $i['appname'] . '</appname>';
			$str .= '<versionstr>' . $i['versionstr'] . '</versionstr>';
			$str .= '<versionnumber>' . $i['versionnumber'] . '</versionnumber>';
			$str .= '<loadurl>' . $i['loadurl'] . '</loadurl>';

			$str .= '</phone>';
		}
		$str .= '</note>';

		$f = fopen('upload/version.xml', 'w') or die('Unable to open file!');
		fwrite($f, $str);
		fclose($f);

	}

	public function delete_version()
	{
		unlink('upload/version.xml');
		$this->success('已删除');
	}

	public function industry()
	{
		$id = I('id');
		if ($id)
		{
			$data = M('industry')->where("id = {$id}")->field('id,name,icon')->find();
			$this->assign('data', $data);
		}
		else
		{
			$p = I('p', 1);
			$page = page('industry');
			$list = $page['list'];
			$this->assign('page', $p);
			$this->assign('max_page', ceil($page['sum'] / 10));
			$this->assign('list', $list);
		}
		// var_dump($page);die;
		// $this->assign('list', category_lists());
		$this->display();
	}

	public function delete_category()
	{
		$id = I('id');
		$re = M('industry')->where('id=' . $id)->delete();
		if ($re)
		{
			$this->success('删除成功');
		}
		else
		{
			$this->error('删除失败');
		}
	}

	public function add_category()
	{
		if (IS_POST)
		{
			$result = $this->editCategoryss();
			if ($result['err'])
			{
				$this->error($result['msg']);
			}
			else
			{
				$this->success($result['msg']);
			}

		}
		else
		{

			$id = I('id', 0, 'intval');
			if ($id)
			{
				$category = M('industry')->find($id);
				$this->assign('category', $category);
			}
			$list = $this->get_categorys(0);
			$this->assign('categorys', $list);
			$this->display();
		}
	}

	public function editCategoryss()
	{
		$pid = I('pid', 0, 'intval');
		$id = I('id', 0, 'intval');
		$name = I('name', '', 'trim');
		if ($id == 0)
		{

			if ($_FILES['icon'])
			{

				$info = common_upload_photo('icon', 'category');

				if ($info['success'] == 1)
				{
					$data = ['name' => $name, 'pid' => $pid, 'icon' => $info['msg']];
				}
				else
				{
					$data = ['name' => $name];
				}

			}
			$data['add_time'] = time();
			M('industry')->add($data);
			return _dat('添加成功', 0);
		}
		else
		{
			if ($_FILES['icon'])
			{
				$info = common_upload_photo('icon', 'category');
				if ($info['success'] == 1)
				{
					$data = ['name' => $name, 'pid' => $pid, 'icon' => $info['msg']];
				}
				else
				{
					$data = ['name' => $name];
				}
			}
			$data['add_time'] = time();
			M('industry')->where('id=' . $id)->save($data);
			return _dat('修改成功', 0);
		}
	}

	function get_categorys($pid = false)
	{

		$where = '';
		if ($pid !== false)
		{
			$where['pid'] = $pid;
		}

		$list = M('industry')->where($where)->getField('id,name');
		return $list;
	}

	public function cdsz_action()
	{
		$parent_id = I('parent_id', 0);
		if ($parent_id == 0)
		{
			$parent = array('id' => 0, 'name' => "", 'level' => 0);
		}
		else
		{
			$parent = M('jscd')->where("id=$parent_id and is_del=0")->find();
		}
		$jscd = M('jscd')->where("parent_id=$parent_id and is_del=0")->select();
		$this->assign('parent', $parent);
		$this->assign('jscd', $jscd);
		$this->display();
	}

	public function jscd_handle()
	{
		$data = I('post.');
		$id = I('parent_id');
		$referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Index/cdsz_action");
		$data['level'] = $data['level'] + 1;
		if (empty($data['name']))
		{
			$this->error("请填写菜单名称", $referurl);
		}
		elseif (empty($data['group1']))
		{
			$this->error("请填写菜单英文名称", $referurl);
		}
		else
		{
			$res = M('jscd')->where("parent_id = " . $data['parent_id'] . " and name='" . $data['name'] . "' and is_del=0 ")->find();
			$res1 = M('jscd')->where("parent_id = " . $data['parent_id'] . " and group1 = '" . $data['group1'] . "' and is_del=0 ")->find();
			if (empty($res) && empty($res1))
			{
				M('jscd')->add($data);
				$this->success("操作成功", $referurl);
			}
			elseif (!empty($res))
			{
				$this->error("请勿创建同名一级菜单", $referurl);
			}
			elseif (!empty($res1))
			{
				$this->error("英文名是唯一标识符不可重复", $referurl);
			}
		}
	}

	public function jscd_del()
	{
		$id = I('id');
		$res = M('jscd')->where("id = " . $id . " or parent_id=" . $id)->setField('is_del', 1);
		$referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Index/cdsz_action");
		if ($res)
		{
			$this->success("操作成功", $referurl);
		}
	}


	function ajax_get_action()
	{
		$control = I('controller');
		$advContrl = get_class_methods("Admin\\Controller\\" . $control);
		//dump($advContrl);
		$baseContrl = get_class_methods('Admin\Controller\BaseController');
		$diffArray = array_diff($advContrl, $baseContrl);
		$html = '';
		foreach ($diffArray as $val)
		{
			$html .= "<option value='" . $val . "'>" . $val . "</option>";
		}
		exit($html);
	}

	function right_list()
	{
		$group = array('system' => '系统设置', 'content' => '内容管理', 'goods' => '商品中心', 'member' => '会员中心',
			'order' => '订单中心', 'marketing' => '营销推广', 'tools' => '插件工具', 'count' => '统计报表', 'shop' => '商家后台'
		);
		$right_list = M('jscd')->select();
		$this->assign('right_list', $right_list);
		$this->assign('group', $group);
		$this->display();
	}

	//角色管理
	public function role()
	{
		$list = D('admin_role')->order('role_id desc')->select();
		$this->assign('list', $list);
		$this->display();
	}


	//添加角色
	public function role_info()
	{

		$role_id = I('get.role_id');
		$tree = $detail = array();
		if ($role_id)
		{
			$detail = M('admin_role')->where("role_id=$role_id")->find();
			$detail['act_list'] = explode(',', $detail['act_list']);
			$this->assign('detail', $detail);
		}

		$right = M('jscd')->where('level=1 and is_del=0')->order('id')->select();
		foreach ($right as $key => $val)
		{
			if (!empty($detail))
			{
				$val['enable'] = in_array($val['id'], $detail['act_list']);
			}
			$right_list = M('jscd')->where('parent_id=' . $val['id'] . " and is_del=0")->order('id')->select();
            foreach ($right_list as $k => $v)
            {
                $v['is_check'] = 0;
                $right_list2 = M('jscd')->where('parent_id=' . $v['id'] . " and is_del=0")->order('id')->select();
                foreach ($right_list2 as $k2 => $v2)
                {
                    $v['list'][] = $v2;
                }
                foreach ($detail['act_list'] as $kee=>$vee){
                    if($vee == $v['id']){
                        $v['is_check'] = 1;
                    }
                }
                $modules[$val['name']][] = $v;
            }
		}

		//权限组
		$group = array('system' => '系统设置', 'content' => '内容管理', 'goods' => '商品中心', 'member' => '会员中心',
			'order' => '订单中心', 'marketing' => '营销推广', 'tools' => '插件工具', 'count' => '统计报表'
		);

		$this->assign('group', $group);

		$this->assign('modules', $modules);

		$this->display();
	}


	public function roleSave()
	{
		$data = I('post.');
		$res = $data['data'];
		$res['act_list'] = is_array($data['right']) ? implode(',', $data['right']) : '';
		if (empty($data['role_id']))
		{
			$r = D('admin_role')->add($res);
		}
		else
		{
			$r = D('admin_role')->where('role_id=' . $data['role_id'])->save($res);
		}
		if ($r)
		{
			//adminLog('管理角色',__ACTION__);
			$this->success("操作成功!", U('Index/role_info', array('role_id' => $data['role_id'])));
		}
		else
		{
			$this->success("操作失败!", U('Index/role'));
		}
	}


	public function roleDel()
	{
		$role_id = I('post.role_id');
		$admin = D('admin')->where('role_id=' . $role_id)->find();
		if ($admin)
		{
			exit(json_encode("请先清空所属该角色的管理员"));
		}
		else
		{
			$d = M('admin_role')->where("role_id=$role_id")->delete();
			if ($d)
			{
				exit(json_encode(1));
			}
			else
			{
				exit(json_encode("删除失败"));
			}
		}
	}


	public function admins()
	{
		$res = $list = array();
		$keywords = I('keywords');
		if (empty($keywords))
		{
			$res = D('admin')->select();
		}
		else
		{
			$res = D()->query("select * from __PREFIX__admin where user_name like '%$keywords%' order by admin_id");
		}
		$role = D('admin_role')->getField('role_id,role_name');
		if ($res && $role)
		{
			foreach ($res as $val)
			{
				$val['role'] = $role[$val['role_id']];
				$val['add_time'] = date('Y-m-d H:i:s', $val['add_time']);
				$list[] = $val;
			}
		}
		$this->assign('list', $list);
		$this->display();
	}

	public function admin_info()
	{

		if (I('uid'))
		{
			$user = M("user")->find(I('uid'));
			$this->assign('user', $user);
		}
		else
		{

			$admin_id = I('get.admin_id', 0);
			if ($admin_id)
			{

				$info = D('admin')->where("id=$admin_id")->find();
				$info['password'] = "";
				$this->assign('info', $info);
			}
			$act = empty($admin_id) ? 'add' : 'edit';
			$this->assign('act', $act);
		}

		$role = D('admin_role')->where('1=1')->select();
		$this->assign('role', $role);
		$this->display();
	}

	//添加管理员
	public function adminHandle()
	{
		$data = I('post.');

		if (empty($data['password']))
		{
			unset($data['password']);
		}
		else
		{
			// $b['password'] = get_pwd($newpwd, 1, $code);
			$data['password'] = get_pwd($data['password'], 1, $data['random_code']);
		}
		if ($data['act'] == 'add')
		{
			unset($data['admin_id']);
			$data['add_time'] = time();
			if (D('admin')->where("username='" . $data['username'] . "'")->count())
			{
				$this->error("此用户名已被注册，请更换", U('Index/admin_info'));
			}
			else
			{
				$r = D('admin')->add($data);
			}
		}

		if ($data['act'] == 'edit')
		{
			$r = D('admin')->where('id=' . $data['admin_id'])->save($data);
		}

		$da = I('get.');
		if ($da['act'] == 'del' && $da['admin_id'] > 1)
		{
			$r = D('admin')->where('id=' . $da['admin_id'])->delete();
			//exit(json_encode(1));
		}

		if ($r)
		{
			$this->success("操作成功", U('Index/admins'));
		}
		else
		{
			$this->error("操作失败", U('Index/admins'));
		}
	}


	// 行业更新
	public function industry_update()
	{
		if (IS_POST)
		{
			$id = I('id');
			$name = I('name');
			$icon = I('icon');
			if ($icon)
			{
				$data['icon'] = $icon;
			}
			$data['name'] = $name;
			if ($id)
			{
				$rs = M('industry')->where("id = {$id}")->save($data);
			}
			else
			{
				$data['add_time'] = time();
				$rs = M('industry')->add($data);
			}
			if ($rs)
			{
				$this->success("操作成功", U("industry"));
			}
			else
			{
				$this->error("操作失败");
			}
		}
		else
		{
			$id = I('id', 0, 'intval');
			$data = M('industry')->where("id=" . $id)->field('id,name,icon')->find();
			$this->assign('data', $data);
			$this->assign('id', $id);
			$this->display();
		}
	}

	public function industry_delete()
	{
		$id = I('id', 0);
		if (!$id)
		{
			$this->error("缺少主要参数");
		}
		$isHave = M("shop")->where("industry=" . $id)->count();
		if ($isHave > 0)
		{
			$this->error("该行业下有商家，不可删除");
		}
		$rs = M("industry")->where("id=" . $id)->delete();
		if ($rs)
		{
			$this->success("操作成功", U("industry"));
		}

	}

	// 提现设置
	public function tx_set()
	{
		$user = M("config_tx")->where("type=1")->order("id desc")->find();
		$shop = M("config_tx")->where("type=2")->order("id desc")->find();
		$agent = M("config_tx")->where("type=3")->order("id desc")->find();

		$this->assign('user', $user);
		$this->assign('shop', $shop);
		$this->assign('agent', $agent);
		$this->display();
	}

	// 提现设置
	public function tx_set_handle()
	{
		$data = I();
		if ($data)
		{
			$data["cre_date"] = date("Y-m-d H:i:s");
			if ($data["id"])
			{
				$id = $data["id"];
				unset($data["id"]);
				$rs = M("config_tx")->where("id=" . $id)->save($data);
			}
			else
			{
				$rs = M("config_tx")->add($data);
			}
		}
		else
		{
			$this->error("数据异常，无法添加");
		}
		if ($rs !== false)
		{
			$this->success("操作成功");
		}
		else
		{
			$this->error("操作失败，请重新操作");
		}
	}

	// 下发配置
	public function topay()
	{
		if (IS_POST)
		{
			$post = I();
			if ($post['id'] > 0)
			{
				$rs = M("config_topay")->where("id=" . $post['id'])->save($post);
			}
			else
			{
				$rs = M("config_topay")->add($post);
			}
			if ($rs !== false)
			{
				$this->success("修改成功");
			}
			else
			{
				$this->error("修改失败，请重新修改");
			}
			exit();
		}
		$data = M("config_topay")->order("id desc")->find();
		$this->assign("data", $data);
		$this->display();
	}

	// 第三方支付
	public function pay3()
	{
		if (IS_POST)
		{
			$post = I();
			if ($post['appid'])
			{
				$post['appid'] = htmlspecialchars_decode($post['appid']);
			}
			if ($post['key1'])
			{
				$post['key1'] = htmlspecialchars_decode($post['key1']);
			}
			if ($post['key2'])
			{
				$post['key2'] = htmlspecialchars_decode($post['key2']);
			}
			$post['cre_date'] = date("Y-m-d H:i:s");
			if ($post['id'] > 0)
			{
				$rs = M("config_pay3")->where("id=" . $post['id'])->save($post);
			}
			else
			{
				$rs = M("config_pay3")->add($post);
			}
			if ($rs !== false)
			{
				$this->success("修改成功");
			}
			else
			{
				$this->error("修改失败，请重新修改");
			}
			exit();
		}
		$data = M("config_pay3")->where("type=1")->order("id desc")->find();    // 微信支付配置
		$data2 = M("config_pay3")->where("type=2")->order("id desc")->find();    // 支付宝支付配置
		$this->assign("data", $data);
		$this->assign("data2", $data2);
		$this->display();
	}

	// 短信配置
	public function sms()
	{
		if (IS_POST)
		{
			$post = I();
			if ($post['id'] > 0)
			{
				$rs = M("config_sms")->where("id=" . $post['id'])->save($post);
			}
			else
			{
				$rs = M("config_sms")->add($post);
			}
			if ($rs !== false)
			{
				$this->success("修改成功");
			}
			else
			{
				$this->error("修改失败，请重新修改");
			}
			exit();
		}
		$data = M("config_sms")->getField('id,name,type,update_time,acc_tmp,account,status');

		$this->assign("data", $data);
		$this->display();
	}

	public function edit_sms()
	{

		$id = I('id');
		if (IS_POST)
		{
			$data = I();

			$status = $data['status'];
			$where['type'] = $data['type'];
			if ($status == 1)
			{

				M('config_sms')->where('id!=' . $data['id'])->where($where)->save(array('status' => 0));

			}
			else
			{
				$count = M('config_sms')->where('status=1')->where($where)->count();


				if ($count <= 1)
				{
					$this->error('必须默认一个短信开启');
					exit();
				}
			}
			$data['update_time'] = time();
			$res = M('config_sms')->save($data);
			if ($res)
			{
				$this->success('修改成功', U('index/sms'));
			}
			else
			{
				$this->success('修改失败');
			}
			exit();
		}
		$data = M("config_sms")->find($id);
		if ($data['type'] == 0)
		{
			$type = '验证码';
		}
		else
		{
			$type = '开发中';
		}
		$this->assign("data", $data);
		$this->assign("type", $type);

		$this->display();
	}


	// 分销提成配置
	public function fxtc()
	{
		if (IS_POST)
		{
			$post = I("id");
			foreach ($post as $id => $val)
			{
				M("config_fx")->where("id=" . $id)->setField("val", $val);
			}
			$this->success("修改成功");
			exit();
		}
		$list = M("config_fx")->order("id asc")->select();
		$this->assign("list", $list);
		$this->display();
	}

	// 项目配置
	public function project()
	{
		if (IS_POST)
		{
			$post = I("id");
			foreach ($post as $id => $val)
			{
				//此处目前不需要更新，
				/* if($id == 7){
					 $this->add_service_description($val);
				 }*/
				M("config_project")->where("id=" . $id)->setField("val", $val);
			}
			$this->success("修改成功");
			exit();
		}
		$where['is_display'] = 1;
		$list = M("config_project")->where($where)->order("id asc")->select();
		$this->assign("list", $list);
		$this->display();
	}

	// 快递设置
	public function express()
	{
		$list = M("express_service")->order("listorder asc")->select();
		$this->assign("list", $list);
		$this->display();
	}

	public function express_upd()
	{
		$id = I('id');
		$status = I('is_start');
		if ($status == 1)
		{
			$is_start = 0;
			$msg = "关闭成功";
		}
		else
		{
			$is_start = 1;
			$msg = "开启成功";
		}
		$result = M('express_service')->where('id=' . $id)->save(array('is_start' => $is_start));
		if ($result)
		{
			$this->success($msg, U('Index/express'));
		}
		else
		{
			$this->error("操作失败，请重新操作");
		}
	}

	public function express_detail()
	{
		$id = I('id');
		$detail = M("express_service")->where("id=$id")->find();
		$this->assign("detail", $detail);
		$this->display();
	}

	public function express_detail_upd()
	{
		$data = I();
		$id = $data['id'];
		unset($data['id']);
		$result = M('express_service')->where('id=' . $id)->save($data);
		if ($result)
		{
			$this->success("操作成功", U('Index/express'));
		}
		else
		{
			$this->error("操作失败，请重新操作");
		}
	}

	/**
	 * 分类列表
	 * @return  json
	 * @wz
	 */
	public function industryList()
	{
		$id = I('id');
		if ($id)
		{
			$list = M('industry')->where("id = {$id}")->field('id,name,icon')->find();
		}
		else
		{
			$list = M('industry')->field('id,name,icon')->select();
		}
		jsonout('分类列表', 0, $list);
	}

	public function qrcode()
	{
		$where['is_display'] = 1;
		$config = M('config_poster')->where($where)->select();
		if (IS_POST)
		{
			$ratio = I('ratio');
			$type = I('type');
			foreach ($ratio as $k => $v)
			{
				if ($type[$k] == 'uint')
				{
					$v = abs($v);
				}
				if ($type[$k] == 'file')
				{
					//$ok = common_upload_photo('file' . $k, 'web');

					// if($ok['success']==1){
					// $v = $ok['path'];
					// }
					$v = $v;
				}
				M('config_poster')->where("id=$k")->setField('val', $v);
			}
			//判断是否开启上传到7牛云
			$where['name'] = 'tuiguang_haibao';
			$config = M('config_poster')->where($where)->find();
			$data['tuiguang_haibao'] = $config['val'];
			$json_string = json_encode($data);
			file_put_contents('Public/data.txt', $json_string);
			//set_config_cache(); //重新设置缓存，每次修改都要执行一次，要不然不会实时更新

            //将所有用户的二维码删除
            M('user')->setField('qrcode','');
			$this->success('设置完成');
		}
		else
		{
			$goods_img = "/Public/images/img_place.png";
			$this->assign('goods_img', $goods_img);
			$this->assign('config', $config);
			$this->display();
		}
	}

	/*
	 * 七牛云配置
	 * @zd
	 * */
	public function qiniuset()
	{
		if (IS_POST)
		{
			$id = I("id", 1);
			$access_key = I("access_key", '');
			$secret_key = I("secret_key", '');
			$bucket = I("bucket", '');
			$img_domain = I("img_domain", '');
			if (!$id || !$access_key || !$secret_key || !$bucket || !$img_domain)
			{
				$this->error("各项内容不可为空");
			}
			$data['access_key'] = $access_key;
			$data['secret_key'] = $secret_key;
			$data['bucket'] = $bucket;
			$data['img_domain'] = $img_domain;
			$rs = M("config_qiniu")->where("id=" . $id)->save($data);
			if ($rs !== false)
			{
				put_qiniu_file();
				$this->success("修改成功");
			}
			else
			{
				$this->error("修改失败，请重新操作");
			}
			exit();
		}
		$data = M("config_qiniu")->find();
		$this->assign("data", $data);
		$this->display();
	}

	/**
	 * 打印列表
	 */
	public function stamp()
	{
		$list = M("express_print_server")->select();
		$this->assign("list", $list);
		$this->display();
	}

	/**
	 * 打印详情
	 */
	public function stamp_detail()
	{
		$id = I('id', 0);
		$detail = M("express_print_server")->find($id);
		$this->assign("detail", $detail);
		$this->display();
	}

	/**
	 * 修改快递打印
	 */
	public function stamp_detail_upd()
	{
		$data = I();

		$id = $data['id'];
		if (empty($id))
		{
			$data['add_time'] = time();
			$result = M('express_print_server')->add($data);
		}
		else
		{
			unset($data['id']);
			$data['up_time'] = time();

			$result = M('express_print_server')->where('id=' . $id)->save($data);
		}

		if ($result)
		{
			$this->success("操作成功", U('Index/stamp'));
		}
		else
		{
			$this->error("操作失败，请重新操作");
		}
	}

	/**
	 * 设置 打印接口是否开启
	 */
	public function stamp_upd()
	{
		$id = I('id');
		$status = I('is_start');
		if ($status == 1)
		{
			$is_start = 0;
			$msg = "关闭成功";
		}
		else
		{
			$is_start = 1;
			$msg = "开启成功";
		}
		$result = M('express_print_server')->where('id=' . $id)->save(array('is_start' => $is_start));
		if ($is_start == 1)
		{
			$where['id'] = array('neq', $id);
			$result = M('express_print_server')->where($where)->save(array('is_start' => 0));
		}
		if ($result)
		{
			$this->success($msg, U('Index/stamp'));
		}
		else
		{
			$this->error("操作失败，请重新操作");
		}
	}

	//快递打印接口删除
	public function stamp_delete()
	{
		$id = I('id', 0);
		$res = M('express_print_server')->where('id=' . $id)->delete();
		if ($res)
		{
			$this->success('删除成功', U('Index/stamp'));
		}
		else
		{
			$this->error('删除失败');
		}
	}

	public function appset()
	{
		if (IS_POST)
		{
			$resData = I('post.');
			$app_add = M('config_app')->where('id=1')->data($resData)->save();
			if ($app_add)
			{
				$this->success('添加成功');
			}
			else
			{
				$this->error('添加失败');
			}
		}
		else
		{
			$app_info = M('config_app')->find();
			$this->assign('data', $app_info);
			$this->display();
		}
	}

	/**
	 * Notes:获取城市首字母大写并存储数据库中
	 * User: WangSong
	 * Date: 2019/10/29
	 * Time: 15:02
	 */
	public function add_indexes()
	{

		set_time_limit(0);
		$region = M("region")->where("indexes IS NULL")->limit(500)->select();

		foreach ($region as $k => $v)
		{
			//print_r($v);exit();
			$re = $this->getfirstchar($v["name"]);
			//print_r($re);exit();
			$da["indexes"] = $re;
			//print_r($da);echo "<br/>";
			$re = M("region")->where("id=" . $v["id"])->save($da);
			if (!$re)
			{
				$da1["indexes"] = 2;
				M("region")->where("id=" . $v["id"])->save($da1);
				echo "ID为" . $v["id"] . "执行失败";
				echo "<br/>";
			}
		}//exit();
		$this->add_json();
	}

	/**
	 * Notes:获取首字母
	 * User: WangSong
	 * Date: 2019/10/29
	 * Time: 17:28
	 * @param $s0
	 * @return string|null
	 */
	function getfirstchar($s0)
	{
		//header('content-type:text/html;charset=utf-8;');
		$fchar = ord(mb_substr($s0, 0, 1, 'utf-8'));
		// print_r($fchar);exit();
		if (($fchar >= ord("a") and $fchar <= ord("z")) or ($fchar >= ord("A") and $fchar <= ord("Z")))
		{
			return strtoupper(chr($fchar));
		}
		$s = iconv("UTF-8", "GBK", $s0);
		$asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
		if ($asc >= -20319 and $asc <= -20284)
		{
			return "A";
		}
		if ($asc >= -20283 and $asc <= -19776)
		{
			return "B";
		}
		if ($asc >= -19775 and $asc <= -19219)
		{
			return "C";
		}
		if ($asc >= -19218 and $asc <= -18711)
		{
			return "D";
		}
		if ($asc >= -18710 and $asc <= -18527)
		{
			return "E";
		}
		if ($asc >= -18526 and $asc <= -18240)
		{
			return "F";
		}
		if ($asc >= -18239 and $asc <= -17923)
		{
			return "G";
		}
		if ($asc >= -17922 and $asc <= -17418)
		{
			return "H";
		}
		if ($asc >= -17417 and $asc <= -16475)
		{
			return "J";
		}
		if ($asc >= -16474 and $asc <= -16213)
		{
			return "K";
		}
		if ($asc >= -16212 and $asc <= -15641)
		{
			return "L";
		}
		if ($asc >= -15640 and $asc <= -15166)
		{
			return "M";
		}
		if ($asc >= -15165 and $asc <= -14923)
		{
			return "N";
		}
		if ($asc >= -14922 and $asc <= -14915)
		{
			return "O";
		}
		if ($asc >= -14914 and $asc <= -14631)
		{
			return "P";
		}
		if ($asc >= -14630 and $asc <= -14150)
		{
			return "Q";
		}
		if ($asc >= -14149 and $asc <= -14091)
		{
			return "R";
		}
		if ($asc >= -14090 and $asc <= -13319)
		{
			return "S";
		}
		if ($asc >= -13318 and $asc <= -12839)
		{
			return "T";
		}
		if ($asc >= -12838 and $asc <= -12557)
		{
			return "W";
		}
		if ($asc >= -12556 and $asc <= -11848)
		{
			return "X";
		}
		if ($asc >= -11847 and $asc <= -11056)
		{
			return "Y";
		}
		if ($asc >= -11055 and $asc <= -10247)
		{
			return "Z";
		}
		return null;
	}

	/**
	 * Notes:输出json文件（Android）
	 * User: WangSong
	 * Date: 2019/10/29
	 * Time: 17:26
	 */
	public function echo_android_josn()
	{


		$data = array();
		$region_in = M("region")->where("level = 2")->order("indexes")->group("indexes")->field("indexes")->select();
		$resultList = array();
		$regio_new = M("region")->where("level = 2")->order("indexes")->select();
		foreach ($regio_new as $k => $v)
		{
			$data1["gStr"] = $v["indexes"];
			$data1["id"] = $v["id"];
			$data1["name"] = $v["name"];
			$data[$v["indexes"]][] = $data1;
		}
		/* foreach ($region_in as $k=>$v){
			 //$data[$k]=$v["indexes"];
			 $region = M("region")->where("indexes = '".$v["indexes"]."'")->select();
			$data1 = array();
			 foreach ($region as $key=>$value){
				 $data1[$key]["gStr"] = $value["indexes"];
				 $data1[$key]["id"] = $value["id"];
				 $data1[$key]["name"] = ($value["name"]);

			 }
			 $newItem[$k] = array(
				 $v["indexes"]=>$data1,

			 );
			 $resultList = $newItem;

		 }*/
		// dump($resultList);exit();
		echo "<pre>";
		print_r($data);
		die();
// 把PHP数组转成JSON字符串
		$json_string = $this->json_encode_no_zh($data);

// 写入文件
		file_put_contents('region.json', ($json_string));
		exit();
	}


	/**
	 * Notes:输出json文件（ios）
	 * User: WangSong
	 * Date: 2019/10/29
	 * Time: 17:26
	 */
	public function echo_ios_josn()
	{


		$data = array();
		$region_in = M("region")->where("level = 2")->order("indexes")->group("indexes")->field("indexes")->select();
		$resultList = array();
		foreach ($region_in as $k => $v)
		{
			//$data[$k]=$v["indexes"];
			$region = M("region")->where("level=2 and  indexes = '" . $v["indexes"] . "'")->select();
			$data1 = array();
			foreach ($region as $key => $value)
			{
				$data1[$key]["gStr"] = $value["indexes"];
				$data1[$key]["id"] = $value["id"];
				$data1[$key]["name"] = ($value["name"]);

			}
			$newItem[$k] = array(
				"name" => $v["indexes"],
				"citys" => $data1

			);
			$resultList = $newItem;

		}

// 把PHP数组转成JSON字符串
		$json_string = $this->json_encode_no_zh($resultList);

// 写入文件
		file_put_contents('ios_region.json', ($json_string));
		exit();
	}


	/**
	 * Notes:json中文不转编译
	 * User: WangSong
	 * Date: 2019/10/29
	 * Time: 17:42
	 * @param $arr
	 * @return string|string[]|null
	 */
	function json_encode_no_zh($arr)
	{
		$str = str_replace("\\/", "/", json_encode($arr));
		$search = "#\\\u([0-9a-f]+)#ie";

		if (strpos(strtoupper(PHP_OS), 'WIN') === false)
		{
			$replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
		}
		else
		{
			$replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
		}

		return preg_replace($search, $replace, $str);
	}

	/**
	 * Notes:输出省市县json文件
	 * User: WangSong
	 * Date: 2019/11/8
	 * Time: 9:25
	 */
	public function echo_country_json()
	{
		$data = array();
		$region_in = M("region")->where("level = 1")->field("id,name")->select();

		foreach ($region_in as $k => $v)
		{
			$region_cities = M("region")->where("level=2 and parent_id=" . $v["id"])->field("id,name")->select();
			foreach ($region_cities as $key => $vaule)
			{
				$region_countrys = M("region")->where("level=3 and parent_id=" . $vaule["id"])->field("id,name")->select();
				foreach ($region_countrys as $ke => $va)
				{
					$data[$k]["cities"][$key]["counties"][$ke]["areaId"] = $va["id"];
					$data[$k]["cities"][$key]["counties"][$ke]["areaName"] = $va["name"];
				}
				$data[$k]["cities"][$key]["areaId"] = $vaule["id"];
				$data[$k]["cities"][$key]["areaName"] = $vaule["name"];
			}
			$data[$k]["areaId"] = $v["id"];
			$data[$k]["areaName"] = $v["name"];

		}
		echo "<pre>";
		print_r($data);
		exit();
		// 把PHP数组转成JSON字符串
		$json_string = $this->json_encode_no_zh($data);
		print_r($json_string);
		exit();
		// 写入文件
		file_put_contents('region_country.json', ($json_string));
		exit();
	}

	/**
	 * 公众号菜单栏的修改
	 * @litianyu
	 */
	public function wechatMenu()
	{
		//渲染一级菜单栏
		$list = M('wechat_menu')->where('level=1')->select();
		$this->assign('list', $list);
		$this->display();
	}

	/**
	 * 添加微信公众号菜单栏
	 * @litianyu
	 */
	public function addMenu()
	{
		if (IS_POST)
		{
			$data = I();
			if ($data['id'])
			{
				//修改
				$save['name'] = $data['name'];
				$save['url'] = $data['url'];
				$where['id'] = $data['id'];
				M('wechat_menu')->where($where)->save($save);
			}
			else
			{
				//加入数据库
				$num = M('wechat_menu')->where('level=1')->count();
				if ($num > 2)
				{
					$this->success('主菜单最多2个');
				}
				$array = array(
					'name' => $data['name'],
					'url' => $data['url'],
					'level' => 1,
					'top' => 0,
					'cre_time' => time()
				);
				M('wechat_menu')->add($array);
			}

			$this->success('操作成功', U('Index/wechatMenu'));
		}
		else
		{
			$id = I('id');
			if ($id)
			{
				$info = M('wechat_menu')->find($id);
				$this->assign('info', $info);
			}
			$this->display();
		}
	}

	/**
	 * 删除公众号菜单栏
	 * @litianyu
	 */
	public function deleteMenu()
	{
		$id = I('id');
		M('wechat_menu')->where('top=' . $id)->delete();
		M('wechat_menu')->delete($id);
		$this->success('删除成功');
	}

	/**
	 * 子菜单列表
	 * @litianyu
	 */
	public function menuZ()
	{
		$id = I('id');

		$list = M('wechat_menu')->where('top=' . $id)->select();
		$this->assign('list', $list);
		$this->assign('top', $id);
		$this->display();
	}

	/**
	 * 添加子菜单
	 * @litianyu
	 */
	public function addMenuZ()
	{
		$id = I('id');//主菜单id
		if (IS_POST)
		{
			$data = I();
			if ($data['menu_id'])
			{
				//修改
				$save['name'] = $data['name'];
				$save['url'] = $data['url'];
				$where['id'] = $data['menu_id'];
				M('wechat_menu')->where($where)->save($save);
			}
			else
			{
				//查询有几个二级菜单
				$num = M('wechat_menu')->where('top=' . $data['top'])->count();
				if ($num > 4)
				{
					$this->success('子菜单最多5个');
				}
				//加入数据库
				$array = array(
					'name' => $data['name'],
					'url' => $data['url'],
					'level' => 2,
					'top' => $data['top'],
					'cre_time' => time()
				);
				M('wechat_menu')->add($array);
			}

			$this->success('操作成功');
		}
		else
		{
			$menu_id = I('menu_id');
			$info = M('wechat_menu')->find($menu_id);

			$this->assign('top', $id);
			$this->assign('info', $info);
			$this->display();
		}
	}


	/**
	 * 运行创建微信公众号
	 * @litianyu
	 */
	public function creatMenu()
	{
		//查询数据库进行循环判断
		$list = M('wechat_menu')->where('level=1')->select();
		$new = array();
		foreach ($list as $k => $v)
		{
			//进行数据处理
			if (empty($v['url']))
			{
				//说明有二级菜单
				$new[$k]['name'] = $v['name'];
				//查询他的二级菜单
				$listTwo = M('wechat_menu')->where('top=' . $v['id'])->select();
				foreach ($listTwo as $kk => $vv)
				{
					$new[$k]['sub_button'][$kk]['type'] = 'view';
					$new[$k]['sub_button'][$kk]['name'] = $vv['name'];
					$new[$k]['sub_button'][$kk]['url'] = $vv['url'];
				}
			}
			else
			{
				//自己有链接 无二级菜单
				$new[$k]['name'] = $v['name'];
				$new[$k]['type'] = 'view';
				$new[$k]['url'] = $v['url'];
			}
		}

		$array['button'] = $new;
		$end = json_encode($array, JSON_UNESCAPED_UNICODE);
		$access_token = $this->getToken();
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
		$this->postcurl($url, $end);
		$this->success('修改成功');

	}

	/**
	 * @return mixed
	 * 获取token的值
	 * @litianyu
	 */
	public function getToken()
	{
		//查询数据库
		$config = M('config_pay3')->where('type=3')->find();
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $config['appid'] . "&secret=" . $config['key2'];
		$date = $this->postcurl($url);
		$access_token = $date['access_token'];
		return $access_token;
	}

	/**
	 * curl请求
	 * @litianyu
	 */
	function postcurl($url, $data = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data))
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output = json_decode($output, true);
	}


	/**
	 * Title： 导出模板列表
	 * Note：
	 * User： zd
	 * Date： 2020/6/16 9:14
	 */
	public function export_template_list()
	{
		$list = M("export_template")->where("is_del=0")->order("id desc")->select();
		foreach ($list as $key => &$value)
		{
			$value['type'] = get_export_template_type($value['type']);
			$value['field_cn'] = implode(",", json_decode($value['field_cn'], true));
		}
		$this->assign("list", $list);
		$this->display();
	}


	/**
	 * Title：删除导出模板
	 * Note：
	 * User： zd
	 * Date： 2020/6/16 10:50
	 */
	public function export_template_del()
	{
		$id = I("id", 0);
		if ($id)
		{
			$rs = M("export_template")->where("id=" . $id)->setField("is_del", 1);
			if ($rs !== false)
			{
				$this->success("删除成功", U('export_template_list'));
			}
			else
			{
				$this->error("删除失败");
			}
		}
		else
		{
			$this->error("删除失败");
		}
	}

	/**
	 * Title：添加/修改导出模板
	 * Note：
	 * User： zd
	 * Date： 2020/6/16 10:50
	 */
	public function export_template_detail()
	{
		$id = I("id", 0);
		$type = I("type", 0);
		if (IS_POST)
		{
			$id = I("id", 0);
			$name = I("name", '');
			$type = I("type", 0);
			$field_all = I("field_en");
			if (!$name)
			{
				$this->error("模板名称必须填写");
			}
			if (!$type)
			{
				$this->error("模板类型必须选择");
			}
			if (!$field_all)
			{
				$this->error("请选择导出列");
			}
			$field_en = array();
			$field_cn = array();
			foreach ($field_all as $key => $value)
			{
				$fdArr = explode("___", $value);
				$field_en[] = $fdArr[0];
				$field_cn[] = $fdArr[1];
			}
			$data['name'] = $name;
			$data['type'] = $type;
			$data['field_en'] = json_encode($field_en);
			$data['field_cn'] = json_encode($field_cn);
			if ($id)
			{
				$res = M("export_template")->where("id=" . $id)->save($data);
			}
			else
			{
				$res = M("export_template")->add($data);
			}
			if ($res !== false)
			{
				$this->success("操作成功", U('export_template_list'));
			}
			else
			{
				$this->error("操作失败，请重新操作");
			}
			exit();
		}
		if ($id)
		{
			$data = M("export_template")->where("id=" . $id)->find();
			if (!$data)
			{
				$this->error("没有此模板");
			}
			$data['field_cn'] = json_decode($data['field_cn'], true);
			$data['field_en'] = json_decode($data['field_en'], true);
			if (!$type)
			{
				$type = $data['type'];
			}
		}

		$field_list = M("export_fields")->where("type=" . $type)->order('sorting asc')->select();
		foreach ($field_list as $key => &$value)
		{
			if (in_array($value['id'], $data['field_en']))
			{
				$value['is_select'] = 1;
			}
			else
			{
				$value['is_select'] = 0;
			}
		}
		$type_list = get_export_template_type();
		$this->assign("data", $data);
		$this->assign("type", $type);
		$this->assign("type_list", $type_list);
		$this->assign("field_list", $field_list);
		$this->display();

	}


	/**
	 * Title： 导出字段管理
	 * Note：
	 * User： zd
	 * Date： 2020/6/18 15:03
	 */
	public function export_fields_list()
	{
		$list = M("export_fields")->where("is_del=0")->group("type")->field('table_name,type,count(*) as num')->select();
		$this->assign("list", $list);
		$this->display();
	}


	/**
	 * Title： 导出字段表的删除
	 * Note： 删除的时候，要看看是否被引用了，如果有被引用，则提示不能删除。
	 * User： zd
	 * Date： 2020/6/18 15:24
	 */
	public function export_fields_del()
	{
		$type = I("type", 0);
		if (!$type)
		{
			$this->error("参数有误，请联系技术");
		}
		$isTemp = M("export_template")->where("is_del=0 and type=" . $type)->find();
		$fieldEn = json_decode($isTemp['field_en'], true);
		if (count($fieldEn) > 0)
		{
			$this->error("该类型已被模板引用，不可删除");
		}
		$res = M("export_fields")->where("type=" . $type)->setField("is_del", 1);
		if ($res !== false)
		{
			$this->success("操作成功");
		}
		else
		{
			$this->error("操作制作，请重新操作");
		}
	}

	/**
	 * Title： 导出字段表某个类型的详细字段列表
	 * Note：
	 * User： zd
	 * Date： 2020/6/18 15:24
	 */
	public function export_fields_detail()
	{
		$type = I("type", 0);
		if (!$type)
		{
			$this->error("参数有误，请联系技术");
		}
		$list = M("export_fields")->where("is_del=0 and type=" . $type)->order("sorting asc,id asc")->select();
		$this->assign("list", $list);
		$this->assign("type", $type);
		$this->display();
	}

	/**
	 * Title： 添加/修改单个字段信息
	 * Note：
	 * User： zd
	 * Date： 2020/6/19 17:08
	 */
	public function export_fields_edit_one()
	{
		$id = I("id", 0);
		$type = I("type", 0);
		$data = array();
		if (IS_POST)
		{
			// 添加的时候，只能添加同表的字段，这里要做严格测试 ***
			$postData = I();
			if ($postData["type"] < 1)
			{
				$this->error("请选择 导出类型");
			}
			if (!$postData["table_name"])
			{
				$this->error("请输入 字段表名");
			}
			if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $postData["table_name"]))
			{
				$this->error("字段表名 不得含有中文");
			}
			if (!$postData["field_en"])
			{
				$this->error("请输入 字段名称");
			}
			if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $postData["field_en"]))
			{
				$this->error("字段名称 不得含有中文");
			}
			if ($postData["field_en_alias"] && preg_match("/([\x81-\xfe][\x40-\xfe])/", $postData["field_en_alias"]))
			{
				$this->error("字段别名 不得含有中文");
			}
			if (!$postData["field_cn"])
			{
				$this->error("请输入 字段中文名称");
			}
			// 不同的关联类型，输入项不一样
			if ($postData['is_relation'] == 1)
			{
				if (!$postData['relation_table_name'])
				{
					$this->error("请输入 被关联表");
				}
				if (!$postData['relation_condition_field_en'])
				{
					$this->error("请输入 被关联表条件字段");
				}
				if (!$postData['relation_field_en'])
				{
					$this->error("请输入 被关联表返回字段");
				}
			}
			elseif ($postData['is_relation'] == 2)
			{
				if (!$postData['relation_table_name'])
				{
					$this->error("请输入 被关联方法");
				}
			}
			if (!is_numeric($postData['sorting']))
			{
				$postData['sorting'] = 100;
			}
			if ($postData['id'])
			{
				$where['id'] = $postData['id'];
			}
			unset($postData['id']);
			if ($where['id'])
			{
				$res = M("export_fields")->where($where)->save($postData);
			}
			else
			{
				$res = M("export_fields")->add($postData);
			}
			if ($res !== false)
			{
				// $this->success("操作成功", U('export_fields_detail', array('type'=>$type)));
				$this->success("操作成功");
			}
			else
			{
				$this->error("操作失败，请重新操作");
			}
			exit();
		}
		if ($id)
		{
			$data = M("export_fields")->where("id=" . $id)->find();
			if (!$data || $data['is_del'] == 1)
			{
				$this->success("此字段已被删除");
			}
			$type = $data['type'];
			$is_relation = $data['is_relation'];
		}
		$table_name = "";
		if ($type)
		{
			$table_name = M("export_fields")->where("type=" . $type)->getField("table_name");
		}
		if (!$data)
		{
			$data['table_name'] = $table_name;
		}
		$type_list = get_export_template_type();
		$this->assign("type", $type);
		$this->assign("type_list", $type_list);
		$this->assign("data", $data);
		$this->assign("table_name", $table_name);
		$this->assign("is_relation", $is_relation);
		$this->display();
	}

	/**
	 * Title：删除单个字段
	 * Note：
	 * User： zd
	 * Date： 2020/6/19 17:17
	 */
	public function export_fields_del_one()
	{
		$id = I("id", 0);
		if (!$id)
		{
			$this->error("参数有误，请联系技术");
		}
		$fieldInfo = M("export_fields")->where("is_del=0 and id=" . $id)->find();
		if (!$fieldInfo)
		{
			$this->success("此字段已被删除");
		}
		$isTemp = M("export_template")->where("is_del=0 and type=" . $fieldInfo['type'])->find();
		$fieldEn = json_decode($isTemp['field_en'], true);
		if (count($fieldEn) > 0 && in_array($id, $fieldEn))
		{
			$this->error("该字段已被导出模板引用，不可删除");
		}
		$res = M("export_fields")->where("id=" . $id)->setField("is_del", 1);
		if ($res !== false)
		{
			$this->success("操作成功");
		}
		else
		{
			$this->error("操作制作，请重新操作");
		}
	}

	/**
	 * @param $type
	 * Notes:添加服务说明
	 * User: WangSong
	 * Date: 2020/7/4
	 * Time: 11:39
	 */
	public function add_service_description($type)
	{
		$where['type'] = array('eq', $type);
		$service_description = M('service_description')->where($where)->find();
		$data = array();
		$title = "相关方可以在";
		switch ($type)
		{
			case 1:
				$data['name'] = '支付后立即获得收益';
				$data['info'] = '支付后立即获得收益';
				$data['type'] = 1;
				break;
			case 2:
				$data['name'] = '收货后立即获得收益';
				$data['info'] = '收货后立即获得收益';
				$data['type'] = 2;
				break;
			case 3:
				$data['name'] = '收货7天后获得收益';
				$data['info'] = '收货7天后获得收益';
				$data['type'] = 3;
				break;
			case 4:
				$data['name'] = '发货7天后获得收益';
				$data['info'] = '发货7天后获得收益';
				$data['type'] = 4;
				break;
		}
		$data['info'] = $title . $data['info'];
		if ($data)
		{
			if (!$service_description)
			{
				M('service_description')->add($data);
			}
		}

	}

	/**
	 * Notes:签到设置
	 * User: WangSong
	 * Date: 2020/9/28
	 * Time: 8:14
	 */
	public function sign()
	{
		if (IS_POST)
		{
			$data = I();
			if ($data['is_cycle'] == 1)
			{
				if ($data['one_cycle_days'] < 1)
				{
					$this->error('周期天数不能为0');
				}
				if ($data['one_cycle_days'] > 99)
				{
					$this->error('周期天数不能超过99天');
				}
				if ($data['sign_award'] <= 0)
				{
					$this->error('普通奖励需要大于0');
				}
				$data['special_award'] = str_replace('，', ',', $data['special_award']);
				if (!preg_match("/^[1-9][0-9]*$/", $data['one_cycle_days']))
				{
					$this->error('周期天数格式不正确');
				}
			}
			else
			{
				$data['is_cycle'] = 0;
			}
			if (!preg_match("/^[1-9][0-9]*$/", $data['sign_award']))
			{
				$this->error('普通奖励格式不正确');
			}
			if (!preg_match("/^[1-9][0-9]*$/", $data['first_sign_award']))
			{
				$this->error('首次签到奖励格式不正确');
			}
			if (!preg_match("/^[1-9][0-9]*$/", $data['cycle_days']))
			{
				$this->error('超过几天算首次签到格式不正确');
			}
			if ($data['cycle_days'] < 7)
			{
				$this->error('超过几天算首次签到不能小于7天');
			}
			$re = M('config_signin')->where('id=' . $data['id'])->save($data);
			if ($re)
			{
				$this->success('操作成功');
				exit();
			}
			else
			{
				$this->error('操作失败');
			}
		}
		$config = M('config_signin')->find();
		$this->assign('data', $config);
		$this->display();
	}


}
