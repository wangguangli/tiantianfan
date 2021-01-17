<?php

namespace Admin\Controller;

//抽奖功能
class LotteryController extends CommonController
{


	//广告列表
	public function index()
	{
		$page = page('lottery');
		$this->assign('list', $page['list']);
		$this->assign('page', $page['show']);
		$this->display();
	}


	public function log()
	{
		$id = I('id', 0, 'intval');
		$page = page('lottery_log', 'lottery_id=' . $id);
		$user = get_array_column($page['list'], 'user_id', 'get_users');

		$this->assign('user', $user);
		$this->assign('list', $page['list']);
		$this->assign('page', $page['show']);
		$this->display();
	}

	public function add()
	{
		if (IS_POST)
		{
			$title = I('title', '', 'trim');
			$begin_date = I('begin_date');
			$end_date = I('end_date');
			$day_count = I('day_count', 0, 'intval');
			$day_sum = I('day_sum', 0, 'intval');
			$lottery_item = I('lottery_item');

			if (empty($title))
			{
				$this->error('请输入标题');
			}
			if (empty($begin_date) || empty($end_date))
			{
				$this->error('请输入抽奖日期');
			}
			if (strtotime($begin_date) > strtotime($end_date))
			{
				$this->error('开始日期不能大于结束日期');
			}
			if (empty($lottery_item))
			{
				$this->error('请输入奖项');
			}

			$first = array_slice($lottery_item, 0, 1);
			if ($first)
			{
				$this->error('第一个奖只能为空奖');
			}


			$data = array(
				'title' => $title,
				'begin_date' => $begin_date,
				'end_date' => $end_date,
				'day_count' => $day_count,
				'day_sum' => $day_sum,
				'time' => time(),
				'ip' => get_client_ip()
			);
			$id = M('lottery')->add($data);
			$all = array();

			foreach ($lottery_item as $i)
			{
				$all[] = array(
					'lottery_id' => $id,
					'item_val' => abs($i)
				);
			}
			M('lottery_item')->addAll($all);
			$this->success('添加成功', U('Lottery/index'));
			exit;
		}

		$this->display();
	}


	public function update()
	{
		$id = I('id', 0, 'intval');
		if (IS_POST)
		{
			$title = I('title', '', 'trim');
			$begin_date = I('begin_date');
			$end_date = I('end_date');
			$day_count = I('day_count', 0, 'intval');
			$day_sum = I('day_sum', 0, 'intval');
			$lottery_item = I('lottery_item');

			if (empty($title))
			{
				$this->error('请输入标题');
			}
			if (empty($begin_date) || empty($end_date))
			{
				$this->error('请输入抽奖日期');
			}
			if (strtotime($begin_date) > strtotime($end_date))
			{
				$this->error('开始日期不能大于结束日期');
			}

			$data = array(
				'title' => $title,
				'begin_date' => $begin_date,
				'end_date' => $end_date,
				'day_count' => $day_count,
				'day_sum' => $day_sum,
			);
			M('lottery')->where('id=' . $id)->save($data);

			$all = array();

			M('lottery_item')->where('lottery_id=' . $id)->setField('lottery_id', -$id);
			foreach ($lottery_item as $i)
			{
				$all[] = array(
					'lottery_id' => $id,
					'item_val' => abs($i)
				);
			}
			M('lottery_item')->addAll($all);
			$this->success('添加成功', U('Lottery/index'));
			exit;
		}

		$lottery = M('lottery')->find($id);
		$item = M('lottery_item')->where('lottery_id=' . $id)->select();

		$this->assign('lottery', $lottery);
		$this->assign('item', $item);
		$this->display();
	}


	public function close()
	{
		$id = I('id', 0, 'intval');
		M('lottery')->where('id=' . $id)->setField('is_close', 1);
		$this->success('OK');
	}


	public function delete()
	{
		$id = I('id', 0, 'intval');
		$c = M('lottery_log')->where('lottery_id=' . $id)->count();
		if ($c)
		{
			$this->error('不能删除有人参与的奖,只能关闭');
		}
		M('lottery')->where('id=' . $id)->delete();
		$this->success('已删除');
	}
}